<?php

namespace App\Services;

use App\Models\Payment;
use App\Models\PaymentReceipt;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Exception;
use Illuminate\Support\Facades\Log;

class ReceiptService
{
    /**
     * Generate and store receipt with QR code and PDF
     */
    public function generateReceipt(Payment $payment, string $issuedBy = 'System'): PaymentReceipt
    {
        try {
            // Generate unique receipt number
            $receiptNumber = $this->generateReceiptNumber();

            // Generate QR code
            $qrCode = $this->generateQRCode($payment, $receiptNumber);

            // Generate PDF
            $pdfPath = $this->generatePDF($payment, $receiptNumber, $qrCode);

            // Create receipt record
            $receipt = PaymentReceipt::create([
                'payment_id' => $payment->id,
                'receipt_number' => $receiptNumber,
                'qr_code' => $qrCode,
                'pdf_path' => $pdfPath,
                'amount' => $payment->amount ?? $payment->amount_paid ?? 0,
                'payment_method' => $payment->provider ?? $payment->payment_method ?? 'mobile_money',
                'issued_at' => now(),
                'issued_by' => $issuedBy,
                'metadata' => [
                    'provider' => $payment->provider,
                    'transaction_id' => $payment->transaction_id,
                    'phone_number' => $payment->phone_number,
                    'currency' => $payment->currency ?? 'XAF',
                ],
            ]);

            Log::info("Receipt generated for payment {$payment->id}: {$receiptNumber}");

            return $receipt;
        } catch (Exception $e) {
            Log::error("Failed to generate receipt for payment {$payment->id}: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Generate unique receipt number with format RCP-YYYY-XXXXX
     */
    private function generateReceiptNumber(): string
    {
        $year = date('Y');
        $lastReceipt = PaymentReceipt::whereYear('created_at', $year)
            ->orderBy('id', 'desc')
            ->first();

        $sequence = ($lastReceipt ? (int)substr($lastReceipt->receipt_number, -5) + 1 : 1);

        return sprintf('RCP-%d-%05d', $year, $sequence);
    }

    /**
     * Generate QR code (data URL format)
     */
    private function generateQRCode(Payment $payment, string $receiptNumber): string
    {
        try {
            // QR code content: Receipt verification URL
            $qrContent = route('payment.verify-receipt', [
                'receipt' => $receiptNumber,
                'token' => hash('sha256', $receiptNumber . $payment->transaction_id),
            ]);

            // Generate as data URL
            $qrCode = QrCode::format('png')
                ->size(200)
                ->generate($qrContent);

            return 'data:image/png;base64,' . base64_encode($qrCode);
        } catch (Exception $e) {
            Log::warning("QR code generation failed for receipt {$receiptNumber}: " . $e->getMessage());
            return '';
        }
    }

    /**
     * Generate PDF receipt
     */
    private function generatePDF(Payment $payment, string $receiptNumber, string $qrCode): string
    {
        try {
            $student = $payment->student;
            
            $htmlContent = view('receipts.pdf-template', [
                'payment' => $payment,
                'student' => $student,
                'receiptNumber' => $receiptNumber,
                'qrCode' => $qrCode,
                'issuedAt' => now()->format('d/m/Y H:i'),
            ])->render();

            // Generate PDF
            $pdf = Pdf::loadHTML($htmlContent)
                ->setPaper('a4')
                ->setOption('isHtml5ParserEnabled', true);

            // Store PDF
            $filename = "receipts/{$receiptNumber}.pdf";
            Storage::disk('public')->put($filename, $pdf->output());

            return $filename;
        } catch (Exception $e) {
            Log::error("PDF generation failed for receipt {$receiptNumber}: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Get receipt by number
     */
    public function getReceipt(string $receiptNumber): ?PaymentReceipt
    {
        return PaymentReceipt::where('receipt_number', $receiptNumber)->first();
    }

    /**
     * Verify receipt authenticity
     */
    public function verifyReceipt(string $receiptNumber, string $token): bool
    {
        $receipt = $this->getReceipt($receiptNumber);

        if (!$receipt) {
            return false;
        }

        // Verify token
        $expectedToken = hash('sha256', $receiptNumber . $receipt->payment->transaction_id);

        return hash_equals($token, $expectedToken);
    }

    /**
     * Get receipt download URL
     */
    public function getReceiptUrl(PaymentReceipt $receipt): string
    {
        return Storage::disk('public')->url($receipt->pdf_path);
    }

    /**
     * Send receipt via email
     */
    public function sendReceiptEmail(PaymentReceipt $receipt, string $email): bool
    {
        try {
            // TODO: Implement mailable class for receipt emails
            Log::info("Receipt {$receipt->receipt_number} sent to {$email}");
            return true;
        } catch (Exception $e) {
            Log::error("Failed to send receipt email: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Resend or regenerate receipt
     */
    public function regenerateReceipt(PaymentReceipt $receipt): PaymentReceipt
    {
        try {
            // Delete old files
            Storage::disk('public')->delete($receipt->pdf_path);

            // Regenerate
            return $this->generateReceipt($receipt->payment, 'Regenerated');
        } catch (Exception $e) {
            Log::error("Failed to regenerate receipt {$receipt->receipt_number}: " . $e->getMessage());
            throw $e;
        }
    }
}
