@extends('layouts.app')

@section('title', 'Mes Devoirs - Élève')

@section('content')
    <div class="page-title">
        <i class="fas fa-tasks"></i> Mes Devoirs et Travaux
    </div>

    <!-- Statistiques -->
    <div class="row mb-4">
        <div class="col-md-6 col-lg-3">
            <div class="stat-box">
                <i class="fas fa-hourglass-half" style="color: #f59e0b;"></i>
                <h5>En Attente</h5>
                <h3>{{ $stats['pending'] }}</h3>
                <small class="text-muted">À faire</small>
            </div>
        </div>

        <div class="col-md-6 col-lg-3">
            <div class="stat-box">
                <i class="fas fa-file-upload" style="color: #3b82f6;"></i>
                <h5>Soumis</h5>
                <h3>{{ $stats['submitted'] }}</h3>
                <small class="text-muted">Rendu</small>
            </div>
        </div>

        <div class="col-md-6 col-lg-3">
            <div class="stat-box">
                <i class="fas fa-check-circle" style="color: #10b981;"></i>
                <h5>Évalués</h5>
                <h3>{{ $stats['graded'] }}</h3>
                <small class="text-muted">Notés</small>
            </div>
        </div>

        <div class="col-md-6 col-lg-3">
            <div class="stat-box">
                <i class="fas fa-calendar-times" style="color: #ef4444;"></i>
                <h5>Dépassé</h5>
                <h3>{{ $stats['overdue'] }}</h3>
                <small class="text-muted">Retardataire</small>
            </div>
        </div>
    </div>

    <!-- Filtres -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Statut</label>
                    <select name="status" class="form-select" onchange="this.form.submit()">
                        <option value="">Tous les statuts</option>
                        <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>En attente</option>
                        <option value="in_progress" {{ request('status') === 'in_progress' ? 'selected' : '' }}>En cours</option>
                        <option value="submitted" {{ request('status') === 'submitted' ? 'selected' : '' }}>Soumis</option>
                        <option value="graded" {{ request('status') === 'graded' ? 'selected' : '' }}>Évalué</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Matière</label>
                    <select name="subject" class="form-select" onchange="this.form.submit()">
                        <option value="">Toutes les matières</option>
                        @foreach($subjects as $subject)
                            <option value="{{ $subject->id }}" {{ request('subject') == $subject->id ? 'selected' : '' }}>
                                {{ $subject->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4 d-flex align-items-end">
                    <a href="{{ route('student.assignments') }}" class="btn btn-secondary w-100">
                        <i class="fas fa-redo"></i> Réinitialiser
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Devoirs urgents -->
    @php
        $urgentAssignments = $assignments->filter(function($a) {
            return $a->status !== 'graded' && \Carbon\Carbon::parse($a->due_date)->diffInDays(now(), false) < 3;
        });
    @endphp

    @if($urgentAssignments->count())
        <div class="alert alert-warning alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-triangle"></i> <strong>Attention!</strong> Vous avez {{ $urgentAssignments->count() }} devoir(s) urgents à rendre dans les 3 prochains jours.
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- Devoirs en attente de notation -->
    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-hourglass-half"></i> Devoirs Urgents
        </div>
        <div class="card-body">
            @forelse($assignments->where('status', '!=', 'graded')->sortBy('due_date')->take(5) as $assignment)
                <div class="d-flex justify-content-between align-items-start mb-3 pb-3" style="border-bottom: 1px solid #e5e7eb;">
                    <div class="flex-grow-1">
                        <h6 class="mb-1">{{ $assignment->title }}</h6>
                        <p class="small text-muted mb-2">
                            <strong>Matière:</strong> {{ $assignment->classSubjectTeacher->subject->name }}
                            <br>
                            <strong>Professeur:</strong> {{ $assignment->classSubjectTeacher->teacher->user->name }}
                            <br>
                            <strong>Description:</strong> {{ Str::limit($assignment->description ?? 'Non fourni', 100) }}
                        </p>
                    </div>
                    <div class="text-end">
                        @php
                            $daysLeft = \Carbon\Carbon::parse($assignment->due_date)->diffInDays(now(), false);
                            if ($daysLeft >= 0) {
                                $color = $daysLeft < 3 ? 'danger' : ($daysLeft < 7 ? 'warning' : 'success');
                                $badgeText = "$daysLeft jours";
                            } else {
                                $color = 'danger';
                                $badgeText = 'Dépassé';
                            }
                        @endphp
                        <span class="badge bg-{{ $color }} p-2">{{ $badgeText }}</span>
                        <br>
                        <small class="text-muted">Échéance: {{ \Carbon\Carbon::parse($assignment->due_date)->format('d/m/Y') }}</small>
                    </div>
                </div>
            @empty
                <p class="text-muted text-center">Aucun devoir urgent</p>
            @endforelse
        </div>
    </div>

    <!-- Tous les devoirs -->
    <div class="card">
        <div class="card-header">
            <i class="fas fa-list"></i> Tous les Devoirs
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Titre</th>
                            <th>Matière</th>
                            <th>Professeur</th>
                            <th>Statut</th>
                            <th>Échéance</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($assignments as $assignment)
                            <tr>
                                <td>
                                    <strong>{{ $assignment->title }}</strong>
                                </td>
                                <td>{{ $assignment->classSubjectTeacher->subject->name }}</td>
                                <td>{{ $assignment->classSubjectTeacher->teacher->user->name }}</td>
                                <td>
                                    @php
                                        $statusBadge = [
                                            'pending' => ['warning', 'En attente'],
                                            'in_progress' => ['info', 'En cours'],
                                            'submitted' => ['primary', 'Soumis'],
                                            'graded' => ['success', 'Noté']
                                        ];
                                        [$badge, $label] = $statusBadge[$assignment->status] ?? ['secondary', $assignment->status];
                                    @endphp
                                    <span class="badge bg-{{ $badge }}">{{ $label }}</span>
                                </td>
                                <td>
                                    @php
                                        $daysLeft = \Carbon\Carbon::parse($assignment->due_date)->diffInDays(now(), false);
                                        if ($daysLeft >= 0) {
                                            $color = $daysLeft < 3 ? 'danger' : ($daysLeft < 7 ? 'warning' : 'success');
                                        } else {
                                            $color = 'danger';
                                        }
                                    @endphp
                                    <span class="badge bg-{{ $color }}">
                                        {{ \Carbon\Carbon::parse($assignment->due_date)->format('d/m/Y') }}
                                    </span>
                                </td>
                                <td>
                                    <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#assignmentModal{{ $assignment->id }}">
                                        <i class="fas fa-eye"></i> Voir
                                    </button>
                                </td>
                            </tr>

                            <!-- Modal de détails -->
                            <div class="modal fade" id="assignmentModal{{ $assignment->id }}" tabindex="-1">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title">{{ $assignment->title }}</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body">
                                            <p><strong>Matière:</strong> {{ $assignment->classSubjectTeacher->subject->name }}</p>
                                            <p><strong>Professeur:</strong> {{ $assignment->classSubjectTeacher->teacher->user->name }}</p>
                                            <p><strong>Échéance:</strong> {{ \Carbon\Carbon::parse($assignment->due_date)->format('d/m/Y H:i') }}</p>
                                            <p><strong>Statut:</strong> 
                                                <span class="badge bg-{{ $statusBadge[$assignment->status][0] ?? 'secondary' }}">
                                                    {{ $statusBadge[$assignment->status][1] ?? $assignment->status }}
                                                </span>
                                            </p>
                                            <hr>
                                            <p><strong>Description:</strong></p>
                                            <p>{{ $assignment->description ?? 'Non fournie' }}</p>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
                                            @if($assignment->status !== 'graded')
                                                <button type="button" class="btn btn-primary">
                                                    <i class="fas fa-upload"></i> Soumettre
                                                </button>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted">Aucun devoir assigné</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        $(document).ready(function() {
            $('table').DataTable({
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.10.21/i18n/French.json'
                },
                responsive: true,
                pageLength: 15
            });
        });
    </script>
@endsection
