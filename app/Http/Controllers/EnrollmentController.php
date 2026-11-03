<?php

namespace App\Http\Controllers;

use App\Events\EnrollmentApproved;
use App\Events\EnrollmentRequested;
use App\Models\Course;
use App\Models\EnrollmentRequest;
use App\Models\User;
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

        $maxSks = self::MAX_SKS;
        $maxCapacity = self::MAX_CAPACITY;

        return view('courses.catalog', compact(
            'courses',
            'currentSks',
            'enrolledCourseIds',
            'pendingCourseIds',
            'rejectedCourseIds',
            'maxSks',
            'maxCapacity'
        ));
    }

    /**
     * Ajukan pendaftaran mandiri mahasiswa pada suatu mata kuliah.
     */
    public function requestEnrollment(Request $request, Course $course)
    {
        $user = $request->user();

        abort_unless($course->status === 'active', 400, 'Mata kuliah tidak aktif untuk pendaftaran.');

        // 1. Cek apakah sudah terdaftar
        if ($course->students()->whereKey($user->id)->exists()) {
            return back()->with('error', 'Anda sudah terdaftar di mata kuliah ini.');
        }

        if ($course->students()->exists()) {
            return back()->with(
                'error',
                'Pendaftaran mandiri hanya tersedia untuk kelas yang belum memiliki mahasiswa terdaftar. Hubungi admin atau dosen untuk pendaftaran langsung.'
            );
        }

        // 2. Cek apakah sudah mengajukan dan statusnya masih pending
        $isPending = EnrollmentRequest::where('course_id', $course->id)
            ->where('user_id', $user->id)
            ->where('status', 'pending')
            ->exists();

        if ($isPending) {
            return back()->with('error', 'Permintaan pendaftaran Anda sedang menunggu persetujuan dosen.');
        }

        // 3. Batasan Kapasitas Kelas (Maksimal 25 Mahasiswa)
        $currentStudentsCount = $course->students()->count();
        if ($currentStudentsCount >= self::MAX_CAPACITY) {
            return back()->with('error', 'Kelas ini sudah penuh (kapasitas maksimal '.self::MAX_CAPACITY.' mahasiswa).');
        }

        // 4. Batasan Total SKS (Maksimal 24 SKS)
        $currentSks = $this->currentSks($user);
        if (($currentSks + $course->sks) > self::MAX_SKS) {
            return back()->with('error', 'Pendaftaran ditolak karena total SKS Anda akan melebihi batas maksimal '.self::MAX_SKS.' SKS.');
        }

        // Simpan / perbarui permintaan pendaftaran
        $enrollmentRequest = EnrollmentRequest::updateOrCreate(
            ['course_id' => $course->id, 'user_id' => $user->id],
            ['status' => 'pending']
        );

        // Picu Event untuk notifikasi asinkron ke dosen
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
            $course = Course::query()
                ->lockForUpdate()
                ->findOrFail($enrollmentRequest->course_id);
            $student = User::query()
                ->lockForUpdate()
                ->findOrFail($enrollmentRequest->user_id);
            $lockedRequest = EnrollmentRequest::query()
                ->lockForUpdate()
                ->findOrFail($enrollmentRequest->id);

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
        $course = $enrollmentRequest->course;

        // Otorisasi: Hanya dosen pengampu yang berhak menolak
        abort_unless($course->lecturer_id === $request->user()->id, 403, 'Anda bukan dosen pengampu mata kuliah ini.');

        $enrollmentRequest->update(['status' => 'rejected']);

        return back()->with('success', 'Permintaan pendaftaran mahasiswa '.$enrollmentRequest->student->name.' telah ditolak.');
    }

    private function currentSks(User $student): int
    {
        return (int) $student->courses()
            ->where('courses.status', 'active')
            ->sum('courses.sks');
    }
}
