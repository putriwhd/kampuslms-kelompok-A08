<?php

namespace App\Http\Controllers;

use App\Events\MaterialChanged;
use App\Http\Requests\StoreMaterialRequest;
use App\Http\Requests\UpdateMaterialRequest;
use App\Models\Course;
use App\Models\Material;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

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

    public function store(StoreMaterialRequest $request, Course $course): RedirectResponse
    {
        Gate::authorize('create', [Material::class, $course]);

        $validated = $request->validated();
        $user = $request->user();

        $data = [
            'course_id' => $course->id,
            'uploaded_by' => $user->id,
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'type' => $validated['type'],
        ];

        if ($validated['type'] === 'file' && $request->hasFile('file')) {
            $file = $request->file('file');
            // Simpan berkas fisik ke disk privat 'local' dengan nama hash acak
            $path = $file->store("materials/{$course->id}", 'local');

            if (! is_string($path)) {
                throw new \RuntimeException('Gagal menyimpan berkas materi.');
            }

            $data['file_path'] = $path;
            $data['original_name'] = $file->getClientOriginalName();
            $data['file_size'] = $file->getSize();
            $data['mime_type'] = $file->getClientMimeType();
            $data['external_url'] = null;
        } else {
            $data['external_url'] = $validated['external_url'] ?? null;
            $data['file_path'] = null;
            $data['original_name'] = null;
            $data['file_size'] = null;
            $data['mime_type'] = null;
        }

        $material = Material::create($data);
        MaterialChanged::dispatch($material, 'uploaded');

        return redirect()
            ->route($user->role . '.courses.materials.index', [
                'course' => $course,
                'as' => $user->role,
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
    ): RedirectResponse {
        Gate::authorize('update', $material);

        $validated = $request->validated();
        $material->loadMissing('course');
        $oldFilePath = $material->file_path;
        $data = [
            'title' => $validated['title'],
            'description' => $validated['description'],
            'type' => $validated['type'],
        ];

        if ($validated['type'] === 'link') {
            $data += [
                'external_url' => $validated['external_url'],
                'file_path' => null,
                'original_name' => null,
                'file_size' => null,
                'mime_type' => null,
            ];
        } else {
            $data['external_url'] = null;
            if ($request->hasFile('file')) {
                $file = $request->file('file');
                $filePath = $file->store(
                    "materials/{$material->course_id}",
                    'local'
                );

                if (! is_string($filePath)) {
                    throw new \RuntimeException('Gagal menyimpan berkas materi.');
                }

                $data += [
                    'file_path' => $filePath,
                    'original_name' => $file->getClientOriginalName(),
                    'file_size' => $file->getSize(),
                    'mime_type' => $file->getClientMimeType(),
                ];
            }
        }

        $material->update($data);

        MaterialChanged::dispatch($material, 'updated');

        if (
            is_string($oldFilePath)
            && $oldFilePath !== ''
            && $oldFilePath !== $material->file_path
            && Storage::disk('local')->exists($oldFilePath)
        ) {
            Storage::disk('local')->delete($oldFilePath);
        }

        $role = $request->user()->role;

        return redirect()
            ->route($role . '.courses.materials.index', [
                'course' => $material->course,
                'as' => $role,
            ])
            ->with('success', 'Materi berhasil diperbarui.');
    }

    public function destroy(Request $request, Material $material): RedirectResponse
    {
        Gate::authorize('delete', $material);

        $course = $material->course;
        $role = $request->user()->role;
        
        // Menghapus record (berkas fisik otomatis terhapus via event static::deleting di model Material)
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

    /**
     * Mengunduh berkas materi privat dengan pemeriksaan otorisasi Policy.
     */
    public function download(Material $material): StreamedResponse
    {
        $material->load('course');

        // Otorisasi: hanya mahasiswa terdaftar, dosen pengampu, dan Admin
        Gate::authorize('view', $material);

        abort_unless($material->type === 'file' && $material->file_path, 404, 'Materi ini bukan berupa berkas.');

        $pathInDb = $material->file_path;
        $pathWithFolder = "materials/{$material->course_id}/{$material->file_path}";

        // Tentukan path mana yang benar-benar ada di storage fisik
        $finalPath = null;
        if (Storage::disk('local')->exists($pathInDb)) {
            $finalPath = $pathInDb;
        } elseif (Storage::disk('local')->exists($pathWithFolder)) {
            $finalPath = $pathWithFolder;
        }

        // Jika berkas tidak ditemukan di kedua jalur tersebut, kembalikan 404
        abort_unless($finalPath !== null, 404, 'Berkas materi tidak ditemukan di server.');

        // Unduh berkas dari disk privat menggunakan nama asli berkas
        return Storage::disk('local')->download(
            $finalPath,
            $material->original_name
        );
    }
}