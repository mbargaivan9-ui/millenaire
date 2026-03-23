@php
    $isFr = auth()->user()->language ?? 'fr' === 'fr';
@endphp

<x-app-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center">
            <h2 class="font-size-h2 font-weight-bolder text-dark mb-0">
                {{ $isFr ? 'Mes Bulletins' : 'My Report Cards' }}
            </h2>
        </div>
    </x-slot>

    <div class="container-fluid py-4">
        @if($bulletins->count())
            <div class="row">
                @foreach($bulletins as $bulletin)
                    <div class="col-md-6 col-lg-4 mb-4">
                        <div class="card border-0 shadow-sm h-100 hover-shadow-lg transition">
                            <div class="card-body">
                                <h5 class="card-title text-primary">
                                    {{ $bulletin->classe->name ?? 'N/A' }}
                                </h5>
                                <p class="text-muted small mb-3">
                                    <i class="fas fa-book"></i>
                                    {{ $isFr ? 'Trimestre' : 'Term' }} {{ $bulletin->term }}
                                    • {{ $isFr ? 'Séquence' : 'Sequence' }} {{ $bulletin->sequence }}
                                </p>

                                <div class="badge bg-success mb-3">
                                    {{ $isFr ? 'Publié' : 'Published' }}
                                </div>

                                <p class="text-muted small">
                                    {{ $isFr ? 'Mise à jour:' : 'Updated:' }}
                                    <br>
                                    {{ $bulletin->updated_at->format('d/m/Y H:i') }}
                                </p>
                            </div>
                            <div class="card-footer bg-transparent border-top">
                                <a href="{{ route('student.bulletins.show', $bulletin->id) }}" class="btn btn-sm btn-primary me-2">
                                    <i class="fas fa-eye"></i> {{ $isFr ? 'Voir' : 'View' }}
                                </a>
                                <a href="{{ route('student.bulletins.pdf', $bulletin->id) }}" class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-download"></i> PDF
                                </a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Pagination -->
            <div class="d-flex justify-content-center mt-4">
                {{ $bulletins->links() }}
            </div>
        @else
            <div class="alert alert-info text-center py-5">
                <i class="fas fa-info-circle fa-3x mb-3 d-block text-muted"></i>
                <h5>{{ $isFr ? 'Aucun bulletin disponible' : 'No bulletins available' }}</h5>
                <p class="text-muted">{{ $isFr ? 'Vos bulletins apparaîtront ici une fois publiés.' : 'Your report cards will appear here once published.' }}</p>
            </div>
        @endif
    </div>
</x-app-layout>
