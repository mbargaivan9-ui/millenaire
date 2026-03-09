@extends('layouts.app')
@section('title', isset($announcement) ? 'Éditer Annonce' : 'Créer Annonce')

@section('content')

{{-- Page Header --}}
<div class="page-header">
  <div>
    <div class="breadcrumb">
      <span>{{ __('app.home') }}</span>
      <span class="breadcrumb-sep">/</span>
      <a href="{{ route('admin.announcements.index') }}" style="color:var(--primary);text-decoration:none">Annonces</a>
      <span class="breadcrumb-sep">/</span>
      <span class="breadcrumb-current">{{ isset($announcement) ? 'Éditer' : 'Créer' }}</span>
    </div>
    <h1 class="page-title">{{ isset($announcement) ? 'Éditer Annonce' : 'Créer Annonce' }}</h1>
    <p class="page-subtitle">{{ isset($announcement) ? 'Modifier l\'annonce' : 'Ajouter une nouvelle annonce' }}</p>
  </div>
</div>

<div style="display:grid;grid-template-columns:1fr 350px;gap:20px;margin-bottom:40px">
  {{-- Main Content --}}
  <div>
    <div class="card">
      <div class="card-header">
        <i data-lucide="loudspeaker" style="width:16px;height:16px"></i>
        <span>{{ isset($announcement) ? 'Éditer' : 'Nouvelle' }} Annonce</span>
      </div>
      <div class="card-body">
        <form action="{{ isset($announcement) ? route('admin.announcements.update', $announcement) : route('admin.announcements.store') }}" 
              method="POST" enctype="multipart/form-data">
          @csrf
          @if(isset($announcement))
            @method('PUT')
          @endif

          {{-- Title --}}
          <div style="margin-bottom:20px">
            <label class="form-label">Titre de l'Annonce *</label>
            <input type="text" class="form-control @error('title') is-invalid @enderror" 
                   name="title" value="{{ $announcement->title ?? old('title') }}" 
                   placeholder="Titre principal de l'annonce" required id="titleInput">
            @error('title')<div style="color:var(--danger);font-size:12px;margin-top:4px">{{ $message }}</div>@enderror
          </div>

          {{-- Content --}}
          <div style="margin-bottom:20px">
            <label class="form-label">Contenu *</label>
            <textarea class="form-control @error('content') is-invalid @enderror" 
                      name="content" rows="6" placeholder="Détails de l'annonce..." required 
                      id="contentInput" maxlength="500">{{ $announcement->content ?? old('content') }}</textarea>
            @error('content')<div style="color:var(--danger);font-size:12px;margin-top:4px">{{ $message }}</div>@enderror
            <small style="color:var(--text-muted);font-size:12px;display:block;margin-top:4px">Caractères restants: <span id="charCount">500</span>/500</small>
          </div>

          {{-- Row 1: Target Audience and Status --}}
          <div style="display:grid;grid-template-columns:repeat(2,1fr);gap:16px;margin-bottom:20px">
            <div>
              <label class="form-label">Public Cible *</label>
              <select class="form-control @error('target_audience') is-invalid @enderror" 
                      name="target_audience" required id="audienceSelect">
                <option value="">-- Sélectionnez --</option>
                <option value="students" {{ (isset($announcement) && $announcement->target_audience === 'students') || old('target_audience') === 'students' ? 'selected' : '' }}>
                  Étudiants
                </option>
                <option value="parents" {{ (isset($announcement) && $announcement->target_audience === 'parents') || old('target_audience') === 'parents' ? 'selected' : '' }}>
                  Parents
                </option>
                <option value="teachers" {{ (isset($announcement) && $announcement->target_audience === 'teachers') || old('target_audience') === 'teachers' ? 'selected' : '' }}>
                  Professeurs
                </option>
                <option value="all" {{ (isset($announcement) && $announcement->target_audience === 'all') || old('target_audience') === 'all' ? 'selected' : '' }}>
                  Tous
                </option>
              </select>
              @error('target_audience')<div style="color:var(--danger);font-size:12px;margin-top:4px">{{ $message }}</div>@enderror
            </div>

            <div>
              <label class="form-label">Statut *</label>
              <select class="form-control @error('status') is-invalid @enderror" 
                      name="status" required id="statusSelect">
                <option value="draft" {{ (isset($announcement) && $announcement->status === 'draft') || old('status') === 'draft' ? 'selected' : '' }}>
                  Brouillon
                </option>
                <option value="active" {{ (isset($announcement) && $announcement->status === 'active') || old('status') === 'active' ? 'selected' : '' }}>
                  Active
                </option>
                <option value="scheduled" {{ (isset($announcement) && $announcement->status === 'scheduled') || old('status') === 'scheduled' ? 'selected' : '' }}>
                  Planifiée
                </option>
                <option value="archived" {{ (isset($announcement) && $announcement->status === 'archived') || old('status') === 'archived' ? 'selected' : '' }}>
                  Archivée
                </option>
              </select>
              @error('status')<div style="color:var(--danger);font-size:12px;margin-top:4px">{{ $message }}</div>@enderror
            </div>
          </div>

          {{-- Row 2: Priority and Publication Date --}}
          <div style="display:grid;grid-template-columns:repeat(2,1fr);gap:16px;margin-bottom:20px">
            <div>
              <label class="form-label">Priorité</label>
              <select class="form-control" name="priority" id="prioritySelect">
                <option value="low" {{ (isset($announcement) && $announcement->priority === 'low') || old('priority') === 'low' ? 'selected' : '' }}>
                  Basse
                </option>
                <option value="normal" {{ (isset($announcement) && $announcement->priority === 'normal') || old('priority') === 'normal' ? 'selected' : '' }}>
                  Normale
                </option>
                <option value="high" {{ (isset($announcement) && $announcement->priority === 'high') || old('priority') === 'high' ? 'selected' : '' }}>
                  Haute
                </option>
              </select>
            </div>

            <div>
              <label class="form-label">Date de Publication</label>
              <input type="datetime-local" class="form-control" 
                     name="published_at" 
                     value="{{ isset($announcement) && $announcement->published_at ? $announcement->published_at->format('Y-m-d\TH:i') : old('published_at') }}">
            </div>
          </div>

          {{-- Image Upload --}}
          <div style="margin-bottom:20px">
            <label class="form-label">Image (optionnel)</label>
            <input type="file" class="form-control" name="image" accept="image/*">
            <small style="color:var(--text-muted);font-size:12px;display:block;margin-top:4px">Format: JPG, PNG, WebP (max 5MB)</small>
            @if(isset($announcement) && $announcement->image)
            <div style="margin-top:12px">
              <img src="{{ asset('storage/' . $announcement->image) }}" alt="Annonce" 
                   style="max-width: 200px; border-radius: 4px;">
            </div>
            @endif
          </div>

          {{-- Actions --}}
          <div style="display:flex;gap:12px;justify-content:flex-end">
            <a href="{{ route('admin.announcements.index') }}" class="btn btn-secondary">
              Annuler
            </a>
            <button type="submit" class="btn btn-primary">
              <i data-lucide="save" style="width:14px;height:14px"></i>
              {{ isset($announcement) ? 'Mettre à jour' : 'Publier' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>

  {{-- Sidebar --}}
  <div>
    <div class="card" style="margin-bottom:20px;position:sticky;top:20px">
      <div class="card-header">
        <i data-lucide="eye" style="width:16px;height:16px"></i>
        <span>Aperçu</span>
      </div>
      <div class="card-body">
        <div style="text-align:center;margin-bottom:12px">
          <small style="color:var(--text-muted);font-weight:600">APERÇU</small>
        </div>
        
        <h6 style="font-weight:bold;color:var(--text-dark);margin-bottom:8px" id="previewTitle">
          {{ $announcement->title ?? 'Titre de l\'annonce' }}
        </h6>

        <p style="font-size:12px;color:var(--text-muted);margin-bottom:12px" id="previewContent">
          {{ $announcement->content ? \Illuminate\Support\Str::limit($announcement->content, 120) : 'Le contenu s\'affichera ici...' }}
        </p>

        <div style="font-size:12px;margin-bottom:12px">
          <span style="background:var(--primary);color:white;padding:4px 8px;border-radius:4px;display:inline-block;margin-right:4px" id="previewAudience">
            {{ $announcement->target_audience ? ucfirst($announcement->target_audience) : 'Public' }}
          </span>
          <span style="background:var(--text-bg);color:var(--text-dark);padding:4px 8px;border-radius:4px;display:inline-block" id="previewStatus">
            {{ $announcement->status ? ucfirst($announcement->status) : 'Statut' }}
          </span>
        </div>

        <small style="color:var(--text-muted);display:flex;gap:4px">
          <i data-lucide="calendar" style="width:14px;height:14px"></i>
          <span>{{ $announcement->created_at?->format('d/m/Y') ?? 'Aujourd\'hui' }}</span>
        </small>
      </div>
    </div>

    <div class="card">
      <div class="card-header">
        <i data-lucide="lightbulb" style="width:16px;height:16px"></i>
        <span>Directives</span>
      </div>
      <div class="card-body">
        <ul style="list-style:none;padding:0;margin:0">
          <li style="margin-bottom:8px;display:flex;gap:8px;font-size:12px">
            <i data-lucide="check" style="width:14px;height:14px;color:var(--success);flex-shrink:0"></i>
            <span>Titre clair et concis</span>
          </li>
          <li style="margin-bottom:8px;display:flex;gap:8px;font-size:12px">
            <i data-lucide="check" style="width:14px;height:14px;color:var(--success);flex-shrink:0"></i>
            <span>Contenu détaillé et informatif</span>
          </li>
          <li style="margin-bottom:8px;display:flex;gap:8px;font-size:12px">
            <i data-lucide="check" style="width:14px;height:14px;color:var(--success);flex-shrink:0"></i>
            <span>Définir la date de publication</span>
          </li>
          <li style="display:flex;gap:8px;font-size:12px">
            <i data-lucide="check" style="width:14px;height:14px;color:var(--success);flex-shrink:0"></i>
            <span>Réviser avant publication</span>
          </li>
        </ul>
      </div>
    </div>
  </div>
</div>

@media (max-width: 768px) {
  div[style*="grid-template-columns:1fr 350px"] {
    grid-template-columns: 1fr !important;
  }
}

<script>
  // Character counter
  const contentInput = document.getElementById('contentInput');
  const charCount = document.getElementById('charCount');

  if (contentInput) {
    contentInput.addEventListener('input', function() {
      const remaining = 500 - this.value.length;
      charCount.textContent = Math.max(0, remaining);
    });
    contentInput.dispatchEvent(new Event('input'));
  }

  // Live preview
  const titleInput = document.getElementById('titleInput');
  const previewTitle = document.getElementById('previewTitle');
  const previewContent = document.getElementById('previewContent');
  const audienceSelect = document.getElementById('audienceSelect');
  const statusSelect = document.getElementById('statusSelect');
  const previewAudience = document.getElementById('previewAudience');
  const previewStatus = document.getElementById('previewStatus');

  if (titleInput) {
    titleInput.addEventListener('input', function() {
      previewTitle.textContent = this.value || 'Titre de l\'annonce';
    });
  }

  if (contentInput) {
    contentInput.addEventListener('input', function() {
      const preview = this.value ? this.value.substring(0, 120) : 'Le contenu s\'affichera ici...';
      previewContent.textContent = preview;
    });
  }

  if (audienceSelect) {
    audienceSelect.addEventListener('change', function() {
      const text = this.options[this.selectedIndex].text || 'Public';
      previewAudience.textContent = text;
    });
  }

  if (statusSelect) {
    statusSelect.addEventListener('change', function() {
      const text = this.options[this.selectedIndex].text || 'Statut';
      previewStatus.textContent = text;
    });
  }
</script>

@endsection


