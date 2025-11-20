<?php

namespace App\Http\Requests\AcademicYear;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AcademicYearRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:255',
            ],
            'semester' => 'required|integer|in:1,2',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'active' => 'required|boolean',
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Nama tahun ajaran wajib diisi',
            'name.unique' => 'Nama tahun ajaran sudah ada',
            'semester.required' => 'Semester wajib diisi',
            'semester.in' => 'Semester harus 1 atau 2',
            'start_date.required' => 'Tanggal mulai wajib diisi',
            'end_date.required' => 'Tanggal selesai wajib diisi',
            'end_date.after' => 'Tanggal selesai harus setelah tanggal mulai',
            'active.required' => 'Status aktif wajib diisi',
        ];
    }
}
