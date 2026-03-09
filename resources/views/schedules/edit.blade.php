@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <div class="row mb-4">
        <div class="col-md-8">
            <h1>Éditer l'Horaire</h1>
        </div>
        <div class="col-md-4 text-end">
            <a href="{{ route('schedules.index') }}" class="btn btn-secondary">Retour</a>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <form action="{{ route('schedules.update', $schedule) }}" method="POST">
                @csrf @method('PUT')

                <div class="mb-3">
                    <label for="day_of_week" class="form-label">Jour de la Semaine</label>
                    <select class="form-control @error('day_of_week') is-invalid @enderror" id="day_of_week" name="day_of_week" required>
                        <option value="monday" {{ $schedule->day_of_week === 'monday' ? 'selected' : '' }}>Lundi</option>
                        <option value="tuesday" {{ $schedule->day_of_week === 'tuesday' ? 'selected' : '' }}>Mardi</option>
                        <option value="wednesday" {{ $schedule->day_of_week === 'wednesday' ? 'selected' : '' }}>Mercredi</option>
                        <option value="thursday" {{ $schedule->day_of_week === 'thursday' ? 'selected' : '' }}>Jeudi</option>
                        <option value="friday" {{ $schedule->day_of_week === 'friday' ? 'selected' : '' }}>Vendredi</option>
                        <option value="saturday" {{ $schedule->day_of_week === 'saturday' ? 'selected' : '' }}>Samedi</option>
                    </select>
                    @error('day_of_week')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="start_time" class="form-label">Heure de Début</label>
                    <input type="time" class="form-control @error('start_time') is-invalid @enderror" id="start_time" name="start_time" value="{{ $schedule->start_time }}" required>
                    @error('start_time')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="end_time" class="form-label">Heure de Fin</label>
                    <input type="time" class="form-control @error('end_time') is-invalid @enderror" id="end_time" name="end_time" value="{{ $schedule->end_time }}" required>
                    @error('end_time')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="room_number" class="form-label">Numéro de Salle</label>
                    <input type="text" class="form-control @error('room_number') is-invalid @enderror" id="room_number" name="room_number" value="{{ $schedule->room_number }}">
                    @error('room_number')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="text-end">
                    <button type="submit" class="btn btn-success">Mettre à jour</button>
                    <a href="{{ route('schedules.index') }}" class="btn btn-secondary">Annuler</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
