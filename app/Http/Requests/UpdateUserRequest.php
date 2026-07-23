<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->role === 'Admin HSSE';
    }

    public function rules(): array
    {
        // Ambil ID user yang sedang diedit dari route parameter
        $userId = $this->route('user')?->id;

        $rules = [
            'name'     => 'required|string|max:255',
            'username' => [
                'required',
                'string',
                'max:255',
                Rule::unique('users', 'username')->ignore($userId),
            ],
            'role'     => ['required', Rule::in(['Admin HSSE', 'Admin Function', 'Manager HSSE', 'Manager Function'])],
            'fungsi'   => ['required', Rule::in(['Operation', 'Maintenance', 'HSSE', 'Business Support'])],
        ];

        // Password hanya wajib jika diisi
        if ($this->filled('password')) {
            $rules['password'] = 'string|min:6|max:255';
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'username.unique' => 'Username sudah digunakan oleh user lain.',
            'password.min'    => 'Password minimal 6 karakter.',
            'role.in'         => 'Role tidak valid.',
            'fungsi.in'       => 'Fungsi tidak valid.',
        ];
    }
}
