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
        Schema::table('students', function (Blueprint $table) {
            $table->string('beasiswa_acc_keuangan', 20)->nullable()->after('beasiswa');
            $table->string('status_rekomendasi_siakad', 50)->nullable()->after('beasiswa_acc_keuangan');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->dropColumn(['beasiswa_acc_keuangan', 'status_rekomendasi_siakad']);
        });
    }
};
