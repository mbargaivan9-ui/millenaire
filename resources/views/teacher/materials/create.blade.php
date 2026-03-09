{{--
    | teacher/materials/create.blade.php — Créer / Modifier une Ressource
    --}}

@extends('layouts.app')
@php
  $pageTitle = $pageTitle ?? (isset($material) ? (app()->getLocale() === 'fr' ? 'Modifier la ressource' : 'Edit resource') : (app()->getLocale() === 'fr' ? 'Nouvelle ressource' : 'New resource'));
@endphp
@section('title', $pageTitle)

@section('content')
@php
    $isFr    = app()->getLocale() === 'fr';
    $editing = isset($material);
@endphp

<div class="page-header">
    <div class="d-flex align-items-center gap-3">
        <a href="{{ route('teacher.materials.index') }}" class="btn btn-light btn-sm">
            <i data-lucide="arrow-left" style="width:14px"></i>
        </a>
        <div class="page-icon" style="background:linear-gradient(135deg,#8b5cf6,#7c3aed)"><i data-lucide="folder-plus"></i></div>
        <h1 class="page-title">{{ $editing ? ($isFr ? 'Modifier la ressource' : 'Edit resource') : ($isFr ? 'Nouvelle ressource' : 'New resource') }}</h1>
    </div>
</div>

<div class="row">
<div class="col-lg-8">
<div class="card">
<div class="card-body">

