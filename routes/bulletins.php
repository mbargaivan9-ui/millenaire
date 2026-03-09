<?php

/*
|--------------------------------------------------------------------------
| Professor Principal Routes - Bulletin Management
|--------------------------------------------------------------------------
|
| Routes pour la gestion des bulletins scolaires (professeur principal)
| Upload OCR → Template → Editor → Validation → Generation → Export
|
*/

use App\Http\Controllers\ProfessorPrincipal\TemplateUploadController;
use App\Http\Controllers\ProfessorPrincipal\TemplateEditorController;
use App\Http\Controllers\ProfessorPrincipal\GradeEntryController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum', 'verified'])->prefix('prof-principal')->name('prof-principal.')->group(function () {
    
    // ════════════════════════════════════════════════════════════════
    // BULLETIN TEMPLATE MANAGEMENT
    // ════════════════════════════════════════════════════════════════

    Route::prefix('templates')->name('templates.')->group(function () {
        
        // ──── UPLOAD & OCR PROCESSING ────
        Route::get('upload', [TemplateUploadController::class, 'showUploadForm'])
            ->name('upload.form');
        
        Route::post('upload', [TemplateUploadController::class, 'uploadAndProcess'])
            ->middleware('throttle:10,1') // 10 uploads per minute
            ->name('upload.process');
        
        Route::post('{template}/reprocess', [TemplateUploadController::class, 'reprocessImage'])
            ->middleware('throttle:5,1')
            ->name('reprocess');

        // ──── TEMPLATE EDITOR ────
        Route::get('/', [TemplateEditorController::class, 'index'])
            ->name('index');
        
        Route::get('{template}', [TemplateEditorController::class, 'show'])
            ->name('show');
        
        Route::get('{template}/edit', [TemplateEditorController::class, 'edit'])
            ->name('edit');
        
        Route::put('{template}', [TemplateEditorController::class, 'update'])
            ->name('update');
        
        Route::post('{template}/validate', [TemplateEditorController::class, 'validate'])
            ->name('validate');
        
        Route::post('{template}/publish', [TemplateEditorController::class, 'publish'])
            ->name('publish');
        
        Route::post('{template}/duplicate', [TemplateEditorController::class, 'duplicate'])
            ->name('duplicate');
        
        Route::delete('{template}', [TemplateEditorController::class, 'destroy'])
            ->name('destroy');
        
        Route::post('{template}/assign-subjects', [TemplateEditorController::class, 'assignSubjects'])
            ->name('assign-subjects');
    });

    // ════════════════════════════════════════════════════════════════
    // STUDENT BULLETINS
    // ════════════════════════════════════════════════════════════════

    Route::prefix('bulletins')->name('bulletins.')->group(function () {
        Route::get('/', 'StudentBulletinController@index')
            ->name('index');
        
        Route::get('{bulletin}', 'StudentBulletinController@show')
            ->name('show');
        
        Route::post('{bulletin}/lock', 'StudentBulletinController@lock')
            ->name('lock');
        
        Route::post('{bulletin}/unlock', 'StudentBulletinController@unlock')
            ->name('unlock');
        
        Route::get('{bulletin}/export', 'StudentBulletinController@export')
            ->name('export');
    });

    // ════════════════════════════════════════════════════════════════
    // GRADE ENTRY & MANAGEMENT
    // ════════════════════════════════════════════════════════════════

    Route::prefix('grades')->name('grades.')->group(function () {
        Route::get('{template}/entry', [GradeEntryController::class, 'index'])
            ->name('entry');
        
        Route::get('classroom/{classroomId}', [GradeEntryController::class, 'byClassroom'])
            ->name('by-classroom');
        
        Route::get('{template}/stats', [GradeEntryController::class, 'getStats'])
            ->name('stats');
        
        Route::post('{template}/lock-multiple', [GradeEntryController::class, 'lockMultiple'])
            ->name('lock-multiple');
        
        Route::get('{template}/export-csv', [GradeEntryController::class, 'exportCSV'])
            ->name('export-csv');
    });

    // ════════════════════════════════════════════════════════════════
    // PROGRESS & DASHBOARD
    // ════════════════════════════════════════════════════════════════

    Route::get('dashboard', 'ProfessorPrincipalDashboardController@show')
        ->name('dashboard');

    Route::get('progress/{classroom}', 'ProgressController@show')
        ->name('progress');

    // ════════════════════════════════════════════════════════════════
    // EXPORT & DOWNLOAD
    // ════════════════════════════════════════════════════════════════

    Route::prefix('export')->name('export.')->group(function () {
        // À implémenter: ExportController
        // Route::post('/pdf/{bulletin}', 'ExportController@exportSingle')->name('single');
        // Route::post('/zip/{classroom}', 'ExportController@exportClassroom')->name('classroom');
        // Route::get('/download/{export}', 'ExportController@download')->name('download');
    });

});

// ════════════════════════════════════════════════════════════════
// API ROUTES (JSON endpoints for Livewire/AJAX)
// ════════════════════════════════════════════════════════════════

Route::middleware(['auth:sanctum', 'api'])->prefix('api/bulletins')->name('api.bulletins.')->group(function () {
    
    // Template validation API
    // POST /api/bulletins/templates/validate
    // Route::post('templates/validate', 'Api\TemplateValidationController@validate')->name('templates.validate');
    
    // Grades entry API (Livewire)
    // GET /api/bulletins/grades/{bulletin}
    // POST /api/bulletins/grades/{bulletin/update
    // Route::get('grades/{bulletin}', 'Api\GradeController@show');
    // Route::post('grades/{bulletin}', 'Api\GradeController@update');
    
    // Calculations (time-real stats)
    // GET /api/bulletins/calculate/{bulletin}
    // Route::get('calculate/{bulletin}', 'Api\CalculationController@show');
    
});
