@extends('layouts.app')

@section('title', __('teacher_absences.edit_title'))

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-lg-8 mx-auto">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">@lang('teacher_absences.edit_title')</h5>
                </div>

                <div class="card-body">
                    <form method="POST" action="{{ route('admin.teacher_absences.update', $teacherAbsence) }}" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        {{-- Teacher Selection --}}
                        <div class="form-group mb-4">
                            <label for="teacher_id" class="form-label">
                                <strong>@lang('teacher_absences.teacher') <span class="text-danger">*</span></strong>
                            </label>
                            <select name="teacher_id" id="teacher_id" class="form-control @error('teacher_id') is-invalid @enderror">
                                @foreach($teachers as $teacher)
                                    <option value="{{ $teacher->id }}" 
                                        {{ (old('teacher_id') ?? $teacherAbsence->teacher_id) == $teacher->id ? 'selected' : '' }}>
                                        {{ $teacher->user->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('teacher_id')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Date --}}
                        <div class="form-group mb-4">
                            <label for="date" class="form-label">
                                <strong>@lang('teacher_absences.date') <span class="text-danger">*</span></strong>
                            </label>
                            <input type="date" name="date" id="date" class="form-control @error('date') is-invalid @enderror" 
                                value="{{ old('date', $teacherAbsence->date) }}" required>
                            @error('date')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Status --}}
                        <div class="form-group mb-4">
                            <label for="status" class="form-label">
                                <strong>@lang('teacher_absences.status') <span class="text-danger">*</span></strong>
                            </label>
                            <select name="status" id="status" class="form-control @error('status') is-invalid @enderror" required>
                                <option value="present" {{ (old('status') ?? $teacherAbsence->status) == 'present' ? 'selected' : '' }}>
                                    @lang('teacher_absences.status_present')
                                </option>
                                <option value="absent" {{ (old('status') ?? $teacherAbsence->status) == 'absent' ? 'selected' : '' }}>
                                    @lang('teacher_absences.status_absent')
                                </option>
                                <option value="late" {{ (old('status') ?? $teacherAbsence->status) == 'late' ? 'selected' : '' }}>
                                    @lang('teacher_absences.status_late')
                                </option>
                                <option value="justified" {{ (old('status') ?? $teacherAbsence->status) == 'justified' ? 'selected' : '' }}>
                                    @lang('teacher_absences.status_justified')
                                </option>
                                <option value="medical_leave" {{ (old('status') ?? $teacherAbsence->status) == 'medical_leave' ? 'selected' : '' }}>
                                    @lang('teacher_absences.status_medical_leave')
                                </option>
                                <option value="authorized_leave" {{ (old('status') ?? $teacherAbsence->status) == 'authorized_leave' ? 'selected' : '' }}>
                                    @lang('teacher_absences.status_authorized_leave')
                                </option>
                            </select>
                            @error('status')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Reason --}}
                        <div class="form-group mb-4">
                            <label for="reason" class="form-label">
                                @lang('teacher_absences.reason')
                            </label>
                            <textarea name="reason" id="reason" class="form-control @error('reason') is-invalid @enderror" 
                                rows="4" placeholder="@lang('forms.description')">{{ old('reason', $teacherAbsence->reason) }}</textarea>
                            @error('reason')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted">500 @lang('forms.characters_max')</small>
                        </div>

                        {{-- Justification Document --}}
                        <div class="form-group mb-4">
                            <label for="justification_document" class="form-label">
                                @lang('teacher_absences.justification_document')
                            </label>

                            @if($teacherAbsence->justification_document)
                                <div class="mb-3 p-3 bg-light border rounded">
                                    <div class="mb-2">
                                        <strong>@lang('forms.current_file'):</strong>
                                        <a href="{{ Storage::url($teacherAbsence->justification_document) }}" target="_blank" class="ms-2">
                                            <i class="fas fa-file"></i> @lang('forms.download')
                                        </a>
                                    </div>
                                    <small class="text-muted">@lang('forms.upload_new_to_replace')</small>
                                </div>
                            @endif

                            <input type="file" name="justification_document" id="justification_document" 
                                class="form-control @error('justification_document') is-invalid @enderror"
                                accept=".pdf,.jpg,.jpeg,.png">
                            @error('justification_document')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted">
                                @lang('forms.file_types'): PDF, JPG, PNG - @lang('forms.max_size'): 5 MB
                            </small>
                        </div>

                        {{-- Approval Status (Info) --}}
                        @if($teacherAbsence->is_approved)
                            <div class="alert alert-success mb-4" role="alert">
                                <i class="fas fa-check-circle"></i>
                                <strong>@lang('forms.approved'):</strong> 
                                {{ $teacherAbsence->approved_by }} 
                                ({{ \Carbon\Carbon::parse($teacherAbsence->approved_at)->format('d/m/Y H:i') }})
                            </div>
                        @else
                            <div class="alert alert-warning mb-4" role="alert">
                                <i class="fas fa-hourglass-half"></i>
                                <strong>@lang('forms.pending_approval')</strong>
                            </div>
                        @endif

                        {{-- Form Actions --}}
                        <div class="form-group">
                            <div class="d-grid gap-2 d-sm-flex justify-content-sm-end">
                                <a href="{{ route('admin.teacher_absences.index') }}" class="btn btn-secondary">
                                    <i class="fas fa-times"></i> @lang('forms.cancel')
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save"></i> @lang('forms.save')
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection


