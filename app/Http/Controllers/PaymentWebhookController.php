<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Services\PaymentGatewayService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class PaymentWebhookController extends Controller
{
    protected PaymentGatewayService $paymentService;

    public function __construct(PaymentGatewayService $paymentService)
    {
        $this->paymentService = $paymentService;
    }

    /**
     * Handle Campay webhook for payment notifications
     * 
     * POST /webhooks/payment/campay
     */
    public function handleCampayWebhook(Request $request): JsonResponse
    {
        try {
            Log::info('Campay webhook received', [
                'payload' => $request->all(),
                'ip' => $request->ip(),
            ]);

            // Verify webhook signature if configured
            if (!$this->verifyCampaySignature($request)) {
                Log::warning('Campay webhook signature verification failed', [
                    'ip' => $request->ip(),
                ]);
                return response()->json(['success' => false, 'message' => 'Signature verification failed'], 401);
            }

            // Delegate to service
            $result = $this->paymentService->handleWebhook($request->all());

            if ($result['success'] ?? false) {
                Log::info('Campay webhook processed successfully', [
                    'transaction_id' => $request->input('reference'),
                ]);
                return response()->json(['success' => true, 'message' => 'Webhook processed'], 200);
            } else {
                Log::warning('Campay webhook processing failed', [
                    'transaction_id' => $request->input('reference'),
                    'error' => $result['error'] ?? 'Unknown error',
                ]);
                return response()->json(['success' => false, 'message' => $result['error'] ?? 'Processing failed'], 400);
            }
        } catch (\Exception $e) {
            Log::error('Campay webhook error', [
                'exception' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json(['success' => false, 'message' => 'Internal server error'], 500);
        }
    }

    /**
     * Handle Orange Money webhook (if using direct integration)
     */
    public function handleOrangeMoneyWebhook(Request $request): JsonResponse
    {
        try {
            Log::info('Orange Money webhook received', [
                'payload' => $request->all(),
            ]);

            // Delegate to service (provider will be determined by payload)
            $result = $this->paymentService->handleWebhook($request->all());

            return response()->json(
                $result['success'] ? ['status' => 'OK'] : ['status' => 'FAILED'],
                $result['success'] ? 200 : 400
            );
        } catch (\Exception $e) {
            Log::error('Orange Money webhook error', [
                'exception' => $e->getMessage(),
            ]);
            return response()->json(['status' => 'ERROR'], 500);
        }
    }

    /**
     * Handle MTN Mobile Money webhook (if using direct integration)
     */
    public function handleMTNMoneyWebhook(Request $request): JsonResponse
    {
        try {
            Log::info('MTN Mobile Money webhook received', [
                'payload' => $request->all(),
            ]);

            // Delegate to service
            $result = $this->paymentService->handleWebhook($request->all());

            return response()->json(
                $result['success'] ? ['status' => 'accepted'] : ['status' => 'rejected'],
                $result['success'] ? 200 : 400
            );
        } catch (\Exception $e) {
            Log::error('MTN Mobile Money webhook error', [
                'exception' => $e->getMessage(),
            ]);
            return response()->json(['status' => 'error'], 500);
        }
    }

    /**
     * Verify Campay webhook signature
     * 
     * @param Request $request
     * @return bool
     */
    private function verifyCampaySignature(Request $request): bool
    {
        try {
            // Get signature from header
            $signature = $request->header('X-Campay-Signature');
            
            // If no configuration for signature verification, allow all
            if (!config('payment.campay.webhook_secret')) {
                return true;
            }

            if (!$signature) {
                return false;
            }

            // Compute expected signature (example: HMAC-SHA256)
            $payload = $request->getContent();
            $secret = config('payment.campay.webhook_secret');
            $expectedSignature = hash_hmac('sha256', $payload, $secret);

            // Use constant-time comparison
            return hash_equals($expectedSignature, $signature);
        } catch (\Exception $e) {
            Log::error('Campay signature verification error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Health check endpoint for webhook configuration
     */
    public function webhookHealth(): JsonResponse
    {
        return response()->json([
            'status' => 'healthy',
            'service' => 'payment-webhook',
            'timestamp' => now()->toIso8601String(),
        ]);
    }

    /**
     * Test webhook endpoint (for development)
     */
    public function testWebhook(Request $request): JsonResponse
    {
        if (!app()->isLocal()) {
            return response()->json(['error' => 'Not available in production'], 403);
        }

        Log::info('Test webhook received', $request->all());

        return response()->json([
            'message' => 'Test webhook received',
            'data' => $request->all(),
        ]);
    }
}
