<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class RoleRouteTest extends TestCase
{
    public function test_query_parameter_cannot_grant_access_to_role_route(): void
    {
        $response = $this->get('/admin/courses?as=admin');

        $this->assertTrue(
            in_array($response->status(), [302, 401, 403, 404])
        );
    }

    public function test_authenticated_user_must_have_route_role(): void
    {
        $mahasiswa = User::factory()->make([
            'role' => 'mahasiswa',
        ]);

        $this->actingAs($mahasiswa);

        $response = $this->get('/admin/courses');

        $this->assertTrue(
            in_array($response->status(), [302, 403, 404])
        );
    }

    public function test_role_groups_have_prefixed_route_names(): void
    {
        $this->assertTrue(Route::has('admin.courses.index'));
        $this->assertTrue(Route::has('dosen.courses.index'));
        $this->assertTrue(Route::has('mahasiswa.courses.index'));
    }

    public function test_admin_navigation_shows_users_and_admin_course_links(): void
    {
        $admin = User::factory()->make([
            'role' => 'admin',
        ]);

        $this->actingAs($admin)
            ->get('/')
            ->assertOk()
            ->assertSee('/admin/courses?as=admin', false)
            ->assertSee('/admin/users?as=admin', false);
    }

    public function test_dosen_navigation_links_to_dosen_courses_and_hides_users_menu(): void
    {
        $dosen = User::factory()->make([
            'role' => 'dosen',
        ]);

        $this->actingAs($dosen)
            ->get('/')
            ->assertOk()
            ->assertSee('/dosen/courses?as=dosen', false)
            ->assertDontSee('/admin/users?as=admin', false);
    }

    public function test_mahasiswa_navigation_links_to_mahasiswa_courses_and_hides_users_menu(): void
    {
        $mahasiswa = User::factory()->make([
            'role' => 'mahasiswa',
        ]);

        $this->actingAs($mahasiswa)
            ->get('/')
            ->assertOk()
            ->assertSee('/mahasiswa/courses?as=mahasiswa', false)
            ->assertDontSee('/admin/users?as=admin', false);
    }

    public function test_unauthenticated_navigation_defaults_to_mahasiswa(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('/mahasiswa/courses?as=mahasiswa', false);
    }

    public function test_guest_dashboard_does_not_appear_logged_in(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSeeText(
                'Silakan login untuk mengakses dashboard sesuai role Anda.'
            )
            ->assertDontSeeText('Akses sebagai Mahasiswa');
    }

    public function test_admin_dashboard_uses_authenticated_user_role(): void
    {
        $admin = User::factory()->make([
            'role' => 'admin',
        ]);

        $this->actingAs($admin)
            ->get('/?as=mahasiswa')
            ->assertOk()
            ->assertSeeText('Selamat datang, Admin')
            ->assertSeeText('Akses sebagai Admin')
            ->assertDontSeeText('Akses sebagai Mahasiswa');
    }

    public function test_dosen_dashboard_uses_authenticated_user_role(): void
    {
        $dosen = User::factory()->make([
            'role' => 'dosen',
        ]);

        $this->actingAs($dosen)
            ->get('/?as=admin')
            ->assertOk()
            ->assertSeeText('Selamat datang, Dosen')
            ->assertSeeText('Akses sebagai Dosen')
            ->assertDontSeeText('Akses sebagai Admin');
    }

    public function test_mahasiswa_dashboard_uses_authenticated_user_role(): void
    {
        $mahasiswa = User::factory()->make([
            'role' => 'mahasiswa',
        ]);

        $this->actingAs($mahasiswa)
            ->get('/?as=admin')
            ->assertOk()
            ->assertSeeText('Selamat datang, Mahasiswa')
            ->assertSeeText('Akses sebagai Mahasiswa')
            ->assertDontSeeText('Akses sebagai Admin');
    }
}
