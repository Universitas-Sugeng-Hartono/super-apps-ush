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
        Schema::table('students', function (Blueprint $table) {
            if (!Schema::hasColumn('students', 'ipk')) {
                $table->decimal('ipk', 3, 2)->nullable()->after('email')->comment('Indeks Prestasi Kumulatif');
            }
            if (!Schema::hasColumn('students', 'sks')) {
                $table->integer('sks')->nullable()->after('ipk')->comment('Jumlah SKS yang telah ditempuh');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            if (Schema::hasColumn('students', 'ipk')) {
                $table->dropColumn('ipk');
            }
            if (Schema::hasColumn('students', 'sks')) {
                $table->dropColumn('sks');
            }
        });
    }
};
