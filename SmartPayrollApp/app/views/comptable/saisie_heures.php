<?php
/**
 * ============================================================================
 * SMARTPAYROLL - Formulaire de Saisie des Heures (Vacataires)
 * ============================================================================
 * 
 * Formulaire pour saisir les heures travaillées par un vacataire
 * 
 * @author [Ton nom] - Étudiante BTS Génie Logiciel
 * @establishment Institut Supérieur Mony Keng (ISMK)
 * @project Stage BTS - Digitalisation de la gestion des salaires
 * @version 1.0
 * @date Février 2026
 * ============================================================================
 */

require_once __DIR__ . '/../../core/Session.php';
\App\Core\Session::start();
\App\Core\Session::requireRole(['admin', 'comptable']);

// Récupérer les données passées par le contrôleur
// $vacataires, $mois, $annee, $vacataire, $error, $success

$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'];
$scriptName = $_SERVER['SCRIPT_NAME'];
$projectPath = preg_replace('#^(/[^/]+).*#', '$1', $scriptName);
$baseUrl = $protocol . '://' . $host . $projectPath;

$error = $_GET['error'] ?? null;
$success = $_GET['success'] ?? null;

$moisOptions = [
    1 => 'Janvier', 2 => 'Février', 3 => 'Mars', 4 => 'Avril',
    5 => 'Mai', 6 => 'Juin', 7 => 'Juillet', 8 => 'Août',
    9 => 'Septembre', 10 => 'Octobre', 11 => 'Novembre', 12 => 'Décembre'
];
$anneesOptions = range(date('Y'), date('Y') - 5);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Saisie Heures Vacataires | SmartPayroll - ISMK</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #2563eb; --primary-dark: #1d4ed8; --primary-light: #dbeafe;
            --secondary: #047857; --accent: #f59e0b; --danger: #ef4444;
            --success: #10b981; --warning: #f59e0b; --dark: #1e293b;
            --gray: #64748b; --border: #cbd5e1; --light: #f8fafc;
            --shadow: 0 1px 3px 0 rgba(0,0,0,0.1);
            --radius: 8px; --radius-lg: 12px;
        }
        * { margin:0; padding:0; box-sizing:border-box; }
        body { font-family:'Inter',sans-serif; background:#f1f5f9; color:var(--dark); }
        .dashboard { display:flex; min-height:100vh; }
        .sidebar { width:260px; background:white; box-shadow:var(--shadow); position:fixed; height:100vh; z-index:100; }
        .sidebar-header { padding:24px 20px; border-bottom:1px solid var(--border); text-align:center; }
        .sidebar-logo-badge { width:40px; height:40px; background:var(--primary); border-radius:var(--radius); display:flex; align-items:center; justify-content:center; color:white; font-weight:700; font-size:16px; }
        .sidebar-logo-text { font-size:18px; font-weight:700; color:var(--dark); margin-top:8px; }
        .sidebar-user { padding:16px 20px; border-bottom:1px solid var(--border); }
        .sidebar-user-avatar { width:40px; height:40px; background:var(--primary-light); border-radius:50%; display:flex; align-items:center; justify-content:center; color:var(--primary); font-weight:600; font-size:16px; }
        .sidebar-user-details h4 { font-size:15px; font-weight:600; margin-bottom:2px; }
        .sidebar-user-details p { font-size:13px; color:var(--gray); }
        .sidebar-menu { padding:16px 0; }
        .sidebar-menu-item { padding:12px 20px; display:flex; align-items:center; gap:12px; color:var(--gray); transition:all 0.3s; cursor:pointer; }
        .sidebar-menu-item:hover { background:var(--primary-light); color:var(--primary); }
        .sidebar-menu-item.active { background:var(--primary); color:white; }
        .main-content { flex:1; margin-left:260px; }
        .header { background:white; box-shadow:var(--shadow); padding:16px 32px; display:flex; justify-content:space-between; align-items:center; position:sticky; top:0; z-index:90; }
        .header h1 { font-size:24px; font-weight:700; }
        .header-user { display:flex; align-items:center; gap:12px; }
        .header-user-avatar { width:36px; height:36px; background:var(--primary-light); border-radius:50%; display:flex; align-items:center; justify-content:center; color:var(--primary); font-weight:600; font-size:14px; }
        .container { padding:32px; max-width:1000px; margin:0 auto; }
        .alert { padding:14px 20px; border-radius:var(--radius); margin-bottom:24px; display:flex; align-items:center; gap:12px; font-size:15px; font-weight:500; }
        .alert-error { background:#fee2e2; color:#991b1b; border:1px solid #fecaca; }
        .alert-success { background:#dcfce7; color:#166534; border:1px solid #bbf7d0; }
        .card { background:white; border-radius:var(--radius-lg); box-shadow:var(--shadow); padding:32px; margin-bottom:24px; }
        .card-header { display:flex; justify-content:space-between; align-items:center; margin-bottom:24px; padding-bottom:16px; border-bottom:2px solid var(--border); }
        .card-header h2 { font-size:20px; font-weight:700; color:var(--dark); }
        .form-grid { display:grid; grid-template-columns:repeat(auto-fit, minmax(300px, 1fr)); gap:24px; }
        .form-group { margin-bottom:20px; }
        .form-group label { display:block; margin-bottom:8px; font-weight:600; color:var(--dark); font-size:14px; }
        .form-control { width:100%; padding:12px 16px; border:2px solid var(--border); border-radius:var(--radius); font-size:16px; font-family:'Inter',sans-serif; transition:all 0.3s; }
        .form-control:focus { outline:none; border-color:var(--primary); box-shadow:0 0 0 3px rgba(37,99,235,0.1); }
        .form-row { display:flex; gap:16px; margin-bottom:16px; }
        .form-row .form-group { flex:1; }
        .btn { padding:12px 24px; border-radius:var(--radius); font-weight:600; cursor:pointer; transition:all 0.3s; border:none; display:inline-flex; align-items:center; gap:8px; font-family:'Inter',sans-serif; font-size:16px; }
        .btn-primary { background:var(--primary); color:white; }
        .btn-primary:hover { background:var(--primary-dark); transform:translateY(-2px); box-shadow:0 4px 12px rgba(37,99,235,0.3); }
        .btn-secondary { background:white; color:var(--primary); border:2px solid var(--primary); }
        .btn-secondary:hover { background:var(--primary); color:white; }
        .btn-large { padding:14px 28px; font-size:16px; }
        .summary-card { background:linear-gradient(135deg, #f59e0b, #d97706); color:white; border-radius:var(--radius-lg); padding:24px; margin-top:24px; }
        .summary-card h3 { font-size:18px; margin-bottom:16px; display:flex; align-items:center; gap:10px; }
        .summary-item { display:flex; justify-content:space-between; padding:12px 0; border-bottom:1px solid rgba(255,255,255,0.2); }
        .summary-item:last-child { border-bottom:none; font-weight:700; font-size:18px; margin-top:8px; padding-top:16px; }
        .info-box { background:#dbeafe; border-left:4px solid var(--primary); padding:16px; border-radius:var(--radius); margin-bottom:24px; }
        .info-box h4 { font-size:16px; margin-bottom:8px; color:var(--primary-dark); }
        .info-box p { font-size:14px; color:var(--dark); line-height:1.6; }
        @media (max-width:768px) {
            .dashboard { flex-direction:column; }
            .sidebar { width:100%; height:auto; position:relative; }
            .main-content { margin-left:0; }
            .form-grid { grid-template-columns:1fr; }
            .form-row { flex-direction:column; }
            .btn { width:100%; justify-content:center; }
        }
    </style>
</head>
<body>
    <div class="dashboard">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="sidebar-header">
                <div class="sidebar-logo-badge">ISMK</div>
                <div class="sidebar-logo-text">SmartPayroll</div>
            </div>
            <div class="sidebar-user">
                <div class="sidebar-user-info">
                    <div class="sidebar-user-avatar"><?= strtoupper(substr($_SESSION['user_prenom'], 0, 1)) ?></div>
                    <div class="sidebar-user-details">
                        <h4><?= htmlspecialchars($_SESSION['user_prenom'] . ' ' . $_SESSION['user_nom']) ?></h4>
                        <p>Comptable</p>
                    </div>
                </div>
            </div>
            <nav class="sidebar-menu">
                <a href="<?= $baseUrl ?>/app/controllers/ComptableController.php?action=dashboard" class="sidebar-menu-item">
                    <i class="fas fa-home"></i> Dashboard
                </a>
                <a href="<?= $baseUrl ?>/app/controllers/ComptableController.php?action=listBulletins" class="sidebar-menu-item">
                    <i class="fas fa-list"></i> Liste des Bulletins
                </a>
                <a href="<?= $baseUrl ?>/app/controllers/ComptableController.php?action=showGenererBulletin" class="sidebar-menu-item">
                    <i class="fas fa-calculator"></i> Générer Bulletin
                </a>
                <a href="<?= $baseUrl ?>/app/controllers/ComptableController.php?action=showSaisieHeures" class="sidebar-menu-item active">
                    <i class="fas fa-clock"></i> Saisie Heures
                </a>
                <a href="<?= $baseUrl ?>/app/controllers/ComptableController.php?action=rapports" class="sidebar-menu-item">
                    <i class="fas fa-chart-bar"></i> Rapports
                </a>
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <header class="header">
                <h1><i class="fas fa-stopwatch"></i> Saisie des Heures (Vacataires)</h1>
                <div class="header-user">
                    <div class="header-user-avatar"><?= strtoupper(substr($_SESSION['user_prenom'], 0, 1)) ?></div>
                    <div>
                        <div class="header-user-name"><?= htmlspecialchars($_SESSION['user_prenom']) ?></div>
                        <div class="header-user-role">Comptable</div>
                    </div>
                    <a href="<?= $baseUrl ?>/app/controllers/AuthController.php?action=logout" class="btn" style="background:var(--danger);color:white;margin-left:16px;">
                        <i class="fas fa-sign-out-alt"></i> Déconnexion
                    </a>
                </div>
            </header>

            <div class="container">
                <?php if ($error): ?>
                    <div class="alert alert-error">
                        <i class="fas fa-exclamation-triangle"></i>
                        <?php
                        $messages = [
                            'champs_vides' => 'Veuillez remplir tous les champs obligatoires.',
                            'bulletin_existe' => 'Un bulletin existe déjà pour ce vacataire ce mois-ci.',
                            'non_vacataire' => 'Cet employé n\'est pas un vacataire.',
                            'erreur_generation' => 'Erreur lors de la génération du bulletin.'
                        ];
                        echo htmlspecialchars($messages[$error] ?? 'Une erreur est survenue.');
                        ?>
                    </div>
                <?php endif; ?>

                <?php if ($success): ?>
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i>
                        <?= htmlspecialchars($success === 'bulletin_vacataire_genere' ? 'Bulletin vacataire généré avec succès !' : 'Opération réussie.'); ?>
                    </div>
                <?php endif; ?>

                <div class="info-box">
                    <h4><i class="fas fa-info-circle"></i> Informations importantes</h4>
                    <p>Ce formulaire permet de saisir les heures travaillées par un <strong>vacataire</strong> pour un mois donné. Le système calculera automatiquement :</p>
                    <ul style="margin-left:20px; margin-top:8px; line-height:1.8;">
                        <li>Le salaire normal (heures × taux horaire)</li>
                        <li>Le salaire des heures supplémentaires (heures sup × taux horaire × 1.5)</li>
                        <li>Les cotisations CNSS (1%)</li>
                        <li>L'impôt IRSA selon le barème malgache</li>
                        <li>Le salaire net à payer</li>
                    </ul>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h2><i class="fas fa-edit"></i> Formulaire de Saisie</h2>
                        <a href="<?= $baseUrl ?>/app/controllers/ComptableController.php?action=listBulletins" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Retour à la liste
                        </a>
                    </div>

                    <form action="<?= $baseUrl ?>/app/controllers/ComptableController.php?action=saisieHeures" method="POST">
                        <div class="form-grid">
                            <!-- Vacataire -->
                            <div class="form-group">
                                <label for="id_employe"><i class="fas fa-user-clock"></i> Vacataire *</label>
                                <select id="id_employe" name="id_employe" class="form-control" required onchange="updateTauxHoraire()">
                                    <option value="">Sélectionner un vacataire</option>
                                    <?php foreach ($vacataires as $vac): ?>
                                        <option value="<?= $vac['id_employe'] ?>" 
                                                data-taux="<?= $vac['taux_horaire'] ?? 10000 ?>">
                                            <?= htmlspecialchars($vac['matricule'] . ' - ' . $vac['prenom'] . ' ' . $vac['nom']) ?>
                                            <?php if (!empty($vac['poste'])): ?>
                                                - <?= htmlspecialchars($vac['poste']) ?>
                                            <?php endif; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <small style="color:var(--gray); font-size:13px; margin-top:6px; display:block;">
                                    <i class="fas fa-user-tag"></i> Seuls les vacataires actifs sont affichés
                                </small>
                            </div>

                            <!-- Mois et Année -->
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="mois"><i class="fas fa-calendar"></i> Mois *</label>
                                    <select id="mois" name="mois" class="form-control" required>
                                        <?php foreach ($moisOptions as $num => $nom): ?>
                                            <option value="<?= $num ?>" <?= ($mois == $num) ? 'selected' : '' ?>>
                                                <?= $nom ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="annee"><i class="fas fa-calendar-alt"></i> Année *</label>
                                    <select id="annee" name="annee" class="form-control" required>
                                        <?php foreach ($anneesOptions as $anneeOpt): ?>
                                            <option value="<?= $anneeOpt ?>" <?= ($annee == $anneeOpt) ? 'selected' : '' ?>>
                                                <?= $anneeOpt ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>

                            <!-- Taux Horaire -->
                            <div class="form-group">
                                <label for="taux_horaire"><i class="fas fa-percentage"></i> Taux Horaire (Ar) *</label>
                                <input type="number" id="taux_horaire" name="taux_horaire" class="form-control" 
                                       value="<?= $vacataire['taux_horaire'] ?? 15000 ?>" step="100" min="1000" required>
                                <small style="color:var(--gray); font-size:13px; margin-top:6px; display:block;">
                                    <i class="fas fa-info-circle"></i> Montant payé par heure travaillée
                                </small>
                            </div>

                            <!-- Heures Normales -->
                            <div class="form-group">
                                <label for="heures_travaillees"><i class="fas fa-business-time"></i> Heures Normales Travaillées *</label>
                                <input type="number" id="heures_travaillees" name="heures_travaillees" class="form-control" 
                                       value="173.33" step="0.5" min="0" required>
                                <small style="color:var(--gray); font-size:13px; margin-top:6px; display:block;">
                                    <i class="fas fa-clock"></i> Moyenne mensuelle : 173.33 heures
                                </small>
                            </div>

                            <!-- Heures Supplémentaires -->
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="heures_sup"><i class="fas fa-business-time"></i> Heures Supplémentaires</label>
                                    <input type="number" id="heures_sup" name="heures_sup" class="form-control" 
                                           value="0" min="0" step="0.5">
                                </div>
                                <div class="form-group">
                                    <label for="taux_heures_sup"><i class="fas fa-percentage"></i> Taux Heures Sup (%)</label>
                                    <input type="number" id="taux_heures_sup" name="taux_heures_sup" class="form-control" 
                                           value="50" min="0" step="1">
                                    <small style="color:var(--gray); font-size:13px; margin-top:6px; display:block;">
                                        50% = 1.5x le taux horaire
                                    </small>
                                </div>
                            </div>

                            <!-- Mode de Paiement -->
                            <div class="form-group">
                                <label for="id_mode_paiement"><i class="fas fa-credit-card"></i> Mode de Paiement</label>
                                <select id="id_mode_paiement" name="id_mode_paiement" class="form-control">
                                    <option value="1">Virement bancaire</option>
                                    <option value="2" selected>Espèces</option>
                                    <option value="3">Chèque</option>
                                    <option value="4">Mobile Money</option>
                                </select>
                            </div>

                            <!-- Remarques -->
                            <div class="form-group">
                                <label for="remarques"><i class="fas fa-sticky-note"></i> Remarques</label>
                                <textarea id="remarques" name="remarques" class="form-control" rows="3" 
                                          placeholder="Précisez les détails du travail effectué..."></textarea>
                            </div>
                        </div>

                        <!-- Résumé Calculs -->
                        <div class="summary-card">
                            <h3><i class="fas fa-calculator"></i> Résumé des Calculs</h3>
                            <div class="summary-item">
                                <span>Heures Normales :</span>
                                <span id="resume_heures_normales">173.33 h</span>
                            </div>
                            <div class="summary-item">
                                <span>Taux Horaire :</span>
                                <span id="resume_taux_horaire">15 000 Ar/h</span>
                            </div>
                            <div class="summary-item">
                                <span><strong>Salaire Normal :</strong></span>
                                <span id="resume_normal">2 600 000 Ar</span>
                            </div>
                            <div class="summary-item">
                                <span>Heures Supplémentaires :</span>
                                <span id="resume_heures_sup">0 h</span>
                            </div>
                            <div class="summary-item">
                                <span><strong>Salaire Heures Sup :</strong></span>
                                <span id="resume_sup">0 Ar</span>
                            </div>
                            <div class="summary-item">
                                <span><strong>Salaire Brut :</strong></span>
                                <span id="resume_brut">2 600 000 Ar</span>
                            </div>
                            <div class="summary-item">
                                <span>CNSS (1%) :</span>
                                <span id="resume_cnss">26 000 Ar</span>
                            </div>
                            <div class="summary-item">
                                <span>IRSA :</span>
                                <span id="resume_irsa">497 500 Ar</span>
                            </div>
                            <div class="summary-item">
                                <span><strong>Salaire Net à Payer :</strong></span>
                                <span id="resume_net">2 076 500 Ar</span>
                            </div>
                        </div>

                        <div style="display:flex; gap:12px; margin-top:24px; flex-wrap:wrap;">
                            <button type="submit" class="btn btn-primary btn-large">
                                <i class="fas fa-stopwatch"></i> Générer le Bulletin
                            </button>
                            <button type="reset" class="btn btn-secondary btn-large">
                                <i class="fas fa-redo"></i> Réinitialiser
                            </button>
                            <a href="<?= $baseUrl ?>/app/controllers/ComptableController.php?action=listBulletins" class="btn btn-secondary btn-large">
                                <i class="fas fa-times"></i> Annuler
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </main>
    </div>

    <script>
        // Mise à jour automatique du taux horaire quand on change de vacataire
        function updateTauxHoraire() {
            const select = document.getElementById('id_employe');
            const option = select.options[select.selectedIndex];
            const tauxHoraire = option.dataset.taux || 15000;
            document.getElementById('taux_horaire').value = tauxHoraire;
            calculateSummary();
        }

        // Calcul automatique du résumé
        function calculateSummary() {
            const tauxHoraire = parseFloat(document.getElementById('taux_horaire').value) || 0;
            const heuresNormales = parseFloat(document.getElementById('heures_travaillees').value) || 0;
            const heuresSup = parseFloat(document.getElementById('heures_sup').value) || 0;
            const tauxHeuresSup = parseFloat(document.getElementById('taux_heures_sup').value) || 50;
            
            // Calculs
            const salaireNormal = heuresNormales * tauxHoraire;
            const salaireHeuresSup = heuresSup * tauxHoraire * (1 + tauxHeuresSup / 100);
            const salaireBrut = salaireNormal + salaireHeuresSup;
            
            // CNSS (1%)
            const cnss = salaireBrut * 0.01;
            
            // IRSA (barème malgache)
            let irsa = 0;
            if (salaireBrut > 350000) {
                if (salaireBrut <= 400000) irsa = (salaireBrut - 350000) * 0.05;
                else if (salaireBrut <= 500000) irsa = 2500 + (salaireBrut - 400000) * 0.10;
                else if (salaireBrut <= 600000) irsa = 12500 + (salaireBrut - 500000) * 0.15;
                else irsa = 27500 + (salaireBrut - 600000) * 0.20;
            }
            
            // Salaire net
            const salaireNet = salaireBrut - cnss - irsa;
            
            // Mise à jour du résumé
            document.getElementById('resume_heures_normales').textContent = heuresNormales.toFixed(2) + ' h';
            document.getElementById('resume_taux_horaire').textContent = formatCurrency(tauxHoraire) + '/h';
            document.getElementById('resume_normal').textContent = formatCurrency(salaireNormal);
            document.getElementById('resume_heures_sup').textContent = heuresSup.toFixed(2) + ' h';
            document.getElementById('resume_sup').textContent = formatCurrency(salaireHeuresSup);
            document.getElementById('resume_brut').textContent = formatCurrency(salaireBrut);
            document.getElementById('resume_cnss').textContent = formatCurrency(cnss);
            document.getElementById('resume_irsa').textContent = formatCurrency(irsa);
            document.getElementById('resume_net').textContent = formatCurrency(salaireNet);
        }

        // Formater en devise malgache
        function formatCurrency(value) {
            return new Intl.NumberFormat('fr-FR', { 
                style: 'decimal', 
                minimumFractionDigits: 0,
                maximumFractionDigits: 0
            }).format(value) + ' Ar';
        }

        // Événements pour recalculer à chaque changement
        document.getElementById('taux_horaire').addEventListener('input', calculateSummary);
        document.getElementById('heures_travaillees').addEventListener('input', calculateSummary);
        document.getElementById('heures_sup').addEventListener('input', calculateSummary);
        document.getElementById('taux_heures_sup').addEventListener('input', calculateSummary);

        // Calcul initial
        document.addEventListener('DOMContentLoaded', calculateSummary);
    </script>
</body>
</html>