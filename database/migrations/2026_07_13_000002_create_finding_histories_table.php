<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Menyimpan audit trail setiap perubahan No. Notifikasi SAP dan Keterangan Tindak Lanjut.
     */
    public function up(): void
    {
        Schema::create('finding_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('finding_id')
                  ->constrained('sipeka_findings')
                  ->onDelete('cascade');
            $table->foreignId('updated_by')
                  ->constrained('users')
                  ->onDelete('cascade');
            $table->string('old_no_notifikasi_sap')->nullable();
            $table->string('new_no_notifikasi_sap')->nullable();
            $table->text('old_keterangan_tindak_lanjut')->nullable();
            $table->text('new_keterangan_tindak_lanjut')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('finding_histories');
    }
};
