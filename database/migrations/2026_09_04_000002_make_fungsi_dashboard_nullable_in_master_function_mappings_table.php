<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Ubah kolom fungsi_dashboard menjadi nullable.
     *
     * Sebelumnya kolom ini NOT NULL karena diasumsikan setiap mapping
     * pasti memiliki salah satu dari 4 fungsi utama.
     *
     * Perubahan ini diperlukan untuk mendukung mapping kelompok khusus
     * (seperti GM untuk Area Lahendong) yang tidak memiliki fungsi dashboard
     * dari 4 fungsi utama (Operation, Maintenance, HSSE, Business Support).
     *
     * Migration ini NON-DESTRUCTIVE:
     *   - Tidak menghapus data existing
     *   - Tidak mengubah data existing
     *   - Hanya mengubah constraint NOT NULL → NULL pada kolom fungsi_dashboard
     *   - Data existing yang sudah punya nilai fungsi_dashboard tidak berubah
     */
    public function up(): void
    {
        Schema::table('master_function_mappings', function (Blueprint $table) {
            $table->string('fungsi_dashboard', 50)
                  ->nullable()
                  ->default(null)
                  ->comment('Fungsi dashboard tujuan: Operation|Maintenance|HSSE|Business Support. NULL untuk mapping kelompok khusus.')
                  ->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * Kembalikan kolom fungsi_dashboard menjadi NOT NULL.
     * Perhatian: jika ada data dengan fungsi_dashboard = NULL, rollback ini akan gagal.
     */
    public function down(): void
    {
        // Update data NULL ke string kosong dahulu agar rollback aman
        \Illuminate\Support\Facades\DB::table('master_function_mappings')
            ->whereNull('fungsi_dashboard')
            ->update(['fungsi_dashboard' => '']);

        Schema::table('master_function_mappings', function (Blueprint $table) {
            $table->string('fungsi_dashboard', 50)
                  ->nullable(false)
                  ->default(null)
                  ->comment('Fungsi dashboard tujuan: Operation|Maintenance|HSSE|Business Support')
                  ->change();
        });
    }
};
