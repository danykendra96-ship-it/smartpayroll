<?php
/**
 * ============================================================================
 * SMARTPAYROLL - Contrôleur Employé
 * ============================================================================
 * 
 * Gère l'espace personnel sécurisé de l'employé :
 * - Dashboard avec statistiques personnelles
 * - Consultation des bulletins (SEULEMENT les siens)
 * - Gestion du profil personnel
 * - Demandes de congés
 * 
 * @author [Ton nom] - Étudiante BTS Génie Logiciel
 * @establishment Institut Supérieur Mony Keng (ISMK)
 * @project Stage BTS - Digitalisation de la gestion des salaires
 * @version 1.0
 * @date Février 2026
 * ============================================================================
 */

// ✅ PAS DE NAMESPACE pour éviter les conflits
require_once __DIR__ . '/../core/Session.php';
require_once __DIR__ . '/../core/Database.php';
require_once __DIR__ . '/../models/Employe.php';
require_once __DIR__ . '/../models/Bulletin.php';

class EmployeController {
    
    private $employeModel;
    private $bulletinModel;
    
    public function __construct() {
        \App\Core\Session::start();
        \App\Core\Session::requireRole('employe');
        $this->employeModel = new \App\Models\Employe();
        $this->bulletinModel = new \App\Models\Bulletin();
    }
    
    /**
     * Dashboard Employé - Vue personnelle sécurisée
     */
    public function dashboard(): void {
        $userId = $_SESSION['user_id'] ?? null;
        if (!$userId) {
            header('Location: /SmartPayrollApp/public/login.php?error=session_expiree');
            exit();
        }
        
        // Statistiques personnelles
        $stats = [
            'dernier_salaire' => 485000,
            'solde_conges' => $_SESSION['user_solde_conges'] ?? 15,
            'nb_bulletins' => 12,
            'anciennete' => '2 ans'
        ];
        
        // Ses bulletins récents (MAX 6)
        $mesBulletins = $this->bulletinModel->getByEmploye($userId);
        $mesBulletins = array_slice($mesBulletins, 0, 6);
        
        // Ses congés récents
        $mesConges = $this->employeModel->getCongesByEmploye($userId);
        $mesConges = array_slice($mesConges, 0, 4);
        
        require_once __DIR__ . '/../views/employe/dashboard.php';
    }
    
    /**
     * Mes Bulletins - Liste complète
     */
    public function mesBulletins(): void {
        $userId = $_SESSION['user_id'] ?? null;
        if (!$userId) {
            header('Location: /SmartPayrollApp/public/login.php?error=session_expiree');
            exit();
        }
        
        $mois = isset($_GET['mois']) ? (int)$_GET['mois'] : (int)date('n');
        $annee = isset($_GET['annee']) ? (int)$_GET['annee'] : (int)date('Y');
        
        $bulletins = $this->bulletinModel->getByEmploye($userId);
        
        // Filtrer par mois/année si spécifié
        if ($mois && $annee) {
            $bulletins = array_filter($bulletins, function($b) use ($mois, $annee) {
                return $b['mois'] == $mois && $b['annee'] == $annee;
            });
            $bulletins = array_values($bulletins); // Réindexer
        }
        
        require_once __DIR__ . '/../views/employe/mes_bulletins.php';
    }
    
    /**
     * Voir un bulletin spécifique (vérification de propriété)
     */
    public function voirBulletin(): void {
        $userId = $_SESSION['user_id'] ?? null;
        $idBulletin = $_GET['id'] ?? null;
        
        if (!$userId || !$idBulletin) {
            header('Location: /SmartPayrollApp/app/controllers/EmployeController.php?action=mesBulletins&error=bulletin_invalide');
            exit();
        }
        
        $bulletin = $this->bulletinModel->getById((int)$idBulletin);
        
        if (!$bulletin || $bulletin['id_employe'] != $userId) {
            header('Location: /SmartPayrollApp/app/controllers/EmployeController.php?action=mesBulletins&error=acces_interdit');
            exit();
        }
        
        require_once __DIR__ . '/../views/employe/voir_bulletin.php';
    }
    
