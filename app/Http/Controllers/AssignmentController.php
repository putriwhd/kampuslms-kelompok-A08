<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ChecksCourseOwnership;
use App\Models\Assignment;
use App\Models\Course;
use Illuminate\Http\Request;

class AssignmentController extends Controller
{
    use ChecksCourseOwnership;

    public function index(Request $request, Course $course)
    {
        $this->ensureCourseOwnership($request, $course);
        $role = $request->attributes->get('selected_role', $request->query('as', 'mahasiswa'));
        $assignments = $course->assignments()->latest()->paginate(15)->withQueryString();

        return view('assignments.index', compact('course', 'assignments', 'role'));
    }

    public function show(Request $request, Assignment $assignment)
    {
        $assignment->load('course');
        $this->ensureCourseOwnership($request, $assignment->course);
        $role = $request->attributes->get('selected_role', $request->query('as', 'mahasiswa'));

        return view('assignments.show', compact('assignment', 'role'));
    }

    public function showInCourse(Request $request, Course $course, Assignment $assignment)
    {
        abort_unless($assignment->course_id === $course->id, 403);
        $this->ensureCourseOwnership($request, $course);

        $role = $request->attributes->get('selected_role', $request->query('as', 'mahasiswa'));
        $assignment->load('course');

        return view('assignments.show', compact('assignment', 'role'));
    }
}
