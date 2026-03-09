@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-md-8">
            <h1 class="h3">
                <a href="{{ route('admin.assignments.index') }}" class="btn btn-link text-muted">
                    <i class="fas fa-arrow-left"></i>
                </a>
                New Teacher Assignment
            </h1>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8 offset-md-2">
            <div class="card">
                <div class="card-body p-4">
                    <form id="assignmentForm" method="POST" action="{{ route('admin.assignments.store') }}">
                        @csrf

                        <div class="mb-4">
                            <label for="class_id" class="form-label">
                                <strong>Select Class</strong>
                            </label>
                            <select name="class_id" id="class_id" class="form-select form-select-lg" required>
                                <option value="">-- Choose a Class --</option>
                                @forelse($classes as $class)
                                    <option value="{{ $class->id }}" 
                                        @if($class->profPrincipal) data-current="{{ $class->profPrincipal->name }}" @endif>
                                        {{ $class->name }} 
                                        @if($class->profPrincipal)
                                            (Current: {{ $class->profPrincipal->name }})
                                        @else
                                            <span class="badge bg-warning">Unassigned</span>
                                        @endif
                                    </option>
                                @empty
                                    <option disabled>No active classes available</option>
                                @endforelse
                            </select>
                            @error('class_id')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label for="new_teacher_id" class="form-label">
                                <strong>Select Teacher</strong>
                            </label>
                            <select name="new_teacher_id" id="new_teacher_id" class="form-select form-select-lg" required>
                                <option value="">-- Choose a Teacher --</option>
                                @forelse($teachers as $teacher)
                                    <option value="{{ $teacher->id }}">
                                        {{ $teacher->name }}
                                        @if($teacher->is_main_teacher)
                                            <span class="badge bg-info">Main Teacher</span>
                                        @endif
                                    </option>
                                @empty
                                    <option disabled>No available teachers</option>
                                @endforelse
                            </select>
                            @error('new_teacher_id')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label for="reason" class="form-label">
                                <strong>Reason for Assignment</strong>
                            </label>
                            <select name="reason" id="reason" class="form-select" required>
                                <option value="">-- Select Reason --</option>
                                <option value="initial_assignment">Initial Assignment</option>
                                <option value="replacement">Replacement</option>
                                <option value="promotion">Promotion</option>
                                <option value="transfer">Transfer from Another Class</option>
                                <option value="temporary">Temporary Assignment</option>
                                <option value="other">Other</option>
                            </select>
                            @error('reason')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label for="notes" class="form-label">
                                <strong>Additional Notes (Optional)</strong>
                            </label>
                            <textarea name="notes" id="notes" class="form-control" rows="4" 
                                placeholder="Add any additional information..."></textarea>
                            @error('notes')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-lg btn-primary">
                                <i class="fas fa-check me-2"></i>Assign Teacher
                            </button>
                            <a href="{{ route('admin.assignments.index') }}" class="btn btn-lg btn-secondary">
                                <i class="fas fa-times me-2"></i>Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Information Panel -->
            <div class="card mt-4 bg-light">
                <div class="card-body">
                    <h5 class="card-title">
                        <i class="fas fa-info-circle me-2"></i>Important Information
                    </h5>
                    <ul class="mb-0">
                        <li>Assigning a teacher to a class will automatically update the class's main teacher (professeur principal)</li>
                        <li>Any previous assignment will be archived and moved to the history</li>
                        <li>All assignments are logged for audit purposes</li>
                        <li>You can view the complete assignment history anytime</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.form-select-lg {
    padding: 0.75rem 1rem;
    font-size: 1rem;
}
</style>
@endsection