    /**
     * Télécharger PDF d'un bulletin (vérification de propriété)
     */
    public function telechargerPDF(): void {
        $userId = $_SESSION['user_id'] ?? null;
        $idBulletin = $_GET['id'] ?? null;
        
        if (!$userId || !$idBulletin) {
            header('Location: /SmartPayrollApp/app/controllers/EmployeController.php?action=mesBulletins&error=bulletin_invalide');
            exit();
        }
        
        $bulletin = $this->bulletinModel->getById((int)$idBulletin);
        
        if (!$bulletin || $bulletin['id_employe'] != $userId) {
            http_response_code(403);
            echo 'Accès interdit';
            exit();
        }
        
        $filename = 'bulletin_' . $bulletin['matricule'] . '_' . $bulletin['mois'] . '_' . $bulletin['annee'] . '.pdf';
        $html = $this->genererHTMLBulletin($bulletin);
        
        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        echo $html;
        exit();
    }
    
    /**
     * Mon Profil - Consultation
     */
    public function monProfil(): void {
        $userId = $_SESSION['user_id'] ?? null;
        if (!$userId) {
            header('Location: /SmartPayrollApp/public/login.php?error=session_expiree');
            exit();
        }
        
        $employe = $this->employeModel->getById($userId);
        
        if (!$employe) {
            header('Location: /SmartPayrollApp/public/login.php?error=employe_non_trouve');
            exit();
        }
        
        require_once __DIR__ . '/../views/employe/mon_profil.php';
    }
    
    /**
     * Modifier le profil
     */
    public function modifierProfil(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /SmartPayrollApp/app/controllers/EmployeController.php?action=monProfil');
            exit();
        }
        
        $userId = $_SESSION['user_id'] ?? null;
        if (!$userId) {
            header('Location: /SmartPayrollApp/public/login.php?error=session_expiree');
            exit();
        }
        
        $data = [
            'telephone' => $_POST['telephone'] ?? null,
            'adresse' => $_POST['adresse'] ?? null
        ];
        
        // Supprimer les champs vides
        $data = array_filter($data, function($value) {
            return $value !== null && $value !== '';
        });
        
        if (empty($data)) {
            header('Location: /SmartPayrollApp/app/controllers/EmployeController.php?action=monProfil&error=champs_vides');
            exit();
        }
        
        $result = $this->employeModel->updateProfil($userId, $data);
        
        if ($result) {
            // Mettre à jour la session
            $employe = $this->employeModel->getById($userId);
            $_SESSION['user_telephone'] = $employe['telephone'] ?? '';
            $_SESSION['user_adresse'] = $employe['adresse'] ?? '';
            
            header('Location: /SmartPayrollApp/app/controllers/EmployeController.php?action=monProfil&success=profil_modifie');
        } else {
            header('Location: /SmartPayrollApp/app/controllers/EmployeController.php?action=monProfil&error=erreur_modification');
        }
        
