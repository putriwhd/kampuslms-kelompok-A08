<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\AssignmentResource;
use App\Http\Resources\CourseResource;
use App\Http\Resources\MaterialResource;
use App\Models\Course;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class CourseController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        Gate::authorize('viewAny', Course::class);

        $query = match ($user->role) {
            'admin' => Course::query(),
            'dosen' => $user->taughtCourses(),
            'mahasiswa' => $user->courses()->where('courses.status', '!=', 'draft'),
            default => abort(403),
        };
        $assignmentCount = $user->role === 'mahasiswa'
            ? fn ($query) => $query->where('status', 'published')
            : fn ($query) => $query;

        $courses = $query
            ->with('lecturer')
            ->withCount([
                'materials',
                'assignments' => $assignmentCount,
            ])
            ->paginate(15);

        return response()->json([
            'data' => CourseResource::collection($courses)->resolve($request),
            'meta' => [
                'current_page' => $courses->currentPage(),
                'last_page'    => $courses->lastPage(),
                'total'        => $courses->total(),
            ],
        ]);
    }
    
    public function show(Request $request, $id)
    {
        $user = $request->user();
        $assignmentCount = $user->role === 'mahasiswa'
            ? fn ($query) => $query->where('status', 'published')
            : fn ($query) => $query;
        $course = Course::with([
            'lecturer',
            'materials.uploader',
            'assignments' => function ($query) use ($user) {
                if ($user->role === 'mahasiswa') {
                    $query->where('status', 'published');
                }
                $query->with('creator');
            },
        ])
            ->withCount([
                'materials',
                'assignments' => $assignmentCount,
            ])
            ->findOrFail($id);

        if ($user->role === 'mahasiswa' && $course->status === 'draft') {
            abort(404);
        }

        Gate::authorize('view', $course);

        return new CourseResource($course);
    }

    // GET /api/v1/courses/{id}/materials
    public function materials(Request $request, $id)
    {
        $user = $request->user();
        $assignmentCount = $user->role === 'mahasiswa'
            ? fn ($query) => $query->where('status', 'published')
            : fn ($query) => $query;
        $course = Course::findOrFail($id);
        Gate::authorize('view', $course);

        $materials = $course->materials()
            ->with([
                'course' => function ($query) use ($assignmentCount) {
                    $query->with('lecturer')
                        ->withCount([
                            'materials',
                            'assignments' => $assignmentCount,
                        ]);
                },
                'uploader',
            ])
            ->paginate(10);

        return response()->json([
            'data' => MaterialResource::collection($materials)->resolve($request),
            'meta' => [
                'current_page' => $materials->currentPage(),
                'last_page'    => $materials->lastPage(),
                'total'        => $materials->total(),
            ],
        ]);
    }

    // GET /api/v1/courses/{id}/assignments
    public function assignments(Request $request, $id)
    {
        $user = $request->user();
        $assignmentCount = $user->role === 'mahasiswa'
            ? fn ($query) => $query->where('status', 'published')
            : fn ($query) => $query;
        $course = Course::findOrFail($id);

        $filters = $request->validate([
            'status' => ['sometimes', 'in:draft,published'],
        ]);

        Gate::authorize('view', $course);

        $query = $course->assignments()
            ->with([
                'course' => function ($query) use ($assignmentCount) {
                    $query->with('lecturer')
                        ->withCount([
                            'materials',
                            'assignments' => $assignmentCount,
                        ]);
                },
                'creator',
            ]);

        if ($user->role === 'mahasiswa') {
            $query->where('status', 'published');
        } elseif (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        $assignments = $query->paginate(10);

        return response()->json([
            'data' => AssignmentResource::collection($assignments)->resolve($request),
            'meta' => [
                'current_page' => $assignments->currentPage(),
                'last_page'    => $assignments->lastPage(),
                'total'        => $assignments->total(),
            ],
        ]);
    }
}