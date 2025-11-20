<?php

namespace App\Http\Requests\Student;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StudentRequest extends FormRequest
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
            'nis'       => [
                'required',
                'string',
                Rule::unique('students', 'nis')->ignore($this->route('id') ?? $this->route('student')),
            ],
            'name'      => 'required|string|max:255',
            'gender'    => 'required',
            'class_id'  => 'required|exists:classes,id',
            'phone'     => [
                'nullable',
                'string',
                Rule::unique('students', 'phone')->ignore($this->route('id') ?? $this->route('student')),
            ],
            'active'    => 'required|boolean',
        ];
    }
}
