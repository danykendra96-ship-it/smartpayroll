<?php
namespace App\Models;
use App\Core\Database;

class Retenue {
    public function getAll(): array {
        return Database::query("SELECT * FROM retenue ORDER BY libelle");
    }
    public function getById(int $id): ?array {
        return Database::fetchOne("SELECT * FROM retenue WHERE id_retenue = :id", ['id' => $id]);
    }
    public function create(array $data) {
        $sql = "INSERT INTO retenue (libelle, type_retenue, montant, description, est_fixe) VALUES (:libelle, :type_retenue, :montant, :description, :est_fixe)";
        $params = [
            'libelle' => $data['libelle'] ?? '',
            'type_retenue' => $data['type_retenue'] ?? 'autre',
            'montant' => (float)($data['montant'] ?? 0),
            'description' => $data['description'] ?? null,
            'est_fixe' => isset($data['est_fixe']) ? (bool)$data['est_fixe'] : true
        ];
        if (Database::execute($sql, $params) !== false) {
            return (int) Database::getInstance()->lastInsertId();
        }
        return false;
    }
    public function update(int $id, array $data): bool {
        $fields = []; $params = ['id' => $id];
        foreach (['libelle','type_retenue','montant','description','est_fixe'] as $col) {
            if (array_key_exists($col, $data)) {
                $fields[] = "$col = :$col";
                $params[$col] = $col === 'montant' ? (float)$data[$col] : ($col === 'est_fixe' ? (int)(bool)$data[$col] : $data[$col]);
            }
        }
        if (empty($fields)) return true;
        return Database::execute("UPDATE retenue SET " . implode(', ', $fields) . " WHERE id_retenue = :id", $params) !== false;
    }
    public function delete(int $id): bool {
        $count = (int) Database::fetchColumn("SELECT COUNT(*) FROM bulletin_retenue WHERE id_retenue = :id", ['id' => $id]);
        if ($count > 0) return false;
        return Database::execute("DELETE FROM retenue WHERE id_retenue = :id", ['id' => $id]) !== false;
    }
}
