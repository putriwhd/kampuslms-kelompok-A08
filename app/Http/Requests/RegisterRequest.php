<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() === null;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')],
            'nim_nip' => ['nullable', 'string', 'max:255', Rule::unique('users', 'nim_nip')],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ];
    }
}
