<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CourseResource;
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
            $courses = Course::with('lecturer')
                ->withCount(['materials', 'assignments'])
                ->where('status', 'active')
                ->paginate(15);
        }

        return CourseResource::collection($courses);
    }

    public function show(Request $request, $id)
    {
        $user = $request->user();
        $course = Course::with(['lecturer', 'materials', 'assignments'])
            ->withCount(['materials', 'assignments'])
            ->findOrFail($id);

        if ($user->role === 'dosen' && $course->lecturer_id !== $user->id) {
            return response()->json(['message' => 'Forbidden. Anda bukan pengampu mata kuliah ini.'], 403);
        }

        if ($user->role !== 'dosen' && $course->status !== 'active') {
            return response()->json(['message' => 'Not found.'], 404);
        }

        return new CourseResource($course);
    }
}
