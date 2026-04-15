<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminController\DashboardController;
use App\Http\Controllers\AdminController\UserManageController;
// use App\Http\Controllers\AdminController\InternshipController; // Controller belum dibuat
use App\Http\Controllers\AdminController\StudentsAdminController;
use App\Http\Controllers\AdminController\CounselingController;
use App\Http\Controllers\AdminController\AnnouncementController;
use App\Http\Controllers\AdminController\SkpiController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Hanya Superadmin & Masteradmin
Route::middleware(['auth', 'role:superadmin,masteradmin'])->group(function () {

    // Management Dosen (Full CRUD untuk superadmin/masteradmin)
    Route::prefix('admin/management/lecturers')->name('admin.management.lecturers.')->controller(UserManageController::class)->group(function () {
        Route::get('/', 'managementIndex')->name('index');
        Route::get('/create', 'create')->name('create');
        Route::post('/', 'store')->name('store');
        Route::get('/template', 'downloadImportTemplate')->name('template');
        Route::post('/import', 'import')->name('import');
        Route::get('/{id}/edit', 'edit')->name('edit');
        Route::put('/{id}', 'update')->name('update');
        Route::get('/{id}/reset-password', 'resetPassword')->name('reset-password');
        Route::delete('/{id}', 'destroy')->name('destroy');
    });

    // Management Mahasiswa (Full CRUD untuk superadmin/masteradmin)
    Route::prefix('admin/management/students')->name('admin.management.students.')->controller(StudentsAdminController::class)->group(function () {
        Route::get('/', 'managementIndex')->name('index');
        Route::get('/create', 'create')->name('create');
        Route::post('/', 'store')->name('store');
        Route::get('/template', 'downloadImportTemplate')->name('template');
        Route::post('/import', 'import')->name('import');
        Route::get('/{id}', 'show')->name('show');
        Route::get('/{id}/edit', 'edit')->name('edit');
        Route::put('/{id}', 'update')->name('update');
        Route::delete('/{id}', 'destroy')->name('destroy');
        Route::get('/{id}/reset-password', 'resetpassword')->name('reset-password');
        Route::post('/{id}/toggle-edit', 'toggleEdit')->name('toggle-edit');
        Route::post('/toggle-all-edit', 'toggleAllEdit')->name('toggle-all-edit');
    });

    // Management Menu (Full CRUD untuk superadmin/masteradmin)
    Route::prefix('admin/management/menus')->name('admin.management.menus.')->controller(\App\Http\Controllers\AdminController\MenuManagementController::class)->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/create', 'create')->name('create');
        Route::post('/', 'store')->name('store');
        Route::get('/{id}/edit', 'edit')->name('edit');
        Route::put('/{id}', 'update')->name('update');
        Route::delete('/{id}', 'destroy')->name('destroy');
    });

    // Students (khusus untuk akses tertentu super/master admin)
    Route::prefix('admin/students')
        ->name('admin.students.')
        ->group(function () {
            Route::get('/get-students-lecture/{batch}/{id}', [StudentsAdminController::class, 'getStudentsByBatchLecturer'])
                ->name('getStudentsByBatchLecturer');
            Route::get('/CheckStudentByLecturer/{id}', [StudentsAdminController::class, 'CheckStudentByLecturer'])
                ->name('CheckStudentByLecturer');
            Route::get('/showCardByLecture/{id}', [StudentsAdminController::class, 'showCardByLecture'])
                ->name('showCardByLecture');
        });

    // User Management (khusus super/master admin)
    Route::controller(UserManageController::class)
        ->prefix('admin/user')
        ->name('user.admin.')
        ->group(function () {
            Route::get('/main', 'indexMain')->name('main');
            Route::get('/create', 'create')->name('create');
        });
});

