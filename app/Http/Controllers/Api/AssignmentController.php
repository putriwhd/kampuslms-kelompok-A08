<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAssignmentRequest;
use App\Http\Requests\UpdateAssignmentRequest;
use App\Http\Resources\AssignmentResource;
use App\Http\Resources\SubmissionResource;
use App\Models\Assignment;
use App\Models\Course;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;

class AssignmentController extends Controller
{
    public function store(StoreAssignmentRequest $request)
    {
        $user = $request->user();
        $validated = $request->validated();

        $course = Course::findOrFail($validated['course_id']);
        Gate::authorize('create', [Assignment::class, $course]);

        $assignment = Assignment::create([
            ...$validated,
            'created_by' => $user->id,
            'max_score' => $validated['max_score'] ?? 100,
            'allow_late' => $validated['allow_late'] ?? true,
            'status' => $validated['status'] ?? 'published',
        ]);

        return (new AssignmentResource(
            $assignment->load([
                'course' => fn ($query) => $query
                    ->with('lecturer')
                    ->withCount(['materials', 'assignments']),
                'creator',
            ])
        ))
            ->response()
            ->setStatusCode(201);
    }

    public function update(UpdateAssignmentRequest $request, $id)
    {
        $assignment = Assignment::with('course')->findOrFail($id);
        Gate::authorize('update', $assignment);

        $assignment->update($request->validated());

        return new AssignmentResource(
            $assignment->load([
                'course' => fn ($query) => $query
                    ->with('lecturer')
                    ->withCount(['materials', 'assignments']),
                'creator',
            ])
        );
    }

    public function destroy(Request $request, $id)
    {
        $assignment = Assignment::with('course')->findOrFail($id);
        Gate::authorize('delete', $assignment);

        $disk = Storage::disk('local');

        $submissionFiles = $assignment->submissions()
            ->pluck('file_path')
            ->filter(
                fn ($path) => is_string($path) && $disk->exists($path)
            )
            ->all();

        if (
            $submissionFiles !== []
            && ! $disk->delete($submissionFiles)
        ) {
            return response()->json([
                'message' => 'Gagal menghapus berkas submission.',
            ], 500);
        }

        $assignment->delete();

        return response()->noContent();
    }

    public function submissions(Request $request, $id)
    {
        $assignment = Assignment::with('course')->findOrFail($id);
        Gate::authorize('viewSubmissions', $assignment);

        $submissions = $assignment->submissions()
            ->with([
                'assignment.course' => fn ($query) => $query
                    ->with('lecturer')
                    ->withCount(['materials', 'assignments']),
                'assignment.creator',
                'student',
                'grade.grader',
            ])
            ->paginate(15);

        return response()->json([
            'data' => SubmissionResource::collection($submissions)
                ->resolve($request),
            'meta' => [
                'current_page' => $submissions->currentPage(),
                'last_page' => $submissions->lastPage(),
                'total' => $submissions->total(),
            ],
        ]);
    }
}