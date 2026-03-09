@extends('layouts.admin')

@section('title', 'Bulletin Structure Validation - Admin')

@section('content')
<div class="container-fluid py-4">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-md-8">
            <h1 class="fw-bold">
                <i class="fas fa-check-circle text-success"></i> Bulletin Structure Validation
            </h1>
            <p class="text-muted">Review and approve OCR-extracted bulletin structures</p>
        </div>
        <div class="col-md-4 text-end">
            <a href="{{ route('admin.bulletin-structure.validation.stats') }}" class="btn btn-info">
                <i class="fas fa-chart-bar"></i> Statistics
            </a>
        </div>
    </div>

    <!-- Validation Statistics -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card border-left-primary">
                <div class="card-body">
                    <div class="h6 text-uppercase text-muted">Total Structures</div>
                    <div class="h2 font-weight-bold text-primary">{{ $structures->total() }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-left-warning">
                <div class="card-body">
                    <div class="h6 text-uppercase text-muted">Pending Review</div>
                    <div class="h2 font-weight-bold text-warning">
                        {{ $structures->where('is_verified', false)->count() }}
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-left-success">
                <div class="card-body">
                    <div class="h6 text-uppercase text-muted">Verified</div>
                    <div class="h2 font-weight-bold text-success">
                        {{ $structures->where('is_verified', true)->count() }}
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-left-info">
                <div class="card-body">
                    <div class="h6 text-uppercase text-muted">Active</div>
                    <div class="h2 font-weight-bold text-info">
                        {{ $structures->where('is_active', true)->count() }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Search</label>
                    <input type="text" name="search" class="form-control" 
                           placeholder="Structure name..." value="{{ request('search') }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Verification Status</label>
                    <select name="verified" class="form-select">
                        <option value="">-- All --</option>
                        <option value="0" {{ request('verified') === '0' ? 'selected' : '' }}>Pending</option>
                        <option value="1" {{ request('verified') === '1' ? 'selected' : '' }}>Verified</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Active Status</label>
                    <select name="active" class="form-select">
                        <option value="">-- All --</option>
                        <option value="0" {{ request('active') === '0' ? 'selected' : '' }}>Inactive</option>
                        <option value="1" {{ request('active') === '1' ? 'selected' : '' }}>Active</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Sort By</label>
                    <select name="sort" class="form-select">
                        <option value="-updated">Recent First</option>
                        <option value="name">Name (A-Z)</option>
                        <option value="confidence">Confidence</option>
                    </select>
                </div>
                <div class="col-md-2 d-flex align-items-end gap-2">
                    <button type="submit" class="btn btn-primary flex-grow-1">
                        <i class="fas fa-filter"></i> Filter
                    </button>
                    <a href="{{ route('admin.bulletin-structure.validation.index') }}" class="btn btn-secondary">
                        <i class="fas fa-redo"></i>
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Structures Table -->
    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="bg-light">
                    <tr>
                        <th class="ps-4">
                            <input type="checkbox" id="selectAll" class="form-check-input">
                        </th>
                        <th>Structure Name</th>
                        <th>Class</th>
                        <th>Created By</th>
                        <th>OCR Confidence</th>
                        <th>Status</th>
                        <th>Verified By</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($structures as $structure)
                    <tr>
                        <td class="ps-4">
                            <input type="checkbox" class="form-check-input structure-checkbox" 
                                   value="{{ $structure->id }}">
                        </td>
                        <td>
                            <div class="fw-bold">{{ $structure->name }}</div>
                            <small class="text-muted">{{ Str::limit($structure->description, 50) }}</small>
                        </td>
                        <td>{{ $structure->classe->name ?? 'N/A' }}</td>
                        <td>{{ $structure->createdBy->name ?? 'N/A' }}</td>
                        <td>
                            <div class="d-flex align-items-center">
                                <span class="fw-bold">{{ $structure->ocr_confidence }}%</span>
                                @if($structure->ocr_confidence >= 80)
                                    <span class="badge bg-success ms-2">Excellent</span>
                                @elseif($structure->ocr_confidence >= 60)
                                    <span class="badge bg-info ms-2">Good</span>
                                @else
                                    <span class="badge bg-warning ms-2">Fair</span>
                                @endif
                            </div>
                        </td>
                        <td>
                            @if($structure->is_verified)
                                <span class="badge bg-success">
                                    <i class="fas fa-check-circle"></i> Verified
                                </span>
                            @else
                                <span class="badge bg-warning">
                                    <i class="fas fa-hourglass-half"></i> Pending
                                </span>
                            @endif
                            @if($structure->is_active)
                                <span class="badge bg-info ms-1">
                                    <i class="fas fa-play-circle"></i> Active
                                </span>
                            @else
                                <span class="badge bg-secondary ms-1">Inactive</span>
                            @endif
                        </td>
                        <td>{{ $structure->verifiedBy->name ?? '--' }}</td>
                        <td>
                            <div class="btn-group btn-group-sm">
                                <a href="{{ route('admin.bulletin-structure.validation.show', $structure) }}" 
                                   class="btn btn-outline-primary" title="View">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('admin.bulletin-structure.validation.edit', $structure) }}" 
                                   class="btn btn-outline-warning" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                                @if(!$structure->is_verified)
                                    <form method="POST" 
                                          action="{{ route('admin.bulletin-structure.validation.approve', $structure) }}" 
                                          style="display:inline;">
                                        @csrf
                                        <button type="submit" class="btn btn-outline-success btn-sm" 
                                                title="Approve" onclick="return confirm('Approve this structure?')">
                                            <i class="fas fa-check"></i>
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center py-4">
                            <i class="fas fa-inbox text-muted" style="font-size: 2rem;"></i>
                            <p class="mt-2 text-muted">No structures found</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Bulk Actions -->
        <div class="card-footer bg-light" id="bulkActions" style="display:none;">
            <form method="POST" action="{{ route('admin.bulletin-structure.validation.bulk-verify') }}" class="d-flex gap-2 align-items-center">
                @csrf
                <input type="hidden" id="bulkIds" name="ids" value="">
                <select name="action" class="form-select" style="max-width: 200px;">
                    <option value="">-- Select Action --</option>
                    <option value="verify">Verify All</option>
                    <option value="activate">Activate All</option>
                    <option value="deactivate">Deactivate All</option>
                </select>
                <button type="submit" class="btn btn-primary">Apply</button>
                <button type="button" class="btn btn-secondary ms-auto" onclick="clearSelection()">Cancel</button>
            </form>
        </div>
    </div>

    <!-- Pagination -->
    <div class="d-flex justify-content-center mt-4">
        {{ $structures->links() }}
    </div>
</div>

@endsection

@section('scripts')
<script>
document.getElementById('selectAll').addEventListener('change', function() {
    const checkboxes = document.querySelectorAll('.structure-checkbox');
    checkboxes.forEach(cb => cb.checked = this.checked);
    updateBulkActions();
});

document.querySelectorAll('.structure-checkbox').forEach(cb => {
    cb.addEventListener('change', updateBulkActions);
});

function updateBulkActions() {
    const selected = document.querySelectorAll('.structure-checkbox:checked');
    const bulkActions = document.getElementById('bulkActions');
    const bulkIds = document.getElementById('bulkIds');
    
    if (selected.length > 0) {
        bulkActions.style.display = 'flex';
        bulkIds.value = Array.from(selected).map(cb => cb.value).join(',');
    } else {
        bulkActions.style.display = 'none';
    }
}

function clearSelection() {
    document.getElementById('selectAll').checked = false;
    document.querySelectorAll('.structure-checkbox').forEach(cb => cb.checked = false);
    updateBulkActions();
}
</script>
@endsection
