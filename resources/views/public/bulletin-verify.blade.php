{{-- public/bulletin-verify.blade.php --}}
@extends('layouts.public')

@php
  $pageTitle = $pageTitle ?? (app()->getLocale() === 'fr' ? 'Vérification de Bulletin' : 'Report Card Verification');
@endphp

@section('title', $pageTitle)
@section('content')
@php $isFr = app()->getLocale() === 'fr'; @endphp

<section class="py-5" style="min-height:70vh;display:flex;align-items:center">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card" style="border-radius:var(--radius-lg);box-shadow:var(--shadow-lg)">
                    <div class="card-body p-5 text-center">
                        @if(isset($bulletin) && $bulletin)
                            {{-- Valid bulletin --}}
                            <div style="width:72px;height:72px;border-radius:50%;background:#ecfdf5;color:#059669;font-size:2rem;display:flex;align-items:center;justify-content:center;margin:0 auto 1.5rem">✓</div>

                            <h2 class="fw-bold mb-1" style="font-size:1.4rem">
                                {{ $isFr ? 'Bulletin authentique' : 'Authentic report card' }}
                            </h2>
                            <p style="color:var(--text-muted);font-size:.88rem;margin-bottom:2rem">
                                {{ $isFr ? 'Ce bulletin a été émis et signé par' : 'This report card was issued and signed by' }}
                                <strong>{{ $settings->school_name_fr ?? config('app.name') }}</strong>
                            </p>

                            <div style="background:var(--surface-2);border-radius:12px;padding:1.25rem;text-align:left">
                                @foreach([
                                    ($isFr ? 'Élève' : 'Student')    => $bulletin->student?->user?->name,
                                    ($isFr ? 'Classe' : 'Class')     => $bulletin->student?->classe?->name,
                                    ($isFr ? 'Trimestre' : 'Term')   => 'T'.$bulletin->term.' — S'.$bulletin->sequence,
                                    ($isFr ? 'Année' : 'Year')       => $bulletin->academic_year ?? '2025/2026',
                                    ($isFr ? 'Moyenne' : 'Average')  => number_format((float)($bulletin->moyenne ?? 0), 2).'/20',
                                    ($isFr ? 'Émis le' : 'Issued')   => $bulletin->published_at?->format('d/m/Y') ?? '—',
                                ] as $lbl => $val)
                                <div class="d-flex justify-content-between mb-2" style="font-size:.85rem">
                                    <span style="color:var(--text-muted)">{{ $lbl }}</span>
                                    <strong>{{ $val ?? '—' }}</strong>
                                </div>
                                @endforeach
                            </div>

                        @else
                            {{-- Invalid / not found --}}
                            <div style="width:72px;height:72px;border-radius:50%;background:#fef2f2;color:#dc2626;font-size:2rem;display:flex;align-items:center;justify-content:center;margin:0 auto 1.5rem">✗</div>

                            <h2 class="fw-bold mb-1" style="font-size:1.4rem;color:#dc2626">
                                {{ $isFr ? 'Bulletin introuvable' : 'Report card not found' }}
                            </h2>
                            <p style="color:var(--text-muted);font-size:.88rem">
                                {{ $message ?? ($isFr
                                    ? 'Ce QR code ne correspond à aucun bulletin valide dans notre système.'
                                    : 'This QR code does not match any valid report card in our system.') }}
                            </p>
                        @endif

                        <a href="{{ route('home') }}" class="btn btn-light mt-4 w-100">
                            {{ $isFr ? '← Retour à l\'accueil' : '← Back to home' }}
                        </a>
                    </div>
                </div>

                <p class="text-center text-muted mt-3" style="font-size:.75rem">
                    {{ $isFr ? 'Vérification effectuée le' : 'Verified on' }}
                    {{ now()->locale($isFr ? 'fr' : 'en')->isoFormat('D MMMM YYYY [à] HH:mm') }}
                </p>
            </div>
        </div>
    </div>
</section>

@endsection
