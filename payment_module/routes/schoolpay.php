<?php

/**
 * ═══════════════════════════════════════════════════════════════
 *  SCHOOL PAY — Routes Laravel
 *  À inclure dans routes/web.php
 * ═══════════════════════════════════════════════════════════════
 *
 * 📌 INSTRUCTIONS D'INTÉGRATION :
 *
 *  1. Dans routes/web.php, ajoutez en haut :
 *     use App\Http\Controllers\Payment\SchoolPayController;
 *
 *  2. Collez ce groupe de routes à l'intérieur du middleware 'auth'
 *     (ou dans le groupe approprié selon votre structure)
 *
 *  3. Pour les webhooks (hors auth), ajoutez le bloc webhooks
 *     dans la section publique de routes/web.php
 */

use App\Http\Controllers\Payment\SchoolPayController;
use Illuminate\Support\Facades\Route;

// ══════════════════════════════════════════════════════════════
//  WEBHOOKS PUBLICS (pas de middleware auth — appelés par les APIs)
// ══════════════════════════════════════════════════════════════

Route::prefix('webhooks/schoolpay')->name('schoolpay.webhooks.')->group(function () {
    Route::post('orange',  [SchoolPayController::class, 'webhookOrange'])->name('orange');
    Route::post('mtn',     [SchoolPayController::class, 'webhookMtn'])->name('mtn');
});

// Reçu public (vérification QR)
Route::get('payment/receipt/{transactionRef}', [SchoolPayController::class, 'showReceipt'])
    ->name('payment.receipt.show')
    ->middleware('auth');

// ══════════════════════════════════════════════════════════════
//  PARENT — Interface de paiement
// ══════════════════════════════════════════════════════════════

Route::middleware(['auth', 'role:parent,admin,intendant'])
    ->prefix('parent/schoolpay')
    ->name('schoolpay.parent.')
    ->group(function () {

    // Page principale de paiement
    Route::get('/',                          [SchoolPayController::class, 'parentIndex'])->name('index');

    // AJAX — Initiation
    Route::post('initiate',                  [SchoolPayController::class, 'initiate'])->name('initiate');

    // AJAX — Polling statut
    Route::get('poll/{transactionRef}',      [SchoolPayController::class, 'poll'])->name('poll');

    // AJAX — Frais élève
    Route::get('student/{student}/fees',     [SchoolPayController::class, 'studentFees'])->name('student.fees');
});

// ══════════════════════════════════════════════════════════════
//  ADMIN — Dashboard temps réel
// ══════════════════════════════════════════════════════════════

Route::middleware(['auth', 'role:admin,intendant,censeur'])
    ->prefix('admin/schoolpay')
    ->name('schoolpay.admin.')
    ->group(function () {

    // Dashboard principal
    Route::get('/',         [SchoolPayController::class, 'adminDashboard'])->name('dashboard');

    // AJAX — Stats temps réel (polling)
    Route::get('stats',     [SchoolPayController::class, 'adminStats'])->name('stats');
});
