@extends('layouts.app')

@section('extra_css')
    <link rel="stylesheet" href="{{ asset('css/dashboard-modern.css') }}">
@endsection

@section('content')
    <div class="container-fluid py-4">
        @yield('dashboard_content')
    </div>
@endsection
