<?php

namespace App\Http\Requests\TeacherAttendance;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\UploadedFile;
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
        $isUpdate = $this->method() == 'PUT';

        $rules = [
            'teacher_id' => 'required|exists:users,id',
            'date' => 'required|date',
            'time_in' => 'required|date_format:H:i',
            'time_out' => 'nullable|date_format:H:i|after:time_in',
            'photo_in' => [
                'nullable',
                function ($attribute, $value, $fail) {
                    if ($value instanceof UploadedFile) {
                        $allowed = ['image/jpeg', 'image/png', 'image/jpg'];
                        $maxSize = 2 * 1024 * 1024; // 2MB
                        if (! in_array($value->getMimeType(), $allowed)) {
                            $fail('File foto in harus berformat jpeg, png, atau jpg');
                        }
                        if ($value->getSize() > $maxSize) {
                            $fail('Ukuran file foto in maksimal 2MB');
                        }
                        return;
                    }
                }
            ],
            'photo_out' => [
                'nullable',
                function ($attribute, $value, $fail) {
                    if ($value instanceof UploadedFile) {
                        $allowed = ['image/jpeg', 'image/png', 'image/jpg'];
                        $maxSize = 2 * 1024 * 1024; // 2MB
                        if (! in_array($value->getMimeType(), $allowed)) {
                            $fail('File foto out harus berformat jpeg, png, atau jpg');
                        }
                        if ($value->getSize() > $maxSize) {
                            $fail('Ukuran file foto out maksimal 2MB');
                        }
                        return;
                    }
                }
            ],
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
            'date.required' => 'Tanggal wajib diisi',
            'date.date' => 'Format tanggal tidak valid',
            'time_in.required' => 'Waktu masuk wajib diisi',
            'time_in.date_format' => 'Format waktu masuk harus HH:MM',
            'time_out.date_format' => 'Format waktu keluar harus HH:MM',
            'time_out.after' => 'Waktu keluar harus setelah waktu masuk',
        ];
    }
}
