<?php
/**
 * ============================================================================
 * SMARTPAYROLL - Liste des Bulletins de Paie
 * ============================================================================
 * 
 * Tableau complet des bulletins avec filtres, actions et export
 * 
 * @author [Ton nom] - Étudiante BTS Génie Logiciel
 * @establishment Institut Supérieur Mony Keng (ISMK)
 * @project Stage BTS - Digitalisation de la gestion des salaires
 * @version 1.0
 * @date Février 2026
 * ============================================================================
 */
foreach (['mois', 'annee', 'id', 'statut'] as $param) {
    if (isset($_GET[$param]) && is_numeric($_GET[$param])) {
        $_GET[$param] = (int)$_GET[$param];
    }
}

// Vérifier l'authentification
require_once __DIR__ . '/../../core/Session.php';
\App\Core\Session::start();
\App\Core\Session::requireRole(['admin', 'comptable']);

// Récupérer les données (passées par le contrôleur)
// $bulletins, $mois, $annee, $statut, $search

// Définir BASE_URL
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'];
$scriptName = $_SERVER['SCRIPT_NAME'];
$projectPath = preg_replace('#^(/[^/]+).*#', '$1', $scriptName);
$baseUrl = $protocol . '://' . $host . $projectPath;



// ✅ CORRECTION CRITIQUE : Normaliser mois/année en entiers pour éviter les erreurs de type
$mois = isset($_GET['mois']) ? (int)$_GET['mois'] : (int)date('n'); // date('n') = mois SANS zéro leading (1-12)
$annee = isset($_GET['annee']) ? (int)$_GET['annee'] : (int)date('Y');
$statut = $_GET['statut'] ?? 'all';
$search = $_GET['search'] ?? '';

// Redéfinir les options avec les valeurs normalisées
$moisOptions = [
    1 => 'Janvier', 2 => 'Février', 3 => 'Mars', 4 => 'Avril',
    5 => 'Mai', 6 => 'Juin', 7 => 'Juillet', 8 => 'Août',
    9 => 'Septembre', 10 => 'Octobre', 11 => 'Novembre', 12 => 'Décembre'
];
$anneesOptions = range(date('Y'), date('Y') - 5);
// Messages d'erreur/succès
$error = $_GET['error'] ?? null;
$success = $_GET['success'] ?? null;

// Mois en français pour le select
$moisOptions = [
    1 => 'Janvier', 2 => 'Février', 3 => 'Mars', 4 => 'Avril',
    5 => 'Mai', 6 => 'Juin', 7 => 'Juillet', 8 => 'Août',
    9 => 'Septembre', 10 => 'Octobre', 11 => 'Novembre', 12 => 'Décembre'
];

