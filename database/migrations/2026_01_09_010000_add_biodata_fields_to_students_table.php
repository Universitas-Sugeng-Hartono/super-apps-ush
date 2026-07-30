<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->string('nik', 16)->nullable()->after('nim');
            $table->string('nisn', 20)->nullable()->after('nik');
            $table->string('tempat_lahir', 100)->nullable()->after('jenis_kelamin');
            $table->string('nama_ibu_kandung', 200)->nullable()->after('nama_orangtua');
        });
    }

    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->dropColumn(['nik', 'nisn', 'tempat_lahir', 'nama_ibu_kandung']);
        });
    }
};

