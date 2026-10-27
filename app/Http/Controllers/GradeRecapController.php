<?php

namespace App\Http\Controllers;

use App\Models\Course;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GradeRecapController extends Controller
{
    public function lecturer(Request $request): JsonResponse
    {
        $courses = $request->user()
            ->taughtCourses()
            ->with([
                'students',
                'assignments.submissions.student',
                'assignments.submissions.grade',
            ])
            ->get()
            ->map(function (Course $course) {
                $assignments = $course->assignments;

                $students = $course->students->map(function ($student) use ($assignments) {
                    $grades = $assignments->map(function ($assignment) use ($student) {
                        $submission = $assignment->submissions
                            ->firstWhere('user_id', $student->getKey());

                        return [
                            'assignment_id' => $assignment->getKey(),
                            'assignment_title' => $assignment->title,
                            'score' => $submission?->grade?->score,
                            'graded_at' => $submission?->grade?->graded_at,
                        ];
                    });
                    $scoredGrades = $grades->whereNotNull('score');

                    return [
                        'student_id' => $student->getKey(),
                        'student_name' => $student->name,
                        'grades' => $grades->values(),
                        'average_score' => $scoredGrades->isNotEmpty()
                            ? round((float) $scoredGrades->avg('score'), 2)
                            : null,
                    ];
                });

                return [
                    'course_id' => $course->getKey(),
                    'course_code' => $course->code,
                    'course_name' => $course->name,
                    'students' => $students->values(),
                ];
            });

        return response()->json(['data' => $courses->values()]);
    }

    public function student(Request $request): JsonResponse
    {
        $courses = $request->user()
            ->courses()
            ->where('courses.status', '!=', 'draft')
            ->with([
                'assignments' => fn ($query) => $query
                    ->where('status', 'published')
                    ->with([
                        'submissions' => fn ($submissions) => $submissions
                            ->where('user_id', $request->user()->getKey())
                            ->with('grade'),
                    ]),
            ])
            ->get()
            ->map(function (Course $course) {
                return [
                    'course_id' => $course->getKey(),
                    'course_code' => $course->code,
                    'course_name' => $course->name,
                    'assignments' => $course->assignments->map(function ($assignment) {
                        $submission = $assignment->submissions->first();

                        return [
                            'assignment_id' => $assignment->getKey(),
                            'assignment_title' => $assignment->title,
                            'max_score' => $assignment->max_score,
                            'due_at' => $assignment->due_at,
                            'submitted_at' => $submission?->submitted_at,
                            'score' => $submission?->grade?->score,
                            'feedback' => $submission?->grade?->feedback,
                            'graded_at' => $submission?->grade?->graded_at,
                        ];
                    })->values(),
                ];
            });

        return response()->json(['data' => $courses->values()]);
    }
}
