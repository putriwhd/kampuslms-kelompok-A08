<?php

namespace App\Http\Requests;

use App\Models\Assignment;
use Illuminate\Foundation\Http\FormRequest;

class UpdateAssignmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();
        if (! $user || ! in_array($user->role, ['admin', 'dosen'], true)) {
            return false;
        }

        $assignment = $this->route('assignment');

        if (! $assignment instanceof Assignment) {
            $assignment = Assignment::find($this->route('id'));
        }

        return ! ($assignment instanceof Assignment)
            || $user->can('update', $assignment);
    }

    public function rules(): array
    {
        return [
            'title' => ['sometimes', 'required', 'string', 'max:255'],
            'instructions' => ['sometimes', 'required', 'string'],
            'due_at' => ['sometimes', 'required', 'date'],
            'max_score' => ['sometimes', 'integer', 'min:1', 'max:100'],
            'allow_late' => ['sometimes', 'boolean'],
            'status' => ['sometimes', 'in:draft,published'],
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'Judul tugas wajib diisi.',
            'instructions.required' => 'Instruksi tugas wajib diisi.',
            'due_at.required' => 'Batas pengumpulan wajib diisi.',
            'due_at.date' => 'Format batas pengumpulan tidak valid.',
            'max_score.integer' => 'Nilai maksimal harus berupa angka bulat.',
            'max_score.min' => 'Nilai maksimal minimal 1.',
            'max_score.max' => 'Nilai maksimal maksimal 100.',
            'status.in' => 'Status tugas tidak valid.',
        ];
    }
}