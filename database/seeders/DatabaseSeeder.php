<?php

namespace Database\Seeders;

use App\Models\Assignment;
use App\Models\Course;
use App\Models\Grade;
use App\Models\Material;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(DemoAccountSeeder::class);

        $admin = User::where('email', 'admin@kampuslms.test')->first();
        $demoLecturer = User::where('email', 'dosen@kampuslms.test')->first();
        $demoStudent = User::where('email', 'mahasiswa@kampuslms.test')->first();

        $lecturers = collect([$demoLecturer])->filter()->merge(
            User::factory(2)->dosen()->create()
        );

        $students = collect([$demoStudent])->filter()->merge(
            User::factory(29)->mahasiswa()->create()
        );

        $courses = collect();
        for ($courseNumber = 1; $courseNumber <= 5; $courseNumber++) {
            $lecturer = $lecturers[($courseNumber - 1) % $lecturers->count()];
            $course = Course::factory()->create([
                'code' => sprintf('SI2514%02d', $courseNumber),
                'name' => 'Mata Kuliah Demo '.$courseNumber,
                'description' => 'Mata kuliah contoh untuk demonstrasi KampusLMS.',
                'sks' => 3,
                'lecturer_id' => $lecturer->id,
                'status' => 'active',
            ]);

            // Mahasiswa pertama (mahasiswa@kampuslms.test) dan 15 mahasiswa lainnya terdaftar.
            // Sisa mahasiswa (termasuk mahasiswa2) sengaja TIDAK didaftarkan agar tes otorisasi 403 berhasil.
            $enrolledStudents = $students->take(16);

            $course->students()->attach(
                $enrolledStudents->mapWithKeys(fn (User $student) => [
                    $student->id => ['enrolled_at' => now()],
                ])->all()
            );

            Material::factory()->create([
                'course_id' => $course->id,
                'uploaded_by' => $lecturer->id,
                'description' => 'Materi pengantar '.$course->name,
            ]);

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
            ])->map(function (array $state, int $index) use ($course, $lecturer): Assignment {
                return Assignment::factory()->create([
                    'course_id' => $course->id,
                    'created_by' => $lecturer->id,
                    'title' => 'Tugas '.($index + 1).' - '.$course->name,
                    'instructions' => 'Kerjakan tugas sesuai materi perkuliahan.',
                    ...$state,
                ]);
            });

            // Dua tugas yang dipublikasikan masing-masing memiliki submission
            foreach ($assignments->take(2) as $assignmentIndex => $assignment) {
                foreach ($enrolledStudents->take(10) as $studentIndex => $student) {
                    $isLate = $assignmentIndex === 0 && $studentIndex % 5 === 0;
                    $submittedAt = $assignment->due_at->isPast()
                        ? ($isLate ? $assignment->due_at->copy()->addHour() : $assignment->due_at->copy()->subHour())
                        : Carbon::now()->subHours($studentIndex + 1);

                    $submission = $assignment->submissions()->create([
                        'user_id' => $student->id,
                        'file_path' => 'submissions/demo/'.$assignment->id.'/'.$student->id.'.pdf',
                        'original_name' => 'jawaban-'.$student->id.'.pdf',
                        'file_size' => 1024 + $studentIndex * 100,
                        'note' => 'Pengumpulan demo.',
                        'submitted_at' => $submittedAt,
                        'is_late' => $isLate,
                    ]);

                    if ($studentIndex < 6) {
                        Grade::factory()->create([
                            'submission_id' => $submission->id,
                            'graded_by' => $lecturer->id,
                            'score' => 70 + ($studentIndex % 31),
                            'feedback' => 'Pekerjaan sudah diperiksa.',
                            'graded_at' => now(),
                        ]);
                    }
                }
            }

            $courses->push($course);
        }
    }
}