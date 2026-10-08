<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Tabel ini menyimpan master data personil (pekerja organik & TAD)
     * sebagai data acuan individual untuk fitur pemantauan pelaporan HSSE.
     *
     * Catatan Integritas:
     * - nopek dibuat NULLABLE karena personil TAD tidak memiliki NOPEK formal.
     * - Unique constraint pada nopek mengabaikan nilai NULL (sesuai standar SQL/MySQL).
     */
    public function up(): void
    {
        Schema::create('master_personils', function (Blueprint $table) {
            $table->id();
            $table->string('nama', 255)->comment('Nama lengkap personil');
            $table->string('nopek', 50)->nullable()->comment('Nomor Pekerja organik Pertamina (NULL untuk TAD)');
            $table->enum('fungsi', ['Operation', 'Maintenance', 'HSSE', 'Business Support'])
                  ->comment('Fungsi penempatan personil');
            $table->string('jabatan', 255)->nullable()->comment('Jabatan atau posisi personil');
            $table->enum('tipe_manpower', ['Pekerja', 'TAD'])
                  ->default('Pekerja')
                  ->comment('Status tipe manpower: Pekerja (organik) atau TAD (Tenaga Alih Daya)');
            $table->boolean('status_aktif')->default(true)->comment('Status aktif personil (true=aktif, false=non-aktif)');
            $table->timestamps();

            // Unique constraint pada nopek (NULL diperbolehkan untuk multiple rows)
            $table->unique('nopek', 'unique_master_personils_nopek');

            // Index pencarian dan filter
            $table->index(['fungsi', 'status_aktif'], 'idx_personils_fungsi_status');
            $table->index('nama', 'idx_personils_nama');
            $table->index('tipe_manpower', 'idx_personils_tipe');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('master_personils');
    }
};
