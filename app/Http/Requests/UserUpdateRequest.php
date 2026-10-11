<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UserUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        // TODO: Ganti dengan otorisasi berbasis Policy pada Minggu 7
        return true;
    }

    public function rules(): array
    {
        $user = null;
        try {
            $user = $this->route('user');
        } catch (\Throwable) {
            // Fallback jika request diuji di luar HTTP pipeline (misalnya di Tinker/Unit Test)
        }

        $user = $user ?? $this->user ?? $this->id;
        $userId = is_object($user) ? $user->id : $user;

        return [
            'name' => [
                'required',
                'string',
                'max:255',
            ],

            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($userId),
            ],

            'nim_nip' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique('users', 'nim_nip')->ignore($userId),
            ],

            'password' => [
                'nullable',
                'string',
                'min:6',
            ],

            'role' => [
                'required',
                Rule::in(['admin', 'dosen', 'mahasiswa']),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Nama pengguna wajib diisi.',
            'name.string'   => 'Nama pengguna harus berupa teks.',
            'name.max'      => 'Nama pengguna tidak boleh lebih dari :max karakter.',

            'email.required' => 'Alamat email wajib diisi.',
            'email.string'   => 'Alamat email harus berupa teks.',
            'email.email'    => 'Format alamat email tidak valid.',
            'email.max'      => 'Alamat email tidak boleh lebih dari :max karakter.',
            'email.unique'   => 'Alamat email sudah digunakan oleh pengguna lain.',

            'nim_nip.string' => 'NIM/NIP harus berupa teks.',
            'nim_nip.max'    => 'NIM/NIP tidak boleh lebih dari :max karakter.',
            'nim_nip.unique' => 'NIM/NIP sudah digunakan oleh pengguna lain.',

            'password.string' => 'Kata sandi harus berupa teks.',
            'password.min'    => 'Kata sandi minimal harus :min karakter.',

            'role.required' => 'Peran pengguna wajib dipilih.',
            'role.in'       => 'Peran pengguna harus salah satu dari: admin, dosen, atau mahasiswa.',
        ];
    }
}
