@extends('layouts.app')

@section('title', 'Bulletin Dashboard - ' . $class->name)

@section('content')
<div class="container-fluid py-4">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-md-8">
            <h1 class="h3 mb-0">
                <i data-lucide="file-text" class="me-2"></i>
                {{ __('Bulletin Dashboard') }}
            </h1>
            <p class="text-muted mt-2">{{ $class->name }} - Term {{ $term }} Sequence {{ $sequence }}</p>
        </div>
        <div class="col-md-4 text-end">
            <a href="{{ route('teacher.dashboard') }}" class="btn btn-outline-secondary">
                <i data-lucide="chevron-left" class="me-1"></i> Back
            </a>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-light border-0">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <p class="text-muted mb-1 small">Total Students</p>
                            <h4 class="mb-0">{{ $classDetails->students->count() }}</h4>
                        </div>
                        <i data-lucide="users" class="text-primary" style="width: 24px; height: 24px;"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-light border-0">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <p class="text-muted mb-1 small">Subjects</p>
                            <h4 class="mb-0">{{ $subjects->count() }}</h4>
                        </div>
                        <i data-lucide="book" class="text-success" style="width: 24px; height: 24px;"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-light border-0">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <p class="text-muted mb-1 small">Grades Entered</p>
                            <h4 class="mb-0">{{ $marks->count() }}</h4>
                        </div>
                        <i data-lucide="check-circle" class="text-warning" style="width: 24px; height: 24px;"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-light border-0">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <p class="text-muted mb-1 small">Total Assignments</p>
                            <h4 class="mb-0">{{ $assignments->count() }}</h4>
                        </div>
                        <i data-lucide="layers" class="text-info" style="width: 24px; height: 24px;"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Subjects & Assignments -->
    <div class="row mb-4">
        <div class="col-lg-8">
            <div class="card border-0">
                <div class="card-header bg-light border-0 pb-3">
                    <h5 class="mb-0">
                        <i data-lucide="book-open" class="me-2"></i> Class Subjects
                    </h5>
                </div>
                <div class="card-body">
                    @if($subjects->isEmpty())
                        <div class="alert alert-info mb-0">
                            <i data-lucide="info" class="me-2"></i>
                            No subjects assigned to this class.
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Subject</th>
                                        <th>Teacher</th>
                                        <th>Grades Entered</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($subjects as $subject)
                                        @php
                                            $assignment = $assignments->where('subject_id', $subject->id)->first();
                                            $subjectMarks = $marks->where('subject_id', $subject->id)->count();
                                        @endphp
                                        <tr>
                                            <td>
                                                <strong>{{ $subject->name }}</strong>
                                                @if($subject->code)
                                                    <br><small class="text-muted">{{ $subject->code }}</small>
                                                @endif
                                            </td>
                                            <td>
                                                @if($assignment)
                                                    {{ $assignment->teacher->user->name ?? 'N/A' }}
                                                @else
                                                    <span class="text-muted">—</span>
                                                @endif
                                            </td>
                                            <td>
                                                <span class="badge bg-info">{{ $subjectMarks }}</span>
                                            </td>
                                            <td>
                                                <button class="btn btn-sm btn-outline-primary" disabled>
                                                    <i data-lucide="eye" style="width: 14px;" class="me-1"></i> View
                                                </button>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Quick Links -->
        <div class="col-lg-4">
            <div class="card border-0">
                <div class="card-header bg-light border-0 pb-3">
                    <h5 class="mb-0">
                        <i data-lucide="zap" class="me-2"></i> Quick Actions
                    </h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route('teacher.marks.index') }}" class="btn btn-outline-primary btn-sm text-start">
                            <i data-lucide="edit" class="me-2"></i>
                            <div>
                                <span>Enter Grades</span>
                                <small class="d-block text-muted">Add or edit student marks</small>
                            </div>
                        </a>

                        <a href="{{ route('teacher.attendance.index') }}" class="btn btn-outline-success btn-sm text-start">
                            <i data-lucide="check" class="me-2"></i>
                            <div>
                                <span>Mark Attendance</span>
                                <small class="d-block text-muted">Record student attendance</small>
                            </div>
                        </a>

                        @if(auth()->user()->teacher && auth()->user()->teacher->is_prof_principal && auth()->user()->teacher->head_class_id === $class->id)
                            <a href="{{ route('teacher.bulletin_ng.index') }}" class="btn btn-outline-warning btn-sm text-start">
                                <i data-lucide="file-check" class="me-2"></i>
                                <div>
                                    <span>Generate Bulletin</span>
                                    <small class="d-block text-muted">Create class report cards</small>
                                </div>
                            </a>
                        @endif
                    </div>

                    <hr class="my-3">

                    <div class="alert alert-info small mb-0">
                        <i data-lucide="info" class="me-1"></i>
                        <strong>Current:</strong> Term {{ $term }}, Sequence {{ $sequence }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Students & Grades Table -->
    <div class="row">
        <div class="col-12">
            <div class="card border-0">
                <div class="card-header bg-light border-0 pb-3">
                    <h5 class="mb-0">
                        <i data-lucide="users" class="me-2"></i> Student Performance Overview
                    </h5>
                </div>
                <div class="card-body">
                    @if($classDetails->students->isEmpty())
                        <div class="alert alert-info mb-0">
                            No students in this class yet.
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-sm table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th style="min-width: 150px;">Student Name</th>
                                        <th>Registration #</th>
                                        <th>Grades Count</th>
                                        <th>Subjects with Grades</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($classDetails->students as $student)
                                        @php
                                            $studentMarks = $marks->where('student_id', $student->id);
                                            $marksCount = $studentMarks->count();
                                            $subjectsWithGrades = $studentMarks->pluck('subject_id')->unique()->count();
                                            $totalSubjects = $subjects->count();
                                            $isComplete = $subjectsWithGrades === $totalSubjects;
                                        @endphp
                                        <tr>
                                            <td>
                                                <strong>{{ $student->user->name ?? 'N/A' }}</strong>
                                            </td>
                                            <td>
                                                <small class="text-muted">{{ $student->registration_number ?? '—' }}</small>
                                            </td>
                                            <td>
                                                <span class="badge bg-secondary">{{ $marksCount }}</span>
                                            </td>
                                            <td>
                                                <div class="progress" style="height: 20px;">
                                                    <div class="progress-bar" role="progressbar" 
                                                         style="width: {{ $totalSubjects > 0 ? ($subjectsWithGrades / $totalSubjects * 100) : 0 }}%;"
                                                         aria-valuenow="{{ $subjectsWithGrades }}" 
                                                         aria-valuemin="0" 
                                                         aria-valuemax="{{ $totalSubjects }}">
                                                        {{ $subjectsWithGrades }}/{{ $totalSubjects }}
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                @if($isComplete)
                                                    <span class="badge bg-success">
                                                        <i data-lucide="check" style="width: 12px;" class="me-1"></i> Complete
                                                    </span>
                                                @else
                                                    <span class="badge bg-warning">
                                                        <i data-lucide="clock" style="width: 12px;" class="me-1"></i> Incomplete
                                                    </span>
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
        </div>
    </div>
</div>

@push('scripts')
<script>
    // Lucide icons initialization (if using Lucide)
    if (typeof lucide !== 'undefined') {
        lucide.createIcons();
    }
</script>
@endpush

@endsection
