<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ChecksCourseOwnership;
use App\Http\Requests\StoreMaterialRequest;
use App\Models\Course;
use App\Models\Material;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

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

    public function create(Request $request, Course $course)
    {
        $this->ensureCourseOwnership($request, $course);
        $role = $request->attributes->get('selected_role', $request->query('as', 'mahasiswa'));

        return view('materials.create', compact('course', 'role'));
    }

    public function store(StoreMaterialRequest $request, Course $course): RedirectResponse
    {
        $this->ensureCourseOwnership($request, $course);

        $validated = $request->validated();
        $user = $request->user();

        $data = [
            'course_id' => $course->id,
            'uploaded_by' => $user ? $user->id : $course->lecturer_id,
            'title' => $validated['title'],
            'description' => $validated['description'],
            'type' => $validated['type'],
        ];

        if ($validated['type'] === 'file' && $request->hasFile('file')) {
            $file = $request->file('file');
            // Simpan berkas fisik ke disk privat 'local' (storage/app/private/materials/{course_id}) dengan nama hash acak
            $path = $file->store("materials/{$course->id}", 'local');

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

        Material::create($data);

        return back()->with('success', 'Materi kuliah berhasil ditambahkan.');
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

    /**
     * Mengunduh berkas materi privat dengan pemeriksaan otorisasi Policy.
     */
    public function download(Material $material): StreamedResponse
    {
        $material->load('course');

        // Otorisasi: hanya mahasiswa terdaftar, dosen pengampu, dan Admin
        Gate::authorize('view', $material);

        abort_unless($material->type === 'file' && $material->file_path, 404, 'Materi ini bukan berupa berkas.');
        abort_unless(
            Storage::disk('local')->exists($material->file_path),
            404,
            'Berkas materi tidak ditemukan di server.'
        );

        // Unduh berkas dari disk privat menggunakan nama asli
        return Storage::disk('local')->download(
            $material->file_path,
            $material->original_name
        );
    }

    /**
     * Menghapus record materi kuliah (berkas fisik otomatis terhapus via event static::deleting).
     */
    public function destroy(Request $request, Material $material): RedirectResponse
    {
        $this->ensureCourseOwnership($request, $material->course);

        $material->delete();

        return back()->with('success', 'Materi berhasil dihapus.');
    }
}
