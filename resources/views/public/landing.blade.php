{{--
    | public/landing.blade.php — Page d'accueil publique (100% FRANÇAIS EN DUR)
    --}}

@extends('layouts.public')
@section('title', 'Millénaire Connect — Collège Millénaire Bilingue')

@section('content')

{{-- ─── Hero ─────────────────────────────────────────────────────────────────── --}}
<section style="
    background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 60%, #0a5a52 100%);
    color: #fff; padding: 6rem 0; position: relative; overflow: hidden;">
    <div style="position:absolute;inset:0;background:url('data:image/svg+xml,%3Csvg width=60 height=60 viewBox=0 0 60 60 xmlns=http://www.w3.org/2000/svg%3E%3Cg fill=none fill-rule=evenodd%3E%3Cg fill=%23ffffff fill-opacity=0.04%3E%3Ccircle cx=30 cy=30 r=4/%3E%3C/g%3E%3C/g%3E%3C/svg%3E');"></div>
    <div class="container" style="max-width:1200px;margin:0 auto;padding:0 1.5rem;position:relative">
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:4rem;align-items:center">
            <div>
                @php $logoUrl = \App\Helpers\SettingsHelper::logoUrl(); @endphp
                @if($logoUrl)
                <img src="{{ $logoUrl }}" style="height:64px;margin-bottom:1.5rem;border-radius:12px" alt="Millénaire Connect">
                @endif
                <h1 style="font-size:3rem;font-weight:900;line-height:1.1;margin-bottom:1rem">
                    Millénaire Connect
                </h1>
                <p style="font-size:1.15rem;opacity:.88;margin-bottom:2rem;line-height:1.7">
                    La plateforme digitale complète du Collège Millénaire Bilingue — Bulletins, présences, paiements et communication en temps réel.
                </p>
                <div style="display:flex;gap:1rem;flex-wrap:wrap">
                    <a href="{{ route('login') }}" style="background:#fff;color:var(--primary);padding:.85rem 2rem;border-radius:12px;font-weight:800;text-decoration:none;font-size:.95rem;transition:all .2s ease;display:inline-flex;align-items:center;gap:.5rem"
                       onmouseover="this.style.transform='translateY(-2px)';this.style.boxShadow='0 8px 25px rgba(0,0,0,.2)'"
                       onmouseout="this.style.transform='';this.style.boxShadow=''">
                        → Se connecter maintenant
                    </a>
                    <a href="#features" style="border:2px solid rgba(255,255,255,.5);color:#fff;padding:.85rem 2rem;border-radius:12px;font-weight:700;text-decoration:none;font-size:.95rem">
                        ↓ Découvrir nos services
                    </a>
                </div>
            </div>
            <div style="text-align:center">
                {{-- Stats --}}
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem">
                    <div style="background:rgba(255,255,255,.1);border:1px solid rgba(255,255,255,.15);border-radius:16px;padding:1.5rem;backdrop-filter:blur(4px);text-align:center">
                        <div style="font-size:2rem;margin-bottom:.25rem">🎓</div>
                        <div style="font-size:1.8rem;font-weight:900;margin-bottom:.2rem">600+</div>
                        <div style="font-size:.78rem;opacity:.8;font-weight:600">Élèves</div>
                    </div>
                    <div style="background:rgba(255,255,255,.1);border:1px solid rgba(255,255,255,.15);border-radius:16px;padding:1.5rem;backdrop-filter:blur(4px);text-align:center">
                        <div style="font-size:2rem;margin-bottom:.25rem">👨‍🏫</div>
                        <div style="font-size:1.8rem;font-weight:900;margin-bottom:.2rem">45+</div>
                        <div style="font-size:.78rem;opacity:.8;font-weight:600">Enseignants</div>
                    </div>
                    <div style="background:rgba(255,255,255,.1);border:1px solid rgba(255,255,255,.15);border-radius:16px;padding:1.5rem;backdrop-filter:blur(4px);text-align:center">
                        <div style="font-size:2rem;margin-bottom:.25rem">📊</div>
                        <div style="font-size:1.8rem;font-weight:900;margin-bottom:.2rem">3000+</div>
                        <div style="font-size:.78rem;opacity:.8;font-weight:600">Bulletins</div>
                    </div>
                    <div style="background:rgba(255,255,255,.1);border:1px solid rgba(255,255,255,.15);border-radius:16px;padding:1.5rem;backdrop-filter:blur(4px);text-align:center">
                        <div style="font-size:2rem;margin-bottom:.25rem">💬</div>
                        <div style="font-size:1.8rem;font-weight:900;margin-bottom:.2rem">99%</div>
                        <div style="font-size:.78rem;opacity:.8;font-weight:600">Satisfaction</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- ─── Features ─────────────────────────────────────────────────────────────── --}}
