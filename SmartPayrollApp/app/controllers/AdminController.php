<?php
/**
 * ============================================================================
 * SMARTPAYROLL - Contrôleur Administrateur
 * ============================================================================
 * 
 * Gère toutes les actions de l'administrateur :
 * - Dashboard
 * - Gestion des employés
 * - Gestion des départements
 * - Gestion des postes
 * - Paramètres
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
use App\Models\Entreprise;
use App\Models\Departement;
use App\Models\Poste;
use App\Models\ModePaiement;
use App\Models\Prime;
use App\Models\Retenue;

require_once __DIR__ . '/../core/Database.php';
require_once __DIR__ . '/../core/Session.php';
require_once __DIR__ . '/../models/Employe.php';
require_once __DIR__ . '/../models/Entreprise.php';
require_once __DIR__ . '/../models/Departement.php';
require_once __DIR__ . '/../models/Poste.php';
require_once __DIR__ . '/../models/ModePaiement.php';
require_once __DIR__ . '/../models/Prime.php';
require_once __DIR__ . '/../models/Retenue.php';

class AdminController {
    
    private Employe $employeModel;
    private Entreprise $entrepriseModel;
    private Departement $departementModel;
    private Poste $posteModel;
    private ModePaiement $modePaiementModel;
    private Prime $primeModel;
    private Retenue $retenueModel;
    
    public function __construct() {
        Session::start();
        Session::requireRole('admin');
        $this->employeModel = new Employe();
        $this->entrepriseModel = new Entreprise();
        $this->departementModel = new Departement();
        $this->posteModel = new Poste();
        $this->modePaiementModel = new ModePaiement();
        $this->primeModel = new Prime();
        $this->retenueModel = new Retenue();
    }
    
    /**
     * Afficher le dashboard admin
     */
    public function dashboard(): void {
        // Récupérer les statistiques
        $totalEmployes = $this->employeModel->countAll();
        $employesActifs = $this->employeModel->countActive();
        $statsByRole = $this->employeModel->getStatsByRole();
        
        // Rendre la vue
        require_once __DIR__ . '/../views/admin/dashboard.php';
    }
    
    /**
     * Afficher la liste des employés
     */
    public function listEmployes(): void {
        $employes = $this->employeModel->getAll();
        require_once __DIR__ . '/../views/admin/list_employes.php';
    }
    
    /**
     * Afficher le formulaire d'ajout d'employé
     */
    public function showAddEmploye(): void {
        $postes = Database::query("SELECT p.id_poste, p.libelle AS poste, d.libelle AS departement 
            FROM poste p LEFT JOIN departement d ON p.id_departement = d.id_departement ORDER BY p.libelle");
        if (empty($postes)) {
            $postes = [['id_poste' => 1, 'poste' => 'Non défini', 'departement' => '-']];
        }
        require_once __DIR__ . '/../views/admin/add_employes.php';
    }
    
    /**
     * Traiter l'ajout d'un employé
     */
    public function addEmploye(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /SmartPayrollApp/app/controllers/AdminController.php?action=showAddEmploye');
            exit();
        }
        
        // Validation des données
        $requiredFields = ['nom', 'prenom', 'email', 'matricule', 'role', 'type_contrat'];
        foreach ($requiredFields as $field) {
            if (empty($_POST[$field])) {
                header('Location: /SmartPayrollApp/app/controllers/AdminController.php?action=showAddEmploye&error=champs_vides');
                exit();
            }
        }
        
        // Vérifier si l'email existe déjà
        if ($this->employeModel->emailExists($_POST['email'])) {
            header('Location: /SmartPayrollApp/app/controllers/AdminController.php?action=showAddEmploye&error=email_existe');
            exit();
        }
        
        // Vérifier si le matricule existe déjà
        if ($this->employeModel->matriculeExists($_POST['matricule'])) {
            header('Location: /SmartPayrollApp/app/controllers/AdminController.php?action=showAddEmploye&error=matricule_existe');
            exit();
        }
        
        // Préparer les données
        $data = [
            'id_entreprise' => 1, // À adapter selon ton contexte
            'id_poste' => $_POST['id_poste'] ?? 1,
            'matricule' => $_POST['matricule'],
            'nom' => $_POST['nom'],
            'prenom' => $_POST['prenom'],
            'email' => $_POST['email'],
            'mot_de_passe' => $_POST['password'] ?? 'password123', // Mot de passe temporaire
            'telephone' => $_POST['telephone'] ?? null,
            'adresse' => $_POST['adresse'] ?? null,
            'date_embauche' => $_POST['date_embauche'] ?? date('Y-m-d'),
            'type_contrat' => $_POST['type_contrat'],
            'date_fin_contrat' => $_POST['date_fin_contrat'] ?? null,
            'taux_horaire' => $_POST['taux_horaire'] ?? 0,
            'solde_conges' => $_POST['solde_conges'] ?? 0,
            'statut' => 'actif',
            'role' => $_POST['role']
        ];
        
        // Créer l'employé
        $result = $this->employeModel->create($data);
        
        if ($result) {
            header('Location: /SmartPayrollApp/app/controllers/AdminController.php?action=listEmployes&success=employe_ajoute');
        } else {
            header('Location: /SmartPayrollApp/app/controllers/AdminController.php?action=showAddEmploye&error=erreur_creation');
        }
        
        exit();
    }
    
    /**
     * Afficher le formulaire de modification d'employé
     */
    public function showEditEmploye(): void {
        $id = $_GET['id'] ?? null;
        
        if (!$id) {
            header('Location: /SmartPayrollApp/app/controllers/AdminController.php?action=listEmployes&error=employe_invalide');
            exit();
        }
        
        $employe = $this->employeModel->getById((int)$id);
        
        if (!$employe) {
            header('Location: /SmartPayrollApp/app/controllers/AdminController.php?action=listEmployes&error=employe_non_trouve');
            exit();
        }
        
        $postes = Database::query("SELECT p.id_poste, p.libelle AS poste, d.libelle AS departement 
            FROM poste p LEFT JOIN departement d ON p.id_departement = d.id_departement ORDER BY p.libelle");
        if (empty($postes)) {
            $postes = [['id_poste' => 1, 'poste' => 'Non défini', 'departement' => '-']];
        }
        require_once __DIR__ . '/../views/admin/edit_employe.php';
    }
    
    /**
     * Traiter la modification d'un employé
     */
    public function editEmploye(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /SmartPayrollApp/app/controllers/AdminController.php?action=listEmployes');
            exit();
        }
        
        $id = $_POST['id'] ?? null;
        
        if (!$id) {
            header('Location: /SmartPayrollApp/app/controllers/AdminController.php?action=listEmployes&error=employe_invalide');
            exit();
        }
        
        // Préparer les données de mise à jour
        $data = [
            'nom' => $_POST['nom'] ?? null,
            'prenom' => $_POST['prenom'] ?? null,
            'email' => $_POST['email'] ?? null,
            'telephone' => $_POST['telephone'] ?? null,
            'adresse' => $_POST['adresse'] ?? null,
            'id_poste' => $_POST['id_poste'] ?? null,
            'type_contrat' => $_POST['type_contrat'] ?? null,
            'date_fin_contrat' => $_POST['date_fin_contrat'] ?? null,
            'taux_horaire' => $_POST['taux_horaire'] ?? null,
            'solde_conges' => $_POST['solde_conges'] ?? null,
            'statut' => $_POST['statut'] ?? null,
            'role' => $_POST['role'] ?? null
        ];
        
        // Supprimer les champs vides
        $data = array_filter($data, function($value) {
            return $value !== null && $value !== '';
        });
        
        // Vérifier si l'email existe déjà (sauf pour l'employé lui-même)
        if (isset($data['email'])) {
            if ($this->employeModel->emailExists($data['email'], (int)$id)) {
                header('Location: /SmartPayrollApp/app/controllers/AdminController.php?action=showEditEmploye&id=' . $id . '&error=email_existe');
                exit();
            }
        }
        
        // Mettre à jour l'employé
        $result = $this->employeModel->update((int)$id, $data);
        
        if ($result) {
            header('Location: /SmartPayrollApp/app/controllers/AdminController.php?action=listEmployes&success=employe_modifie');
        } else {
            header('Location: /SmartPayrollApp/app/controllers/AdminController.php?action=showEditEmploye&id=' . $id . '&error=erreur_modification');
        }
        
        exit();
    }
    
    /**
     * Activer/Désactiver un employé
     */
    public function toggleEmployeStatus(): void {
        $id = $_GET['id'] ?? null;
        $statut = $_GET['statut'] ?? 'inactif';
        
        if (!$id) {
            header('Location: /SmartPayrollApp/app/controllers/AdminController.php?action=listEmployes&error=employe_invalide');
            exit();
        }
        
        $result = $this->employeModel->toggleStatus((int)$id, $statut);
        
        if ($result) {
            $message = $statut === 'actif' ? 'employe_active' : 'employe_desactive';
            header('Location: /SmartPayrollApp/app/controllers/AdminController.php?action=listEmployes&success=' . $message);
        } else {
            header('Location: /SmartPayrollApp/app/controllers/AdminController.php?action=listEmployes&error=erreur_statut');
        }
        
        exit();
    }
    
    /**
     * Supprimer un employé (soft delete)
     */
    public function deleteEmploye(): void {
        $id = $_GET['id'] ?? null;
        
        if (!$id) {
            header('Location: /SmartPayrollApp/app/controllers/AdminController.php?action=listEmployes&error=employe_invalide');
            exit();
        }
        
        $result = $this->employeModel->delete((int)$id);
        
        if ($result) {
            header('Location: /SmartPayrollApp/app/controllers/AdminController.php?action=listEmployes&success=employe_supprime');
        } else {
            header('Location: /SmartPayrollApp/app/controllers/AdminController.php?action=listEmployes&error=erreur_suppression');
        }
        
        exit();
    }
    
    // ==================== ENTREPRISE ====================
    public function gestionEntreprise(): void {
        $entreprises = $this->entrepriseModel->getAll();
        require_once __DIR__ . '/../views/admin/gestion_entreprise.php';
    }
    public function showAddEntreprise(): void {
        require_once __DIR__ . '/../views/admin/entreprise_form.php';
    }
    public function addEntreprise(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /SmartPayrollApp/app/controllers/AdminController.php?action=showAddEntreprise');
            exit;
        }
        $data = ['nom' => $_POST['nom'] ?? '', 'adresse' => $_POST['adresse'] ?? '', 'telephone' => $_POST['telephone'] ?? null, 'email' => $_POST['email'] ?? null, 'nif' => $_POST['nif'] ?? '', 'rc' => $_POST['rc'] ?? null, 'cnss' => $_POST['cnss'] ?? null];
        if (empty($data['nom']) || empty($data['nif'])) {
            header('Location: /SmartPayrollApp/app/controllers/AdminController.php?action=showAddEntreprise&error=champs_vides');
            exit;
        }
        $id = $this->entrepriseModel->create($data);
        header('Location: /SmartPayrollApp/app/controllers/AdminController.php?action=gestionEntreprise&success=' . ($id ? 'entreprise_ajoutee' : 'erreur'));
        exit;
    }
    public function showEditEntreprise(): void {
        $id = $_GET['id'] ?? null;
        if (!$id) { header('Location: /SmartPayrollApp/app/controllers/AdminController.php?action=gestionEntreprise&error=invalid'); exit; }
        $entreprise = $this->entrepriseModel->getById((int)$id);
        if (!$entreprise) { header('Location: /SmartPayrollApp/app/controllers/AdminController.php?action=gestionEntreprise&error=not_found'); exit; }
        require_once __DIR__ . '/../views/admin/entreprise_form.php';
    }
    public function editEntreprise(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_POST['id'])) {
            header('Location: /SmartPayrollApp/app/controllers/AdminController.php?action=gestionEntreprise');
            exit;
        }
        $id = (int)$_POST['id'];
        $data = ['nom' => $_POST['nom'] ?? '', 'adresse' => $_POST['adresse'] ?? '', 'telephone' => $_POST['telephone'] ?? null, 'email' => $_POST['email'] ?? null, 'nif' => $_POST['nif'] ?? '', 'rc' => $_POST['rc'] ?? null, 'cnss' => $_POST['cnss'] ?? null];
        $this->entrepriseModel->update($id, $data);
        header('Location: /SmartPayrollApp/app/controllers/AdminController.php?action=gestionEntreprise&success=entreprise_modifiee');
        exit;
    }
    public function deleteEntreprise(): void {
        $id = $_GET['id'] ?? null;
        if (!$id) { header('Location: /SmartPayrollApp/app/controllers/AdminController.php?action=gestionEntreprise&error=invalid'); exit; }
        $ok = $this->entrepriseModel->delete((int)$id);
        header('Location: /SmartPayrollApp/app/controllers/AdminController.php?action=gestionEntreprise&' . ($ok ? 'success=entreprise_supprimee' : 'error=has_employes'));
        exit;
    }

    // ==================== DÉPARTEMENTS ====================
    public function gestionDepartements(): void {
        $departements = $this->departementModel->getAll();
        $entreprises = $this->entrepriseModel->getAll();
        require_once __DIR__ . '/../views/admin/gestion_departements.php';
    }
    public function showAddDepartement(): void {
        $entreprises = $this->entrepriseModel->getAll();
        require_once __DIR__ . '/../views/admin/departement_form.php';
    }
    public function addDepartement(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /SmartPayrollApp/app/controllers/AdminController.php?action=showAddDepartement');
            exit;
        }
        $data = ['id_entreprise' => $_POST['id_entreprise'] ?? 1, 'libelle' => $_POST['libelle'] ?? '', 'description' => $_POST['description'] ?? null];
        if (empty($data['libelle'])) {
            header('Location: /SmartPayrollApp/app/controllers/AdminController.php?action=showAddDepartement&error=champs_vides');
            exit;
        }
        $id = $this->departementModel->create($data);
        header('Location: /SmartPayrollApp/app/controllers/AdminController.php?action=gestionDepartements&success=' . ($id ? 'departement_ajoute' : 'erreur'));
        exit;
    }
    public function showEditDepartement(): void {
        $id = $_GET['id'] ?? null;
        if (!$id) { header('Location: /SmartPayrollApp/app/controllers/AdminController.php?action=gestionDepartements&error=invalid'); exit; }
        $departement = $this->departementModel->getById((int)$id);
        if (!$departement) { header('Location: /SmartPayrollApp/app/controllers/AdminController.php?action=gestionDepartements&error=not_found'); exit; }
        $entreprises = $this->entrepriseModel->getAll();
        require_once __DIR__ . '/../views/admin/departement_form.php';
    }
    public function editDepartement(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_POST['id'])) {
            header('Location: /SmartPayrollApp/app/controllers/AdminController.php?action=gestionDepartements');
            exit;
        }
        $id = (int)$_POST['id'];
        $data = ['id_entreprise' => $_POST['id_entreprise'] ?? 1, 'libelle' => $_POST['libelle'] ?? '', 'description' => $_POST['description'] ?? null];
        $this->departementModel->update($id, $data);
        header('Location: /SmartPayrollApp/app/controllers/AdminController.php?action=gestionDepartements&success=departement_modifie');
        exit;
    }
    public function deleteDepartement(): void {
        $id = $_GET['id'] ?? null;
        if (!$id) { header('Location: /SmartPayrollApp/app/controllers/AdminController.php?action=gestionDepartements&error=invalid'); exit; }
        $ok = $this->departementModel->delete((int)$id);
        header('Location: /SmartPayrollApp/app/controllers/AdminController.php?action=gestionDepartements&' . ($ok ? 'success=departement_supprime' : 'error=has_postes'));
        exit;
    }

    // ==================== POSTES ====================
    public function gestionPostes(): void {
        $postes = $this->posteModel->getAll();
        $departements = $this->departementModel->getAll();
        require_once __DIR__ . '/../views/admin/gestion_postes.php';
    }
    public function showAddPoste(): void {
        $departements = $this->departementModel->getAll();
        require_once __DIR__ . '/../views/admin/poste_form.php';
    }
    public function addPoste(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /SmartPayrollApp/app/controllers/AdminController.php?action=showAddPoste');
            exit;
        }
        $data = ['id_departement' => $_POST['id_departement'] ?? 1, 'libelle' => $_POST['libelle'] ?? '', 'salaire_base' => !empty($_POST['salaire_base']) ? (float)$_POST['salaire_base'] : null, 'description' => $_POST['description'] ?? null];
        if (empty($data['libelle'])) {
            header('Location: /SmartPayrollApp/app/controllers/AdminController.php?action=showAddPoste&error=champs_vides');
            exit;
        }
        $id = $this->posteModel->create($data);
        header('Location: /SmartPayrollApp/app/controllers/AdminController.php?action=gestionPostes&success=' . ($id ? 'poste_ajoute' : 'erreur'));
        exit;
    }
    public function showEditPoste(): void {
        $id = $_GET['id'] ?? null;
        if (!$id) { header('Location: /SmartPayrollApp/app/controllers/AdminController.php?action=gestionPostes&error=invalid'); exit; }
        $poste = $this->posteModel->getById((int)$id);
        if (!$poste) { header('Location: /SmartPayrollApp/app/controllers/AdminController.php?action=gestionPostes&error=not_found'); exit; }
        $departements = $this->departementModel->getAll();
        require_once __DIR__ . '/../views/admin/poste_form.php';
    }
    public function editPoste(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_POST['id'])) {
            header('Location: /SmartPayrollApp/app/controllers/AdminController.php?action=gestionPostes');
            exit;
        }
        $id = (int)$_POST['id'];
        $data = ['id_departement' => $_POST['id_departement'] ?? 1, 'libelle' => $_POST['libelle'] ?? '', 'salaire_base' => isset($_POST['salaire_base']) && $_POST['salaire_base'] !== '' ? (float)$_POST['salaire_base'] : null, 'description' => $_POST['description'] ?? null];
        $this->posteModel->update($id, $data);
        header('Location: /SmartPayrollApp/app/controllers/AdminController.php?action=gestionPostes&success=poste_modifie');
        exit;
    }
    public function deletePoste(): void {
        $id = $_GET['id'] ?? null;
        if (!$id) { header('Location: /SmartPayrollApp/app/controllers/AdminController.php?action=gestionPostes&error=invalid'); exit; }
        $ok = $this->posteModel->delete((int)$id);
        header('Location: /SmartPayrollApp/app/controllers/AdminController.php?action=gestionPostes&' . ($ok ? 'success=poste_supprime' : 'error=has_employes'));
        exit;
    }
    
    // ==================== PARAMÈTRES ====================
    public function parametres(): void {
        $modesPaiement = $this->modePaiementModel->getAll();
        $primes = $this->primeModel->getAll();
        $retenues = $this->retenueModel->getAll();
        require_once __DIR__ . '/../views/admin/parametres.php';
    }
    public function addModePaiement(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_POST['libelle'])) {
            header('Location: /SmartPayrollApp/app/controllers/AdminController.php?action=parametres&error=champs_vides');
            exit;
        }
        $this->modePaiementModel->create(['libelle' => $_POST['libelle'], 'description' => $_POST['description'] ?? null]);
        header('Location: /SmartPayrollApp/app/controllers/AdminController.php?action=parametres&success=mode_ajoute');
        exit;
    }
    public function editModePaiement(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_POST['id'])) { header('Location: /SmartPayrollApp/app/controllers/AdminController.php?action=parametres'); exit; }
        $this->modePaiementModel->update((int)$_POST['id'], ['libelle' => $_POST['libelle'] ?? '', 'description' => $_POST['description'] ?? null]);
        header('Location: /SmartPayrollApp/app/controllers/AdminController.php?action=parametres&success=mode_modifie');
        exit;
    }
    public function deleteModePaiement(): void {
        $id = $_GET['id'] ?? null;
        if (!$id) { header('Location: /SmartPayrollApp/app/controllers/AdminController.php?action=parametres'); exit; }
        $ok = $this->modePaiementModel->delete((int)$id);
        header('Location: /SmartPayrollApp/app/controllers/AdminController.php?action=parametres&' . ($ok ? 'success=mode_supprime' : 'error=has_bulletins'));
        exit;
    }
    public function addPrime(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_POST['libelle'])) {
            header('Location: /SmartPayrollApp/app/controllers/AdminController.php?action=parametres&error=champs_vides');
            exit;
        }
        $this->primeModel->create([
            'libelle' => $_POST['libelle'],
            'type_prime' => $_POST['type_prime'] ?? 'autre',
            'montant' => (float)($_POST['montant'] ?? 0),
            'description' => $_POST['description'] ?? null,
            'est_fixe' => isset($_POST['est_fixe']) ? (bool)$_POST['est_fixe'] : true
        ]);
        header('Location: /SmartPayrollApp/app/controllers/AdminController.php?action=parametres&success=prime_ajoutee');
        exit;
    }
    public function editPrime(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_POST['id'])) { header('Location: /SmartPayrollApp/app/controllers/AdminController.php?action=parametres'); exit; }
        $this->primeModel->update((int)$_POST['id'], [
            'libelle' => $_POST['libelle'] ?? '',
            'type_prime' => $_POST['type_prime'] ?? 'autre',
            'montant' => (float)($_POST['montant'] ?? 0),
            'description' => $_POST['description'] ?? null,
            'est_fixe' => isset($_POST['est_fixe'])
        ]);
        header('Location: /SmartPayrollApp/app/controllers/AdminController.php?action=parametres&success=prime_modifiee');
        exit;
    }
    public function deletePrime(): void {
        $id = $_GET['id'] ?? null;
        if (!$id) { header('Location: /SmartPayrollApp/app/controllers/AdminController.php?action=parametres'); exit; }
        $ok = $this->primeModel->delete((int)$id);
        header('Location: /SmartPayrollApp/app/controllers/AdminController.php?action=parametres&' . ($ok ? 'success=prime_supprimee' : 'error=has_bulletins'));
        exit;
    }
    public function addRetenue(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_POST['libelle'])) {
            header('Location: /SmartPayrollApp/app/controllers/AdminController.php?action=parametres&error=champs_vides');
            exit;
        }
        $this->retenueModel->create([
            'libelle' => $_POST['libelle'],
            'type_retenue' => $_POST['type_retenue'] ?? 'autre',
            'montant' => (float)($_POST['montant'] ?? 0),
            'description' => $_POST['description'] ?? null,
            'est_fixe' => isset($_POST['est_fixe']) ? (bool)$_POST['est_fixe'] : true
        ]);
        header('Location: /SmartPayrollApp/app/controllers/AdminController.php?action=parametres&success=retenue_ajoutee');
        exit;
    }
    public function editRetenue(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_POST['id'])) { header('Location: /SmartPayrollApp/app/controllers/AdminController.php?action=parametres'); exit; }
        $this->retenueModel->update((int)$_POST['id'], [
            'libelle' => $_POST['libelle'] ?? '',
            'type_retenue' => $_POST['type_retenue'] ?? 'autre',
            'montant' => (float)($_POST['montant'] ?? 0),
            'description' => $_POST['description'] ?? null,
            'est_fixe' => isset($_POST['est_fixe'])
        ]);
        header('Location: /SmartPayrollApp/app/controllers/AdminController.php?action=parametres&success=retenue_modifiee');
        exit;
    }
    public function deleteRetenue(): void {
        $id = $_GET['id'] ?? null;
        if (!$id) { header('Location: /SmartPayrollApp/app/controllers/AdminController.php?action=parametres'); exit; }
        $ok = $this->retenueModel->delete((int)$id);
        header('Location: /SmartPayrollApp/app/controllers/AdminController.php?action=parametres&' . ($ok ? 'success=retenue_supprimee' : 'error=has_bulletins'));
        exit;
    }

    // ==================== RAPPORTS ====================
    public function rapports(): void {
        $statsPaie = Database::query("SELECT * FROM vue_stats_paie_departement");
        $bulletinsMois = Database::query("SELECT * FROM vue_bulletins_mois_en_cours");
        $congesAttente = Database::query("SELECT * FROM vue_conges_en_attente");
        $employesActifs = Database::query("SELECT * FROM vue_employes_actifs");
        require_once __DIR__ . '/../views/admin/rapports.php';
    }
}
// -------------------------
// Handler pour appels directs (ex: /app/controllers/AdminController.php?action=dashboard)
// Placé en dehors des méthodes pour permettre l'appel direct du contrôleur.
// -------------------------
try {
    $controller = new AdminController();

    $action = $_GET['action'] ?? 'dashboard';

    switch ($action) {
        case 'dashboard':
            $controller->dashboard();
            break;
        case 'listEmployes':
            $controller->listEmployes();
            break;
        case 'showAddEmploye':
            $controller->showAddEmploye();
            break;
        case 'addEmploye':
            $controller->addEmploye();
            break;
        case 'showEditEmploye':
            $controller->showEditEmploye();
            break;
        case 'editEmploye':
            $controller->editEmploye();
            break;
        case 'toggleEmployeStatus':
            $controller->toggleEmployeStatus();
            break;
        case 'deleteEmploye':
            $controller->deleteEmploye();
            break;
        case 'gestionEntreprise':
            $controller->gestionEntreprise();
            break;
        case 'showAddEntreprise':
            $controller->showAddEntreprise();
            break;
        case 'addEntreprise':
            $controller->addEntreprise();
            break;
        case 'showEditEntreprise':
            $controller->showEditEntreprise();
            break;
        case 'editEntreprise':
            $controller->editEntreprise();
            break;
        case 'deleteEntreprise':
            $controller->deleteEntreprise();
            break;
        case 'gestionDepartements':
            $controller->gestionDepartements();
            break;
        case 'showAddDepartement':
            $controller->showAddDepartement();
            break;
        case 'addDepartement':
            $controller->addDepartement();
            break;
        case 'showEditDepartement':
            $controller->showEditDepartement();
            break;
        case 'editDepartement':
            $controller->editDepartement();
            break;
        case 'deleteDepartement':
            $controller->deleteDepartement();
            break;
        case 'gestionPostes':
            $controller->gestionPostes();
            break;
        case 'showAddPoste':
            $controller->showAddPoste();
            break;
        case 'addPoste':
            $controller->addPoste();
            break;
        case 'showEditPoste':
            $controller->showEditPoste();
            break;
        case 'editPoste':
            $controller->editPoste();
            break;
        case 'deletePoste':
            $controller->deletePoste();
            break;
        case 'parametres':
            $controller->parametres();
            break;
        case 'addModePaiement':
            $controller->addModePaiement();
            break;
        case 'editModePaiement':
            $controller->editModePaiement();
            break;
        case 'deleteModePaiement':
            $controller->deleteModePaiement();
            break;
        case 'addPrime':
            $controller->addPrime();
            break;
        case 'editPrime':
            $controller->editPrime();
            break;
        case 'deletePrime':
            $controller->deletePrime();
            break;
        case 'addRetenue':
            $controller->addRetenue();
            break;
        case 'editRetenue':
            $controller->editRetenue();
            break;
        case 'deleteRetenue':
            $controller->deleteRetenue();
            break;
        case 'rapports':
            $controller->rapports();
            break;
        default:
            // action inconnue -> dashboard par défaut
            $controller->dashboard();
            break;
    }
} catch (\Throwable $e) {
    error_log(sprintf("[AdminController handler error] %s in %s on line %d\nTrace:\n%s",
        $e->getMessage(),
        $e->getFile(),
        $e->getLine(),
        $e->getTraceAsString()
    ));
    http_response_code(500);
    if (ini_get('display_errors')) {
        echo '<h3>Erreur serveur lors du traitement de la requête Admin</h3>';
        echo '<pre>' . htmlspecialchars($e->getMessage() . "\n" . $e->getTraceAsString()) . '</pre>';
    } else {
        echo 'Erreur serveur.';
    }
    
}

?>