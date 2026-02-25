<?php
namespace App\Models;
use App\Core\Database;

class Bulletin {
    public function getByEmploye(int $idEmploye): array {
        return Database::query("
            SELECT b.*, mp.libelle AS mode_paiement 
            FROM bulletin b 
            LEFT JOIN mode_paiement mp ON b.id_mode_paiement = mp.id_mode 
            WHERE b.id_employe = :id 
            ORDER BY b.annee DESC, b.mois DESC 
            LIMIT 50
        ", ['id' => $idEmploye]);
    }
    public function getByIdAndEmploye(int $idBulletin, int $idEmploye): ?array {
        return Database::fetchOne("
            SELECT b.*, mp.libelle AS mode_paiement,
                   CONCAT(e.prenom, ' ', e.nom) AS employe_nom, e.matricule, e.type_contrat,
                   p.libelle AS poste, d.libelle AS departement
            FROM bulletin b 
            LEFT JOIN mode_paiement mp ON b.id_mode_paiement = mp.id_mode
            LEFT JOIN employe e ON b.id_employe = e.id_employe
            LEFT JOIN poste p ON e.id_poste = p.id_poste
            LEFT JOIN departement d ON p.id_departement = d.id_departement
            WHERE b.id_bulletin = :id AND b.id_employe = :id_employe
        ", ['id' => $idBulletin, 'id_employe' => $idEmploye]);
    }
    public function countByEmploye(int $idEmploye): int {
        return (int) Database::fetchColumn("SELECT COUNT(*) FROM bulletin WHERE id_employe = :id", ['id' => $idEmploye]);
    }
}
