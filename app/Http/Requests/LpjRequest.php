<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class LpjRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return \Illuminate\Support\Facades\Auth::check() && \Illuminate\Support\Facades\Auth::user()->canAccessLpj();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'nama_lpj' => 'required|string|max:255',
            'kepanitiaan_id' => 'nullable|integer|exists:kegiatans,id',
            'nama_kepanitiaan' => 'required|string|max:255',
            'tanggal_kegiatan' => 'nullable|date',
            'keterangan' => 'nullable|string',
            'file_lpj' => 'nullable|file|mimes:pdf,doc,docx',
            'link_lpj' => 'nullable|url|max:500',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'nama_lpj' => 'Nama / Judul LPJ',
            'kepanitiaan_id' => 'Kepanitiaan',
            'nama_kepanitiaan' => 'Nama Kepanitiaan',
            'tanggal_kegiatan' => 'Tanggal Kegiatan',
            'keterangan' => 'Keterangan / Deskripsi',
            'file_lpj' => 'Berkas File LPJ',
            'link_lpj' => 'Tautan (Link) LPJ',
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'nama_lpj.required' => 'Nama / Judul LPJ wajib diisi.',
            'nama_kepanitiaan.required' => 'Nama kepanitiaan wajib dipilih atau diisi.',
            'tanggal_kegiatan.date' => 'Format tanggal kegiatan tidak valid.',
            'file_lpj.file' => 'Berkas LPJ harus berupa file yang valid.',
            'file_lpj.mimes' => 'Format berkas LPJ harus berupa PDF atau Word (.pdf, .doc, .docx).',
            'link_lpj.url' => 'Format tautan (link) LPJ harus berupa URL yang valid (contoh: https://drive.google.com/...).',
        ];
    }
}
