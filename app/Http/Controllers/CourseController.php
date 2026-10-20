<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCourseRequest;
use App\Http\Requests\UpdateCourseRequest;
use App\Models\Course;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate; // FIXED: Typo FacadeAs -> Facades
use Illuminate\Support\Facades\Storage;

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

        $course->load([
            'lecturer',
            'materials',
            'assignments',
        ]);

        return view('courses.show', compact('course', 'role'));
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