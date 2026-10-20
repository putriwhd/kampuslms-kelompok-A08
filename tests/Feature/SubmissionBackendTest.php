<?php

namespace Tests\Feature;

use App\Http\Requests\StoreSubmissionRequest;
use App\Models\Assignment;
use App\Models\Course;
use App\Models\Material;
use App\Models\Submission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Routing\Route;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class SubmissionBackendTest extends TestCase
{
    use RefreshDatabase;

    private function validateRequest(
        StoreSubmissionRequest $request,
        array $data,
        ?int $routeAssignmentId = null
    ): \Illuminate\Validation\Validator {
        if ($routeAssignmentId !== null) {
            $route = new Route('POST', 'api/v1/assignments/{id}/submissions', []);
            $route->bind(request());
            $route->setParameter('id', $routeAssignmentId);
            $request->setRouteResolver(fn () => $route);
        }

        return Validator::make(
            $data,
            $request->rules(),
            $request->messages()
        );
    }

    public function test_store_submission_request_validates_required_file_mimes_and_size(): void
    {
        Storage::fake('local');
        $lecturer = User::factory()->dosen()->create();
        $student = User::factory()->mahasiswa()->create();
        $course = Course::factory()->for($lecturer, 'lecturer')->create();
        $assignment = Assignment::factory()->for($course)->create();

        // 1. Missing file fails
        $validatorMissing = $this->validateRequest(new StoreSubmissionRequest(), [
            'assignment_id' => $assignment->id,
        ]);
        $this->assertTrue($validatorMissing->fails());
        $this->assertArrayHasKey('file', $validatorMissing->errors()->toArray());

        // 2. Disallowed extension (e.g., txt or png) fails
        $validatorInvalidMime = $this->validateRequest(new StoreSubmissionRequest(), [
            'assignment_id' => $assignment->id,
            'file' => UploadedFile::fake()->create('report.txt', 100, 'text/plain'),
        ]);
        $this->assertTrue($validatorInvalidMime->fails());
        $this->assertArrayHasKey('file', $validatorInvalidMime->errors()->toArray());

        // 3. Exceeds max 5MB (5120 KB) fails (e.g., 5121 KB)
        $validatorTooLarge = $this->validateRequest(new StoreSubmissionRequest(), [
            'assignment_id' => $assignment->id,
            'file' => UploadedFile::fake()->create('large.pdf', 5121, 'application/pdf'),
        ]);
        $this->assertTrue($validatorTooLarge->fails());
        $this->assertArrayHasKey('file', $validatorTooLarge->errors()->toArray());

        // 4. Valid PDF under 5MB passes
        $validatorPdf = $this->validateRequest(new StoreSubmissionRequest(), [
            'assignment_id' => $assignment->id,
            'file' => UploadedFile::fake()->create('tugas.pdf', 2048, 'application/pdf'),
        ]);
        $this->assertTrue($validatorPdf->passes());

        // 5. Valid DOC passes
        $validatorDoc = $this->validateRequest(new StoreSubmissionRequest(), [
            'assignment_id' => $assignment->id,
            'file' => UploadedFile::fake()->create('tugas.doc', 1024, 'application/msword'),
        ]);
        $this->assertTrue($validatorDoc->passes());

        // 6. Valid DOCX passes
        $validatorDocx = $this->validateRequest(new StoreSubmissionRequest(), [
            'assignment_id' => $assignment->id,
            'file' => UploadedFile::fake()->create('tugas.docx', 1024, 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'),
        ]);
        $this->assertTrue($validatorDocx->passes());

        // 7. Route ID parameter satisfies assignment_id requirement
        $validatorRoute = $this->validateRequest(
            new StoreSubmissionRequest(),
            ['file' => UploadedFile::fake()->create('tugas.pdf', 100, 'application/pdf')],
            $assignment->id
        );
        $this->assertTrue($validatorRoute->passes());
    }

    public function test_store_submission_request_authorize_rules(): void
    {
        $student = User::factory()->mahasiswa()->create();
        $lecturer = User::factory()->dosen()->create();
        $admin = User::factory()->admin()->create();

        $reqGuest = new StoreSubmissionRequest();
        $this->assertFalse($reqGuest->authorize());

        $reqStudent = new StoreSubmissionRequest();
        $reqStudent->setUserResolver(fn () => $student);
        $this->assertTrue($reqStudent->authorize());

        $reqLecturer = new StoreSubmissionRequest();
        $reqLecturer->setUserResolver(fn () => $lecturer);
        $this->assertFalse($reqLecturer->authorize());

        $reqAdmin = new StoreSubmissionRequest();
        $reqAdmin->setUserResolver(fn () => $admin);
        $this->assertFalse($reqAdmin->authorize());
    }

    public function test_submission_update_or_create_cleans_old_physical_file_on_resubmission(): void
    {
        Storage::fake('local');
        $lecturer = User::factory()->dosen()->create();
        $student = User::factory()->mahasiswa()->create();
        $course = Course::factory()->for($lecturer, 'lecturer')->create(['status' => 'active']);
        $course->students()->attach($student, ['enrolled_at' => now()]);
        $assignment = Assignment::factory()->for($course)->create([
            'status' => 'published',
            'due_at' => now()->addDays(2),
            'allow_late' => true,
        ]);

        Sanctum::actingAs($student);

        // First submission
        $firstResponse = $this->postJson("/api/v1/assignments/{$assignment->id}/submissions", [
            'file' => UploadedFile::fake()->create('jawaban_v1.pdf', 500, 'application/pdf'),
            'note' => 'Versi pertama',
        ]);
        $firstResponse->assertCreated();

        $submission = Submission::where('assignment_id', $assignment->id)
            ->where('user_id', $student->id)
            ->firstOrFail();
        $firstFilePath = $submission->file_path;

        Storage::disk('local')->assertExists($firstFilePath);
        $this->assertSame('jawaban_v1.pdf', $submission->original_name);
        $this->assertSame(1, Submission::where('assignment_id', $assignment->id)->where('user_id', $student->id)->count());

        // Re-submission by the same student
        $secondResponse = $this->postJson("/api/v1/assignments/{$assignment->id}/submissions", [
            'file' => UploadedFile::fake()->create('jawaban_v2.docx', 750, 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'),
            'note' => 'Versi revisi',
        ]);
        $secondResponse->assertOk();

        // Exactly one submission record exists (updateOrCreate)
        $this->assertSame(1, Submission::where('assignment_id', $assignment->id)->where('user_id', $student->id)->count());

        $submission->refresh();
        $secondFilePath = $submission->file_path;

        $this->assertNotEquals($firstFilePath, $secondFilePath);
        $this->assertSame('jawaban_v2.docx', $submission->original_name);
        $this->assertSame('Versi revisi', $submission->note);

        // Old file must be cleaned up, new file must exist
        Storage::disk('local')->assertMissing($firstFilePath);
        Storage::disk('local')->assertExists($secondFilePath);
    }

    public function test_server_lateness_calculation_and_deadline_rejection(): void
    {
        Storage::fake('local');
        $lecturer = User::factory()->dosen()->create();
        $student = User::factory()->mahasiswa()->create();
        $course = Course::factory()->for($lecturer, 'lecturer')->create(['status' => 'active']);
        $course->students()->attach($student, ['enrolled_at' => now()]);

        // Case A: Submitted before deadline -> is_late = false
        $onTimeAssignment = Assignment::factory()->for($course)->create([
            'status' => 'published',
            'due_at' => now()->addHour(),
            'allow_late' => false,
        ]);

        Sanctum::actingAs($student);
        $resA = $this->postJson("/api/v1/assignments/{$onTimeAssignment->id}/submissions", [
            'file' => UploadedFile::fake()->create('on_time.pdf', 100, 'application/pdf'),
        ]);
        $resA->assertCreated();

        $subA = Submission::where('assignment_id', $onTimeAssignment->id)->firstOrFail();
        $this->assertFalse((bool) $subA->is_late);

        // Case B: Submitted after deadline when allow_late = true -> is_late = true, accepted
        $allowLateAssignment = Assignment::factory()->for($course)->create([
            'status' => 'published',
            'due_at' => now()->subHour(),
            'allow_late' => true,
        ]);

        $resB = $this->postJson("/api/v1/assignments/{$allowLateAssignment->id}/submissions", [
            'file' => UploadedFile::fake()->create('late_allowed.pdf', 100, 'application/pdf'),
        ]);
        $resB->assertCreated();

        $subB = Submission::where('assignment_id', $allowLateAssignment->id)->firstOrFail();
        $this->assertTrue((bool) $subB->is_late);

        // Case C: Submitted after deadline when allow_late = false -> rejected (422)
        $noLateAssignment = Assignment::factory()->for($course)->create([
            'status' => 'published',
            'due_at' => now()->subHour(),
            'allow_late' => false,
        ]);

        $resC = $this->postJson("/api/v1/assignments/{$noLateAssignment->id}/submissions", [
            'file' => UploadedFile::fake()->create('late_rejected.pdf', 100, 'application/pdf'),
        ]);
        $resC->assertUnprocessable();
        $resC->assertJsonValidationErrors('file');

        $this->assertDatabaseMissing('submissions', [
            'assignment_id' => $noLateAssignment->id,
            'user_id' => $student->id,
        ]);
    }

    public function test_submission_policy_view_restricts_download_to_owner_lecturer_and_admin(): void
    {
        Storage::fake('local');
        $admin = User::factory()->admin()->create();
        $lecturer = User::factory()->dosen()->create();
        $otherLecturer = User::factory()->dosen()->create();
        $ownerStudent = User::factory()->mahasiswa()->create();
        $otherStudent = User::factory()->mahasiswa()->create();

        $course = Course::factory()->for($lecturer, 'lecturer')->create(['status' => 'active']);
        $course->students()->attach($ownerStudent, ['enrolled_at' => now()]);
        $course->students()->attach($otherStudent, ['enrolled_at' => now()]);

        $assignment = Assignment::factory()->for($course)->create(['status' => 'published']);

        $submission = Submission::create([
            'assignment_id' => $assignment->id,
            'user_id' => $ownerStudent->id,
            'file_path' => 'submissions/student_doc.pdf',
            'original_name' => 'student_doc.pdf',
            'file_size' => 1024,
            'submitted_at' => now(),
            'is_late' => false,
        ]);
        Storage::disk('local')->put('submissions/student_doc.pdf', 'file content');

        // 1. Owner student can download
        $this->actingAs($ownerStudent)
            ->get(route('mahasiswa.courses.assignments.submissions.download', [
                'course' => $course,
                'assignment' => $assignment,
                'submission' => $submission,
            ]))
            ->assertOk();

        // 2. Course lecturer can download
        $this->actingAs($lecturer)
            ->get(route('dosen.courses.assignments.submissions.download', [
                'course' => $course,
                'assignment' => $assignment,
                'submission' => $submission,
            ]))
            ->assertOk();

        // 3. Admin can download
        $this->actingAs($admin)
            ->get(route('admin.courses.assignments.submissions.download', [
                'course' => $course,
                'assignment' => $assignment,
                'submission' => $submission,
            ]))
            ->assertOk();

        // 4. Other student gets 403 Forbidden
        $this->actingAs($otherStudent)
            ->get(route('mahasiswa.courses.assignments.submissions.download', [
                'course' => $course,
                'assignment' => $assignment,
                'submission' => $submission,
            ]))
            ->assertForbidden();

        // 5. Other lecturer gets 403 Forbidden
        $this->actingAs($otherLecturer)
            ->get(route('dosen.courses.assignments.submissions.download', [
                'course' => $course,
                'assignment' => $assignment,
                'submission' => $submission,
            ]))
            ->assertForbidden();

        // 6. API download endpoint also enforces the same policy
        Sanctum::actingAs($otherStudent);
        $this->getJson("/api/v1/submissions/{$submission->id}/download")
            ->assertForbidden();

        Sanctum::actingAs($ownerStudent);
        $this->getJson("/api/v1/submissions/{$submission->id}/download")
            ->assertOk();
    }

    public function test_static_deleting_event_and_assignment_destroy_file_cleanup(): void
    {
        Storage::fake('local');
        $lecturer = User::factory()->dosen()->create();
        $student = User::factory()->mahasiswa()->create();
        $course = Course::factory()->for($lecturer, 'lecturer')->create(['status' => 'active']);
        $assignment = Assignment::factory()->for($course)->create();

        $submission = Submission::create([
            'assignment_id' => $assignment->id,
            'user_id' => $student->id,
            'file_path' => 'submissions/standalone_file.pdf',
            'original_name' => 'standalone_file.pdf',
            'file_size' => 1024,
            'submitted_at' => now(),
            'is_late' => false,
        ]);
        Storage::disk('local')->put('submissions/standalone_file.pdf', 'standalone content');

        // Test static::deleting on Submission directly
        Storage::disk('local')->assertExists('submissions/standalone_file.pdf');
        $submission->delete();
        Storage::disk('local')->assertMissing('submissions/standalone_file.pdf');

        // Test AssignmentController::destroy file cleanup
        $assignment2 = Assignment::factory()->for($course)->create();
        $subA = Submission::create([
            'assignment_id' => $assignment2->id,
            'user_id' => $student->id,
            'file_path' => 'submissions/assignment_file_a.pdf',
            'original_name' => 'file_a.pdf',
            'file_size' => 1024,
            'submitted_at' => now(),
            'is_late' => false,
        ]);
        Storage::disk('local')->put('submissions/assignment_file_a.pdf', 'file a content');

        $this->actingAs($lecturer)
            ->delete(route('dosen.assignments.destroy', $assignment2))
            ->assertRedirect();

        Storage::disk('local')->assertMissing('submissions/assignment_file_a.pdf');
        $this->assertDatabaseMissing('assignments', ['id' => $assignment2->id]);
        $this->assertDatabaseMissing('submissions', ['id' => $subA->id]);
    }

    public function test_course_destroy_cleans_material_and_submission_files_before_database_cascade(): void
    {
        Storage::fake('local');
        $admin = User::factory()->admin()->create();
        $lecturer = User::factory()->dosen()->create();
        $student = User::factory()->mahasiswa()->create();
        $course = Course::factory()->for($lecturer, 'lecturer')->create([
            'status' => 'active',
        ]);
        $assignment = Assignment::factory()->for($course)->create();
        $material = Material::create([
            'course_id' => $course->id,
            'uploaded_by' => $lecturer->id,
            'title' => 'Materi untuk uji cascade',
            'description' => 'File harus dibersihkan saat kelas dihapus.',
            'type' => 'file',
            'file_path' => 'materials/'.$course->id.'/cascade-material.pdf',
            'original_name' => 'cascade-material.pdf',
            'file_size' => 1024,
            'mime_type' => 'application/pdf',
        ]);
        $submission = Submission::create([
            'assignment_id' => $assignment->id,
            'user_id' => $student->id,
            'file_path' => 'submissions/cascade-submission.pdf',
            'original_name' => 'cascade-submission.pdf',
            'file_size' => 1024,
            'submitted_at' => now(),
            'is_late' => false,
        ]);
        Storage::disk('local')->put($material->file_path, 'material');
        Storage::disk('local')->put($submission->file_path, 'submission');

        $this->actingAs($admin)
            ->delete(route('admin.courses.destroy', $course))
            ->assertRedirect();

        Storage::disk('local')->assertMissing($material->file_path);
        Storage::disk('local')->assertMissing($submission->file_path);
        $this->assertDatabaseMissing('courses', ['id' => $course->id]);
        $this->assertDatabaseMissing('submissions', ['id' => $submission->id]);
    }

    public function test_lecturer_recap_data_provides_student_list_status_and_lateness(): void
    {
        $lecturer = User::factory()->dosen()->create();
        $studentSubmittedOnTime = User::factory()->mahasiswa()->create(['name' => 'Andi OnTime']);
        $studentSubmittedLate = User::factory()->mahasiswa()->create(['name' => 'Budi Late']);
        $studentNotSubmitted = User::factory()->mahasiswa()->create(['name' => 'Citra NotYet']);

        $course = Course::factory()->for($lecturer, 'lecturer')->create(['status' => 'active']);
        $course->students()->attach([
            $studentSubmittedOnTime->id => ['enrolled_at' => now()],
            $studentSubmittedLate->id => ['enrolled_at' => now()],
            $studentNotSubmitted->id => ['enrolled_at' => now()],
        ]);

        $dueAt = Carbon::parse('2026-10-15 12:00:00');
        $assignment = Assignment::factory()->for($course)->create([
            'status' => 'published',
            'due_at' => $dueAt,
            'allow_late' => true,
        ]);

        // Student 1: on time
        Submission::create([
            'assignment_id' => $assignment->id,
            'user_id' => $studentSubmittedOnTime->id,
            'file_path' => 'submissions/andi.pdf',
            'original_name' => 'andi.pdf',
            'file_size' => 1024,
            'submitted_at' => $dueAt->copy()->subHours(2),
            'is_late' => false,
        ]);

        // Student 2: late
        Submission::create([
            'assignment_id' => $assignment->id,
            'user_id' => $studentSubmittedLate->id,
            'file_path' => 'submissions/budi.pdf',
            'original_name' => 'budi.pdf',
            'file_size' => 1024,
            'submitted_at' => $dueAt->copy()->addHours(3),
            'is_late' => true,
        ]);

        // Web view test
        $this->actingAs($lecturer)
            ->get(route('dosen.assignments.show', $assignment))
            ->assertOk()
            ->assertSee('Andi OnTime')
            ->assertSee('Budi Late')
            ->assertSee('Citra NotYet')
            ->assertSee('Sudah mengumpulkan')
            ->assertSee('Belum mengumpulkan')
            ->assertSee('Terlambat')
            ->assertSee('Tepat waktu');

        // API recap test
        Sanctum::actingAs($lecturer);
        $response = $this->getJson("/api/v1/assignments/{$assignment->id}/recap");
        $response->assertOk();

        $recap = collect($response->json('data'));
        $this->assertCount(3, $recap);

        $andi = $recap->firstWhere('student_id', $studentSubmittedOnTime->id);
        $this->assertNotNull($andi);
        $this->assertSame('submitted', $andi['submission_status']);
        $this->assertFalse($andi['is_late']);
        $this->assertNotNull($andi['submitted_at']);

        $budi = $recap->firstWhere('student_id', $studentSubmittedLate->id);
        $this->assertNotNull($budi);
        $this->assertSame('submitted', $budi['submission_status']);
        $this->assertTrue($budi['is_late']);
        $this->assertNotNull($budi['submitted_at']);

        $citra = $recap->firstWhere('student_id', $studentNotSubmitted->id);
        $this->assertNotNull($citra);
        $this->assertSame('not_submitted', $citra['submission_status']);
        $this->assertFalse($citra['is_late']);
        $this->assertNull($citra['submitted_at']);
    }

    public function test_web_submission_and_resubmission_for_mahasiswa(): void
    {
        Storage::fake('local');
        $lecturer = User::factory()->dosen()->create();
        $student = User::factory()->mahasiswa()->create();
        $course = Course::factory()->for($lecturer, 'lecturer')->create(['status' => 'active']);
        $course->students()->attach($student, ['enrolled_at' => now()]);
        $assignment = Assignment::factory()->for($course)->create([
            'status' => 'published',
            'due_at' => now()->addDays(3),
            'allow_late' => true,
        ]);

        $this->actingAs($student)
            ->post(route('mahasiswa.courses.assignments.submissions.store', [
                'course' => $course,
                'assignment' => $assignment,
            ]), [
                'file' => UploadedFile::fake()->create('web_answer.pdf', 300, 'application/pdf'),
                'note' => 'Unggahan web',
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseHas('submissions', [
            'assignment_id' => $assignment->id,
            'user_id' => $student->id,
            'original_name' => 'web_answer.pdf',
        ]);

        $this->get(route('mahasiswa.courses.assignments.scoped-show', [
            'course' => $course,
            'assignment' => $assignment,
        ]))
            ->assertOk()
            ->assertSee('name="file"', false)
            ->assertSee('required', false)
            ->assertSee('Pengumpulan ulang mengganti berkas sebelumnya.');
    }
}
