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
      action="{{ $editing ? route('admin.announcements.update', $announcement->id) : route('admin.announcements.store') }}">
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
// Category selection
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


