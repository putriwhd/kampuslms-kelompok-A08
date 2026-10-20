<?php

namespace Tests\Feature;

use App\Models\Assignment;
use App\Models\Course;
use App\Models\Grade;
use App\Models\Submission;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class WeekSevenDeliveryTest extends TestCase
{
    use RefreshDatabase;

    public function test_authentication_and_password_reset_pages_are_routed(): void
    {
        $this->assertSame(url('/login'), route('login'));
        $this->assertSame(url('/api/login'), route('api.login'));

        $this->get('/login')
            ->assertOk()
            ->assertSee('Masuk ke KampusLMS')
            ->assertSee(route('login.store'), false)
            ->assertSee(route('password.request'), false)
            ->assertDontSee(route('register'), false)
            ->assertDontSee('Daftar sebagai mahasiswa');

        $this->getJson('/api/login')
            ->assertUnauthorized()
            ->assertJsonPath('message', 'Tidak terautentikasi.');

        $this->get('/forgot-password')->assertOk();
        $this->get(route('password.reset', [
            'token' => 'sample-token',
            'email' => 'user@kampuslms.test',
        ]))->assertOk();

        $this->assertTrue(route('password.email') !== route('password.update'));
    }

    public function test_login_uses_dashboard_instead_of_a_saved_role_specific_url(): void
    {
        $admin = User::factory()->admin()->create([
            'email' => 'admin-login-test@kampuslms.test',
            'password' => 'test-password',
        ]);

        $this->get('/dosen/courses')
            ->assertRedirect(route('login'));

        $this->post(route('login.store'), [
            'email' => $admin->email,
            'password' => 'test-password',
        ])->assertRedirect(route('dashboard'));

        $this->get(route('dashboard'))
            ->assertOk()
            ->assertSeeText('Dashboard Admin')
            ->assertDontSeeText('Dashboard Dosen');
    }

    public function test_demo_role_accounts_follow_the_role_policy_boundaries(): void
    {
        $this->seed(DatabaseSeeder::class);

        $admin = User::where('email', 'admin2@kampuslms.test')->firstOrFail();
        $lecturer = User::where('email', 'dosen2@kampuslms.test')->firstOrFail();
        $student = User::where('email', 'mahasiswa2@kampuslms.test')->firstOrFail();
        $course = $lecturer->taughtCourses()->firstOrFail();
        $draftAssignment = $course->assignments()
            ->where('status', 'draft')
            ->firstOrFail();

        $this->actingAs($admin)
            ->get('/admin/courses')
            ->assertOk()
            ->assertSee($course->name);
        $this->get("/admin/courses/{$course->id}/assignments")
            ->assertOk()
            ->assertSee($draftAssignment->title);

        $this->actingAs($lecturer)
            ->get('/dosen/courses')
            ->assertOk()
            ->assertSee($course->name);
        $this->get("/dosen/courses/{$course->id}/assignments")
            ->assertOk()
            ->assertSee($draftAssignment->title);

        $this->actingAs($student)
            ->get('/mahasiswa/courses')
            ->assertOk()
            ->assertDontSee($course->name);
        $this->get("/mahasiswa/courses/{$course->id}")
            ->assertForbidden();
    }

    public function test_seeded_submission_demo_files_can_be_downloaded(): void
    {
        Storage::fake('local');
        $this->seed(DatabaseSeeder::class);

        $student = User::where('email', 'mahasiswa@kampuslms.test')->firstOrFail();
        $submission = Submission::where('user_id', $student->id)->firstOrFail();

        Storage::disk('local')->assertExists($submission->file_path);
        $this->assertStringStartsWith(
            '%PDF-1.4',
            Storage::disk('local')->get($submission->file_path)
        );

        Sanctum::actingAs($student);
        $this->getJson("/api/v1/submissions/{$submission->id}/download")
            ->assertOk()
            ->assertDownload($submission->original_name);
    }

    public function test_logout_clears_the_authenticated_session(): void
    {
        $user = User::factory()->mahasiswa()->create();

        $this->actingAs($user)
            ->post(route('logout'))
            ->assertRedirect(route('login'));

        $this->assertGuest();
    }

    public function test_public_registration_creates_a_student_and_cannot_create_an_admin(): void
    {
        $this->get('/register')
            ->assertOk()
            ->assertSee('Daftar Akun Mahasiswa')
            ->assertDontSee('name="role"', false);

        $this->post('/register', [
            'name' => 'Pendaftar Baru',
            'email' => 'pendaftar-baru@kampuslms.test',
            'nim_nip' => 'MHSBARU01',
            'password' => 'student-password',
            'password_confirmation' => 'student-password',
            'role' => 'admin',
        ])
            ->assertRedirect(route('dashboard'))
            ->assertSessionHas('success');

        $newStudent = User::where('email', 'pendaftar-baru@kampuslms.test')->firstOrFail();
        $this->assertSame('mahasiswa', $newStudent->role);
        $this->assertAuthenticatedAs($newStudent);

        $lecturer = User::factory()->dosen()->create();
        $this->actingAs($lecturer)
            ->get('/admin/users/create')
            ->assertForbidden();
    }

    public function test_admin_enrolls_students_and_role_views_keep_courses_separate(): void
    {
        $admin = User::factory()->admin()->create();
        $lecturer = User::factory()->dosen()->create();
        $student = User::factory()->mahasiswa()->create();
        $otherStudent = User::factory()->mahasiswa()->create();
        $course = Course::factory()->create([
            'name' => 'Kelas yang Dikelola Admin',
            'lecturer_id' => $lecturer->id,
            'status' => 'active',
        ]);
        $otherCourse = Course::factory()->create([
            'name' => 'Kelas Dosen Lain',
            'lecturer_id' => User::factory()->dosen()->create()->id,
            'status' => 'active',
        ]);

        $this->actingAs($admin)
            ->get(route('admin.courses.show', $course))
            ->assertOk()
            ->assertSee('Pendaftaran Mahasiswa')
            ->assertSee($student->email);

        $this->put(route('admin.courses.students.update', $course), [
            'student_ids' => [$student->id],
        ])
            ->assertRedirect(route('admin.courses.show', [
                'course' => $course,
                'as' => 'admin',
            ]));

        $this->assertDatabaseHas('course_user', [
            'course_id' => $course->id,
            'user_id' => $student->id,
        ]);
        $this->assertDatabaseMissing('course_user', [
            'course_id' => $course->id,
            'user_id' => $otherStudent->id,
        ]);

        $this->actingAs($lecturer)
            ->get(route('dosen.courses.index'))
            ->assertOk()
            ->assertSee($course->name)
            ->assertDontSee($otherCourse->name);

        $this->actingAs($student)
            ->get(route('mahasiswa.courses.index'))
            ->assertOk()
            ->assertSee($course->name)
            ->assertDontSee($otherCourse->name);

        $this->actingAs($otherStudent)
            ->get(route('mahasiswa.courses.index'))
            ->assertOk()
            ->assertDontSee($course->name)
            ->assertDontSee($otherCourse->name);

        $this->actingAs($lecturer)
            ->put(route('admin.courses.students.update', $course), [
                'student_ids' => [$otherStudent->id],
            ])
            ->assertForbidden();
    }

    public function test_lecturer_can_grade_submissions_and_student_can_only_see_own_grade(): void
    {
        Storage::fake('local');
        $lecturer = User::factory()->dosen()->create();
        $otherLecturer = User::factory()->dosen()->create();
        $student = User::factory()->mahasiswa()->create();
        $otherStudent = User::factory()->mahasiswa()->create();
        $outsider = User::factory()->mahasiswa()->create();
        $course = Course::factory()->create([
            'lecturer_id' => $lecturer->id,
            'status' => 'active',
        ]);
        $course->students()->attach($student, ['enrolled_at' => now()]);
        $course->students()->attach($otherStudent, ['enrolled_at' => now()]);
        $assignment = Assignment::factory()->create([
            'course_id' => $course->id,
            'created_by' => $lecturer->id,
            'status' => 'published',
            'max_score' => 100,
        ]);
        $submission = Submission::create([
            'assignment_id' => $assignment->id,
            'user_id' => $student->id,
            'file_path' => 'submissions/test-answer.pdf',
            'original_name' => 'jawaban.pdf',
            'file_size' => 1024,
            'submitted_at' => now(),
            'is_late' => false,
        ]);
        Storage::disk('local')->put('submissions/test-answer.pdf', 'Isi jawaban demo');
        $otherSubmission = Submission::create([
            'assignment_id' => $assignment->id,
            'user_id' => $otherStudent->id,
            'file_path' => 'submissions/test-answer-other.pdf',
            'original_name' => 'jawaban-lain.pdf',
            'file_size' => 1024,
            'submitted_at' => now(),
            'is_late' => false,
        ]);
        Grade::create([
            'submission_id' => $otherSubmission->id,
            'graded_by' => $lecturer->id,
            'score' => 42,
            'feedback' => 'Nilai milik mahasiswa lain.',
            'graded_at' => now(),
        ]);

        $this->actingAs($lecturer)
            ->get(route('dosen.assignments.show', $assignment))
            ->assertOk()
            ->assertSee('Pengumpulan Mahasiswa')
            ->assertSee('Simpan Nilai');

        $this->get(route('dosen.courses.assignments.submissions.download', [
            'course' => $course,
            'assignment' => $assignment,
            'submission' => $submission,
        ]))
            ->assertOk()
            ->assertHeader('content-disposition', 'attachment; filename=jawaban.pdf');

        $this->actingAs($otherLecturer)
            ->get(route('dosen.courses.assignments.submissions.download', [
                'course' => $course,
                'assignment' => $assignment,
                'submission' => $submission,
            ]))
            ->assertForbidden();

        $this->actingAs($lecturer);
        $this->post(route('dosen.courses.assignments.submissions.grade', [
            'course' => $course,
            'assignment' => $assignment,
            'submission' => $submission,
        ]), [
            'score' => 87.5,
            'feedback' => 'Pemahaman materi sangat baik.',
        ])
            ->assertRedirect(route('dosen.assignments.show', $assignment));

        $this->assertDatabaseHas('grades', [
            'submission_id' => $submission->id,
            'graded_by' => $lecturer->id,
            'score' => 87.5,
            'feedback' => 'Pemahaman materi sangat baik.',
        ]);

        $this->actingAs($student)
            ->get(route('mahasiswa.assignments.show', $assignment))
            ->assertOk()
            ->assertSeeText('87.50')
            ->assertSeeText('Pemahaman materi sangat baik.')
            ->assertDontSeeText('Nilai milik mahasiswa lain.');

        $this->actingAs($outsider)
            ->get(route('mahasiswa.assignments.show', $assignment))
            ->assertForbidden();
    }

    public function test_password_reset_link_changes_the_password(): void
    {
        Notification::fake();
        $user = User::factory()->mahasiswa()->create([
            'email' => 'reset@kampuslms.test',
            'password' => 'old-password',
        ]);

        $this->post(route('password.email'), [
            'email' => $user->email,
        ])->assertRedirect();

        $notification = Notification::sent($user, ResetPassword::class)->first();
        $this->assertNotNull($notification);

        $this->post(route('password.update'), [
            'token' => $notification->token,
            'email' => $user->email,
            'password' => 'new-password',
            'password_confirmation' => 'new-password',
        ])->assertRedirect(route('login'));

        $this->assertTrue(
            Hash::check('new-password', $user->fresh()->password)
        );
    }

    public function test_lecturer_can_manage_material_and_assignment_metadata(): void
    {
        Storage::fake('local');
        $lecturer = User::factory()->dosen()->create();
        $course = Course::factory()->create([
            'lecturer_id' => $lecturer->id,
            'status' => 'active',
        ]);
        $otherLecturer = User::factory()->dosen()->create();

        $this->actingAs($lecturer)
            ->get(route('dosen.courses.edit', $course))
            ->assertOk()
            ->assertDontSee('name="lecturer_id"', false);

        $this->put(route('dosen.courses.update', $course), [
            'code' => $course->code,
            'name' => 'Nama course baru',
            'sks' => $course->sks,
            'lecturer_id' => $otherLecturer->id,
            'status' => $course->status,
        ])->assertSessionHasErrors('lecturer_id');

        $this->assertDatabaseHas('courses', [
            'id' => $course->id,
            'lecturer_id' => $lecturer->id,
        ]);

        $this->actingAs($lecturer)
            ->get(route('dosen.courses.materials.create', $course))
            ->assertOk()
            ->assertSee('Tambah Materi');

        $this->post(route('dosen.courses.materials.store', $course), [
            'title' => 'Materi Pengantar',
            'description' => 'Catatan pertemuan pertama.',
            'type' => 'link',
            'external_url' => 'https://example.test/materi',
        ])->assertRedirect(route('dosen.courses.materials.index', [
            'course' => $course,
            'as' => 'dosen',
        ]));

        $this->assertDatabaseHas('materials', [
            'course_id' => $course->id,
            'uploaded_by' => $lecturer->id,
            'title' => 'Materi Pengantar',
            'type' => 'link',
        ]);

        $material = $course->materials()->firstOrFail();
        $this->get(route('dosen.materials.edit', $material))
            ->assertOk()
            ->assertSee('Edit Materi');

        $this->get(route('dosen.materials.show', $material))
            ->assertOk()
            ->assertSee('Materi Pengantar');

        $this->put(route('dosen.materials.update', $material), [
            'title' => 'Materi Pengantar Revisi',
            'description' => 'Catatan yang diperbarui.',
            'type' => 'file',
            'file' => UploadedFile::fake()->create('materi.pdf', 400, 'application/pdf'),
        ])->assertRedirect(route('dosen.courses.materials.index', [
            'course' => $course,
            'as' => 'dosen',
        ]));

        $this->assertDatabaseHas('materials', [
            'id' => $material->id,
            'title' => 'Materi Pengantar Revisi',
            'type' => 'file',
            'external_url' => null,
        ]);
        $material->refresh();
        Storage::disk('local')->assertExists($material->file_path);

        $oldFilePath = $material->file_path;
        $this->put(route('dosen.materials.update', $material), [
            'title' => 'Materi Tautan',
            'description' => 'Tautan materi terbaru.',
            'type' => 'link',
            'external_url' => 'https://example.test/materi-terbaru',
        ])->assertRedirect(route('dosen.courses.materials.index', [
            'course' => $course,
            'as' => 'dosen',
        ]));

        Storage::disk('local')->assertMissing($oldFilePath);
        $this->assertDatabaseHas('materials', [
            'id' => $material->id,
            'type' => 'link',
            'external_url' => 'https://example.test/materi-terbaru',
            'file_path' => null,
        ]);

        $this->delete(route('dosen.materials.destroy', $material))
            ->assertRedirect(route('dosen.courses.materials.index', [
                'course' => $course,
                'as' => 'dosen',
            ]));
        $this->assertDatabaseMissing('materials', ['id' => $material->id]);

        $this->get(route('dosen.courses.assignments.create', $course))
            ->assertOk()
            ->assertSee('Tambah Tugas');

        $this->post(route('dosen.courses.assignments.store', $course), [
            'course_id' => $course->id,
            'title' => 'Tugas Pertama',
            'instructions' => 'Kerjakan sesuai materi.',
            'due_at' => now()->addDays(7)->format('Y-m-d H:i:s'),
            'max_score' => 90,
            'allow_late' => 1,
            'status' => 'draft',
        ])->assertRedirect(route('dosen.courses.assignments.index', [
            'course' => $course,
            'as' => 'dosen',
        ]));

        $assignment = $course->assignments()->firstOrFail();
        $this->assertDatabaseHas('assignments', [
            'id' => $assignment->id,
            'created_by' => $lecturer->id,
            'status' => 'draft',
        ]);
        $this->get(route('dosen.assignments.edit', $assignment))
            ->assertOk()
            ->assertSee('Edit Tugas');

        $this->put(route('dosen.assignments.update', $assignment), [
            'title' => 'Tugas Pertama Dipublikasikan',
            'instructions' => 'Instruksi yang diperbarui.',
            'due_at' => now()->addDays(10)->format('Y-m-d H:i:s'),
            'max_score' => 100,
            'allow_late' => 0,
            'status' => 'published',
        ])->assertRedirect(route('dosen.courses.assignments.index', [
            'course' => $course,
            'as' => 'dosen',
        ]));

        $this->assertDatabaseHas('assignments', [
            'id' => $assignment->id,
            'title' => 'Tugas Pertama Dipublikasikan',
            'status' => 'published',
        ]);
        $this->get(route('dosen.assignments.show', $assignment))
            ->assertOk()
            ->assertSee('Instruksi yang diperbarui.');
        $this->delete(route('dosen.assignments.destroy', $assignment))
            ->assertRedirect(route('dosen.courses.assignments.index', [
                'course' => $course,
                'as' => 'dosen',
            ]));
        $this->assertDatabaseMissing('assignments', ['id' => $assignment->id]);
    }

    public function test_material_views_and_crud_are_limited_to_admin_owner_lecturer_and_enrolled_students(): void
    {
        Storage::fake('local');
        $admin = User::factory()->admin()->create();
        $lecturer = User::factory()->dosen()->create();
        $otherLecturer = User::factory()->dosen()->create();
        $student = User::factory()->mahasiswa()->create();
        $outsider = User::factory()->mahasiswa()->create();
        $course = Course::factory()->for($lecturer, 'lecturer')->create([
            'status' => 'active',
        ]);
        $otherCourse = Course::factory()->for($otherLecturer, 'lecturer')->create([
            'status' => 'active',
        ]);
        $course->students()->attach($student, ['enrolled_at' => now()]);
        $material = \App\Models\Material::factory()
            ->for($course)
            ->for($lecturer, 'uploader')
            ->create([
                'type' => 'file',
                'file_path' => 'materials/'.$course->id.'/handout.pdf',
                'original_name' => 'handout.pdf',
                'file_size' => 1024,
                'mime_type' => 'application/pdf',
            ]);
        Storage::disk('local')->put($material->file_path, 'private course handout');
        $otherMaterial = \App\Models\Material::factory()
            ->for($otherCourse)
            ->for($otherLecturer, 'uploader')
            ->create();

        $this->actingAs($lecturer)
            ->get(route('dosen.courses.show', $course))
            ->assertOk()
            ->assertSee('Lihat Materi');
        $this->get(route('dosen.courses.materials.index', $course))
            ->assertOk()
            ->assertSee('Tambah Materi')
            ->assertSee('handout.pdf')
            ->assertSee('Edit Materi')
            ->assertSee('Hapus');
        $this->get(route('dosen.courses.materials.index', $otherCourse))
            ->assertForbidden();
        $this->get(route('dosen.materials.edit', $material))
            ->assertOk();
        $this->get(route('dosen.materials.edit', $otherMaterial))
            ->assertForbidden();
        $this->delete(route('dosen.materials.destroy', $otherMaterial))
            ->assertForbidden();

        $this->actingAs($admin)
            ->get(route('admin.courses.materials.index', $course))
            ->assertOk()
            ->assertSee('Tambah Materi')
            ->assertSee('handout.pdf');
        $this->get(route('admin.materials.edit', $material))
            ->assertOk();

        $this->actingAs($student)
            ->get(route('mahasiswa.courses.show', $course))
            ->assertOk()
            ->assertSee('Lihat Materi');
        $this->get(route('mahasiswa.courses.materials.index', $course))
            ->assertOk()
            ->assertSee('handout.pdf')
            ->assertSee('Unduh Berkas Materi')
            ->assertDontSee('Tambah Materi')
            ->assertDontSee('Edit Materi')
            ->assertDontSee('Hapus');
        $this->get(route('materials.download', $material))
            ->assertOk()
            ->assertDownload('handout.pdf');
        $this->get(route('admin.materials.edit', $material))
            ->assertForbidden();
        $this->delete(route('admin.materials.destroy', $material))
            ->assertForbidden();
        $this->post(route('admin.courses.materials.store', $course), [
            'title' => 'Tidak boleh dibuat',
            'description' => 'Percobaan',
            'type' => 'link',
            'external_url' => 'https://example.test',
        ])->assertForbidden();

        $this->actingAs($outsider)
            ->get(route('mahasiswa.courses.materials.index', $course))
            ->assertForbidden();
        $this->get(route('materials.download', $material))
            ->assertForbidden();
    }

    public function test_student_course_and_assignment_lists_are_scoped_in_web_and_api(): void
    {
        $lecturer = User::factory()->dosen()->create();
        $student = User::factory()->mahasiswa()->create();
        $otherLecturer = User::factory()->dosen()->create();
        $enrolledCourse = Course::factory()->create([
            'name' => 'Kelas Terdaftar',
            'lecturer_id' => $lecturer->id,
            'status' => 'active',
        ]);
        $otherCourse = Course::factory()->create([
            'name' => 'Kelas Lain',
            'lecturer_id' => $otherLecturer->id,
            'status' => 'active',
        ]);
        $enrolledCourse->students()->attach($student, [
            'enrolled_at' => now(),
        ]);

        $draftAssignment = Assignment::factory()->create([
            'course_id' => $enrolledCourse->id,
            'created_by' => $lecturer->id,
            'status' => 'draft',
        ]);
        $publishedAssignment = Assignment::factory()->create([
            'course_id' => $enrolledCourse->id,
            'created_by' => $lecturer->id,
            'status' => 'published',
        ]);

        $this->actingAs($lecturer)
            ->get(route('dosen.courses.index'))
            ->assertOk()
            ->assertSee($enrolledCourse->name)
            ->assertDontSee($otherCourse->name);

        $this->actingAs($student)
            ->get(route('mahasiswa.courses.index'))
            ->assertOk()
            ->assertSee($enrolledCourse->name)
            ->assertDontSee($otherCourse->name);

        $this->get(route('mahasiswa.courses.assignments.index', $enrolledCourse))
            ->assertOk()
            ->assertSee($publishedAssignment->title)
            ->assertDontSee($draftAssignment->title);

        Sanctum::actingAs($lecturer);
        $this->getJson('/api/v1/courses')
            ->assertOk()
            ->assertJsonPath('meta.total', 1)
            ->assertJsonFragment(['id' => $enrolledCourse->id]);

        Sanctum::actingAs($student);
        $this->getJson('/api/v1/courses')
            ->assertOk()
            ->assertJsonPath('meta.total', 1)
            ->assertJsonFragment(['id' => $enrolledCourse->id]);

        $this->getJson("/api/v1/courses/{$enrolledCourse->id}/assignments?status=draft")
            ->assertOk()
            ->assertJsonFragment(['title' => $publishedAssignment->title])
            ->assertJsonMissing(['title' => $draftAssignment->title]);

        $admin = User::factory()->admin()->create();
        Sanctum::actingAs($admin);
        $this->getJson('/api/v1/courses')
            ->assertOk()
            ->assertJsonPath('meta.total', 2)
            ->assertJsonFragment(['id' => $enrolledCourse->id])
            ->assertJsonFragment(['id' => $otherCourse->id]);
    }
}
