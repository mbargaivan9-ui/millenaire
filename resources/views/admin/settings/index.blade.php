{{--
    |--------------------------------------------------------------------------
    | admin/settings/index.blade.php — Interface Paramètres Admin Refactorisée
    |--------------------------------------------------------------------------
    | Phase 10 — Interface Tabbed avec Livewire
    | Chargement dynamique, sauvegarde AJAX, mise à jour en temps réel
    --}}

@extends('layouts.app')

@section('title', __('admin.settings'))

@section('content')

<livewire:admin.settings-tabs />

@endsection

@push('scripts')
<script>
    // Écouter les événements de sauvegarde
    Livewire.on('settingsSaved', ({ tab }) => {
        console.log('Tab saved:', tab);
        // Recharger les données côté client si nécessaire
    });

    // Broadcaster les mises à jour en temps réel
    if (typeof window.Echo !== 'undefined') {
        window.Echo.channel('settings').listen('SettingsUpdated', (data) => {
            console.log('Settings updated in real-time:', data);
            // Mettre à jour les éléments visibles de la page publique
            updatePublicContentRealtime(data);
        });
    }

    function updatePublicContentRealtime(data) {
        // Met à jour le contenu de la page publique en temps réel
        if (data.platform_name) {
            document.querySelectorAll('[data-content="platform_name"]').forEach(el => {
                el.textContent = data.platform_name;
            });
        }
        if (data.primary_color) {
            document.documentElement.style.setProperty('--primary', data.primary_color);
        }
        // Ajouter d'autres mises à jour selon vos besoins
    }
</script>
@endpush
