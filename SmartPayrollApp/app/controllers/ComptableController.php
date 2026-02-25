<?php
/**
 * ============================================================================
 * SMARTPAYROLL - Contrôleur Comptable
 * ============================================================================
 * 
 * Gère toutes les actions du comptable :
 * - Dashboard avec statistiques paie
 * - Gestion des bulletins de paie (CRUD)
 * - Calculs salariaux (CNSS, IRSA, heures sup)
 * - Génération PDF
 * - Rapports et exports
 * 
 * @author [Ton nom] - Étudiante BTS Génie Logiciel
 * @establishment Institut Supérieur Mony Keng (ISMK)
 * @project Stage BTS - Digitalisation de la gestion des salaires
 * @version 1.0
 * @date Février 2026
 * ============================================================================
 */

// ✅ DÉSACTIVER LE NAMESPACE POUR ÉVITER LES CONFLITS (comme dans ton AdminController)
// namespace App\Controllers;

require_once __DIR__ . '/../core/Session.php';
require_once __DIR__ . '/../core/Database.php';
require_once __DIR__ . '/../models/Employe.php';
require_once __DIR__ . '/../models/Bulletin.php';
require_once __DIR__ . '/../models/Poste.php';
require_once __DIR__ . '/../models/Prime.php';
require_once __DIR__ . '/../models/Retenue.php';

class ComptableController {
    
    private $employeModel;
    private $bulletinModel;
    private $posteModel;
    private $primeModel;
    private $retenueModel;
    
    public function __construct() {
        \App\Core\Session::start();
        \App\Core\Session::requireRole(['admin', 'comptable']);
        $this->employeModel = new \App\Models\Employe();
        $this->bulletinModel = new \App\Models\Bulletin();
        $this->posteModel = new \App\Models\Poste();
        $this->primeModel = new \App\Models\Prime();
        $this->retenueModel = new \App\Models\Retenue();
    }
    
    /**
     * Dashboard Comptable - Statistiques et actions rapides
     */
    public function dashboard(): void {
        // Statistiques du mois en cours
        $mois = date('m');
        $annee = date('Y');
        
        $stats = [
            'bulletins_brouillon' => $this->bulletinModel->countByStatut('brouillon', $mois, $annee),
            'bulletins_valides' => $this->bulletinModel->countByStatut('valide', $mois, $annee),
            'bulletins_payes' => $this->bulletinModel->countByStatut('paye', $mois, $annee),
            'total_a_payer' => $this->bulletinModel->sumSalaireNetMois($mois, $annee),
            'employes_actifs' => $this->employeModel->countActive(),
            'vacataires_ce_mois' => $this->employeModel->countByTypeContrat('Vacataire')
        ];
        
        // Bulletins en attente de validation
        $bulletinsEnAttente = $this->bulletinModel->getByStatut('brouillon', $mois, $annee);
        
        // Dernières activités (simulées pour l'instant)
        $recentActivity = [
            ['type' => 'bulletin', 'description' => 'Bulletin généré pour Marie Andry', 'date' => '2024-12-15 14:30'],
            ['type' => 'validation', 'description' => '5 bulletins validés', 'date' => '2024-12-14 16:45'],
            ['type' => 'conge', 'description' => 'Congé approuvé pour Paul Tiana', 'date' => '2024-12-13 10:20']
        ];
        
        require_once __DIR__ . '/../views/comptable/dashboard.php';
    }
    
    /**
     * Liste de tous les bulletins avec filtres
     */
    public function listBulletins(): void {
        $mois = $_GET['mois'] ?? date('m');
        $annee = $_GET['annee'] ?? date('Y');
        $statut = $_GET['statut'] ?? 'all';
        $search = $_GET['search'] ?? '';
        
        $bulletins = $this->bulletinModel->search($mois, $annee, $statut, $search);
        
        require_once __DIR__ . '/../views/comptable/list_bulletins.php';
    }
    
