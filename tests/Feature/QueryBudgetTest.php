<?php

namespace Tests\Feature;

use App\Models\Assignment;
use App\Models\Course;
use App\Models\Grade;
use App\Models\Submission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class QueryBudgetTest extends TestCase
{
    use RefreshDatabase;

    public function test_lecturer_page_uses_fewer_than_fifteen_queries(): void
    {
        [$lecturer] = $this->createRecapData();

        $this->assertUnderQueryBudget(function () use ($lecturer): void {
            $this->actingAs($lecturer)
                ->get(route('dosen.grade-recap.page'))
                ->assertOk();
        });
    }

    public function test_student_page_uses_fewer_than_fifteen_queries(): void
    {
        [, $student] = $this->createRecapData();

        $this->assertUnderQueryBudget(function () use ($student): void {
            $this->actingAs($student)
                ->get(route('mahasiswa.grade-recap.page'))
                ->assertOk();
        });
    }

    public function test_lecturer_api_uses_fewer_than_fifteen_queries_and_is_paginated(): void
    {
        [$lecturer] = $this->createRecapData();

        for ($index = 2; $index <= 11; $index++) {
            Course::query()->create([
                'code' => "QB{$index}",
                'name' => "Query budget course {$index}",
                'description' => 'Course used to verify recap pagination.',
                'sks' => 3,
                'lecturer_id' => $lecturer->getKey(),
                'status' => 'active',
            ]);
        }

        $this->assertUnderQueryBudget(function () use ($lecturer): void {
            $this->actingAs($lecturer, 'sanctum')
                ->getJson('/api/v1/grade-recap/lecturer')
                ->assertOk()
                ->assertJsonCount(10, 'data')
                ->assertJsonPath('meta.per_page', 10)
                ->assertJsonPath('meta.total', 11)
                ->assertJsonPath('meta.current_page', 1);
        });
    }

    public function test_student_api_uses_fewer_than_fifteen_queries(): void
    {
        [, $student] = $this->createRecapData();
        $lecturer = User::factory()->dosen()->create();

        for ($index = 2; $index <= 11; $index++) {
            $course = Course::query()->create([
                'code' => "ST{$index}",
                'name' => "Student recap course {$index}",
                'description' => 'Course used to verify recap pagination.',
                'sks' => 3,
                'lecturer_id' => $lecturer->getKey(),
                'status' => 'active',
            ]);
            $course->students()->attach($student, ['enrolled_at' => now()]);
        }

        $this->assertUnderQueryBudget(function () use ($student): void {
            $this->actingAs($student, 'sanctum')
                ->getJson('/api/v1/grade-recap/student')
                ->assertOk()
                ->assertJsonCount(10, 'data')
                ->assertJsonPath('meta.per_page', 10)
                ->assertJsonPath('meta.total', 11)
                ->assertJsonPath('meta.current_page', 1);
        });
    }

    public function test_assignment_detail_uses_fewer_than_fifteen_queries(): void
    {
        $lecturer = User::factory()->dosen()->create();
        $course = Course::factory()->for($lecturer, 'lecturer')->create();
        $assignment = Assignment::factory()->for($course)->create([
            'created_by' => $lecturer->getKey(),
            'status' => 'published',
        ]);
        $students = User::factory()->mahasiswa()->count(25)->create();
        $course->students()->attach(
            $students->pluck('id')->mapWithKeys(
                fn (int $studentId) => [$studentId => ['enrolled_at' => now()]]
            )->all()
        );
        foreach ($students as $student) {
            Submission::factory()->for($assignment)->create([
                'user_id' => $student->getKey(),
            ]);
        }

        $this->assertUnderQueryBudget(function () use ($lecturer, $course, $assignment): void {
            $this->actingAs($lecturer)
                ->get(route('dosen.courses.assignments.scoped-show', [
                    'course' => $course,
                    'assignment' => $assignment,
                ]))
                ->assertOk();
        });
    }

    public function test_assignment_submission_recap_uses_fewer_than_fifteen_queries(): void
    {
        $lecturer = User::factory()->dosen()->create();
        $course = Course::factory()->for($lecturer, 'lecturer')->create();
        $assignment = Assignment::factory()->for($course)->create([
            'created_by' => $lecturer->getKey(),
            'status' => 'published',
        ]);
        $students = User::factory()->mahasiswa()->count(25)->create();
        $course->students()->attach(
            $students->pluck('id')->mapWithKeys(
                fn (int $studentId) => [$studentId => ['enrolled_at' => now()]]
            )->all()
        );
        foreach ($students as $student) {
            Submission::factory()->for($assignment)->create([
                'user_id' => $student->getKey(),
            ]);
        }

        $this->assertUnderQueryBudget(function () use ($lecturer, $course, $assignment): void {
            $this->actingAs($lecturer)
                ->get(route('dosen.courses.assignments.submissions.index', [
                    'course' => $course,
                    'assignment' => $assignment,
                ]))
                ->assertOk();
        });
    }

    /**
     * @return array{User, User}
     */
    private function createRecapData(): array
    {
        $lecturer = User::factory()->dosen()->create();
        $student = User::factory()->mahasiswa()->create();
        $course = Course::factory()->for($lecturer, 'lecturer')->create();
        $course->students()->attach($student, ['enrolled_at' => now()]);

        $assignment = Assignment::factory()->for($course)->create([
            'created_by' => $lecturer->getKey(),
            'status' => 'published',
        ]);
        $submission = Submission::factory()->for($assignment)->create([
            'user_id' => $student->getKey(),
        ]);

        Grade::factory()->for($submission)->create([
            'graded_by' => $lecturer->getKey(),
        ]);

        return [$lecturer, $student];
    }

    private function assertUnderQueryBudget(callable $request): void
    {
        $queryCount = 0;
        DB::listen(function () use (&$queryCount): void {
            $queryCount++;
        });

        $request();

        $this->assertLessThan(
            15,
            $queryCount,
            "Expected fewer than 15 queries, but the request ran {$queryCount}."
        );
    }
}
