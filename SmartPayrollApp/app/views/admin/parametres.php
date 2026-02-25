<?php
require_once __DIR__ . '/../../core/Session.php';
\App\Core\Session::requireRole('admin');
$error = $_GET['error'] ?? null;
$success = $_GET['success'] ?? null;
$modesPaiement = $modesPaiement ?? [];
$primes = $primes ?? [];
$retenues = $retenues ?? [];
$msgS = ['mode_ajoute'=>'Mode ajouté.','mode_modifie'=>'Mode modifié.','mode_supprime'=>'Mode supprimé.','prime_ajoutee'=>'Prime ajoutée.','prime_modifiee'=>'Prime modifiée.','prime_supprimee'=>'Prime supprimée.','retenue_ajoutee'=>'Retenue ajoutée.','retenue_modifiee'=>'Retenue modifiée.','retenue_supprimee'=>'Retenue supprimée.'];
$msgE = ['champs_vides'=>'Veuillez remplir les champs.','has_bulletins'=>'Impossible : utilisé dans des bulletins.'];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Paramètres - SmartPayroll</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', sans-serif; background: #f1f5f9; }
        .container { max-width: 1200px; margin: 0 auto; padding: 20px; }
        header { background: white; padding: 20px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; }
        .section { background: white; padding: 24px; border-radius: 12px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); margin-bottom: 24px; }
        .section h2 { margin-bottom: 16px; color: #1e293b; font-size: 18px; }
        .btn { display: inline-block; padding: 8px 16px; background: #2563eb; color: white; text-decoration: none; border-radius: 6px; font-weight: 600; font-size: 14px; border: none; cursor: pointer; }
        .btn-sm { padding: 4px 10px; font-size: 13px; }
        .btn-danger { background: #ef4444; }
        .btn-warning { background: #f59e0b; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #e2e8f0; }
        th { background: #f8fafc; font-weight: 600; }
        .form-inline { display: flex; gap: 8px; margin-bottom: 16px; flex-wrap: wrap; }
        .form-inline input, .form-inline select { padding: 8px 12px; border: 2px solid #e2e8f0; border-radius: 6px; }
        .alert { padding: 12px; border-radius: 8px; margin-bottom: 16px; }
        .alert-success { background: #dcfce7; color: #166534; }
        .alert-error { background: #fee2e2; color: #991b1b; }
    </style>
</head>
<body>
    <div class="container">
        <header>
            <h1><i class="fas fa-cog"></i> Paramètres</h1>
            <div>
                <a href="/SmartPayrollApp/app/controllers/AdminController.php?action=dashboard" class="btn" style="background:#64748b;">← Dashboard</a>
                <a href="/SmartPayrollApp/app/controllers/AuthController.php?action=logout" class="btn btn-danger">Déconnexion</a>
            </div>
        </header>
        <?php if ($error): ?><div class="alert alert-error"><?= htmlspecialchars($msgE[$error] ?? $error) ?></div><?php endif; ?>
        <?php if ($success): ?><div class="alert alert-success"><?= htmlspecialchars($msgS[$success] ?? $success) ?></div><?php endif; ?>

        <div class="section">
            <h2><i class="fas fa-credit-card"></i> Modes de paiement</h2>
            <form method="POST" action="/SmartPayrollApp/app/controllers/AdminController.php?action=addModePaiement" class="form-inline">
                <input type="text" name="libelle" placeholder="Libellé" required>
                <input type="text" name="description" placeholder="Description (optionnel)">
                <button type="submit" class="btn">Ajouter</button>
            </form>
            <table><thead><tr><th>ID</th><th>Libellé</th><th>Description</th><th>Actions</th></tr></thead><tbody>
                <?php foreach ($modesPaiement as $m): ?>
                <tr><td><?= $m['id_mode'] ?></td><td><?= htmlspecialchars($m['libelle']) ?></td><td><?= htmlspecialchars($m['description'] ?? '-') ?></td>
                <td><a href="/SmartPayrollApp/app/controllers/AdminController.php?action=deleteModePaiement&id=<?= $m['id_mode'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Supprimer ?');">Supprimer</a></td></tr>
                <?php endforeach; ?>
                <?php if (empty($modesPaiement)): ?><tr><td colspan="4" style="color:#64748b;">Aucun mode.</td></tr><?php endif; ?>
            </tbody></table>
        </div>

        <div class="section">
            <h2><i class="fas fa-gift"></i> Types de primes</h2>
            <form method="POST" action="/SmartPayrollApp/app/controllers/AdminController.php?action=addPrime" class="form-inline">
                <input type="text" name="libelle" placeholder="Libellé" required>
                <select name="type_prime"><option value="performance">Performance</option><option value="anciennete">Ancienneté</option><option value="prime_fin_annee">Fin d'année</option><option value="prime_exceptionnelle">Exceptionnelle</option><option value="autre">Autre</option></select>
                <input type="number" name="montant" placeholder="Montant" step="0.01" value="0" required>
                <input type="text" name="description" placeholder="Description (opt)">
                <label><input type="checkbox" name="est_fixe" checked> Fixe</label>
                <button type="submit" class="btn">Ajouter</button>
            </form>
            <table><thead><tr><th>ID</th><th>Libellé</th><th>Type</th><th>Montant</th><th>Fixe</th><th>Actions</th></tr></thead><tbody>
                <?php foreach ($primes as $p): ?>
                <tr><td><?= $p['id_prime'] ?></td><td><?= htmlspecialchars($p['libelle']) ?></td><td><?= htmlspecialchars($p['type_prime']) ?></td><td><?= number_format($p['montant'], 0, ',', ' ') ?> Ar</td><td><?= $p['est_fixe'] ? 'Oui' : 'Non' ?></td>
                <td><a href="/SmartPayrollApp/app/controllers/AdminController.php?action=deletePrime&id=<?= $p['id_prime'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Supprimer ?');">Supprimer</a></td></tr>
                <?php endforeach; ?>
                <?php if (empty($primes)): ?><tr><td colspan="6" style="color:#64748b;">Aucune prime.</td></tr><?php endif; ?>
            </tbody></table>
        </div>

        <div class="section">
            <h2><i class="fas fa-minus-circle"></i> Types de retenues</h2>
            <form method="POST" action="/SmartPayrollApp/app/controllers/AdminController.php?action=addRetenue" class="form-inline">
                <input type="text" name="libelle" placeholder="Libellé" required>
                <select name="type_retenue"><option value="absence">Absence</option><option value="avance">Avance</option><option value="pret">Prêt</option><option value="amende">Amende</option><option value="autre">Autre</option></select>
                <input type="number" name="montant" placeholder="Montant" step="0.01" value="0" required>
                <input type="text" name="description" placeholder="Description (opt)">
                <label><input type="checkbox" name="est_fixe" checked> Fixe</label>
                <button type="submit" class="btn">Ajouter</button>
            </form>
            <table><thead><tr><th>ID</th><th>Libellé</th><th>Type</th><th>Montant</th><th>Fixe</th><th>Actions</th></tr></thead><tbody>
                <?php foreach ($retenues as $r): ?>
                <tr><td><?= $r['id_retenue'] ?></td><td><?= htmlspecialchars($r['libelle']) ?></td><td><?= htmlspecialchars($r['type_retenue']) ?></td><td><?= number_format($r['montant'], 0, ',', ' ') ?> Ar</td><td><?= $r['est_fixe'] ? 'Oui' : 'Non' ?></td>
                <td><a href="/SmartPayrollApp/app/controllers/AdminController.php?action=deleteRetenue&id=<?= $r['id_retenue'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Supprimer ?');">Supprimer</a></td></tr>
                <?php endforeach; ?>
                <?php if (empty($retenues)): ?><tr><td colspan="6" style="color:#64748b;">Aucune retenue.</td></tr><?php endif; ?>
            </tbody></table>
        </div>
    </div>
</body>
</html>
