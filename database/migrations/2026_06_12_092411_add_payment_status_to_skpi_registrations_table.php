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
        Schema::table('skpi_registrations', function (Blueprint $table) {
            $table->string('payment_status')->default('pending')->after('doc_naskah_publikasi');
            $table->text('payment_approval_notes')->nullable()->after('payment_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('skpi_registrations', function (Blueprint $table) {
            $table->dropColumn(['payment_status', 'payment_approval_notes']);
        });
    }
};
