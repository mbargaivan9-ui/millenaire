<?php

namespace App\Services;

use App\Contracts\FinanceServiceInterface;
use App\Models\Student;
use App\Models\Classe;
use App\Models\FeeSetting;
use App\Models\Payment;
use App\Models\Fee;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Exception;
use DateTime;

class FinanceService implements FinanceServiceInterface
{
    /**
     * Get financial summary for a specific student
     */
    public function getStudentFinancialSummary(Student $student): array
    {
        try {
            $class = $student->classe;
            $feeSetting = FeeSetting::where('class_id', $class->id)
                ->currentYear()
                ->active()
                ->first();

            if (!$feeSetting) {
                return [
                    'success' => false,
                    'message' => 'No fee setting found for this class',
                ];
            }

            $totalDue = $feeSetting->getDiscountedAmount();
            
            $totalPaid = Payment::where('student_id', $student->id)
                ->where('status', 'completed')
                ->sum('amount');

            $pendingAmount = max(0, $totalDue - $totalPaid);
            
            $paymentStatus = $this->determinePaymentStatus($totalDue, $totalPaid);

            // Late fine if overdue
            $lateFine = 0;
            if ($feeSetting->isOverdue() && $feeSetting->apply_late_fine && $pendingAmount > 0) {
                $lateFine = $feeSetting->late_fine_amount;
            }

            return [
                'success' => true,
                'student_id' => $student->id,
                'student_name' => $student->user->name,
                'class_id' => $class->id,
                'class_name' => $class->name,
                'total_due' => $totalDue,
                'total_paid' => $totalPaid,
                'pending_amount' => $pendingAmount,
                'late_fine' => $lateFine,
                'final_amount_due' => $pendingAmount + $lateFine,
                'payment_status' => $paymentStatus,
                'is_overdue' => $feeSetting->isOverdue(),
                'payment_deadline' => $feeSetting->payment_deadline,
                'days_until_deadline' => $feeSetting->daysUntilDeadline(),
            ];
        } catch (Exception $e) {
            Log::error("Failed to get student financial summary: " . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Get financial summary for an entire class
     */
    public function getClassFinancialSummary(Classe $class): array
    {
        try {
            $students = $class->students;
            $feeSetting = FeeSetting::where('class_id', $class->id)
                ->currentYear()
                ->active()
                ->first();

            if (!$feeSetting) {
                return [
                    'success' => false,
                    'message' => 'No fee setting found for this class',
                ];
            }

            $studentSummaries = [];
            $totalClassDue = 0;
            $totalClassPaid = 0;
            $totalPending = 0;

            foreach ($students as $student) {
                $summary = $this->getStudentFinancialSummary($student);
                if ($summary['success']) {
                    $studentSummaries[] = $summary;
                    $totalClassDue += $summary['total_due'];
                    $totalClassPaid += $summary['total_paid'];
                    $totalPending += $summary['pending_amount'];
                }
            }

            $paymentRate = $students->count() > 0 
                ? round(($totalClassPaid / $totalClassDue) * 100, 2)
                : 0;

            return [
                'success' => true,
                'class_id' => $class->id,
                'class_name' => $class->name,
                'total_students' => $students->count(),
                'students' => $studentSummaries,
                'total_class_due' => $totalClassDue,
                'total_class_paid' => $totalClassPaid,
                'total_pending' => $totalPending,
                'payment_rate' => $paymentRate,
            ];
        } catch (Exception $e) {
            Log::error("Failed to get class financial summary: " . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Get unpaid students in a class
     */
    public function getUnpaidStudents(Classe $class): Collection
    {
        $feeSetting = FeeSetting::where('class_id', $class->id)
            ->currentYear()
            ->active()
            ->first();

        if (!$feeSetting) {
            return collect();
        }

        $totalDue = $feeSetting->getDiscountedAmount();

        $students = $class->students;
        $unpaidStudents = $students->filter(function ($student) use ($totalDue) {
            $totalPaid = Payment::where('student_id', $student->id)
                ->where('status', 'completed')
                ->sum('amount');

            return $totalPaid < $totalDue;
        });

        return $unpaidStudents;
    }

    /**
     * Get partially paid students in a class
     */
    public function getPartiallyPaidStudents(Classe $class): Collection
    {
        $feeSetting = FeeSetting::where('class_id', $class->id)
            ->currentYear()
            ->active()
            ->first();

        if (!$feeSetting) {
            return collect();
        }

        $totalDue = $feeSetting->getDiscountedAmount();

        $students = $class->students;
        $partiallyPaid = $students->filter(function ($student) use ($totalDue) {
            $totalPaid = Payment::where('student_id', $student->id)
                ->where('status', 'completed')
                ->sum('amount');

            return $totalPaid > 0 && $totalPaid < $totalDue;
        });

        return $partiallyPaid;
    }

    /**
     * Get fully paid students in a class
     */
    public function getFullyPaidStudents(Classe $class): Collection
    {
        $feeSetting = FeeSetting::where('class_id', $class->id)
            ->currentYear()
            ->active()
            ->first();

        if (!$feeSetting) {
            return collect();
        }

        $totalDue = $feeSetting->getDiscountedAmount();

        $students = $class->students;
        $fullyPaid = $students->filter(function ($student) use ($totalDue) {
            $totalPaid = Payment::where('student_id', $student->id)
                ->where('status', 'completed')
                ->sum('amount');

            return $totalPaid >= $totalDue;
        });

        return $fullyPaid;
    }

    /**
     * Generate financial report for all classes
     */
    public function getSchoolFinancialReport(): array
    {
        try {
            $classes = Classe::where('is_active', true)->get();
            $classSummaries = [];
            
            $totalSchoolDue = 0;
            $totalSchoolPaid = 0;
            $totalSchoolPending = 0;

            foreach ($classes as $class) {
                $summary = $this->getClassFinancialSummary($class);
                if ($summary['success']) {
                    $classSummaries[] = $summary;
                    $totalSchoolDue += $summary['total_class_due'];
                    $totalSchoolPaid += $summary['total_class_paid'];
                    $totalSchoolPending += $summary['total_pending'];
                }
            }

            $schoolPaymentRate = $totalSchoolDue > 0
                ? round(($totalSchoolPaid / $totalSchoolDue) * 100, 2)
                : 0;

            return [
                'success' => true,
                'generated_at' => now(),
                'total_classes' => count($classSummaries),
                'classes' => $classSummaries,
                'total_school_due' => $totalSchoolDue,
                'total_school_paid' => $totalSchoolPaid,
                'total_school_pending' => $totalSchoolPending,
                'school_payment_rate' => $schoolPaymentRate,
            ];
        } catch (Exception $e) {
            Log::error("Failed to generate school financial report: " . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Determine payment status based on paid vs due amounts
     */
    private function determinePaymentStatus(float $totalDue, float $totalPaid): string
    {
        if ($totalPaid <= 0) {
            return 'unpaid';
        } elseif ($totalPaid >= $totalDue) {
            return 'paid';
        } else {
            return 'partial';
        }
    }

    /**
     * Get all overdue payments
     */
    public function getOverduePayments(): array
    {
        try {
            $overdueFees = FeeSetting::overdue()->get();
            $overdueStudents = [];

            foreach ($overdueFees as $fee) {
                $unpaidStudents = $this->getUnpaidStudents($fee->classe);
                foreach ($unpaidStudents as $student) {
                    $summary = $this->getStudentFinancialSummary($student);
                    $overdueStudents[] = $summary;
                }
            }

            return [
                'success' => true,
                'data' => $overdueStudents,
                'count' => count($overdueStudents),
            ];
        } catch (Exception $e) {
            Log::error("Failed to get overdue payments: " . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Create or update fee setting for a class
     */
    public function setClassFee(
        int $classId,
        float $amount,
        ?string $description = null,
        ?string $paymentDeadline = null,
        ?array $installments = null,
        ?bool $applyLateFine = false,
        ?float $lateFineAmount = null,
        ?float $discountPercentage = null,
    ): array {
        try {
            $feeSetting = FeeSetting::updateOrCreate(
                [
                    'class_id' => $classId,
                    'academic_year' => config('app.current_academic_year', date('Y') . '-' . (date('Y') + 1)),
                ],
                [
                    'amount' => $amount,
                    'description' => $description,
                    'payment_deadline' => $paymentDeadline,
                    'installments' => $installments,
                    'apply_late_fine' => $applyLateFine,
                    'late_fine_amount' => $lateFineAmount ?? 0,
                    'discount_percentage' => $discountPercentage ?? 0,
                    'is_active' => true,
                ]
            );

            Log::info("Fee setting updated", [
                'class_id' => $classId,
                'amount' => $amount,
            ]);

            return [
                'success' => true,
                'message' => 'Fee setting saved successfully',
                'fee_setting' => $feeSetting,
            ];
        } catch (Exception $e) {
            Log::error("Failed to set class fee: " . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Export financial data to array for Excel
     */
    public function exportClassFinancialData(Classe $class): array
    {
        $summary = $this->getClassFinancialSummary($class);

        if (!$summary['success']) {
            return [];
        }

        $exportData = [];
        foreach ($summary['students'] as $student) {
            $exportData[] = [
                'Student Name' => $student['student_name'],
                'Class' => $student['class_name'],
                'Total Due' => $student['total_due'],
                'Total Paid' => $student['total_paid'],
                'Pending Amount' => $student['pending_amount'],
                'Payment Status' => ucfirst($student['payment_status']),
                'Is Overdue' => $student['is_overdue'] ? 'Yes' : 'No',
                'Days Until Deadline' => $student['days_until_deadline'] ?? 'N/A',
            ];
        }

        return $exportData;
    }

    /**
     * Export all school financial data
     */
    public function exportSchoolFinancialData(): array
    {
        $report = $this->getSchoolFinancialReport();

        if (!$report['success']) {
            return [];
        }

        $exportData = [];
        foreach ($report['classes'] as $class) {
            foreach ($class['students'] as $student) {
                $exportData[] = [
                    'Class' => $class['class_name'],
                    'Student Name' => $student['student_name'],
                    'Total Due' => $student['total_due'],
                    'Total Paid' => $student['total_paid'],
                    'Pending Amount' => $student['pending_amount'],
                    'Payment Status' => ucfirst($student['payment_status']),
                    'Is Overdue' => $student['is_overdue'] ? 'Yes' : 'No',
                ];
            }
        }

        return $exportData;
    }

    /**
     * Calculate total fees owed by a student (Interface Implementation)
     */
    public function calculateStudentBalance(Student $student): float
    {
        try {
            $class = $student->classe;
            if (!$class) {
                return 0;
            }

            $feeSetting = FeeSetting::where('class_id', $class->id)
                ->currentYear()
                ->active()
                ->first();

            if (!$feeSetting) {
                return 0;
            }

            $totalDue = $feeSetting->getDiscountedAmount();
            $totalPaid = Payment::where('student_id', $student->id)
                ->where('status', 'completed')
                ->sum('amount');

            $balance = max(0, $totalDue - $totalPaid);

            // Add late fine if applicable
            if ($feeSetting->isOverdue() && $feeSetting->apply_late_fine && $balance > 0) {
                $balance += $feeSetting->late_fine_amount;
            }

            return $balance;
        } catch (Exception $e) {
            Log::error("Failed to calculate student balance: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Get unpaid fees for a student (Interface Implementation)
     */
    public function getUnpaidFees(Student $student): array
    {
        try {
            $class = $student->classe;
            if (!$class) {
                return [];
            }

            $feeSetting = FeeSetting::where('class_id', $class->id)
                ->currentYear()
                ->active()
                ->first();

            if (!$feeSetting) {
                return [];
            }

            $totalDue = $feeSetting->getDiscountedAmount();
            $totalPaid = Payment::where('student_id', $student->id)
                ->where('status', 'completed')
                ->sum('amount');

            $unpaidAmount = max(0, $totalDue - $totalPaid);

            return [
                'student_id' => $student->id,
                'student_name' => $student->user->name,
                'total_due' => $totalDue,
                'total_paid' => $totalPaid,
                'unpaid_amount' => $unpaidAmount,
                'has_late_fine' => $feeSetting->isOverdue() && $feeSetting->apply_late_fine,
                'late_fine_amount' => $feeSetting->late_fine_amount ?? 0,
                'is_overdue' => $feeSetting->isOverdue(),
                'deadline' => $feeSetting->payment_deadline,
            ];
        } catch (Exception $e) {
            Log::error("Failed to get unpaid fees: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Generate invoice for a student (Interface Implementation)
     */
    public function generateInvoice(Student $student, array $fees): array
    {
        try {
            $invoiceNumber = 'INV-' . date('Ymd') . '-' . $student->id;
            $invoiceDate = now();
            
            $class = $student->classe;
            $totalAmount = array_sum(array_column($fees, 'amount'));

            $totalPaid = Payment::where('student_id', $student->id)
                ->where('status', 'completed')
                ->sum('amount');

            $dueAmount = max(0, $totalAmount - $totalPaid);

            $dueDate = $invoiceDate->addDays(30);

            return [
                'success' => true,
                'invoice_number' => $invoiceNumber,
                'invoice_date' => $invoiceDate->toDateString(),
                'due_date' => $dueDate->toDateString(),
                'student_id' => $student->id,
                'student_name' => $student->user->name,
                'class_name' => $class->name ?? 'N/A',
                'fees' => $fees,
                'total_amount' => $totalAmount,
                'amount_paid' => $totalPaid,
                'amount_due' => $dueAmount,
                'generated_at' => $invoiceDate->toDateTimeString(),
            ];
        } catch (Exception $e) {
            Log::error("Failed to generate invoice: " . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Get treasury report for a period (Interface Implementation)
     */
    public function getTreasuryReport(DateTime $startDate, DateTime $endDate): array
    {
        try {
            $payments = Payment::where('status', 'completed')
                ->whereBetween('created_at', [$startDate, $endDate])
                ->get();

            $totalRevenue = $payments->sum('amount');
            $paymentMethods = $payments->groupBy('payment_method')
                ->map(fn ($group) => $group->sum('amount'))
                ->toArray();

            $paymentsByDay = $payments->groupBy(fn ($p) => $p->created_at->toDateString())
                ->map(fn ($group) => $group->sum('amount'))
                ->toArray();

            // Calculate collection rate
            $feesSettings = FeeSetting::where('academic_year', config('app.current_academic_year', date('Y') . '-' . (date('Y') + 1)))
                ->get();

            $totalDue = $feesSettings->sum(fn ($fee) => $fee->getDiscountedAmount());
            $collectionRate = $totalDue > 0 ? round(($totalRevenue / $totalDue) * 100, 2) : 0;

            return [
                'success' => true,
                'period' => [
                    'start_date' => $startDate->format('Y-m-d'),
                    'end_date' => $endDate->format('Y-m-d'),
                ],
                'total_revenue' => $totalRevenue,
                'transaction_count' => $payments->count(),
                'collection_rate' => $collectionRate,
                'payment_methods' => $paymentMethods,
                'revenue_by_day' => $paymentsByDay,
                'average_daily_revenue' => $payments->count() > 0 ? round($totalRevenue / $payments->count(), 2) : 0,
            ];
        } catch (Exception $e) {
            Log::error("Failed to get treasury report: " . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Record fee payment (Interface Implementation)
     */
    public function recordPayment(Student $student, float $amount, string $method): void
    {
        try {
            Payment::create([
                'student_id' => $student->id,
                'amount' => $amount,
                'payment_method' => $method,
                'status' => 'completed',
                'transaction_id' => 'TXN-' . time() . '-' . $student->id,
                'paid_at' => now(),
            ]);

            Log::info("Payment recorded", [
                'student_id' => $student->id,
                'amount' => $amount,
                'method' => $method,
            ]);

            // Log audit trail
            \App\Services\AdminAuditService::log(
                auth()->user(),
                'record_payment',
                'Payment',
                $student->id,
                ['amount' => $amount, 'method' => $method],
                []
            );
        } catch (Exception $e) {
            Log::error("Failed to record payment: " . $e->getMessage());
            throw $e;
        }
    }
}
