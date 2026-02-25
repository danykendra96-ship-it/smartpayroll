<?php
namespace App\Models;
use App\Core\Database;

class Prime {
    public function getAll(): array {
        return Database::query("SELECT * FROM prime ORDER BY libelle");
    }
    public function getById(int $id): ?array {
        return Database::fetchOne("SELECT * FROM prime WHERE id_prime = :id", ['id' => $id]);
    }
    public function create(array $data) {
        $estFix = isset($data['est_fixe']) ? (int)(bool)$data['est_fixe'] : 1;
        $sql = "INSERT INTO prime (libelle, type_prime, montant, description, est_fixe) VALUES (:libelle, :type_prime, :montant, :description, :est_fixe)";
        $params = [
            'libelle' => $data['libelle'] ?? '',
            'type_prime' => $data['type_prime'] ?? 'autre',
            'montant' => (float)($data['montant'] ?? 0),
            'description' => $data['description'] ?? null,
            'est_fixe' => $estFix
        ];
        if (Database::execute($sql, $params) !== false) {
            return (int) Database::getInstance()->lastInsertId();
        }
        return false;
    }
    public function update(int $id, array $data): bool {
        $fields = []; $params = ['id' => $id];
        foreach (['libelle','type_prime','montant','description','est_fixe'] as $col) {
            if (array_key_exists($col, $data)) {
                $fields[] = "$col = :$col";
                $params[$col] = $col === 'montant' ? (float)$data[$col] : ($col === 'est_fixe' ? (int)(bool)$data[$col] : $data[$col]);
            }
        }
        if (empty($fields)) return true;
        return Database::execute("UPDATE prime SET " . implode(', ', $fields) . " WHERE id_prime = :id", $params) !== false;
    }
    public function delete(int $id): bool {
        $count = (int) Database::fetchColumn("SELECT COUNT(*) FROM bulletin_prime WHERE id_prime = :id", ['id' => $id]);
        if ($count > 0) return false;
        return Database::execute("DELETE FROM prime WHERE id_prime = :id", ['id' => $id]) !== false;
    }
}
