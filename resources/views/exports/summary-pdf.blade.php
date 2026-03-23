<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Résumé de Classe</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: #333;
            line-height: 1.5;
        }

        .page {
            width: 210mm;
            height: 297mm;
            padding: 15mm;
            background: white;
            page-break-after: always;
        }

        .header {
            text-align: center;
            border-bottom: 3px solid #1e40af;
            padding-bottom: 10mm;
            margin-bottom: 15mm;
        }

        .title {
            font-size: 20pt;
            font-weight: bold;
            color: #1e40af;
            margin-bottom: 5mm;
        }

        .subtitle {
            font-size: 12pt;
            color: #666;
        }

        .class-info {
            background: #f3f4f6;
            padding: 8mm;
            margin: 10mm 0;
            border-left: 4px solid #1e40af;
        }

        .class-info-row {
            display: flex;
            justify-content: space-between;
            margin: 2mm 0;
        }

        .class-info-label {
            font-weight: bold;
        }

        /* Statistics Grid */
        .statistics {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 5mm;
            margin: 15mm 0;
        }

        .stat-card {
            background: #f0f9ff;
            border: 2px solid #bfdbfe;
            border-radius: 3mm;
            padding: 6mm;
        }

        .stat-card h3 {
            color: #0369a1;
            font-size: 10pt;
            margin-bottom: 3mm;
            border-bottom: 1px solid #bfdbfe;
            padding-bottom: 2mm;
        }

        .stat-item {
            display: flex;
            justify-content: space-between;
            margin: 2mm 0;
            font-size: 9pt;
        }

        .stat-value {
            font-weight: bold;
            color: #1e40af;
        }

        /* Rankings Table */
        .rankings-section {
            margin: 15mm 0;
        }

        .section-title {
            font-size: 12pt;
            font-weight: bold;
            color: #1e40af;
            margin: 10mm 0 5mm 0;
            padding-bottom: 2mm;
            border-bottom: 2px solid #e5e7eb;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 9pt;
            margin-bottom: 10mm;
        }

        thead {
            background: #e0e7ff;
            color: #1e40af;
            font-weight: bold;
        }

        th, td {
            padding: 2.5mm;
            border: 1px solid #d1d5db;
            text-align: left;
        }

        th {
            text-align: center;
        }

        td:nth-child(2), td:nth-child(3) {
            text-align: center;
        }

        tbody tr:nth-child(even) {
            background: #f9fafb;
        }

        .rank {
            font-weight: bold;
            color: #1e40af;
        }

        /* Subject Statistics */
        .subject-stats {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 5mm;
            margin: 15mm 0;
        }

        .subject-card {
            background: #fef3c7;
            border: 1px solid #fcd34d;
            border-radius: 3mm;
            padding: 5mm;
            font-size: 8pt;
        }

        .subject-name {
            font-weight: bold;
            color: #92400e;
            margin-bottom: 2mm;
        }

        .subject-stat {
            display: flex;
            justify-content: space-between;
            margin: 1mm 0;
        }

        /* Footer */
        .footer {
            position: absolute;
            bottom: 10mm;
            left: 0;
            right: 0;
            text-align: center;
            font-size: 8pt;
            color: #999;
            border-top: 1px solid #e5e7eb;
            padding-top: 5mm;
        }

        @media print {
            body {
                margin: 0;
                padding: 0;
            }

            .page {
                page-break-after: always;
            }
        }
    </style>
