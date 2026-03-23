# queue-stop.ps1
# Arrête tous les workers queue (PowerShell - Windows)

Write-Host "════════════════════════════════════════════════════════" -ForegroundColor Cyan
Write-Host "🛑 ARRÊT DES QUEUE WORKERS" -ForegroundColor Yellow
Write-Host "════════════════════════════════════════════════════════" -ForegroundColor Cyan
Write-Host ""

$pidFiles = @(
    "storage\.queue-pids-ai",
    "storage\.queue-pids-pdf",
    "storage\.queue-pids-notif",
    "storage\.queue-pids-calc",
    "storage\.queue-pids-default"
)

$killed = 0
$notFound = 0

foreach ($pidFile in $pidFiles) {
    if (Test-Path $pidFile) {
        $pid = Get-Content $pidFile
        try {
            $process = Get-Process -Id $pid -ErrorAction Stop
            Write-Host "🛑 Arrêt du worker PID $pid..." -ForegroundColor Cyan
            Stop-Process -Id $pid -Force
            Remove-Item $pidFile -Force
            $killed++
            Write-Host "   ✅ Worker arrêté" -ForegroundColor Green
        } catch {
            Write-Host "⚠️  Worker PID $pid n'existe plus" -ForegroundColor Yellow
            Remove-Item $pidFile -Force
            $notFound++
        }
    }
}

Write-Host ""
Write-Host "════════════════════════════════════════════════════════" -ForegroundColor Cyan
Write-Host "✅ ARRÊT TERMINÉ" -ForegroundColor Green
Write-Host "════════════════════════════════════════════════════════" -ForegroundColor Cyan
Write-Host ""
Write-Host "📊 Résumé:" -ForegroundColor Yellow
Write-Host "  • Workers arrêtés: $killed" -ForegroundColor White
Write-Host "  • Workers déjà inactifs: $notFound" -ForegroundColor White
Write-Host ""

# Vérifier qu'il n'y a plus de processus artisan "queue:work"
Write-Host "🔍 Vérification des processus queue restants..." -ForegroundColor Yellow
$queueProcesses = Get-Process -Name "php" -ErrorAction SilentlyContinue | Where-Object {
    $_.CommandLine -match "queue:work"
}

if ($queueProcesses) {
    Write-Host "⚠️  Certains processus queue sont toujours actifs:" -ForegroundColor Yellow
    $queueProcesses | ForEach-Object {
        Write-Host "   PID: $($_.Id) - Command: $($_.CommandLine)" -ForegroundColor Gray
    }
    Write-Host ""
    Write-Host "💡 Pour les forcer à s'arrêter:" -ForegroundColor Yellow
    Write-Host "   Get-Process -Name php | Stop-Process -Force" -ForegroundColor Gray
} else {
    Write-Host "✅ Aucun processus queue actif" -ForegroundColor Green
}
Write-Host ""
