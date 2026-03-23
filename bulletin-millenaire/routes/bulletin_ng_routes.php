<?php

/**
 * ══════════════════════════════════════════════════════════════════
 *  ROUTES — Bulletin NG (Nouvelle Génération)
 *  À ajouter dans routes/web.php, dans le groupe teacher
 *  middleware(['role:teacher,prof_principal,admin', 'ensure.teacher.record'])
 *
 *  use App\Http\Controllers\Teacher\BulletinNgController;
 * ══════════════════════════════════════════════════════════════════
 */

// Dans le groupe : Route::middleware([...])->prefix('teacher')->name('teacher.')->group(function () { ... });

Route::prefix('bulletin-ng')->name('bulletin_ng.')->group(function () {

    // ── Dashboard sessions
    Route::get('/', [BulletinNgController::class, 'index'])->name('index');

    // ── Wizard Étapes (GET)
    Route::get('step1',                  [BulletinNgController::class, 'step1Section'])->name('step1');
    Route::get('step2',                  [BulletinNgController::class, 'step2Config'])->name('step2');
    Route::get('{config}/step3',         [BulletinNgController::class, 'step3Subjects'])->name('step3');
    Route::get('{config}/step4',         [BulletinNgController::class, 'step4Students'])->name('step4');
    Route::get('{config}/step5',         [BulletinNgController::class, 'step5Notes'])->name('step5');
    Route::get('{config}/step6',         [BulletinNgController::class, 'step6Conduite'])->name('step6');
    Route::get('{config}/step7',         [BulletinNgController::class, 'step7Generate'])->name('step7');

    // ── Actions POST (formulaires)
    Route::post('store-config',                  [BulletinNgController::class, 'storeConfig'])->name('store-config');
    Route::post('{config}/store-subjects',       [BulletinNgController::class, 'storeSubjects'])->name('store-subjects');
    Route::post('{config}/finaliser-conduite',   [BulletinNgController::class, 'finaliserConduite'])->name('finaliser-conduite');

    // ── API JSON (AJAX - temps réel)
    Route::post('{config}/students',             [BulletinNgController::class, 'storeStudent'])->name('students.store');
    Route::delete('{config}/students/{student}', [BulletinNgController::class, 'deleteStudent'])->name('students.delete');
    Route::post('{config}/ouvrir-saisie',        [BulletinNgController::class, 'ouvrirSaisie'])->name('ouvrir-saisie');
    Route::post('{config}/save-note',            [BulletinNgController::class, 'saveNote'])->name('save-note');
    Route::post('{config}/verrouiller',          [BulletinNgController::class, 'verrouillerNotes'])->name('verrouiller');
    Route::post('{config}/students/{student}/conduite', [BulletinNgController::class, 'saveConduite'])->name('save-conduite');
    Route::get('{config}/api/stats',             [BulletinNgController::class, 'apiStats'])->name('api.stats');
    Route::get('{config}/students/{student}/notes', [BulletinNgController::class, 'apiStudentNotes'])->name('api.student-notes');

    // ── PDF & Export
    Route::get('{config}/students/{student}/pdf',  [BulletinNgController::class, 'pdfStudent'])->name('pdf.student');
    Route::get('{config}/pdf-all',                 [BulletinNgController::class, 'pdfAll'])->name('pdf.all');
    Route::get('{config}/students/{student}/preview', [BulletinNgController::class, 'previewStudent'])->name('preview.student');
});
