<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

/**
 * SimpleOCRService - Extraction basique de texte sans dépendance API
 * 
 * Supporte:
 * - Extraction de texte depuis PDF (via pdftotext si disponible)
 * - Extraction de texte depuis images (via Tesseract/pytesseract)
 * - Fallback: Extraction basée sur le nom du fichier pour testing
 */
class SimpleOCRService
{
    /**
     * Extraire le texte d'un fichier (PDF ou image)
     */
    public function extract(UploadedFile $file): array
    {
        $ext = strtolower($file->getClientOriginalExtension());
        $path = $file->getRealPath();

        Log::info('SimpleOCR: Extraction started', [
            'file' => $file->getClientOriginalName(),
            'ext' => $ext,
            'size' => $file->getSize(),
        ]);

        try {
            if ($ext === 'pdf') {
                return $this->extractFromPDF($path);
            } elseif (in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'tiff'])) {
                return $this->extractFromImage($path);
            } else {
                throw new \Exception("Format de fichier non supporté: $ext");
            }
        } catch (\Exception $e) {
            Log::error('SimpleOCR error', ['error' => $e->getMessage()]);
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'text' => '',
            ];
        }
    }

    /**
     * Extraire texte d'un PDF
     */
    private function extractFromPDF(string $filePath): array
    {
        // Try pdftotext first
        if ($this->isPdftotextAvailable()) {
            return $this->extractPdfViaCmd($filePath);
        }

        // Try Python pdfplumber
        if ($this->isPdfplumberAvailable()) {
            return $this->extractPdfViaPython($filePath);
        }

        throw new \Exception(
            'Aucun outil PDF disponible. Installer: ' .
            'pdftotext (Poppler) ou "pip install pdfplumber"'
        );
    }

    /**
     * Extraire texte d'une image
     */
    private function extractFromImage(string $filePath): array
    {
        // Try Tesseract CLI
        if ($this->isTesseractAvailable()) {
            return $this->extractImageViaCmd($filePath);
        }

        // Try Python pytesseract
        if ($this->isPytesseractAvailable()) {
            return $this->extractImageViaPython($filePath);
        }

        throw new \Exception(
            'Aucun OCR disponible. Installer Tesseract ou "pip install pytesseract"'
        );
    }

    /**
     * Extraction PDF via pdftotext
     */
    private function extractPdfViaCmd(string $filePath): array
    {
        $outputFile = sys_get_temp_dir() . '/pdf_' . uniqid() . '.txt';
        $command = sprintf('pdftotext "%s" "%s" 2>&1', 
            escapeshellarg($filePath), 
            escapeshellarg($outputFile)
        );

        exec($command, $output, $returnCode);

        if ($returnCode === 0 && file_exists($outputFile)) {
            $text = file_get_contents($outputFile);
            @unlink($outputFile);

            return [
                'success' => true,
                'text' => trim($text),
                'raw_text' => trim($text),
                'confidence' => 90,
                'method' => 'pdftotext',
                'metadata' => ['source' => 'pdftotext'],
            ];
        }

        throw new \Exception("pdftotext failed with code $returnCode");
    }

    /**
     * Extraction PDF via Python pdfplumber
     */
    private function extractPdfViaPython(string $filePath): array
    {
        $pythonScript = <<<'PYTHON'
import pdfplumber
import json
import sys

try:
    with pdfplumber.open(sys.argv[1]) as pdf:
        text = ""
        for page in pdf.pages:
            text += page.extract_text() + "\n"
    
    print(json.dumps({
        'success': True,
        'text': text.strip(),
        'confidence': 90,
        'method': 'pdfplumber'
    }))
except Exception as e:
    print(json.dumps({
        'success': False,
        'error': str(e),
        'text': ''
    }))
PYTHON;

        return $this->executePythonScript($pythonScript, $filePath, 'pdfplumber');
    }

    /**
     * Extraction image via Tesseract CLI
     */
    private function extractImageViaCmd(string $filePath): array
    {
        $outputFile = sys_get_temp_dir() . '/img_' . uniqid();
        
        // Sur Windows, gérer les chemins correctement
        if (PHP_OS_FAMILY === 'Windows') {
            $filePath = escapeshellarg($filePath);
            $outputFile = escapeshellarg($outputFile);
        } else {
            $filePath = escapeshellarg($filePath);
            $outputFile = escapeshellarg($outputFile);
        }
        
        $command = sprintf(
            'tesseract %s %s -l fra+eng --psm 6 --oem 3 2>&1',
            $filePath,
            $outputFile
        );

        exec($command, $output, $returnCode);

        if ($returnCode === 0 && file_exists($outputFile . '.txt')) {
            $text = file_get_contents($outputFile . '.txt');
            @unlink($outputFile . '.txt');

            return [
                'success' => true,
                'text' => trim($text),
                'raw_text' => trim($text),
                'confidence' => 85,
                'method' => 'tesseract-cli',
                'metadata' => ['source' => 'tesseract-cli'],
            ];
        }

        throw new \Exception("Tesseract failed with code $returnCode");
    }

    /**
     * Extraction image via Python pytesseract
     */
    private function extractImageViaPython(string $filePath): array
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
        'text': text.strip(),
        'confidence': 85,
        'method': 'pytesseract'
    }))
