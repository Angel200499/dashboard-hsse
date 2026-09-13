<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tambahkan kolom kelompok_khusus ke tabel master_function_mappings.
     *
     * Kolom ini digunakan untuk mengidentifikasi mapping yang bukan merupakan
     * bagian dari 4 fungsi utama Dashboard HSSE, melainkan kelompok khusus
     * seperti GM (General Manager).
     *
     * Contoh penggunaan:
     *   fungsi_sipeka  = 'Area Lahendong'
     *   fungsi_dashboard = NULL   ← bukan salah satu dari 4 fungsi utama
     *   kelompok_khusus = 'GM'   ← dikenali sebagai kelompok GM
     *
     * Migration ini BERSIFAT NON-DESTRUCTIVE:
     *   - Tidak menghapus data existing
     *   - Tidak mengubah data existing
     *   - Tidak mengubah struktur kolom yang sudah ada
     *   - Hanya menambah kolom baru dengan default NULL
     */
    public function up(): void
    {
        Schema::table('master_function_mappings', function (Blueprint $table) {
            $table->string('kelompok_khusus', 50)
                  ->nullable()
                  ->default(null)
                  ->after('fungsi_dashboard')
                  ->comment('Kelompok khusus di luar 4 fungsi utama, misal: GM. NULL jika bukan kelompok khusus.');
        });
    }

    /**
     * Reverse the migrations.
     *
     * Drop kolom kelompok_khusus saja — data lain tetap utuh.
     */
    public function down(): void
    {
        Schema::table('master_function_mappings', function (Blueprint $table) {
            $table->dropColumn('kelompok_khusus');
        });
    }
};
