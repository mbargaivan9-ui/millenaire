@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <div class="row mb-4">
        <div class="col-md-8">
            <h1>Éditer l'Attendance</h1>
        </div>
        <div class="col-md-4 text-end">
            <a href="{{ route('attendance.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Retour
            </a>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <form action="{{ route('attendance.update', $attendance) }}" method="POST">
                @csrf @method('PUT')

                <div class="mb-3">
                    <label for="student_id" class="form-label">Étudiant</label>
                    <select class="form-control @error('student_id') is-invalid @enderror" id="student_id" name="student_id" required>
                        @foreach($students as $student)
                            <option value="{{ $student->id }}" {{ $student->id === $attendance->student_id ? 'selected' : '' }}>
                                {{ $student->user->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('student_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="status" class="form-label">Statut</label>
                    <select class="form-control @error('status') is-invalid @enderror" id="status" name="status" required>
                        <option value="present" {{ $attendance->status === 'present' ? 'selected' : '' }}>Présent</option>
                        <option value="absent" {{ $attendance->status === 'absent' ? 'selected' : '' }}>Absent</option>
                        <option value="justified" {{ $attendance->status === 'justified' ? 'selected' : '' }}>Justifié</option>
                    </select>
                    @error('status')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="reason" class="form-label">Raison</label>
                    <textarea class="form-control @error('reason') is-invalid @enderror" id="reason" name="reason" rows="3">{{ $attendance->reason }}</textarea>
                    @error('reason')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="text-end">
                    <button type="submit" class="btn btn-success">Mettre à jour</button>
                    <a href="{{ route('attendance.index') }}" class="btn btn-secondary">Annuler</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
