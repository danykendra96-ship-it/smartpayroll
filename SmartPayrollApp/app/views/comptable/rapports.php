<?php
require_once __DIR__ . '/../../core/Session.php';
\App\Core\Session::start();
\App\Core\Session::requireRole(['admin', 'comptable']);

$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'];
$scriptName = $_SERVER['SCRIPT_NAME'];
$projectPath = preg_replace('#^(/[^/]+).*#', '$1', $scriptName);
$baseUrl = $protocol . '://' . $host . $projectPath;

// ✅ CORRECTION : Normaliser mois/année
$mois = isset($_GET['mois']) ? (int)$_GET['mois'] : (int)date('n');
$annee = isset($_GET['annee']) ? (int)$_GET['annee'] : (int)date('Y');
$moisOptions = [1=>'Janvier',2=>'Février',3=>'Mars',4=>'Avril',5=>'Mai',6=>'Juin',7=>'Juillet',8=>'Août',9=>'Septembre',10=>'Octobre',11=>'Novembre',12=>'Décembre'];
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Rapports de Paie | SmartPayroll</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { margin:0; padding:0; box-sizing:border-box; }
        body { font-family:Arial; background:#f1f5f9; }
        .container { max-width:1200px; margin:40px auto; padding:20px; }
        .card { background:white; border-radius:12px; padding:30px; box-shadow:0 2px 10px rgba(0,0,0,0.1); margin-bottom:24px; }
        h1 { color:#2563eb; margin-bottom:20px; }
        .filters { display:flex; gap:16px; margin-bottom:24px; flex-wrap:wrap; }
        select, button { padding:10px 16px; border:2px solid #cbd5e1; border-radius:8px; font-size:16px; }
        button { background:#2563eb; color:white; cursor:pointer; border:none; }
        table { width:100%; border-collapse:collapse; margin-top:20px; }
        th, td { padding:12px; text-align:left; border-bottom:1px solid #e2e8f0; }
        th { background:#f8fafc; font-weight:600; }
        .stat { font-size:24px; font-weight:bold; color:#2563eb; }
    </style>
</head>
<body>
    <div class="container">
        <div class="card">
            <h1><i class="fas fa-chart-bar"></i> Rapports de Paie - <?= $moisOptions[$mois] ?> <?= $annee ?></h1>
            
            <div class="filters">
                <form method="GET" action="<?= $baseUrl ?>/app/controllers/ComptableController.php">
                    <input type="hidden" name="action" value="rapports">
                    <select name="mois">
                        <?php foreach ($moisOptions as $num => $nom): ?>
                            <option value="<?= $num ?>" <?= ($mois === $num) ? 'selected' : '' ?>><?= $nom ?></option>
                        <?php endforeach; ?>
                    </select>
                    <select name="annee">
                        <?php for($y=date('Y'); $y>=date('Y')-5; $y--): ?>
                            <option value="<?= $y ?>" <?= ($annee === $y) ? 'selected' : '' ?>><?= $y ?></option>
                        <?php endfor; ?>
                    </select>
                    <button type="submit"><i class="fas fa-search"></i> Générer Rapport</button>
                </form>
            </div>

            <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(250px,1fr)); gap:24px; margin:30px 0;">
                <div class="card" style="text-align:center; padding:24px;">
                    <h3>Total Bulletins</h3>
                    <div class="stat"><?= $stats['total_bulletins'] ?? 0 ?></div>
                </div>
                <div class="card" style="text-align:center; padding:24px;">
                    <h3>Total Payé</h3>
                    <div class="stat"><?= number_format($stats['total_paye'] ?? 0, 0, ',', ' ') ?> Ar</div>
                </div>
                <div class="card" style="text-align:center; padding:24px;">
                    <h3>Moyenne Salariale</h3>
                    <div class="stat"><?= number_format($stats['moyenne_salaire'] ?? 0, 0, ',', ' ') ?> Ar</div>
                </div>
            </div>

            <?php if (!empty($stats['par_departement'])): ?>
                <h2 style="margin:30px 0 20px; color:#1e293b;">Par Département</h2>
                <table>
                    <thead>
                        <tr>
                            <th>Département</th>
                            <th>Nombre de Bulletins</th>
                            <th>Total Salaire Net</th>
                            <th>Moyenne</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($stats['par_departement'] as $dept): ?>
                        <tr>
                            <td><?= htmlspecialchars($dept['departement']) ?></td>
                            <td><?= $dept['nb_bulletins'] ?></td>
                            <td><?= number_format($dept['total_salaire_net'], 0, ',', ' ') ?> Ar</td>
                            <td><?= number_format($dept['moyenne_salaire_net'], 0, ',', ' ') ?> Ar</td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p style="text-align:center; padding:40px; color:#64748b;">
                    <i class="fas fa-info-circle" style="font-size:48px; margin-bottom:16px;"></i><br>
                    Aucun bulletin trouvé pour ce mois. Générez d'abord des bulletins de paie.
                </p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>