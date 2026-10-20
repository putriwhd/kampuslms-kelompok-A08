<?php

namespace Database\Seeders;

use App\Models\Assignment;
use App\Models\Course;
use App\Models\Grade;
use App\Models\Material;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        /*
         * Membuat hasil Faker dapat direproduksi.
         * Semua anggota yang menggunakan kode Seeder yang sama
         * akan mendapatkan urutan data Faker yang sama.
         */
        fake()->seed(12345);

        $this->call(DemoAccountSeeder::class);

        $admin = User::where('email', 'admin@kampuslms.test')->first();

        $demoLecturer = User::where(
            'email',
            'dosen@kampuslms.test'
        )->first();

        $secondDemoLecturer = User::where(
            'email',
            'dosen2@kampuslms.test'
        )->first();

        $demoStudent = User::where(
            'email',
            'mahasiswa@kampuslms.test'
        )->first();

        $lecturers = collect([$demoLecturer, $secondDemoLecturer])
            ->filter()
            ->merge(
                User::factory(1)
                    ->dosen()
                    ->create()
            );

        $students = collect([$demoStudent])
            ->filter()
            ->merge(
                User::factory(29)
                    ->mahasiswa()
                    ->create()
            );

        $courses = collect();

        for ($courseNumber = 1; $courseNumber <= 5; $courseNumber++) {
            $lecturer = $lecturers[
                ($courseNumber - 1) % $lecturers->count()
            ];

            $course = Course::factory()->create([
                'code' => sprintf(
                    'SI2514%02d',
                    $courseNumber
                ),

                'name' => 'Mata Kuliah Demo '.$courseNumber,

                'description' =>
                    'Mata kuliah contoh untuk demonstrasi KampusLMS.',

                'sks' => 3,

                'lecturer_id' => $lecturer->id,

                'status' => 'active',
            ]);

            /*
             * 16 mahasiswa pertama didaftarkan ke mata kuliah.
             * Mahasiswa lainnya sengaja tidak didaftarkan
             * untuk kebutuhan pengujian akses 403.
             */
            $enrolledStudents = $students->take(16);

            $course->students()->attach(
                $enrolledStudents
                    ->mapWithKeys(
                        fn (User $student) => [
                            $student->id => [
                                'enrolled_at' => now(),
                            ],
                        ]
                    )
                    ->all()
            );

            /*
             * Membuat materi.
             */
            Material::factory()->create([
                'course_id' => $course->id,

                'uploaded_by' => $lecturer->id,

                'description' =>
                    'Materi pengantar '.$course->name,
            ]);

            /*
             * Membuat 3 tugas.
             */
            $assignments = collect([
                [
                    'due_at' => Carbon::now()->subDays(7),
                    'status' => 'published',
                ],
                [
                    'due_at' => Carbon::now()->addDays(7),
                    'status' => 'published',
                ],
                [
                    'due_at' => Carbon::now()->addDays(21),
                    'status' => 'draft',
                ],
            ])->map(
                function (
                    array $state,
                    int $index
                ) use (
                    $course,
                    $lecturer
                ): Assignment {
                    return Assignment::factory()->create([
                        'course_id' => $course->id,

                        'created_by' => $lecturer->id,

                        'title' =>
                            'Tugas '.($index + 1).' - '.$course->name,

                        'instructions' =>
                            'Kerjakan tugas sesuai materi perkuliahan.',

                        ...$state,
                    ]);
                }
            );

            /*
             * Dua tugas yang dipublikasikan
             * memiliki submission.
             */
            foreach (
                $assignments->take(2)
                as $assignmentIndex => $assignment
            ) {
                foreach (
                    $enrolledStudents->take(10)
                    as $studentIndex => $student
                ) {
                    $isLate =
                        $assignmentIndex === 0
                        && $studentIndex % 5 === 0;

                    $submittedAt = $assignment->due_at->isPast()
                        ? (
                            $isLate
                                ? $assignment->due_at
                                    ->copy()
                                    ->addHour()
                                : $assignment->due_at
                                    ->copy()
                                    ->subHour()
                        )
                        : Carbon::now()
                            ->subHours($studentIndex + 1);

                    $submission =
                        $assignment->submissions()->create([
                            'user_id' => $student->id,

                            'file_path' =>
                                'submissions/demo/'
                                .$assignment->id
                                .'/'
                                .$student->id
                                .'.pdf',

                            'original_name' =>
                                'jawaban-'
                                .$student->id
                                .'.pdf',

                            'file_size' =>
                                1024 + ($studentIndex * 100),

                            'note' => 'Pengumpulan demo.',

                            'submitted_at' => $submittedAt,

                            'is_late' => $isLate,
                        ]);

                    Storage::disk('local')->put(
                        $submission->file_path,
                        $this->demoSubmissionPdf()
                    );

                    /*
                     * Enam submission pertama
                     * diberi nilai.
                     */
                    if ($studentIndex < 6) {
                        Grade::factory()->create([
                            'submission_id' =>
                                $submission->id,

                            'graded_by' =>
                                $lecturer->id,

                            'score' =>
                                70 + ($studentIndex % 31),

                            'feedback' =>
                                'Pekerjaan sudah diperiksa.',

                            'graded_at' => now(),
                        ]);
                    }
                }
            }

            $courses->push($course);
        }
    }

    private function demoSubmissionPdf(): string
    {
        $stream = "BT /F1 18 Tf 72 720 Td (Demo submission KampusLMS) Tj ET\n";
        $objects = [
            1 => '<< /Type /Catalog /Pages 2 0 R >>',
            2 => '<< /Type /Pages /Kids [3 0 R] /Count 1 >>',
            3 => '<< /Type /Page /Parent 2 0 R /MediaBox [0 0 612 792] /Resources << /Font << /F1 4 0 R >> >> /Contents 5 0 R >>',
            4 => '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>',
            5 => '<< /Length '.strlen($stream)." >>\nstream\n".$stream.'endstream',
        ];

        $pdf = "%PDF-1.4\n";
        $offsets = [0];

        foreach ($objects as $number => $object) {
            $offsets[$number] = strlen($pdf);
            $pdf .= $number." 0 obj\n".$object."\nendobj\n";
        }

        $crossReferenceOffset = strlen($pdf);
        $pdf .= "xref\n0 6\n0000000000 65535 f \n";

        for ($number = 1; $number <= 5; $number++) {
            $pdf .= sprintf("%010d 00000 n \n", $offsets[$number]);
        }

        return $pdf
            ."trailer\n<< /Size 6 /Root 1 0 R >>\n"
            ."startxref\n".$crossReferenceOffset."\n%%EOF\n";
    }
}