#!/usr/bin/env pwsh

Write-Host "`n========================================" -ForegroundColor Cyan
Write-Host "  ✅ TEST CONFIGURATION VITE  " -ForegroundColor Cyan
Write-Host "========================================`n" -ForegroundColor Cyan

$projectPath = Split-Path -Parent $MyInvocation.MyCommand.Path
$errors = @()
$warnings = @()

# Test 1: Vérifier app.blade.php
Write-Host "Test 1️⃣  Vérifier que @vite() est présent..." -ForegroundColor Yellow
$appBladeContent = Get-Content "$projectPath\resources\views\layouts\app.blade.php" -Raw
if ($appBladeContent -match '@vite') {
    Write-Host "  ✓ @vite() trouvé dans app.blade.php" -ForegroundColor Green
} else {
    $errors += "❌ @vite() NOT found in app.blade.php"
    Write-Host "  ❌ @vite() NOT found" -ForegroundColor Red
}

# Test 2: Vérifier vite.config.js
Write-Host "`nTest 2️⃣  Vérifier la configuration Vite..." -ForegroundColor Yellow
$viteConfigFile = "$projectPath\vite.config.js"
if (Test-Path $viteConfigFile) {
    $viteConfig = Get-Content $viteConfigFile -Raw
    
    if ($viteConfig -match 'laravel-vite-plugin') {
        Write-Host "  ✓ Laravel Vite Plugin configuré" -ForegroundColor Green
    } else {
        $errors += "❌ Laravel Vite Plugin not configured"
        Write-Host "  ❌ Laravel Vite Plugin not configured" -ForegroundColor Red
    }
    
    if ($viteConfig -match 'tailwindcss') {
        Write-Host "  ✓ Tailwind CSS intégré" -ForegroundColor Green
    } else {
        $warnings += "⚠ Tailwind CSS not found"
        Write-Host "  ⚠ Tailwind CSS not found" -ForegroundColor Yellow
    }
    
    if ($viteConfig -match 'watch.*usePolling') {
        Write-Host "  ✓ File watching configuré avec polling" -ForegroundColor Green
    } else {
        Write-Host "  ⚠ File watching basique" -ForegroundColor Yellow
    }
} else {
    $errors += "❌ vite.config.js not found"
    Write-Host "  ❌ vite.config.js not found!" -ForegroundColor Red
}

# Test 3: Vérifier package.json
Write-Host "`nTest 3️⃣  Vérifier les scripts npm..." -ForegroundColor Yellow
$packageJsonFile = "$projectPath\package.json"
if (Test-Path $packageJsonFile) {
    $packageJson = Get-Content $packageJsonFile -Raw | ConvertFrom-Json
    
    if ($packageJson.scripts.dev) {
        Write-Host "  ✓ Script 'npm run dev' présent" -ForegroundColor Green
        Write-Host "    Commande: $($packageJson.scripts.dev)" -ForegroundColor Gray
    } else {
        $errors += "❌ npm run dev script not found"
        Write-Host "  ❌ npm run dev script not found" -ForegroundColor Red
    }
    
    if ($packageJson.scripts.build) {
        Write-Host "  ✓ Script 'npm run build' présent" -ForegroundColor Green
    } else {
        $errors += "❌ npm run build script not found"
        Write-Host "  ❌ npm run build script not found" -ForegroundColor Red
    }
} else {
    $errors += "❌ package.json not found"
    Write-Host "  ❌ package.json not found!" -ForegroundColor Red
}

# Test 4: Vérifier .env.local
Write-Host "`nTest 4️⃣  Vérifier configuration HMR..." -ForegroundColor Yellow
$envLocalFile = "$projectPath\.env.local"
if (Test-Path $envLocalFile) {
    $envLocalContent = Get-Content $envLocalFile -Raw
    if ($envLocalContent -match 'VITE_HMR') {
        Write-Host "  ✓ HMR configuré dans .env.local" -ForegroundColor Green
    } else {
        $warnings += "⚠ VITE_HMR not in .env.local"
        Write-Host "  ⚠ VITE_HMR not configured" -ForegroundColor Yellow
    }
} else {
    Write-Host "  ⚠ .env.local not found (will be created)" -ForegroundColor Yellow
}

