@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-md-8">
            <h1 class="h3">
                <a href="{{ route('admin.assignments.index') }}" class="btn btn-link text-muted">
                    <i class="fas fa-arrow-left"></i>
                </a>
                Assignment Details
            </h1>
        </div>
        <div class="col-md-4 text-end">
            <a href="{{ route('admin.assignments.edit', $assignment) }}" class="btn btn-warning">
                <i class="fas fa-edit me-2"></i>Edit
            </a>
            <form action="{{ route('admin.assignments.destroy', $assignment) }}" method="POST" class="d-inline">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-danger" onclick="return confirm('Are you sure?')">
                    <i class="fas fa-trash me-2"></i>Delete
                </button>
            </form>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8 offset-md-2">
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Assignment Information</h5>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label text-muted">Class</label>
                            <div class="h5 mb-0">{{ $assignment->classe->name }}</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted">Level</label>
                            <div class="h5 mb-0">{{ $assignment->classe->level }}</div>
                        </div>
                    </div>

                    <hr>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label text-muted">Current Teacher (Professeur Principal)</label>
                            <div class="h5 mb-0">
                                <span class="badge bg-success">{{ $assignment->newTeacher?->name }}</span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted">Previous Teacher</label>
                            <div class="h5 mb-0">
                                {{ $assignment->oldTeacher?->name ?? 'None (Initial Assignment)' }}
                            </div>
                        </div>
                    </div>

                    <hr>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label text-muted">Assignment Date</label>
                            <div class="h5 mb-0">{{ $assignment->assigned_at->format('d/m/Y H:i:s') }}</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted">Assigned By</label>
                            <div class="h5 mb-0">{{ $assignment->assignedBy?->name ?? 'System' }}</div>
                        </div>
                    </div>

                    <hr>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label text-muted">Reason</label>
                            <div class="h5 mb-0">{{ ucfirst(str_replace('_', ' ', $assignment->reason)) }}</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted">Status</label>
                            <div class="mb-0">
                                @if($assignment->status === 'active')
                                    <span class="badge bg-success">Active</span>
                                @else
                                    <span class="badge bg-secondary">{{ ucfirst($assignment->status) }}</span>
                                @endif
                            </div>
                        </div>
                    </div>

                    <hr>

                    @if($assignment->notes)
                    <div class="row">
                        <div class="col-md-12">
                            <label class="form-label text-muted">Notes</label>
                            <div class="alert alert-info">
                                {{ $assignment->notes }}
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Class Overview -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Class Overview</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <small class="text-muted">Total Students</small>
                            <div class="h4 mb-0">{{ $assignment->classe->students->count() ?? 0 }}</div>
                        </div>
                        <div class="col-md-4">
                            <small class="text-muted">Section</small>
                            <div class="h4 mb-0">{{ $assignment->classe->section ?? '-' }}</div>
                        </div>
                        <div class="col-md-4">
                            <small class="text-muted">Academic Year</small>
                            <div class="h4 mb-0">{{ config('app.academic_year', now()->year) }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection


