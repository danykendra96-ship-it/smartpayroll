<?php
namespace App\Models;
use App\Core\Database;

class Entreprise {
    public function getAll(): array {
        return Database::query("SELECT * FROM entreprise ORDER BY nom");
    }
    public function getById(int $id): ?array {
        return Database::fetchOne("SELECT * FROM entreprise WHERE id_entreprise = :id", ['id' => $id]);
    }
    public function create(array $data) {
        $sql = "INSERT INTO entreprise (nom, adresse, telephone, email, nif, rc, cnss) VALUES (:nom, :adresse, :telephone, :email, :nif, :rc, :cnss)";
        $params = [
            'nom' => $data['nom'] ?? '',
            'adresse' => $data['adresse'] ?? '',
            'telephone' => $data['telephone'] ?? null,
            'email' => $data['email'] ?? null,
            'nif' => $data['nif'] ?? '',
            'rc' => $data['rc'] ?? null,
            'cnss' => $data['cnss'] ?? null
        ];
        if (Database::execute($sql, $params) !== false) {
            return (int)\App\Core\Database::getInstance()->lastInsertId();
        }
        return false;
    }
    public function update(int $id, array $data): bool {
        $fields = []; $params = ['id' => $id];
        foreach (['nom','adresse','telephone','email','nif','rc','cnss'] as $col) {
            if (array_key_exists($col, $data)) { $fields[] = "$col = :$col"; $params[$col] = $data[$col]; }
        }
        if (empty($fields)) return true;
        return Database::execute("UPDATE entreprise SET " . implode(', ', $fields) . " WHERE id_entreprise = :id", $params) !== false;
    }
    public function delete(int $id): bool {
        if ((int)Database::fetchColumn("SELECT COUNT(*) FROM employe WHERE id_entreprise = :id", ['id' => $id]) > 0) return false;
        return Database::execute("DELETE FROM entreprise WHERE id_entreprise = :id", ['id' => $id]) !== false;
    }
}
