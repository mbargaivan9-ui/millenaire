<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Receipt - {{ $receiptNumber }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
            background: #f5f5f5;
        }
        .receipt-container {
            max-width: 800px;
            margin: 20px auto;
            background: white;
            padding: 40px;
            border: 2px solid #2c3e50;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #3498db;
            padding-bottom: 20px;
        }
        .header h1 {
            color: #2c3e50;
            font-size: 28px;
            margin-bottom: 5px;
        }
        .header p {
            color: #7f8c8d;
            font-size: 12px;
        }
        .receipt-number {
            text-align: center;
            font-size: 16px;
            color: #2980b9;
            font-weight: bold;
            margin: 15px 0;
        }
        .qr-code {
            text-align: center;
            margin: 20px 0;
        }
        .qr-code img {
            width: 150px;
            height: 150px;
            border: 1px solid #bdc3c7;
            padding: 5px;
        }
        .info-section {
            margin: 25px 0;
        }
        .info-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
            padding: 8px 0;
            border-bottom: 1px dotted #ecf0f1;
        }
        .info-label {
            font-weight: 600;
            color: #2c3e50;
            width: 40%;
        }
        .info-value {
            text-align: right;
            color: #555;
        }
        .student-info {
            background: #ecf0f1;
            padding: 15px;
            border-left: 4px solid #3498db;
            margin-bottom: 20px;
        }
        .student-info h3 {
            color: #2c3e50;
            margin-bottom: 10px;
            font-size: 14px;
        }
        .student-detail {
            display: flex;
            margin: 8px 0;
            font-size: 13px;
        }
        .student-detail span:first-child {
            font-weight: 600;
            width: 100px;
        }
        .payment-section {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin: 25px 0;
        }
        .payment-amount {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 0;
            border-bottom: 2px solid #3498db;
            margin-bottom: 15px;
        }
        .payment-amount-label {
            font-size: 16px;
            color: #2c3e50;
            font-weight: 600;
        }
        .payment-amount-value {
            font-size: 24px;
            color: #27ae60;
            font-weight: bold;
        }
        .payment-details {
            font-size: 13px;
        }
        .payment-row {
            display: flex;
            justify-content: space-between;
            margin: 8px 0;
        }
        .timestamp {
            text-align: center;
            color: #95a5a6;
            font-size: 11px;
            margin-top: 20px;
            padding-top: 15px;
            border-top: 1px solid #bdc3c7;
        }
        .footer {
            text-align: center;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 2px solid #ecf0f1;
            color: #7f8c8d;
            font-size: 12px;
        }
        .footer p {
            margin: 5px 0;
        }
        .status-badge {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            margin: 10px 0;
        }
        .status-completed {
            background: #d5f4e6;
            color: #27ae60;
        }
        .status-pending {
            background: #fde5c8;
            color: #e67e22;
        }
        .print-only {
            display: block;
        }
    </style>
</head>
<body>
    <div class="receipt-container">
        <!-- Header -->
        <div class="header">
            <h1>{{ config('app.name', 'Millénaire connect') }}</h1>
            <p>Official Payment Receipt</p>
        </div>

        <!-- Receipt Number -->
        <div class="receipt-number">
            Receipt #{{ $receiptNumber }}
        </div>

        <!-- QR Code -->
        <div class="qr-code">
            @if($qrCode)
                <img src="{{ $qrCode }}" alt="QR Code">
            @endif
        </div>

        <!-- Student Information -->
        <div class="student-info">
            <h3>Student Information</h3>
            <div class="student-detail">
                <span>Name:</span>
                <span>{{ $student->user->name ?? $student->first_name . ' ' . $student->last_name }}</span>
            </div>
            <div class="student-detail">
                <span>Matricule:</span>
                <span>{{ $student->matricule }}</span>
            </div>
            <div class="student-detail">
                <span>Email:</span>
                <span>{{ $student->user->email ?? 'N/A' }}</span>
            </div>
            @if($student->classe)
            <div class="student-detail">
                <span>Class:</span>
                <span>{{ $student->classe->name }}</span>
            </div>
            @endif
        </div>

        <!-- Payment Information -->
        <div class="payment-section">
            <div class="payment-amount">
                <span class="payment-amount-label">Amount Paid</span>
                <span class="payment-amount-value">
                    {{ number_format($payment->amount ?? $payment->amount_paid ?? 0, 2) }}
                    {{ $payment->currency ?? 'XAF' }}
                </span>
            </div>

            <div class="payment-details">
                <div class="payment-row">
                    <span>Payment Method:</span>
                    <span>{{ ucfirst(str_replace('_', ' ', $payment->provider ?? $payment->payment_method ?? 'Mobile Money')) }}</span>
                </div>

                @if($payment->transaction_id)
                <div class="payment-row">
                    <span>Transaction ID:</span>
                    <span>{{ $payment->transaction_id }}</span>
                </div>
                @endif

                <div class="payment-row">
                    <span>Purpose:</span>
                    <span>{{ ucfirst(str_replace('_', ' ', $payment->purpose ?? 'School Fees')) }}</span>
                </div>

                @if($payment->description)
                <div class="payment-row">
                    <span>Description:</span>
                    <span>{{ $payment->description }}</span>
                </div>
                @endif

                <div class="payment-row">
                    <span>Payment Date:</span>
                    <span>{{ $payment->completed_at ? $payment->completed_at->format('d/m/Y H:i') : $issuedAt }}</span>
                </div>

                <div class="payment-row">
                    <span>Status:</span>
                    <span class="status-badge status-{{ strtolower($payment->status ?? 'pending') }}">
                        {{ strtoupper($payment->status ?? 'PENDING') }}
                    </span>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div class="footer">
            <p>This is an official payment receipt from {{ config('app.name', 'Millénaire connect') }}</p>
            <p>Please keep this receipt for your records</p>
            <p class="timestamp">Generated on: {{ $issuedAt }}</p>
        </div>
    </div>
</body>
</html>

