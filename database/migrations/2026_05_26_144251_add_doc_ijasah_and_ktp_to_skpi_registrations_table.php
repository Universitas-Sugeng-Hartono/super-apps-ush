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
        Schema::table('skpi_registrations', function (Blueprint $table) {
            $table->string('doc_ijasah')->nullable()->after('nomor_ijazah');
            $table->string('doc_ktp')->nullable()->after('doc_ijasah');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('skpi_registrations', function (Blueprint $table) {
            $table->dropColumn(['doc_ijasah', 'doc_ktp']);
        });
    }
};
