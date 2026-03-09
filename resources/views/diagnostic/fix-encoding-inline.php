@php
// Correction d'encodage urgente pour les vues
$viewFile = __DIR__ . '/../setup.blade.php';
if (file_exists($viewFile)) {
    $content = file_get_contents($viewFile);
    
    // Remplacer les flèches mal encodées
    $content = str_replace('→', '→', $content);
    $content = str_replace('→', '→', $content);
    $content = str_replace('à', 'à', $content);
    $content = str_replace('é', 'é', $content);
    $content = str_replace('ê', 'ê', $content);
    $content = str_replace('à', 'à', $content);
    
    file_put_contents($viewFile, $content);
}
@endphp
