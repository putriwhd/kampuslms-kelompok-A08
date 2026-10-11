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
use Illuminate\Routing\Route;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class RequestValidationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Helper untuk memvalidasi instance FormRequest secara presisi.
     */
    protected function validateRequest(
        FormRequest $request,
        array $data,
        mixed $routeEntity = null,
        string $paramName = 'id'
    ): \Illuminate\Validation\Validator {
        if ($routeEntity !== null) {
            $route = new Route('PUT', 'dummy/{' . $paramName . '}', []);
            $route->setParameter($paramName, $routeEntity);
            $request->setRouteResolver(fn () => $route);

            $idValue = is_object($routeEntity) ? $routeEntity->id : $routeEntity;
            $request->merge([$paramName => $routeEntity, 'id' => $idValue]);
        }

        return Validator::make($data, $request->rules(), $request->messages());
    }

    /*
    |--------------------------------------------------------------------------
    | StoreCourseRequest Tests
    |--------------------------------------------------------------------------
    */

    public function test_store_course_request_passes_with_valid_data(): void
    {
        $lecturer = User::factory()->dosen()->create();

        $data = [
            'code' => 'CS101',
            'name' => 'Algoritma & Struktur Data',
            'description' => 'Mata kuliah dasar algoritma.',
            'sks' => 3,
            'lecturer_id' => $lecturer->id,
            'status' => 'active',
        ];

        $validator = $this->validateRequest(new StoreCourseRequest(), $data);

        $this->assertTrue($validator->passes());
    }

    public function test_store_course_request_fails_if_code_is_duplicate(): void
    {
        $lecturer = User::factory()->dosen()->create();
        Course::factory()->create(['code' => 'CS101', 'lecturer_id' => $lecturer->id]);

        $data = [
            'code' => 'CS101',
            'name' => 'Pemrograman Baru',
            'sks' => 3,
            'lecturer_id' => $lecturer->id,
            'status' => 'active',
        ];

        $validator = $this->validateRequest(new StoreCourseRequest(), $data);

        $this->assertTrue($validator->fails());
        $this->assertContains(
            'Kode mata kuliah sudah terdaftar. Gunakan kode lain.',
            $validator->errors()->get('code')
        );
    }

    public function test_store_course_request_fails_if_sks_is_outside_1_to_6(): void
    {
        $lecturer = User::factory()->dosen()->create();

        $dataZero = [
            'code' => 'CS102',
            'name' => 'Kriptografi',
            'sks' => 0,
            'lecturer_id' => $lecturer->id,
            'status' => 'active',
        ];

        $validatorZero = $this->validateRequest(new StoreCourseRequest(), $dataZero);
        $this->assertTrue($validatorZero->fails());
        $this->assertContains(
            'Jumlah SKS harus antara 1 sampai 6.',
            $validatorZero->errors()->get('sks')
        );

        $dataSeven = [
            'code' => 'CS103',
            'name' => 'Kriptografi Lanjut',
            'sks' => 7,
            'lecturer_id' => $lecturer->id,
            'status' => 'active',
        ];

        $validatorSeven = $this->validateRequest(new StoreCourseRequest(), $dataSeven);
        $this->assertTrue($validatorSeven->fails());
        $this->assertContains(
            'Jumlah SKS harus antara 1 sampai 6.',
            $validatorSeven->errors()->get('sks')
        );
    }

    public function test_store_course_request_fails_if_lecturer_is_not_dosen(): void
    {
        $student = User::factory()->mahasiswa()->create();

        $data = [
            'code' => 'CS104',
            'name' => 'Basis Data',
            'sks' => 3,
            'lecturer_id' => $student->id,
            'status' => 'active',
        ];

        $validator = $this->validateRequest(new StoreCourseRequest(), $data);

        $this->assertTrue($validator->fails());
        $this->assertContains(
            'Dosen pengampu yang dipilih tidak terdaftar atau bukan dosen.',
            $validator->errors()->get('lecturer_id')
        );
    }

    public function test_store_course_request_fails_if_status_is_invalid(): void
    {
        $lecturer = User::factory()->dosen()->create();

        $data = [
            'code' => 'CS105',
            'name' => 'Jaringan Komputer',
            'sks' => 3,
            'lecturer_id' => $lecturer->id,
            'status' => 'superadmin',
        ];

        $validator = $this->validateRequest(new StoreCourseRequest(), $data);

        $this->assertTrue($validator->fails());
        $this->assertContains(
            'Status mata kuliah harus salah satu dari: draft, active, atau archived.',
            $validator->errors()->get('status')
        );
    }

    /*
    |--------------------------------------------------------------------------
    | UpdateCourseRequest Tests (Unique Ignore)
    |--------------------------------------------------------------------------
    */

    public function test_update_course_request_passes_when_keeping_the_same_code(): void
    {
        $lecturer = User::factory()->dosen()->create();
        $course = Course::factory()->create([
            'code' => 'CS201',
            'lecturer_id' => $lecturer->id,
        ]);

        $updateData = [
            'code' => 'CS201',
            'name' => 'Nama Kursus Baru',
            'sks' => 4,
            'lecturer_id' => $lecturer->id,
            'status' => 'active',
        ];

        $validator = $this->validateRequest(new UpdateCourseRequest(), $updateData, $course, 'course');

        $this->assertTrue($validator->passes());
    }

    public function test_update_course_request_fails_when_code_used_by_another_course(): void
    {
        $lecturer = User::factory()->dosen()->create();
        $courseA = Course::factory()->create([
            'code' => 'CS201',
            'lecturer_id' => $lecturer->id,
        ]);
        $courseB = Course::factory()->create([
            'code' => 'CS202',
            'lecturer_id' => $lecturer->id,
        ]);

        $updateData = [
            'code' => 'CS201',
            'name' => 'Course B mencoba pakai kode Course A',
            'sks' => 3,
            'lecturer_id' => $lecturer->id,
            'status' => 'active',
        ];

        $validator = $this->validateRequest(new UpdateCourseRequest(), $updateData, $courseB, 'course');

        $this->assertTrue($validator->fails());
        $this->assertContains(
            'Kode mata kuliah sudah digunakan oleh mata kuliah lain.',
            $validator->errors()->get('code')
        );
    }

    /*
    |--------------------------------------------------------------------------
    | UserStoreRequest Tests
    |--------------------------------------------------------------------------
    */

    public function test_user_store_request_passes_with_valid_data(): void
    {
        $data = [
            'name' => 'Budi Santoso',
            'email' => 'budi.santoso@kampuslms.test',
            'nim_nip' => 'MHS2026001',
            'password' => 'password123',
            'role' => 'mahasiswa',
        ];

        $validator = $this->validateRequest(new UserStoreRequest(), $data);

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

        $data = [
            'name' => 'User Baru',
            'email' => 'terdaftar@kampuslms.test',
            'nim_nip' => 'NIM99999',
            'password' => 'secret123',
            'role' => 'dosen',
        ];

        $validator = $this->validateRequest(new UserStoreRequest(), $data);

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
        $data = [
            'name' => 'Pengguna Pendek',
            'email' => 'pendek@kampuslms.test',
            'password' => '12345',
            'role' => 'mahasiswa',
        ];

        $validator = $this->validateRequest(new UserStoreRequest(), $data);

        $this->assertTrue($validator->fails());
        $this->assertContains(
            'Kata sandi minimal harus 6 karakter.',
            $validator->errors()->get('password')
        );
    }

    public function test_user_store_request_fails_if_role_is_invalid(): void
    {
        $data = [
            'name' => 'Role Liar',
            'email' => 'roleliar@kampuslms.test',
            'password' => 'validpassword',
            'role' => 'superadmin',
        ];

        $validator = $this->validateRequest(new UserStoreRequest(), $data);

        $this->assertTrue($validator->fails());
        $this->assertContains(
            'Peran pengguna harus salah satu dari: admin, dosen, atau mahasiswa.',
            $validator->errors()->get('role')
        );
    }

    /*
    |--------------------------------------------------------------------------
    | UserUpdateRequest Tests (Unique Ignore & Password Opsional)
    |--------------------------------------------------------------------------
    */

    public function test_user_update_request_passes_when_keeping_the_same_email_and_nim_nip(): void
    {
        $user = User::factory()->create([
            'email' => 'user.tetap@kampuslms.test',
            'nim_nip' => 'NIM88888',
        ]);

        $updateData = [
            'name' => 'Nama Baru Pengguna',
            'email' => 'user.tetap@kampuslms.test',
            'nim_nip' => 'NIM88888',
            'role' => 'dosen',
        ];

        $validator = $this->validateRequest(new UserUpdateRequest(), $updateData, $user, 'user');

        $this->assertTrue($validator->passes());
    }

    public function test_user_update_request_fails_when_email_or_nim_nip_used_by_another_user(): void
    {
        $userA = User::factory()->create([
            'email' => 'user.a@kampuslms.test',
            'nim_nip' => 'NIMA111',
        ]);
        $userB = User::factory()->create([
            'email' => 'user.b@kampuslms.test',
            'nim_nip' => 'NIMB222',
        ]);

        $updateData = [
            'name' => 'User B coba pakai punya User A',
            'email' => 'user.a@kampuslms.test',
            'nim_nip' => 'NIMA111',
            'role' => 'mahasiswa',
        ];

        $validator = $this->validateRequest(new UserUpdateRequest(), $updateData, $userB, 'user');

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

        $updateData = [
            'name' => 'Update Tanpa Password',
            'email' => 'update.nopass@kampuslms.test',
            'password' => null,
            'role' => 'mahasiswa',
        ];

        $validator = $this->validateRequest(new UserUpdateRequest(), $updateData, $user, 'user');

        $this->assertTrue($validator->passes());
    }

    public function test_user_update_request_fails_if_new_password_is_less_than_6_characters(): void
    {
        $user = User::factory()->create();

        $updateData = [
            'name' => 'Update Password Pendek',
            'email' => 'update.shortpass@kampuslms.test',
            'password' => 'abc',
            'role' => 'mahasiswa',
        ];

        $validator = $this->validateRequest(new UserUpdateRequest(), $updateData, $user, 'user');

        $this->assertTrue($validator->fails());
        $this->assertContains(
            'Kata sandi minimal harus 6 karakter.',
            $validator->errors()->get('password')
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Authorize Method Tests
    |--------------------------------------------------------------------------
    */

    public function test_all_form_requests_authorize_method_returns_true(): void
    {
        $this->assertTrue((new StoreCourseRequest())->authorize());
        $this->assertTrue((new UpdateCourseRequest())->authorize());
        $this->assertTrue((new UserStoreRequest())->authorize());
        $this->assertTrue((new UserUpdateRequest())->authorize());
    }
}
