<?php
/**
 * ============================================================================
 * SMARTPAYROLL - Configuration de la Base de Données
 * ============================================================================
 * 
 * Paramètres de connexion à MySQL
 * 
 * @author [Ton nom] - Étudiante BTS Génie Logiciel
 * @establishment Institut Supérieur Mony Keng (ISMK)
 * @project Stage BTS - Digitalisation de la gestion des salaires
 * @version 1.0
 * @date Février 2026
 * ============================================================================
 */

return [
    'host' => 'localhost',
    'dbname' => 'payroll_db',
    'username' => 'root',
    'password' => '',  // Mettre ton mot de passe MySQL si nécessaire
    
    'charset' => 'utf8mb4',
    
    'options' => [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci"
    ]
];
?>