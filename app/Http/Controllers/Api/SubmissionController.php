<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\SubmissionResource;
use App\Models\Assignment;
use App\Models\Grade;
use App\Models\Submission;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SubmissionController extends Controller
{
    public function store(Request $request)
    {
        $user = $request->user();

        if ($user->role !== 'mahasiswa') {
            return response()->json(['message' => 'Anda tidak memiliki akses ke sumber daya ini.'], 403);
        }

        $validated = $request->validate([
            'assignment_id' => [
                'required',
                Rule::unique('submissions', 'assignment_id')
                    ->where(fn ($query) => $query->where('user_id', $user->id)),
                'exists:assignments,id',
            ],
            'file' => ['required', 'file'],
            'note' => ['nullable', 'string'],
        ]);

        $assignment = Assignment::with('course')->findOrFail($validated['assignment_id']);
        if (! $assignment->course->students()->whereKey($user->id)->exists()) {
            return response()->json(['message' => 'Anda tidak memiliki akses ke sumber daya ini.'], 403);
        }

        $file = $validated['file'];

        $submission = Submission::create([
            'assignment_id' => $validated['assignment_id'],
            'user_id'       => $user->id,
            'file_path'     => $file->store('submissions'),
            'original_name' => $file->getClientOriginalName(),
            'file_size'     => $file->getSize(),
            'note'          => $validated['note'] ?? null,
            'submitted_at'  => now(),
            'is_late'       => $assignment->due_at ? $assignment->due_at->isPast() : false,
        ]);

        return (new SubmissionResource($submission->load([
            'assignment.course' => fn ($query) => $query
                ->with('lecturer')
                ->withCount(['materials', 'assignments']),
            'student',
            'grade.grader',
        ])))
            ->response()
            ->setStatusCode(201);
    }

    // POST /assignments/{id}/submissions
    public function storeForAssignment(Request $request, $id)
    {
        $user = $request->user();

        if ($user->role !== 'mahasiswa') {
            return response()->json(['message' => 'Anda tidak memiliki akses ke sumber daya ini.'], 403);
        }

        $assignment = Assignment::with('course')->findOrFail($id);

        // Periksa apakah mahasiswa terdaftar pada course
        if (! $assignment->course->students()->whereKey($user->id)->exists()) {
            return response()->json(['message' => 'Anda tidak memiliki akses ke sumber daya ini.'], 403);
        }

        // Cek apakah sudah pernah mengumpulkan
        $alreadySubmitted = Submission::where('assignment_id', $assignment->id)
            ->where('user_id', $user->id)
            ->exists();

        if ($alreadySubmitted) {
            return response()->json(['message' => 'Data yang diberikan tidak valid.'], 422);
        }

        $validated = $request->validate([
            'file' => ['required', 'file'],
            'note' => ['nullable', 'string'],
        ]);

        $file = $validated['file'];

        $submission = Submission::create([
            'assignment_id' => $assignment->id,
            'user_id'       => $user->id,
            'file_path'     => $file->store('submissions'),
            'original_name' => $file->getClientOriginalName(),
            'file_size'     => $file->getSize(),
            'note'          => $validated['note'] ?? null,
            'submitted_at'  => now(),
            'is_late'       => $assignment->due_at ? $assignment->due_at->isPast() : false,
        ]);

        return (new SubmissionResource($submission->load([
            'assignment.course' => fn ($query) => $query
                ->with('lecturer')
                ->withCount(['materials', 'assignments']),
            'student',
            'grade.grader',
        ])))
            ->response()
            ->setStatusCode(201);
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

        // Dinamis: 201 untuk penilain baru, 200 untuk update
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
}