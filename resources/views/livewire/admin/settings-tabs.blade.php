<div>
    {{-- Page Header --}}
    <div class="page-header">
        <div class="d-flex align-items-center gap-3">
            <div class="page-icon" style="background:linear-gradient(135deg,var(--primary),var(--primary-light))">
                <i data-lucide="settings"></i>
            </div>
            <div>
                <h1 class="page-title">{{ $isFr ? 'Paramètres de l\'Établissement' : 'Establishment Settings' }}</h1>
                <p class="page-subtitle text-muted">{{ $isFr ? 'Configuration générale de la plateforme' : 'General platform configuration' }}</p>
            </div>
        </div>
    </div>

    {{-- Save Status Alert --}}
    @if($saveStatus === 'success')
    <div class="alert alert-success d-flex align-items-center gap-2 mb-4 alert-dismissible fade show" role="alert">
        <i data-lucide="check-circle" style="width:18px"></i>
        <span>{{ $isFr ? 'Paramètres enregistrés avec succès !' : 'Settings saved successfully!' }}</span>
        <button type="button" class="btn-close" wire:click="$set('saveStatus', '')"></button>
    </div>
    @elseif(str_starts_with($saveStatus, 'error'))
    <div class="alert alert-danger d-flex align-items-center gap-2 mb-4 alert-dismissible fade show" role="alert">
        <i data-lucide="alert-circle" style="width:18px"></i>
        <span>{{ $saveStatus }}</span>
        <button type="button" class="btn-close" wire:click="$set('saveStatus', '')"></button>
    </div>
    @endif

    {{-- Tabs Navigation --}}
    <div class="mb-4">
        <div class="d-flex gap-2 flex-wrap" style="border-bottom:2px solid var(--border); padding-bottom:0">
            @foreach([
                ['id' => 'identity',     'icon' => 'building-2',    'label_fr' => 'Identité',        'label_en' => 'Identity'],
                ['id' => 'hero',         'icon' => 'image',          'label_fr' => 'Hero Section',    'label_en' => 'Hero Section'],
                ['id' => 'proviseur',    'icon' => 'user-tie',       'label_fr' => 'Proviseur',       'label_en' => 'Director'],
                ['id' => 'about',        'icon' => 'info',           'label_fr' => 'À Propos',        'label_en' => 'About'],
                ['id' => 'testimonials', 'icon' => 'message-square', 'label_fr' => 'Témoignages',     'label_en' => 'Testimonials'],
                ['id' => 'contact',      'icon' => 'phone',          'label_fr' => 'Contact',         'label_en' => 'Contact'],
                ['id' => 'academic',     'icon' => 'graduation-cap', 'label_fr' => 'Académique',      'label_en' => 'Academic'],
                ['id' => 'notifications','icon' => 'bell',           'label_fr' => 'Notifications',   'label_en' => 'Notifications'],
            ] as $t)
            <button type="button"
                wire:click="setActiveTab('{{ $t['id'] }}')"
                class="btn btn-link px-3 py-2 text-decoration-none border-0"
                style="border-bottom:3px solid transparent; margin-bottom:-2px; transition:all .2s ease; {{ $activeTab === $t['id'] ? 'border-bottom-color: var(--primary); color: var(--primary); font-weight: bold;' : '' }}"
            >
                <i data-lucide="{{ $t['icon'] }}" style="width:16px;height:16px;vertical-align:middle"></i>
                <span class="ms-1">{{ $isFr ? $t['label_fr'] : $t['label_en'] }}</span>
            </button>
            @endforeach
        </div>
    </div>

    {{-- Tab Content --}}
    <div wire:loading.remove>
        {{-- IDENTITÉ --}}
        @if($activeTab === 'identity')
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">{{ $isFr ? 'Identité de la Plateforme' : 'Platform Identity' }}</h5>
            </div>
            <div class="card-body">
                <form wire:submit="saveTab">
                    <div class="row gy-4">
                        <div class="col-md-6">
                            <label class="form-label">{{ $isFr ? 'Nom de la Plateforme' : 'Platform Name' }}<span class="text-danger">*</span></label>
                            <input type="text" wire:model="formData.platform_name" class="form-control" placeholder="Millénaire Connect">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">{{ $isFr ? 'Slogan' : 'Tagline' }}</label>
                            <input type="text" wire:model="formData.slogan" class="form-control">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">{{ $isFr ? 'Couleur Principale' : 'Primary Color' }}</label>
                            <div class="d-flex gap-2">
                                <input type="color" wire:model="formData.primary_color" class="form-control form-control-color" style="width:60px;height:40px">
                                <input type="text" wire:model="formData.primary_color" class="form-control">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">{{ $isFr ? 'Couleur Secondaire' : 'Secondary Color' }}</label>
                            <div class="d-flex gap-2">
                                <input type="color" wire:model="formData.secondary_color" class="form-control form-control-color" style="width:60px;height:40px">
                                <input type="text" wire:model="formData.secondary_color" class="form-control">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">{{ $isFr ? "Années d'Existence" : 'Years of Existence' }}</label>
                            <input type="number" wire:model="formData.years_existence" class="form-control" min="1" max="200">
                        </div>
                    </div>
                    <div class="d-flex gap-2 mt-4">
                        <button type="submit" class="btn btn-primary" wire:loading.attr="disabled">
                            <span wire:loading.remove><i data-lucide="save" style="width:16px" class="me-1"></i>{{ $isFr ? 'Enregistrer' : 'Save' }}</span>
                            <span wire:loading><i class="spinner-border spinner-border-sm me-1"></i>{{ $isFr ? 'Enregistrement...' : 'Saving...' }}</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
        @endif

        {{-- HERO SECTION --}}
        @if($activeTab === 'hero')
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Hero Section</h5>
            </div>
            <div class="card-body">
                <form wire:submit="saveTab">
                    <div class="row gy-4">
                        <div class="col-md-6">
                            <label class="form-label">{{ $isFr ? 'Titre Principal' : 'Main Title' }}</label>
                            <input type="text" wire:model="formData.hero_title" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">{{ $isFr ? 'Texte Bouton CTA' : 'CTA Button Text' }}</label>
                            <input type="text" wire:model="formData.hero_cta_text" class="form-control">
                        </div>
                        <div class="col-12">
                            <label class="form-label">{{ $isFr ? 'Sous-titre / Description' : 'Subtitle / Description' }}</label>
                            <textarea wire:model="formData.hero_subtitle" class="form-control" rows="3"></textarea>
                        </div>
                    </div>
                    <div class="d-flex gap-2 mt-4">
                        <button type="submit" class="btn btn-primary" wire:loading.attr="disabled">
                            <span wire:loading.remove><i data-lucide="save" style="width:16px" class="me-1"></i>{{ $isFr ? 'Enregistrer' : 'Save' }}</span>
                            <span wire:loading><i class="spinner-border spinner-border-sm me-1"></i>{{ $isFr ? 'Enregistrement...' : 'Saving...' }}</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
        @endif

        {{-- PROVISEUR --}}
        @if($activeTab === 'proviseur')
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">{{ $isFr ? 'Profil du Proviseur / Directeur' : 'Director Profile' }}</h5>
            </div>
            <div class="card-body">
                <form wire:submit="saveTab">
                    <div class="row gy-4">
                        <div class="col-md-6">
                            <label class="form-label">{{ $isFr ? 'Nom Complet' : 'Full Name' }}</label>
                            <input type="text" wire:model="formData.proviseur_name" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">{{ $isFr ? 'Titre du Poste' : 'Job Title' }}</label>
                            <input type="text" wire:model="formData.proviseur_title" class="form-control">
                        </div>
                        <div class="col-12">
                            <label class="form-label">{{ $isFr ? 'Message / Bio' : 'Message / Bio' }}</label>
                            <textarea wire:model="formData.proviseur_bio" class="form-control" rows="5"></textarea>
                        </div>
                    </div>
                    <div class="d-flex gap-2 mt-4">
                        <button type="submit" class="btn btn-primary" wire:loading.attr="disabled">
                            <span wire:loading.remove><i data-lucide="save" style="width:16px" class="me-1"></i>{{ $isFr ? 'Enregistrer' : 'Save' }}</span>
                            <span wire:loading><i class="spinner-border spinner-border-sm me-1"></i>{{ $isFr ? 'Enregistrement...' : 'Saving...' }}</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
        @endif

        {{-- À PROPOS --}}
        @if($activeTab === 'about')
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">{{ $isFr ? 'Section À Propos' : 'About Section' }}</h5>
            </div>
            <div class="card-body">
                <form wire:submit="saveTab">
                    <div class="row gy-4">
                        <div class="col-md-6">
                            <label class="form-label">{{ $isFr ? 'Titre Section À Propos' : 'About Section Title' }}</label>
                            <input type="text" wire:model="formData.about_title" class="form-control">
                        </div>
                        <div class="col-12">
                            <label class="form-label">{{ $isFr ? 'Description Générale' : 'General Description' }}</label>
                            <textarea wire:model="formData.about_description" class="form-control" rows="6"></textarea>
                        </div>
                    </div>
                    <div class="d-flex gap-2 mt-4">
                        <button type="submit" class="btn btn-primary" wire:loading.attr="disabled">
                            <span wire:loading.remove><i data-lucide="save" style="width:16px" class="me-1"></i>{{ $isFr ? 'Enregistrer' : 'Save' }}</span>
                            <span wire:loading><i class="spinner-border spinner-border-sm me-1"></i>{{ $isFr ? 'Enregistrement...' : 'Saving...' }}</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
        @endif

        {{-- TÉMOIGNAGES --}}
        @if($activeTab === 'testimonials')
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">{{ $isFr ? 'Gestion des Témoignages' : 'Testimonials Management' }}</h5>
                <a href="{{ route('admin.testimonials.create') }}" class="btn btn-primary btn-sm">
                    <i data-lucide="plus" style="width:14px" class="me-1"></i>{{ $isFr ? 'Ajouter' : 'Add' }}
                </a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>{{ $isFr ? 'Nom' : 'Name' }}</th>
                                <th>{{ $isFr ? 'Rôle' : 'Role' }}</th>
                                <th>{{ $isFr ? 'Statut' : 'Status' }}</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($testimonials ?? [] as $testimonial)
                            <tr>
                                <td>{{ $testimonial['name'] ?? '' }}</td>
                                <td>{{ $testimonial['role'] ?? '' }}</td>
                                <td><span class="badge {{ ($testimonial['is_active'] ?? false) ? 'bg-success' : 'bg-secondary' }}">{{ ($testimonial['is_active'] ?? false) ? ($isFr ? 'Actif' : 'Active') : ($isFr ? 'Inactif' : 'Inactive') }}</span></td>
                                <td>
                                    <a href="{{ route('admin.testimonials.edit', $testimonial['id'] ?? 0) }}" class="btn btn-sm btn-light"><i data-lucide="edit-2" style="width:14px"></i></a>
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="4" class="text-center py-3 text-muted">{{ $isFr ? 'Aucun témoignage' : 'No testimonials' }}</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        @endif

        {{-- CONTACT --}}
        @if($activeTab === 'contact')
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">{{ $isFr ? 'Informations de Contact' : 'Contact Information' }}</h5>
            </div>
            <div class="card-body">
                <form wire:submit="saveTab">
                    <div class="row gy-4">
                        <div class="col-md-6">
                            <label class="form-label">{{ $isFr ? 'Téléphone' : 'Phone' }}</label>
                            <input type="text" wire:model="formData.phone" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Email</label>
                            <input type="email" wire:model="formData.email" class="form-control">
                        </div>
                        <div class="col-12">
                            <label class="form-label">{{ $isFr ? 'Adresse' : 'Address' }}</label>
                            <input type="text" wire:model="formData.address" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Facebook</label>
                            <input type="url" wire:model="formData.social_facebook" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Twitter / X</label>
                            <input type="url" wire:model="formData.social_twitter" class="form-control">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Google Maps Embed URL</label>
                            <input type="url" wire:model="formData.google_maps_url" class="form-control">
                        </div>
                    </div>
                    <div class="d-flex gap-2 mt-4">
                        <button type="submit" class="btn btn-primary" wire:loading.attr="disabled">
                            <span wire:loading.remove><i data-lucide="save" style="width:16px" class="me-1"></i>{{ $isFr ? 'Enregistrer' : 'Save' }}</span>
                            <span wire:loading><i class="spinner-border spinner-border-sm me-1"></i>{{ $isFr ? 'Enregistrement...' : 'Saving...' }}</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
        @endif

        {{-- ACADÉMIQUE --}}
        @if($activeTab === 'academic')
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">{{ $isFr ? 'Configuration Académique' : 'Academic Configuration' }}</h5>
            </div>
            <div class="card-body">
                <form wire:submit="saveTab">
                    <div class="row gy-4">
                        <div class="col-md-6">
                            <label class="form-label">{{ $isFr ? 'Système de Notation Anglophone' : 'Anglophone Grading System' }}</label>
                            <select wire:model="formData.anglophone_grading" class="form-select">
                                <option value="letter">A–F (Letter Grades)</option>
                                <option value="percentage">Percentage %</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">{{ $isFr ? 'Nombre de Séquences par Trimestre' : 'Number of Sequences per Term' }}</label>
                            <select wire:model="formData.sequences_per_term" class="form-select">
                                <option value="2">2 {{ $isFr ? 'séquences' : 'sequences' }}</option>
                                <option value="3">3 {{ $isFr ? 'séquences' : 'sequences' }}</option>
                            </select>
                        </div>
                    </div>
                    <div class="d-flex gap-2 mt-4">
                        <button type="submit" class="btn btn-primary" wire:loading.attr="disabled">
                            <span wire:loading.remove><i data-lucide="save" style="width:16px" class="me-1"></i>{{ $isFr ? 'Enregistrer' : 'Save' }}</span>
                            <span wire:loading><i class="spinner-border spinner-border-sm me-1"></i>{{ $isFr ? 'Enregistrement...' : 'Saving...' }}</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
        @endif

        {{-- NOTIFICATIONS --}}
        @if($activeTab === 'notifications')
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">{{ $isFr ? 'Paramètres Notifications' : 'Notification Settings' }}</h5>
            </div>
            <div class="card-body">
                <form wire:submit="saveTab">
                    <div class="row gy-4">
                        <div class="col-md-6">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" wire:model="formData.notify_absence_parent" id="notifAbsence">
                                <label class="form-check-label" for="notifAbsence">{{ $isFr ? 'Notifier parent lors d\'une absence' : 'Notify parent on absence' }}</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" wire:model="formData.notify_new_bulletin" id="notifBulletin">
                                <label class="form-check-label" for="notifBulletin">{{ $isFr ? 'Notifier publication bulletin' : 'Notify on bulletin publication' }}</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" wire:model="formData.notify_payment_success" id="notifPayment">
                                <label class="form-check-label" for="notifPayment">{{ $isFr ? 'Notifier paiement confirmé' : 'Notify on confirmed payment' }}</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" wire:model="formData.email_notifications" id="notifEmail">
                                <label class="form-check-label" for="notifEmail">{{ $isFr ? 'Envoyer emails notification' : 'Send notification emails' }}</label>
                            </div>
                        </div>
                    </div>
                    <div class="d-flex gap-2 mt-4">
                        <button type="submit" class="btn btn-primary" wire:loading.attr="disabled">
                            <span wire:loading.remove><i data-lucide="save" style="width:16px" class="me-1"></i>{{ $isFr ? 'Enregistrer' : 'Save' }}</span>
                            <span wire:loading><i class="spinner-border spinner-border-sm me-1"></i>{{ $isFr ? 'Enregistrement...' : 'Saving...' }}</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
        @endif
    </div>

    {{-- Loading State --}}
    <div wire:loading class="text-center py-5">
        <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">{{ $isFr ? 'Chargement...' : 'Loading...' }}</span>
        </div>
    </div>

    <style>
        button.btn-link.active {
            border-bottom-color: var(--primary) !important;
            color: var(--primary) !important;
            font-weight: bold !important;
        }
    </style>
</div>
