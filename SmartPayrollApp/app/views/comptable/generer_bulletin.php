<?php
/**
 * ============================================================================
 * SMARTPAYROLL - Formulaire de Génération de Bulletin (CDI/CDD)
 * ============================================================================
 * 
 * Formulaire pour générer un bulletin de paie pour un employé CDI/CDD
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
// $employes, $primesStandards, $retenuesStandards, $mois, $annee, $employe, $error, $success

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
    <title>Générer Bulletin CDI/CDD | SmartPayroll - ISMK</title>
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
        .checkbox-group { display:flex; flex-wrap:wrap; gap:12px; margin-top:8px; }
        .checkbox-item { display:flex; align-items:center; gap:6px; margin-right:16px; }
        .checkbox-item input { width:auto; }
        .btn { padding:12px 24px; border-radius:var(--radius); font-weight:600; cursor:pointer; transition:all 0.3s; border:none; display:inline-flex; align-items:center; gap:8px; font-family:'Inter',sans-serif; font-size:16px; }
        .btn-primary { background:var(--primary); color:white; }
        .btn-primary:hover { background:var(--primary-dark); transform:translateY(-2px); box-shadow:0 4px 12px rgba(37,99,235,0.3); }
        .btn-secondary { background:white; color:var(--primary); border:2px solid var(--primary); }
        .btn-secondary:hover { background:var(--primary); color:white; }
        .btn-large { padding:14px 28px; font-size:16px; }
        .summary-card { background:linear-gradient(135deg, var(--primary), var(--primary-dark)); color:white; border-radius:var(--radius-lg); padding:24px; margin-top:24px; }
        .summary-card h3 { font-size:18px; margin-bottom:16px; display:flex; align-items:center; gap:10px; }
        .summary-item { display:flex; justify-content:space-between; padding:12px 0; border-bottom:1px solid rgba(255,255,255,0.2); }
        .summary-item:last-child { border-bottom:none; font-weight:700; font-size:18px; margin-top:8px; padding-top:16px; }
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
                <a href="<?= $baseUrl ?>/app/controllers/ComptableController.php?action=showGenererBulletin" class="sidebar-menu-item active">
                    <i class="fas fa-calculator"></i> Générer Bulletin
                </a>
                <a href="<?= $baseUrl ?>/app/controllers/ComptableController.php?action=showSaisieHeures" class="sidebar-menu-item">
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
                <h1><i class="fas fa-file-invoice-dollar"></i> Générer Bulletin de Paie (CDI/CDD)</h1>
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
                            'bulletin_existe' => 'Un bulletin existe déjà pour cet employé ce mois-ci.',
                            'employe_invalide' => 'Employé invalide.',
                            'erreur_generation' => 'Erreur lors de la génération du bulletin.'
                        ];
                        echo htmlspecialchars($messages[$error] ?? 'Une erreur est survenue.');
                        ?>
                    </div>
                <?php endif; ?>

                <?php if ($success): ?>
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i>
                        <?= htmlspecialchars($success === 'bulletin_genere' ? 'Bulletin généré avec succès !' : 'Opération réussie.'); ?>
                    </div>
                <?php endif; ?>

                <div class="card">
                    <div class="card-header">
                        <h2><i class="fas fa-edit"></i> Formulaire de Génération</h2>
                        <a href="<?= $baseUrl ?>/app/controllers/ComptableController.php?action=listBulletins" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Retour à la liste
                        </a>
                    </div>

                    <form action="<?= $baseUrl ?>/app/controllers/ComptableController.php?action=genererBulletin" method="POST">
                        <div class="form-grid">
                            <!-- Employé -->
                            <div class="form-group">
                                <label for="id_employe"><i class="fas fa-user"></i> Employé *</label>
                                <select id="id_employe" name="id_employe" class="form-control" required onchange="updateSalaireBase()">
                                    <option value="">Sélectionner un employé</option>
                                    <?php foreach ($employes as $emp): ?>
                                        <option value="<?= $emp['id_employe'] ?>" 
                                                data-salaire="<?= $emp['salaire_base'] ?? 0 ?>"
                                                <?= ($employe && $employe['id_employe'] == $emp['id_employe']) ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($emp['matricule'] . ' - ' . $emp['prenom'] . ' ' . $emp['nom'] . ' (' . $emp['poste'] . ')') ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
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

                            <!-- Salaire de Base -->
                            <div class="form-group">
                                <label for="salaire_base"><i class="fas fa-money-bill-wave"></i> Salaire de Base (Ar) *</label>
                                <input type="number" id="salaire_base" name="salaire_base" class="form-control" 
                                       value="<?= $employe['salaire_base'] ?? 450000 ?>" step="1000" min="0" required>
                                <small style="color:var(--gray); font-size:13px; margin-top:6px; display:block;">
                                    <i class="fas fa-info-circle"></i> Montant mensuel basé sur le poste de l'employé
                                </small>
                            </div>

                            <!-- Heures Supplémentaires -->
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="heures_sup"><i class="fas fa-clock"></i> Heures Supplémentaires</label>
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

                            <!-- Primes -->
                            <div class="form-group">
                                <label><i class="fas fa-gift"></i> Primes</label>
                                <div class="checkbox-group">
                                    <?php foreach ($primesStandards as $prime): ?>
                                        <div class="checkbox-item">
                                            <input type="checkbox" id="prime_<?= $prime['id_prime'] ?>" 
                                                   name="prime_ids[]" value="<?= $prime['id_prime'] ?>">
                                            <label for="prime_<?= $prime['id_prime'] ?>" style="font-weight:400; font-size:14px;">
                                                <?= htmlspecialchars($prime['libelle']) ?> 
                                                (<?= number_format($prime['montant'], 0, ',', ' ') ?> Ar)
                                            </label>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                                <div style="margin-top:12px;">
                                    <label for="primes"><i class="fas fa-plus-circle"></i> Prime(s) supplémentaire(s) (Ar)</label>
                                    <input type="number" id="primes" name="primes" class="form-control" value="0" min="0" step="1000">
                                </div>
                            </div>

                            <!-- Retenues -->
                            <div class="form-group">
                                <label><i class="fas fa-minus-circle"></i> Retenues</label>
                                <div class="checkbox-group">
                                    <?php foreach ($retenuesStandards as $retenue): ?>
                                        <div class="checkbox-item">
                                            <input type="checkbox" id="retenue_<?= $retenue['id_retenue'] ?>" 
                                                   name="retenue_ids[]" value="<?= $retenue['id_retenue'] ?>">
                                            <label for="retenue_<?= $retenue['id_retenue'] ?>" style="font-weight:400; font-size:14px;">
                                                <?= htmlspecialchars($retenue['libelle']) ?> 
                                                (<?= number_format($retenue['montant'], 0, ',', ' ') ?> Ar)
                                            </label>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                                <div style="margin-top:12px;">
                                    <label for="retenues"><i class="fas fa-minus-circle"></i> Retenue(s) supplémentaire(s) (Ar)</label>
                                    <input type="number" id="retenues" name="retenues" class="form-control" value="0" min="0" step="1000">
                                </div>
                            </div>

                            <!-- Mode de Paiement -->
                            <div class="form-group">
                                <label for="id_mode_paiement"><i class="fas fa-credit-card"></i> Mode de Paiement</label>
                                <select id="id_mode_paiement" name="id_mode_paiement" class="form-control">
                                    <option value="1">Virement bancaire</option>
                                    <option value="2">Espèces</option>
                                    <option value="3">Chèque</option>
                                    <option value="4">Mobile Money</option>
                                </select>
                            </div>

                            <!-- Remarques -->
                            <div class="form-group">
                                <label for="remarques"><i class="fas fa-sticky-note"></i> Remarques</label>
                                <textarea id="remarques" name="remarques" class="form-control" rows="3" 
                                          placeholder="Remarques complémentaires sur ce bulletin..."></textarea>
                            </div>
                        </div>

                        <!-- Résumé Calculs -->
                        <div class="summary-card">
                            <h3><i class="fas fa-calculator"></i> Résumé des Calculs</h3>
                            <div class="summary-item">
                                <span>Salaire de Base :</span>
                                <span id="resume_salaire_base">450 000 Ar</span>
                            </div>
                            <div class="summary-item">
                                <span>Heures Supplémentaires :</span>
                                <span id="resume_heures_sup">0 Ar</span>
                            </div>
                            <div class="summary-item">
                                <span>Primes :</span>
                                <span id="resume_primes">0 Ar</span>
                            </div>
                            <div class="summary-item">
                                <span>Retenues :</span>
                                <span id="resume_retenues">0 Ar</span>
                            </div>
                            <div class="summary-item">
                                <span><strong>Salaire Brut :</strong></span>
                                <span id="resume_brut">450 000 Ar</span>
                            </div>
                            <div class="summary-item">
                                <span>CNSS (1%) :</span>
                                <span id="resume_cnss">4 500 Ar</span>
                            </div>
                            <div class="summary-item">
                                <span>IRSA :</span>
                                <span id="resume_irsa">0 Ar</span>
                            </div>
                            <div class="summary-item">
                                <span><strong>Salaire Net à Payer :</strong></span>
                                <span id="resume_net">445 500 Ar</span>
                            </div>
                        </div>

                        <div style="display:flex; gap:12px; margin-top:24px; flex-wrap:wrap;">
                            <button type="submit" class="btn btn-primary btn-large">
                                <i class="fas fa-file-invoice"></i> Générer le Bulletin
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
        // Mise à jour automatique du salaire de base quand on change d'employé
        function updateSalaireBase() {
            const select = document.getElementById('id_employe');
            const option = select.options[select.selectedIndex];
            const salaireBase = option.dataset.salaire || 450000;
            document.getElementById('salaire_base').value = salaireBase;
            calculateSummary();
        }

        // Calcul automatique du résumé
        function calculateSummary() {
            const salaireBase = parseFloat(document.getElementById('salaire_base').value) || 0;
            const heuresSup = parseFloat(document.getElementById('heures_sup').value) || 0;
            const tauxHeuresSup = parseFloat(document.getElementById('taux_heures_sup').value) || 50;
            const primes = parseFloat(document.getElementById('primes').value) || 0;
            const retenues = parseFloat(document.getElementById('retenues').value) || 0;
            
            // Calcul heures sup (basé sur 173.33h/mois)
            const tauxHoraire = salaireBase / 173.33;
            const montantHeuresSup = heuresSup * tauxHoraire * (1 + tauxHeuresSup / 100);
            
            // Salaire brut
            const salaireBrut = salaireBase + montantHeuresSup + primes - retenues;
            
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
            document.getElementById('resume_salaire_base').textContent = formatCurrency(salaireBase);
            document.getElementById('resume_heures_sup').textContent = formatCurrency(montantHeuresSup);
            document.getElementById('resume_primes').textContent = formatCurrency(primes);
            document.getElementById('resume_retenues').textContent = formatCurrency(retenues);
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
        document.getElementById('salaire_base').addEventListener('input', calculateSummary);
        document.getElementById('heures_sup').addEventListener('input', calculateSummary);
        document.getElementById('taux_heures_sup').addEventListener('input', calculateSummary);
        document.getElementById('primes').addEventListener('input', calculateSummary);
        document.getElementById('retenues').addEventListener('input', calculateSummary);

        // Calcul initial
        document.addEventListener('DOMContentLoaded', calculateSummary);
    </script>
</body>
</html>