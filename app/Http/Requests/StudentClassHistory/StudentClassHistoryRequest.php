<?php

namespace App\Http\Requests\StudentClassHistory;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StudentClassHistoryRequest extends FormRequest
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
        $isUpdate = $this->method() == 'PUT';

        $rules = [
            'class_id' => 'required|exists:classes,id',
            'student_id' => 'required|exists:students,id',
            'academic_year_id' => 'required|exists:academic_years,id',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ];

        return $rules;
    }

    /**
     * Get custom error messages for validation rules.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'class_id.required' => 'Kelas wajib diisi',
            'class_id.exists' => 'Kelas tidak ditemukan',
            'student_id.required' => 'Siswa wajib diisi',
            'student_id.exists' => 'Siswa tidak ditemukan',
            'academic_year_id.required' => 'Tahun ajaran wajib diisi',
            'academic_year_id.exists' => 'Tahun ajaran tidak ditemukan',
            'start_date.required' => 'Tanggal mulai wajib diisi',
            'start_date.date' => 'Format tanggal mulai tidak valid',
            'end_date.date' => 'Format tanggal selesai tidak valid',
            'end_date.after_or_equal' => 'Tanggal selesai harus setelah atau sama dengan tanggal mulai',
        ];
    }
}
