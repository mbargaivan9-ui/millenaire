@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-md-8">
            <h1 class="h3">
                <a href="{{ route('admin.finance.index') }}" class="btn btn-link text-muted">
                    <i class="fas fa-arrow-left me-1"></i>
                </a>
                Financial Details: {{ $summary['student_name'] }}
            </h1>
        </div>
        <div class="col-md-4 text-end">
            <a href="{{ route('admin.finance.invoice', $student) }}" class="btn btn-success">
                <i class="fas fa-receipt me-2"></i>Generate Invoice
            </a>
        </div>
    </div>

    <!-- Student Information -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card border-left-info">
                <div class="card-body">
                    <div class="text-info text-uppercase mb-1 small fw-bold">Class</div>
                    <div class="h5 mb-0">{{ $summary['class_name'] }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-left-primary">
                <div class="card-body">
                    <div class="text-primary text-uppercase mb-1 small fw-bold">Total Due</div>
                    <div class="h5 mb-0">{{ number_format($summary['total_due'] ?? 0, 0) }} FCFA</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-left-success">
                <div class="card-body">
                    <div class="text-success text-uppercase mb-1 small fw-bold">Total Paid</div>
                    <div class="h5 mb-0">{{ number_format($summary['total_paid'] ?? 0, 0) }} FCFA</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card @if($summary['pending_amount'] > 0) border-left-danger @else border-left-success @endif">
                <div class="card-body">
                    <div class="text-uppercase mb-1 small fw-bold">
                        @if($summary['pending_amount'] > 0)
                            <span class="text-danger">Balance Due</span>
                        @else
                            <span class="text-success">FULLY PAID</span>
                        @endif
                    </div>
                    <div class="h5 mb-0">
                        @if($summary['pending_amount'] > 0)
                            <span class="text-danger fw-bold">{{ number_format($summary['pending_amount'] ?? 0, 0) }} FCFA</span>
                        @else
                            <span class="text-success fw-bold">✓ {{ number_format($summary['pending_amount'] ?? 0, 0) }} FCFA</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Payment Status -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0">Payment Status</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <h6>Payment Status</h6>
                    <p>
                        @if($summary['payment_status'] === 'unpaid')
                            <span class="badge bg-danger fs-5">UNPAID</span>
                        @elseif($summary['payment_status'] === 'partial')
                            <span class="badge bg-warning fs-5">PARTIALLY PAID</span>
                        @else
                            <span class="badge bg-success fs-5">FULLY PAID</span>
                        @endif
                    </p>
                </div>
                <div class="col-md-6">
                    <h6>Overdue Status</h6>
                    <p>
                        @if($summary['is_overdue'])
                            <span class="badge bg-danger fs-5">
                                <i class="fas fa-exclamation-triangle me-1"></i>
                                OVERDUE
                            </span>
                            <br>
                            <small class="text-muted">Payment deadline: {{ $summary['payment_deadline'] ?? 'N/A' }}</small>
                        @else
                            <span class="badge bg-success fs-5">
                                <i class="fas fa-check me-1"></i>
                                ON TIME
                            </span>
                        @endif
                    </p>
                </div>
            </div>

            <hr>

            <!-- Late Fine -->
            @if($summary['late_fine'] > 0)
            <div class="alert alert-warning">
                <i class="fas fa-bell me-2"></i>
                <strong>Late Fine Applied:</strong> {{ number_format($summary['late_fine'] ?? 0, 0) }} FCFA
                <br>
                <strong>Final Amount Due:</strong> {{ number_format($summary['final_amount_due'] ?? 0, 0) }} FCFA
            </div>
            @endif

            <!-- Progress Bar -->
            @php
                $progressPercent = $summary['total_due'] > 0 ? round(($summary['total_paid'] / $summary['total_due']) * 100, 1) : 0;
            @endphp
            <div class="mb-2 d-flex justify-content-between">
                <span>Payment Progress</span>
                <strong>{{ $progressPercent }}%</strong>
            </div>
            <div class="progress" style="height: 25px;">
                <div class="progress-bar bg-success" style="width: {{ $progressPercent }}%">
                    <span class="ms-2">{{ number_format($summary['total_paid'] ?? 0, 0) }} / {{ number_format($summary['total_due'] ?? 0, 0) }} FCFA</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Payment Records -->
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">
                <i class="fas fa-history me-2"></i>Payment History
            </h5>
        </div>
        <div class="card-body">
            <div id="paymentHistoryContainer">
                <div class="text-center py-4">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.border-left-primary {
    border-left: 4px solid #007bff;
}

.border-left-success {
    border-left: 4px solid #28a745;
}

.border-left-danger {
    border-left: 4px solid #dc3545;
}

.border-left-info {
    border-left: 4px solid #17a2b8;
}
</style>

@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    loadPaymentHistory();
});

function loadPaymentHistory() {
    const studentId = {{ $student->id }};
    fetch(`{{ route('admin.finance.api-payment-history', ['student' => '']) }}${studentId}`)
        .then(response => response.json())
        .then(data => {
            const container = document.getElementById('paymentHistoryContainer');
            container.innerHTML = '';

            if (data.success && data.data && data.data.length > 0) {
                const html = `
                    <div class="table-responsive">
                        <table class="table table-hover table-striped">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Amount</th>
                                    <th>Method</th>
                                    <th>Status</th>
                                    <th>Transaction ID</th>
                                </tr>
                            </thead>
                            <tbody>
                                ${data.data.map(payment => `
                                    <tr>
                                        <td>${new Date(payment.paid_at).toLocaleDateString('fr-CM')}</td>
                                        <td><strong class="text-success">${formatMoney(payment.amount)}</strong></td>
                                        <td>${payment.payment_method || 'N/A'}</td>
                                        <td>
                                            <span class="badge bg-${payment.status === 'completed' ? 'success' : 'warning'}">
                                                ${payment.status === 'completed' ? 'Completed' : 'Pending'}
                                            </span>
                                        </td>
                                        <td><small>${payment.transaction_id || '-'}</small></td>
                                    </tr>
                                `).join('')}
                            </tbody>
                        </table>
                    </div>
                `;
                container.innerHTML = html;
            } else {
                container.innerHTML = '<div class="alert alert-info">No payment records yet</div>';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            document.getElementById('paymentHistoryContainer').innerHTML = 
                '<div class="alert alert-danger">Error loading payment history</div>';
        });
}

function formatMoney(value) {
    return parseFloat(value).toLocaleString('fr-CM', { style: 'currency', currency: 'XAF' });
}
</script>
@endsection


