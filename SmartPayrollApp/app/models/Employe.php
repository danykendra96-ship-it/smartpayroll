<?php
/**
 * ============================================================================
 * SMARTPAYROLL - Modèle Employe (VERSION COMPLÈTE)
 * ============================================================================
 * 
 * Gère toutes les opérations sur les employés :
 * - CRUD complet
 * - Statistiques par rôle/type de contrat
 * - Validation des données
 * - Génération de matricules
 * 
 * @author [Ton nom] - Étudiante BTS Génie Logiciel
 * @establishment Institut Supérieur Mony Keng (ISMK)
 * @project Stage BTS - Digitalisation de la gestion des salaires
 * @version 2.0
 * @date Février 2026
 * ============================================================================
 */

namespace App\Models;

use App\Core\Database;

class Employe {
    
    // ==================== MÉTHODES DE BASE (CRUD) ====================
    
    public function getAll(): array {
        $sql = "SELECT e.*, p.libelle AS poste, d.libelle AS departement, en.nom AS entreprise
                FROM employe e
                INNER JOIN poste p ON e.id_poste = p.id_poste
                INNER JOIN departement d ON p.id_departement = d.id_departement
                INNER JOIN entreprise en ON e.id_entreprise = en.id_entreprise
                ORDER BY e.nom ASC, e.prenom ASC";
        return Database::query($sql);
    }
    
    public function getById(int $id): ?array {
        $sql = "SELECT e.*, p.libelle AS poste, p.id_departement, d.libelle AS departement, en.nom AS entreprise
                FROM employe e
                LEFT JOIN poste p ON e.id_poste = p.id_poste
                LEFT JOIN departement d ON p.id_departement = d.id_departement
                LEFT JOIN entreprise en ON e.id_entreprise = en.id_entreprise
                WHERE e.id_employe = :id";
        return Database::fetchOne($sql, ['id' => $id]);
    }
    
    public function create(array $data): ?int {
        $data['mot_de_passe'] = password_hash($data['mot_de_passe'], PASSWORD_BCRYPT);
        
        $sql = "INSERT INTO employe (
                    id_entreprise, id_poste, matricule, nom, prenom, email, mot_de_passe,
                    telephone, adresse, date_embauche, type_contrat, date_fin_contrat,
                    taux_horaire, solde_conges, statut, role
                ) VALUES (
                    :id_entreprise, :id_poste, :matricule, :nom, :prenom, :email, :mot_de_passe,
                    :telephone, :adresse, :date_embauche, :type_contrat, :date_fin_contrat,
                    :taux_horaire, :solde_conges, :statut, :role
                )";
        
