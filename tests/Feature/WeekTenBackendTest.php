<?php

namespace Tests\Feature;

use App\Events\AssignmentPublished;
use App\Events\SubmissionGraded;
use App\Models\Assignment;
use App\Models\Course;
use App\Models\Submission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class WeekTenBackendTest extends TestCase
{
    use RefreshDatabase;

    public function test_web_assignment_publication_and_grading_dispatch_events(): void
    {
        Event::fake();

        $lecturer = User::factory()->dosen()->create();
        $student = User::factory()->mahasiswa()->create();
        $course = Course::factory()->for($lecturer, 'lecturer')->create();
        $course->students()->attach($student, ['enrolled_at' => now()]);

        $this->actingAs($lecturer)
            ->post(route('dosen.courses.assignments.store', $course), [
                'course_id' => $course->getKey(),
                'title' => 'Published web assignment',
                'instructions' => 'Complete the task.',
                'due_at' => now()->addDays(3)->toDateTimeString(),
                'status' => 'published',
            ])
            ->assertRedirect();

        Event::assertDispatched(AssignmentPublished::class);

        $draft = Assignment::factory()->for($course)->create([
            'created_by' => $lecturer->getKey(),
            'status' => 'draft',
        ]);

        $this->patch(route('dosen.assignments.update', $draft), [
            'status' => 'published',
        ])->assertRedirect();

        Event::assertDispatchedTimes(AssignmentPublished::class, 2);

        $submission = Submission::factory()->for($draft)->create([
            'user_id' => $student->getKey(),
        ]);

        $this->post(route('dosen.courses.assignments.submissions.grade', [
            'course' => $course,
            'assignment' => $draft,
            'submission' => $submission,
        ]), [
            'score' => 82,
            'feedback' => 'Good work.',
        ])->assertRedirect();

        Event::assertDispatched(SubmissionGraded::class);

        $this->get(route('dosen.grade-recap.index'))
            ->assertOk()
            ->assertJsonFragment([
                'student_id' => $student->getKey(),
                'assignment_id' => $draft->getKey(),
                'score' => '82.00',
            ]);

        $this->actingAs($student)
            ->get(route('mahasiswa.grade-recap.index'))
            ->assertOk()
            ->assertJsonFragment([
                'assignment_id' => $draft->getKey(),
                'score' => '82.00',
                'feedback' => 'Good work.',
            ]);

        $this->actingAs($lecturer)
            ->get(route('mahasiswa.grade-recap.index'))
            ->assertForbidden();
    }
}
