<?php

namespace App\Http\Requests\TeacherAttendance;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CheckInRequest extends FormRequest
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
            'date' => [
                'required',
                'date',
                Rule::unique('teacher_attendances', 'date')->where(function ($query) {
                    return $query->where('teacher_id', $this->teacher_id);
                }),
            ],
            'time_in' => 'required|date_format:H:i',
            'photo_in' => 'nullable|file|mimes:jpeg,png,jpg|max:2048',
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
            'date.required' => 'Tanggal harus diisi',
            'date.date' => 'Format tanggal tidak valid',
            'date.unique' => 'Kehadiran guru untuk tanggal ini sudah ada',
            'time_in.required' => 'Waktu masuk harus diisi',
            'time_in.date_format' => 'Format waktu masuk tidak valid (HH:MM)',
            'photo_in.file' => 'Photo masuk harus berupa file',
            'photo_in.mimes' => 'Photo masuk harus berformat jpeg, png, atau jpg',
            'photo_in.max' => 'Photo masuk maksimal 2MB',
        ];
    }
}
