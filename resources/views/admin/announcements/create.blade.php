{{--
    | admin/announcements/create.blade.php — Créer / Modifier une annonce
    --}}
@extends('layouts.app')
@section('title', isset($announcement)
    ? (app()->getLocale() === 'fr' ? 'Modifier l\'annonce' : 'Edit Announcement')
    : (app()->getLocale() === 'fr' ? 'Nouvelle annonce' : 'New Announcement'))

@section('content')
@php $isFr = app()->getLocale() === 'fr'; $editing = isset($announcement); @endphp

<div class="page-header">
    <div class="d-flex align-items-center gap-3">
        <a href="{{ route('admin.announcements.index') }}" class="btn btn-light btn-sm">
            <i data-lucide="arrow-left" style="width:14px"></i>
        </a>
        <div class="page-icon" style="background:linear-gradient(135deg,#f59e0b,#d97706)">
            <i data-lucide="{{ $editing ? 'edit-2' : 'megaphone' }}"></i>
        </div>
        <h1 class="page-title">
            {{ $editing
                ? ($isFr ? 'Modifier l\'annonce' : 'Edit Announcement')
                : ($isFr ? 'Nouvelle annonce' : 'New Announcement') }}
        </h1>
    </div>
</div>

<div class="row">
<div class="col-lg-8">
<div class="card">
<div class="card-body">

