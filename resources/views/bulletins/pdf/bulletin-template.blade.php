<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bulletin - {{ $student->first_name }} {{ $student->last_name }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Arial', sans-serif;
            font-size: 10pt;
            line-height: 1.4;
            color: #333;
            background-color: white;
        }

        .page {
            margin: 0;
            padding: 20px;
            min-height: 297mm;
        }

        /* Header */
        .header {
            border-bottom: 3px solid #003366;
            margin-bottom: 20px;
            padding-bottom: 15px;
        }

        .school-info {
            text-align: center;
            margin-bottom: 10px;
        }

        .school-name {
            font-size: 16pt;
            font-weight: bold;
            color: #003366;
        }

        .school-motto {
            font-size: 8pt;
            color: #666;
            font-style: italic;
        }

        .title {
            text-align: center;
            font-size: 14pt;
            font-weight: bold;
            color: #003366;
            margin: 15px 0;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        /* Student Info */
        .student-info {
            background-color: #f5f5f5;
            border: 1px solid #ddd;
            padding: 10px;
            margin-bottom: 15px;
            display: grid;
            grid-template-columns: 1fr 1fr 1fr 1fr;
            gap: 10px;
            font-size: 9pt;
        }

        .info-field {
            display: flex;
            flex-direction: column;
        }

        .info-label {
            font-weight: bold;
            color: #003366;
            font-size: 8pt;
        }

        .info-value {
            color: #333;
            padding-top: 2px;
        }

        /* Grades Table */
        .grades-table {
            width: 100%;
            border-collapse: collapse;
            margin: 15px 0;
            font-size: 9pt;
        }

        .grades-table thead th {
            background-color: #003366;
            color: white;
            padding: 8px 5px;
            text-align: left;
            font-weight: bold;
            border: 1px solid #333;
        }

        .grades-table tbody td {
            padding: 8px 5px;
            border: 1px solid #ddd;
        }

        .grades-table tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        .grades-table tr:hover {
            background-color: #f0f0f0;
        }

        .subject-cell {
            font-weight: 500;
            color: #003366;
        }

        .grade-cell {
            text-align: center;
            font-weight: bold;
            color: #006600;
        }

        .coef-cell {
            text-align: center;
            color: #666;
        }

        /* Summary Section */
        .summary {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 10px;
            margin: 20px 0;
        }

        .summary-card {
            background-color: #f0f0f0;
            border: 2px solid #003366;
            padding: 12px;
            text-align: center;
            border-radius: 5px;
        }

        .summary-label {
            font-size: 8pt;
            color: #666;
            margin-bottom: 5px;
            text-transform: uppercase;
        }

        .summary-value {
            font-size: 16pt;
            font-weight: bold;
            color: #003366;
        }

        /* Appreciation */
        .appreciation {
            background-color: #fff9e6;
            border: 2px solid #ffb800;
            padding: 12px;
            margin: 15px 0;
            border-radius: 5px;
            font-style: italic;
            text-align: center;
            color: #333;
        }

        .appreciation-label {
            font-weight: bold;
            color: #333;
            display: block;
            margin-bottom: 5px;
            font-style: normal;
        }

        /* Footer */
        .footer {
            margin-top: 30px;
            padding-top: 15px;
            border-top: 1px solid #ddd;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            font-size: 8pt;
            color: #666;
        }

        .signature-block {
            text-align: center;
        }

        .signature-line {
            border-top: 1px solid #333;
            margin-top: 20px;
            padding-top: 5px;
        }

        .generated-info {
            text-align: right;
            font-size: 8pt;
            color: #999;
        }

        /* Remarks */
        .remarks-section {
            margin-top: 20px;
            padding: 10px;
            background-color: #f5f5f5;
            border: 1px solid #ddd;
            min-height: 60px;
        }

        .remarks-label {
            font-weight: bold;
            color: #003366;
            margin-bottom: 5px;
        }

        /* Status Badge */
        .status-badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 3px;
            font-weight: bold;
            font-size: 8pt;
        }

        .status-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .status-warning {
            background-color: #fff3cd;
            color: #856404;
            border: 1px solid #ffeaa7;
        }

        .status-danger {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        @media print {
            body {
                margin: 0;
                padding: 0;
            }
            .page {
                margin: 0;
                padding: 20px;
                page-break-after: always;
            }
        }

        page {
            margin: 0;
            padding: 0;
        }
    </style>
</head>
<body>
    <div class="page">
        {{-- Header --}}
        <div class="header">
            <div class="school-info">
                <div class="school-name">ÉCOLE SECONDAIRE MILLENAIRE</div>
                <div class="school-motto">Excellence Académique - Valeurs Humaines</div>
            </div>
        </div>

        {{-- Title --}}
        <div class="title">📋 BULLETIN SCOLAIRE</div>

        {{-- Student Information --}}
        <div class="student-info">
            <div class="info-field">
                <span class="info-label">Nom de l'étudiant</span>
                <span class="info-value">{{ ucfirst($student->last_name) }}</span>
            </div>
            <div class="info-field">
                <span class="info-label">Prénom</span>
                <span class="info-value">{{ ucfirst($student->first_name) }}</span>
            </div>
            <div class="info-field">
                <span class="info-label">Classe</span>
                <span class="info-value">{{ $structure->classe->name }}</span>
            </div>
            <div class="info-field">
                <span class="info-label">Période</span>
                <span class="info-value">{{ $structure->metadata['term'] ?? 'S1' }} - {{ $structure->metadata['academic_year'] ?? date('Y') }}</span>
            </div>
        </div>

        {{-- Grades Table --}}
        <table class="grades-table">
            <thead>
                <tr>
                    <th style="width: 45%;">Matière</th>
                    <th style="width: 15%;">Note</th>
                    <th style="width: 15%;">Coef.</th>
                    <th style="width: 25%;">Observation</th>
                </tr>
            </thead>
            <tbody>
                @foreach($subjectFields as $field)
                    @php
                        $grade = $grades[$field->field_name] ?? 0;
                        $status = '';
                        if ($grade >= 16) {
                            $status = 'Excellent';
                        } elseif ($grade >= 14) {
                            $status = 'Très bien';
                        } elseif ($grade >= 12) {
                            $status = 'Bien';
                        } elseif ($grade >= 10) {
                            $status = 'Satisfaisant';
                        } else {
                            $status = 'À améliorer';
                        }
                    @endphp
                    <tr>
                        <td class="subject-cell">{{ $field->field_label }}</td>
                        <td class="grade-cell">
                            {{ number_format($grade, 2) }}/{{ $field->max_value ?? 20 }}
                        </td>
                        <td class="coef-cell">{{ $field->coefficient }}</td>
                        <td>{{ $status }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        {{-- Summary Cards --}}
        <div class="summary">
            <div class="summary-card">
                <div class="summary-label">Moyenne Générale</div>
                <div class="summary-value">{{ number_format($calculations['average'] ?? 0, 2) }}/20</div>
            </div>

            @php
                $avg = $calculations['average'] ?? 0;
                $statusClass = 'status-success';
                if ($avg < 10) {
                    $statusClass = 'status-danger';
                } elseif ($avg < 12) {
                    $statusClass = 'status-warning';
                }
            @endphp

            <div class="summary-card">
                <div class="summary-label">Appréciation</div>
                <div class="summary-value">
                    <span class="status-badge {{ $statusClass }}">
                        {{ $calculations['appreciation'] ?? 'Voir notes' }}
                    </span>
                </div>
            </div>

            <div class="summary-card">
                <div class="summary-label">Matières</div>
                <div class="summary-value">{{ count($subjectFields) }}</div>
            </div>
        </div>

        {{-- Appreciation Section --}}
        @if(isset($calculations['appreciation']))
            <div class="appreciation">
                <span class="appreciation-label">Appréciation des enseignants:</span>
                {{ $calculations['appreciation'] }}
            </div>
        @endif

        {{-- Remarks Section --}}
        <div class="remarks-section">
            <div class="remarks-label">Remarques et Observations:</div>
            <div style="margin-top: 10px;">
                L'étudiant(e) montre des progrès réguliers. Continuer les efforts pour maintenir ce niveau d'excellence.
            </div>
        </div>

        {{-- Footer --}}
        <div class="footer">
            <div class="signature-block">
                <div style="font-weight: bold;">Enseignant(e)</div>
                <div class="signature-line"></div>
            </div>
            <div class="signature-block">
                <div style="font-weight: bold;">Chef d'établissement</div>
                <div class="signature-line"></div>
            </div>
        </div>

        <div class="generated-info">
            Généré le {{ $generatedAt ?? now()->format('d/m/Y H:i') }}
        </div>
    </div>
</body>
</html>
