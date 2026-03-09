<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Exception;

/**
 * OCRZoneDetectionService
 * 
 * Détecte les zones de texte dans les images OCR
 * Retourne les coordonnées (bounding boxes) pour un affichage visual
 */
class OCRZoneDetectionService
{
    /**
     * Détecter les zones de texte dans une image via Tesseract
     * Retourne les bounding boxes (position + dimensions)
     */
    public function detectZones(string $imagePath): array
    {
        try {
            // Utiliser tesseract avec output tsv pour obtenir les positions
            $outputFile = sys_get_temp_dir() . '/ocr_zones_' . uniqid();
            $tesseractPath = config('ocr.tesseract.path', 'C:/Program Files/Tesseract-OCR/tesseract.exe');
            $tessdataPath = $this->getTessdataPath();
            $language = config('ocr.tesseract.language', 'fra+eng');
            
            putenv("TESSDATA_PREFIX=$tessdataPath");
            
            // Convertir slashes sur Windows
            if (PHP_OS_FAMILY === 'Windows') {
                $tesseractPath = str_replace('/', '\\', $tesseractPath);
                $tessdataPath = str_replace('/', '\\', $tessdataPath);
            }
            
            // Tesseract TSV output format: confid box
            $command = sprintf(
                '%s %s %s tsv 2>&1',
                (strpos($tesseractPath, ' ') !== false ? '"' . $tesseractPath . '"' : $tesseractPath),
                (strpos($imagePath, ' ') !== false ? '"' . $imagePath . '"' : $imagePath),
                $outputFile
            );
            
            Log::debug('Tesseract zone detection command', ['command' => $command]);
            
            exec($command, $output, $returnCode);
            
            $zones = [];
            $tsvFile = $outputFile . '.tsv';
            
            if (file_exists($tsvFile)) {
                $zones = $this->parseTesseractTSV($tsvFile);
                @unlink($tsvFile);
            }
            
            return [
                'success' => true,
                'zones' => $zones,
                'count' => count($zones),
            ];
        } catch (Exception $e) {
            Log::error('Zone detection failed', ['error' => $e->getMessage()]);
            return [
                'success' => false,
                'zones' => [],
                'error' => $e->getMessage(),
            ];
        }
    }
    
    /**
     * Parser le format TSV de Tesseract
     * Format: level page_num block_num par_num line_num word_num left top width height conf text
     */
    private function parseTesseractTSV(string $tsvFile): array
    {
        $zones = [];
        $lines = file($tsvFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        
        foreach ($lines as $line) {
            $parts = preg_split('/\t+/', trim($line));
            
            // Ignorer les headers et les lignes mal formées
            if (count($parts) < 11 || $parts[0] === 'level') {
                continue;
            }
            
            $level = (int)$parts[0];
            $confidence = (int)$parts[9];
            $text = trim($parts[10] ?? '');
            
            // Ignorer les mots avec confiance faible ou vides
            if ($confidence < 50 || empty($text)) {
                continue;
            }
            
            // Seulement garder les mots (level = 5)
            if ($level === 5) {
                $zones[] = [
                    'text' => $text,
                    'x' => (int)$parts[6],      // left
                    'y' => (int)$parts[7],      // top
                    'width' => (int)$parts[8],  // width
                    'height' => (int)$parts[9], // height
                    'confidence' => $confidence,
                    'id' => uniqid('zone_'),
                ];
            }
        }
        
        return $this->groupZonesByLine($zones);
    }
    
    /**
     * Grouper les zones par ligne (pour gestion pour plus efficace)
     */
    private function groupZonesByLine(array $zones): array
    {
        if (empty($zones)) {
            return [];
        }
        
        // Trier par Y (top) puis X (left)
        usort($zones, function($a, $b) {
            if (abs($a['y'] - $b['y']) > 5) {
                return $a['y'] <=> $b['y'];
            }
            return $a['x'] <=> $b['x'];
        });
        
        // Grouper par ligne
        $lines = [];
        $currentLine = null;
        $currentLineY = null;
        
        foreach ($zones as $zone) {
            // Vérifier si on doit créer une nouvelle ligne
            if ($currentLineY === null || abs($zone['y'] - $currentLineY) > 10) {
                if ($currentLine !== null) {
                    $lines[] = $currentLine;
                }
                $currentLine = [
                    'id' => uniqid('line_'),
                    'y' => $zone['y'],
                    'zones' => [$zone],
                ];
                $currentLineY = $zone['y'];
            } else {
                $currentLine['zones'][] = $zone;
            }
        }
        
        if ($currentLine !== null) {
            $lines[] = $currentLine;
        }
        
        return $lines;
    }
    
    /**
     * Obtenir le chemin tessdata
     */
    private function getTessdataPath(): string
    {
        $paths = [
            getenv('TESSDATA_PREFIX'),
            'C:/Program Files/Tesseract-OCR/tessdata',
            'C:/Program Files (x86)/Tesseract-OCR/tessdata',
            '/usr/share/tesseract-ocr/4.00/tessdata',
        ];
        
        foreach ($paths as $path) {
            if ($path) {
                $pathCheck = PHP_OS_FAMILY === 'Windows' ? str_replace('/', '\\', $path) : $path;
                if (is_dir($pathCheck)) {
                    return $pathCheck;
                }
            }
        }
        
        return PHP_OS_FAMILY === 'Windows' 
            ? 'C:\\Program Files\\Tesseract-OCR\\tessdata'
            : '/usr/share/tesseract-ocr/4.00/tessdata';
    }
}
