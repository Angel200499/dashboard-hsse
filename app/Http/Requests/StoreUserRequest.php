<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->role === 'Admin HSSE';
    }

    public function rules(): array
    {
        return [
            'name'     => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:users,username',
            'password' => 'required|string|min:6|max:255',
            'role'     => ['required', Rule::in(['Admin HSSE', 'Admin Function', 'Manager HSSE', 'Manager Function'])],
            'fungsi'   => ['required', Rule::in(['Operation', 'Maintenance', 'HSSE', 'Business Support'])],
        ];
    }

    public function messages(): array
    {
        return [
            'username.unique'   => 'Username sudah digunakan oleh user lain.',
            'password.min'      => 'Password minimal 6 karakter.',
            'role.in'           => 'Role tidak valid.',
            'fungsi.in'         => 'Fungsi tidak valid.',
        ];
    }
}
