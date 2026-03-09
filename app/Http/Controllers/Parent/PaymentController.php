<?php

namespace App\Http\Controllers\Parent;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\Payment;
use App\Models\PaymentReceipt;
use App\Services\PaymentGatewayService;
use App\Services\ReceiptService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    protected PaymentGatewayService $paymentService;
    protected ReceiptService $receiptService;

    public function __construct(PaymentGatewayService $paymentService, ReceiptService $receiptService)
    {
        $this->paymentService = $paymentService;
        $this->receiptService = $receiptService;
    }

    /**
     * Display payment dashboard for parent
     */
    public function index(): View
    {
        $parent = Auth::user();
        $children = $parent->children ?? collect();

        $paymentsSummary = [];
        foreach ($children as $child) {
            $paymentsSummary[$child->id] = [
                'total_due' => $child->getTotalAmountDue(),
                'total_paid' => $child->getTotalAmountPaid(),
                'status' => $child->getFinancialStatus(),
                'pending_payments' => Payment::where('student_id', $child->id)
                    ->where('status', 'pending')
                    ->count(),
            ];
        }

        return view('parent.payments.index', [
            'children' => $children,
            'paymentsSummary' => $paymentsSummary,
        ]);
    }

    /**
     * Show payment initiation form
     */
    public function show(Student $student): View
    {
        // Verify parent owns this student
        $this->authorizeParentStudent($student);

        $payments = Payment::where('student_id', $student->id)
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        $financialStatus = $student->getFinancialStatus();
        $totalDue = $student->getTotalAmountDue();
        $totalPaid = $student->getTotalAmountPaid();

        return view('parent.payments.show', [
            'student' => $student,
            'payments' => $payments,
            'financialStatus' => $financialStatus,
            'totalDue' => $totalDue,
            'totalPaid' => $totalPaid,
            'balance' => $totalDue - $totalPaid,
        ]);
    }

    /**
     * Initiate payment (AJAX)
     */
    public function initiate(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'student_id' => 'required|exists:students,id',
                'amount' => 'required|numeric|min:1000|max:5000000',
                'phone_number' => 'required|string|regex:/^([+]|00)?[0-9\s\-()]{9,15}$/',
                'purpose' => 'required|in:tuition_fees,exam_fees,uniform,books,other',
                'description' => 'nullable|string|max:500',
            ]);

            $student = Student::findOrFail($validated['student_id']);

            // Verify parent owns this student
            $this->authorizeParentStudent($student);

            // Initiate payment
            $result = $this->paymentService->initiatePayment(
                $student,
                $validated['amount'],
                $validated['purpose'],
                $validated['description'] ?? 'School payment'
            );

            if ($result['success']) {
                return response()->json([
                    'success' => true,
                    'message' => __('Payment initiated successfully'),
                    'transaction_id' => $result['transaction_id'] ?? null,
                    'data' => $result,
                ], 200);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => $result['message'] ?? __('Payment initiation failed'),
                ], 400);
            }
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => __('Validation failed'),
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            Log::error('Payment initiation error: ' . $e->getMessage(), [
                'student_id' => $request->input('student_id'),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => __('An error occurred. Please try again.'),
            ], 500);
        }
    }

    /**
     * Get payment history for a student
     */
    public function history(Student $student): JsonResponse
    {
        $this->authorizeParentStudent($student);

        $payments = Payment::where('student_id', $student->id)
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($payment) {
                return [
                    'id' => $payment->id,
                    'transaction_id' => $payment->transaction_id,
                    'amount' => $payment->amount ?? $payment->amount_paid,
                    'currency' => $payment->currency ?? 'XAF',
                    'status' => $payment->status,
                    'status_label' => $payment->getStatusLabel(),
                    'purpose' => $payment->purpose ?? $payment->payment_method,
                    'provider' => $payment->provider,
                    'completed_at' => $payment->completed_at?->format('d/m/Y H:i'),
                    'initiated_at' => $payment->initiated_at?->format('d/m/Y H:i'),
                    'has_receipt' => (bool) $payment->receipt,
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $payments,
            'total' => $payments->count(),
        ]);
    }

    /**
     * Download receipt PDF
     */
    public function downloadReceipt(PaymentReceipt $receipt): \Symfony\Component\HttpFoundation\BinaryFileResponse
    {
        // Verify authorization
        $payment = $receipt->payment;
        $student = $payment->student;
        
        $this->authorizeParentStudent($student);

        $filePath = storage_path('app/public/' . $receipt->pdf_path);

        if (!file_exists($filePath)) {
            abort(404, 'Receipt file not found');
        }

        return response()->download($filePath, "receipt-{$receipt->receipt_number}.pdf");
    }

    /**
     * Verify receipt authenticity (public)
     */
    public function verifyReceipt(Request $request): JsonResponse
    {
        try {
            $receiptNumber = $request->input('receipt');
            $token = $request->input('token');

            $isValid = $this->receiptService->verifyReceipt($receiptNumber, $token);

            if ($isValid) {
                $receipt = $this->receiptService->getReceipt($receiptNumber);
                return response()->json([
                    'valid' => true,
                    'message' => __('Receipt is authentic'),
                    'receipt_number' => $receipt->receipt_number,
                    'amount' => $receipt->amount,
                    'issued_at' => $receipt->issued_at?->format('d/m/Y H:i'),
                    'student_name' => $receipt->payment->student->user->name ?? 'N/A',
                ]);
            } else {
                return response()->json([
                    'valid' => false,
                    'message' => __('Receipt verification failed'),
                ], 400);
            }
        } catch (\Exception $e) {
            Log::error('Receipt verification error: ' . $e->getMessage());
            return response()->json([
                'valid' => false,
                'message' => __('Unable to verify receipt'),
            ], 500);
        }
    }

    /**
     * Check payment status (polling)
     */
    public function checkStatus(Payment $payment): JsonResponse
    {
        $this->authorizeParentStudent($payment->student);

        $result = $this->paymentService->verifyPayment($payment);

        return response()->json([
            'success' => true,
            'status' => $payment->status,
            'status_label' => $payment->getStatusLabel(),
            'completed_at' => $payment->completed_at?->format('d/m/Y H:i'),
            'has_receipt' => (bool) $payment->receipt,
            'data' => $result,
        ]);
    }

    /**
     * Get payment statistics for dashboard
     */
    public function statistics(): JsonResponse
    {
        $parent = Auth::user();
        $children = $parent->children ?? collect();

        $stats = [
            'total_children' => $children->count(),
            'total_due' => 0,
            'total_paid' => 0,
            'pending_payments' => 0,
            'completed_payments' => 0,
        ];

        foreach ($children as $child) {
            $stats['total_due'] += $child->getTotalAmountDue();
            $stats['total_paid'] += $child->getTotalAmountPaid();
            $stats['pending_payments'] += Payment::where('student_id', $child->id)
                ->where('status', 'pending')->count();
            $stats['completed_payments'] += Payment::where('student_id', $child->id)
                ->where('status', 'completed')->count();
        }

        return response()->json([
            'success' => true,
            'data' => $stats,
        ]);
    }

    /**
     * Authorize parent-student relationship
     */
    private function authorizeParentStudent(Student $student): void
    {
        $parent = Auth::user();

        // Check if parent has this student as child
        if (!$parent->children || !$parent->children->contains('id', $student->id)) {
            abort(403, 'Unauthorized');
        }
    }
}
