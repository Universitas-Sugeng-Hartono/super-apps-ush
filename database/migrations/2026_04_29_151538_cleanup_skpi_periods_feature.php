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
        // 1. Hapus foreign key dan kolom skpi_period_id dari skpi_registrations (jika ada)
        if (Schema::hasColumn('skpi_registrations', 'skpi_period_id')) {
            Schema::table('skpi_registrations', function (Blueprint $table) {
                // Cek apakah foreign key ada sebelum di-drop
                try {
                    $table->dropForeign(['skpi_period_id']);
                } catch (\Throwable $e) {
                    // Foreign key mungkin sudah tidak ada, lanjut saja
                }
                $table->dropColumn('skpi_period_id');
            });
        }

        // 2. Hapus tabel skpi_periods (jika ada)
        Schema::dropIfExists('skpi_periods');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::create('skpi_periods', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->date('start_date');
            $table->date('end_date');
            $table->boolean('is_active')->default(false);
            $table->timestamps();
        });

        Schema::table('skpi_registrations', function (Blueprint $table) {
            $table->unsignedBigInteger('skpi_period_id')->nullable()->after('student_id');
        });
    }
};
