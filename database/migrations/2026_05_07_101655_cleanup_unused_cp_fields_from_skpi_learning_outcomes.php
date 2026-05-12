<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('skpi_learning_outcomes', function (Blueprint $table) {
            $table->dropColumn([
                'cp_keterampilan_umum',
                'cp_keterampilan_umum_en',
                'cp_keterampilan_khusus',
                'cp_keterampilan_khusus_en'
            ]);
        });
    }

    public function down(): void
    {
        Schema::table('skpi_learning_outcomes', function (Blueprint $table) {
            $table->text('cp_keterampilan_umum')->nullable();
            $table->text('cp_keterampilan_umum_en')->nullable();
            $table->text('cp_keterampilan_khusus')->nullable();
            $table->text('cp_keterampilan_khusus_en')->nullable();
        });
    }
};
