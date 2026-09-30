<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class RoleRouteTest extends TestCase
{
    public function test_query_parameter_cannot_grant_access_to_a_role_route(): void
    {
        $this->get('/admin/users?as=admin')
            ->assertForbidden()
            ->assertSee('403 — Akses Ditolak');

        $this->get('/dosen/courses?as=dosen')
            ->assertForbidden()
            ->assertSee('403 — Akses Ditolak');

        $this->get('/mahasiswa/courses?as=mahasiswa')
            ->assertForbidden()
            ->assertSee('403 — Akses Ditolak');
    }

    public function test_authenticated_user_must_have_the_route_role(): void
    {
        Route::get('/_test/admin-role', fn () => response('allowed'))
            ->middleware('role:admin');

        $this->actingAs(User::factory()->make(['role' => 'admin']))
            ->get('/_test/admin-role?as=dosen')
            ->assertOk()
            ->assertSeeText('allowed');

        $this->actingAs(User::factory()->make(['role' => 'dosen']))
            ->get('/_test/admin-role?as=admin')
            ->assertForbidden()
            ->assertSee('403 — Akses Ditolak');
    }

    public function test_role_groups_have_prefixed_route_names(): void
    {
        $routes = app('router')->getRoutes();

        $adminCourses = $routes->getByName('admin.courses.index');
        $dosenCourses = $routes->getByName('dosen.courses.index');
        $mahasiswaCourses = $routes->getByName('mahasiswa.courses.index');
        $this->assertNotNull($routes->getByName('admin.users.index'));

        $this->assertSame('admin/courses', $adminCourses->uri());
        $this->assertSame('dosen/courses', $dosenCourses->uri());
        $this->assertSame('mahasiswa/courses', $mahasiswaCourses->uri());
        $this->assertContains('role:admin', $adminCourses->middleware());
        $this->assertContains('role:dosen', $dosenCourses->middleware());
        $this->assertContains('role:mahasiswa', $mahasiswaCourses->middleware());

        $nestedMaterialRoute = $routes->getByName('admin.courses.materials.scoped-show');
        $this->assertTrue($nestedMaterialRoute->enforcesScopedBindings());
        $this->assertSame('admin/materials/{material}', $routes->getByName('admin.materials.show')->uri());

        $nestedAssignmentRoute = $routes->getByName('admin.courses.assignments.scoped-show');
        $this->assertTrue($nestedAssignmentRoute->enforcesScopedBindings());
        $this->assertSame(
            'admin/assignments/{assignment}',
            $routes->getByName('admin.assignments.show')->uri()
        );
    }
}
