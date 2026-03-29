<?php

namespace App\Services\Payment;

use App\Models\MobilePayment;
use Illuminate\Support\Facades\Log;

/**
 * ReceiptService
 * Génère et stocke les reçus PDF de paiement.
 * Utilise barryvdh/laravel-dompdf (déjà installé sur la plateforme).
 */
class ReceiptService
{
    /**
     * Générer et stocker le PDF d'un reçu de paiement.
     */
    public function generate(MobilePayment $payment): ?string
    {
        try {
            if (!class_exists(\Barryvdh\DomPDF\Facade\Pdf::class)) {
                Log::warning('[ReceiptService] dompdf non installé — reçu PDF ignoré.');
                return null;
            }

            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView(
                'payment.receipt-pdf',
                ['payment' => $payment]
            )->setPaper([0, 0, 311, 567]); // Format ticket ~110mm×200mm

            $filename = 'receipts/' . $payment->transaction_ref . '.pdf';
            $path     = storage_path('app/public/' . $filename);

            // S'assurer que le dossier existe
            if (!is_dir(dirname($path))) {
                mkdir(dirname($path), 0755, true);
            }

            $pdf->save($path);

            // Mettre à jour le chemin sur le modèle
            $payment->update(['receipt_pdf_path' => $filename]);

            Log::info('[ReceiptService] Reçu généré : ' . $filename);
            return $filename;

        } catch (\Throwable $e) {
            Log::error('[ReceiptService] Échec génération PDF : ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Retourner l'URL publique du reçu.
     */
    public function getUrl(MobilePayment $payment): ?string
    {
        return $payment->receipt_pdf_path
            ? asset('storage/' . $payment->receipt_pdf_path)
            : null;
    }
}
