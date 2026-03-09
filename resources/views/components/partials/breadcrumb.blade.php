{{-- Composant Breadcrumb --}}

<nav aria-label="breadcrumb" class="mb-4">
    <ol class="breadcrumb bg-transparent px-0 mb-0">
        <li class="breadcrumb-item">
            <a href="{{ route('admin.dashboard') }}" class="text-primary text-decoration-none">
                <i class="fas fa-home me-2"></i>Accueil
            </a>
        </li>
        {{ $slot }}
    </ol>
</nav>
