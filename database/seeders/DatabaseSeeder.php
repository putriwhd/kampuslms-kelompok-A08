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

        $lecturers = collect([$demoLecturer, $secondDemoLecturer])
            ->filter()
            ->merge(
                User::factory(1)
                    ->dosen()
                    ->create()
            );

        $demoStudents = User::query()
            ->where('role', 'mahasiswa')
            ->whereIn('email', [
                'mahasiswa@kampuslms.test',
                'mahasiswa2@kampuslms.test',
            ])
            ->orderBy('id')
            ->get();

        $students = $demoStudents->merge(
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

                'description' => 'Mata kuliah contoh untuk demonstrasi KampusLMS.',

                'sks' => 3,

                'lecturer_id' => $lecturer->id,

                'status' => 'active',
            ]);

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

            $course->students()->syncWithoutDetaching($courseStudents);

            /*
             * Membuat materi.
             */
            Material::factory()->create([
                'course_id' => $course->id,

                'uploaded_by' => $lecturer->id,

                'description' => 'Materi pengantar '.$course->name,
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
                function (array $state, int $index) use ($course, $lecturer): Assignment {
                    return Assignment::factory()->create([
                        'course_id' => $course->id,
                        'created_by' => $lecturer->id,
                        'title' => 'Tugas '.($index + 1).' - '.$course->name,
                        'instructions' => 'Kerjakan tugas sesuai materi perkuliahan.',
                        ...$state,
                    ]);
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
                            'graded_by' => $lecturer->id,
                            'score' => fake()->numberBetween(70, 100),
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
