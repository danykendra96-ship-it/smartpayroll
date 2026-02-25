<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

/**
 * ============================================================================
 * SMARTPAYROLL - Page de Connexion
 * ============================================================================
 * 
 * Page de login sécurisée pour l'application SmartPayroll
 * Permet l'authentification des utilisateurs selon leur rôle
 */

// Démarrer la session
session_start();

// Définir BASE_URL dynamiquement
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'];
$scriptDir = dirname($_SERVER['SCRIPT_NAME']);
$baseUrl = rtrim($protocol . '://' . $host . $scriptDir, '/');

// Gérer la déconnexion si demandée (?logout=1)
if (isset($_GET['logout']) && $_GET['logout'] == 1) {
    session_unset();
    session_destroy();
    header('Location: ' . $baseUrl . '/index.php?success=1');
    exit();
}

// Vérifier si l'utilisateur est connecté (pour affichage conditionnel)
$isLoggedIn = isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
$userRole = $_SESSION['user_role'] ?? 'employe';
$userName = $_SESSION['user_prenom'] ?? 'Utilisateur';

// Vérifier les paramètres d'erreur/succès dans l'URL
$error = $_GET['error'] ?? null;
$success = $_GET['success'] ?? null;
$accountType = $_GET['role'] ?? 'employe';

