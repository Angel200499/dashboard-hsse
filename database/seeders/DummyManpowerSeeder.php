<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * DummyManpowerSeeder
 *
 * =========================================================
 * DUMMY / DEVELOPMENT ONLY — JANGAN DIGUNAKAN DI PRODUCTION
 * =========================================================
 *
 * Seeder ini mengisi data manpower DUMMY untuk tahun 2026,
 * bulan Januari–April dan Juni–Desember.
 *
 * Nilai diambil dari data ASLI Mei 2026:
 *   Operation: 68, Maintenance: 53, HSSE: 83, Business Support: 94
 *
 * Tujuan: testing fitur Reporting Rate berbasis manpower bulanan.
 *
 * CATATAN PENTING:
 *   - Bulan 5 (Mei 2026) TIDAK disentuh (menggunakan insertOrIgnore + skip bulan 5)
 *   - Data ini dapat dihapus sepenuhnya dengan menjalankan:
 *       php artisan db:seed --class=DummyManpowerSeeder --rollback
 *     ATAU dengan perintah artisan custom di bawah, ATAU dengan query:
 *       DELETE FROM master_manpowers
 *         WHERE tahun = 2026 AND bulan != 5
 *         AND jumlah_manpower IN (68, 53, 83, 94)
 *         AND created_at >= '2026-09-03';
 *
 * CARA HAPUS YANG PALING AMAN:
 *   php artisan db:seed --class=DeleteDummyManpowerSeeder
 */
class DummyManpowerSeeder extends Seeder
{
    /**
     * Tahun dummy manpower.
     */
    private const TAHUN = 2026;

    /**
     * Bulan yang akan diisi dummy (Mei = 5 DIKECUALIKAN).
     */
    private const BULAN_DUMMY = [1, 2, 3, 4, 6, 7, 8, 9, 10, 11, 12];

    /**
     * Nilai dummy manpower per fungsi (disamakan dengan data Mei 2026 yang asli).
     */
    private const DUMMY_VALUES = [
        'Operation'        => 68,
        'Maintenance'      => 53,
        'HSSE'             => 83,
        'Business Support' => 94,
    ];

    public function run(): void
    {
        $this->command->info('');
        $this->command->warn('=========================================');
        $this->command->warn('  DUMMY MANPOWER SEEDER — DEV/TEST ONLY');
        $this->command->warn('=========================================');
        $this->command->info('Mengisi dummy manpower 2026 (bulan Mei TIDAK disentuh)...');

        $inserted = 0;
        $skipped  = 0;

        foreach (self::BULAN_DUMMY as $bulan) {
            foreach (self::DUMMY_VALUES as $fungsi => $jumlah) {
                // Cek apakah sudah ada data untuk fungsi+tahun+bulan ini
                $exists = DB::table('master_manpowers')
                    ->where('fungsi', $fungsi)
                    ->where('tahun', self::TAHUN)
                    ->where('bulan', $bulan)
                    ->exists();

                if ($exists) {
                    $this->command->line("  ⏭  Skip (sudah ada): {$fungsi} | " . self::TAHUN . " | bulan {$bulan}");
                    $skipped++;
                    continue;
                }

                DB::table('master_manpowers')->insert([
                    'fungsi'          => $fungsi,
                    'tahun'           => self::TAHUN,
                    'bulan'           => $bulan,
                    'jumlah_manpower' => $jumlah,
                    'created_at'      => now(),
                    'updated_at'      => now(),
                ]);

                $this->command->line("  ✅ Insert: {$fungsi} | " . self::TAHUN . " | bulan {$bulan} | {$jumlah}");
                $inserted++;
            }
        }

        $this->command->info('');
        $this->command->info("Selesai. Insert: {$inserted}, Skip: {$skipped}");
        $this->command->warn('');
        $this->command->warn('REMINDER: Hapus dummy setelah testing selesai dengan:');
        $this->command->warn('  php artisan db:seed --class=DeleteDummyManpowerSeeder');
        $this->command->warn('');

        // Verifikasi Mei tidak tertimpa
        $mei = DB::table('master_manpowers')
            ->where('tahun', self::TAHUN)
            ->where('bulan', 5)
            ->get();

        $this->command->info('--- Verifikasi Mei 2026 (harus tetap sama) ---');
        foreach ($mei as $row) {
            $this->command->line("  {$row->fungsi} | bulan {$row->bulan} | {$row->jumlah_manpower}");
        }
    }
}