        if (Database::execute($sql, $data)) {
            return (int)Database::getInstance()->lastInsertId();
        }
        return null;
    }
    
    public function update(int $id, array $data): bool {
        if (!empty($data['mot_de_passe'])) {
            $data['mot_de_passe'] = password_hash($data['mot_de_passe'], PASSWORD_BCRYPT);
        } else {
            unset($data['mot_de_passe']);
        }
        
        $fields = [];
        foreach ($data as $key => $value) {
            $fields[] = "$key = :$key";
        }
        
        $sql = "UPDATE employe SET " . implode(', ', $fields) . " WHERE id_employe = :id";
        $data['id'] = $id;
        
        return Database::execute($sql, $data) !== false;
    }
    
    public function delete(int $id): bool {
        $sql = "UPDATE employe SET statut = 'inactif' WHERE id_employe = :id";
        return Database::execute($sql, ['id' => $id]) !== false;
    }
    
    public function toggleStatus(int $id, string $statut): bool {
        $sql = "UPDATE employe SET statut = :statut WHERE id_employe = :id";
        return Database::execute($sql, ['id' => $id, 'statut' => $statut]) !== false;
    }
    
    // ==================== STATISTIQUES ====================
    
    public function countAll(): int {
        $sql = "SELECT COUNT(*) as count FROM employe";
        $result = Database::fetchOne($sql);
        return (int)($result['count'] ?? 0);
    }
    
    public function countActive(): int {
        $sql = "SELECT COUNT(*) as count FROM employe WHERE statut = 'actif'";
        $result = Database::fetchOne($sql);
        return (int)($result['count'] ?? 0);
    }
    
    public function getStatsByRole(): array {
        $sql = "SELECT role, COUNT(*) as count FROM employe WHERE statut = 'actif' GROUP BY role";
        return Database::query($sql);
    }
    
    /**
     * Compter les employés actifs par type de contrat (NOUVEAU - pour comptable)
     */
    public function countByTypeContrat(string $typeContrat): int {
        $sql = "SELECT COUNT(*) as count FROM employe 
                WHERE type_contrat = :type_contrat AND statut = 'actif'";
        $result = Database::fetchOne($sql, ['type_contrat' => $typeContrat]);
        return (int)($result['count'] ?? 0);
    }
    
    // ==================== VALIDATION ====================
    
    public function emailExists(string $email, ?int $excludeId = null): bool {
        if ($excludeId !== null) {
            $sql = "SELECT COUNT(*) as count FROM employe WHERE email = :email AND id_employe != :exclude_id";
            $result = Database::fetchOne($sql, ['email' => $email, 'exclude_id' => $excludeId]);
        } else {
            $sql = "SELECT COUNT(*) as count FROM employe WHERE email = :email";
            $result = Database::fetchOne($sql, ['email' => $email]);
        }
        return ((int)$result['count']) > 0;
    }
    
    public function matriculeExists(string $matricule, ?int $excludeId = null): bool {
        if ($excludeId !== null) {
            $sql = "SELECT COUNT(*) as count FROM employe WHERE matricule = :matricule AND id_employe != :exclude_id";
            $result = Database::fetchOne($sql, ['matricule' => $matricule, 'exclude_id' => $excludeId]);
        } else {
            $sql = "SELECT COUNT(*) as count FROM employe WHERE matricule = :matricule";
            $result = Database::fetchOne($sql, ['matricule' => $matricule]);
        }
        return ((int)$result['count']) > 0;
    }
    
    // ==================== GÉNÉRATION ====================
    
    public function generateMatricule(string $typeContrat = 'EMP'): string {
        $prefix = strtoupper(substr($typeContrat, 0, 3));
        $sql = "SELECT COUNT(*) as count FROM employe WHERE matricule LIKE :prefix";
        $result = Database::fetchOne($sql, ['prefix' => $prefix . '%']);
        $count = (int)($result['count'] ?? 0) + 1;
        return $prefix . str_pad($count, 4, '0', STR_PAD_LEFT);
    }
    
    // ==================== MÉTHODES SPÉCIFIQUES COMPTABLE (NOUVEAU) ====================
    
    /**
     * Obtenir les employés actifs CDI et CDD (pour génération bulletin)
     */
    public function getActifsCDI_CDD(): array {
        $sql = "SELECT e.id_employe, e.matricule, e.nom, e.prenom, e.email, e.type_contrat,
                       e.date_embauche, p.libelle AS poste, p.salaire_base, d.libelle AS departement
                FROM employe e
                INNER JOIN poste p ON e.id_poste = p.id_poste
                INNER JOIN departement d ON p.id_departement = d.id_departement
                WHERE e.statut = 'actif' AND e.type_contrat IN ('CDI', 'CDD')
                ORDER BY e.nom, e.prenom";
        return Database::query($sql);
    }
    
    /**
     * Obtenir les employés actifs par type de contrat (pour saisie heures)
     */
    public function getActifsByType(string $typeContrat): array {
        $sql = "SELECT e.id_employe, e.matricule, e.nom, e.prenom, e.email, e.taux_horaire,
                       e.date_embauche, p.libelle AS poste, d.libelle AS departement
                FROM employe e
                INNER JOIN poste p ON e.id_poste = p.id_poste
                INNER JOIN departement d ON p.id_departement = d.id_departement
                WHERE e.statut = 'actif' AND e.type_contrat = :type_contrat
                ORDER BY e.nom, e.prenom";
        return Database::query($sql, ['type_contrat' => $typeContrat]);
    }
    
    /**
     * Obtenir tous les postes avec départements (pour formulaire ajout employé)
     */
    public function getAllPostes(): array {
        $sql = "SELECT p.id_poste, p.libelle, p.salaire_base, d.libelle AS departement 
                FROM poste p
                INNER JOIN departement d ON p.id_departement = d.id_departement
                ORDER BY d.libelle, p.libelle";
        return Database::query($sql);
    }
    
    /**
     * Obtenir les bulletins d'un employé (pour dashboard employé)
     */
    public function getBulletins(int $idEmploye): array {
        $sql = "SELECT b.*, mp.libelle AS mode_paiement
                FROM bulletin b
                LEFT JOIN mode_paiement mp ON b.id_mode_paiement = mp.id_mode
                WHERE b.id_employe = :id_employe
                ORDER BY b.annee DESC, b.mois DESC";
        return Database::query($sql, ['id_employe' => $idEmploye]);
    }
      /**
     * Authentifier un utilisateur
     * 
     * @param string $email Email de l'utilisateur
     * @param string $password Mot de passe non hashé
     * @return array|false Données de l'utilisateur ou false si échec
     */
    public function authenticate(string $email, string $password) {
        try {
            // Récupérer l'utilisateur par email avec jointures
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
                        d.libelle AS departement,
                        en.nom AS entreprise
                    FROM employe e
                    LEFT JOIN poste p ON e.id_poste = p.id_poste
                    LEFT JOIN departement d ON p.id_departement = d.id_departement
                    LEFT JOIN entreprise en ON e.id_entreprise = en.id_entreprise
                    WHERE e.email = :email 
                    AND e.statut = 'actif'";
            
            $user = Database::fetchOne($sql, ['email' => $email]);
            
            // Vérifier si l'utilisateur existe
            if (!$user) {
                error_log("Auth: Utilisateur non trouvé pour l'email: $email");
                return false;
            }
            
            // Vérifier le mot de passe
            if (!password_verify($password, $user['mot_de_passe'])) {
                error_log("Auth: Mot de passe incorrect pour l'email: $email");
                return false;
            }
            
            // Supprimer le mot de passe des résultats retournés
            unset($user['mot_de_passe']);
            
            // Journaliser la connexion réussie
            error_log("Auth: Connexion réussie pour l'email: $email");
            
            return $user;
            
        } catch (\Exception $e) {
            error_log("Employe::authenticate() - Erreur : " . $e->getMessage());
            return false;
        }
        
    }
     public function updateProfil(int $idEmploye, array $data): bool {
    $fields = [];
    foreach ($data as $key => $value) {
        if ($value !== null && $value !== '') {
            $fields[] = "$key = :$key";
        }
    }
    
    if (empty($fields)) {
        return false;
    }
    
    $sql = "UPDATE employe SET " . implode(', ', $fields) . " WHERE id_employe = :id_employe";
    $data['id_employe'] = $idEmploye;
    
    return Database::execute($sql, $data) !== false;
}
    // ... autres méthodes ...
    
    /**
     * Obtenir les bulletins D'UN SEUL employé (confidentialité)
     */
    public function getBulletinsByEmploye(int $idEmploye, int $mois = null, int $annee = null): array {
        $sql = "SELECT b.*, mp.libelle AS mode_paiement
                FROM bulletin b
                LEFT JOIN mode_paiement mp ON b.id_mode_paiement = mp.id_mode
                WHERE b.id_employe = :id_employe";
        
        $params = ['id_employe' => $idEmploye];
        
        if ($mois && $annee) {
            $sql .= " AND b.mois = :mois AND b.annee = :annee";
            $params['mois'] = $mois;
            $params['annee'] = $annee;
        }
        
        $sql .= " ORDER BY b.annee DESC, b.mois DESC";
        
        return Database::query($sql, $params);
    }
    
    /**
     * Obtenir les congés D'UN SEUL employé (confidentialité)
     */
    public function getCongesByEmploye(int $idEmploye): array {
        $sql = "SELECT * FROM conge 
                WHERE id_employe = :id_employe 
                ORDER BY date_demande DESC";
        return Database::query($sql, ['id_employe' => $idEmploye]);
    }
    
}



?>