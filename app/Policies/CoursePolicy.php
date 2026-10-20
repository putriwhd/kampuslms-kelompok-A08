<?php

namespace App\Policies;

use App\Models\Course;
use App\Models\User;

class CoursePolicy
{
    /**
     * Menentukan apakah pengguna dapat melihat daftar mata kuliah.
     */
    public function viewAny(User $user): bool
    {
        return in_array($user->role, ['admin', 'dosen', 'mahasiswa'], true);
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

    /**
     * Menentukan apakah pengguna dapat melihat daftar mahasiswa terdaftar.
     */
    public function viewEnrollments(User $user, Course $course): bool
    {
        return $user->role === 'admin'
            || ($user->role === 'dosen'
                && $user->taughtCourses()->whereKey($course->getKey())->exists());
    }

    /**
     * Menentukan apakah pengguna dapat melihat enrollment mahasiswa tertentu.
     */
    public function viewEnrollment(User $user, Course $course, User $student): bool
    {
        if (! $course->students()->whereKey($student->getKey())->exists()) {
            return false;
        }

        if ($user->role === 'admin') {
            return true;
        }

        if ($user->role === 'dosen') {
            return $user->taughtCourses()->whereKey($course->getKey())->exists();
        }

        return $user->role === 'mahasiswa'
            && $user->is($student);
    }

    /**
     * Menentukan apakah pengguna dapat menambah atau menghapus enrollment.
     */
    public function manageEnrollment(User $user, Course $course): bool
    {
        return $user->role === 'admin'
            || ($user->role === 'dosen'
                && $user->taughtCourses()->whereKey($course->getKey())->exists());
    }


    public function enroll(User $user, Course $course, User $student): bool
    {
        return $student->role === 'mahasiswa'
            && $this->manageEnrollment($user, $course)
            && ! $course->students()->whereKey($student->getKey())->exists();
    }

    /**
     * Menentukan apakah pengguna dapat mengeluarkan mahasiswa dari mata kuliah.
     */
    public function unenroll(User $user, Course $course, User $student): bool
    {
        return $student->role === 'mahasiswa'
            && $this->manageEnrollment($user, $course)
            && $course->students()->whereKey($student->getKey())->exists();
    }

   public function manageStudents(User $user, Course $course): bool
    {
        return $user->role === 'admin'
            || (
                $user->role === 'dosen'
                && $user->taughtCourses()
                    ->whereKey($course->getKey())
                    ->exists()
            );
    }
}
