@extends('layouts.app')

@section('title', 'Tokens d\'Accès Actifs — ' . ($class->name ?? ''))

@section('content')

<div class="page-header mb-4">
    <div class="d-flex align-items-center gap-2 mb-3">
        <a href="{{ route('teacher.parent-management.index', $class->id) }}" class="btn btn-light btn-sm">
            <i data-lucide="arrow-left" style="width:14px" class="me-1"></i>
            Retour
        </a>
    </div>
    <div class="d-flex align-items-center gap-3">
        <div class="page-icon" style="background:linear-gradient(135deg,#3b82f6,#60a5fa)">
            <i data-lucide="lock"></i>
        </div>
        <div>
            <h1 class="page-title">Tokens d'Accès Actifs</h1>
            <p class="text-muted mb-0">Classe: {{ $class->name }}</p>
        </div>
        <div class="ms-auto">
            <a href="{{ route('teacher.parent-management.export-tokens', $class->id) }}" class="btn btn-info btn-sm">
                <i data-lucide="download" style="width:14px" class="me-1"></i>
                Exporter CSV
            </a>
        </div>
    </div>
</div>

@if(session('success'))
<div class="alert alert-success alert-dismissible fade show" role="alert">
    <i data-lucide="check-circle" style="width:18px" class="me-2"></i>
    {{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

<div class="card">
    <div class="card-header">
        <h6 class="card-title mb-0">{{ $tokens->count() }} token(s) actif(s)</h6>
    </div>
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th>Parent</th>
                    <th>Email</th>
                    <th>Token</th>
                    <th>Utilisations</th>
                    <th>Expire le</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($tokens as $token)
                <tr>
                    <td>
                        <strong>{{ $token->parent->name }}</strong>
                    </td>
                    <td>
                        <small>{{ $token->parent->email }}</small>
                    </td>
                    <td>
                        <code class="text-break" style="font-size:0.75rem">{{ substr($token->token, 0, 20) }}...</code>
                        <button type="button" class="btn btn-sm btn-link p-0 ms-2" 
                                onclick="copyToClipboard('{{ $token->token }}', this)">
                            <i data-lucide="copy" style="width:12px"></i>
                        </button>
                    </td>
                    <td>
                        @if($token->max_uses)
                        <span class="badge bg-info">{{ $token->uses_count ?? 0 }}/{{ $token->max_uses }}</span>
                        @else
                        <span class="badge bg-secondary">Illimité</span>
                        @endif
                    </td>
                    <td>
                        <small class="text-muted">
                            {{ $token->expires_at->format('d/m/Y') }}
                            @if($token->expires_at->diffInDays(now()) <= 7)
                            <span class="badge bg-warning ms-2">Expire bientôt</span>
                            @endif
                        </small>
                    </td>
                    <td>
                        <form action="{{ route('teacher.parent-management.revoke-token', $token->id) }}" 
                              method="POST" style="display:inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-outline-danger" 
                                    onclick="return confirm('Révoquer ce token?')">
                                <i data-lucide="x" style="width:14px"></i>
                            </button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="text-center text-muted py-4">
                        <i data-lucide="inbox" style="width:24px" class="d-block mb-2"></i>
                        Aucun token actif
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<script>
function copyToClipboard(text, btn) {
    navigator.clipboard.writeText(text).then(() => {
        const originalText = btn.innerHTML;
        btn.innerHTML = '<i data-lucide="check" style="width:12px"></i>';
        setTimeout(() => { btn.innerHTML = originalText; }, 2000);
    });
}
</script>

@endsection