// Khusus Superuser
Route::middleware(['auth', 'role:masteradmin'])->group(function () {
    Route::prefix('admin/skpi')->name('admin.skpi.')->controller(SkpiController::class)->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/daftar-skpi', 'daftarSkpi')->name('daftar-skpi.index');
        Route::post('/daftar-skpi/{id}/approve', 'approveDaftarSkpi')->name('daftar-skpi.approve');
        Route::post('/daftar-skpi/AproveAll','AproveAllDaftarSkpi')->name('daftar-skpi.approve-all');
        Route::post('/daftar-skpi/{id}/revision', 'revisionDaftarSkpi')->name('daftar-skpi.revision');
        Route::post('/daftar-skpi/{id}/reject', 'rejectDaftarSkpi')->name('daftar-skpi.reject');
        Route::get('/input-data-akademi', 'inputDataAkademi')->name('input-data-akademi.index');
        Route::post('/input-data-akademi', 'storeInputDataAkademi')->name('input-data-akademi.store');
        Route::post('/input-data-akademi/study-program', 'storeStudyProgram')->name('input-data-akademi.store-prodi');
        Route::get('/verifikasi-data', 'verifikasiData')->name('verifikasi-data.index');
        Route::post('/verifikasi-data/{id}/approve', 'approveVerifikasiData')->name('verifikasi-data.approve');
        Route::post('/verifikasi-data/{id}/reject', 'rejectVerifikasiData')->name('verifikasi-data.reject');
        Route::post('/verifikasi-data/approve-all', 'approveAllVerifikasiData')->name('verifikasi-data.approve-all');
        Route::get('/generate-skpi', 'generateSkpi')->name('generate-skpi.index');
        Route::post('/generate-skpi/metadata', 'storeGenerateMetadata')->name('generate-skpi.metadata.store');
        Route::post('/generate-skpi/export-pdf', 'exportGeneratedSkpiPdf')->name('generate-skpi.export-pdf');
    });
});


// Admin, Superadmin, Masteradmin
Route::middleware(['auth', 'role:admin,superadmin,masteradmin'])->group(function () {

    // Dashboard SuperApp
    Route::get('/admin/dashboard', [DashboardController::class, 'dashboard'])->name('admin.dashboard');

    // Dashboard Personal (old)
    Route::controller(DashboardController::class)
        ->prefix('admin/personal')
        ->name('dashboard.admin.')
        ->group(function () {
            Route::get('/', 'index')->name('index');
        });

    // Counseling
    Route::controller(CounselingController::class)
        ->prefix('admin/counseling')
        ->name('admin.counseling.')
        ->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/get-students/{batch}', 'getStudentsByBatch')->name('getStudentsByBatch');
            Route::get('/get-course/failorretakecourse/{batch}', 'getStudentsByBatchByCourse')->name('getStudentsByBatchByCourse');
            Route::get('/open-close/{id}', 'openclose')->name('openclose');
            Route::get('/open-close/data/{id}', 'opencloseedit')->name('opencloseedit');
        });

    // Internship - Disabled karena controller belum dibuat
    // Route::controller(InternshipController::class)
    //     ->prefix('admin/internship')
    //     ->name('admin.internship.')
    //     ->group(function () {
    //         Route::get('/', 'index')->name('index');
    //         Route::get('/create', 'create')->name('create');
    //         Route::post('/', 'store')->name('store');
    //         Route::get('/{id}/edit', 'edit')->name('edit');
    //         Route::put('/{id}', 'update')->name('update');
    //         Route::delete('/{id}', 'destroy')->name('destroy');
    //     });

    // Students
    Route::controller(StudentsAdminController::class)
        ->prefix('admin/students')
        ->name('admin.students.')
        ->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/create', 'create')->name('create');
            Route::post('/', 'store')->name('store');
            Route::get('/{id}/edit', 'edit')->name('edit');
            Route::put('/{id}', 'update')->name('update');
            Route::get('/{id}/editcard', 'editcard')->name('editcard');
            Route::put('/{id}/updatecard', 'updatecard')->name('updatecard');
            Route::delete('/{id}', 'destroy')->name('destroy');
            Route::delete('/{id}/deletecard', 'destroycard')->name('deletecard');
            Route::get('/{id}/resetpassword', 'resetpassword')->name('resetpassword');
            Route::get('/unclock/all', 'unlock')->name('unlockAllCounseling');
            Route::get('/lock/all', 'lock')->name('lockAllCounseling');
            Route::get('/change-advisor/', 'changeAdvisor')->name('changeAdvisor');
            Route::get('/change-advisor-byid/{id}', 'changeAdvisorById')->name('changeAdvisorById');
            Route::post('/change-advisor/all', 'changeAdvisorAll')->name('changeAdvisorAll');
            Route::post('/students/bulk-change-advisor', 'bulkChangeAdvisor')->name('bulkChangeAdvisor');
            // Tambahan spesifik
            Route::get('/CheckStudentByLecturer/{id}', 'CheckStudentByLecturer')->name('CheckStudentByLecturer');
            Route::get('/showCardByLecture/{id}', 'showCardByLecture')->name('showCardByLecture');
        });

    // User Management
    Route::controller(UserManageController::class)
        ->prefix('admin/user')
        ->name('user.admin.')
        ->group(function () {
            Route::get('/', 'index')->name('index');
            Route::post('/', 'store')->name('store');
            Route::get('/{id}/edit', 'edit')->name('edit');
            Route::put('/{id}', 'update')->name('update');
            Route::delete('/{id}', 'destroy')->name('destroy');
        });

    // Final Project - Dosen: Log Bimbingan + Log Dokumen (view-only)
