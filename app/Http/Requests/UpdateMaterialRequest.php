<?php

namespace App\Http\Requests;

use App\Models\Material;
use Illuminate\Foundation\Http\FormRequest;

class UpdateMaterialRequest extends FormRequest
{
    public function authorize(): bool
    {
        $material = $this->route('material');

        return $material instanceof Material
            && ($this->user()?->can('update', $material) ?? false);
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
        ];
    }
}
