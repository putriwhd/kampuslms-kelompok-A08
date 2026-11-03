<?php

namespace App\Http\Controllers;

use App\Http\Controllers\DashboardController;
use App\Http\Requests\StoreCourseRequest;
use App\Http\Requests\UpdateCourseRequest;
use App\Models\Course;
use App\Models\EnrollmentRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class CourseController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        Gate::authorize('viewAny', Course::class);

        $role = $this->selectedRole($request);

        $search = $request->query('search');
        $search = is_string($search) ? trim($search) : '';

        $query = match ($user->role) {
            'admin' => Course::query(),
            'dosen' => $user->taughtCourses(),
            'mahasiswa' => $user->courses()->where('courses.status', '!=', 'draft'),
            default => abort(403),
        };

        $courses = $query
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('code', 'like', "%{$search}%")
                        ->orWhere('name', 'like', "%{$search}%");
                });
            })
            ->when(
                in_array(
                    $request->query('status'),
                    ['draft', 'active', 'archived'],
                    true
                ),
                fn ($query) => $query->where(
                    'status',
                    $request->query('status')
                )
            )
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('courses.index', compact('courses', 'role'));
    }

    public function create(Request $request)
    {
        Gate::authorize('create', Course::class);

        $role = $this->selectedRole($request);
        $lecturers = User::where('role', 'dosen')->get();

        return view('courses.create', compact('role', 'lecturers'));
    }

    public function store(StoreCourseRequest $request)
    {
        Gate::authorize('create', Course::class);

        Course::create($request->validated());

        $role = $this->selectedRole($request);

        return redirect()
            ->route($role.'.courses.index', ['as' => $role])
            ->with('success', 'Mata kuliah berhasil ditambahkan.');
    }

    public function show(Request $request, Course $course)
    {
        Gate::authorize('view', $course);

        $course->loadMissing('lecturer');

        $role = $this->selectedRole($request);

        $canViewStudentDetails = in_array(
            $role,
            ['admin', 'dosen'],
            true
        );

        // Daftar mahasiswa terdaftar dengan pagination.
        $students = $course->students()
            ->orderBy('users.name')
            ->paginate(25, ['users.*'], 'students_page')
            ->withQueryString();

        // Ambil seluruh mahasiswa beserta status pendaftarannya.
        // Menggantikan query terpisah untuk mengambil student IDs.
        $availableStudents = $canViewStudentDetails
            ? User::query()
                ->leftJoin('course_user', function ($join) use ($course) {
                    $join->on('users.id', '=', 'course_user.user_id')
                        ->where(
                            'course_user.course_id',
                            '=',
                            $course->id
                        );
                })
                ->where('users.role', 'mahasiswa')
                ->orderBy('users.name')
                ->get([
                    'users.id',
                    'users.name',
                    'users.email',
                    'users.nim_nip',
                    DB::raw(
                        'CASE
                            WHEN course_user.user_id IS NULL THEN 0
                            ELSE 1
                        END AS is_enrolled'
                    ),
                ])
            : collect();

        // Daftar dosen hanya ditampilkan kepada admin.
        $lecturers = $role === 'admin'
            ? User::query()
                ->where('role', 'dosen')
                ->orderBy('name')
                ->get()
            : collect();

        // Permintaan pendaftaran hanya untuk dosen pengampu.
        $pendingRequests = (
            $role === 'dosen'
            && $course->lecturer_id === $request->user()->id
        )
            ? $course->enrollmentRequests()
                ->with('student')
                ->where('status', 'pending')
                ->latest()
                ->paginate(15, ['*'], 'pending_page')
                ->withQueryString()
            : collect();

        return view(
            'courses.show',
            compact(
                'course',
                'role',
                'students',
                'availableStudents',
                'lecturers',
                'pendingRequests',
                'canViewStudentDetails'
            )
        );
    }

    public function updateLecturer(Request $request, Course $course)
    {
        Gate::authorize('manage', $course);

        $validated = $request->validate([
            'lecturer_id' => [
                'required',
                'integer',
                Rule::exists('users', 'id')->where(
                    fn ($query) => $query->where('role', 'dosen')
                ),
            ],
        ]);

        $course->update(['lecturer_id' => $validated['lecturer_id']]);

        return redirect()
            ->route('admin.courses.show', [
                'course' => $course,
                'as' => 'admin',
            ])
            ->with('success', 'Dosen pengampu berhasil diperbarui.');
    }

    public function updateStudents(Request $request, Course $course)
    {
        Gate::authorize('manageEnrollment', $course);

        $validated = $request->validate([
            'student_ids' => ['sometimes', 'array'],
            'student_ids.*' => [
                'required',
                'integer',
                'distinct',
                Rule::exists('users', 'id')->where(
                    fn ($query) => $query->where('role', 'mahasiswa')
                ),
            ],
        ]);

        $studentIds = array_map('intval', $validated['student_ids'] ?? []);

        $currentStudents = $course->students()->get();

        $currentStudentIds = $currentStudents->modelKeys();

        $newStudentIds = array_values(
            array_diff($studentIds, $currentStudentIds)
        );

        if (count($studentIds) > EnrollmentController::MAX_CAPACITY) {
            return back()
                ->withErrors([
                    'student_ids' => 'Kelas tidak dapat memiliki lebih dari '
                        .EnrollmentController::MAX_CAPACITY.' mahasiswa.',
                ])
                ->withInput();
        }

        foreach ($newStudentIds as $studentId) {
            $student = User::findOrFail($studentId);

            $currentSks = $student->courses()
                ->where('courses.status', 'active')
                ->sum('courses.sks');

            if (
                $course->status === 'active'
                && $currentSks + $course->sks > EnrollmentController::MAX_SKS
            ) {
                return back()
                    ->withErrors([
                        'student_ids' => "{$student->name} akan melebihi batas maksimal "
                            .EnrollmentController::MAX_SKS.' SKS.',
                    ])
                    ->withInput();
            }
        }

        $existingEnrolledAt = $currentStudents->mapWithKeys(
            fn (User $student) => [
                $student->getKey() => $student->pivot->enrolled_at,
            ]
        );

        $studentPivotData = collect($studentIds)->mapWithKeys(
            fn (int $studentId) => [
                $studentId => [
                    'enrolled_at' => $existingEnrolledAt->get($studentId) ?? now(),
                ],
            ]
        )->all();

        DB::transaction(function () use (
            $course,
            $studentPivotData,
            $newStudentIds
        ): void {
            $course->students()->sync($studentPivotData);

            foreach ($newStudentIds as $studentId) {
                EnrollmentRequest::updateOrCreate(
                    [
                        'course_id' => $course->getKey(),
                        'user_id' => $studentId,
                    ],
                    ['status' => 'approved']
                );
            }
        });

        DashboardController::invalidateCache();

        return redirect()
            ->route($request->user()->role.'.courses.show', [
                'course' => $course,
                'as' => $request->user()->role,
            ])
            ->with('success', 'Daftar mahasiswa kelas berhasil diperbarui.');
    }

    public function edit(Request $request, Course $course)
    {
        Gate::authorize('update', $course);

        $role = $this->selectedRole($request);
        $lecturers = User::where('role', 'dosen')->get();

        return view('courses.edit', compact('course', 'role', 'lecturers'));
    }

    public function update(
        UpdateCourseRequest $request,
        Course $course
    ) {
        Gate::authorize('update', $course);

        $course->update($request->validated());

        $role = $this->selectedRole($request);

        return redirect()
            ->route($role.'.courses.index', ['as' => $role])
            ->with('success', 'Mata kuliah berhasil diperbarui.');
    }

    public function destroy(Request $request, Course $course)
    {
        Gate::authorize('delete', $course);

        $course->assignments()
            ->with('submissions')
            ->get()
            ->each(fn ($assignment) => $assignment->submissions->each->delete());

        foreach ($course->materials as $material) {
            if (
                $material->file_path
                && Storage::disk('local')->exists($material->file_path)
            ) {
                Storage::disk('local')->delete($material->file_path);
            }
        }

        Storage::disk('local')->deleteDirectory("materials/{$course->id}");

        $course->delete();

        $role = $this->selectedRole($request);

        return redirect()
            ->route($role.'.courses.index', ['as' => $role])
            ->with('success', 'Mata kuliah berhasil dihapus.');
    }

    private function selectedRole(Request $request): string
    {
        return $request->user()->role;
    }
}