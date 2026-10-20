<?php

namespace App\Policies;

use App\Models\Course;
use App\Models\Material;
use App\Models\User;

class MaterialPolicy
{
    /**
     * Daftar materi dapat dilihat semua peran yang valid; akses kelas
     * tertentu tetap dibatasi oleh CoursePolicy di controller.
     */
    public function viewAny(User $user): bool
    {
        return in_array($user->role, ['admin', 'dosen', 'mahasiswa'], true);
    }

    /**
     * Tentukan apakah pengguna berhak melihat/mengunduh materi kuliah.
     * Hanya mahasiswa terdaftar, dosen pengampu mata kuliah terkait, dan Admin.
     */
    public function view(User $user, Material $material): bool
    {
        // 1. Admin selalu diizinkan
        if ($user->role === 'admin') {
            return true;
        }

        $course = $material->course;
        if (! $course) {
            return false;
        }

        // 2. Dosen pengampu mata kuliah terkait diizinkan
        if ((int) $course->lecturer_id === (int) $user->id) {
            return true;
        }

        // 3. Mahasiswa yang terdaftar (enrolled) pada mata kuliah terkait diizinkan
        if ($user->role === 'mahasiswa') {
            return $course->students()->where('users.id', $user->id)->exists();
        }

        return false;
    }

    /**
     * Tentukan apakah pengguna boleh mengunggah materi ke mata kuliah.
     */
    public function create(User $user, Course $course): bool
    {
        if ($user->role === 'admin') {
            return true;
        }

        if ($user->role === 'dosen') {
            return (int) $course->lecturer_id === (int) $user->id;
        }

        return false;
    }

    /**
     * Tentukan apakah pengguna boleh memperbarui materi kuliah.
     */
    public function update(User $user, Material $material): bool
    {
        if ($user->role === 'admin') {
            return true;
        }

        $course = $material->course;
        if ($course && (int) $course->lecturer_id === (int) $user->id) {
            return true;
        }

        return false;
    }

    /**
     * Tentukan apakah pengguna boleh menghapus materi kuliah.
     */
    public function delete(User $user, Material $material): bool
    {
        if ($user->role === 'admin') {
            return true;
        }

        $course = $material->course;
        if ($course && (int) $course->lecturer_id === (int) $user->id) {
            return true;
        }

        return false;
    }
}
