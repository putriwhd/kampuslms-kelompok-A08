<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAssignmentRequest;
use App\Http\Requests\UpdateAssignmentRequest;
use App\Models\Assignment;
use App\Models\Course;
use App\Models\Grade;
use App\Models\Submission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;

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
            ->route($role.'.courses.assignments.index', [
                'course' => $course,
                'as' => $role,
            ])
            ->with('success', 'Tugas berhasil ditambahkan.');
    }

    public function show(Request $request, Assignment $assignment)
    {
        $assignment->load('course');

        return $this->showAssignment($request, $assignment);
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
            ->route($role.'.courses.assignments.index', [
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
            ->route($role.'.courses.assignments.index', [
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

        $assignment->load('course');

        return $this->showAssignment($request, $assignment);
    }

    public function gradeSubmission(
        Request $request,
        Course $course,
        Assignment $assignment,
        Submission $submission
    ) {
        abort_unless(
            $assignment->course_id === $course->getKey()
                && $submission->assignment_id === $assignment->getKey(),
            404
        );
        Gate::authorize('grade', $submission);

        $validated = $request->validate([
            'score' => ['required', 'numeric', 'min:0', 'max:'.$assignment->max_score],
            'feedback' => ['nullable', 'string', 'max:5000'],
        ]);

        Grade::updateOrCreate(
            ['submission_id' => $submission->getKey()],
            [
                'graded_by' => $request->user()->getKey(),
                'score' => $validated['score'],
                'feedback' => $validated['feedback'] ?? null,
                'graded_at' => now(),
            ]
        );

        return redirect()
            ->route('dosen.assignments.show', $assignment)
            ->with('success', 'Nilai submission berhasil disimpan.');
    }

    public function downloadSubmission(
        Request $request,
        Course $course,
        Assignment $assignment,
        Submission $submission
    ) {
        abort_unless(
            $assignment->course_id === $course->getKey()
                && $submission->assignment_id === $assignment->getKey(),
            404
        );
        Gate::authorize('view', $submission);

        $disk = Storage::disk('local');
        abort_unless($disk->exists($submission->file_path), 404);

        return $disk->download(
            $submission->file_path,
            $submission->original_name
        );
    }

    private function showAssignment(Request $request, Assignment $assignment)
    {
        $user = $request->user();
        Gate::authorize('view', $assignment);

        $role = $user->role;
        $submissions = collect();
        $ownSubmission = null;

        if (in_array($role, ['admin', 'dosen'], true)) {
            Gate::authorize('viewSubmissions', $assignment);
            $submissions = $assignment->submissions()
                ->with(['student', 'grade.grader'])
                ->orderBy('submitted_at')
                ->get();
        } elseif ($role === 'mahasiswa') {
            $ownSubmission = $assignment->submissions()
                ->where('user_id', $user->getKey())
                ->with('grade.grader')
                ->first();
        }

        return view(
            'assignments.show',
            compact('assignment', 'role', 'submissions', 'ownSubmission')
        );
    }
}