<form method="POST"
      action="{{ $editing ? route('admin.announcements.update', $announcement->id) : route('admin.announcements.store') }}"
      enctype="multipart/form-data">
    @csrf
    @if($editing) @method('PUT') @endif

    @if($errors->any())
    <div class="alert alert-danger mb-4">
        @foreach($errors->all() as $err)
        <div style="font-size:.83rem">• {{ $err }}</div>
        @endforeach
    </div>
    @endif

    {{-- Title --}}
    <div class="mb-3">
        <label class="form-label fw-semibold">
            {{ $isFr ? 'Titre' : 'Title' }} <span class="text-danger">*</span>
        </label>
        <input type="text" name="title" class="form-control form-control-lg"
               value="{{ old('title', $announcement->title ?? '') }}"
               required
               placeholder="{{ $isFr ? 'Ex: Réunion de parents — Jeudi 15 Mai' : 'E.g: Parent Meeting — Thursday May 15' }}">
    </div>

    {{-- Category --}}
    <div class="mb-3">
        <label class="form-label fw-semibold">
            {{ $isFr ? 'Catégorie' : 'Category' }} <span class="text-danger">*</span>
        </label>
        <div class="d-flex gap-2 flex-wrap">
            @php
            $categories = [
                'general'    => ['🏫', $isFr ? 'Général'       : 'General'],
                'event'      => ['📅', $isFr ? 'Événement'     : 'Event'],
                'exam'       => ['📝', $isFr ? 'Examen'        : 'Exam'],
                'holiday'    => ['🏖️', $isFr ? 'Congé'         : 'Holiday'],
                'urgent'     => ['🚨', $isFr ? 'Urgent'        : 'Urgent'],
                'payment'    => ['💳', $isFr ? 'Paiement'      : 'Payment'],
            ];
            $currentCat = old('category', $announcement->category ?? 'general');
            @endphp
            @foreach($categories as $val => [$emoji, $label])
            <label style="cursor:pointer">
                <input type="radio" name="category" value="{{ $val }}" hidden
                       {{ $currentCat === $val ? 'checked' : '' }}>
                <span class="cat-pill {{ $currentCat === $val ? 'selected' : '' }}"
                      id="cat-{{ $val }}"
                      style="display:inline-flex;align-items:center;gap:.4rem;padding:.42rem .9rem;border:1.5px solid var(--border);border-radius:20px;font-size:.8rem;font-weight:600;cursor:pointer;transition:all .15s ease;{{ $currentCat === $val ? 'border-color:var(--primary);background:var(--primary-bg);color:var(--primary)' : '' }}"
                      onclick="selectCat('{{ $val }}')">
                    <span>{{ $emoji }}</span> {{ $label }}
                </span>
            </label>
            @endforeach
        </div>
        <input type="hidden" id="category-input" name="category" value="{{ $currentCat }}">
    </div>

    {{-- Content --}}
    <div class="mb-3">
        <label class="form-label fw-semibold">
            {{ $isFr ? 'Contenu' : 'Content' }} <span class="text-danger">*</span>
        </label>
        <textarea name="content" class="form-control" rows="8"
                  required
                  placeholder="{{ $isFr ? 'Rédigez votre annonce ici...' : 'Write your announcement here...' }}"
                  style="resize:vertical">{{ old('content', $announcement->content ?? '') }}</textarea>
        <div style="font-size:.72rem;color:var(--text-muted);margin-top:.3rem">
            {{ $isFr ? 'Supports le Markdown basique (gras, italique, listes).' : 'Supports basic Markdown (bold, italic, lists).' }}
        </div>
    </div>

    {{-- Photo de Couverture & Fichiers --}}
    <div class="mb-4">
        <h6 class="fw-semibold mb-3">
            <svg class="me-2" style="width:16px;height:16px;display:inline" fill="currentColor" viewBox="0 0 20 20">
                <path d="M4 3a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V5a2 2 0 00-2-2H4zm12 12H4l4-8 3 6 2-4 3 6z" />
            </svg>
            {{ $isFr ? 'Médias & Fichiers' : 'Media & Files' }}
        </h6>

        {{-- Photo de Couverture --}}
        <div class="card mb-3">
            <div class="card-body">
                <label class="form-label fw-semibold mb-2">
                    <i class="fas fa-image text-primary me-2"></i>
                    {{ $isFr ? 'Photo de Couverture' : 'Cover Image' }}
                </label>
                <p style="font-size:.8rem;color:var(--text-muted)">
                    {{ $isFr ? 'JPG, PNG, GIF, WebP - Max 5MB' : 'JPG, PNG, GIF, WebP - Max 5MB' }}
                </p>

                {{-- Image actuelle --}}
                @if($editing && $announcement->cover_image)
                <div class="mb-3" style="position:relative;display:inline-block">
                    <img src="{{ asset('storage/' . $announcement->cover_image) }}" 
                         alt="Cover" 
                         style="height:150px;width:auto;border-radius:8px;border:2px solid var(--border);object-fit:cover">
                    <label style="cursor:pointer;position:absolute;top:5px;right:5px">
                        <input type="checkbox" name="remove_cover" value="1" style="accent-color:var(--primary)">
                        <span style="font-size:.75rem;background:var(--danger);color:white;padding:4px 8px;border-radius:4px;display:inline-block;margin-left:4px">{{ $isFr ? 'Supprimer' : 'Delete' }}</span>
                    </label>
                </div>
                @endif

                {{-- Upload --}}
                <input type="file" 
                       name="cover_image" 
                       accept="image/jpeg,image/png,image/gif,image/webp"
                       class="form-control form-control-sm"
                       id="coverImage">
                @error('cover_image')
                <div class="text-danger" style="font-size:.8rem;margin-top:5px">{{ $message }}</div>
                @enderror
                <div id="coverPreview" class="mt-2"></div>
            </div>
        </div>

        {{-- Fichier Joint --}}
        <div class="card">
            <div class="card-body">
                <label class="form-label fw-semibold mb-2">
                    <i class="fas fa-paperclip text-success me-2"></i>
                    {{ $isFr ? 'Fichier Joint (Optionnel)' : 'Attached File (Optional)' }}
                </label>
                <p style="font-size:.8rem;color:var(--text-muted)">
                    {{ $isFr ? 'PDF, DOC, DOCX, XLS, XLSX, PPT, PPTX, ZIP - Max 10MB' : 'PDF, DOC, DOCX, XLS, XLSX, PPT, PPTX, ZIP - Max 10MB' }}
                </p>

                {{-- Fichier actuel --}}
                @if($editing && $announcement->attached_file)
                <div class="mb-3 p-3" style="background:var(--surface-2);border-radius:8px">
                    <div style="display:flex;align-items:center;gap:10px">
                        <span style="font-size:1.5rem">
                            @php
                                $ext = strtolower(pathinfo($announcement->attachment_name ?? '', PATHINFO_EXTENSION));
                                echo match($ext) {
                                    'pdf' => '📄',
                                    'doc', 'docx' => '📝',
                                    'xls', 'xlsx' => '📊',
                                    'ppt', 'pptx' => '🎬',
                                    'zip' => '📦',
                                    default => '📎',
                                };
                            @endphp
                        </span>
                        <div style="flex:1">
                            <div style="font-weight:600;font-size:.9rem">{{ $announcement->attachment_name }}</div>
                            <div style="font-size:.8rem;color:var(--text-muted)">{{ number_format($announcement->attachment_size / 1024, 1) }} KB</div>
                        </div>
                        <label style="cursor:pointer">
                            <input type="checkbox" name="remove_file" value="1" style="accent-color:var(--danger)">
                            <span style="font-size:.75rem;background:var(--danger);color:white;padding:4px 8px;border-radius:4px;display:inline-block">{{ $isFr ? 'Supprimer' : 'Delete' }}</span>
                        </label>
                    </div>
                </div>
                @endif

                {{-- Upload --}}
                <input type="file" 
                       name="attached_file" 
                       accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.zip"
                       class="form-control form-control-sm"
                       id="attachedFile">
                @error('attached_file')
                <div class="text-danger" style="font-size:.8rem;margin-top:5px">{{ $message }}</div>
                @enderror
                <div id="filePreview" class="mt-2"></div>
            </div>
        </div>
    </div>

    {{-- Audience --}}
    <div class="mb-4">
        <label class="form-label fw-semibold">
            {{ $isFr ? 'Audience' : 'Audience' }}
        </label>
        <div class="d-flex gap-3 flex-wrap">
            @foreach(['all' => ($isFr ? 'Tous' : 'All'), 'parents' => 'Parents', 'teachers' => ($isFr ? 'Enseignants' : 'Teachers'), 'students' => ($isFr ? 'Élèves' : 'Students')] as $val => $label)
            <label style="display:flex;align-items:center;gap:.45rem;cursor:pointer;font-size:.85rem">
                <input type="radio" name="audience" value="{{ $val }}"
                       {{ old('audience', $announcement->audience ?? 'all') === $val ? 'checked' : '' }}
                       style="accent-color:var(--primary)">
                {{ $label }}
            </label>
            @endforeach
        </div>
    </div>

    {{-- Publish immediately --}}
    <div class="d-flex align-items-center gap-3 p-3 rounded-3 mb-4"
         style="background:var(--surface-2)">
        <div>
            <div class="fw-bold" style="font-size:.88rem">
                {{ $isFr ? 'Publier immédiatement' : 'Publish immediately' }}
            </div>
            <div style="font-size:.76rem;color:var(--text-muted)">
                {{ $isFr
                    ? 'Visible par tous les utilisateurs dès l\'enregistrement.'
                    : 'Visible to all users upon saving.' }}
            </div>
        </div>
        <div class="ms-auto">
            <input type="checkbox" name="is_published" value="1" id="is_published"
                   {{ old('is_published', $announcement->is_published ?? false) ? 'checked' : '' }}
                   style="width:42px;height:22px;accent-color:var(--primary);cursor:pointer">
        </div>
    </div>

    <div class="d-flex gap-2">
        <button type="submit" class="btn btn-primary">
            <i data-lucide="{{ $editing ? 'save' : 'megaphone' }}" style="width:14px" class="me-1"></i>
            {{ $editing
                ? ($isFr ? 'Enregistrer' : 'Save changes')
                : ($isFr ? 'Créer l\'annonce' : 'Create announcement') }}
        </button>
        <a href="{{ route('admin.announcements.index') }}" class="btn btn-light">
            {{ $isFr ? 'Annuler' : 'Cancel' }}
        </a>
    </div>
