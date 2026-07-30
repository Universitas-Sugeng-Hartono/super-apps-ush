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
        Schema::create('final_projects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->onDelete('cascade');
            $table->string('title')->nullable();
            $table->timestamp('title_approved_at')->nullable();
            $table->foreignId('supervisor_1_id')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('supervisor_2_id')->nullable()->constrained('users')->onDelete('set null');
            $table->enum('status', ['proposal', 'research', 'defense', 'completed', 'rejected'])->default('proposal');
            $table->integer('progress_percentage')->default(0);
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('final_projects');
    }
};
