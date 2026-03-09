@extends('layouts.app')
@section('title', 'Rapport de Présences')
@section('content')
<div class="container-fluid py-4">
    <h1 class="h4 mb-4">Rapport de Présences</h1>
    @include('admin.attendance.report')
</div>
@endsection
