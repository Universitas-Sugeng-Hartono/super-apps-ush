<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('skpi_registrations', function (Blueprint $table) {
            if (!Schema::hasColumn('skpi_registrations', 'nomor_skpi')) {
                $table->string('nomor_skpi')->nullable()->after('student_id');
            }
        });

        Schema::table('skpi_document_settings', function (Blueprint $table) {
            if (!Schema::hasColumn('skpi_document_settings', 'nomor_skpi_format')) {
                $table->string('nomor_skpi_format')->nullable()->default('{no}/SKPI/USH/{tahun}')->after('nomor_skpi');
            }
        });
    }

    public function down(): void
    {
        Schema::table('skpi_registrations', function (Blueprint $table) {
            if (Schema::hasColumn('skpi_registrations', 'nomor_skpi')) {
                $table->dropColumn('nomor_skpi');
            }
        });

        Schema::table('skpi_document_settings', function (Blueprint $table) {
            if (Schema::hasColumn('skpi_document_settings', 'nomor_skpi_format')) {
                $table->dropColumn('nomor_skpi_format');
            }
        });
    }
};
