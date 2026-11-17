<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'login' => ['nullable', 'string'],
            'email' => ['nullable', 'string'],
            'password' => ['required', 'string'],
            'captcha' => [app()->environment('testing') ? 'nullable' : 'required', 'string'],
            'remember' => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'login.required' => 'NISN, username, atau email wajib diisi.',
            'password.required' => 'Kata sandi wajib diisi.',
            'captcha.required' => 'Kode keamanan captcha wajib diisi.',
        ];
    }
}
