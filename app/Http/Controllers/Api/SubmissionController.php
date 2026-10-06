<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Submission;
use App\Models\Assignment;
use App\Http\Resources\SubmissionResource;
use Illuminate\Http\Request;

class SubmissionController extends Controller
{
    public function store(Request $request)
    {
        $user = $request->user();

        if ($user->role !== 'mahasiswa') {
            return response()->json(['message' => 'Forbidden. Hanya mahasiswa yang dapat mengumpulkan tugas.'], 403);
        }

        $validated = $request->validate([
            'assignment_id' => 'required|exists:assignments,id',
            'content' => 'required|string',
        ]);

        $submission = Submission::create([
            'assignment_id' => $validated['assignment_id'],
            'student_id' => $user->id,
            'content' => $validated['content'],
            'submitted_at' => now(),
        ]);

        return (new SubmissionResource($submission->load(['assignment', 'student'])))
            ->response()
            ->setStatusCode(201);
    }

    public function grade(Request $request, $id)
    {
        $user = $request->user();

        if ($user->role !== 'dosen') {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        $submission = Submission::with('assignment.course')->findOrFail($id);

        // Anti-IDOR (Cross-Dosen)
        if ($submission->assignment->course->lecturer_id !== $user->id) {
            return response()->json(['message' => 'Forbidden. Anda tidak dapat memberi nilai pada tugas ini.'], 403);
        }

        $validated = $request->validate([
            'grade' => 'required|numeric|min:0|max:100',
            'feedback' => 'nullable|string',
        ]);

        $submission->update([
            'grade' => $validated['grade'],
            'feedback' => $validated['feedback'] ?? null,
            'graded_at' => now(),
        ]);

        return new SubmissionResource($submission);
    }
}