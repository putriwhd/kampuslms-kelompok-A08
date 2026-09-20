<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCourseRequest extends FormRequest
{
    public function authorize(): bool
    {
        // TODO: Ganti dengan otorisasi berbasis Policy pada Minggu 7
        return true;
    }

    public function rules(): array
    {
        return [
            'code' => [
                'required',
                'string',
                'max:20',
                Rule::unique('courses', 'code'),
            ],

            'name' => [
                'required',
                'string',
                'max:150',
            ],

            'description' => [
                'nullable',
                'string',
            ],

            'sks' => [
                'required',
                'integer',
                'between:1,6',
            ],

            'lecturer_id' => [
                'required',
                'integer',
                Rule::exists('users', 'id')
                    ->where(fn ($query) => $query->where('role', 'dosen')),
            ],

            'status' => [
                'required',
                Rule::in(['draft', 'active', 'archived']),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'code.required' => 'Kode mata kuliah wajib diisi.',
            'code.string'   => 'Kode mata kuliah harus berupa teks.',
            'code.max'      => 'Kode mata kuliah tidak boleh lebih dari :max karakter.',
            'code.unique'   => 'Kode mata kuliah sudah terdaftar. Gunakan kode lain.',

            'name.required' => 'Nama mata kuliah wajib diisi.',
            'name.string'   => 'Nama mata kuliah harus berupa teks.',
            'name.max'      => 'Nama mata kuliah tidak boleh lebih dari :max karakter.',

            'description.string' => 'Deskripsi mata kuliah harus berupa teks.',

            'sks.required' => 'Jumlah SKS wajib diisi.',
            'sks.integer'  => 'Jumlah SKS harus berupa angka bulat.',
            'sks.between'  => 'Jumlah SKS harus antara :min sampai :max.',

            'lecturer_id.required' => 'Dosen pengampu wajib dipilih.',
            'lecturer_id.integer'  => 'Format ID dosen pengampu tidak valid.',
            'lecturer_id.exists'   => 'Dosen pengampu yang dipilih tidak terdaftar atau bukan dosen.',

            'status.required' => 'Status mata kuliah wajib dipilih.',
            'status.in'       => 'Status mata kuliah harus draft, active, atau archived.',
        ];
    }
}