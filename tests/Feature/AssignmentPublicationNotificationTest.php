<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\Material;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AssignmentPublicationNotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_publishing_an_assignment_notifies_only_enrolled_students_and_links_to_its_detail(): void
    {
        $lecturer = User::factory()->dosen()->create();
        $student = User::factory()->mahasiswa()->create();
        $otherStudent = User::factory()->mahasiswa()->create();
        $course = Course::factory()->for($lecturer, 'lecturer')->create([
            'status' => 'active',
        ]);
        $course->students()->attach($student, ['enrolled_at' => now()]);

        $this->actingAs($lecturer)
            ->post(route('dosen.courses.assignments.store', $course), [
                'course_id' => $course->getKey(),
                'title' => 'Tugas Notifikasi',
                'instructions' => 'Kerjakan soal yang diberikan.',
                'due_at' => now()->addDays(3)->toDateTimeString(),
                'status' => 'published',
            ])
            ->assertRedirect();

        $assignment = $course->assignments()->firstOrFail();
        $notification = $student->notifications()->firstOrFail();

        $this->assertSame('Tugas Notifikasi', $notification->data['title']);
        $this->assertSame(
            route('mahasiswa.assignments.show', $assignment, false),
            $notification->data['url']
        );
        $this->assertDatabaseCount('notifications', 1);

        $this->actingAs($student)
            ->get($notification->data['url'])
            ->assertOk()
            ->assertSeeText('Tugas Notifikasi')
            ->assertSeeText('Kerjakan soal yang diberikan.');

        $this->assertCount(0, $otherStudent->notifications);
    }

    public function test_assignment_detail_page_renders_for_admin_and_course_lecturer(): void
    {
        $admin = User::factory()->admin()->create();
        $lecturer = User::factory()->dosen()->create();
        $course = Course::factory()->for($lecturer, 'lecturer')->create();
        $assignment = $course->assignments()->create([
            'created_by' => $lecturer->getKey(),
            'title' => 'Tugas Detail',
            'instructions' => 'Instruksi untuk pengujian.',
            'due_at' => now()->addDays(2),
            'max_score' => 100,
            'allow_late' => true,
            'status' => 'published',
        ]);

        $this->actingAs($admin)
            ->get(route('admin.courses.assignments.scoped-show', [
                'course' => $course,
                'assignment' => $assignment,
            ]))
            ->assertOk()
            ->assertSeeText('Instruksi untuk pengujian.')
            ->assertSeeText('Lihat Rekap Pengumpulan');

        $this->actingAs($lecturer)
            ->get(route('dosen.courses.assignments.scoped-show', [
                'course' => $course,
                'assignment' => $assignment,
            ]))
            ->assertOk()
            ->assertSeeText('Instruksi untuk pengujian.');
    }

    public function test_lecturer_can_upload_and_download_a_private_course_material(): void
    {
        Storage::fake('local');
        $lecturer = User::factory()->dosen()->create();
        $course = Course::factory()->for($lecturer, 'lecturer')->create([
            'status' => 'active',
        ]);

        $this->actingAs($lecturer)
            ->post(route('dosen.courses.materials.store', $course), [
                'title' => 'Handout Mingguan',
                'description' => 'Berkas materi untuk pengujian.',
                'type' => 'file',
                'file' => UploadedFile::fake()->create('handout.pdf', 128, 'application/pdf'),
            ])
            ->assertRedirect(route('dosen.courses.materials.index', [
                'course' => $course,
                'as' => 'dosen',
            ]));

        $material = Material::query()->firstOrFail();
        Storage::disk('local')->assertExists($material->file_path);

        $this->get(route('materials.download', $material))
            ->assertOk()
            ->assertDownload('handout.pdf');
    }
}
