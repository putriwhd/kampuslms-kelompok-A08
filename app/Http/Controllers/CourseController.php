<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ChecksCourseOwnership;
use App\Http\Requests\StoreCourseRequest;
use App\Http\Requests\UpdateCourseRequest;
use App\Models\Course;
use App\Models\User;
use Illuminate\Http\Request;

class CourseController extends Controller
{
    use ChecksCourseOwnership;

    public function index(Request $request)
    {
        $role = $this->selectedRole($request);
        $user = $request->user();
        $search = $request->query('search');
        $search = is_string($search) ? trim($search) : '';

        $courses = Course::with('lecturer')
            ->when($user && $user->role === 'dosen', function ($query) use ($user) {
                $query->where('lecturer_id', $user->id);
            })
            ->when($user && $user->role === 'mahasiswa', function ($query) use ($user) {
                $query->whereHas('students', function ($q) use ($user) {
                    $q->where('users.id', $user->id);
                });
            })
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('code', 'like', "%{$search}%")
                        ->orWhere('name', 'like', "%{$search}%");
                });
            })
            ->when(
                in_array($request->query('status'), ['draft', 'active', 'archived'], true),
                fn ($query) => $query->where('status', $request->query('status'))
            )
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('courses.index', compact('courses', 'role'));
    }

    public function create(Request $request)
    {
        $role = $this->selectedRole($request);
        $lecturers = User::where('role', 'dosen')->get();

        return view('courses.create', compact('role', 'lecturers'));
    }

    public function store(StoreCourseRequest $request)
    {
        Course::create($request->validated());
        $role = $this->selectedRole($request);

        return redirect()
            ->route($role . '.courses.index', [
                'as' => $role,
            ])
            ->with('success', 'Mata kuliah berhasil ditambahkan.');
    }

    public function show(Request $request, Course $course)
    {
        $this->ensureCourseOwnership($request, $course);
        $role = $this->selectedRole($request);

        $course->load([
            'lecturer',
            'students',
            'materials',
            'assignments'
        ]);

        return view('courses.show', compact('course', 'role'));
    }

    public function edit(Request $request, Course $course)
    {
        $this->ensureCourseOwnership($request, $course);
        $role = $this->selectedRole($request);
        $lecturers = User::where('role', 'dosen')->get();

        return view('courses.edit', compact('course', 'role', 'lecturers'));
    }

    public function update(UpdateCourseRequest $request, Course $course)
    {
        $this->ensureCourseOwnership($request, $course);
        $course->update($request->validated());
        $role = $this->selectedRole($request);

        return redirect()
            ->route($role . '.courses.index', [
                'as' => $role,
            ])
            ->with('success', 'Mata kuliah berhasil diperbarui.');
    }

    public function destroy(Request $request, Course $course)
    {
        $this->ensureCourseOwnership($request, $course);
        $course->delete();
        $role = $this->selectedRole($request);

        return redirect()
            ->route($role . '.courses.index', [
                'as' => $role,
            ])
            ->with('success', 'Mata kuliah berhasil dihapus.');
    }

    private function selectedRole(Request $request): string
    {
        // 1. Request attribute set by middleware
        if ($request->attributes->has('selected_role')) {
            return $request->attributes->get('selected_role');
        }

        // 2. Explicit query parameter
        if ($request->query('as')) {
            return $request->query('as');
        }

        // 3. Infer from URL prefix
        $prefix = $request->segment(1); // 'admin', 'dosen', or 'mahasiswa'
        if (in_array($prefix, ['admin', 'dosen', 'mahasiswa'], true)) {
            return $prefix;
        }

        return 'mahasiswa';
    }

}