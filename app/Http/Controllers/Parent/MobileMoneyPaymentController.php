<?php

namespace App\Http\Controllers\Parent;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\Payment;
use App\Services\PaymentGatewayService;
use App\Services\PaymentSimulationService;
use App\Services\ReceiptService;
use App\Services\MobileMoneyService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;

/**
 * MobileMoneyPaymentController
 * 
 * PHASE 10: Mobile Money Payment System
 * Handles Orange Money & MTN MoMo payments with complete step-by-step flow
 * 
 * Security:
 * - Middleware throttle:10,1 (max 10 requests per minute)
 * - Server-side amount validation (NEVER trust frontend)
 * - HMAC signature verification on webhooks
 * - Activity logging for all transactions
 */
class MobileMoneyPaymentController extends Controller
{
    protected PaymentGatewayService $paymentService;
    protected PaymentSimulationService $simulationService;
    protected ReceiptService $receiptService;
    protected MobileMoneyService $mobileMoneyService;

    public function __construct(
        PaymentGatewayService $paymentService,
        PaymentSimulationService $simulationService,
        ReceiptService $receiptService,
        MobileMoneyService $mobileMoneyService
    ) {
        $this->paymentService = $paymentService;
        $this->simulationService = $simulationService;
        $this->receiptService = $receiptService;
        $this->mobileMoneyService = $mobileMoneyService;

        // Apply middleware
        $this->middleware(['auth', 'role:parent', 'throttle:10,1'])->only(['initiate', 'checkStatus', 'webhook']);
    }

    /**
     * Show mobile money payment interface 
     * PHASE 10: 11.1 Interface de Paiement Haute Gamme (from payment folder)
     */
    public function show(Student $student): View
    {
        // Verify parent owns this student
        $this->authorizeParentStudent($student);

        // Get amount due for student
        $totalDue = $student->getTotalAmountDue();
        $totalPaid = $student->getTotalAmountPaid();
        $balance = $totalDue - $totalPaid;

        // If fully paid, redirect
        if ($balance <= 0) {
            abort(403, 'No amount due for this student');
        }

        return view('payment.mobile-money', [
            'student' => $student,
            'amount' => $balance, // Amount to pay
        ]);
    }

