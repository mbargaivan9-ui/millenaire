@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-md-6">
            <h1 class="h3">
                <i class="fas fa-vault me-2"></i>Treasury Report
            </h1>
        </div>
        <div class="col-md-6 text-end">
            <div class="btn-group" role="group">
                <button type="button" class="btn btn-outline-secondary" id="filterByDateRange">
                    <i class="fas fa-calendar me-1"></i>Date Range
                </button>
                <button type="button" class="btn btn-outline-secondary" onclick="exportToExcel()">
                    <i class="fas fa-download me-1"></i>Export Excel
                </button>
                <button type="button" class="btn btn-outline-secondary" onclick="printReport()">
                    <i class="fas fa-print me-1"></i>Print
                </button>
            </div>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card border-left-primary">
                <div class="card-body">
                    <div class="text-primary font-weight-bold text-uppercase mb-1">Total Revenue</div>
                    <div class="h3 mb-0" id="total-revenue">Loading...</div>
                    <small class="text-muted">For selected period</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-left-success">
                <div class="card-body">
                    <div class="text-success font-weight-bold text-uppercase mb-1">Collection Rate</div>
                    <div class="h3 mb-0" id="collection-rate">-</div>
                    <small class="text-muted">% of total due</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-left-warning">
                <div class="card-body">
                    <div class="text-warning font-weight-bold text-uppercase mb-1">Transactions</div>
                    <div class="h3 mb-0" id="transaction-count">-</div>
                    <small class="text-muted">Number of payments</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-left-info">
                <div class="card-body">
                    <div class="text-info font-weight-bold text-uppercase mb-1">Avg Daily Revenue</div>
                    <div class="h3 mb-0" id="avg-daily-revenue">-</div>
                    <small class="text-muted">Per transaction day</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Date Range Filter -->
    <div class="card mb-4">
        <div class="card-body">
            <form id="filterForm" class="row g-3">
                <div class="col-md-3">
                    <label for="startDate" class="form-label">Start Date</label>
                    <input type="date" id="startDate" class="form-control" value="{{ date('Y-m-01') }}">
                </div>
                <div class="col-md-3">
                    <label for="endDate" class="form-label">End Date</label>
                    <input type="date" id="endDate" class="form-control" value="{{ date('Y-m-d') }}">
                </div>
                <div class="col-md-3">
                    <label for="paymentMethod" class="form-label">Payment Method</label>
                    <select id="paymentMethod" class="form-select">
                        <option value="">All Methods</option>
                        <option value="cash">Cash</option>
                        <option value="check">Check</option>
                        <option value="transfer">Bank Transfer</option>
                        <option value="mobile">Mobile Payment</option>
                    </select>
                </div>
                <div class="col-md-3 d-flex align-items-end gap-2">
                    <button type="button" class="btn btn-primary" onclick="loadTreasuryReport()">
                        <i class="fas fa-filter me-1"></i>Apply Filter
                    </button>
                    <button type="button" class="btn btn-secondary" onclick="resetFilters()">
                        <i class="fas fa-redo me-1"></i>Reset
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Revenue by Payment Method Chart -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Revenue by Payment Method</h5>
                </div>
                <div class="card-body">
                    <canvas id="methodChart" height="250"></canvas>
                </div>
            </div>
        </div>

        <!-- Daily Revenue Trend -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Daily Revenue Trend</h5>
                </div>
                <div class="card-body">
                    <canvas id="trendChart" height="250"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Detailed Transactions Table -->
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Payment Transactions</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover table-striped" id="transactionsTable">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Student</th>
                            <th>Class</th>
                            <th>Amount</th>
                            <th>Method</th>
                            <th>Status</th>
                            <th>Reference</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">
                                <div class="spinner-border spinner-border-sm" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div> Loading transactions...
                            </td>
                        </tr>
                    </tbody>
                </table>
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

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-adapter-date-fns@2.0.0/dist/chartjs-adapter-date-fns.bundle.min.js"></script>

<script>
let methodChart, trendChart;

document.addEventListener('DOMContentLoaded', function() {
    loadTreasuryReport();
});

