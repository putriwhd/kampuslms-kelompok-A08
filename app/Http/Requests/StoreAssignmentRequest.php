<?php

namespace App\Http\Requests;

use App\Models\Assignment;
use App\Models\Course;
use Illuminate\Foundation\Http\FormRequest;

class StoreAssignmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();
        if (! $user || ! in_array($user->role, ['admin', 'dosen'], true)) {
            return false;
        }

        $course = $this->route('course');

        if (! $course instanceof Course && $this->filled('course_id')) {
            $course = Course::find($this->input('course_id'));
        }

        return ! ($course instanceof Course)
            || $user->can('create', [Assignment::class, $course]);
    }

    public function rules(): array
    {
        return [
            'course_id' => ['required', 'integer', 'exists:courses,id'],
            'title' => ['required', 'string', 'max:255'],
            'instructions' => ['required', 'string'],
            'due_at' => ['required', 'date'],
            'max_score' => ['sometimes', 'integer', 'min:1', 'max:100'],
            'allow_late' => ['sometimes', 'boolean'],
            'status' => ['sometimes', 'in:draft,published'],
        ];
    }

    public function messages(): array
    {
        return [
            'course_id.required' => 'Mata kuliah wajib dipilih.',
            'course_id.exists' => 'Mata kuliah tidak ditemukan.',
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