    /**
     * Initiate payment via Orange Money or MTN MoMo
     * PHASE 10: 11.1.2 Processus de Paiement Détaillé (Étape 5)
     * 
     * Performs:
     * - Server-side validation of all inputs
     * - Phone number format validation (Cameroon format: 6XXXXXXXX)
     * - Amount verification against database
     * - Payment creation and initiation
     */
    public function initiate(Request $request): JsonResponse
    {
        try {
            // Validate request
            $validated = $request->validate([
                'student_id' => 'required|exists:students,id',
                'operator' => 'required|in:orange,mtn',
                'phone_number' => 'required|string',
                'amount' => 'required|numeric|min:1000|max:5000000',
                'purpose' => 'required|in:tuition_fees,exam_fees,uniform,books,other',
                'description' => 'nullable|string|max:500',
            ]);

            // Get student
            $student = Student::findOrFail($validated['student_id']);
            $this->authorizeParentStudent($student);

            // ─── SECURITY: Validate amount from database, NEVER trust frontend ───
            $totalDue = $student->getTotalAmountDue();
            $totalPaid = $student->getTotalAmountPaid();
            $actualBalance = $totalDue - $totalPaid;

            if ($validated['amount'] > $actualBalance) {
                Log::warning('Payment amount exceeds student balance', [
                    'student_id' => $student->id,
                    'requested_amount' => $validated['amount'],
                    'actual_balance' => $actualBalance,
                ]);

                return response()->json([
                    'success' => false,
                    'message' => __('Invalid payment amount'),
                ], 422);
            }

            // Normalize and validate phone number (Cameroon format)
            $phoneNumber = $this->validateCameroonPhone($validated['phone_number']);
            if (!$phoneNumber) {
                return response()->json([
                    'success' => false,
                    'message' => __('Invalid phone number. Expected format: 6XXXXXXXX'),
                ], 422);
            }

            // Create payment record
            $payment = Payment::create([
                'student_id' => $student->id,
                'amount' => $validated['amount'],
                'currency' => config('payment.currency', 'XAF'),
                'status' => 'pending',
                'purpose' => $validated['purpose'],
                'description' => $validated['description'] ?? 'School Fee Payment',
                'phone_number' => $phoneNumber,
                'provider' => $validated['operator'],
                'initiated_at' => now(),
            ]);

            // ─── Use simulation service in sandbox mode ───
            if ($this->simulationService->isEnabled()) {
                $result = $validated['operator'] === 'orange'
                    ? $this->simulationService->simulateOrangeMoneyInitiation(
                        $validated['amount'],
                        '+237' . $phoneNumber,
                        $validated['description'] ?? 'School Tuition'
                    )
                    : $this->simulationService->simulateMTNMoMoInitiation(
                        $validated['amount'],
                        '+237' . $phoneNumber,
                        $validated['description'] ?? 'School Tuition'
                    );
            } else {
                // Real API call via PaymentGatewayService
                $result = $this->paymentService->initiatePayment(
                    $student,
                    $validated['amount'],
                    $validated['purpose'],
                    $validated['description'] ?? 'School Tuition'
                );
            }

            if (!$result['success']) {
                $payment->update(['status' => 'failed']);
                Log::error('Payment initiation failed', [
                    'payment_id' => $payment->id,
                    'error' => $result['error'] ?? 'Unknown error',
                ]);

                return response()->json([
                    'success' => false,
                    'message' => $result['error'] ?? __('Payment initiation failed'),
                ], 400);
            }

            // Update payment with transaction ID
            $payment->update([
                'transaction_id' => $result['transaction_id'],
                'provider_data' => $result['data'] ?? [],
            ]);

            // Log activity
            Log::info('Payment initiated', [
                'payment_id' => $payment->id,
                'student_id' => $student->id,
                'operator' => $validated['operator'],
                'amount' => $validated['amount'],
                'parent_id' => Auth::id(),
            ]);

            return response()->json([
                'success' => true,
                'message' => __('Payment initiated successfully'),
                'transaction_id' => $result['transaction_id'],
                'payment_id' => $payment->id,
                'data' => $result['data'] ?? [],
            ], 200);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => __('Validation failed'),
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            Log::error('Payment initiation exception', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => __('An error occurred. Please try again.'),
            ], 500);
        }
    }

    /**
     * Check payment status via polling
     * PHASE 10: 11.1.2 Étape 6 - Polling du statut (vérification toutes les 3 secondes pendant 2 minutes)
     * 
     * Performs status verification from payment provider
     */
    public function checkStatus(Request $request): JsonResponse
    {
        try {
            $transactionId = $request->query('transaction_id');

            if (!$transactionId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Transaction ID required',
                ], 400);
            }

            // Find payment
            $payment = Payment::where('transaction_id', $transactionId)->first();

            if (!$payment) {
                return response()->json([
                    'success' => false,
                    'status' => 'not_found',
                    'message' => __('Payment not found'),
                ], 404);
            }

            // Verify parent-student relationship
            $this->authorizeParentStudent($payment->student);

            // If already completed or failed, return status
            if (in_array($payment->status, ['completed', 'failed'])) {
                return response()->json([
                    'success' => true,
                    'status' => $payment->status,
                    'payment_id' => $payment->id,
                    'message' => $payment->status === 'completed'
                        ? __('Payment completed')
                        : __('Payment failed'),
                ]);
            }

            // Verify with payment provider
            if ($this->simulationService->isEnabled()) {
                $result = $this->simulationService->simulatePaymentVerification($transactionId);
            } else {
                $result = $this->paymentService->verifyPayment($payment);
            }

            if ($result['success'] && $result['status'] === 'completed') {
                // Payment confirmed
                $payment->update([
                    'status' => 'completed',
                    'completed_at' => now(),
                ]);

                // Generate receipt
                try {
                    $receipt = $this->receiptService->generateReceipt($payment);
                    $payment->update(['receipt_id' => $receipt->id]);
                } catch (\Exception $e) {
                    Log::warning('Receipt generation failed', [
                        'payment_id' => $payment->id,
                        'error' => $e->getMessage(),
                    ]);
                }

                Log::info('Payment confirmed', [
                    'payment_id' => $payment->id,
                    'operator' => $payment->provider,
                ]);

                return response()->json([
                    'success' => true,
                    'status' => 'completed',
                    'payment_id' => $payment->id,
                    'message' => __('Payment completed successfully'),
                ], 200);
            } elseif ($result['success'] && $result['status'] === 'failed') {
                $payment->update(['status' => 'failed']);

                Log::warning('Payment failed', [
                    'payment_id' => $payment->id,
                ]);

                return response()->json([
                    'success' => false,
                    'status' => 'failed',
                    'payment_id' => $payment->id,
                    'message' => $result['message'] ?? __('Payment failed'),
                ], 200);
            }

            // Still pending
            return response()->json([
                'success' => true,
                'status' => 'pending',
                'payment_id' => $payment->id,
                'message' => __('Payment still pending'),
            ], 200);

        } catch (\Exception $e) {
            Log::error('Status check exception', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => __('Error checking payment status'),
            ], 500);
        }
    }

    /**
     * Webhook endpoint for payment provider callbacks
     * PHASE 10: 11.2 Sécurité - Hash HMAC sur les webhooks entrants
     * 
     * Security:
     * - Verifies HMAC signature from provider
     * - IP whitelist check
     * - Idempotent: prevents duplicate webhook processing
     */
    public function webhook(Request $request): JsonResponse
    {
        try {
            // Verify IP whitelist
            $clientIp = $request->ip();
            $whitelistedIps = config('payment.webhook_ips', []);

            if (!empty($whitelistedIps) && !in_array($clientIp, $whitelistedIps)) {
                Log::warning('Webhook from unauthorized IP', ['ip' => $clientIp]);
                return response()->json(['success' => false], 403);
            }

            // Verify HMAC signature
            $signature = $request->header('X-Webhook-Signature');
            if (!$signature || !$this->verifyWebhookSignature($request->all(), $signature)) {
                Log::warning('Invalid webhook signature');
                return response()->json(['success' => false], 401);
            }

            // Extract transaction reference
            $transactionId = $request->input('transaction_id') ?? $request->input('reference');
            $status = $request->input('status');

            if (!$transactionId || !$status) {
                return response()->json(['success' => false, 'message' => 'Missing data'], 400);
            }

            // Find payment
            $payment = Payment::where('transaction_id', $transactionId)->first();
            if (!$payment) {
                Log::warning('Webhook for unknown transaction', ['transaction_id' => $transactionId]);
                return response()->json(['success' => false], 404);
            }

            // Prevent duplicate processing
            if ($payment->status !== 'pending') {
                Log::info('Webhook already processed', [
                    'payment_id' => $payment->id,
                    'current_status' => $payment->status,
                ]);
                return response()->json(['success' => true, 'message' => 'Already processed']);
            }

            // Update payment status
            if (in_array($status, ['completed', 'succeeded', 'paid'])) {
                $payment->update([
                    'status' => 'completed',
                    'completed_at' => now(),
                ]);

                // Generate receipt
                $receipt = $this->receiptService->generateReceipt($payment, 'webhook');
                $payment->update(['receipt_id' => $receipt->id]);

                Log::info('Payment completed via webhook', [
                    'payment_id' => $payment->id,
                    'operator' => $payment->provider,
                ]);
            } elseif (in_array($status, ['failed', 'cancelled'])) {
                $payment->update(['status' => 'failed']);

                Log::warning('Payment failed via webhook', [
                    'payment_id' => $payment->id,
                ]);
            }

            return response()->json(['success' => true, 'message' => 'Webhook processed']);

        } catch (\Exception $e) {
            Log::error('Webhook processing error', [
                'error' => $e->getMessage(),
            ]);

            return response()->json(['success' => false], 500);
        }
    }

    /**
     * Validate Cameroon phone number format
     * Expected: 6XXXXXXXX (9 digits starting with 6)
     */
    private function validateCameroonPhone(string $phone): ?string
    {
        // Remove all non-digits
        $cleaned = preg_replace('/\D/', '', $phone);

        // Remove leading +237 or 00237 if present
        $cleaned = preg_replace('/^(237|00237)/', '', $cleaned);

        // Check format: 9 digits starting with 6
        if (preg_match('/^6\d{8}$/', $cleaned)) {
            return $cleaned;
        }

        return null;
    }

    /**
     * Verify HMAC signature of webhook
     */
    private function verifyWebhookSignature(array $payload, string $signature): bool
    {
        $secret = config('payment.webhook_secret', '');
        if (!$secret) {
            return false;
        }

        $expected = hash_hmac('sha256', json_encode($payload), $secret);
        return hash_equals($expected, $signature);
    }

    /**
     * Authorize parent-student relationship
     */
    private function authorizeParentStudent(Student $student): void
    {
        $parent = Auth::user();

        if (!$parent->children || !$parent->children->contains('id', $student->id)) {
            abort(403, 'Unauthorized');
        }
    }
}
