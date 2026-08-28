<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Models\MasterManpower;

class UpdateMasterManpowerRequest extends FormRequest
{
    /**
     * Hanya Admin HSSE yang dapat mengubah data manpower.
     */
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->role === 'Admin HSSE';
    }

    public function rules(): array
    {
        // Ambil ID record yang sedang diedit dari route parameter
        $manpowerId = $this->route('manpower')?->id;

        return [
            'fungsi' => [
                'required',
                Rule::in(MasterManpower::FUNGSI_LIST),
            ],
            'tahun' => [
                'required',
                'integer',
                'digits:4',
                'min:2020',
                'max:2050',
            ],
            'jumlah_manpower' => [
                'required',
                'integer',
                'min:1',
            ],
            // Kombinasi fungsi + tahun harus unique,
            // tapi abaikan record yang sedang diedit (ignore by ID)
            'fungsi_tahun_unique' => [
                Rule::unique('master_manpowers')
                    ->where(function ($query) {
                        return $query->where('fungsi', $this->fungsi)
                                     ->where('tahun', $this->tahun);
                    })
                    ->ignore($manpowerId),
            ],
        ];
    }

    /**
     * Prepare data sebelum validasi berjalan.
     * Menambahkan virtual field untuk pengecekan unique kombinasi.
     */
    protected function prepareForValidation(): void
    {
        // Field virtual untuk trigger unique rule kombinasi fungsi+tahun
        $this->merge(['fungsi_tahun_unique' => $this->fungsi . '|' . $this->tahun]);
    }

    public function messages(): array
    {
        return [
            'fungsi.required'                  => 'Fungsi wajib dipilih.',
            'fungsi.in'                        => 'Fungsi tidak valid. Pilih salah satu: Operation, Maintenance, HSSE, atau Business Support.',
            'tahun.required'                   => 'Tahun wajib diisi.',
            'tahun.integer'                    => 'Tahun harus berupa angka.',
            'tahun.digits'                     => 'Tahun harus terdiri dari 4 digit.',
            'tahun.min'                        => 'Tahun minimal adalah 2020.',
            'tahun.max'                        => 'Tahun maksimal adalah 2050.',
            'jumlah_manpower.required'         => 'Jumlah manpower wajib diisi.',
            'jumlah_manpower.integer'          => 'Jumlah manpower harus berupa bilangan bulat.',
            'jumlah_manpower.min'              => 'Jumlah manpower minimal adalah 1.',
            'fungsi_tahun_unique.unique'       => 'Data manpower untuk fungsi dan tahun tersebut sudah tersedia. Silakan edit data yang sudah ada.',
        ];
    }
}
