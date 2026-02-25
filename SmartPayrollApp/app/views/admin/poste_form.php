<?php
require_once __DIR__ . '/../../core/Session.php';
\App\Core\Session::requireRole('admin');
$poste = $poste ?? [];
$departements = $departements ?? [];
$isEdit = !empty($poste['id_poste']);
$error = $_GET['error'] ?? null;
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title><?= $isEdit ? 'Modifier' : 'Ajouter' ?> Poste - SmartPayroll</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', sans-serif; background: #f1f5f9; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        header { background: white; padding: 20px; margin-bottom: 30px; display: flex; justify-content: space-between; align-items: center; }
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
            <h1><?= $isEdit ? 'Modifier' : 'Ajouter' ?> un Poste</h1>
            <a href="/SmartPayrollApp/app/controllers/AdminController.php?action=gestionPostes" class="btn" style="background:#64748b;">Retour</a>
        </header>
        <?php if ($error === 'champs_vides'): ?><div class="alert-error">Veuillez remplir le libelle.</div><?php endif; ?>
        <div class="box">
            <form method="POST" action="/SmartPayrollApp/app/controllers/AdminController.php?action=<?= $isEdit ? 'editPoste' : 'addPoste' ?>">
                <?php if ($isEdit): ?><input type="hidden" name="id" value="<?= (int)$poste['id_poste'] ?>"><?php endif; ?>
                <div class="form-group">
                    <label>Departement *</label>
                    <select name="id_departement" class="form-control" required>
                        <?php foreach ($departements as $d): ?>
                        <option value="<?= $d['id_departement'] ?>" <?= ($poste['id_departement'] ?? 0) == $d['id_departement'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($d['libelle']) ?>
                        </option>
                        <?php endforeach; ?>
                        <?php if (empty($departements)): ?><option value="1">Par defaut</option><?php endif; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Libelle *</label>
                    <input type="text" name="libelle" class="form-control" value="<?= htmlspecialchars($poste['libelle'] ?? '') ?>" required>
                </div>
                <div class="form-group">
                    <label>Salaire de base (Ar)</label>
                    <input type="number" name="salaire_base" class="form-control" step="0.01" value="<?= htmlspecialchars($poste['salaire_base'] ?? '') ?>" placeholder="Optionnel">
                </div>
                <div class="form-group">
                    <label>Description</label>
                    <textarea name="description" class="form-control" rows="3"><?= htmlspecialchars($poste['description'] ?? '') ?></textarea>
                </div>
                <button type="submit" class="btn"><?= $isEdit ? 'Enregistrer' : 'Ajouter' ?></button>
                <a href="/SmartPayrollApp/app/controllers/AdminController.php?action=gestionPostes" class="btn" style="background:#64748b;">Annuler</a>
            </form>
        </div>
    </div>
</body>
</html>
