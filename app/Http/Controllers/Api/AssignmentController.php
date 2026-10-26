<?php

namespace App\Http\Controllers\Api;

use App\Events\AssignmentPublished;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAssignmentRequest;
use App\Http\Requests\UpdateAssignmentRequest;
use App\Http\Resources\AssignmentResource;
use App\Http\Resources\SubmissionResource;
use App\Models\Assignment;
use App\Models\Course;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

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

        if ($assignment->status === 'published') {
            AssignmentPublished::dispatch($assignment);
        }

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

        $wasPublished = $assignment->status === 'published';
        $assignment->update($request->validated());

        if (! $wasPublished && $assignment->status === 'published') {
            AssignmentPublished::dispatch($assignment);
        }

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

        $assignment->submissions->each->delete();
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

        $recap = $assignment->course
            ->students()
            ->with([
                'submissions' => fn ($query) => $query
                    ->where('assignment_id', $assignment->getKey()),
            ])
            ->orderBy('users.name')
            ->get()
            ->map(function ($student) {
                $submission = $student->submissions->first();

                return [
                    'student_id' => $student->getKey(),
                    'student_name' => $student->name,
                    'submission_status' => $submission ? 'submitted' : 'not_submitted',
                    'submitted_at' => $submission?->submitted_at,
                    'is_late' => (bool) ($submission?->is_late ?? false),
                ];
            });

        return response()->json([
            'data' => SubmissionResource::collection($submissions)
                ->resolve($request),
            'recap' => $recap,
            'meta' => [
                'current_page' => $submissions->currentPage(),
                'last_page' => $submissions->lastPage(),
                'total' => $submissions->total(),
            ],
        ]);
    }

    public function recap($id)
    {
        $assignment = Assignment::with('course')->findOrFail($id);
        Gate::authorize('viewSubmissions', $assignment);

        $recap = $assignment->course
            ->students()
            ->with([
                'submissions' => fn ($query) => $query
                    ->where('assignment_id', $assignment->getKey()),
            ])
            ->orderBy('users.name')
            ->get()
            ->map(function ($student) {
                $submission = $student->submissions->first();

                return [
                    'student_id' => $student->getKey(),
                    'student_name' => $student->name,
                    'submission_status' => $submission ? 'submitted' : 'not_submitted',
                    'submitted_at' => $submission?->submitted_at,
                    'is_late' => (bool) ($submission?->is_late ?? false),
                ];
            });

        return response()->json([
            'data' => $recap,
        ]);
    }
}
