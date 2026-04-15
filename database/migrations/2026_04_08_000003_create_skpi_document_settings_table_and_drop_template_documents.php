<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('skpi_document_settings', function (Blueprint $table) {
            $table->id();
            $table->string('nomor_skpi')->nullable();
            $table->string('authorization_place_date')->nullable();
            $table->string('vice_rector_name')->nullable();
            $table->string('vice_rector_title')->nullable();
            $table->string('signature_path')->nullable();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::dropIfExists('skpi_template_documents');
    }

    public function down(): void
    {
        Schema::dropIfExists('skpi_document_settings');

        Schema::create('skpi_template_documents', function (Blueprint $table) {
            $table->id();
            $table->string('title')->nullable();
            $table->string('file_path');
            $table->string('original_name');
            $table->string('mime_type')->nullable();
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }
};
