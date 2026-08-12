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
        Schema::table('student_achievements', function (Blueprint $table) {
            $table->string('organizer')->nullable()->after('event')->comment('Penyelenggara kegiatan');
            $table->string('event_year')->nullable()->after('organizer')->comment('Tahun kegiatan diselenggarakan');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('student_achievements', function (Blueprint $table) {
            $table->dropColumn(['organizer', 'event_year']);
        });
    }
};
