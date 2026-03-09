{{--
    | admin/bulletins/index.blade.php — Validation des Bulletins
    | Admin valide → publie → parents/élèves peuvent télécharger
    --}}

@extends('layouts.app')
@section('title', app()->getLocale() === 'fr' ? 'Bulletins — Validation' : 'Report Cards — Validation')

@section('content')
@php $isFr = app()->getLocale() === 'fr'; @endphp

<div class="page-header">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
        <div class="d-flex align-items-center gap-3">
            <div class="page-icon" style="background:linear-gradient(135deg,#f59e0b,#d97706)"><i data-lucide="file-text"></i></div>
            <div>
                <h1 class="page-title">{{ $isFr ? 'Validation des Bulletins' : 'Report Card Validation' }}</h1>
                <p class="page-subtitle text-muted">{{ $isFr ? 'Vérifiez et publiez les bulletins soumis par les enseignants' : 'Review and publish report cards submitted by teachers' }}</p>
            </div>
        </div>
        <div class="d-flex gap-2">
            <span style="padding:.4rem .9rem;border-radius:8px;background:#fef3c7;color:#92400e;font-size:.8rem;font-weight:700">
                {{ $pendingCount ?? 0 }} {{ $isFr ? 'en attente' : 'pending' }}
            </span>
        </div>
    </div>
</div>

{{-- Status filter tabs --}}
<div class="d-flex gap-2 mb-4 flex-wrap">
    @foreach(['all' => $isFr ? 'Tous' : 'All', 'draft' => $isFr ? 'Brouillons' : 'Drafts', 'submitted' => $isFr ? 'Soumis' : 'Submitted', 'validated' => $isFr ? 'Validés' : 'Validated', 'published' => $isFr ? 'Publiés' : 'Published'] as $status => $label)
    <a href="{{ request()->fullUrlWithQuery(['status' => $status]) }}"
       class="btn btn-sm {{ (request('status', 'all') === $status) ? 'btn-primary' : 'btn-light' }}">
        {{ $label }}
    </a>
    @endforeach
</div>

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>{{ $isFr ? 'Classe' : 'Class' }}</th>
                        <th>{{ $isFr ? 'Prof. Principal' : 'Head Teacher' }}</th>
                        <th>{{ $isFr ? 'Trimestre / Séq.' : 'Term / Seq.' }}</th>
                        <th style="text-align:center">{{ $isFr ? 'Élèves' : 'Students' }}</th>
                        <th style="text-align:center">{{ $isFr ? 'Complétés' : 'Completed' }}</th>
                        <th style="text-align:center">{{ $isFr ? 'Statut' : 'Status' }}</th>
                        <th>{{ $isFr ? 'Soumis le' : 'Submitted' }}</th>
                        <th style="text-align:center">{{ $isFr ? 'Actions' : 'Actions' }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($bulletins ?? [] as $bulletin)
                    @php
                        $statusConfig = [
                            'draft'     => ['bg-secondary', $isFr ? 'Brouillon' : 'Draft'],
                            'submitted' => ['bg-warning',   $isFr ? 'Soumis'    : 'Submitted'],
                            'validated' => ['bg-info',      $isFr ? 'Validé'    : 'Validated'],
                            'published' => ['bg-success',   $isFr ? 'Publié'    : 'Published'],
                        ];
                        [$badgeClass, $statusLabel] = $statusConfig[$bulletin->status] ?? ['bg-secondary', $bulletin->status];
                        $pct = $bulletin->total_students > 0 ? round(($bulletin->completed_count / $bulletin->total_students) * 100) : 0;
                    @endphp
                    <tr>
                        <td class="fw-bold" style="font-size:.88rem">{{ $bulletin->classe?->name }}</td>
                        <td style="font-size:.83rem">{{ $bulletin->classe?->headTeacher?->user?->name ?? '—' }}</td>
                        <td style="font-size:.8rem">
                            {{ $isFr ? 'T' : 'Term' }}{{ $bulletin->term }} / {{ $isFr ? 'Séq.' : 'Seq.' }}{{ $bulletin->sequence }}
                        </td>
                        <td style="text-align:center;font-weight:700">{{ $bulletin->total_students }}</td>
                        <td style="text-align:center">
                            <div style="font-size:.8rem;font-weight:700;color:{{ $pct === 100 ? '#10b981' : ($pct >= 80 ? '#f59e0b' : '#ef4444') }}">
                                {{ $bulletin->completed_count }}/{{ $bulletin->total_students }}
                            </div>
                            <div style="height:4px;background:var(--border);border-radius:2px;margin-top:3px;min-width:60px">
                                <div style="height:100%;background:{{ $pct === 100 ? '#10b981' : '#f59e0b' }};border-radius:2px;width:{{ $pct }}%"></div>
                            </div>
                        </td>
                        <td style="text-align:center"><span class="badge {{ $badgeClass }}">{{ $statusLabel }}</span></td>
                        <td style="font-size:.77rem;color:var(--text-muted)">
                            {{ $bulletin->submitted_at?->format('d/m/Y H:i') ?? '—' }}
                        </td>
                        <td style="text-align:center">
                            <div class="d-flex gap-1 justify-content-center">
                                <a href="{{ route('teacher.bulletin.show', $bulletin->id) }}" class="btn btn-xs btn-light" title="{{ $isFr ? 'Aperçu' : 'Preview' }}">
                                    <i data-lucide="eye" style="width:13px"></i>
                                </a>
                                @if($bulletin->status === 'submitted')
                                <form method="POST" action="{{ route('admin.bulletins.validate', $bulletin->id) }}">
                                    @csrf
                                    <button type="submit" class="btn btn-xs btn-primary" title="{{ $isFr ? 'Valider' : 'Validate' }}">
                                        <i data-lucide="check" style="width:13px"></i>
                                    </button>
                                </form>
                                @endif
                                @if(in_array($bulletin->status, ['submitted', 'validated']))
                                <form method="POST" action="{{ route('admin.bulletins.publish', $bulletin->id) }}"
                                      onsubmit="return confirm('{{ $isFr ? 'Publier tous les bulletins de cette classe ?' : 'Publish all report cards for this class?' }}')">
                                    @csrf
                                    <button type="submit" class="btn btn-xs btn-warning" title="{{ $isFr ? 'Publier' : 'Publish' }}">
                                        <i data-lucide="send" style="width:13px"></i>
                                    </button>
                                </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center py-5 text-muted">
                            <i data-lucide="file-text" style="width:36px;opacity:.25;display:block;margin:0 auto 1rem"></i>
                            {{ $isFr ? 'Aucun bulletin trouvé.' : 'No report cards found.' }}
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if(method_exists($bulletins ?? new stdClass, 'links'))
        <div class="card-footer">{{ $bulletins->links() }}</div>
        @endif
    </div>
</div>

@endsection


