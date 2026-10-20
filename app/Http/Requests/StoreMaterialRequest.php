<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreMaterialRequest extends FormRequest
{
    /**
     * Tentukan apakah pengguna diizinkan membuat permintaan ini.
     * Otorisasi kepemilikan kelas diperiksa di Controller via ensureCourseOwnership.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Aturan validasi materi kuliah: berkas (PDF/PPTX/DOCX max 20MB) atau link eksternal.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'type' => ['required', 'in:file,link'],

            // Validasi jika tipe adalah 'file' (Maksimal 20 MB = 20.480 KB, format PDF/PPTX/DOCX)
            'file' => [
                'required_if:type,file',
                'nullable',
                'file',
                'mimes:pdf,pptx,docx',
                'max:20480', // 20 MB dalam kilobyte
            ],

            // Validasi jika tipe adalah 'link' (Tautan URL valid)
            'external_url' => [
                'required_if:type,link',
                'nullable',
                'url',
                'max:255',
            ],
        ];
    }

    /**
     * Pesan validasi kustom dalam bahasa Indonesia.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'title.required' => 'Judul materi wajib diisi.',
            'description.required' => 'Deskripsi materi wajib diisi.',
            'type.required' => 'Pilih jenis materi (berkas atau tautan).',
            'file.required_if' => 'Berkas materi wajib diunggah jika jenis materi adalah berkas.',
            'file.file' => 'Unggahan berkas materi tidak valid.',
            'file.mimes' => 'Format berkas harus berupa PDF, PPTX, atau DOCX.',
            'file.max' => 'Ukuran berkas materi tidak boleh melebihi 20 MB (20480 KB).',
            'external_url.required_if' => 'Tautan eksternal wajib diisi jika jenis materi adalah tautan.',
            'external_url.url' => 'Format tautan eksternal tidak valid.',
        ];
    }
}
