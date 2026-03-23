<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bulletin de Notes - {{ $student->name }}</title>
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
            background: white;
        }

        .bulletin {
            width: 210mm;
            height: 297mm;
            padding: 15mm;
            background: white;
            position: relative;
            page-break-after: always;
        }

        /* Header */
        .header {
            text-align: center;
            border-bottom: 3px solid #1e40af;
            padding-bottom: 10mm;
            margin-bottom: 10mm;
        }

        .school-name {
            font-size: 20pt;
            font-weight: bold;
            color: #1e40af;
            margin-bottom: 3mm;
        }

        .academic-info {
            font-size: 10pt;
            color: #666;
            display: flex;
            justify-content: space-around;
            margin-top: 5mm;
        }

        /* Student Info */
        .student-info {
            background: #f3f4f6;
            padding: 8mm;
            margin: 10mm 0;
            border-left: 4px solid #1e40af;
            font-size: 10pt;
        }

        .student-info-row {
            display: flex;
            justify-content: space-between;
            margin: 2mm 0;
        }

        .student-info-label {
            font-weight: bold;
            width: 40mm;
        }

        /* Grades Table */
        .grades-section {
            margin: 15mm 0;
        }

        .grades-title {
            font-size: 12pt;
            font-weight: bold;
            color: #1e40af;
            margin-bottom: 5mm;
            padding-bottom: 2mm;
            border-bottom: 2px solid #e5e7eb;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10mm;
            font-size: 9pt;
        }

        thead {
            background: #e0e7ff;
            color: #1e40af;
            font-weight: bold;
        }

        th {
            padding: 3mm;
            text-align: left;
            border: 1px solid #d1d5db;
        }

        td {
            padding: 3mm;
            border: 1px solid #e5e7eb;
            text-align: center;
        }

        td:first-child {
            text-align: left;
        }

        tbody tr:nth-child(even) {
            background: #f9fafb;
        }

        tbody tr:hover {
            background: #f3f4f6;
        }

        /* Statistics */
        .statistics {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr 1fr;
            gap: 3mm;
            margin: 10mm 0;
            font-size: 9pt;
        }

        .stat-box {
            background: #f0f9ff;
            border: 1px solid #bfdbfe;
            border-radius: 2mm;
            padding: 4mm;
            text-align: center;
        }

        .stat-label {
            color: #0369a1;
            font-weight: bold;
            font-size: 8pt;
        }

        .stat-value {
            font-size: 14pt;
            font-weight: bold;
            color: #1e40af;
            margin-top: 1mm;
        }

        /* Appreciation */
        .appreciation {
            background: #f0fdf4;
            border: 1px solid #bbf7d0;
            border-radius: 3mm;
            padding: 5mm;
            text-align: center;
            margin: 5mm 0;
            font-weight: bold;
            color: #16a34a;
        }

        /* Signatures */
        .signatures {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 5mm;
            margin-top: 20mm;
            text-align: center;
            font-size: 9pt;
        }

        .signature-section {
            border-top: 1px solid #d1d5db;
            padding-top: 8mm;
        }

        .signature-label {
            font-weight: bold;
            margin-top: 8mm;
            font-size: 8pt;
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
            padding: 0 15mm;
        }

        .page-number {
            text-align: right;
            font-size: 8pt;
            color: #999;
            margin-top: 5mm;
        }

        /* Print styles */
        @media print {
            body {
                margin: 0;
                padding: 0;
            }

            .bulletin {
                page-break-after: always;
            }
        }
    </style>
