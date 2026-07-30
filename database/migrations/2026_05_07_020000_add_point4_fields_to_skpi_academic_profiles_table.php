<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('skpi_learning_outcomes', function (Blueprint $table) {
            $table->id();

            $table->foreignId('study_program_id')
                ->unique()
                ->constrained('study_programs')
                ->cascadeOnUpdate()
                ->cascadeOnDelete()
                ->comment('FK ke study_programs – satu prodi satu record');

            $table->text('cp_sikap')->nullable()
                ->comment('Point 4A: Capaian Pembelajaran – Sikap | Placeholder: ${CP_SIKAP}');
            $table->text('cp_pengetahuan')->nullable()
                ->comment('Point 4B: Capaian Pembelajaran – Pengetahuan | Placeholder: ${CP_PENGETAHUAN}');
            $table->text('cp_keterampilan_umum')->nullable()
                ->comment('Point 4C: Capaian Pembelajaran – Keterampilan Umum | Placeholder: ${CP_KTR_UMUM}');
            $table->text('cp_keterampilan_khusus')->nullable()
                ->comment('Point 4D: Capaian Pembelajaran – Keterampilan Khusus | Placeholder: ${CP_KTR_KHUSUS}');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('skpi_learning_outcomes');
    }
};
