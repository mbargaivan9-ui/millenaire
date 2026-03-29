<!DOCTYPE html>
<html lang="{{ $config->langue==='FR'?'fr':'en' }}">
<head>
<meta charset="UTF-8">
<title>Bulletin — {{ $student->nom }}</title>
<style>
*{box-sizing:border-box;margin:0;padding:0}
body{font-family:'DejaVu Serif','Times New Roman',serif;font-size:9.5pt;color:#111;background:#fff}
.page{width:210mm;min-height:297mm;padding:10mm 12mm;background:#fff}
/* Header */
.header-table{width:100%;border-collapse:collapse;margin-bottom:4mm}
.header-col{width:42%;font-size:7.5pt;line-height:1.7;text-align:center;vertical-align:top}
.header-logo{width:16%;text-align:center;vertical-align:middle}
.header-logo img{width:55pt;height:55pt;object-fit:contain}
.logo-placeholder{width:55pt;height:55pt;border:2pt solid #0d9488;border-radius:6pt;display:inline-flex;align-items:center;justify-content:center;font-size:7pt;color:#0d9488;text-align:center}
.school-name-bar{text-align:center;font-size:13pt;font-weight:bold;border-top:2.5pt solid #0d9488;border-bottom:2.5pt solid #0d9488;padding:2mm 0;margin-bottom:3mm}
.bulletin-title{text-align:center;font-weight:bold;font-size:11pt;text-transform:uppercase;margin-bottom:1mm}
.bulletin-sub{text-align:center;font-size:9pt;margin-bottom:4mm}
/* Student Info Box */
.info-box{border:1.5pt solid #0d9488;border-radius:3pt;padding:4mm 5mm;margin-bottom:4mm}
.info-box-title{font-weight:bold;font-size:9.5pt;color:#0d9488;text-transform:uppercase;margin-bottom:3mm}
.info-grid{display:grid;grid-template-columns:1fr 1fr;gap:2mm 10mm;font-size:8.5pt}
/* Notes Table */
table{width:100%;border-collapse:collapse;margin-bottom:4mm;font-size:8.5pt}
thead tr{background:#0d9488;color:#fff}
th{padding:3pt 5pt;text-align:center;font-size:7.5pt;font-weight:bold;border:0.5pt solid #0a7a72}
td{padding:3pt 5pt;border:0.5pt solid #d1fae5;vertical-align:middle}
.td-left{text-align:left}
.td-center{text-align:center}
.row-even{background:#f8fffe}
/* Summary tables */
.summary-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:3mm;margin-bottom:4mm}
.sum-table{width:100%;border-collapse:collapse;font-size:7.5pt}
.sum-header{background:#0d9488;color:#fff;text-align:center;padding:3pt 4pt;font-weight:bold;font-size:7pt;text-transform:uppercase}
.sum-td{padding:2.5pt 4pt;border:0.5pt solid #d1fae5;vertical-align:middle}
/* Signatures */
.sig-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:4mm;margin-top:3mm}
.sig-box{border:1pt solid #0d9488;border-radius:3pt;min-height:40pt;padding:4pt 5pt}
.sig-title{font-size:7pt;font-weight:bold;text-align:center;color:#0d9488;text-transform:uppercase;margin-bottom:3mm}
.footer-note{margin-top:5mm;font-size:6.5pt;color:#888;border-top:0.5pt solid #e2e8f0;padding-top:3pt}
/* Colour helpers */
.c-pass{color:#059669}.c-fail{color:#dc2626}.c-ok{color:#2563eb}
</style>
</head>
<body>
<div class="page">

{{-- ── HEADER ── --}}
<table class="header-table">
<tr>
  <td class="header-col">
    <strong>RÉPUBLIQUE DU CAMEROUN</strong><br>
    Paix - Travail - Patrie<br>***********<br>
    Ministère de l'Éducation de Base<br>
    {{ $config->delegation_fr ?? 'Délégation Régionale' }}
  </td>
  <td class="header-logo">
    @if($config->logo_path)
      <img src="{{ public_path('storage/'.$config->logo_path) }}" alt="Logo">
    @else
      <div class="logo-placeholder">LOGO<br>ÉTS</div>
    @endif
  </td>
  <td class="header-col">
    <strong>REPUBLIC OF CAMEROON</strong><br>
    Peace - Work - Fatherland<br>***********<br>
    Ministry of Basic Education<br>
    {{ $config->delegation_en ?? 'Regional Delegation' }}
  </td>
</tr>
</table>

<div class="school-name-bar">{{ strtoupper($config->school_name) }}</div>

<div class="bulletin-title">
    @if($config->langue==='EN')
        REPORT CARD —
        @if($config->trimestre==1) 1ST TERM @elseif($config->trimestre==2) 2ND TERM @else 3RD TERM @endif
    @else
        BULLETIN SCOLAIRE — {{ $config->trimestre }}
        @if($config->trimestre==1)ER @else ÈME @endif TRIMESTRE
    @endif
</div>
<div class="bulletin-sub">
    {{ $config->langue==='EN'?'Academic Year':'Année Scolaire' }}: {{ $config->annee_academique }}
    &nbsp;|&nbsp; Séquence {{ $config->sequence }}
</div>

{{-- ── INFOS ÉLÈVE ── --}}
<div class="info-box">
  <div class="info-box-title">
    {{ $config->langue==='EN'?'STUDENT INFORMATION':'INFORMATIONS DE L\'ÉLÈVE' }}
  </div>
  <div class="info-grid">
    <div><strong>{{ $config->langue==='EN'?'Name':'Nom et Prénom' }}:</strong> {{ $student->nom }}</div>
    <div><strong>{{ $config->langue==='EN'?'ID':'Matricule' }}:</strong> {{ $student->matricule }}</div>
    <div><strong>{{ $config->langue==='EN'?'Date of Birth':'Né(e) le' }}:</strong> {{ $student->date_naissance ? (\Carbon\Carbon::parse($student->date_naissance)->format('d/m/Y')) : '—' }}</div>
    <div><strong>{{ $config->langue==='EN'?'Class':'Classe' }}:</strong> {{ $config->nom_classe }}</div>
    <div><strong>{{ $config->langue==='EN'?'Place of Birth':'Lieu de naissance' }}:</strong> {{ $student->lieu_naissance ?: '—' }}</div>
    <div><strong>{{ $config->langue==='EN'?'Class Size':'Effectif' }}:</strong> {{ $config->effectif }}</div>
    <div><strong>{{ $config->langue==='EN'?'Gender':'Sexe' }}:</strong> {{ $student->sexe==='M'?($config->langue==='EN'?'Male':'Masculin'):($config->langue==='EN'?'Female':'Féminin') }}</div>
    <div><strong>{{ $config->langue==='EN'?'Class Teacher':'Enseignant(e) principal(e)' }}:</strong> {{ $config->profPrincipal->name ?? '—' }}</div>
  </div>
</div>

{{-- ── TABLEAU DES NOTES ── --}}
@php
$totalPts  = 0; $totalCoef = 0;
foreach($subjects as $sub){
    $note=$student->notes->firstWhere('ng_subject_id',$sub->id)?->note;
    if($note!==null){ $totalPts+=$note*$sub->coefficient; $totalCoef+=$sub->coefficient; }
}
$moyenne = $totalCoef>0 ? round($totalPts/$totalCoef,2) : 0;
$rang    = $stats['ranks'][$student->id] ?? '-';
@endphp

<table>
<thead>
<tr>
  <th style="text-align:left;width:28%">{{ $config->langue==='EN'?'SUBJECTS':'MATIÈRES' }}</th>
  <th>SEQ1</th><th>SEQ2</th><th>COMP</th>
  <th>MOY</th><th>COEF</th><th>TOTAL</th><th>RANG</th>
  <th>{{ $config->langue==='EN'?'APPRECIATION':'APPRÉCIATION' }}</th>
</tr>
</thead>
<tbody>
@foreach($subjects as $i=>$sub)
@php
  $noteObj = $student->notes->firstWhere('ng_subject_id',$sub->id);
  $note    = $noteObj?->note;
  $total   = $note!==null ? round($note*$sub->coefficient,2) : null;
  $notesArr= $student->notes->where('ng_subject_id',$sub->id)->pluck('note')->toArray();
  $subRankSorted = \App\Models\BulletinNgNote::where('ng_subject_id',$sub->id)->orderByDesc('note')->pluck('ng_student_id')->values();
  $subRank = $subRankSorted->search($student->id);
  $subRank = $subRank!==false ? $subRank+1 : '—';
  $app = $note!==null ? match(true){
    $note<10  => $config->langue==='EN'?'Fail':'Échec',
    $note<12  => $config->langue==='EN'?'Pass':'Passable',
    $note<15  => $config->langue==='EN'?'Fairly Good':'Assez Bien',
    $note<17  => $config->langue==='EN'?'Good':'Bien',
    default   => 'Excellent'
  } : '—';
  $cls = $note!==null ? ($note<10?'c-fail':($note<15?'c-ok':'c-pass')) : '';
@endphp
<tr class="{{ $i%2==0?'row-even':'' }}">
  <td class="td-left">{{ $sub->nom }}</td>
  <td class="td-center">—</td>
  <td class="td-center">—</td>
  <td class="td-center {{ $cls }}">{{ $note!==null?number_format($note,2):'—' }}</td>
  <td class="td-center" style="font-weight:bold" class="{{ $cls }}">{{ $note!==null?number_format($note,2):'—' }}</td>
  <td class="td-center">{{ $sub->coefficient }}</td>
  <td class="td-center">{{ $total!==null?number_format($total,2):'—' }}</td>
  <td class="td-center">{{ $note!==null?$subRank:'—' }}</td>
  <td class="td-center {{ $cls }}" style="font-style:italic">{{ $app }}</td>
</tr>
@endforeach
</tbody>
</table>

{{-- ── TABLEAUX RÉCAPITULATIFS ── --}}
@php
$appGen = match(true){
  $moyenne<10  => $config->langue==='EN'?'FAIL':'ÉCHEC',
  $moyenne<12  => $config->langue==='EN'?'PASS':'PASSABLE',
  $moyenne<15  => $config->langue==='EN'?'FAIRLY GOOD':'ASSEZ BIEN',
  $moyenne<17  => $config->langue==='EN'?'GOOD':'BIEN',
  default      => 'EXCELLENT',
};
$clsGen = $moyenne>=10?'c-pass':'c-fail';
@endphp

<div class="summary-grid">
  {{-- Résultat élève --}}
  <div>
    <div class="sum-header">{{ $config->langue==='EN'?'STUDENT RESULT':'RÉSULTAT DE L\'ÉLÈVE' }}</div>
    <table class="sum-table">
      <tr><td class="sum-td">{{ $config->langue==='EN'?'Average':'Moyenne' }}</td><td class="sum-td td-center {{ $clsGen }}" style="font-weight:bold">{{ number_format($moyenne,2) }}/20</td></tr>
      <tr><td class="sum-td">{{ $config->langue==='EN'?'Rank':'Rang' }}</td><td class="sum-td td-center" style="font-weight:bold">{{ $rang }}/{{ $config->effectif }}</td></tr>
      <tr><td colspan="2" class="sum-td td-center {{ $clsGen }}" style="font-weight:bold">{{ $appGen }}</td></tr>
    </table>
  </div>
  {{-- Profil classe --}}
  <div>
    <div class="sum-header">{{ $config->langue==='EN'?'CLASS PROFILE':'PROFIL DE LA CLASSE' }}</div>
    <table class="sum-table">
      <tr><td class="sum-td">{{ $config->langue==='EN'?'Pass (≥10)':'Moy. ≥10' }}</td><td class="sum-td td-center">{{ $stats['passing'] }}</td></tr>
      <tr><td class="sum-td">{{ $config->langue==='EN'?'Class Avg':'Moy. Classe' }}</td><td class="sum-td td-center">{{ number_format($stats['avg'],2) }}</td></tr>
      <tr><td class="sum-td">{{ $config->langue==='EN'?'% Success':'% Réussite' }}</td><td class="sum-td td-center">{{ $stats['pct'] }}%</td></tr>
      <tr><td class="sum-td">{{ $config->langue==='EN'?'Highest':'Moy. Max' }}</td><td class="sum-td td-center">{{ number_format($stats['max'],2) }}</td></tr>
      <tr><td class="sum-td">{{ $config->langue==='EN'?'Lowest':'Moy. Min' }}</td><td class="sum-td td-center">{{ number_format($stats['min'],2) }}</td></tr>
    </table>
  </div>
  {{-- Travail --}}
  @php $cond = $student->conduite; @endphp
  <div>
    <div class="sum-header">{{ $config->langue==='EN'?'WORK':'TRAVAIL DE L\'ÉLÈVE' }}</div>
    <table class="sum-table">
      <tr><td class="sum-td">{{ $config->langue==='EN'?'Honor Roll':"T. d'Honneur" }}</td><td class="sum-td td-center">{{ $cond&&$cond->tableau_honneur?'Oui':'Non' }}</td></tr>
      <tr><td class="sum-td">{{ $config->langue==='EN'?'Encouragement':'Encouragement' }}</td><td class="sum-td td-center">{{ $cond&&$cond->encouragement?'Oui':'Non' }}</td></tr>
      <tr><td class="sum-td">{{ $config->langue==='EN'?'Congratulations':'Félicitations' }}</td><td class="sum-td td-center">{{ $cond&&$cond->felicitations?'Oui':'Non' }}</td></tr>
      <tr><td class="sum-td">{{ $config->langue==='EN'?'Work Warning':'Avert. Travail' }}</td><td class="sum-td td-center">{{ $cond?$cond->avert_travail:'Non' }}</td></tr>
      <tr><td class="sum-td">{{ $config->langue==='EN'?'Work Blame':'Blame Travail' }}</td><td class="sum-td td-center">{{ $cond&&$cond->blame_travail?'Oui':'Non' }}</td></tr>
    </table>
  </div>
  {{-- Conduite --}}
  <div>
    <div class="sum-header">{{ $config->langue==='EN'?'CONDUCT':'CONDUITE DE L\'ÉLÈVE' }}</div>
    <table class="sum-table">
      <tr><td class="sum-td">{{ $config->langue==='EN'?'Total Abs.':'Absences Tot.' }}</td><td class="sum-td td-center">{{ $cond?$cond->absences_totales:0 }} H</td></tr>
      <tr><td class="sum-td">{{ $config->langue==='EN'?'Unj. Abs.':'Absences NJ' }}</td><td class="sum-td td-center">{{ $cond?$cond->absences_nj:0 }} H</td></tr>
      <tr><td class="sum-td">{{ $config->langue==='EN'?'Exclusions':'Exclusions' }}</td><td class="sum-td td-center">{{ $cond&&$cond->exclusion?'Oui':'Non' }}</td></tr>
      <tr><td class="sum-td">{{ $config->langue==='EN'?'Cond. Warning':'Aver. Conduite' }}</td><td class="sum-td td-center">{{ $cond?$cond->avert_conduite:'Non' }}</td></tr>
      <tr><td class="sum-td">{{ $config->langue==='EN'?'Cond. Blame':'Blame Conduite' }}</td><td class="sum-td td-center">{{ $cond?$cond->blame_conduite:'Non' }}</td></tr>
    </table>
  </div>
</div>

{{-- ── SIGNATURES ── --}}
<div class="sig-grid">
  <div class="sig-box"><div class="sig-title">{{ $config->langue==='EN'?'PARENT SIGNATURE':'VISA DU PARENT' }}</div></div>
  <div class="sig-box"><div class="sig-title">{{ $config->langue==='EN'?'CLASS COUNCIL DECISION':'DÉCISION DU CONSEIL DE CLASSE' }}</div></div>
  <div class="sig-box">
    <div class="sig-title">{{ $config->langue==='EN'?'PRINCIPAL\'S SIGNATURE':'VISA DU CHEF D\'ÉTABLISSEMENT' }}</div>
    <div style="text-align:center;font-size:7.5pt;margin-top:18pt;">LE PRINCIPAL</div>
  </div>
</div>

<div class="footer-note">
  Le bulletin est délivré sans rature, ni surcharge. | Report card issued without erasures or overwriting.
</div>

</div>
</body>
</html>
