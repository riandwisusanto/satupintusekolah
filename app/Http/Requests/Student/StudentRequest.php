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
            'nis'       => 'required|string|max:255|unique:students,nis,' . $this->route('id'),
            'name'      => 'required|string|max:255',
            'gender'    => 'required|in:L,P',
            'class_id'  => 'required|exists:classes,id',
            'active'    => 'required|boolean',
        ];
    }
}
