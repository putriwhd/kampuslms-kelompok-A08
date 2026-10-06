<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Assignment;
use App\Models\Course;
use App\Http\Resources\AssignmentResource;
use Illuminate\Http\Request;

class AssignmentController extends Controller
{
    public function store(Request $request)
    {
        $user = $request->user();

        // Hak akses: Hanya dosen
        if ($user->role !== 'dosen') {
            return response()->json(['message' => 'Forbidden. Hanya dosen yang dapat membuat tugas.'], 403);
        }

        $validated = $request->validate([
            'course_id' => 'required|exists:courses,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'due_date' => 'required|date',
        ]);

        // Anti-IDOR: Dosen hanya bisa buat assignment di course miliknya
        $course = Course::findOrFail($validated['course_id']);
        if ($course->lecturer_id !== $user->id) {
            return response()->json(['message' => 'Forbidden. Anda bukan pengampu mata kuliah ini.'], 403);
        }

        $assignment = Assignment::create($validated);

        return (new AssignmentResource($assignment->load('course')))
            ->response()
            ->setStatusCode(201);
    }

    public function update(Request $request, $id)
    {
        $user = $request->user();

        if ($user->role !== 'dosen') {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        $assignment = Assignment::with('course')->findOrFail($id);

        // Anti-IDOR (Cross-Dosen)
        if ($assignment->course->lecturer_id !== $user->id) {
            return response()->json(['message' => 'Forbidden. Anda tidak memiliki akses ke tugas ini.'], 403);
        }

        $validated = $request->validate([
            'title' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'due_date' => 'sometimes|date',
        ]);

        $assignment->update($validated);

        return new AssignmentResource($assignment);
    }

    public function destroy(Request $request, $id)
    {
        $user = $request->user();

        if ($user->role !== 'dosen') {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        $assignment = Assignment::with('course')->findOrFail($id);

        // Anti-IDOR (Cross-Dosen)
        if ($assignment->course->lecturer_id !== $user->id) {
            return response()->json(['message' => 'Forbidden. Anda tidak memiliki akses ke tugas ini.'], 403);
        }

        $assignment->delete();

        return response()->json(null, 204);
    }
}