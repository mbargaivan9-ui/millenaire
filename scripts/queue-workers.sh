#!/bin/bash
# queue-workers.sh
# Lance tous les workers queue pour Smart Bulletin System

set -e

APP_PATH="${1:-.}"
ENVIRONMENT="${2:-local}"

cd "$APP_PATH"

echo "════════════════════════════════════════════════════════"
echo "🚀 LANCEMENT DES QUEUE WORKERS — Millénaire Connect"
echo "════════════════════════════════════════════════════════"
echo ""
echo "Environnement: $ENVIRONMENT"
echo "Chemin: $(pwd)"
echo ""

# Vérifier que Redis est accessible
echo "🔍 Vérification Redis..."
if ! redis-cli ping &>/dev/null; then
    echo "❌ ERREUR: Redis n'est pas accessible"
    echo "   Démarrer Redis avec: redis-server"
    exit 1
fi
echo "✅ Redis accessible"
echo ""

# Vérifier les dépendances Laravel
if [ ! -f "artisan" ]; then
    echo "❌ ERREUR: Laravel artisan non trouvé"
    echo "   Êtes-vous dans le répertoire racine du projet?"
    exit 1
fi
echo "✅ Laravel trouvé"
echo ""

# ========== DÉMARRAGE DES WORKERS ==========

echo "════════════════════════════════════════════════════════"
echo "📍 LANCEMENT DES WORKERS"
echo "════════════════════════════════════════════════════════"
echo ""

# Worker 1: IA Processing (Analyse bulletins)
echo "1️⃣  Lancement: AI Processing Worker"
echo "   Queue: ai-processing | Timeout: 120s | Tries: 2"
nohup php artisan queue:work redis \
    --queue=ai-processing \
    --timeout=120 \
    --tries=2 \
    --sleep=3 \
    > storage/logs/queue-ai-processing.log 2>&1 &
AI_PID=$!
echo "   📌 PID: $AI_PID"
echo ""

# Worker 2: PDF Exports
echo "2️⃣  Lancement: PDF Export Worker"
echo "   Queue: pdf-exports | Timeout: 600s | Tries: 1"
nohup php artisan queue:work redis \
    --queue=pdf-exports \
    --timeout=600 \
    --tries=1 \
    --sleep=3 \
    > storage/logs/queue-pdf-exports.log 2>&1 &
PDF_PID=$!
echo "   📌 PID: $PDF_PID"
echo ""

# Worker 3: Notifications
echo "3️⃣  Lancement: Notifications Worker"
echo "   Queue: notifications | Timeout: 30s | Tries: 3"
nohup php artisan queue:work redis \
    --queue=notifications \
    --timeout=30 \
    --tries=3 \
    --sleep=1 \
    > storage/logs/queue-notifications.log 2>&1 &
NOTIF_PID=$!
echo "   📌 PID: $NOTIF_PID"
echo ""

# Worker 4: Calculations
echo "4️⃣  Lancement: Calculations Worker"
echo "   Queue: calculations | Timeout: 60s | Tries: 3"
nohup php artisan queue:work redis \
    --queue=calculations \
    --timeout=60 \
    --tries=3 \
    --sleep=2 \
    > storage/logs/queue-calculations.log 2>&1 &
CALC_PID=$!
echo "   📌 PID: $CALC_PID"
echo ""

# Worker 5: Default (fallback)
echo "5️⃣  Lancement: Default Worker (fallback)"
echo "   Queue: default | Timeout: 60s | Tries: 3"
nohup php artisan queue:work redis \
    --timeout=60 \
    --tries=3 \
    --sleep=3 \
    > storage/logs/queue-default.log 2>&1 &
DEFAULT_PID=$!
echo "   📌 PID: $DEFAULT_PID"
echo ""

# Sauvegarder les PIDs
echo "$AI_PID" > storage/.queue-pids-ai
echo "$PDF_PID" > storage/.queue-pids-pdf
echo "$NOTIF_PID" > storage/.queue-pids-notif
echo "$CALC_PID" > storage/.queue-pids-calc
echo "$DEFAULT_PID" > storage/.queue-pids-default

echo "════════════════════════════════════════════════════════"
echo "✅ TOUS LES WORKERS LANCÉS"
echo "════════════════════════════════════════════════════════"
echo ""
echo "📊 Résumé:"
echo "  • AI Processing Worker ............ PID $AI_PID"
echo "  • PDF Export Worker .............. PID $PDF_PID"
echo "  • Notifications Worker ........... PID $NOTIF_PID"
echo "  • Calculations Worker ............ PID $CALC_PID"
echo "  • Default Worker ................. PID $DEFAULT_PID"
echo ""
echo "📋 Logs:"
echo "  storage/logs/queue-ai-processing.log"
echo "  storage/logs/queue-pdf-exports.log"
echo "  storage/logs/queue-notifications.log"
echo "  storage/logs/queue-calculations.log"
echo "  storage/logs/queue-default.log"
echo ""
echo "🛑 Pour arrêter les workers:"
echo "   bash scripts/queue-stop.sh"
echo ""
echo "📊 Pour vérifier les jobs:"
echo "   php artisan queue:failed"
echo "   php artisan queue:retry all"
echo ""
