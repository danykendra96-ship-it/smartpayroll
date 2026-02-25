<?php
require_once __DIR__ . '/../../core/Session.php';
\App\Core\Session::requireRole('admin');
$departement = $departement ?? [];
$entreprises = $entreprises ?? [];
$isEdit = !empty($departement['id_departement']);
$error = $_GET['error'] ?? null;
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title><?= $isEdit ? 'Modifier' : 'Ajouter' ?> Département - SmartPayroll</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', sans-serif; background: #f1f5f9; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        header { background: white; padding: 20px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); margin-bottom: 30px; display: flex; justify-content: space-between; align-items: center; }
        .alert-error { padding: 12px; background: #fee2e2; color: #991b1b; border-radius: 8px; margin-bottom: 20px; }
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; margin-bottom: 8px; font-weight: 600; }
        .form-control { width: 100%; padding: 12px; border: 2px solid #e2e8f0; border-radius: 8px; }
        .btn { display: inline-block; padding: 10px 20px; background: #2563eb; color: white; text-decoration: none; border-radius: 6px; font-weight: 600; border: none; cursor: pointer; }
        .box { background: white; padding: 30px; border-radius: 12px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
    </style>
</head>
<body>
    <div class="container">
        <header>
            <h1><?= $isEdit ? '✏️ Modifier' : '➕ Ajouter' ?> un Département</h1>
            <a href="/SmartPayrollApp/app/controllers/AdminController.php?action=gestionDepartements" class="btn" style="background:#64748b;">← Retour</a>
        </header>
        <?php if ($error === 'champs_vides'): ?><div class="alert-error">Veuillez remplir le libellé.</div><?php endif; ?>
        <div class="box">
            <form method="POST" action="/SmartPayrollApp/app/controllers/AdminController.php?action=<?= $isEdit ? 'editDepartement' : 'addDepartement' ?>">
                <?php if ($isEdit): ?><input type="hidden" name="id" value="<?= (int)$departement['id_departement'] ?>"><?php endif; ?>
                <div class="form-group">
                    <label>Entreprise</label>
                    <select name="id_entreprise" class="form-control">
                        <?php foreach ($entreprises as $e): ?>
                        <option value="<?= $e['id_entreprise'] ?>" <?= ($departement['id_entreprise'] ?? 1) == $e['id_entreprise'] ? 'selected' : '' ?>><?= htmlspecialchars($e['nom']) ?></option>
                        <?php endforeach; ?>
                        <?php if (empty($entreprises)): ?><option value="1">Par défaut</option><?php endif; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Libellé *</label>
                    <input type="text" name="libelle" class="form-control" value="<?= htmlspecialchars($departement['libelle'] ?? '') ?>" required>
                </div>
                <div class="form-group">
                    <label>Description</label>
                    <textarea name="description" class="form-control" rows="3"><?= htmlspecialchars($departement['description'] ?? '') ?></textarea>
                </div>
                <button type="submit" class="btn"><?= $isEdit ? 'Enregistrer' : 'Ajouter' ?></button>
                <a href="/SmartPayrollApp/app/controllers/AdminController.php?action=gestionDepartements" class="btn" style="background:#64748b;">Annuler</a>
            </form>
        </div>
    </div>
</body>
</html>
