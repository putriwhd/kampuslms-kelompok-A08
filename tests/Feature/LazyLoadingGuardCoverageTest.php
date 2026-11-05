<?php

namespace Tests\Feature;

use App\Models\Assignment;
use App\Models\Course;
use App\Models\Grade;
use App\Models\Material;
use App\Models\Submission;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LazyLoadingGuardCoverageTest extends TestCase
{
    use RefreshDatabase;

    private bool $previousLazyLoadingState;

    private User $admin;

    private User $lecturer;

    private User $student;

    private Course $course;

    private Assignment $assignment;

    private Material $material;

    protected function setUp(): void
    {
        parent::setUp();

        $this->previousLazyLoadingState = Model::preventsLazyLoading();
        Model::preventLazyLoading(true);

        $this->admin = User::factory()->admin()->create();
        $this->lecturer = User::factory()->dosen()->create();
        $this->student = User::factory()->mahasiswa()->create();
        $this->course = Course::factory()
            ->for($this->lecturer, 'lecturer')
            ->create(['status' => 'active']);
        $this->course->students()->attach($this->student, [
            'enrolled_at' => now(),
        ]);

        $this->assignment = Assignment::factory()
            ->for($this->course)
            ->create([
                'created_by' => $this->lecturer->getKey(),
                'status' => 'published',
            ]);

        $submission = Submission::factory()
            ->for($this->assignment)
            ->create(['user_id' => $this->student->getKey()]);

        Grade::factory()->for($submission)->create([
            'graded_by' => $this->lecturer->getKey(),
        ]);

        $this->material = Material::query()->create([
            'course_id' => $this->course->getKey(),
            'uploaded_by' => $this->lecturer->getKey(),
            'title' => 'Coverage material',
            'description' => 'Material used by lazy-loading guard coverage.',
            'type' => 'link',
            'external_url' => 'https://example.test/material',
        ]);
    }

    protected function tearDown(): void
    {
        Model::preventLazyLoading($this->previousLazyLoadingState);

        parent::tearDown();
    }

    public function test_main_pages_render_without_lazy_loading_violations(): void
    {
        $this->assertPage($this->admin, route('dashboard'));
        $this->assertPage($this->lecturer, route('dashboard'));
        $this->assertPage($this->student, route('dashboard'));

        $this->assertPage($this->admin, route('admin.courses.index'));
        $this->assertPage($this->admin, route('admin.courses.create'));
        $this->assertPage($this->admin, route('admin.courses.show', $this->course));
        $this->assertPage($this->admin, route('admin.courses.edit', $this->course));
        $this->assertPage($this->admin, route('admin.users.index'));
        $this->assertPage($this->admin, route('admin.users.show', $this->student));
        $this->assertPage(
            $this->admin,
            route('admin.courses.materials.index', $this->course)
        );
        $this->assertPage(
            $this->admin,
            route('admin.courses.materials.scoped-show', [
                'course' => $this->course,
                'material' => $this->material,
            ])
        );
        $this->assertPage(
            $this->admin,
            route('admin.courses.assignments.index', $this->course)
        );
        $this->assertPage(
            $this->admin,
            route('admin.courses.assignments.scoped-show', [
                'course' => $this->course,
                'assignment' => $this->assignment,
            ])
        );

        $this->assertPage($this->lecturer, route('dosen.courses.index'));
        $this->assertPage($this->lecturer, route('dosen.courses.show', $this->course));
        $this->assertPage(
            $this->lecturer,
            route('dosen.courses.materials.index', $this->course)
        );
        $this->assertPage(
            $this->lecturer,
            route('dosen.courses.assignments.index', $this->course)
        );
        $this->assertPage(
            $this->lecturer,
            route('dosen.courses.assignments.scoped-show', [
                'course' => $this->course,
                'assignment' => $this->assignment,
            ])
        );
        $this->assertPage($this->lecturer, route('dosen.grade-recap.index'));
        $this->assertPage($this->lecturer, route('dosen.grade-recap.page'));

        $this->assertPage($this->student, route('mahasiswa.catalog.index'));
        $this->assertPage($this->student, route('mahasiswa.courses.index'));
        $this->assertPage($this->student, route('mahasiswa.courses.show', $this->course));
        $this->assertPage(
            $this->student,
            route('mahasiswa.courses.materials.index', $this->course)
        );
        $this->assertPage(
            $this->student,
            route('mahasiswa.courses.materials.scoped-show', [
                'course' => $this->course,
                'material' => $this->material,
            ])
        );
        $this->assertPage(
            $this->student,
            route('mahasiswa.courses.assignments.index', $this->course)
        );
        $this->assertPage(
            $this->student,
            route('mahasiswa.courses.assignments.scoped-show', [
                'course' => $this->course,
                'assignment' => $this->assignment,
            ])
        );
        $this->assertPage($this->student, route('mahasiswa.grade-recap.index'));
        $this->assertPage($this->student, route('mahasiswa.grade-recap.page'));
    }

    private function assertPage(User $user, string $url): void
    {
        $this->actingAs($user)
            ->get($url)
            ->assertOk();
    }
}
