<?php

namespace App\Imports;

use App\Models\SipekaFinding;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithUpserts;
use Maatwebsite\Excel\Concerns\WithUpsertColumns;

class SipekaFindingsImport implements ToModel, WithHeadingRow, WithUpserts, WithUpsertColumns
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        if (!isset($row['idtemuan'])) {
            return null; // Skip if no IDTEMUAN
        }

        return new SipekaFinding([
            'id_temuan'   => (string) $row['idtemuan'],
            'data_sipeka' => $row,
        ]);
    }

    /**
     * Tentukan kolom mana yang menjadi acuan unique untuk Upsert
     */
    public function uniqueBy()
    {
        return 'id_temuan';
    }

    /**
     * Tentukan kolom mana Saja yang Boleh di-update jika data sudah ada.
     * Kita TIDAK mendaftarkan 'no_notifikasi_sap' dan 'keterangan_tindak_lanjut' di sini
     * agar kedua kolom tersebut tidak tertimpa oleh file Excel.
     */
    public function upsertColumns()
    {
        return ['data_sipeka', 'updated_at'];
    }
}
