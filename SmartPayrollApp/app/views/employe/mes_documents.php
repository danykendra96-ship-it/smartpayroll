<?php
require_once __DIR__ . '/../../core/Session.php';
\App\Core\Session::start();
\App\Core\Session::requireRole('employe');
$bulletins = $bulletins ?? [];
$moisNoms = $moisNoms ?? [1=>'Janvier',2=>'Février',3=>'Mars',4=>'Avril',5=>'Mai',6=>'Juin',7=>'Juillet',8=>'Août',9=>'Septembre',10=>'Octobre',11=>'Novembre',12=>'Décembre'];
?>
<!DOCTYPE html>
<html lang="fr"><head><meta charset="UTF-8"><title>Mes Documents | SmartPayroll</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>*{margin:0;padding:0;box-sizing:border-box}body{font-family:sans-serif;background:#f1f5f9}.wrap{display:flex;min-height:100vh}.sidebar{width:220px;background:#fff;padding:20px 0;box-shadow:0 2px 8px rgba(0,0,0,.08)}.sidebar a{display:flex;align-items:center;gap:10px;padding:12px 20px;color:#64748b;text-decoration:none}.sidebar a:hover{background:#dbeafe;color:#2563eb}.sidebar a.active{background:#2563eb;color:#fff}.main{flex:1;padding:24px}.card{background:#fff;border-radius:12px;box-shadow:0 1px 3px rgba(0,0,0,.08);padding:24px}.doc-item{display:flex;justify-content:space-between;align-items:center;padding:16px;border-bottom:1px solid #e2e8f0}.doc-item:hover{background:#f8fafc}.btn{display:inline-flex;align-items:center;gap:8px;padding:8px 16px;background:#2563eb;color:#fff;text-decoration:none;border-radius:8px;font-weight:600}</style>
</head><body>
<div class="wrap">
<aside class="sidebar">
<div style="padding:0 20px 16px;font-weight:700">SmartPayroll</div>
<a href="/SmartPayrollApp/app/controllers/EmployeController.php?action=dashboard"><i class="fas fa-home"></i> Dashboard</a>
<a href="/SmartPayrollApp/app/controllers/EmployeController.php?action=mesBulletins"><i class="fas fa-file-invoice"></i> Mes Bulletins</a>
<a href="/SmartPayrollApp/app/controllers/EmployeController.php?action=monProfil"><i class="fas fa-user"></i> Mon Profil</a>
<a href="/SmartPayrollApp/app/controllers/EmployeController.php?action=mesConges"><i class="fas fa-umbrella-beach"></i> Mes Congés</a>
<a href="/SmartPayrollApp/app/controllers/EmployeController.php?action=mesDocuments" class="active"><i class="fas fa-download"></i> Documents</a>
<a href="/SmartPayrollApp/app/controllers/AuthController.php?action=logout" style="color:#ef4444;margin-top:16px"><i class="fas fa-sign-out-alt"></i> Déconnexion</a>
</aside>
<main class="main">
<h1 style="margin-bottom:24px">Mes Documents</h1>
<div class="card">
<h2 style="margin-bottom:16px;font-size:16px">Bulletins de paie</h2>
<?php foreach ($bulletins as $b): 
    $mois = (int)($b['mois']??0); $annee = (int)($b['annee']??0);
    $lib = ($moisNoms[$mois]??$mois).' '.$annee;
?>
<div class="doc-item">
<span><i class="fas fa-file-pdf" style="color:#ef4444;margin-right:8px"></i> Bulletin <?= htmlspecialchars($lib) ?></span>
<a href="/SmartPayrollApp/app/controllers/EmployeController.php?action=telechargerPDF&id=<?= $b['id_bulletin'] ?>" class="btn"><i class="fas fa-download"></i> Télécharger</a>
</div>
<?php endforeach; ?>
<?php if (empty($bulletins)): ?><p style="padding:24px;color:#64748b;text-align:center">Aucun bulletin disponible.</p><?php endif; ?>
</div>
</main></div></body></html>
