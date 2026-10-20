<?php

namespace Tests\Feature;

use App\Http\Requests\StoreCourseRequest;
use App\Http\Requests\UpdateCourseRequest;
use App\Http\Requests\UserStoreRequest;
use App\Http\Requests\UserUpdateRequest;
use App\Models\Course;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Routing\Route;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class RequestValidationTest extends TestCase
{
    use RefreshDatabase;

    protected function validateRequest(
        FormRequest $request,
        array $data,
        mixed $routeEntity = null,
        string $paramName = 'id'
    ): \Illuminate\Validation\Validator {
        if ($routeEntity !== null) {
            $idValue = is_object($routeEntity)
                ? $routeEntity->getKey()
                : $routeEntity;

            $route = new Route(
                'PUT',
                'dummy/{' . $paramName . '}',
                []
            );

            $route->bind(
                Request::create('/dummy/' . $idValue, 'PUT')
            );

            $route->setParameter($paramName, $routeEntity);
            $request->setRouteResolver(fn () => $route);

            $request->merge([
                $paramName => $routeEntity,
                'id' => $idValue,
            ]);
        }

        return Validator::make(
            $data,
            $request->rules(),
            $request->messages()
        );
    }

    public function test_store_course_request_passes_with_valid_data(): void
    {
        $lecturer = User::factory()->dosen()->create();

        $validator = $this->validateRequest(new StoreCourseRequest(), [
            'code' => 'CS101',
            'name' => 'Algoritma & Struktur Data',
            'description' => 'Mata kuliah dasar algoritma.',
            'sks' => 3,
            'lecturer_id' => $lecturer->id,
            'status' => 'active',
        ]);

        $this->assertTrue($validator->passes());
    }

    public function test_store_course_request_fails_if_code_is_duplicate(): void
    {
        $lecturer = User::factory()->dosen()->create();

        Course::factory()->create([
            'code' => 'CS101',
            'lecturer_id' => $lecturer->id,
        ]);

        $validator = $this->validateRequest(new StoreCourseRequest(), [
            'code' => 'CS101',
            'name' => 'Pemrograman Baru',
            'sks' => 3,
            'lecturer_id' => $lecturer->id,
            'status' => 'active',
        ]);

        $this->assertTrue($validator->fails());
        $this->assertContains(
            'Kode mata kuliah sudah terdaftar. Gunakan kode lain.',
            $validator->errors()->get('code')
        );
    }

    public function test_store_course_request_fails_if_sks_is_outside_1_to_6(): void
    {
        $lecturer = User::factory()->dosen()->create();

        foreach ([0, 7] as $sks) {
            $validator = $this->validateRequest(new StoreCourseRequest(), [
                'code' => 'CS' . (100 + $sks),
                'name' => 'Mata Kuliah',
                'sks' => $sks,
                'lecturer_id' => $lecturer->id,
                'status' => 'active',
            ]);

            $this->assertTrue($validator->fails());
            $this->assertContains(
                'Jumlah SKS harus antara 1 sampai 6.',
                $validator->errors()->get('sks')
            );
        }
    }

    public function test_store_course_request_fails_if_lecturer_is_not_dosen(): void
    {
        $student = User::factory()->mahasiswa()->create();

        $validator = $this->validateRequest(new StoreCourseRequest(), [
            'code' => 'CS104',
            'name' => 'Basis Data',
            'sks' => 3,
            'lecturer_id' => $student->id,
            'status' => 'active',
        ]);

        $this->assertTrue($validator->fails());
        $this->assertContains(
            'Dosen pengampu yang dipilih tidak terdaftar atau bukan dosen.',
            $validator->errors()->get('lecturer_id')
        );
    }

    public function test_store_course_request_fails_if_status_is_invalid(): void
    {
        $lecturer = User::factory()->dosen()->create();

        $validator = $this->validateRequest(new StoreCourseRequest(), [
            'code' => 'CS105',
            'name' => 'Jaringan Komputer',
            'sks' => 3,
            'lecturer_id' => $lecturer->id,
            'status' => 'superadmin',
        ]);

        $this->assertTrue($validator->fails());
        $this->assertContains(
            'Status mata kuliah harus salah satu dari: draft, active, atau archived.',
            $validator->errors()->get('status')
        );
    }

    public function test_update_course_request_passes_when_keeping_the_same_code(): void
    {
        $lecturer = User::factory()->dosen()->create();
        $course = Course::factory()->create([
            'code' => 'CS201',
            'lecturer_id' => $lecturer->id,
        ]);

        $validator = $this->validateRequest(new UpdateCourseRequest(), [
            'code' => 'CS201',
            'name' => 'Nama Kursus Baru',
            'sks' => 4,
            'lecturer_id' => $lecturer->id,
            'status' => 'active',
        ], $course, 'course');

        $this->assertTrue($validator->passes());
    }

    public function test_update_course_request_fails_when_code_used_by_another_course(): void
    {
        $lecturer = User::factory()->dosen()->create();

        Course::factory()->create([
            'code' => 'CS201',
            'lecturer_id' => $lecturer->id,
        ]);

        $courseB = Course::factory()->create([
            'code' => 'CS202',
            'lecturer_id' => $lecturer->id,
        ]);

        $validator = $this->validateRequest(new UpdateCourseRequest(), [
            'code' => 'CS201',
            'name' => 'Course B mencoba pakai kode Course A',
            'sks' => 3,
            'lecturer_id' => $lecturer->id,
            'status' => 'active',
        ], $courseB, 'course');

        $this->assertTrue($validator->fails());
        $this->assertContains(
            'Kode mata kuliah sudah digunakan oleh mata kuliah lain.',
            $validator->errors()->get('code')
        );
    }

    public function test_user_store_request_passes_with_valid_data(): void
    {
        $validator = $this->validateRequest(new UserStoreRequest(), [
            'name' => 'Budi Santoso',
            'email' => 'budi.santoso@kampuslms.test',
            'nim_nip' => 'MHS2026001',
            'password' => 'password123',
            'role' => 'mahasiswa',
        ]);

        $this->assertTrue($validator->passes());
    }

    public function test_user_store_request_fails_if_required_fields_are_missing(): void
    {
        $validator = $this->validateRequest(new UserStoreRequest(), []);

        $this->assertTrue($validator->fails());
        $this->assertContains('Nama pengguna wajib diisi.', $validator->errors()->get('name'));
        $this->assertContains('Alamat email wajib diisi.', $validator->errors()->get('email'));
        $this->assertContains('Kata sandi wajib diisi.', $validator->errors()->get('password'));
        $this->assertContains('Peran pengguna wajib dipilih.', $validator->errors()->get('role'));
    }

    public function test_user_store_request_fails_if_email_or_nim_nip_is_duplicate(): void
    {
        User::factory()->create([
            'email' => 'terdaftar@kampuslms.test',
            'nim_nip' => 'NIM99999',
        ]);

        $validator = $this->validateRequest(new UserStoreRequest(), [
            'name' => 'User Baru',
            'email' => 'terdaftar@kampuslms.test',
            'nim_nip' => 'NIM99999',
            'password' => 'secret123',
            'role' => 'dosen',
        ]);

        $this->assertTrue($validator->fails());
        $this->assertContains(
            'Alamat email sudah terdaftar. Gunakan email lain.',
            $validator->errors()->get('email')
        );
        $this->assertContains(
            'NIM/NIP sudah terdaftar. Gunakan NIM/NIP lain.',
            $validator->errors()->get('nim_nip')
        );
    }

    public function test_user_store_request_fails_if_password_is_less_than_6_characters(): void
    {
        $validator = $this->validateRequest(new UserStoreRequest(), [
            'name' => 'Pengguna Pendek',
            'email' => 'pendek@kampuslms.test',
            'password' => '12345',
            'role' => 'mahasiswa',
        ]);

        $this->assertTrue($validator->fails());
        $this->assertContains(
            'Kata sandi minimal harus 6 karakter.',
            $validator->errors()->get('password')
        );
    }

    public function test_user_store_request_fails_if_role_is_invalid(): void
    {
        $validator = $this->validateRequest(new UserStoreRequest(), [
            'name' => 'Role Liar',
            'email' => 'roleliar@kampuslms.test',
            'password' => 'validpassword',
            'role' => 'superadmin',
        ]);

        $this->assertTrue($validator->fails());
        $this->assertContains(
            'Peran pengguna harus salah satu dari: admin, dosen, atau mahasiswa.',
            $validator->errors()->get('role')
        );
    }

    public function test_user_update_request_passes_when_keeping_the_same_email_and_nim_nip(): void
    {
        $user = User::factory()->create([
            'email' => 'user.tetap@kampuslms.test',
            'nim_nip' => 'NIM88888',
        ]);

        $validator = $this->validateRequest(new UserUpdateRequest(), [
            'name' => 'Nama Baru Pengguna',
            'email' => 'user.tetap@kampuslms.test',
            'nim_nip' => 'NIM88888',
            'role' => 'dosen',
        ], $user, 'user');

        $this->assertTrue($validator->passes());
    }

    public function test_user_update_request_fails_when_email_or_nim_nip_used_by_another_user(): void
    {
        User::factory()->create([
            'email' => 'user.a@kampuslms.test',
            'nim_nip' => 'NIMA111',
        ]);

        $userB = User::factory()->create([
            'email' => 'user.b@kampuslms.test',
            'nim_nip' => 'NIMB222',
        ]);

        $validator = $this->validateRequest(new UserUpdateRequest(), [
            'name' => 'User B coba pakai punya User A',
            'email' => 'user.a@kampuslms.test',
            'nim_nip' => 'NIMA111',
            'role' => 'mahasiswa',
        ], $userB, 'user');

        $this->assertTrue($validator->fails());
        $this->assertContains(
            'Alamat email sudah digunakan oleh pengguna lain.',
            $validator->errors()->get('email')
        );
        $this->assertContains(
            'NIM/NIP sudah digunakan oleh pengguna lain.',
            $validator->errors()->get('nim_nip')
        );
    }

    public function test_user_update_request_allows_empty_or_nullable_password(): void
    {
        $user = User::factory()->create();

        $validator = $this->validateRequest(new UserUpdateRequest(), [
            'name' => 'Update Tanpa Password',
            'email' => 'update.nopass@kampuslms.test',
            'password' => null,
            'role' => 'mahasiswa',
        ], $user, 'user');

        $this->assertTrue($validator->passes());
    }

    public function test_user_update_request_fails_if_new_password_is_less_than_6_characters(): void
    {
        $user = User::factory()->create();

        $validator = $this->validateRequest(new UserUpdateRequest(), [
            'name' => 'Update Password Pendek',
            'email' => 'update.shortpass@kampuslms.test',
            'password' => 'abc',
            'role' => 'mahasiswa',
        ], $user, 'user');

        $this->assertTrue($validator->fails());
        $this->assertContains(
            'Kata sandi minimal harus 6 karakter.',
            $validator->errors()->get('password')
        );
    }

    public function test_course_form_requests_authorize_method_returns_true(): void
    {
        $admin = User::factory()->admin()->create();
        $course = Course::factory()->create();

        $this->actingAs($admin);

        $storeRequest = StoreCourseRequest::create('/courses', 'POST');
        $storeRequest->setContainer($this->app);
        $storeRequest->setRedirector($this->app['redirect']);
        $storeRequest->setUserResolver(fn () => $admin);

        $this->assertTrue($storeRequest->authorize());

        $updateRequest = UpdateCourseRequest::create('/courses/' . $course->id, 'PUT');
        $updateRequest->setContainer($this->app);
        $updateRequest->setRedirector($this->app['redirect']);
        $updateRequest->setUserResolver(fn () => $admin);

        $route = new Route('PUT', 'courses/{course}', []);
        $route->bind(Request::create('/courses/' . $course->id, 'PUT'));
        $route->setParameter('course', $course);
        $updateRequest->setRouteResolver(fn () => $route);

        $this->assertTrue($updateRequest->authorize());
    }

    public function test_user_update_request_denies_unauthenticated_access(): void
    {
        $this->assertFalse((new UserUpdateRequest())->authorize());
    }

    public function test_user_store_request_denies_unauthenticated_access(): void
    {
        $this->assertFalse((new UserStoreRequest())->authorize());
    }
}
