@extends('layouts.public')

@section('title', $announcement->title ?? 'Annonce')

@section('content')
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-10">
                <div class="card mb-4">
                    @php $img = (isset($announcement->featured_image) ? (App\Models\EstablishmentSetting::getInstance() ? null : null) : null); @endphp
                    @if(!empty($announcement->featured_image))
                        <img src="{{ asset('storage/' . ltrim($announcement->featured_image, '/')) }}" class="card-img-top" alt="{{ $announcement->title }}">
                    @endif
                    <div class="card-body">
                        <h1 class="card-title">{{ $announcement->title }}</h1>
                        <p class="text-muted">Par {{ $announcement->author_name }} • {{ $announcement->published_date }}</p>
                        <div class="mt-4">
                            {!! $announcement->content !!}
                        </div>

                        @if(!empty($announcement->attachment_path))
                            <p class="mt-4">
                                <a href="{{ asset('storage/' . ltrim($announcement->attachment_path, '/')) }}" class="btn btn-outline-primary" target="_blank">Télécharger le fichier</a>
                            </p>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
