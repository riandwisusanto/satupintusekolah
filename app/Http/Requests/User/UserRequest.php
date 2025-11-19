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
            'email' => [
                'required',
                'string',
                'email',
                Rule::unique('users', 'email')->ignore($this->route('id') ?? $this->route('user')),
            ],
            'nip' => [
                'nullable',
                Rule::unique('users', 'nip')->ignore($this->route('id') ?? $this->route('user')),
            ],
            'phone' => [
                'nullable',
                Rule::unique('users', 'phone')->ignore($this->route('id') ?? $this->route('user')),
            ],
            'photo' => [
                $isUpdate ? 'nullable' : 'required',
                'sometimes',
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
                        return;
                    }

                    // selain file upload, HARUS dianggap kosong, bukan error
                    if ($value === null || $value === '' || $value === 'null') {
                        return; // valid, abaikan
                    }
                }
            ],
            'password'  => $isUpdate ? 'nullable|string|min:6' : 'required|string|min:6',
            'role_id'   => 'required|exists:roles,id',
        ];

        return $rules;
    }
}
