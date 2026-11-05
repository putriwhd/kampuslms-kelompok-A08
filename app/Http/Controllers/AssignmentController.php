<?php

namespace App\Http\Controllers;

use App\Events\AssignmentPublished;
use App\Events\SubmissionGraded;
use App\Http\Controllers\Api\SubmissionController as ApiSubmissionController;
use App\Http\Requests\StoreAssignmentRequest;
use App\Http\Requests\StoreSubmissionRequest;
use App\Http\Requests\UpdateAssignmentRequest;
use App\Models\Assignment;
use App\Models\Course;
use App\Models\Grade;
use App\Models\Submission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

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
            ->with('course')
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

        $assignment = $course->assignments()->create([
            ...$validated,
            'created_by' => $request->user()->getKey(),
            'status' => $validated['status'] ?? 'draft',
        ]);

        if ($assignment->status === 'published') {
            AssignmentPublished::dispatch($assignment);
        }

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
        $assignment->load([
            'course',
        ]);

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
        $wasPublished = $assignment->status === 'published';
        $assignment->update($request->validated());

        if (! $wasPublished && $assignment->status === 'published') {
            AssignmentPublished::dispatch($assignment);
        }

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
        $assignment->submissions->each->delete();
        $assignment->delete();

        return redirect()
            ->route($role.'.courses.assignments.index', [
                'course' => $course,
                'as' => $role,
            ])
            ->with('success', 'Tugas berhasil dihapus.');
    }

    /**
     * Menangani pengumpulan tugas mahasiswa via form HTML web (bukan API JSON).
     * Bridge ke logic saveSubmission milik SubmissionController API.
     */
    public function submitWeb(StoreSubmissionRequest $request, ...$params)
    {
        $assignment = null;
        foreach ($params as $param) {
            if ($param instanceof Assignment) {
                $assignment = $param;
            }
        }
        if (! $assignment) {
            $last = end($params);
            $assignment = $last instanceof Assignment ? $last : Assignment::findOrFail($last);
        }

        $assignment->load('course');

        $alreadySubmitted = $assignment->submissions()
            ->where('user_id', $request->user()->getKey())
            ->exists();

        if ($alreadySubmitted) {
            return back()
                ->withErrors(['file' => 'Anda sudah mengumpulkan tugas ini dan tidak dapat mengumpulkan ulang.'])
                ->withInput();
        }

        try {
            (new ApiSubmissionController)->saveSubmission($request, $assignment);
        } catch (ValidationException $e) {
            return back()
                ->withErrors($e->errors())
                ->withInput();
        }

        return redirect()
            ->route('mahasiswa.courses.assignments.scoped-show', [
                'course' => $assignment->course,
                'assignment' => $assignment,
            ])
            ->with('success', 'Tugas berhasil dikumpulkan.');
    }

    public function showInCourse(
        Request $request,
        Course $course,
        Assignment $assignment
    ) {
        abort_unless($assignment->course_id === $course->id, 404);

        Gate::authorize('view', $assignment);

        $assignment->load([
            'course',
        ]);

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
        SubmissionGraded::dispatch($submission);

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
        $submissionRecap = collect();
        $ownSubmission = null;
        $submissionTotal = 0;
        $submittedTotal = 0;

        if (in_array($role, ['admin', 'dosen'], true)) {
            Gate::authorize('viewSubmissions', $assignment);
            $submissionRecap = $assignment->course
                ->students()
                ->with([
                    'submissions' => fn ($query) => $query
                        ->where('assignment_id', $assignment->getKey())
                        ->with('grade'),
                ])
                ->orderBy('users.name')
                ->select([
                    'users.*',
                    DB::raw(
                        '(SELECT COUNT(*) FROM submissions WHERE submissions.assignment_id = '
                        .$assignment->getKey().') AS assignment_submitted_count'
                    ),
                ])
                ->paginate(15, ['users.*'], 'submission_recap_page')
                ->withQueryString()
                ->through(function ($student) use ($assignment) {
                    $submission = $student->submissions->first();

                    return [
                        'student' => $student,
                        'student_id' => $student->getKey(),
                        'student_name' => $student->name,
                        'student_nim_nip' => $student->nim_nip,
                        'submitted_total' => (int) ($student->assignment_submitted_count ?? 0),
                        'submission_status' => $submission ? 'submitted' : 'not_submitted',
                        'submitted_at' => $submission?->submitted_at,
                        'is_late' => (bool) ($submission?->is_late ?? false),
                        'submission' => $submission,
                        'original_name' => $submission?->original_name,
                        'file_size' => $submission?->file_size,
                        'formatted_file_size' => $submission?->file_size !== null
                            ? ($submission->file_size >= 1048576
                                ? number_format($submission->file_size / 1048576, 1).' MB'
                                : number_format($submission->file_size / 1024, 1).' KB')
                            : null,
                    ];
                });

            $submissions = $submissionRecap->getCollection()
                ->pluck('submission')
                ->filter()
                ->values();
            $submissionTotal = $submissionRecap->total();
            $submittedTotal = (int) (
                $submissionRecap->getCollection()->first()['submitted_total'] ?? 0
            );
        } elseif ($role === 'mahasiswa') {
            $ownSubmission = $assignment->submissions()
                ->where('user_id', $user->getKey())
                ->with('grade.grader')
                ->first();
        }

        return view(
            'assignments.show',
            compact(
                'assignment',
                'role',
                'submissions',
                'submissionRecap',
                'ownSubmission',
                'submissionTotal',
                'submittedTotal'
            )
        );
    }

    public function submissionsRecap(
        Request $request,
        Course $course,
        Assignment $assignment
    ) {
        abort_unless($assignment->course_id === $course->getKey(), 404);
        $assignment->setRelation('course', $course);
        Gate::authorize('viewSubmissions', $assignment);

        $role = $request->user()->role;
        $assignment->load([
            'course',
        ]);
        $submissionRecap = $assignment->course
            ->students()
            ->with([
                'submissions' => fn ($query) => $query
                    ->where('assignment_id', $assignment->getKey())
                    ->select([
                        'id',
                        'assignment_id',
                        'user_id',
                        'file_path',
                        'original_name',
                        'file_size',
                        'submitted_at',
                        'is_late',
                    ]),
            ])
            ->orderBy('users.name')
            ->paginate(15, ['users.*'], 'submission_recap_page')
            ->withQueryString()
            ->through(function ($student) {
                $submission = $student->submissions->first();

                return [
                    'student' => $student,
                    'student_id' => $student->getKey(),
                    'student_name' => $student->name,
                    'student_nim_nip' => $student->nim_nip,
                    'submission_status' => $submission ? 'submitted' : 'not_submitted',
                    'submitted_at' => $submission?->submitted_at,
                    'is_late' => (bool) ($submission?->is_late ?? false),
                    'submission' => $submission,
                    'original_name' => $submission?->original_name,
                    'file_size' => $submission?->file_size,
                    'formatted_file_size' => $submission?->file_size !== null
                        ? ($submission->file_size >= 1048576
                            ? number_format($submission->file_size / 1048576, 1).' MB'
                            : number_format($submission->file_size / 1024, 1).' KB')
                        : null,
                ];
            });

        $submissions = $submissionRecap->getCollection()
            ->pluck('submission')
            ->filter()
            ->values();
        $totalStudents = $submissionRecap->total();
        $submissionTotals = DB::table('submissions')
            ->where('assignment_id', $assignment->getKey())
            ->selectRaw('COUNT(*) AS total_submitted')
            ->selectRaw('SUM(CASE WHEN is_late = 1 THEN 1 ELSE 0 END) AS total_late')
            ->first();
        $totalSubmitted = (int) ($submissionTotals->total_submitted ?? 0);
        $totalLate = (int) ($submissionTotals->total_late ?? 0);

        return view(
            'assignments.submissions',
            compact('assignment', 'course', 'role', 'submissions', 'submissionRecap', 'totalStudents', 'totalSubmitted', 'totalLate')
        );
    }

    public function submissionsRecapShallow(Request $request, Assignment $assignment)
    {
        $assignment->load('course');

        return $this->submissionsRecap($request, $assignment->course, $assignment);
    }
}