<form method="POST" action="{{ $editing ? route('teacher.materials.update', $material->id) : route('teacher.materials.store') }}" enctype="multipart/form-data">
    @csrf
    @if($editing) @method('PUT') @endif

    @if($errors->any())
    <div class="alert alert-danger mb-4">
        <ul class="mb-0 ps-3">
            @foreach($errors->all() as $err)<li style="font-size:.83rem">{{ $err }}</li>@endforeach
        </ul>
    </div>
    @endif

    {{-- Type selector --}}
    <div class="mb-4">
        <label class="form-label">{{ $isFr ? 'Type de ressource' : 'Resource type' }} <span class="text-danger">*</span></label>
        <div class="d-flex gap-2 flex-wrap">
            @foreach(['pdf' => ['📄', $isFr ? 'Fichier PDF' : 'PDF File'], 'video' => ['🎬', 'Vidéo'], 'powerpoint' => ['📊', 'PowerPoint'], 'link' => ['🔗', $isFr ? 'Lien externe' : 'External link']] as $type => $meta)
            <label style="cursor:pointer">
                <input type="radio" name="type" value="{{ $type }}" hidden
                       {{ old('type', $material->type ?? 'pdf') === $type ? 'checked' : '' }}
                       onchange="switchType('{{ $type }}')">
                <div class="type-pill {{ old('type', $material->type ?? 'pdf') === $type ? 'selected' : '' }}" id="type-{{ $type }}"
                     style="padding:.55rem 1.1rem;border:1.5px solid var(--border);border-radius:10px;display:flex;align-items:center;gap:.5rem;font-size:.83rem;font-weight:600;transition:all .15s ease">
                    <span style="font-size:1.1rem">{{ $meta[0] }}</span> {{ $meta[1] }}
                </div>
            </label>
            @endforeach
        </div>
    </div>

    {{-- Title --}}
    <div class="mb-3">
        <label class="form-label">{{ $isFr ? 'Titre' : 'Title' }} <span class="text-danger">*</span></label>
        <input type="text" name="title" class="form-control" value="{{ old('title', $material->title ?? '') }}" required placeholder="{{ $isFr ? 'Ex: Cours de mathématiques — Chapitre 3' : 'E.g: Mathematics Chapter 3' }}">
    </div>

    {{-- Subject & Classes --}}
    <div class="row mb-3">
        <div class="col-md-6">
            <label class="form-label">{{ $isFr ? 'Matière' : 'Subject' }} <span class="text-danger">*</span></label>
            <select name="subject_id" class="form-select" required>
                <option value="">{{ $isFr ? 'Choisir...' : 'Choose...' }}</option>
                @foreach($subjects as $sub)
                <option value="{{ $sub->id }}" {{ old('subject_id', $material->subject_id ?? '') == $sub->id ? 'selected' : '' }}>{{ $sub->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-6">
            <label class="form-label">{{ $isFr ? 'Classes visées' : 'Target classes' }} <span class="text-danger">*</span></label>
            <select name="class_ids[]" class="form-select" multiple required style="height:42px">
                @foreach($classes as $class)
                <option value="{{ $class->id }}" {{ in_array($class->id, old('class_ids', $material?->classes->pluck('id')->toArray() ?? [])) ? 'selected' : '' }}>
                    {{ $class->name }}
                </option>
                @endforeach
            </select>
            <div style="font-size:.72rem;color:var(--text-muted);margin-top:.3rem">{{ $isFr ? 'Ctrl+clic pour sélectionner plusieurs' : 'Ctrl+click for multiple' }}</div>
        </div>
    </div>

    {{-- Description --}}
    <div class="mb-3">
        <label class="form-label">{{ $isFr ? 'Description (optionnelle)' : 'Description (optional)' }}</label>
        <textarea name="description" class="form-control" rows="3" style="resize:none" placeholder="{{ $isFr ? 'Résumé du contenu...' : 'Content summary...' }}">{{ old('description', $material->description ?? '') }}</textarea>
    </div>

    {{-- Type-specific fields --}}
    <div id="field-pdf" class="mb-3">
        <label class="form-label">{{ $isFr ? 'Fichier PDF / PowerPoint' : 'PDF / PowerPoint file' }}</label>
        <input type="file" name="file" class="form-control" accept=".pdf,.ppt,.pptx">
        @if($editing && $material->file_path)
        <div style="font-size:.78rem;color:var(--text-muted);margin-top:.35rem">
            <i data-lucide="paperclip" style="width:12px"></i>
            {{ $isFr ? 'Fichier actuel:' : 'Current file:' }} {{ basename($material->file_path) }}
            <span style="color:var(--text-muted)">({{ $isFr ? 'laissez vide pour conserver' : 'leave empty to keep' }})</span>
        </div>
        @endif
    </div>

    <div id="field-video" class="mb-3" style="display:none">
        <label class="form-label">{{ $isFr ? 'URL Vidéo (YouTube / Vimeo)' : 'Video URL (YouTube / Vimeo)' }}</label>
        <input type="url" name="video_url" class="form-control" value="{{ old('video_url', $material->video_url ?? '') }}"
               placeholder="https://youtube.com/watch?v=...">
    </div>

    <div id="field-link" class="mb-3" style="display:none">
        <label class="form-label">{{ $isFr ? 'URL externe' : 'External URL' }}</label>
        <input type="url" name="external_url" class="form-control" value="{{ old('external_url', $material->external_url ?? '') }}"
               placeholder="https://...">
    </div>

    {{-- Publish toggle --}}
    <div class="d-flex align-items-center gap-3 mb-4 p-3 rounded-3" style="background:var(--surface-2)">
        <div>
            <div class="fw-bold" style="font-size:.88rem">{{ $isFr ? 'Publier immédiatement' : 'Publish immediately' }}</div>
            <div style="font-size:.77rem;color:var(--text-muted)">{{ $isFr ? 'Les élèves verront cette ressource dès maintenant.' : 'Students will see this resource immediately.' }}</div>
        </div>
        <div class="ms-auto">
            <input type="checkbox" name="is_published" value="1" id="is_published"
                   {{ old('is_published', $material->is_published ?? true) ? 'checked' : '' }}
                   style="width:42px;height:22px;accent-color:var(--primary);cursor:pointer">
        </div>
    </div>

    <div class="d-flex gap-2">
        <button type="submit" class="btn btn-primary">
            <i data-lucide="{{ $editing ? 'save' : 'upload' }}" style="width:14px" class="me-1"></i>
            {{ $editing ? ($isFr ? 'Enregistrer' : 'Save') : ($isFr ? 'Publier la ressource' : 'Publish resource') }}
        </button>
        <a href="{{ route('teacher.materials.index') }}" class="btn btn-light">{{ $isFr ? 'Annuler' : 'Cancel' }}</a>
    </div>
</form>

</div></div>
</div>
</div>

@endsection

@push('scripts')
<script>
function switchType(type) {
    // Update pill styles
    document.querySelectorAll('.type-pill').forEach(p => {
        p.style.borderColor = 'var(--border)';
        p.style.background  = 'var(--surface)';
        p.style.color       = 'var(--text-secondary)';
    });
    const sel = document.getElementById(`type-${type}`);
    if (sel) { sel.style.borderColor = 'var(--primary)'; sel.style.background = 'var(--primary-bg)'; sel.style.color = 'var(--primary)'; }

    // Show/hide relevant fields
    ['pdf','video','link'].forEach(t => {
        const el = document.getElementById(`field-${t}`);
        if (el) el.style.display = (t === type || (t === 'pdf' && type === 'powerpoint')) ? '' : 'none';
    });
}

// Init on load
document.addEventListener('DOMContentLoaded', () => {
    const checked = document.querySelector('[name="type"]:checked')?.value ?? 'pdf';
    switchType(checked);
    if (typeof lucide !== 'undefined') lucide.createIcons();
});
</script>
@endpush
