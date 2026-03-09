<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Config;
use Exception;

/**
 * OCR Service - Extrait le texte des images/PDFs de bulletins
 * 
 * Supporte deux backends:
 * 1. OCR.Space API (cloud, gratuit avec limites)
 * 2. Local Tesseract (nécessite installation système)
 * 
 * Configuration via config/ocr.php ou .env
 */
class BulletinOCRService
{
    const BACKEND_OCRDOTSPACE = 'ocr.space';
    const BACKEND_TESSERACT = 'tesseract';
    const MIN_CONFIDENCE = 60;
    
    private string $backend;
    private string $language;
    private array $extractedData = [];
    private array $metadata = [];
    
    public function __construct(
        ?string $backend = null,
        ?string $language = null
    ) {
        // Lire depuis config/ocr.php si pas fourni
        $this->backend = $backend ?? Config::get('ocr.backend', self::BACKEND_TESSERACT);
        $this->language = $language ?? Config::get('ocr.tesseract.language', 'fra+eng');
        
        Log::info('BulletinOCRService initialized', [
            'backend' => $this->backend,
            'language' => $this->language,
        ]);
    }

    /**
     * Process uploaded file and extract text
     * 
     * @param UploadedFile $file
     * @return array Extracted data with OCR text
     */
    public function processFile(UploadedFile $file): array
    {
        try {
            $this->validateFile($file);
            $path = $file->getRealPath();
            
            Log::info("OCR: Processing file", [
                'name' => $file->getClientOriginalName(),
                'size' => $file->getSize(),
                'mime' => $file->getMimeType(),
                'backend' => $this->backend,
            ]);

            // Essayer le backend configuré
            $result = match ($this->backend) {
                self::BACKEND_OCRDOTSPACE => $this->extractWithOCRSpace($path),
                self::BACKEND_TESSERACT => $this->extractWithTesseract($path),
                default => throw new Exception("Unknown OCR backend: {$this->backend}"),
            };

            // Si le backend principal échoue, faire un fallback
            if (!($result['success'] ?? false)) {
                Log::warning("OCR Primary backend failed, trying fallback", [
                    'backend' => $this->backend,
                    'error' => $result['error'] ?? 'Unknown',
                ]);
                
                // Fallback: Si configuaré en Tesseract, essayer OCR.Space
                if ($this->backend === self::BACKEND_TESSERACT) {
                    Log::info('OCR: Falling back to OCR.Space');
                    $result = $this->extractWithOCRSpace($path);
                }
                
                // Si toujours échoué, essayer SimpleOCR
                if (!($result['success'] ?? false)) {
                    Log::info('OCR: Falling back to SimpleOCR');
                    // SimpleOCR tentérait aussi ses propres fallbacks
                }
            }

            Log::info("OCR: Extraction complete", [
                'text_length' => strlen($result['text'] ?? ''),
                'method' => $result['method'] ?? 'unknown',
                'success' => $result['success'] ?? false,
            ]);

            return $result;
        } catch (Exception $e) {
            Log::error("OCR: Processing failed - " . $e->getMessage());
            
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'backend' => $this->backend,
            ];
        }
    }

    /**
     * Extraire texte d'une image via OCR.Space (online, gratuit)
     */
    public function extractWithOCRSpace(string $filePath): array
    {
        try {
            if (!file_exists($filePath)) {
                throw new Exception("File not found: $filePath");
            }

            $fileContent = file_get_contents($filePath);
            $base64 = base64_encode($fileContent);
            $mimeType = $this->getMimeType($filePath);
            
            // Récupérer l'URL et la clé depuis config
            $apiUrl = Config::get('ocr.ocr_space.api_url', 'https://api.ocr.space/parse/image');
            $apiKey = Config::get('ocr.ocr_space.api_key');
            
            // Essayer d'abord sans clé API (limite: 25 req/jour)
            $payload = [
                'base64Image' => "data:{$mimeType};base64,{$base64}",
                'language' => $this->language,
                'isOverlayRequired' => false,
            ];
            
            Log::info('OCR.Space: Tentative sans clé API');

            $response = Http::timeout(60)->post($apiUrl, $payload);

            if ($response->status() === 403) {
                Log::warning('OCR.Space 403: Quota atteint ou clé invalide, basculer sur Tesseract');
                // Fallback to Tesseract si quota atteint
                return $this->extractWithTesseract($filePath);
            }

            if (!$response->successful()) {
                Log::error('OCR.Space error', ['status' => $response->status(), 'body' => $response->body()]);
                throw new Exception("OCR.Space API error: {$response->status()}");
            }

            $data = $response->json();

            if (!($data['IsErroredOnProcessing'] ?? false) && isset($data['ParsedText'])) {
                $text = $data['ParsedText'];
                
                Log::info('OCR.Space success', ['text_length' => strlen($text), 'confidence' => $data['Confidence'] ?? 'N/A']);
                
                return [
                    'success' => true,
                    'text' => $text,
                    'raw_text' => $text,
                    'confidence' => $data['Confidence'] ?? 75,
                    'method' => 'ocr.space',
                    'tables' => $this->detectTables($text),
                    'metadata' => [
                        'source' => 'ocr.space',
                        'language' => $this->language,
                        'processing_time' => $data['ProcessingTimeInMilliseconds'] ?? null,
                    ],
                ];
            } else {
                $errorMsg = $data['ErrorMessage']['ErrorMessage'] ?? $data['ErrorMessage'] ?? 'Unknown OCR.Space error';
                Log::error('OCR.Space processing error', ['error' => $errorMsg]);
                throw new Exception($errorMsg);
            }
        } catch (Exception $e) {
            throw new Exception("OCR.Space extraction failed: " . $e->getMessage());
        }
    }

    /**
     * Alias pour extractWithOCRSpace - entrée publique
     */
    public function extractFromImage(string $filePath): array
    {
        return $this->extractWithOCRSpace($filePath);
    }

    /**
     * Vérifier que la confiance OCR est acceptable
     */
    public function isConfidenceAcceptable(float $confidence): bool
    {
        return $confidence >= self::MIN_CONFIDENCE;
    }
    /**
     * Extract via local Tesseract (requires system installation)
     * Supporte Python + pytesseract également
     */
    public function extractWithTesseract(string $filePath): array
    {
        try {
            // Try Python + Pytesseract first (better on Windows)
            if ($this->isPytesseractAvailable()) {
                return $this->extractWithPytesseract($filePath);
            }
            
            // Check if Tesseract CLI is installed
            if (!$this->isTesseractInstalled()) {
                // Si ni Tesseract ni Python disponibles, donner une option OCR.Space
                throw new Exception(
                    "Tesseract OCR et Python Pytesseract non disponibles. " .
                    "Options: " .
                    "1) Installer Tesseract: https://github.com/UB-Mannheim/tesseract/wiki " .
                    "2) Utiliser OCR.Space (recommandé, cloud API gratuit) en mettant OCR_BACKEND=ocr.space"
                );
            }
            
            // Vérifier que les fichiers de langue existent
            $this->validateTesseractLanguages();

            // Use Tesseract CLI
            return $this->extractViaCliTesseract($filePath);
            
        } catch (Exception $e) {
            Log::error('Tesseract extraction failed', ['error' => $e->getMessage()]);
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'text' => '',
            ];
        }
    }

    /**
     * Extraction via Python pytesseract
     */
    private function extractWithPytesseract(string $filePath): array
    {
        $pythonScript = <<<'PYTHON'
import pytesseract
from PIL import Image
import json
import sys

try:
    img = Image.open(sys.argv[1])
    text = pytesseract.image_to_string(img, lang='fra+eng')
    print(json.dumps({
        'success': True,
        'text': text,
        'confidence': 80,
        'method': 'pytesseract'
    }))
except Exception as e:
    print(json.dumps({
        'success': False,
        'error': str(e),
        'text': ''
    }))
PYTHON;

        $scriptFile = sys_get_temp_dir() . '/ocr_' . uniqid() . '.py';
        file_put_contents($scriptFile, $pythonScript);
        
        $command = sprintf('python "%s" "%s" 2>&1', $scriptFile, escapeshellarg($filePath));
        exec($command, $output, $returnCode);
        unlink($scriptFile);
        
        $result = json_decode(implode("\n", $output), true);
        
        if ($result['success'] ?? false) {
            return [
                'success' => true,
                'text' => $result['text'],
                'raw_text' => $result['text'],
                'confidence' => $result['confidence'] ?? 80,
                'method' => 'pytesseract',
                'tables' => $this->detectTables($result['text']),
                'metadata' => ['source' => 'pytesseract'],
            ];
        } else {
            throw new Exception($result['error'] ?? 'Python extraction failed');
        }
    }

    /**
     * Extraction via Tesseract CLI
     */
    private function extractViaCliTesseract(string $filePath): array
    {
        // Définir TESSDATA_PREFIX directement en PHP (fonctionne mieux sur Windows)
        $tessdataPath = $this->getTessdataPath();
        putenv("TESSDATA_PREFIX=$tessdataPath");
        
        $outputFile = sys_get_temp_dir() . '/ocr_' . uniqid();
        
        // Récupérer le chemin Tesseract depuis config
        $tesseractPath = Config::get('ocr.tesseract.path', 'C:/Program Files/Tesseract-OCR/tesseract.exe');
        
        // Convertir les slashes / en \ sur Windows
        if (PHP_OS_FAMILY === 'Windows') {
            $tesseractPath = str_replace('/', '\\', $tesseractPath);
            $tessdataPath = str_replace('/', '\\', $tessdataPath);
            putenv("TESSDATA_PREFIX=$tessdataPath");
            
            // Utiliser des guillemets pour les chemins avec espaces
            if (strpos($tesseractPath, ' ') !== false) {
                $tesseractPath = '"' . $tesseractPath . '"';
            }
            // Ne pas utiliser escapeshellarg car cela ajoute des quotes supplémentaires
            $filePathEscaped = (strpos($filePath, ' ') !== false) ? '"' . $filePath . '"' : $filePath;
            $outputFileEscaped = (strpos($outputFile, ' ') !== false) ? '"' . $outputFile . '"' : $outputFile;
        } else {
            $filePathEscaped = escapeshellarg($filePath);
            $outputFileEscaped = escapeshellarg($outputFile);
        }
        
        // Construire la commande Tesseract
        $language = $this->language;
        
        // Si fra n'existe pas, utiliser eng seulement
        if (strpos($language, 'fra') !== false && !$this->hasLanguageFile('fra', $tessdataPath)) {
            Log::warning('Tesseract: Language fra not found, using eng only');
            $language = 'eng';
        }
        
        $command = sprintf(
            '%s %s %s -l %s --psm 6 --oem 3 2>&1',
            $tesseractPath,
            $filePathEscaped,
            $outputFileEscaped,
            escapeshellarg($language)
        );

        Log::info('Tesseract Command', [
            'language' => $language,
            'tesseract_path' => $tesseractPath,
            'tessdata_path' => $tessdataPath,
            'psm' => 6,
            'oem' => 3,
        ]);
        
        exec($command, $output, $returnCode);
        
        // Vérifier si le fichier de sortie a été créé
        $outputTxtFile = $outputFile . '.txt';

        if ($returnCode === 0 && file_exists($outputTxtFile)) {
            $text = file_get_contents($outputTxtFile);
            @unlink($outputTxtFile);

            Log::info('Tesseract success', [
                'text_length' => strlen($text),
                'language' => $language,
                'return_code' => $returnCode,
            ]);

            return [
                'success' => true,
                'text' => trim($text),
                'raw_text' => trim($text),
                'confidence' => 85,
                'method' => 'tesseract',
                'tables' => $this->detectTables($text),
                'metadata' => [
                    'source' => 'tesseract',
                    'language' => $language,
                    'version' => 'cli',
                ],
            ];
        }

        // Le fichier n'a pas été créé - analyser l'erreur
        $errorMsg = "Tesseract failed to generate output file";
        if (!empty($output)) {
            $errorMsg = implode("\n", $output);
        } elseif ($returnCode !== 0) {
            $errorMsg = "Tesseract failed with code: $returnCode";
        }
        
        Log::error('Tesseract Error', [
            'return_code' => $returnCode,
            'output' => $output,
            'file' => basename($filePath),
            'output_file_expected' => $outputTxtFile,
        ]);

        throw new Exception($errorMsg);
    }
    
    /**
     * Obtenirdirectory avec fichiers de langue Tesseract
     */
    private function getTessdataPath(): string
    {
        // Vérifier les chemins courants pour tessdata
        $paths = [
            getenv('TESSDATA_PREFIX'),
            'C:\Program Files\Tesseract-OCR\tessdata',
            'C:\Program Files (x86)\Tesseract-OCR\tessdata',
            '/usr/share/tesseract-ocr/4.00/tessdata',
            '/usr/local/share/tessdata',
            '/opt/homebrew/share/tessadata',
        ];
        
        foreach ($paths as $path) {
            if ($path) {
                // Normaliser les chemins Windows
                if (PHP_OS_FAMILY === 'Windows') {
                    $pathCheck = str_replace('/', '\\', $path);
                } else {
                    $pathCheck = $path;
                }
                
                if (is_dir($pathCheck)) {
                    Log::debug('Tesseract tessdata found', ['path' => $pathCheck]);
                    return $pathCheck;
                }
            }
        }
        
        // Fallback: retourner le standard
        if (PHP_OS_FAMILY === 'Windows') {
            return 'C:\\Program Files\\Tesseract-OCR\\tessdata';
        } else {
            return '/usr/share/tesseract-ocr/4.00/tessdata';
        }
    }
    
    /**
     * Vérifier si un fichier de langue existe
     */
    private function hasLanguageFile(string $language, string $tessdataPath): bool
    {
        $file = $tessdataPath . '/' . $language . '.traineddata';
        $exists = file_exists($file);
        Log::debug('Language file check', ['language' => $language, 'path' => $file, 'exists' => $exists]);
        return $exists;
    }
    
    /**
     * Valider que Tesseract peut accéder aux fichiers de langue
     */
    private function validateTesseractLanguages(): void
    {
        $tessdataPath = $this->getTessdataPath();
        $languages = explode('+', $this->language);
        
        Log::debug('Validating Tesseract languages', ['languages' => $languages, 'tessdata_path' => $tessdataPath]);
        
        foreach ($languages as $lang) {
            $lang = trim($lang);
            if (!$this->hasLanguageFile($lang, $tessdataPath)) {
                Log::warning('Tesseract language file missing', [
                    'language' => $lang,
                    'tessdata_path' => $tessdataPath,
                ]);
            }
        }
    }

    /**
     * Check if Tesseract is installed
     */
    private function isTesseractInstalled(): bool
    {
        $path = Config::get('ocr.tesseract.path', 'tesseract');
        
        Log::debug('Checking Tesseract', ['path' => $path, 'os' => PHP_OS_FAMILY]);
        
        // Si c'est un chemin complet (pas juste 'tesseract'), vérifier si fichier existe
        if (strpos($path, '/') !== false || strpos($path, '\\') !== false) {
            $exists = file_exists($path);
            Log::debug('Tesseract path check', ['path' => $path, 'exists' => $exists]);
            return $exists;
        }
        
        // Sinon vérifier si commande est disponible
        if (PHP_OS_FAMILY === 'Windows') {
            exec('where.exe tesseract 2>nul', $output, $returnCode);
        } else {
            exec('which tesseract 2>/dev/null', $output, $returnCode);
        }
        
        $available = $returnCode === 0 && !empty($output);
        Log::debug('Tesseract availability', ['available' => $available]);
        return $available;
    }

    /**
     * Check if Python pytesseract is available
     */
    private function isPytesseractAvailable(): bool
    {
        // Try multiple Python commands because of Windows Store Python issues
        $pythonCommands = ['python3', 'python', 'py'];
        
        foreach ($pythonCommands as $pythonCmd) {
            if (PHP_OS_FAMILY === 'Windows') {
                $testCmd = sprintf('%s -c "import pytesseract; import PIL; from PIL import Image" 2>nul', $pythonCmd);
            } else {
                $testCmd = sprintf('%s -c "import pytesseract; import PIL; from PIL import Image" 2>/dev/null', $pythonCmd);
            }
            
            exec($testCmd, $output, $returnCode);
            
            if ($returnCode === 0) {
                Log::debug('Pytesseract available', ['python_cmd' => $pythonCmd]);
                return true;
            }
        }
        
        Log::debug('Pytesseract not available after trying all commands');
        return false;
    }

    /**
     * Detect table structures in OCR text
     */
    private function detectTables(string $text): array
    {
        $tables = [];
        $lines = array_filter(explode("\n", $text), fn($l) => trim($l) !== '');

        $currentTable = [];
        foreach ($lines as $line) {
            $cleanLine = trim($line);
            
            // Detect separators
            if (preg_match('/[\|\t]|  {2,}/', $cleanLine)) {
                $columns = preg_split('/[\|\t]|  {2,}/', $cleanLine, -1, PREG_SPLIT_NO_EMPTY);
                $columns = array_map('trim', $columns);
                
                if (count($columns) > 1) {
                    $currentTable[] = $columns;
                }
            } elseif (!empty($currentTable)) {
                if (count($currentTable) > 1) {
                    $tables[] = [
                        'rows' => $currentTable,
                        'column_count' => count($currentTable[0]),
                    ];
                }
                $currentTable = [];
            }
        }

        if (!empty($currentTable) && count($currentTable) > 1) {
            $tables[] = [
                'rows' => $currentTable,
                'column_count' => count($currentTable[0]),
            ];
        }

        return $tables;
    }

    /**
     * Déterminer le type MIME de manière fiable et robuste
     */
    private function getMimeType(string $filePath): string
    {
        // Méthode 1: finfo_file (le plus fiable)
        if (function_exists('finfo_file')) {
            $finfo = @finfo_open(FILEINFO_MIME_TYPE);
            if ($finfo) {
                $mime = @finfo_file($finfo, $filePath);
                @finfo_close($finfo);
                if ($mime && strpos($mime, 'application/octet-stream') === false) {
                    return $mime;
                }
            }
        }
        
        // Méthode 2: mime_content_type (si disponible, bien que dépréciée)
        if (function_exists('mime_content_type')) {
            $mime = @mime_content_type($filePath);
            if ($mime) {
                return $mime;
            }
        }
        
        // Méthode 3: Extension du fichier (fallback)
        $ext = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
        $mimeTypes = [
            'pdf' => 'application/pdf',
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'gif' => 'image/gif',
            'tiff' => 'image/tiff',
            'tif' => 'image/tiff',
            'bmp' => 'image/bmp',
            'webp' => 'image/webp',
            'jp2' => 'image/jp2',
        ];
        
        return $mimeTypes[$ext] ?? 'application/octet-stream';
    }

    /**     * Clean extracted text
     */
    public function cleanText(string $text): string
    {
        // Remove excessive whitespace
        $text = preg_replace('/\s+/', ' ', $text);
        
        // Remove control characters
        $text = preg_replace('/[\x00-\x1F\x7F]/u', '', $text);
        
        // Fix common OCR errors
        $replacements = [
            'l0' => '10',
            '|0' => '10',
            'O0' => '00',
            '8o' => '80',
        ];
        
        foreach ($replacements as $from => $to) {
            $text = str_ireplace($from, $to, $text);
        }
        
        return trim($text);
    }

    /**
     * Validate uploaded file
     */
    private function validateFile(UploadedFile $file): void
    {
        $maxSize = 50 * 1024 * 1024; // 50MB
        $allowedMimes = [
            'image/jpeg',
            'image/png',
            'image/gif',
            'image/bmp',
            'image/webp',
            'application/pdf',
        ];

        if ($file->getSize() > $maxSize) {
            throw new Exception("File size exceeds 50MB");
        }

        if (!in_array($file->getMimeType(), $allowedMimes)) {
            throw new Exception("Invalid file type. Allowed: JPG, PNG, GIF, BMP, WebP, PDF");
        }

        if (!$file->isValid()) {
            throw new Exception("File upload error: " . $file->getErrorMessage());
        }
    }

    /**
     * Set OCR backend
     */
    public function setBackend(string $backend): self
    {
        $this->backend = $backend;
        return $this;
    }

    /**
     * Set language for OCR
     */
    public function setLanguage(string $language): self
    {
        $this->language = $language;
        return $this;
    }

    /**
     * Get current backend
     */
    public function getBackend(): string
    {
        return $this->backend;
    }

    /**
     * Get metadata
     */
    public function getMetadata(): array
    {
        return $this->metadata;
    }
}
