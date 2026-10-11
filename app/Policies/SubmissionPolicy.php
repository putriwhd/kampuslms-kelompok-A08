<?php

namespace App\Policies;

use App\Models\Assignment;
use App\Models\Submission;
use App\Models\User;

/**
 * Kontrak ability (argumen selain User):
 * - viewAny(): tanpa argumen; daftar submission.
 * - view(Submission $submission): admin/pengampu atau mahasiswa pemilik.
 * - create(Assignment $assignment): mahasiswa terdaftar untuk tugas published.
 * - update(Submission $submission): pemilik mahasiswa, tugas masih published,
 *   dan submission belum dinilai.
 * - delete(Submission $submission): admin atau dosen pengampu.
 * - grade(Submission $submission): hanya dosen pengampu.
 *
 * Blade: @can('view', $submission),
 * @can('create', [\App\Models\Submission::class, $assignment]),
 * @can('update', $submission), @can('delete', $submission), dan
 * @can('grade', $submission).
 */
class SubmissionPolicy
{
    /**
     * Menentukan apakah pengguna dapat melihat daftar submission.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Menentukan apakah pengguna dapat melihat detail submission.
     * Aturan:
     * - Admin: diizinkan.
     * - Dosen: hanya dosen pengampu dari mata kuliah tugas tersebut.
     * - Mahasiswa: hanya pemilik submission tersebut.
     */
    public function view(User $user, Submission $submission): bool
    {
        if ($user->role === 'admin') {
            return true;
        }

        if ($user->role === 'dosen') {
            return $user->taughtCourses()
                ->whereHas('assignments.submissions', fn ($query) => $query->whereKey($submission->getKey()))
                ->exists();
        }

        if ($user->role === 'mahasiswa') {
            return $submission->student()->whereKey($user->getKey())->exists();
        }

        return false;
    }

    /**
     * Menentukan apakah pengguna dapat membuat submission untuk suatu tugas.
     * Aturan:
     * - Submission HANYA dapat dibuat oleh mahasiswa terdaftar untuk tugas berstatus published.
     */
    public function create(User $user, Assignment $assignment): bool
    {
        if ($user->role !== 'mahasiswa') {
            return false;
        }

        if ($assignment->status !== 'published') {
            return false;
        }

        return $assignment->course()
            ->where('status', '!=', 'draft')
            ->whereHas('students', fn ($query) => $query->whereKey($user->getKey()))
            ->exists();
    }

    /**
     * Menentukan apakah pengguna dapat memperbarui file submission.
     * Aturan: Hanya mahasiswa pemilik submission yang belum dinilai dan tugas masih published.
     */
    public function update(User $user, Submission $submission): bool
    {
        if ($user->role !== 'mahasiswa') {
            return false;
        }

        if (! $submission->student()->whereKey($user->getKey())->exists()) {
            return false;
        }

        if ($submission->assignment->status !== 'published') {
            return false;
        }

        return ! $submission->grade()->exists();
    }

    /**
     * Menentukan apakah pengguna dapat menghapus submission.
     * Aturan:
     * - Penghapusan submission HANYA oleh admin atau dosen pengampu (mahasiswa tidak diizinkan).
     */
    public function delete(User $user, Submission $submission): bool
    {
        if ($user->role === 'admin') {
            return true;
        }

        if ($user->role === 'dosen') {
            return $user->taughtCourses()
                ->whereHas('assignments.submissions', fn ($query) => $query->whereKey($submission->getKey()))
                ->exists();
        }

        return false;
    }

    /**
     * Menentukan apakah pengguna dapat menilai submission.
     * Aturan:
     * - Nilai HANYA dikelola oleh dosen pengampu mata kuliah.
     */
    public function grade(User $user, Submission $submission): bool
    {
        return $user->role === 'dosen'
            && $user->taughtCourses()
                ->whereHas('assignments.submissions', fn ($query) => $query->whereKey($submission->getKey()))
                ->exists();
    }
}
