<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCourseRequest;
use App\Http\Requests\UpdateCourseRequest;
use App\Models\Course;
use App\Models\User;
use Illuminate\Http\Request;
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
            ->with('lecturer')
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
            ->route($role . '.courses.index', ['as' => $role])
            ->with('success', 'Mata kuliah berhasil ditambahkan.');
    }

    public function show(Request $request, Course $course)
    {
        Gate::authorize('view', $course);

        $role = $this->selectedRole($request);

        $canManageStudents = in_array($role, ['admin', 'dosen'], true);
        $students = $canManageStudents
            ? User::query()->where('role', 'mahasiswa')->orderBy('name')->get()
            : collect();
        $enrolledStudentIds = $canManageStudents
            ? $course->students()->pluck('users.id')->all()
            : [];

        return view(
            'courses.show',
            compact('course', 'role', 'students', 'enrolledStudentIds')
        );
    }

    public function updateStudents(Request $request, Course $course)
    {
        Gate::authorize('manageStudents', $course);

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

        $studentIds = $validated['student_ids'] ?? [];
        $role = $request->user()->role;
        $syncData = collect($studentIds)
            ->mapWithKeys(fn (int $studentId) => [
                $studentId => ['enrolled_at' => now()],
            ])
            ->all();

        $currentlyEnrolledIds = $course->students()->pluck('users.id')->all();
        $course->students()->syncWithoutDetaching($syncData);
        $course->students()->detach(
            array_diff($currentlyEnrolledIds, $studentIds)
        );

        return redirect()
            ->route($role . '.courses.show', [
                'course' => $course,
                'as' => $role,
            ])
            ->with('success', 'Pendaftaran mahasiswa pada mata kuliah berhasil diperbarui.');
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
            ->route($role . '.courses.index', ['as' => $role])
            ->with('success', 'Mata kuliah berhasil diperbarui.');
    }

    public function destroy(Request $request, Course $course)
    {
        // FIXED 1: Lakukan otorisasi di paling awal
        Gate::authorize('delete', $course);

        // FIXED 2: Hapus method ensureCourseOwnership yang tidak ada

        // Hapus submission melalui model agar event deleting membersihkan file privat sebelum cascade database.
        $course->assignments()
            ->with('submissions')
            ->get()
            ->each(fn ($assignment) => $assignment->submissions->each->delete());

        // Bersihkan berkas materi fisik secara eksplisit sebelum cascadeOnDelete MySQL menghapus datanya
        foreach ($course->materials as $material) {
            if ($material->file_path && Storage::disk('local')->exists($material->file_path)) {
                Storage::disk('local')->delete($material->file_path);
            }
        }
        Storage::disk('local')->deleteDirectory("materials/{$course->id}");

        $course->delete();

        $role = $this->selectedRole($request);

        return redirect()
            ->route($role . '.courses.index', ['as' => $role])
            ->with('success', 'Mata kuliah berhasil dihapus.');
    }

    private function selectedRole(Request $request): string
    {
        return $request->user()->role;
    }
}