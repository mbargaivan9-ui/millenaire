@extends('layouts.app')
@section('title', 'Exporter Emploi du Temps')
@section('content')
<div class="container-fluid py-4" style="max-width:600px">
    <h1 class="h4 mb-4">Exporter Emploi du Temps</h1>
    <div class="card shadow-sm">
        <div class="card-body">
            <form method="GET" action="{{ route('schedules.export') }}">
                <div class="mb-3">
                    <label class="form-label">Classe</label>
                    <select name="class_id" class="form-select">
                        <option value="">Toutes les classes</option>
                        @foreach($classes ?? [] as $class)
                        <option value="{{ $class->id }}">{{ $class->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Format</label>
                    <select name="format" class="form-select">
                        <option value="pdf">PDF</option>
                        <option value="excel">Excel</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary"><i class="bi bi-download me-1"></i>Télécharger</button>
            </form>
        </div>
    </div>
</div>
@endsection
