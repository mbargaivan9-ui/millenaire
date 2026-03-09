@extends('layouts.app')

@section('title', 'Mes Notes - Élève')

@section('content')
    <div class="page-title">
        <i class="fas fa-star"></i> Mes Notes
    </div>

    <!-- Filtres -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Trimestre</label>
                    <select name="term" class="form-select" onchange="this.form.submit()">
                        <option value="">Tous les trimestres</option>
                        <option value="Term1" {{ request('term') === 'Term1' ? 'selected' : '' }}>Trimestre 1</option>
                        <option value="Term2" {{ request('term') === 'Term2' ? 'selected' : '' }}>Trimestre 2</option>
                        <option value="Term3" {{ request('term') === 'Term3' ? 'selected' : '' }}>Trimestre 3</option>
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
                    <a href="{{ route('student.marks') }}" class="btn btn-secondary w-100">
                        <i class="fas fa-redo"></i> Réinitialiser
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Statistiques -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="stat-box">
                <i class="fas fa-square-root-alt" style="color: #667eea;"></i>
                <h5>Moyenne Générale</h5>
                <h3>{{ number_format($averageScore, 2) }}/20</h3>
                <small class="text-muted">Toutes notes</small>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stat-box">
                <i class="fas fa-arrow-up" style="color: #10b981;"></i>
                <h5>Meilleure Note</h5>
                <h3>{{ $bestScore }}/20</h3>
                <small class="text-muted">Maximum</small>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stat-box">
                <i class="fas fa-arrow-down" style="color: #ef4444;"></i>
                <h5>Plus Faible Note</h5>
                <h3>{{ $lowestScore }}/20</h3>
                <small class="text-muted">Minimum</small>
            </div>
        </div>
    </div>

    <!-- Notes par matière -->
    <div class="card">
        <div class="card-header">
            <i class="fas fa-list"></i> Détail des Notes
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Matière</th>
                            <th>Professeur</th>
                            <th>Type</th>
                            <th>Trimestre</th>
                            <th>Séquence</th>
                            <th>Note</th>
                            <th>Coefficient</th>
                            <th>Statut</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($marks as $mark)
                            <tr>
                                <td>
                                    <strong>{{ $mark->classSubjectTeacher->subject->name }}</strong>
                                </td>
                                <td>{{ $mark->classSubjectTeacher->teacher->user->name }}</td>
                                <td>
                                    @php
                                        $typeLabel = [
                                            'Test' => 'Test',
                                            'Exam' => 'Examen',
                                            'Assignment' => 'Devoir',
                                            'Project' => 'Projet'
                                        ];
                                    @endphp
                                    <span class="badge bg-info">{{ $typeLabel[$mark->evaluation_type] ?? $mark->evaluation_type }}</span>
                                </td>
                                <td>{{ str_replace('Term', 'T', $mark->term) }}</td>
                                <td>{{ $mark->sequence }}</td>
                                <td>
                                    @php
                                        $score = $mark->score;
                                        $color = $score >= 15 ? 'success' : ($score >= 12 ? 'info' : ($score >= 10 ? 'warning' : 'danger'));
                                    @endphp
                                    <span class="badge bg-{{ $color }} p-2">{{ $score }}/20</span>
                                </td>
                                <td>{{ $mark->classSubjectTeacher->subject->coefficient }}</td>
                                <td>
                                    @if($mark->score >= 15)
                                        <span class="badge bg-success">Excellent</span>
                                    @elseif($mark->score >= 12)
                                        <span class="badge bg-info">Très Bien</span>
                                    @elseif($mark->score >= 10)
                                        <span class="badge bg-warning">Bien</span>
                                    @else
                                        <span class="badge bg-danger">À Améliorer</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center text-muted">Aucune note enregistrée</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Moyenne par matière -->
    @if($subjectAverages->count())
        <div class="card mt-4">
            <div class="card-header">
                <i class="fas fa-chart-bar"></i> Moyenne par Matière
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Matière</th>
                                <th>Nombre de Notes</th>
                                <th>Moyenne</th>
                                <th>Statut</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($subjectAverages as $avg)
                                <tr>
                                    <td><strong>{{ $avg->subject_name }}</strong></td>
                                    <td>{{ $avg->count }}</td>
                                    <td>
                                        <strong>{{ number_format($avg->average, 2) }}/20</strong>
                                    </td>
                                    <td>
                                        @php
                                            $average = $avg->average;
                                            $color = $average >= 15 ? 'success' : ($average >= 12 ? 'info' : ($average >= 10 ? 'warning' : 'danger'));
                                        @endphp
                                        <span class="badge bg-{{ $color }}">
                                            @if($average >= 15)
                                                Excellent
                                            @elseif($average >= 12)
                                                Très Bien
                                            @elseif($average >= 10)
                                                Bien
                                            @else
                                                À Améliorer
                                            @endif
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif
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
