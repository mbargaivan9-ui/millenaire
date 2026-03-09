@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-md-8">
            <h1 class="h3">
                <a href="{{ route('admin.roles.index') }}" class="btn btn-link text-muted">
                    <i class="fas fa-arrow-left me-1"></i>
                </a>
                Create New Administrative Role
            </h1>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8 offset-md-2">
            <div class="card">
                <div class="card-body p-4">
                    <form action="{{ route('admin.roles.store') }}" method="POST">
                        @csrf

                        <div class="mb-4">
                            <label for="role_type" class="form-label fw-bold">
                                Role Type
                            </label>
                            <select name="role_type" id="role_type" class="form-select form-select-lg" required>
                                <option value="">-- Select Role Type --</option>
                                @foreach($roleTypes as $value => $label)
                                    <option value="{{ $value }}">{{ $label }}</option>
                                @endforeach
                            </select>
                            @error('role_type')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label for="display_name" class="form-label fw-bold">
                                Display Name
                            </label>
                            <input type="text" name="display_name" id="display_name" class="form-control form-control-lg" 
                                placeholder="e.g., Senior Censeur" value="{{ old('display_name') }}" required>
                            @error('display_name')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label for="description" class="form-label fw-bold">
                                Description
                            </label>
                            <textarea name="description" id="description" class="form-control" rows="3" 
                                placeholder="Describe the purpose and responsibilities of this role...">{{ old('description') }}</textarea>
                            @error('description')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-bold mb-3">
                                Assign Permissions
                            </label>
                            <div class="row">
                                @foreach($permissions as $permission => $label)
                                <div class="col-md-6 mb-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="permissions[]" 
                                            value="{{ $permission }}" id="perm_{{ $permission }}"
                                            @if(in_array($permission, old('permissions', []))) checked @endif>
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
                                    @if(old('is_active', true)) checked @endif>
                                <label class="form-check-label" for="is_active">
                                    <strong>Active (Role is currently in use)</strong>
                                </label>
                            </div>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-lg btn-primary">
                                <i class="fas fa-check me-2"></i>Create Role
                            </button>
                            <a href="{{ route('admin.roles.index') }}" class="btn btn-lg btn-secondary">
                                <i class="fas fa-times me-2"></i>Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Information Panel -->
            <div class="card mt-4 bg-light">
                <div class="card-body">
                    <h5 class="card-title">
                        <i class="fas fa-info-circle me-2"></i>Role Hierarchy
                    </h5>
                    <p class="mb-1">
                        <strong>Administrator:</strong> Full system access, highest priority
                    </p>
                    <p class="mb-1">
                        <strong>Censeur:</strong> Academic supervisor, validates report cards and manages classes
                    </p>
                    <p class="mb-1">
                        <strong>Intendant:</strong> Financial manager, manages school finances and budgets
                    </p>
                    <p class="mb-1">
                        <strong>Secrétaire:</strong> Administrative secretary, manages documents and records
                    </p>
                    <p class="mb-0">
                        <strong>Surveillant Général:</strong> General supervisor, oversees student discipline and attendance
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection


