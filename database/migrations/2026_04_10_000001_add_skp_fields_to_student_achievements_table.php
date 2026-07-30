<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('student_achievements', function (Blueprint $table) {
            // Sub-tipe kegiatan (pengurus_organisasi, lomba_kti, forum_ilmiah, dll.)
            $table->string('activity_type')->nullable()->after('category');

            // Jabatan / peran / prestasi (Ketua, Juara I, Pembicara, dll.)
            $table->string('participation_role')->nullable()->after('level');

            // Poin SKP hasil kalkulasi otomatis
            $table->unsignedSmallInteger('skp_points')->default(0)->after('participation_role');
        });
    }

    public function down(): void
    {
        Schema::table('student_achievements', function (Blueprint $table) {
            $table->dropColumn(['activity_type', 'participation_role', 'skp_points']);
        });
    }
};
