<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class NPlusOneQueryScalingTest extends TestCase
{
    use RefreshDatabase;

    public function test_catalog_query_count_is_flat_between_three_and_thirty_courses(): void
    {
        $student = User::factory()->mahasiswa()->create();

        $this->createCoursesWithLecturers(3);
        $smallDatasetQueryCount = $this->catalogQueryCount($student);

        $this->createCoursesWithLecturers(27, 3);
        $largeDatasetQueryCount = $this->catalogQueryCount($student);

        $this->assertSame(
            $smallDatasetQueryCount,
            $largeDatasetQueryCount,
            'Catalog query count should remain constant as the course dataset grows.'
        );
        $this->assertLessThan(
            15,
            $largeDatasetQueryCount,
            'The paginated catalog should stay below the query budget.'
        );
    }

    private function createCoursesWithLecturers(int $count, int $offset = 0): void
    {
        for ($index = $offset + 1; $index <= $offset + $count; $index++) {
            $lecturer = User::factory()->dosen()->create([
                'name' => "Query scaling lecturer {$index}",
                'email' => "query-scaling-lecturer-{$index}@example.test",
            ]);

            Course::query()->create([
                'code' => "QS{$index}",
                'name' => "Query scaling course {$index}",
                'description' => 'Course used to verify catalog query scaling.',
                'sks' => 3,
                'lecturer_id' => $lecturer->getKey(),
                'status' => 'active',
            ]);
        }
    }

    private function catalogQueryCount(User $student): int
    {
        $connection = DB::connection();
        $connection->enableQueryLog();
        $connection->flushQueryLog();

        try {
            $this->actingAs($student)
                ->get(route('mahasiswa.catalog.index'))
                ->assertOk();

            return count($connection->getQueryLog());
        } finally {
            $connection->disableQueryLog();
        }
    }
}