<section id="features" style="padding:5rem 0;background:#f8fafc">
    <div class="container" style="max-width:1200px;margin:0 auto;padding:0 1.5rem">
        <div style="text-align:center;margin-bottom:3rem">
            <h2 style="font-size:2rem;font-weight:900;color:#0f172a;margin-bottom:.75rem">
                Tout ce dont votre école a besoin
            </h2>
            <p style="font-size:1rem;color:#64748b;max-width:600px;margin:0 auto">
                Une plateforme complète et intégrée pour digitaliser la gestion scolaire
            </p>
        </div>
        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(280px,1fr));gap:1.5rem">
            <div style="background:#fff;border:1px solid #e2e8f0;border-radius:16px;padding:1.5rem;transition:all .2s ease"
                 onmouseover="this.style.transform='translateY(-4px)';this.style.boxShadow='0 12px 30px rgba(0,0,0,.08)'"
                 onmouseout="this.style.transform='';this.style.boxShadow=''">
                <div style="font-size:2rem;margin-bottom:1rem">📊</div>
                <h3 style="font-size:1rem;font-weight:800;color:#0f172a;margin-bottom:.5rem">Bulletins Numériques</h3>
                <p style="font-size:.85rem;color:#64748b;line-height:1.6;margin:0">Générés automatiquement avec signature électronique et QR code d'authentification.</p>
            </div>
            <div style="background:#fff;border:1px solid #e2e8f0;border-radius:16px;padding:1.5rem;transition:all .2s ease"
                 onmouseover="this.style.transform='translateY(-4px)';this.style.boxShadow='0 12px 30px rgba(0,0,0,.08)'"
                 onmouseout="this.style.transform='';this.style.boxShadow=''">
                <div style="font-size:2rem;margin-bottom:1rem">📅</div>
                <h3 style="font-size:1rem;font-weight:800;color:#0f172a;margin-bottom:.5rem">Emplois du Temps</h3>
                <p style="font-size:.85rem;color:#64748b;line-height:1.6;margin:0">Gestion complète des emplois du temps pour toutes les classes et enseignants.</p>
            </div>
            <div style="background:#fff;border:1px solid #e2e8f0;border-radius:16px;padding:1.5rem;transition:all .2s ease"
                 onmouseover="this.style.transform='translateY(-4px)';this.style.boxShadow='0 12px 30px rgba(0,0,0,.08)'"
                 onmouseout="this.style.transform='';this.style.boxShadow=''">
                <div style="font-size:2rem;margin-bottom:1rem">✅</div>
                <h3 style="font-size:1rem;font-weight:800;color:#0f172a;margin-bottom:.5rem">Appel & Présences</h3>
                <p style="font-size:.85rem;color:#64748b;line-height:1.6;margin:0">Appel numérique avec notifications parents et enseignants en temps réel.</p>
            </div>
            <div style="background:#fff;border:1px solid #e2e8f0;border-radius:16px;padding:1.5rem;transition:all .2s ease"
                 onmouseover="this.style.transform='translateY(-4px)';this.style.boxShadow='0 12px 30px rgba(0,0,0,.08)'"
                 onmouseout="this.style.transform='';this.style.boxShadow=''">
                <div style="font-size:2rem;margin-bottom:1rem">💰</div>
                <h3 style="font-size:1rem;font-weight:800;color:#0f172a;margin-bottom:.5rem">Paiements Mobile Money</h3>
                <p style="font-size:.85rem;color:#64748b;line-height:1.6;margin:0">Orange Money et MTN MoMo intégrés avec reçus PDF automatiques.</p>
            </div>
            <div style="background:#fff;border:1px solid #e2e8f0;border-radius:16px;padding:1.5rem;transition:all .2s ease"
                 onmouseover="this.style.transform='translateY(-4px)';this.style.boxShadow='0 12px 30px rgba(0,0,0,.08)'"
                 onmouseout="this.style.transform='';this.style.boxShadow=''">
                <div style="font-size:2rem;margin-bottom:1rem">💬</div>
                <h3 style="font-size:1rem;font-weight:800;color:#0f172a;margin-bottom:.5rem">Messagerie Temps Réel</h3>
                <p style="font-size:.85rem;color:#64748b;line-height:1.6;margin:0">Chat sécurisé entre enseignants, parents et administration.</p>
            </div>
            <div style="background:#fff;border:1px solid #e2e8f0;border-radius:16px;padding:1.5rem;transition:all .2s ease"
                 onmouseover="this.style.transform='translateY(-4px)';this.style.boxShadow='0 12px 30px rgba(0,0,0,.08)'"
                 onmouseout="this.style.transform='';this.style.boxShadow=''">
                <div style="font-size:2rem;margin-bottom:1rem">📚</div>
                <h3 style="font-size:1rem;font-weight:800;color:#0f172a;margin-bottom:.5rem">E-Learning Interactif</h3>
                <p style="font-size:.85rem;color:#64748b;line-height:1.6;margin:0">Ressources pédagogiques, quiz et devoirs en ligne avec suivi des progrès.</p>
            </div>
        </div>
    </div>
