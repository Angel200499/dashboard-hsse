<?php

namespace App\Imports;

use App\Models\SipekaFinding;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithUpserts;
use Maatwebsite\Excel\Concerns\WithUpsertColumns;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\SkipsErrors;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Validators\Failure;
use Throwable;

/**
 * SipekaFindingsImport
 *
 * Business Rule #5: foto_temuan dan foto_close disimpan sebagai URL string.
 * Business Rule #6: Import TIDAK menimpa no_notifikasi_sap dan keterangan_tindak_lanjut.
 *                   Dijamin oleh upsertColumns() yang tidak menyertakan kedua field tersebut.
 *
 * Menggunakan SkipsOnError + SkipsOnFailure agar satu baris error
 * tidak menghentikan seluruh proses import.
 */
class SipekaFindingsImport implements
    ToModel,
    WithHeadingRow,
    WithUpserts,
    WithUpsertColumns,
    SkipsOnError,
    SkipsOnFailure
{
    use SkipsErrors, SkipsFailures;

    /** Counter untuk ImportLog */
    private int $rowCount    = 0;
    private int $insertCount = 0;
    private int $updateCount = 0;
    private int $skipCount   = 0;
    private array $errorMessages = [];

    /**
     * Map satu baris Excel ke Model SipekaFinding.
     *
     * Heading row di-normalize secara otomatis oleh Laravel Excel
     * (lowercase, trim, replace spasi dengan underscore).
     *
     * Kolom foto disimpan sebagai string URL (Business Rule #5).
     */
    public function model(array $row): ?SipekaFinding
    {
        // Ambil ID Temuan (Laravel Excel memformat heading "ID Temuan" menjadi "id_temuan" atau "idtemuan")
        $idTemuanRaw = $row['id_temuan'] ?? $row['idtemuan'] ?? null;

        // Jika id temuan tidak ada, kita log apa saja keys yang TERBACA dari excel ini
        if (empty($idTemuanRaw)) {
            if ($this->skipCount === 0) {
                // Lempar exception agar tertangkap di catatan error (ImportLog)
                $keys = implode(', ', array_keys($row));
                throw new \Exception("Kolom ID Temuan tidak ditemukan. Kolom yang terbaca dari Excel: [{$keys}]");
            }
            $this->skipCount++;
            return null;
        }

        $idTemuan = trim((string) $idTemuanRaw);

        if (empty($idTemuan)) {
            $this->skipCount++;
            return null;
        }

        $this->rowCount++;

        // Cek apakah record sudah ada (untuk counter insert vs update)
        $exists = SipekaFinding::where('id_temuan', $idTemuan)->exists();
        if ($exists) {
            $this->updateCount++;
        } else {
            $this->insertCount++;
        }

        // Sanitasi data_sipeka — buang key yang null/empty heading
        $rawData = array_filter(
            $row,
            fn ($key) => !empty($key) && $key !== '_empty_',
            ARRAY_FILTER_USE_KEY
        );

        $dataSipeka = [];
        foreach ($rawData as $k => $v) {
            // Normalisasi key agar sesuai dengan ekspektasi app (backward compatibility)
            // Laravel Excel mengubah "Foto Temuan" menjadi "foto_temuan", tapi app mengharapkan "fototemuan"
            $k = match ($k) {
                'foto_temuan'      => 'fototemuan',
                'foto_close'       => 'fotoclose',
                'unsafe_condition' => 'unsafe_conditon', // typo dari format Excel SIPEKA asli
                'id_temuan'        => 'idtemuan',
                default            => $k,
            };
            $dataSipeka[$k] = $v;
        }

        // Foto disimpan sebagai URL string — tidak didownload (Business Rule #5)

        return new SipekaFinding([
            'id_temuan'   => $idTemuan,
            'data_sipeka' => $dataSipeka,
            // no_notifikasi_sap dan keterangan_tindak_lanjut TIDAK disertakan di sini
            // sehingga tidak pernah di-overwrite (Business Rule #6)
        ]);
    }

    /**
     * Kolom yang menjadi acuan unique untuk Upsert.
     */
    public function uniqueBy(): string
    {
        return 'id_temuan';
    }

    /**
     * Kolom yang boleh di-update jika record sudah ada.
     *
     * Business Rule #6:
     *   no_notifikasi_sap dan keterangan_tindak_lanjut TIDAK ada di sini
     *   → tidak akan pernah ditimpa oleh import ulang Excel.
     */
    public function upsertColumns(): array
    {
        return ['data_sipeka', 'updated_at'];
    }

    // -----------------------------------------------------------------
    // COUNTER GETTERS (untuk ImportLog)
    // -----------------------------------------------------------------

    public function getRowCount(): int    { return $this->rowCount; }
    public function getInsertCount(): int { return $this->insertCount; }
    public function getUpdateCount(): int { return $this->updateCount; }
    public function getSkipCount(): int   { return $this->skipCount; }
    public function getErrorCount(): int  { return count($this->getErrors()) + count($this->failures()); }

    public function getErrors(): array
    {
        $messages = $this->errorMessages;

        foreach ($this->failures() as $failure) {
            $messages[] = "Baris {$failure->row()}: " . implode(', ', $failure->errors());
        }

        return $messages;
    }

    /**
     * Tangkap error teknis (exception) saat processing baris.
     */
    public function onError(Throwable $e): void
    {
        $this->errorMessages[] = $e->getMessage();
    }

    /**
     * Tangkap failure validasi per baris.
     * (SkipsFailures sudah menangani penyimpanan, ini override untuk logging)
     */
    public function onFailure(Failure ...$failures): void
    {
        foreach ($failures as $failure) {
            $this->errorMessages[] = "Baris {$failure->row()}: " . implode(', ', $failure->errors());
        }
    }
}
