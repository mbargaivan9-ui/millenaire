<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AdminAPIController;

/*
|--------------------------------------------------------------------------
| API Routes for Admin Panel
|--------------------------------------------------------------------------
*/

Route::middleware(['auth:sanctum', 'role:admin|censeur'])->prefix('api/admin')->name('api.admin.')->group(function () {
    
    /**
     * Student Management API
     */
    Route::get('/check-matricule/{matricule}', [AdminAPIController::class, 'checkMatricule'])
        ->name('check-matricule');
    
    Route::get('/check-email/{email}', [AdminAPIController::class, 'checkEmail'])
        ->name('check-email');

    Route::post('/students/import-preview', [AdminAPIController::class, 'previewStudentImport'])
        ->name('students.import-preview');

    Route::patch('/students/{student}/status', [AdminAPIController::class, 'updateStudentStatus'])
        ->name('students.update-status');

    /**
     * Attendance Management API
     */
    Route::get('/class/{classe}/students', [AdminAPIController::class, 'getClassStudents'])
        ->name('class.students');

    Route::get('/attendance/export-report', [AdminAPIController::class, 'exportAttendanceReport'])
        ->name('attendance.export-report');

    /**
     * Schedule Management API
     */
    Route::post('/schedule/check-conflict', [AdminAPIController::class, 'checkScheduleConflict'])
        ->name('schedule.check-conflict');

    /**
     * Fee Management API
     */
    Route::post('/fees/preview-assignment', [AdminAPIController::class, 'previewFeeAssignment'])
        ->name('fees.preview-assignment');

    /**
     * Dashboard Charts API
     */
    Route::get('/charts/{type}', [AdminAPIController::class, 'getChartData'])
        ->name('charts.data');

});
