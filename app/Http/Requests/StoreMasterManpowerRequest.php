<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Models\MasterManpower;

class StoreMasterManpowerRequest extends FormRequest
{
    /**
     * Hanya Admin HSSE yang dapat membuat data manpower.
     */
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->role === 'Admin HSSE';
    }

    public function rules(): array
    {
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
            // Unique rule diletakkan di `bulan` dengan where() untuk cek kombinasi
            // fungsi + tahun + bulan → cukup satu Rule::unique, tidak perlu virtual field
            'bulan' => [
                'required',
                'integer',
                'between:1,12',
                Rule::unique('master_manpowers')->where(function ($query) {
                    return $query->where('fungsi', $this->fungsi)
                                 ->where('tahun', $this->tahun);
                }),
            ],
            'jumlah_manpower' => [
                'required',
                'integer',
                'min:1',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'fungsi.required'              => 'Fungsi wajib dipilih.',
            'fungsi.in'                    => 'Fungsi tidak valid. Pilih salah satu: Operation, Maintenance, HSSE, atau Business Support.',
            'tahun.required'               => 'Tahun wajib diisi.',
            'tahun.integer'                => 'Tahun harus berupa angka.',
            'tahun.digits'                 => 'Tahun harus terdiri dari 4 digit.',
            'tahun.min'                    => 'Tahun minimal adalah 2020.',
            'tahun.max'                    => 'Tahun maksimal adalah 2050.',
            'bulan.required'               => 'Bulan wajib dipilih.',
            'bulan.integer'                => 'Bulan harus berupa angka.',
            'bulan.between'                => 'Bulan harus antara 1 (Januari) hingga 12 (Desember).',
            'bulan.unique'                 => 'Data manpower untuk fungsi, tahun, dan bulan tersebut sudah tersedia. Silakan edit data yang sudah ada.',
            'jumlah_manpower.required'     => 'Jumlah manpower wajib diisi.',
            'jumlah_manpower.integer'      => 'Jumlah manpower harus berupa bilangan bulat.',
            'jumlah_manpower.min'          => 'Jumlah manpower minimal adalah 1.',
        ];
    }
}
