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
                            'Fungsi SIPEKA sudah memiliki mapping (ditemukan: "' . $conflict->fungsi_sipeka . '" \u2192 ' .
                            ($conflict->fungsi_dashboard ?? '[kelompok khusus: ' . $conflict->kelompok_khusus . ']') .
                            '). Silakan edit mapping tersebut jika perlu diubah.'
                        );
                    }
                },
            ],
            // fungsi_dashboard bersifat nullable: boleh NULL jika mapping adalah kelompok khusus (misal GM)
            'fungsi_dashboard' => [
                'nullable',
                Rule::in(MasterFunctionMapping::DASHBOARD_FUNGSI_LIST),
            ],
            // kelompok_khusus bersifat optional: hanya boleh diisi dengan nilai dari KELOMPOK_KHUSUS_LIST
            'kelompok_khusus' => [
                'nullable',
                Rule::in(MasterFunctionMapping::KELOMPOK_KHUSUS_LIST),
            ],
            // Custom rule: minimal salah satu dari fungsi_dashboard atau kelompok_khusus harus diisi
            'fungsi_dashboard_or_kelompok' => [
                function (string $attribute, mixed $value, \Closure $fail) {
                    $hasFungsi   = !empty($this->input('fungsi_dashboard'));
                    $hasKelompok = !empty($this->input('kelompok_khusus'));

                    if (!$hasFungsi && !$hasKelompok) {
                        $fail('Pilih Fungsi Dashboard atau Kelompok Khusus. Minimal salah satu harus diisi.');
                    }
                },
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'fungsi_sipeka.required'                    => 'Fungsi SIPEKA wajib diisi.',
            'fungsi_sipeka.string'                      => 'Fungsi SIPEKA harus berupa teks.',
            'fungsi_sipeka.max'                         => 'Fungsi SIPEKA maksimal 255 karakter.',
            'fungsi_sipeka.unique'                      => 'Fungsi SIPEKA tersebut sudah memiliki mapping. Silakan edit mapping yang sudah ada.',
            'fungsi_dashboard.in'                       => 'Fungsi Dashboard tidak valid. Pilih salah satu: Operation, Maintenance, HSSE, atau Business Support.',
            'kelompok_khusus.in'                        => 'Kelompok Khusus tidak valid. Pilih salah satu dari daftar yang tersedia.',
            'fungsi_dashboard_or_kelompok.required_if'  => 'Pilih Fungsi Dashboard atau Kelompok Khusus.',
        ];
    }
}
