<?php
require_once __DIR__ . '/../../core/Session.php';
\App\Core\Session::requireRole('admin');
$error = $_GET['error'] ?? null;
$success = $_GET['success'] ?? null;
$entreprises = $entreprises ?? [];
$msgError = ['champs_vides' => 'Veuillez remplir le nom.', 'invalid' => 'ID invalide.', 'not_found' => 'Entreprise non trouvée.', 'has_employes' => 'Impossible : des employés sont rattachés.', 'erreur' => 'Erreur.'];
$msgSuccess = ['entreprise_ajoutee' => 'Entreprise ajoutée.', 'entreprise_modifiee' => 'Entreprise modifiée.', 'entreprise_supprimee' => 'Entreprise supprimée.'];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Gestion Entreprises - SmartPayroll</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', sans-serif; background: #f1f5f9; }
        .container { max-width: 1200px; margin: 0 auto; padding: 20px; }
        header { background: white; padding: 20px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; }
        .alert { padding: 12px 15px; border-radius: 8px; margin-bottom: 20px; }
        .alert-error { background: #fee2e2; color: #991b1b; }
        .alert-success { background: #dcfce7; color: #166534; }
        .btn { display: inline-block; padding: 10px 20px; background: #2563eb; color: white; text-decoration: none; border-radius: 6px; font-weight: 600; border: none; cursor: pointer; }
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
            <h1><i class="fas fa-building"></i> Gestion des Entreprises</h1>
            <div>
                <a href="/SmartPayrollApp/app/controllers/AdminController.php?action=dashboard" class="btn" style="background:#64748b;">← Dashboard</a>
                <a href="/SmartPayrollApp/app/controllers/AdminController.php?action=showAddEntreprise" class="btn">➕ Ajouter</a>
                <a href="/SmartPayrollApp/app/controllers/AuthController.php?action=logout" class="btn btn-danger">Déconnexion</a>
            </div>
        </header>
        <?php if ($error): ?><div class="alert alert-error"><?= htmlspecialchars($msgError[$error] ?? $error) ?></div><?php endif; ?>
        <?php if ($success): ?><div class="alert alert-success"><?= htmlspecialchars($msgSuccess[$success] ?? $success) ?></div><?php endif; ?>
        <table>
            <thead>
                <tr><th>ID</th><th>Nom</th><th>NIF</th><th>Adresse</th><th>Téléphone</th><th>Actions</th></tr>
            </thead>
            <tbody>
                <?php foreach ($entreprises as $e): ?>
                <tr>
                    <td><?= (int)($e['id_entreprise'] ?? 0) ?></td>
                    <td><strong><?= htmlspecialchars($e['nom'] ?? '') ?></strong></td>
                    <td><?= htmlspecialchars($e['nif'] ?? '-') ?></td>
                    <td><?= htmlspecialchars($e['adresse'] ?? '-') ?></td>
                    <td><?= htmlspecialchars($e['telephone'] ?? '-') ?></td>
                    <td class="actions">
                        <a href="/SmartPayrollApp/app/controllers/AdminController.php?action=showEditEntreprise&id=<?= $e['id_entreprise'] ?>" class="btn btn-sm btn-warning">✏️ Modifier</a>
                        <a href="/SmartPayrollApp/app/controllers/AdminController.php?action=deleteEntreprise&id=<?= $e['id_entreprise'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Supprimer ?');">🗑️ Supprimer</a>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($entreprises)): ?><tr><td colspan="6" style="text-align:center;color:#64748b;">Aucune entreprise. <a href="/SmartPayrollApp/app/controllers/AdminController.php?action=showAddEntreprise">Ajouter</a></td></tr><?php endif; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
