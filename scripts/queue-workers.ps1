# queue-workers.ps1
# Lance tous les workers queue pour Smart Bulletin System (PowerShell - Windows)

param(
    [string]$AppPath = ".",
    [string]$Environment = "local"
)

Set-Location $AppPath

Write-Host "════════════════════════════════════════════════════════" -ForegroundColor Cyan
Write-Host "🚀 LANCEMENT DES QUEUE WORKERS — Millénaire Connect" -ForegroundColor Green
Write-Host "════════════════════════════════════════════════════════" -ForegroundColor Cyan
Write-Host ""
Write-Host "Environnement: $Environment" -ForegroundColor Yellow
Write-Host "Chemin: $(Get-Location)" -ForegroundColor Yellow
Write-Host ""

# Vérifier que Redis est accessible (optionnel en Windows si pas installé)
Write-Host "🔍 Vérification de Redis..." -ForegroundColor Yellow
try {
    $redisTest = redis-cli ping 2>$null
    if ($redisTest -eq "PONG") {
        Write-Host "✅ Redis accessible" -ForegroundColor Green
    } else {
        Write-Host "⚠️  Redis peut ne pas être disponible" -ForegroundColor Yellow
        Write-Host "   Pour Windows, utiliser Docker: docker run -d -p 6379:6379 redis:latest" -ForegroundColor Gray
    }
} catch {
    Write-Host "⚠️  Redis CLI non trouvé (optionnel)" -ForegroundColor Yellow
}
Write-Host ""

# Vérifier les dépendances Laravel
if (-not (Test-Path "artisan")) {
    Write-Host "❌ ERREUR: Laravel artisan non trouvé" -ForegroundColor Red
    Write-Host "   Êtes-vous dans le répertoire racine du projet?" -ForegroundColor Red
    exit 1
}
Write-Host "✅ Laravel trouvé" -ForegroundColor Green
Write-Host ""

Write-Host "════════════════════════════════════════════════════════" -ForegroundColor Cyan
Write-Host "📍 LANCEMENT DES WORKERS EN ARRIÈRE-PLAN" -ForegroundColor Green
Write-Host "════════════════════════════════════════════════════════" -ForegroundColor Cyan
Write-Host ""

# Créer le dossier storage/logs s'il n'existe pas
if (-not (Test-Path "storage\logs")) {
    New-Item -ItemType Directory -Path "storage\logs" | Out-Null
}

# Worker 1: IA Processing
Write-Host "1️⃣  Lancement: AI Processing Worker" -ForegroundColor Cyan
Write-Host "   Queue: ai-processing | Timeout: 120s | Tries: 2" -ForegroundColor Gray
$aiProcess = Start-Process -FilePath "php" `
    -ArgumentList "artisan", "queue:work", "redis", `
                  "--queue=ai-processing", `
                  "--timeout=120", `
                  "--tries=2", `
                  "--sleep=3" `
    -RedirectStandardOutput "storage\logs\queue-ai-processing.log" `
    -RedirectStandardError "storage\logs\queue-ai-processing-error.log" `
    -WindowStyle Hidden `
    -PassThru
Write-Host "   📌 PID: $($aiProcess.Id)" -ForegroundColor Green
$aiProcess.Id | Out-File "storage\.queue-pids-ai" -Force
Write-Host ""

# Worker 2: PDF Exports
Write-Host "2️⃣  Lancement: PDF Export Worker" -ForegroundColor Cyan
Write-Host "   Queue: pdf-exports | Timeout: 600s | Tries: 1" -ForegroundColor Gray
$pdfProcess = Start-Process -FilePath "php" `
    -ArgumentList "artisan", "queue:work", "redis", `
                  "--queue=pdf-exports", `
                  "--timeout=600", `
                  "--tries=1", `
                  "--sleep=3" `
    -RedirectStandardOutput "storage\logs\queue-pdf-exports.log" `
    -RedirectStandardError "storage\logs\queue-pdf-exports-error.log" `
    -WindowStyle Hidden `
    -PassThru
Write-Host "   📌 PID: $($pdfProcess.Id)" -ForegroundColor Green
$pdfProcess.Id | Out-File "storage\.queue-pids-pdf" -Force
Write-Host ""

# Worker 3: Notifications
Write-Host "3️⃣  Lancement: Notifications Worker" -ForegroundColor Cyan
Write-Host "   Queue: notifications | Timeout: 30s | Tries: 3" -ForegroundColor Gray
$notifProcess = Start-Process -FilePath "php" `
    -ArgumentList "artisan", "queue:work", "redis", `
                  "--queue=notifications", `
                  "--timeout=30", `
                  "--tries=3", `
                  "--sleep=1" `
    -RedirectStandardOutput "storage\logs\queue-notifications.log" `
    -RedirectStandardError "storage\logs\queue-notifications-error.log" `
    -WindowStyle Hidden `
    -PassThru
Write-Host "   📌 PID: $($notifProcess.Id)" -ForegroundColor Green
$notifProcess.Id | Out-File "storage\.queue-pids-notif" -Force
Write-Host ""

# Worker 4: Calculations
Write-Host "4️⃣  Lancement: Calculations Worker" -ForegroundColor Cyan
Write-Host "   Queue: calculations | Timeout: 60s | Tries: 3" -ForegroundColor Gray
$calcProcess = Start-Process -FilePath "php" `
    -ArgumentList "artisan", "queue:work", "redis", `
                  "--queue=calculations", `
                  "--timeout=60", `
                  "--tries=3", `
                  "--sleep=2" `
    -RedirectStandardOutput "storage\logs\queue-calculations.log" `
    -RedirectStandardError "storage\logs\queue-calculations-error.log" `
    -WindowStyle Hidden `
    -PassThru
Write-Host "   📌 PID: $($calcProcess.Id)" -ForegroundColor Green
$calcProcess.Id | Out-File "storage\.queue-pids-calc" -Force
Write-Host ""

Write-Host "════════════════════════════════════════════════════════" -ForegroundColor Cyan
Write-Host "✅ TOUS LES WORKERS LANCÉS" -ForegroundColor Green
Write-Host "════════════════════════════════════════════════════════" -ForegroundColor Cyan
Write-Host ""
Write-Host "📊 Résumé:" -ForegroundColor Yellow
Write-Host "  • AI Processing Worker ............ PID $($aiProcess.Id)" -ForegroundColor White
Write-Host "  • PDF Export Worker .............. PID $($pdfProcess.Id)" -ForegroundColor White
Write-Host "  • Notifications Worker ........... PID $($notifProcess.Id)" -ForegroundColor White
Write-Host "  • Calculations Worker ............ PID $($calcProcess.Id)" -ForegroundColor White
Write-Host ""
Write-Host "📋 Logs:" -ForegroundColor Yellow
Write-Host "  storage\logs\queue-ai-processing.log" -ForegroundColor Gray
Write-Host "  storage\logs\queue-pdf-exports.log" -ForegroundColor Gray
Write-Host "  storage\logs\queue-notifications.log" -ForegroundColor Gray
Write-Host "  storage\logs\queue-calculations.log" -ForegroundColor Gray
Write-Host ""
Write-Host "🛑 Pour arrêter les workers:" -ForegroundColor Yellow
Write-Host "   powershell scripts\queue-stop.ps1" -ForegroundColor Gray
Write-Host ""
Write-Host "📊 Pour vérifier les jobs échoués:" -ForegroundColor Yellow
Write-Host "   php artisan queue:failed" -ForegroundColor Gray
Write-Host "   php artisan queue:retry all" -ForegroundColor Gray
Write-Host ""
