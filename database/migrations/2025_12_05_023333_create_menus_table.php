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
        Schema::create('menus', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Nama menu
            $table->string('icon')->nullable(); // Icon (bootstrap icons class)
            $table->string('url'); // URL route atau path
            $table->string('route_name')->nullable(); // Nama route (optional)
            $table->integer('order')->default(0); // Urutan tampil
            $table->enum('type', ['dashboard', 'sidebar', 'mobile'])->default('dashboard'); // Tipe menu
            $table->string('description')->nullable(); // Deskripsi menu
            $table->boolean('is_active')->default(true); // Aktif/tidak
            $table->string('badge_text')->nullable(); // Text badge (opsional)
            $table->string('badge_color')->nullable(); // Warna badge
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('menus');
    }
};
