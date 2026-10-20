<?php

namespace App\Http\Requests;

use App\Models\Material;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

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
        $material = $this->route('material');

        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'type' => ['required', 'in:file,link'],
            'file' => [
                'nullable',
                'file',
                'mimes:pdf,pptx,docx',
                'max:20480',
                Rule::requiredIf(
                    fn (): bool => $this->input('type') === 'file'
                        && (
                            ! $material instanceof Material
                            || $material->type !== 'file'
                            || ! $material->file_path
                        )
                ),
            ],
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
            'file.required' => 'Berkas PDF, PPTX, atau DOCX maksimal 20 MB wajib diunggah untuk jenis berkas.',
            'file.mimes' => 'Format berkas harus berupa PDF, PPTX, atau DOCX.',
            'file.max' => 'Ukuran berkas materi tidak boleh melebihi 20 MB.',
            'external_url.required_if' => 'URL wajib diisi untuk materi berbentuk link.',
            'external_url.url' => 'Masukkan URL yang valid.',
        ];
    }
}
