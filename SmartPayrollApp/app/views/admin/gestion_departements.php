<?php
require_once __DIR__ . '/../../core/Session.php';
\App\Core\Session::requireRole('admin');
$error = $_GET['error'] ?? null;
$success = $_GET['success'] ?? null;
$departements = $departements ?? [];
$msgE = ['champs_vides'=>'Veuillez remplir.','invalid'=>'ID invalide.','has_postes'=>'Impossible: postes rattachés.'];
$msgS = ['departement_ajoute'=>'Département ajouté.','departement_modifie'=>'Modifié.','departement_supprime'=>'Supprimé.'];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Gestion Départements - SmartPayroll</title>
    <style>body{font-family:sans-serif;padding:40px;background:#f1f5f9;} .box{background:white;padding:30px;border-radius:12px;max-width:600px;} a{color:#2563eb;}</style>
</head>
<body>
    <div class="container" style="max-width:1200px;margin:0 auto;">
        <header style="background:white;padding:20px;margin-bottom:20px;display:flex;justify-content:space-between;">
            <h1>Gestion Départements</h1>
            <div>
                <a href="/SmartPayrollApp/app/controllers/AdminController.php?action=dashboard" style="padding:10px;background:#64748b;color:white;text-decoration:none;">← Dashboard</a>
                <a href="/SmartPayrollApp/app/controllers/AdminController.php?action=showAddDepartement" style="padding:10px;background:#2563eb;color:white;text-decoration:none;">➕ Ajouter</a>
            </div>
        </header>
        <?php if ($error): ?><div style="background:#fee2e2;padding:12px;margin-bottom:20px;"><?= htmlspecialchars($msgE[$error] ?? $error) ?></div><?php endif; ?>
        <?php if ($success): ?><div style="background:#dcfce7;padding:12px;margin-bottom:20px;"><?= htmlspecialchars($msgS[$success] ?? $success) ?></div><?php endif; ?>
        <table style="width:100%;background:white;border-collapse:collapse;">
            <thead><tr style="background:#f8fafc;"><th style="padding:15px;">ID</th><th>Libellé</th><th>Entreprise</th><th>Actions</th></tr></thead>
            <tbody>
                <?php foreach ($departements as $d): ?>
                <tr><td><?= (int)($d['id_departement']??0) ?></td><td><?= htmlspecialchars($d['libelle']??'') ?></td><td><?= htmlspecialchars($d['entreprise']??'-') ?></td>
                <td>
                    <a href="/SmartPayrollApp/app/controllers/AdminController.php?action=showEditDepartement&id=<?= $d['id_departement'] ?>">Modifier</a>
                    <a href="/SmartPayrollApp/app/controllers/AdminController.php?action=deleteDepartement&id=<?= $d['id_departement'] ?>" onclick="return confirm('Supprimer ?');">Supprimer</a>
                </td></tr>
                <?php endforeach; ?>
                <?php if (empty($departements)): ?><tr><td colspan="4" style="text-align:center;">Aucun département.</td></tr><?php endif; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
