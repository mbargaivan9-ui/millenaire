<?php

namespace App\Http\Controllers\Api\V1\Parent;

use App\Http\Controllers\Controller;
use App\Http\Requests\InitiatePaymentRequest;
use App\Http\Resources\PaymentResource;
use App\Models\Payment;
use App\Models\Student;
use App\Services\MobileMoneyService;
use App\Services\AuditService;
use App\Repositories\Interfaces\PaymentRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

/**
 * PaymentController API
 * 
 * 🔥 MOBILE MONEY INTEGRATION
 * Handles payment initiation and status checking
 * Supports: Campay, Orange Money, MTN Money
 * 
 * SOLID: Dependency Injection, Single Responsibility
 */
class PaymentController extends Controller
{
    public function __construct(
        private MobileMoneyService $mobileMoneyService,
        private PaymentRepositoryInterface $paymentRepo,
        private AuditService $auditService,
    ) {
    }

    /**
     * Get all payments for a child
     * 
     * @param int $childId
     * @return JsonResponse
     */
    public function index(int $childId): JsonResponse
    {
        $student = Student::findOrFail($childId);

        // Check authorization (can only view own child's payments)
        $this->authorize('viewPayments', $student);

        $payments = $this->paymentRepo->getStudentPayments($childId);

        return response()->json([
            'success' => true,
            'payments' => PaymentResource::collection($payments),
            'total_paid' => $this->paymentRepo->getTotalPaid($childId),
        ]);
    }

    /**
     * Initiate a mobile money payment
     * 🔥 Core payment feature
     * 
     * @param InitiatePaymentRequest $request
     * @return JsonResponse
     */
    public function initiate(InitiatePaymentRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $student = Student::findOrFail($validated['student_id']);

        // Check authorization
        $this->authorize('pay', $student);

        try {
            // Initiate payment through mobile money service
            $result = $this->mobileMoneyService->initiatePayment(
                student: $student,
                amount: $validated['amount'],
                phone: $validated['phone'],
                provider: $validated['provider'] ?? 'campay'
            );

            // Log the payment initiation
            $this->auditService->log(
                action: 'PAYMENT_INITIATED',
                model: 'Payment',
                modelId: $result['payment_id'],
                changes: ['amount' => $validated['amount'], 'provider' => $validated['provider'] ?? 'campay'],
                notes: "Parent initiated payment for {$student->user->name}, Amount: {$validated['amount']} XAF"
            );

            return response()->json([
                'success' => true,
                'message' => $result['message'],
                'payment_id' => $result['payment_id'],
                'transaction_ref' => $result['transaction_ref'],
                'status' => $result['status'],
            ]);

        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        } catch (\Exception $e) {
            Log::error("Payment initiation error: {$e->getMessage()}");

            return response()->json([
                'success' => false,
                'message' => 'Error initiating payment. Please try again.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Check payment status
     * 
     * @param int $paymentId
     * @return JsonResponse
     */
    public function checkStatus(int $paymentId): JsonResponse
    {
        $payment = Payment::findOrFail($paymentId);

        // Check authorization
        $this->authorize('viewPayment', $payment);

        try {
            $statusResult = $this->mobileMoneyService->checkStatus($payment);

            // Refresh payment data
            $payment = $payment->fresh();

            return response()->json([
                'success' => $statusResult['success'],
                'payment' => new PaymentResource($payment),
                'status' => $payment->status,
                'message' => $statusResult['message'] ?? null,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Could not check payment status',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Get payment history for a child
     * 
     * @param int $childId
     * @return JsonResponse
     */
    public function history(int $childId): JsonResponse
    {
        $student = Student::findOrFail($childId);

        $this->authorize('viewPayments', $student);

        $payments = $this->paymentRepo->getStudentPayments($childId);

        $byStatus = $payments->groupBy('status')->map(function ($group) {
            return [
                'count' => $group->count(),
                'total' => $group->sum('amount'),
                'payments' => PaymentResource::collection($group),
            ];
        });

        return response()->json([
            'success' => true,
            'by_status' => $byStatus,
            'total_payments' => $payments->count(),
            'total_amount' => $payments->sum('amount'),
        ]);
    }

    /**
     * Get payment statistics for a child
     * 
     * @param int $childId
     * @return JsonResponse
     */
    public function statistics(int $childId): JsonResponse
    {
        $student = Student::findOrFail($childId);

        $this->authorize('viewPayments', $student);

        $payments = $this->paymentRepo->getStudentPayments($childId);

        $stats = [
            'total_payments' => $payments->count(),
            'total_amount' => $payments->sum('amount'),
            'completed_count' => $payments->where('status', 'completed')->count(),
            'completed_amount' => $payments->where('status', 'completed')->sum('amount'),
            'pending_count' => $payments->where('status', 'pending')->count(),
            'pending_amount' => $payments->where('status', 'pending')->sum('amount'),
            'failed_count' => $payments->where('status', 'failed')->count(),
            'failed_amount' => $payments->where('status', 'failed')->sum('amount'),
            'completion_rate' => $payments->count() > 0 
                ? round(($payments->where('status', 'completed')->count() / $payments->count()) * 100, 2)
                : 0,
            'by_provider' => $payments->groupBy('provider')->map->count(),
        ];

        return response()->json([
            'success' => true,
            'statistics' => $stats,
        ]);
    }

    /**
     * Download payment receipt as PDF
     * 
     * @param int $paymentId
     * @return \Illuminate\Http\Response
     */
    public function downloadReceipt(int $paymentId)
    {
        $payment = Payment::findOrFail($paymentId);

        $this->authorize('viewPayment', $payment);

        if (!$payment->receipt) {
            return response()->json([
                'success' => false,
                'message' => 'No receipt available for this payment',
            ], 404);
        }

        $this->auditService->logFileAccess($payment->receipt->file_path, 'payment_receipt');

        return response()->download(storage_path("app/{$payment->receipt->file_path}"));
    }

    /**
     * Get available payment methods
     * 
     * @return JsonResponse
     */
    public function paymentMethods(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'methods' => [
                [
                    'provider' => 'campay',
                    'name' => 'Campay (Monetbil)',
                    'description' => 'Mobile money payment via Monetbil',
                    'icon' => '/images/providers/campay.png',
                    'min_amount' => 100,
                    'max_amount' => 1000000,
                ],
                [
                    'provider' => 'orange',
                    'name' => 'Orange Money',
                    'description' => 'Orange Money payment for all Orange networks',
                    'icon' => '/images/providers/orange.png',
                    'min_amount' => 100,
                    'max_amount' => 500000,
                ],
                [
                    'provider' => 'mtn',
                    'name' => 'MTN Money',
                    'description' => 'Mobile money payment via MTN',
                    'icon' => '/images/providers/mtn.png',
                    'min_amount' => 100,
                    'max_amount' => 1000000,
                ],
            ],
        ]);
    }
}
