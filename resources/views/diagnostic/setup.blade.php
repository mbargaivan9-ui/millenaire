<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configuration - Millénaire Connect</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 40px 20px;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .setup-container {
            max-width: 900px;
            margin: 0 auto;
        }
        .setup-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            padding: 40px;
            margin-bottom: 30px;
        }
        .setup-header {
            text-align: center;
            margin-bottom: 40px;
            color: white;
        }
        .setup-header h1 {
            font-size: 2.5rem;
            font-weight: bold;
            margin-bottom: 10px;
        }
        .setup-section {
            margin-bottom: 40px;
        }
        .setup-section h2 {
            color: #667eea;
            font-weight: bold;
            margin-bottom: 20px;
            border-bottom: 3px solid #667eea;
            padding-bottom: 10px;
        }
        .setup-step {
            background: #f8f9fa;
            border-left: 4px solid #667eea;
            padding: 20px;
            margin-bottom: 15px;
            border-radius: 5px;
        }
        .step-number {
            display: inline-block;
            background: #667eea;
            color: white;
            width: 35px;
            height: 35px;
            border-radius: 50%;
            text-align: center;
            line-height: 35px;
            font-weight: bold;
            margin-right: 10px;
            margin-bottom: 10px;
        }
        .code-block {
            background: #2c3e50;
            color: #ecf0f1;
            padding: 15px;
            border-radius: 5px;
            overflow-x: auto;
            margin: 10px 0;
            font-family: 'Courier New', monospace;
            font-size: 0.9rem;
        }
        .credentials-table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        .credentials-table th,
        .credentials-table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        .credentials-table th {
            background: #667eea;
            color: white;
        }
        .credentials-table tr:hover {
            background: #f5f5f5;
        }
        .alert-info {
            border-left: 4px solid #0ea5e9;
        }
        .success-badge {
            display: inline-block;
            background: #10b981;
            color: white;
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 0.85rem;
            margin-left: 10px;
        }
    </style>
