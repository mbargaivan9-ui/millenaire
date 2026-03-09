<?php

namespace App\Contracts;

/**
 * PaymentInterface
 * 
 * Contract for payment gateway implementations
 * Follows the Dependency Inversion Principle (SOLID)
 * 
 * Supports all 4 roles: Admin, Teacher, Parent, Student
 * 
 * - Admin/Intendant: Full payment management and webhooks
 * - Parent: Initiate payments for student fees
 * - Student: View payment history
 * - Teacher: View student payment status
 */
interface PaymentInterface
{
    /**
     * Initialize payment request - triggers notification
     * 
     * @param float $amount Amount to charge
     * @param string $phoneNumber Customer phone number
     * @param string $description Payment description
     * @param array $metadata Additional metadata (student_id, fee_type, etc.)
     * 
     * @return array ['success' => bool, 'transaction_id' => string, 'data' => array]
     */
    public function initiate(
        float $amount,
        string $phoneNumber,
        string $description = '',
        array $metadata = []
    ): array;

    /**
     * Verify payment status
     * 
     * @param string $transactionId Transaction ID from payment provider
     * 
     * @return array ['success' => bool, 'status' => string, 'data' => array]
     */
    public function verify(string $transactionId): array;

    /**
     * Handle webhook from payment provider - triggers notifications
     * 
     * @param array $payload Webhook payload
     * 
     * @return array ['success' => bool, 'message' => string]
     */
    public function handleWebhook(array $payload): array;

    /**
     * Get payment provider name
     * 
     * @return string
     */
    public function getProviderName(): string;

    /**
     * Check if provider is configured
     * 
     * @return bool
     */
    public function isConfigured(): bool;

    /**
     * Get payment status name
     * 
     * @param string $status
     * @return string Human-readable status
     */
    public function getStatusLabel(string $status): string;

    /**
     * Cancel a pending payment
     * 
     * @param string $transactionId
     * @return bool
     */
    public function cancel(string $transactionId): bool;

    /**
     * Refund a completed payment - triggers notification
     * 
     * @param string $transactionId
     * @param float|null $amount Partial refund amount
     * @return array ['success' => bool, 'refund_id' => string]
     */
    public function refund(string $transactionId, ?float $amount = null): array;
}