</head>
<body>
    <div class="page">
        <!-- Header -->
        <div class="header">
            <div class="title">RÉSUMÉ DE CLASSE</div>
            <div class="subtitle">{{ $template->classroom->school->name ?? 'Établissement' }}</div>
        </div>

        <!-- Class Info -->
        <div class="class-info">
            <div class="class-info-row">
                <span class="class-info-label">Classe:</span>
                <span>{{ $template->classroom->name }}</span>
            </div>
            <div class="class-info-row">
                <span class="class-info-label">Nombre d'élèves:</span>
                <span>{{ $stats['total'] }}</span>
            </div>
            <div class="class-info-row">
                <span class="class-info-label">Année scolaire:</span>
                <span>2025/2026</span>
            </div>
            <div class="class-info-row">
                <span class="class-info-label">Date du rapport:</span>
                <span>{{ now()->format('d/m/Y') }}</span>
            </div>
        </div>

        <!-- Overall Statistics -->
        <div class="statistics">
            <div class="stat-card">
                <h3>📊 Statistiques Globales</h3>
                <div class="stat-item">
                    <span>Moyenne de classe:</span>
                    <span class="stat-value">{{ number_format($stats['average'], 2) }}/20</span>
                </div>
                <div class="stat-item">
                    <span>Meilleure note:</span>
                    <span class="stat-value">{{ number_format($stats['max'], 2) }}/20</span>
                </div>
                <div class="stat-item">
                    <span>Note la plus basse:</span>
                    <span class="stat-value">{{ number_format($stats['min'], 2) }}/20</span>
                </div>
                <div class="stat-item">
                    <span>Médiane:</span>
                    <span class="stat-value">{{ number_format($stats['median'], 2) }}/20</span>
                </div>
            </div>

            <div class="stat-card">
                <h3>✓ Distribution des Résultats</h3>
                @php
                $excellent = $bulletins->where('general_average', '>=', 16)->count();
                $good = $bulletins->where('general_average', '>=', 12)->where('general_average', '<', 16)->count();
                $average = $bulletins->where('general_average', '>=', 10)->where('general_average', '<', 12)->count();
                $pass = $bulletins->where('general_average', '>=', 0)->where('general_average', '<', 10)->count();
                @endphp
                <div class="stat-item">
                    <span>Très Bien (≥16):</span>
                    <span class="stat-value">{{ $excellent }}</span>
                </div>
                <div class="stat-item">
                    <span>Bien (12-15):</span>
                    <span class="stat-value">{{ $good }}</span>
                </div>
                <div class="stat-item">
                    <span>Assez Bien (10-11):</span>
                    <span class="stat-value">{{ $average }}</span>
                </div>
                <div class="stat-item">
                    <span>Passable (<10):</span>
                    <span class="stat-value">{{ $pass }}</span>
                </div>
            </div>
        </div>

        <!-- Top 10 Rankings -->
        <div class="rankings-section">
            <div class="section-title">🏆 Top 10 Meilleurs Élèves</div>
            <table>
                <thead>
                    <tr>
                        <th>Rang</th>
                        <th>Élève</th>
                        <th>Moyenne</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($bulletins->sortByDesc('general_average')->take(10) as $idx => $b)
                    <tr>
                        <td class="rank">#{{ $idx + 1 }}</td>
                        <td>{{ $b->student->name }}</td>
                        <td>{{ number_format($b->general_average, 2) }}/20</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Subject Analysis -->
        <div class="section-title">📚 Analyse par Matière</div>
        <div class="subject-stats">
            @php
            $subjectStats = collect();
            foreach($bulletins as $bulletin) {
                foreach($bulletin->grades as $grade) {
                    if(!isset($subjectStats[$grade->subject->id])) {
                        $subjectStats[$grade->subject->id] = [
                            'name' => $grade->subject->name,
                            'grades' => collect(),
                        ];
                    }
                    $subjectStats[$grade->subject->id]['grades']->push($grade->average ?? 0);
                }
            }
            @endphp

            @foreach($subjectStats as $subject)
            <div class="subject-card">
                <div class="subject-name">{{ $subject['name'] }}</div>
                <div class="subject-stat">
                    <span>Moyenne:</span>
                    <span>{{ number_format($subject['grades']->avg(), 2) }}/20</span>
                </div>
                <div class="subject-stat">
                    <span>Max:</span>
                    <span>{{ number_format($subject['grades']->max(), 2) }}</span>
                </div>
                <div class="subject-stat">
                    <span>Min:</span>
                    <span>{{ number_format($subject['grades']->min(), 2) }}</span>
                </div>
            </div>
            @endforeach
        </div>

        <!-- Footer -->
        <div class="footer">
            Rapport généré automatiquement le {{ now()->format('d/m/Y à H:i') }}
        </div>
    </div>
</body>
</html>
