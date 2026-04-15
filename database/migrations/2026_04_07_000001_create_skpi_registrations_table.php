<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('skpi_registrations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->unique()->constrained('students')->cascadeOnDelete();
            $table->string('nama_lengkap', 200);
            $table->string('tempat_lahir', 100);
            $table->date('tanggal_lahir');
            $table->string('nim', 20);
            $table->integer('angkatan');
            $table->string('nomor_ijazah', 100);
            $table->string('gelar', 100);
            $table->enum('status', ['pending', 'approved', 'needs_revision', 'rejected'])->default('pending');
            $table->text('approval_notes')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('skpi_registrations');
    }
};
