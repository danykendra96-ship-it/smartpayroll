<?php
/**
 * ============================================================================
 * SMARTPAYROLL - Classe de Base des Contrôleurs (MVC)
 * ============================================================================
 * 
 * Classe abstraite de base pour tous les contrôleurs de l'application
 * Fournit des méthodes utilitaires pour le rendu des vues, la gestion des données,
 * et l'intégration avec les modèles et la session.
 * 
 * @author [Ton nom] - Étudiante BTS Génie Logiciel
 * @establishment Institut Supérieur Mony Keng (ISMK)
 * @project Stage BTS - Digitalisation de la gestion des salaires
 * @version 1.0
 * @date Février 2026
 * ============================================================================
 */

namespace App\Core;

use App\Core\Session;

class Controller {
    
    /**
     * Données à passer à la vue
     * 
     * @var array
     */
    protected array $data = [];
    
    /**
     * Titre de la page
     * 
     * @var string
     */
    protected string $pageTitle = '';
    
    /**
     * Constructeur du contrôleur
     */
    public function __construct() {
        // Démarrer la session si nécessaire
        if (session_status() === PHP_SESSION_NONE) {
            Session::start();
        }
        
        // Vérifier le timeout de session
        if (Session::isLoggedIn()) {
            if (!Session::checkTimeout(30)) {
                Session::destroy();
                header('Location: /SmartPayrollApp/public/login.php?error=session_expiree');
                exit();
            }
        }
    }
    
    /**
     * Rendre une vue
     * 
     * @param string $view Chemin de la vue (ex: 'admin/dashboard')
     * @param array $data Données à passer à la vue
     * @return void
     */
    protected function view(string $view, array $data = []): void {
        // Définir le chemin complet de la vue
        $viewPath = APP_PATH . DS . 'views' . DS . str_replace('/', DS, $view) . '.php';
        
        // Vérifier si la vue existe
        if (!file_exists($viewPath)) {
            throw new \Exception("Vue introuvable : $viewPath");
        }
        
        // Fusionner les données du contrôleur avec celles passées en paramètre
        $this->data = array_merge($this->data, $data);
        
        // Extraire les données pour la vue
        extract($this->data);
        
        // Inclure la vue
        require_once $viewPath;
    }
    
    /**
     * Rediriger vers une URL
     * 
     * @param string $url URL de redirection
     * @param int $statusCode Code de statut HTTP (302 par défaut)
     * @return void
     */
    protected function redirect(string $url, int $statusCode = 302): void {
        // Construire l'URL complète si elle est relative
        if (strpos($url, 'http') !== 0 && strpos($url, '/') !== 0) {
            $url = BASE_URL . '/' . ltrim($url, '/');
        }
        
        header('Location: ' . $url, true, $statusCode);
        exit();
    }
    
    /**
     * Obtenir une donnée spécifique
     * 
     * @param string $key Clé de la donnée
     * @param mixed $default Valeur par défaut si non trouvée
     * @return mixed
     */
    protected function get(string $key, $default = null) {
        return $this->data[$key] ?? $default;
    }
    
    /**
     * Définir une donnée
     * 
     * @param string $key Clé de la donnée
     * @param mixed $value Valeur de la donnée
     * @return void
     */
    protected function set(string $key, $value): void {
        $this->data[$key] = $value;
    }
    
    /**
     * Ajouter un message flash (session)
     * 
     * @param string $type Type du message (success, error, warning, info)
     * @param string $message Contenu du message
     * @return void
     */
    protected function flash(string $type, string $message): void {
        $_SESSION['flash'] = [
            'type' => $type,
            'message' => $message
        ];
    }
    
    /**
     * Obtenir et supprimer le message flash
     * 
     * @return array|null
     */
    protected function getFlash(): ?array {
        if (isset($_SESSION['flash'])) {
            $flash = $_SESSION['flash'];
            unset($_SESSION['flash']);
            return $flash;
        }
        return null;
    }
    
    /**
     * Vérifier si l'utilisateur est connecté
     * 
     * @return bool
     */
    protected function isLoggedIn(): bool {
        return Session::isLoggedIn();
    }
    
    /**
     * Obtenir l'ID de l'utilisateur connecté
     * 
     * @return int|null
     */
    protected function getUserId(): ?int {
        return Session::getUserId();
    }
    
    /**
     * Obtenir le rôle de l'utilisateur connecté
     * 
     * @return string|null
     */
    protected function getUserRole(): ?string {
        return Session::getUserRole();
    }
    
    /**
     * Vérifier si l'utilisateur a un rôle spécifique
     * 
     * @param string|array $roles Rôle ou tableau de rôles autorisés
     * @return bool
     */
    protected function hasRole($roles): bool {
        return Session::hasRole($roles);
    }
    
