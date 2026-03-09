@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <div class="row mb-4">
        <div class="col-md-8">
            <h1>Rapports et Tableaux de Bord</h1>
        </div>
    </div>

    <div class="row">
        <div class="col-md-3 mb-3">
            <div class="card text-center">
                <div class="card-body">
                    <i class="fas fa-chart-line" style="font-size: 2rem; color: #007bff;"></i>
                    <h5 class="card-title mt-2">Activités</h5>
                    <a href="{{ route('admin.reports.activity-logs') }}" class="btn btn-primary btn-sm">Voir</a>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-3">
            <div class="card text-center">
                <div class="card-body">
                    <i class="fas fa-lock" style="font-size: 2rem; color: #dc3545;"></i>
                    <h5 class="card-title mt-2">Audit</h5>
                    <a href="{{ route('admin.reports.activity-logs') }}" class="btn btn-danger btn-sm">Voir</a>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-3">
            <div class="card text-center">
                <div class="card-body">
                    <i class="fas fa-money-bill" style="font-size: 2rem; color: #28a745;"></i>
                    <h5 class="card-title mt-2">Finances</h5>
                    <a href="{{ route('admin.reports.financial') }}" class="btn btn-success btn-sm">Voir</a>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-3">
            <div class="card text-center">
                <div class="card-body">
                    <i class="fas fa-graduation-cap" style="font-size: 2rem; color: #ffc107;"></i>
                    <h5 class="card-title mt-2">Performance</h5>
                    <a href="{{ route('admin.reports.student-performance') }}" class="btn btn-warning btn-sm">Voir</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