</head>
<body>
    <div class="setup-container">
        <div class="setup-header">
            <h1><i class="fas fa-cog me-3"></i>Configuration Initiale</h1>
            <p>Guide de configuration pour Millénaire Connect</p>
        </div>

        <div class="setup-card">
            <div class="setup-section">
                <h2><i class="fas fa-database me-2"></i>1. Configuration de la Base de Données</h2>
                
                <div class="alert alert-info">
                    <strong>Étape importante:</strong> Vous devez d'abord avoir une base de données MySQL fonctionnelle.
                </div>

                <div class="setup-step">
                    <span class="step-number">1</span>
                    <strong>Installez MySQL (si ce n'est pas déjÉ  fait)</strong>
                    <p style="margin-top: 10px; color: #666;">Téléchargez et installez MySQL depuis: <a href="https://www.mysql.com/downloads/" target="_blank">https://www.mysql.com/downloads/</a></p>
                </div>

                <div class="setup-step">
                    <span class="step-number">2</span>
                    <strong>Configurez les variables d'environnement</strong>
                    <p style="margin-top: 10px;">Éditez le fichier <code>.env</code> à la racine du projet:</p>
                    <div class="code-block">
DB_CONNECTION=mysql<br>
DB_HOST=127.0.0.1<br>
DB_PORT=3306<br>
DB_DATABASE=millenaire<br>
DB_USERNAME=root<br>
DB_PASSWORD=votre_mot_de_passe
                    </div>
                    <p style="margin-top: 10px; color: #666;"><strong>Note:</strong> Remplacez `votre_mot_de_passe` par votre mot de passe MySQL réel.</p>
                </div>

                <div class="setup-step">
                    <span class="step-number">3</span>
                    <strong>Créez la base de données</strong>
                    <p style="margin-top: 10px;">Ouvrez MySQL et exécutez:</p>
                    <div class="code-block">
CREATE DATABASE IF NOT EXISTS millenaire CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
                    </div>
                </div>

                <div class="setup-step">
                    <span class="step-number">4</span>
                    <strong>Exécutez les migrations</strong>
                    <p style="margin-top: 10px;">Dans terminal/PowerShell, allez au dossier du projet et exécutez:</p>
                    <div class="code-block">
php artisan migrate --seed
                    </div>
                    <p style="margin-top: 10px; color: #666;"><strong>Ou</strong>, si vous voulez juste les migrations sans données:</p>
                    <div class="code-block">
php artisan migrate
                    </div>
                </div>

                <div class="setup-step">
                    <span class="step-number">5</span>
                    <strong>Peuplez la base de données avec des utilisateurs par défaut</strong>
                    <p style="margin-top: 10px;">Exécutez le seeder:</p>
                    <div class="code-block">
php artisan db:seed --class=DefaultUsersSeeder
                    </div>
                </div>
            </div>

            <div class="setup-section">
                <h2><i class="fas fa-users me-2"></i>2. Utilisateurs par Défaut</h2>
                
                <p style="margin-bottom: 20px;">Après avoir exécuté les commandes ci-dessus, utilisez ces identifiants pour vous connecter:</p>
                
                <table class="credentials-table">
                    <thead>
                        <tr>
                            <th>Rôle</th>
                            <th>Email</th>
                            <th>Mot de passe</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><strong>Admin</strong></td>
                            <td><code>admin@Millénaire connect.local</code></td>
                            <td><code>admin@123456</code></td>
                        </tr>
                        <tr>
                            <td><strong>Professeur</strong></td>
                            <td><code>teacher@Millénaire connect.local</code></td>
                            <td><code>teacher@123456</code></td>
                        </tr>
                        <tr>
                            <td><strong>Professeur Principal</strong></td>
                            <td><code>prof_principal@Millénaire connect.local</code></td>
                            <td><code>prof@123456</code></td>
                        </tr>
                        <tr>
                            <td><strong>Parent</strong></td>
                            <td><code>parent@Millénaire connect.local</code></td>
                            <td><code>parent@123456</code></td>
                        </tr>
                        <tr>
                            <td><strong>Étudiants</strong></td>
                            <td><code>alice@Millénaire connect.local</code>, etc.</td>
                            <td><code>student@123456</code></td>
                        </tr>
                    </tbody>
                </table>

                <div class="alert alert-warning" style="margin-top: 20px;">
                    <strong><i class="fas fa-exclamation-triangle me-2"></i>Important:</strong>
                    Changez les mots de passe par défaut immédiatement après la première connexion!
                </div>
            </div>

            <div class="setup-section">
                <h2><i class="fas fa-check-circle me-2"></i>3. Vérification</h2>
                
                <div class="setup-step">
                    <p style="margin: 0;">
                        <i class="fas fa-heartbeat me-2"></i>
                        <strong>Vérifiez que tout fonctionne:</strong>
                    </p>
                    <div class="code-block" style="margin-top: 15px;">
curl http://127.0.0.1:8000/health
                    </div>
                    <p style="margin-top: 10px; color: #666;">Ou visitez directement: <a href="http://127.0.0.1:8000/health" target="_blank">http://127.0.0.1:8000/health</a></p>
                </div>
            </div>

            <div class="setup-section">
                <h2><i class="fas fa-question-circle me-2"></i>Dépannage</h2>
                
                <div class="setup-step">
                    <strong>Erreur: "SQLSTATE[HY000] [2002] Connection refused"</strong>
                    <p style="margin-top: 10px; color: #666;">
                        â†’ MySQL n'est pas en cours d'exécution. Vérifiez que le service MySQL est bien démarré.
                    </p>
                </div>

                <div class="setup-step">
                    <strong>Erreur: "Base de données 'millenaire' n'existe pas"</strong>
                    <p style="margin-top: 10px; color: #666;">
                        â†’ Créez la base de données avec la commande SQL fournie É  l'étape 3.
                    </p>
                </div>

                <div class="setup-step">
                    <strong>Les migrations ne s'exécutent pas</strong>
                    <p style="margin-top: 10px; color: #666;">
                        â†’ Assurez-vous d'être dans le répertoire du projet et exécutez:
                    </p>
                    <div class="code-block">
php artisan migrate:fresh --seed
                    </div>
                </div>
            </div>

            <div style="text-align: center; margin-top: 40px;">
                <a href="{{ route('login') }}" class="btn btn-primary btn-lg" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border: none;">
                    <i class="fas fa-sign-in-alt me-2"></i>Aller É  la connexion
                </a>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
</body>
</html>

