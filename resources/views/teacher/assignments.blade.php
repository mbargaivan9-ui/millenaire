@extends('layouts.app')

@section('title', 'Mes Assignations')

@push('styles')
    <style>
        .assignment-banner {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 14px;
            color: #fff;
            padding: 30px;
            margin-bottom: 30px;
            box-shadow: 0 10px 30px rgba(102, 126, 234, 0.15);
        }
        .assignment-banner h1 {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 10px;
        }
        .assignment-banner > .d-flex > div:first-child > p {
            font-size: 1rem;
            opacity: 0.95;
            margin-bottom: 0;
        }
        .class-card {
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            transition: all .3s ease;
            background: #fff;
            overflow: hidden;
            height: 100%;
        }
        .class-card:hover {
            border-color: #667eea;
            box-shadow: 0 12px 30px rgba(102, 126, 234, 0.15);
            transform: translateY(-4px);
        }
        .class-header {
            background: linear-gradient(135deg, #667eea15, #764ba215);
            border-bottom: 2px solid #667eea;
            padding: 18px;
            border-radius: 12px 12px 0 0;
        }
        .class-name {
            font-size: 1.2rem;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 10px;
        }
        .class-meta {
            display: flex;
            gap: 18px;
            font-size: 0.9rem;
            color: #64748b;
            flex-wrap: wrap;
        }
        .meta-item {
            display: flex;
            align-items: center;
            gap: 6px;
        }
        .subjects-list {
            padding: 18px;
        }
        .subjects-label {
            font-weight: 600;
            color: #475569;
            margin-bottom: 12px;
            display: block;
            font-size: 0.95rem;
        }
        .subject-badge {
            display: inline-block;
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: #fff;
            padding: 8px 14px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
            margin: 0 8px 8px 0;
        }
        .stat-pill {
            background: rgba(255, 255, 255, 0.15);
            border-radius: 20px;
            padding: 8px 18px;
            font-size: 0.9rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .empty-state {
            text-align: center;
            padding: 60px 20px;
        }
        .empty-state-icon {
            font-size: 4rem;
            color: #cbd5e1;
            margin-bottom: 20px;
        }
    </style>
    @endpush

@section('content')

    {{-- -- PAGE HEADER -- --}}
    <div class="page-header">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
            <div class="d-flex align-items-center gap-3">
                <div class="page-icon" style="background:linear-gradient(135deg,#667eea,#764ba2)">
                    <i data-lucide="users"></i>
                </div>
                <div>
                    <h1 class="page-title">Mes Assignations</h1>
                    <p class="page-subtitle text-muted">Consultez toutes vos assignations et matières</p>
                </div>
            </div>
        </div>
    </div>

    @if($assignmentsByClass->count() > 0)
    <div class="assignment-banner">
        <div style="display: flex; gap: 15px; flex-wrap: wrap;">
            <div class="stat-pill">
                <i class="fas fa-chalkboard"></i>
                <span><strong>{{ $assignmentsByClass->count() }}</strong> classe{{ $assignmentsByClass->count() > 1 ? 's' : '' }}</span>
            </div>
            <div class="stat-pill">
                <i class="fas fa-book"></i>
                <span><strong>{{ $assignments->count() }}</strong> matière{{ $assignments->count() > 1 ? 's' : '' }}</span>
            </div>
            <div class="stat-pill">
                <i class="fas fa-users"></i>
                <span><strong>{{ $assignmentsByClass->sum('studentCount') }}</strong> élève{{ $assignmentsByClass->sum('studentCount') > 1 ? 's' : '' }}</span>
            </div>
            <div class="stat-pill">
                <i class="fas fa-calendar-alt"></i>
                <span>{{ now()->format('d/m/Y') }}</span>
            </div>
        </div>
    </div>

    <div class="row g-4">
        @foreach($assignmentsByClass as $classId => $data)
        <div class="col-md-6 col-lg-4">
            <div class="class-card">
                <div class="class-header">
                    <div class="class-name">
                        <i class="fas fa-chalkboard-user me-2" style="color: #667eea;"></i>{{ $data['class']->name ?? 'N/A' }}
                    </div>
                    <div class="class-meta">
                        <div class="meta-item">
                            <i class="fas fa-users"></i>
                            <span>{{ $data['studentCount'] }} élève{{ $data['studentCount'] > 1 ? 's' : '' }}</span>
                        </div>
                        <div class="meta-item">
                            <i class="fas fa-book"></i>
                            <span>{{ $data['subjects']->count() }} matière{{ $data['subjects']->count() > 1 ? 's' : '' }}</span>
                        </div>
                    </div>
                </div>

                <div class="subjects-list">
                    <span class="subjects-label">
                        <i class="fas fa-graduation-cap me-2" style="color: #667eea;"></i>Matières enseignées
                    </span>
                    <div style="margin-bottom: 15px;">
                        @foreach($data['subjects'] as $subject)
                        <span class="subject-badge">
                            {{ $subject->name }}
                        </span>
                        @endforeach
                    </div>

                    <div class="d-grid gap-2">
                        @php
                            $classSubjectTeacher = $assignments->where('classe_id', $classId)->first();
                        @endphp
                        @if($classSubjectTeacher)
                        <a href="{{ route('teacher.grades.entry.index', $classSubjectTeacher->id) }}" class="btn btn-sm btn-outline-primary fw-semibold">
                            <i class="fas fa-edit me-1"></i>Saisir les notes
                        </a>
                        <a href="{{ route('teacher.attendance.index') }}?class={{ $classId }}" class="btn btn-sm btn-outline-info fw-semibold">
                            <i class="fas fa-clipboard-check me-1"></i>Présences
                        </a>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>
    @else
    <div class="empty-state">
        <div class="empty-state-icon">
            <i class="fas fa-tasks"></i>
        </div>
        <h4 class="text-muted mb-2">Aucune assignation trouvée</h4>
        <p class="text-muted">Les administrateurs doivent vous assigner à des classes et des matières pour que vous puissiez les consulter ici.</p>
    </div>
    @endif
@endsection
