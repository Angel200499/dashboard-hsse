<?php

namespace Database\Seeders;

use App\Models\MasterPersonil;
use App\Services\UnreportedPersonilService;
use Illuminate\Database\Seeder;
use PhpOffice\PhpSpreadsheet\IOFactory;

/**
 * Mengisi `master_personils` dari data aktual yang diberikan mentor:
 *   - Data Pekerja Umum MEI 2026.XLSX                       (sheet "m sheet")
 *   - Data TAD Area Lahendong Periode Tahun 2026.xlsx       (sheet "TAD 2026")
 *
 * Hanya field yang dibutuhkan yang diambil: Nama, NOPEK (Pekerja), Fungsi, Jabatan,
 * Tipe Manpower, Status Aktif. Data pribadi lain (NIK, alamat, telepon, tgl lahir, dst.)
 * TIDAK dibaca/disimpan. EEID TAD TIDAK dianggap NOPEK.
 *
 * Aman dijalankan ulang (idempotent, tanpa duplikat). Tidak ada data dummy.
 *
 * Jalankan:
 *   php artisan db:seed --class=MasterPersonilSeeder
 *
 * Lokasi file dapat diatur lewat .env:
 *   PERSONIL_PEKERJA_XLSX="C:\path\Data Pekerja Umum MEI 2026.XLSX"
 *   PERSONIL_TAD_XLSX="C:\path\Data TAD Area Lahendong Periode Tahun 2026.xlsx"
 */
class MasterPersonilSeeder extends Seeder
{
    /** Mapping nilai kolom Fungsi sumber → 4 fungsi dashboard (kunci: lowercase). */
    private const FUNGSI_MAP = [
        'operation'        => 'Operation',
        'operaton'         => 'Operation',   // typo pada file sumber TAD
        'maintenance'      => 'Maintenance',
        'hsse'             => 'HSSE',
        'business support' => 'Business Support',
        // Konfirmasi mentor: ADMIN, CS, SCM, ICT → Business Support
        'admin'            => 'Business Support',
        'cs'               => 'Business Support',
        'scm'              => 'Business Support',
        'ict'              => 'Business Support',
    ];

    private array $stats = [];

    public function run(): void
    {
        $pekerjaFile = env('PERSONIL_PEKERJA_XLSX', $this->downloads('Data Pekerja Umum MEI 2026.XLSX'));
        $tadFile     = env('PERSONIL_TAD_XLSX', $this->downloads('Data TAD Area Lahendong Periode Tahun 2026.xlsx'));

        foreach ([$pekerjaFile, $tadFile] as $f) {
            if (!is_file($f)) {
                $this->command?->error("File tidak ditemukan: {$f}");
                return;
            }
        }

        $this->stats = [
            'Pekerja' => ['baru' => 0, 'update' => 0, 'dilewati' => []],
            'TAD'     => ['baru' => 0, 'update' => 0, 'dilewati' => []],
        ];

        $this->importPekerja($pekerjaFile);
        $this->importTad($tadFile);
        $this->report();
    }

    // -----------------------------------------------------------------

    private function importPekerja(string $file): void
    {
        $sheet = IOFactory::load($file)->getSheetByName('m sheet');
        if (!$sheet) {
            $this->command?->error('Sheet "m sheet" tidak ditemukan pada file Pekerja.');
            return;
        }

        // Kolom: B=NOPEK, C=Nama Pegawai, F=Fungsi, H=Jabatan
        foreach ($sheet->toArray(null, true, false, true) as $i => $row) {
            if ($i === 1) {
                continue; // header
            }

            $nama = $this->cleanText($row['C'] ?? null);
            if ($nama === '') {
                continue;
            }

            $fungsiRaw = $this->cleanText($row['F'] ?? null);
            $fungsi    = self::FUNGSI_MAP[mb_strtolower($fungsiRaw)] ?? null;
            if (!$fungsi) {
                $this->stats['Pekerja']['dilewati'][] = "{$nama} (fungsi '{$fungsiRaw}' bukan salah satu dari 4 fungsi dashboard)";
                continue;
            }

            $this->upsert('Pekerja', [
                'nama'    => $nama,
                'nopek'   => $this->cleanNopek($row['B'] ?? null),
                'fungsi'  => $fungsi,
                'jabatan' => $this->cleanText($row['H'] ?? null) ?: null,
            ]);
        }
    }