</head>
<body>
    <div class="bulletin">
        <!-- Header -->
        <div class="header">
            <div class="school-name">{{ $template->classroom->school->name ?? 'Établissement Scolaire' }}</div>
            <div class="academic-info">
                <span>Année: 2025/2026</span>
                <span>Classe: {{ $template->classroom->name }}</span>
                <span>Période: {{ $structure['header']['period'] ?? 'Premier trimestre' }}</span>
            </div>
        </div>

        <!-- Student Info -->
        <div class="student-info">
            <div class="student-info-row">
                <span class="student-info-label">Nom & Prénom:</span>
                <span>{{ $student->name }}</span>
            </div>
            <div class="student-info-row">
                <span class="student-info-label">Matricule:</span>
                <span>{{ $student->matricule ?? '-' }}</span>
            </div>
            <div class="student-info-row">
                <span class="student-info-label">Classe:</span>
                <span>{{ $template->classroom->name }}</span>
            </div>
        </div>

        <!-- Grades -->
        <div class="grades-section">
            <div class="grades-title">RELEVÉ DE NOTES</div>
            <table>
                <thead>
                    <tr>
                        <th>Matière</th>
                        <th>Note Classe</th>
                        <th>Note Composition</th>
                        <th>Moyenne</th>
                        <th>Coefficient</th>
                        <th>Points</th>
                    </tr>
                </thead>
                <tbody>
                    @php $totalPoints = 0; $totalCoeff = 0; @endphp
                    @foreach($grades as $grade)
                    <tr>
                        <td>{{ $grade->subject->name }}</td>
                        <td>{{ $grade->note_classe ?? '-' }}</td>
                        <td>{{ $grade->note_composition ?? '-' }}</td>
                        <td><strong>{{ $grade->average ? number_format($grade->average, 2) : '-' }}</strong></td>
                        <td>{{ $grade->subject->coefficient ?? 1 }}</td>
                        <td>
                            @php
                            $points = ($grade->average ?? 0) * ($grade->subject->coefficient ?? 1);
                            $totalPoints += $points;
                            $totalCoeff += ($grade->subject->coefficient ?? 1);
                            @endphp
                            {{ number_format($points, 2) }}
                        </td>
                    </tr>
                    @endforeach
                    <tr style="background: #dbeafe; font-weight: bold;">
                        <td colspan="2">TOTAUX</td>
                        <td></td>
                        <td></td>
                        <td>{{ $totalCoeff }}</td>
                        <td>{{ number_format($totalPoints, 2) }}</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Statistics -->
        <div class="statistics">
            <div class="stat-box">
                <div class="stat-label">Moyenne Générale</div>
                <div class="stat-value">
                    {{ $bulletin->general_average ? number_format($bulletin->general_average, 2) : '-' }}
                </div>
            </div>
            <div class="stat-box">
                <div class="stat-label">Rang de Classe</div>
                <div class="stat-value">{{ $bulletin->class_rank ?? '-' }}</div>
            </div>
            <div class="stat-box">
                <div class="stat-label">Total Élèves</div>
                <div class="stat-value">{{ $template->studentBulletins->count() }}</div>
            </div>
            <div class="stat-box">
                <div class="stat-label">Moyenne Classe</div>
                <div class="stat-value">
                    @php
                    $classAvg = $template->studentBulletins()
                        ->whereNotNull('general_average')
                        ->avg('general_average');
                    @endphp
                    {{ $classAvg ? number_format($classAvg, 2) : '-' }}
                </div>
            </div>
        </div>

        <!-- Appreciation -->
        @if($bulletin->appreciation)
        <div class="appreciation">
            Appréciation: {{ $bulletin->appreciation }}
        </div>
        @endif

        <!-- Signatures -->
        <div class="signatures">
            <div class="signature-section">
                <div class="signature-label">Parent/Tuteur</div>
            </div>
            <div class="signature-section">
                <div class="signature-label">Professeur Principal</div>
            </div>
            <div class="signature-section">
                <div class="signature-label">Directeur/Directrice</div>
            </div>
        </div>

        <!-- Footer -->
        <div class="footer">
            Bulletin généré le {{ now()->format('d/m/Y à H:i') }}
        </div>

        <div class="page-number">
            Page 1
        </div>
    </div>
</body>
</html>
