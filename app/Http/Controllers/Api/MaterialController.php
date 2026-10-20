<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\MaterialResource;
use App\Models\Course;
use App\Models\Material;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class MaterialController extends Controller
{
    public function store(Request $request, int $courseId)
    {
        $course = Course::findOrFail($courseId);
        Gate::authorize('create', [Material::class, $course]);

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'type' => ['required', Rule::in(['file', 'link'])],
            'external_url' => [
                'required_if:type,link',
                'nullable',
                'url',
                'max:255',
            ],
        ]);

        $material = $course->materials()->create([
            ...$validated,
            'external_url' => $validated['type'] === 'link'
                ? ($validated['external_url'] ?? null)
                : null,
            'uploaded_by' => $request->user()->getKey(),
        ]);

        return (new MaterialResource(
            $material->load([
                'course' => fn ($query) => $query
                    ->with('lecturer')
                    ->withCount(['materials', 'assignments']),
                'uploader',
            ])
        ))
            ->response()
            ->setStatusCode(201);
    }

    public function update(Request $request, int $id)
    {
        $material = Material::with('course')->findOrFail($id);
        Gate::authorize('update', $material);

        $validated = $request->validate([
            'title' => ['sometimes', 'required', 'string', 'max:255'],
            'description' => ['sometimes', 'required', 'string'],
            'type' => ['sometimes', 'required', Rule::in(['file', 'link'])],
            'external_url' => [
                Rule::requiredIf($request->input('type') === 'link'),
                'nullable',
                'url',
                'max:255',
            ],
        ]);

        if (($validated['type'] ?? $material->type) === 'file') {
            $validated['external_url'] = null;
        }

        $material->update($validated);

        return new MaterialResource(
            $material->load([
                'course' => fn ($query) => $query
                    ->with('lecturer')
                    ->withCount(['materials', 'assignments']),
                'uploader',
            ])
        );
    }

    public function destroy(int $id)
    {
        $material = Material::with('course')->findOrFail($id);
        Gate::authorize('delete', $material);

        $material->delete();

        return response()->noContent();
    }
}
