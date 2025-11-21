<?php

namespace App\Http\Requests\StudentAttendance;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StudentAttendanceRequest extends FormRequest
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
        $rules = [
            'date' => [
                'required',
                'date',
                Rule::unique('student_attendances', 'date')
                    ->where(function ($query) {
                        return $query->where('class_id', request('class_id'));
                    })
                    ->ignore($this->route('id') ?? $this->route('student_attendance')),
            ],
            'teacher_id' => 'required|exists:users,id',
            'class_id' => 'required|exists:classes,id',
            'academic_year_id' => 'required|exists:academic_years,id',
            'subjects' => 'required|array|min:1',
            'subjects.*.subject_id' => 'required|exists:subjects,id',
            'details' => 'required|array|min:1',
            'details.*.student_id' => 'required|exists:students,id',
            'details.*.status' => 'required|in:hadir,ijin,sakit,alpa,telat',
            'details.*.note' => 'nullable|string|max:500',
        ];

        // For update, make date unique validation conditional
        if ($this->isMethod('PUT') || $this->isMethod('PATCH')) {
            $rules['date'] = [
                'required',
                'date',
                Rule::unique('student_attendances', 'date')
                    ->where(function ($query) {
                        return $query->where('class_id', request('class_id'));
                    })
                    ->ignore($this->route('id') ?? $this->route('student_attendance')),
            ];
        }

        return $rules;
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'date.required' => 'Tanggal harus diisi',
            'date.date' => 'Format tanggal tidak valid',
            'date.unique' => 'Tanggal kehadiran sudah ada',
            'teacher_id.required' => 'Guru harus dipilih',
            'teacher_id.exists' => 'Guru tidak valid',
            'class_id.required' => 'Kelas harus dipilih',
            'class_id.exists' => 'Kelas tidak valid',
            'academic_year_id.required' => 'Tahun ajaran harus dipilih',
            'academic_year_id.exists' => 'Tahun ajaran tidak valid',
            'subjects.required' => 'Mata pelajaran harus dipilih',
            'subjects.array' => 'Mata pelajaran harus berupa array',
            'subjects.min' => 'Pilih minimal 1 mata pelajaran',
            'subjects.*.subject_id.required' => 'Subject ID harus diisi',
            'subjects.*.subject_id.exists' => 'Subject ID tidak valid',
            'details.required' => 'Detail siswa harus diisi',
            'details.array' => 'Detail siswa harus berupa array',
            'details.min' => 'Pilih minimal 1 siswa',
            'details.*.student_id.required' => 'ID siswa harus diisi',
            'details.*.student_id.exists' => 'ID siswa tidak valid',
            'details.*.status.required' => 'Status kehadiran harus diisi',
            'details.*.status.in' => 'Status tidak valid (hadir, ijin, sakit, alpa, telat)',
            'details.*.note.string' => 'Catatan harus berupa string',
            'details.*.note.max' => 'Catatan maksimal 500 karakter',
        ];
    }
}
