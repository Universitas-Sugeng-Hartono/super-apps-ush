<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('card_counselings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_student')   // kolom foreign key
                  ->constrained('students', 'id')
                  ->onDelete('cascade');
            $table->unsignedTinyInteger('semester');
            $table->unsignedSmallInteger('sks');
            $table->decimal('ip', 3, 2)->nullable();
            $table->date('tanggal');
            $table->text('failed_courses')->nullable();
            $table->text('retaken_courses')->nullable();
            $table->text('komentar')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('card_counselings');
    }
};