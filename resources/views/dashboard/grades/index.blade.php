@extends('layouts.app')

@section('title', 'Saisie des Notes')

@section('content')
<div class="container-fluid">
    
    <div class="page-header mb-4">
        <h1 class="fw-bold text-dark">Saisie et Visualisation des Notes</h1>
        <p class="text-muted">Gérez les notes de vos élèves en temps réel</p>
    </div>

    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card border-0 bg-light-primary">
                <div class="card-body text-center">
                    <h2 class="fw-bold text-primary">{{ $totalMarks }}</h2>
                    <p class="text-muted mb-0">Notes saisies</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 bg-light-success">
                <div class="card-body text-center">
                    <h2 class="fw-bold text-success">{{ $averageGrade }}</h2>
                    <p class="text-muted mb-0">Moyenne générale</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 bg-light-warning">
                <div class="card-body text-center">
                    <h2 class="fw-bold text-warning">{{ $pendingGrades }}</h2>
                    <p class="text-muted mb-0">À corriger</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 bg-light-info">
                <div class="card-body text-center">
                    <h2 class="fw-bold text-info">{{ $lastUpdate }}</h2>
                    <p class="text-muted mb-0">Dernière maj</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Formulaire de saisie -->
    <div class="card border-0 mb-4">
        <div class="card-header bg-light border-0 py-3">
            <h5 class="card-title fw-bold mb-0">Saisie des Notes</h5>
        </div>
        <div class="card-body">
            <form method="POST" class="row g-3">
                @csrf
                <div class="col-md-4">
                    <label class="form-label">Classe</label>
                    <select name="classe_id" class="form-select" required onchange="loadStudents(this.value)">
                        <option value="">Sélectionner une classe...</option>
                        @foreach($classes as $class)
                        <option value="{{ $class->id }}">{{ $class->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Matière</label>
                    <select name="subject_id" class="form-select" required>
                        <option value="">Sélectionner une matière...</option>
                        @foreach($subjects as $subject)
                        <option value="{{ $subject->id }}">{{ $subject->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Type d'évaluation</label>
                    <select name="evaluation_type" class="form-select" required>
                        <option value="">Sélectionner...</option>
                        <option value="devoir">Devoir</option>
                        <option value="controle">Contrôle</option>
                        <option value="examen">Examen</option>
                        <option value="participation">Participation</option>
                    </select>
                </div>
                <div class="col-12">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-plus me-2"></i> Créer Évaluation
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Tableau des notes récentes -->
    <div class="card border-0">
        <div class="card-header bg-light border-0 py-3">
            <h5 class="card-title fw-bold mb-0">Dernières Notes</h5>
        </div>
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Élève</th>
                        <th>Matière</th>
                        <th>Type</th>
                        <th>Note</th>
                        <th>Sur</th>
                        <th>Appréciation</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recentMarks as $mark)
                    <tr>
                        <td>
                            <strong>{{ $mark->student->user->name }}</strong>
                        </td>
                        <td>{{ $mark->subject->name }}</td>
                        <td>
                            <span class="badge bg-light-info">{{ $mark->evaluation_type }}</span>
                        </td>
                        <td>
                            <span class="fw-bold">{{ $mark->value }}</span>
                        </td>
                        <td>{{ $mark->max_value ?? 20 }}</td>
                        <td>
                            @if($mark->value >= 15)
                                <span class="badge bg-success">Très bien</span>
                            @elseif($mark->value >= 12)
                                <span class="badge bg-info">Bien</span>
                            @elseif($mark->value >= 10)
                                <span class="badge bg-warning">Passable</span>
                            @else
                                <span class="badge bg-danger">Insuffisant</span>
                            @endif
                        </td>
                        <td>{{ $mark->created_at->format('d/m/Y') }}</td>
                        <td>
                            <div class="btn-group btn-group-sm">
                                <button class="btn btn-outline-warning" title="Modifier">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="btn btn-outline-danger" title="Supprimer">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center py-5 text-muted">
                            Aucune note enregistrée
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
