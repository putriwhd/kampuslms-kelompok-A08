<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ChecksCourseOwnership;
use App\Models\Course;
use App\Models\Material;
use Illuminate\Http\Request;

class MaterialController extends Controller
{
    use ChecksCourseOwnership;

    public function index(Request $request, Course $course)
    {
        $this->ensureCourseOwnership($request, $course);
        $role = $request->attributes->get('selected_role', $request->query('as', 'mahasiswa'));
        $materials = $course->materials()->latest()->paginate(15)->withQueryString();

        return view('materials.index', compact('course', 'materials', 'role'));
    }

    public function show(Request $request, Material $material)
    {
        $material->load('course');
        $this->ensureCourseOwnership($request, $material->course);
        $role = $request->attributes->get('selected_role', $request->query('as', 'mahasiswa'));

        return view('materials.show', compact('material', 'role'));
    }

    public function showInCourse(Request $request, Course $course, Material $material)
    {
        abort_unless($material->course_id === $course->id, 403);
        $this->ensureCourseOwnership($request, $course);

        $role = $request->attributes->get('selected_role', $request->query('as', 'mahasiswa'));
        $material->load('course');

        return view('materials.show', compact('material', 'role'));
    }
}
