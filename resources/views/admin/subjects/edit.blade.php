@extends('layouts.app')
@section('title', app()->getLocale() === 'fr' ? 'Modifier une Matière' : 'Edit Subject')

@section('content')
@include('admin.subjects.form')
@endsection

          @error('name_en')
            <div style="color:var(--danger);font-size:12px;margin-top:6px">{{ $message }}</div>
          @enderror
        </div>

        {{-- Coefficient --}}
        <div class="mb-3">
          <label class="form-label">{{ $isFr ? 'Coefficient' : 'Coefficient' }} <span style="color:var(--danger)">*</span></label>
          <input type="number" name="coefficient" class="form-control @error('coefficient') is-invalid @enderror" 
                 placeholder="1" min="0.5" max="10" step="0.5" value="{{ old('coefficient', $subject->coefficient) }}" required>
          @error('coefficient')
            <div style="color:var(--danger);font-size:12px;margin-top:6px">{{ $message }}</div>
          @enderror
        </div>

        {{-- Actions --}}
        <div style="display:flex;gap:12px;margin-top:30px">
          <button type="submit" class="btn btn-primary">
            <i data-lucide="check" style="width:14px;height:14px"></i>
            {{ $isFr ? 'Enregistrer' : 'Save' }}
          </button>
          <a href="{{ route('admin.subjects.index') }}" class="btn btn-outline">
            <i data-lucide="x" style="width:14px;height:14px"></i>
            {{ $isFr ? 'Annuler' : 'Cancel' }}
          </a>
        </div>
      </div>
    </form>
  </div>
</div>

@endsection