// Gérer la déconnexion
$isLoggedIn = isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion | SmartPayroll</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #2563eb;
            --primary-dark: #1d4ed8;
            --light: #f8fafc;
            --gray: #64748b;
            --border: #cbd5e1;
            --success: #10b981;
            --danger: #ef4444;
            --radius-lg: 12px;
            --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Inter', sans-serif;
            color: #1e293b;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .login-container {
            background: white;
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-lg);
            width: 100%;
            max-width: 450px;
            padding: 50px 40px;
        }

        .login-header {
            text-align: center;
            margin-bottom: 40px;
        }

        .logo-badge {
            width: 60px;
            height: 60px;
            background: var(--primary);
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 700;
            font-size: 22px;
            margin: 0 auto 16px;
        }

        .app-title { font-size: 26px; font-weight: 700; margin-bottom: 8px; }
        .app-subtitle { font-size: 15px; color: var(--gray); }
        .establishment-name { font-size: 13px; color: var(--gray); margin-top: 4px; font-weight: 500; }

        .alert {
            padding: 14px 20px;
            border-radius: 8px;
            margin-bottom: 24px;
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 15px;
            font-weight: 500;
        }

        .alert-error { background: #fee2e2; color: #991b1b; border: 1px solid #fecaca; }
        .alert-success { background: #dcfce7; color: #166534; border: 1px solid #bbf7d0; }

        .account-type-selector {
            margin-bottom: 24px;
            text-align: center;
        }

        .account-type-label {
            font-size: 14px;
            color: var(--gray);
            margin-bottom: 12px;
            display: block;
        }

        .account-type-buttons {
            display: flex;
            gap: 12px;
            justify-content: center;
        }

        .account-type-btn {
            padding: 10px 20px;
            border: 2px solid var(--border);
            background: white;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            color: var(--gray);
        }

        .account-type-btn:hover { border-color: var(--primary); color: var(--primary); }
        .account-type-btn.active { background: var(--primary); color: white; border-color: var(--primary); }

        .form-group { margin-bottom: 24px; }
        .form-group label { display: block; font-weight: 600; font-size: 15px; margin-bottom: 8px; }

        .form-control {
            width: 100%;
            padding: 14px 16px;
            border: 2px solid var(--border);
            border-radius: 8px;
            font-size: 16px;
            font-family: 'Inter', sans-serif;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
        }

        .btn-submit {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
        }

        .btn-submit:hover { transform: translateY(-2px); }

        .login-links {
            text-align: center;
            margin-top: 28px;
            padding-top: 24px;
            border-top: 1px solid var(--border);
        }

        .login-links p { font-size: 15px; color: var(--gray); margin-bottom: 12px; }
        .login-links a { color: var(--primary); font-weight: 600; }

        .login-footer {
            text-align: center;
            margin-top: 20px;
            font-size: 13px;
            color: var(--gray);
        }

        .login-footer a { color: var(--primary); font-weight: 600; }

        @media (max-width: 480px) {
            .login-container { padding: 40px 30px; }
            .account-type-buttons { flex-direction: column; gap: 8px; }
            .account-type-btn { width: 100%; }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-header">
            <?php if ($isLoggedIn): ?>
                <div style="text-align: right; margin-bottom: 20px; padding-bottom: 20px; border-bottom: 1px solid #cbd5e1;">
                    <a href="?logout=1" 
                       style="color: #ef4444; font-size: 14px; text-decoration: none; font-weight: 600;">
                        <i class="fas fa-sign-out-alt"></i> Se déconnecter
                    </a>
                </div>
            <?php endif; ?>
            <div class="logo-badge">ISMK</div>
            <h1 class="app-title">SmartPayroll</h1>
            <p class="app-subtitle">Gestion des Salaires</p>
            <p class="establishment-name">Institut Supérieur Mony Keng</p>
        </div>

        <?php if ($success): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i>
                Déconnexion effectuée avec succès.
            </div>
        <?php endif; ?>

        <?php if ($isLoggedIn): ?>
            <!-- Section pour utilisateur connecté -->
            <div style="text-align: center; padding: 30px 20px; background: #f8fafc; border-radius: 8px; margin-bottom: 24px;">
                <p style="font-size: 15px; color: #64748b; margin-bottom: 8px;">Vous êtes connecté en tant que :</p>
                <p style="font-size: 18px; font-weight: 600; color: #2563eb; margin-bottom: 4px;"><?= htmlspecialchars($userName) ?></p>
                <p style="font-size: 14px; color: #64748b; margin-bottom: 20px;">Rôle : <strong><?= ucfirst(htmlspecialchars($userRole)) ?></strong></p>
                
                <a href="<?php 
                    $dashboardUrl = '';
                    switch ($userRole) {
                        case 'admin':
                            $dashboardUrl = $baseUrl . '/../app/controllers/AdminController.php?action=dashboard';
                            break;
                        case 'comptable':
                            $dashboardUrl = $baseUrl . '/../app/controllers/ComptableController.php?action=dashboard';
                            break;
                        case 'employe':
                        default:
                            $dashboardUrl = $baseUrl . '/../app/controllers/EmployeController.php?action=dashboard';
                            break;
                    }
                    echo $dashboardUrl;
                ?>" style="display: inline-block; padding: 12px 24px; background: #10b981; color: white; text-decoration: none; border-radius: 8px; font-weight: 600; margin-right: 12px;">
                    <i class="fas fa-arrow-right"></i> Aller au Dashboard
                </a>
                
                <a href="?logout=1" style="display: inline-block; padding: 12px 24px; background: #ef4444; color: white; text-decoration: none; border-radius: 8px; font-weight: 600;">
                    <i class="fas fa-sign-out-alt"></i> Se déconnecter
                </a>
            </div>

            <hr style="border: none; border-top: 1px solid #cbd5e1; margin: 24px 0;">
            
            <p style="text-align: center; font-size: 14px; color: #64748b; margin-bottom: 24px;">
                <strong>Ou se connecter avec un autre compte :</strong>
            </p>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-triangle"></i>
                <?php
                switch ($error) {
                    case 'non_connecte':
                        echo 'Veuillez vous connecter pour accéder à l\'application.';
                        break;
                    case 'credentials_invalides':
                        echo 'Email ou mot de passe incorrect. Veuillez réessayer.';
                        break;
                    default:
                        echo 'Une erreur est survenue. Contactez le support.';
                }
                ?>
            </div>
        <?php endif; ?>

        <form action="<?= $baseUrl ?>/../app/controllers/AuthController.php" method="POST">
            <div class="account-type-selector">
                <span class="account-type-label">Type de compte :</span>
                <div class="account-type-buttons">
                    <button type="button" class="account-type-btn <?= $accountType === 'employe' ? 'active' : '' ?>" 
                            onclick="setAccountType('employe')">
                        <i class="fas fa-user"></i> Employé
                    </button>
                    <button type="button" class="account-type-btn <?= $accountType === 'comptable' ? 'active' : '' ?>" 
                            onclick="setAccountType('comptable')">
                        <i class="fas fa-calculator"></i> Comptable
                    </button>
                    <button type="button" class="account-type-btn <?= $accountType === 'admin' ? 'active' : '' ?>" 
                            onclick="setAccountType('admin')">
                        <i class="fas fa-user-shield"></i> Admin
                    </button>
                </div>
                <input type="hidden" name="account_type" id="accountTypeInput" value="<?= htmlspecialchars($accountType) ?>">
            </div>

            <div class="form-group">
                <label for="email"><i class="fas fa-envelope"></i> Email</label>
                <input type="email" id="email" name="email" class="form-control" placeholder="votre.email@ismk.mg" required autofocus>
            </div>

            <div class="form-group">
                <label for="password"><i class="fas fa-lock"></i> Mot de passe</label>
                <div style="position: relative;">
                    <input type="password" id="password" name="password" class="form-control" placeholder="••••••••" required>
                    <button type="button" onclick="togglePassword()" 
                            style="position: absolute; right: 12px; top: 50%; transform: translateY(-50%); background: none; border: none; color: var(--gray); cursor: pointer;">
                        <i class="fas fa-eye" id="passwordIcon"></i>
                    </button>
                </div>
            </div>

            <button type="submit" class="btn-submit">
                <i class="fas fa-sign-in-alt"></i> Se Connecter
            </button>
        </form>

        <div class="login-footer">
            <p><i class="fas fa-shield-alt"></i> Application interne - Accès réservé au personnel ISMK</p>
            <?php if ($isLoggedIn): ?>
                <p style="margin-top: 12px; font-size: 12px;">
                    <a href="?logout=1">Se déconnecter</a>
                </p>
            <?php endif; ?>
        </div>
    </div>

    <script>
        function togglePassword() {
            const input = document.getElementById('password');
            const icon = document.getElementById('passwordIcon');
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }

        function setAccountType(role) {
            document.querySelectorAll('.account-type-btn').forEach(btn => btn.classList.remove('active'));
            event.target.closest('button').classList.add('active');
            document.getElementById('accountTypeInput').value = role;
        }
    </script>
</body>
</html>
