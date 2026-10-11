<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreMaterialRequest extends FormRequest
{
    public function authorize(): bool
    {
        return in_array($this->user()?->role, ['admin', 'dosen'], true);
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'type' => ['required', 'in:file,link'],
            'external_url' => [
                'required_if:type,link',
                'nullable',
                'url',
                'max:255',
            ],
            'file' => [
                'required_if:type,file',
                'nullable',
                'file',
                'max:10240',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'Judul materi wajib diisi.',
            'description.required' => 'Deskripsi materi wajib diisi.',
            'type.required' => 'Jenis materi wajib dipilih.',
            'external_url.required_if' => 'URL wajib diisi untuk materi berbentuk link.',
            'external_url.url' => 'Masukkan URL yang valid.',
            'file.required_if' => 'File materi wajib diunggah.',
            'file.max' => 'Ukuran file maksimal 10 MB.',
        ];
    }
}
