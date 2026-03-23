#!/bin/bash
# queue-stop.sh
# Arrête tous les workers queue

echo "════════════════════════════════════════════════════════"
echo "🛑 ARRÊT DES QUEUE WORKERS"
echo "════════════════════════════════════════════════════════"
echo ""

STORAGE_DIR="${1:-storage}"

# Liste des fichiers de PID
PID_FILES=(
    "$STORAGE_DIR/.queue-pids-ai"
    "$STORAGE_DIR/.queue-pids-pdf"
    "$STORAGE_DIR/.queue-pids-notif"
    "$STORAGE_DIR/.queue-pids-calc"
    "$STORAGE_DIR/.queue-pids-default"
)

KILLED=0
NOTFOUND=0

for pid_file in "${PID_FILES[@]}"; do
    if [ -f "$pid_file" ]; then
        PID=$(cat "$pid_file")
        if kill -0 "$PID" 2>/dev/null; then
            echo "🛑 Arrêt du worker PID $PID..."
            kill "$PID"
            rm "$pid_file"
            KILLED=$((KILLED + 1))
            echo "   ✅ Worker arrêté"
        else
            echo "⚠️  Worker PID $PID n'existe plus"
            rm "$pid_file"
            NOTFOUND=$((NOTFOUND + 1))
        fi
    fi
done

echo ""
echo "════════════════════════════════════════════════════════"
echo "✅ ARRÊT TERMINÉ"
echo "════════════════════════════════════════════════════════"
echo ""
echo "📊 Résumé:"
echo "  • Workers arrêtés: $KILLED"
echo "  • Workers déjà inactifs: $NOTFOUND"
echo ""

# Vérifier qu'il n'y a plus de processus artisan
echo "🔍 Vérification des processus queue restants..."
if pgrep -f "queue:work" > /dev/null; then
    echo "⚠️  Certains processus queue sont toujours actifs:"
    pgrep -f "queue:work"
    echo ""
    echo "💡 Pour les forcer à s'arrêter:"
    echo "   pkill -f 'queue:work'"
else
    echo "✅ Aucun processus queue actif"
fi
echo ""
