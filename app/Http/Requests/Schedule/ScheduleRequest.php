<?php

namespace App\Http\Requests\Schedule;

use Illuminate\Foundation\Http\FormRequest;

class ScheduleRequest extends FormRequest
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
            'teacher_id' => 'required|exists:users,id',
            'subject_id' => 'required|exists:subjects,id',
            'class_id' => 'required|exists:classes,id',
            'day' => 'required|in:Senin,Selasa,Rabu,Kamis,Jumat,Sabtu',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'semester' => 'required|string|max:255',
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
            'teacher_id.required' => 'Guru harus dipilih',
            'teacher_id.exists' => 'Guru tidak valid',
            'subject_id.required' => 'Mata pelajaran harus dipilih',
            'subject_id.exists' => 'Mata pelajaran tidak valid',
            'class_id.required' => 'Kelas harus dipilih',
            'class_id.exists' => 'Kelas tidak valid',
            'day.required' => 'Hari harus dipilih',
            'day.in' => 'Hari tidak valid',
            'start_time.required' => 'Waktu mulai harus diisi',
            'start_time.date_format' => 'Format waktu mulai tidak valid (HH:MM)',
            'end_time.required' => 'Waktu selesai harus diisi',
            'end_time.date_format' => 'Format waktu selesai tidak valid (HH:MM)',
            'end_time.after' => 'Waktu selesai harus setelah waktu mulai',
            'semester.required' => 'Semester harus diisi',
        ];
    }
}
