<?php
namespace App\Models;
use App\Core\Database;

class ModePaiement {
    public function getAll(): array {
        return Database::query("SELECT * FROM mode_paiement ORDER BY libelle");
    }
    public function getById(int $id): ?array {
        return Database::fetchOne("SELECT * FROM mode_paiement WHERE id_mode = :id", ['id' => $id]);
    }
    public function create(array $data) {
        $sql = "INSERT INTO mode_paiement (libelle, description) VALUES (:libelle, :description)";
        $params = ['libelle' => $data['libelle'] ?? '', 'description' => $data['description'] ?? null];
        if (Database::execute($sql, $params) !== false) {
            return (int) Database::getInstance()->lastInsertId();
        }
        return false;
    }
    public function update(int $id, array $data): bool {
        $fields = []; $params = ['id' => $id];
        foreach (['libelle','description'] as $col) {
            if (array_key_exists($col, $data)) { $fields[] = "$col = :$col"; $params[$col] = $data[$col]; }
        }
        if (empty($fields)) return true;
        return Database::execute("UPDATE mode_paiement SET " . implode(', ', $fields) . " WHERE id_mode = :id", $params) !== false;
    }
    public function delete(int $id): bool {
        $count = (int) Database::fetchColumn("SELECT COUNT(*) FROM bulletin WHERE id_mode_paiement = :id", ['id' => $id]);
        if ($count > 0) return false;
        return Database::execute("DELETE FROM mode_paiement WHERE id_mode = :id", ['id' => $id]) !== false;
    }
}
