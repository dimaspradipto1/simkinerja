<?php

namespace App\Http\Requests;

use App\Models\PengaturanPesanWhatsapp;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PengaturanPesanWhatsappRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $pesan = $this->input('pesan', []);
        if (is_array($pesan)) {
            foreach (PengaturanPesanWhatsapp::STATUSES as $status) {
                if (isset($pesan[$status])) {
                    $rawTime = $pesan[$status]['waktu_kirim'] ?? null;
                    if (is_string($rawTime)) {
                        $rawTime = trim($rawTime);
                        $pesan[$status]['waktu_kirim'] = $rawTime !== '' ? substr($rawTime, 0, 5) : null;
                    } else {
                        $pesan[$status]['waktu_kirim'] = null;
                    }
                }
            }
            $this->merge(['pesan' => $pesan]);
        }
    }

    /**
     * @return array<string, array<int, ValidationRule|array<mixed>|string>>
     */
    public function rules(): array
    {
        $rules = [
            'penerima_user_ids' => ['required', 'array', 'min:1'],
            'penerima_user_ids.*' => [
                'required',
                'integer',
                'distinct',
                Rule::exists('users', 'id')->where(fn ($query) => $query
                    ->where('is_active', true)
                    ->whereNotNull('supervisor_id')
                    ->whereNotNull('nomor_wa')
                    ->where('nomor_wa', '!=', '')),
            ],
        ];

        foreach (PengaturanPesanWhatsapp::STATUSES as $status) {
            $prefix = 'pesan.' . $status;
            $rules[$prefix . '.isi_pesan'] = ['required', 'string', 'max:5000'];
            $rules[$prefix . '.waktu_kirim'] = [
                'nullable',
                'date_format:H:i',
            ];
            $rules[$prefix . '.is_active'] = ['required', 'boolean'];
        }

        return $rules;
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'penerima_user_ids' => 'tujuan penerima',
            'penerima_user_ids.*' => 'pegawai penerima',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [];
    }
}
