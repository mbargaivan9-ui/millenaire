@extends('layouts.app')

@section('title', 'Mes Absences - Élève')

@section('content')
    <div class="page-title">
        <i class="fas fa-calendar-check"></i> Mes Absences
    </div>

    <!-- Statistiques d'absences -->
    <div class="row mb-4">
        <div class="col-md-6 col-lg-3">
            <div class="stat-box">
                <i class="fas fa-calendar-x" style="color: #ef4444;"></i>
                <h5>Absences Non Justifiées</h5>
                <h3>{{ $stats['unjustified'] }}</h3>
                <small class="text-muted">Total</small>
            </div>
        </div>

        <div class="col-md-6 col-lg-3">
            <div class="stat-box">
                <i class="fas fa-check-circle" style="color: #10b981;"></i>
                <h5>Absences Justifiées</h5>
                <h3>{{ $stats['justified'] }}</h3>
                <small class="text-muted">Total</small>
            </div>
        </div>

        <div class="col-md-6 col-lg-3">
            <div class="stat-box">
                <i class="fas fa-hourglass-end" style="color: #f59e0b;"></i>
                <h5>Retards</h5>
                <h3>{{ $stats['late'] }}</h3>
                <small class="text-muted">Total</small>
            </div>
        </div>

        <div class="col-md-6 col-lg-3">
            <div class="stat-box">
                <i class="fas fa-eye-slash" style="color: #667eea;"></i>
                <h5>Taux de Présence</h5>
                <h3>{{ number_format($stats['attendance_rate'], 1) }}%</h3>
                <small class="text-muted">Moyenne</small>
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
                        <option value="present" {{ request('status') === 'present' ? 'selected' : '' }}>Présent</option>
                        <option value="absent" {{ request('status') === 'absent' ? 'selected' : '' }}>Absent</option>
                        <option value="late" {{ request('status') === 'late' ? 'selected' : '' }}>En retard</option>
                        <option value="justified" {{ request('status') === 'justified' ? 'selected' : '' }}>Justifié</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Période</label>
                    <select name="period" class="form-select" onchange="this.form.submit()">
                        <option value="">Toutes les périodes</option>
                        <option value="week" {{ request('period') === 'week' ? 'selected' : '' }}>Cette semaine</option>
                        <option value="month" {{ request('period') === 'month' ? 'selected' : '' }}>Ce mois</option>
                        <option value="term" {{ request('period') === 'term' ? 'selected' : '' }}>Ce trimestre</option>
                    </select>
                </div>
                <div class="col-md-4 d-flex align-items-end">
                    <a href="{{ route('student.attendance') }}" class="btn btn-secondary w-100">
                        <i class="fas fa-redo"></i> Réinitialiser
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Tableau des absences -->
    <div class="card">
        <div class="card-header">
            <i class="fas fa-list"></i> Historique des Absences
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Statut</th>
                            <th>Raison</th>
                            <th>Matière</th>
                            <th>Justifié</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($attendances as $attendance)
                            <tr>
                                <td>
                                    <strong>{{ $attendance->date->format('d/m/Y') }}</strong>
                                    <br>
                                    <small class="text-muted">{{ $attendance->date->translatedFormat('l') }}</small>
                                </td>
                                <td>
                                    @php
                                        $statusBadge = [
                                            'present' => ['success', 'Présent'],
                                            'absent' => ['danger', 'Absent'],
                                            'late' => ['warning', 'En retard'],
                                            'justified' => ['info', 'Justifié']
                                        ];
                                        [$badge, $label] = $statusBadge[$attendance->status] ?? ['secondary', $attendance->status];
                                    @endphp
                                    <span class="badge bg-{{ $badge }}">{{ $label }}</span>
                                </td>
                                <td>
                                    @if($attendance->reason)
                                        <span title="{{ $attendance->reason }}">
                                            {{ Str::limit($attendance->reason, 30) }}
                                        </span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    @if(isset($attendance->classSubjectTeacher))
                                        {{ $attendance->classSubjectTeacher->subject->name }}
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    @if($attendance->status === 'justified' || $attendance->reason)
                                        <i class="fas fa-check-circle" style="color: #10b981;"></i> Oui
                                    @else
                                        <i class="fas fa-times-circle" style="color: #ef4444;"></i> Non
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted">Aucune absence enregistrée</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Graphique de présence -->
    <div class="card mt-4">
        <div class="card-header">
            <i class="fas fa-chart-pie"></i> Résumé des Absences
        </div>
        <div class="card-body">
            <canvas id="attendanceChart" height="80"></canvas>
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

            // Graphique pie
            var ctx = document.getElementById('attendanceChart').getContext('2d');
            var chart = new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: ['Présents', 'Absents', 'En retard', 'Justifiés'],
                    datasets: [{
                        data: [
                            {{ $stats['present'] }},
                            {{ $stats['absent'] }},
                            {{ $stats['late'] }},
                            {{ $stats['justified'] }}
                        ],
                        backgroundColor: ['#10b981', '#ef4444', '#f59e0b', '#3b82f6'],
                        borderColor: '#fff',
                        borderWidth: 2
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    plugins: {
                        legend: {
                            position: 'bottom'
                        }
                    }
                }
            });
        });
    </script>
@endsection
