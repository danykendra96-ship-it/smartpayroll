<?php
require_once __DIR__ . '/../../core/Session.php';
\App\Core\Session::requireRole('admin');

$error = $_GET['error'] ?? $_SESSION['error'] ?? null;
unset($_SESSION['error']);
$employe = $employe ?? [];
$postes = $postes ?? [];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Modifier un Employé - SmartPayroll</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #f1f5f9; }
        .container { max-width: 800px; margin: 0 auto; padding: 20px; }
        header { background: white; padding: 20px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; }
        .alert { padding: 12px 15px; border-radius: 8px; margin-bottom: 20px; font-size: 14px; }
        .alert-error { background: #fee2e2; color: #991b1b; }
        .btn { display: inline-block; padding: 10px 20px; background: #2563eb; color: white; text-decoration: none; border-radius: 6px; font-weight: 600; }
        .form-container { background: white; padding: 30px; border-radius: 12px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; margin-bottom: 8px; font-weight: 600; }
        .form-control { width: 100%; padding: 12px 15px; border: 2px solid #e2e8f0; border-radius: 8px; }
        .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
    </style>
</head>
<body>
    <div class="container">
        <header>
            <h1>✏️ Modifier l'Employé</h1>
            <div>
                <a href="/SmartPayrollApp/app/controllers/AdminController.php?action=listEmployes" class="btn">← Retour</a>
                <a href="/SmartPayrollApp/app/controllers/AuthController.php?action=logout" class="btn" style="background: #ef4444; margin-left: 10px;">Déconnexion</a>
            </div>
        </header>

        <?php if ($error): ?>
            <div class="alert alert-error"><?php
                $msg = ['email_existe' => 'Cet email existe déjà.', 'erreur_modification' => 'Erreur lors de la modification.'];
                echo htmlspecialchars($msg[$error] ?? $error);
            ?></div>
        <?php endif; ?>

        <div class="form-container">
            <form action="/SmartPayrollApp/app/controllers/AdminController.php?action=editEmploye" method="POST">
                <input type="hidden" name="id" value="<?= (int)($employe['id_employe'] ?? 0) ?>">
                <div class="form-row">
                    <div class="form-group">
                        <label>Nom *</label>
                        <input type="text" name="nom" class="form-control" value="<?= htmlspecialchars($employe['nom'] ?? '') ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Prénom *</label>
                        <input type="text" name="prenom" class="form-control" value="<?= htmlspecialchars($employe['prenom'] ?? '') ?>" required>
                    </div>
                </div>
                <div class="form-group">
                    <label>Email *</label>
                    <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($employe['email'] ?? '') ?>" required>
                </div>
                <div class="form-group">
                    <label>Téléphone</label>
                    <input type="tel" name="telephone" class="form-control" value="<?= htmlspecialchars($employe['telephone'] ?? '') ?>">
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Poste</label>
                        <select name="id_poste" class="form-control">
                            <?php foreach ($postes as $p): ?>
                                <option value="<?= $p['id_poste'] ?>" <?= ($employe['id_poste'] ?? 0) == $p['id_poste'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($p['poste']) ?> (<?= htmlspecialchars($p['departement']) ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Type contrat</label>
                        <select name="type_contrat" class="form-control">
                            <option value="CDI" <?= ($employe['type_contrat'] ?? '') === 'CDI' ? 'selected' : '' ?>>CDI</option>
                            <option value="CDD" <?= ($employe['type_contrat'] ?? '') === 'CDD' ? 'selected' : '' ?>>CDD</option>
                            <option value="Stage" <?= ($employe['type_contrat'] ?? '') === 'Stage' ? 'selected' : '' ?>>Stage</option>
                            <option value="Vacataire" <?= ($employe['type_contrat'] ?? '') === 'Vacataire' ? 'selected' : '' ?>>Vacataire</option>
                        </select>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Statut</label>
                        <select name="statut" class="form-control">
                            <option value="actif" <?= ($employe['statut'] ?? '') === 'actif' ? 'selected' : '' ?>>Actif</option>
                            <option value="inactif" <?= ($employe['statut'] ?? '') === 'inactif' ? 'selected' : '' ?>>Inactif</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Rôle</label>
                        <select name="role" class="form-control">
                            <option value="employe" <?= ($employe['role'] ?? '') === 'employe' ? 'selected' : '' ?>>Employé</option>
                            <option value="comptable" <?= ($employe['role'] ?? '') === 'comptable' ? 'selected' : '' ?>>Comptable</option>
                            <option value="admin" <?= ($employe['role'] ?? '') === 'admin' ? 'selected' : '' ?>>Admin</option>
                        </select>
                    </div>
                </div>
                <div style="margin-top: 24px;">
                    <button type="submit" class="btn">✅ Enregistrer</button>
                    <a href="/SmartPayrollApp/app/controllers/AdminController.php?action=listEmployes" class="btn" style="background: #64748b;">Annuler</a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
