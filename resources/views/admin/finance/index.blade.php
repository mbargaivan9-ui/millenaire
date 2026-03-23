@extends('layouts.app')

@section('content')
<style>
  :root {
    --primary: #2563eb;
    --success: #10b981;
    --warning: #f59e0b;
    --danger: #ef4444;
    --info: #06b6d4;
  }

  .finance-header {
    background: linear-gradient(135deg, var(--primary) 0%, var(--info) 100%);
    color: white;
    padding: 40px 0;
    margin: -20px -15px 0;
    border-radius: 0 0 20px 20px;
  }

  .finance-header h1 {
    font-weight: 700;
    font-size: 2rem;
    letter-spacing: -0.5px;
  }

  .metric-card {
    position: relative;
    overflow: hidden;
    border: none;
    border-radius: 16px;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    height: 100%;
  }

  .metric-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: linear-gradient(135deg, rgba(255, 255, 255, 0.1) 0%, transparent 100%);
    pointer-events: none;
  }

  .metric-card:hover {
    transform: translateY(-8px);
    box-shadow: 0 12px 32px rgba(0, 0, 0, 0.15);
  }

  .metric-card.due {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
  }

  .metric-card.paid {
    background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
    color: white;
  }

  .metric-card.pending {
    background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
    color: white;
  }

  .metric-card.collection {
    background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);
    color: white;
  }

  .metric-icon {
    position: absolute;
    top: 20px;
    right: 20px;
    font-size: 2.5rem;
    opacity: 0.15;
  }

  .metric-label {
    font-size: 12px;
    font-weight: 600;
    letter-spacing: 1px;
    text-transform: uppercase;
    opacity: 0.9;
    margin-bottom: 12px;
  }

  .metric-value {
    font-size: 2rem;
    font-weight: 700;
    line-height: 1.2;
    margin-bottom: 8px;
    letter-spacing: -1px;
  }

  .metric-subtitle {
    font-size: 13px;
    opacity: 0.85;
    font-weight: 500;
  }

  .metric-footer {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 12px;
    margin-top: 12px;
    padding-top: 12px;
    border-top: 1px solid rgba(255, 255, 255, 0.2);
  }

  .metric-footer svg {
    width: 14px;
    height: 14px;
  }

  .alert-custom {
    border: none;
    border-radius: 12px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
    border-left: 4px solid var(--danger);
    background: #fef2f2;
    color: #7f1d1d;
  }

  .tab-navigation {
    border: none;
    border-bottom: 2px solid #e5e7eb;
    gap: 10px;
  }

  .tab-navigation .nav-link {
    border: none;
    border-radius: 8px 8px 0 0;
    color: #6b7280;
    font-weight: 600;
    font-size: 14px;
    padding: 12px 16px;
    transition: all 0.3s ease;
    position: relative;
  }

  .tab-navigation .nav-link:hover {
    color: var(--primary);
    background: #f3f4f6;
  }

  .tab-navigation .nav-link.active {
    color: var(--primary);
    background: #f3f4f6;
  }

  .tab-navigation .nav-link.active::after {
    content: '';
    position: absolute;
    bottom: -2px;
    left: 0;
    right: 0;
    height: 2px;
    background: var(--primary);
  }

  .data-card {
    background: white;
    border: none;
    border-radius: 16px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
    overflow: hidden;
  }

  .data-table {
    border: none;
  }

  .data-table thead {
    background: #f9fafb;
    border-bottom: 2px solid #e5e7eb;
  }

  .data-table thead th {
    font-weight: 600;
    text-transform: uppercase;
    font-size: 12px;
    letter-spacing: 0.5px;
    color: #374151;
    border: none;
    padding: 16px;
  }

  .data-table tbody tr {
    border-bottom: 1px solid #e5e7eb;
    transition: all 0.2s ease;
  }

  .data-table tbody tr:hover {
    background: #f9fafb;
  }

  .data-table tbody td {
    padding: 16px;
    color: #374151;
    border: none;
  }

  .badge-custom {
    border-radius: 8px;
    font-weight: 600;
    font-size: 12px;
    padding: 6px 12px;
  }

  .progress-custom {
    background: #e5e7eb;
    border-radius: 8px;
    overflow: hidden;
    height: 24px;
  }

  .progress-custom .progress-bar {
    border-radius: 8px;
    font-size: 12px;
    font-weight: 600;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
  }

  .btn-action {
    padding: 6px 12px;
    border-radius: 8px;
    font-size: 12px;
    font-weight: 600;
    transition: all 0.2s ease;
    border: none;
  }

  .btn-action:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
  }
</style>

