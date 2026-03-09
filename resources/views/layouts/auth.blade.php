<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" data-theme="{{ Cookie::get('theme', 'light') }}">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>@yield('title') — {{ config('app.name', 'Millenaire') }}</title>

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="{{ asset('css/app.css') }}">
  <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.js"></script>

  @stack('styles')
</head>
<body>

@yield('content')

<script src="{{ asset('js/app.js') }}"></script>
<script>lucide.createIcons();</script>
@stack('scripts')

</body>
</html>