</form>

</div>
</div>
</div>

{{-- Preview sidebar --}}
<div class="col-lg-4">
    <div class="card">
        <div class="card-header">
            <h6 class="card-title mb-0">
                <i data-lucide="eye" style="width:15px" class="me-2"></i>
                {{ $isFr ? 'Aperçu' : 'Preview' }}
            </h6>
        </div>
        <div class="card-body" id="preview-body">
            <p class="text-muted" style="font-size:.82rem;font-style:italic">
                {{ $isFr ? 'Commencez à écrire pour voir l\'aperçu.' : 'Start typing to see preview.' }}
            </p>
        </div>
    </div>
</div>
</div>

@endsection

@push('scripts')
<script>
// ════════════════════════════════════════════════════════════════════════════
// Aperçus des fichiers
// ════════════════════════════════════════════════════════════════════════════

// Aperçu image de couverture
document.getElementById('coverImage')?.addEventListener('change', function(e) {
    const file = e.target.files[0];
    const preview = document.getElementById('coverPreview');
    
    if (file) {
        const reader = new FileReader();
        reader.onload = function(event) {
            preview.innerHTML = `
                <div style="position:relative;display:inline-block;margin-top:10px">
                    <img src="${event.target.result}" 
                         alt="Preview" 
                         style="height:150px;width:auto;border-radius:8px;border:2px solid var(--primary);object-fit:cover">
                    <div style="margin-top:8px;font-size:.8rem;color:var(--text-secondary)">
                        ✓ ${file.name} (${(file.size/1024).toFixed(1)} KB)
                    </div>
                </div>`;
        };
        reader.readAsDataURL(file);
    } else {
        preview.innerHTML = '';
    }
});

