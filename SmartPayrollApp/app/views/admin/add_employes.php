<?php
require_once __DIR__ . '/../../core/Session.php';
\App\Core\Session::requireRole('admin');

$error = $_GET['error'] ?? $_SESSION['error'] ?? null;
unset($_SESSION['error']);
$postes = $postes ?? [];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Ajouter un Employé - SmartPayroll</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #f1f5f9; }
        .container { max-width: 800px; margin: 0 auto; padding: 20px; }
        header { 
            background: white; 
            padding: 20px; 
            box-shadow: 0 2px 4px rgba(0,0,0,0.1); 
            display: flex; 
            justify-content: space-between; 
            align-items: center; 
            margin-bottom: 30px; 
        }
        .alert { padding: 12px 15px; border-radius: 8px; margin-bottom: 20px; font-size: 14px; }
        .alert-error { background: #fee2e2; color: #991b1b; border: 1px solid #fecaca; }
        .btn { 
            display: inline-block; 
            padding: 10px 20px; 
            background: #2563eb; 
            color: white; 
            text-decoration: none; 
            border-radius: 6px; 
            font-weight: 600;
            transition: transform 0.2s;
        }
        .btn:hover { transform: translateY(-2px); }
        .form-container { background: white; padding: 30px; border-radius: 12px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; margin-bottom: 8px; font-weight: 600; color: #1e293b; }
        .form-control { 
            width: 100%; 
            padding: 12px 15px; 
            border: 2px solid #e2e8f0; 
            border-radius: 8px; 
            font-size: 16px; 
            transition: border-color 0.3s;
        }
        .form-control:focus { outline: none; border-color: #2563eb; }
        .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
        .btn-group { display: flex; gap: 10px; margin-top: 20px; }
    </style>
</head>
<body>
    <div class="container">
        <header>
            <h1>➕ Ajouter un Employé</h1>
            <div>
                <a href="/SmartPayrollApp/app/controllers/AdminController.php?action=listEmployes" class="btn">← Retour à la liste</a>
                <a href="/SmartPayrollApp/app/controllers/AuthController.php?action=logout" class="btn" style="background: #ef4444; margin-left: 10px;">Déconnexion</a>
            </div>
        </header>

        <?php if ($error): ?>
            <div class="alert alert-error"><?php
                $msg = ['champs_vides' => 'Veuillez remplir tous les champs obligatoires.', 'email_existe' => 'Cet email existe déjà.', 'matricule_existe' => 'Ce matricule existe déjà.', 'erreur_creation' => 'Erreur lors de la création.'];
                echo htmlspecialchars($msg[$error] ?? $error);
            ?></div>
        <?php endif; ?>

        <div class="form-container">
            <form action="/SmartPayrollApp/app/controllers/AdminController.php?action=addEmploye" method="POST">
                <div class="form-row">
                    <div class="form-group">
                        <label for="nom">Nom *</label>
                        <input type="text" id="nom" name="nom" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="prenom">Prénom *</label>
                        <input type="text" id="prenom" name="prenom" class="form-control" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="email">Email *</label>
                        <input type="email" id="email" name="email" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="matricule">Matricule *</label>
                        <input type="text" id="matricule" name="matricule" class="form-control" placeholder="Ex: EMP0001" required>
                    </div>
                </div>

                <div class="form-group">
                    <label for="password">Mot de passe (par défaut: password123)</label>
                    <input type="password" id="password" name="password" class="form-control" value="password123">
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="telephone">Téléphone</label>
                        <input type="tel" id="telephone" name="telephone" class="form-control">
                    </div>
                    <div class="form-group">
                        <label for="adresse">Adresse</label>
                        <input type="text" id="adresse" name="adresse" class="form-control">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="id_poste">Poste *</label>
                        <select id="id_poste" name="id_poste" class="form-control" required>
                            <option value="">Sélectionner un poste</option>
                            <?php foreach ($postes as $poste): ?>
                                <option value="<?= $poste['id_poste'] ?>">
                                    <?= htmlspecialchars($poste['poste']) ?> (<?= htmlspecialchars($poste['departement']) ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="type_contrat">Type de contrat *</label>
                        <select id="type_contrat" name="type_contrat" class="form-control" required>
                            <option value="CDI">CDI</option>
                            <option value="CDD">CDD</option>
                            <option value="Stage">Stage</option>
                            <option value="Vacataire">Vacataire</option>
                        </select>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="date_embauche">Date d'embauche *</label>
                        <input type="date" id="date_embauche" name="date_embauche" class="form-control" value="<?= date('Y-m-d') ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="date_fin_contrat">Date de fin (CDD uniquement)</label>
                        <input type="date" id="date_fin_contrat" name="date_fin_contrat" class="form-control">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="taux_horaire">Taux horaire (Vacataires)</label>
                        <input type="number" id="taux_horaire" name="taux_horaire" class="form-control" step="0.01" min="0" value="0">
                    </div>
                    <div class="form-group">
                        <label for="solde_conges">Solde congés (jours)</label>
                        <input type="number" id="solde_conges" name="solde_conges" class="form-control" min="0" value="0">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="role">Rôle *</label>
                        <select id="role" name="role" class="form-control" required>
                            <option value="employe">Employé</option>
                            <option value="comptable">Comptable</option>
                            <option value="admin">Administrateur</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="statut">Statut *</label>
                        <select id="statut" name="statut" class="form-control" required>
                            <option value="actif">Actif</option>
                            <option value="inactif">Inactif</option>
                        </select>
                    </div>
                </div>

                <div class="btn-group">
                    <button type="submit" class="btn">✅ Ajouter l'Employé</button>
                    <a href="/SmartPayrollApp/app/controllers/AdminController.php?action=listEmployes" class="btn" style="background: #64748b;">Annuler</a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>