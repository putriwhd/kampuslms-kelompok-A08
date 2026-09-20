<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCourseRequest;
use App\Http\Requests\UpdateCourseRequest;
use App\Models\Course;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class CourseController extends Controller
{
    public function index(Request $request)
    {
        $role = $this->selectedRole($request);

        $courses = Course::with('lecturer')->latest()->paginate(10);

        return view('courses.index', compact('courses', 'role'));
    }

    public function create(Request $request)
    {
        $role = $this->selectedRole($request);

        return view('courses.create', compact('role'));
    }

    public function store(StoreCourseRequest $request)
    {
        Course::create($request->validated());

        return redirect()
            ->route('courses.index', [
                'as' => $request->query('as', 'admin')
            ])
            ->with('success', 'Mata kuliah berhasil ditambahkan.');
    }

    public function show(Request $request, Course $course)
    {
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
    $role = $this->selectedRole($request);
    $lecturers = User::where('role', 'dosen')->get(); // Mengambil daftar dosen

    return view('courses.edit', compact('course', 'role', 'lecturers'));
}

    public function update(UpdateCourseRequest $request, Course $course)
    {
        $course->update($request->validated());

        return redirect()
            ->route('courses.index', [
                'as' => $request->query('as', 'admin')
            ])
            ->with('success', 'Mata kuliah berhasil diperbarui.');
    }

    public function destroy(Request $request, Course $course)
    {
        $course->delete();

        return redirect()
            ->route('courses.index', [
                'as' => $request->query('as', 'admin')
            ])
            ->with('success', 'Mata kuliah berhasil dihapus.');
    }

    private function selectedRole(Request $request): string
    {
        return in_array(
            $request->query('as'),
            ['mahasiswa', 'dosen', 'admin'],
            true
        )
            ? $request->query('as')
            : 'mahasiswa';
    }

    private function findOrCreateLecturer(string $name): User
    {
        $lecturer = User::where('role', 'dosen')
            ->where('name', $name)
            ->first();

        if ($lecturer) {
            return $lecturer;
        }

        $baseEmail = Str::slug($name, '.') ?: 'dosen';
        $email = $baseEmail . '@kampuslms.test';
        $suffix = 1;

        while (User::where('email', $email)->exists()) {
            $email = $baseEmail . $suffix . '@kampuslms.test';
            $suffix++;
        }

        $lecturer = new User();
        $lecturer->name = $name;
        $lecturer->email = $email;
        $lecturer->password = Hash::make('password');
        $lecturer->role = 'dosen';
        $lecturer->save();

        return $lecturer;
    }
}