<?php

namespace Tests\Feature;

use App\Events\EnrollmentApproved;
use App\Events\EnrollmentRequested;
use App\Models\Course;
use App\Models\EnrollmentRequest;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class SelfEnrollmentTest extends TestCase
{
    use RefreshDatabase;

    public function test_demo_courses_have_seeded_students_without_enrolling_students_in_new_courses(): void
    {
        $this->seed(DatabaseSeeder::class);

        $demoCourses = Course::query()
            ->where('code', 'like', 'SI2514%')
            ->get();

        $this->assertCount(5, $demoCourses);
        foreach ($demoCourses as $course) {
            $this->assertSame(6, $course->students()->count());
        }

        $demoStudent = User::where('email', 'mahasiswa@kampuslms.test')->firstOrFail();
        $firstDemoCourse = $demoCourses->firstWhere('code', 'SI251401');
        $this->assertNotNull($firstDemoCourse);
        $this->assertTrue($firstDemoCourse->students()->whereKey($demoStudent->id)->exists());

        $this->actingAs($demoStudent)
            ->get(route('mahasiswa.catalog.index'))
            ->assertOk()
            ->assertSee('3 / 24 SKS')
            ->assertSee('✓ Terdaftar');

        $newCourse = Course::factory()->create();

        $this->assertSame(0, $newCourse->students()->count());
    }

    public function test_student_can_view_course_catalog_and_sks_info(): void
    {
        $lecturer = User::factory()->dosen()->create();
        $student = User::factory()->mahasiswa()->create();

        $courseA = Course::factory()->for($lecturer, 'lecturer')->create([
            'status' => 'active',
            'name' => 'Pemrograman Web Lanjut',
            'sks' => 3,
        ]);
        $courseDraft = Course::factory()->for($lecturer, 'lecturer')->create([
            'status' => 'draft',
            'name' => 'Mata Kuliah Draf Rahasia',
        ]);

        $this->actingAs($student)
            ->get(route('mahasiswa.catalog.index', ['as' => 'mahasiswa']))
            ->assertOk()
            ->assertSee('Daftar Kelas')
            ->assertSee('Pemrograman Web Lanjut')
            ->assertDontSee('Mata Kuliah Draf Rahasia')
            ->assertSee('0 / 24 SKS');
    }

    public function test_student_can_request_enrollment_and_event_is_dispatched(): void
    {
        Event::fake([EnrollmentRequested::class]);

        $lecturer = User::factory()->dosen()->create();
        $student = User::factory()->mahasiswa()->create();
        $course = Course::factory()->for($lecturer, 'lecturer')->create([
            'status' => 'active',
            'sks' => 3,
        ]);

        $this->actingAs($student)
            ->post(route('mahasiswa.courses.enroll', $course))
            ->assertRedirect()
            ->assertSessionHas('success');

        Event::assertDispatched(EnrollmentRequested::class);

        $this->assertDatabaseHas('enrollment_requests', [
            'course_id' => $course->id,
            'user_id' => $student->id,
            'status' => 'pending',
        ]);

        // Tidak boleh mengajukan dua kali saat masih pending
        $this->actingAs($student)
            ->post(route('mahasiswa.courses.enroll', $course))
            ->assertRedirect()
            ->assertSessionHas('error');
    }

    public function test_student_cannot_request_self_enrollment_into_a_prepopulated_course(): void
    {
        $lecturer = User::factory()->dosen()->create();
        $student = User::factory()->mahasiswa()->create();
        $alreadyEnrolledStudent = User::factory()->mahasiswa()->create();
        $course = Course::factory()->for($lecturer, 'lecturer')->create([
            'status' => 'active',
            'sks' => 3,
        ]);
        $course->students()->attach($alreadyEnrolledStudent, ['enrolled_at' => now()]);

        $this->actingAs($student)
            ->post(route('mahasiswa.courses.enroll', $course))
            ->assertRedirect()
            ->assertSessionHas('error', 'Pendaftaran mandiri hanya tersedia untuk kelas yang belum memiliki mahasiswa terdaftar. Hubungi admin atau dosen untuk pendaftaran langsung.');

        $this->assertDatabaseMissing('enrollment_requests', [
            'course_id' => $course->id,
            'user_id' => $student->id,
        ]);
    }

    public function test_student_cannot_enroll_if_exceeding_max_24_sks(): void
    {
        $lecturer = User::factory()->dosen()->create();
        $student = User::factory()->mahasiswa()->create();

        // Daftarkan mahasiswa ke beberapa matkul hingga 22 SKS
        $existingCourse = Course::factory()->for($lecturer, 'lecturer')->create([
            'status' => 'active',
            'sks' => 22,
        ]);
        $existingCourse->students()->attach($student, ['enrolled_at' => now()]);

        // Coba daftar matkul baru berbobot 3 SKS (total jadi 25 SKS -> melebihi 24)
        $newCourse = Course::factory()->for($lecturer, 'lecturer')->create([
            'status' => 'active',
            'sks' => 3,
        ]);

        $this->actingAs($student)
            ->post(route('mahasiswa.courses.enroll', $newCourse))
            ->assertRedirect()
            ->assertSessionHas('error');

        $this->assertDatabaseMissing('enrollment_requests', [
            'course_id' => $newCourse->id,
            'user_id' => $student->id,
        ]);
    }

    public function test_archived_courses_do_not_count_toward_current_semester_sks(): void
    {
        $lecturer = User::factory()->dosen()->create();
        $student = User::factory()->mahasiswa()->create();

        $archivedCourse = Course::factory()->for($lecturer, 'lecturer')->create([
            'status' => 'archived',
            'sks' => 22,
        ]);
        $archivedCourse->students()->attach($student, ['enrolled_at' => now()]);

        $activeCourse = Course::factory()->for($lecturer, 'lecturer')->create([
            'status' => 'active',
            'sks' => 3,
        ]);

        $this->actingAs($student)
            ->post(route('mahasiswa.courses.enroll', $activeCourse))
            ->assertRedirect()
            ->assertSessionHas('success');
    }

    public function test_lecturer_cannot_approve_enrollment_that_exceeds_24_sks(): void
    {
        $lecturer = User::factory()->dosen()->create();
        $student = User::factory()->mahasiswa()->create();

        $existingCourse = Course::factory()->for($lecturer, 'lecturer')->create([
            'status' => 'active',
            'sks' => 22,
        ]);
        $existingCourse->students()->attach($student, ['enrolled_at' => now()]);

        $newCourse = Course::factory()->for($lecturer, 'lecturer')->create([
            'status' => 'active',
            'sks' => 3,
        ]);
        $enrollmentRequest = EnrollmentRequest::create([
            'course_id' => $newCourse->id,
            'user_id' => $student->id,
            'status' => 'pending',
        ]);

        $this->actingAs($lecturer)
            ->post(route('dosen.enrollment-requests.approve', $enrollmentRequest))
            ->assertRedirect()
            ->assertSessionHas('error');

        $this->assertDatabaseHas('enrollment_requests', [
            'id' => $enrollmentRequest->id,
            'status' => 'pending',
        ]);
        $this->assertDatabaseMissing('course_user', [
            'course_id' => $newCourse->id,
            'user_id' => $student->id,
        ]);
    }

    public function test_student_cannot_enroll_if_class_capacity_reaches_25_students(): void
    {
        $lecturer = User::factory()->dosen()->create();
        $student = User::factory()->mahasiswa()->create();
        $course = Course::factory()->for($lecturer, 'lecturer')->create([
            'status' => 'active',
            'sks' => 3,
        ]);

        // Isi kelas dengan 25 mahasiswa
        $students = User::factory()->mahasiswa()->count(25)->create();
        $course->students()->attach($students->pluck('id'), ['enrolled_at' => now()]);

        $this->actingAs($student)
            ->post(route('mahasiswa.courses.enroll', $course))
            ->assertRedirect()
            ->assertSessionHas('error');

        $this->assertDatabaseMissing('enrollment_requests', [
            'course_id' => $course->id,
            'user_id' => $student->id,
        ]);
    }

    public function test_lecturer_can_approve_enrollment_request_and_student_is_officially_enrolled(): void
    {
        Event::fake([EnrollmentApproved::class]);

        $lecturer = User::factory()->dosen()->create();
        $otherLecturer = User::factory()->dosen()->create();
        $student = User::factory()->mahasiswa()->create();

        $course = Course::factory()->for($lecturer, 'lecturer')->create(['status' => 'active']);
        $enrollmentRequest = EnrollmentRequest::create([
            'course_id' => $course->id,
            'user_id' => $student->id,
            'status' => 'pending',
        ]);

        // Dosen lain mencoba menyetujui -> 403 Forbidden
        $this->actingAs($otherLecturer)
            ->post(route('dosen.enrollment-requests.approve', $enrollmentRequest))
            ->assertForbidden();

        // Dosen pengampu menyetujui -> Sukses & Mahasiswa masuk tabel course_user
        $this->actingAs($lecturer)
            ->post(route('dosen.enrollment-requests.approve', $enrollmentRequest))
            ->assertRedirect()
            ->assertSessionHas('success');

        Event::assertDispatched(EnrollmentApproved::class);

        $this->assertDatabaseHas('enrollment_requests', [
            'id' => $enrollmentRequest->id,
            'status' => 'approved',
        ]);

        $this->assertDatabaseHas('course_user', [
            'course_id' => $course->id,
            'user_id' => $student->id,
        ]);

        $this->actingAs($student)
            ->get(route('mahasiswa.courses.show', $course))
            ->assertOk()
            ->assertSee('Mahasiswa Terdaftar')
            ->assertSee($student->name);
    }
}