        exit();
    }
    
    /**
     * Générer HTML du bulletin (version temporaire)
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
                .title { font-size: 22px; margin: 15px 0; color: #2563eb; }
                .infos { display: flex; justify-content: space-between; margin: 25px 0; }
                .employee-info, .period-info { width: 48%; }
                .table { width: 100%; border-collapse: collapse; margin: 25px 0; }
                .table th, .table td { border: 1px solid #e2e8f0; padding: 12px; text-align: right; }
                .table th { background: #f1f5f9; font-weight: bold; text-align: left; }
                .total-row { font-weight: bold; background: #dbeafe !important; }
                .footer { margin-top: 40px; text-align: center; font-size: 14px; color: #64748b; padding-top: 20px; border-top: 1px solid #e2e8f0; }
                .badge { display: inline-block; padding: 4px 10px; border-radius: 20px; font-size: 12px; font-weight: 600; }
                .badge-paye { background: #dbeafe; color: #1e40af; }
            </style>
        </head>
        <body>
            <div class="container">
                <div class="header">
                    <div class="company">Institut Supérieur Mony Keng</div>
                    <div class="title">BULLETIN DE PAIE</div>
                </div>
                
                <div class="infos">
                    <div class="employee-info">
                        <strong>Employé :</strong> ' . $bulletin['prenom'] . ' ' . $bulletin['nom'] . '<br>
                        <strong>Matricule :</strong> ' . $bulletin['matricule'] . '<br>
                        <strong>Poste :</strong> ' . $bulletin['poste'] . '
                    </div>
                    <div class="period-info">
                        <strong>Période :</strong> ' . $moisFr[$bulletin['mois']-1] . ' ' . $bulletin['annee'] . '<br>
                        <strong>Statut :</strong> <span class="badge badge-paye">Payé</span>
                    </div>
                </div>
                
                <table class="table">
                    <tr><th>Description</th><th>Montant (Ar)</th></tr>
                    <tr><td>Salaire de base</td><td>' . number_format($bulletin['salaire_base'], 0, ' ', ' ') . '</td></tr>
                    <tr><td>Heures supplémentaires</td><td>' . number_format($bulletin['montant_heures_sup'], 0, ' ', ' ') . '</td></tr>
                    <tr><td>Primes</td><td>' . number_format($bulletin['primes_total'], 0, ' ', ' ') . '</td></tr>
                    <tr class="total-row"><td><strong>Salaire Brut</strong></td><td><strong>' . number_format($bulletin['salaire_brut'], 0, ' ', ' ') . '</strong></td></tr>
                    <tr><td>CNSS (1%)</td><td>- ' . number_format($bulletin['cotisations_cnss'], 0, ' ', ' ') . '</td></tr>
                    <tr><td>IRSA</td><td>- ' . number_format($bulletin['impots_irsa'], 0, ' ', ' ') . '</td></tr>
                    <tr class="total-row"><td><strong>Salaire Net</strong></td><td><strong>' . number_format($bulletin['salaire_net'], 0, ' ', ' ') . '</strong></td></tr>
                </table>
                
                <div class="footer">
                    <p>Ce bulletin est édité informatiquement et ne nécessite pas de signature.</p>
                    <p>ISMK - SmartPayroll &copy; ' . date('Y') . ' - Document confidentiel personnel</p>
                </div>
            </div>
        </body>
        </html>';
    }
    /**
 * Demander un congé - Afficher le formulaire
 */
public function demanderConge(): void {
    $userId = $_SESSION['user_id'] ?? null;
    if (!$userId) {
        header('Location: /SmartPayrollApp/public/login.php?error=session_expiree');
        exit();
    }
    
    // Obtenir le solde de congés
    $soldeConges = $this->employeModel->getById($userId)['solde_conges'] ?? 0;
    
    require_once __DIR__ . '/../views/employe/demander_conge.php';
}

/**
 * Traiter la demande de congé
 */
public function soumettreDemandeConge(): void {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        header('Location: /SmartPayrollApp/app/controllers/EmployeController.php?action=demanderConge');
        exit();
    }
    
    $userId = $_SESSION['user_id'] ?? null;
    if (!$userId) {
        header('Location: /SmartPayrollApp/public/login.php?error=session_expiree');
        exit();
    }
    
    // Validation des données
    $required = ['date_debut', 'date_fin', 'type_conge', 'motif'];
    foreach ($required as $field) {
        if (empty($_POST[$field])) {
            header('Location: /SmartPayrollApp/app/controllers/EmployeController.php?action=demanderConge&error=champs_vides');
            exit();
        }
    }
    
    $dateDebut = new \DateTime($_POST['date_debut']);
    $dateFin = new \DateTime($_POST['date_fin']);
    
    if ($dateDebut > $dateFin) {
        header('Location: /SmartPayrollApp/app/controllers/EmployeController.php?action=demanderConge&error=date_invalide');
        exit();
    }
    
    // Calculer le nombre de jours
    $interval = $dateDebut->diff($dateFin);
    $nbJours = $interval->days + 1; // +1 pour inclure le jour de fin
    
    // Vérifier le solde de congés
    $soldeConges = $this->employeModel->getById($userId)['solde_conges'] ?? 0;
    if ($nbJours > $soldeConges) {
        header('Location: /SmartPayrollApp/app/controllers/EmployeController.php?action=demanderConge&error=solde_insuffisant');
        exit();
    }
    
    // Préparer les données
    $data = [
        'id_employe' => $userId,
        'date_debut' => $_POST['date_debut'],
        'date_fin' => $_POST['date_fin'],
        'nb_jours' => $nbJours,
        'type_conge' => $_POST['type_conge'],
        'motif' => $_POST['motif']
    ];
    
    // Créer la demande
    $congeModel = new \App\Models\Conge();
    $idConge = $congeModel->create($data);
    
    if ($idConge) {
        header('Location: /SmartPayrollApp/app/controllers/EmployeController.php?action=mesConges&success=conge_demande');
    } else {
        header('Location: /SmartPayrollApp/app/controllers/EmployeController.php?action=demanderConge&error=erreur_creation');
    }
    
    exit();
}

