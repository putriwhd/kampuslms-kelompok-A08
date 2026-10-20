<?php

namespace Tests\Feature;

use App\Models\Assignment;
use App\Models\Course;
use App\Models\Grade;
use App\Models\Material;
use App\Models\Submission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;

class ApiEndpointsTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_returns_a_user_resource_and_protected_routes_require_authentication(): void
    {
        $user = User::factory()->dosen()->create([
            'email' => 'dosen@kampuslms.test',
            'password' => 'password',
        ]);

        $this->getJson('/api/v1/me')->assertUnauthorized();

        $this->postJson('/api/v1/auth/login', [
            'email' => $user->email,
            'password' => 'password',
            'device_name' => 'feature-test',
        ])
            ->assertOk()
            ->assertJsonPath('data.user.id', $user->id)
            ->assertJsonMissingPath('data.user.password');

        $this->postJson('/api/v1/auth/login', [
            'email' => $user->email,
            'password' => 'wrong-password',
        ])
            ->assertUnprocessable()
            ->assertJsonPath('message', 'Data yang diberikan tidak valid.')
            ->assertJsonStructure(['errors' => ['email']]);
    }

    public function test_course_collection_is_paginated_and_includes_loaded_counts(): void
    {
        $lecturer = User::factory()->dosen()->create();
        $otherLecturer = User::factory()->dosen()->create();
        $course = Course::factory()->for($lecturer, 'lecturer')->create([
            'status' => 'active',
        ]);
        $otherCourse = Course::factory()->for($otherLecturer, 'lecturer')->create([
            'status' => 'active',
        ]);

        $response = $this->actingAs($lecturer, 'sanctum')
            ->getJson('/api/v1/courses')
            ->assertOk()
            ->assertJsonPath('data.0.id', $course->id)
            ->assertJsonPath('data.0.counts.materials', 0)
            ->assertJsonPath('data.0.counts.assignments', 0);

        $this->assertSame([
            'current_page' => 1,
            'last_page' => 1,
            'total' => 1,
        ], $response->json('meta'));

        $this->getJson("/api/v1/courses/{$course->id}")->assertOk();

        $this->getJson("/api/v1/courses/{$otherCourse->id}")
            ->assertForbidden();

        $draftCourse = Course::factory()->for($otherLecturer, 'lecturer')->create([
            'status' => 'draft',
        ]);
        $student = User::factory()->mahasiswa()->create();

        $this->actingAs($student, 'sanctum')
            ->getJson("/api/v1/courses/{$course->id}")
            ->assertForbidden();

        $course->students()->attach($student, ['enrolled_at' => now()]);

        $this->getJson("/api/v1/courses/{$course->id}")
            ->assertOk();

        $this->getJson("/api/v1/courses/{$draftCourse->id}")
            ->assertNotFound();
    }

    public function test_assignment_endpoints_use_schema_fields_and_enforce_course_ownership(): void
    {
        $lecturer = User::factory()->dosen()->create();
        $otherLecturer = User::factory()->dosen()->create();
        $course = Course::factory()->for($lecturer, 'lecturer')->create();
        $payload = [
            'course_id' => $course->id,
            'title' => 'API assignment',
            'instructions' => 'Complete the exercise.',
            'due_at' => '2030-12-31 23:59:00',
        ];

        $this->actingAs(User::factory()->mahasiswa()->create(), 'sanctum')
            ->postJson('/api/v1/assignments', $payload)
            ->assertForbidden();

        $this->actingAs($lecturer, 'sanctum')
            ->postJson('/api/v1/assignments', $payload)
            ->assertCreated()
            ->assertJsonPath('data.created_by', $lecturer->id)
            ->assertJsonPath('data.instructions', $payload['instructions'])
            ->assertJsonPath('data.course.counts.materials', 0)
            ->assertJsonPath('data.course.counts.assignments', 1);

        $assignment = Assignment::firstOrFail();
        $this->assertSame('published', $assignment->status);
        $this->assertSame($lecturer->id, $assignment->created_by);

        $this->actingAs($otherLecturer, 'sanctum')
            ->patchJson("/api/v1/assignments/{$assignment->id}", ['title' => 'Unauthorized'])
            ->assertForbidden();

        $this->actingAs($lecturer, 'sanctum')
            ->patchJson("/api/v1/assignments/{$assignment->id}", ['title' => 'Updated title'])
            ->assertOk()
            ->assertJsonPath('data.title', 'Updated title');

        $this->deleteJson("/api/v1/assignments/{$assignment->id}")
            ->assertNoContent();

        $this->assertDatabaseMissing('assignments', ['id' => $assignment->id]);
    }

    public function test_submission_and_grade_endpoints_write_to_their_own_tables(): void
    {
        Storage::fake('local');

        $lecturer = User::factory()->dosen()->create();
        $student = User::factory()->mahasiswa()->create();
        $course = Course::factory()->for($lecturer, 'lecturer')->create();
        $assignment = Assignment::factory()->for($course)->create();

        $this->actingAs($student, 'sanctum')
            ->post('/api/v1/submissions', [
                'assignment_id' => $assignment->id,
                'file' => UploadedFile::fake()->create('answer.pdf', 10, 'application/pdf'),
            ], ['Accept' => 'application/json'])
            ->assertForbidden();

        $course->students()->attach($student, ['enrolled_at' => now()]);

        $this->actingAs($student, 'sanctum')
            ->post('/api/v1/submissions', [
                'assignment_id' => $assignment->id,
                'file' => UploadedFile::fake()->create('answer.pdf', 10, 'application/pdf'),
                'note' => 'My work',
            ], ['Accept' => 'application/json'])
            ->assertCreated()
            ->assertJsonPath('data.user_id', $student->id);

        $submission = Submission::firstOrFail();
        $this->assertNotEmpty($submission->file_path);
        Storage::disk('local')->assertExists($submission->file_path);

        $this->actingAs($lecturer, 'sanctum')
            ->putJson("/api/v1/submissions/{$submission->id}/grade", [
                'score' => 88,
                'feedback' => 'Good work.',
            ])
            ->assertCreated()
            ->assertJsonPath('data.grade.score', '88.00');

        $this->putJson("/api/v1/submissions/{$submission->id}/grade", [
            'score' => 90,
            'feedback' => 'Revised score.',
        ])
            ->assertOk()
            ->assertJsonPath('data.grade.score', '90.00');

        $this->assertDatabaseHas('grades', [
            'submission_id' => $submission->id,
            'graded_by' => $lecturer->id,
            'score' => 90,
        ]);
        $this->assertInstanceOf(Grade::class, $submission->fresh()->grade);

        $this->actingAs($lecturer, 'sanctum')
            ->deleteJson("/api/v1/assignments/{$assignment->id}")
            ->assertNoContent();

        Storage::disk('local')->assertMissing($submission->file_path);
    }

    public function test_only_the_course_lecturer_can_grade_a_submission(): void
    {
        $lecturer = User::factory()->dosen()->create();
        $otherLecturer = User::factory()->dosen()->create();
        $course = Course::factory()->for($lecturer, 'lecturer')->create();
        $assignment = Assignment::factory()->for($course)->create();
        $submission = Submission::factory()->for($assignment)->create();

        $this->actingAs($otherLecturer, 'sanctum')
            ->putJson("/api/v1/submissions/{$submission->id}/grade", ['score' => 75])
            ->assertForbidden();

        $this->assertDatabaseCount('grades', 0);
    }

    public function test_nested_collections_eager_load_resources_and_notifications_are_scoped(): void
    {
        $lecturer = User::factory()->dosen()->create();
        $student = User::factory()->mahasiswa()->create();
        $course = Course::factory()->for($lecturer, 'lecturer')->create([
            'status' => 'active',
        ]);
        $course->students()->attach($student, ['enrolled_at' => now()]);

        Material::factory()->for($course)->for($lecturer, 'uploader')->create();
        $published = Assignment::factory()->for($course)->create([
            'created_by' => $lecturer->id,
            'status' => 'published',
        ]);
        Assignment::factory()->for($course)->create([
            'created_by' => $lecturer->id,
            'status' => 'draft',
        ]);
        Submission::factory()->for($published)->create([
            'user_id' => $student->id,
        ]);

        $this->actingAs($lecturer, 'sanctum')
            ->getJson("/api/v1/courses/{$course->id}/materials")
            ->assertOk()
            ->assertJsonPath('data.0.uploader.id', $lecturer->id)
            ->assertJsonPath('data.0.course.lecturer.id', $lecturer->id);

        $this->getJson("/api/v1/courses/{$course->id}/assignments?status=published")
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $published->id)
            ->assertJsonPath('data.0.creator.id', $lecturer->id)
            ->assertJsonMissingPath('data.0.submissions');

        $this->getJson("/api/v1/assignments/{$published->id}/submissions")
            ->assertOk()
            ->assertJsonPath('data.0.assignment.id', $published->id)
            ->assertJsonPath('data.0.student.id', $student->id);

        $notificationId = (string) Str::uuid();
        $student->notifications()->create([
            'id' => $notificationId,
            'type' => 'test',
            'data' => ['message' => 'Test notification'],
        ]);

        $this->actingAs($student, 'sanctum')
            ->getJson('/api/v1/notifications')
            ->assertOk()
            ->assertJsonPath('data.0.id', $notificationId)
            ->assertJsonMissingPath('meta.per_page');

        $readResponse = $this->postJson("/api/v1/notifications/{$notificationId}/read")
            ->assertOk()
            ->assertJsonPath('data.id', $notificationId);

        $this->assertNotNull($student->notifications()->find($notificationId)->read_at);
    }
}
