<?php

namespace Tests\Feature;

use App\Events\EnrollmentApproved;
use App\Events\EnrollmentRequested;
use App\Models\Assignment;
use App\Models\Course;
use App\Models\EnrollmentRequest;
use App\Models\Grade;
use App\Models\Submission;
use App\Models\User;
use App\Notifications\EnrollmentRejectedNotification;
use App\Notifications\EnrollmentRequestedNotification;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
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
            $this->assertGreaterThanOrEqual(15, $course->students()->count());
            $this->assertSame(2, $course->assignments()->where('status', 'published')->count());
            $this->assertSame(1, $course->assignments()->where('status', 'draft')->count());
            $this->assertTrue($course->assignments()
                ->where('title', 'Tugas 1 - '.$course->name)
                ->firstOrFail()
                ->due_at
                ->isPast());
            $this->assertTrue($course->assignments()
                ->where('title', 'Tugas 2 - '.$course->name)
                ->firstOrFail()
                ->due_at
                ->isFuture());
        }

        $this->assertGreaterThanOrEqual(1, User::where('role', 'admin')->count());
        $this->assertGreaterThanOrEqual(3, User::where('role', 'dosen')->count());
        $this->assertGreaterThanOrEqual(30, User::where('role', 'mahasiswa')->count());

        $demoAssignmentIds = Assignment::query()
            ->whereHas('course', fn ($query) => $query->where('code', 'like', 'SI2514%'))
            ->pluck('id');
        $submissionCount = Submission::whereIn('assignment_id', $demoAssignmentIds)->count();
        $gradedCount = Grade::whereHas('submission', fn ($query) => $query
            ->whereIn('assignment_id', $demoAssignmentIds))->count();
        $this->assertGreaterThanOrEqual(100, $submissionCount);
        $this->assertEqualsWithDelta(0.6, $gradedCount / max(1, $submissionCount), 0.02);

        $demoStudent = User::where('email', 'mahasiswa@kampuslms.test')->firstOrFail();
        $firstDemoCourse = $demoCourses->firstWhere('code', 'SI251401');
        $this->assertNotNull($firstDemoCourse);
        $this->assertTrue($firstDemoCourse->students()->whereKey($demoStudent->id)->exists());

        $this->actingAs($demoStudent)
            ->get(route('mahasiswa.catalog.index'))
            ->assertOk()
            ->assertSee('✓ Terdaftar');

        $newCourse = Course::factory()->create();

        $this->assertSame(0, $newCourse->students()->count());
    }

    public function test_database_seeder_is_repeatable_and_preserves_existing_demo_accounts(): void
    {
        $this->seed(DatabaseSeeder::class);

        $student = User::where('email', 'mahasiswa@kampuslms.test')->firstOrFail();
        $student->update([
            'name' => 'Akun Demo yang Sudah Disesuaikan',
            'password' => Hash::make('existing-demo-password'),
        ]);
        $submission = Submission::firstOrFail();
        $submission->update(['note' => 'Pengumpulan yang sudah disesuaikan']);
        $grade = Grade::where('submission_id', $submission->id)->firstOrFail();
        $grade->update(['score' => 66.5]);
        $counts = [
            User::count(),
            Course::count(),
            Assignment::count(),
            Submission::count(),
            Grade::count(),
        ];

        $this->seed(DatabaseSeeder::class);

        $student->refresh();
        $submission->refresh();
        $grade->refresh();
        $this->assertSame('Akun Demo yang Sudah Disesuaikan', $student->name);
        $this->assertTrue(Hash::check('existing-demo-password', $student->password));
        $this->assertSame('Pengumpulan yang sudah disesuaikan', $submission->note);
        $this->assertSame('66.50', $grade->score);
        $this->assertSame($counts, [
            User::count(),
            Course::count(),
            Assignment::count(),
            Submission::count(),
            Grade::count(),
        ]);
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

    public function test_authenticated_user_can_get_only_public_student_fields_for_a_course(): void
    {
        $lecturer = User::factory()->dosen()->create();
        $student = User::factory()->mahasiswa()->create([
            'name' => 'Mahasiswa Terdaftar',
            'nim_nip' => '20260001',
            'email' => 'privat@example.test',
        ]);
        $course = Course::factory()->for($lecturer, 'lecturer')->create();
        $course->students()->attach($student, ['enrolled_at' => now()]);

        $this->actingAs($lecturer)
            ->getJson(route('courses.students.index', $course))
            ->assertOk()
            ->assertExactJson([
                [
                    'name' => 'Mahasiswa Terdaftar',
                    'nim_nip' => '20260001',
                ],
            ])
            ->assertDontSee('privat@example.test');
    }

    public function test_guest_cannot_get_course_students(): void
    {
        $course = Course::factory()->create();

        $this->get(route('courses.students.index', $course))
            ->assertRedirect(route('login'));
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

    public function test_student_enrollment_request_notifies_course_lecturer(): void
    {
        $lecturer = User::factory()->dosen()->create();
        $student = User::factory()->mahasiswa()->create();
        $course = Course::factory()->for($lecturer, 'lecturer')->create([
            'status' => 'active',
        ]);

        $this->actingAs($student)
            ->post(route('mahasiswa.courses.enroll', $course))
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseHas('notifications', [
            'notifiable_type' => User::class,
            'notifiable_id' => $lecturer->id,
            'type' => EnrollmentRequestedNotification::class,
        ]);
    }

    public function test_student_can_request_self_enrollment_into_a_prepopulated_course(): void
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
            ->assertSessionHas('success');

        $this->assertDatabaseHas('enrollment_requests', [
            'course_id' => $course->id,
            'user_id' => $student->id,
            'status' => 'pending',
        ]);
    }

    public function test_student_can_reapply_after_rejection_and_old_reason_is_cleared(): void
    {
        Event::fake([EnrollmentRequested::class]);

        $lecturer = User::factory()->dosen()->create();
        $student = User::factory()->mahasiswa()->create();
        $course = Course::factory()->for($lecturer, 'lecturer')->create([
            'status' => 'active',
            'sks' => 3,
        ]);
        $enrollmentRequest = EnrollmentRequest::create([
            'course_id' => $course->id,
            'user_id' => $student->id,
            'status' => 'rejected',
            'rejection_reason' => 'Perlu melengkapi prasyarat.',
        ]);

        $this->actingAs($student)
            ->post(route('mahasiswa.courses.enroll', $course))
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseHas('enrollment_requests', [
            'id' => $enrollmentRequest->id,
            'status' => 'pending',
            'rejection_reason' => null,
        ]);
        Event::assertDispatched(EnrollmentRequested::class);
    }

    public function test_lecturer_can_reject_pending_request_with_reason_and_notify_student(): void
    {
        Notification::fake();

        $lecturer = User::factory()->dosen()->create();
        $student = User::factory()->mahasiswa()->create();
        $course = Course::factory()->for($lecturer, 'lecturer')->create();
        $enrollmentRequest = EnrollmentRequest::create([
            'course_id' => $course->id,
            'user_id' => $student->id,
            'status' => 'pending',
        ]);

        $this->actingAs($lecturer)
            ->post(route('dosen.enrollment-requests.reject', $enrollmentRequest), [
                'rejection_reason' => 'Mohon lengkapi prasyarat terlebih dahulu.',
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseHas('enrollment_requests', [
            'id' => $enrollmentRequest->id,
            'status' => 'rejected',
            'rejection_reason' => 'Mohon lengkapi prasyarat terlebih dahulu.',
        ]);

        Notification::assertSentTo($student, EnrollmentRejectedNotification::class);
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
