/**
 * Dashboard Charts & Analytics
 * Millénaire Connect
 */

// Initialize Charts on DOMContentLoaded
document.addEventListener('DOMContentLoaded', function() {
    initializeCharts();
});

/**
 * Initialize all charts
 */
function initializeCharts() {
    if (typeof Chart === 'undefined') {
        console.warn('Chart.js library not loaded');
        return;
    }

    initEnrollmentChart();
    initFinancialChart();
    initAttendanceChart();
    initGradeDistributionChart();
}

/**
 * Monthly Enrollment Chart
 */
function initEnrollmentChart() {
    const ctx = document.getElementById('enrollmentChart');
    if (!ctx) return;

    const data = window.enrollmentData || {
        months: ['Jan', 'Fév', 'Mar', 'Avr', 'Mai', 'Juin', 'Juil', 'Août', 'Sep', 'Oct', 'Nov', 'Déc'],
        data: []
    };

    new Chart(ctx, {
        type: 'line',
        data: {
            labels: data.months,
            datasets: [{
                label: 'Nouvelles Inscriptions',
                data: data.data,
                borderColor: '#4F46E5',
                backgroundColor: 'rgba(79, 70, 229, 0.1)',
                borderWidth: 3,
                fill: true,
                tension: 0.4,
                pointRadius: 5,
                pointBackgroundColor: '#4F46E5',
                pointBorderColor: '#fff',
                pointBorderWidth: 2
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    display: true,
                    position: 'top'
                }
            },
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
}

/**
 * Financial Trends Chart
 */
function initFinancialChart() {
    const ctx = document.getElementById('financialChart');
    if (!ctx) return;

    const data = window.financialData || {
        months: [],
        paid: [],
        pending: []
    };

    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: data.months,
            datasets: [
                {
                    label: 'Paiements Reçus',
                    data: data.paid,
                    backgroundColor: '#10B981',
                    borderRadius: 8,
                    borderSkipped: false
                },
                {
                    label: 'Paiements en Attente',
                    data: data.pending,
                    backgroundColor: '#F59E0B',
                    borderRadius: 8,
                    borderSkipped: false
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            indexAxis: 'x',
            plugins: {
                legend: {
                    position: 'bottom'
                }
            },
            scales: {
                y: {
                    stacked: false
                }
            }
        }
    });
}

/**
 * Attendance Chart
 */
function initAttendanceChart() {
    const ctx = document.getElementById('attendanceChart');
    if (!ctx) return;

    new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: ['Présents', 'Absents', 'Justifiés', 'Malades'],
            datasets: [{
                data: window.attendanceData || [70, 15, 10, 5],
                backgroundColor: [
                    '#10B981',
                    '#EF4444',
                    '#F59E0B',
                    '#3B82F6'
                ],
                borderColor: '#fff',
                borderWidth: 2
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });
}

/**
 * Grade Distribution Chart
 */
function initGradeDistributionChart() {
    const ctx = document.getElementById('gradeChart');
    if (!ctx) return;

    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: ['0-5', '5-10', '10-15', '15-20'],
            datasets: [{
                label: 'Distribution des Notes',
                data: window.gradeData || [10, 25, 40, 25],
                backgroundColor: [
                    '#EF4444',
                    '#F59E0B',
                    '#10B981',
                    '#4F46E5'
                ],
                borderRadius: 8
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            indexAxis: 'y',
            plugins: {
                legend: {
                    display: false
                }
            }
        }
    });
}

/**
 * Update Chart Data (Real-time)
 */
function updateChartData(chartId, newData) {
    if (typeof Chart === 'undefined') return;

    const canvas = document.getElementById(chartId);
    if (!canvas || !canvas.chart) return;

    const chart = canvas.chart;
    chart.data.datasets[0].data = newData;
    chart.update();
}

/**
 * Export Chart as Image
 */
function exportChartAsImage(chartId, filename = 'chart.png') {
    const canvas = document.getElementById(chartId);
    if (!canvas) return;

    const link = document.createElement('a');
    link.href = canvas.toDataURL('image/png');
    link.download = filename;
    link.click();
}
