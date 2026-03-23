
@echo off
REM Script pour nettoyer les caches et démarrer le serveur de développement

echo === Nettoyage et configuration initialisation ===

REM Exécuter le script de nettoyage PHP
php clear-all-caches.php

echo.
echo === Installation des dépendances Node ===
call npm install

echo.
echo === Démarrage du serveur Vite en mode développement ===
call npm run dev

echo.
echo === Serveur en cours d'exécution ===
pause
