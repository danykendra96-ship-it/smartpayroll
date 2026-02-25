<?php
/**
 * ============================================================================
 * SMARTPAYROLL - Gestion Sécurisée des Sessions
 * ============================================================================
 * 
 * Classe responsable de la gestion des sessions utilisateur :
 * - Démarrage sécurisé
 * - Vérification de connexion
 * - Gestion des rôles
 * - Timeout automatique
 * - Protection contre les attaques
 * 
 * @author [Ton nom] - Étudiante BTS Génie Logiciel
 * @establishment Institut Supérieur Mony Keng (ISMK)
 * @project Stage BTS - Digitalisation de la gestion des salaires
 * @version 1.0
 * @date Février 2026
 * ============================================================================
 */

namespace App\Core;

class Session {
    
    /**
     * Démarrer la session sécurisée
     */
    public static function start() {
        if (session_status() === PHP_SESSION_NONE) {
            // Si les en-têtes ont déjà été envoyés, on ne peut pas modifier
            // les paramètres de session ni appeler session_start() sans générer
            // des warnings "Headers already sent". On journalise et on quitte.
            if (headers_sent()) {
                error_log('[Session] Impossible de démarrer la session : headers déjà envoyés');
                return;
            }

            // Configuration sécurisée des sessions
            ini_set('session.cookie_httponly', 1);
            ini_set('session.use_only_cookies', 1);
            ini_set('session.cookie_secure', 0);
            ini_set('session.use_strict_mode', 1);
            ini_set('session.gc_maxlifetime', 1800);

            // Démarrer la session en toute sécurité
            session_start();

            // Regénérer l'ID de session pour éviter la fixation de session
            if (!isset($_SESSION['initiated'])) {
                session_regenerate_id(true);
                $_SESSION['initiated'] = true;
                $_SESSION['ip_address'] = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
                $_SESSION['user_agent'] = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
            }
        }
    }
    
    /**
     * Vérifier si l'utilisateur est connecté
     * 
     * @return bool
     */
    public static function isLoggedIn(): bool {
        return isset($_SESSION['user_id']) && 
               !empty($_SESSION['user_id']) && 
               isset($_SESSION['user_role']);
    }
    
    /**
     * Obtenir l'ID de l'utilisateur connecté
     * 
     * @return int|null
     */
    public static function getUserId(): ?int {
        return self::isLoggedIn() ? (int)$_SESSION['user_id'] : null;
    }
    
    /**
     * Obtenir le rôle de l'utilisateur connecté
     * 
     * @return string|null
     */
    public static function getUserRole(): ?string {
        return self::isLoggedIn() ? $_SESSION['user_role'] : null;
    }
    
    /**
     * Obtenir le matricule de l'utilisateur connecté
     * 
     * @return string|null
     */
    public static function getUserMatricule(): ?string {
        return self::isLoggedIn() ? $_SESSION['user_matricule'] : null;
    }
    
    /**
     * Obtenir le nom complet de l'utilisateur connecté
     * 
     * @return string|null
     */
    public static function getUserName(): ?string {
        if (!self::isLoggedIn()) {
            return null;
        }
        return $_SESSION['user_prenom'] . ' ' . $_SESSION['user_nom'];
    }
    
    /**
     * Créer une session après login réussi
     * 
     * @param array $userData Données de l'utilisateur depuis la BDD
     * @return void
     */
    public static function create(array $userData): void {
        $_SESSION['user_id'] = $userData['id_employe'];
        $_SESSION['user_matricule'] = $userData['matricule'];
        $_SESSION['user_nom'] = $userData['nom'];
        $_SESSION['user_prenom'] = $userData['prenom'];
        $_SESSION['user_email'] = $userData['email'];
        $_SESSION['user_role'] = isset($userData['role']) ? mb_strtolower($userData['role']) : 'employe';
        $_SESSION['user_type_contrat'] = $userData['type_contrat'] ?? 'CDI';
        $_SESSION['user_poste'] = $userData['poste'] ?? '';
        $_SESSION['user_departement'] = $userData['departement'] ?? '';
        $_SESSION['last_activity'] = time();
        $_SESSION['ip_address'] = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $_SESSION['user_agent'] = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
        
        // Regénérer l'ID pour la sécurité
        session_regenerate_id(true);
    }
    
    /**
     * Détruire la session (logout)
     * 
     * @return void
     */
    public static function destroy(): void {
        if (session_status() === PHP_SESSION_ACTIVE) {
            // Sauvegarder l'IP pour le log
            $ip = $_SESSION['ip_address'] ?? 'unknown';
            
            // Détruire toutes les variables de session
            $_SESSION = [];
            
            // Supprimer le cookie de session
            if (ini_get('session.use_cookies')) {
                $params = session_get_cookie_params();
                setcookie(
                    session_name(),
                    '',
                    time() - 42000,
                    $params['path'],
                    $params['domain'],
                    $params['secure'],
                    $params['httponly']
                );
            }
            
            // Détruire la session
            session_destroy();
            
            // Journaliser la déconnexion
            error_log("[LOGOUT] Déconnexion utilisateur - IP: $ip");
        }
    }
    
