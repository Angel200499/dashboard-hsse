<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateFindingRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     * Otorisasi dilakukan di Controller menggunakan canEditFinding().
     */
    public function authorize(): bool
    {
        return auth()->check();
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'no_notifikasi_sap'        => 'nullable|string|max:255',
            'keterangan_tindak_lanjut' => 'nullable|string|max:5000',
        ];
    }

    public function messages(): array
    {
        return [
            'no_notifikasi_sap.max'        => 'No. Notifikasi SAP maksimal 255 karakter.',
            'keterangan_tindak_lanjut.max' => 'Keterangan Tindak Lanjut maksimal 5000 karakter.',
        ];
    }
}
