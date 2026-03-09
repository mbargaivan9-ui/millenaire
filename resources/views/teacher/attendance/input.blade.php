@extends('layouts.app')

@section('title', 'Saisie des absences')

@section('content')
    <h1 class="page-title">Absences — {{ $classSubjectTeacher->classe->name ?? '' }} / {{ $classSubjectTeacher->subject->name ?? '' }}</h1>

    <div class="card">
        <div class="card-body">
            <form method="post" id="attendance-form">
                @csrf
                <input type="hidden" name="class_subject_teacher_id" value="{{ $classSubjectTeacher->id }}">
                <div class="mb-3">
                    <label class="form-label">Date</label>
                    <input type="date" name="date" class="form-control" value="{{ $date }}">
                </div>

                <table class="table">
                    <thead>
                        <tr>
                            <th>Élève</th>
                            <th>Statut</th>
                            <th>Raison</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($students as $student)
                            <tr>
                                <td>{{ $student->user->name ?? 'N/A' }}</td>
                                <td>
                                    <select name="attendances[{{ $student->id }}][status]" class="form-select">
                                        <option value="present">Présent</option>
                                        <option value="absent">Absent</option>
                                        <option value="late">En retard</option>
                                        <option value="justified">Justifié</option>
                                    </select>
                                </td>
                                <td>
                                    <input type="text" name="attendances[{{ $student->id }}][reason]" class="form-control">
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                <button class="btn btn-primary">Enregistrer</button>
            </form>
        </div>
    </div>

@endsection
