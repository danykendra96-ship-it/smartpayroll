<?php
/**
 * ============================================================================
 * SMARTPAYROLL - Contrôleur Comptable
 * ============================================================================
 * 
 * Gère toutes les actions du comptable :
 * - Dashboard
 * - Gestion des bulletins de paie
 * - Calcul des salaires
 * - Génération PDF
 * - Rapports
 * 
 * @author [Ton nom] - Étudiante BTS Génie Logiciel
 * @establishment Institut Supérieur Mony Keng (ISMK)
 * @project Stage BTS - Digitalisation de la gestion des salaires
 * @version 1.0
 * @date Février 2026
 * ============================================================================
 */

namespace App\Controllers;

use App\Core\Session;
use App\Core\Database;
use App\Models\Employe;

require_once __DIR__ . '/../core/Database.php';
require_once __DIR__ . '/../core/Session.php';
require_once __DIR__ . '/../models/Employe.php';

class ComptableController {
    
    private Employe $employeModel;
    
    public function __construct() {
        Session::start();
        Session::requireRole(['admin', 'comptable']);
        $this->employeModel = new Employe();
    }
    
    /**
     * Afficher le dashboard comptable
     */
    public function dashboard(): void {
        // Récupérer les statistiques de paie
        $stats = [
            'bulletins_brouillon' => 12,
            'bulletins_valides' => 28,
            'total_a_payer' => 12450000,
            'bulletins_annules' => 2
        ];
        
        // Récupérer les bulletins en attente
        $bulletinsEnAttente = [
            [
                'id' => 1,
                'employe' => 'Claire Rakotomalala',
                'matricule' => 'EMP005',
                'mois' => 'Décembre 2024',
                'salaire_net' => 485000,
                'statut' => 'brouillon',
                'date_creation' => '15/12/2024'
            ],
            [
                'id' => 2,
                'employe' => 'Paul Tiana',
                'matricule' => 'EMP004',
                'mois' => 'Décembre 2024',
                'salaire_net' => 512000,
                'statut' => 'brouillon',
                'date_creation' => '15/12/2024'
            ],
            [
                'id' => 3,
                'employe' => 'Marie Andry',
                'matricule' => 'EMP003',
                'mois' => 'Décembre 2024',
                'salaire_net' => 750000,
                'statut' => 'brouillon',
                'date_creation' => '14/12/2024'
            ],
            [
                'id' => 4,
                'employe' => 'Tiana Razafy',
                'matricule' => 'VAC001',
                'mois' => 'Décembre 2024',
                'salaire_net' => 810000,
                'statut' => 'brouillon',
                'date_creation' => '16/12/2024'
            ]
        ];
        
        require_once __DIR__ . '/../views/comptable/dashboard.php';
    }
    
    /**
     * Afficher la liste des bulletins
     */
    public function listBulletins(): void {
        // À implémenter : récupérer tous les bulletins
        $bulletins = [];
        require_once __DIR__ . '/../views/comptable/list_bulletins.php';
    }
    
    /**
     * Afficher le formulaire de génération de bulletin
     */
    public function showGenererBulletin(): void {
        $idEmploye = $_GET['id'] ?? null;
        
        if ($idEmploye) {
            $employe = $this->employeModel->getById((int)$idEmploye);
            if (!$employe) {
                header('Location: /app/controllers/ComptableController.php?action=listBulletins&error=employe_non_trouve');
                exit();
            }
        } else {
            $employe = null;
        }
        
        // Récupérer tous les employés actifs
        $employes = $this->employeModel->getAllActive();
        
        require_once __DIR__ . '/../views/comptable/generer_bulletin.php';
    }
    
    /**
     * Traiter la génération d'un bulletin
     */
    public function genererBulletin(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /app/controllers/ComptableController.php?action=showGenererBulletin');
            exit();
        }
        
