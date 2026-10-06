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
            return response()->json(['message' => 'Forbidden. Hanya mahasiswa yang dapat mengumpulkan tugas.'], 403);
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
            return response()->json(['message' => 'Forbidden. Anda tidak terdaftar di mata kuliah ini.'], 403);
        }

        $file = $validated['file'];

        $submission = Submission::create([
            'assignment_id' => $validated['assignment_id'],
            'user_id' => $user->id,
            'file_path' => $file->store('submissions'),
            'original_name' => $file->getClientOriginalName(),
            'file_size' => $file->getSize(),
            'note' => $validated['note'] ?? null,
            'submitted_at' => now(),
            'is_late' => $assignment->due_at->isPast(),
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
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        $submission = Submission::with(['assignment.course', 'grade'])->findOrFail($id);

        // Anti-IDOR (Cross-Dosen)
        if ($submission->assignment->course->lecturer_id !== $user->id) {
            return response()->json(['message' => 'Forbidden. Anda tidak dapat memberi nilai pada tugas ini.'], 403);
        }

        $validated = $request->validate([
            'score' => ['required', 'numeric', 'min:0', 'max:100'],
            'feedback' => ['nullable', 'string'],
        ]);

        Grade::updateOrCreate(
            ['submission_id' => $submission->id],
            [
                'graded_by' => $user->id,
                'score' => $validated['score'],
                'feedback' => $validated['feedback'] ?? null,
                'graded_at' => now(),
            ]
        );

        return new SubmissionResource($submission->load([
            'assignment.course' => fn ($query) => $query
                ->with('lecturer')
                ->withCount(['materials', 'assignments']),
            'student',
            'grade.grader',
        ]));
    }
}
