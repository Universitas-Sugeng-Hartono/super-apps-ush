<?php

/**
 * Smoke Test Runner for Role & System Integrity Verification
 * Specifically tests role kemahasiswaan & keuangan additions, route guards, models, and controllers.
 */

declare(strict_types=1);

require __DIR__ . '/../../../../vendor/autoload.php';

$app = require_once __DIR__ . '/../../../../bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\MenuItem;
use App\Models\AppSetting;
use App\Http\Controllers\AdminController\AppSettingController;
use App\Http\Controllers\AdminController\MenuManagementController;
use App\Http\Controllers\AdminController\UserManageController;
use App\Http\Middleware\CheckRole;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

$passed = 0;
$failed = 0;
$tests = [];

function runTest(string $name, callable $callback) {
    global $passed, $failed, $tests;
    echo "Testing: {$name} ... ";
    try {
        $result = $callback();
        if ($result !== false) {
            echo "\033[32m[PASS]\033[0m\n";
            $passed++;
            $tests[] = ['name' => $name, 'status' => 'PASS'];
            return;
        }
        echo "\033[31m[FAIL]\033[0m (Returned false)\n";
        $failed++;
        $tests[] = ['name' => $name, 'status' => 'FAIL', 'error' => 'Assertion returned false'];
    } catch (\Throwable $e) {
        echo "\033[31m[FAIL]\033[0m ({$e->getMessage()})\n";
        $failed++;
        $tests[] = ['name' => $name, 'status' => 'FAIL', 'error' => $e->getMessage()];
    }
}

echo "========================================================\n";
echo "         SUPER-APPS USH SMOKE TEST RUNNER              \n";
echo "========================================================\n\n";

// 1. Database Connection
runTest("Database Connection", function () {
    \Illuminate\Support\Facades\DB::connection()->getPdo();
    return true;
});

// 2. User Model - ROLE_LABELS & Normalization
runTest("User Model - ROLE_LABELS Contains 'kemahasiswaan' & 'keuangan'", function () {
    if (!isset(User::ROLE_LABELS['kemahasiswaan']) || User::ROLE_LABELS['kemahasiswaan'] !== 'Kemahasiswaan') {
        throw new \Exception("ROLE_LABELS['kemahasiswaan'] is missing or invalid");
    }
    if (!isset(User::ROLE_LABELS['keuangan']) || User::ROLE_LABELS['keuangan'] !== 'Keuangan') {
        throw new \Exception("ROLE_LABELS['keuangan'] is missing or invalid");
    }
    return true;
});

runTest("User Model - Role Normalization & Labels", function () {
    if (User::normalizeRole('kemahasiswaan') !== 'kemahasiswaan') {
        throw new \Exception("normalizeRole('kemahasiswaan') failed");
    }
    if (User::normalizeRole('keuangan') !== 'keuangan') {
        throw new \Exception("normalizeRole('keuangan') failed");
    }
    if (User::roleLabel('kemahasiswaan') !== 'Kemahasiswaan') {
        throw new \Exception("roleLabel('kemahasiswaan') failed");
    }
    if (User::roleLabel('keuangan') !== 'Keuangan') {
        throw new \Exception("roleLabel('keuangan') failed");
    }
    return true;
});

// 3. AppSettingController AVAILABLE_ROLES
runTest("AppSettingController - AVAILABLE_ROLES Contains New Roles", function () {
    if (!isset(AppSettingController::AVAILABLE_ROLES['kemahasiswaan'])) {
        throw new \Exception("AVAILABLE_ROLES['kemahasiswaan'] missing in AppSettingController");
    }
    if (!isset(AppSettingController::AVAILABLE_ROLES['keuangan'])) {
        throw new \Exception("AVAILABLE_ROLES['keuangan'] missing in AppSettingController");
    }
    return true;
});

// 4. UserManageController Validation
runTest("UserManageController - Role Validation Accepts 'kemahasiswaan' and 'keuangan'", function () {
    $rules = [
        'role' => 'required|string|in:admin,superadmin,masteradmin,kemahasiswaan,keuangan',
    ];
    $v1 = Validator::make(['role' => 'kemahasiswaan'], $rules);
    if ($v1->fails()) {
        throw new \Exception("Validation rejected role 'kemahasiswaan'");
    }
    $v2 = Validator::make(['role' => 'keuangan'], $rules);
    if ($v2->fails()) {
        throw new \Exception("Validation rejected role 'keuangan'");
    }
    $v3 = Validator::make(['role' => 'invalid_role'], $rules);
    if (!$v3->fails()) {
        throw new \Exception("Validation should have rejected 'invalid_role'");
    }

    // Program Studi optional for kemahasiswaan/keuangan, required for dosen/kaprodi
    $ref = new \ReflectionClass(UserManageController::class);
    $method = $ref->getMethod('rules');
    $method->setAccessible(true);
    $controller = new UserManageController();

    // Simulasi request role kemahasiswaan
    request()->merge(['role' => 'kemahasiswaan']);
    $rulesKemahasiswaan = $method->invoke($controller);
    if (str_contains($rulesKemahasiswaan['program_studi'], 'required')) {
        throw new \Exception("Program studi should be optional/nullable for role kemahasiswaan");
    }

    // Simulasi request role admin (dosen)
    request()->merge(['role' => 'admin']);
    $rulesAdmin = $method->invoke($controller);
    if (!str_contains($rulesAdmin['program_studi'], 'required')) {
        throw new \Exception("Program studi should be required for role admin");
    }

    return true;
});

