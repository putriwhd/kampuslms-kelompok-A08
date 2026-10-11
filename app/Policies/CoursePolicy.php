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
 * - viewEnrollments(Course $course): admin atau dosen pengampu; daftar anggota.
 * - viewEnrollment(Course $course, User $student): admin, dosen pengampu,
 *   atau mahasiswa tersebut untuk enrollment miliknya sendiri.
 * - manageEnrollment(Course $course): admin atau dosen pengampu.
 * - enroll(Course $course, User $student): admin atau dosen pengampu dapat
 *   mendaftarkan pengguna ber-role mahasiswa.
 * - unenroll(Course $course, User $student): admin atau dosen pengampu dapat
 *   mengeluarkan mahasiswa yang terdaftar.
 *
 * Blade: @can('view', $course), @can('create', \App\Models\Course::class),
 * @can('manage', $course), @can('viewDraft', $course),
 * @can('viewEnrollments', $course),
 * @can('viewEnrollment', [$course, $student]),
 * @can('enroll', [$course, $student]), dan
 * @can('unenroll', [$course, $student]).
 */
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

    /**
     * Menentukan apakah pengguna dapat mendaftarkan mahasiswa ke mata kuliah.
     */
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
}