    /**
     * Afficher le formulaire de génération de bulletin (CDI/CDD)
     */
    public function showGenererBulletin(): void {
        $idEmploye = $_GET['id'] ?? null;
        $mois = $_GET['mois'] ?? date('m');
        $annee = $_GET['annee'] ?? date('Y');
        
        // Récupérer l'employé s'il est spécifié
        $employe = $idEmploye ? $this->employeModel->getById((int)$idEmploye) : null;
        
        // Récupérer tous les employés actifs CDI/CDD (pas les vacataires/stagiaires pour ce formulaire)
        $employes = $this->employeModel->getActifsCDI_CDD();
        
        // Récupérer les primes et retenues standards
        $primesStandards = $this->primeModel->getAll();
        $retenuesStandards = $this->retenueModel->getAll();
        
        require_once __DIR__ . '/../views/comptable/generer_bulletin.php';
    }
    
    /**
     * Traiter la génération d'un bulletin (CDI/CDD)
     */
    public function genererBulletin(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /SmartPayrollApp/app/controllers/ComptableController.php?action=showGenererBulletin');
            exit();
        }
        
        // Validation des données
        $required = ['id_employe', 'mois', 'annee', 'salaire_base'];
        foreach ($required as $field) {
            if (empty($_POST[$field])) {
                header('Location: /SmartPayrollApp/app/controllers/ComptableController.php?action=showGenererBulletin&error=champs_vides');
                exit();
            }
        }
        
        $idEmploye = (int)$_POST['id_employe'];
        $mois = (int)$_POST['mois'];
        $annee = (int)$_POST['annee'];
        
        // Vérifier si le bulletin existe déjà pour ce mois/année
        if ($this->bulletinModel->existsForEmploye($idEmploye, $mois, $annee)) {
            header('Location: /SmartPayrollApp/app/controllers/ComptableController.php?action=showGenererBulletin&error=bulletin_existe');
            exit();
        }
        
        // Récupérer l'employé
        $employe = $this->employeModel->getById($idEmploye);
        if (!$employe) {
            header('Location: /SmartPayrollApp/app/controllers/ComptableController.php?action=showGenererBulletin&error=employe_invalide');
            exit();
        }
        
        // Calculer le salaire brut
        $salaireBase = (float)$_POST['salaire_base'];
        $primes = (float)($_POST['primes'] ?? 0);
        $retenues = (float)($_POST['retenues'] ?? 0);
        $heuresSup = (float)($_POST['heures_sup'] ?? 0);
        $tauxHeuresSup = (float)($_POST['taux_heures_sup'] ?? 1.5);
        
        $montantHeuresSup = $heuresSup * $salaireBase / 173.33 * $tauxHeuresSup; // 173.33h = moyenne mensuelle
        $salaireBrut = $salaireBase + $primes + $montantHeuresSup - $retenues;
        
        // Calculer les cotisations et impôts
        $cnss = $this->calculerCNSS($salaireBrut);
        $irsa = $this->calculerIRSA($salaireBrut);
        $salaireNet = $salaireBrut - $cnss - $irsa;
        
        // Préparer les données du bulletin
        $data = [
            'id_employe' => $idEmploye,
            'id_mode_paiement' => (int)($_POST['id_mode_paiement'] ?? 1),
            'mois' => $mois,
            'annee' => $annee,
            'salaire_base' => $salaireBase,
            'heures_travaillees' => 173.33, // Standard CDI
            'heures_sup' => $heuresSup,
            'montant_heures_sup' => $montantHeuresSup,
            'primes_total' => $primes,
            'retenues_total' => $retenues,
            'cotisations_cnss' => $cnss,
            'cotisations_autres' => 0,
            'impots_irsa' => $irsa,
            'salaire_brut' => $salaireBrut,
            'salaire_net' => $salaireNet,
            'statut' => 'brouillon',
            'remarques' => $_POST['remarques'] ?? null
        ];
        
        // Créer le bulletin
        $idBulletin = $this->bulletinModel->create($data);
        
