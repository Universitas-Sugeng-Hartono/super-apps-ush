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
            $table->string('lama_studi', 255)->nullable()->after('periode_lulus');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('skpi_registrations', function (Blueprint $table) {
            $table->dropColumn('lama_studi');
        });
    }
};