Route::prefix('admin/final-project')->name('admin.final-project.')->group(function () {
    Route::prefix('guidance')->name('guidance.')->controller(\App\Http\Controllers\Admin\FinalProject\GuidanceReviewController::class)->group(function () {
        Route::get('/', 'index')->name('index');
        Route::post('/{id}/approve', 'approve')->middleware('role:admin')->name('approve');
        Route::post('/{id}/reject', 'reject')->middleware('role:admin')->name('reject');
        Route::get('/{id}/download', 'download')->name('download');
    });

    Route::prefix('documents')->name('documents.')->controller(\App\Http\Controllers\Admin\FinalProject\DocumentReviewController::class)->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/{id}/download', 'download')->name('download');
    });
});

    // Final Project - Khusus Kaprodi/Superuser
    Route::middleware(['role:superadmin,masteradmin'])->prefix('admin/final-project')->name('admin.final-project.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\FinalProject\DashboardController::class, 'index'])->name('index');
        Route::get('/{id}', [\App\Http\Controllers\Admin\FinalProject\DashboardController::class, 'show'])
            ->whereNumber('id')
            ->name('show');

        Route::prefix('titles')->name('titles.')->controller(\App\Http\Controllers\Admin\FinalProject\TitleApprovalController::class)->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/{id}', 'show')->name('show');
            Route::post('/{id}/approve', 'approve')->name('approve');
            Route::post('/{id}/reject', 'reject')->name('reject');
        });

        Route::prefix('supervisors')->name('supervisors.')->controller(\App\Http\Controllers\Admin\FinalProject\SupervisorManagementController::class)->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/{id}/edit', 'edit')->name('edit');
            Route::put('/{id}', 'update')->name('update');
        });

        Route::prefix('proposals')->name('proposals.')->controller(\App\Http\Controllers\Admin\FinalProject\ProposalApprovalController::class)->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/{id}', 'show')->name('show');
            Route::post('/{id}/approve', 'approve')->name('approve');
            Route::post('/{id}/reject', 'reject')->name('reject');
        });

        Route::prefix('defenses')->name('defenses.')->controller(\App\Http\Controllers\Admin\FinalProject\DefenseApprovalController::class)->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/{id}', 'show')->name('show');
            Route::post('/{id}/approve', 'approve')->name('approve');
            Route::post('/{id}/reject', 'reject')->name('reject');
        });

        Route::prefix('documents')->name('documents.')->controller(\App\Http\Controllers\Admin\FinalProject\DocumentReviewController::class)->group(function () {
            Route::post('/{id}/approve', 'approve')->name('approve');
            Route::post('/{id}/revision', 'revision')->name('revision');
            Route::post('/{id}/reject', 'reject')->name('reject');
        });
    });

    // Pengumuman (CRUD)
    Route::prefix('admin/announcements')->name('admin.announcements.')->controller(AnnouncementController::class)->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/create', 'create')->name('create');
        Route::post('/', 'store')->name('store');
        Route::get('/{id}/edit', 'edit')->name('edit');
        Route::put('/{id}', 'update')->name('update');
        Route::post('/{id}/toggle-publish', 'togglePublish')->name('toggle-publish');
        Route::delete('/{id}', 'destroy')->name('destroy');
    });
});