    /**
     * Vérifier le timeout de session (inactivité)
     * 
     * @param int $maxMinutes Durée maximale d'inactivité en minutes
     * @return bool
     */
    public static function checkTimeout(int $maxMinutes = 30): bool {
        if (!isset($_SESSION['last_activity'])) {
            return false;
        }
        
        $inactiveMinutes = (time() - $_SESSION['last_activity']) / 60;
        
        if ($inactiveMinutes > $maxMinutes) {
            self::destroy();
            return false;
        }
        
        // Mettre à jour le timestamp d'activité
        $_SESSION['last_activity'] = time();
        return true;
    }
    
    /**
     * Vérifier qu'un employé accède uniquement à SES données
     * 
     * @param int $employeId ID de l'employé dont on veut accéder aux données
     * @return bool
     */
    public static function checkOwnership(int $employeId): bool {
        if (!self::isLoggedIn()) {
            return false;
        }
        
        // Les admins et comptables peuvent accéder à tous les employés
        if (self::getUserRole() === 'admin' || self::getUserRole() === 'comptable') {
            return true;
        }
        
        // Les employés simples ne peuvent accéder qu'à leurs propres données
        return self::getUserId() === $employeId;
    }
    
    /**
     * Vérifier si l'utilisateur a un rôle spécifique
     * 
     * @param string|array $roles Rôle ou tableau de rôles autorisés
     * @return bool
     */
    public static function hasRole($roles): bool {
        if (!self::isLoggedIn()) {
            return false;
        }
        
        $userRole = self::getUserRole();
        
        if (is_array($roles)) {
            return in_array($userRole, $roles);
        }
        
        return $userRole === $roles;
    }
    
    /**
     * Vérifier si l'utilisateur est Admin
     * 
     * @return bool
     */
    public static function isAdmin(): bool {
        return self::hasRole('admin');
    }
    
    /**
     * Vérifier si l'utilisateur est Comptable
     * 
     * @return bool
     */
    public static function isComptable(): bool {
        return self::hasRole('comptable');
    }
    
    /**
     * Vérifier si l'utilisateur est Employé simple
     * 
     * @return bool
     */
    public static function isEmploye(): bool {
        return self::hasRole('employe');
    }
    
    /**
     * Rediriger vers le dashboard selon le rôle
     * 
     * @return void
     */
   /**
 * Rediriger vers le dashboard selon le rôle
 */
public static function redirectToDashboard(): void {
    if (!self::isLoggedIn()) {
        header('Location: /');
        exit();
    }
    
    // ✅ Construire l'URL de base dynamiquement
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'];
    $scriptDir = dirname($_SERVER['SCRIPT_NAME']);
    $baseUrl = rtrim($protocol . '://' . $host . $scriptDir, '/');
    
    $role = self::getUserRole();
    
    // ✅ Construire l'URL de redirection
    switch ($role) {
        case 'admin':
            $redirectUrl = $baseUrl . '/app/controllers/AdminController.php?action=dashboard';
            break;
        case 'comptable':
            $redirectUrl = $baseUrl . '/app/controllers/ComptableController.php?action=dashboard';
            break;
        case 'employe':
            $redirectUrl = $baseUrl . '/app/controllers/EmployeController.php?action=dashboard';
            break;
        default:
            $redirectUrl = $baseUrl . '/?error=role_inconnu';
    }
    
    // ✅ Redirection
    header('Location: ' . $redirectUrl);
    exit();
}
    
    /**
     * Vérifier l'accès et rediriger si non autorisé
     * 
     * @param string|array $allowedRoles Rôles autorisés
     * @return void
     */
    public static function requireRole($allowedRoles): void {
        self::start();
        
        if (!self::isLoggedIn()) {
            $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
            $host = $_SERVER['HTTP_HOST'];
            $scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
            $parts = explode('/', trim($scriptName, '/'));
            $projectPath = '/' . ($parts[0] ?? 'SmartPayrollApp');
            $loginUrl = $protocol . '://' . $host . $projectPath . '/public/index.php?error=non_connecte';
            header('Location: ' . $loginUrl);
            exit();
        }
        
        $userRole = self::getUserRole();
        
        if (!in_array($userRole, (array)$allowedRoles)) {
            self::redirectToDashboard();
        }
    }
    
    /**
     * Journaliser une activité suspecte
     * 
     * @param string $message Message à journaliser
     * @param mixed $data Données supplémentaires
     * @return void
     */
    public static function logSuspiciousActivity(string $message, $data = null): void {
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
        $userId = self::getUserId() ?? 'anonymous';
        
        $logMessage = sprintf(
            "[SECURITY] %s | User: %s | IP: %s | UA: %s | Data: %s",
            $message,
            $userId,
            $ip,
            $userAgent,
            $data ? json_encode($data) : 'none'
        );
        
        error_log($logMessage);
    }
}
?>