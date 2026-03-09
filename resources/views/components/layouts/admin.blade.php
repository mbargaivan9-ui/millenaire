<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Dashboard - Le Millénaire' }}</title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="{{ asset('assets/css/custom.css') }}">
    
    <style>
        /* Lucide Icons Default Styling */
        [data-lucide] {
            display: inline-block;
            width: 1.25rem;
            height: 1.25rem;
            vertical-align: middle;
        }
        
        :root {
            --primary: #4F46E5;
            --primary-light: #818CF8;
            --primary-dark: #3730A3;
        }
        
        * {
            --bs-primary: #4F46E5;
        }
        
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f5f7fa;
        }
        
        h1, h2, h3, h4, h5, h6 {
            font-family: 'Poppins', sans-serif;
            font-weight: 600;
        }
        
        .main-content {
            display: flex;
            min-height: 100vh;
        }
        
        .sidebar {
            width: 280px;
            position: sticky;
            top: 0;
            height: 100vh;
            overflow-y: auto;
        }
        
        .content-wrapper {
            flex: 1;
            display: flex;
            flex-direction: column;
        }
        
        .content-area {
            flex: 1;
            padding: 30px;
            overflow-y: auto;
        }
        
        @media (max-width: 768px) {
            .sidebar {
                width: 200px;
            }
            
            .content-area {
                padding: 20px;
            }
        }
    </style>
    
    @stack('styles')
</head>
<body>
    <div id="app" class="main-content">
        {{-- Sidebar --}}
        <x-partials.sidebar />
        
        {{-- Main Content --}}
        <div class="content-wrapper w-100">
            {{-- Header --}}
            <x-partials.header />
            
            {{-- Content --}}
            <div class="content-area">
                {{ $slot }}
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Setup AJAX token
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
    </script>
    
    {{-- Load Lucide Icons --}}
    <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.js"></script>
    <script>
        // Initialize Lucide icons when DOM is ready
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', function() {
                if (typeof lucide !== 'undefined') {
                    lucide.createIcons();
                }
            });
        } else {
            if (typeof lucide !== 'undefined') {
                lucide.createIcons();
            }
        }
        
        // Re-initialize Lucide icons whenever AJAX loads new content
        $(document).ajaxComplete(function() {
            if (typeof lucide !== 'undefined') {
                lucide.createIcons();
            }
        });
    </script>
    @stack('scripts')
</body>
</html>
