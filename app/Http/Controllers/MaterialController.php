<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreMaterialRequest;
use App\Http\Requests\UpdateMaterialRequest;
use App\Models\Course;
use App\Models\Material;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class MaterialController extends Controller
{
    public function index(Request $request, Course $course)
    {
        Gate::authorize('view', $course);
        Gate::authorize('viewAny', Material::class);

        $role = $request->user()->role;

        $materials = $course->materials()
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view(
            'materials.index',
            compact('course', 'materials', 'role')
        );
    }

    public function create(Request $request, Course $course)
    {
        Gate::authorize('create', [Material::class, $course]);

        $role = $request->user()->role;

        return view('materials.create', compact('course', 'role'));
    }

    public function store(StoreMaterialRequest $request, Course $course)
    {
        Gate::authorize('create', [Material::class, $course]);

        $validated = $request->validated();
        $course->materials()->create([
            ...$validated,
            'external_url' => $validated['type'] === 'link'
                ? ($validated['external_url'] ?? null)
                : null,
            'uploaded_by' => $request->user()->getKey(),
        ]);

        return redirect()
            ->route($request->user()->role . '.courses.materials.index', [
                'course' => $course,
                'as' => $request->user()->role,
            ])
            ->with('success', 'Materi berhasil ditambahkan.');
    }

    public function show(Request $request, Material $material)
    {
        $material->load('course');
        Gate::authorize('view', $material);

        $role = $request->user()->role;

        return view('materials.show', compact('material', 'role'));
    }

    public function edit(Request $request, Material $material)
    {
        $material->load('course');
        Gate::authorize('update', $material);

        $role = $request->user()->role;

        return view('materials.edit', compact('material', 'role'));
    }

    public function update(
        UpdateMaterialRequest $request,
        Material $material
    ) {
        Gate::authorize('update', $material);

        $validated = $request->validated();
        $validated['external_url'] = $validated['type'] === 'link'
            ? ($validated['external_url'] ?? null)
            : null;
        $material->update($validated);

        $role = $request->user()->role;

        return redirect()
            ->route($role . '.courses.materials.index', [
                'course' => $material->course,
                'as' => $role,
            ])
            ->with('success', 'Materi berhasil diperbarui.');
    }

    public function destroy(Request $request, Material $material)
    {
        Gate::authorize('delete', $material);

        $course = $material->course;
        $role = $request->user()->role;
        $material->delete();

        return redirect()
            ->route($role . '.courses.materials.index', [
                'course' => $course,
                'as' => $role,
            ])
            ->with('success', 'Materi berhasil dihapus.');
    }

    public function showInCourse(
        Request $request,
        Course $course,
        Material $material
    ) {
        abort_unless($material->course_id === $course->id, 404);

        Gate::authorize('view', $material);

        $role = $request->user()->role;

        $material->load('course');

        return view('materials.show', compact('material', 'role'));
    }
}
