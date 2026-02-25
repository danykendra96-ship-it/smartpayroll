<?php
require_once __DIR__ . '/../../core/Session.php';
\App\Core\Session::requireRole('admin');
$error = $_GET['error'] ?? null;
$success = $_GET['success'] ?? null;
$postes = $postes ?? [];
$msgError = ['champs_vides' => 'Veuillez remplir le libellé.', 'invalid' => 'ID invalide.', 'not_found' => 'Poste non trouvé.', 'has_employes' => 'Impossible : des employés sont rattachés.', 'erreur' => 'Erreur.'];
$msgSuccess = ['poste_ajoute' => 'Poste ajouté.', 'poste_modifie' => 'Poste modifié.', 'poste_supprime' => 'Poste supprimé.'];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Gestion Postes - SmartPayroll</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', sans-serif; background: #f1f5f9; }
        .container { max-width: 1200px; margin: 0 auto; padding: 20px; }
        header { background: white; padding: 20px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; }
        .alert { padding: 12px 15px; border-radius: 8px; margin-bottom: 20px; }
        .alert-error { background: #fee2e2; color: #991b1b; }
        .alert-success { background: #dcfce7; color: #166534; }
        .btn { display: inline-block; padding: 10px 20px; background: #2563eb; color: white; text-decoration: none; border-radius: 6px; font-weight: 600; }
        .btn-warning { background: #f59e0b; }
        .btn-danger { background: #ef4444; }
        .btn-sm { padding: 6px 12px; font-size: 14px; }
        table { width: 100%; background: white; border-radius: 12px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); overflow: hidden; }
        th, td { padding: 15px; text-align: left; border-bottom: 1px solid #e2e8f0; }
        th { background: #f8fafc; font-weight: 600; }
        .actions { display: flex; gap: 8px; }
    </style>
</head>
<body>
    <div class="container">
        <header>
            <h1><i class="fas fa-briefcase"></i> Gestion des Postes</h1>
            <div>
                <a href="/SmartPayrollApp/app/controllers/AdminController.php?action=dashboard" class="btn" style="background:#64748b;">← Dashboard</a>
                <a href="/SmartPayrollApp/app/controllers/AdminController.php?action=showAddPoste" class="btn">➕ Ajouter</a>
                <a href="/SmartPayrollApp/app/controllers/AuthController.php?action=logout" class="btn btn-danger">Déconnexion</a>
            </div>
        </header>
        <?php if ($error): ?><div class="alert alert-error"><?= htmlspecialchars($msgError[$error] ?? $error) ?></div><?php endif; ?>
        <?php if ($success): ?><div class="alert alert-success"><?= htmlspecialchars($msgSuccess[$success] ?? $success) ?></div><?php endif; ?>
        <table>
            <thead>
                <tr><th>ID</th><th>Libellé</th><th>Département</th><th>Salaire base</th><th>Actions</th></tr>
            </thead>
            <tbody>
                <?php foreach ($postes as $p): ?>
                <tr>
                    <td><?= (int)($p['id_poste'] ?? 0) ?></td>
                    <td><strong><?= htmlspecialchars($p['libelle'] ?? '') ?></strong></td>
                    <td><?= htmlspecialchars($p['departement'] ?? '-') ?></td>
                    <td><?= isset($p['salaire_base']) && $p['salaire_base'] !== null ? number_format((float)$p['salaire_base'], 0, ',', ' ') . ' Ar' : '-' ?></td>
                    <td class="actions">
                        <a href="/SmartPayrollApp/app/controllers/AdminController.php?action=showEditPoste&id=<?= $p['id_poste'] ?>" class="btn btn-sm btn-warning">✏️ Modifier</a>
                        <a href="/SmartPayrollApp/app/controllers/AdminController.php?action=deletePoste&id=<?= $p['id_poste'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Supprimer ?');">🗑️ Supprimer</a>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($postes)): ?><tr><td colspan="5" style="text-align:center;color:#64748b;">Aucun poste.</td></tr><?php endif; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
