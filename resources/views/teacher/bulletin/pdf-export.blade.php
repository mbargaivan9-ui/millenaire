<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Bulletins — {{ $classe->name }} — Trimestre {{ $term }}</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'DejaVu Sans', Arial, sans-serif; font-size: 10px; color: #222; }

        /* Page par bulletin */
        .bulletin-page { page-break-after: always; padding: 12mm 10mm; min-height: 270mm; }
        .bulletin-page:last-child { page-break-after: auto; }

        /* En-tête établissement */
        .header { border-bottom: 2px solid #1a4480; padding-bottom: 6px; margin-bottom: 8px; display: flex; justify-content: space-between; align-items: center; }
        .school-name { font-size: 14px; font-weight: 700; color: #1a4480; }
        .school-sub { font-size: 9px; color: #666; }
        .bulletin-title { text-align: center; font-size: 13px; font-weight: 700; color: #1a4480; border: 2px solid #1a4480; padding: 4px 16px; border-radius: 4px; }

        /* Info élève */
        .student-info { display: flex; gap: 8px; background: #f0f4f8; border-radius: 6px; padding: 8px; margin-bottom: 8px; }
        .info-block { flex: 1; }
        .info-label { font-size: 8px; color: #888; text-transform: uppercase; }
        .info-value { font-size: 11px; font-weight: 600; }

        /* Tableau des notes */
        table { width: 100%; border-collapse: collapse; margin-bottom: 8px; }
        table thead { background: #1a4480; color: #fff; }
        table thead th { padding: 5px 4px; text-align: center; font-size: 9px; font-weight: 600; border: 1px solid #0d3470; }
        table tbody tr { border-bottom: 1px solid #e0e0e0; }
        table tbody tr:nth-child(even) { background: #f8f9fa; }
        table tbody tr.failing { background: #fff3f3; }
        table tbody td { padding: 4px; border: 1px solid #e0e0e0; font-size: 9.5px; }
        .td-center { text-align: center; }
        .td-subject { font-weight: 600; }
        .score-good { color: #155724; font-weight: 700; }
        .score-fail { color: #721c24; font-weight: 700; }
        .score-na { color: #999; }

        /* Bilan général */
        .summary-box { border: 2px solid #1a4480; border-radius: 8px; padding: 10px; margin-bottom: 8px; }
        .summary-grid { display: flex; gap: 8px; text-align: center; }
        .summary-item { flex: 1; border: 1px solid #dee2e6; border-radius: 6px; padding: 6px; }
        .summary-item .big { font-size: 16px; font-weight: 900; color: #1a4480; }
        .summary-item .label { font-size: 8px; color: #666; }
        .appreciation { background: #e8f4fd; border-left: 3px solid #1a4480; padding: 6px; border-radius: 0 4px 4px 0; font-style: italic; margin-top: 6px; font-size: 9px; }

        /* Signatures */
        .signatures { display: flex; gap: 20px; margin-top: 12px; }
        .sig-box { flex: 1; border-top: 1px solid #333; padding-top: 4px; text-align: center; font-size: 8.5px; color: #555; }

        /* Impression */
        @media print {
            .bulletin-page { padding: 8mm; }
        }
    </style>
</head>
<body>

@foreach($bulletinsData as $bd)
<div class="bulletin-page">

    {{-- En-tête --}}
    <div class="header">
        <div>
            <div class="school-name">MILLENAIRE CONNECT</div>
            <div class="school-sub">République du Cameroun — Éducation de Base</div>
        </div>
        <div class="bulletin-title">BULLETIN DE NOTES<br>TRIMESTRE {{ $term }} — {{ $academicYear }}</div>
        <div style="text-align:right; font-size:9px; color:#666">
            Classe : <strong>{{ $classe->name }}</strong><br>
            Effectif : <strong>{{ $bd['total_students'] }}</strong> élèves
        </div>
    </div>

    {{-- Info élève --}}
    <div class="student-info">
        <div class="info-block">
            <div class="info-label">Nom & Prénom</div>
            <div class="info-value">{{ $bd['student']['name'] }}</div>
        </div>
        <div class="info-block">
            <div class="info-label">Matricule</div>
            <div class="info-value">{{ $bd['student']['matricule'] }}</div>
        </div>
        <div class="info-block">
            <div class="info-label">Classe</div>
            <div class="info-value">{{ $bd['student']['classe'] }}</div>
        </div>
        <div class="info-block">
            <div class="info-label">Rang</div>
            <div class="info-value">{{ $bd['rank_display'] ?? '—' }}</div>
        </div>
        <div class="info-block">
            <div class="info-label">Moy. Trim.</div>
            <div class="info-value {{ ($bd['term_average'] ?? 0) >= 10 ? 'score-good' : 'score-fail' }}">
                {{ $bd['term_average'] ? number_format($bd['term_average'], 2) : '—' }}/20
            </div>
        </div>
    </div>

    {{-- Tableau des matières --}}
    <table>
        <thead>
            <tr>
                <th style="width:25%; text-align:left; padding-left:6px">Matière</th>
                <th style="width:8%">Coeff.</th>
                <th style="width:10%">Séq.1<br>/20</th>
                <th style="width:10%">Séq.2<br>/20</th>
                <th style="width:10%">Moy.<br>/20</th>
                <th style="width:10%">Points</th>
                <th style="width:10%">Professeur</th>
                <th style="width:17%">Appréciation</th>
            </tr>
        </thead>
        <tbody>
            @foreach($bd['subjects'] as $subj)
            @php
                $avg = $subj['subject_average'];
                $pass = $avg !== null && $avg >= 10;
                $points = $avg !== null ? round($avg * $subj['coefficient'], 2) : null;
            @endphp
            <tr class="{{ $avg !== null && $avg < 10 ? 'failing' : '' }}">
                <td class="td-subject">{{ $subj['subject_name'] }}</td>
                <td class="td-center">{{ $subj['coefficient'] }}</td>
                <td class="td-center {{ $subj['seq1_score'] !== null ? ($subj['seq1_score'] >= 10 ? 'score-good' : 'score-fail') : 'score-na' }}">
                    {{ $subj['seq1_score'] !== null ? number_format($subj['seq1_score'], 2) : 'ABS' }}
                </td>
                <td class="td-center {{ $subj['seq2_score'] !== null ? ($subj['seq2_score'] >= 10 ? 'score-good' : 'score-fail') : 'score-na' }}">
                    {{ $subj['seq2_score'] !== null ? number_format($subj['seq2_score'], 2) : 'ABS' }}
                </td>
                <td class="td-center {{ $avg !== null ? ($pass ? 'score-good' : 'score-fail') : 'score-na' }}">
                    {{ $avg !== null ? number_format($avg, 2) : '—' }}
                </td>
                <td class="td-center">{{ $points !== null ? number_format($points, 2) : '—' }}</td>
                <td style="font-size:8.5px">{{ Str::limit($subj['teacher_name'], 15) }}</td>
                <td style="font-size:8.5px; font-style:italic; color:#555">
                    {{ $subj['auto_appreciation']['text'] ?? ($subj['teacher_comment'] ? Str::limit($subj['teacher_comment'], 30) : '—') }}
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    {{-- Résumé --}}
    <div class="summary-box">
        <div class="summary-grid">
            <div class="summary-item">
                <div class="big">{{ $bd['seq1_average'] ? number_format($bd['seq1_average'], 2) : '—' }}</div>
                <div class="label">Moy. Séquence 1</div>
            </div>
            <div class="summary-item">
                <div class="big">{{ $bd['seq2_average'] ? number_format($bd['seq2_average'], 2) : '—' }}</div>
                <div class="label">Moy. Séquence 2</div>
            </div>
            <div class="summary-item">
                <div class="big" style="color: {{ ($bd['term_average'] ?? 0) >= 10 ? '#155724' : '#721c24' }}">
                    {{ $bd['term_average'] ? number_format($bd['term_average'], 2) : '—' }}/20
                </div>
                <div class="label">Moy. Trimestre</div>
            </div>
            <div class="summary-item">
                <div class="big">{{ $bd['rank_display'] ?? '—' }}</div>
                <div class="label">Classement</div>
            </div>
        </div>

        @if($bd['appreciation'])
        <div class="appreciation">
            Appréciation générale : <strong>{{ $bd['appreciation'] }}</strong>
        </div>
        @endif

        @if($bd['summary'] && $bd['summary']->principal_teacher_comment)
        <div class="appreciation" style="border-left-color: #28a745; background:#e8f5e9;">
            Prof. Principal : {{ $bd['summary']->principal_teacher_comment }}
        </div>
        @endif
    </div>

    {{-- Signatures --}}
    <div class="signatures">
        <div class="sig-box">Le Professeur Principal</div>
        <div class="sig-box">Le Censeur</div>
        <div class="sig-box">Parent/Tuteur</div>
    </div>

</div>
@endforeach

<script>
    // Auto-print en mode export
    if (window.location.search.includes('print=1')) {
        window.onload = () => window.print();
    }
</script>
</body>
</html>
