<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Http\Resources\CourseResource;
use Illuminate\Http\Request;

class CourseController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        if ($user->role === 'dosen') {
            $courses = Course::with('lecturer')->where('lecturer_id', $user->id)->get();
        } else {
            $courses = Course::with('lecturer')->where('status', 'active')->get();
        }

        return CourseResource::collection($courses);
    }

    public function show($id)
    {
        $course = Course::with(['lecturer', 'materials', 'assignments'])->findOrFail($id);
        return new CourseResource($course);
    }
}