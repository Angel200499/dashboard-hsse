<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * DeleteDummyManpowerSeeder
 *
 * Menghapus seluruh dummy manpower yang di-insert oleh DummyManpowerSeeder.
 *
 * AMAN: hanya menghapus bulan selain Mei (5) untuk tahun 2026.
 *
 * Jalankan:
 *   php artisan db:seed --class=DeleteDummyManpowerSeeder
 */
class DeleteDummyManpowerSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->warn('');
        $this->command->warn('Menghapus dummy manpower 2026 (bulan != Mei)...');

        // Backup: tampilkan dulu yang akan dihapus
        $rows = DB::table('master_manpowers')
            ->where('tahun', 2026)
            ->where('bulan', '!=', 5)
            ->get();

        if ($rows->isEmpty()) {
            $this->command->info('Tidak ada dummy manpower untuk dihapus.');
            return;
        }

        $this->command->info("Data yang akan dihapus ({$rows->count()} baris):");
        foreach ($rows as $r) {
            $this->command->line("  {$r->fungsi} | tahun {$r->tahun} | bulan {$r->bulan} | {$r->jumlah_manpower}");
        }

        $deleted = DB::table('master_manpowers')
            ->where('tahun', 2026)
            ->where('bulan', '!=', 5)
            ->delete();

        $this->command->info("✅ Berhasil menghapus {$deleted} baris dummy manpower.");

        // Verifikasi Mei masih ada
        $mei = DB::table('master_manpowers')
            ->where('tahun', 2026)
            ->where('bulan', 5)
            ->get();

        $this->command->info('--- Verifikasi Mei 2026 masih ada ---');
        foreach ($mei as $r) {
            $this->command->line("  {$r->fungsi} | bulan {$r->bulan} | {$r->jumlah_manpower}");
        }
    }
}
