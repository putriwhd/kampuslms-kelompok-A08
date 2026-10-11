<?php

namespace App\Policies;

use App\Models\Course;
use App\Models\User;

/**
 * Kontrak ability (argumen selain User):
 * - viewAny(): tanpa argumen; daftar mata kuliah.
 * - view(Course $course): mahasiswa harus terdaftar dan mata kuliah bukan draft.
 * - create(): tanpa argumen; hanya admin.
 * - update(Course $course), delete(Course $course), manage(Course $course):
 *   hanya admin, sesuai hak CRUD mata kuliah.
 * - viewDraft(Course $course): admin atau dosen pengampu.
 *
 * Blade: @can('view', $course), @can('create', \App\Models\Course::class),
 * @can('manage', $course), dan @can('viewDraft', $course).
 */
class CoursePolicy
{
    /**
     * Menentukan apakah pengguna dapat melihat daftar mata kuliah.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Menentukan apakah pengguna dapat melihat detail mata kuliah tertentu.
     * Aturan:
     * - Admin: semua mata kuliah.
     * - Dosen: hanya dosen pengampu mata kuliah tersebut.
     * - Mahasiswa: hanya jika terdaftar dan status mata kuliah bukan draft (draft hanya untuk admin & dosen pengampu).
     */
    public function view(User $user, Course $course): bool
    {
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
     * Menentukan apakah pengguna dapat membuat mata kuliah baru.
     * Aturan: Hanya admin.
     */
    public function create(User $user): bool
    {
        return $user->role === 'admin';
    }

    /**
     * Menentukan apakah pengguna dapat memperbarui data mata kuliah.
     * Aturan: Hanya admin yang dapat mengubah mata kuliah.
     */
    public function update(User $user, Course $course): bool
    {
        return $user->role === 'admin';
    }

    /**
     * Menentukan apakah pengguna dapat menghapus mata kuliah.
     * Aturan: Hanya admin.
     */
    public function delete(User $user, Course $course): bool
    {
        return $user->role === 'admin';
    }

    /**
     * Menentukan apakah pengguna dapat melihat status draft mata kuliah.
     * Aturan: Hanya admin dan dosen pengampu.
     */
    public function viewDraft(User $user, Course $course): bool
    {
        return $user->role === 'admin'
            || ($user->role === 'dosen'
                && $user->taughtCourses()->whereKey($course->getKey())->exists());
    }

    /**
     * Menentukan apakah pengguna memiliki hak CRUD mata kuliah.
     */
    public function manage(User $user, Course $course): bool
    {
        return $user->role === 'admin';
    }
}
