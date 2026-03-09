@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-md-6">
            <h1 class="h3 d-inline-block">
                <i class="fas fa-money-bill-wave me-2"></i>Financial Dashboard
            </h1>
        </div>
        <div class="col-md-6 text-end">
            <a href="{{ route('admin.finance.fee-settings') }}" class="btn btn-secondary me-2">
                <i class="fas fa-cog me-2"></i>Manage Fees
            </a>
            <div class="btn-group">
                <button type="button" class="btn btn-primary dropdown-toggle" data-bs-toggle="dropdown">
                    <i class="fas fa-download me-2"></i>Export
                </button>
                <ul class="dropdown-menu">
                    <li><a class="dropdown-item" href="{{ route('admin.finance.export-school') }}">School Report (Excel)</a></li>
                </ul>
            </div>
        </div>
    </div>

    <!-- Key Metrics -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card border-left-primary">
                <div class="card-body">
                    <div class="text-primary text-uppercase mb-1 small fw-bold">Total Due</div>
                    <div class="h3 mb-0">{{ number_format($statistics['total_due'] ?? 0, 2) }} FCFA</div>
                    <small class="text-muted">All classes</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-left-success">
                <div class="card-body">
                    <div class="text-success text-uppercase mb-1 small fw-bold">Total Paid</div>
                    <div class="h3 mb-0">{{ number_format($statistics['total_paid'] ?? 0, 2) }} FCFA</div>
                    <small class="text-muted">Received</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-left-warning">
                <div class="card-body">
                    <div class="text-warning text-uppercase mb-1 small fw-bold">Total Pending</div>
                    <div class="h3 mb-0">{{ number_format($statistics['total_pending'] ?? 0, 2) }} FCFA</div>
                    <small class="text-muted">Outstanding</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-left-info">
                <div class="card-body">
                    <div class="text-info text-uppercase mb-1 small fw-bold">Collection Rate</div>
                    <div class="h3 mb-0">
                        @php
                            $rate = $statistics['total_due'] > 0 ? round(($statistics['total_paid'] / $statistics['total_due']) * 100, 1) : 0;
                        @endphp
                        {{ $rate }}%
                    </div>
                    <small class="text-muted">Payment success</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Alerts -->
    @if($overdueCount > 0)
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="fas fa-exclamation-triangle me-2"></i>
        <strong>{{ $overdueCount }} student(s)</strong> have overdue payments
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    <!-- Tabs -->
    <ul class="nav nav-tabs mb-3" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="classes-tab" data-bs-toggle="tab" data-bs-target="#classes-panel" type="button" role="tab">
                <i class="fas fa-layer-group me-2"></i>By Classes
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="unpaid-tab" data-bs-toggle="tab" data-bs-target="#unpaid-panel" type="button" role="tab">
                <i class="fas fa-user-clock me-2"></i>Unpaid Students
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="overdue-tab" data-bs-toggle="tab" data-bs-target="#overdue-panel" type="button" role="tab">
                <i class="fas fa-bell me-2"></i>Overdue Accounts
            </button>
        </li>
    </ul>

    <div class="tab-content">
        <!-- Classes Tab -->
        <div class="tab-pane fade show active" id="classes-panel" role="tabpanel">
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover table-striped">
                            <thead>
                                <tr>
                                    <th>Class</th>
                                    <th>Students</th>
                                    <th>Total Due</th>
                                    <th>Total Paid</th>
                                    <th>Pending</th>
                                    <th>Collection %</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($classes as $class)
                                <tr>
                                    <td>
                                        <strong>{{ $class['class_name'] }}</strong>
                                    </td>
                                    <td>
                                        <span class="badge bg-info">{{ $class['total_students'] }}</span>
                                    </td>
                                    <td>{{ number_format($class['total_class_due'] ?? 0, 0) }}</td>
                                    <td>
                                        <span class="text-success fw-bold">{{ number_format($class['total_class_paid'] ?? 0, 0) }}</span>
                                    </td>
                                    <td>
                                        <span class="text-danger">{{ number_format($class['total_pending'] ?? 0, 0) }}</span>
                                    </td>
                                    <td>
                                        @php
                                            $collection = $class['total_class_due'] > 0 ? round(($class['total_class_paid'] / $class['total_class_due']) * 100, 1) : 0;
                                        @endphp
                                        <div class="progress" style="height: 20px;">
                                            <div class="progress-bar bg-success" style="width: {{ $collection }}%">
                                                {{ $collection }}%
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <a href="{{ route('admin.finance.class', ['class' => $class['class_id']]) }}" class="btn btn-sm btn-primary">
                                            <i class="fas fa-eye"></i> Details
                                        </a>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="7" class="text-center py-4 text-muted">No class data available</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Unpaid Students Tab -->
        <div class="tab-pane fade" id="unpaid-panel" role="tabpanel">
            <div class="card">
                <div class="card-body">
                    <p class="text-muted">
                        <i class="fas fa-info-circle me-2"></i>
                        Showing all students with outstanding fees
                    </p>
                    <div class="table-responsive">
                        <table class="table table-hover table-striped">
                            <thead>
                                <tr>
                                    <th>Student Name</th>
                                    <th>Class</th>
                                    <th>Amount Due</th>
                                    <th>Amount Paid</th>
                                    <th>Balance</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody id="unpaidTableBody">
                                <tr>
                                    <td colspan="6" class="text-center py-4">Loading...</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Overdue Tab -->
        <div class="tab-pane fade" id="overdue-panel" role="tabpanel">
            <div class="card">
                <div class="card-body">
                    <p class="text-muted">
                        <i class="fas fa-info-circle me-2"></i>
                        Showing students with payments past the deadline
                    </p>
                    <div class="table-responsive">
                        <table class="table table-hover table-striped">
                            <thead>
                                <tr>
                                    <th>Student Name</th>
                                    <th>Class</th>
                                    <th>Amount Due</th>
                                    <th>Days Overdue</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody id="overdueTableBody">
                                <tr>
                                    <td colspan="5" class="text-center py-4">Loading...</td>
                                </tr>
                            </tbody>
                        </table>
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

