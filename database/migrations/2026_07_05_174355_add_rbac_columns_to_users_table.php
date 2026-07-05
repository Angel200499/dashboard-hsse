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
        Schema::table('users', function (Blueprint $table) {
            $table->string('username')->unique()->after('id');
            $table->string('email')->nullable()->change();
            $table->enum('role', ['Admin HSSE', 'Admin Function', 'Manager HSSE', 'Manager Function'])->after('password');
            $table->enum('fungsi', ['Operation', 'Maintenance', 'HSSE', 'Business Support'])->after('role');
            $table->boolean('is_active')->default(true)->after('fungsi');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['username', 'role', 'fungsi', 'is_active']);
            $table->string('email')->nullable(false)->change();
        });
    }
};
