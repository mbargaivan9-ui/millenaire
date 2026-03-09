@extends('layouts.app')

@section('title', 'Mes Cours')

@push('styles')
    <style>
        .course-banner {
            background: linear-gradient(135deg, #4F46E5 0%, #7c3aed 100%);
            border-radius: 14px;
            color: #fff;
            padding: 30px;
            margin-bottom: 30px;
            box-shadow: 0 10px 30px rgba(79, 70, 229, 0.15);
        }
        .course-banner h1 {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 10px;
        }
        .course-banner p {
            font-size: 1rem;
            opacity: 0.95;
            margin-bottom: 20px;
        }
        .banner-stats {
            display: flex;
            gap: 30px;
            flex-wrap: wrap;
            margin-top: 20px;
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
        .course-card {
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            transition: all .3s ease;
            background: #fff;
            overflow: hidden;
            height: 100%;
        }
        .course-card:hover {
            border-color: #4F46E5;
            box-shadow: 0 12px 30px rgba(79, 70, 229, 0.15);
            transform: translateY(-4px);
        }
        .course-card-header {
            background: linear-gradient(135deg, #4F46E515, #7c3aed15);
            border-bottom: 2px solid #4F46E5;
            padding: 15px;
        }
        .course-title {
            font-size: 1.1rem;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 8px;
        }
        .course-subject {
            font-size: 0.9rem;
            color: #64748b;
            display: flex;
            align-items: center;
            gap: 6px;
        }
        .course-content {
            padding: 15px;
        }
        .course-description {
            color: #475569;
            font-size: 0.95rem;
            margin-bottom: 15px;
            flex-grow: 1;
        }
        .type-badge {
            display: inline-block;
            padding: 6px 14px;
            border-radius: 6px;
            font-size: 0.75rem;
            font-weight: 600;
            margin-bottom: 12px;
        }
        .type-pdf { background: #fee2e2; color: #992e29; }
        .type-video { background: #dbeafe; color: #0c4a6e; }
        .type-document { background: #f3e8ff; color: #581c87; }
        .type-presentation { background: #fef3c7; color: #78350f; }
        .type-exercise { background: #dcfce7; color: #15803d; }
        .course-date {
            font-size: 0.85rem;
            color: #94a3b8;
            display: flex;
            align-items: center;
            gap: 6px;
        }
        .course-actions {
            display: flex;
            gap: 8px;
            margin-top: 15px;
        }
        .course-actions .btn {
            flex: 1;
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
                <div class="page-icon" style="background:linear-gradient(135deg,#4F46E5,#7c3aed)">
                    <i data-lucide="book"></i>
                </div>
                <div>
                    <h1 class="page-title">Mes Cours</h1>
                    <p class="page-subtitle text-muted">Consultez et gérez vos matériaux pédagogiques</p>
                </div>
            </div>
            <a href="{{ route('course-materials.create') }}" class="btn btn-primary">
                <i data-lucide="plus" style="width:14px" class="me-1"></i>Ajouter un Matériau
            </a>
        </div>
    </div>

    <div class="course-banner">
        <div class="banner-stats">
            <div class="stat-pill">
                <i class="fas fa-file"></i>
                <span>{{ $materials->count() }} matériau{{ $materials->count() > 1 ? 'x' : '' }}</span>
            </div>
            <div class="stat-pill">
                <i class="fas fa-calendar-alt"></i>
                <span>{{ now()->format('d/m/Y') }}</span>
            </div>
        </div>
    </div>

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert" style="margin-bottom: 20px;">
        <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    @if($materials->count() > 0)
    <div class="row g-4">
        @foreach($materials as $material)
        <div class="col-md-6 col-lg-4">
            <div class="course-card">
                <div class="course-card-header">
                    <div class="course-title">{{ $material->title }}</div>
                    <div class="course-subject">
                        <i class="fas fa-book"></i>
                        {{ $material->classSubjectTeacher->subject->name ?? 'N/A' }}
                    </div>
                </div>

                <div class="course-content d-flex flex-column">
                    <p class="course-description">
                        {{ Str::limit($material->description, 100) }}
                    </p>

                    <span class="type-badge type-{{ $material->type }}">
                        <i class="fas fa-tag me-1"></i>{{ ucfirst($material->type) }}
                    </span>

                    <div class="course-date">
                        <i class="fas fa-calendar"></i>
                        {{ $material->upload_date ? \Carbon\Carbon::parse($material->upload_date)->format('d/m/Y') : 'N/A' }}
                    </div>

                    <div class="course-actions">
                        @if($material->file_path)
                        <a href="{{ Storage::url($material->file_path) }}" target="_blank" class="btn btn-sm btn-outline-primary">
                            <i class="fas fa-download"></i>
                        </a>
                        @endif
                        <a href="{{ route('course-materials.edit', $material) }}" class="btn btn-sm btn-outline-warning">
                            <i class="fas fa-edit"></i>
                        </a>
                        <form action="{{ route('course-materials.destroy', $material) }}" method="POST" style="display:inline; flex: 1;">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-outline-danger w-100" onclick="return confirm('Êtes-vous sûr de vouloir supprimer?')">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    <div class="mt-4">
        {{ $materials->links() }}
    </div>
    @else
    <div class="empty-state">
        <div class="empty-state-icon">
            <i class="fas fa-book-open"></i>
        </div>
        <h4 class="text-muted mb-2">Aucun matériau trouvé</h4>
        <p class="text-muted mb-4">Vous n'avez pas encore créé de matériel pédagogique. Commencez par ajouter votre premier matériel.</p>
        <a href="{{ route('course-materials.create') }}" class="btn btn-primary">
            <i class="fas fa-plus me-2"></i>Créer un matériau
        </a>
    </div>
    @endif
@endsection
