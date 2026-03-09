#!/usr/bin/env php
<?php
/**
 * Artisan Command: php artisan ocr:test
 * Teste la configuration OCR du système
 */

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;

class TestOCRCommand extends Command
{
    protected $signature = 'ocr:test {--backend=auto : ocr.space, tesseract, ou auto}';
    protected $description = 'Test OCR configuration and availability';

    public function handle()
    {
        $this->info('═══════════════════════════════════════════════════════════');
        $this->info('  🔍 TEST SYSTÈME OCR - Millenaire Connect');
        $this->info('═══════════════════════════════════════════════════════════');
        
        // 1. Configuration
        $this->testConfiguration();
        
        // 2. Outils disponibles
        $this->testTools();
        
        // 3. Tests fonctionnels
        $this->testFunctionality();
        
        // 4. Résumé
        $this->testSummary();
    }

    private function testConfiguration()
    {
        $this->info("\n📋 Configuration OCR:");
        
        $backend = Config::get('ocr.backend');
        $this->line("  Backend défaut: <comment>$backend</comment>");
        
        $apiKey = Config::get('ocr.ocr_space.api_key');
        if ($apiKey) {
            $keyDisplay = substr($apiKey, 0, 8) . '***';
            $this->line("  OCR.Space API: <comment>$keyDisplay</comment>");
        }
        
        $tesseractPath = Config::get('ocr.tesseract.path');
        $this->line("  Chemin Tesseract: <comment>$tesseractPath</comment>");
        
        $language = Config::get('ocr.tesseract.language');
        $this->line("  Langue: <comment>$language</comment>");
    }

    private function testTools()
    {
        $this->info("\n🔧 Outils disponibles:");
        
        $hasTesseract = $this->checkTesseract();
        $status = $hasTesseract ? '<fg=green>✅</>' : '<fg=red>❌</>';
        $this->line("  $status Tesseract: " . ($hasTesseract ? 'DISPONIBLE' : 'NON DISPONIBLE'));
        
        $hasPython = $this->checkPython();
        $status = $hasPython ? '<fg=green>✅</>' : '<fg=yellow>⚠️ </>';
        $this->line("  $status Python: " . ($hasPython ? 'DISPONIBLE' : 'NON DISPONIBLE'));
        
        if ($hasPython) {
            if ($this->checkPythonPackage('pytesseract')) {
                $this->line("    <fg=green>✅</> pytesseract");
            } else {
                $this->line("    <fg=yellow>⚠️ </> pytesseract (pip install pytesseract)");
            }
            
            if ($this->checkPythonPackage('PIL')) {
                $this->line("    <fg=green>✅</> Pillow");
            } else {
                $this->line("    <fg=yellow>⚠️ </> Pillow (pip install pillow)");
            }
        }
        
        $hasInternet = $this->checkInternet();
        $status = $hasInternet ? '<fg=green>✅</>' : '<fg=red>❌</>';
        $this->line("  $status Connexion Internet: " . ($hasInternet ? 'OK' : 'PERDUE'));
    }

    private function testFunctionality()
    {
        $this->info("\n🚀 Tests fonctionnels:");
        
        // Test OCR.Space
        if (Config::get('ocr.backend') === 'ocr.space' || Config::get('ocr.backend') === 'auto') {
            $this->testOCRSpace();
        }
        
        // Test Tesseract
        if ($this->checkTesseract()) {
            $this->testTesseractCli();
        }
    }

    private function testOCRSpace()
    {
        $this->line("\n  🌐 OCR.Space (API Cloud):");
        
        if (!$this->checkInternet()) {
            $this->line("    <fg=yellow>⚠️ </> Pas de connexion Internet");
            return;
        }
        
        try {
            // Petite image PNG 1x1 pixel blanc
            $response = Http::timeout(10)->post('https://api.ocr.space/parse/image', [
                'base64Image' => 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP4//8/AwAI/AL+O965/gAAAABJRU5ErkJggg==',
                'language' => 'fre',
            ]);
            
            if ($response->successful()) {
                $this->line("    <fg=green>✅</> API fonctionnelle");
                $this->line("    <fg=green>✅</> Backend OCR.Space OPÉRATIONNEL");
                return;
            }
            
            if ($response->status() === 403) {
                $this->line("    <fg=yellow>⚠️ </> Quota dépassé (limit: 25 requêtes/jour)");
                return;
            }
            
            $this->line("    <fg=red>❌</> Erreur API: " . $response->status());
        } catch (\Exception $e) {
            $this->line("    <fg=red>❌</> " . $e->getMessage());
        }
    }

