<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Bulletin — {{ $reportCard->student->user->last_name }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Arial', sans-serif;
            font-size: 12px;
            color: #333;
        }
        
        .container {
            max-width: 850px;
            margin: 20px auto;
            padding: 30px;
            background: white;
            border: 1px solid #ddd;
        }
        
        .header {
            text-align: center;
            border-bottom: 2px solid #1e3a5f;
            margin-bottom: 30px;
            padding-bottom: 20px;
        }
        
        .school-name {
            font-size: 18px;
            font-weight: bold;
            color: #1e3a5f;
            margin-bottom: 5px;
        }
        
        .school-subtitle {
            font-size: 11px;
            color: #666;
            margin-bottom: 10px;
        }
        
        .document-title {
            font-size: 16px;
            font-weight: bold;
            color: #2563eb;
            margin-top: 10px;
        }
        
        .student-info {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
            padding: 15px;
            background: #f5f7fa;
            border-radius: 4px;
        }
        
        .student-info-item {
            flex: 1;
        }
        
        .info-label {
            font-weight: bold;
            color: #1e3a5f;
            font-size: 11px;
            text-transform: uppercase;
        }
        
        .info-value {
            font-size: 12px;
            color: #333;
            margin-top: 3px;
        }
        
        .section {
            margin-bottom: 25px;
        }
        
        .section-title {
            font-size: 13px;
            font-weight: bold;
            background: #2563eb;
            color: white;
            padding: 10px 15px;
            border-radius: 4px;
            margin-bottom: 15px;
        }
        
        .info-box {
            padding: 15px;
            border: 1px solid #ddd;
            border-radius: 4px;
            background: white;
            margin-bottom: 10px;
        }
        
        .info-box-label {
            font-weight: bold;
            color: #1e3a5f;
            font-size: 11px;
            text-transform: uppercase;
            margin-bottom: 5px;
        }
        
        .info-box-content {
            font-size: 12px;
            line-height: 1.6;
            color: #333;
            min-height: 30px;
        }
        
        .stats {
            display: flex;
            gap: 15px;
            margin-bottom: 10px;
        }
        
        .stat-box {
            flex: 1;
            padding: 15px;
            border: 1px solid #ddd;
            border-radius: 4px;
            text-align: center;
            background: #f9f9f9;
        }
        
        .stat-label {
            font-size: 11px;
            color: #666;
            font-weight: bold;
            text-transform: uppercase;
            margin-bottom: 5px;
        }
        
        .stat-value {
            font-size: 16px;
            font-weight: bold;
            color: #2563eb;
        }
        
        .seal {
            text-align: right;
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
        }
        
        .seal-text {
            font-size: 10px;
            color: #666;
            margin-bottom: 5px;
        }
        
        .signature-line {
            width: 150px;
            margin-left: auto;
            border-top: 1px solid #333;
            text-align: center;
            padding-top: 5px;
            font-size: 11px;
        }
        
        .footer {
            text-align: center;
            font-size: 10px;
            color: #999;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
        }
        
        @media print {
            body {
                margin: 0;
                padding: 0;
            }
            .container {
                max-width: 100%;
                margin: 0;
                padding: 20px;
                border: none;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <div class="school-name">Collège Millénaire Bilingue</div>
            <div class="school-subtitle">Douala, Cameroun — Année Scolaire 2025/2026</div>
            <div class="document-title">BULLETIN DE CLASSE</div>
        </div>

        <!-- Student Info -->
        <div class="student-info">
            <div class="student-info-item">
                <div class="info-label">Étudiant</div>
                <div class="info-value">
                    {{ $reportCard->student->user->last_name }} {{ $reportCard->student->user->first_name }}
                </div>
            </div>
            <div class="student-info-item">
                <div class="info-label">Classe</div>
                <div class="info-value">{{ $reportCard->student->classe->name }}</div>
            </div>
            <div class="student-info-item">
                <div class="info-label">Période</div>
                <div class="info-value">
                    Trimestre {{ $reportCard->term }} - Séquence {{ $reportCard->sequence }}
                </div>
            </div>
            <div class="student-info-item">
                <div class="info-label">Rang</div>
                <div class="info-value">
                    @if($reportCard->rank)
                        {{ $reportCard->rank . ($reportCard->rank === 1 ? 'er' : 'e') }}
                    @else
                        N/A
                    @endif
                </div>
            </div>
        </div>

        <!-- Statistics -->
        @if($reportCard->term_average !== null)
            <div class="section">
                <div class="section-title">Résultats Académiques</div>
                <div class="stats">
                    <div class="stat-box">
                        <div class="stat-label">Moyenne Générale</div>
                        <div class="stat-value">{{ number_format($reportCard->term_average, 2) }}/20</div>
                    </div>
                </div>
            </div>
        @endif

        <!-- Appreciation -->
        @if($reportCard->appreciation)
            <div class="section">
                <div class="section-title">Appréciation Générale</div>
                <div class="info-box">
                    <div class="info-box-content">{{ $reportCard->appreciation }}</div>
                </div>
            </div>
        @endif

        <!-- Behavior Comment -->
        @if($reportCard->behavior_comment)
            <div class="section">
                <div class="section-title">Comportement</div>
                <div class="info-box">
                    <div class="info-box-content">{{ $reportCard->behavior_comment }}</div>
                </div>
            </div>
        @endif

        <!-- Seal & Signature -->
        <div class="seal">
            <div class="seal-text">Sceau de l'établissement</div>
            <div class="signature-line">Professeur Principal</div>
        </div>

        <!-- Footer -->
        <div class="footer">
            <p>
                Bulletin émis le {{ now()->format('d/m/Y à H:i') }}
                | Millénaire Connect — Plateforme de Gestion Scolaire
            </p>
        </div>
    </div>
</body>
</html>
