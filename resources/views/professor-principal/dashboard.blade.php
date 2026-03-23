@extends('layouts.app')

@section('content')
<div class="container-fluid px-4">
    <!-- Page Header -->
    <div class="mb-4">
        <h1 class="h3 mb-0">{{ __('Professor Principal Dashboard') }}</h1>
        <p class="text-muted small mt-1">{{ __('Welcome') }}, {{ auth()->user()->name }}</p>
    </div>

    <!-- Statistics Cards -->
    <div class="row g-3 mb-4">
        {{-- Total Classrooms --}}
        <div class="col-6 col-lg-3">
            <div class="card kpi-card shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <p class="text-muted small mb-1">{{ __('Total Classrooms') }}</p>
                            <h2 class="fw-bold mb-0">{{ $totalClassrooms }}</h2>
                        </div>
                        <div class="kpi-icon bg-primary bg-opacity-10 text-primary">👥</div>
                    </div>
                    <div class="mt-2 small text-muted">{{ __('Active') }}</div>
                </div>
            </div>
        </div>

        {{-- Active Templates --}}
        <div class="col-6 col-lg-3">
            <div class="card kpi-card shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <p class="text-muted small mb-1">{{ __('Active Templates') }}</p>
                            <h2 class="fw-bold mb-0">{{ $activeTemplates }}</h2>
                        </div>
                        <div class="kpi-icon bg-success bg-opacity-10 text-success">✓</div>
                    </div>
                    <div class="mt-2 small text-muted">{{ __('Templates') }}</div>
                </div>
            </div>
        </div>

        {{-- Pending Templates --}}
        <div class="col-6 col-lg-3">
            <div class="card kpi-card shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <p class="text-muted small mb-1">{{ __('Pending Templates') }}</p>
                            <h2 class="fw-bold mb-0">{{ $pendingTemplates }}</h2>
                        </div>
                        <div class="kpi-icon bg-warning bg-opacity-10 text-warning">⏳</div>
                    </div>
                    <div class="mt-2 small text-muted">{{ __('In progress') }}</div>
                </div>
            </div>
        </div>

        {{-- Total Bulletins --}}
        <div class="col-6 col-lg-3">
            <div class="card kpi-card shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <p class="text-muted small mb-1">{{ __('Bulletins') }}</p>
                            <h2 class="fw-bold mb-0">{{ $totalBulletins }}</h2>
                        </div>
                        <div class="kpi-icon bg-info bg-opacity-10 text-info">📊</div>
                    </div>
                    <div class="mt-2 small text-muted">{{ __('Published') }}</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Completion Progress -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-light">
            <h6 class="mb-0 fw-600">{{ __('Overall Completion Status') }}</h6>
        </div>
        <div class="card-body">
            <div class="d-flex align-items-center">
                <div style="flex: 0 0 80px;">
                    <div class="text-center">
                        <div class="display-6 fw-700 text-primary">{{ $completionPercentage }}%</div>
                        <small class="text-muted">{{ $completedBulletins }}/{{ $totalBulletins }}</small>
                    </div>
                </div>
                <div style="flex: 1; margin-left: 2rem;">
                    <div class="progress" style="height: 20px;">
                        <div class="progress-bar bg-success" role="progressbar" 
                             style="width: {{ $completionPercentage }}%;" 
                             aria-valuenow="{{ $completionPercentage }}" 
                             aria-valuemin="0" 
                             aria-valuemax="100">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Templates -->
    <div class="row mb-4">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-light">
                    <div class="d-flex justify-content-between align-items-center">
                        <h6 class="mb-0 fw-600">{{ __('Recent Templates') }}</h6>
                        <a href="{{ route('prof-principal.templates.index') }}" class="btn btn-sm btn-outline-primary">
                            {{ __('View All') }}
                        </a>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-sm mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>{{ __('Name') }}</th>
                                <th>{{ __('Status') }}</th>
                                <th>{{ __('Created') }}</th>
                                <th>{{ __('Action') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentTemplates as $template)
                                <tr>
                                    <td class="fw-600">{{ $template->name ?? 'Template ' . $template->id }}</td>
                                    <td>
                                        <span class="badge bg-{{ $template->is_validated ? 'success' : 'warning' }}">
                                            {{ $template->is_validated ? __('Active') : __('Pending') }}
                                        </span>
                                    </td>
                                    <td class="text-muted small">{{ $template->created_at->format('d/m/Y H:i') }}</td>
                                    <td>
                                        <a href="{{ route('prof-principal.templates.show', $template) }}" class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center py-4 text-muted">
                                        {{ __('No templates yet') }}
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-light">
                    <h6 class="mb-0 fw-600">{{ __('Quick Actions') }}</h6>
                </div>
                <div class="list-group list-group-flush">
                    <a href="{{ route('prof-principal.templates.upload.form') }}" class="list-group-item list-group-item-action px-4 py-3">
                        <i class="fas fa-cloud-upload-alt me-2 text-primary"></i>
                        <div class="fw-600">{{ __('Upload Template') }}</div>
                        <small class="text-muted">{{ __('Upload bulletin image') }}</small>
                    </a>
                    <a href="{{ route('prof-principal.templates.index') }}" class="list-group-item list-group-item-action px-4 py-3">
                        <i class="fas fa-file-alt me-2 text-success"></i>
                        <div class="fw-600">{{ __('Manage Templates') }}</div>
                        <small class="text-muted">{{ __('Edit and validate templates') }}</small>
                    </a>
                    <a href="{{ route('prof-principal.grades.entry', 0) }}" class="list-group-item list-group-item-action px-4 py-3">
                        <i class="fas fa-edit me-2 text-warning"></i>
                        <div class="fw-600">{{ __('Enter Grades') }}</div>
                        <small class="text-muted">{{ __('Record student grades') }}</small>
                    </a>
                    <a href="{{ route('prof-principal.bulletins.index') }}" class="list-group-item list-group-item-action px-4 py-3">
                        <i class="fas fa-list me-2 text-info"></i>
                        <div class="fw-600">{{ __('View Bulletins') }}</div>
                        <small class="text-muted">{{ __('See all bulletins') }}</small>
                    </a>
                    <a href="{{ route('prof-principal.export.form', 0) }}" class="list-group-item list-group-item-action px-4 py-3">
                        <i class="fas fa-download me-2 text-danger"></i>
                        <div class="fw-600">{{ __('Export to PDF') }}</div>
                        <small class="text-muted">{{ __('Generate reports') }}</small>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Classrooms List -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-light">
            <h6 class="mb-0 fw-600">{{ __('My Classrooms') }}</h6>
        </div>
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>{{ __('Classroom') }}</th>
                        <th>{{ __('Students') }}</th>
                        <th>{{ __('Templates') }}</th>
                        <th>{{ __('Action') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($classrooms as $classroom)
                        <tr>
                            <td class="fw-600">{{ $classroom->name }}</td>
                            <td>
                                <span class="badge bg-light text-dark">{{ $classroom->students?->count() ?? 0 }} {{ __('students') }}</span>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark">{{ $classroom->templates?->count() ?? 0 }} {{ __('templates') }}</span>
                            </td>
                            <td>
                                <a href="{{ route('prof-principal.progress', $classroom) }}" class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-chart-pie me-1"></i>{{ __('Progress') }}
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center py-4 text-muted">
                                {{ __('No classrooms assigned') }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .fw-600 { font-weight: 600; }
    .fw-700 { font-weight: 700; }
    .display-6 { font-size: 2.5rem; }
</style>
@endpush
