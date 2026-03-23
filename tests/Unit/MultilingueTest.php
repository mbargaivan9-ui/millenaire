<?php

/**
 * Test multilingue - Vérifier que toutes les traductions fonctionnent
 */

// Test command: php artisan tinker
// Puis exécuter ce fichier

use App\Helpers\LanguageHelper;
use Illuminate\Support\Facades\App;

echo "═══ TEST SYSTÈME MULTILINGUE ───\n\n";

// Test 1: Vérifier les locales supportées
echo "✓ Locales supportées: " . implode(', ', LanguageHelper::SUPPORTED_LANGUAGES) . "\n";
echo "✓ Locale par défaut: " . LanguageHelper::DEFAULT_LANGUAGE . "\n\n";

// Test 2: Vérifier la locale actuelle
echo "Locale actuelle: " . LanguageHelper::getCurrentLocale() . "\n";
echo "Est français? " . (LanguageHelper::isFrench() ? 'OUI' : 'NON') . "\n";
echo "Est anglais? " . (LanguageHelper::isEnglish() ? 'OUI' : 'NON') . "\n\n";

// Test 3: Obtenir les infos de langue
$info = LanguageHelper::getLanguageInfo();
echo "Info langue actuelle:\n";
echo "  - Nom: " . $info['name'] . "\n";
echo "  - Code: " . $info['code'] . "\n";
echo "  - Drapeau: " . $info['flag'] . "\n";
echo "  - Direction: " . $info['direction'] . "\n\n";

// Test 4: Obtenir langues disponibles
$languages = LanguageHelper::getAvailableLanguages();
echo "Langues disponibles:\n";
foreach ($languages as $code => $lang) {
    echo "  [$code] " . $lang['flag'] . " " . $lang['name'] . " (". $lang['code'] .")\n";
}
echo "\n";

// Test 5: Vérifier les traductions
echo "Tests des traductions (FR):\n";
App::setLocale('fr');
echo "  common.home = " . __('common.home') . "\n";
echo "  common.login = " . __('common.login') . "\n";
echo "  public.about_title = " . __('public.about_title') . "\n";
echo "  auth.login = " . __('auth.login') . "\n\n";

echo "Tests des traductions (EN):\n";
App::setLocale('en');
echo "  common.home = " . __('common.home') . "\n";
echo "  common.login = " . __('common.login') . "\n";
echo "  public.about_title = " . __('public.about_title') . "\n";
echo "  auth.login = " . __('auth.login') . "\n\n";

// Reset to default
App::setLocale(LanguageHelper::DEFAULT_LANGUAGE);

echo "═══ TESTS COMPLÉTÉS ✓ ═══\n";
