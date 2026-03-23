{{--
=================================================================
SIDEBAR SNIPPET — Bulletin Intelligent
=================================================================
Remplacer les deux blocs sidebar existants liés aux bulletins
(blocs "Bulletin Vivant" et "Template Grid / Prof Principal")
par ce nouveau bloc dans :
resources/views/layouts/partials/sidebar.blade.php
=================================================================
--}}

{{-- ═══ BULLETINS INTELLIGENTS — Tous enseignants ═══ --}}
<div class="sidebar-item {{ request()->routeIs('teacher.bulletin.*') ? 'active open' : '' }}"
     data-toggle="sub-smart-bulletin">
    <span class="sidebar-icon"><i data-lucide="file-text"></i></span>
    <span class="sidebar-label">Bulletins</span>
    <svg class="sidebar-arrow" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
         stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <polyline points="9 18 15 12 9 6"/>
    </svg>
</div>
<div class="sidebar-submenu {{ request()->routeIs('teacher.bulletin.*') ? 'open' : '' }}" id="sub-smart-bulletin">

    {{-- Toujours visible : liste des classes --}}
    <a href="{{ route('teacher.bulletin.index') }}"
       class="sidebar-subitem {{ request()->routeIs('teacher.bulletin.index') ? 'active' : '' }}">
        <i class="fas fa-list me-2"></i>Mes classes
    </a>

    {{-- Saisie des notes (enseignants) --}}
    <a href="{{ route('teacher.bulletin.index') }}"
       class="sidebar-subitem {{ request()->routeIs('teacher.bulletin.grade-entry') ? 'active' : '' }}">
        <i class="fas fa-edit me-2"></i>Saisir mes notes
    </a>

    {{-- ── Prof Principal uniquement ── --}}
    @if(auth()->user()?->teacher?->is_prof_principal || auth()->user()?->role === 'admin')
        <div class="sidebar-divider my-1 border-t border-white/10"></div>

        <div class="px-3 py-1">
            <span class="text-[10px] font-bold uppercase tracking-wider opacity-50 flex items-center gap-1">
                <i class="fas fa-crown text-yellow-400 text-xs"></i> Prof Principal
            </span>
        </div>

        {{-- Upload IA --}}
        <a href="{{ route('teacher.bulletin.index') }}#upload"
           class="sidebar-subitem {{ request()->routeIs('teacher.bulletin.upload','teacher.bulletin.processUpload','teacher.bulletin.template.*') ? 'active' : '' }}">
            <i class="fas fa-upload me-2 text-purple-400"></i>
            <span>Upload &amp; IA</span>
        </a>

        {{-- Tableau de bord --}}
        <a href="{{ route('teacher.bulletin.index') }}#dashboard"
           class="sidebar-subitem {{ request()->routeIs('teacher.bulletin.dashboard') ? 'active' : '' }}">
            <i class="fas fa-chart-bar me-2 text-indigo-400"></i>Tableau de bord
        </a>

        {{-- Barème (admin only) --}}
        @if(auth()->user()?->role === 'admin')
            <a href="{{ route('admin.bulletin.grade-scales') }}"
               class="sidebar-subitem {{ request()->routeIs('admin.bulletin.grade-scales*') ? 'active' : '' }}">
                <i class="fas fa-sliders-h me-2 text-orange-400"></i>Barème notes
            </a>

            <a href="{{ route('admin.bulletin.analytics') }}"
               class="sidebar-subitem {{ request()->routeIs('admin.bulletin.analytics') ? 'active' : '' }}">
                <i class="fas fa-chart-line me-2 text-green-400"></i>Analytique
            </a>

            <a href="{{ route('admin.bulletin.index') }}"
               class="sidebar-subitem {{ request()->routeIs('admin.bulletin.index','admin.bulletin.show') ? 'active' : '' }}">
                <i class="fas fa-paper-plane me-2 text-blue-400"></i>Publier bulletins
            </a>
        @endif
    @endif
</div>
