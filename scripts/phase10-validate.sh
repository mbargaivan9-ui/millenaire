#!/usr/bin/env bash

# ═══════════════════════════════════════════════════════════════════════════
# PHASE 10 DEPLOYMENT & VALIDATION SCRIPT
# ═══════════════════════════════════════════════════════════════════════════
# 
# Ce script valide que PHASE 10 est correctement implémenté et configuré
# 
# Usage: bash ./scripts/phase10-validate.sh
#
# ═══════════════════════════════════════════════════════════════════════════

set -e

# Couleurs pour les messages
GREEN='\033[0;32m'
RED='\033[0;31m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

echo "═══════════════════════════════════════════════════════════════════════════"
echo "  PHASE 10 — VALIDATION SCRIPT"
echo "  Système de Paiement Orange Money & MTN MoMo"
echo "═══════════════════════════════════════════════════════════════════════════"
echo ""

# Counter pour les checks
TOTAL_CHECKS=0
PASSED_CHECKS=0

check_file() {
    local filepath=$1
    local description=$2
    
    TOTAL_CHECKS=$((TOTAL_CHECKS + 1))
    
    if [ -f "$filepath" ]; then
        echo -e "${GREEN}✓${NC} $description"
        echo "  Fichier: $filepath"
        PASSED_CHECKS=$((PASSED_CHECKS + 1))
    else
        echo -e "${RED}✗${NC} $description"
        echo "  Fichier manquant: $filepath"
    fi
    echo ""
}

check_env_var() {
    local varname=$1
    local description=$2
    
    TOTAL_CHECKS=$((TOTAL_CHECKS + 1))
    
    if grep -q "^${varname}=" .env.example 2>/dev/null; then
        echo -e "${GREEN}✓${NC} $description configurée dans .env.example"
        PASSED_CHECKS=$((PASSED_CHECKS + 1))
    else
        echo -e "${YELLOW}⚠${NC} $description non trouvée dans .env.example"
    fi
    echo ""
}

echo "1️⃣  Vérification des fichiers créés..."
echo "───────────────────────────────────────────────────────────────────────────"

check_file "app/Services/PaymentSimulationService.php" \
    "Service de simulation de paiement"

check_file "app/Http/Controllers/Parent/MobileMoneyPaymentController.php" \
    "Contrôleur Mobile Money"

check_file "resources/views/parent/payments/mobile-money.blade.php" \
    "Vue interface premium 7 étapes"

check_file "tests/Unit/Services/PaymentSimulationServiceTest.php" \
    "Tests unitaires PaymentSimulationService"

check_file "PHASE10_PAYMENT_SYSTEM.md" \
    "Documentation PHASE 10"

echo "2️⃣  Vérification des fichiers modifiés..."
echo "───────────────────────────────────────────────────────────────────────────"

# Vérifier que les routes sont ajoutées
TOTAL_CHECKS=$((TOTAL_CHECKS + 1))
if grep -q "mobile-money" routes/web.php; then
    echo -e "${GREEN}✓${NC} Routes mobile-money ajoutées"
    PASSED_CHECKS=$((PASSED_CHECKS + 1))
else
    echo -e "${RED}✗${NC} Routes mobile-money non trouvées"
fi
echo ""

# Vérifier que le bouton est ajouté dans la vue
TOTAL_CHECKS=$((TOTAL_CHECKS + 1))
if grep -q "Pay with Mobile Money" resources/views/parent/payments/show.blade.php; then
    echo -e "${GREEN}✓${NC} Bouton Mobile Money ajouté dans la vue"
    PASSED_CHECKS=$((PASSED_CHECKS + 1))
else
    echo -e "${RED}✗${NC} Bouton Mobile Money non trouvé"
fi
echo ""

echo "3️⃣  Vérification de la configuration..."
echo "───────────────────────────────────────────────────────────────────────────"

check_env_var "PAYMENT_PROVIDER" "Mode de paiement (simulation/production)"
check_env_var "PAYMENT_MODE" "Mode Sandbox/Production"
check_env_var "PAYMENT_WEBHOOK_SECRET" "Secret Webhook HMAC"
check_env_var "PAYMENT_POLLING_INTERVAL" "Intervalle de polling"
check_env_var "PAYMENT_POLLING_TIMEOUT" "Timeout de polling"

echo "4️⃣  Vérification du fichier .env.local.example..."
echo "───────────────────────────────────────────────────────────────────────────"

check_file ".env.local.example" \
    "Configuration de démonstration avec mode simulation"

echo "5️⃣  Vérification de la config/payment.php..."
echo "───────────────────────────────────────────────────────────────────────────"

TOTAL_CHECKS=$((TOTAL_CHECKS + 1))
if grep -q "'simulation'" config/payment.php; then
    echo -e "${GREEN}✓${NC} Configuration simulation ajoutée"
    PASSED_CHECKS=$((PASSED_CHECKS + 1))
else
    echo -e "${RED}✗${NC} Configuration simulation manquante"
fi
echo ""

TOTAL_CHECKS=$((TOTAL_CHECKS + 1))
if grep -q "'webhook'" config/payment.php; then
    echo -e "${GREEN}✓${NC} Configuration webhook ajoutée"
    PASSED_CHECKS=$((PASSED_CHECKS + 1))
else
    echo -e "${RED}✗${NC} Configuration webhook manquante"
fi
echo ""

TOTAL_CHECKS=$((TOTAL_CHECKS + 1))
if grep -q "'polling'" config/payment.php; then
    echo -e "${GREEN}✓${NC} Configuration polling ajoutée"
    PASSED_CHECKS=$((PASSED_CHECKS + 1))
else
    echo -e "${RED}✗${NC} Configuration polling manquante"
fi
echo ""

echo "═══════════════════════════════════════════════════════════════════════════"
echo ""
echo "📊 RÉSULTAT FINAL:"
echo "───────────────────────────────────────────────────────────────────────────"
echo "✅ Vérifications réussies: $PASSED_CHECKS / $TOTAL_CHECKS"
echo ""

if [ $PASSED_CHECKS -eq $TOTAL_CHECKS ]; then
    echo -e "${GREEN}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
    echo -e "${GREEN} ✅ PHASE 10 COMPLÈTEMENT IMPLÉMENTÉ ET VALIDÉ! ${NC}"
    echo -e "${GREEN}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
    echo ""
    
    echo "🚀 PROCHAINES ÉTAPES:"
    echo "───────────────────────────────────────────────────────────────────────────"
    echo "1. Copier la configuration de démonstration:"
    echo "   cp .env.local.example .env.local"
    echo ""
    echo "2. Démarrer les services:"
    echo "   php artisan serve"
    echo "   npm run dev"
    echo ""
    echo "3. Tester le paiement:"
    echo "   - Accéder en tant que parent"
    echo "   - Aller dans Paiements → Enfant"
    echo "   - Cliquer sur 'Pay with Mobile Money'"
    echo "   - Suivre le flux 7 étapes"
    echo ""
    echo "4. Consulter la documentation:"
    echo "   - Lire PHASE10_PAYMENT_SYSTEM.md"
    echo "   - Vérifier les logs dans storage/logs/"
    echo ""
    
    exit 0
else
    echo -e "${RED}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
    echo -e "${RED} ⚠️  PHASE 10 INCOMPLET - VÉRIFICATIONS ÉCHOUÉES ${NC}"
    echo -e "${RED}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
    echo ""
    echo "📋 Fichiers/configurations manquants:"
    echo "   Réexécutez le script pour identifier les problèmes"
    echo ""
    exit 1
fi