function loadTreasuryReport() {
    const startDate = document.getElementById('startDate').value;
    const endDate = document.getElementById('endDate').value;
    const paymentMethod = document.getElementById('paymentMethod').value;

    const url = new URL('{{ route('admin.finance.api-treasury-report') }}');
    url.searchParams.append('start_date', startDate);
    url.searchParams.append('end_date', endDate);
    if (paymentMethod) url.searchParams.append('payment_method', paymentMethod);

    fetch(url)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                updateSummaryCards(data.data);
                updateCharts(data.data);
                loadTransactions(startDate, endDate, paymentMethod);
            }
        })
        .catch(error => console.error('Error:', error));
}

function updateSummaryCards(data) {
    document.getElementById('total-revenue').textContent = 
        formatCurrency(data.total_revenue);
    document.getElementById('collection-rate').textContent = 
        data.collection_rate + '%';
    document.getElementById('transaction-count').textContent = 
        data.transaction_count;
    document.getElementById('avg-daily-revenue').textContent = 
        formatCurrency(data.average_daily_revenue);
}

function updateCharts(data) {
    // Payment methods chart
    const methodCtx = document.getElementById('methodChart').getContext('2d');
    if (methodChart) methodChart.destroy();
    
    methodChart = new Chart(methodCtx, {
        type: 'doughnut',
        data: {
            labels: Object.keys(data.payment_methods),
            datasets: [{
                data: Object.values(data.payment_methods),
                backgroundColor: [
                    '#007bff', '#28a745', '#ffc107', '#dc3545', '#17a2b8'
                ],
                borderWidth: 2,
                borderColor: '#fff'
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { position: 'bottom' }
            }
        }
    });

    // Daily trend chart
    const trendCtx = document.getElementById('trendChart').getContext('2d');
    if (trendChart) trendChart.destroy();
    
    trendChart = new Chart(trendCtx, {
        type: 'line',
        data: {
            labels: Object.keys(data.revenue_by_day).sort(),
            datasets: [{
                label: 'Daily Revenue',
                data: Object.values(data.revenue_by_day),
                borderColor: '#007bff',
                backgroundColor: 'rgba(0, 123, 255, 0.1)',
                tension: 0.4,
                fill: true,
                pointRadius: 5,
                pointBackgroundColor: '#007bff',
                pointBorderColor: '#fff',
                pointBorderWidth: 2
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return formatCurrency(value);
                        }
                    }
                }
            },
            plugins: {
                legend: { display: true }
            }
        }
    });
}

function loadTransactions(startDate, endDate, paymentMethod) {
    const url = new URL('{{ route('admin.finance.api-transactions') }}');
    url.searchParams.append('start_date', startDate);
    url.searchParams.append('end_date', endDate);
    if (paymentMethod) url.searchParams.append('payment_method', paymentMethod);

    fetch(url)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const tbody = document.querySelector('#transactionsTable tbody');
                tbody.innerHTML = '';

                if (data.data.length === 0) {
                    tbody.innerHTML = '<tr><td colspan="7" class="text-center text-muted py-4">No transactions found</td></tr>';
                    return;
                }

                data.data.forEach(transaction => {
                    const row = document.createElement('tr');
                    row.innerHTML = `
                        <td><small>${new Date(transaction.created_at).toLocaleDateString()}</small></td>
                        <td><strong>${transaction.student_name}</strong></td>
                        <td>${transaction.class_name}</td>
                        <td>${formatCurrency(transaction.amount)}</td>
                        <td><span class="badge bg-info">${transaction.payment_method}</span></td>
                        <td><span class="badge bg-success">${transaction.status}</span></td>
                        <td><code>${transaction.transaction_id}</code></td>
                    `;
                    tbody.appendChild(row);
                });
            }
        })
        .catch(error => console.error('Error:', error));
}

function formatCurrency(value) {
    return new Intl.NumberFormat('fr-CM', {
        style: 'currency',
        currency: 'XAF'
    }).format(value);
}

function resetFilters() {
    document.getElementById('startDate').value = new Date(new Date().getFullYear(), new Date().getMonth(), 1).toISOString().split('T')[0];
    document.getElementById('endDate').value = new Date().toISOString().split('T')[0];
    document.getElementById('paymentMethod').value = '';
    loadTreasuryReport();
}

function exportToExcel() {
    const startDate = document.getElementById('startDate').value;
    const endDate = document.getElementById('endDate').value;
    window.location.href = `{{ route('admin.finance.export-treasury') }}?start_date=${startDate}&end_date=${endDate}`;
}

function printReport() {
    window.print();
}
</script>
@endsection