# Test 5: Vérifier resources/css/app.css
Write-Host "`nTest 5️⃣  Vérifier les fichiers CSS..." -ForegroundColor Yellow
$appCssFile = "$projectPath\resources\css\app.css"
if (Test-Path $appCssFile) {
    $cssSize = (Get-Item $appCssFile).Length
    Write-Host "  ✓ resources/css/app.css trouvé ($cssSize bytes)" -ForegroundColor Green
} else {
    $errors += "❌ resources/css/app.css not found"
    Write-Host "  ❌ resources/css/app.css not found" -ForegroundColor Red
}

# Test 6: Vérifier resources/js/app.js
Write-Host "`nTest 6️⃣  Vérifier les fichiers JavaScript..." -ForegroundColor Yellow
$appJsFile = "$projectPath\resources\js\app.js"
if (Test-Path $appJsFile) {
    $jsSize = (Get-Item $appJsFile).Length
    Write-Host "  ✓ resources/js/app.js trouvé ($jsSize bytes)" -ForegroundColor Green
} else {
    $errors += "❌ resources/js/app.js not found"
    Write-Host "  ❌ resources/js/app.js not found" -ForegroundColor Red
}

# Test 7: Vérifier clear-all-caches.php
Write-Host "`nTest 7️⃣  Vérifier les scripts de nettoyage..." -ForegroundColor Yellow
$clearCachesFile = "$projectPath\clear-all-caches.php"
if (Test-Path $clearCachesFile) {
    Write-Host "  ✓ clear-all-caches.php présent" -ForegroundColor Green
} else {
    $warnings += "⚠ clear-all-caches.php not found"
    Write-Host "  ⚠ clear-all-caches.php not found (will be created)" -ForegroundColor Yellow
}

# Test 8: Vérifier Node.js installation
Write-Host "`nTest 8️⃣  Vérifier l'installation Node.js..." -ForegroundColor Yellow
try {
    $nodeVersion = & node --version 2>$null
    if ($LASTEXITCODE -eq 0) {
        Write-Host "  ✓ Node.js installé: $nodeVersion" -ForegroundColor Green
    }
} catch {
    $errors += "❌ Node.js not installed"
    Write-Host "  ❌ Node.js not installed!" -ForegroundColor Red
}

try {
    $npmVersion = & npm --version 2>$null
    if ($LASTEXITCODE -eq 0) {
        Write-Host "  ✓ npm installé: $npmVersion" -ForegroundColor Green
    }
} catch {
    $errors += "❌ npm not installed"
    Write-Host "  ❌ npm not installed!" -ForegroundColor Red
}

# Test 9: Vérifier PHP
Write-Host "`nTest 9️⃣  Vérifier l'installation PHP..." -ForegroundColor Yellow
try {
    $phpVersion = & php --version 2>$null | Select-Object -First 1
    if ($LASTEXITCODE -eq 0) {
        Write-Host "  ✓ PHP disponible: $phpVersion" -ForegroundColor Green
    }
} catch {
    $errors += "❌ PHP not installed"
    Write-Host "  ❌ PHP not installed!" -ForegroundColor Red
}

# Résumé
Write-Host "`n========================================" -ForegroundColor Cyan
Write-Host "  📊 RÉSUMÉ DES TESTS" -ForegroundColor Cyan
Write-Host "========================================`n" -ForegroundColor Cyan

if ($errors.Count -eq 0 -and $warnings.Count -eq 0) {
    Write-Host "✅ TOUT EST OK! Votre configuration Vite est correcte!" -ForegroundColor Green
    Write-Host "`nVous pouvez maintenant exécuter: npm run dev" -ForegroundColor Cyan
} elseif ($errors.Count -eq 0) {
    Write-Host "✅ CONFIGURATION VALIDE avec quelques avertissements" -ForegroundColor Green
    Write-Host "`nAvertissements:" -ForegroundColor Yellow
    $warnings | ForEach-Object { Write-Host "  $_" -ForegroundColor Yellow }
    Write-Host "`nVous pouvez toujours exécuter: npm run dev" -ForegroundColor Cyan
} else {
    Write-Host "❌ ERREURS DÉTECTÉES!" -ForegroundColor Red
    Write-Host "`nErreurs:" -ForegroundColor Red
    $errors | ForEach-Object { Write-Host "  $_" -ForegroundColor Red }
    
    if ($warnings.Count -gt 0) {
        Write-Host "`nAvertissements:" -ForegroundColor Yellow
        $warnings | ForEach-Object { Write-Host "  $_" -ForegroundColor Yellow }
    }
    
    Write-Host "`n⚠️  Veuillez corriger les erreurs avant de continuer" -ForegroundColor Red
}

Write-Host "`n"
