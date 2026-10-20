<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreSubmissionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->role === 'mahasiswa';
    }

    public function rules(): array
    {
        $requiresAssignmentId = ! $this->route('id') && ! $this->route('assignment');

        return [
            'assignment_id' => [
                $requiresAssignmentId ? 'required' : 'sometimes',
                'integer',
                'exists:assignments,id',
            ],
            'file' => [
                'required',
                'file',
                'mimes:pdf,doc,docx',
                'max:5120',
            ],
            'note' => ['nullable', 'string', 'max:5000'],
        ];
    }

    public function messages(): array
    {
        return [
            'assignment_id.required' => 'ID tugas wajib diisi.',
            'assignment_id.exists' => 'Tugas tidak ditemukan.',
            'file.required' => 'Berkas tugas wajib diunggah.',
            'file.file' => 'Berkas harus berupa file yang valid.',
            'file.mimes' => 'Berkas tugas harus berformat PDF, DOC, atau DOCX.',
            'file.max' => 'Ukuran berkas tugas maksimal 5MB.',
            'note.max' => 'Catatan maksimal 5000 karakter.',
        ];
    }
}
