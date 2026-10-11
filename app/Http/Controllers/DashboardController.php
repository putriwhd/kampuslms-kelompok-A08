<?php

namespace App\Http\Controllers;

use App\Models\Assignment;
use App\Models\Course;
use App\Models\Grade;
use App\Models\Material;
use App\Models\Submission;
use App\Models\User;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        if (! $user) {
            return view('dashboard', [
                'role' => null,
                'stats' => [],
                'courses' => collect(),
            ]);
        }

        $role = $user->role;
        [$stats, $courses] = match ($role) {
            'admin' => [[
                'Pengguna' => User::query()->count(),
                'Mata Kuliah' => Course::query()->count(),
                'Materi' => Material::query()->count(),
                'Tugas' => Assignment::query()->count(),
                'Submission' => Submission::query()->count(),
                'Nilai' => Grade::query()->count(),
            ], collect()],
            'dosen' => $this->lecturerDashboard($user),
            'mahasiswa' => $this->studentDashboard($user),
            default => abort(403),
        };

        return view('dashboard', compact('role', 'stats', 'courses'));
    }

    private function lecturerDashboard(User $user): array
    {
        $courses = $user->taughtCourses()
            ->withCount(['materials', 'assignments'])
            ->orderBy('name')
            ->get();

        $stats = [
            'Mata Kuliah Diampu' => $courses->count(),
            'Materi' => Material::query()
                ->whereHas('course', fn ($query) => $query->where('lecturer_id', $user->getKey()))
                ->count(),
            'Tugas' => Assignment::query()
                ->whereHas('course', fn ($query) => $query->where('lecturer_id', $user->getKey()))
                ->count(),
            'Submission' => Submission::query()
                ->whereHas('assignment.course', fn ($query) => $query->where('lecturer_id', $user->getKey()))
                ->count(),
            'Nilai' => Grade::query()
                ->whereHas('submission.assignment.course', fn ($query) => $query->where('lecturer_id', $user->getKey()))
                ->count(),
        ];

        return [$stats, $courses];
    }

    private function studentDashboard(User $user): array
    {
        $courses = $user->courses()
            ->where('courses.status', '!=', 'draft')
            ->withCount([
                'materials',
                'assignments' => fn ($query) => $query->where('status', 'published'),
            ])
            ->orderBy('name')
            ->get();

        $stats = [
            'Mata Kuliah Diikuti' => $courses->count(),
            'Tugas Published' => Assignment::query()
                ->where('status', 'published')
                ->whereHas('course', function ($query) use ($user) {
                    $query->where('status', '!=', 'draft')
                        ->whereHas(
                            'students',
                            fn ($students) => $students->whereKey($user->getKey())
                        );
                })
                ->count(),
            'Submission Saya' => Submission::query()
                ->where('user_id', $user->getKey())
                ->count(),
            'Nilai Saya' => Grade::query()
                ->whereHas('submission', fn ($query) => $query->where('user_id', $user->getKey()))
                ->count(),
        ];

        return [$stats, $courses];
    }
}
