<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ChecksCourseOwnership;
use App\Models\Assignment;
use App\Models\Course;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class AssignmentController extends Controller
{
    use ChecksCourseOwnership;

    public function index(Request $request, Course $course)
    {
        $this->ensureCourseOwnership($request, $course);
        Gate::authorize('view', $course);

        $role = $request->attributes->get(
            'selected_role',
            $request->query('as', 'mahasiswa')
        );

        $query = $course->assignments();

        // Mahasiswa hanya melihat tugas yang sudah dipublikasikan.
        if ($request->user()?->role === 'mahasiswa') {
            $query->where('status', 'published');
        }

        $assignments = $query
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view(
            'assignments.index',
            compact('course', 'assignments', 'role')
        );
    }

    public function show(Request $request, Assignment $assignment)
    {
        $assignment->load('course');

        $this->ensureCourseOwnership($request, $assignment->course);
        Gate::authorize('view', $assignment);

        // Mahasiswa tidak boleh membuka tugas yang belum dipublikasikan.
        if (
            $request->user()?->role === 'mahasiswa'
            && $assignment->status !== 'published'
        ) {
            abort(403);
        }

        $role = $request->attributes->get(
            'selected_role',
            $request->query('as', 'mahasiswa')
        );

        return view('assignments.show', compact('assignment', 'role'));
    }

    public function showInCourse(
        Request $request,
        Course $course,
        Assignment $assignment
    ) {
        abort_unless($assignment->course_id === $course->id, 404);

        $this->ensureCourseOwnership($request, $course);
        Gate::authorize('view', $assignment);

        if (
            $request->user()?->role === 'mahasiswa'
            && $assignment->status !== 'published'
        ) {
            abort(403);
        }

        $role = $request->attributes->get(
            'selected_role',
            $request->query('as', 'mahasiswa')
        );

        $assignment->load('course');

        return view('assignments.show', compact('assignment', 'role'));
    }
}
