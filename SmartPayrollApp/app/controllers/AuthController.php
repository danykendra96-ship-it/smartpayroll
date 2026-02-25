<?php
/**
 * ============================================================================
 * SMARTPAYROLL - Contrôleur d'Authentification (VERSION CORRIGÉE SANS ERREUR)
 * ============================================================================
 * 
 * Correction des problèmes de namespace et d'inclusion
 * 
 * @author [Ton nom] - Étudiante BTS Génie Logiciel
 * @establishment Institut Supérieur Mony Keng (ISMK)
 * @project Stage BTS - Digitalisation de la gestion des salaires
 * @version 2.2
 * @date Février 2026
 * ============================================================================
 */

// ✅ ACTIVER LES ERREURS POUR LE DÉBOGAGE (À RETIRER EN PRODUCTION)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// ✅ CORRECTION 1 : NE PAS UTILISER DE NAMESPACE (pour éviter les conflits)
// namespace App\Controllers; ← COMMENTER CETTE LIGNE

// ✅ CORRECTION 2 : UTILISER require_once SANS namespace
require_once __DIR__ . '/../core/Session.php';
require_once __DIR__ . '/../core/Database.php';
require_once __DIR__ . '/../models/Employe.php';

// ✅ CORRECTION 3 : UTILISER LES CLASSES SANS namespace
// use App\Core\Session; ← COMMENTER CETTE LIGNE
// use App\Models\Employe; ← COMMENTER CETTE LIGNE

class AuthController {
    private $employeModel;

    public function __construct() {
        // Démarrer la session et initialiser le modèle
        \App\Core\Session::start();
        $this->employeModel = new \App\Models\Employe();
    }
    
    public function login() {
        // Vérifier que la requête est POST
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /SmartPayrollApp/public/index.php?error=method_invalid');
            exit();
        }
        
        // Récupérer les données
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $accountType = $_POST['account_type'] ?? 'employe';
        
        // Validation
        if (empty($email) || empty($password)) {
            header('Location: /SmartPayrollApp/public/index.php?error=champs_vides');
            exit();
        }
        
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            header('Location: /SmartPayrollApp/public/index.php?error=email_invalide');
            exit();
        }
        
        // Authentifier
        $user = $this->employeModel->authenticate($email, $password);
        
        if (!$user) {
            header('Location: /SmartPayrollApp/public/index.php?error=credentials_invalides');
            exit();
        }
        
        // Vérifier le rôle (insensible à la casse)
        $accountType = mb_strtolower($accountType);
        $userRole = mb_strtolower($user['role'] ?? 'employe');
        if ($accountType !== 'all' && $userRole !== $accountType) {
            header('Location: /SmartPayrollApp/public/index.php?error=role_incorrect');
            exit();
        }
        
        // Créer la session
        \App\Core\Session::create($user);
        
        // Redirection selon le rôle
        $protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'];
        $projectPath = '/SmartPayrollApp';
        
        $role = $user['role'] ?? 'employe';
        $dashboardMap = [
            'admin' => $projectPath . '/app/controllers/AdminController.php?action=dashboard',
            'comptable' => $projectPath . '/app/controllers/ComptableController.php?action=dashboard',
            'employe' => $projectPath . '/app/controllers/EmployeController.php?action=dashboard'
        ];
        
        $redirectUrl = $protocol . '://' . $host . ($dashboardMap[$role] ?? $dashboardMap['employe']);
        
        header('Location: ' . $redirectUrl);
        exit();
    }
    
    public function logout() {
        \App\Core\Session::destroy();
        header('Location: /SmartPayrollApp/public/index.php?success=deconnecte');
        exit();
    }
    
    public function showLogin() {
        if (\App\Core\Session::isLoggedIn()) {
            \App\Core\Session::redirectToDashboard();
        }
    }
}

// -------------------------
// Gestionnaire pour appels directs (cible du formulaire)
// Placé en dehors de la classe pour éviter les erreurs de syntaxe
// -------------------------
try {
    $authController = new AuthController();

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Journaliser les champs POST utiles (ne jamais logger le mot de passe)
        $postEmail = $_POST['email'] ?? '(vide)';
        $postAccount = $_POST['account_type'] ?? '(non précisé)';
        $reqUri = $_SERVER['REQUEST_URI'] ?? 'unknown';
        error_log(sprintf("[AuthController POST] URI=%s | email=%s | account_type=%s", $reqUri, $postEmail, $postAccount));

        $authController->login();
    }

    if (isset($_GET['action'])) {
        $action = $_GET['action'];
        if ($action === 'logout') {
            $authController->logout();
        }
        if ($action === 'showLogin') {
            $authController->showLogin();
        }
    }
} catch (\Throwable $e) {
    // Journaliser détails complets pour debug
    $msg = sprintf("[AuthController handler error] %s in %s on line %d\nTrace:\n%s",
        $e->getMessage(),
        $e->getFile(),
        $e->getLine(),
        $e->getTraceAsString()
    );
    error_log($msg);
    http_response_code(500);
    // Afficher une sortie légèrement plus informative en dev
    if (ini_get('display_errors')) {
        echo '<h3>Erreur serveur lors du traitement de la connexion</h3>';
        echo '<pre>' . htmlspecialchars($msg) . '</pre>';
    } else {
        echo 'Erreur serveur lors du traitement de la connexion.';
    }
}
?>