<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('card_counselings', function (Blueprint $table) {
            if (!Schema::hasColumn('card_counselings', 'ipk')) {
                $table->decimal('ipk', 3, 2)->nullable()->after('ip')->comment('Indeks Prestasi Kumulatif saat bimbingan');
            }
        });
    }

    public function down(): void
    {
        Schema::table('card_counselings', function (Blueprint $table) {
            if (Schema::hasColumn('card_counselings', 'ipk')) {
                $table->dropColumn('ipk');
            }
        });
    }
};
