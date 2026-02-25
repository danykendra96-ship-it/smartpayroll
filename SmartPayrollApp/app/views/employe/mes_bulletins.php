<?php
require_once __DIR__ . '/../../core/Session.php';
\App\Core\Session::start();
\App\Core\Session::requireRole('employe');
$bulletins = $bulletins ?? [];
$moisNoms = $moisNoms ?? [1=>'Janvier',2=>'Février',3=>'Mars',4=>'Avril',5=>'Mai',6=>'Juin',7=>'Juillet',8=>'Août',9=>'Septembre',10=>'Octobre',11=>'Novembre',12=>'Décembre'];
?>
<!DOCTYPE html>
<html lang="fr"><head><meta charset="UTF-8"><title>Mes Bulletins | SmartPayroll</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>*{margin:0;padding:0;box-sizing:border-box}body{font-family:sans-serif;background:#f1f5f9}.wrap{display:flex;min-height:100vh}.sidebar{width:220px;background:#fff;padding:20px 0;box-shadow:0 2px 8px rgba(0,0,0,.08)}.sidebar a{display:flex;align-items:center;gap:10px;padding:12px 20px;color:#64748b;text-decoration:none}.sidebar a:hover{background:#dbeafe;color:#2563eb}.sidebar a.active{background:#2563eb;color:#fff}.main{flex:1;padding:24px}.card{background:#fff;border-radius:12px;box-shadow:0 1px 3px rgba(0,0,0,.08);padding:24px}.btn{display:inline-flex;align-items:center;gap:8px;padding:8px 16px;background:#2563eb;color:#fff;text-decoration:none;border-radius:8px;font-weight:600}table{width:100%;border-collapse:collapse}th,td{padding:12px;text-align:left;border-bottom:1px solid #e2e8f0}th{background:#f8fafc}</style>
</head><body>
<div class="wrap">
<aside class="sidebar">
<div style="padding:0 20px 16px;font-weight:700">SmartPayroll</div>
<a href="/SmartPayrollApp/app/controllers/EmployeController.php?action=dashboard"><i class="fas fa-home"></i> Dashboard</a>
<a href="/SmartPayrollApp/app/controllers/EmployeController.php?action=mesBulletins" class="active"><i class="fas fa-file-invoice"></i> Mes Bulletins</a>
<a href="/SmartPayrollApp/app/controllers/EmployeController.php?action=monProfil"><i class="fas fa-user"></i> Mon Profil</a>
<a href="/SmartPayrollApp/app/controllers/EmployeController.php?action=mesConges"><i class="fas fa-umbrella-beach"></i> Mes Congés</a>
<a href="/SmartPayrollApp/app/controllers/EmployeController.php?action=mesDocuments"><i class="fas fa-download"></i> Documents</a>
<a href="/SmartPayrollApp/app/controllers/AuthController.php?action=logout" style="color:#ef4444;margin-top:16px"><i class="fas fa-sign-out-alt"></i> Déconnexion</a>
</aside>
<main class="main">
<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:24px">
<h1>Mes Bulletins de Paie</h1>
<a href="/SmartPayrollApp/app/controllers/EmployeController.php?action=dashboard" class="btn" style="background:#64748b">← Dashboard</a>
</div>
<div class="card">
    <h1>Mes Bulletins de Paie</h1>
    <a href="/SmartPayrollApp/app/controllers/EmployeController.php?action=dashboard" class="btn" style="background:#64748b;">← Dashboard</a>
</div>
<div class="card">
    <table style="width:100%; border-collapse: collapse;">
        <thead><tr style="background:#f8fafc;"><th style="padding:12px;">Mois/Année</th><th>Salaire base</th><th>Primes</th><th>Retenues</th><th>Salaire net</th><th>Statut</th><th>Actions</th></tr></thead>
        <tbody>
            <?php foreach ($bulletins as $b): 
                $mois = (int)($b['mois']??0); $annee = (int)($b['annee']??0);
                $lib = ($moisNoms[$mois]??$mois).' '.$annee;
            ?>
            <tr style="border-bottom:1px solid #e2e8f0;">
                <td style="padding:12px;"><?= htmlspecialchars($lib) ?></td>
                <td><?= number_format((float)($b['salaire_base']??0),0,',',' ') ?> Ar</td>
                <td><?= number_format((float)($b['primes_total']??0),0,',',' ') ?> Ar</td>
                <td><?= number_format((float)($b['retenues_total']??0),0,',',' ') ?> Ar</td>
                <td><strong><?= number_format((float)($b['salaire_net']??0),0,',',' ') ?> Ar</strong></td>
                <td><span style="padding:4px 10px;border-radius:20px;font-size:12px;background:#dbeafe;color:#1d4ed8;"><?= ucfirst($b['statut']??'') ?></span></td>
                <td>
                    <a href="/SmartPayrollApp/app/controllers/EmployeController.php?action=voirBulletin&id=<?= $b['id_bulletin'] ?>" class="btn" style="padding:6px 12px;font-size:13px;">Voir</a>
                    <a href="/SmartPayrollApp/app/controllers/EmployeController.php?action=telechargerPDF&id=<?= $b['id_bulletin'] ?>" class="btn" style="padding:6px 12px;font-size:13px;background:#10b981;">PDF</a>
                </td>
            </tr>
            <?php endforeach; ?>
            <?php if (empty($bulletins)): ?><tr><td colspan="7" style="padding:24px;text-align:center;color:#64748b;">Aucun bulletin.</td></tr><?php endif; ?>
        </tbody>
    </table>
</div>
</main></div></body></html>
