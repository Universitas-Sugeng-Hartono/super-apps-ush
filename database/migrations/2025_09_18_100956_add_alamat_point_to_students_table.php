<?php

// database/migrations/2025_09_18_000000_add_alamat_point_to_students.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('students', function (Blueprint $table) {
            $table->decimal('alamat_lat', 10, 7)->nullable()->after('alamat');
            $table->decimal('alamat_lng', 10, 7)->nullable()->after('alamat_lat');
        });
    }

    public function down()
    {
        Schema::table('students', function (Blueprint $table) {
            $table->dropColumn(['alamat_lat', 'alamat_lng']);
        });
    }
};