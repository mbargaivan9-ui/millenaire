@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-md-8">
            <h1 class="h3">
                <a href="{{ route('admin.roles.index') }}" class="btn btn-link text-muted">
                    <i class="fas fa-arrow-left me-1"></i>
                </a>
                Edit Role: {{ $role->display_name }}
            </h1>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8 offset-md-2">
            <!-- Role Information -->
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Role Information</h5>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label text-muted">Role Type</label>
                            <div class="h5 mb-0">{{ ucfirst(str_replace('_', ' ', $role->role_type)) }}</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted">Hierarchy Level</label>
                            <div class="h5 mb-0">{{ $role->hierarchy_level }}</div>
                        </div>
                    </div>
                    <hr>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label text-muted">Status</label>
                            <div class="h5 mb-0">
                                @if($role->is_active)
                                    <span class="badge bg-success">Active</span>
                                @else
                                    <span class="badge bg-secondary">Inactive</span>
                                @endif
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted">Assigned Users</label>
                            <div class="h5 mb-0">
                                <span class="badge bg-info">{{ $role->users->count() }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Edit Form -->
            <div class="card">
                <div class="card-body p-4">
                    <form action="{{ route('admin.roles.update', $role) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="mb-4">
                            <label for="display_name" class="form-label fw-bold">
                                Display Name
                            </label>
                            <input type="text" name="display_name" id="display_name" class="form-control form-control-lg" 
                                value="{{ old('display_name', $role->display_name) }}" required>
                            @error('display_name')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label for="description" class="form-label fw-bold">
                                Description
                            </label>
                            <textarea name="description" id="description" class="form-control" rows="3">{{ old('description', $role->description) }}</textarea>
                            @error('description')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-bold mb-3">
                                Permissions
                            </label>
                            <div class="row">
                                @foreach($permissions as $permission => $label)
                                <div class="col-md-6 mb-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="permissions[]" 
                                            value="{{ $permission }}" id="perm_{{ $permission }}"
                                            @if(in_array($permission, $rolePermissions)) checked @endif>
                                        <label class="form-check-label" for="perm_{{ $permission }}">
                                            {{ $label }}
                                        </label>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                            @error('permissions')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="is_active" id="is_active" value="1" 
                                    @if(old('is_active', $role->is_active)) checked @endif>
                                <label class="form-check-label" for="is_active">
                                    <strong>Active</strong>
                                </label>
                            </div>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-lg btn-primary">
                                <i class="fas fa-check me-2"></i>Save Changes
                            </button>
                            <a href="{{ route('admin.roles.index') }}" class="btn btn-lg btn-secondary">
                                <i class="fas fa-times me-2"></i>Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Assigned Users -->
            @if($role->users()->exists())
            <div class="card mt-4">
                <div class="card-header">
                    <h5 class="mb-0">Users with this Role ({{ $role->users->count() }})</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover table-striped">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($role->users as $user)
                                <tr>
                                    <td><strong>{{ $user->name }}</strong></td>
                                    <td>{{ $user->email }}</td>
                                    <td>
                                        @if($user->is_active)
                                            <span class="badge bg-success">Active</span>
                                        @else
                                            <span class="badge bg-secondary">Inactive</span>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection


