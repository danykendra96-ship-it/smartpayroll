<?php
/**
 * ============================================================================
 * SMARTPAYROLL - Modèle Bulletin de Paie
 * ============================================================================
 * 
 * Gère toutes les opérations sur les bulletins de paie :
 * - CRUD complet
 * - Calculs salariaux
 * - Statistiques
 * - Relations avec primes/retenues
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

class Bulletin {
    
    /**
     * Compter les bulletins par statut pour un mois/année
     */
    public function countByStatut(string $statut, int $mois, int $annee): int {
        $sql = "SELECT COUNT(*) as count FROM bulletin 
                WHERE statut = :statut AND mois = :mois AND annee = :annee";
        $result = Database::fetchOne($sql, [
            'statut' => $statut,
            'mois' => $mois,
            'annee' => $annee
        ]);
        return (int)($result['count'] ?? 0);
    }
    
    /**
     * Somme des salaires nets pour un mois/année
     */
    public function sumSalaireNetMois(int $mois, int $annee): float {
        $sql = "SELECT SUM(salaire_net) as total FROM bulletin 
                WHERE mois = :mois AND annee = :annee AND statut IN ('valide', 'paye')";
        $result = Database::fetchOne($sql, ['mois' => $mois, 'annee' => $annee]);
        return (float)($result['total'] ?? 0);
    }
    
    /**
     * Moyenne des salaires nets pour un mois/année
     */
    public function avgSalaireNetMois(int $mois, int $annee): float {
        $sql = "SELECT AVG(salaire_net) as moyenne FROM bulletin 
                WHERE mois = :mois AND annee = :annee AND statut IN ('valide', 'paye')";
        $result = Database::fetchOne($sql, ['mois' => $mois, 'annee' => $annee]);
        return (float)($result['moyenne'] ?? 0);
    }
    
    /**
     * Obtenir les bulletins par statut pour un mois/année
     */
    public function getByStatut(string $statut, int $mois, int $annee): array {
        $sql = "SELECT b.*, e.matricule, e.nom, e.prenom, e.type_contrat, p.libelle AS poste, d.libelle AS departement
                FROM bulletin b
                INNER JOIN employe e ON b.id_employe = e.id_employe
                LEFT JOIN poste p ON e.id_poste = p.id_poste
                LEFT JOIN departement d ON p.id_departement = d.id_departement
                WHERE b.statut = :statut AND b.mois = :mois AND b.annee = :annee
                ORDER BY b.date_generation DESC";
        return Database::query($sql, [
            'statut' => $statut,
            'mois' => $mois,
            'annee' => $annee
        ]);
    }
    
    /**
     * Rechercher des bulletins avec filtres
     */
    public function search(int $mois, int $annee, string $statut = 'all', string $search = ''): array {
        $conditions = ["b.mois = :mois", "b.annee = :annee"];
        $params = ['mois' => $mois, 'annee' => $annee];
        
        if ($statut !== 'all') {
            $conditions[] = "b.statut = :statut";
            $params['statut'] = $statut;
        }
        
        if (!empty($search)) {
            $conditions[] = "(e.matricule LIKE :search OR e.nom LIKE :search OR e.prenom LIKE :search)";
            $params['search'] = "%$search%";
        }
        
        $sql = "SELECT b.*, e.matricule, e.nom, e.prenom, e.type_contrat, p.libelle AS poste, d.libelle AS departement
                FROM bulletin b
                INNER JOIN employe e ON b.id_employe = e.id_employe
                LEFT JOIN poste p ON e.id_poste = p.id_poste
                LEFT JOIN departement d ON p.id_departement = d.id_departement
                WHERE " . implode(' AND ', $conditions) . "
                ORDER BY b.date_generation DESC";
        
        return Database::query($sql, $params);
    }
    
    /**
     * Vérifier si un bulletin existe déjà pour un employé/mois/année
     */
    public function existsForEmploye(int $idEmploye, int $mois, int $annee): bool {
        $sql = "SELECT COUNT(*) as count FROM bulletin 
                WHERE id_employe = :id_employe AND mois = :mois AND annee = :annee";
        $result = Database::fetchOne($sql, [
            'id_employe' => $idEmploye,
            'mois' => $mois,
            'annee' => $annee
        ]);
        return ((int)$result['count']) > 0;
    }
    
    /**
     * Créer un nouveau bulletin
     */
    public function create(array $data): ?int {
        $fields = implode(', ', array_keys($data));
        $placeholders = ':' . implode(', :', array_keys($data));
        
        $sql = "INSERT INTO bulletin ($fields) VALUES ($placeholders)";
        
        if (Database::execute($sql, $data)) {
            return (int)Database::getInstance()->lastInsertId();
        }
        return null;
    }
    
    /**
     * Mettre à jour un bulletin
     */
    public function update(int $id, array $data): bool {
        $fields = [];
        foreach ($data as $key => $value) {
            $fields[] = "$key = :$key";
        }
        
        $sql = "UPDATE bulletin SET " . implode(', ', $fields) . " WHERE id_bulletin = :id";
        $data['id'] = $id;
        
        return Database::execute($sql, $data) !== false;
    }
    
    /**
     * Mettre à jour le statut d'un bulletin
     */
    public function updateStatut(int $id, string $statut): bool {
        $sql = "UPDATE bulletin SET statut = :statut WHERE id_bulletin = :id";
        return Database::execute($sql, ['id' => $id, 'statut' => $statut]) !== false;
    }
    
    /**
     * Obtenir un bulletin par ID avec infos complètes
     */
    public function getById(int $id): ?array {
        $sql = "SELECT b.*, e.matricule, e.nom, e.prenom, e.email, e.telephone, e.adresse,
                       e.type_contrat, p.libelle AS poste, d.libelle AS departement,
                       en.nom AS entreprise, en.adresse AS adresse_entreprise
                FROM bulletin b
                INNER JOIN employe e ON b.id_employe = e.id_employe
                LEFT JOIN poste p ON e.id_poste = p.id_poste
                LEFT JOIN departement d ON p.id_departement = d.id_departement
                LEFT JOIN entreprise en ON e.id_entreprise = en.id_entreprise
                WHERE b.id_bulletin = :id";
        return Database::fetchOne($sql, ['id' => $id]);
    }
    
    /**
     * Obtenir un bulletin complet avec primes/retenues
     */
    public function getCompleteById(int $id): ?array {
        $bulletin = $this->getById($id);
        if (!$bulletin) return null;
        
        // Récupérer les primes
        $sqlPrimes = "SELECT bp.*, p.libelle, p.type_prime 
                      FROM bulletin_prime bp
                      INNER JOIN prime p ON bp.id_prime = p.id_prime
                      WHERE bp.id_bulletin = :id";
        $bulletin['primes'] = Database::query($sqlPrimes, ['id' => $id]);
        
        // Récupérer les retenues
        $sqlRetenues = "SELECT br.*, r.libelle, r.type_retenue 
                        FROM bulletin_retenue br
                        INNER JOIN retenue r ON br.id_retenue = r.id_retenue
                        WHERE br.id_bulletin = :id";
        $bulletin['retenues'] = Database::query($sqlRetenues, ['id' => $id]);
        
        return $bulletin;
    }
    
    /**
     * Ajouter une prime à un bulletin
     */
    public function addPrime(int $idBulletin, int $idPrime, float $montant, string $remarque = ''): bool {
        $sql = "INSERT INTO bulletin_prime (id_bulletin, id_prime, montant, remarque) 
                VALUES (:id_bulletin, :id_prime, :montant, :remarque)";
        return Database::execute($sql, [
            'id_bulletin' => $idBulletin,
            'id_prime' => $idPrime,
            'montant' => $montant,
            'remarque' => $remarque
        ]) !== false;
    }
    
    /**
     * Ajouter une retenue à un bulletin
     */
    public function addRetenue(int $idBulletin, int $idRetenue, float $montant, string $remarque = ''): bool {
        $sql = "INSERT INTO bulletin_retenue (id_bulletin, id_retenue, montant, remarque) 
                VALUES (:id_bulletin, :id_retenue, :montant, :remarque)";
        return Database::execute($sql, [
            'id_bulletin' => $idBulletin,
            'id_retenue' => $idRetenue,
            'montant' => $montant,
            'remarque' => $remarque
        ]) !== false;
    }
    
    /**
     * Compter les bulletins pour un mois/année
     */
    public function countByMois(int $mois, int $annee): int {
        $sql = "SELECT COUNT(*) as count FROM bulletin WHERE mois = :mois AND annee = :annee";
        $result = Database::fetchOne($sql, ['mois' => $mois, 'annee' => $annee]);
        return (int)($result['count'] ?? 0);
    }
    
    /**
     * Obtenir les bulletins pour un mois/année
     */
    public function getByMois(int $mois, int $annee): array {
        $sql = "SELECT b.*, e.matricule, e.nom, e.prenom, p.libelle AS poste
                FROM bulletin b
                INNER JOIN employe e ON b.id_employe = e.id_employe
                LEFT JOIN poste p ON e.id_poste = p.id_poste
                WHERE b.mois = :mois AND b.annee = :annee
                ORDER BY b.date_generation DESC";
        return Database::query($sql, ['mois' => $mois, 'annee' => $annee]);
    }
    
    /**
     * Statistiques par département pour un mois/année
     */
    public function statsParDepartement(int $mois, int $annee): array {
        $sql = "SELECT d.libelle AS departement, 
                       COUNT(DISTINCT b.id_bulletin) AS nb_bulletins,
                       SUM(b.salaire_net) AS total_salaire_net,
                       AVG(b.salaire_net) AS moyenne_salaire_net
                FROM bulletin b
                INNER JOIN employe e ON b.id_employe = e.id_employe
                INNER JOIN poste p ON e.id_poste = p.id_poste
                INNER JOIN departement d ON p.id_departement = d.id_departement
                WHERE b.mois = :mois AND b.annee = :annee AND b.statut IN ('valide', 'paye')
                GROUP BY d.id_departement, d.libelle
                ORDER BY total_salaire_net DESC";
        return Database::query($sql, ['mois' => $mois, 'annee' => $annee]);
    }
    
    /**
     * Obtenir tous les bulletins d'un employé
     */
    public function getByEmploye(int $idEmploye): array {
        $sql = "SELECT b.*, mp.libelle AS mode_paiement
                FROM bulletin b
                LEFT JOIN mode_paiement mp ON b.id_mode_paiement = mp.id_mode
                WHERE b.id_employe = :id_employe
                ORDER BY b.annee DESC, b.mois DESC";
        return Database::query($sql, ['id_employe' => $idEmploye]);
    }
}
?>