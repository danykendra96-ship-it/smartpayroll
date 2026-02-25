<?php
/**
 * ============================================================================
 * SMARTPAYROLL - Classe de Base des Modèles (MVC)
 * ============================================================================
 * 
 * Classe abstraite de base pour tous les modèles de l'application
 * Fournit des méthodes utilitaires pour l'accès à la base de données
 * 
 * @author [Ton nom] - Étudiante BTS Génie Logiciel
 * @establishment Institut Supérieur Mony Keng (ISMK)
 * @project Stage BTS - Digitalisation de la gestion des salaires
 * @version 1.0
 * @date Février 2026
 * ============================================================================
 */

namespace App\Core;

use App\Core\Database;

class Model {
    
    /**
     * Nom de la table associée
     * 
     * @var string
     */
    protected string $table = '';
    
    /**
     * Instance de la connexion à la base de données
     * 
     * @var \PDO|null
     */
    protected ?\PDO $db = null;
    
    /**
     * Constructeur du modèle
     */
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    /**
     * Obtenir tous les enregistrements
     * 
     * @param string $orderBy Champ de tri
     * @param string $order Direction du tri (ASC/DESC)
     * @return array
     */
    public function all(string $orderBy = 'id', string $order = 'ASC'): array {
        $sql = "SELECT * FROM {$this->table} ORDER BY {$orderBy} {$order}";
        return Database::query($sql);
    }
    
    /**
     * Obtenir un enregistrement par ID
     * 
     * @param int $id ID de l'enregistrement
     * @return array|null
     */
    public function find(int $id): ?array {
        $sql = "SELECT * FROM {$this->table} WHERE id = :id";
        return Database::fetchOne($sql, ['id' => $id]);
    }
    
    /**
     * Obtenir le premier enregistrement correspondant aux critères
     * 
     * @param array $where Critères de recherche
     * @return array|null
     */
    public function first(array $where): ?array {
        $conditions = [];
        $params = [];
        
        foreach ($where as $key => $value) {
            $conditions[] = "$key = :$key";
            $params[$key] = $value;
        }
        
        $sql = "SELECT * FROM {$this->table} WHERE " . implode(' AND ', $conditions) . " LIMIT 1";
        return Database::fetchOne($sql, $params);
    }
    
    /**
     * Obtenir tous les enregistrements correspondant aux critères
     * 
     * @param array $where Critères de recherche
     * @param string $orderBy Champ de tri
     * @param string $order Direction du tri
     * @return array
     */
    public function where(array $where, string $orderBy = 'id', string $order = 'ASC'): array {
        $conditions = [];
        $params = [];
        
        foreach ($where as $key => $value) {
            $conditions[] = "$key = :$key";
            $params[$key] = $value;
        }
        
        $sql = "SELECT * FROM {$this->table} WHERE " . implode(' AND ', $conditions) . 
               " ORDER BY {$orderBy} {$order}";
        return Database::query($sql, $params);
    }
    
    /**
     * Compter le nombre d'enregistrements
     * 
     * @param array $where Critères de recherche (optionnel)
     * @return int
     */
    public function count(array $where = []): int {
        if (empty($where)) {
            $sql = "SELECT COUNT(*) as count FROM {$this->table}";
            $result = Database::fetchOne($sql);
        } else {
            $conditions = [];
            $params = [];
            
            foreach ($where as $key => $value) {
                $conditions[] = "$key = :$key";
                $params[$key] = $value;
            }
            
            $sql = "SELECT COUNT(*) as count FROM {$this->table} WHERE " . implode(' AND ', $conditions);
            $result = Database::fetchOne($sql, $params);
        }
        
        return (int)($result['count'] ?? 0);
    }
    
    /**
     * Insérer un nouvel enregistrement
     * 
     * @param array $data Données à insérer
     * @return int|false ID de l'enregistrement inséré ou false en cas d'erreur
     */
    public function create(array $data) {
        $fields = implode(', ', array_keys($data));
        $placeholders = ':' . implode(', :', array_keys($data));
        
        $sql = "INSERT INTO {$this->table} ($fields) VALUES ($placeholders)";
        
        if (Database::execute($sql, $data)) {
            return (int)$this->db->lastInsertId();
        }
        
        return false;
    }
    
    /**
     * Mettre à jour un enregistrement
     * 
     * @param int $id ID de l'enregistrement
     * @param array $data Données à mettre à jour
     * @return bool
     */
    public function update(int $id, array $data): bool {
        $fields = [];
        foreach ($data as $key => $value) {
            $fields[] = "$key = :$key";
        }
        
        $sql = "UPDATE {$this->table} SET " . implode(', ', $fields) . " WHERE id = :id";
        $data['id'] = $id;
        
        return Database::execute($sql, $data) !== false;
    }
    
    /**
     * Supprimer un enregistrement
     * 
     * @param int $id ID de l'enregistrement
     * @return bool
     */
    public function delete(int $id): bool {
        $sql = "DELETE FROM {$this->table} WHERE id = :id";
        return Database::execute($sql, ['id' => $id]) !== false;
    }
    
    /**
     * Exécuter une requête SQL personnalisée
     * 
     * @param string $sql Requête SQL
     * @param array $params Paramètres (optionnel)
     * @return array
     */
    public function query(string $sql, array $params = []): array {
        return Database::query($sql, $params);
    }
    
    /**
     * Exécuter une requête SQL personnalisée (INSERT, UPDATE, DELETE)
     * 
     * @param string $sql Requête SQL
     * @param array $params Paramètres (optionnel)
     * @return bool|int Nombre de lignes affectées ou false
     */
    public function execute(string $sql, array $params = []) {
        return Database::execute($sql, $params);
    }
}
?>