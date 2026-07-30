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
        Schema::create('menu_items', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Nama menu
            $table->string('icon')->nullable(); // Icon (bootstrap icons class)
            $table->string('url')->nullable(); // URL atau route name
            $table->string('route_name')->nullable(); // Route name (alternatif dari URL)
            $table->string('roles')->nullable(); // Roles yang bisa akses (comma separated: student,admin,superadmin)
            $table->integer('order')->default(0); // Urutan menu
            $table->boolean('is_active')->default(true); // Aktif/tidak
            $table->string('target')->default('_self'); // _self, _blank
            $table->text('description')->nullable(); // Deskripsi menu
            $table->string('badge_text')->nullable(); // Text badge (opsional)
            $table->string('badge_color')->nullable(); // Warna badge (opsional)
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('menu_items');
    }
};