        if ($idBulletin) {
            // Ajouter les primes spécifiques si sélectionnées
            if (!empty($_POST['prime_ids'])) {
                foreach ($_POST['prime_ids'] as $idPrime) {
                    $prime = $this->primeModel->getById((int)$idPrime);
                    if ($prime) {
                        $this->bulletinModel->addPrime($idBulletin, $idPrime, $prime['montant'], 'Prime standard');
                    }
                }
            }
            
            // Ajouter les retenues spécifiques si sélectionnées
            if (!empty($_POST['retenue_ids'])) {
                foreach ($_POST['retenue_ids'] as $idRetenue) {
                    $retenue = $this->retenueModel->getById((int)$idRetenue);
                    if ($retenue) {
                        $this->bulletinModel->addRetenue($idBulletin, $idRetenue, $retenue['montant'], 'Retenue standard');
                    }
                }
            }
            
            header('Location: /SmartPayrollApp/app/controllers/ComptableController.php?action=listBulletins&success=bulletin_genere');
        } else {
            header('Location: /SmartPayrollApp/app/controllers/ComptableController.php?action=showGenererBulletin&error=erreur_generation');
        }
        
        exit();
    }
    
    /**
     * Afficher le formulaire de saisie des heures (Vacataires)
     */
    public function showSaisieHeures(): void {
        $idEmploye = $_GET['id'] ?? null;
        $mois = $_GET['mois'] ?? date('m');
        $annee = $_GET['annee'] ?? date('Y');
        
        // Récupérer les vacataires actifs
        $vacataires = $this->employeModel->getActifsByType('Vacataire');
        
        // Récupérer le vacataire sélectionné
        $vacataire = $idEmploye ? $this->employeModel->getById((int)$idEmploye) : null;
        
        if ($vacataire && $vacataire['type_contrat'] !== 'Vacataire') {
            header('Location: /SmartPayrollApp/app/controllers/ComptableController.php?action=showSaisieHeures&error=non_vacataire');
            exit();
        }
        
        require_once __DIR__ . '/../views/comptable/saisie_heures.php';
    }
    
    /**
     * Traiter la saisie des heures pour un vacataire
     */
    public function saisieHeures(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /SmartPayrollApp/app/controllers/ComptableController.php?action=showSaisieHeures');
            exit();
        }
        
        $required = ['id_employe', 'mois', 'annee', 'heures_travaillees', 'taux_horaire'];
        foreach ($required as $field) {
            if (empty($_POST[$field])) {
                header('Location: /SmartPayrollApp/app/controllers/ComptableController.php?action=showSaisieHeures&error=champs_vides');
                exit();
            }
        }
        
        $idEmploye = (int)$_POST['id_employe'];
        $mois = (int)$_POST['mois'];
        $annee = (int)$_POST['annee'];
        
        // Vérifier si bulletin existe déjà
        if ($this->bulletinModel->existsForEmploye($idEmploye, $mois, $annee)) {
            header('Location: /SmartPayrollApp/app/controllers/ComptableController.php?action=showSaisieHeures&error=bulletin_existe');
            exit();
        }
        
        // Récupérer l'employé
        $employe = $this->employeModel->getById($idEmploye);
        if (!$employe || $employe['type_contrat'] !== 'Vacataire') {
            header('Location: /SmartPayrollApp/app/controllers/ComptableController.php?action=showSaisieHeures&error=non_vacataire');
            exit();
        }
        
        // Calculer le salaire
        $heuresTravaillees = (float)$_POST['heures_travaillees'];
        $heuresSup = (float)($_POST['heures_sup'] ?? 0);
        $tauxHoraire = (float)$_POST['taux_horaire'];
        $tauxHeuresSup = (float)($_POST['taux_heures_sup'] ?? 1.5);
        
        $salaireNormal = $heuresTravaillees * $tauxHoraire;
        $salaireHeuresSup = $heuresSup * $tauxHoraire * $tauxHeuresSup;
        $salaireBrut = $salaireNormal + $salaireHeuresSup;
        
        // Calculer cotisations et impôts
        $cnss = $this->calculerCNSS($salaireBrut);
        $irsa = $this->calculerIRSA($salaireBrut);
        $salaireNet = $salaireBrut - $cnss - $irsa;
        
        // Créer le bulletin
        $data = [
            'id_employe' => $idEmploye,
            'id_mode_paiement' => (int)($_POST['id_mode_paiement'] ?? 1),
            'mois' => $mois,
            'annee' => $annee,
            'salaire_base' => 0, // Pas de salaire de base pour les vacataires
            'heures_travaillees' => $heuresTravaillees,
            'heures_sup' => $heuresSup,
            'montant_heures_sup' => $salaireHeuresSup,
            'primes_total' => 0,
            'retenues_total' => 0,
            'cotisations_cnss' => $cnss,
            'cotisations_autres' => 0,
            'impots_irsa' => $irsa,
            'salaire_brut' => $salaireBrut,
            'salaire_net' => $salaireNet,
            'statut' => 'brouillon',
            'remarques' => $_POST['remarques'] ?? 'Bulletin vacataire - ' . $heuresTravaillees . 'h normales + ' . $heuresSup . 'h sup'
        ];
        
        $idBulletin = $this->bulletinModel->create($data);
        
        if ($idBulletin) {
            header('Location: /SmartPayrollApp/app/controllers/ComptableController.php?action=listBulletins&success=bulletin_vacataire_genere');
        } else {
            header('Location: /SmartPayrollApp/app/controllers/ComptableController.php?action=showSaisieHeures&error=erreur_generation');
        }
        
        exit();
    }
    
    /**
     * Valider un bulletin (passer de brouillon à validé)
     */
    public function validerBulletin(): void {
        $idBulletin = $_GET['id'] ?? null;
        
        if (!$idBulletin) {
            header('Location: /SmartPayrollApp/app/controllers/ComptableController.php?action=listBulletins&error=bulletin_invalide');
            exit();
        }
        
        $bulletin = $this->bulletinModel->getById((int)$idBulletin);
        if (!$bulletin || $bulletin['statut'] !== 'brouillon') {
            header('Location: /SmartPayrollApp/app/controllers/ComptableController.php?action=listBulletins&error=statut_invalide');
            exit();
        }
        
        if ($this->bulletinModel->updateStatut((int)$idBulletin, 'valide')) {
            header('Location: /SmartPayrollApp/app/controllers/ComptableController.php?action=listBulletins&success=bulletin_valide');
        } else {
            header('Location: /SmartPayrollApp/app/controllers/ComptableController.php?action=listBulletins&error=erreur_validation');
        }
        
        exit();
    }
    
    /**
     * Marquer un bulletin comme payé
     */
    public function marquerPaye(): void {
        $idBulletin = $_GET['id'] ?? null;
        
        if (!$idBulletin) {
            header('Location: /SmartPayrollApp/app/controllers/ComptableController.php?action=listBulletins&error=bulletin_invalide');
            exit();
        }
        
        $bulletin = $this->bulletinModel->getById((int)$idBulletin);
        if (!$bulletin || !in_array($bulletin['statut'], ['brouillon', 'valide'])) {
            header('Location: /SmartPayrollApp/app/controllers/ComptableController.php?action=listBulletins&error=statut_invalide');
            exit();
        }
        
        $data = [
            'statut' => 'paye',
            'date_paiement' => date('Y-m-d')
        ];
        
        if ($this->bulletinModel->update((int)$idBulletin, $data)) {
            header('Location: /SmartPayrollApp/app/controllers/ComptableController.php?action=listBulletins&success=bulletin_paye');
        } else {
            header('Location: /SmartPayrollApp/app/controllers/ComptableController.php?action=listBulletins&error=erreur_paiement');
        }
        
        exit();
    }
    
    /**
     * Générer le PDF d'un bulletin (version HTML temporaire)
     */
    public function genererPDF(): void {
        $idBulletin = $_GET['id'] ?? null;
        
        if (!$idBulletin) {
            header('Location: /SmartPayrollApp/app/controllers/ComptableController.php?action=listBulletins&error=bulletin_invalide');
            exit();
        }
        
        $bulletin = $this->bulletinModel->getCompleteById((int)$idBulletin);
        if (!$bulletin) {
            header('Location: /SmartPayrollApp/app/controllers/ComptableController.php?action=listBulletins&error=bulletin_non_trouve');
            exit();
        }
        
        // Générer le PDF (version simplifiée - à remplacer par TCPDF en production)
        $filename = 'bulletin_' . $bulletin['matricule'] . '_' . $bulletin['mois'] . '_' . $bulletin['annee'] . '.pdf';
        $filepath = $_SERVER['DOCUMENT_ROOT'] . '/SmartPayrollApp/public/uploads/bulletins/' . $filename;
        
        // Créer le dossier si nécessaire
        if (!is_dir(dirname($filepath))) {
            mkdir(dirname($filepath), 0777, true);
        }
        
        // Générer le HTML du bulletin
        $html = $this->genererHTMLBulletin($bulletin);
        
        // Sauvegarder le HTML comme "PDF" temporaire
        file_put_contents($filepath, $html);
        
        // Mettre à jour le chemin dans la BDD
        $this->bulletinModel->update((int)$idBulletin, ['pdf_path' => 'uploads/bulletins/' . $filename]);
        
        // Télécharger le fichier
        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Length: ' . filesize($filepath));
        readfile($filepath);
        exit();
    }
    
    /**
     * Afficher les rapports de paie
     */
    public function rapports(): void {
        $mois = $_GET['mois'] ?? date('m');
        $annee = $_GET['annee'] ?? date('Y');
        
        $stats = [
            'total_bulletins' => $this->bulletinModel->countByMois($mois, $annee),
            'total_paye' => $this->bulletinModel->sumSalaireNetMois($mois, $annee),
            'moyenne_salaire' => $this->bulletinModel->avgSalaireNetMois($mois, $annee),
            'par_departement' => $this->bulletinModel->statsParDepartement($mois, $annee)
        ];
        
        require_once __DIR__ . '/../views/comptable/rapports.php';
    }
    
    // ==================== MÉTHODES PRIVÉES ====================
    
    /**
     * Calculer la CNSS (1% du salaire brut)
     */
    private function calculerCNSS(float $salaireBrut): float {
        return $salaireBrut * 0.01;
    }
    
    /**
     * Calculer l'IRSA selon le barème malgache
     */
    private function calculerIRSA(float $salaireBrut): float {
        if ($salaireBrut <= 350000) {
            return 0;
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
     * Générer le HTML du bulletin (version simplifiée pour débogage)
     */
    private function genererHTMLBulletin(array $bulletin): string {
        $moisFr = ['Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin', 'Juillet', 'Août', 'Septembre', 'Octobre', 'Novembre', 'Décembre'];
        
        return '<!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <title>Bulletin de Paie - ' . $bulletin['matricule'] . '</title>
            <style>
                body { font-family: Arial, sans-serif; margin: 40px; background: #f8fafc; }
                .container { max-width: 800px; margin: 0 auto; background: white; padding: 40px; border-radius: 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
                .header { text-align: center; border-bottom: 3px solid #2563eb; padding-bottom: 20px; margin-bottom: 30px; }
                .company { font-size: 24px; font-weight: bold; color: #1e293b; margin-bottom: 5px; }
                .address { color: #64748b; font-size: 14px; }
                .title { font-size: 22px; margin: 15px 0; color: #2563eb; }
                .infos { display: flex; justify-content: space-between; margin: 25px 0; }
                .employee-info, .period-info { width: 48%; }
                .table { width: 100%; border-collapse: collapse; margin: 25px 0; }
                .table th, .table td { border: 1px solid #e2e8f0; padding: 12px; text-align: right; }
                .table th { background: #f1f5f9; font-weight: bold; text-align: left; }
                .table tr:hover { background: #f8fafc; }
                .total-row { font-weight: bold; background: #dbeafe !important; }
                .footer { margin-top: 40px; text-align: center; font-size: 14px; color: #64748b; padding-top: 20px; border-top: 1px solid #e2e8f0; }
                .badge { display: inline-block; padding: 4px 10px; border-radius: 20px; font-size: 12px; font-weight: 600; }
                .badge-brouillon { background: #fee2e2; color: #991b1b; }
                .badge-valide { background: #dcfce7; color: #166534; }
                .badge-paye { background: #dbeafe; color: #1e40af; }
            </style>
        </head>
        <body>
            <div class="container">
                <div class="header">
                    <div class="company">Institut Supérieur Mony Keng</div>
                    <div class="address">Lot IV G 45 Bis, Ivandry, Antananarivo, Madagascar</div>
                    <div class="title">BULLETIN DE PAIE</div>
                </div>
                
                <div class="infos">
                    <div class="employee-info">
                        <strong>Employé :</strong> ' . $bulletin['prenom'] . ' ' . $bulletin['nom'] . '<br>
                        <strong>Matricule :</strong> ' . $bulletin['matricule'] . '<br>
                        <strong>Poste :</strong> ' . $bulletin['poste'] . '<br>
                        <strong>Département :</strong> ' . $bulletin['departement'] . '
                    </div>
                    <div class="period-info">
                        <strong>Période :</strong> ' . $moisFr[$bulletin['mois']-1] . ' ' . $bulletin['annee'] . '<br>
                        <strong>Date génération :</strong> ' . date('d/m/Y') . '<br>
                        <strong>Statut :</strong> <span class="badge badge-' . $bulletin['statut'] . '">' . ucfirst($bulletin['statut']) . '</span>
                    </div>
                </div>
                
                <table class="table">
                    <tr>
                        <th>Description</th>
                        <th>Montant (Ar)</th>
                    </tr>
                    <tr>
                        <td>Salaire de base</td>
                        <td>' . number_format($bulletin['salaire_base'], 0, ',', ' ') . '</td>
                    </tr>
                    <tr>
                        <td>Heures supplémentaires (' . $bulletin['heures_sup'] . 'h)</td>
                        <td>' . number_format($bulletin['montant_heures_sup'], 0, ',', ' ') . '</td>
                    </tr>
                    <tr>
                        <td>Primes</td>
                        <td>' . number_format($bulletin['primes_total'], 0, ',', ' ') . '</td>
                    </tr>
                    <tr class="total-row">
                        <td><strong>Salaire Brut</strong></td>
                        <td><strong>' . number_format($bulletin['salaire_brut'], 0, ',', ' ') . '</strong></td>
                    </tr>
                    <tr>
                        <td>CNSS (1%)</td>
                        <td>- ' . number_format($bulletin['cotisations_cnss'], 0, ',', ' ') . '</td>
                    </tr>
                    <tr>
                        <td>IRSA</td>
                        <td>- ' . number_format($bulletin['impots_irsa'], 0, ',', ' ') . '</td>
                    </tr>
                    <tr class="total-row">
                        <td><strong>Salaire Net à Payer</strong></td>
                        <td><strong>' . number_format($bulletin['salaire_net'], 0, ',', ' ') . '</strong></td>
                    </tr>
                </table>
                
                <div class="footer">
                    <p>Ce bulletin est édité informatiquement et ne nécessite pas de signature.</p>
                    <p>ISMK - SmartPayroll &copy; ' . date('Y') . ' - Bulletin confidentiel</p>
                </div>
            </div>
        </body>
        </html>';
    }
}

// Handler pour appels directs
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
        default:
            $controller->dashboard();
    }
} catch (\Throwable $e) {
    error_log("[ComptableController] " . $e->getMessage());
    http_response_code(500);
    echo '<h2 style="color: #ef4444;">Erreur serveur comptable</h2><pre>' . htmlspecialchars($e->getMessage()) . '</pre>';
}
?>