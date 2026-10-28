<?php

namespace App\Http\Controllers;

use App\Models\Course;
use Illuminate\Http\Request;
use Illuminate\View\View;

class GradeRecapPageController extends Controller
{
    public function lecturer(Request $request): View
    {
        $courses = $request->user()
            ->taughtCourses()
            ->with([
                'students',
                'assignments.submissions.student',
                'assignments.submissions.grade',
            ])
            ->get()
            ->map(function (Course $course): array {
                $assignments = $course->assignments;
                $students = $course->students->map(function ($student) use ($assignments): array {
                    $grades = $assignments->map(function ($assignment) use ($student): array {
                        $submission = $assignment->submissions
                            ->firstWhere('user_id', $student->getKey());

                        return [
                            'assignment_id' => $assignment->getKey(),
                            'score' => $submission?->grade?->score,
                        ];
                    });
                    $scoredGrades = $grades->whereNotNull('score');

                    return [
                        'student_id' => $student->getKey(),
                        'student_name' => $student->name,
                        'grades' => $grades->keyBy('assignment_id'),
                        'average_score' => $scoredGrades->isNotEmpty()
                            ? round((float) $scoredGrades->avg('score'), 2)
                            : null,
                    ];
                });

                return [
                    'course_code' => $course->code,
                    'course_name' => $course->name,
                    'assignments' => $assignments->map(fn ($assignment): array => [
                        'id' => $assignment->getKey(),
                        'title' => $assignment->title,
                    ])->all(),
                    'students' => $students->all(),
                ];
            });

        return view('grade-recap.lecturer', compact('courses'));
    }

    public function student(Request $request): View
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
            ->map(fn (Course $course): array => [
                'course_code' => $course->code,
                'course_name' => $course->name,
                'assignments' => $course->assignments->map(function ($assignment): array {
                    $submission = $assignment->submissions->first();

                    return [
                        'title' => $assignment->title,
                        'max_score' => $assignment->max_score,
                        'submitted_at' => $submission?->submitted_at,
                        'score' => $submission?->grade?->score,
                        'feedback' => $submission?->grade?->feedback,
                        'graded_at' => $submission?->grade?->graded_at,
                    ];
                })->all(),
            ]);

        return view('grade-recap.student', compact('courses'));
    }
}
