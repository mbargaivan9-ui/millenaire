<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" data-theme="{{ auth()->user()?->theme ?? 'light' }}">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>@yield('title', __('dashboard')) — {{ config('app.name', 'Millenaire') }}</title>

  {{-- PWA --}}
  <link rel="manifest" href="/manifest.json">
  <meta name="theme-color" content="#0d9488">
  <link rel="apple-touch-icon" href="/icons/icon-192.png">

  {{-- Fonts --}}
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">

  {{-- App CSS --}}
  <link rel="stylesheet" href="{{ asset('css/app.css') }}">

  {{-- DataTables CSS --}}
  <link rel="stylesheet" href="//cdn.datatables.net/2.3.7/css/dataTables.dataTables.min.css">

  @stack('styles')
</head>
<body>

{{-- Flash data for JS toast --}}
@if(session('success') || session('error') || session('warning') || session('info'))
<div id="flash-data" hidden
  data-type="{{ session('success') ? 'success' : (session('error') ? 'error' : (session('warning') ? 'warning' : 'info')) }}"
  data-message="{{ session('success') ?? session('error') ?? session('warning') ?? session('info') }}">
</div>
@endif

{{-- Language switch form (hidden) --}}
<form id="lang-form" method="POST" action="#" style="display:none">
  @csrf
  <input type="hidden" name="lang" value="">
</form>

<div class="app-wrapper">

  {{-- ─── SIDEBAR ─────────────────────────────────────── --}}
  @include('layouts.partials.sidebar')

  {{-- ─── MAIN CONTENT ───────────────────────────────── --}}
  <div class="main-content" id="main-content">

    {{-- Topbar --}}
    @include('layouts.partials.topbar')

    {{-- Page Content --}}
    <main class="page-content">
      @yield('content')
    </main>

  </div>{{-- end main-content --}}

</div>{{-- end app-wrapper --}}

{{-- App Scripts --}}
<script src="{{ asset('js/app.js') }}"></script>

{{-- jQuery (required for DataTables) --}}
<script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>

{{-- DataTables JS --}}
<script src="//cdn.datatables.net/2.3.7/js/dataTables.min.js"></script>

<script>
  // Initialize DataTables for all tables with 'data-table' or 'table' class
  $(document).ready(function() {
    $('table.data-table, table.table').each(function() {
      if (!$.fn.dataTable.isDataTable(this)) {
        $(this).DataTable({
          paging: true,
          searching: true,
          ordering: true,
          responsive: true,
          pageLength: 10
        });
      }
    });
  });
</script>

{{-- PWA Service Worker --}}
<script src="{{ asset('js/pwa.js') }}"></script>

{{-- Lucide Icons --}}
<script src="https://unpkg.com/lucide@latest/dist/umd/lucide.js"></script>
<script>
  // Initialize Lucide icons when script loads
  function initLucideIcons() {
    if (typeof lucide !== 'undefined') {
      lucide.createIcons();
      if (typeof Millenaire !== 'undefined' && Millenaire.IconManager) {
        // Icon manager is now available after app.js loads
      }
    }
  }
  
  // Call immediately if Lucide is already loaded
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initLucideIcons);
  } else {
    initLucideIcons();
  }

  // Handle avatar refresh after profile update
  document.addEventListener('DOMContentLoaded', function() {
    const profileUpdated = sessionStorage.getItem('profileUpdated');
    if (profileUpdated) {
      sessionStorage.removeItem('profileUpdated');
      
      // Refresh avatar images with cache-busting
      const topbarAvatar = document.getElementById('topbar-avatar');
      const sidebarAvatar = document.getElementById('sidebar-avatar');
      const timestamp = Date.now();
      
      if (topbarAvatar && topbarAvatar.src) {
        const baseSrc = topbarAvatar.src.split('?')[0];
        topbarAvatar.src = baseSrc + '?v=' + timestamp;
      }
      
      if (sidebarAvatar && sidebarAvatar.src) {
        const baseSrc = sidebarAvatar.src.split('?')[0];
        sidebarAvatar.src = baseSrc + '?v=' + timestamp;
      }
    }
  });
</script>

@stack('scripts')

</body>
</html>
