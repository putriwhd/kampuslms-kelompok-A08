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

        $lecturers = User::query()
            ->where('role', 'dosen')
            ->whereIn('email', [
                'dosen@kampuslms.test',
                'dosen2@kampuslms.test',
                'dosen3@kampuslms.test',
            ])
            ->orderBy('id')
            ->get();

        $demoStudentEmails = [
            'mahasiswa@kampuslms.test',
            'mahasiswa2@kampuslms.test',
        ];
        foreach (range(3, 30) as $studentNumber) {
            $demoStudentEmails[] = sprintf('mahasiswa%02d@kampuslms.test', $studentNumber);
        }
        $demoStudents = User::query()
            ->where('role', 'mahasiswa')
            ->whereIn('email', $demoStudentEmails)
            ->orderBy('id')
            ->get();

        $students = $demoStudents;

        for ($courseNumber = 1; $courseNumber <= 5; $courseNumber++) {
            $lecturer = $lecturers[
                ($courseNumber - 1) % max(1, $lecturers->count())
            ] ?? null;

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

            $courseStudentSlice = $students->slice(($courseNumber - 1) * 6, 15);
            if ($courseStudentSlice->count() < 15) {
                $courseStudentSlice = $courseStudentSlice->concat(
                    $students->take(15 - $courseStudentSlice->count())
                );
            }
            $courseStudentSlice = $courseStudentSlice->values();

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
                    $assignment = Assignment::firstOrCreate(
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

                    if ($assignment->status !== $state['status']) {
                        $assignment->status = $state['status'];
                    }

                    if (
                        ($index === 0 && $assignment->due_at->isFuture())
                        || ($index === 1 && $assignment->due_at->isPast())
                    ) {
                        $assignment->due_at = $state['due_at'];
                    }

                    if ($assignment->isDirty()) {
                        $assignment->save();
                    }

                    return $assignment;
                }
            );

            $demoPdf = "%PDF-1.4\n1 0 obj<</Type/Catalog>>endobj\ntrailer<</Root 1 0 R>>\n%%EOF\n";

            foreach ($courseStudentSlice as $studentIndex => $student) {
                foreach ($assignments->values() as $assignmentIndex => $assignment) {
                    if ($assignment->status !== 'published') {
                        continue;
                    }

                    $studentId = $student->getKey();
                    $filePath = sprintf(
                        'submissions/demo/course-%d/assignment-%d/student-%d.pdf',
                        $course->id,
                        $assignment->id,
                        $studentId
                    );
                    Storage::disk('local')->put($filePath, $demoPdf);

                    $submission = Submission::firstOrCreate(
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
                            'is_late' => $assignment->due_at->isPast(),
                        ]
                    );

                    if (($studentIndex + $assignmentIndex + $courseNumber) % 5 < 3) {
                        Grade::firstOrCreate(
                            ['submission_id' => $submission->id],
                            [
                                'graded_by' => $lecturer?->id,
                                'score' => mt_rand(70, 100),
                                'feedback' => 'Nilai tugas contoh.',
                                'graded_at' => now(),
                            ]
                        );
                    }
                }
            }
        }
    }
}
