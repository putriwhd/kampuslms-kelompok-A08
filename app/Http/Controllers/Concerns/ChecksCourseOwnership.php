<?php

namespace App\Http\Controllers\Concerns;

use App\Models\Course;
use Illuminate\Http\Request;

trait ChecksCourseOwnership
{
    protected function ensureCourseOwnership(Request $request, Course $course): void
    {
        $user = $request->user();
        $ownsCourse = match ($user?->role) {
            'admin' => true,
            'dosen' => (int) $course->lecturer_id === (int) $user->id,
            'mahasiswa' => $user->courses()->whereKey($course->getKey())->exists(),
            default => false,
        };

        abort_unless($ownsCourse, 403);
    }
}
