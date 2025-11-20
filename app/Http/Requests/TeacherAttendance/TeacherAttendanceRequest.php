<?php

namespace App\Http\Requests\TeacherAttendance;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TeacherAttendanceRequest extends FormRequest
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
            'teacher_id' => 'required|exists:users,id',
            'date' => [
                'required',
                'date',
                Rule::unique('teacher_attendances', 'date')->ignore($this->route('id') ?? $this->route('teacher_attendance')),
            ],
            'time_in' => 'required|date_format:H:i',
            'time_out' => 'nullable|date_format:H:i|after:time_in',
            'photo_in' => 'nullable|string|max:255',
            'photo_out' => 'nullable|string|max:255',
        ];

        // For update, make date unique validation conditional
        if ($this->isMethod('PUT') || $this->isMethod('PATCH')) {
            $rules['date'] = [
                'required',
                'date',
                Rule::unique('teacher_attendances', 'date')->ignore($this->route('id') ?? $this->route('teacher_attendance')),
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
            'teacher_id.required' => 'Guru harus dipilih',
            'teacher_id.exists' => 'Guru tidak valid',
            'date.required' => 'Tanggal harus diisi',
            'date.date' => 'Format tanggal tidak valid',
            'date.unique' => 'Tanggal kehadiran guru sudah ada',
            'time_in.required' => 'Waktu masuk harus diisi',
            'time_in.date_format' => 'Format waktu masuk tidak valid (HH:MM)',
            'time_out.required' => 'Waktu keluar harus diisi',
            'time_out.date_format' => 'Format waktu keluar tidak valid (HH:MM)',
            'time_out.after' => 'Waktu keluar harus setelah waktu masuk',
            'photo_in.string' => 'Photo masuk harus berupa string',
            'photo_in.max' => 'Photo masuk maksimal 255 karakter',
            'photo_out.string' => 'Photo keluar harus berupa string',
            'photo_out.max' => 'Photo keluar maksimal 255 karakter',
        ];
    }
}
