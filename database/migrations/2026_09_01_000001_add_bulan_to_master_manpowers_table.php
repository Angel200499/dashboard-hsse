<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration: Tambah kolom `bulan` ke tabel `master_manpowers`.
 *
 * PERUBAHAN:
 *   - Tambah kolom `bulan` (tinyInteger unsigned, nullable) setelah `tahun`.
 *   - Drop unique constraint lama `unique_fungsi_tahun` (fungsi, tahun).
 *   - Tambah unique constraint baru `unique_fungsi_tahun_bulan` (fungsi, tahun, bulan).
 *
 * KEAMANAN DATA:
 *   - Kolom `bulan` dibuat nullable agar data existing tidak rusak.
 *   - Data manpower lama yang belum memiliki bulan tetap tersimpan (bulan = NULL).
 *   - Tidak ada truncate, tidak ada DROP TABLE.
 *   - Admin dapat memperbarui atau menghapus data lama secara manual via UI.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('master_manpowers', function (Blueprint $table) {
            // Tambah kolom bulan setelah tahun, nullable agar data lama tidak rusak
            $table->tinyInteger('bulan')->unsigned()->nullable()
                  ->after('tahun')
                  ->comment('Bulan data manpower (1=Januari ... 12=Desember). Nullable untuk kompatibilitas data lama.');

            // Drop unique constraint lama (fungsi, tahun)
            $table->dropUnique('unique_fungsi_tahun');

            // Buat unique constraint baru (fungsi, tahun, bulan)
            // NULL tidak dianggap sama oleh MySQL sehingga data lama (bulan=NULL) tidak konflik
            $table->unique(['fungsi', 'tahun', 'bulan'], 'unique_fungsi_tahun_bulan');
        });
    }

    public function down(): void
    {
        Schema::table('master_manpowers', function (Blueprint $table) {
            $table->dropUnique('unique_fungsi_tahun_bulan');
            $table->dropColumn('bulan');

            // Kembalikan unique constraint lama
            $table->unique(['fungsi', 'tahun'], 'unique_fungsi_tahun');
        });
    }
};
