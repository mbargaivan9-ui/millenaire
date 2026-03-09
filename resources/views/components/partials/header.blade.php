{{-- Composant Header Admin --}}
@props(['user' => null])

<header class="navbar navbar-expand-lg navbar-light bg-white border-bottom border-light sticky-top shadow-sm" style="--bs-bg-opacity: 0.95;">
    <nav class="container-fluid px-4 py-3">
        <a class="navbar-brand fw-bold text-primary d-flex align-items-center gap-2" href="{{ route('admin.dashboard') }}">
            <i class="fas fa-graduation-cap fa-lg"></i>
            <span>Le Millénaire</span>
        </a>
        
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarContent">
            <span class="navbar-toggler-icon"></span>
        </button>
        
        <div class="collapse navbar-collapse ms-0 ms-lg-4" id="navbarContent">
            {{-- Recherche globale --}}
            <div class="flex-grow-1 me-4">
                <div class="input-group rounded-3 bg-light border border-light">
                    <span class="input-group-text bg-transparent border-0">
                        <i class="fas fa-search text-muted"></i>
                    </span>
                    <input type="text" class="form-control border-0 bg-transparent" 
                           placeholder="Rechercher un élève, prof, classe..."
                           id="globalSearch">
                </div>
            </div>
        </div>
        
        {{-- Menu utilisateur --}}
        <div class="d-flex align-items-center gap-3 ms-auto">
            {{-- Notifications --}}
            <div class="dropdown">
                <button class="btn btn-link text-dark position-relative p-0" data-bs-toggle="dropdown">
                    <i class="fas fa-bell fa-lg"></i>
                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size: 0.65rem;">3</span>
                </button>
                <ul class="dropdown-menu dropdown-menu-end shadow-lg rounded-3">
                    <li><a class="dropdown-item" href="#"><i class="fas fa-user-plus text-primary me-2"></i>Nouvel inscription</a></li>
                    <li><a class="dropdown-item" href="#"><i class="fas fa-credit-card text-warning me-2"></i>Paiement dû</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item" href="#">Voir toutes les notifications</a></li>
                </ul>
            </div>

            {{-- Menu utilisateur --}}
            <div class="dropdown">
                <button class="btn btn-link text-dark p-0" data-bs-toggle="dropdown">
                    <img src="{{ Auth::user()?->avatar_url }}" 
                         class="rounded-circle" width="40" height="40" alt="Avatar" style="object-fit:cover">
                </button>
                <ul class="dropdown-menu dropdown-menu-end shadow-lg rounded-3">
                    <li><a class="dropdown-item" href="{{ route('account.profile') }}">
                        <i class="fas fa-user me-2"></i>Mon profil
                    </a></li>
                    <li><a class="dropdown-item" href="{{ route('account.profile') }}">
                        <i class="fas fa-cog me-2"></i>Paramètres
                    </a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li>
                        <form action="{{ route('logout') }}" method="POST" class="m-0">
                            @csrf
                            <button type="submit" class="dropdown-item text-danger">
                                <i class="fas fa-sign-out-alt me-2"></i>Déconnexion
                            </button>
                        </form>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
</header>
