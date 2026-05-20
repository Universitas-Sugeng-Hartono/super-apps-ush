<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('app_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->string('label')->nullable();        // Label tampilan di form admin
            $table->string('group')->default('general'); // Kelompok setting
            $table->string('type')->default('text');    // text | textarea | json | boolean
            $table->timestamps();
        });

        // Seed default WA button settings
        DB::table('app_settings')->insert([
            [
                'key'   => 'wa_button_enabled',
                'value' => '1',
                'label' => 'Aktifkan Tombol WhatsApp',
                'group' => 'whatsapp',
                'type'  => 'boolean',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key'   => 'wa_number',
                'value' => '6289613942890',
                'label' => 'Nomor WhatsApp Admin',
                'group' => 'whatsapp',
                'type'  => 'text',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key'   => 'wa_message_template',
                'value' => 'Halo Admin USH, saya mahasiswa yang ingin bertanya mengenai...',
                'label' => 'Template Pesan WhatsApp',
                'group' => 'whatsapp',
                'type'  => 'textarea',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key'   => 'wa_visible_roles',
                'value' => '["student"]',
                'label' => 'Tampilkan Kepada Role',
                'group' => 'whatsapp',
                'type'  => 'json',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key'   => 'wa_tooltip_title',
                'value' => 'Butuh bantuan? 👋',
                'label' => 'Judul Tooltip',
                'group' => 'whatsapp',
                'type'  => 'text',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key'   => 'wa_tooltip_message',
                'value' => 'Halo! Ada yang bisa kami bantu?\nSilakan chat langsung dengan Admin lewat WhatsApp. ✨',
                'label' => 'Pesan Tooltip',
                'group' => 'whatsapp',
                'type'  => 'textarea',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('app_settings');
    }
};
