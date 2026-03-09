<?php

namespace App\Services;

use App\Models\Classe;
use App\Models\Student;
use Illuminate\Support\Facades\Log;
use League\Csv\Writer;

class FinancialExportService
{
    private FinanceService $financeService;

    public function __construct(FinanceService $financeService)
    {
        $this->financeService = $financeService;
    }

    /**
     * Export class financial data to CSV
     */
    public function exportClassFinancialDataToCsv(Classe $class): string
    {
        try {
            $exportData = $this->financeService->exportClassFinancialData($class);

            if (empty($exportData)) {
                throw new \Exception('No data to export');
            }

            // Create CSV
            $csv = Writer::createFromString('');
            
            // Add headers
            $headers = array_keys($exportData[0]);
            $csv->insertOne($headers);
            
            // Add data rows
            foreach ($exportData as $row) {
                $csv->insertOne(array_values($row));
            }

            return $csv->toString();
        } catch (\Exception $e) {
            Log::error("Failed to export class financial data: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Export school financial data to CSV
     */
    public function exportSchoolFinancialDataToCsv(): string
    {
        try {
            $exportData = $this->financeService->exportSchoolFinancialData();

            if (empty($exportData)) {
                throw new \Exception('No data to export');
            }

            // Create CSV
            $csv = Writer::createFromString('');
            
            // Add headers
            $headers = array_keys($exportData[0]);
            $csv->insertOne($headers);
            
            // Add data rows
            foreach ($exportData as $row) {
                $csv->insertOne(array_values($row));
            }

            return $csv->toString();
        } catch (\Exception $e) {
            Log::error("Failed to export school financial data: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Export treasury report to CSV
     */
    public function exportTreasuryReportToCsv(\DateTime $startDate, \DateTime $endDate): string
    {
        try {
            $report = $this->financeService->getTreasuryReport($startDate, $endDate);

            if (!$report['success']) {
                throw new \Exception('Failed to generate treasury report');
            }

            $csv = Writer::createFromString('');

            // Add header section
            $csv->insertOne(['Treasury Report']);
            $csv->insertOne(['Period', $report['period']['start_date'] . ' to ' . $report['period']['end_date']]);
            $csv->insertOne(['Total Revenue', $report['total_revenue']]);
            $csv->insertOne(['Collection Rate', $report['collection_rate'] . '%']);
            $csv->insertOne(['Transaction Count', $report['transaction_count']]);
            $csv->insertOne([]);

            // Payment methods section
            $csv->insertOne(['Payment Methods']);
            foreach ($report['payment_methods'] as $method => $amount) {
                $csv->insertOne([$method, $amount]);
            }
            $csv->insertOne([]);

            // Daily revenue section
            $csv->insertOne(['Daily Revenue']);
            foreach ($report['revenue_by_day'] as $day => $amount) {
                $csv->insertOne([$day, $amount]);
            }

            return $csv->toString();
        } catch (\Exception $e) {
            Log::error("Failed to export treasury report: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Export invoice data to CSV
     */
    public function exportInvoiceToCsv(Student $student, array $fees): string
    {
        try {
            $invoice = $this->financeService->generateInvoice($student, $fees);

            if (!$invoice['success']) {
                throw new \Exception('Failed to generate invoice');
            }

            $csv = Writer::createFromString('');

            // Header
            $csv->insertOne(['INVOICE']);
            $csv->insertOne(['Invoice Number', $invoice['invoice_number']]);
            $csv->insertOne(['Invoice Date', $invoice['invoice_date']]);
            $csv->insertOne(['Due Date', $invoice['due_date']]);
            $csv->insertOne([]);

            // Student info
            $csv->insertOne(['Student Information']);
            $csv->insertOne(['Student Name', $invoice['student_name']]);
            $csv->insertOne(['Class', $invoice['class_name']]);
            $csv->insertOne([]);

            // Fees
            $csv->insertOne(['Description', 'Amount']);
            foreach ($invoice['fees'] as $fee) {
                $csv->insertOne([$fee['description'] ?? 'Fee', $fee['amount']]);
            }
            $csv->insertOne([]);

            // Summary
            $csv->insertOne(['SUMMARY']);
            $csv->insertOne(['Total Amount', $invoice['total_amount']]);
            $csv->insertOne(['Amount Paid', $invoice['amount_paid']]);
            $csv->insertOne(['Amount Due', $invoice['amount_due']]);

            return $csv->toString();
        } catch (\Exception $e) {
            Log::error("Failed to export invoice: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Export unpaid students list to CSV
     */
    public function exportUnpaidStudentsToCsv(Classe $class): string
    {
        try {
            $unpaidStudents = $this->financeService->getUnpaidStudents($class);

            if ($unpaidStudents->isEmpty()) {
                throw new \Exception('No unpaid students found');
            }

            $csv = Writer::createFromString('');
            
            // Headers
            $csv->insertOne(['Unpaid Students Report - ' . $class->name]);
            $csv->insertOne(['Generated at', now()->format('Y-m-d H:i:s')]);
            $csv->insertOne([]);
            
            $csv->insertOne(['Student Name', 'Total Due', 'Total Paid', 'Pending Amount', 'Is Overdue', 'Days Until Deadline']);

            // Add students
            foreach ($unpaidStudents as $student) {
                $summary = $this->financeService->getStudentFinancialSummary($student);
                
                $csv->insertOne([
                    $summary['student_name'],
                    $summary['total_due'],
                    $summary['total_paid'],
                    $summary['pending_amount'],
                    $summary['is_overdue'] ? 'Yes' : 'No',
                    $summary['days_until_deadline'] ?? 'N/A',
                ]);
            }

            return $csv->toString();
        } catch (\Exception $e) {
            Log::error("Failed to export unpaid students: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Generate HTML report (for PDF conversion via browser print)
     */
    public function generateHtmlReport(Classe $class): string
    {
        try {
            $summary = $this->financeService->getClassFinancialSummary($class);

            if (!$summary['success']) {
                throw new \Exception('Failed to get financial summary');
            }

            $html = '
            <!DOCTYPE html>
            <html lang="fr">
            <head>
                <meta charset="UTF-8">
                <meta name="viewport" content="width=device-width, initial-scale=1.0">
                <title>Financial Report - ' . $class->name . '</title>
                <style>
                    body {
                        font-family: Arial, sans-serif;
                        margin: 20px;
                        color: #333;
                    }
                    .header {
                        text-align: center;
                        margin-bottom: 30px;
                        border-bottom: 2px solid #333;
                        padding-bottom: 20px;
                    }
                    .header h1 {
                        margin: 0;
                        color: #2c3e50;
                    }
                    .summary {
                        display: grid;
                        grid-template-columns: 1fr 1fr;
                        gap: 20px;
                        margin-bottom: 30px;
                    }
                    .summary-box {
                        border: 1px solid #ddd;
                        padding: 15px;
                        border-radius: 5px;
                        background-color: #f9f9f9;
                    }
                    .summary-box h3 {
                        margin: 0 0 10px 0;
                        color: #2c3e50;
                    }
                    .summary-box p {
                        margin: 5px 0;
                        font-size: 14px;
                    }
                    .summary-box .value {
                        font-size: 24px;
                        font-weight: bold;
                        color: #27ae60;
                    }
                    table {
                        width: 100%;
                        border-collapse: collapse;
                        margin-top: 20px;
                    }
                    table thead {
                        background-color: #34495e;
                        color: white;
                    }
                    table th, table td {
                        padding: 12px;
                        text-align: left;
                        border-bottom: 1px solid #ddd;
                    }
                    table tbody tr:nth-child(even) {
                        background-color: #ecf0f1;
                    }
                    table tbody tr:hover {
                        background-color: #d5dbdb;
                    }
                    .status {
                        padding: 5px 10px;
                        border-radius: 3px;
                        font-size: 12px;
                        font-weight: bold;
                    }
                    .status.paid {
                        background-color: #d5f4e6;
                        color: #27ae60;
                    }
                    .status.partial {
                        background-color: #fef5e7;
                        color: #f39c12;
                    }
                    .status.unpaid {
                        background-color: #fadbd8;
                        color: #e74c3c;
                    }
                    .footer {
                        margin-top: 30px;
                        text-align: right;
                        font-size: 12px;
                        color: #7f8c8d;
                    }
                    @media print {
                        body { margin: 0; }
                        .no-print { display: none; }
                    }
                </style>
            </head>
            <body>
                <div class="header">
                    <h1>Financial Report</h1>
                    <p class="no-print">Class: ' . $class->name . '</p>
                    <p class="no-print">Generated: ' . now()->format('Y-m-d H:i:s') . '</p>
                </div>

                <div class="summary">
                    <div class="summary-box">
                        <h3>Total Due</h3>
                        <div class="value">' . number_format($summary['total_class_due'], 2) . ' FCFA</div>
                    </div>
                    <div class="summary-box">
                        <h3>Total Paid</h3>
                        <div class="value" style="color: #27ae60;">' . number_format($summary['total_class_paid'], 2) . ' FCFA</div>
                    </div>
                    <div class="summary-box">
                        <h3>Pending</h3>
                        <div class="value" style="color: #e74c3c;">' . number_format($summary['total_pending'], 2) . ' FCFA</div>
                    </div>
                    <div class="summary-box">
                        <h3>Collection Rate</h3>
                        <div class="value">' . $summary['payment_rate'] . '%</div>
                    </div>
                </div>

                <h2>Student Details</h2>
                <table>
                    <thead>
                        <tr>
                            <th>Student Name</th>
                            <th>Total Due</th>
                            <th>Total Paid</th>
                            <th>Pending</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>';

            foreach ($summary['students'] as $student) {
                $statusClass = $student['payment_status'];
                $html .= '
                        <tr>
                            <td>' . $student['student_name'] . '</td>
                            <td>' . number_format($student['total_due'], 2) . '</td>
                            <td>' . number_format($student['total_paid'], 2) . '</td>
                            <td>' . number_format($student['pending_amount'], 2) . '</td>
                            <td><span class="status ' . $statusClass . '">' . ucfirst($statusClass) . '</span></td>
                        </tr>';
            }

            $html .= '
                    </tbody>
                </table>

                <div class="footer">
                    <p>This report was generated on ' . now()->format('Y-m-d H:i:s') . '</p>
                </div>
            </body>
            </html>';

            return $html;
        } catch (\Exception $e) {
            Log::error("Failed to generate HTML report: " . $e->getMessage());
            throw $e;
        }
    }
}
