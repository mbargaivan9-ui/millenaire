@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-md-8">
            <h1 class="h3">
                <a href="{{ route('admin.roles.index') }}" class="btn btn-link text-muted">
                    <i class="fas fa-arrow-left me-1"></i>
                </a>
                Role: {{ $role->display_name }}
            </h1>
        </div>
        <div class="col-md-4 text-end">
            <a href="{{ route('admin.roles.edit', $role) }}" class="btn btn-warning">
                <i class="fas fa-edit me-1"></i>Edit
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-md-4">
            <!-- Role Summary -->
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Role Information</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="text-muted small">Role Type</label>
                        <div class="h6 mb-0">
                            <span class="badge bg-info">{{ ucfirst(str_replace('_', ' ', $role->code)) }}</span>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="text-muted small">Display Name</label>
                        <div class="h6 mb-0">{{ $role->name }}</div>
                    </div>

                    <div class="mb-3">
                        <label class="text-muted small">Hierarchy Level</label>
                        <div class="h6 mb-0">
                            {{ $role->hierarchy_level }}
                            <small class="text-muted">(Higher = More Authority)</small>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="text-muted small">Status</label>
                        <div>
                            @if($role->is_active)
                                <span class="badge bg-success">Active</span>
                            @else
                                <span class="badge bg-secondary">Inactive</span>
                            @endif
                        </div>
                    </div>

                    @if($role->description)
                    <div>
                        <label class="text-muted small">Description</label>
                        <p class="small">{{ $role->description }}</p>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Special Capabilities -->
            <div class="card">
                <div class="card-header bg-secondary text-white">
                    <h5 class="mb-0">Capabilities</h5>
                </div>
                <div class="card-body">
                    <div class="mb-2">
                        <i class="fas {{ $role->can_validate_bulletins ? 'fa-check text-success' : 'fa-times text-danger' }} me-2"></i>
                        <small>Validate Bulletins</small>
                    </div>
                    <div class="mb-2">
                        <i class="fas {{ $role->can_manage_assignments ? 'fa-check text-success' : 'fa-times text-danger' }} me-2"></i>
                        <small>Manage Assignments</small>
                    </div>
                    <div class="mb-2">
                        <i class="fas {{ $role->can_manage_finances ? 'fa-check text-success' : 'fa-times text-danger' }} me-2"></i>
                        <small>Manage Finances</small>
                    </div>
                    <div>
                        <i class="fas {{ $role->can_generate_schedules ? 'fa-check text-success' : 'fa-times text-danger' }} me-2"></i>
                        <small>Generate Schedules</small>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <!-- Permissions -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Assigned Permissions</h5>
                </div>
                <div class="card-body">
                    @php
                        $permissions = $role->getPermissionsArray();
                        $allPermissions = [
                            'manage_users' => 'Manage Users',
                            'manage_classes' => 'Manage Classes',
                            'manage_teachers' => 'Manage Teachers',
                            'manage_assignments' => 'Manage Teacher Assignments',
                            'manage_finance' => 'Manage Finance',
                            'manage_fees' => 'Manage Fees',
                            'manage_payments' => 'Manage Payments',
                            'manage_reports' => 'Generate Reports',
                            'manage_announcements' => 'Manage Announcements',
                            'manage_settings' => 'Manage Settings',
                            'view_audit_logs' => 'View Audit Logs',
                            'manage_admin_roles' => 'Manage Admin Roles',
                        ];
                    @endphp

                    @if(count($permissions) > 0)
                        <div class="row">
                            @foreach($permissions as $perm)
                                @if(isset($allPermissions[$perm]))
                                <div class="col-md-6 mb-2">
                                    <div class="form-check">
                                        <div class="form-check-label">
                                            <i class="fas fa-check text-success me-2"></i>{{ $allPermissions[$perm] }}
                                        </div>
                                    </div>
                                </div>
                                @endif
                            @endforeach
                        </div>
                    @else
                        <div class="alert alert-info mb-0">
                            <i class="fas fa-info-circle me-2"></i>No permissions assigned
                        </div>
                    @endif
                </div>
            </div>

            <!-- Assigned Users -->
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Users with this Role ({{ $role->users->count() }})</h5>
                    <button class="btn btn-sm btn-primary" id="addUserBtn">
                        <i class="fas fa-plus me-1"></i>Add User
                    </button>
                </div>
                <div class="card-body">
                    @if($role->users()->exists())
                        <div class="table-responsive">
                            <table class="table table-hover table-striped">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Email</th>
                                        <th>Assigned At</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($role->users as $user)
                                    <tr>
                                        <td><strong>{{ $user->name }}</strong></td>
                                        <td>{{ $user->email }}</td>
                                        <td><small>{{ $user->pivot->assigned_at->format('d/m/Y H:i') ?? 'N/A' }}</small></td>
                                        <td>
                                            @if($user->is_active)
                                                <span class="badge bg-success">Active</span>
                                            @else
                                                <span class="badge bg-secondary">Inactive</span>
                                            @endif
                                        </td>
                                        <td>
                                            <form action="{{ route('admin.roles.remove-user', $role) }}" method="POST" class="d-inline">
                                                @csrf
                                                <input type="hidden" name="user_id" value="{{ $user->id }}">
                                                <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Remove this user from the role?')">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="alert alert-info mb-0">
                            <i class="fas fa-user-slash me-2"></i>No users assigned to this role yet
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add User Modal -->
<div class="modal fade" id="addUserModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add User to Role</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="assignUserForm" action="{{ route('admin.roles.assign-user', $role) }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="userSelect" class="form-label">Select User</label>
                        <select name="user_id" id="userSelect" class="form-select" required>
                            <option value="">Loading users...</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Assign User</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
document.getElementById('addUserBtn')?.addEventListener('click', function() {
    const modal = new bootstrap.Modal(document.getElementById('addUserModal'));
    loadAvailableUsers();
    modal.show();
});

function loadAvailableUsers() {
    fetch('{{ route('admin.roles.available-users', $role) }}')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const select = document.getElementById('userSelect');
                select.innerHTML = '<option value="">-- Select User --</option>';
                data.data.forEach(user => {
                    const option = document.createElement('option');
                    option.value = user.id;
                    option.textContent = user.name + ' (' + user.email + ')';
                    select.appendChild(option);
                });
            }
        })
        .catch(error => console.error('Error:', error));
}

document.getElementById('assignUserForm')?.addEventListener('submit', function(e) {
    e.preventDefault();
    const userId = document.getElementById('userSelect').value;
    
    if (!userId) {
        alert('Please select a user');
        return;
    }

    fetch('{{ route('admin.roles.assign-user', $role) }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
        },
        body: JSON.stringify({ user_id: userId })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(data.message);
            bootstrap.Modal.getInstance(document.getElementById('addUserModal')).hide();
            location.reload();
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred');
    });
});
</script>
@endsection