    private function importTad(string $file): void
    {
        $sheet = IOFactory::load($file)->getSheetByName('TAD 2026');
        if (!$sheet) {
            $this->command?->error('Sheet "TAD 2026" tidak ditemukan pada file TAD.');
            return;
        }

        // Kolom: J=Fungsi, P=Nama Lengkap TAD, Q=jabatan  (EEID kolom O sengaja diabaikan)
        foreach ($sheet->toArray(null, true, false, true) as $i => $row) {
            if ($i === 1) {
                continue; // header
            }

            $nama = $this->cleanText($row['P'] ?? null);
            if ($nama === '') {
                continue;
            }

            // Baris placeholder posisi kosong, bukan personil nyata
            if (str_starts_with(mb_strtolower($nama), 'vacant')) {
                $this->stats['TAD']['dilewati'][] = 'VACANT (posisi kosong)';
                continue;
            }

            $fungsiRaw = $this->cleanText($row['J'] ?? null);
            $fungsi    = self::FUNGSI_MAP[mb_strtolower($fungsiRaw)] ?? null;
            if (!$fungsi) {
                $this->stats['TAD']['dilewati'][] = "{$nama} (fungsi '{$fungsiRaw}' tidak dikenali)";
                continue;
            }

            $this->upsert('TAD', [
                'nama'    => $nama,
                'nopek'   => null, // TAD tidak punya NOPEK; tidak dibuat/ditebak
                'fungsi'  => $fungsi,
                'jabatan' => $this->cleanText($row['Q'] ?? null) ?: null,
            ]);
        }
    }

    /**
     * Prioritas identitas:
     *  1. NOPEK (bila ada)
     *  2. Nama ternormalisasi + fungsi + tipe manpower (TAD / Pekerja tanpa NOPEK)
     */
    private function upsert(string $tipe, array $d): void
    {
        $attrs = [
            'nama'          => $d['nama'],
            'fungsi'        => $d['fungsi'],
            'jabatan'       => $d['jabatan'],
            'tipe_manpower' => $tipe,
            'status_aktif'  => true,
        ];

        if ($d['nopek']) {
            $existing = MasterPersonil::where('nopek', $d['nopek'])->first();
        } else {
            $key      = UnreportedPersonilService::normalizeName($d['nama']);
            $existing = MasterPersonil::whereNull('nopek')
                ->where('tipe_manpower', $tipe)
                ->where('fungsi', $d['fungsi'])
                ->get()
                ->first(fn (MasterPersonil $p) => UnreportedPersonilService::normalizeName($p->nama) === $key);
        }

        if ($existing) {
            $existing->update($attrs);
            $this->stats[$tipe]['update']++;
        } else {
            MasterPersonil::create($attrs + ['nopek' => $d['nopek']]);
            $this->stats[$tipe]['baru']++;
        }
    }

    private function report(): void
    {
        if (!$this->command) {
            return;
        }

        foreach ($this->stats as $tipe => $s) {
            $this->command->info("{$tipe}: baru={$s['baru']}, diperbarui={$s['update']}, dilewati=" . count($s['dilewati']));
            foreach (array_count_values($s['dilewati']) as $msg => $n) {
                $this->command->warn("   dilewati x{$n}: {$msg}");
            }
        }

        $this->command->info('Total master_personils aktif: ' . MasterPersonil::active()->count()
            . ' (Pekerja ' . MasterPersonil::active()->byTipe('Pekerja')->count()
            . ', TAD ' . MasterPersonil::active()->byTipe('TAD')->count() . ')');
    }

    // -----------------------------------------------------------------

    private function cleanText($v): string
    {
        return trim((string) preg_replace('/\s+/u', ' ', (string) ($v ?? '')));
    }

    private function cleanNopek($v): ?string
    {
        if ($v === null || $v === '') {
            return null;
        }
        if (is_float($v) && floor($v) == $v) {
            $v = (int) $v;
        }
        $v = trim((string) $v);

        return $v === '' ? null : $v;
    }

    private function downloads(string $file): string
    {
        return rtrim((string) (getenv('USERPROFILE') ?: getenv('HOME')), '\\/') . DIRECTORY_SEPARATOR . 'Downloads' . DIRECTORY_SEPARATOR . $file;
    }
}
