<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
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
        $userId = $this->route('user') ? $this->route('user')->id ?? $this->route('user') : null;

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($userId)],
            'password' => $userId ? ['nullable', 'string', 'min:6'] : ['required', 'string', 'min:6'],
            'roles' => ['required', 'string'],
            'is_active' => ['required', 'boolean'],
            'nidn' => ['nullable', 'string', 'max:100'],
            'unit' => ['nullable', 'string', 'max:255'],
            'jabatan' => ['nullable', 'string', 'max:255'],
            'jabatan_pkkmb' => ['nullable', 'string', 'max:255'],
            'jabatan_esq' => ['nullable', 'string', 'max:255'],
            'jabatan_milad' => ['nullable', 'string', 'max:255'],
            'jabatan_kuliah_umum' => ['nullable', 'string', 'max:255'],
            'status' => ['required', 'string', 'max:100'],
        ];
    }

    /**
     * Custom validation messages.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Nama wajib diisi.',
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Email sudah terdaftar.',
            'password.required' => 'Password wajib diisi.',
            'password.min' => 'Password minimal 6 karakter.',
            'roles.required' => 'Role wajib dipilih.',
            'is_active.required' => 'Status Aktif wajib dipilih.',
            'status.required' => 'Status wajib diisi.',
        ];
    }
}
