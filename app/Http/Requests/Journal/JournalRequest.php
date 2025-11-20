<?php

namespace App\Http\Requests\Journal;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class JournalRequest extends FormRequest
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
            'teacher_id' => 'required|exists:users,id',
            'class_id' => 'required|exists:classes,id',
            'academic_year_id' => 'required|exists:academic_years,id',
            'date' => 'required|date',
            'theme' => 'nullable|string|max:500',
            'activity' => 'nullable|string|max:1000',
            'notes' => 'nullable|string|max:1000',
            'subjects' => 'nullable|array',
            'subjects.*' => 'exists:subjects,id',
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
            'teacher_id.required' => 'Guru wajib diisi',
            'teacher_id.exists' => 'Guru tidak ditemukan',
            'class_id.required' => 'Kelas wajib diisi',
            'class_id.exists' => 'Kelas tidak ditemukan',
            'academic_year_id.required' => 'Tahun ajaran wajib diisi',
            'academic_year_id.exists' => 'Tahun ajaran tidak ditemukan',
            'date.required' => 'Tanggal wajib diisi',
            'date.date' => 'Format tanggal tidak valid',
            'theme.max' => 'Tema maksimal 500 karakter',
            'activity.max' => 'Aktivitas maksimal 1000 karakter',
            'notes.max' => 'Catatan maksimal 1000 karakter',
            'subjects.*.exists' => 'Mata pelajaran tidak ditemukan',
        ];
    }
}
