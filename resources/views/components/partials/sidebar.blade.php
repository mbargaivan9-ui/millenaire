{{-- Composant Sidebar Admin --}}

<aside class="sidebar bg-white border-end" style="width: 280px; position: sticky; top: 73px; height: calc(100vh - 73px); overflow-y: auto;">
    <nav class="p-4 pt-3">
        {{-- Menu Items --}}
        <div class="nav flex-column">
            {{-- Dashboard --}}
            <a href="{{ route('admin.dashboard') }}" class="nav-link px-3 py-2 rounded-3 mb-2 {{ Route::is('admin.dashboard') ? 'bg-primary text-white' : 'text-dark' }}">
                <i class="fas fa-chart-line me-2"></i>
                <span>Tableau de Bord</span>
            </a>

            {{-- Gestion des Utilisateurs --}}
            <a href="{{ route('admin.users.index') }}" class="nav-link px-3 py-2 rounded-3 mb-2 {{ Route::is('admin.users.*') ? 'bg-primary text-white' : 'text-dark' }}">
                <i class="fas fa-users me-2"></i>
                <span>Utilisateurs</span>
            </a>

            {{-- Classes --}}
            <a href="{{ route('admin.classes.index') }}" class="nav-link px-3 py-2 rounded-3 mb-2 {{ Route::is('admin.classes.*') ? 'bg-primary text-white' : 'text-dark' }}">
                <i class="fas fa-school me-2"></i>
                <span>Classes</span>
            </a>

            {{-- Matières --}}
            <a href="{{ route('admin.subjects.index') }}" class="nav-link px-3 py-2 rounded-3 mb-2 {{ Route::is('admin.subjects.*') ? 'bg-primary text-white' : 'text-dark' }}">
                <i class="fas fa-book me-2"></i>
                <span>Matières</span>
            </a>

            {{-- Affectations Professeurs --}}
            <a href="{{ route('admin.assignments.index') }}" class="nav-link px-3 py-2 rounded-3 mb-2 {{ Route::is('admin.assignments.*') ? 'bg-primary text-white' : 'text-dark' }}">
                <i class="fas fa-user-tie me-2"></i>
                <span>Affectations</span>
            </a>

            {{-- Module Financier --}}
            <a href="{{ route('admin.finance.index') }}" class="nav-link px-3 py-2 rounded-3 mb-2 {{ Route::is('admin.finance.*') ? 'bg-primary text-white' : 'text-dark' }}">
                <i class="fas fa-coins me-2"></i>
                <span>Finances</span>
            </a>

            {{-- Annonces --}}
            <a href="{{ route('admin.announcements.index') }}" class="nav-link px-3 py-2 rounded-3 mb-2 {{ Route::is('admin.announcements.*') ? 'bg-primary text-white' : 'text-dark' }}">
                <i class="fas fa-bullhorn me-2"></i>
                <span>Annonces</span>
            </a>

            {{-- Rôles et Permissions --}}
            <a href="{{ route('admin.roles.index') }}" class="nav-link px-3 py-2 rounded-3 mb-2 {{ Route::is('admin.roles.*') ? 'bg-primary text-white' : 'text-dark' }}">
                <i class="fas fa-shield-alt me-2"></i>
                <span>Rôles</span>
            </a>

            {{-- Paramètres --}}
            <a href="{{ route('admin.settings.edit') }}" class="nav-link px-3 py-2 rounded-3 mb-2 {{ Route::is('admin.settings.*') ? 'bg-primary text-white' : 'text-dark' }}">
                <i class="fas fa-cog me-2"></i>
                <span>Paramètres</span>
            </a>
        </div>

        {{-- Version Info --}}
        <div class="mt-5 pt-4 border-top text-center">
            <p class="text-muted small mb-0">Millénaire v2.0</p>
            <p class="text-muted small mb-0">{{ Auth::user()?->role }}</p>
        </div>
    </nav>
</aside>
