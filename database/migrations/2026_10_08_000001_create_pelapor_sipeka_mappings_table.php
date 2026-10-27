<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Tabel relasi MANUAL antara nama pelapor di SIPEKA (data_sipeka.pelapor)
     * dengan Master Personil. Tidak ada fuzzy matching — setiap baris dibuat
     * oleh Admin HSSE.
     *
     * Catatan Integritas:
     * - Mapping menunjuk ke master_personils.id (bukan salinan nama/NOPEK),
     *   sehingga perubahan nama/NOPEK master tidak merusak relasi.
     * - Tabel ini TIDAK mengubah sipeka_findings maupun master_manpowers.
     * - Hanya dipakai oleh fitur "Daftar Belum Melapor", bukan grafik Keterlibatan.
     */
    public function up(): void
    {
        Schema::create('pelapor_sipeka_mappings', function (Blueprint $table) {
            $table->id();
            $table->string('nama_sipeka', 255)->comment('Nama pelapor apa adanya dari SIPEKA (untuk tampilan)');
            $table->string('nama_sipeka_normalized', 255)->comment('trim + whitespace dirapikan + lowercase');
            $table->foreignId('master_personil_id')
                  ->constrained('master_personils')
                  ->cascadeOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            // Satu nama SIPEKA (ternormalisasi) hanya boleh dipetakan ke satu personil
            $table->unique('nama_sipeka_normalized', 'unique_pelapor_mapping_normalized');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pelapor_sipeka_mappings');
    }
};
