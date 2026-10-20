<?php

namespace App\Policies;

use App\Models\Assignment;
use App\Models\Course;
use App\Models\User;

/**
 * Kontrak ability (argumen selain User):
 * - viewAny(): tanpa argumen; daftar tugas.
 * - view(Assignment $assignment): mahasiswa hanya untuk tugas published di course
 *   yang diikuti; draft hanya admin dan dosen pengampu.
 * - create(?Course $course = null): admin tanpa konteks; dosen wajib memberi course
 *   yang diampu (mis. @can('create', [Assignment::class, $course])).
 * - update(Assignment $assignment), delete(Assignment $assignment): pengelolaan tugas.
 * - viewSubmissions(Assignment $assignment): daftar submission untuk admin/pengampu.
 *
 * Blade: @can('view', $assignment),
 * @can('create', [\App\Models\Assignment::class, $course]),
 * @can('update', $assignment), @can('delete', $assignment), dan
 * @can('viewSubmissions', $assignment).
 */
class AssignmentPolicy
{
    /**
     * Menentukan apakah pengguna dapat melihat daftar tugas.
     */
    public function viewAny(User $user): bool
    {
        return in_array($user->role, ['admin', 'dosen', 'mahasiswa'], true);
    }

    /**
     * Menentukan apakah pengguna dapat melihat detail tugas.
     * Aturan:
     * - Admin: semua tugas.
     * - Dosen: hanya dosen pengampu mata kuliah terkait (bisa melihat draft & published).
     * - Mahasiswa: hanya tugas dengan status 'published' pada mata kuliah yang diikutinya (draft hanya untuk admin & dosen pengampu).
     */
    public function view(User $user, Assignment $assignment): bool
    {
        $course = $assignment->course;

        if ($user->role === 'admin') {
            return true;
        }

        if ($user->role === 'dosen') {
            return $user->taughtCourses()->whereKey($course->getKey())->exists();
        }

        if ($user->role === 'mahasiswa') {
            // Draft hanya untuk admin dan dosen pengampu
            if ($assignment->status === 'draft' || $course->status === 'draft') {
                return false;
            }

            return $course->students()->whereKey($user->id)->exists();
        }

        return false;
    }

    /**
     * Menentukan apakah pengguna dapat membuat tugas baru.
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
     * Menentukan apakah pengguna dapat memperbarui tugas.
     * Aturan: Admin atau dosen pengampu mata kuliah terkait.
     */
    public function update(User $user, Assignment $assignment): bool
    {
        if ($user->role === 'admin') {
            return true;
        }

        if ($user->role === 'dosen') {
            return $user->taughtCourses()
                ->whereHas('assignments', fn ($query) => $query->whereKey($assignment->getKey()))
                ->exists();
        }

        return false;
    }

    /**
     * Menentukan apakah pengguna dapat menghapus tugas.
     * Aturan: Admin atau dosen pengampu mata kuliah terkait.
     */
    public function delete(User $user, Assignment $assignment): bool
    {
        if ($user->role === 'admin') {
            return true;
        }

        if ($user->role === 'dosen') {
            return $user->taughtCourses()
                ->whereHas('assignments', fn ($query) => $query->whereKey($assignment->getKey()))
                ->exists();
        }

        return false;
    }

    /**
     * Menentukan apakah pengguna dapat melihat daftar submission tugas.
     * Aturan: Admin atau dosen pengampu mata kuliah terkait.
     */
    public function viewSubmissions(User $user, Assignment $assignment): bool
    {
        if ($user->role === 'admin') {
            return true;
        }

        if ($user->role === 'dosen') {
            return $user->taughtCourses()
                ->whereHas('assignments', fn ($query) => $query->whereKey($assignment->getKey()))
                ->exists();
        }

        return false;
    }
}
