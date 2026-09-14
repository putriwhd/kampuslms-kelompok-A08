<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\User;
use Illuminate\Http\Request;

class CourseController extends Controller
{
    public function index(Request $request)
    {
        // 1. Ambil role simulasi dari URL (?as=...), default ke 'mahasiswa'
        $role = $request->query('as', 'mahasiswa');

        $courses = Course::with('lecturer')->latest()->paginate(10);

        // 2. Kirim $role ke view bersama $courses
        return view('courses.index', compact('courses', 'role'));
    }

    public function create(Request $request)
    {
        $role = $request->query('as', 'mahasiswa');
        $lecturers = User::where('role', 'dosen')->get();

        return view('courses.create', compact('lecturers', 'role'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string|unique:courses,code',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'lecturer_id' => 'required|exists:users,id',
        ]);

        Course::create($validated);

        // Kembalikan ke index dengan tetap mempertahankan status role
        return redirect()->route('courses.index', ['as' => $request->query('as', 'admin')])
            ->with('success', 'Mata kuliah berhasil ditambahkan.');
    }

    public function show(Request $request, Course $course)
    {
        $role = $request->query('as', 'mahasiswa');
        $course->load(['lecturer', 'students', 'materials', 'assignments']);

        return view('courses.show', compact('course', 'role'));
    }

    public function edit(Request $request, Course $course)
    {
        $role = $request->query('as', 'mahasiswa');
        $lecturers = User::where('role', 'dosen')->get();

        return view('courses.edit', compact('course', 'lecturers', 'role'));
    }

    public function update(Request $request, Course $course)
    {
        $validated = $request->validate([
            'code' => 'required|string|unique:courses,code,' . $course->id,
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'lecturer_id' => 'required|exists:users,id',
        ]);

        $course->update($validated);

        return redirect()->route('courses.index', ['as' => $request->query('as', 'admin')])
            ->with('success', 'Mata kuliah berhasil diperbarui.');
    }

    public function destroy(Request $request, Course $course)
    {
        $course->delete();

        return redirect()->route('courses.index', ['as' => $request->query('as', 'admin')])
            ->with('success', 'Mata kuliah berhasil dihapus.');
    }
}