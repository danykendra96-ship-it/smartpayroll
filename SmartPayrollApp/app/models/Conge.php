<?php
namespace App\Models;
use App\Core\Database;

class Conge {
    public function getByEmploye(int $idEmploye): array {
        return Database::query("
            SELECT * FROM conge 
            WHERE id_employe = :id 
            ORDER BY date_debut DESC
        ", ['id' => $idEmploye]);
    }
    public function getByIdAndEmploye(int $idConge, int $idEmploye): ?array {
        return Database::fetchOne("SELECT * FROM conge WHERE id_conge = :id AND id_employe = :id_employe", ['id' => $idConge, 'id_employe' => $idEmploye]);
    }
    public function create(array $data) {
        $sql = "INSERT INTO conge (id_employe, date_debut, date_fin, nb_jours, type_conge, statut, motif) 
                VALUES (:id_employe, :date_debut, :date_fin, :nb_jours, :type_conge, 'en_attente', :motif)";
        $params = [
            'id_employe' => $data['id_employe'],
            'date_debut' => $data['date_debut'],
            'date_fin' => $data['date_fin'],
            'nb_jours' => (int)($data['nb_jours'] ?? 0),
            'type_conge' => $data['type_conge'] ?? 'annuel',
            'motif' => $data['motif'] ?? null
        ];
        if (Database::execute($sql, $params) !== false) {
            return (int) Database::getInstance()->lastInsertId();
        }
        return false;
    }
    public function annuler(int $idConge, int $idEmploye): bool {
        $c = $this->getByIdAndEmploye($idConge, $idEmploye);
        if (!$c || $c['statut'] !== 'en_attente') return false;
        return Database::execute("DELETE FROM conge WHERE id_conge = :id AND id_employe = :id_employe", ['id' => $idConge, 'id_employe' => $idEmploye]) !== false;
    }
}
