<?php
/**
 * SMARTPAYROLL - Modèle Poste
 * Gestion CRUD des postes
 */

namespace App\Models;

use App\Core\Database;

class Poste {

    public function getAll(): array {
        return Database::query("SELECT p.*, d.libelle AS departement 
            FROM poste p 
            LEFT JOIN departement d ON p.id_departement = d.id_departement 
            ORDER BY d.libelle, p.libelle");
    }

    public function getById(int $id): ?array {
        return Database::fetchOne("SELECT p.*, d.libelle AS departement 
            FROM poste p 
            LEFT JOIN departement d ON p.id_departement = d.id_departement 
            WHERE p.id_poste = :id", ['id' => $id]);
    }

    public function getByDepartement(int $idDepartement): array {
        return Database::query("SELECT * FROM poste WHERE id_departement = :id ORDER BY libelle", ['id' => $idDepartement]);
    }

    public function create(array $data) {
        $params = [
            'id_departement' => $data['id_departement'] ?? 1,
            'libelle' => $data['libelle'] ?? ''
        ];
        $sql = "INSERT INTO poste (id_departement, libelle) VALUES (:id_departement, :libelle)";
        if (Database::execute($sql, $params) !== false) {
            return (int) Database::getInstance()->lastInsertId();
        }
        return false;
    }

    public function update(int $id, array $data): bool {
        $fields = [];
        $params = ['id' => $id];
        $allowed = ['id_departement', 'libelle', 'salaire_base', 'description'];
        foreach ($allowed as $col) {
            if (array_key_exists($col, $data)) {
                $fields[] = "$col = :$col";
                $params[$col] = $data[$col];
            }
        }
        if (empty($fields)) return true;
        $sql = "UPDATE poste SET " . implode(', ', $fields) . " WHERE id_poste = :id";
        return Database::execute($sql, $params) !== false;
    }

    public function delete(int $id): bool {
        $count = (int) Database::fetchColumn("SELECT COUNT(*) FROM employe WHERE id_poste = :id", ['id' => $id]);
        if ($count > 0) return false;
        return Database::execute("DELETE FROM poste WHERE id_poste = :id", ['id' => $id]) !== false;
    }
}
