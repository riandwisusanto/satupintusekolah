<?php

namespace App\Http\Requests\Journal;

use Illuminate\Foundation\Http\FormRequest;

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
        return [
            'teacher_id' => 'required|exists:users,id',
            'class_id' => 'required|exists:classes,id',
            'date' => 'required|date',
            'theme' => 'required|string|max:255',
            'activity' => 'required|string',
            'notes' => 'nullable|string',
            'active' => 'required|boolean',
            'subject_ids' => 'required|array|min:1',
            'subject_ids.*' => 'required|exists:subjects,id',
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
            'class_id.required' => 'Kelas harus dipilih',
            'class_id.exists' => 'Kelas tidak valid',
            'date.required' => 'Tanggal harus diisi',
            'date.date' => 'Format tanggal tidak valid',
            'theme.required' => 'Tema harus diisi',
            'theme.max' => 'Tema maksimal 255 karakter',
            'activity.required' => 'Aktivitas harus diisi',
            'notes.string' => 'Catatan harus berupa string',
            'active.required' => 'Status harus dipilih',
            'active.boolean' => 'Status tidak valid',
            'subject_ids.required' => 'Mata pelajaran harus dipilih',
            'subject_ids.array' => 'Mata pelajaran harus berupa array',
            'subject_ids.min' => 'Pilih minimal 1 mata pelajaran',
            'subject_ids.*.required' => 'Mata pelajaran harus valid',
            'subject_ids.*.exists' => 'Mata pelajaran tidak valid',
        ];
    }
}