    /**
     * Vérifier si l'utilisateur est admin
     * 
     * @return bool
     */
    protected function isAdmin(): bool {
        return Session::isAdmin();
    }
    
    /**
     * Vérifier si l'utilisateur est comptable
     * 
     * @return bool
     */
    protected function isComptable(): bool {
        return Session::isComptable();
    }
    
    /**
     * Vérifier si l'utilisateur est employé
     * 
     * @return bool
     */
    protected function isEmploye(): bool {
        return Session::isEmploye();
    }
    
    /**
     * Vérifier l'accès et rediriger si non autorisé
     * 
     * @param string|array $allowedRoles Rôles autorisés
     * @param string|null $redirectUrl URL de redirection en cas de refus
     * @return void
     */
    protected function requireRole($allowedRoles, ?string $redirectUrl = null): void {
        if (!$this->isLoggedIn()) {
            $this->redirect($redirectUrl ?? '/public/login.php?error=non_connecte');
        }
        
        if (!$this->hasRole($allowedRoles)) {
            $this->redirect($redirectUrl ?? '/?error=acces_interdit');
        }
    }
    
    /**
     * Obtenir la valeur d'un paramètre POST
     * 
     * @param string $key Clé du paramètre
     * @param mixed $default Valeur par défaut
     * @return mixed
     */
    protected function post(string $key, $default = null) {
        return $_POST[$key] ?? $default;
    }
    
    /**
     * Obtenir la valeur d'un paramètre GET
     * 
     * @param string $key Clé du paramètre
     * @param mixed $default Valeur par défaut
     * @return mixed
     */
    protected function getQuery(string $key, $default = null) {
        return $_GET[$key] ?? $default;
    }
    
    /**
     * Obtenir la valeur d'un paramètre de requête (GET ou POST)
     * 
     * @param string $key Clé du paramètre
     * @param mixed $default Valeur par défaut
     * @return mixed
     */
    protected function input(string $key, $default = null) {
        return $_REQUEST[$key] ?? $default;
    }
    
    /**
     * Vérifier si la requête est de type POST
     * 
     * @return bool
     */
    protected function isPost(): bool {
        return $_SERVER['REQUEST_METHOD'] === 'POST';
    }
    
    /**
     * Vérifier si la requête est de type GET
     * 
     * @return bool
     */
    protected function isGet(): bool {
        return $_SERVER['REQUEST_METHOD'] === 'GET';
    }
    
    /**
     * Obtenir l'URL actuelle
     * 
     * @return string
     */
    protected function getCurrentUrl(): string {
        $protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        $uri = $_SERVER['REQUEST_URI'] ?? '/';
        return $protocol . '://' . $host . $uri;
    }
    
    /**
     * Obtenir l'adresse IP du client
     * 
     * @return string
     */
    protected function getClientIp(): string {
        return $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    }
    
    /**
     * Journaliser une action
     * 
     * @param string $message Message à journaliser
     * @param string $level Niveau de journalisation (info, warning, error)
     * @return void
     */
    protected function log(string $message, string $level = 'info'): void {
        $timestamp = date('Y-m-d H:i:s');
        $ip = $this->getClientIp();
        $userId = $this->getUserId() ?? 'anonymous';
        
        $logMessage = sprintf(
            "[%s] [%s] [User: %s] [IP: %s] %s",
            strtoupper($level),
            $timestamp,
            $userId,
            $ip,
            $message
        );
        
        // Écrire dans le fichier de log
        $logFile = LOGS_PATH . DS . 'app.log';
        file_put_contents($logFile, $logMessage . PHP_EOL, FILE_APPEND);
    }
    
    /**
     * Obtenir les données JSON du corps de la requête
     * 
     * @return array|null
     */
    protected function getJsonInput(): ?array {
        $json = file_get_contents('php://input');
        return json_decode($json, true);
    }
    
    /**
     * Répondre avec du JSON
     * 
     * @param array $data Données à encoder en JSON
     * @param int $statusCode Code de statut HTTP
     * @return void
     */
    protected function jsonResponse(array $data, int $statusCode = 200): void {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data, JSON_PRETTY_PRINT);
        exit();
    }
    
    /**
     * Obtenir l'URL de base de l'application
     * 
     * @return string
     */
    protected function baseUrl(): string {
        return BASE_URL;
    }
    
    /**
     * Générer une URL complète
     * 
     * @param string $path Chemin relatif
     * @return string
     */
    protected function url(string $path = ''): string {
        return rtrim(BASE_URL, '/') . '/' . ltrim($path, '/');
    }
    
    /**
     * Méthode par défaut (à surcharger dans les contrôleurs enfants)
     */
    public function index() {
        // À implémenter dans les contrôleurs enfants
    }
}
?>