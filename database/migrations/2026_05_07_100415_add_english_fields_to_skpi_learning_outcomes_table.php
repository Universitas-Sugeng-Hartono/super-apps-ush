<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('skpi_learning_outcomes', function (Blueprint $table) {
            $table->text('cp_sikap_en')->nullable()->after('cp_sikap');
            $table->text('cp_pengetahuan_en')->nullable()->after('cp_pengetahuan');
            $table->text('cp_keterampilan_umum_en')->nullable()->after('cp_keterampilan_umum');
            $table->text('cp_keterampilan_khusus_en')->nullable()->after('cp_keterampilan_khusus');
        });
    }

    public function down(): void
    {
        Schema::table('skpi_learning_outcomes', function (Blueprint $table) {
            $table->dropColumn(['cp_sikap_en', 'cp_pengetahuan_en', 'cp_keterampilan_umum_en', 'cp_keterampilan_khusus_en']);
        });
    }
};
