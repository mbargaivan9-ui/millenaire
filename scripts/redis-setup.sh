#!/bin/bash
# redis-setup.sh
# Installation et configuration de Redis pour Millénaire Connect

echo "================================"
echo "🔧 SETUP REDIS pour Millénaire"
echo "================================"
echo ""

OS_TYPE=$(uname)

# ========== LINUX / UBUNTU ==========
if [[ "$OS_TYPE" == "Linux" ]]; then
    echo "📦 Système détecté: Linux"
    echo ""
    
    # Vérifier si Redis est déjà installé
    if command -v redis-server &> /dev/null; then
        echo "✅ Redis est déjà installé"
        redis-server --version
    else
        echo "📥 Installation de Redis..."
        sudo apt-get update
        sudo apt-get install -y redis-server
        echo "✅ Redis installé"
    fi
    
    echo ""
    echo "🚀 Démarrage de Redis..."
    sudo systemctl start redis-server
    sudo systemctl enable redis-server
    echo "✅ Redis démarré et activé au démarrage"
    
    # Vérifier la connexion
    echo ""
    echo "🔍 Test de connexion..."
    if redis-cli ping | grep -q "PONG"; then
        echo "✅ Redis est actif et fonctionnel"
        redis-cli info server | head -5
    else
        echo "❌ Erreur: Redis n'est pas accessible"
        exit 1
    fi

# ========== macOS / Darwin ==========
elif [[ "$OS_TYPE" == "Darwin" ]]; then
    echo "📦 Système détecté: macOS"
    echo ""
    
    if command -v redis-server &> /dev/null; then
        echo "✅ Redis est déjà installé"
        redis-server --version
    else
        echo "📥 Installation de Redis via Homebrew..."
        if ! command -v brew &> /dev/null; then
            echo "❌ Homebrew n'est pas installé. Installer Homebrew d'abord:"
            echo "   /bin/bash -c \"\$(curl -fsSL https://raw.githubusercontent.com/Homebrew/install/HEAD/install.sh)\""
            exit 1
        fi
        brew install redis
        echo "✅ Redis installé"
    fi
    
    echo ""
    echo "🚀 Démarrage de Redis..."
    brew services start redis
    echo "✅ Redis démarré"
    
    # Vérifier la connexion
    echo ""
    echo "🔍 Test de connexion..."
    if redis-cli ping | grep -q "PONG"; then
        echo "✅ Redis est actif et fonctionnel"
        redis-cli info server | head -5
    else
        echo "❌ Erreur: Redis n'est pas accessible"
        exit 1
    fi

# ========== WINDOWS ==========
elif [[ "$OS_TYPE" == "MINGW"* ]] || [[ "$OS_TYPE" == "MSYS"* ]]; then
    echo "📦 Système détecté: Windows"
    echo ""
    echo "⚠️  OPTIONS pour Windows:"
    echo ""
    echo "1️⃣  WSL (Windows Subsystem for Linux) - RECOMMANDÉ"
    echo "   Suivre les instructions Linux ci-dessus dans WSL"
    echo ""
    echo "2️⃣  Docker Desktop"
    echo "   docker run -d -p 6379:6379 redis:latest"
    echo ""
    echo "3️⃣  Redis pour Windows (legacy)"
    echo "   https://github.com/microsoftarchive/redis/releases"
    echo ""
    exit 1
else
    echo "❌ Système non reconnu: $OS_TYPE"
    exit 1
fi

echo ""
echo "================================"
echo "✅ Redis est prêt!"
echo "================================"
echo ""
echo "Configuration Laravel .env:"
echo "  CACHE_STORE=redis"
echo "  QUEUE_CONNECTION=redis"
echo "  REDIS_HOST=127.0.0.1"
echo "  REDIS_PORT=6379"
echo ""
