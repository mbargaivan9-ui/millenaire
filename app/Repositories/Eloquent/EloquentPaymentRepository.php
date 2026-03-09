<?php

namespace App\Repositories\Eloquent;

use App\Repositories\Interfaces\PaymentRepositoryInterface;
use App\Models\Payment;
use Illuminate\Support\Collection;

/**
 * EloquentPaymentRepository
 * 
 * SOLID - Concrete implementation of PaymentRepositoryInterface
 */
class EloquentPaymentRepository implements PaymentRepositoryInterface
{
    /**
     * Get a payment by ID.
     *
     * @param int $id
     * @return Payment|null
     */
    public function find(int $id): ?Payment
    {
        return Payment::with(['student', 'receipt'])
            ->find($id);
    }

    /**
     * Get all payments for a student.
     *
     * @param int $studentId
     * @return Collection
     */
    public function getStudentPayments(int $studentId): Collection
    {
        return Payment::where('student_id', $studentId)
            ->with('receipt')
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Get all payments for a class.
     *
     * @param int $classId
     * @return Collection
     */
    public function getClassPayments(int $classId): Collection
    {
        return Payment::whereHas('student', function ($query) use ($classId) {
                $query->where('classe_id', $classId);
            })
            ->with(['student', 'receipt'])
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Create a new payment.
     *
     * @param array $data
     * @return Payment
     */
    public function create(array $data): Payment
    {
        return Payment::create($data);
    }

    /**
     * Update a payment.
     *
     * @param Payment $payment
     * @param array $data
     * @return Payment
     */
    public function update(Payment $payment, array $data): Payment
    {
        $payment->update($data);
        return $payment->refresh();
    }

    /**
     * Get payments by status.
     *
     * @param string $status 'pending', 'completed', 'failed'
     * @param int|null $classId
     * @return Collection
     */
    public function getByStatus(string $status, ?int $classId = null): Collection
    {
        $query = Payment::where('status', $status);

        if ($classId) {
            $query->whereHas('student', function ($q) use ($classId) {
                $q->where('classe_id', $classId);
            });
        }

        return $query->with(['student', 'receipt'])
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Get overdue payments (not received by expected date).
     *
     * @param int|null $classId
     * @return Collection
     */
    public function getOverduePayments(?int $classId = null): Collection
    {
        $query = Payment::where('status', '!=', 'completed')
            ->where('expected_date', '<', now());

        if ($classId) {
            $query->whereHas('student', function ($q) use ($classId) {
                $q->where('classe_id', $classId);
            });
        }

        return $query->with(['student', 'receipt'])
            ->orderBy('expected_date', 'asc')
            ->get();
    }

    /**
     * Calculate total paid amount for a student.
     *
     * @param int $studentId
     * @return float
     */
    public function getTotalPaid(int $studentId): float
    {
        return Payment::where('student_id', $studentId)
            ->where('status', 'completed')
            ->sum('amount') ?? 0;
    }

    /**
     * Get payment statistics for a class.
     *
     * @param int $classId
     * @return array
     */
    public function getClassStatistics(int $classId): array
    {
        $payments = $this->getClassPayments($classId);

        return [
            'total_students' => $payments->pluck('student_id')->unique()->count(),
            'total_amount' => $payments->sum('amount'),
            'total_completed' => $payments->where('status', 'completed')->sum('amount'),
            'total_pending' => $payments->where('status', 'pending')->sum('amount'),
            'completion_rate' => $payments->where('status', 'completed')->count() / max($payments->count(), 1) * 100,
            'by_status' => $payments->groupBy('status')->map->count(),
        ];
    }

    /**
     * Delete a payment record.
     *
     * @param Payment $payment
     * @return bool
     */
    public function delete(Payment $payment): bool
    {
        return $payment->delete();
    }
}
