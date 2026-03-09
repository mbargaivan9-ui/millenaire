@extends('layouts.app')

@section('title', 'Emploi du Temps - Élève')

@section('content')
    <div class="page-title">
        <i class="fas fa-calendar-alt"></i> Emploi du Temps
    </div>

    <!-- Infos de classe -->
    <div class="card mb-4">
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <h5>Ma Classe</h5>
                    <p class="mb-0">
                        <strong style="font-size: 1.3rem;">{{ $student->classe->name }}</strong>
                    </p>
                </div>
                <div class="col-md-6">
                    <h5>Professeur Principal</h5>
                    <p class="mb-0">
                        <strong>{{ $student->classe->profPrincipal->user->name }}</strong>
                        <br>
                        <small class="text-muted">{{ $student->classe->profPrincipal->user->email }}</small>
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Affichage de l'emploi du temps -->
    <div class="card">
        <div class="card-header">
            <i class="fas fa-clock"></i> Emploi du Temps de la Semaine
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover" style="font-size: 0.95rem;">
                    <thead>
                        <tr style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
                            <th>Heure</th>
                            <th>Lundi</th>
                            <th>Mardi</th>
                            <th>Mercredi</th>
                            <th>Jeudi</th>
                            <th>Vendredi</th>
                            <th>Samedi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $timeSlots = [];
                            $allHours = [];
                            
                            // Générer les créneaux horaires de 7h à 18h
                            for ($h = 7; $h <= 17; $h++) {
                                $allHours[] = sprintf('%02d:00', $h);
                            }
                            
                            // Organiser les cours par heure et jour
                            $scheduleByTime = [];
                            foreach($schedules as $schedule) {
                                $hour = substr($schedule->start_time, 0, 2);
                                $day = $schedule->day_of_week;
                                if(!isset($scheduleByTime[$hour])) {
                                    $scheduleByTime[$hour] = [];
                                }
                                $scheduleByTime[$hour][$day] = $schedule;
                            }
                        @endphp

                        @foreach($allHours as $hour)
                            <tr>
                                <td style="background-color: #f3f4f6; font-weight: bold;">{{ $hour }}</td>
                                @php
                                    $days = [0 => 'Monday', 1 => 'Tuesday', 2 => 'Wednesday', 3 => 'Thursday', 4 => 'Friday', 5 => 'Saturday'];
                                @endphp
                                @foreach($days as $dayCode => $dayName)
                                    <td>
                                        @if(isset($scheduleByTime[substr($hour, 0, 2)][$dayName]))
                                            @php
                                                $sched = $scheduleByTime[substr($hour, 0, 2)][$dayName];
                                            @endphp
                                            <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 10px; border-radius: 5px; font-size: 0.9rem;">
                                                <strong>{{ $sched->classSubjectTeacher->subject->name }}</strong>
                                                <br>
                                                <small>Prof: {{ $sched->classSubjectTeacher->teacher->user->name }}</small>
                                                <br>
                                                <small>Salle: {{ $sched->room }}</small>
                                                <br>
                                                <small>{{ substr($sched->start_time, 0, 5) }} - {{ substr($sched->end_time, 0, 5) }}</small>
                                            </div>
                                        @else
                                            <span class="text-muted text-center d-block">-</span>
                                        @endif
                                    </td>
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Légère dans matières de la semaine -->
    <div class="card mt-4">
        <div class="card-header">
            <i class="fas fa-book"></i> Matières de la Semaine
        </div>
        <div class="card-body">
            <div class="row">
                @php
                    $uniqueSubjects = collect($schedules)->pluck('classSubjectTeacher.subject')->unique('id');
                @endphp
                @forelse($uniqueSubjects as $subject)
                    <div class="col-md-4 mb-3">
                        <div class="card" style="border-left: 4px solid #667eea;">
                            <div class="card-body">
                                <h6 class="card-title">{{ $subject->name }}</h6>
                                <p class="card-text small text-muted">
                                    Coefficient: <strong>{{ $subject->coefficient }}</strong>
                                </p>
                                @php
                                    $subjectSchedules = $schedules->filter(function($s) use($subject) {
                                        return $s->classSubjectTeacher->subject->id === $subject->id;
                                    });
                                @endphp
                                <p class="card-text small">
                                    <strong>{{ $subjectSchedules->count() }}</strong> cours cette semaine
                                </p>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-12">
                        <p class="text-muted text-center">Aucune matière programmée</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Professeurs -->
    <div class="card mt-4">
        <div class="card-header">
            <i class="fas fa-chalkboard-user"></i> Vos Professeurs
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Matière</th>
                            <th>Professeur</th>
                            <th>Qualifications</th>
                            <th>Experience</th>
                            <th>Email</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $uniqueTeachers = collect($schedules)->map(function($s) {
                                return $s->classSubjectTeacher;
                            })->unique('teacher_id');
                        @endphp
                        @forelse($uniqueTeachers as $cst)
                            <tr>
                                <td><strong>{{ $cst->subject->name }}</strong></td>
                                <td>{{ $cst->teacher->user->name }}</td>
                                <td>{{ $cst->teacher->qualification }}</td>
                                <td>{{ $cst->teacher->years_experience }} ans</td>
                                <td>
                                    <a href="mailto:{{ $cst->teacher->user->email }}">
                                        {{ $cst->teacher->user->email }}
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted">Aucun professeur assigné</td>
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
                responsive: true
            });
        });
    </script>
@endsection
