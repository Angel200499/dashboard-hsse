<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Tabel ini menyimpan pemetaan nilai FUNGSI asli dari data SIPEKA
     * ke salah satu dari 4 fungsi utama Dashboard HSSE:
     *   Operation | Maintenance | HSSE | Business Support
     *
     * Unique constraint pada fungsi_sipeka: satu nilai FUNGSI SIPEKA
     * hanya boleh memiliki satu mapping tujuan.
     *
     * Data SIPEKA (sipeka_findings) TIDAK diubah oleh tabel ini.
     * Mapping hanya berfungsi sebagai layer referensi untuk kebutuhan dashboard.
     */
    public function up(): void
    {
        Schema::create('master_function_mappings', function (Blueprint $table) {
            $table->id();

            $table->string('fungsi_sipeka', 255)
                  ->comment('Nilai FUNGSI asli dari data SIPEKA (tidak diubah)');

            $table->string('fungsi_dashboard', 50)
                  ->comment('Fungsi dashboard tujuan: Operation|Maintenance|HSSE|Business Support');

            $table->timestamps();

            // Satu nilai fungsi_sipeka hanya boleh punya satu mapping tujuan
            $table->unique('fungsi_sipeka', 'unique_fungsi_sipeka');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('master_function_mappings');
    }
};
