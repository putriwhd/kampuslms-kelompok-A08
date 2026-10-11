<?php

namespace App\Policies;

use App\Models\Course;
use App\Models\User;

/**
 * Kontrak ability:
 * - viewAny(): daftar mata kuliah.
 * - view(Course $course): sesuai role dan hak akses.
 * - create(): hanya admin.
 * - update(Course $course): admin atau dosen pengampu.
 * - delete(Course $course): hanya admin.
 * - manage(Course $course): hanya admin.
 * - viewDraft(Course $course): admin atau dosen pengampu.
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
     * Menentukan apakah pengguna dapat melihat mata kuliah.
     */
    public function view(User $user, Course $course): bool
    {
        if ($user->role === 'admin') {
            return true;
        }

        if ($user->role === 'dosen') {
            return $user->taughtCourses()
                ->whereKey($course->getKey())
                ->exists();
        }

        if ($user->role === 'mahasiswa') {
            if ($course->status === 'draft') {
                return false;
            }

            return $course->students()
                ->whereKey($user->id)
                ->exists();
        }

        return false;
    }

    /**
     * Hanya admin yang dapat membuat mata kuliah.
     */
    public function create(User $user): bool
    {
        return $user->role === 'admin';
    }

    /**
     * Admin dapat memperbarui semua mata kuliah.
     * Dosen hanya dapat memperbarui mata kuliah yang diampu.
     */
    public function update(User $user, Course $course): bool
    {
        if ($user->role === 'admin') {
            return true;
        }

        if ($user->role === 'dosen') {
            return $user->taughtCourses()
                ->whereKey($course->getKey())
                ->exists();
        }

        return false;
    }

    /**
     * Hanya admin yang dapat menghapus mata kuliah.
     */
    public function delete(User $user, Course $course): bool
    {
        return $user->role === 'admin';
    }

    /**
     * Admin dan dosen pengampu dapat melihat mata kuliah draft.
     */
    public function viewDraft(User $user, Course $course): bool
    {
        return $user->role === 'admin'
            || (
                $user->role === 'dosen'
                && $user->taughtCourses()
                    ->whereKey($course->getKey())
                    ->exists()
            );
    }

    /**
     * Hanya admin yang memiliki hak pengelolaan penuh.
     */
    public function manage(User $user, Course $course): bool
    {
        return $user->role === 'admin';
    }
}
