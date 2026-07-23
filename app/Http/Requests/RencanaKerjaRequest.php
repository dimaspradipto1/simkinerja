<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RencanaKerjaRequest extends FormRequest
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
            'uraian_tugas' => ['required', 'string'],
            'user_id' => ['nullable', 'exists:users,id'],
            'file' => ['nullable', 'file'],
            'url_external' => ['nullable', 'string'],
            'waktu_mulai' => ['nullable'],
            'waktu_selesai' => ['nullable'],
            'tanggal_mulai' => ['nullable', 'date'],
            'tanggal_selesai' => ['nullable', 'date'],
        ];
    }

    /**
     * Custom validation messages.
     */
    public function messages(): array
    {
        return [
            'uraian_tugas.required' => 'Uraian tugas wajib diisi.',
            'file.file' => 'File harus berupa dokumen atau berkas valid.',
            'file.max' => 'Ukuran file maksimal 5MB.',
        ];
    }
}
