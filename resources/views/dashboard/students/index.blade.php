@extends('layouts.app')

@section('title', 'Gestion des Élèves')

@section('content')
<div class="container-fluid">
    
    <div class="page-header mb-4">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h1 class="fw-bold text-dark">Gestion des Élèves</h1>
                <p class="text-muted">{{ $students->count() }} élèves inscrit(s)</p>
            </div>
            <a href="{{ route('students.create') }}" class="btn btn-primary">
                <i class="fas fa-plus me-2"></i> Nouvel Élève
            </a>
        </div>
    </div>

    <!-- Filtres -->
    <div class="card mb-4 border-0">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-3">
                    <input type="text" name="search" class="form-control" placeholder="Rechercher..." 
                           value="{{ request('search') }}">
                </div>
                <div class="col-md-3">
                    <select name="class" class="form-select">
                        <option value="">Toutes les classes</option>
                        @foreach($classes as $class)
                        <option value="{{ $class->id }}" @selected(request('class') == $class->id)>
                            {{ $class->name }}
                        </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <select name="status" class="form-select">
                        <option value="">Tous les statuts</option>
                        <option value="active" @selected(request('status') == 'active')>Actif</option>
                        <option value="inactive" @selected(request('status') == 'inactive')>Inactif</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-outline-primary w-100">
                        <i class="fas fa-filter me-2"></i> Filtrer
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Tableau des élèves -->
    <div class="card border-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Nom</th>
                        <th>Classe</th>
                        <th>Numéro Matricule</th>
                        <th>Email</th>
                        <th>Présence</th>
                        <th>Statut</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($students as $student)
                    <tr>
                        <td>
                            <div class="d-flex align-items-center">
                                <img src="{{ $student->user->avatar_url ?? asset('assets/avatars/default.png') }}" 
                                     alt="{{ $student->user->name }}" class="avatar-sm rounded-circle me-2">
                                <div>
                                    <h6 class="mb-0 fw-bold">{{ $student->user->name }}</h6>
                                    <small class="text-muted">ID: {{ $student->id }}</small>
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="badge bg-light-primary">{{ $student->classe->name ?? 'N/A' }}</span>
                        </td>
                        <td>{{ $student->registration_number ?? '-' }}</td>
                        <td>{{ $student->user->email }}</td>
                        <td>
                            <div class="progress" style="height: 6px;">
                                <div class="progress-bar bg-success" role="progressbar" 
                                     style="width: {{ $student->getAttendanceRate() }}%"></div>
                            </div>
                            <small>{{ $student->getAttendanceRate() }}%</small>
                        </td>
                        <td>
                            @if($student->status === 'active')
                                <span class="badge bg-success">Actif</span>
                            @else
                                <span class="badge bg-secondary">Inactif</span>
                            @endif
                        </td>
                        <td>
                            <div class="btn-group btn-group-sm">
                                <a href="{{ route('students.show', $student->id) }}" class="btn btn-outline-primary" title="Voir">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('students.edit', $student->id) }}" class="btn btn-outline-secondary" title="Modifier">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <button class="btn btn-outline-danger" title="Supprimer" onclick="confirm('Êtes-vous sûr?') && this.form.submit()">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-5 text-muted">
                            Aucun élève trouvé
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Pagination -->
    @if($students->hasPages())
    <div class="d-flex justify-content-center mt-4">
        {{ $students->links() }}
    </div>
    @endif

</div>
@endsection
