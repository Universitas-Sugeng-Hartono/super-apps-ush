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
        if (!Schema::hasColumn('skpi_academic_profiles', 'gelar_lulusan')) {
            Schema::table('skpi_academic_profiles', function (Blueprint $table) {
                $table->string('gelar_lulusan', 100)->nullable()->after('status_profesi');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('skpi_academic_profiles', 'gelar_lulusan')) {
            Schema::table('skpi_academic_profiles', function (Blueprint $table) {
                $table->dropColumn('gelar_lulusan');
            });
        }
    }
};
