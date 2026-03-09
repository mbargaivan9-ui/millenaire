{{--
    | admin/announcements/index.blade.php — Gestion des Annonces
    --}}

@extends('layouts.app')
@section('title', app()->getLocale() === 'fr' ? 'Annonces' : 'Announcements')

@section('content')
@php $isFr = app()->getLocale() === 'fr'; @endphp

<div class="page-header">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
        <div class="d-flex align-items-center gap-3">
            <div class="page-icon" style="background:linear-gradient(135deg,#8b5cf6,#7c3aed)"><i data-lucide="megaphone"></i></div>
            <div>
                <h1 class="page-title">{{ $isFr ? 'Annonces' : 'Announcements' }}</h1>
                <p class="page-subtitle text-muted">{{ $announcements->total() ?? 0 }} {{ $isFr ? 'annonces' : 'announcements' }}</p>
            </div>
        </div>
        <a href="{{ route('admin.announcements.create') }}" class="btn btn-primary btn-sm">
            <i data-lucide="plus" style="width:14px" class="me-1"></i>
            {{ $isFr ? 'Nouvelle annonce' : 'New announcement' }}
        </a>
    </div>
</div>

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>{{ $isFr ? 'Titre' : 'Title' }}</th>
                        <th>{{ $isFr ? 'Catégorie' : 'Category' }}</th>
                        <th style="text-align:center">{{ $isFr ? 'Statut' : 'Status' }}</th>
                        <th>{{ $isFr ? 'Date publication' : 'Published at' }}</th>
                        <th style="text-align:center">{{ $isFr ? 'Actions' : 'Actions' }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($announcements as $ann)
                    <tr>
                        <td>
                            <div class="fw-semibold" style="font-size:.85rem">{{ $ann->title }}</div>
                            <div style="font-size:.73rem;color:var(--text-muted)">{{ Str::limit($ann->content, 60) }}</div>
                        </td>
                        <td><span class="badge bg-secondary">{{ $ann->category ?? '—' }}</span></td>
                        <td style="text-align:center">
                            @if($ann->is_published)
                                <span class="badge bg-success">{{ $isFr ? 'Publié' : 'Published' }}</span>
                            @else
                                <span class="badge bg-warning">{{ $isFr ? 'Brouillon' : 'Draft' }}</span>
                            @endif
                        </td>
                        <td style="font-size:.8rem;color:var(--text-muted)">{{ $ann->published_at?->format('d/m/Y') ?? '—' }}</td>
                        <td style="text-align:center">
                            <div class="d-flex gap-1 justify-content-center">
                                <a href="{{ route('admin.announcements.edit', $ann->id) }}" class="btn btn-xs btn-light">
                                    <i data-lucide="edit-2" style="width:13px"></i>
                                </a>
                                @if(!$ann->is_published)
                                <form method="POST" action="{{ route('admin.announcements.publish', $ann->id) }}">
                                    @csrf @method('PATCH')
                                    <button type="submit" class="btn btn-xs btn-primary" title="{{ $isFr ? 'Publier' : 'Publish' }}">
                                        <i data-lucide="send" style="width:13px"></i>
                                    </button>
                                </form>
                                @endif
                                <form method="POST" action="{{ route('admin.announcements.destroy', $ann->id) }}"
                                      onsubmit="return confirm('{{ $isFr ? 'Supprimer cette annonce ?' : 'Delete this announcement?' }}')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-xs btn-danger">
                                        <i data-lucide="trash-2" style="width:13px"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="5" class="text-center py-5 text-muted">
                        <i data-lucide="megaphone" style="width:32px;opacity:.3;display:block;margin:0 auto .75rem"></i>
                        {{ $isFr ? 'Aucune annonce.' : 'No announcements.' }}
                    </td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer">{{ $announcements->links() }}</div>
    </div>
</div>

@endsection


