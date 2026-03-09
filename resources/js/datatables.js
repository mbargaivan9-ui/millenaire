/**
 * DataTables Configuration & Initialization
 * Millénaire Connect Admin Panel
 */

document.addEventListener('DOMContentLoaded', function() {
    // Initialize all data tables with consistent configuration
    const tables = document.querySelectorAll('table.data-table');
    
    tables.forEach(table => {
        initializeDataTable(table);
    });
});

/**
 * Initialize DataTable with default config
 */
function initializeDataTable(table) {
    if (typeof DataTable === 'undefined') {
        console.warn('DataTable library not loaded');
        return;
    }

    new DataTable(table, {
        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/fr-FR.json'
        },
        responsive: true,
        pageLength: 25,
        lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
        dom: '<"d-flex justify-content-between align-items-center mb-3"lf>rtip',
        buttons: [
            'copy', 'csv', 'excel', 'pdf', 'print'
        ],
        columnDefs: [
            {
                targets: 0,
                orderable: false,
                searchable: false
            }
        ],
        order: [[1, 'asc']],
        initComplete: function() {
            // Add custom styling
            const wrapper = this.api().table().container().parentElement;
            wrapper.classList.add('datatable-container');
        }
    });
}

/**
 * Filter Table by Column
 */
function filterTableByColumn(tableSelector, columnIndex, filterValue) {
    const table = document.querySelector(tableSelector);
    if (!table.dtInstance) return;

    table.dtInstance.column(columnIndex).search(filterValue).draw();
}

/**
 * Export Table to CSV
 */
function exportTableToCSV(filename = 'export.csv') {
    const table = document.querySelector('table');
    let csv = [];
    
    // Get headers
    const headers = [];
    table.querySelectorAll('thead th').forEach(th => {
        headers.push(th.textContent.trim());
    });
    csv.push(headers.join(','));

    // Get rows
    table.querySelectorAll('tbody tr').forEach(tr => {
        const row = [];
        tr.querySelectorAll('td').forEach(td => {
            row.push(`"${td.textContent.trim()}"`);
        });
        csv.push(row.join(','));
    });

    // Download
    const csvContent = 'data:text/csv;charset=utf-8,' + csv.join('\n');
    const link = document.createElement('a');
    link.setAttribute('href', encodeURI(csvContent));
    link.setAttribute('download', filename);
    link.click();
}

/**
 * Export Table to PDF
 */
function exportTableToPDF(filename = 'export.pdf') {
    if (typeof html2pdf === 'undefined') {
        alert('PDF library not loaded');
        return;
    }

    const table = document.querySelector('table');
    html2pdf().set({
        margin: 10,
        filename: filename,
        image: {type: 'jpeg', quality: 0.98},
        html2canvas: {scale: 2},
        jsPDF: {orientation: 'landscape', unit: 'mm', format: 'a4'}
    }).from(table).save();
}

/**
 * Print Table
 */
function printTable() {
    const table = document.querySelector('table');
    const printWindow = window.open('', '', 'width=800,height=600');
    printWindow.document.write('<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">');
    printWindow.document.write(table.outerHTML);
    printWindow.document.close();
    printWindow.print();
}

/**
 * Refresh Table
 */
function refreshTable(url) {
    fetch(url, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.text())
    .then(html => {
        const parser = new DOMParser();
        const newDoc = parser.parseFromString(html, 'text/html');
        const newTable = newDoc.querySelector('table');
        
        if (newTable) {
            document.querySelector('table').replaceWith(newTable);
            // Reinitialize
            initializeDataTable(newTable);
            showSuccessMessage('Table rechargée');
        }
    })
    .catch(error => console.error('Error:', error));
}

/**
 * Show Success Message
 */
function showSuccessMessage(message) {
    const alertDiv = document.createElement('div');
    alertDiv.className = 'alert alert-success alert-dismissible fade show';
    alertDiv.innerHTML = `
        <i class="fas fa-check-circle me-2"></i> ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    const container = document.querySelector('main') || document.body;
    container.insertBefore(alertDiv, container.firstChild);

    setTimeout(() => {
        alertDiv.remove();
    }, 5000);
}
