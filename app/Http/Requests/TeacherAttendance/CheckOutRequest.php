<?php

namespace App\Http\Requests\TeacherAttendance;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CheckOutRequest extends FormRequest
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
            'time_out' => 'required|date_format:H:i',
            'photo_out' => 'nullable|file|mimes:jpeg,png,jpg|max:2048',
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
            'time_out.required' => 'Waktu keluar harus diisi',
            'time_out.date_format' => 'Format waktu keluar tidak valid (HH:MM)',
            'time_out.after' => 'Waktu keluar harus setelah waktu masuk',
            'photo_out.file' => 'Photo keluar harus berupa file',
            'photo_out.mimes' => 'Photo keluar harus berformat jpeg, png, atau jpg',
            'photo_out.max' => 'Photo keluar maksimal 2MB',
        ];
    }
}
