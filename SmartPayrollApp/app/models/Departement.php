<?php
/**
 * SMARTPAYROLL - Modèle Département
 * Gestion CRUD des départements
 */

namespace App\Models;

use App\Core\Database;

class Departement {

    public function getAll(): array {
        return Database::query("SELECT d.*, e.nom AS entreprise FROM departement d LEFT JOIN entreprise e ON d.id_entreprise = e.id_entreprise ORDER BY d.libelle");
    }

    public function getById(int $id): ?array {
        return Database::fetchOne("SELECT d.*, e.nom AS entreprise 
            FROM departement d 
            LEFT JOIN entreprise e ON d.id_entreprise = e.id_entreprise 
            WHERE d.id_departement = :id", ['id' => $id]);
    }

    public function getByEntreprise(int $idEntreprise): array {
        return Database::query("SELECT * FROM departement WHERE id_entreprise = :id ORDER BY libelle", ['id' => $idEntreprise]);
    }

    public function create(array $data) {
        $idEntreprise = $data['id_entreprise'] ?? 1;
        $sql = "INSERT INTO departement (id_entreprise, libelle, description) VALUES (:id_entreprise, :libelle, :description)";
        $params = [
            'id_entreprise' => $idEntreprise,
            'libelle' => $data['libelle'] ?? '',
            'description' => $data['description'] ?? null
        ];
        if (Database::execute($sql, $params) !== false) {
            return (int) Database::getInstance()->lastInsertId();
        }
        return false;
    }

    public function update(int $id, array $data): bool {
        $fields = [];
        $params = ['id' => $id];
        $allowed = ['id_entreprise', 'libelle', 'description'];
        foreach ($allowed as $col) {
            if (array_key_exists($col, $data)) {
                $fields[] = "$col = :$col";
                $params[$col] = $data[$col];
            }
        }
        if (empty($fields)) return true;
        $sql = "UPDATE departement SET " . implode(', ', $fields) . " WHERE id_departement = :id";
        return Database::execute($sql, $params) !== false;
    }

    public function delete(int $id): bool {
        $count = (int) Database::fetchColumn("SELECT COUNT(*) FROM poste WHERE id_departement = :id", ['id' => $id]);
        if ($count > 0) return false;
        return Database::execute("DELETE FROM departement WHERE id_departement = :id", ['id' => $id]) !== false;
    }
}
