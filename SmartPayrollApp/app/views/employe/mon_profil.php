<?php
require_once __DIR__ . '/../../core/Session.php';
\App\Core\Session::start();
\App\Core\Session::requireRole('employe');
$employe = $employe ?? [];
$success = $success ?? null;
?>
<!DOCTYPE html>
<html lang="fr"><head><meta charset="UTF-8"><title>Mon Profil | SmartPayroll</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>*{margin:0;padding:0;box-sizing:border-box}body{font-family:sans-serif;background:#f1f5f9}.wrap{display:flex;min-height:100vh}.sidebar{width:220px;background:#fff;padding:20px 0;box-shadow:0 2px 8px rgba(0,0,0,.08)}.sidebar a{display:flex;align-items:center;gap:10px;padding:12px 20px;color:#64748b;text-decoration:none}.sidebar a:hover{background:#dbeafe;color:#2563eb}.sidebar a.active{background:#2563eb;color:#fff}.main{flex:1;padding:24px}.card{background:#fff;border-radius:12px;box-shadow:0 1px 3px rgba(0,0,0,.08);padding:24px;margin-bottom:24px}.btn{display:inline-flex;align-items:center;gap:8px;padding:10px 20px;background:#2563eb;color:#fff;text-decoration:none;border-radius:8px;font-weight:600}.row{padding:12px 0;border-bottom:1px solid #e2e8f0}.row strong{display:inline-block;width:180px;color:#64748b}.alert{padding:12px;border-radius:8px;margin-bottom:20px}.alert-success{background:#dcfce7;color:#166534}</style>
</head><body>
<div class="wrap">
<aside class="sidebar">
<div style="padding:0 20px 16px;font-weight:700">SmartPayroll</div>
<a href="/SmartPayrollApp/app/controllers/EmployeController.php?action=dashboard"><i class="fas fa-home"></i> Dashboard</a>
<a href="/SmartPayrollApp/app/controllers/EmployeController.php?action=mesBulletins"><i class="fas fa-file-invoice"></i> Mes Bulletins</a>
<a href="/SmartPayrollApp/app/controllers/EmployeController.php?action=monProfil" class="active"><i class="fas fa-user"></i> Mon Profil</a>
<a href="/SmartPayrollApp/app/controllers/EmployeController.php?action=mesConges"><i class="fas fa-umbrella-beach"></i> Mes Congés</a>
<a href="/SmartPayrollApp/app/controllers/EmployeController.php?action=mesDocuments"><i class="fas fa-download"></i> Documents</a>
<a href="/SmartPayrollApp/app/controllers/AuthController.php?action=logout" style="color:#ef4444;margin-top:16px"><i class="fas fa-sign-out-alt"></i> Déconnexion</a>
</aside>
<main class="main">
<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:24px">
<h1>Mon Profil</h1>
<a href="/SmartPayrollApp/app/controllers/EmployeController.php?action=showModifierProfil" class="btn">Modifier</a>
<a href="/SmartPayrollApp/app/controllers/EmployeController.php?action=showChangerPassword" class="btn" style="background:#10b981">Changer mot de passe</a>
</div>
<?php if ($success === 'profil_modifie'): ?><div class="alert alert-success">Profil mis à jour.</div><?php endif; ?>
<?php if ($success === 'password_modifie'): ?><div class="alert alert-success">Mot de passe modifié.</div><?php endif; ?>
<div class="card">
<div class="row"><strong>Matricule</strong><?= htmlspecialchars($employe['matricule']??'') ?></div>
<div class="row"><strong>Nom</strong><?= htmlspecialchars($employe['nom']??'') ?></div>
<div class="row"><strong>Prénom</strong><?= htmlspecialchars($employe['prenom']??'') ?></div>
<div class="row"><strong>Email</strong><?= htmlspecialchars($employe['email']??'') ?></div>
<div class="row"><strong>Téléphone</strong><?= htmlspecialchars($employe['telephone']??'-') ?></div>
<div class="row"><strong>Adresse</strong><?= htmlspecialchars($employe['adresse']??'-') ?></div>
<div class="row"><strong>Poste</strong><?= htmlspecialchars($employe['poste']??'') ?></div>
<div class="row"><strong>Département</strong><?= htmlspecialchars($employe['departement']??'') ?></div>
<div class="row"><strong>Type de contrat</strong><?= htmlspecialchars($employe['type_contrat']??'') ?></div>
<div class="row"><strong>Date d'embauche</strong><?= htmlspecialchars($employe['date_embauche']??'') ?></div>
<div class="row"><strong>Solde congés</strong><?= (int)($employe['solde_conges']??0) ?> jours</div>
</div>
</main></div></body></html>
