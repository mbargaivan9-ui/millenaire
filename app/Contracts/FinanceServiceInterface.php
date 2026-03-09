<?php

namespace App\Contracts;

use App\Models\Fee;
use App\Models\Student;
use App\Models\User;

/**
 * FinanceServiceInterface
 * 
 * Contract for financial management and reporting
 * Supports all 4 roles: Admin, Teacher, Parent, Student
 * 
 * - Admin/Intendant: Full financial reports and treasury
 * - Teacher: View student payment status
 * - Parent: View family payments
 * - Student: View personal fees and payment status
 */
interface FinanceServiceInterface
{
    /**
     * Calculate total fees owed by a student
     * 
     * @param Student $student
     * @return float
     */
    public function calculateStudentBalance(Student $student): float;

    /**
     * Get unpaid fees for a student
     * 
     * @param Student $student
     * @return array
     */
    public function getUnpaidFees(Student $student): array;

    /**
     * Generate invoice for a student
     * 
     * @param Student $student
     * @param array $fees
     * @return array
     */
    public function generateInvoice(Student $student, array $fees): array;

    /**
     * Get treasury report for a period
     * 
     * @param \DateTime $startDate
     * @param \DateTime $endDate
     * @return array
     */
    public function getTreasuryReport(\DateTime $startDate, \DateTime $endDate): array;

    /**
     * Record fee payment - triggers notification
     * 
     * @param Student $student
     * @param float $amount
     * @param string $method
     * @param User|null $recordedBy - User who recorded it (Admin/Intendant)
     * @return void
     */
    public function recordPayment(Student $student, float $amount, string $method, ?User $recordedBy = null): void;

    /**
     * Send payment reminder to parent
     * 
     * @param User $parent
     * @param Student $student
     * @param float $amountDue
     * @return bool
     */
    public function sendPaymentReminder(User $parent, Student $student, float $amountDue): bool;

    /**
     * Get payment history for a student
     * 
     * @param Student $student
     * @param int $limit
     * @return \Illuminate\Support\Collection
     */
    public function getPaymentHistory(Student $student, int $limit = 20);

    /**
     * Generate payment receipt
     * 
     * @param int $paymentId
     * @return array Receipt data
     */
    public function generateReceipt(int $paymentId): array;

    /**
     * Get financial summary for admin dashboard
     * 
     * @return array Contains totals, pending, recent payments
     */
    public function getFinancialSummary(): array;
}
