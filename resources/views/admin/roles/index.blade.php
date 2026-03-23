@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-md-6">
            <h1 class="h3">
                <i class="fas fa-shield-alt me-2"></i>Administrative Roles Management
            </h1>
        </div>
        <div class="col-md-6 text-end">
            <a href="{{ route('admin.roles.create') }}" class="btn btn-primary">
                <i class="fas fa-plus me-2"></i>Create New Role
            </a>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4 g-3">
        <div class="col-md-2">
            <div class="card kpi-card shadow-sm h-100 text-center border-0">
                <div class="card-body">
                    <div class="h4 mb-0">{{ $statistics['total_roles'] }}</div>
                    <small class="text-muted">Total Roles</small>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card kpi-card shadow-sm h-100 text-center border-0">
                <div class="card-body">
                    <div class="h4 mb-0">{{ $statistics['total_admins'] }}</div>
                    <small class="text-muted">Admins</small>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card kpi-card shadow-sm h-100 text-center border-0">
                <div class="card-body">
                    <div class="h4 mb-0">{{ $statistics['censeur_count'] }}</div>
                    <small class="text-muted">Censeurs</small>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card kpi-card shadow-sm h-100 text-center border-0">
                <div class="card-body">
                    <div class="h4 mb-0">{{ $statistics['intendant_count'] }}</div>
                    <small class="text-muted">Intendants</small>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card kpi-card shadow-sm h-100 text-center border-0">
                <div class="card-body">
                    <div class="h4 mb-0">{{ $statistics['secretaire_count'] }}</div>
                    <small class="text-muted">Secrétaires</small>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card kpi-card shadow-sm h-100 text-center border-0">
                <div class="card-body">
                    <div class="h4 mb-0">{{ $statistics['surveillant_count'] }}</div>
                    <small class="text-muted">Surveillants</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Roles Table -->
    <div class="card kpi-card shadow-sm h-100 border-0">
        <div class="card-header">
            <h5 class="mb-0">Administrative Roles</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover table-striped">
                    <thead>
                        <tr>
                            <th>Role Name</th>
                            <th>Type</th>
                            <th>Hierarchy Level</th>
                            <th>Assigned Users</th>
                            <th>Description</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($roles as $role)
                        <tr>
                            <td>
                                <strong>{{ $role->display_name }}</strong>
                            </td>
                            <td>
                                <span class="badge bg-info">{{ ucfirst(str_replace('_', ' ', $role->role_type)) }}</span>
                            </td>
                            <td>
                                <span class="badge bg-secondary">Level {{ $role->hierarchy_level }}</span>
                            </td>
                            <td>
                                <span class="badge bg-primary">{{ $role->users->count() }}</span>
                            </td>
                            <td>
                                <small>{{ Str::limit($role->description, 50) }}</small>
                            </td>
                            <td>
                                @if($role->is_active)
                                    <span class="badge bg-success">Active</span>
                                @else
                                    <span class="badge bg-secondary">Inactive</span>
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('admin.roles.edit', $role) }}" class="btn btn-sm btn-primary">
                                    <i class="fas fa-edit"></i>
                                </a>
                                @if(!$role->users()->exists())
                                <form action="{{ route('admin.roles.destroy', $role) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Delete this role?')">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center py-4 text-muted">
                                No administrative roles found. <a href="{{ route('admin.roles.create') }}">Create one</a>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Role Details Panel -->
    <div class="card mt-4">
        <div class="card-header bg-light">
            <h5 class="mb-0">Role Hierarchy</h5>
        </div>
        <div class="card-body">
            <div class="row">
                @forelse($roles->sortBy('hierarchy_level') as $role)
                <div class="col-md-4 mb-3">
                    <div class="card border-secondary">
                        <div class="card-header">
                            <h6 class="mb-0">{{ $role->display_name }}</h6>
                        </div>
                        <div class="card-body">
                            <small class="text-muted">
                                <strong>Type:</strong> {{ ucfirst(str_replace('_', ' ', $role->role_type)) }}
                            </small>
                            <hr>
                            <small>
                                <strong>Permissions:</strong>
                                @php
                                    $perms = json_decode($role->permissions, true) ?? [];
                                @endphp
                                @if(count($perms) > 0)
                                    <ul class="mt-2">
                                        @foreach($perms as $perm)
                                            <li>{{ ucfirst(str_replace('_', ' ', $perm)) }}</li>
                                        @endforeach
                                    </ul>
                                @else
                                    <p class="text-muted mt-2">No permissions assigned</p>
                                @endif
                            </small>
                        </div>
                    </div>
                </div>
                @empty
                @endforelse
            </div>
        </div>
    </div>
</div>

<style>
.border-left-primary {
    border-left: 4px solid #007bff;
}

.border-left-success {
    border-left: 4px solid #28a745;
}

.border-left-warning {
    border-left: 4px solid #ffc107;
}

.border-left-info {
    border-left: 4px solid #17a2b8;
}

.border-left-secondary {
    border-left: 4px solid #6c757d;
}

.border-left-dark {
    border-left: 4px solid #343a40;
}
</style>
@endsection


