@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <div class="row mb-4">
        <div class="col-md-8">
            <h1>Ajouter une Attendance</h1>
        </div>
        <div class="col-md-4 text-end">
            <a href="{{ route('attendance.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Retour
            </a>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <form action="{{ route('attendance.store') }}" method="POST">
                @csrf

                <div class="mb-3">
                    <label for="student_id" class="form-label">Étudiant</label>
                    <select class="form-control @error('student_id') is-invalid @enderror" id="student_id" name="student_id" required>
                        <option value="">Sélectionner un étudiant</option>
                        @foreach($students as $student)
                            <option value="{{ $student->id }}">{{ $student->user->name }}</option>
                        @endforeach
                    </select>
                    @error('student_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="class_subject_teacher_id" class="form-label">Classe/Matière/Enseignant</label>
                    <select class="form-control @error('class_subject_teacher_id') is-invalid @enderror" id="class_subject_teacher_id" name="class_subject_teacher_id" required>
                        <option value="">Sélectionner</option>
                        @foreach($classSubjectTeachers as $cst)
                            <option value="{{ $cst->id }}">
                                {{ $cst->classe->name }} - {{ $cst->subject->name }} - {{ $cst->teacher->user->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('class_subject_teacher_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="date" class="form-label">Date</label>
                    <input type="date" class="form-control @error('date') is-invalid @enderror" id="date" name="date" required>
                    @error('date')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="status" class="form-label">Statut</label>
                    <select class="form-control @error('status') is-invalid @enderror" id="status" name="status" required>
                        <option value="">Sélectionner le statut</option>
                        <option value="present">Présent</option>
                        <option value="absent">Absent</option>
                        <option value="justified">Justifié</option>
                    </select>
                    @error('status')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="reason" class="form-label">Raison (optionnel)</label>
                    <textarea class="form-control @error('reason') is-invalid @enderror" id="reason" name="reason" rows="3"></textarea>
                    @error('reason')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="text-end">
                    <button type="submit" class="btn btn-success">Enregistrer</button>
                    <a href="{{ route('attendance.index') }}" class="btn btn-secondary">Annuler</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
