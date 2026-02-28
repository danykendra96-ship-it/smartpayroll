<?php
namespace App\Models;

use App\Core\Database;

class Conge {
    public function getByEmploye(int $idEmploye): array {
        $sql = "SELECT * FROM conge WHERE id_employe = :id_employe ORDER BY date_demande DESC";
        return Database::query($sql, ['id_employe' => $idEmploye]);
    }
    
    public function create(array $data): ?int {
        $sql = "INSERT INTO conge (id_employe, date_demande, date_debut, date_fin, nb_jours, type_conge, statut, motif)
                VALUES (:id_employe, NOW(), :date_debut, :date_fin, :nb_jours, :type_conge, 'en_attente', :motif)";
        if (Database::execute($sql, $data)) {
            return (int)Database::getInstance()->lastInsertId();
        }
        return null;
    }
    
    public function cancel(int $idConge, int $idEmploye): bool {
        $sql = "UPDATE conge SET statut = 'annule' WHERE id_conge = :id_conge AND id_employe = :id_employe AND statut = 'en_attente'";
        return Database::execute($sql, ['id_conge' => $idConge, 'id_employe' => $idEmploye]) !== false;
    }
    
    public function countByStatut(int $idEmploye, string $statut): int {
        $sql = "SELECT COUNT(*) as count FROM conge WHERE id_employe = :id_employe AND statut = :statut";
        $result = Database::fetchOne($sql, ['id_employe' => $idEmploye, 'statut' => $statut]);
        return (int)($result['count'] ?? 0);
    }
    
    public function getSoldeConges(int $idEmploye): int {
        $sql = "SELECT solde_conges FROM employe WHERE id_employe = :id_employe";
        $result = Database::fetchOne($sql, ['id_employe' => $idEmploye]);
        return (int)($result['solde_conges'] ?? 0);
    }
}
?>