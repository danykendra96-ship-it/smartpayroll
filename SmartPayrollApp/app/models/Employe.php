<?php
/**
 * ============================================================================
 * SMARTPAYROLL - Modèle Employé
 * ============================================================================
 * 
 * Classe modèle pour la gestion des employés
 * CRUD + Authentification
 * 
 * @author [Ton nom] - Étudiante BTS Génie Logiciel
 * @establishment Institut Supérieur Mony Keng (ISMK)
 * @project Stage BTS - Digitalisation de la gestion des salaires
 * @version 1.0
 * @date Février 2026
 * ============================================================================
 */

namespace App\Models;

use App\Core\Database;
use PDO;

class Employe {
    
    /**
     * Table de la base de données
     */
    private string $table = 'employe';
    
    /**
     * Authentifier un utilisateur
     * 
     * @param string $email Email de l'utilisateur
     * @param string $password Mot de passe (non hashé)
     * @return array|null Données de l'utilisateur ou null si échec
     */
    public function authenticate(string $email, string $password): ?array {
        try {
            // Récupérer l'utilisateur par email avec jointures
            // Debug: journaliser la tentative (ne pas logger le mot de passe)
            error_log("[AUTH DEBUG] authenticate() appelé avec email=" . $email);
            // Rechercher l'utilisateur par email (insensible à la casse)
            $sql = "SELECT 
                        e.id_employe,
                        e.matricule,
                        e.nom,
                        e.prenom,
                        e.email,
                        e.mot_de_passe,
                        e.role,
                        e.type_contrat,
                        e.statut,
                        p.libelle AS poste,
                        d.libelle AS departement
                    FROM employe e
                    LEFT JOIN poste p ON e.id_poste = p.id_poste
                    LEFT JOIN departement d ON p.id_departement = d.id_departement
                    WHERE LOWER(e.email) = :email
                    AND e.statut = 'actif'";

            $user = Database::fetchOne($sql, ['email' => mb_strtolower($email)]);
            error_log("[AUTH DEBUG] Database::fetchOne retourné: " . var_export($user, true));

            if (!$user) {
                error_log("[AUTH] Aucune entrée trouvée pour l'email: $email");
                return null;
            }

            $stored = $user['mot_de_passe'] ?? '';
            error_log("[AUTH DEBUG] mot_de_passe (prefix): " . substr($stored, 0, 6));
            if (empty($stored)) {
                error_log("[AUTH] Mot de passe absent en base pour l'ID: {$user['id_employe']}");
                return null;
            }

            // Vérifier mot de passe hashé
            if (password_verify($password, $stored)) {
                // OK
            } else {
                // Si le mot de passe en base est stocké en clair, permettre la compatibilité
                if (hash_equals($stored, $password)) {
                    // Ré-hasher le mot de passe en base pour sécurité
                    $newHash = password_hash($password, PASSWORD_BCRYPT);
                    $updateSql = "UPDATE employe SET mot_de_passe = :hash WHERE id_employe = :id";
                    Database::execute($updateSql, ['hash' => $newHash, 'id' => $user['id_employe']]);
                    error_log("[AUTH] Mot de passe en clair détecté et ré-hashé pour ID: {$user['id_employe']}");
                } else {
                    error_log("[AUTH] Échec vérification mot de passe pour Email: $email, ID: {$user['id_employe']}");
                    return null;
                }
            }

            // Normaliser le rôle et supprimer le mot de passe du résultat retourné
            if (isset($user['role'])) {
                $user['role'] = mb_strtolower($user['role']);
            }
            unset($user['mot_de_passe']);

            // Journaliser la connexion réussie
            error_log("[AUTH] Connexion réussie - Email: {$user['email']}, ID: {$user['id_employe']}");

            return $user;
            
        } catch (\Exception $e) {
            error_log("[AUTH ERROR] " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Obtenir un employé par son ID
     * 
     * @param int $id ID de l'employé
     * @return array|null Données de l'employé ou null
     */
    public function getById(int $id): ?array {
        $sql = "SELECT 
                    e.*,
                    p.libelle AS poste,
                    d.libelle AS departement,
                    en.nom AS entreprise
                FROM employe e
                LEFT JOIN poste p ON e.id_poste = p.id_poste
                LEFT JOIN departement d ON p.id_departement = d.id_departement
                LEFT JOIN entreprise en ON e.id_entreprise = en.id_entreprise
                WHERE e.id_employe = :id";
        
        return Database::fetchOne($sql, ['id' => $id]);
    }
    
    /**
     * Obtenir tous les employés actifs
     * 
     * @return array Tableau d'employés
     */
    public function getAllActive(): array {
        $sql = "SELECT 
                    e.id_employe,
                    e.matricule,
                    e.nom,
                    e.prenom,
                    e.email,
                    e.telephone,
                    e.role,
                    e.type_contrat,
                    e.statut,
                    e.date_embauche,
                    p.libelle AS poste,
                    d.libelle AS departement
                FROM employe e
                LEFT JOIN poste p ON e.id_poste = p.id_poste
                LEFT JOIN departement d ON p.id_departement = d.id_departement
                WHERE e.statut = 'actif'
                ORDER BY e.nom, e.prenom";
        
        return Database::query($sql);
    }
    
    /**
     * Obtenir tous les employés (actifs + inactifs)
     * 
     * @return array Tableau d'employés
     */
    public function getAll(): array {
        $sql = "SELECT 
                    e.id_employe,
                    e.matricule,
                    e.nom,
                    e.prenom,
                    e.email,
                    e.telephone,
                    e.role,
                    e.type_contrat,
                    e.statut,
                    e.date_embauche,
                    p.libelle AS poste,
                    d.libelle AS departement
                FROM employe e
                LEFT JOIN poste p ON e.id_poste = p.id_poste
                LEFT JOIN departement d ON p.id_departement = d.id_departement
                ORDER BY e.nom, e.prenom";
        
        return Database::query($sql);
    }
    
    /**
     * Créer un nouvel employé
     * 
     * @param array $data Données de l'employé
     * @return int|false ID de l'employé créé ou false en cas d'erreur
     */
    public function create(array $data) {
        try {
            // Hasher le mot de passe
            $data['mot_de_passe'] = password_hash($data['mot_de_passe'], PASSWORD_BCRYPT);
            
            $sql = "INSERT INTO employe (
                        id_entreprise,
                        id_poste,
                        matricule,
                        nom,
                        prenom,
                        email,
                        mot_de_passe,
                        telephone,
                        adresse,
                        date_embauche,
                        type_contrat,
                        date_fin_contrat,
                        taux_horaire,
                        solde_conges,
                        statut,
                        role
                    ) VALUES (
                        :id_entreprise,
                        :id_poste,
                        :matricule,
                        :nom,
                        :prenom,
                        :email,
                        :mot_de_passe,
                        :telephone,
                        :adresse,
                        :date_embauche,
                        :type_contrat,
                        :date_fin_contrat,
                        :taux_horaire,
                        :solde_conges,
                        :statut,
                        :role
                    )";
            
            $result = Database::execute($sql, $data);
            
            if ($result) {
                return Database::getInstance()->lastInsertId();
            }
            
            return false;
            
        } catch (\Exception $e) {
            error_log("[EMPLOYE CREATE ERROR] " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Mettre à jour un employé
     * 
     * @param int $id ID de l'employé
     * @param array $data Données à mettre à jour
     * @return bool
     */
    public function update(int $id, array $data): bool {
        try {
            // Si le mot de passe est dans les données, le hasher
            if (isset($data['mot_de_passe'])) {
                $data['mot_de_passe'] = password_hash($data['mot_de_passe'], PASSWORD_BCRYPT);
            }
            
            // Construire la requête UPDATE dynamiquement
            $fields = [];
            foreach ($data as $key => $value) {
                $fields[] = "$key = :$key";
            }
            
            $sql = "UPDATE employe SET " . implode(', ', $fields) . " WHERE id_employe = :id";
            $data['id'] = $id;
            
            return Database::execute($sql, $data) !== false;
            
        } catch (\Exception $e) {
            error_log("[EMPLOYE UPDATE ERROR] " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Supprimer un employé (soft delete)
     * 
     * @param int $id ID de l'employé
     * @return bool
     */
    public function delete(int $id): bool {
        try {
            // Soft delete : mettre le statut à 'inactif'
            $sql = "UPDATE employe SET statut = 'inactif' WHERE id_employe = :id";
            return Database::execute($sql, ['id' => $id]) !== false;
            
        } catch (\Exception $e) {
            error_log("[EMPLOYE DELETE ERROR] " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Activer/Désactiver un employé
     * 
     * @param int $id ID de l'employé
     * @param string $statut 'actif' ou 'inactif'
     * @return bool
     */
    public function toggleStatus(int $id, string $statut): bool {
        $statut = $statut === 'actif' ? 'actif' : 'inactif';
        $sql = "UPDATE employe SET statut = :statut WHERE id_employe = :id";
        return Database::execute($sql, ['id' => $id, 'statut' => $statut]) !== false;
    }
    
    /**
     * Compter le nombre total d'employés actifs
     * 
     * @return int
     */
    public function countActive(): int {
        $sql = "SELECT COUNT(*) FROM employe WHERE statut = 'actif'";
        return (int)Database::fetchColumn($sql);
    }
    
    /**
     * Compter le nombre total d'employés
     * 
     * @return int
     */
    public function countAll(): int {
        $sql = "SELECT COUNT(*) FROM employe";
        return (int)Database::fetchColumn($sql);
    }
    
    /**
     * Obtenir les statistiques par rôle
     * 
     * @return array
     */
    public function getStatsByRole(): array {
        $sql = "SELECT role, COUNT(*) as count FROM employe WHERE statut = 'actif' GROUP BY role";
        return Database::query($sql);
    }
    
    /**
     * Vérifier si un email existe déjà
     * 
     * @param string $email Email à vérifier
     * @param int|null $excludeId ID à exclure (pour les mises à jour)
     * @return bool
     */
    public function emailExists(string $email, ?int $excludeId = null): bool {
        $sql = "SELECT COUNT(*) FROM employe WHERE email = :email";
        
        if ($excludeId !== null) {
            $sql .= " AND id_employe != :exclude_id";
            return (int)Database::fetchColumn($sql, ['email' => $email, 'exclude_id' => $excludeId]) > 0;
        }
        
        return (int)Database::fetchColumn($sql, ['email' => $email]) > 0;
    }
    
    /**
     * Vérifier si un matricule existe déjà
     * 
     * @param string $matricule Matricule à vérifier
     * @param int|null $excludeId ID à exclure (pour les mises à jour)
     * @return bool
     */
    public function matriculeExists(string $matricule, ?int $excludeId = null): bool {
        $sql = "SELECT COUNT(*) FROM employe WHERE matricule = :matricule";
        
        if ($excludeId !== null) {
            $sql .= " AND id_employe != :exclude_id";
            return (int)Database::fetchColumn($sql, ['matricule' => $matricule, 'exclude_id' => $excludeId]) > 0;
        }
        
        return (int)Database::fetchColumn($sql, ['matricule' => $matricule]) > 0;
    }
    
    /**
     * Générer un matricule unique
     * 
     * @param string $type_contrat Type de contrat (CDI, CDD, VAC, STA)
     * @return string
     */
    public function generateMatricule(string $type_contrat = 'EMP'): string {
        $prefix = strtoupper(substr($type_contrat, 0, 3));
        
        // Compter le nombre d'employés avec ce préfixe
        $sql = "SELECT COUNT(*) FROM employe WHERE matricule LIKE :prefix";
        $count = (int)Database::fetchColumn($sql, ['prefix' => $prefix . '%']);
        
        // Générer le matricule
        $number = str_pad($count + 1, 4, '0', STR_PAD_LEFT);
        
        return $prefix . $number;
    }
    
}
?>