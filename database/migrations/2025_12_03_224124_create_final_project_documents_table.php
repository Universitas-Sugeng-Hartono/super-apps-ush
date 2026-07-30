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
        Schema::create('final_project_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('final_project_id')->constrained('final_projects')->onDelete('cascade');
            $table->enum('document_type', ['proposal', 'chapter', 'full_draft', 'final', 'presentation', 'other'])->default('other');
            $table->string('title');
            $table->string('file_path');
            $table->integer('version')->default(1);
            $table->foreignId('uploaded_by')->constrained('students')->onDelete('cascade');
            $table->timestamp('uploaded_at')->nullable();
            $table->enum('review_status', ['pending', 'approved', 'needs_revision', 'rejected'])->default('pending');
            $table->foreignId('reviewer_id')->nullable()->constrained('users')->onDelete('set null');
            $table->text('review_notes')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('final_project_documents');
    }
};
