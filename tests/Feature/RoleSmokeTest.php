<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\MenuItem;
use App\Http\Controllers\AdminController\AppSettingController;
use Illuminate\Support\Facades\Route;

class RoleSmokeTest extends TestCase
{
    public function test_user_model_role_labels_contain_kemahasiswaan_and_keuangan(): void
    {
        $this->assertArrayHasKey('kemahasiswaan', User::ROLE_LABELS);
        $this->assertEquals('Kemahasiswaan', User::ROLE_LABELS['kemahasiswaan']);

        $this->assertArrayHasKey('keuangan', User::ROLE_LABELS);
        $this->assertEquals('Keuangan', User::ROLE_LABELS['keuangan']);
    }

    public function test_user_role_normalization_and_label(): void
    {
        $this->assertEquals('kemahasiswaan', User::normalizeRole('kemahasiswaan'));
        $this->assertEquals('keuangan', User::normalizeRole('keuangan'));

        $this->assertEquals('Kemahasiswaan', User::roleLabel('kemahasiswaan'));
        $this->assertEquals('Keuangan', User::roleLabel('keuangan'));
    }

    public function test_app_setting_controller_available_roles_contain_new_roles(): void
    {
        $this->assertArrayHasKey('kemahasiswaan', AppSettingController::AVAILABLE_ROLES);
        $this->assertArrayHasKey('keuangan', AppSettingController::AVAILABLE_ROLES);
    }

    public function test_routes_middleware_configuration(): void
    {
        $routeVerifData = Route::getRoutes()->getByName('admin.skpi.verifikasi-data.index');
        $this->assertNotNull($routeVerifData);
        $this->assertContains('role:masteradmin,kemahasiswaan', $routeVerifData->middleware());

        $routeVerifBayar = Route::getRoutes()->getByName('admin.skpi.verifikasi-pembayaran.index');
        $this->assertNotNull($routeVerifBayar);
        $this->assertContains('role:masteradmin,keuangan', $routeVerifBayar->middleware());

        $routeDashboard = Route::getRoutes()->getByName('admin.dashboard');
        $this->assertNotNull($routeDashboard);
        $this->assertContains('role:admin,superadmin,masteradmin,kemahasiswaan,keuangan', $routeDashboard->middleware());
    }

    public function test_kemahasiswaan_can_access_dashboard_and_verifikasi_data(): void
    {
        $user = new User([
            'id' => 99991,
            'name' => 'Staf Kemahasiswaan',
            'email' => 'kemahasiswaan@test.com',
            'role' => 'kemahasiswaan',
            'program_studi' => 'Bisnis Digital',
        ]);

        $response = $this->actingAs($user)->get(route('admin.dashboard'));
        $this->assertTrue(in_array($response->status(), [200, 302]));

        $responseVerif = $this->actingAs($user)->get(route('admin.skpi.verifikasi-data.index'));
        $this->assertEquals(200, $responseVerif->status());

        // Kemahasiswaan should not be allowed into verifikasi pembayaran
        $responseBayar = $this->actingAs($user)->get(route('admin.skpi.verifikasi-pembayaran.index'));
        $this->assertTrue(in_array($responseBayar->status(), [302, 403]));
    }

    public function test_keuangan_can_access_dashboard_and_verifikasi_pembayaran(): void
    {
        $user = new User([
            'id' => 99992,
            'name' => 'Staf Keuangan',
            'email' => 'keuangan@test.com',
            'role' => 'keuangan',
            'program_studi' => 'Bisnis Digital',
        ]);

        $response = $this->actingAs($user)->get(route('admin.dashboard'));
        $this->assertTrue(in_array($response->status(), [200, 302]));

        $responseBayar = $this->actingAs($user)->get(route('admin.skpi.verifikasi-pembayaran.index'));
        $this->assertEquals(200, $responseBayar->status());

        // Keuangan should not be allowed into verifikasi data prestasi
        $responseVerif = $this->actingAs($user)->get(route('admin.skpi.verifikasi-data.index'));
        $this->assertTrue(in_array($responseVerif->status(), [302, 403]));
    }
}
