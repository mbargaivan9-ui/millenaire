@extends('layouts.app')

@section('title', 'Module Bulletins')

@push('styles')
    <style>
        .bulletin-banner {
            background: linear-gradient(135deg, #4F46E5 0%, #7c3aed 100%);
            border-radius: 14px;
            color: #fff;
            padding: 30px;
            margin-bottom: 30px;
            box-shadow: 0 10px 30px rgba(79, 70, 229, 0.15);
        }
        .bulletin-banner h1 {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 10px;
        }
        .bulletin-banner p {
            font-size: 1rem;
            opacity: 0.95;
            margin-bottom: 0;
        }
        .assignment-card {
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            transition: all .3s ease;
            cursor: pointer;
            background: #fff;
            overflow: hidden;
            height: 100%;
        }
        .assignment-card:hover {
            border-color: #4F46E5;
            box-shadow: 0 12px 30px rgba(79, 70, 229, 0.15);
            transform: translateY(-4px);
        }
        .subject-badge {
            background: linear-gradient(135deg, #4F46E5, #818CF8);
            color: #fff;
            border-radius: 8px;
            padding: 6px 12px;
            font-size: 0.75rem;
            font-weight: 700;
        }
        .completion-bar {
            height: 6px;
            border-radius: 99px;
            background: #e5e7eb;
            overflow: hidden;
        }
        .completion-bar-fill {
            height: 100%;
            border-radius: 99px;
            background: linear-gradient(90deg, #4F46E5, #818CF8);
            transition: width 0.4s ease;
        }
        .principal-banner {
            background: linear-gradient(135deg, #0ea5e9 0%, #0284c7 100%);
            border-radius: 14px;
            color: #fff;
            padding: 30px;
            margin-bottom: 30px;
            box-shadow: 0 10px 30px rgba(14, 165, 233, 0.15);
        }
        .principal-banner h2 {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 8px;
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
                <div class="page-icon" style="background:linear-gradient(135deg,#4F46E5,#7c3aed)">
                    <i data-lucide="book-open"></i>
                </div>
                <div>
                    <h1 class="page-title">Module Bulletins</h1>
                    <p class="page-subtitle text-muted">Saisissez, consultez et gérez les bulletins de vos classes</p>
                </div>
            </div>
        </div>
    </div>

    {{-- -- BANNIÈRE PROF PRINCIPAL -- --}}
    @if($isPrincipal && $principalClass)
    <div class="principal-banner mb-4">
        <div class="d-flex align-items-start justify-content-between flex-wrap gap-3">
            <div>
                <h2 class="mb-0">
                    <i class="fas fa-crown me-2" style="color:#fbbf24;"></i>Professeur Principal — {{ $principalClass->name }}
                </h2>
                <p class="mb-0 opacity-75 mt-2" style="font-size: 0.95rem;">
                    Vous avez accès étendu : saisie de toutes les matières, verrouillage du trimestre, tableau de complétion.
                </p>
            </div>
            <div class="d-flex gap-2 flex-wrap">
                <span class="stat-pill">
                    <i class="fas fa-users"></i>
                    <span><strong>{{ $principalClass->students()->where('is_active',true)->count() }}</strong> élève{{ $principalClass->students()->where('is_active',true)->count() > 1 ? 's' : '' }}</span>
                </span>
                <a href="{{ route('teacher.bulletin.completion', $principalClass->id) }}"
                   class="btn btn-light btn-sm fw-semibold">
                    <i class="fas fa-chart-bar me-1"></i>Tableau complétion
                </a>
                <a href="{{ route('teacher.bulletin.show', [$principalClass->id, $principalClass->students()->where('is_active',true)->first()?->id ?? 0]) }}"
                   class="btn btn-warning btn-sm fw-semibold text-dark">
                    <i class="fas fa-eye me-1"></i>Voir bulletins
                </a>
            </div>
        </div>
    </div>
    @endif

    {{-- ── FILTRES / PARAMÈTRES ────────────────────────────────────── --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form class="row g-3 align-items-end" id="filter-form">
                <div class="col-sm-4">
                    <label class="form-label fw-semibold small">Trimestre</label>
                    <select name="term" id="filter-term" class="form-select">
                        <option value="1">Trimestre 1</option>
                        <option value="2">Trimestre 2</option>
                        <option value="3">Trimestre 3</option>
                    </select>
                </div>
                <div class="col-sm-4">
                    <label class="form-label fw-semibold small">Séquence</label>
                    <select name="sequence" id="filter-sequence" class="form-select">
                        <option value="1">Séquence 1</option>
                        <option value="2">Séquence 2</option>
                        <option value="3">Séquence 3</option>
                        <option value="4">Séquence 4</option>
                        <option value="5">Séquence 5</option>
                        <option value="6">Séquence 6</option>
                    </select>
                </div>
                <div class="col-sm-4">
                    <label class="form-label fw-semibold small">Année scolaire</label>
                    <select name="academic_year" id="filter-year" class="form-select">
                        @php
                            $currentYear = now()->year;
                            $yearLabel = now()->month >= 9 ? "$currentYear-" . ($currentYear+1) : ($currentYear-1) . "-$currentYear";
                        @endphp
                        <option value="{{ $yearLabel }}">{{ $yearLabel }}</option>
                    </select>
                </div>
            </form>
        </div>
    </div>

    {{-- ── GRILLE DES MATIÈRES ─────────────────────────────────────── --}}
    @if($assignments && $assignments->count() > 0)
    <div class="row g-4">
        @foreach($assignments as $cst)
        <div class="col-md-4 col-lg-3">
            <div class="assignment-card">
                <div style="padding: 15px; background: linear-gradient(135deg, #4F46E515, #7c3aed15); border-bottom: 2px solid #4F46E5;">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="subject-badge">{{ $cst->subject->code ?? substr($cst->subject->name,0,3) }}</span>
                        <span class="text-muted small fw-semibold">Coeff. {{ $cst->subject->coefficient ?? '1' }}</span>
                    </div>
                    <div>
                        <h6 class="fw-bold mb-1" style="color:#1e293b;">{{ $cst->subject->name }}</h6>
                        <p class="text-muted small mb-0">
                            <i class="fas fa-school me-1"></i>{{ $cst->classe->name }}
                        </p>
                    </div>
                </div>

                <div style="padding: 15px;">
                    {{-- Barre de complétion (dynamique par JS) --}}
                    <div class="mb-3">
                        <div class="d-flex justify-content-between small mb-1">
                            <span class="text-muted">Complétion</span>
                            <span class="fw-semibold completion-pct" data-cst="{{ $cst->id }}">—</span>
                        </div>
                        <div class="completion-bar">
                            <div class="completion-bar-fill" data-cst="{{ $cst->id }}" style="width:0%"></div>
                        </div>
                    </div>

                    <div class="d-grid gap-2">
                        <a href="{{ route('teacher.bulletin.grid', $cst->id) }}?term=1&sequence=1&academic_year={{ $yearLabel }}"
                           class="btn btn-outline-primary btn-sm fw-semibold">
                            <i class="fas fa-table me-1"></i>Grille
                        </a>
                        @if($cst->classe->students()->where('is_active',true)->first())
                        <a href="{{ route('teacher.bulletin.show', [$cst->classe->id, $cst->classe->students()->where('is_active',true)->first()->id]) }}?term=1&academic_year={{ $yearLabel }}"
                           class="btn btn-primary btn-sm fw-semibold">
                            <i class="fas fa-scroll me-1"></i>Bulletins
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
            <i class="fas fa-inbox"></i>
        </div>
        <h4 class="text-muted mb-2">Aucune matière assignée</h4>
        <p class="text-muted">Vous n'avez pas encore de matière assignée. Les administrateurs doivent vous assigner à des classes et des matières.</p>
    </div>
    @endif

    @push('scripts')
    <script>
    // Mettre à jour les liens quand les filtres changent
    document.querySelectorAll('#filter-form select').forEach(sel => {
        sel.addEventListener('change', () => {
            const term = document.getElementById('filter-term').value;
            const seq  = document.getElementById('filter-sequence').value;
            const year = document.getElementById('filter-year').value;

            document.querySelectorAll('a[href*="grid"]').forEach(a => {
                const base = a.href.split('?')[0];
                a.href = `${base}?term=${term}&sequence=${seq}&academic_year=${year}`;
            });
        });
    });
    </script>
    @endpush
@endsection
