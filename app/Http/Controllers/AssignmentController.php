<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAssignmentRequest;
use App\Http\Requests\UpdateAssignmentRequest;
use App\Models\Assignment;
use App\Models\Course;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class AssignmentController extends Controller
{
    public function index(Request $request, Course $course)
    {
        Gate::authorize('view', $course);
        Gate::authorize('viewAny', Assignment::class);

        $role = $request->user()->role;

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

    public function create(Request $request, Course $course)
    {
        Gate::authorize('create', [Assignment::class, $course]);

        $role = $request->user()->role;

        return view('assignments.create', compact('course', 'role'));
    }

    public function store(StoreAssignmentRequest $request, Course $course)
    {
        Gate::authorize('create', [Assignment::class, $course]);

        $validated = $request->validated();
        unset($validated['course_id']);

        $course->assignments()->create([
            ...$validated,
            'created_by' => $request->user()->getKey(),
            'status' => $validated['status'] ?? 'draft',
        ]);

        $role = $request->user()->role;

        return redirect()
            ->route($role . '.courses.assignments.index', [
                'course' => $course,
                'as' => $role,
            ])
            ->with('success', 'Tugas berhasil ditambahkan.');
    }

    public function show(Request $request, Assignment $assignment)
    {
        $assignment->load('course');

        Gate::authorize('view', $assignment);

        $role = $request->user()->role;

        return view('assignments.show', compact('assignment', 'role'));
    }

    public function edit(Request $request, Assignment $assignment)
    {
        $assignment->load('course');
        Gate::authorize('update', $assignment);

        $role = $request->user()->role;

        return view('assignments.edit', compact('assignment', 'role'));
    }

    public function update(
        UpdateAssignmentRequest $request,
        Assignment $assignment
    ) {
        Gate::authorize('update', $assignment);
        $assignment->update($request->validated());

        $role = $request->user()->role;

        return redirect()
            ->route($role . '.courses.assignments.index', [
                'course' => $assignment->course,
                'as' => $role,
            ])
            ->with('success', 'Tugas berhasil diperbarui.');
    }

    public function destroy(Request $request, Assignment $assignment)
    {
        Gate::authorize('delete', $assignment);

        $course = $assignment->course;
        $role = $request->user()->role;
        $assignment->delete();

        return redirect()
            ->route($role . '.courses.assignments.index', [
                'course' => $course,
                'as' => $role,
            ])
            ->with('success', 'Tugas berhasil dihapus.');
    }

    public function showInCourse(
        Request $request,
        Course $course,
        Assignment $assignment
    ) {
        abort_unless($assignment->course_id === $course->id, 404);

        Gate::authorize('view', $assignment);

        $role = $request->user()->role;

        $assignment->load('course');

        return view('assignments.show', compact('assignment', 'role'));
    }
}
