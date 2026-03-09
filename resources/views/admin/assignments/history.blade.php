{{-- admin/assignments/history.blade.php --}}
@extends('layouts.app')
@section('title', app()->getLocale() === 'fr' ? 'Historique des affectations' : 'Assignment History')
@section('content')
@php $isFr = app()->getLocale() === 'fr'; @endphp

<div class="page-header">
    <div class="d-flex align-items-center gap-3">
        <a href="{{ route('admin.assignments') }}" class="btn btn-light btn-sm">
            <i data-lucide="arrow-left" style="width:14px"></i>
        </a>
        <div class="page-icon" style="background:linear-gradient(135deg,#8b5cf6,#7c3aed)">
            <i data-lucide="history"></i>
        </div>
        <div>
            <h1 class="page-title">{{ $isFr ? 'Historique des Affectations' : 'Assignment History' }}</h1>
            <p class="page-subtitle text-muted">
                {{ $isFr ? 'Journal de toutes les modifications' : 'Log of all changes' }}
            </p>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>{{ $isFr ? 'Date' : 'Date' }}</th>
                        <th>{{ $isFr ? 'Action' : 'Action' }}</th>
                        <th>{{ $isFr ? 'Enseignant' : 'Teacher' }}</th>
                        <th>{{ $isFr ? 'Matière' : 'Subject' }}</th>
                        <th>{{ $isFr ? 'Classe' : 'Class' }}</th>
                        <th>{{ $isFr ? 'Année académique' : 'Academic year' }}</th>
                        <th>{{ $isFr ? 'Modifié par' : 'Modified by' }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($history as $entry)
                    <tr>
                        <td style="font-size:.8rem;color:var(--text-muted);white-space:nowrap">
                            {{ $entry->created_at?->format('d/m/Y H:i') }}
                        </td>
                        <td>
                            @php
                            $actionMap = [
                                'assigned'   => ['bg-success',  $isFr ? 'Affecté'  : 'Assigned'],
                                'unassigned' => ['bg-danger',   $isFr ? 'Retiré'   : 'Removed'],
                                'principal'  => ['bg-primary',  $isFr ? 'Prof. Principal' : 'Head Teacher'],
                            ];
                            [$bg, $label] = $actionMap[$entry->action] ?? ['bg-secondary', $entry->action];
                            @endphp
                            <span class="badge {{ $bg }}" style="font-size:.72rem">{{ $label }}</span>
                        </td>
                        <td style="font-size:.84rem;font-weight:600">
                            {{ $entry->teacher?->user?->name ?? '—' }}
                        </td>
                        <td style="font-size:.83rem">{{ $entry->subject?->name ?? '—' }}</td>
                        <td style="font-size:.83rem">{{ $entry->classe?->name ?? '—' }}</td>
                        <td style="font-size:.78rem;color:var(--text-muted)">{{ $entry->academic_year }}</td>
                        <td style="font-size:.78rem;color:var(--text-muted)">
                            {{ $entry->performedBy?->name ?? '—' }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-5 text-muted">
                            <i data-lucide="history" style="width:32px;opacity:.3;display:block;margin:0 auto .75rem"></i>
                            {{ $isFr ? 'Aucun historique.' : 'No history found.' }}
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if(method_exists($history, 'links'))
        <div class="card-footer">{{ $history->links() }}</div>
        @endif
    </div>
</div>

@endsection


