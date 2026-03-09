@extends('layouts.app')

@section('title', __('admin.bulletin_validation'))

@section('content')
<div class="container-fluid py-4">
    {{-- Header --}}
    <div class="row mb-4">
        <div class="col-md-8">
            <h1 class="h3 mb-0">
                <i class="fas fa-clipboard-check text-success me-2"></i>
                @if(app()->getLocale() === 'fr')
                    Validation et Publication des Bulletins
                @else
                    Bulletin Validation and Publishing
                @endif
            </h1>
        </div>
    </div>

    {{-- Validation Workflow Info --}}
    <div class="alert alert-info border-0">
        <h6 class="alert-heading mb-2">
            <i class="fas fa-info-circle me-2"></i>
            @if(app()->getLocale() === 'fr')
                Workflow de Validation
            @else
                Validation Workflow
            @endif
        </h6>
        <p class="mb-0 small">
            @if(app()->getLocale() === 'fr')
                1. <strong>Brouillon</strong> (enseignant saisit) → 2. <strong>Soumis</strong> (enseignant soumet) → 
                3. <strong>Validé</strong> (prof principal) → 4. <strong>Publié</strong> (admin) → Visible aux parents/élèves
            @else
                1. <strong>Draft</strong> (teacher enters) → 2. <strong>Submitted</strong> (teacher submits) → 
                3. <strong>Validated</strong> (principal) → 4. <strong>Published</strong> (admin) → Visible to parents/students
            @endif
        </p>
    </div>

    {{-- Tabs for Status --}}
    <ul class="nav nav-tabs mb-4" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="submitted-tab" data-bs-toggle="tab" 
                data-bs-target="#submitted-pane" type="button" role="tab">
                @if(app()->getLocale() === 'fr') Soumis @else Submitted @endif
                <span class="badge bg-warning ms-2">
                    {{ $bulletins->where('status', 'submitted')->count() }}
                </span>
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="validated-tab" data-bs-toggle="tab" 
                data-bs-target="#validated-pane" type="button" role="tab">
                @if(app()->getLocale() === 'fr') Validés @else Validated @endif
                <span class="badge bg-info ms-2">
                    {{ $bulletins->where('status', 'validated')->count() }}
                </span>
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="published-tab" data-bs-toggle="tab" 
                data-bs-target="#published-pane" type="button" role="tab">
                @if(app()->getLocale() === 'fr') Publiés @else Published @endif
                <span class="badge bg-success ms-2">
                    {{ $bulletins->where('status', 'published')->count() }}
                </span>
            </button>
        </li>
    </ul>

    {{-- Tab Content --}}
    <div class="tab-content">
        {{-- Submitted Bulletins --}}
        <div class="tab-pane fade show active" id="submitted-pane" role="tabpanel">
            @php $submitted = $bulletins->where('status', 'submitted')->values(); @endphp
            
            @if($submitted->count() > 0)
                <div class="card border-0 shadow-sm">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>@if(app()->getLocale() === 'fr') Élève @else Student @endif</th>
                                    <th>@if(app()->getLocale() === 'fr') Classe @else Class @endif</th>
                                    <th>@if(app()->getLocale() === 'fr') Période @else Period @endif</th>
                                    <th>@if(app()->getLocale() === 'fr') Moyenne @else Average @endif</th>
                                    <th>@if(app()->getLocale() === 'fr') Soumis le @else Submitted @endif</th>
                                    <th>@if(app()->getLocale() === 'fr') Actions @else Actions @endif</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($submitted as $bulletin)
                                    <tr>
                                        <td class="fw-bold">{{ $bulletin->student->user->name ?? '—' }}</td>
                                        <td>{{ $bulletin->student->classe?->name ?? '—' }}</td>
                                        <td>
                                            <span class="badge bg-warning">
                                                T{{ $bulletin->term }}/S{{ $bulletin->sequence ?? '-' }}
                                            </span>
                                        </td>
                                        <td>
                                            <strong>{{ $bulletin->moyenne ? number_format($bulletin->moyenne, 2) : '—' }}</strong>/20
                                        </td>
                                        <td>
                                            <small>{{ $bulletin->submitted_at?->format('d/m/Y H:i') ?? '—' }}</small>
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm" role="group">
                                                <a href="#" class="btn btn-outline-primary" 
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#viewModal{{ $bulletin->id }}"
                                                    title="Voir">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <form action="{{ route('admin.bulletins.validate', $bulletin->id) }}" 
                                                    method="POST" style="display:inline">
                                                    @csrf
                                                    <button type="submit" class="btn btn-outline-success" title="Valider">
                                                        <i class="fas fa-check"></i>
                                                    </button>
                                                </form>
                                                <button type="button" class="btn btn-outline-danger" 
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#rejectModal{{ $bulletin->id }}"
                                                    title="Rejeter">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>

                                    {{-- View Modal --}}
                                    <div class="modal fade" id="viewModal{{ $bulletin->id }}" tabindex="-1">
                                        <div class="modal-dialog modal-lg">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">{{ $bulletin->student->user->name }}</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <div class="row mb-3">
                                                        <div class="col-md-4">
                                                            <strong>@if(app()->getLocale() === 'fr') Classe @else Class @endif:</strong>
                                                            {{ $bulletin->student->classe?->name ?? '—' }}
                                                        </div>
                                                        <div class="col-md-4">
                                                            <strong>@if(app()->getLocale() === 'fr') Moyenne @else Average @endif:</strong>
                                                            {{ $bulletin->moyenne ? number_format($bulletin->moyenne, 2) : '—' }}/20
                                                        </div>
                                                        <div class="col-md-4">
                                                            <strong>@if(app()->getLocale() === 'fr') Rang @else Rank @endif:</strong>
                                                            {{ $bulletin->rang ?? '—' }}
                                                        </div>
                                                    </div>

                                                    {{-- Notes table preview --}}
                                                    <h6 class="mt-4 mb-2">@if(app()->getLocale() === 'fr') Notes @else Grades @endif</h6>
                                                    <div class="table-responsive">
                                                        <table class="table table-sm">
                                                            <thead>
                                                                <tr>
                                                                    <th>@if(app()->getLocale() === 'fr') Matière @else Subject @endif</th>
                                                                    <th class="text-center">@if(app()->getLocale() === 'fr') Note @else Score @endif</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                @foreach($bulletin->marks()->with('subject')->get() as $mark)
                                                                    <tr>
                                                                        <td>{{ $mark->subject?->name ?? '—' }}</td>
                                                                        <td class="text-center">
                                                                            {{ $mark->score ? number_format($mark->score, 2) : '—' }}
                                                                        </td>
                                                                    </tr>
                                                                @endforeach
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                                        @if(app()->getLocale() === 'fr') Fermer @else Close @endif
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Reject Modal --}}
                                    <div class="modal fade" id="rejectModal{{ $bulletin->id }}" tabindex="-1">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">
                                                        @if(app()->getLocale() === 'fr')
                                                            Rejeter le bulletin
                                                        @else
                                                            Reject bulletin
                                                        @endif
                                                    </h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <form action="{{ route('admin.bulletins.reject', $bulletin->id) }}" method="POST">
                                                    @csrf
                                                    <div class="modal-body">
                                                        <div class="mb-3">
                                                            <label class="form-label">
                                                                @if(app()->getLocale() === 'fr')
                                                                    Raison du rejet
                                                                @else
                                                                    Reason for rejection
                                                                @endif
                                                            </label>
                                                            <textarea name="reason" class="form-control" rows="4" required 
                                                                placeholder="@if(app()->getLocale() === 'fr')
                                                                    Ex: Notes manquantes, erreurs de calcul...
                                                                @else
                                                                    Ex: Missing grades, calculation errors...
                                                                @endif"></textarea>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                                            @if(app()->getLocale() === 'fr') Annuler @else Cancel @endif
                                                        </button>
                                                        <button type="submit" class="btn btn-danger">
                                                            @if(app()->getLocale() === 'fr') Rejeter @else Reject @endif
                                                        </button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @else
                <div class="alert alert-info">
                    @if(app()->getLocale() === 'fr')
                        Aucun bulletin en attente de validation.
                    @else
                        No bulletins pending validation.
                    @endif
                </div>
            @endif
        </div>

        {{-- Validated & Published Bulletins --}}
        <div class="tab-pane fade" id="validated-pane" role="tabpanel">
            @php $validated = $bulletins->where('status', 'validated')->values(); @endphp
            
            @if($validated->count() > 0)
                <div class="alert alert-info mb-3">
                    @if(app()->getLocale() === 'fr')
                        Ces bulletins ont été validés par le prof principal. Cliquez sur Publier pour les rendre visibles aux parents et élèves.
                    @else
                        These bulletins have been validated by the principal. Click Publish to make them visible to parents and students.
                    @endif
                </div>

                <div class="card border-0 shadow-sm">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>@if(app()->getLocale() === 'fr') Élève @else Student @endif</th>
                                    <th>@if(app()->getLocale() === 'fr') Classe @else Class @endif</th>
                                    <th>@if(app()->getLocale() === 'fr') Période @else Period @endif</th>
                                    <th>@if(app()->getLocale() === 'fr') Validé par @else Validated by @endif</th>
                                    <th>@if(app()->getLocale() === 'fr') Actions @else Actions @endif</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($validated as $bulletin)
                                    <tr>
                                        <td class="fw-bold">{{ $bulletin->student->user->name ?? '—' }}</td>
                                        <td>{{ $bulletin->student->classe?->name ?? '—' }}</td>
                                        <td>
                                            <span class="badge bg-info">
                                                T{{ $bulletin->term }}/S{{ $bulletin->sequence ?? '-' }}
                                            </span>
                                        </td>
                                        <td>
                                            <small>{{ $bulletin->validator->name ?? '—' }}</small>
                                        </td>
                                        <td>
                                            <form action="{{ route('admin.bulletins.publish', $bulletin->id) }}" method="POST" style="display:inline">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-success">
                                                    <i class="fas fa-globe me-1"></i>
                                                    @if(app()->getLocale() === 'fr') Publier @else Publish @endif
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @else
                <div class="alert alert-info">
                    @if(app()->getLocale() === 'fr')
                        Aucun bulletin validé en attente de publication.
                    @else
                        No validated bulletins pending publication.
                    @endif
                </div>
            @endif
        </div>

        {{-- Published Bulletins --}}
        <div class="tab-pane fade" id="published-pane" role="tabpanel">
            @php $published = $bulletins->where('status', 'published')->values(); @endphp
            
            @if($published->count() > 0)
                <div class="card border-0 shadow-sm">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>@if(app()->getLocale() === 'fr') Élève @else Student @endif</th>
                                    <th>@if(app()->getLocale() === 'fr') Classe @else Class @endif</th>
                                    <th>@if(app()->getLocale() === 'fr') Période @else Period @endif</th>
                                    <th>@if(app()->getLocale() === 'fr') Moyenne @else Average @endif</th>
                                    <th>@if(app()->getLocale() === 'fr') Publié le @else Published @endif</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($published as $bulletin)
                                    <tr>
                                        <td class="fw-bold">{{ $bulletin->student->user->name ?? '—' }}</td>
                                        <td>{{ $bulletin->student->classe?->name ?? '—' }}</td>
                                        <td>
                                            <span class="badge bg-success">
                                                T{{ $bulletin->term }}/S{{ $bulletin->sequence ?? '-' }}
                                            </span>
                                        </td>
                                        <td>
                                            <strong>{{ $bulletin->moyenne ? number_format($bulletin->moyenne, 2) : '—' }}</strong>/20
                                        </td>
                                        <td>
                                            <small>{{ $bulletin->published_at?->format('d/m/Y') ?? '—' }}</small>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @else
                <div class="alert alert-info">
                    @if(app()->getLocale() === 'fr')
                        Aucun bulletin publié.
                    @else
                        No published bulletins.
                    @endif
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
