<?php

namespace Tests\Feature;

use App\Models\Assignment;
use App\Models\Course;
use App\Models\Grade;
use App\Models\Submission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class WeekTenFrontendTest extends TestCase
{
    use RefreshDatabase;

    public function test_notification_center_and_preview_use_the_signed_in_users_notifications(): void
    {
        $student = User::factory()->mahasiswa()->create();
        $otherStudent = User::factory()->mahasiswa()->create();

        $student->notifications()->create([
            'id' => (string) Str::uuid(),
            'type' => 'App\\Notifications\\NewAssignmentNotification',
            'data' => [
                'title' => 'Tugas Algoritma',
                'course_name' => 'Algoritma',
                'url' => '/mahasiswa/assignments/1',
            ],
        ]);
        $otherStudent->notifications()->create([
            'id' => (string) Str::uuid(),
            'type' => 'test',
            'data' => ['title' => 'Notifikasi privat'],
        ]);

        $this->actingAs($student)
            ->get(route('notifications.index'))
            ->assertOk()
            ->assertSeeText('Tugas Algoritma')
            ->assertSeeText('Mata kuliah: Algoritma')
            ->assertDontSeeText('Notifikasi privat');

        $this->getJson(route('notifications.preview'))
            ->assertOk()
            ->assertJsonPath('data.0.data.title', 'Tugas Algoritma')
            ->assertJsonPath('unread_count', 1)
            ->assertJsonMissing(['title' => 'Notifikasi privat']);

        $notification = $student->notifications()->first();

        // Mahasiswa lain mencoba menandai notifikasi milik student -> 403 Forbidden
        $this->actingAs($otherStudent)
            ->patch(route('notifications.read', $notification->id))
            ->assertForbidden();

        // Student menandai notifikasinya sendiri -> Redirect sukses & read_at terisi
        $this->actingAs($student)
            ->patch(route('notifications.read', $notification->id))
            ->assertRedirect();

        $this->assertNotNull($notification->fresh()->read_at);
    }

    public function test_grade_recap_pages_show_course_and_student_scoped_grades(): void
    {
        $lecturer = User::factory()->dosen()->create();
        $student = User::factory()->mahasiswa()->create();
        $otherStudent = User::factory()->mahasiswa()->create();
        $course = Course::factory()->for($lecturer, 'lecturer')->create([
            'status' => 'active',
        ]);
        $course->students()->attach([$student->id, $otherStudent->id], [
            'enrolled_at' => now(),
        ]);
        $assignment = Assignment::factory()->for($course)->create([
            'created_by' => $lecturer->id,
            'status' => 'published',
            'title' => 'Tugas Minggu 10',
        ]);
        $studentSubmission = Submission::factory()->for($assignment)->create([
            'user_id' => $student->id,
        ]);
        $otherSubmission = Submission::factory()->for($assignment)->create([
            'user_id' => $otherStudent->id,
        ]);
        Grade::factory()->for($studentSubmission)->create([
            'graded_by' => $lecturer->id,
            'score' => 91,
            'feedback' => 'Bagus.',
        ]);
        Grade::factory()->for($otherSubmission)->create([
            'graded_by' => $lecturer->id,
            'score' => 42,
            'feedback' => 'Feedback privat mahasiswa lain.',
        ]);

        $this->actingAs($lecturer)
            ->get(route('dosen.grade-recap.page'))
            ->assertOk()
            ->assertSeeText('Rekap Nilai Mata Kuliah')
            ->assertSeeText('Tugas Minggu 10')
            ->assertSeeText('91.00')
            ->assertSeeText('42.00');

        $this->actingAs($student)
            ->get(route('mahasiswa.grade-recap.page'))
            ->assertOk()
            ->assertSeeText('Rekap Nilai Saya')
            ->assertSeeText('Tugas Minggu 10')
            ->assertSeeText('91.00')
            ->assertSeeText('Bagus.')
            ->assertDontSeeText('42.00')
            ->assertDontSeeText('Feedback privat mahasiswa lain.');

        $this->actingAs($otherStudent)
            ->get(route('mahasiswa.grade-recap.page'))
            ->assertOk()
            ->assertSeeText('42.00')
            ->assertDontSeeText('Bagus.');
    }
}
