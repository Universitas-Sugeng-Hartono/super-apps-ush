<?php

use Illuminate\Support\Facades\DB;
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
        DB::statement("ALTER TABLE skpi_registrations MODIFY COLUMN status ENUM('draft', 'pending', 'approved', 'needs_revision', 'rejected') DEFAULT 'draft'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Peringatan: jika di-rollback, baris yang statusnya 'draft' bisa jadi error atau dikonversi ke nilai lain
        DB::statement("ALTER TABLE skpi_registrations MODIFY COLUMN status ENUM('pending', 'approved', 'needs_revision', 'rejected') DEFAULT 'pending'");
    }
};
