<?php

namespace Database\Seeders;

use App\Models\Assignment;
use App\Models\Course;
use App\Models\Grade;
use App\Models\Material;
use App\Models\Submission;
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
        // Seed angka acak native PHP agar hasil dapat direproduksi
        mt_srand(12345);

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

        $lecturers = collect([$demoLecturer, $secondDemoLecturer])
            ->filter();

        // Jika tidak ada user dosen bawaan dari factory, buat secara manual
        if ($lecturers->isEmpty()) {
            $lecturers = User::where('role', 'dosen')->get();
        }

        $demoStudents = User::query()
            ->where('role', 'mahasiswa')
            ->whereIn('email', [
                'mahasiswa@kampuslms.test',
                'mahasiswa2@kampuslms.test',
            ])
            ->orderBy('id')
            ->get();

        $students = $demoStudents;

        $courses = collect();

        for ($courseNumber = 1; $courseNumber <= 5; $courseNumber++) {
            $lecturer = $lecturers[
                ($courseNumber - 1) % max(1, $lecturers->count())
            ] ?? $demoLecturer;

            $course = Course::firstOrCreate(
                ['code' => sprintf('SI2514%02d', $courseNumber)],
                [
                    'name' => 'Mata Kuliah Demo '.$courseNumber,
                    'description' => 'Mata kuliah contoh untuk demonstrasi KampusLMS.',
                    'sks' => 3,
                    'lecturer_id' => $lecturer ? $lecturer->id : null,
                    'status' => 'active',
                ]
            );

            $courseStudentSlice = $students->slice(($courseNumber - 1) * 6, 6);
            if ($courseNumber === 1) {
                $courseStudentSlice = $courseStudentSlice
                    ->merge($demoStudents)
                    ->unique('id');
            }

            $courseStudents = $courseStudentSlice
                ->mapWithKeys(fn (User $student) => [
                    $student->getKey() => ['enrolled_at' => now()],
                ]);

            if ($courseStudents->isNotEmpty()) {
                $course->students()->syncWithoutDetaching($courseStudents);
            }

            /*
             * Membuat materi.
             */
            Material::firstOrCreate(
                [
                    'course_id' => $course->id,
                    'description' => 'Materi pengantar '.$course->name,
                ],
                [
                    'title' => 'Materi 1 - '.$course->name,
                    'uploaded_by' => $lecturer ? $lecturer->id : null,
                ]
            );

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
                function (array $state, int $index) use ($course, $lecturer): Assignment {
                    return Assignment::firstOrCreate(
                        [
                            'course_id' => $course->id,
                            'title' => 'Tugas '.($index + 1).' - '.$course->name,
                        ],
                        [
                            'created_by' => $lecturer ? $lecturer->id : null,
                            'instructions' => 'Kerjakan tugas sesuai materi perkuliahan.',
                            ...$state,
                        ]
                    );
                }
            );

            $demoPdf = "%PDF-1.4\n1 0 obj<</Type/Catalog>>endobj\ntrailer<</Root 1 0 R>>\n%%EOF\n";

            foreach ($courseStudents as $studentId => $_pivot) {
                foreach ($assignments as $assignment) {
                    $filePath = sprintf(
                        'submissions/demo/course-%d/assignment-%d/student-%d.pdf',
                        $course->id,
                        $assignment->id,
                        $studentId
                    );
                    Storage::disk('local')->put($filePath, $demoPdf);

                    $submission = Submission::updateOrCreate(
                        [
                            'assignment_id' => $assignment->id,
                            'user_id' => $studentId,
                        ],
                        [
                            'file_path' => $filePath,
                            'original_name' => 'tugas-demo.pdf',
                            'file_size' => strlen($demoPdf),
                            'note' => 'Pengumpulan tugas contoh.',
                            'submitted_at' => now(),
                            'is_late' => false,
                        ]
                    );

                    Grade::updateOrCreate(
                        ['submission_id' => $submission->id],
                        [
                            'graded_by' => $lecturer ? $lecturer->id : null,
                            'score' => mt_rand(70, 100), // Menggunakan mt_rand bawaan PHP
                            'feedback' => 'Nilai tugas contoh.',
                            'graded_at' => now(),
                        ]
                    );
                }
            }

            $courses->push($course);
        }
    }
}