<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\AssignmentResource;
use App\Http\Resources\SubmissionResource;
use App\Models\Assignment;
use App\Models\Course;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class AssignmentController extends Controller
{
    public function store(Request $request)
    {
        $user = $request->user();

        // Hak akses: Hanya dosen
        if ($user->role !== 'dosen') {
            return response()->json(['message' => 'Anda tidak memiliki akses ke sumber daya ini.'], 403);
        }

        $validated = $request->validate([
            'course_id'    => ['required', 'exists:courses,id'],
            'title'        => ['required', 'string', 'max:255'],
            'instructions' => ['required', 'string'],
            'due_at'       => ['required', 'date'],
            'max_score'    => ['sometimes', 'integer', 'min:0', 'max:255'],
            'allow_late'   => ['sometimes', 'boolean'],
            'status'       => ['sometimes', Rule::in(['draft', 'published'])],
        ]);

        // Anti-IDOR: Dosen hanya bisa buat assignment di course miliknya
        $course = Course::with('lecturer')->findOrFail($validated['course_id']);
        if ($course->lecturer_id !== $user->id) {
            return response()->json(['message' => 'Anda tidak memiliki akses ke sumber daya ini.'], 403);
        }

        $assignment = Assignment::create([
            ...$validated,
            'created_by' => $user->id,
            'status'     => $validated['status'] ?? 'published',
        ]);

        return (new AssignmentResource($assignment->load([
            'course' => fn ($query) => $query
                ->with('lecturer')
                ->withCount(['materials', 'assignments']),
            'creator',
        ])))
            ->response()
            ->setStatusCode(201);
    }

    public function update(Request $request, $id)
    {
        $user = $request->user();

        if ($user->role !== 'dosen') {
            return response()->json(['message' => 'Anda tidak memiliki akses ke sumber daya ini.'], 403);
        }

        $assignment = Assignment::with('course')->findOrFail($id);

        // Anti-IDOR (Cross-Dosen)
        if ($assignment->course->lecturer_id !== $user->id) {
            return response()->json(['message' => 'Anda tidak memiliki akses ke sumber daya ini.'], 403);
        }

        $validated = $request->validate([
            'title'        => ['sometimes', 'required', 'string', 'max:255'],
            'instructions' => ['sometimes', 'required', 'string'],
            'due_at'       => ['sometimes', 'required', 'date'],
            'max_score'    => ['sometimes', 'integer', 'min:0', 'max:255'],
            'allow_late'   => ['sometimes', 'boolean'],
            'status'       => ['sometimes', Rule::in(['draft', 'published'])],
        ]);

        $assignment->update($validated);

        return new AssignmentResource($assignment->load([
            'course' => fn ($query) => $query
                ->with('lecturer')
                ->withCount(['materials', 'assignments']),
            'creator',
        ]));
    }

    public function destroy(Request $request, $id)
    {
        $user = $request->user();

        if ($user->role !== 'dosen') {
            return response()->json(['message' => 'Anda tidak memiliki akses ke sumber daya ini.'], 403);
        }

        $assignment = Assignment::with('course')->findOrFail($id);

        // Anti-IDOR (Cross-Dosen)
        if ($assignment->course->lecturer_id !== $user->id) {
            return response()->json(['message' => 'Anda tidak memiliki akses ke sumber daya ini.'], 403);
        }

        $disk = Storage::disk('local');
        $submissionFiles = $assignment->submissions()
            ->pluck('file_path')
            ->filter(fn (string $path) => $disk->exists($path))
            ->all();

        if ($submissionFiles !== [] && ! $disk->delete($submissionFiles)) {
            return response()->json(['message' => 'Gagal menghapus berkas submission.'], 500);
        }

        $assignment->delete();

        return response()->noContent();
    }

    // --- METHOD BARU: GET /api/v1/assignments/{id}/submissions ---
    public function submissions(Request $request, $id)
    {
        $user = $request->user();

        if ($user->role !== 'dosen') {
            return response()->json(['message' => 'Anda tidak memiliki akses ke sumber daya ini.'], 403);
        }

        $assignment = Assignment::with('course')->findOrFail($id);

        // Anti-IDOR (Dosen pengampu course)
        if ($assignment->course->lecturer_id !== $user->id) {
            return response()->json(['message' => 'Anda tidak memiliki akses ke sumber daya ini.'], 403);
        }

        $submissions = $assignment->submissions()
            ->with(['student', 'grade.grader'])
            ->paginate(15);

      return response()->json([
    'data' => CourseResource::collection($courses)->resolve($request),
    'meta' => [
        'current_page' => $courses->currentPage(),
        'last_page'    => $courses->lastPage(),
        'per_page'     => $courses->perPage(), // <-- Tambahkan baris ini
        'total'        => $courses->total(),
    ],
]);
    }
}