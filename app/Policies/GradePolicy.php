<?php

namespace App\Policies;

use App\Models\Grade;
use App\Models\Submission;
use App\Models\User;

/**
 * Kontrak ability (argumen selain User):
 * - viewAny(): tanpa argumen; admin, dosen, dan mahasiswa dapat meminta daftar
 *   yang wajib dibatasi ke nilai yang boleh mereka lihat.
 * - view(Grade $grade): admin, dosen pengampu, atau mahasiswa pemilik submission.
 * - create(?Submission $submission = null): hanya dosen pengampu dan submission
 *   wajib diberikan (mis. @can('create', [Grade::class, $submission])).
 * - update(Grade $grade), delete(Grade $grade), manage(Grade $grade):
 *   hanya dosen pengampu mata kuliah.
 *
 * Blade: @can('view', $grade),
 * @can('create', [\App\Models\Grade::class, $submission]),
 * @can('update', $grade), @can('delete', $grade), dan @can('manage', $grade).
 */
class GradePolicy
{
    /**
     * Menentukan apakah pengguna dapat meminta daftar nilai yang terscope.
     */
    public function viewAny(User $user): bool
    {
        return in_array($user->role, ['admin', 'dosen', 'mahasiswa'], true);
    }

    /**
     * Menentukan apakah pengguna dapat melihat nilai.
     * Aturan:
     * - Admin: diizinkan.
     * - Dosen pengampu: diizinkan.
     * - Mahasiswa: hanya mahasiswa pemilik submission yang dinilai.
     */
    public function view(User $user, Grade $grade): bool
    {
        if ($user->role === 'admin') {
            return true;
        }

        if ($user->role === 'dosen') {
            return $user->taughtCourses()
                ->whereHas('assignments.submissions.grades', fn ($query) => $query->whereKey($grade->getKey()))
                ->exists();
        }

        if ($user->role === 'mahasiswa') {
            return $grade->submission()->where('user_id', $user->getKey())->exists();
        }

        return false;
    }

    /**
     * Menentukan apakah pengguna dapat membuat/memberi nilai.
     * Aturan:
     * - Nilai HANYA dikelola oleh dosen pengampu mata kuliah terkait.
     */
    public function create(User $user, ?Submission $submission = null): bool
    {
        if ($user->role !== 'dosen') {
            return false;
        }

        if ($submission === null) {
            return false;
        }

        return $user->taughtCourses()
            ->whereHas('assignments.submissions', fn ($query) => $query->whereKey($submission->getKey()))
            ->exists();
    }

    /**
     * Menentukan apakah pengguna dapat memperbarui nilai.
     * Aturan:
     * - Nilai HANYA dikelola oleh dosen pengampu mata kuliah terkait.
     */
    public function update(User $user, Grade $grade): bool
    {
        return $user->role === 'dosen'
            && $user->taughtCourses()
                ->whereHas('assignments.submissions.grades', fn ($query) => $query->whereKey($grade->getKey()))
                ->exists();
    }

    /**
     * Menentukan apakah pengguna dapat menghapus nilai.
     * Aturan:
     * - Nilai HANYA dikelola oleh dosen pengampu mata kuliah terkait.
     */
    public function delete(User $user, Grade $grade): bool
    {
        return $user->role === 'dosen'
            && $user->taughtCourses()
                ->whereHas('assignments.submissions.grades', fn ($query) => $query->whereKey($grade->getKey()))
                ->exists();
    }

    /**
     * Menentukan apakah pengguna memiliki hak kelola atas nilai.
     */
    public function manage(User $user, Grade $grade): bool
    {
        return $user->role === 'dosen'
            && $user->taughtCourses()
                ->whereHas('assignments.submissions.grades', fn ($query) => $query->whereKey($grade->getKey()))
                ->exists();
    }
}
