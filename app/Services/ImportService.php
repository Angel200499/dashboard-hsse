<?php

namespace App\Services;

use App\Models\ImportLog;
use App\Models\SipekaFinding;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\SipekaFindingsImport;

/**
 * ImportService
 *
 * Menangani logika import Excel SIPEKA:
 * - Validasi awal file
 * - Jalankan import via SipekaFindingsImport
 * - Rekam ImportLog (jumlah insert, update, skip, error, durasi)
 *
 * Business Rule #6:
 *   Import TIDAK boleh overwrite no_notifikasi_sap dan keterangan_tindak_lanjut.
 *   Kedua field tersebut hanya boleh diubah dari Dashboard (manual oleh Admin).
 */
class ImportService
{
    /**
     * Jalankan proses import dan simpan hasilnya ke ImportLog.
     *
     * @param  UploadedFile $file
     * @return ImportLog
     */
    public function import(UploadedFile $file): ImportLog
    {
        $startTime = microtime(true);
        $importer = new SipekaFindingsImport();

        // Baca SEMUA sheet mentah tanpa format header
        $allSheets = Excel::toArray(new \stdClass(), $file);
        
        $dataRows = [];
        $headerRow = [];
        $foundHeader = false;

        // Cari sheet dan baris yang mengandung "ID Temuan"
        foreach ($allSheets as $sheet) {
            foreach ($sheet as $index => $row) {
                // Bersihkan baris untuk pencarian
                $cleanRow = array_map(fn($v) => strtolower(trim((string)$v)), $row);
                
                // Jika baris ini punya 'id temuan' atau 'idtemuan'
                if (in_array('id temuan', $cleanRow) || in_array('idtemuan', $cleanRow) || in_array('id_temuan', $cleanRow)) {
                    $headerRow = $cleanRow;
                    // Ambil sisa baris di sheet ini sebagai data
                    $dataRows = array_slice($sheet, $index + 1);
                    $foundHeader = true;
                    break 2;
                }
            }
        }

        if (!$foundHeader) {
            return ImportLog::create([
                'user_id'       => Auth::id(),
                'filename'      => $file->getClientOriginalName(),
                'jumlah_insert' => 0,
                'jumlah_update' => 0,
                'jumlah_skip'   => 0,
                'jumlah_error'  => 1,
                'durasi_detik'  => round(microtime(true) - $startTime, 3),
                'status'        => 'failed',
                'catatan'       => 'Gagal: Tidak menemukan kolom ID Temuan di sheet manapun.',
            ]);
        }

        // Proses data secara manual
        foreach ($dataRows as $rawRow) {
            // Map raw row ke associative array berdasarkan header
            $mappedRow = [];
            foreach ($headerRow as $colIndex => $colName) {
                if (!empty($colName)) {
                    // Normalisasi nama kolom seperti Laravel Excel (lowercase, replace spasi dengan underscore)
                    $normalizedColName = str_replace(' ', '_', $colName);
                    $cellValue = $rawRow[$colIndex] ?? null;

                    // Parse Excel Date Serial (misal: 46196.3355) menjadi tanggal yang bisa dibaca
                    if (
                        (str_contains($normalizedColName, 'tanggal') || str_contains($normalizedColName, 'date')) 
                        && is_numeric($cellValue) 
                        && $cellValue > 20000
                    ) {
                        try {
                            $dateObj = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($cellValue);
                            // SIPEKA biasanya pakai d/m/Y H:i atau Y-m-d H:i, kita standarisasi ke Y-m-d H:i
                            $cellValue = $dateObj->format('Y-m-d H:i');
                        } catch (\Exception $e) {
                            // Abaikan jika gagal parse, biarkan raw value
                        }
                    }

                    $mappedRow[$normalizedColName] = $cellValue;
                }
            }
            
            // Masukkan ke importer
            try {
                $model = $importer->model($mappedRow);
                if ($model) {
                    // Karena kita by-pass ToModel Excel::import, kita harus save manual.
                    // Upsert lebih baik, tapi karena kita mau tracking insert/update:
                    $exists = SipekaFinding::where('id_temuan', $model->id_temuan)->first();
                    if ($exists) {
                        $exists->update(['data_sipeka' => $model->data_sipeka]);
                    } else {
                        $model->save();
                    }
                }
            } catch (\Exception $e) {
                $importer->onError($e);
            }
        }

        $durasi = round(microtime(true) - $startTime, 3);
        
        $jumlahInsert = $importer->getInsertCount();
        $jumlahUpdate = $importer->getUpdateCount();
        $jumlahSkip   = $importer->getSkipCount();
        $jumlahError  = $importer->getErrorCount();

        $status = 'success';
        if ($jumlahError > 0 && ($jumlahInsert + $jumlahUpdate) === 0) {
            $status = 'failed';
        } elseif ($jumlahError > 0) {
            $status = 'partial';
        }

        $catatan = null;
        $errors  = $importer->getErrors();
        if (!empty($errors)) {
            $catatan = implode('; ', array_slice($errors, 0, 5));
        }

        return ImportLog::create([
            'user_id'       => Auth::id(),
            'filename'      => $file->getClientOriginalName(),
            'jumlah_insert' => $jumlahInsert,
            'jumlah_update' => $jumlahUpdate,
            'jumlah_skip'   => $jumlahSkip,
            'jumlah_error'  => $jumlahError,
            'durasi_detik'  => $durasi,
            'status'        => $status,
            'catatan'       => $catatan,
        ]);
    }
}
