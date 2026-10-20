<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminWebAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_log_in_and_manage_courses_and_users(): void
    {
        $admin = User::factory()->admin()->create([
            'email' => 'admin@kampuslms.test',
            'password' => 'admin-password',
        ]);
        $lecturer = User::factory()->dosen()->create();

        $this->post('/login', [
            'email' => $admin->email,
            'password' => 'admin-password',
        ])->assertRedirect(route('dashboard'));

        $this->get('/admin/courses')
            ->assertOk()
            ->assertSee('Tambah Mata Kuliah');
        $this->get('/admin/users')
            ->assertOk()
            ->assertSee('Tambah Pengguna');

        $courseData = [
            'code' => 'ADM101',
            'name' => 'Administrasi Kampus',
            'description' => 'Mata kuliah uji CRUD admin.',
            'sks' => 3,
            'lecturer_id' => $lecturer->id,
            'status' => 'active',
        ];

        $this->post('/admin/courses', $courseData)
            ->assertRedirect(route('admin.courses.index', ['as' => 'admin']));

        $course = Course::where('code', 'ADM101')->firstOrFail();

        $this->get("/admin/courses/{$course->id}")->assertOk();
        $this->get("/admin/courses/{$course->id}/edit")->assertOk();

        $updatedCourseData = [...$courseData, 'name' => 'Administrasi Kampus Revisi'];
        $this->put("/admin/courses/{$course->id}", $updatedCourseData)
            ->assertRedirect(route('admin.courses.index', ['as' => 'admin']));
        $this->assertDatabaseHas('courses', [
            'id' => $course->id,
            'name' => 'Administrasi Kampus Revisi',
        ]);

        $this->delete("/admin/courses/{$course->id}")
            ->assertRedirect(route('admin.courses.index', ['as' => 'admin']));
        $this->assertDatabaseMissing('courses', ['id' => $course->id]);

        $userData = [
            'name' => 'Mahasiswa CRUD',
            'email' => 'crud-student@kampuslms.test',
            'nim_nip' => 'MHSCRUD01',
            'password' => 'student-password',
            'role' => 'mahasiswa',
        ];

        $this->post('/admin/users', $userData)
            ->assertRedirect(route('admin.users.index', ['as' => 'admin']));

        $managedUser = User::where('email', $userData['email'])->firstOrFail();

        $this->get("/admin/users/{$managedUser->id}?as=mahasiswa")
            ->assertOk()
            ->assertSee($managedUser->name);

        $updatedUserData = [
            ...$userData,
            'name' => 'Mahasiswa CRUD Revisi',
            'password' => '',
        ];
        $this->put("/admin/users/{$managedUser->id}", $updatedUserData)
            ->assertRedirect(route('admin.users.index', ['as' => 'admin']));
        $this->assertDatabaseHas('users', [
            'id' => $managedUser->id,
            'name' => 'Mahasiswa CRUD Revisi',
        ]);

        $this->delete("/admin/users/{$managedUser->id}")
            ->assertRedirect(route('admin.users.index', ['as' => 'admin']));
        $this->assertSoftDeleted('users', ['id' => $managedUser->id]);
    }

    public function test_only_an_authenticated_admin_can_open_admin_crud_routes(): void
    {
        $this->get('/test-leak')->assertNotFound();

        $this->get('/admin/courses?as=admin')
            ->assertRedirect(route('login'));

        $lecturer = User::factory()->dosen()->create();

        $this->actingAs($lecturer)
            ->get('/admin/courses?as=admin')
            ->assertForbidden();

        $this->actingAs($lecturer)
            ->get('/admin/users?as=admin')
            ->assertForbidden();
    }

    public function test_lecturer_can_enroll_and_unenroll_students_only_in_their_course(): void
    {
        $lecturer = User::factory()->dosen()->create();
        $otherLecturer = User::factory()->dosen()->create();
        $enrolledStudent = User::factory()->mahasiswa()->create();
        $availableStudent = User::factory()->mahasiswa()->create();
        $course = Course::factory()->create([
            'lecturer_id' => $lecturer->id,
            'status' => 'active',
        ]);
        $otherCourse = Course::factory()->create([
            'lecturer_id' => $otherLecturer->id,
            'status' => 'active',
        ]);
        $course->students()->attach($enrolledStudent, ['enrolled_at' => now()]);

        $this->actingAs($lecturer)
            ->get(route('dosen.courses.show', $course))
            ->assertOk()
            ->assertSee('Pendaftaran Mahasiswa')
            ->assertSee($availableStudent->email);

        $this->put(route('dosen.courses.students.update', $course), [
            'student_ids' => [$availableStudent->id],
        ])->assertRedirect(route('dosen.courses.show', [
            'course' => $course,
            'as' => 'dosen',
        ]));

        $this->assertDatabaseHas('course_user', [
            'course_id' => $course->id,
            'user_id' => $availableStudent->id,
        ]);
        $this->assertDatabaseMissing('course_user', [
            'course_id' => $course->id,
            'user_id' => $enrolledStudent->id,
        ]);

        $this->put(route('dosen.courses.students.update', $otherCourse), [
            'student_ids' => [$enrolledStudent->id],
        ])->assertForbidden();

        $this->assertDatabaseMissing('course_user', [
            'course_id' => $otherCourse->id,
            'user_id' => $enrolledStudent->id,
        ]);
    }

    public function test_user_pagination_uses_application_styling_without_unstyled_svg_icons(): void
    {
        $admin = User::factory()->admin()->create();
        User::factory()->mahasiswa()->count(16)->create();

        $this->actingAs($admin)
            ->get('/admin/users')
            ->assertOk()
            ->assertSee('pagination-control', false)
            ->assertSee('Menampilkan 1 - 15 dari 17 data')
            ->assertSee('Berikutnya')
            ->assertDontSee('<svg', false);
    }
}