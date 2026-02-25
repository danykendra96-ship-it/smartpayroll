<?php
require_once __DIR__ . '/../../core/Session.php';
\App\Core\Session::requireRole('admin');
$entreprise = $entreprise ?? [];
$isEdit = !empty($entreprise['id_entreprise']);
$error = $_GET['error'] ?? null;
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title><?= $isEdit ? 'Modifier' : 'Ajouter' ?> Entreprise - SmartPayroll</title>
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
        .btn:hover { opacity: 0.9; }
        .box { background: white; padding: 30px; border-radius: 12px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
    </style>
</head>
<body>
    <div class="container">
        <header>
            <h1><?= $isEdit ? '✏️ Modifier' : '➕ Ajouter' ?> une Entreprise</h1>
            <a href="/SmartPayrollApp/app/controllers/AdminController.php?action=gestionEntreprise" class="btn" style="background:#64748b;">← Retour</a>
        </header>
        <?php if ($error === 'champs_vides'): ?><div class="alert-error">Veuillez remplir le nom.</div><?php endif; ?>
        <div class="box">
            <form method="POST" action="/SmartPayrollApp/app/controllers/AdminController.php?action=<?= $isEdit ? 'editEntreprise' : 'addEntreprise' ?>">
                <?php if ($isEdit): ?><input type="hidden" name="id" value="<?= (int)$entreprise['id_entreprise'] ?>"><?php endif; ?>
                <div class="form-group">
                    <label>Nom *</label>
                    <input type="text" name="nom" class="form-control" value="<?= htmlspecialchars($entreprise['nom'] ?? '') ?>" required>
                </div>
                <div class="form-group">
                    <label>Adresse *</label>
                    <input type="text" name="adresse" class="form-control" value="<?= htmlspecialchars($entreprise['adresse'] ?? '') ?>" required>
                </div>
                <div class="form-group">
                    <label>NIF *</label>
                    <input type="text" name="nif" class="form-control" value="<?= htmlspecialchars($entreprise['nif'] ?? '') ?>" required placeholder="Numéro d'Identification Fiscale">
                </div>
                <div class="form-group">
                    <label>RC (Registre du Commerce)</label>
                    <input type="text" name="rc" class="form-control" value="<?= htmlspecialchars($entreprise['rc'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label>CNSS</label>
                    <input type="text" name="cnss" class="form-control" value="<?= htmlspecialchars($entreprise['cnss'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label>Téléphone</label>
                    <input type="text" name="telephone" class="form-control" value="<?= htmlspecialchars($entreprise['telephone'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($entreprise['email'] ?? '') ?>">
                </div>
                <button type="submit" class="btn"><?= $isEdit ? 'Enregistrer' : 'Ajouter' ?></button>
                <a href="/SmartPayrollApp/app/controllers/AdminController.php?action=gestionEntreprise" class="btn" style="background:#64748b;">Annuler</a>
            </form>
        </div>
    </div>
</body>
</html>
