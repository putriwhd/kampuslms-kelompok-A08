<?php

namespace App\Http\Controllers;

use App\Events\EnrollmentApproved;
use App\Events\EnrollmentRequested;
use App\Models\Course;
use App\Models\EnrollmentRequest;
use App\Models\User;
use App\Notifications\EnrollmentRejectedNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EnrollmentController extends Controller
{
    public const MAX_SKS = 24;

    public const MAX_CAPACITY = 25;

    /**
     * Tampilkan daftar kelas aktif untuk pendaftaran mandiri mahasiswa.
     */
    public function catalog(Request $request)
    {
        $user = $request->user();

        $currentSks = $this->currentSks($user);

        $query = Course::query()
            ->where('status', 'active')
            ->with('lecturer')
            ->withCount('students');

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%");
            });
        }

        $courses = $query->orderBy('name')->paginate(12)->withQueryString();

        // Ambil status pendaftaran mahasiswa pada mata kuliah yang ditampilkan
        $enrolledCourseIds = $user->courses()->pluck('courses.id')->all();

        $pendingCourseIds = EnrollmentRequest::query()
            ->where('user_id', $user->id)
            ->where('status', 'pending')
            ->pluck('course_id')
            ->all();

        $rejectedCourseIds = EnrollmentRequest::query()
            ->where('user_id', $user->id)
            ->where('status', 'rejected')
            ->pluck('course_id')
            ->all();

        $enrollmentRequests = EnrollmentRequest::query()
            ->where('user_id', $user->id)
            ->where('status', 'rejected')
            ->pluck('rejection_reason', 'course_id');

        $maxSks = self::MAX_SKS;
        $maxCapacity = self::MAX_CAPACITY;

        return view('courses.catalog', compact(
            'courses',
            'currentSks',
            'enrolledCourseIds',
            'pendingCourseIds',
            'rejectedCourseIds',
            'enrollmentRequests',
            'maxSks',
            'maxCapacity'
        ));
    }

    /**
     * Return the names and NIM/NIP of students enrolled in a course.
     */
    public function students(Course $course)
    {
        $students = $course->students()
            ->get(['users.name', 'users.nim_nip'])
            ->map(fn (User $student): array => [
                'name' => $student->name,
                'nim_nip' => $student->nim_nip,
            ]);

        return response()->json($students);
    }

    /**
     * Ajukan pendaftaran mandiri mahasiswa pada suatu mata kuliah.
     */
    public function requestEnrollment(Request $request, Course $course)
    {
        $user = $request->user();
        $userId = auth()->id();

        abort_unless($course->status === 'active', 400, 'Mata kuliah tidak aktif untuk pendaftaran.');

        if ($course->students()->whereKey($userId)->exists()) {
            return back()->with('error', 'Anda sudah terdaftar di mata kuliah ini.');
        }

        $enrollmentRequest = EnrollmentRequest::query()
            ->where('course_id', $course->id)
            ->where('user_id', $userId)
            ->first();

        if ($enrollmentRequest?->status === 'pending') {
            return back()->with('error', 'Permintaan pendaftaran Anda sedang menunggu persetujuan dosen.');
        }

        if ($enrollmentRequest?->status === 'approved') {
            return back()->with('error', 'Anda sudah disetujui untuk mata kuliah ini.');
        }

        $currentStudentsCount = $course->students()->count();
        if ($currentStudentsCount >= self::MAX_CAPACITY) {
            return back()->with('error', 'Kelas ini sudah penuh (kapasitas maksimal '.self::MAX_CAPACITY.' mahasiswa).');
        }

        $currentSks = $this->currentSks($user);
        if (($currentSks + $course->sks) > self::MAX_SKS) {
            return back()->with('error', 'Pendaftaran ditolak karena total SKS Anda akan melebihi batas maksimal '.self::MAX_SKS.' SKS.');
        }

        if ($enrollmentRequest) {
            $enrollmentRequest->update([
                'status' => 'pending',
                'rejection_reason' => null,
            ]);
        } else {
            $enrollmentRequest = EnrollmentRequest::create([
                'course_id' => $course->id,
                'user_id' => $userId,
                'status' => 'pending',
            ]);
        }

        $enrollmentRequest->refresh();
        EnrollmentRequested::dispatch($enrollmentRequest);

        return back()->with('success', 'Permintaan pendaftaran pada mata kuliah '.$course->name.' berhasil diajukan. Menunggu persetujuan dosen.');
    }

    /**
     * Dosen menyetujui pendaftaran mahasiswa.
     */
    public function approve(Request $request, EnrollmentRequest $enrollmentRequest)
    {
        $course = $enrollmentRequest->course;

        // Otorisasi: Hanya dosen pengampu yang berhak menyetujui
        abort_unless($course->lecturer_id === $request->user()->id, 403, 'Anda bukan dosen pengampu mata kuliah ini.');

        $error = DB::transaction(function () use ($request, $enrollmentRequest) {
            $lockedRequest = EnrollmentRequest::query()
                ->lockForUpdate()
                ->findOrFail($enrollmentRequest->id);
            $course = Course::query()
                ->lockForUpdate()
                ->findOrFail($lockedRequest->course_id);
            $student = User::query()
                ->lockForUpdate()
                ->findOrFail($lockedRequest->user_id);

            abort_unless(
                $course->lecturer_id === $request->user()->id,
                403,
                'Anda bukan dosen pengampu mata kuliah ini.'
            );

            if ($lockedRequest->status !== 'pending') {
                return 'Permintaan pendaftaran ini sudah diproses.';
            }

            if ($course->students()->whereKey($student->id)->exists()) {
                return 'Mahasiswa sudah terdaftar di kelas ini.';
            }

            if ($course->students()->count() >= self::MAX_CAPACITY) {
                return 'Kelas sudah mencapai kapasitas maksimal '.self::MAX_CAPACITY.' mahasiswa.';
            }

            $currentSks = $this->currentSks($student);

            if (($currentSks + $course->sks) > self::MAX_SKS) {
                return 'Persetujuan ditolak karena total SKS mahasiswa akan melebihi batas maksimal '.self::MAX_SKS.' SKS.';
            }

            $lockedRequest->update(['status' => 'approved']);
            $course->students()->attach($student->id, ['enrolled_at' => now()]);

            return null;
        });

        if ($error !== null) {
            return back()->with('error', $error);
        }

        DashboardController::invalidateCache();

        // Picu Event untuk notifikasi asinkron ke mahasiswa
        $enrollmentRequest->refresh();
        EnrollmentApproved::dispatch($enrollmentRequest);

        return back()->with('success', 'Mahasiswa '.$enrollmentRequest->student->name.' berhasil disetujui masuk ke kelas.');
    }

    /**
     * Dosen menolak pendaftaran mahasiswa.
     */
    public function reject(Request $request, EnrollmentRequest $enrollmentRequest)
    {
        $validated = $request->validate([
            'rejection_reason' => ['required', 'string', 'max:1000'],
        ]);

        $studentId = DB::transaction(function () use ($request, $enrollmentRequest, $validated) {
            $lockedRequest = EnrollmentRequest::query()
                ->lockForUpdate()
                ->findOrFail($enrollmentRequest->id);
            $course = Course::query()->findOrFail($lockedRequest->course_id);

            abort_unless(
                $course->lecturer_id === $request->user()->id,
                403,
                'Anda bukan dosen pengampu mata kuliah ini.'
            );

            if ($lockedRequest->status !== 'pending') {
                return null;
            }

            $lockedRequest->update([
                'status' => 'rejected',
                'rejection_reason' => $validated['rejection_reason'],
            ]);

            return $lockedRequest->user_id;
        });

        if ($studentId === null) {
            return back()->with('error', 'Permintaan pendaftaran ini sudah diproses.');
        }

        $enrollmentRequest->refresh()->load('course');
        User::query()->findOrFail($studentId)
            ->notify(new EnrollmentRejectedNotification($enrollmentRequest));

        return back()->with('success', 'Permintaan pendaftaran mahasiswa '.$enrollmentRequest->student->name.' telah ditolak.');
    }

    private function currentSks(User $student): int
    {
        return (int) $student->courses()
            ->where('courses.status', 'active')
            ->sum('courses.sks');
    }
}
