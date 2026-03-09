@extends('layouts.app')

@section('title', __('teacher_absences.title'))

@section('content')
<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2>@lang('teacher_absences.title')</h2>
                <a href="{{ route('admin.teacher_absences.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus"></i> @lang('teacher_absences.add_new')
                </a>
            </div>

            {{-- Statistics Cards --}}
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card border-left-primary">
                        <div class="card-body">
                            <div class="text-primary font-weight-bold text-uppercase mb-1">
                                @lang('teacher_absences.total_records')
                            </div>
                            <div class="h3 mb-0">{{ $stats['total_records'] }}</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border-left-danger">
                        <div class="card-body">
                            <div class="text-danger font-weight-bold text-uppercase mb-1">
                                @lang('teacher_absences.total_absences')
                            </div>
                            <div class="h3 mb-0">{{ $stats['absences'] }}</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border-left-warning">
                        <div class="card-body">
                            <div class="text-warning font-weight-bold text-uppercase mb-1">
                                @lang('teacher_absences.total_justified')
                            </div>
                            <div class="h3 mb-0">{{ $stats['justified'] }}</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border-left-info">
                        <div class="card-body">
                            <div class="text-info font-weight-bold text-uppercase mb-1">
                                @lang('teacher_absences.pending_approval')
                            </div>
                            <div class="h3 mb-0">{{ $stats['pending'] }}</div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Filters --}}
            <div class="card mb-4">
                <div class="card-body">
                    <form method="GET" class="form-inline gap-3">
                        <div class="form-group">
                            <label for="teacher_id" class="mr-2">@lang('teacher_absences.filter_by_teacher')</label>
                            <select name="teacher_id" id="teacher_id" class="form-control">
                                <option value="">@lang('forms.all')</option>
                                @foreach($teachers as $teacher)
                                    <option value="{{ $teacher->id }}" 
                                        {{ request('teacher_id') == $teacher->id ? 'selected' : '' }}>
                                        {{ $teacher->user->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="status" class="mr-2">@lang('teacher_absences.filter_by_status')</label>
                            <select name="status" id="status" class="form-control">
                                <option value="">@lang('forms.all')</option>
                                <option value="absent" {{ request('status') == 'absent' ? 'selected' : '' }}>
                                    @lang('teacher_absences.status_absent')
                                </option>
                                <option value="justified" {{ request('status') == 'justified' ? 'selected' : '' }}>
                                    @lang('teacher_absences.status_justified')
                                </option>
                                <option value="medical_leave" {{ request('status') == 'medical_leave' ? 'selected' : '' }}>
                                    @lang('teacher_absences.status_medical_leave')
                                </option>
                                <option value="authorized_leave" {{ request('status') == 'authorized_leave' ? 'selected' : '' }}>
                                    @lang('teacher_absences.status_authorized_leave')
                                </option>
                                <option value="late" {{ request('status') == 'late' ? 'selected' : '' }}>
                                    @lang('teacher_absences.status_late')
                                </option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="start_date" class="mr-2">@lang('teacher_absences.start_date')</label>
                            <input type="date" name="start_date" id="start_date" class="form-control"
                                value="{{ request('start_date') }}">
                        </div>

                        <div class="form-group">
                            <label for="end_date" class="mr-2">@lang('teacher_absences.end_date')</label>
                            <input type="date" name="end_date" id="end_date" class="form-control"
                                value="{{ request('end_date') }}">
                        </div>

                        <button type="submit" class="btn btn-sm btn-primary">
                            <i class="fas fa-search"></i> @lang('forms.filter')
                        </button>
                        <a href="{{ route('admin.teacher_absences.index') }}" class="btn btn-sm btn-secondary">
                            @lang('forms.reset')
                        </a>
                    </form>
                </div>
            </div>

            {{-- Table --}}
            <div class="card">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th>@lang('teacher_absences.header_teacher')</th>
                                <th>@lang('teacher_absences.header_date')</th>
                                <th>@lang('teacher_absences.header_status')</th>
                                <th>@lang('teacher_absences.header_reason')</th>
                                <th>@lang('teacher_absences.header_approval')</th>
                                <th>@lang('teacher_absences.header_actions')</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($absences as $absence)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <img src="{{ $absence->teacher->user->avatar_url }}" 
                                                 alt="{{ $absence->teacher->user->name }}"
                                                 class="rounded-circle me-2" style="width: 32px; height: 32px;">
                                            <span>{{ $absence->teacher->user->name }}</span>
                                        </div>
                                    </td>
                                    <td>
                                        {{ \Carbon\Carbon::parse($absence->date)->format('d/m/Y') }}
                                    </td>
                                    <td>
                                        <span class="badge {{ $absence->getStatusClass() }}">
                                            @lang('teacher_absences.status_' . $absence->status)
                                        </span>
                                    </td>
                                    <td>
                                        <small>{{ $absence->reason ? Str::limit($absence->reason, 50) : '-' }}</small>
                                    </td>
                                    <td>
                                        @if($absence->is_approved)
                                            <span class="badge badge-success">
                                                <i class="fas fa-check"></i> @lang('forms.approved')
                                            </span>
                                        @else
                                            <span class="badge badge-warning">
                                                <i class="fas fa-hourglass-half"></i> @lang('forms.pending')
                                            </span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm" role="group">
                                            <a href="{{ route('admin.teacher_absences.edit', $absence) }}" 
                                               class="btn btn-outline-primary" title="@lang('teacher_absences.edit')">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            
                                            @if(!$absence->is_approved)
                                                <form method="POST" 
                                                      action="{{ route('admin.teacher_absences.approve', $absence) }}"
                                                      style="display:inline;">
                                                    @csrf
                                                    <button type="submit" class="btn btn-outline-success btn-sm"
                                                            title="@lang('teacher_absences.approve')"
                                                            onclick="return confirm('@lang('forms.confirm_action')')">
                                                        <i class="fas fa-check"></i>
                                                    </button>
                                                </form>

                                                <form method="POST" 
                                                      action="{{ route('admin.teacher_absences.reject', $absence) }}"
                                                      style="display:inline;">
                                                    @csrf
                                                    <button type="submit" class="btn btn-outline-danger btn-sm"
                                                            title="@lang('teacher_absences.reject')"
                                                            onclick="return confirm('@lang('forms.confirm_action')')">
                                                        <i class="fas fa-times"></i>
                                                    </button>
                                                </form>
                                            @endif

                                            <form method="POST" 
                                                  action="{{ route('admin.teacher_absences.destroy', $absence) }}"
                                                  style="display:inline;">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-outline-danger btn-sm"
                                                        title="@lang('teacher_absences.delete')"
                                                        onclick="return confirm('@lang('forms.confirm_delete')')">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-4 text-muted">
                                        @lang('teacher_absences.no_records')
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Pagination --}}
                <div class="card-footer">
                    {{ $absences->links() }}
                </div>
            </div>

            <div class="mt-4">
                <a href="{{ route('admin.teacher_absences.report') }}" class="btn btn-info">
                    <i class="fas fa-chart-bar"></i> @lang('teacher_absences.view_report')
                </a>
            </div>
        </div>
    </div>
</div>
@endsection


