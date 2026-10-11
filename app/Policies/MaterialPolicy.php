<?php

namespace App\Policies;

use App\Models\Course;
use App\Models\Material;
use App\Models\User;

/**
 * Kontrak ability (argumen selain User):
 * - viewAny(): tanpa argumen; daftar materi.
 * - view(Material $material): detail sesuai akses ke mata kuliah induk.
 * - create(?Course $course = null): admin tanpa konteks; dosen wajib memberi course
 *   yang diampu (mis. @can('create', [Material::class, $course])).
 * - update(Material $material), delete(Material $material): pengelolaan materi.
 *
 * Blade: @can('view', $material), @can('create', [\App\Models\Material::class, $course]),
 * @can('update', $material), dan @can('delete', $material).
 */
class MaterialPolicy
{
    /**
     * Menentukan apakah pengguna dapat melihat daftar materi.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Menentukan apakah pengguna dapat melihat detail materi.
     * Aturan:
     * - Admin: semua materi.
     * - Dosen: hanya dosen pengampu dari mata kuliah materi tersebut.
     * - Mahasiswa: hanya mahasiswa yang terdaftar di mata kuliah dan mata kuliah bukan draft.
     */
    public function view(User $user, Material $material): bool
    {
        $course = $material->course;

        if ($user->role === 'admin') {
            return true;
        }

        if ($user->role === 'dosen') {
            return $user->taughtCourses()->whereKey($course->getKey())->exists();
        }

        if ($user->role === 'mahasiswa') {
            if ($course->status === 'draft') {
                return false;
            }

            return $course->students()->whereKey($user->id)->exists();
        }

        return false;
    }

    /**
     * Menentukan apakah pengguna dapat membuat materi baru.
     * Aturan:
     * - Admin: diizinkan.
     * - Dosen: hanya pada mata kuliah yang diampunya.
     */
    public function create(User $user, ?Course $course = null): bool
    {
        if ($user->role === 'admin') {
            return true;
        }

        if ($user->role === 'dosen') {
            return $course !== null
                && $user->taughtCourses()->whereKey($course->getKey())->exists();
        }

        return false;
    }

    /**
     * Menentukan apakah pengguna dapat memperbarui materi.
     * Aturan: Admin atau dosen pengampu mata kuliah materi tersebut.
     */
    public function update(User $user, Material $material): bool
    {
        if ($user->role === 'admin') {
            return true;
        }

        if ($user->role === 'dosen') {
            return $user->taughtCourses()->whereKey($material->course_id)->exists();
        }

        return false;
    }

    /**
     * Menentukan apakah pengguna dapat menghapus materi.
     * Aturan: Admin atau dosen pengampu mata kuliah materi tersebut.
     */
    public function delete(User $user, Material $material): bool
    {
        if ($user->role === 'admin') {
            return true;
        }

        if ($user->role === 'dosen') {
            return $user->taughtCourses()->whereKey($material->course_id)->exists();
        }

        return false;
    }
}
