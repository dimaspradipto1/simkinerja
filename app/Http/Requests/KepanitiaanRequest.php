<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class KepanitiaanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'uraian_tugas' => ['required', 'string'],
            'periode_akademik_id' => ['required', 'exists:periode_akademiks,id'],
            'user_id' => ['nullable', 'exists:users,id'],
            'hari' => ['nullable', 'string', 'max:50'],
            'estimasi_jam_mulai' => ['nullable'],
            'estimasi_jam_selesai' => ['nullable'],
            'estimasi_tanggal_mulai' => ['nullable', 'date'],
            'estimasi_tanggal_selesai' => ['nullable', 'date'],
            'file' => ['nullable', 'file'],
            'url_external' => ['nullable', 'string'],
            'waktu_mulai' => ['nullable'],
            'waktu_selesai' => ['nullable'],
            'tanggal_mulai' => ['nullable', 'date'],
            'tanggal_selesai' => ['nullable', 'date'],
            'tagged_users' => ['nullable', 'array'],
            'tagged_users.*' => ['exists:users,id'],
        ];
    }

    /**
     * Custom validation messages.
     */
    public function messages(): array
    {
        return [
            'uraian_tugas.required' => 'Uraian tugas wajib diisi.',
            'periode_akademik_id.required' => 'Periode Akademik wajib dipilih.',
            'periode_akademik_id.exists' => 'Periode Akademik tidak valid.',
            'file.file' => 'File harus berupa dokumen atau berkas valid.',
            'file.max' => 'Ukuran file maksimal 5MB.',
        ];
    }
}
