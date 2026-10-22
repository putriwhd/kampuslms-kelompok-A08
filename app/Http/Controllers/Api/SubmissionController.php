<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSubmissionRequest;
use App\Http\Resources\SubmissionResource;
use App\Models\Assignment;
use App\Models\Grade;
use App\Models\Submission;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;

class SubmissionController extends Controller
{
    public function store(StoreSubmissionRequest $request)
    {
        return $this->saveSubmission(
            $request,
            Assignment::findOrFail($request->validated('assignment_id'))
        );
    }

    // POST /assignments/{id}/submissions
    public function storeForAssignment(StoreSubmissionRequest $request, int $id)
    {
        return $this->saveSubmission($request, Assignment::findOrFail($id));
    }

    public function saveSubmission(
        StoreSubmissionRequest $request,
        Assignment $assignment
    ) {
        $user = $request->user();
        Gate::authorize('create', [Submission::class, $assignment]);

        $submittedAt = now('Asia/Makassar');
        $dueAt = $assignment->due_at ? $assignment->due_at->copy()->setTimezone('Asia/Makassar') : null;
        $isLate = $dueAt ? $submittedAt->gt($dueAt) : false;

        if ($isLate && ! $assignment->allow_late) {
            throw ValidationException::withMessages([
                'file' => ['Batas waktu pengumpulan tugas telah lewat.'],
            ]);
        }

        $validated = $request->validated();
        $file = $validated['file'];
        $disk = Storage::disk('local');
        $existingSubmission = Submission::where('assignment_id', $assignment->getKey())
            ->where('user_id', $user->getKey())
            ->first();
        $oldFilePath = $existingSubmission?->file_path;
        $filePath = $file->store('submissions', 'local');

        if (! is_string($filePath)) {
            throw new \RuntimeException('Gagal menyimpan berkas submission.');
        }

        $submission = Submission::updateOrCreate(
            [
                'assignment_id' => $assignment->getKey(),
                'user_id' => $user->getKey(),
            ],
            [
                'file_path' => $filePath,
                'original_name' => $file->getClientOriginalName(),
                'file_size' => $file->getSize(),
                'note' => $validated['note'] ?? null,
                'submitted_at' => $submittedAt,
                'is_late' => $isLate,
            ]
        );

        if (
            is_string($oldFilePath)
            && $oldFilePath !== ''
            && $oldFilePath !== $filePath
            && $disk->exists($oldFilePath)
        ) {
            $disk->delete($oldFilePath);
        }

        return (new SubmissionResource($submission->load([
            'assignment.course' => fn ($query) => $query
                ->with('lecturer')
                ->withCount(['materials', 'assignments']),
            'student',
            'grade.grader',
        ])))
            ->response()
            ->setStatusCode($existingSubmission ? 200 : 201);
    }

    public function grade(Request $request, $id)
    {
        $user = $request->user();

        if ($user->role !== 'dosen') {
            return response()->json(['message' => 'Anda tidak memiliki akses ke sumber daya ini.'], 403);
        }

        $submission = Submission::with(['assignment.course', 'grade'])->findOrFail($id);

        if ($submission->assignment->course->lecturer_id !== $user->id) {
            return response()->json(['message' => 'Anda tidak memiliki akses ke sumber daya ini.'], 403);
        }

        $validated = $request->validate([
            'score'    => ['required', 'numeric', 'min:0', 'max:100'],
            'feedback' => ['nullable', 'string'],
        ]);

        // Cek apakah nilai sudah pernah dibuat sebelumnya
        $isNew = ! $submission->grade()->exists();

        Grade::updateOrCreate(
            ['submission_id' => $submission->id],
            [
                'graded_by' => $user->id,
                'score'     => $validated['score'],
                'feedback'  => $validated['feedback'] ?? null,
                'graded_at' => now(),
            ]
        );

        $statusCode = $isNew ? 201 : 200;

        return (new SubmissionResource($submission->load([
            'assignment.course' => fn ($query) => $query
                ->with('lecturer')
                ->withCount(['materials', 'assignments']),
            'student',
            'grade.grader',
        ])))
            ->response()
            ->setStatusCode($statusCode);
    }

    public function download($id)
    {
        $submission = Submission::with('assignment.course')->findOrFail($id);
        Gate::authorize('view', $submission);

        $disk = Storage::disk('local');
        abort_unless($submission->file_path && $disk->exists($submission->file_path), 404);

        return $disk->download($submission->file_path, $submission->original_name);
    }
}