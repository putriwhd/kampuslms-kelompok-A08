<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ForbiddenPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_forbidden_page_explains_denied_access_and_links_to_dashboard(): void
    {
        $lecturer = User::factory()->dosen()->create();

        $this->actingAs($lecturer)
            ->get('/admin/users')
            ->assertForbidden()
            ->assertSee('Anda tidak memiliki akses')
            ->assertSee('hubungi administrator')
            ->assertSee(route('dashboard'), false);
    }
}
