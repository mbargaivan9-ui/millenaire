#!/usr/bin/env pwsh

Write-Host "`n========================================" -ForegroundColor Cyan
Write-Host "  🚀 DÉMARRAGE SERVEUR DÉVELOPPEMENT  " -ForegroundColor Cyan
Write-Host "========================================`n" -ForegroundColor Cyan

$projectPath = Split-Path -Parent $MyInvocation.MyCommand.Path

Set-Location $projectPath

# 1. Nettoyer les caches
Write-Host "Step 1️⃣  Nettoyage des caches..." -ForegroundColor Yellow
php clear-all-caches.php
if ($LASTEXITCODE -ne 0) {
    Write-Host "Erreur lors du nettoyage des caches!" -ForegroundColor Red
}

# 2. Vérifier node_modules
Write-Host "`nStep 2️⃣  Vérification des dépendances Node..." -ForegroundColor Yellow
if (!(Test-Path "node_modules")) {
    Write-Host "node_modules manquant. Installation en cours..." -ForegroundColor Cyan
    npm install
    if ($LASTEXITCODE -ne 0) {
        Write-Host "Erreur lors de l'installation npm!" -ForegroundColor Red
        exit 1
    }
} else {
    Write-Host "✓ node_modules trouvé" -ForegroundColor Green
}

# 3. Déterminer la commande npm run dev
Write-Host "`nStep 3️⃣  Démarrage du serveur Vite..." -ForegroundColor Yellow
Write-Host "    Port: 5173" -ForegroundColor Cyan
Write-Host "    Hot Reload: ✓ Activé" -ForegroundColor Cyan
Write-Host "`n    Appuyez sur Ctrl+C pour arrêter`n" -ForegroundColor Gray

npm run dev

# Fin
Write-Host "`n❌ Serveur arrêté" -ForegroundColor Yellow