        // Validation des données
        $idEmploye = $_POST['id_employe'] ?? null;
        $mois = $_POST['mois'] ?? date('m');
        $annee = $_POST['annee'] ?? date('Y');
        
        if (!$idEmploye) {
            header('Location: /app/controllers/ComptableController.php?action=showGenererBulletin&error=employe_requis');
            exit();
        }
        
        // Récupérer les données de l'employé
        $employe = $this->employeModel->getById((int)$idEmploye);
        
        if (!$employe) {
            header('Location: /app/controllers/ComptableController.php?action=showGenererBulletin&error=employe_non_trouve');
            exit();
        }
        
        // Calculer le salaire selon le type de contrat
        if ($employe['type_contrat'] === 'Vacataire') {
            // Pour les vacataires : heures × taux horaire
            $heuresTravaillees = $_POST['heures_travaillees'] ?? 0;
            $heuresSup = $_POST['heures_sup'] ?? 0;
            $tauxHoraire = $employe['taux_horaire'] ?? 0;
            
            $salaireBase = $heuresTravaillees * $tauxHoraire;
            $montantHeuresSup = $heuresSup * $tauxHoraire * 1.5;
            $salaireBrut = $salaireBase + $montantHeuresSup;
        } else {
            // Pour les CDI/CDD : salaire de base du poste
            $salaireBase = $employe['salaire_base'] ?? 0;
            $salaireBrut = $salaireBase;
        }
        
        // Ajouter primes et retenues
        $primes = $_POST['primes'] ?? 0;
        $retenues = $_POST['retenues'] ?? 0;
        
        $salaireBrut += $primes;
        $salaireBrut -= $retenues;
        
        // Calculer les cotisations et impôts
        $cnss = $salaireBrut * 0.01; // 1% CNSS
        $irsa = $this->calculerIRSA($salaireBrut);
        
        // Calculer le salaire net
        $salaireNet = $salaireBrut - $cnss - $irsa;
        
        // À implémenter : enregistrer le bulletin en BDD
        // $bulletinModel->create([...]);
        
