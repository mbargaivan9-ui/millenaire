@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-md-8">
            <h1 class="h3">
                <a href="{{ route('admin.finance.index') }}" class="btn btn-link text-muted">
                    <i class="fas fa-arrow-left me-1"></i>
                </a>
                Financial Report: {{ $class->name }}
            </h1>
        </div>
        <div class="col-md-4 text-end">
            <a href="{{ route('admin.finance.export-class', $class) }}" class="btn btn-success">
                <i class="fas fa-download me-2"></i>Export Excel
            </a>
        </div>
    </div>

    <!-- Summary Statistics -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card border-left-info">
                <div class="card-body">
                    <div class="text-info text-uppercase mb-1 small fw-bold">Total Students</div>
                    <div class="h3 mb-0">{{ $summary['total_students'] ?? 0 }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-left-primary">
                <div class="card-body">
                    <div class="text-primary text-uppercase mb-1 small fw-bold">Total Due</div>
                    <div class="h3 mb-0">{{ number_format($summary['total_class_due'] ?? 0, 0) }}</div>
                    <small class="text-muted">FCFA</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-left-success">
                <div class="card-body">
                    <div class="text-success text-uppercase mb-1 small fw-bold">Total Paid</div>
                    <div class="h3 mb-0">{{ number_format($summary['total_class_paid'] ?? 0, 0) }}</div>
                    <small class="text-muted">FCFA</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-left-warning">
                <div class="card-body">
                    <div class="text-warning text-uppercase mb-1 small fw-bold">Pending</div>
                    <div class="h3 mb-0">{{ number_format($summary['total_pending'] ?? 0, 0) }}</div>
                    <small class="text-muted">FCFA</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Collection Progress -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0">Collection Progress</h5>
        </div>
        <div class="card-body">
            @php
                $rate = $summary['total_class_due'] > 0 ? round(($summary['total_class_paid'] / $summary['total_class_due']) * 100, 1) : 0;
            @endphp
            <div class="mb-2 d-flex justify-content-between">
                <span>Collection Rate:</span>
                <strong>{{ $rate }}%</strong>
            </div>
            <div class="progress" style="height: 25px;">
                <div class="progress-bar bg-success" style="width: {{ $rate }}%">
                    <span class="ms-2">{{ $rate }}%</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Student Details Tab -->
    <ul class="nav nav-tabs mb-3" role="tablist">
        <li class="nav-item">
            <button class="nav-link active" id="all-tab" data-bs-toggle="tab" data-bs-target="#all-panel" type="button" role="tab">
                All Students ({{ count($summary['students'] ?? []) }})
            </button>
        </li>
        <li class="nav-item">
            <button class="nav-link" id="unpaid-tab" data-bs-toggle="tab" data-bs-target="#unpaid-panel" type="button" role="tab">
                Unpaid
            </button>
        </li>
        <li class="nav-item">
            <button class="nav-link" id="partial-tab" data-bs-toggle="tab" data-bs-target="#partial-panel" type="button" role="tab">
                Partial
            </button>
        </li>
        <li class="nav-item">
            <button class="nav-link" id="paid-tab" data-bs-toggle="tab" data-bs-target="#paid-panel" type="button" role="tab">
                Fully Paid
            </button>
        </li>
    </ul>

    <div class="tab-content">
        <!-- All Students -->
        <div class="tab-pane fade show active" id="all-panel" role="tabpanel">
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover table-striped">
                            <thead>
                                <tr>
                                    <th>Student Name</th>
                                    <th>Total Due</th>
                                    <th>Amount Paid</th>
                                    <th>Balance</th>
                                    <th>Status</th>
                                    <th>Overdue</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($summary['students'] ?? [] as $student)
                                <tr>
                                    <td><strong>{{ $student['student_name'] }}</strong></td>
                                    <td>{{ number_format($student['total_due'] ?? 0, 0) }}</td>
                                    <td><span class="text-success fw-bold">{{ number_format($student['total_paid'] ?? 0, 0) }}</span></td>
                                    <td><span class="text-danger">{{ number_format($student['pending_amount'] ?? 0, 0) }}</span></td>
                                    <td>
                                        @if($student['payment_status'] === 'unpaid')
                                            <span class="badge bg-danger">Unpaid</span>
                                        @elseif($student['payment_status'] === 'partial')
                                            <span class="badge bg-warning">Partial</span>
                                        @else
                                            <span class="badge bg-success">Paid</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($student['is_overdue'])
                                            <span class="badge bg-dark">{{ $student['days_until_deadline'] ?? 'N/A' }} days</span>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        <a href="{{ route('admin.finance.student', ['student' => $student['student_id']]) }}" class="btn btn-sm btn-primary">
                                            Detail
                                        </a>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="7" class="text-center py-4 text-muted">No student data available</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Unpaid Tab -->
        <div class="tab-pane fade" id="unpaid-panel" role="tabpanel">
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover table-striped">
                            <thead>
                                <tr>
                                    <th>Student Name</th>
                                    <th>Amount Due</th>
                                    <th>Days Late</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($summary['students'] ?? [] as $student)
                                    @if($student['payment_status'] === 'unpaid')
                                    <tr>
                                        <td><strong>{{ $student['student_name'] }}</strong></td>
                                        <td><span class="text-danger fw-bold">{{ number_format($student['pending_amount'] ?? 0, 0) }}</span></td>
                                        <td>{{ $student['days_until_deadline'] ?? '-' }}</td>
                                        <td>
                                            <a href="{{ route('admin.finance.student', ['student' => $student['student_id']]) }}" class="btn btn-sm btn-primary">
                                                Detail
                                            </a>
                                        </td>
                                    </tr>
                                    @endif
                                @empty
                                <tr>
                                    <td colspan="4" class="text-center py-4 text-success">All students have paid!</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Partial Tab -->
        <div class="tab-pane fade" id="partial-panel" role="tabpanel">
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover table-striped">
                            <thead>
                                <tr>
                                    <th>Student Name</th>
                                    <th>Total Due</th>
                                    <th>Amount Paid</th>
                                    <th>Remaining</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($summary['students'] ?? [] as $student)
                                    @if($student['payment_status'] === 'partial')
                                    <tr>
                                        <td><strong>{{ $student['student_name'] }}</strong></td>
                                        <td>{{ number_format($student['total_due'] ?? 0, 0) }}</td>
                                        <td><span class="text-success">{{ number_format($student['total_paid'] ?? 0, 0) }}</span></td>
                                        <td><span class="text-warning fw-bold">{{ number_format($student['pending_amount'] ?? 0, 0) }}</span></td>
                                        <td>
                                            <a href="{{ route('admin.finance.student', ['student' => $student['student_id']]) }}" class="btn btn-sm btn-primary">
                                                Detail
                                            </a>
                                        </td>
                                    </tr>
                                    @endif
                                @empty
                                <tr>
                                    <td colspan="5" class="text-center py-4 text-muted">No partial payments</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Paid Tab -->
        <div class="tab-pane fade" id="paid-panel" role="tabpanel">
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover table-striped">
                            <thead>
                                <tr>
                                    <th>Student Name</th>
                                    <th>Total Due</th>
                                    <th>Amount Paid</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($summary['students'] ?? [] as $student)
                                    @if($student['payment_status'] === 'paid')
                                    <tr>
                                        <td><strong>{{ $student['student_name'] }}</strong></td>
                                        <td>{{ number_format($student['total_due'] ?? 0, 0) }}</td>
                                        <td><span class="text-success fw-bold">{{ number_format($student['total_paid'] ?? 0, 0) }}</span></td>
                                        <td><span class="badge bg-success">Fully Paid</span></td>
                                    </tr>
                                    @endif
                                @empty
                                <tr>
                                    <td colspan="4" class="text-center py-4 text-muted">No fully paid students</td>
                                </tr>
                                @endforelse
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


