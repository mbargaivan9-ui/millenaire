@extends('layouts.admin')

@section('title', $structure->name . ' - Validation Review')

@section('content')
<div class="container-fluid py-4">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-md-8">
            <h1 class="fw-bold">{{ $structure->name }}</h1>
            <p class="text-muted">{{ $structure->description }}</p>
        </div>
        <div class="col-md-4 text-end">
            <h5>
                @if($structure->is_verified)
                    <span class="badge bg-success"><i class="fas fa-check-circle"></i> Verified</span>
                @else
                    <span class="badge bg-warning"><i class="fas fa-hourglass-half"></i> Pending</span>
                @endif
                @if($structure->is_active)
                    <span class="badge bg-info"><i class="fas fa-play-circle"></i> Active</span>
                @endif
            </h5>
        </div>
    </div>

    <!-- Overview Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <div class="text-muted small">Created By</div>
                    <div class="fw-bold">{{ $structure->createdBy->name ?? 'N/A' }}</div>
                    <div class="text-muted small">{{ $structure->created_at->format('d/m/Y H:i') }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <div class="text-muted small">OCR Confidence</div>
                    <div class="fw-bold text-primary">{{ $structure->ocr_confidence }}%</div>
                    @if($structure->ocr_confidence >= 80)
                        <div class="text-success small"><i class="fas fa-check"></i> Excellent Quality</div>
                    @elseif($structure->ocr_confidence >= 60)
                        <div class="text-info small"><i class="fas fa-info-circle"></i> Good Quality</div>
                    @else
                        <div class="text-warning small"><i class="fas fa-exclamation-circle"></i> Fair Quality</div>
                    @endif
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <div class="text-muted small">Class</div>
                    <div class="fw-bold">{{ $structure->classe->name ?? 'N/A' }}</div>
                    <div class="text-muted small">{{ $structure->classe->level ?? 'N/A' }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <div class="text-muted small">Subjects</div>
                    <div class="fw-bold">{{ count($subjects) }} subjects</div>
                    <div class="text-muted small">{{ count($fieldCoordinates) }} zones defined</div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Main Content -->
        <div class="col-md-8">
            <!-- Subjects & Coefficients -->
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-book"></i> Subjects & Coefficients</h5>
                </div>
                <div class="card-body">
                    @if(empty($subjects))
                        <p class="text-muted">No subjects defined</p>
                    @else
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Subject</th>
                                        <th style="width: 120px;">Coefficient</th>
                                        <th style="width: 200px;">Zone Location</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($subjects as $subject)
                                    <tr>
                                        <td>
                                            <strong>{{ $subject }}</strong>
                                        </td>
                                        <td>
                                            @php
                                                $coeff = $coefficients[$subject] ?? null;
                                            @endphp
                                            <span class="badge bg-info">{{ $coeff ?? 'N/A' }}</span>
                                        </td>
                                        <td>
                                            @php
                                                $zone = collect($fieldCoordinates)->firstWhere('name', $subject);
                                            @endphp
                                            @if($zone)
                                                <small class="text-muted">
                                                    X: {{ $zone['x'] }}, Y: {{ $zone['y'] }}
                                                </small>
                                            @else
                                                <small class="text-danger">No zone defined</small>
                                            @endif
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Grading Scale -->
            <div class="card mb-4">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0"><i class="fas fa-star"></i> Grading Scale</h5>
                </div>
                <div class="card-body">
                    @if(empty($structure->structure_json['grading_scale'] ?? null))
                        <p class="text-muted">No grading scale defined</p>
                    @else
                        @php
                            $grade = $structure->structure_json['grading_scale'];
                        @endphp
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label><strong>Minimum Score</strong></label>
                                    <div class="fw-bold text-primary" style="font-size: 1.5rem;">
                                        {{ $grade['min'] ?? 0 }}
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label><strong>Maximum Score</strong></label>
                                    <div class="fw-bold text-primary" style="font-size: 1.5rem;">
                                        {{ $grade['max'] ?? 100 }}
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="alert alert-info mt-3 mb-0">
                            <small>Range: {{ $grade['min'] ?? 0 }} - {{ $grade['max'] ?? 100 }}</small>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Calculation Rules -->
            <div class="card mb-4">
                <div class="card-header bg-warning text-dark">
                    <h5 class="mb-0"><i class="fas fa-calculator"></i> Calculation Rules</h5>
                </div>
                <div class="card-body">
                    @if(empty($calculationRules))
                        <p class="text-muted">No calculation rules defined</p>
                    @else
                        <div class="space-y-3">
                            @foreach($calculationRules as $rule => $value)
                            <div class="mb-3">
                                <label class="form-label">
                                    <strong>{{ ucfirst(str_replace('_', ' ', $rule)) }}</strong>
                                </label>
                                <div class="bg-light p-2 rounded">
                                    <code>{{ $value }}</code>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>

            <!-- Appreciation Rules -->
            @if(!empty($appreciationRules))
            <div class="card mb-4">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0"><i class="fas fa-heart"></i> Appreciation Rules</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Appreciation</th>
                                    <th>Range</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($appreciationRules as $level => $range)
                                <tr>
                                    <td>
                                        <strong>{{ ucfirst($level) }}</strong>
                                    </td>
                                    <td>
                                        @if(is_array($range))
                                            {{ $range[0] ?? 'N/A' }} - {{ $range[1] ?? 'N/A' }}
                                        @else
                                            {{ $range }}
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            @endif
        </div>

        <!-- Sidebar -->
        <div class="col-md-4">
            <!-- Actions -->
            <div class="card mb-4">
                <div class="card-header bg-dark text-white">
                    <h5 class="mb-0">Actions</h5>
                </div>
                <div class="card-body d-grid gap-2">
                    @if(!$structure->is_verified)
                        <form method="POST" action="{{ route('admin.bulletin-structure.validation.approve', $structure) }}">
                            @csrf
                            <div class="mb-2">
                                <textarea name="approval_notes" class="form-control form-control-sm" 
                                          placeholder="Approval notes..." rows="3"></textarea>
                            </div>
                            <button type="submit" class="btn btn-success btn-block">
                                <i class="fas fa-check-circle"></i> Approve Structure
                            </button>
                        </form>

                        <form method="POST" action="{{ route('admin.bulletin-structure.validation.reject', $structure) }}" class="mt-2">
                            @csrf
                            <div class="mb-2">
                                <textarea name="rejection_reason" class="form-control form-control-sm" 
                                          placeholder="Rejection reason..." rows="3" required></textarea>
                            </div>
                            <button type="submit" class="btn btn-danger btn-block" 
                                    onclick="return confirm('Are you sure you want to reject this structure?')">
                                <i class="fas fa-times-circle"></i> Reject Structure
                            </button>
                        </form>
                    @else
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle"></i> Structure already verified
                        </div>

                        @if(!$structure->is_active)
                            <form method="POST" action="{{ route('admin.bulletin-structure.validation.activate', $structure) }}">
                                @csrf
                                <button type="submit" class="btn btn-primary btn-block">
                                    <i class="fas fa-play-circle"></i> Activate Structure
                                </button>
                            </form>
                        @else
                            <form method="POST" action="{{ route('admin.bulletin-structure.validation.deactivate', $structure) }}">
                                @csrf
                                <button type="submit" class="btn btn-secondary btn-block">
                                    <i class="fas fa-stop-circle"></i> Deactivate Structure
                                </button>
                            </form>
                        @endif
                    @endif

                    <a href="{{ route('admin.bulletin-structure.validation.edit', $structure) }}" class="btn btn-warning">
                        <i class="fas fa-edit"></i> Edit Structure
                    </a>

                    <a href="{{ route('admin.bulletin-structure.validation.export', $structure) }}" class="btn btn-info">
                        <i class="fas fa-download"></i> Export as JSON
                    </a>

                    <a href="{{ route('admin.bulletin-structure.validation.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Back to List
                    </a>
                </div>
            </div>

            <!-- Validation Notes -->
            @if($structure->validation_notes)
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Validation Notes</h5>
                    </div>
                    <div class="card-body">
                        <p class="text-muted">{{ $structure->validation_notes }}</p>
                        @if($structure->verified_by)
                            <small class="text-muted">
                                <i class="fas fa-user"></i> 
                                Verified by: {{ $structure->verifiedBy->name ?? 'N/A' }}<br>
                                <i class="fas fa-calendar"></i>
                                {{ $structure->verified_at?->format('d/m/Y H:i') }}
                            </small>
                        @endif
                    </div>
                </div>
            @endif

            <!-- Field Coordinates -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Field Coordinates ({{ count($fieldCoordinates) }})</h5>
                </div>
                <div class="card-body" style="max-height: 400px; overflow-y: auto;">
                    @if(empty($fieldCoordinates))
                        <p class="text-muted small">No field coordinates defined</p>
                    @else
                        <div class="space-y-2">
                            @foreach($fieldCoordinates as $field)
                            <div class="p-2 bg-light rounded">
                                <div class="fw-bold small">{{ $field['name'] }}</div>
                                <div class="text-muted small">
                                    X: {{ $field['x'] }} | Y: {{ $field['y'] }}<br>
                                    W: {{ $field['width'] }} | H: {{ $field['height'] }}
                                </div>
                            </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