.border-left-warning {
    border-left: 4px solid #ffc107;
}

.border-left-info {
    border-left: 4px solid #17a2b8;
}
</style>

@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    loadUnpaidStudents();
    loadOverduePayments();
});

function loadUnpaidStudents() {
    fetch('{{ route('admin.finance.api-unpaid') }}')
        .then(response => response.json())
        .then(data => {
            const tbody = document.getElementById('unpaidTableBody');
            tbody.innerHTML = '';
            
            if (data.success && data.data.length > 0) {
                data.data.forEach(student => {
                    const row = `
                        <tr>
                            <td>${student.student_name}</td>
                            <td>${student.class_name}</td>
                            <td>${formatMoney(student.total_due)}</td>
                            <td>${formatMoney(student.total_paid)}</td>
                            <td><strong class="text-danger">${formatMoney(student.pending_amount)}</strong></td>
                            <td>
                                <a href="/admin/finance/student/${student.student_id}" class="btn btn-sm btn-primary">
                                    View
                                </a>
                            </td>
                        </tr>
                    `;
                    tbody.innerHTML += row;
                });
            } else {
                tbody.innerHTML = '<tr><td colspan="6" class="text-center py-4 text-success">All students have paid their fees!</td></tr>';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            document.getElementById('unpaidTableBody').innerHTML = '<tr><td colspan="6" class="text-center py-4 text-danger">Error loading data</td></tr>';
        });
}

function loadOverduePayments() {
    fetch('{{ route('admin.finance.api-overdue') }}')
        .then(response => response.json())
        .then(data => {
            const tbody = document.getElementById('overdueTableBody');
            tbody.innerHTML = '';
            
            if (data.success && data.data.length > 0) {
                data.data.forEach(student => {
                    const daysOverdue = student.days_until_deadline ? Math.abs(student.days_until_deadline) : 'N/A';
                    const row = `
                        <tr>
                            <td>${student.student_name}</td>
                            <td>${student.class_name}</td>
                            <td>${formatMoney(student.final_amount_due)}</td>
                            <td><strong class="text-danger">${daysOverdue} days</strong></td>
                            <td>
                                <a href="/admin/finance/student/${student.student_id}" class="btn btn-sm btn-warning">
                                    Follow Up
                                </a>
                            </td>
                        </tr>
                    `;
                    tbody.innerHTML += row;
                });
            } else {
                tbody.innerHTML = '<tr><td colspan="5" class="text-center py-4 text-success">No overdue accounts</td></tr>';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            document.getElementById('overdueTableBody').innerHTML = '<tr><td colspan="5" class="text-center py-4 text-danger">Error loading data</td></tr>';
        });
}

function formatMoney(value) {
    return parseFloat(value).toLocaleString('fr-CM', { style: 'currency', currency: 'XAF' });
}
</script>
@endsection