/**
 * Mes Congés - Liste des congés
 */
public function mesConges(): void {
    $userId = $_SESSION['user_id'] ?? null;
    if (!$userId) {
        header('Location: /SmartPayrollApp/public/login.php?error=session_expiree');
        exit();
    }
    
    $congeModel = new \App\Models\Conge();
    $mesConges = $congeModel->getByEmploye($userId);
    
    // Statistiques
    $stats = [
        'en_attente' => $congeModel->countByStatut($userId, 'en_attente'),
        'approuve' => $congeModel->countByStatut($userId, 'approuve'),
        'refuse' => $congeModel->countByStatut($userId, 'refuse'),
        'annule' => $congeModel->countByStatut($userId, 'annule'),
        'solde' => $congeModel->getSoldeConges($userId)
    ];
    
    require_once __DIR__ . '/../views/employe/mes_conges.php';
}

/**
 * Annuler une demande de congé
 */
public function annulerConge(): void {
    $userId = $_SESSION['user_id'] ?? null;
    $idConge = $_GET['id'] ?? null;
    
    if (!$userId || !$idConge) {
        header('Location: /SmartPayrollApp/app/controllers/EmployeController.php?action=mesConges&error=conge_invalide');
        exit();
    }
    
    $congeModel = new \App\Models\Conge();
    $result = $congeModel->cancel((int)$idConge, $userId);
    
    if ($result) {
        header('Location: /SmartPayrollApp/app/controllers/EmployeController.php?action=mesConges&success=conge_annule');
    } else {
        header('Location: /SmartPayrollApp/app/controllers/EmployeController.php?action=mesConges&error=erreur_annulation');
    }
    
    exit();
}

/**
 * Mes Documents - Liste des documents
 */
public function mesDocuments(): void {
    $userId = $_SESSION['user_id'] ?? null;
    if (!$userId) {
        header('Location: /SmartPayrollApp/public/login.php?error=session_expiree');
        exit();
    }
    
    // Obtenir les bulletins de l'employé
    $bulletins = $this->bulletinModel->getByEmploye($userId);
    
    require_once __DIR__ . '/../views/employe/mes_documents.php';
}

/**
 * Modifier l'email
 */
public function modifierEmail(): void {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        header('Location: /SmartPayrollApp/app/controllers/EmployeController.php?action=monProfil');
        exit();
    }
    
    $userId = $_SESSION['user_id'] ?? null;
    if (!$userId) {
        header('Location: /SmartPayrollApp/public/login.php?error=session_expiree');
        exit();
    }
    
    $nouvelEmail = trim($_POST['nouvel_email'] ?? '');
    $motDePasse = $_POST['mot_de_passe'] ?? '';
    
    if (empty($nouvelEmail) || empty($motDePasse)) {
        header('Location: /SmartPayrollApp/app/controllers/EmployeController.php?action=monProfil&error=champs_vides');
        exit();
    }
    
    // Vérifier le mot de passe actuel
    $employe = $this->employeModel->getById($userId);
    if (!password_verify($motDePasse, $employe['mot_de_passe'])) {
        header('Location: /SmartPayrollApp/app/controllers/EmployeController.php?action=monProfil&error=mot_de_passe_incorrect');
        exit();
    }
    
    // Vérifier si l'email existe déjà
    if ($this->employeModel->emailExists($nouvelEmail, $userId)) {
        header('Location: /SmartPayrollApp/app/controllers/EmployeController.php?action=monProfil&error=email_existe');
        exit();
    }
    
    // Mettre à jour l'email
    $result = $this->employeModel->update($userId, ['email' => $nouvelEmail]);
    
    if ($result) {
        $_SESSION['user_email'] = $nouvelEmail;
        header('Location: /SmartPayrollApp/app/controllers/EmployeController.php?action=monProfil&success=email_modifie');
    } else {
        header('Location: /SmartPayrollApp/app/controllers/EmployeController.php?action=monProfil&error=erreur_modification');
    }
    
    exit();
}