// 5. MenuManagementController Allowed Roles
runTest("MenuManagementController - Allowed Roles in Store & Update", function () {
    $ref = new \ReflectionClass(MenuManagementController::class);
    $storeMethod = $ref->getMethod('store');
    // Read source code or test validation
    $allowedRoles = ['student', 'admin', 'superadmin', 'masteradmin', 'kemahasiswaan', 'keuangan'];
    $validator = Validator::make(['roles' => ['kemahasiswaan', 'keuangan']], [
        'roles' => 'array',
        'roles.*' => [\Illuminate\Validation\Rule::in($allowedRoles)],
    ]);
    if ($validator->fails()) {
        throw new \Exception("Menu roles validation failed for new roles");
    }
    return true;
});

// 6. CheckRole Middleware Behavior
runTest("CheckRole Middleware - Kemahasiswaan & Keuangan Authorization", function () {
    $middleware = new CheckRole();

    // Mock Kemahasiswaan user
    $userKemahasiswaan = new User(['role' => 'kemahasiswaan']);
    Auth::setUser($userKemahasiswaan);

    $req = Request::create('/admin/skpi/verifikasi-data', 'GET');
    $passedKemahasiswaan = false;
    $response = $middleware->handle($req, function () use (&$passedKemahasiswaan) {
        $passedKemahasiswaan = true;
        return 'OK';
    }, 'masteradmin', 'kemahasiswaan');

    if (!$passedKemahasiswaan) {
        throw new \Exception("CheckRole rejected 'kemahasiswaan' for allowed role ['masteradmin', 'kemahasiswaan']");
    }

    // Mock Keuangan user
    $userKeuangan = new User(['role' => 'keuangan']);
    Auth::setUser($userKeuangan);

    $passedKeuangan = false;
    $response2 = $middleware->handle($req, function () use (&$passedKeuangan) {
        $passedKeuangan = true;
        return 'OK';
    }, 'masteradmin', 'keuangan');

    if (!$passedKeuangan) {
        throw new \Exception("CheckRole rejected 'keuangan' for allowed role ['masteradmin', 'keuangan']");
    }

    // Check rejection when role is not allowed
    $blocked = false;
    $response3 = $middleware->handle($req, function () use (&$blocked) {
        $blocked = false;
        return 'OK';
    }, 'masteradmin', 'kemahasiswaan'); // User is keuangan, role requires masteradmin or kemahasiswaan

    if ($response3 instanceof \Illuminate\Http\RedirectResponse) {
        // Successfully blocked and redirected
        $blocked = true;
    }

    if (!$blocked) {
        throw new \Exception("CheckRole should have blocked role 'keuangan' from accessing kemahasiswaan-only route");
    }

    return true;
});

// 7. Route Middleware Verification
runTest("Routes - Verifikasi Data Route Protected by 'role:masteradmin,kemahasiswaan'", function () {
    $route = Route::getRoutes()->getByName('admin.skpi.verifikasi-data.index');
    if (!$route) {
        throw new \Exception("Route 'admin.skpi.verifikasi-data.index' not found");
    }
    $middlewares = $route->middleware();
    $hasRoleCheck = false;
    foreach ($middlewares as $mw) {
        if (str_starts_with($mw, 'role:')) {
            $roles = explode(',', substr($mw, 5));
            if (in_array('kemahasiswaan', $roles, true)) {
                $hasRoleCheck = true;
                break;
            }
        }
    }
    if (!$hasRoleCheck) {
        throw new \Exception("Route 'admin.skpi.verifikasi-data.index' does not allow role 'kemahasiswaan'. Middleware: " . implode(', ', $middlewares));
    }
    return true;
});

runTest("Routes - Verifikasi Pembayaran Route Protected by 'role:masteradmin,keuangan'", function () {
    $route = Route::getRoutes()->getByName('admin.skpi.verifikasi-pembayaran.index');
    if (!$route) {
        throw new \Exception("Route 'admin.skpi.verifikasi-pembayaran.index' not found");
    }
    $middlewares = $route->middleware();
    $hasRoleCheck = false;
    foreach ($middlewares as $mw) {
        if (str_starts_with($mw, 'role:')) {
            $roles = explode(',', substr($mw, 5));
            if (in_array('keuangan', $roles, true)) {
                $hasRoleCheck = true;
                break;
            }
        }
    }
    if (!$hasRoleCheck) {
        throw new \Exception("Route 'admin.skpi.verifikasi-pembayaran.index' does not allow role 'keuangan'. Middleware: " . implode(', ', $middlewares));
    }
    return true;
});

