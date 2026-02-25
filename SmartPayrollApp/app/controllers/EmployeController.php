<?php
/**
 * ============================================================================
 * SMARTPAYROLL - Contrôleur Employé
 * ============================================================================
 * 
 * Gère toutes les actions de l'employé :
 * - Dashboard personnel
 * - Consultation de ses bulletins
 * - Gestion de son profil
 * - Gestion de ses congés
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
use App\Models\Bulletin;
use App\Models\Conge;

require_once __DIR__ . '/../core/Database.php';
require_once __DIR__ . '/../core/Session.php';
require_once __DIR__ . '/../models/Employe.php';
require_once __DIR__ . '/../models/Bulletin.php';
require_once __DIR__ . '/../models/Conge.php';

class EmployeController {
    
    private Employe $employeModel;
    private Bulletin $bulletinModel;
    private Conge $congeModel;
    
    public function __construct() {
        Session::start();
        Session::requireRole('employe');
        $this->employeModel = new Employe();
        $this->bulletinModel = new Bulletin();
        $this->congeModel = new Conge();
    }
    
    /**
     * Afficher le dashboard employé
     */
    public function dashboard(): void {
        $userId = Session::getUserId();
        $employe = $this->employeModel->getById($userId);
        if (!$employe) {
            Session::destroy();
            header('Location: /SmartPayrollApp/public/index.php?error=employe_non_trouve');
            exit();
        }
        $mesBulletins = $this->bulletinModel->getByEmploye($userId);
        $nbBulletins = $this->bulletinModel->countByEmploye($userId);
        $dernierSalaire = 0;
        if (!empty($mesBulletins)) {
            $b = $mesBulletins[0];
            $dernierSalaire = (float)($b['salaire_net'] ?? 0);
        }
        $moisNoms = [1=>'Janvier',2=>'Février',3=>'Mars',4=>'Avril',5=>'Mai',6=>'Juin',7=>'Juillet',8=>'Août',9=>'Septembre',10=>'Octobre',11=>'Novembre',12=>'Décembre'];
        $anciennete = '';
        if (!empty($employe['date_embauche'])) {
            $de = new \DateTime($employe['date_embauche']);
            $now = new \DateTime();
            $diff = $de->diff($now);
            if ($diff->y > 0) $anciennete = $diff->y . ' an' . ($diff->y > 1 ? 's' : '');
            elseif ($diff->m > 0) $anciennete = $diff->m . ' mois';
            else $anciennete = $diff->d . ' jour' . ($diff->d > 1 ? 's' : '');
        }
        require_once __DIR__ . '/../views/employe/dashboard.php';
    }
    
    /**
     * Afficher mes bulletins de paie
     */
    public function mesBulletins(): void {
        $userId = Session::getUserId();
        $bulletins = $this->bulletinModel->getByEmploye($userId);
        $moisNoms = [1=>'Janvier',2=>'Février',3=>'Mars',4=>'Avril',5=>'Mai',6=>'Juin',7=>'Juillet',8=>'Août',9=>'Septembre',10=>'Octobre',11=>'Novembre',12=>'Décembre'];
        require_once __DIR__ . '/../views/employe/mes_bulletins.php';
    }
    
    /**
     * Voir un bulletin spécifique
     */
    public function voirBulletin(): void {
        $userId = Session::getUserId();
        $idBulletin = $_GET['id'] ?? null;
        
        if (!$idBulletin) {
            header('Location: /SmartPayrollApp/app/controllers/EmployeController.php?action=mesBulletins&error=bulletin_invalide');
            exit();
        }
        
        // À implémenter : vérifier que le bulletin appartient à l'employé
        
        $bulletin = [
            'id' => $idBulletin,
            'mois' => 'Décembre 2024',
            'annee' => 2024,
            'salaire_base' => 450000,
            'primes' => 35000,
            'retenues' => 0,
            'cnss' => 4850,
            'irsa' => 0,
            'salaire_net' => 485000,
            'statut' => 'paye'
        ];
        
        require_once __DIR__ . '/../views/employe/voir_bulletin.php';
    }
    
    /**
     * Télécharger un bulletin en PDF
     */
    public function telechargerPDF(): void {
        $userId = Session::getUserId();
        $idBulletin = $_GET['id'] ?? null;
        if (!$idBulletin) {
            header('Location: /SmartPayrollApp/app/controllers/EmployeController.php?action=mesBulletins&error=bulletin_invalide');
            exit();
        }
        $bulletin = $this->bulletinModel->getByIdAndEmploye((int)$idBulletin, $userId);
        if (!$bulletin) {
            header('Location: /SmartPayrollApp/app/controllers/EmployeController.php?action=mesBulletins&error=bulletin_invalide');
            exit();
        }
        $mois = (int)($bulletin['mois'] ?? 0);
        $annee = (int)($bulletin['annee'] ?? date('Y'));
        $moisNoms = [1=>'Janvier',2=>'Février',3=>'Mars',4=>'Avril',5=>'Mai',6=>'Juin',7=>'Juillet',8=>'Août',9=>'Septembre',10=>'Octobre',11=>'Novembre',12=>'Décembre'];
        $nomMois = $moisNoms[$mois] ?? $mois;
        $filename = 'bulletin_' . $nomMois . '_' . $annee . '.pdf';
        if (!empty($bulletin['pdf_path']) && file_exists($bulletin['pdf_path'])) {
            header('Content-Type: application/pdf');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            readfile($bulletin['pdf_path']);
        } else {
            header('Content-Type: text/html; charset=utf-8');
            echo '<p>PDF non disponible pour ce bulletin. Le fichier n\'a pas encore été généré.</p>';
            echo '<p><a href="/SmartPayrollApp/app/controllers/EmployeController.php?action=mesBulletins">Retour aux bulletins</a></p>';
        }
        exit();
    }
    
    /**
     * Afficher mon profil
     */
    public function monProfil(): void {
        $userId = Session::getUserId();
        $employe = $this->employeModel->getById($userId);
        
        if (!$employe) {
            Session::destroy();
            header('Location: /SmartPayrollApp/public/index.php?error=employe_non_trouve');
            exit();
        }
        $success = $_GET['success'] ?? null;
        require_once __DIR__ . '/../views/employe/mon_profil.php';
    }
    
    /**
     * Afficher le formulaire de modification de profil
     */
    public function showModifierProfil(): void {
        $userId = Session::getUserId();
        $employe = $this->employeModel->getById($userId);
        
        if (!$employe) {
            Session::destroy();
            header('Location: /SmartPayrollApp/public/index.php?error=employe_non_trouve');
            exit();
        }
        $error = $_GET['error'] ?? null;
        require_once __DIR__ . '/../views/employe/modifier_profil.php';
    }
    
    /**
     * Traiter la modification du profil
     */
    public function modifierProfil(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /SmartPayrollApp/app/controllers/EmployeController.php?action=monProfil');
            exit();
        }
        
        $userId = Session::getUserId();
        
        // Validation des données
        $data = [
            'telephone' => $_POST['telephone'] ?? null,
            'adresse' => $_POST['adresse'] ?? null
        ];
        
        // Supprimer les champs vides
        $data = array_filter($data, function($value) {
            return $value !== null && $value !== '';
        });
        
        if (empty($data)) {
            header('Location: /SmartPayrollApp/app/controllers/EmployeController.php?action=showModifierProfil&error=champs_vides');
            exit();
        }
        $result = $this->employeModel->update($userId, $data);
        if ($result) {
            header('Location: /SmartPayrollApp/app/controllers/EmployeController.php?action=monProfil&success=profil_modifie');
        } else {
            header('Location: /SmartPayrollApp/app/controllers/EmployeController.php?action=showModifierProfil&error=erreur_modification');
        }
        
        exit();
    }
    
    /**
     * Afficher le formulaire de changement de mot de passe
     */
    public function showChangerPassword(): void {
        require_once __DIR__ . '/../views/employe/changer_password.php';
    }
    
    /**
     * Traiter le changement de mot de passe
     */
    public function changerPassword(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /SmartPayrollApp/app/controllers/EmployeController.php?action=monProfil');
            exit();
        }
        $userId = Session::getUserId();
        $employe = $this->employeModel->getById($userId);
        if (!$employe) { header('Location: /SmartPayrollApp/public/index.php'); exit; }
        $ancienPassword = $_POST['ancien_password'] ?? '';
        $nouveauPassword = $_POST['nouveau_password'] ?? '';
        $confirmation = $_POST['confirmation'] ?? '';
        if (empty($ancienPassword) || empty($nouveauPassword) || empty($confirmation)) {
            header('Location: /SmartPayrollApp/app/controllers/EmployeController.php?action=showChangerPassword&error=champs_vides');
            exit();
        }
        if ($nouveauPassword !== $confirmation) {
            header('Location: /SmartPayrollApp/app/controllers/EmployeController.php?action=showChangerPassword&error=mots_de_passe_differents');
            exit();
        }
        if (strlen($nouveauPassword) < 8) {
            header('Location: /SmartPayrollApp/app/controllers/EmployeController.php?action=showChangerPassword&error=mot_de_passe_trop_court');
            exit();
        }
        $user = Database::fetchOne("SELECT mot_de_passe FROM employe WHERE id_employe = :id", ['id' => $userId]);
        if (!$user || !password_verify($ancienPassword, $user['mot_de_passe'])) {
            header('Location: /SmartPayrollApp/app/controllers/EmployeController.php?action=showChangerPassword&error=ancien_incorrect');
            exit();
        }
        $hash = password_hash($nouveauPassword, PASSWORD_BCRYPT);
        Database::execute("UPDATE employe SET mot_de_passe = :hash WHERE id_employe = :id", ['hash' => $hash, 'id' => $userId]);
        header('Location: /SmartPayrollApp/app/controllers/EmployeController.php?action=monProfil&success=password_modifie');
        exit();
    }
    
    /**
     * Afficher mes congés
     */
    public function mesConges(): void {
        $userId = Session::getUserId();
        $employe = $this->employeModel->getById($userId);
        if (!$employe) {
            header('Location: /SmartPayrollApp/public/index.php');
            exit;
        }
        $mesConges = $this->congeModel->getByEmploye($userId);
        $soldeConges = (int)($employe['solde_conges'] ?? 0);
        $error = $_GET['error'] ?? null;
        $success = $_GET['success'] ?? null;
        require_once __DIR__ . '/../views/employe/mes_conges.php';
    }
    
    /**
     * Afficher le formulaire de demande de congé
     */
    public function showDemanderConge(): void {
        $userId = Session::getUserId();
        $employe = $this->employeModel->getById($userId);
        $soldeConges = (int)($employe['solde_conges'] ?? 0);
        $error = $_GET['error'] ?? null;
        require_once __DIR__ . '/../views/employe/demander_conge.php';
    }
    
    /**
     * Traiter la demande de congé
     */
    public function demanderConge(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /app/controllers/EmployeController.php?action=mesConges');
            exit();
        }
        
        // Validation des données
        $dateDebut = $_POST['date_debut'] ?? '';
        $dateFin = $_POST['date_fin'] ?? '';
        $typeConge = $_POST['type_conge'] ?? '';
        $motif = $_POST['motif'] ?? '';
        
        if (empty($dateDebut) || empty($dateFin) || empty($typeConge)) {
            header('Location: /SmartPayrollApp/app/controllers/EmployeController.php?action=showDemanderConge&error=champs_vides');
            exit();
        }
        
        // À implémenter : calculer le nombre de jours et vérifier le solde
        
        // À implémenter : enregistrer la demande en BDD
        
        header('Location: /app/controllers/EmployeController.php?action=mesConges&success=conge_demande');
        exit();
    }
    
    /**
     * Annuler une demande de congé
     */
    public function annulerConge(): void {
        $userId = Session::getUserId();
        $idConge = $_GET['id'] ?? null;
        if (!$idConge) {
            header('Location: /SmartPayrollApp/app/controllers/EmployeController.php?action=mesConges&error=conge_invalide');
            exit();
        }
        $ok = $this->congeModel->annuler((int)$idConge, $userId);
        header('Location: /SmartPayrollApp/app/controllers/EmployeController.php?action=mesConges&' . ($ok ? 'success=conge_annule' : 'error=conge_invalide'));
        exit();
    }
    
    /**
     * Afficher mes documents
     */
    public function mesDocuments(): void {
        $userId = Session::getUserId();
        $bulletins = $this->bulletinModel->getByEmploye($userId);
        $moisNoms = [1=>'Janvier',2=>'Février',3=>'Mars',4=>'Avril',5=>'Mai',6=>'Juin',7=>'Juillet',8=>'Août',9=>'Septembre',10=>'Octobre',11=>'Novembre',12=>'Décembre'];
        require_once __DIR__ . '/../views/employe/mes_documents.php';
    }
    
    /**
     * Télécharger un document
     */
    public function telechargerDocument(): void {
        $type = $_GET['type'] ?? 'bulletin';
        $id = $_GET['id'] ?? null;
        $userId = Session::getUserId();
        if ($type === 'bulletin' && $id) {
            $this->telechargerPDF();
            return;
        }
        header('Location: /SmartPayrollApp/app/controllers/EmployeController.php?action=mesDocuments');
        exit();
    }
}

