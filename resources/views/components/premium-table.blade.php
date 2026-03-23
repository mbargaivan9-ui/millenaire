{{-- Premium Table Component/Include Template --}}
{{-- Usage: @include('components.premium-table', ['tables' => $tables]) --}}

<div class="premium-tables-wrapper">
    {{-- Configuration Script --}}
    <script>
        // Auto-initialize all premium tables
        document.addEventListener('DOMContentLoaded', function() {
            // Ensure all tables get the premium treatment
            document.querySelectorAll('table').forEach(function(table) {
                // Add premium class if not already present
                if (!table.classList.contains('premium-table')) {
                    table.classList.add('premium-table');
                }
                
                // Wrap in premium container if not already wrapped
                if (!table.closest('.premium-table-wrapper')) {
                    const wrapper = document.createElement('div');
                    wrapper.className = 'premium-table-wrapper';
                    table.parentNode.insertBefore(wrapper, table);
                    wrapper.appendChild(table);
                }
            });

            // Initialize PremiumTableManager
            if (window.PremiumTableManager) {
                window.PremiumTableManager.initTables();
            }
        });
    </script>
</div>
