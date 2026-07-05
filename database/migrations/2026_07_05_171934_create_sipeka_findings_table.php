<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('sipeka_findings', function (Blueprint $table) {
            $table->id();
            $table->string('id_temuan')->unique()->comment('Primary Reference dari Excel');
            $table->string('no_notifikasi_sap')->nullable()->comment('Dimasukkan lewat Dashboard');
            $table->text('keterangan_tindak_lanjut')->nullable()->comment('Dimasukkan lewat Dashboard');
            $table->json('data_sipeka')->nullable()->comment('Menyimpan raw 35 kolom dari Excel SIPEKA');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sipeka_findings');
    }
};
