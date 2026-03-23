<?php

namespace Tests\Unit\Services;

use App\Models\Payment;
use App\Models\Student;
use App\Services\PaymentSimulationService;
use Tests\TestCase;

/**
 * PaymentSimulationService Tests
 * 
 * PHASE 10: Unit tests for payment simulation
 */
class PaymentSimulationServiceTest extends TestCase
{
    private PaymentSimulationService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(PaymentSimulationService::class);
    }

    /**
     * Test Orange Money simulation initiation
     */
    public function test_simulate_orange_money_initiation()
    {
        $result = $this->service->simulateOrangeMoneyInitiation(
            amount: 500000,
            phoneNumber: '655123456',
            description: 'Test payment'
        );

        $this->assertTrue($result['success']);
        $this->assertArrayHasKey('transaction_id', $result);
        $this->assertEquals($result['operator'], 'orange');
        $this->assertEquals($result['amount'], 500000);
        $this->assertStringStartsWith('OM-SIM-', $result['transaction_id']);
        $this->assertArrayHasKey('simulated_delay', $result);
        $this->assertBetween(2, 5, $result['simulated_delay']);
    }

    /**
     * Test MTN MoMo simulation initiation
     */
    public function test_simulate_mtn_momo_initiation()
    {
        $result = $this->service->simulateMTNMoMoInitiation(
            amount: 250000,
            phoneNumber: '654987123',
            description: 'MTN Test'
        );

        $this->assertTrue($result['success']);
        $this->assertArrayHasKey('transaction_id', $result);
        $this->assertEquals($result['operator'], 'mtn');
        $this->assertEquals($result['amount'], 250000);
        $this->assertStringStartsWith('MTN-SIM-', $result['transaction_id']);
    }

    /**
     * Test payment status verification (success case)
     */
    public function test_simulate_payment_verification_success()
    {
        // Multiple attempts to get at least one success (90% rate)
        for ($i = 0; $i < 10; $i++) {
            $result = $this->service->simulatePaymentVerification('OM-SIM-TEST123');

            if ($result['success'] && $result['status'] === 'completed') {
                $this->assertTrue($result['success']);
                $this->assertEquals($result['status'], 'completed');
                return;
            }
        }

        // If we got here, we got 10 consecutive failures (unlikely with 90% rate)
        $this->fail('Expected at least one successful verification in 10 attempts');
    }

    /**
     * Test payment status verification (failure case)
     */
    public function test_simulate_payment_verification_failure()
    {
        // Loop until we get a failure (10% chance)
        for ($i = 0; $i < 50; $i++) {
            $result = $this->service->simulatePaymentVerification('OM-SIM-TEST123');

            if (!$result['success'] || $result['status'] === 'failed') {
                $this->assertFalse($result['success']);
                $this->assertEquals($result['status'], 'failed');
                return;
            }
        }

        // If we loop 50 times without seeing a failure, skip this test
        // (statistically extremely unlikely but theoretically possible)
        $this->markTestSkipped('Did not encounter simulated failure in random tests');
    }

    /**
     * Test webhook payload generation
     */
    public function test_simulate_webhook_callback()
    {
        $payment = Payment::factory()->create([
            'status' => 'pending',
            'provider' => 'orange',
            'amount' => 500000,
        ]);

        $payload = $this->service->simulateWebhookCallback($payment, success: true);

        $this->assertArrayHasKey('transaction_id', $payload);
        $this->assertArrayHasKey('operator', $payload);
        $this->assertArrayHasKey('phone', $payload);
        $this->assertArrayHasKey('amount', $payload);
        $this->assertArrayHasKey('status', $payload);
        $this->assertArrayHasKey('signature', $payload);
        $this->assertEquals($payload['status'], 'completed');
    }

    /**
     * Test HMAC signature generation
     */
    public function test_hmac_signature_generation()
    {
        $payload = [
            'transaction_id' => 'TEST-123',
            'amount' => 500000,
            'status' => 'completed',
        ];

        $signature = $this->service->generateHmacSignature($payload);

        $this->assertNotEmpty($signature);
        $this->assertIsString($signature);
        // HMAC SHA256 produces 64 character hex string
        $this->assertEquals(strlen($signature), 64);
    }

    /**
     * Test HMAC signature consistency
     */
    public function test_hmac_signature_consistency()
    {
        $payload = [
            'transaction_id' => 'TEST-123',
            'amount' => 500000,
        ];

        $sig1 = $this->service->generateHmacSignature($payload);
        $sig2 = $this->service->generateHmacSignature($payload);

        $this->assertEquals($sig1, $sig2, 'HMAC signatures should be consistent');
    }

    /**
     * Test payment completion
     */
    public function test_complete_payment_simulation()
    {
        $payment = Payment::factory()->create([
            'status' => 'pending',
        ]);

        $result = $this->service->completePaymentSimulation($payment);

        $this->assertTrue($result['success']);
        $this->assertEquals($result['status'], 'completed');

        // Verify payment was updated
        $payment->refresh();
        $this->assertEquals($payment->status, 'completed');
        $this->assertNotNull($payment->completed_at);
    }

    /**
     * Test simulation mode detection
     */
    public function test_simulation_mode_enabled()
    {
        config(['payment.default' => 'simulation']);
        $service = new PaymentSimulationService();

        $this->assertTrue($service->isEnabled());
    }

    /**
     * Test simulation mode disabled
     */
    public function test_simulation_mode_disabled()
    {
        config(['payment.default' => 'production']);
        $service = new PaymentSimulationService();

        $this->assertFalse($service->isEnabled());
    }

    /**
     * Helper: assert value is between min and max
     */
    private function assertBetween($min, $max, $value)
    {
        $this->assertGreaterThanOrEqual($min, $value);
        $this->assertLessThanOrEqual($max, $value);
    }
}
