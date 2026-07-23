<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Menambah index pada tabel sipeka_findings untuk performa query 3000-10000+ record.
     */
    public function up(): void
    {
        Schema::table('sipeka_findings', function (Blueprint $table) {
            // Index untuk sorting dan filtering berdasarkan waktu
            $table->index('created_at', 'idx_sipeka_created_at');

            // Index untuk filter dan sorting no_notifikasi_sap
            // (digunakan untuk computed status "In Progress")
            $table->index('no_notifikasi_sap', 'idx_sipeka_no_sap');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sipeka_findings', function (Blueprint $table) {
            $table->dropIndex('idx_sipeka_created_at');
            $table->dropIndex('idx_sipeka_no_sap');
        });
    }
};
