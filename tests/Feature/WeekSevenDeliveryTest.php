<?php

namespace Tests\Feature;

use App\Models\Assignment;
use App\Models\Course;
use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class WeekSevenDeliveryTest extends TestCase
{
    use RefreshDatabase;

    public function test_authentication_and_password_reset_pages_are_routed(): void
    {
        $this->get('/login')
            ->assertOk()
            ->assertSee(route('login.store'), false)
            ->assertSee(route('password.request'), false);

        $this->get('/forgot-password')->assertOk();
        $this->get(route('password.reset', [
            'token' => 'sample-token',
            'email' => 'user@kampuslms.test',
        ]))->assertOk();

        $this->assertTrue(route('password.email') !== route('password.update'));
    }

    public function test_logout_clears_the_authenticated_session(): void
    {
        $user = User::factory()->mahasiswa()->create();

        $this->actingAs($user)
            ->post(route('logout'))
            ->assertRedirect(route('login'));

        $this->assertGuest();
    }

    public function test_account_registration_is_only_available_through_admin_routes(): void
    {
        $this->get('/register')->assertNotFound();

        $lecturer = User::factory()->dosen()->create();
        $this->actingAs($lecturer)
            ->get('/admin/users/create')
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
        ])->assertRedirect(route('dosen.courses.materials.index', [
            'course' => $course,
            'as' => 'dosen',
        ]));

        $this->assertDatabaseHas('materials', [
            'id' => $material->id,
            'title' => 'Materi Pengantar Revisi',
            'external_url' => null,
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
