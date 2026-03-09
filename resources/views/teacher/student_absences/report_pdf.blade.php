<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Rapport d'Absences Élèves</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 11px; color: #333; }
        .header { text-align: center; border-bottom: 2px solid #333; padding-bottom: 10px; margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
        th, td { border: 1px solid #ccc; padding: 5px 8px; }
        th { background-color: #f0f0f0; font-weight: bold; }
        .footer { text-align: center; margin-top: 20px; font-size: 10px; color: #999; }
    </style>
</head>
<body>
    <div class="header">
        <h2>{{ config('app.school_name', 'Collège Millénaire Bilingue') }}</h2>
        <h3>RAPPORT D'ABSENCES ÉLÈVES</h3>
        <p>Classe : <strong>{{ $classe->name ?? '—' }}</strong> | Période : {{ $period ?? 'Année scolaire' }}</p>
    </div>
    <table>
        <thead>
            <tr><th>Élève</th><th>Date</th><th>Statut</th><th>Justifiée</th><th>Motif</th></tr>
        </thead>
        <tbody>
            @forelse($absences ?? [] as $absence)
            <tr>
                <td>{{ $absence->student->user->name ?? '—' }}</td>
                <td>{{ $absence->date }}</td>
                <td>{{ $absence->status }}</td>
                <td>{{ $absence->justified ? 'Oui' : 'Non' }}</td>
                <td>{{ $absence->reason ?? '—' }}</td>
            </tr>
            @empty
            <tr><td colspan="5" style="text-align:center">Aucune absence enregistrée.</td></tr>
            @endforelse
        </tbody>
    </table>
    <div class="footer">
        <p>Rapport généré le {{ now()->format('d/m/Y à H:i') }} — Millénaire Connect</p>
    </div>
</body>
</html>
