<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\UploadedFile;
use Illuminate\Validation\Rule;

class UserRequest extends FormRequest
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
            'name'      => 'required|string|max:255',
            'email'     => 'required|string|email|unique:users,email,' . $this->route('id'),
            'nip'       => 'nullable|string|max:255|unique:users,nip,' . $this->route('id'),
            'phone'     => 'nullable|string|max:255|unique:users,phone,' . $this->route('id'),
            'photo'     => [
                $isUpdate ? 'nullable' : 'required',
                function ($attribute, $value, $fail) {
                    if ($value instanceof UploadedFile) {
                        $allowed = ['image/jpeg', 'image/png', 'image/jpg'];
                        $maxSize = 2 * 1024 * 1024; // 2MB
                        if (! in_array($value->getMimeType(), $allowed)) {
                            $fail('File harus berformat jpeg, png, atau jpg');
                        }
                        if ($value->getSize() > $maxSize) {
                            $fail('Ukuran file maksimal 2MB');
                        }
                    } elseif (!is_string($value) && !is_null($value)) {
                        $fail('Field tidak valid');
                    }
                }
            ],
            'password'  => $isUpdate ? 'nullable|string|min:6' : 'required|string|min:6',
            'role_id'   => 'required|exists:roles,id',
        ];

        return $rules;
    }
}
