@extends('layouts.app')

@section('title', 'Saisie des notes')

@section('content')
    <h1 class="page-title">Saisie des notes</h1>

    <div class="card">
        <div class="card-body">
            <table class="table data-table">
                <thead>
                    <tr>
                        <th>Classe</th>
                        <th>Matière</th>
                        <th>Heures / Semaine</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                @foreach($classesTeaching as $cst)
                    <tr>
                        <td>{{ $cst->classe->name ?? 'N/A' }}</td>
                        <td>{{ $cst->subject->name ?? 'N/A' }}</td>
                        <td>{{ $cst->hours_per_week ?? '-' }}</td>
                        <td>
                            <a href="{{ route('teacher.marks.index', ['class_subject_teacher_id' => $cst->id]) }}" class="btn btn-sm btn-primary">Saisir</a>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>

@endsection
