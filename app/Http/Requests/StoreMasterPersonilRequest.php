<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Models\MasterPersonil;

class StoreMasterPersonilRequest extends FormRequest
{
    /**
     * Hanya Admin HSSE yang dapat menambah data personil.
     */
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->role === 'Admin HSSE';
    }

    /**
     * Normalisasi input sebelum validasi:
     * - Trim spasi berlebih
     * - Pastikan NOPEK kosong diubah menjadi NULL agar tidak melanggar unique constraint.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'nama'    => is_string($this->nama) ? trim($this->nama) : $this->nama,
            'nopek'   => (is_string($this->nopek) && trim($this->nopek) !== '') ? trim($this->nopek) : null,
            'jabatan' => (is_string($this->jabatan) && trim($this->jabatan) !== '') ? trim($this->jabatan) : null,
        ]);
    }

    public function rules(): array
    {
        return [
            'nama' => [
                'required',
                'string',
                'max:255',
            ],
            'nopek' => [
                'nullable',
                'string',
                'max:50',
                Rule::unique('master_personils', 'nopek'),
            ],
            'fungsi' => [
                'required',
                Rule::in(MasterPersonil::FUNGSI_LIST),
            ],
            'jabatan' => [
                'nullable',
                'string',
                'max:255',
            ],
            'tipe_manpower' => [
                'required',
                Rule::in(MasterPersonil::TIPE_LIST),
            ],
            'status_aktif' => [
                'nullable',
                'boolean',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'nama.required'          => 'Nama personil wajib diisi.',
            'nama.max'               => 'Nama personil maksimal 255 karakter.',
            'nopek.unique'           => 'NOPEK sudah terdaftar untuk personil lain. NOPEK harus bersifat unik.',
            'nopek.max'              => 'NOPEK maksimal 50 karakter.',
            'fungsi.required'        => 'Fungsi wajib dipilih.',
            'fungsi.in'              => 'Fungsi tidak valid. Pilih: Operation, Maintenance, HSSE, atau Business Support.',
            'jabatan.max'            => 'Jabatan maksimal 255 karakter.',
            'tipe_manpower.required' => 'Tipe manpower wajib dipilih.',
            'tipe_manpower.in'       => 'Tipe manpower harus berupa Pekerja atau TAD.',
        ];
    }
}