    private function testTesseractCli()
    {
        $this->line("\n  📄 Tesseract (CLI Local):");
        
        $path = Config::get('ocr.tesseract.path', 'tesseract');
        
        try {
            if (PHP_OS_FAMILY === 'Windows') {
                exec(sprintf('"%s" --version 2>nul', $path), $output, $code);
            } else {
                exec(sprintf("'%s' --version 2>/dev/null", $path), $output, $code);
            }
            
            if ($code === 0 && !empty($output)) {
                $version = trim($output[0]);
                $this->line("    <fg=green>✅</> Version: $version");
                $this->line("    <fg=green>✅</> Tesseract OPÉRATIONNEL");
            } else {
                $this->line("    <fg=red>❌</> Erreur lors du test");
            }
        } catch (\Exception $e) {
            $this->line("    <fg=red>❌</> " . $e->getMessage());
        }
    }

    private function testSummary()
    {
        $this->info("\n═══════════════════════════════════════════════════════════");
        
        $backend = Config::get('ocr.backend');
        $hasInternet = $this->checkInternet();
        $hasTesseract = $this->checkTesseract();
        
        $isOCRSpaceFunctional = ($backend === 'ocr.space' || $backend === 'auto') && $hasInternet;
        $isTesseractFunctional = $backend === 'tesseract' && $hasTesseract;
        
        if ($isOCRSpaceFunctional || $isTesseractFunctional) {
            $this->line("<fg=green>✅ SYSTÈME OCR FONCTIONNEL</>");
            $this->newLine();
            $this->line("<fg=green>MODE ACTUEL:</> $backend");
            $this->newLine();
            $this->line("<fg=green>VOUS POUVEZ UTILISER LE SYSTÈME OCR:</>");
            $this->line("  1. Accéder à: <comment>http://localhost:8000/teacher/bulletin/ocr-wizard</comment>");
            $this->line("  2. Uploader une image ou PDF de bulletin");
            $this->line("  3. Vérifier et corriger les données extraites");
            $this->newLine();
            return 0;
        } else {
            $this->error("<fg=red>⚠️  SYSTÈME OCR NON FONCTIONNEL</>");
            $this->newLine();
            $this->line("<fg=yellow>SOLUTIONS:</>"); 
            $this->line("  A) Utiliser OCR.Space (recommandé, aucune installation):");
            $this->line("     • Mettre dans .env: <comment>OCR_BACKEND=ocr.space</comment>");
            $this->line("     • Vérifier votre connexion Internet");
            $this->line("     • Limit gratuit: 25 requêtes/jour");
            $this->newLine();
            $this->line("  B) Installer Tesseract (mode offline):");
            $this->line("     • Windows: https://github.com/UB-Mannheim/tesseract/wiki");
            $this->line("     • Linux: apt-get install tesseract-ocr");
            $this->line("     • Mac: brew install tesseract");
            $this->line("     • Python (pip install pytesseract pillow)");
            $this->line("     • Mettre dans .env: <comment>OCR_BACKEND=tesseract</comment>");
            return 1;
        }
    }

    // ═════════════════════════════════════════════════════════════════════

    private function checkTesseract(): bool
    {
        $path = Config::get('ocr.tesseract.path', 'tesseract');
        
        if (file_exists($path)) {
            return true;
        }
        
        if (PHP_OS_FAMILY === 'Windows') {
            exec('where.exe tesseract 2>nul', $output, $code);
        } else {
            exec('which tesseract 2>/dev/null', $output, $code);
        }
        
        return $code === 0;
    }

    private function checkPython(): bool
    {
        foreach (['python3', 'python', 'py'] as $cmd) {
            if (PHP_OS_FAMILY === 'Windows') {
                exec(sprintf('%s --version 2>nul', $cmd), $output, $code);
            } else {
                exec(sprintf('%s --version 2>/dev/null', $cmd), $output, $code);
            }
            
            if ($code === 0) {
                return true;
            }
        }
        
        return false;
    }

    private function checkPythonPackage(string $package): bool
    {
        foreach (['python3', 'python', 'py'] as $cmd) {
            if (PHP_OS_FAMILY === 'Windows') {
                exec(sprintf('%s -c "import %s" 2>nul', $cmd, $package), $output, $code);
            } else {
                exec(sprintf('%s -c "import %s" 2>/dev/null', $cmd, $package), $output, $code);
            }
            
            if ($code === 0) {
                return true;
            }
        }
        
        return false;
    }

    private function checkInternet(): bool
    {
        try {
            $response = Http::timeout(5)->get('https://api.ocr.space');
            return $response->successful() || $response->status() !== 0;
        } catch (\Exception $e) {
            return false;
        }
    }
}
