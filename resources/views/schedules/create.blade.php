@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <div class="row mb-4">
        <div class="col-md-8">
            <h1>Ajouter un Horaire</h1>
        </div>
        <div class="col-md-4 text-end">
            <a href="{{ route('schedules.index') }}" class="btn btn-secondary">Retour</a>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <form action="{{ route('schedules.store') }}" method="POST">
                @csrf

                <div class="mb-3">
                    <label for="class_id" class="form-label">Classe</label>
                    <select class="form-control @error('class_id') is-invalid @enderror" id="class_id" name="class_id" required>
                        <option value="">Sélectionner une classe</option>
                        @foreach($classes as $class)
                            <option value="{{ $class->id }}">{{ $class->name }}</option>
                        @endforeach
                    </select>
                    @error('class_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="class_subject_teacher_id" class="form-label">Matière/Enseignant</label>
                    <select class="form-control @error('class_subject_teacher_id') is-invalid @enderror" id="class_subject_teacher_id" name="class_subject_teacher_id" required>
                        <option value="">Sélectionner</option>
                        @foreach($classSubjectTeachers as $cst)
                            <option value="{{ $cst->id }}">
                                {{ $cst->subject->name }} - {{ $cst->teacher->user->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('class_subject_teacher_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="day_of_week" class="form-label">Jour de la Semaine</label>
                    <select class="form-control @error('day_of_week') is-invalid @enderror" id="day_of_week" name="day_of_week" required>
                        <option value="">Sélectionner</option>
                        <option value="monday">Lundi</option>
                        <option value="tuesday">Mardi</option>
                        <option value="wednesday">Mercredi</option>
                        <option value="thursday">Jeudi</option>
                        <option value="friday">Vendredi</option>
                        <option value="saturday">Samedi</option>
                    </select>
                    @error('day_of_week')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="start_time" class="form-label">Heure de Début</label>
                    <input type="time" class="form-control @error('start_time') is-invalid @enderror" id="start_time" name="start_time" required>
                    @error('start_time')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="end_time" class="form-label">Heure de Fin</label>
                    <input type="time" class="form-control @error('end_time') is-invalid @enderror" id="end_time" name="end_time" required>
                    @error('end_time')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="room_number" class="form-label">Numéro de Salle</label>
                    <input type="text" class="form-control @error('room_number') is-invalid @enderror" id="room_number" name="room_number">
                    @error('room_number')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="text-end">
                    <button type="submit" class="btn btn-success">Enregistrer</button>
                    <a href="{{ route('schedules.index') }}" class="btn btn-secondary">Annuler</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