// -------------------------
// Handler pour appels directs (ex: /app/controllers/EmployeController.php?action=dashboard)
// -------------------------
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
        case 'showModifierProfil':
            $controller->showModifierProfil();
            break;
        case 'modifierProfil':
            $controller->modifierProfil();
            break;
        case 'showChangerPassword':
            $controller->showChangerPassword();
            break;
        case 'changerPassword':
            $controller->changerPassword();
            break;
        case 'mesConges':
            $controller->mesConges();
            break;
        case 'showDemanderConge':
            $controller->showDemanderConge();
            break;
        case 'demanderConge':
            $controller->demanderConge();
            break;
        case 'annulerConge':
            $controller->annulerConge();
            break;
        case 'mesDocuments':
            $controller->mesDocuments();
            break;
        case 'telechargerDocument':
            $controller->telechargerDocument();
            break;
        default:
            $controller->dashboard();
            break;
    }
} catch (\Throwable $e) {
    error_log(sprintf("[EmployeController handler error] %s in %s on line %d\nTrace:\n%s",
        $e->getMessage(),
        $e->getFile(),
        $e->getLine(),
        $e->getTraceAsString()
    ));
    http_response_code(500);
    if (ini_get('display_errors')) {
        echo '<h3>Erreur serveur lors du traitement de la requête Employé</h3>';
        echo '<pre>' . htmlspecialchars($e->getMessage() . "\n" . $e->getTraceAsString()) . '</pre>';
    } else {
        echo 'Erreur serveur.';
    }
}
?>