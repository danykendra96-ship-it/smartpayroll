-- Référence du schéma pour la gestion Entreprise, Département, Poste
-- Exécuter uniquement les lignes nécessaires (ignorer si la colonne existe déjà)

-- Entreprise : adresse, telephone, email
-- ALTER TABLE entreprise ADD COLUMN adresse VARCHAR(255) NULL;
-- ALTER TABLE entreprise ADD COLUMN telephone VARCHAR(50) NULL;
-- ALTER TABLE entreprise ADD COLUMN email VARCHAR(100) NULL;

-- Département : id_entreprise, description
-- ALTER TABLE departement ADD COLUMN id_entreprise INT DEFAULT 1;
-- ALTER TABLE departement ADD COLUMN description TEXT NULL;

-- Poste : salaire_base, description
-- ALTER TABLE poste ADD COLUMN salaire_base DECIMAL(12,2) NULL;
-- ALTER TABLE poste ADD COLUMN description TEXT NULL;
