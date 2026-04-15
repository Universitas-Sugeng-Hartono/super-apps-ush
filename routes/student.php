<?php

use App\Http\Controllers\CardCounselingController;
use App\Http\Controllers\StudentsController;

Route::middleware(['student'])->group(function () {

    // Dashboard SuperApp
    Route::get('/student/dashboard', [StudentsController::class, 'dashboard'])->name('student.dashboard');

    // Counseling
    Route::controller(CardCounselingController::class)
        ->prefix('student/counseling')
        ->name('student.counseling.')
        ->group(function () {
            Route::get('/show', 'show')->name('show');
            Route::post('/{student}', 'store')->name('store');
        });

    // Personal Data
    Route::controller(StudentsController::class)
        ->prefix('student/personal')
        ->name('student.personal.')
        ->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/edit', 'editDataIndex')->name('editDataIndex');
            Route::get('/achievements', 'achievementIndex')->name('achievements.index');
            Route::put('/update', 'updateData')->name('updateData');
            Route::get('/cv/download', 'downloadCv')->name('cv.download');
            Route::get('/cv/preview', 'previewCv')->name('cv.preview');
            // Achievements
            Route::post('/achievement/store', 'storeAchievement')->name('achievement.store');
            Route::put('/achievement/update/{id}', 'updateAchievement')->name('achievement.update');
            Route::delete('/achievement/delete/{id}', 'deleteAchievement')->name('achievement.delete');
        });

    // SKPI
    Route::prefix('student/skpi')->name('student.skpi.')->controller(\App\Http\Controllers\Student\SkpiController::class)->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/download-pdf', 'downloadPdf')->name('download-pdf');
        Route::prefix('daftar')->name('daftar.')->group(function () {
            Route::get('/', 'daftarIndex')->name('index');
            Route::get('/create', 'daftarCreate')->name('create');
            Route::post('/', 'daftarStore')->name('store');
            Route::get('/show', 'daftarShow')->name('show');

        });
    });

    // Final Project (Tugas Akhir)
    Route::prefix('student/final-project')->name('student.final-project.')->group(function () {
        // Dashboard
        Route::get('/', [\App\Http\Controllers\Student\FinalProjectController::class, 'index'])->name('index');

        // Title Request
        Route::prefix('title')->name('title.')->controller(\App\Http\Controllers\Student\TitleRequestController::class)->group(function () {
            Route::get('/create', 'create')->name('create');
            Route::post('/', 'store')->name('store');
            Route::get('/edit', 'edit')->name('edit');
            Route::put('/', 'update')->name('update');
        });

        // Proposal Registration
        // Proposal Registration
        Route::prefix('proposal')->name('proposal.')->group(function () {
            Route::get('/create',    [\App\Http\Controllers\Student\ProposalRegistrationController::class, 'create'])->name('create');
            Route::post('/',         [\App\Http\Controllers\Student\ProposalRegistrationController::class, 'store'])->name('store');
            Route::get('/{id}',      [\App\Http\Controllers\Student\ProposalRegistrationController::class, 'show'])->name('show');
            Route::get('/{id}/edit', [\App\Http\Controllers\Student\ProposalRegistrationController::class, 'edit'])->name('edit');   // ← fix
            Route::put('/{id}',      [\App\Http\Controllers\Student\ProposalRegistrationController::class, 'update'])->name('update'); // ← fix
        });

        // Defense Registration
        Route::prefix('defense')->name('defense.')->group(function () {
            Route::get('/create', [\App\Http\Controllers\Student\DefenseRegistrationController::class, 'create'])->name('create');
            Route::post('/', [\App\Http\Controllers\Student\DefenseRegistrationController::class, 'store'])->name('store');
            Route::get('/{id}', [\App\Http\Controllers\Student\DefenseRegistrationController::class, 'show'])->name('show');
            Route::get('/{id}/edit', [\App\Http\Controllers\Student\DefenseRegistrationController::class, 'edit'])->name('edit');   // ← tambah
            Route::put('/{id}',      [\App\Http\Controllers\Student\DefenseRegistrationController::class, 'update'])->name('update'); // ← tambah
        });

        // Guidance Logs
        Route::prefix('guidance')->name('guidance.')->controller(\App\Http\Controllers\Student\GuidanceLogController::class)->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/create', 'create')->name('create');
            Route::post('/', 'store')->name('store');
            Route::get('/{id}/download', 'download')->name('download');
            Route::get('/{id}/edit', 'edit')->name('edit');
            Route::put('/{id}', 'update')->name('update');
            Route::delete('/{id}', 'destroy')->name('destroy');
        });

        // Documents
        Route::prefix('documents')->name('documents.')->controller(\App\Http\Controllers\Student\DocumentController::class)->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/create', 'create')->name('create');
            Route::post('/', 'store')->name('store');
            Route::get('/{id}/download', 'download')->name('download');
            Route::delete('/{id}', 'destroy')->name('destroy');
        });
    });
});
