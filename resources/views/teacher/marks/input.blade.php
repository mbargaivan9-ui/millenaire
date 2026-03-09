@extends('layouts.app')

@section('title', 'Saisie notes - ' . ($classSubjectTeacher->classe->name ?? ''))

@section('content')
    <h1 class="page-title">Saisie des notes — {{ $classSubjectTeacher->classe->name ?? '' }} / {{ $classSubjectTeacher->subject->name ?? '' }}</h1>

    <div class="card">
        <div class="card-body">
            <form id="marks-form">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Élève</th>
                            <th>Note (0-20)</th>
                            <th>Commentaire</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($students as $student)
                            <tr>
                                <td>{{ $student->user->name ?? 'N/A' }}</td>
                                <td>
                                    <input type="number" step="0.01" min="0" max="20" name="marks[{{ $student->id }}][score]" class="form-control">
                                </td>
                                <td>
                                    <input type="text" name="marks[{{ $student->id }}][comment]" class="form-control">
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                <button type="submit" class="btn btn-primary">Enregistrer</button>
            </form>
        </div>
    </div>

@endsection