</section>

{{-- ─── Announcements ────────────────────────────────────────────────────────── --}}
@if($announcements->isNotEmpty())
<section style="padding:4rem 0;background:#fff">
    <div class="container" style="max-width:1200px;margin:0 auto;padding:0 1.5rem">
        <h2 style="font-size:1.5rem;font-weight:900;color:#0f172a;margin-bottom:1.5rem;text-align:center">
            Dernières Annonces de l'École
        </h2>
        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(280px,1fr));gap:1.25rem">
            @foreach($announcements->take(3) as $ann)
            <div style="border:1px solid #e2e8f0;border-radius:14px;padding:1.25rem;background:#fff">
                @if($ann->category)
                <span style="background:var(--primary-bg);color:var(--primary);padding:.2rem .6rem;border-radius:8px;font-size:.7rem;font-weight:700;display:inline-block;margin-bottom:.5rem">📌 {{ $ann->category }}</span>
                @endif
                <h3 style="font-size:.9rem;font-weight:800;color:#0f172a;margin-bottom:.4rem">{{ $ann->title }}</h3>
                <p style="font-size:.8rem;color:#64748b;margin-bottom:.75rem">{{ Str::limit($ann->content, 100) }}</p>
                <div style="font-size:.72rem;color:#94a3b8">📅 {{ $ann->published_at?->format('d/m/Y') }}</div>
            </div>
            @endforeach
        </div>
    </div>
</section>
@endif

{{-- ─── Testimonials ─────────────────────────────────────────────────────────── --}}
@if($testimonials->isNotEmpty())
<section style="padding:4rem 0;background:#f8fafc">
    <div class="container" style="max-width:1200px;margin:0 auto;padding:0 1.5rem">
        <h2 style="font-size:1.5rem;font-weight:900;text-align:center;color:#0f172a;margin-bottom:2rem">
            Ce qu'en disent nos utilisateurs
        </h2>
        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(280px,1fr));gap:1.25rem">
            @foreach($testimonials as $t)
            <div style="background:#fff;border:1px solid #e2e8f0;border-radius:14px;padding:1.5rem">
                <div style="font-size:1.25rem;color:#f59e0b;margin-bottom:.75rem">⭐⭐⭐⭐⭐</div>
                <p style="font-size:.85rem;color:#475569;line-height:1.7;margin-bottom:1rem;font-style:italic">
                    "{{ $t->content_fr ?? $t->content }}"
                </p>
                <div style="display:flex;align-items:center;gap:.75rem">
                    <div style="width:36px;height:36px;border-radius:50%;background:linear-gradient(135deg,var(--primary),var(--primary-dark));color:#fff;font-weight:700;font-size:.85rem;display:flex;align-items:center;justify-content:center;flex-shrink:0">
                        {{ strtoupper(substr($t->author_name, 0, 1)) }}
                    </div>
                    <div>
                        <div style="font-weight:700;font-size:.85rem;color:#0f172a">{{ $t->author_name }}</div>
                        <div style="font-size:.73rem;color:#94a3b8">{{ $t->author_role }}</div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>
@endif

{{-- ─── CTA ──────────────────────────────────────────────────────────────────── --}}
<section style="padding:4rem 0;background:linear-gradient(135deg,var(--primary),var(--primary-dark));color:#fff;text-align:center">
    <div class="container" style="max-width:600px;margin:0 auto;padding:0 1.5rem">
        <h2 style="font-size:1.75rem;font-weight:900;margin-bottom:.75rem">
            Prêt à rejoindre Millénaire Connect ?
        </h2>
        <p style="opacity:.88;margin-bottom:2rem">
            Connectez-vous avec vos identifiants fournis par l'établissement pour accéder à tous nos services.
        </p>
        <a href="{{ route('login') }}" style="background:#fff;color:var(--primary);padding:.9rem 2.5rem;border-radius:12px;font-weight:800;text-decoration:none;font-size:1rem;display:inline-block">
            🔐 Se connecter maintenant →
        </a>
    </div>
</section>

@endsection
