<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ChecksCourseOwnership;
use App\Http\Requests\StoreCourseRequest;
use App\Http\Requests\UpdateCourseRequest;
use App\Models\Course;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class CourseController extends Controller
{
    use ChecksCourseOwnership;

    public function index(Request $request)
    {
        $user = $request->user();
        $role = $this->selectedRole($request);

        $search = $request->query('search');
        $search = is_string($search) ? trim($search) : '';

        $query = Course::with('lecturer');

        if ($user && $user->role === 'admin') {
            // Admin dapat melihat semua mata kuliah.
        } elseif ($user && $user->role === 'dosen') {
            $query->where('lecturer_id', $user->id);
        } elseif ($user && $user->role === 'mahasiswa') {
            $query->whereHas('students', function ($q) use ($user) {
                $q->where('users.id', $user->id);
            });
        } else {
            $query->whereRaw('1 = 0');
        }

        $query
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
            ->latest();

        $courses = $query->paginate(15)->withQueryString();

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
        $this->ensureCourseOwnership($request, $course);

        Gate::authorize('view', $course);

        $role = $this->selectedRole($request);

        $course->load([
            'lecturer',
            'students',
            'materials',
            'assignments',
        ]);

        return view('courses.show', compact('course', 'role'));
    }

    public function edit(Request $request, Course $course)
    {
        $this->ensureCourseOwnership($request, $course);

        Gate::authorize('update', $course);

        $role = $this->selectedRole($request);
        $lecturers = User::where('role', 'dosen')->get();

        return view('courses.edit', compact('course', 'role', 'lecturers'));
    }

    public function update(
        UpdateCourseRequest $request,
        Course $course
    ) {
        $this->ensureCourseOwnership($request, $course);

        Gate::authorize('update', $course);

        $course->update($request->validated());

        $role = $this->selectedRole($request);

        return redirect()
            ->route($role . '.courses.index', ['as' => $role])
            ->with('success', 'Mata kuliah berhasil diperbarui.');
    }

    public function destroy(Request $request, Course $course)
    {
        $this->ensureCourseOwnership($request, $course);

        Gate::authorize('delete', $course);

        $course->delete();

        $role = $this->selectedRole($request);

        return redirect()
            ->route($role . '.courses.index', ['as' => $role])
            ->with('success', 'Mata kuliah berhasil dihapus.');
    }

    private function selectedRole(Request $request): string
    {
        if ($request->attributes->has('selected_role')) {
            return $request->attributes->get('selected_role');
        }

        if ($request->query('as')) {
            return $request->query('as');
        }

        $prefix = $request->segment(1);

        if (in_array($prefix, ['admin', 'dosen', 'mahasiswa'], true)) {
            return $prefix;
        }

        return 'mahasiswa';
    }
}