// Années disponibles (5 dernières années + courante)
$anneesOptions = range(date('Y'), date('Y') - 5);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Liste des Bulletins | SmartPayroll - ISMK</title>
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --primary: #2563eb;
            --primary-dark: #1d4ed8;
            --primary-light: #dbeafe;
            --secondary: #047857;
            --accent: #f59e0b;
            --danger: #ef4444;
            --success: #10b981;
            --warning: #f59e0b;
            --dark: #1e293b;
            --dark-light: #334155;
            --light: #f8fafc;
            --gray: #64748b;
            --border: #cbd5e1;
            
            --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
            --shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06);
            --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
            
            --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            --radius: 8px;
            --radius-lg: 12px;
            --radius-xl: 16px;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            line-height: 1.6;
            color: var(--dark);
            background-color: #f1f5f9;
        }

        a {
            text-decoration: none;
            color: inherit;
            transition: var(--transition);
        }

        .dashboard {
            display: flex;
            min-height: 100vh;
        }

        /* SIDEBAR */
        .sidebar {
            width: 260px;
            background: white;
            box-shadow: var(--shadow);
            position: fixed;
            height: 100vh;
            overflow-y: auto;
            z-index: 100;
        }

        .sidebar-header {
            padding: 24px 20px;
            border-bottom: 1px solid var(--border);
            text-align: center;
        }

        .sidebar-logo {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 8px;
        }

        .sidebar-logo-badge {
            width: 40px;
            height: 40px;
            background: var(--primary);
            border-radius: var(--radius);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 700;
            font-size: 16px;
        }

        .sidebar-logo-text {
            font-size: 18px;
            font-weight: 700;
            color: var(--dark);
        }

        .sidebar-user {
            padding: 16px 20px;
            border-bottom: 1px solid var(--border);
        }

        .sidebar-user-info {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .sidebar-user-avatar {
            width: 40px;
            height: 40px;
            background: var(--primary-light);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--primary);
            font-weight: 600;
            font-size: 16px;
        }

        .sidebar-user-details h4 {
            font-size: 15px;
            font-weight: 600;
            margin-bottom: 2px;
        }

        .sidebar-user-details p {
            font-size: 13px;
            color: var(--gray);
        }

        .sidebar-menu {
            padding: 16px 0;
        }

        .sidebar-menu-item {
            padding: 12px 20px;
            display: flex;
            align-items: center;
            gap: 12px;
            color: var(--gray);
            transition: var(--transition);
            cursor: pointer;
            border-left: 3px solid transparent;
        }

        .sidebar-menu-item:hover {
            background: var(--primary-light);
            color: var(--primary);
        }

        .sidebar-menu-item.active {
            background: var(--primary);
            color: white;
            border-left-color: var(--accent);
        }

        /* MAIN CONTENT */
        .main-content {
            flex: 1;
            margin-left: 260px;
        }

        .header {
            background: white;
            box-shadow: var(--shadow);
            padding: 16px 32px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: sticky;
            top: 0;
            z-index: 90;
        }

        .header-left h1 {
            font-size: 24px;
            font-weight: 700;
            color: var(--dark);
        }

        .header-right {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .header-user {
            display: flex;
            align-items: center;
            gap: 12px;
            cursor: pointer;
        }

        .header-user-avatar {
            width: 36px;
            height: 36px;
            background: var(--primary-light);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--primary);
            font-weight: 600;
            font-size: 14px;
        }

        .header-user-info {
            display: flex;
            flex-direction: column;
        }

        .header-user-name {
            font-size: 14px;
            font-weight: 600;
        }

        .header-user-role {
            font-size: 12px;
            color: var(--gray);
        }

        .container {
            padding: 32px;
        }

        /* Alerts */
        .alert {
            padding: 14px 20px;
            border-radius: var(--radius);
            margin-bottom: 24px;
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 15px;
            font-weight: 500;
        }

        .alert-error {
            background: #fee2e2;
            color: #991b1b;
            border: 1px solid #fecaca;
        }

        .alert-success {
            background: #dcfce7;
            color: #166534;
            border: 1px solid #bbf7d0;
        }

        /* Filters */
        .filters-container {
            background: white;
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow);
            padding: 24px;
            margin-bottom: 24px;
        }

        .filters-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 16px;
            margin-bottom: 16px;
        }

        .filter-group {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .filter-group label {
            font-size: 14px;
            font-weight: 600;
            color: var(--dark);
        }

        .filter-group select,
        .filter-group input {
            padding: 10px 12px;
            border: 2px solid var(--border);
            border-radius: var(--radius);
            font-size: 14px;
            font-family: 'Inter', sans-serif;
            transition: var(--transition);
        }

        .filter-group select:focus,
        .filter-group input:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
        }

        .filters-actions {
            display: flex;
            gap: 12px;
            justify-content: flex-end;
            flex-wrap: wrap;
        }

        .btn {
            padding: 10px 20px;
            border-radius: var(--radius);
            font-weight: 600;
            cursor: pointer;
            transition: var(--transition);
            border: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-family: 'Inter', sans-serif;
            font-size: 14px;
        }

        .btn-primary {
            background: var(--primary);
            color: white;
        }

        .btn-primary:hover {
            background: var(--primary-dark);
        }

        .btn-secondary {
            background: white;
            color: var(--primary);
            border: 2px solid var(--primary);
        }

        .btn-secondary:hover {
            background: var(--primary);
            color: white;
        }

        .btn-success {
            background: var(--success);
            color: white;
        }

        .btn-success:hover {
            background: #0da271;
        }

        .btn-export {
            background: #f3e8ff;
            color: #7e22ce;
        }

        .btn-export:hover {
            background: #7e22ce;
            color: white;
        }

        /* Stats Summary */
        .stats-summary {
            background: white;
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow);
            padding: 20px;
            margin-bottom: 24px;
            display: flex;
            justify-content: space-around;
            flex-wrap: wrap;
            gap: 20px;
        }

        .stat-item {
            text-align: center;
        }

        .stat-label {
            font-size: 13px;
            color: var(--gray);
            margin-bottom: 4px;
        }

        .stat-value {
            font-size: 24px;
            font-weight: 700;
            color: var(--dark);
        }

        .stat-value.primary {
            color: var(--primary);
        }

        .stat-value.success {
            color: var(--success);
        }

        .stat-value.warning {
            color: var(--warning);
        }

        /* Table */
        .table-container {
            background: white;
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow);
            padding: 24px;
        }

        .table-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 16px;
            border-bottom: 2px solid var(--border);
        }

        .table-header h2 {
            font-size: 18px;
            font-weight: 700;
            color: var(--dark);
        }

        .table-responsive {
            overflow-x: auto;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
        }

        .table thead {
            background: var(--light);
        }

        .table th {
            padding: 14px 16px;
            text-align: left;
            font-weight: 600;
            color: var(--dark);
            font-size: 14px;
            border-bottom: 2px solid var(--border);
            position: sticky;
            top: 0;
            background: var(--light);
        }

        .table td {
            padding: 14px 16px;
            border-bottom: 1px solid var(--border);
            font-size: 14px;
            vertical-align: middle;
        }

        .table tbody tr:hover {
            background: var(--light);
        }

        .badge {
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }

        .badge-brouillon {
            background: #fee2e2;
            color: var(--danger);
        }

        .badge-valide {
            background: #dcfce7;
            color: var(--success);
        }

        .badge-paye {
            background: #dbeafe;
            color: var(--primary);
        }

        .action-btn {
            padding: 6px 12px;
            border-radius: var(--radius);
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            transition: var(--transition);
            border: none;
            margin-right: 6px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }

        .action-btn-view {
            background: var(--primary-light);
            color: var(--primary);
        }

        .action-btn-view:hover {
            background: var(--primary);
            color: white;
        }

        .action-btn-validate {
            background: #dcfce7;
            color: var(--success);
        }

        .action-btn-validate:hover {
            background: var(--success);
            color: white;
        }

        .action-btn-pay {
            background: #dbeafe;
            color: var(--primary-dark);
        }

        .action-btn-pay:hover {
            background: var(--primary);
            color: white;
        }

        .action-btn-pdf {
            background: #f3e8ff;
            color: #7e22ce;
        }

        .action-btn-pdf:hover {
            background: #7e22ce;
            color: white;
        }

        .action-btn-delete {
            background: #fee2e2;
            color: var(--danger);
        }

        .action-btn-delete:hover {
            background: var(--danger);
            color: white;
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: var(--gray);
        }

        .empty-state i {
            font-size: 64px;
            margin-bottom: 16px;
            color: var(--primary-light);
        }

        .empty-state h3 {
            font-size: 20px;
            color: var(--dark);
            margin-bottom: 8px;
        }

        .empty-state p {
            font-size: 14px;
            margin-bottom: 20px;
        }

        /* Responsive */
        @media (max-width: 1024px) {
            .sidebar {
                width: 220px;
            }
            
            .main-content {
                margin-left: 220px;
            }
            
            .filters-grid {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 768px) {
            .dashboard {
                flex-direction: column;
            }
            
            .sidebar {
                width: 100%;
                height: auto;
                position: relative;
            }
            
            .main-content {
                margin-left: 0;
            }
            
            .header {
                flex-direction: column;
                gap: 16px;
                text-align: center;
            }
            
            .filters-actions {
                flex-direction: column;
            }
            
            .btn {
                width: 100%;
                justify-content: center;
            }
            
            .stats-summary {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <div class="dashboard">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="sidebar-header">
                <div class="sidebar-logo">
                    <div class="sidebar-logo-badge">ISMK</div>
                    <div class="sidebar-logo-text">SmartPayroll</div>
                </div>
                <div style="font-size: 12px; color: var(--gray); margin-top: 4px;">Comptable Panel</div>
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
                    <i class="fas fa-home"></i>
                    <span>Dashboard</span>
                </a>
                <a href="<?= $baseUrl ?>/app/controllers/ComptableController.php?action=listBulletins" class="sidebar-menu-item active">
                    <i class="fas fa-file-invoice"></i>
                    <span>Bulletins de Paie</span>
                </a>
                <a href="<?= $baseUrl ?>/app/controllers/ComptableController.php?action=showGenererBulletin" class="sidebar-menu-item">
                    <i class="fas fa-calculator"></i>
                    <span>Calcul Paie</span>
                </a>
                <a href="<?= $baseUrl ?>/app/controllers/ComptableController.php?action=showSaisieHeures" class="sidebar-menu-item">
                    <i class="fas fa-clock"></i>
                    <span>Saisie Heures</span>
                </a>
                <a href="<?= $baseUrl ?>/app/controllers/ComptableController.php?action=rapports" class="sidebar-menu-item">
                    <i class="fas fa-chart-bar"></i>
                    <span>Rapports</span>
                </a>
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <!-- Header -->
            <header class="header">
                <div class="header-left">
                    <h1>Liste des Bulletins de Paie</h1>
                </div>
                <div class="header-right">
                    <div class="header-user">
                        <div class="header-user-avatar"><?= strtoupper(substr($_SESSION['user_prenom'], 0, 1)) ?></div>
                        <div class="header-user-info">
                            <div class="header-user-name"><?= htmlspecialchars($_SESSION['user_prenom']) ?></div>
                            <div class="header-user-role">Comptable</div>
                        </div>
                    </div>
                    <a href="<?= $baseUrl ?>/app/controllers/AuthController.php?action=logout" 
                       class="btn" style="background: var(--danger); color: white;">
                        <i class="fas fa-sign-out-alt"></i> Déconnexion
                    </a>
                </div>
            </header>

            <!-- Container -->
            <div class="container">
                <!-- Messages -->
                <?php if ($error): ?>
                    <div class="alert alert-error">
                        <i class="fas fa-exclamation-triangle"></i>
                        <?php
                        $messages = [
                            'bulletin_invalide' => 'Bulletin invalide.',
                            'statut_invalide' => 'Statut du bulletin invalide.',
                            'erreur_validation' => 'Erreur lors de la validation.',
                            'erreur_paiement' => 'Erreur lors du paiement.'
                        ];
                        echo htmlspecialchars($messages[$error] ?? 'Une erreur est survenue.');
                        ?>
                    </div>
                <?php endif; ?>

                <?php if ($success): ?>
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i>
                        <?php
                        $messages = [
                            'bulletin_genere' => 'Bulletin généré avec succès.',
                            'bulletin_valide' => 'Bulletin validé avec succès.',
                            'bulletin_paye' => 'Bulletin marqué comme payé.',
                            'bulletin_supprime' => 'Bulletin supprimé.'
                        ];
                        echo htmlspecialchars($messages[$success] ?? 'Opération réussie.');
                        ?>
                    </div>
                <?php endif; ?>

                <!-- Filters -->
                <div class="filters-container">
                    <form method="GET" action="<?= $baseUrl ?>/app/controllers/ComptableController.php">
                        <input type="hidden" name="action" value="listBulletins">
                        
                        <div class="filters-grid">
                            <div class="filter-group">
                                <label for="mois">Mois</label>
                                <select id="mois" name="mois">
                                    <?php foreach ($moisOptions as $num => $nom): ?>
                                        <option value="<?= $num ?>" <?= ($mois == $num) ? 'selected' : '' ?>>
                                            <?= $nom ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="filter-group">
                                <label for="annee">Année</label>
                                <select id="annee" name="annee">
                                    <?php foreach ($anneesOptions as $anneeOpt): ?>
                                        <option value="<?= $anneeOpt ?>" <?= ($annee == $anneeOpt) ? 'selected' : '' ?>>
                                            <?= $anneeOpt ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="filter-group">
                                <label for="statut">Statut</label>
                                <select id="statut" name="statut">
                                    <option value="all" <?= ($statut == 'all') ? 'selected' : '' ?>>Tous les statuts</option>
                                    <option value="brouillon" <?= ($statut == 'brouillon') ? 'selected' : '' ?>>Brouillon</option>
                                    <option value="valide" <?= ($statut == 'valide') ? 'selected' : '' ?>>Validé</option>
                                    <option value="paye" <?= ($statut == 'paye') ? 'selected' : '' ?>>Payé</option>
                                </select>
                            </div>
                            
                            <div class="filter-group">
                                <label for="search">Recherche</label>
                                <input type="text" id="search" name="search" placeholder="Matricule, nom..." 
                                       value="<?= htmlspecialchars($search ?? '') ?>">
                            </div>
                        </div>
                        
                        <div class="filters-actions">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-search"></i> Filtrer
                            </button>
                            <button type="reset" class="btn btn-secondary" onclick="window.location.href='<?= $baseUrl ?>/app/controllers/ComptableController.php?action=listBulletins'">
                                <i class="fas fa-undo"></i> Réinitialiser
                            </button>
                            <a href="<?= $baseUrl ?>/app/controllers/ComptableController.php?action=exportCSV&mois=<?= $mois ?>&annee=<?= $annee ?>" 
                               class="btn btn-export">
                                <i class="fas fa-file-csv"></i> Export CSV
                            </a>
                            <a href="<?= $baseUrl ?>/app/controllers/ComptableController.php?action=showGenererBulletin" 
                               class="btn btn-success">
                                <i class="fas fa-plus"></i> Générer Bulletin
                            </a>
                        </div>
                    </form>
                </div>

                <!-- Stats Summary -->
                <?php
                $stats = [
                    'total' => count($bulletins),
                    'brouillon' => array_filter($bulletins, fn($b) => $b['statut'] === 'brouillon'),
                    'valide' => array_filter($bulletins, fn($b) => $b['statut'] === 'valide'),
                    'paye' => array_filter($bulletins, fn($b) => $b['statut'] === 'paye')
                ];
                ?>
                <div class="stats-summary">
                    <div class="stat-item">
                        <div class="stat-label">Total Bulletins</div>
                        <div class="stat-value"><?= count($bulletins) ?></div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-label">Brouillon</div>
                        <div class="stat-value" style="color: var(--danger);"><?= count($stats['brouillon']) ?></div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-label">Validés</div>
                        <div class="stat-value success"><?= count($stats['valide']) ?></div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-label">Payés</div>
                        <div class="stat-value primary"><?= count($stats['paye']) ?></div>
                    </div>
                </div>

                <!-- Table -->
                <div class="table-container">
                    <div class="table-header">
                        <h2>
                            <i class="fas fa-list"></i> 
                            Liste des Bulletins - <?= $moisOptions[$mois] ?? 'Mois inconnu' ?> <?= $annee ?>
                        </h2>
                        <span style="font-size: 14px; color: var(--gray);">
                            <?= count($bulletins) ?> bulletin(s) trouvé(s)
                        </span>
                    </div>
                    
                    <?php if (!empty($bulletins)): ?>
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Matricule</th>
                                        <th>Employé</th>
                                        <th>Poste</th>
                                        <th>Département</th>
                                        <th>Mois/Année</th>
                                        <th>Salaire Brut</th>
                                        <th>Salaire Net</th>
                                        <th>Statut</th>
                                        <th>Date Génération</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($bulletins as $bulletin): ?>
                                    <tr>
                                        <td><strong><?= htmlspecialchars($bulletin['matricule']) ?></strong></td>
                                        <td><?= htmlspecialchars($bulletin['prenom'] . ' ' . $bulletin['nom']) ?></td>
                                        <td><?= htmlspecialchars($bulletin['poste'] ?? '-') ?></td>
                                        <td><?= htmlspecialchars($bulletin['departement'] ?? '-') ?></td>
                                        <td><?= $bulletin['mois'] ?>/<?= $bulletin['annee'] ?></td>
                                        <td><?= number_format($bulletin['salaire_brut'], 0, ',', ' ') ?> Ar</td>
                                        <td><strong><?= number_format($bulletin['salaire_net'], 0, ',', ' ') ?> Ar</strong></td>
                                        <td>
                                            <span class="badge badge-<?= $bulletin['statut'] ?>">
                                                <?= ucfirst($bulletin['statut']) ?>
                                            </span>
                                        </td>
                                        <td><?= date('d/m/Y', strtotime($bulletin['date_generation'])) ?></td>
                                        <td>
                                            <?php if ($bulletin['statut'] === 'brouillon'): ?>
                                                <a href="<?= $baseUrl ?>/app/controllers/ComptableController.php?action=validerBulletin&id=<?= $bulletin['id_bulletin'] ?>" 
                                                   class="action-btn action-btn-validate" 
                                                   onclick="return confirm('Valider ce bulletin ?')">
                                                    <i class="fas fa-check"></i> Valider
                                                </a>
                                            <?php elseif ($bulletin['statut'] === 'valide'): ?>
                                                <a href="<?= $baseUrl ?>/app/controllers/ComptableController.php?action=marquerPaye&id=<?= $bulletin['id_bulletin'] ?>" 
                                                   class="action-btn action-btn-pay" 
                                                   onclick="return confirm('Marquer comme payé ?')">
                                                    <i class="fas fa-money-bill-wave"></i> Payer
                                                </a>
                                            <?php endif; ?>
                                            
                                            <a href="<?= $baseUrl ?>/app/controllers/ComptableController.php?action=genererPDF&id=<?= $bulletin['id_bulletin'] ?>" 
                                               class="action-btn action-btn-pdf" target="_blank">
                                                <i class="fas fa-file-pdf"></i> PDF
                                            </a>
                                            
                                            <a href="#" class="action-btn action-btn-view">
                                                <i class="fas fa-eye"></i> Voir
                                            </a>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="empty-state">
                            <i class="fas fa-file-invoice"></i>
                            <h3>Aucun bulletin trouvé</h3>
                            <p>Aucun bulletin ne correspond à vos critères de recherche.</p>
                            <a href="<?= $baseUrl ?>/app/controllers/ComptableController.php?action=showGenererBulletin" 
                               class="btn btn-primary">
                                <i class="fas fa-plus"></i> Générer un premier bulletin
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>
</body>
</html>