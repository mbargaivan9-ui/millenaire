<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Bulletin de Notes — Impression</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; }
        .header { text-align: center; border-bottom: 2px solid #333; padding-bottom: 10px; margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th, td { border: 1px solid #ccc; padding: 6px 8px; }
        th { background: #f5f5f5; }
        .footer { text-align: center; margin-top: 30px; font-size: 10px; color: #666; }
        @media print { .no-print { display: none; } }
    </style>
</head>
<body>
    <div class="no-print" style="margin:20px;text-align:center">
        <button onclick="window.print()" style="padding:8px 20px;font-size:14px">🖨️ Imprimer</button>
        <button onclick="history.back()" style="padding:8px 20px;font-size:14px;margin-left:10px">← Retour</button>
    </div>
    <div class="header">
        <h2>{{ config('app.school_name', 'Collège Millénaire Bilingue') }}</h2>
        <h3>BULLETIN DE NOTES — {{ $bulletin->term_label ?? 'Trimestre' }}</h3>
        <p>Élève : <strong>{{ $bulletin->student->user->name ?? '—' }}</strong> | Classe : <strong>{{ $bulletin->student->classe->name ?? '—' }}</strong></p>
    </div>
    <table>
        <thead>
            <tr><th>Matière</th><th>Note /20</th><th>Moyenne Classe</th><th>Rang</th><th>Appréciation</th></tr>
        </thead>
        <tbody>
            @forelse($bulletin->entries ?? [] as $entry)
            <tr>
                <td>{{ $entry->subject->name ?? '—' }}</td>
                <td style="text-align:center;font-weight:bold;color:{{ $entry->score < 10 ? '#c00' : ($entry->score >= 14 ? '#060' : '#333') }}">{{ number_format($entry->score ?? 0, 2) }}</td>
                <td style="text-align:center">{{ number_format($entry->class_average ?? 0, 2) }}</td>
                <td style="text-align:center">{{ $entry->rank ?? '—' }}/{{ $entry->total_students ?? '—' }}</td>
                <td>{{ $entry->appreciation ?? '—' }}</td>
            </tr>
            @empty
            <tr><td colspan="5" style="text-align:center">Aucune note disponible</td></tr>
            @endforelse
        </tbody>
    </table>
    <div style="display:flex;justify-content:space-between">
        <div><strong>Moyenne Générale :</strong> {{ number_format($bulletin->average ?? 0, 2) }}/20</div>
        <div><strong>Rang :</strong> {{ $bulletin->rank ?? '—' }}/{{ $bulletin->class_size ?? '—' }}</div>
        <div><strong>Appréciation :</strong> {{ $bulletin->appreciation ?? '—' }}</div>
    </div>
    <div class="footer">
        <p>Document généré le {{ now()->format('d/m/Y') }} — Millénaire Connect</p>
    </div>
</body>
</html>
