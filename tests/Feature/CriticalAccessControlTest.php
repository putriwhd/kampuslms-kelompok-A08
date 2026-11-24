<?php

namespace Tests\Feature;

use App\Models\Assignment;
use App\Models\Course;
use App\Models\Material;
use App\Models\Submission;
use App\Models\User;
use App\Notifications\NewAssignmentNotification;
use App\Notifications\SubmissionGradedNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CriticalAccessControlTest extends TestCase
{
    use RefreshDatabase;

    /*
    |--------------------------------------------------------------------------
    | 1. AUTENTIKASI (3 Feature Test)
    |--------------------------------------------------------------------------
    */

    /**
     * Test 1 (Jalur Gagal):
     * Mengirim POST /login dengan password salah harus gagal autentikasi,
     * tetap berstatus guest, dan mengembalikan error validasi di session.
     */
    public function test_web_login_fails_with_invalid_password_and_keeps_user_as_guest(): void
    {
        $user = User::factory()->create([
            'email' => 'user@kampuslms.test',
            'password' => bcrypt('correct-password'),
        ]);

        $response = $this->post(route('login.store'), [
            'email' => 'user@kampuslms.test',
            'password' => 'wrong-password',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    /**
     * Test 2 (Jalur Sukses):
     * Login web berhasil dengan kredensial valid dan mengautentikasi pengguna.
     */
    public function test_web_login_succeeds_with_valid_credentials(): void
    {
        $user = User::factory()->create([
            'email' => 'valid-user@kampuslms.test',
            'password' => bcrypt('correct-password'),
        ]);

        $response = $this->post(route('login.store'), [
            'email' => 'valid-user@kampuslms.test',
            'password' => 'correct-password',
        ]);

        $response->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($user);
    }

    /**
     * Test 3 (Jalur Gagal):
     * Akses rute terproteksi (admin, dosen, mahasiswa) tanpa login ditolak dan diarahkan ke login.
     */
    public function test_unauthenticated_guest_accessing_protected_routes_is_redirected_to_login(): void
    {
        $this->get(route('admin.courses.index'))->assertRedirect(route('login'));
        $this->get(route('dosen.courses.index'))->assertRedirect(route('login'));
        $this->get(route('mahasiswa.courses.index'))->assertRedirect(route('login'));
    }

    /*
    |--------------------------------------------------------------------------
    | 2. CRUD MATA KULIAH (4 Feature Test)
    |--------------------------------------------------------------------------
    */

    /**
     * Test 4 (Jalur Gagal):
     * Pengguna dengan role dosen maupun mahasiswa yang mencoba membuat (POST),
     * mengubah (PUT/PATCH), atau menghapus (DELETE) data mata kuliah harus ditolak dengan 403.
     */
    public function test_non_admin_users_cannot_create_update_or_delete_courses(): void
    {
        $dosen = User::factory()->dosen()->create();
        $mahasiswa = User::factory()->mahasiswa()->create();
        $course = Course::factory()->create([
            'code' => 'IF101',
            'name' => 'Struktur Data Asli',
        ]);

        $newCourseData = [
            'code' => 'IF999',
            'name' => 'Mata Kuliah Tidak Sah',
            'description' => 'Mata kuliah yang coba dibuat oleh non-admin.',
            'sks' => 3,
            'lecturer_id' => $dosen->id,
            'status' => 'active',
        ];

        $updateCourseData = [
            'code' => 'IF101',
            'name' => 'Struktur Data Dikelabui',
            'description' => 'Mencoba merubah mata kuliah.',
            'sks' => 3,
            'lecturer_id' => $dosen->id,
            'status' => 'active',
        ];

        foreach ([$dosen, $mahasiswa] as $user) {
            // Coba Create (POST)
            $responseStore = $this->actingAs($user)->post(route('admin.courses.store'), $newCourseData);
            $responseStore->assertForbidden();

            // Coba Update (PUT)
            $responseUpdate = $this->actingAs($user)->put(route('admin.courses.update', $course), $updateCourseData);
            $responseUpdate->assertForbidden();

            // Coba Delete (DELETE)
            $responseDelete = $this->actingAs($user)->delete(route('admin.courses.destroy', $course));
            $responseDelete->assertForbidden();
        }

        // Verifikasi database tidak mengalami perubahan
        $this->assertDatabaseMissing('courses', ['code' => 'IF999']);
        $this->assertDatabaseHas('courses', [
            'id' => $course->id,
            'name' => 'Struktur Data Asli',
        ]);
    }

    /**
     * Test 5 (Jalur Sukses):
     * Admin berhasil membuat mata kuliah baru melalui POST /admin/courses.
     */
    public function test_admin_can_create_new_course(): void
    {
        $admin = User::factory()->admin()->create();
        $lecturer = User::factory()->dosen()->create();

        $courseData = [
            'code' => 'IF201',
            'name' => 'Rekayasa Perangkat Lunak',
            'description' => 'Mempelajari SDLC dan pengujian perangkat lunak.',
            'sks' => 3,
            'lecturer_id' => $lecturer->id,
            'status' => 'active',
        ];

        $response = $this->actingAs($admin)->post(route('admin.courses.store'), $courseData);

        $response->assertRedirect();
        $this->assertDatabaseHas('courses', [
            'code' => 'IF201',
            'name' => 'Rekayasa Perangkat Lunak',
            'lecturer_id' => $lecturer->id,
        ]);
    }

    /**
     * Test 6 (Jalur Sukses):
     * Admin berhasil memperbarui data mata kuliah melalui PUT /admin/courses/{course}.
     */
    public function test_admin_can_update_existing_course(): void
    {
        $admin = User::factory()->admin()->create();
        $course = Course::factory()->create([
            'code' => 'IF202',
            'name' => 'Basis Data Lama',
            'sks' => 3,
        ]);

        $updateData = [
            'code' => 'IF202',
            'name' => 'Basis Data Terdistribusi',
            'description' => 'Mempelajari basis data terdistribusi dan replikasi.',
            'sks' => 4,
            'lecturer_id' => $course->lecturer_id,
            'status' => 'active',
        ];

        $response = $this->actingAs($admin)->put(route('admin.courses.update', $course), $updateData);

        $response->assertRedirect();
        $this->assertDatabaseHas('courses', [
            'id' => $course->id,
            'name' => 'Basis Data Terdistribusi',
            'sks' => 4,
        ]);
    }

    /**
     * Test 7 (Jalur Sukses):
     * Admin berhasil menghapus mata kuliah melalui DELETE /admin/courses/{course}.
     */
    public function test_admin_can_delete_course(): void
    {
        $admin = User::factory()->admin()->create();
        $course = Course::factory()->create();

        $response = $this->actingAs($admin)->delete(route('admin.courses.destroy', $course));

        $response->assertRedirect();
        $this->assertDatabaseMissing('courses', [
            'id' => $course->id,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | 3. ENROLLMENT (2 Feature Test)
    |--------------------------------------------------------------------------
    */

    /**
     * Test 8 (Jalur Sukses):
     * Mahasiswa hanya melihat mata kuliah yang diikutinya pada daftar kelas mahasiswa.
     */
    public function test_student_only_sees_enrolled_courses_in_course_list(): void
    {
        $student = User::factory()->mahasiswa()->create();

        $enrolledCourse = Course::factory()->create([
            'name' => 'Mata Kuliah Wajib Mahasiswa',
            'status' => 'active',
        ]);
        $enrolledCourse->students()->attach($student, ['enrolled_at' => now()]);

        $unenrolledCourse = Course::factory()->create([
            'name' => 'Mata Kuliah Lain Diluar Kelas',
            'status' => 'active',
        ]);

        $response = $this->actingAs($student)->get(route('mahasiswa.courses.index'));

        $response->assertOk();
        $response->assertSeeText('Mata Kuliah Wajib Mahasiswa');
        $response->assertDontSeeText('Mata Kuliah Lain Diluar Kelas');
    }

    /**
     * Test 9 (Jalur Gagal):
     * Mahasiswa yang tidak terdaftar ditolak (403) saat mencoba membuka detail kelas.
     */
    public function test_unenrolled_student_is_forbidden_from_viewing_course_details(): void
    {
        $course = Course::factory()->create(['status' => 'active']);
        $unenrolledStudent = User::factory()->mahasiswa()->create();

        $response = $this->actingAs($unenrolledStudent)->get(route('mahasiswa.courses.show', $course));

        $response->assertForbidden();
    }

    /*
    |--------------------------------------------------------------------------
    | 4. UNGGAH MATERI (2 Feature Test)
    |--------------------------------------------------------------------------
    */

    /**
     * Test 10 (Jalur Gagal):
     * Dosen B (bukan dosen pengampu) mencoba mengunggah materi ke kelas Dosen A ditolak dengan 403.
     */
    public function test_non_course_lecturer_cannot_upload_material_to_course(): void
    {
        Storage::fake('local');

        $dosenA = User::factory()->dosen()->create();
        $dosenB = User::factory()->dosen()->create();

        $courseA = Course::factory()->create([
            'lecturer_id' => $dosenA->id,
        ]);

        $file = UploadedFile::fake()->create('materi_ilegal.pdf', 500, 'application/pdf');

        $materialPayload = [
            'title' => 'Materi Tidak Berizin',
            'description' => 'Mencoba unggah materi ke kelas dosen lain.',
            'type' => 'file',
            'file' => $file,
        ];

        $response = $this->actingAs($dosenB)->post(
            route('dosen.courses.materials.store', $courseA),
            $materialPayload
        );

        $response->assertForbidden();

        $this->assertDatabaseMissing('materials', [
            'title' => 'Materi Tidak Berizin',
            'course_id' => $courseA->id,
        ]);

        $this->assertEmpty(Storage::disk('local')->allFiles());
    }

    /**
     * Test 11 (Jalur Sukses):
     * Dosen pengampu berhasil mengunggah file materi ke kelasnya.
     */
    public function test_course_lecturer_can_upload_material_file_to_course(): void
    {
        Storage::fake('local');

        $lecturer = User::factory()->dosen()->create();
        $course = Course::factory()->for($lecturer, 'lecturer')->create(['status' => 'active']);

        $file = UploadedFile::fake()->create('slide_kuliah.pdf', 500, 'application/pdf');

        $response = $this->actingAs($lecturer)->post(
            route('dosen.courses.materials.store', $course),
            [
                'title' => 'Slide Kuliah Bab 1',
                'description' => 'Materi pembahasan bab 1.',
                'type' => 'file',
                'file' => $file,
            ]
        );

        $response->assertRedirect();

        $material = Material::where('title', 'Slide Kuliah Bab 1')
            ->where('course_id', $course->id)
            ->firstOrFail();

        Storage::disk('local')->assertExists($material->file_path);
        $this->assertDatabaseHas('materials', [
            'id' => $material->id,
            'title' => 'Slide Kuliah Bab 1',
            'course_id' => $course->id,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | 5. PENGUMPULAN TUGAS (3 Feature Test)
    |--------------------------------------------------------------------------
    */

    /**
     * Test 12 (Jalur Gagal):
     * Mahasiswa yang belum terdaftar mencoba mengumpulkan tugas ditolak dengan 403.
     */
    public function test_non_enrolled_student_cannot_submit_assignment(): void
    {
        Storage::fake('local');

        $course = Course::factory()->create(['status' => 'active']);
        $assignment = Assignment::factory()->for($course)->create([
            'status' => 'published',
            'due_at' => now()->addDays(3),
        ]);

        $unenrolledStudent = User::factory()->mahasiswa()->create();
        $file = UploadedFile::fake()->create('submission_ilegal.pdf', 300, 'application/pdf');

        $response = $this->actingAs($unenrolledStudent)->post(
            route('mahasiswa.courses.assignments.submissions.store', [
                'course' => $course,
                'assignment' => $assignment,
            ]),
            [
                'file' => $file,
            ]
        );

        $response->assertForbidden();

        $this->assertDatabaseMissing('submissions', [
            'assignment_id' => $assignment->id,
            'user_id' => $unenrolledStudent->id,
        ]);

        $this->assertEmpty(Storage::disk('local')->allFiles());
    }

    /**
     * Test 13 (Jalur Sukses):
     * Mahasiswa terdaftar berhasil mengumpulkan tugas sebelum deadline.
     */
    public function test_enrolled_student_can_submit_assignment_before_deadline(): void
    {
        Storage::fake('local');

        $course = Course::factory()->create(['status' => 'active']);
        $student = User::factory()->mahasiswa()->create();
        $course->students()->attach($student, ['enrolled_at' => now()]);

        $assignment = Assignment::factory()->for($course)->create([
            'status' => 'published',
            'due_at' => now()->addDays(2),
            'allow_late' => true,
        ]);

        $file = UploadedFile::fake()->create('jawaban_tugas.pdf', 250, 'application/pdf');

        $response = $this->actingAs($student)->post(
            route('mahasiswa.courses.assignments.submissions.store', [
                'course' => $course,
                'assignment' => $assignment,
            ]),
            [
                'file' => $file,
            ]
        );

        $response->assertRedirect()->assertSessionHas('success');

        $this->assertDatabaseHas('submissions', [
            'assignment_id' => $assignment->id,
            'user_id' => $student->id,
            'is_late' => false,
        ]);

        $submission = Submission::where('assignment_id', $assignment->id)
            ->where('user_id', $student->id)
            ->firstOrFail();

        Storage::disk('local')->assertExists($submission->file_path);
    }

    /**
     * Test 14 (Jalur Gagal):
     * Pengumpulan tugas setelah deadline ditolak jika allow_late bernilai false.
     */
    public function test_student_submission_after_deadline_is_rejected_when_allow_late_is_false(): void
    {
        Storage::fake('local');

        $course = Course::factory()->create(['status' => 'active']);
        $student = User::factory()->mahasiswa()->create();
        $course->students()->attach($student, ['enrolled_at' => now()]);

        $assignment = Assignment::factory()->for($course)->create([
            'status' => 'published',
            'due_at' => now()->subDay(),
            'allow_late' => false,
        ]);

        $file = UploadedFile::fake()->create('tugas_terlambat.pdf', 250, 'application/pdf');

        $response = $this->actingAs($student)->post(
            route('mahasiswa.courses.assignments.submissions.store', [
                'course' => $course,
                'assignment' => $assignment,
            ]),
            [
                'file' => $file,
            ]
        );

        $response->assertSessionHasErrors('file');

        $this->assertDatabaseMissing('submissions', [
            'assignment_id' => $assignment->id,
            'user_id' => $student->id,
        ]);

        $this->assertEmpty(Storage::disk('local')->allFiles());
    }

    /*
    |--------------------------------------------------------------------------
    | 6. UNDUHAN (2 Feature Test)
    |--------------------------------------------------------------------------
    */

    /**
     * Test 15 (Jalur Sukses):
     * Pemilik submission berhasil mengunduh file tugas yang telah dikumpulkan.
     */
    public function test_submission_owner_can_download_own_submitted_file(): void
    {
        Storage::fake('local');

        $lecturer = User::factory()->dosen()->create();
        $course = Course::factory()->for($lecturer, 'lecturer')->create(['status' => 'active']);
        $ownerStudent = User::factory()->mahasiswa()->create();
        $course->students()->attach($ownerStudent, ['enrolled_at' => now()]);

        $assignment = Assignment::factory()->for($course)->create();
        Storage::disk('local')->put('submissions/berkas_tugas.pdf', 'konten tugas mahasiswa');

        $submission = Submission::factory()->for($assignment)->create([
            'user_id' => $ownerStudent->id,
            'file_path' => 'submissions/berkas_tugas.pdf',
            'original_name' => 'berkas_tugas.pdf',
        ]);

        $response = $this->actingAs($ownerStudent)->get(
            route('mahasiswa.courses.assignments.submissions.download', [
                'course' => $course,
                'assignment' => $assignment,
                'submission' => $submission,
            ])
        );

        $response->assertOk();
    }

    /**
     * Test 16 (Jalur Gagal):
     * Mahasiswa lain (bukan pemilik) ditolak (403) saat mencoba mengunduh file submission tersebut.
     */
    public function test_other_student_is_forbidden_from_downloading_submission_file(): void
    {
        Storage::fake('local');

        $lecturer = User::factory()->dosen()->create();
        $course = Course::factory()->for($lecturer, 'lecturer')->create(['status' => 'active']);
        $ownerStudent = User::factory()->mahasiswa()->create();
        $otherStudent = User::factory()->mahasiswa()->create();
        $course->students()->attach($ownerStudent, ['enrolled_at' => now()]);
        $course->students()->attach($otherStudent, ['enrolled_at' => now()]);

        $assignment = Assignment::factory()->for($course)->create();
        Storage::disk('local')->put('submissions/berkas_rahasia.pdf', 'konten rahasia');

        $submission = Submission::factory()->for($assignment)->create([
            'user_id' => $ownerStudent->id,
            'file_path' => 'submissions/berkas_rahasia.pdf',
            'original_name' => 'berkas_rahasia.pdf',
        ]);

        $response = $this->actingAs($otherStudent)->get(
            route('mahasiswa.courses.assignments.submissions.download', [
                'course' => $course,
                'assignment' => $assignment,
                'submission' => $submission,
            ])
        );

        $response->assertForbidden();
    }

    /*
    |--------------------------------------------------------------------------
    | 7. PENILAIAN (2 Feature Test)
    |--------------------------------------------------------------------------
    */

    /**
     * Test 17 (Jalur Gagal):
     * Dosen B mencoba memberi nilai pada submission mahasiswa di kelas Dosen A ditolak dengan 403.
     */
    public function test_non_course_lecturer_cannot_grade_submission_via_web(): void
    {
        $dosenA = User::factory()->dosen()->create();
        $dosenB = User::factory()->dosen()->create();

        $courseA = Course::factory()->create([
            'lecturer_id' => $dosenA->id,
            'status' => 'active',
        ]);

        $student = User::factory()->mahasiswa()->create();
        $courseA->students()->attach($student, ['enrolled_at' => now()]);

        $assignment = Assignment::factory()->for($courseA)->create([
            'status' => 'published',
            'max_score' => 100,
        ]);

        $submission = Submission::factory()->for($assignment)->create([
            'user_id' => $student->id,
        ]);

        $gradePayload = [
            'score' => 88.5,
            'feedback' => 'Percobaan penilaian tidak berizin.',
        ];

        $response = $this->actingAs($dosenB)->post(
            route('dosen.courses.assignments.submissions.grade', [
                'course' => $courseA,
                'assignment' => $assignment,
                'submission' => $submission,
            ]),
            $gradePayload
        );

        $response->assertForbidden();

        $this->assertDatabaseMissing('grades', [
            'submission_id' => $submission->id,
        ]);
    }

    /**
     * Test 18 (Jalur Sukses):
     * Dosen pengampu berhasil memberikan nilai pada submission mahasiswa di kelasnya melalui web.
     */
    public function test_course_lecturer_can_grade_submission_via_web(): void
    {
        $lecturer = User::factory()->dosen()->create();
        $course = Course::factory()->for($lecturer, 'lecturer')->create(['status' => 'active']);
        $student = User::factory()->mahasiswa()->create();
        $course->students()->attach($student, ['enrolled_at' => now()]);

        $assignment = Assignment::factory()->for($course)->create([
            'status' => 'published',
            'max_score' => 100,
        ]);

        $submission = Submission::factory()->for($assignment)->create([
            'user_id' => $student->id,
        ]);

        $response = $this->actingAs($lecturer)->post(
            route('dosen.courses.assignments.submissions.grade', [
                'course' => $course,
                'assignment' => $assignment,
                'submission' => $submission,
            ]),
            [
                'score' => 95,
                'feedback' => 'Penjelasan sangat komprehensif.',
            ]
        );

        $response->assertRedirect();

        $this->assertDatabaseHas('grades', [
            'submission_id' => $submission->id,
            'graded_by' => $lecturer->id,
            'score' => 95,
            'feedback' => 'Penjelasan sangat komprehensif.',
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | 8. NOTIFIKASI (2 Feature Test)
    |--------------------------------------------------------------------------
    */

    /**
     * Test 19 (Jalur Sukses):
     * Notifikasi terkirim ke mahasiswa terdaftar saat tugas baru dibuat/dipublikasikan.
     */
    public function test_notification_sent_to_enrolled_students_when_new_assignment_published(): void
    {
        Notification::fake();

        $lecturer = User::factory()->dosen()->create();
        $course = Course::factory()->for($lecturer, 'lecturer')->create(['status' => 'active']);
        $student = User::factory()->mahasiswa()->create();
        $otherStudent = User::factory()->mahasiswa()->create();

        $course->students()->attach($student, ['enrolled_at' => now()]);

        $response = $this->actingAs($lecturer)->post(
            route('dosen.courses.assignments.store', $course),
            [
                'course_id' => $course->getKey(),
                'title' => 'Tugas 1 Pemrograman',
                'instructions' => 'Selesaikan latihan bab 1.',
                'due_at' => now()->addDays(4)->toDateTimeString(),
                'status' => 'published',
            ]
        );

        $response->assertRedirect();

        Notification::assertSentTo(
            $student,
            NewAssignmentNotification::class
        );

        Notification::assertNotSentTo(
            $otherStudent,
            NewAssignmentNotification::class
        );
    }

    /**
     * Test 20 (Jalur Sukses):
     * Notifikasi terkirim ke mahasiswa pemilik tugas saat dosen memberikan nilai.
     */
    public function test_notification_sent_to_submission_owner_when_assignment_is_graded(): void
    {
        Notification::fake();

        $lecturer = User::factory()->dosen()->create();
        $course = Course::factory()->for($lecturer, 'lecturer')->create(['status' => 'active']);
        $student = User::factory()->mahasiswa()->create();
        $course->students()->attach($student, ['enrolled_at' => now()]);

        $assignment = Assignment::factory()->for($course)->create([
            'status' => 'published',
            'max_score' => 100,
        ]);

        $submission = Submission::factory()->for($assignment)->create([
            'user_id' => $student->id,
        ]);

        $response = $this->actingAs($lecturer)->post(
            route('dosen.courses.assignments.submissions.grade', [
                'course' => $course,
                'assignment' => $assignment,
                'submission' => $submission,
            ]),
            [
                'score' => 92,
                'feedback' => 'Hasil pengerjaan sangat baik!',
            ]
        );

        $response->assertRedirect();

        Notification::assertSentTo(
            $student,
            SubmissionGradedNotification::class
        );
    }
}