        header('Location: /app/controllers/ComptableController.php?action=listBulletins&success=bulletin_genere');
        exit();
    }
    
    /**
     * Calculer l'IRSA selon le barème malgache
     */
    private function calculerIRSA(float $salaireBrut): float {
        if ($salaireBrut <= 350000) {
            return 0; // Exonéré
        } elseif ($salaireBrut <= 400000) {
            return ($salaireBrut - 350000) * 0.05;
        } elseif ($salaireBrut <= 500000) {
            return 2500 + ($salaireBrut - 400000) * 0.10;
        } elseif ($salaireBrut <= 600000) {
            return 12500 + ($salaireBrut - 500000) * 0.15;
        } else {
            return 27500 + ($salaireBrut - 600000) * 0.20;
        }
    }
    
    /**
     * Afficher le formulaire de saisie des heures (vacataires)
     */
    public function showSaisieHeures(): void {
        $idEmploye = $_GET['id'] ?? null;
        
        if (!$idEmploye) {
            header('Location: /app/controllers/ComptableController.php?action=listBulletins&error=employe_requis');
            exit();
        }
        
        $employe = $this->employeModel->getById((int)$idEmploye);
        
        if (!$employe) {
            header('Location: /app/controllers/ComptableController.php?action=listBulletins&error=employe_non_trouve');
            exit();
        }
        
        // Vérifier que c'est bien un vacataire
        if ($employe['type_contrat'] !== 'Vacataire') {
            header('Location: /app/controllers/ComptableController.php?action=listBulletins&error=non_vacataire');
            exit();
        }
        
        require_once __DIR__ . '/../views/comptable/saisie_heures.php';
    }
    
    /**
     * Traiter la saisie des heures
     */
    public function saisieHeures(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /app/controllers/ComptableController.php?action=listBulletins');
            exit();
        }
        
        // À implémenter : traitement de la saisie des heures
        
        header('Location: /app/controllers/ComptableController.php?action=listBulletins&success=heures_saisies');
        exit();
    }
    
    /**
     * Valider un bulletin
     */
    public function validerBulletin(): void {
        $idBulletin = $_GET['id'] ?? null;
        
        if (!$idBulletin) {
            header('Location: /app/controllers/ComptableController.php?action=listBulletins&error=bulletin_invalide');
            exit();
        }
        
        // À implémenter : mettre à jour le statut du bulletin
        
        header('Location: /app/controllers/ComptableController.php?action=listBulletins&success=bulletin_valide');
        exit();
    }
    
    /**
     * Marquer un bulletin comme payé
     */
    public function marquerPaye(): void {
        $idBulletin = $_GET['id'] ?? null;
        
        if (!$idBulletin) {
            header('Location: /app/controllers/ComptableController.php?action=listBulletins&error=bulletin_invalide');
            exit();
        }
        
        // À implémenter : mettre à jour le statut du bulletin
        
        header('Location: /app/controllers/ComptableController.php?action=listBulletins&success=bulletin_paye');
        exit();
    }
    
    /**
     * Générer un PDF de bulletin
     */
    public function genererPDF(): void {
        $idBulletin = $_GET['id'] ?? null;
        
        if (!$idBulletin) {
            header('Location: /app/controllers/ComptableController.php?action=listBulletins&error=bulletin_invalide');
            exit();
        }
        
        // À implémenter : génération PDF avec TCPDF/FPDF
        
        // Exemple de génération PDF simple
        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="bulletin_' . $idBulletin . '.pdf"');
        
        echo "PDF du bulletin #" . $idBulletin . " - À implémenter avec TCPDF";
        
        exit();
    }
    
    /**
     * Afficher les rapports de paie
     */
    public function rapports(): void {
        // À implémenter
        require_once __DIR__ . '/../views/comptable/rapports.php';
    }
    
    /**
     * Exporter les données de paie
     */
    public function export(): void {
        $format = $_GET['format'] ?? 'pdf'; // pdf, excel, csv
        
        // À implémenter : export selon le format
        
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="export_paie.' . $format . '"');
        
        echo "Export des données de paie au format " . strtoupper($format) . " - À implémenter";
        
        exit();
    }
}

// -------------------------
// Handler pour appels directs (ex: /app/controllers/ComptableController.php?action=dashboard)
// -------------------------
try {
    $controller = new ComptableController();
    $action = $_GET['action'] ?? 'dashboard';

    switch ($action) {
        case 'dashboard':
            $controller->dashboard();
            break;
        case 'listBulletins':
            $controller->listBulletins();
            break;
        case 'showGenererBulletin':
            $controller->showGenererBulletin();
            break;
        case 'genererBulletin':
            $controller->genererBulletin();
            break;
        case 'showSaisieHeures':
            $controller->showSaisieHeures();
            break;
        case 'saisieHeures':
            $controller->saisieHeures();
            break;
        case 'validerBulletin':
            $controller->validerBulletin();
            break;
        case 'marquerPaye':
            $controller->marquerPaye();
            break;
        case 'genererPDF':
            $controller->genererPDF();
            break;
        case 'rapports':
            $controller->rapports();
            break;
        case 'export':
            $controller->export();
            break;
        default:
            $controller->dashboard();
            break;
    }
} catch (\Throwable $e) {
    error_log(sprintf("[ComptableController handler error] %s in %s on line %d\nTrace:\n%s",
        $e->getMessage(),
        $e->getFile(),
        $e->getLine(),
        $e->getTraceAsString()
    ));
    http_response_code(500);
    if (ini_get('display_errors')) {
        echo '<h3>Erreur serveur lors du traitement de la requête Comptable</h3>';
        echo '<pre>' . htmlspecialchars($e->getMessage() . "\n" . $e->getTraceAsString()) . '</pre>';
    } else {
        echo 'Erreur serveur.';
    }
}
?>