// Aperçu fichier joint
document.getElementById('attachedFile')?.addEventListener('change', function(e) {
    const file = e.target.files[0];
    const preview = document.getElementById('filePreview');
    
    if (file) {
        const ext = file.name.split('.').pop().toLowerCase();
        const icons = {
            'pdf': '📄', 'doc': '📝', 'docx': '📝',
            'xls': '📊', 'xlsx': '📊',
            'ppt': '🎬', 'pptx': '🎬',
            'zip': '📦'
        };
        const icon = icons[ext] || '📎';
        
        preview.innerHTML = `
            <div style="padding:12px;background:var(--surface-2);border-radius:6px;margin-top:10px">
                <div style="display:flex;align-items:center;gap:10px">
                    <span style="font-size:1.5rem">${icon}</span>
                    <div>
                        <div style="font-weight:600;font-size:.9rem">✓ ${file.name}</div>
                        <div style="font-size:.8rem;color:var(--text-muted)">${(file.size/1024).toFixed(1)} KB</div>
                    </div>
                </div>
            </div>`;
    } else {
        preview.innerHTML = '';
    }
});

// ════════════════════════════════════════════════════════════════════════════
window.selectCat = function(val) {
    document.querySelectorAll('.cat-pill').forEach(p => {
        p.style.borderColor = 'var(--border)';
        p.style.background  = 'var(--surface)';
        p.style.color       = 'var(--text-secondary)';
    });
    const sel = document.getElementById(`cat-${val}`);
    if (sel) {
        sel.style.borderColor = 'var(--primary)';
        sel.style.background  = 'var(--primary-bg)';
        sel.style.color       = 'var(--primary)';
    }
    document.getElementById('category-input').value = val;
    // Also check the hidden radio
    const radio = document.querySelector(`[name="category"][value="${val}"]`);
    if (radio) radio.checked = true;
};

// Live preview
const textarea = document.querySelector('[name="content"]');
const title    = document.querySelector('[name="title"]');
const preview  = document.getElementById('preview-body');

function updatePreview() {
    const t = title?.value?.trim() || '';
    const c = textarea?.value?.trim() || '';
    if (!t && !c) {
        preview.innerHTML = '<p class="text-muted" style="font-size:.82rem;font-style:italic">{{ $isFr ? "Commencez à écrire pour voir l\'aperçu." : "Start typing to see preview." }}</p>';
        return;
    }
    preview.innerHTML = `
        <h6 class="fw-bold mb-2">${t || '(sans titre)'}</h6>
        <p style="font-size:.83rem;color:var(--text-secondary);white-space:pre-wrap">${c || ''}</p>
        <div style="font-size:.72rem;color:var(--text-muted);margin-top:.75rem">
            📅 ${new Date().toLocaleDateString('{{ $isFr ? "fr-FR" : "en-GB" }}', {day:'2-digit',month:'long',year:'numeric'})}
        </div>`;
}

textarea?.addEventListener('input', updatePreview);
title?.addEventListener('input', updatePreview);
updatePreview();
</script>
@endpush


