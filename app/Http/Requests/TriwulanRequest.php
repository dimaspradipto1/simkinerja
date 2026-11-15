<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TriwulanRequest extends FormRequest
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
     */
    public function rules(): array
    {
        return [
            'periode_akademik_id' => 'required|exists:periode_akademiks,id',
            'nama' => 'required|string|max:50',
            'label' => 'nullable|string|max:100',
            'rentang_bulan' => 'nullable|string|max:100',
            'keterangan' => 'nullable|string|max:255',
            'bulan_mulai' => 'required|integer|between:1,12',
            'bulan_selesai' => 'required|integer|between:1,12',
            'tanggal_mulai' => 'nullable|date',
            'tanggal_selesai' => 'nullable|date|after_or_equal:tanggal_mulai',
            'urutan' => 'nullable|integer',
            'is_active' => 'nullable|boolean',
        ];
    }

    /**
     * Custom attribute names for validation errors.
     */
    public function attributes(): array
    {
        return [
            'periode_akademik_id' => 'Periode Akademik',
            'nama' => 'Nama Triwulan',
            'bulan_mulai' => 'Bulan Mulai',
            'bulan_selesai' => 'Bulan Selesai',
        ];
    }
}