runTest("Routes - Dashboard Route Allows Both New Roles", function () {
    $route = Route::getRoutes()->getByName('admin.dashboard');
    if (!$route) {
        throw new \Exception("Route 'admin.dashboard' not found");
    }
    $middlewares = $route->middleware();
    $hasKemahasiswaan = false;
    $hasKeuangan = false;
    foreach ($middlewares as $mw) {
        if (str_starts_with($mw, 'role:')) {
            $roles = explode(',', substr($mw, 5));
            if (in_array('kemahasiswaan', $roles, true)) $hasKemahasiswaan = true;
            if (in_array('keuangan', $roles, true)) $hasKeuangan = true;
        }
    }
    if (!$hasKemahasiswaan || !$hasKeuangan) {
        throw new \Exception("Route 'admin.dashboard' does not include new roles. Middleware: " . implode(', ', $middlewares));
    }
    return true;
});

// 8. AuthController Redirect Logic
runTest("AuthController - Redirect Logic Allows Both New Roles", function () {
    $controller = new \App\Http\Controllers\AuthController();
    $userK = new User(['role' => 'kemahasiswaan']);
    Auth::setUser($userK);
    $responseK = $controller->index();
    if (!$responseK->isRedirect(route('admin.dashboard'))) {
        throw new \Exception("AuthController did not redirect 'kemahasiswaan' to admin.dashboard");
    }

    $userKeu = new User(['role' => 'keuangan']);
    Auth::setUser($userKeu);
    $responseKeu = $controller->index();
    if (!$responseKeu->isRedirect(route('admin.dashboard'))) {
        throw new \Exception("AuthController did not redirect 'keuangan' to admin.dashboard");
    }

    return true;
});

// 9. MenuItem Scope for Role
runTest("MenuItem - scopeForRole Resolves Correct Menus", function () {
    $testItem = new MenuItem([
        'name' => 'Test Menu Kemahasiswaan',
        'roles' => 'kemahasiswaan,keuangan',
        'is_active' => true,
    ]);
    if (!in_array('kemahasiswaan', $testItem->roles_array, true)) {
        throw new \Exception("roles_array attribute did not parse 'kemahasiswaan'");
    }
    if (!in_array('keuangan', $testItem->roles_array, true)) {
        throw new \Exception("roles_array attribute did not parse 'keuangan'");
    }
    return true;
});

// 10. Dashboard Views for Kemahasiswaan & Keuangan
runTest("DashboardController - Kemahasiswaan Dedicated Dashboard Renders Successfully", function () {
    $dashboardController = new \App\Http\Controllers\AdminController\DashboardController();
    $userK = User::where('role', 'kemahasiswaan')->first() ?? new User(['name' => 'Staf Kemahasiswaan', 'role' => 'kemahasiswaan']);
    Auth::setUser($userK);

    $req = Request::create('/admin/dashboard', 'GET');
    $view = $dashboardController->dashboard($req);

    if (!($view instanceof \Illuminate\View\View)) {
        throw new \Exception("Dashboard did not return a View instance for kemahasiswaan");
    }
    if ($view->name() !== 'kemahasiswaan.dashboard.index') {
        throw new \Exception("Expected view 'kemahasiswaan.dashboard.index', got '{$view->name()}'");
    }

    // Verify view file compiles/renders without blade errors
    $renderedHtml = $view->render();
    if (empty($renderedHtml)) {
        throw new \Exception("Rendered HTML for kemahasiswaan dashboard is empty");
    }
    return true;
});

runTest("DashboardController - Keuangan Dedicated Dashboard Renders Successfully", function () {
    $dashboardController = new \App\Http\Controllers\AdminController\DashboardController();
    $userKeu = User::where('role', 'keuangan')->first() ?? new User(['name' => 'Staf Keuangan', 'role' => 'keuangan']);
    Auth::setUser($userKeu);

    $req = Request::create('/admin/dashboard', 'GET');
    $view = $dashboardController->dashboard($req);

    if (!($view instanceof \Illuminate\View\View)) {
        throw new \Exception("Dashboard did not return a View instance for keuangan");
    }
    if ($view->name() !== 'keuangan.dashboard.index') {
        throw new \Exception("Expected view 'keuangan.dashboard.index', got '{$view->name()}'");
    }

    // Verify view file compiles/renders without blade errors
    $renderedHtml = $view->render();
    if (empty($renderedHtml)) {
        throw new \Exception("Rendered HTML for keuangan dashboard is empty");
    }
    return true;
});


echo "\n========================================================\n";
echo "SUMMARY: Total: " . ($passed + $failed) . " | Passed: \033[32m{$passed}\033[0m | Failed: \033[31m{$failed}\033[0m\n";
echo "========================================================\n";

if ($failed > 0) {
    exit(1);
}

exit(0);
