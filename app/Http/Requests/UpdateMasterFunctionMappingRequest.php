<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Models\MasterFunctionMapping;

class UpdateMasterFunctionMappingRequest extends FormRequest
{
    /**
     * Hanya Admin HSSE yang dapat mengubah mapping fungsi.
     */
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->role === 'Admin HSSE';
    }

    public function rules(): array
    {
        // Ambil ID record yang sedang diedit dari route parameter
        $mappingId = $this->route('mapping')?->id;

        return [
            'fungsi_sipeka' => [
                'required',
                'string',
                'max:255',
                // Layer 1: standard unique, abaikan record yang sedang diedit
                Rule::unique('master_function_mappings', 'fungsi_sipeka')->ignore($mappingId),
                // Layer 2: custom rule — deteksi konflik case-insensitive dan beri pesan jelas
                function (string $attribute, mixed $value, \Closure $fail) use ($mappingId) {
                    $conflict = MasterFunctionMapping::whereRaw(
                        'LOWER(fungsi_sipeka) = ?',
                        [strtolower(trim($value))]
                    )
                    ->where('id', '!=', $mappingId) // abaikan record sendiri
                    ->first();

                    if ($conflict) {
                        $fail(
                            'Fungsi SIPEKA sudah memiliki mapping (ditemukan: "' . $conflict->fungsi_sipeka . '" → ' .
                            $conflict->fungsi_dashboard . '). Silakan edit mapping tersebut jika perlu diubah.'
                        );
                    }
                },
            ],
            'fungsi_dashboard' => [
                'required',
                Rule::in(MasterFunctionMapping::DASHBOARD_FUNGSI_LIST),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'fungsi_sipeka.required'   => 'Fungsi SIPEKA wajib diisi.',
            'fungsi_sipeka.string'     => 'Fungsi SIPEKA harus berupa teks.',
            'fungsi_sipeka.max'        => 'Fungsi SIPEKA maksimal 255 karakter.',
            'fungsi_sipeka.unique'     => 'Fungsi SIPEKA tersebut sudah memiliki mapping. Silakan edit mapping yang sudah ada.',
            'fungsi_dashboard.required'=> 'Fungsi Dashboard wajib dipilih.',
            'fungsi_dashboard.in'      => 'Fungsi Dashboard tidak valid. Pilih salah satu: Operation, Maintenance, HSSE, atau Business Support.',
        ];
    }
}
