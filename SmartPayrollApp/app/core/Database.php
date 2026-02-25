<?php
/**
 * ============================================================================
 * SMARTPAYROLL - Connexion Sécurisée à la Base de Données
 * ============================================================================
 * 
 * Classe singleton pour la gestion de la connexion PDO à MySQL
 * Implémente le pattern Singleton pour une seule instance partagée
 * 
 * @author [Ton nom] - Étudiante BTS Génie Logiciel
 * @establishment Institut Supérieur Mony Keng (ISMK)
 * @project Stage BTS - Digitalisation de la gestion des salaires
 * @version 1.0
 * @date Février 2026
 * ============================================================================
 */

namespace App\Core;

use PDO;
use PDOException;

class Database {
    
    /**
     * Instance unique du singleton Database
     *
     * @var self|null
     */
    private static ?self $instance = null;
    
    /**
     * Connexion PDO
     * 
     * @var PDO|null
     */
    private ?PDO $connection = null;
    
    /**
     * Constructeur privé (Singleton)
     */
    private function __construct() {
        try {
            // Charger la configuration depuis le fichier
            $config = require __DIR__ . '/../config/database.php';
            
            $dsn = sprintf(
                'mysql:host=%s;dbname=%s;charset=%s',
                $config['host'],
                $config['dbname'],
                $config['charset'] ?? 'utf8mb4'
            );
            
            $this->connection = new PDO(
                $dsn,
                $config['username'],
                $config['password'],
                $config['options'] ?? [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                ]
            );
            
            // Journaliser la connexion réussie
            error_log("[DB] Connexion à la base de données établie");
            
        } catch (PDOException $e) {
            // Journaliser l'erreur
            error_log("[DB ERROR] " . $e->getMessage());
            
            // Afficher un message d'erreur propre en production
            die('Erreur de connexion à la base de données. Veuillez contacter le support informatique.');
        }
    }
    
    /**
     * Empêcher le clonage de l'instance
     */
    private function __clone() {}
    
    /**
     * Empêcher la désérialisation
     */
    public function __wakeup() {
        throw new \Exception("Cannot unserialize singleton");
    }
    
    /**
     * Obtenir l'instance unique de la connexion
     * 
     * @return PDO
     */
    public static function getInstance(): PDO {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        
        return self::$instance->connection;
    }
    
    /**
     * Exécuter une requête SELECT
     * 
     * @param string $sql Requête SQL
     * @param array $params Paramètres pour la requête préparée
     * @return array Résultats de la requête
     */
    public static function query(string $sql, array $params = []): array {
        try {
            $stmt = self::getInstance()->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("[DB QUERY ERROR] " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Exécuter une requête INSERT, UPDATE ou DELETE
     * 
     * @param string $sql Requête SQL
     * @param array $params Paramètres pour la requête préparée
     * @return bool|int Nombre de lignes affectées ou false en cas d'erreur
     */
    public static function execute(string $sql, array $params = []) {
        try {
            $stmt = self::getInstance()->prepare($sql);
            $result = $stmt->execute($params);
            return $result ? $stmt->rowCount() : false;
        } catch (PDOException $e) {
            error_log("[DB EXECUTE ERROR] " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Exécuter une requête SELECT et retourner une seule ligne
     * 
     * @param string $sql Requête SQL
     * @param array $params Paramètres pour la requête préparée
     * @return array|null Une ligne ou null
     */
    public static function fetchOne(string $sql, array $params = []): ?array {
        try {
            $stmt = self::getInstance()->prepare($sql);
            $stmt->execute($params);
            $result = $stmt->fetch();
            return $result ?: null;
        } catch (PDOException $e) {
            error_log("[DB FETCHONE ERROR] " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Exécuter une requête SELECT et retourner une seule valeur
     * 
     * @param string $sql Requête SQL
     * @param array $params Paramètres pour la requête préparée
     * @return mixed|null Une valeur ou null
     */
    public static function fetchColumn(string $sql, array $params = []) {
        try {
            $stmt = self::getInstance()->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchColumn();
        } catch (PDOException $e) {
            error_log("[DB FETCHCOLUMN ERROR] " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Démarrer une transaction
     * 
     * @return bool
     */
    public static function beginTransaction(): bool {
        return self::getInstance()->beginTransaction();
    }
    
    /**
     * Valider une transaction
     * 
     * @return bool
     */
    public static function commit(): bool {
        return self::getInstance()->commit();
    }
    
    /**
     * Annuler une transaction
     * 
     * @return bool
     */
    public static function rollback(): bool {
        return self::getInstance()->rollBack();
    }
    
    /**
     * Échapper une chaîne pour l'affichage HTML
     * 
     * @param string $string Chaîne à échapper
     * @return string Chaîne échappée
     */
    public static function escape(string $string): string {
        return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
    }
    
    /**
     * Fermer la connexion (optionnel, PHP le fait automatiquement)
     */
    public static function close(): void {
        self::$instance = null;
    }
}
?>