<div class="container-fluid">
  {{-- Header Section --}}
  <div class="finance-header mb-5">
    <div class="container-fluid px-4">
      <div class="row align-items-center">
        <div class="col-md-6">
          <h1 class="mb-2">
            <i data-lucide="wallet" style="width: 32px; height: 32px; display: inline-block; margin-right: 12px; vertical-align: middle;"></i>
            Financial Dashboard
          </h1>
          <p class="mb-0 opacity-90">Real-time overview of school finances and payment status</p>
        </div>
        <div class="col-md-6 text-end">
          <a href="{{ route('admin.finance.fee-settings') }}" class="btn btn-outline-light me-2" style="border-radius: 8px; font-weight: 600;">
            <i data-lucide="settings" style="width: 16px; height: 16px; display: inline-block; margin-right: 8px; vertical-align: middle;"></i>Manage Fees
          </a>
          <div class="btn-group">
            <button type="button" class="btn btn-light dropdown-toggle" data-bs-toggle="dropdown" style="border-radius: 8px; font-weight: 600;">
              <i data-lucide="download" style="width: 16px; height: 16px; display: inline-block; margin-right: 8px; vertical-align: middle;"></i>Export
            </button>
            <ul class="dropdown-menu">
              <li><a class="dropdown-item" href="{{ route('admin.finance.export-school') }}">School Report (Excel)</a></li>
            </ul>
          </div>
        </div>
      </div>
    </div>
  </div>

  {{-- Key Metrics Section --}}
  <div class="container-fluid px-4 mb-5">
    <div class="row g-4">
      {{-- Total Due Card --}}
      <div class="col-12 col-sm-6 col-lg-3">
        <div class="card kpi-card shadow-sm h-100">
          <div class="card-body">
            <i data-lucide="trending-up" class="metric-icon"></i>
            <div style="position: relative; z-index: 1;">
              <div class="metric-label">Total Due</div>
              <div class="metric-value">{{ number_format($statistics['total_due'] ?? 0, 0) }}</div>
              <div class="metric-subtitle">FCFA • All classes</div>
              <div class="metric-footer">
                <span>📊 Outstanding fees</span>
              </div>
            </div>
          </div>
        </div>
      </div>

      {{-- Total Paid Card --}}
      <div class="col-12 col-sm-6 col-lg-3">
        <div class="card kpi-card shadow-sm h-100">
          <div class="card-body">
            <i data-lucide="check-circle" class="metric-icon"></i>
            <div style="position: relative; z-index: 1;">
              <div class="metric-label">Total Paid</div>
              <div class="metric-value">{{ number_format($statistics['total_paid'] ?? 0, 0) }}</div>
              <div class="metric-subtitle">FCFA • Received</div>
              <div class="metric-footer">
                <span>✓ Successfully collected</span>
              </div>
            </div>
          </div>
        </div>
      </div>

      {{-- Total Pending Card --}}
      <div class="col-12 col-sm-6 col-lg-3">
        <div class="card kpi-card shadow-sm h-100">
          <div class="card-body">
            <i data-lucide="clock" class="metric-icon"></i>
            <div style="position: relative; z-index: 1;">
              <div class="metric-label">Total Pending</div>
              <div class="metric-value">{{ number_format($statistics['total_pending'] ?? 0, 0) }}</div>
              <div class="metric-subtitle">FCFA • Outstanding</div>
              <div class="metric-footer">
                <span>⏳ In progress</span>
              </div>
            </div>
          </div>
        </div>
      </div>

      {{-- Collection Rate Card --}}
      <div class="col-12 col-sm-6 col-lg-3">
        <div class="card kpi-card shadow-sm h-100">
          <div class="card-body">
            <i data-lucide="bar-chart-3" class="metric-icon"></i>
            <div style="position: relative; z-index: 1;">
              <div class="metric-label">Collection Rate</div>
              <div class="metric-value">
                @php
                  $rate = $statistics['total_due'] > 0 ? round(($statistics['total_paid'] / $statistics['total_due']) * 100, 1) : 0;
                @endphp
                {{ $rate }}%
              </div>
              <div class="metric-subtitle">Payment Success</div>
              <div class="metric-footer">
                <span>📈 Achievement</span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  {{-- Alert Section --}}
  @if($overdueCount > 0)
  <div class="container-fluid px-4 mb-4">
    <div class="alert alert-custom alert-dismissible fade show" role="alert">
      <i data-lucide="alert-triangle" style="width: 18px; height: 18px; display: inline-block; margin-right: 12px; vertical-align: middle;"></i>
      <strong>Action Required:</strong> {{ $overdueCount }} student(s) have overdue payments
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
  </div>
  @endif

  {{-- Tabs Section --}}
  <div class="container-fluid px-4">
    <ul class="nav tab-navigation mb-4" role="tablist">
      <li class="nav-item" role="presentation">
        <button class="nav-link active" id="classes-tab" data-bs-toggle="tab" data-bs-target="#classes-panel" type="button" role="tab">
          <i data-lucide="layers" style="width: 16px; height: 16px; display: inline-block; margin-right: 8px; vertical-align: middle;"></i>By Classes
        </button>
      </li>
      <li class="nav-item" role="presentation">
        <button class="nav-link" id="unpaid-tab" data-bs-toggle="tab" data-bs-target="#unpaid-panel" type="button" role="tab">
          <i data-lucide="user-x" style="width: 16px; height: 16px; display: inline-block; margin-right: 8px; vertical-align: middle;"></i>Unpaid Students
        </button>
      </li>
      <li class="nav-item" role="presentation">
        <button class="nav-link" id="overdue-tab" data-bs-toggle="tab" data-bs-target="#overdue-panel" type="button" role="tab">
          <i data-lucide="alert-circle" style="width: 16px; height: 16px; display: inline-block; margin-right: 8px; vertical-align: middle;"></i>Overdue Accounts
        </button>
      </li>
    </ul>

    <div class="tab-content mb-5">
      {{-- Classes Tab --}}
      <div class="tab-pane fade show active" id="classes-panel" role="tabpanel">
        <div class="card kpi-card shadow-sm h-100 border-0">
          <div class="card-body p-0">
            <div class="table-responsive">
              <table class="table data-table mb-0">
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
                    <strong style="color: #1f2937;">{{ $class['class_name'] }}</strong>
                  </td>
                  <td>
                    <span class="badge-custom" style="background: #dbeafe; color: #1e40af;">{{ $class['total_students'] }}</span>
                  </td>
                  <td style="font-weight: 600; color: #374151;">{{ number_format($class['total_class_due'] ?? 0, 0) }} FCFA</td>
                  <td>
                    <span style="color: #059669; font-weight: 600;">{{ number_format($class['total_class_paid'] ?? 0, 0) }} FCFA</span>
                  </td>
                  <td>
                    <span style="color: #dc2626; font-weight: 600;">{{ number_format($class['total_pending'] ?? 0, 0) }} FCFA</span>
                  </td>
                  <td>
                    @php
                      $collection = $class['total_class_due'] > 0 ? round(($class['total_class_paid'] / $class['total_class_due']) * 100, 1) : 0;
                    @endphp
                    <div class="progress-custom">
                      <div class="progress-bar" style="width: {{ $collection }}%; background: linear-gradient(90deg, #43e97b 0%, #38f9d7 100%);">
                        {{ $collection }}%
                      </div>
                    </div>
                  </td>
                  <td>
                    <a href="{{ route('admin.finance.class', ['class' => $class['class_id']]) }}" class="btn btn-action" style="background: #dbeafe; color: #1e40af;">
                      <i data-lucide="eye" style="width: 14px; height: 14px; display: inline-block; margin-right: 4px; vertical-align: middle;"></i> Details
                    </a>
                  </td>
                </tr>
                @empty
                <tr>
                  <td colspan="7" class="text-center py-4" style="color: #9ca3af;">No class data available</td>
                </tr>
                @endforelse
              </tbody>
            </table>
          </div>
        </div>
      </div>

      {{-- Unpaid Students Tab --}}
      <div class="tab-pane fade" id="unpaid-panel" role="tabpanel">
        <div class="card kpi-card shadow-sm h-100 border-0">
          <div class="card-body p-0">
            <div style="padding: 16px; border-bottom: 1px solid #e5e7eb;">
              <p style="margin: 0; color: #6b7280; font-size: 14px;">
                <i data-lucide="info" style="width: 16px; height: 16px; display: inline-block; margin-right: 8px; vertical-align: middle;"></i>
                Showing all students with outstanding fees
              </p>
            </div>
            <div class="table-responsive">
              <table class="table data-table mb-0">
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
                  <td colspan="6" class="text-center py-4" style="color: #9ca3af;">Loading...</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      {{-- Overdue Accounts Tab --}}
      <div class="tab-pane fade" id="overdue-panel" role="tabpanel">
        <div class="card kpi-card shadow-sm h-100 border-0">
          <div class="card-body p-0">
            <div style="padding: 16px; border-bottom: 1px solid #e5e7eb;">
              <p style="margin: 0; color: #6b7280; font-size: 14px;">
                <i data-lucide="info" style="width: 16px; height: 16px; display: inline-block; margin-right: 8px; vertical-align: middle;"></i>
                Showing students with payments past the deadline
              </p>
            </div>
            <div class="table-responsive">
              <table class="table data-table mb-0">
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
                  <td colspan="5" class="text-center py-4" style="color: #9ca3af;">Loading...</td>
                </tr>
              </tbody>
            </table>
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


