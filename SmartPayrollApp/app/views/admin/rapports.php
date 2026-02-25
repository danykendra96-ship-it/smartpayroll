<?php
require_once __DIR__ . '/../../core/Session.php';
\App\Core\Session::requireRole('admin');
$statsPaie = $statsPaie ?? [];
$bulletinsMois = $bulletinsMois ?? [];
$congesAttente = $congesAttente ?? [];
$employesActifs = $employesActifs ?? [];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Rapports - SmartPayroll</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', sans-serif; background: #f1f5f9; }
        .container { max-width: 1400px; margin: 0 auto; padding: 20px; }
        header { background: white; padding: 20px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; }
        .section { background: white; padding: 24px; border-radius: 12px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); margin-bottom: 24px; }
        .section h2 { margin-bottom: 16px; color: #1e293b; font-size: 18px; }
        .btn { display: inline-block; padding: 8px 16px; background: #2563eb; color: white; text-decoration: none; border-radius: 6px; font-weight: 600; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #e2e8f0; }
        th { background: #f8fafc; font-weight: 600; }
        .stat-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; margin-bottom: 24px; }
        .stat-card { background: linear-gradient(135deg, #2563eb, #1d4ed8); color: white; padding: 20px; border-radius: 12px; }
        .stat-card h3 { font-size: 14px; opacity: 0.9; margin-bottom: 4px; }
        .stat-card p { font-size: 24px; font-weight: 700; }
    </style>
</head>
<body>
    <div class="container">
        <header>
            <h1><i class="fas fa-chart-bar"></i> Rapports</h1>
            <a href="/SmartPayrollApp/app/controllers/AdminController.php?action=dashboard" class="btn" style="background:#64748b;">← Dashboard</a>
        </header>

        <div class="stat-grid">
            <div class="stat-card"><h3>Employés actifs</h3><p><?= count($employesActifs) ?></p></div>
            <div class="stat-card"><h3>Bulletins ce mois</h3><p><?= count($bulletinsMois) ?></p></div>
            <div class="stat-card"><h3>Congés en attente</h3><p><?= count($congesAttente) ?></p></div>
            <div class="stat-card"><h3>Départements</h3><p><?= count($statsPaie) ?></p></div>
        </div>

        <div class="section">
            <h2><i class="fas fa-chart-pie"></i> Statistiques de paie par département</h2>
            <table><thead><tr><th>Département</th><th>Nb employés</th><th>Total salaire net (payé)</th><th>Moyenne salaire net</th></tr></thead><tbody>
                <?php foreach ($statsPaie as $s): ?>
                <tr><td><?= htmlspecialchars($s['departement'] ?? '-') ?></td><td><?= (int)($s['nb_employes'] ?? 0) ?></td><td><?= number_format((float)($s['total_salaire_net'] ?? 0), 0, ',', ' ') ?> Ar</td><td><?= number_format((float)($s['moyenne_salaire_net'] ?? 0), 0, ',', ' ') ?> Ar</td></tr>
                <?php endforeach; ?>
                <?php if (empty($statsPaie)): ?><tr><td colspan="4" style="color:#64748b;">Aucune donnée.</td></tr><?php endif; ?>
            </tbody></table>
        </div>

        <div class="section">
            <h2><i class="fas fa-file-invoice-dollar"></i> Bulletins du mois en cours</h2>
            <table><thead><tr><th>Matricule</th><th>Employé</th><th>Type</th><th>Mois/Année</th><th>Salaire brut</th><th>Salaire net</th><th>Statut</th><th>Mode paiement</th></tr></thead><tbody>
                <?php foreach ($bulletinsMois as $b): ?>
                <tr><td><?= htmlspecialchars($b['matricule'] ?? '') ?></td><td><?= htmlspecialchars($b['employe'] ?? '') ?></td><td><?= htmlspecialchars($b['type_contrat'] ?? '') ?></td><td><?= (int)($b['mois'] ?? 0) ?>/<?= (int)($b['annee'] ?? 0) ?></td><td><?= number_format((float)($b['salaire_base'] ?? 0), 0, ',', ' ') ?> Ar</td><td><?= number_format((float)($b['salaire_net'] ?? 0), 0, ',', ' ') ?> Ar</td><td><?= htmlspecialchars($b['statut'] ?? '') ?></td><td><?= htmlspecialchars($b['mode_paiement'] ?? '') ?></td></tr>
                <?php endforeach; ?>
                <?php if (empty($bulletinsMois)): ?><tr><td colspan="8" style="color:#64748b;">Aucun bulletin pour ce mois.</td></tr><?php endif; ?>
            </tbody></table>
        </div>

        <div class="section">
            <h2><i class="fas fa-umbrella-beach"></i> Congés en attente d'approbation</h2>
            <table><thead><tr><th>Matricule</th><th>Employé</th><th>Date début</th><th>Date fin</th><th>Jours</th><th>Type</th><th>Motif</th></tr></thead><tbody>
                <?php foreach ($congesAttente as $c): ?>
                <tr><td><?= htmlspecialchars($c['matricule'] ?? '') ?></td><td><?= htmlspecialchars($c['employe'] ?? '') ?></td><td><?= htmlspecialchars($c['date_debut'] ?? '') ?></td><td><?= htmlspecialchars($c['date_fin'] ?? '') ?></td><td><?= (int)($c['nb_jours'] ?? 0) ?></td><td><?= htmlspecialchars($c['type_conge'] ?? '') ?></td><td><?= htmlspecialchars($c['motif'] ?? '-') ?></td></tr>
                <?php endforeach; ?>
                <?php if (empty($congesAttente)): ?><tr><td colspan="7" style="color:#64748b;">Aucun congé en attente.</td></tr><?php endif; ?>
            </tbody></table>
        </div>

        <div class="section">
            <h2><i class="fas fa-users"></i> Employés actifs (résumé)</h2>
            <table><thead><tr><th>Matricule</th><th>Nom complet</th><th>Poste</th><th>Département</th><th>Type contrat</th><th>Salaire poste</th></tr></thead><tbody>
                <?php foreach (array_slice($employesActifs, 0, 20) as $e): ?>
                <tr><td><?= htmlspecialchars($e['matricule'] ?? '') ?></td><td><?= htmlspecialchars($e['nom_complet'] ?? '') ?></td><td><?= htmlspecialchars($e['poste'] ?? '') ?></td><td><?= htmlspecialchars($e['departement'] ?? '') ?></td><td><?= htmlspecialchars($e['type_contrat'] ?? '') ?></td><td><?= number_format((float)($e['salaire_poste'] ?? 0), 0, ',', ' ') ?> Ar</td></tr>
                <?php endforeach; ?>
                <?php if (empty($employesActifs)): ?><tr><td colspan="6" style="color:#64748b;">Aucun employé actif.</td></tr><?php endif; ?>
            </tbody></table>
            <?php if (count($employesActifs) > 20): ?><p style="margin-top:12px;color:#64748b;">Affichage des 20 premiers sur <?= count($employesActifs) ?>.</p><?php endif; ?>
        </div>
    </div>
</body>
</html>
