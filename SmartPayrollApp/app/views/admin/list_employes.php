<?php
require_once __DIR__ . '/../../core/Session.php';
\App\Core\Session::requireRole('admin');

// Récupérer les messages de session
$error = $_SESSION['error'] ?? null;
$success = $_SESSION['success'] ?? null;
unset($_SESSION['error'], $_SESSION['success']);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Gestion des Employés - SmartPayroll</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #f1f5f9; }
        .container { max-width: 1400px; margin: 0 auto; padding: 20px; }
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
        .alert-success { background: #dcfce7; color: #166534; border: 1px solid #bbf7d0; }
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
        .btn-success { background: #10b981; }
        .btn-warning { background: #f59e0b; }
        .btn-danger { background: #ef4444; }
        .btn-sm { padding: 6px 12px; font-size: 14px; }
        table { 
            width: 100%; 
            background: white; 
            border-radius: 12px; 
            box-shadow: 0 2px 4px rgba(0,0,0,0.1); 
            overflow: hidden;
        }
        th, td { padding: 15px; text-align: left; border-bottom: 1px solid #e2e8f0; }
        th { background: #f8fafc; font-weight: 600; color: #1e293b; }
        tr:hover { background: #f8fafc; }
        .badge { padding: 4px 10px; border-radius: 20px; font-size: 12px; font-weight: 600; }
        .badge-admin { background: #dbeafe; color: #1d4ed8; }
        .badge-comptable { background: #dcfce7; color: #166534; }
        .badge-employe { background: #fef3c7; color: #92400e; }
        .badge-actif { background: #dcfce7; color: #166534; }
        .badge-inactif { background: #fee2e2; color: #991b1b; }
        .badge-cdi { background: #dbeafe; color: #1d4ed8; }
        .badge-cdd { background: #f3e8ff; color: #7e22ce; }
        .badge-vacataire { background: #ffedd5; color: #c2410c; }
        .actions { display: flex; gap: 8px; }
    </style>
</head>
<body>
    <div class="container">
        <header>
            <h1>👥 Gestion des Employés</h1>
            <div>
                <span>Bienvenue, <?= htmlspecialchars(($_SESSION['user_prenom'] ?? '') . ' ' . ($_SESSION['user_nom'] ?? '')) ?></span>
                <a href="/SmartPayrollApp/app/controllers/AdminController.php?action=dashboard" class="btn" style="margin-left: 10px;">← Dashboard</a>
                <a href="/SmartPayrollApp/app/controllers/AuthController.php?action=logout" class="btn" style="background: #ef4444; margin-left: 10px;">Déconnexion</a>
            </div>
        </header>

        <?php if ($error): ?>
            <div class="alert alert-error"><?php
                $msg = ['champs_vides' => 'Veuillez remplir tous les champs.', 'email_existe' => 'Cet email existe déjà.', 'matricule_existe' => 'Ce matricule existe déjà.', 'employe_invalide' => 'Employé invalide.', 'employe_non_trouve' => 'Employé non trouvé.', 'erreur_creation' => 'Erreur lors de la création.', 'erreur_modification' => 'Erreur lors de la modification.', 'erreur_statut' => 'Erreur lors du changement de statut.', 'erreur_suppression' => 'Erreur lors de la suppression.'];
                echo htmlspecialchars($msg[$error] ?? $error);
            ?></div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="alert alert-success"><?php
                $msg = ['employe_ajoute' => 'Employé ajouté avec succès.', 'employe_modifie' => 'Employé modifié avec succès.', 'employe_active' => 'Employé activé.', 'employe_desactive' => 'Employé désactivé.', 'employe_supprime' => 'Employé supprimé.'];
                echo htmlspecialchars($msg[$success] ?? $success);
            ?></div>
        <?php endif; ?>

        <div style="display: flex; justify-content: space-between; margin-bottom: 20px;">
            <h2>Liste des Employés (<?= count($employes) ?>)</h2>
            <a href="/SmartPayrollApp/app/controllers/AdminController.php?action=showAddEmploye" class="btn">➕ Ajouter un Employé</a>
        </div>

        <table>
            <thead>
                <tr>
                    <th>Matricule</th>
                    <th>Nom & Prénom</th>
                    <th>Email</th>
                    <th>Téléphone</th>
                    <th>Poste (Département)</th>
                    <th>Type</th>
                    <th>Rôle</th>
                    <th>Statut</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($employes as $employe): ?>
                <tr>
                    <td><strong><?= htmlspecialchars($employe['matricule']) ?></strong></td>
                    <td><?= htmlspecialchars($employe['nom']) ?> <?= htmlspecialchars($employe['prenom']) ?></td>
                    <td><?= htmlspecialchars($employe['email']) ?></td>
                    <td><?= htmlspecialchars($employe['telephone'] ?? '-') ?></td>
                    <td>
                        <strong><?= htmlspecialchars($employe['poste']) ?></strong><br>
                        <small style="color: #64748b;"><?= htmlspecialchars($employe['departement']) ?></small>
                    </td>
                    <td>
                        <span class="badge badge-<?= strtolower($employe['type_contrat']) ?>">
                            <?= htmlspecialchars($employe['type_contrat']) ?>
                        </span>
                    </td>
                    <td>
                        <span class="badge badge-<?= $employe['role'] ?>">
                            <?= htmlspecialchars(ucfirst($employe['role'])) ?>
                        </span>
                    </td>
                    <td>
                        <span class="badge badge-<?= $employe['statut'] ?>">
                            <?= htmlspecialchars(ucfirst($employe['statut'])) ?>
                        </span>
                    </td>
                    <td class="actions">
                        <a href="/SmartPayrollApp/app/controllers/AdminController.php?action=showEditEmploye&id=<?= $employe['id_employe'] ?>" class="btn btn-sm btn-warning">✏️ Modifier</a>
                        <?php if ($employe['statut'] === 'actif'): ?>
                            <a href="/SmartPayrollApp/app/controllers/AdminController.php?action=toggleEmployeStatus&id=<?= $employe['id_employe'] ?>&statut=inactif" class="btn btn-sm btn-danger" onclick="return confirm('Désactiver cet employé ?')">⏸️ Désactiver</a>
                        <?php else: ?>
                            <a href="/SmartPayrollApp/app/controllers/AdminController.php?action=toggleEmployeStatus&id=<?= $employe['id_employe'] ?>&statut=actif" class="btn btn-sm btn-success" onclick="return confirm('Activer cet employé ?')">▶️ Activer</a>
                        <?php endif; ?>
                        <a href="/SmartPayrollApp/app/controllers/AdminController.php?action=deleteEmploye&id=<?= $employe['id_employe'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Supprimer définitivement cet employé ?')">🗑️ Supprimer</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>
</html>