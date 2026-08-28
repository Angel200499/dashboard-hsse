<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Tabel ini menyimpan jumlah manpower per fungsi per tahun.
     * Digunakan sebagai denominator dalam perhitungan Reporting Rate.
     *
     * Unique constraint: satu fungsi hanya boleh memiliki satu record per tahun.
     */
    public function up(): void
    {
        Schema::create('master_manpowers', function (Blueprint $table) {
            $table->id();
            $table->enum('fungsi', ['Operation', 'Maintenance', 'HSSE', 'Business Support'])
                  ->comment('Fungsi utama dashboard HSSE');
            $table->smallInteger('tahun')->unsigned()
                  ->comment('Tahun data manpower');
            $table->integer('jumlah_manpower')->unsigned()
                  ->comment('Total jumlah manpower pada fungsi dan tahun tersebut');
            $table->timestamps();

            // Satu fungsi hanya boleh punya satu data manpower per tahun
            $table->unique(['fungsi', 'tahun'], 'unique_fungsi_tahun');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('master_manpowers');
    }
};