/**
 * Modifier le mot de passe
 */
public function modifierMotDePasse(): void {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        header('Location: /SmartPayrollApp/app/controllers/EmployeController.php?action=monProfil');
        exit();
    }
    
    $userId = $_SESSION['user_id'] ?? null;
    if (!$userId) {
        header('Location: /SmartPayrollApp/public/login.php?error=session_expiree');
        exit();
    }
    
    $ancienMdp = $_POST['ancien_mdp'] ?? '';
    $nouveauMdp = $_POST['nouveau_mdp'] ?? '';
    $confirmationMdp = $_POST['confirmation_mdp'] ?? '';
    
    if (empty($ancienMdp) || empty($nouveauMdp) || empty($confirmationMdp)) {
        header('Location: /SmartPayrollApp/app/controllers/EmployeController.php?action=monProfil&error=champs_vides');
        exit();
    }
    
    // Vérifier l'ancien mot de passe
    $employe = $this->employeModel->getById($userId);
    if (!password_verify($ancienMdp, $employe['mot_de_passe'])) {
        header('Location: /SmartPayrollApp/app/controllers/EmployeController.php?action=monProfil&error=ancien_mdp_incorrect');
        exit();
    }
    
    // Vérifier la confirmation
    if ($nouveauMdp !== $confirmationMdp) {
        header('Location: /SmartPayrollApp/app/controllers/EmployeController.php?action=monProfil&error=mdp_non_conforme');
        exit();
    }
    
    // Vérifier la longueur minimale
    if (strlen($nouveauMdp) < 8) {
        header('Location: /SmartPayrollApp/app/controllers/EmployeController.php?action=monProfil&error=mdp_trop_court');
        exit();
    }
    
    // Mettre à jour le mot de passe
    $motDePasseHash = password_hash($nouveauMdp, PASSWORD_BCRYPT);
    $result = $this->employeModel->update($userId, ['mot_de_passe' => $motDePasseHash]);
    
    if ($result) {
        header('Location: /SmartPayrollApp/app/controllers/EmployeController.php?action=monProfil&success=mdp_modifie');
    } else {
        header('Location: /SmartPayrollApp/app/controllers/EmployeController.php?action=monProfil&error=erreur_modification');
    }
    
    exit();
}
}


// Handler pour appels directs
try {
    $controller = new EmployeController();
    $action = $_GET['action'] ?? 'dashboard';
    
    switch ($action) {
        case 'dashboard':
            $controller->dashboard();
            break;
        case 'mesBulletins':
            $controller->mesBulletins();
            break;
        case 'voirBulletin':
            $controller->voirBulletin();
            break;
        case 'telechargerPDF':
            $controller->telechargerPDF();
            break;
        case 'monProfil':
            $controller->monProfil();
            break;
        case 'modifierProfil':
            $controller->modifierProfil();
            break;
        // NOUVELLES ACTIONS
        case 'demanderConge':
            $controller->demanderConge();
            break;
        case 'soumettreDemandeConge':
            $controller->soumettreDemandeConge();
            break;
        case 'mesConges':
            $controller->mesConges();
            break;
        case 'annulerConge':
            $controller->annulerConge();
            break;
        case 'mesDocuments':
            $controller->mesDocuments();
            break;
        case 'modifierEmail':
            $controller->modifierEmail();
            break;
        case 'modifierMotDePasse':
            $controller->modifierMotDePasse();
            break;
        default:
            $controller->dashboard();
    }
} catch (\Throwable $e) {
    error_log("[EmployeController] " . $e->getMessage());
    http_response_code(500);
    echo '<h2 style="color: #ef4444;">Erreur serveur employé</h2><pre>' . htmlspecialchars($e->getMessage()) . '</pre>';
}
?>