{{-- components/admin/specialized-sidebar.blade.php --}}
@php
$user = auth()->user();
$isFr = app()->getLocale() === 'fr';

// Get specialized role assignment
$specializedRole = $user->specializedRoles()
    ->where('deactivated_at', null)
    ->with(['role', 'role.sections'])
    ->first();

// Get accessible sections
$sections = collect();
if ($specializedRole) {
    if (!empty($specializedRole->assigned_sections)) {
        // Specific sections assigned
        $sections = \App\Models\AdminRoleSection::whereIn('id', $specializedRole->assigned_sections)
            ->where('is_active', true)
            ->orderBy('order')
            ->get();
    } else {
        // All sections from role
        $sections = $specializedRole->role->sections()
            ->where('is_active', true)
            ->orderBy('order')
            ->get();
    }
}
@endphp

<div class="specialized-admin-sidebar">
    {{-- Role Header --}}
    @if($specializedRole)
    <div class="role-header" style="
        background: linear-gradient(135deg, {{ $specializedRole->role->color }}, {{ $specializedRole->role->color }}dd);
        padding: 1.5rem;
        border-radius: 12px;
        color: white;
        margin-bottom: 1.5rem;
    ">
        <div style="display:flex;align-items:center;gap:1rem">
            <div style="font-size:2rem">{{ $specializedRole->role->icon ?? '📋' }}</div>
            <div>
                <div class="fw-bold" style="font-size:1.1rem">{{ $specializedRole->role->name }}</div>
                <div style="font-size:0.85rem;opacity:0.9">{{ $user->name }}</div>
            </div>
        </div>
        <div style="margin-top:1rem;font-size:0.85rem;padding-top:1rem;border-top:1px solid rgba(255,255,255,0.2)">
            {{ $specializedRole->role->description }}
        </div>
    </div>

    {{-- Sections Navigation --}}
    <nav class="sections-nav">
        <h6 class="nav-title text-uppercase text-muted" style="font-size:0.75rem;letter-spacing:0.5px;padding:0 1rem;margin-bottom:0.5rem">
            {{ $isFr ? 'Sections Accessibles' : 'Accessible Sections' }}
        </h6>

        <ul class="nav flex-column" style="gap:0.5rem">
            @forelse($sections as $section)
            <li class="nav-item">
                <a href="{{ route($section->route) }}" 
                   class="nav-link {{ request()->routeIs(str_replace('.index', '.*', $section->route)) ? 'active' : '' }}"
                   style="padding:0.75rem 1rem;border-radius:8px;transition:all 0.15s">
                    <span style="font-size:1.2rem;margin-right:0.75rem">{{ $section->icon ?? '📋' }}</span>
                    <span style="flex:1">{{ $section->name }}</span>
                    @if(request()->routeIs(str_replace('.index', '.*', $section->route)))
                    <i data-lucide="chevron-right" style="width:16px"></i>
                    @endif
                </a>
            </li>
            @empty
            <li class="nav-item">
                <div style="padding:1rem;text-align:center;color:var(--text-secondary);font-size:0.85rem">
                    {{ $isFr ? 'Aucune section disponible' : 'No sections available' }}
                </div>
            </li>
            @endforelse
        </ul>

        {{-- Common Sections --}}
        <hr style="margin:1rem 0;opacity:0.3">

        <h6 class="nav-title text-uppercase text-muted" style="font-size:0.75rem;letter-spacing:0.5px;padding:0 1rem;margin-bottom:0.5rem">
            {{ $isFr ? 'Outils' : 'Tools' }}
        </h6>

        <ul class="nav flex-column" style="gap:0.5rem">
            <li class="nav-item">
                <a href="{{ route('admin.settings.edit') }}" 
                   class="nav-link {{ request()->routeIs('admin.settings.*') ? 'active' : '' }}"
                   style="padding:0.75rem 1rem;border-radius:8px;transition:all 0.15s">
                    <i data-lucide="settings" style="width:18px;margin-right:0.75rem"></i>
                    {{ $isFr ? 'Paramètres' : 'Settings' }}
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('admin.reports.dashboard') }}" 
                   class="nav-link {{ request()->routeIs('admin.reports.*') ? 'active' : '' }}"
                   style="padding:0.75rem 1rem;border-radius:8px;transition:all 0.15s">
                    <i data-lucide="bar-chart-2" style="width:18px;margin-right:0.75rem"></i>
                    {{ $isFr ? 'Rapports' : 'Reports' }}
                </a>
            </li>
        </ul>
    </nav>

    {{-- Action Buttons --}}
    <div style="margin-top:2rem;padding-top:1rem;border-top:1px solid var(--border)">
        <button type="button" class="btn btn-sm btn-outline-secondary w-100 mb-2"
                onclick="document.getElementById('chatToggle').click()">
            <i data-lucide="message-square" style="width:16px" class="me-1"></i>
            {{ $isFr ? 'Chat & Notifications' : 'Chat & Notifications' }}
        </button>
        <a href="{{ route('profile.edit') }}" class="btn btn-sm btn-outline-secondary w-100">
            <i data-lucide="user" style="width:16px" class="me-1"></i>
            {{ $isFr ? 'Mon Profil' : 'My Profile' }}
        </a>
    </div>

    {{-- Activity Card --}}
    <div style="margin-top:1.5rem;padding:1rem;background:var(--bg-secondary);border-radius:8px;font-size:0.85rem">
        <div class="fw-600 mb-2">
            <i data-lucide="activity" style="width:14px" class="me-1"></i>
            {{ $isFr ? 'Activités Récentes' : 'Recent Activities' }}
        </div>
        @php
        $recentLogs = $user->adminSectionLogs()
            ->with('section')
            ->latest('logged_at')
            ->limit(5)
            ->get();
        @endphp
        @forelse($recentLogs as $log)
        <div style="padding:0.5rem;border-bottom:1px solid var(--border);font-size:0.75rem">
            <div style="color:var(--text-secondary)">{{ $log->section->icon ?? '' }} {{ $log->section->name }}</div>
            <div style="color:var(--text-secondary)">{{ $log->action }} • {{ $log->logged_at->diffForHumans() }}</div>
        </div>
        @empty
        <div style="color:var(--text-secondary);text-align:center;padding:0.5rem">
            {{ $isFr ? 'Aucune activité' : 'No activity' }}
        </div>
        @endforelse
    </div>

@else
    {{-- No Specialized Role --}}
    <div style="padding:2rem;text-align:center">
        <div style="font-size:3rem;margin-bottom:1rem">👤</div>
        <div class="fw-600">{{ $isFr ? 'Pas de rôle spécialisé' : 'No specialized role' }}</div>
        <div style="font-size:0.85rem;color:var(--text-secondary);margin-top:0.5rem">
            {{ $isFr 
                ? 'Aucun rôle spécialisé n\'a été assigné à votre compte'
                : 'No specialized role assigned to your account' }}
        </div>
    </div>
@endif
</div>

<style>
.specialized-admin-sidebar {
    padding: 1rem;
}

.sections-nav .nav-link {
    display: flex;
    align-items: center;
    color: var(--text-secondary);
    cursor: pointer;
}

.sections-nav .nav-link:hover {
    background: var(--bg-secondary);
    color: var(--text-primary);
}

.sections-nav .nav-link.active {
    background: #6366f1;
    color: white;
    font-weight: 600;
}

.role-header {
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.nav-title {
    display: block;
    font-weight: 600;
    margin-bottom: 0.75rem;
}
</style>
