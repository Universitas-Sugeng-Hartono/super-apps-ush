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
            $table->decimal('ipk', 3, 2)->nullable()->after('gelar');
            $table->integer('sks')->nullable()->after('ipk');
            $table->string('judul_ta_indo')->nullable()->after('sks');
            $table->string('judul_ta_inggris')->nullable()->after('judul_ta_indo');
            $table->string('periode_lulus', 100)->nullable()->after('judul_ta_inggris');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('skpi_registrations', function (Blueprint $table) {
            $table->dropColumn(['ipk', 'sks', 'judul_ta_indo', 'judul_ta_inggris', 'periode_lulus']);
        });
    }
};
