/* ═══════════════════════════════════════════════════════════════════════════════
   CLOUDFLARE CDN TABLES INITIALIZATION - Professional Table Handler
   Using Cloudflare's table management library for high-performance data tables
   ═══════════════════════════════════════════════════════════════════════════════ */

(function() {
    'use strict';

    /**
     * Premium Table Manager
     * Initialize and manage high-end professional tables using Cloudflare CDN
     */
    const PremiumTableManager = {
        /**
         * Initialize all premium tables on the page
         */
        init: function() {
            console.log('🎯 Initializing Premium Tables');
            
            // Wait for DOM to be fully loaded
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', () => this.initTables());
            } else {
                this.initTables();
            }
        },

        /**
         * Find and initialize all tables
         */
        initTables: function() {
            const tables = document.querySelectorAll('table.premium-table, table[data-premium], table.data-table, table.table');
            
            console.log(`Found ${tables.length} tables to initialize`);
            
            tables.forEach((table, index) => {
                // Skip already initialized tables
                if (table.hasAttribute('data-table-initialized')) {
                    return;
                }

                this.setupTable(table, index);
                table.setAttribute('data-table-initialized', 'true');
            });
        },

        /**
         * Setup individual table with premium styling and features
         */
        setupTable: function(table, index) {
            const tableId = table.id || `premium-table-${index}`;
            table.id = tableId;

            // Wrap table in premium container
            const wrapper = document.createElement('div');
            wrapper.className = 'premium-table-wrapper';
            table.parentNode.insertBefore(wrapper, table);
            wrapper.appendChild(table);

            // Add classes for premium styling
            table.classList.add('premium-table');

            // Initialize with Cloudflare DataTables library
            if (window.DataTable) {
                this.initDataTable(table, tableId);
            } else {
                console.warn('DataTable library not loaded. Loading from Cloudflare CDN...');
                this.loadCloudflareDataTable(() => {
                    this.initDataTable(table, tableId);
                });
            }
        },

        /**
         * Initialize DataTable with professional configuration
         */
        initDataTable: function(table, tableId) {
            try {
                // Skip if already initialized
                if ($.fn.dataTable.isDataTable(table)) {
                    return;
                }

                const config = {
                    // Pagination
                    paging: true,
                    pageLength: 10,
                    lengthMenu: [[5, 10, 25, 50, 100], [5, 10, 25, 50, 100]],
                    
                    // Search and filtering
                    searching: true,
                    serverSide: false,
                    
                    // Ordering
                    ordering: true,
                    order: [],
                    
                    // Info display
                    info: true,
                    
                    // Responsive
                    responsive: {
                        details: {
                            type: 'column',
                            target: 0
                        }
                    },
                    
                    // Scrolling
                    scrollX: true,
                    fixedHeader: true,
                    
                    // Styling
                    columnDefs: [
                        {
                            targets: 0,
                            orderable: false,
                            className: 'dt-control'
                        }
                    ],
                    
                    // Language and i18n
                    language: this.getLanguageConfig(),
                    
                    // Callbacks
                    drawCallback: () => this.onTableDraw(table),
                    initComplete: () => this.onTableInit(table),
                };

                // Initialize DataTable
                const dataTable = $(table).DataTable(config);

                // Store reference
                table.dataTable = dataTable;

                // Add event listeners for premium interactions
                this.setupTableEvents(table, dataTable);

                console.log(`✅ Table "${tableId}" initialized successfully`);
            } catch (error) {
                console.error(`Error initializing table ${tableId}:`, error);
            }
        },

        /**
         * Setup premium table event listeners
         */
        setupTableEvents: function(table, dataTable) {
            // Row click interactions
            $(table).on('click', 'tbody tr', function() {
                $(this).toggleClass('selected');
            });

            // Hover effects
            $(table).on('mouseenter', 'tbody tr', function() {
                $(this).addClass('row-hover');
            }).on('mouseleave', 'tbody tr', function() {
                $(this).removeClass('row-hover');
            });

            // Action buttons
            $(table).on('click', '.table-action-btn', (e) => {
                e.stopPropagation();
                const button = e.target;
                const action = button.dataset.action;
                const id = button.closest('tr').dataset.id || button.dataset.id;

                this.handleTableAction(action, id, button);
            });

            // Export functionality
            this.setupExportButtons(table, dataTable);
        },

        /**
         * Handle table action buttons (edit, delete, view)
         */
        handleTableAction: function(action, id, button) {
            const actionConfigs = {
                'edit': { icon: 'pencil', color: 'primary' },
                'delete': { icon: 'trash', color: 'danger' },
                'view': { icon: 'eye', color: 'info' }
            };

            const config = actionConfigs[action];
            
            if (!config) return;

            console.log(`Executing action: ${action} on ID: ${id}`);

            // Add ripple effect
            this.createRippleEffect(button);

            // Emit custom event for application to handle
            const event = new CustomEvent('tableActionClick', {
                detail: { action, id, button }
            });
            document.dispatchEvent(event);
        },

        /**
         * Create ripple effect on button click
         */
        createRippleEffect: function(button) {
            const ripple = document.createElement('span');
            const rect = button.getBoundingClientRect();
            const size = Math.max(rect.width, rect.height);
            const x = event.clientX - rect.left - size / 2;
            const y = event.clientY - rect.top - size / 2;

            ripple.style.width = ripple.style.height = size + 'px';
            ripple.style.left = x + 'px';
            ripple.style.top = y + 'px';
            ripple.classList.add('ripple');

            button.appendChild(ripple);

            setTimeout(() => ripple.remove(), 600);
        },

        /**
         * Setup export functionality
         */
        setupExportButtons: function(table, dataTable) {
            const wrapper = table.closest('.premium-table-wrapper');
            if (!wrapper) return;

            // Create export container if not exists
            let exportContainer = wrapper.querySelector('.table-export-controls');
            if (!exportContainer) {
                exportContainer = document.createElement('div');
                exportContainer.className = 'table-export-controls';
                exportContainer.style.marginBottom = '20px';
                wrapper.parentNode.insertBefore(exportContainer, wrapper);
            }

            // Export to CSV
            const csvBtn = document.createElement('button');
            csvBtn.className = 'btn-export btn-export-csv';
            csvBtn.innerHTML = '<i class="fas fa-file-csv"></i> Export CSV';
            csvBtn.onclick = () => this.exportToCSV(table, dataTable);

            // Export to Excel
            const excelBtn = document.createElement('button');
            excelBtn.className = 'btn-export btn-export-excel';
            excelBtn.innerHTML = '<i class="fas fa-file-excel"></i> Export Excel';
            excelBtn.onclick = () => this.exportToExcel(table, dataTable);

            // Export to PDF
            const pdfBtn = document.createElement('button');
            pdfBtn.className = 'btn-export btn-export-pdf';
            pdfBtn.innerHTML = '<i class="fas fa-file-pdf"></i> Export PDF';
            pdfBtn.onclick = () => this.exportToPDF(table, dataTable);

            // Print
            const printBtn = document.createElement('button');
            printBtn.className = 'btn-export btn-export-print';
            printBtn.innerHTML = '<i class="fas fa-print"></i> Print';
            printBtn.onclick = () => this.printTable(table);

            exportContainer.appendChild(csvBtn);
            exportContainer.appendChild(excelBtn);
            exportContainer.appendChild(pdfBtn);
            exportContainer.appendChild(printBtn);
        },

        /**
         * Export table data to CSV
         */
        exportToCSV: function(table, dataTable) {
            const data = dataTable.rows({ search: 'applied' }).data().toArray();
            const headers = [];

            $(table).find('thead th').each(function() {
                headers.push($(this).text());
            });

            let csv = headers.join(',') + '\n';
            data.forEach(row => {
                const rowData = Array.isArray(row) ? row : Object.values(row);
                csv += rowData.map(cell => `"${cell}"`).join(',') + '\n';
            });

            this.downloadFile(csv, 'table-export.csv', 'text/csv');
        },

        /**
         * Export table data to Excel
         */
        exportToExcel: function(table, dataTable) {
            const data = dataTable.rows({ search: 'applied' }).data().toArray();
            const headers = [];

            $(table).find('thead th').each(function() {
                headers.push($(this).text());
            });

            // Create HTML table
            let html = '<table><thead><tr>';
            headers.forEach(h => html += `<th>${h}</th>`);
            html += '</tr></thead><tbody>';

            data.forEach(row => {
                html += '<tr>';
                const rowData = Array.isArray(row) ? row : Object.values(row);
                rowData.forEach(cell => html += `<td>${cell}</td>`);
                html += '</tr>';
            });
            html += '</tbody></table>';

            this.downloadFile(html, 'table-export.xls', 'application/vnd.ms-excel');
        },

        /**
         * Export table data to PDF
         */
        exportToPDF: function(table, dataTable) {
            if (!window.jsPDF || !window.html2canvas) {
                alert('PDF libraries are not loaded. Please try again.');
                return;
            }

            const element = table.closest('.premium-table-wrapper');
            html2canvas(element).then(canvas => {
                const imgData = canvas.toDataURL('image/png');
                const pdf = new jsPDF.jsPDF();
                const imgWidth = 210;
                const pageHeight = 295;
                const imgHeight = (canvas.height * imgWidth) / canvas.width;

                pdf.addImage(imgData, 'PNG', 0, 0, imgWidth, imgHeight);
                pdf.save('table-export.pdf');
            });
        },

        /**
         * Print table
         */
        printTable: function(table) {
            const printWindow = window.open('', '', 'width=800,height=600');
            printWindow.document.write('<html><head><title>Print Table</title>');
            printWindow.document.write(`<link rel="stylesheet" href="${window.location.origin}/css/tables-premium.css">`);
            printWindow.document.write('</head><body>');
            printWindow.document.write(table.outerHTML);
            printWindow.document.write('</body></html>');
            printWindow.document.close();
            printWindow.print();
        },

        /**
         * Download file helper
         */
        downloadFile: function(content, filename, mimeType) {
            const blob = new Blob([content], { type: mimeType });
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = filename;
            document.body.appendChild(a);
            a.click();
            window.URL.revokeObjectURL(url);
            document.body.removeChild(a);
        },

        /**
         * Load Cloudflare DataTables library
         */
        loadCloudflareDataTable: function(callback) {
            // CSS
            const link = document.createElement('link');
            link.rel = 'stylesheet';
            link.href = 'https://cdn.jsdelivr.net/npm/datatables.net-dt@2.1.0/css/dataTables.dataTables.min.css';
            document.head.appendChild(link);

            // JavaScript
            const script = document.createElement('script');
            script.src = 'https://cdn.jsdelivr.net/npm/datatables.net@2.1.0/js/dataTables.min.js';
            script.onload = () => {
                console.log('✅ DataTables library loaded from Cloudflare CDN');
                if (callback) callback();
            };
            script.onerror = () => {
                console.error('❌ Failed to load DataTables from Cloudflare CDN');
            };
            document.head.appendChild(script);
        },

        /**
         * Get language configuration
         */
        getLanguageConfig: function() {
            const lang = document.documentElement.lang || 'en';
            
            const langConfigs = {
                'fr': {
                    lengthMenu: 'Afficher _MENU_ entrées',
                    search: 'Rechercher:',
                    zeroRecords: 'Aucune donnée trouvée',
                    info: 'Affichage de _START_ à _END_ sur _TOTAL_ entrées',
                    infoEmpty: 'Aucune entrée disponible',
                    paginate: {
                        first: 'Première',
                        last: 'Dernière',
                        next: 'Suivant',
                        previous: 'Précédent'
                    }
                },
                'en': {
                    lengthMenu: 'Show _MENU_ entries',
                    search: 'Search:',
                    zeroRecords: 'No data found',
                    info: 'Showing _START_ to _END_ of _TOTAL_ entries',
                    infoEmpty: 'No entries available',
                    paginate: {
                        first: 'First',
                        last: 'Last',
                        next: 'Next',
                        previous: 'Previous'
                    }
                }
            };

            return langConfigs[lang] || langConfigs['en'];
        },

        /**
         * Callback when table is drawn
         */
        onTableDraw: function(table) {
            // Add animations to rows
            $(table).find('tbody tr').each(function(index) {
                $(this).css('animation', `slideIn 0.3s ease-out ${index * 0.05}s`);
            });
        },

        /**
         * Callback when table is initialized
         */
        onTableInit: function(table) {
            console.log('Table initialization complete:', table.id);
            
            // Dispatch custom event
            const event = new CustomEvent('premiumTableInitialized', {
                detail: { table }
            });
            document.dispatchEvent(event);
        },

        /**
         * Refresh table data
         */
        refresh: function(tableId) {
            const table = document.getElementById(tableId);
            if (table && table.dataTable) {
                table.dataTable.ajax.reload();
            }
        },

        /**
         * Destroy table
         */
        destroy: function(tableId) {
            const table = document.getElementById(tableId);
            if (table && table.dataTable) {
                table.dataTable.destroy();
            }
        }
    };

    // Initialize when DOM is ready
    document.addEventListener('DOMContentLoaded', () => {
        PremiumTableManager.init();
    });

    // Expose to global scope
    window.PremiumTableManager = PremiumTableManager;

})();
