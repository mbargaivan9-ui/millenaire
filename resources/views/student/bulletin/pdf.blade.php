<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Bulletin - {{ $student->user->full_name ?? 'Student' }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: #333;
            line-height: 1.6;
        }
        .container {
            max-width: 100%;
            padding: 40px;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #007bff;
            padding-bottom: 20px;
        }
        .header h1 {
            color: #007bff;
            font-size: 28px;
            margin-bottom: 10px;
        }
        .header p {
            color: #666;
            font-size: 14px;
        }
        .student-info {
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
            padding: 15px;
            background-color: #f8f9fa;
            border-radius: 5px;
        }
        .student-info div {
            flex: 1;
        }
        .student-info strong {
            color: #333;
            display: block;
            margin-bottom: 5px;
        }
        .student-info span {
            color: #666;
            font-size: 14px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }
        th {
            background-color: #007bff;
            color: white;
            padding: 12px;
            text-align: left;
            font-size: 13px;
        }
        td {
            padding: 12px;
            border-bottom: 1px solid #ddd;
            font-size: 13px;
        }
        tr:nth-child(even) {
            background-color: #f8f9fa;
        }
        .grade-badge {
            display: inline-block;
            background-color: #007bff;
            color: white;
            padding: 5px 10px;
            border-radius: 3px;
            font-weight: bold;
        }
        .stats {
            display: flex;
            justify-content: space-around;
            margin-bottom: 30px;
        }
        .stat-box {
            text-align: center;
            padding: 15px;
            background-color: #f8f9fa;
            border-radius: 5px;
            flex: 1;
            margin: 0 10px;
        }
        .stat-box .label {
            color: #666;
            font-size: 12px;
            margin-bottom: 5px;
        }
        .stat-box .value {
            color: #007bff;
            font-size: 24px;
            font-weight: bold;
        }
        .comments {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 30px;
            border-left: 4px solid #007bff;
        }
        .comments h3 {
            color: #333;
            margin-bottom: 10px;
            font-size: 14px;
        }
        .comments p {
            color: #666;
            font-size: 13px;
            white-space: pre-wrap;
        }
        .footer {
            text-align: center;
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
            color: #999;
            font-size: 12px;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <h1>Bulletin Scolaire</h1>
            <p>Trimestre {{ $bulletin->term }} - Séquence {{ $bulletin->sequence }}</p>
        </div>

        <!-- Student Info -->
        <div class="student-info">
            <div>
                <strong>Étudiant:</strong>
                <span>{{ $student->user->full_name ?? 'N/A' }}</span>
            </div>
            <div>
                <strong>Classe:</strong>
                <span>{{ $bulletin->classe->name ?? 'N/A' }}</span>
            </div>
            <div>
                <strong>Année Académique:</strong>
                <span>{{ $bulletin->academic_year ?? 'N/A' }}</span>
            </div>
        </div>

        <!-- Marks Table -->
        @if($bulletin->marks && $bulletin->marks->count())
            <h3 style="margin-bottom: 15px; color: #333;">Résultats</h3>
            <table>
                <thead>
                    <tr>
                        <th>Matière</th>
                        <th style="text-align: center;">Note</th>
                        <th style="text-align: center;">Moyenne Classe</th>
                        <th style="text-align: center;">Position</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($bulletin->marks as $mark)
                        <tr>
                            <td>{{ $mark->subject->name ?? 'N/A' }}</td>
                            <td style="text-align: center;">
                                <span class="grade-badge">{{ number_format($mark->value ?? 0, 2) }}/20</span>
                            </td>
                            <td style="text-align: center;">{{ number_format($mark->class_average ?? 0, 2) }}/20</td>
                            <td style="text-align: center;">{{ $mark->rank ?? '-' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif

        <!-- Statistics -->
        <div class="stats">
            <div class="stat-box">
                <div class="label">Moyenne Générale</div>
                <div class="value">{{ number_format($bulletin->overall_average ?? 0, 2) }}/20</div>
            </div>
            <div class="stat-box">
                <div class="label">Moyenne Classe</div>
                <div class="value">{{ number_format($classMoyenne ?? 0, 2) }}/20</div>
            </div>
            <div class="stat-box">
                <div class="label">Étudiants en Classe</div>
                <div class="value">{{ $totalStudents ?? '-' }}</div>
            </div>
        </div>

        <!-- Comments -->
        @if($bulletin->comments)
            <div class="comments">
                <h3>Observations du Professeur Principal</h3>
                <p>{{ $bulletin->comments }}</p>
            </div>
        @endif

        <!-- Footer -->
        <div class="footer">
            <p>Document généré le {{ now()->format('d/m/Y à H:i') }}</p>
            <p>Millénaire Connect - Système de Gestion Scolaire</p>
        </div>
    </div>
</body>
</html>
