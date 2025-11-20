<?php

namespace App\Http\Requests\TeacherAttendance;

use Illuminate\Foundation\Http\FormRequest;

class PhotoUploadRequest extends FormRequest
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
            'photo_type' => 'required|in:in,out',
            'photo' => 'required|file|mimes:jpeg,jpg,png|max:5120', // Max 5MB
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
            'photo_type.required' => 'Tipe foto harus dipilih',
            'photo_type.in' => 'Tipe foto tidak valid (in/out)',
            'photo.required' => 'Foto harus diupload',
            'photo.file' => 'File harus berupa file',
            'photo.mimes' => 'Format foto tidak valid (jpeg, jpg, png)',
            'photo.max' => 'Ukuran foto maksimal 5MB',
        ];
    }
}
