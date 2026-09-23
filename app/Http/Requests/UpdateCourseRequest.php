<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCourseRequest extends FormRequest
{
    public function authorize(): bool
    {
        // TODO: Ganti dengan otorisasi berbasis Policy pada Minggu 7
        return true;
    }

    public function rules(): array
    {
        $course = $this->route('course');
        $courseId = is_object($course) ? $course->id : $course;

        return [
            'code' => [
                'required',
                'string',
                'max:20',
                Rule::unique('courses', 'code')->ignore($courseId),
            ],
            'name'        => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'sks'         => ['required', 'integer', 'min:1', 'max:6'],
            'lecturer_id' => [
                'required',
                'integer',
                Rule::exists('users', 'id')->where(fn ($query) => $query->where('role', 'dosen')),
            ],
            'status'      => ['required', Rule::in(['draft', 'active', 'archived'])],
        ];
    }

    public function messages(): array
    {
        return [
            'code.required'        => 'Kode mata kuliah wajib diisi.',
            'code.string'          => 'Kode mata kuliah harus berupa teks.',
            'code.max'             => 'Kode mata kuliah tidak boleh lebih dari :max karakter.',
            'code.unique'          => 'Kode mata kuliah sudah digunakan oleh mata kuliah lain.',
            
            'name.required'        => 'Nama mata kuliah wajib diisi.',
            'name.string'          => 'Nama mata kuliah harus berupa teks.',
            'name.max'             => 'Nama mata kuliah tidak boleh lebih dari :max karakter.',
            
            'description.string'   => 'Deskripsi mata kuliah harus berupa teks.',
            
            'sks.required'         => 'Jumlah SKS wajib diisi.',
            'sks.integer'          => 'Jumlah SKS harus berupa angka bulat.',
            'sks.min'              => 'Jumlah SKS minimal adalah :min SKS.',
            'sks.max'              => 'Jumlah SKS maksimal adalah :max SKS.',
            
            'lecturer_id.required' => 'Dosen pengampu wajib dipilih.',
            'lecturer_id.integer'  => 'Format ID dosen pengampu tidak valid.',
            'lecturer_id.exists'   => 'Dosen pengampu yang dipilih tidak terdaftar sebagai dosen.',
            
            'status.required'      => 'Status mata kuliah wajib dipilih.',
            'status.in'            => 'Status mata kuliah harus salah satu dari: Draft, Active, atau Archived.',
        ];
    }
}