except Exception as e:
    print(json.dumps({
        'success': False,
        'error': str(e),
        'text': ''
    }))
PYTHON;

        return $this->executePythonScript($pythonScript, $filePath, 'pytesseract');
    }

    /**
     * Exécuter un script Python
     */
    private function executePythonScript(string $script, string $filePath, string $tool): array
    {
        $scriptFile = sys_get_temp_dir() . '/ocr_' . uniqid() . '.py';
        file_put_contents($scriptFile, $script);

        $command = sprintf('python "%s" "%s" 2>&1', $scriptFile, escapeshellarg($filePath));
        exec($command, $output, $returnCode);
        @unlink($scriptFile);

        $jsonOutput = implode("\n", $output);
        $result = json_decode($jsonOutput, true);

        if ($result['success'] ?? false) {
            return array_merge($result, [
                'raw_text' => $result['text'],
                'metadata' => ['source' => $tool],
            ]);
        }

        throw new \Exception($result['error'] ?? "$tool processing failed");
    }

    /**
     * Vérifier que pdftotext est disponible
     */
    private function isPdftotextAvailable(): bool
    {
        $cmd = PHP_OS_FAMILY === 'Windows' ? 'where pdftotext' : 'which pdftotext';
        exec($cmd . ' 2>&1', $output, $code);
        return $code === 0;
    }

    /**
     * Vérifier que Tesseract CLI est disponible
     */
    private function isTesseractAvailable(): bool
    {
        $cmd = PHP_OS_FAMILY === 'Windows' ? 'where tesseract' : 'which tesseract';
        exec($cmd . ' 2>&1', $output, $code);
        return $code === 0;
    }

    /**
     * Vérifier que pdfplumber Python est disponible
     */
    private function isPdfplumberAvailable(): bool
    {
        $pythonCommands = ['python3', 'python', 'py'];
        
        foreach ($pythonCommands as $pythonCmd) {
            if (PHP_OS_FAMILY === 'Windows') {
                exec(sprintf('%s -c "import pdfplumber" 2>nul', $pythonCmd), $output, $code);
            } else {
                exec(sprintf('%s -c "import pdfplumber" 2>/dev/null', $pythonCmd), $output, $code);
            }
            
            if ($code === 0) {
                return true;
            }
        }
        
        return false;
    }

    /**
     * Vérifier que pytesseract Python est disponible
     */
    private function isPytesseractAvailable(): bool
    {
        $pythonCommands = ['python3', 'python', 'py'];
        
        foreach ($pythonCommands as $pythonCmd) {
            if (PHP_OS_FAMILY === 'Windows') {
                exec(sprintf('%s -c "import pytesseract; from PIL import Image" 2>nul', $pythonCmd), $output, $code);
            } else {
                exec(sprintf('%s -c "import pytesseract; from PIL import Image" 2>/dev/null', $pythonCmd), $output, $code);
            }
            
            if ($code === 0) {
                return true;
            }
        }
        
        return false;
    }
}
