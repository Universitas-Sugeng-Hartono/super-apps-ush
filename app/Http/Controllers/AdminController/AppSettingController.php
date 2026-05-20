<?php

namespace App\Http\Controllers\AdminController;

use App\Http\Controllers\Controller;
use App\Models\AppSetting;
use Illuminate\Http\Request;

class AppSettingController extends Controller
{
    // Semua role yang tersedia di sistem
    const AVAILABLE_ROLES = [
        'student'     => 'Mahasiswa',
        'admin'       => 'Dosen',
        'superadmin'  => 'Kaprodi',
        'masteradmin' => 'Superuser',
    ];

    /**
     * Halaman pengaturan WhatsApp
     */
    public function waSettings()
    {
        $settings = AppSetting::getGroup('whatsapp');
        $availableRoles = self::AVAILABLE_ROLES;
        $visibleRoles = AppSetting::getWaVisibleRoles();

        return view('admin.settings.whatsapp', compact('settings', 'availableRoles', 'visibleRoles'));
    }

    /**
     * Simpan pengaturan WhatsApp
     */
    public function saveWaSettings(Request $request)
    {
        $request->validate([
            'wa_number'           => ['required', 'string', 'regex:/^[0-9]{10,15}$/'],
            'wa_message_template' => ['required', 'string', 'max:500'],
            'wa_tooltip_title'    => ['required', 'string', 'max:100'],
            'wa_tooltip_message'  => ['required', 'string', 'max:300'],
            'wa_visible_roles'    => ['nullable', 'array'],
            'wa_visible_roles.*'  => ['in:student,admin,superadmin,masteradmin'],
        ], [
            'wa_number.regex'           => 'Nomor WA harus berformat angka, 10-15 digit (tanpa tanda + atau strip)',
            'wa_message_template.max'   => 'Template pesan maksimal 500 karakter',
            'wa_tooltip_title.max'      => 'Judul tooltip maksimal 100 karakter',
            'wa_tooltip_message.max'    => 'Pesan tooltip maksimal 300 karakter',
        ]);

        // Simpan toggle aktif/nonaktif
        AppSetting::set('wa_button_enabled', $request->has('wa_button_enabled') ? '1' : '0');

        // Simpan nomor & template
        AppSetting::set('wa_number', $request->wa_number);
        AppSetting::set('wa_message_template', $request->wa_message_template);
        AppSetting::set('wa_tooltip_title', $request->wa_tooltip_title);
        AppSetting::set('wa_tooltip_message', $request->wa_tooltip_message);

        // Simpan roles yang dipilih (sebagai JSON)
        $roles = $request->input('wa_visible_roles', []);
        AppSetting::set('wa_visible_roles', json_encode(array_values($roles)));

        // Bersihkan semua cache
        AppSetting::clearAllCache();

        return redirect()->route('admin.settings.whatsapp')
            ->with('success', 'Pengaturan WhatsApp berhasil disimpan!');
    }
}
