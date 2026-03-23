<!DOCTYPE html>
<html lang="{{ $config->langue==='FR'?'fr':'en' }}">
<head>
<meta charset="UTF-8">
<title>Bulletins — {{ $config->nom_classe }}</title>
<style>
  .page-break { page-break-after: always; }
</style>
</head>
<body>
@foreach($students as $i => $student)
  @include('teacher.bulletin_ng.pdf.bulletin', [
      'config'   => $config,
      'student'  => $student,
      'subjects' => $subjects,
      'stats'    => $stats,
  ])
  @if(!$loop->last)
    <div class="page-break"></div>
  @endif
@endforeach
</body>
</html>
