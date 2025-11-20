<?php

namespace App\Http\Requests\Classroom;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ClassroomRequest extends FormRequest
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
            'name'      => [
                'required',
                'string',
                'max:255',
                Rule::unique('classes', 'name')->ignore($this->route('id') ?? $this->route('classroom')),
            ],
            'teacher_id' => 'exists:users,id',
            'active'    => 'required|boolean',
        ];
    }
}
