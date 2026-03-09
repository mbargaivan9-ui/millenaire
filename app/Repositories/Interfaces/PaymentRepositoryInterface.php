<?php

namespace App\Repositories\Interfaces;

use App\Models\Payment;
use Illuminate\Support\Collection;

/**
 * PaymentRepositoryInterface
 * 
 * SOLID - Repository Pattern for Payment data access
 */
interface PaymentRepositoryInterface
{
    /**
     * Get a payment by ID.
     *
     * @param int $id
     * @return Payment|null
     */
    public function find(int $id): ?Payment;

    /**
     * Get all payments for a student.
     *
     * @param int $studentId
     * @return Collection
     */
    public function getStudentPayments(int $studentId): Collection;

    /**
     * Get all payments for a class.
     *
     * @param int $classId
     * @return Collection
     */
    public function getClassPayments(int $classId): Collection;

    /**
     * Create a new payment.
     *
     * @param array $data
     * @return Payment
     */
    public function create(array $data): Payment;

    /**
     * Update a payment.
     *
     * @param Payment $payment
     * @param array $data
     * @return Payment
     */
    public function update(Payment $payment, array $data): Payment;

    /**
     * Get payments by status.
     *
     * @param string $status 'pending', 'completed', 'failed'
     * @param int|null $classId
     * @return Collection
     */
    public function getByStatus(string $status, ?int $classId = null): Collection;

    /**
     * Get overdue payments (not received by expected date).
     *
     * @param int|null $classId
     * @return Collection
     */
    public function getOverduePayments(?int $classId = null): Collection;

    /**
     * Calculate total paid amount for a student.
     *
     * @param int $studentId
     * @return float
     */
    public function getTotalPaid(int $studentId): float;

    /**
     * Get payment statistics for a class.
     *
     * @param int $classId
     * @return array
     */
    public function getClassStatistics(int $classId): array;

    /**
     * Delete a payment record.
     *
     * @param Payment $payment
     * @return bool
     */
    public function delete(Payment $payment): bool;
}
