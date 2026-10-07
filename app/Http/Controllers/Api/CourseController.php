<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\AssignmentResource;
use App\Http\Resources\CourseResource;
use App\Http\Resources\MaterialResource;
use App\Models\Course;
use Illuminate\Http\Request;

class CourseController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        if ($user->role === 'dosen') {
            $courses = Course::with('lecturer')
                ->withCount(['materials', 'assignments'])
                ->where('lecturer_id', $user->id)
                ->paginate(15);
        } else {
            // Mahasiswa: Hanya menampilkan course aktif yang diikuti oleh mahasiswa tersebut
            $courses = Course::with('lecturer')
                ->withCount(['materials', 'assignments'])
                ->where('status', 'active')
                ->whereHas('students', function ($query) use ($user) {
                    $query->where('users.id', $user->id);
                })
                ->paginate(15);
        }

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
        $course = Course::with(['lecturer', 'materials', 'assignments'])
            ->withCount(['materials', 'assignments'])
            ->findOrFail($id);

        if ($user->role === 'dosen' && $course->lecturer_id !== $user->id) {
            return response()->json(['message' => 'Anda tidak memiliki akses ke sumber daya ini.'], 403);
        }

        if ($user->role !== 'dosen' && $course->status !== 'active') {
            return response()->json(['message' => 'Not found.'], 404);
        }

        return new CourseResource($course);
    }

    // GET /api/v1/courses/{id}/materials
    public function materials(Request $request, $id)
    {
        $user = $request->user();
        $course = Course::findOrFail($id);

        // Otorisasi Akses Course
        if ($user->role === 'dosen' && $course->lecturer_id !== $user->id) {
            return response()->json(['message' => 'Anda tidak memiliki akses ke sumber daya ini.'], 403);
        }

        if ($user->role === 'mahasiswa' && ! $course->students()->whereKey($user->id)->exists()) {
            return response()->json(['message' => 'Anda tidak memiliki akses ke sumber daya ini.'], 403);
        }

        $materials = $course->materials()
            ->with([
                'course' => fn ($query) => $query
                    ->with('lecturer')
                    ->withCount(['materials', 'assignments']),
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
        $course = Course::findOrFail($id);

        // Otorisasi Akses Course
        if ($user->role === 'dosen' && $course->lecturer_id !== $user->id) {
            return response()->json(['message' => 'Anda tidak memiliki akses ke sumber daya ini.'], 403);
        }

        if ($user->role === 'mahasiswa' && ! $course->students()->whereKey($user->id)->exists()) {
            return response()->json(['message' => 'Anda tidak memiliki akses ke sumber daya ini.'], 403);
        }

        $assignments = $course->assignments()
            ->with([
                'course' => fn ($query) => $query
                    ->with('lecturer')
                    ->withCount(['materials', 'assignments']),
                'creator',
                'submissions.student',
                'submissions.grade.grader',
            ])
            ->paginate(10);

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