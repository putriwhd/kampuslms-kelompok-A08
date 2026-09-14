<?php

namespace App\Http\Controllers;

use Illuminate\View\View;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class CourseController extends Controller
{
    /**
     * Data mata kuliah masih berupa array statis, BUKAN Eloquent Model,
     * karena Minggu 3 (migrasi + database) belum dikerjakan. Ditaruh
     * sebagai method terpisah (bukan ditulis ulang di index() dan
     * show()) supaya kedua method memakai satu sumber data yang sama.
     *
     * Key array (1, 2, 3) SENGAJA dipakai sebagai pengganti sementara
     * "id", supaya show() bisa mencari data tanpa query database.
     */
    private function data(): array
    {
        return [
            1 => [
                'id' => 1,
                'kode' => 'IF101',
                'nama' => 'Algoritma dan Pemrograman',
                'sks' => 3,
                'dosen' => 'Henokh Lugo Hariyanto',
                'deskripsi' => 'Pengantar dasar algoritma, struktur kontrol, dan implementasi program sederhana.',
            ],
            2 => [
                'id' => 2,
                'kode' => 'IF204',
                'nama' => 'Administrasi Basis Data',
                'sks' => 3,
                'dosen' => 'Siti Aminah',
                'deskripsi' => 'Perancangan, instalasi, dan pengelolaan basis data relasional.',
            ],
            3 => [
                'id' => 3,
                'kode' => 'IF305',
                'nama' => 'Keamanan Informasi',
                'sks' => 2,
                'dosen' => 'Budi Santoso',
                'deskripsi' => 'Konsep keamanan sistem informasi, audit, dan manajemen risiko.',
            ],
        ];
    }

    /**
     * GET /courses
     * Menyerahkan seluruh daftar mata kuliah ke view. Controller ini
     * SENGAJA tidak melakukan apa-apa selain "minta data lalu serahkan
     * ke view" (lihat Bagian 2.1 modul: "Controller: penerima tamu,
     * bukan koki").
     */
    public function index(): View
    {
        $courses = $this->data();

        return view('courses.index', compact('courses'));
    }

    /**
     * GET /courses/{course}
     * $course di sini adalah STRING dari URL (mis. "1"), bukan Model
     * hasil route model binding -- karena belum ada Eloquent Model
     * di Minggu 1-2. Dicocokkan manual ke array data().
     */
    public function show(string $course): View
    {
        $courses = $this->data();
        $course = $courses[(int) $course] ?? null;

        // 404 dilempar manual di sini. Perilakunya SENGAJA disamakan
        // dengan yang nanti dihasilkan otomatis oleh route model
        // binding Eloquent di Minggu 3, supaya saat controller ini
        // di-upgrade ke database, cara Blade menampilkan halaman
        // "tidak ditemukan" tidak perlu berubah.
        if (! $course) {
            throw new NotFoundHttpException('Mata kuliah tidak ditemukan.');
        }

        return view('courses.show', compact('course'));
    }
}