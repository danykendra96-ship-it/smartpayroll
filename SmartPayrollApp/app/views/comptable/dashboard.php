<?php
/**
 * ============================================================================
 * SMARTPAYROLL - Dashboard Comptable
 * ============================================================================
 * 
 * Tableau de bord du comptable avec statistiques et gestion des bulletins
 * 
 * @author [Ton nom] - Étudiante BTS Génie Logiciel
 * @establishment Institut Supérieur Mony Keng (ISMK)
 * @project Stage BTS - Digitalisation de la gestion des salaires
 * @version 1.0
 * @date Février 2026
 * ============================================================================
 */


// Vérifier l'authentification
require_once __DIR__ . '/../../core/Session.php';
\App\Core\Session::start();
\App\Core\Session::requireRole(['admin', 'comptable']);

// Récupérer les données du dashboard (passées par le contrôleur)
// $stats, $bulletinsEnAttente, $recentActivity

// Définir BASE_URL
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'];
$scriptName = $_SERVER['SCRIPT_NAME'];
$projectPath = preg_replace('#^(/[^/]+).*#', '$1', $scriptName);
$baseUrl = $protocol . '://' . $host . $projectPath;

// Messages d'erreur/succès
$error = $_GET['error'] ?? null;
$success = $_GET['success'] ?? null;
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Comptable | SmartPayroll - ISMK</title>
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        /* ============================================================================
           VARIABLES CSS - Identiques à ton thème SmartPayroll
           ============================================================================ */
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

        /* ============================================================================
           RESET & BASE
           ============================================================================ */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            line-height: 1.6;
            color: var(--dark);
            background-color: #f1f5f9;
        }

        a {
            text-decoration: none;
            color: inherit;
            transition: var(--transition);
        }

        /* ============================================================================
           LAYOUT
           ============================================================================ */
        .dashboard {
            display: flex;
            min-height: 100vh;
        }

        /* ============================================================================
           SIDEBAR
           ============================================================================ */
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

        .sidebar-menu-item i {
            width: 20px;
            font-size: 18px;
        }

        /* ============================================================================
           MAIN CONTENT
           ============================================================================ */
        .main-content {
            flex: 1;
            margin-left: 260px;
        }

        /* Header */
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

        /* Container */
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

        .alert i {
            font-size: 18px;
        }

        /* Stats Cards */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 24px;
            margin-bottom: 32px;
        }

        .stat-card {
            background: white;
            padding: 24px;
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow);
            display: flex;
            align-items: center;
            gap: 16px;
            transition: var(--transition);
        }

        .stat-card:hover {
            transform: translateY(-4px);
            box-shadow: var(--shadow-md);
        }

        .stat-icon {
            width: 56px;
            height: 56px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
        }

        .stat-icon.primary {
            background: var(--primary-light);
            color: var(--primary);
        }

        .stat-icon.success {
            background: #dcfce7;
            color: var(--success);
        }

        .stat-icon.warning {
            background: #fef3c7;
            color: var(--warning);
        }

        .stat-icon.danger {
            background: #fee2e2;
            color: var(--danger);
        }

        .stat-info h3 {
            font-size: 14px;
            color: var(--gray);
            margin-bottom: 4px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .stat-info p {
            font-size: 28px;
            font-weight: 700;
            color: var(--dark);
        }

        .stat-trend {
            display: flex;
            align-items: center;
            gap: 4px;
            font-size: 13px;
            margin-top: 4px;
        }

        .stat-trend.positive {
            color: var(--success);
        }

        .stat-trend.negative {
            color: var(--danger);
        }

        /* Dashboard Grid */
        .dashboard-grid {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 24px;
            margin-bottom: 32px;
        }

        .dashboard-card {
            background: white;
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow);
            padding: 24px;
        }

        .dashboard-card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .dashboard-card-header h2 {
            font-size: 18px;
            font-weight: 700;
            color: var(--dark);
        }

        .dashboard-card-header a {
            color: var(--primary);
            font-size: 14px;
            font-weight: 600;
        }

        .dashboard-card-header a:hover {
            text-decoration: underline;
        }

        /* Bulletins Table */
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
        }

        .table td {
            padding: 14px 16px;
            border-bottom: 1px solid var(--border);
            font-size: 14px;
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
            display: inline-block;
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

        /* Quick Actions */
        .quick-actions {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
            gap: 16px;
            margin-top: 16px;
        }

        .quick-action-btn {
            padding: 16px;
            border-radius: var(--radius);
            background: white;
            border: 2px solid var(--border);
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 8px;
            transition: var(--transition);
            cursor: pointer;
            text-align: center;
        }

        .quick-action-btn:hover {
            border-color: var(--primary);
            transform: translateY(-2px);
            box-shadow: var(--shadow-sm);
        }

        .quick-action-btn i {
            font-size: 24px;
            color: var(--primary);
        }

        .quick-action-btn span {
            font-size: 13px;
            font-weight: 600;
            color: var(--dark);
        }

        /* Recent Activity */
        .activity-list {
            display: flex;
            flex-direction: column;
            gap: 16px;
        }

        .activity-item {
            display: flex;
            gap: 12px;
            padding: 12px;
            border-radius: var(--radius);
            background: var(--light);
            transition: var(--transition);
        }

        .activity-item:hover {
            background: #e2e8f0;
        }

        .activity-icon {
            width: 36px;
            height: 36px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .activity-icon.create {
            background: #dcfce7;
            color: var(--success);
        }

        .activity-icon.update {
            background: #dbeafe;
            color: var(--primary);
        }

        .activity-icon.delete {
            background: #fee2e2;
            color: var(--danger);
        }

        .activity-content h4 {
            font-size: 14px;
            font-weight: 600;
            margin-bottom: 4px;
        }

        .activity-content p {
            font-size: 13px;
            color: var(--gray);
        }

        .activity-time {
            font-size: 12px;
            color: var(--gray);
            margin-top: 4px;
        }

        /* ============================================================================
           RESPONSIVE DESIGN
           ============================================================================ */
        @media (max-width: 1024px) {
            .dashboard-grid {
                grid-template-columns: 1fr;
            }
            
            .sidebar {
                width: 220px;
            }
            
            .main-content {
                margin-left: 220px;
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
            
            .stats-grid {
                grid-template-columns: 1fr;
            }
            
            .header {
                flex-direction: column;
                gap: 16px;
                text-align: center;
            }
            
            .dashboard-grid {
                grid-template-columns: 1fr;
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
                <div class="sidebar-menu-item active">
                    <i class="fas fa-home"></i>
                    <span>Dashboard</span>
                </div>
                <div class="sidebar-menu-item">
                    <i class="fas fa-file-invoice"></i>
                    <span>Bulletins de Paie</span>
                </div>
                <div class="sidebar-menu-item">
                    <i class="fas fa-calculator"></i>
                    <span>Calcul Paie</span>
                </div>
                <div class="sidebar-menu-item">
                    <i class="fas fa-clock"></i>
                    <span>Saisie Heures</span>
                </div>
                <div class="sidebar-menu-item">
                    <i class="fas fa-file-pdf"></i>
                    <span>Génération PDF</span>
                </div>
                <div class="sidebar-menu-item">
                    <i class="fas fa-chart-bar"></i>
                    <span>Rapports</span>
                </div>
                <div class="sidebar-menu-item">
                    <i class="fas fa-download"></i>
                    <span>Exports</span>
                </div>
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <!-- Header -->
            <header class="header">
                <div class="header-left">
                    <h1>Tableau de Bord Comptable</h1>
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
                       style="background: var(--danger); color: white; padding: 8px 16px; border-radius: var(--radius); font-weight: 600;">
                        <i class="fas fa-sign-out-alt"></i> Déconnexion
                    </a>
                </div>
            </header>

            <!-- Container -->
            <div class="container">
                <!-- Messages d'erreur/succès -->
                <?php if ($error): ?>
                    <div class="alert alert-error">
                        <i class="fas fa-exclamation-triangle"></i>
                        <?php
                        $messages = [
                            'bulletin_invalide' => 'Bulletin invalide.',
                            'statut_invalide' => 'Statut du bulletin invalide.',
                            'erreur_validation' => 'Erreur lors de la validation du bulletin.',
                            'erreur_paiement' => 'Erreur lors du paiement.',
                            'non_vacataire' => 'Cet employé n\'est pas un vacataire.',
                            'bulletin_existe' => 'Un bulletin existe déjà pour ce mois/année.',
                            'champs_vides' => 'Veuillez remplir tous les champs obligatoires.'
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
                            'bulletin_vacataire_genere' => 'Bulletin vacataire généré avec succès.',
                            'bulletin_valide' => 'Bulletin validé avec succès.',
                            'bulletin_paye' => 'Bulletin marqué comme payé.',
                            'bulletin_pdf_genere' => 'PDF du bulletin généré.'
                        ];
                        echo htmlspecialchars($messages[$success] ?? 'Opération réussie.');
                        ?>
                    </div>
                <?php endif; ?>

                <!-- Welcome Message -->
                <div style="background: linear-gradient(135deg, var(--secondary), #065f46); color: white; padding: 24px; border-radius: var(--radius-lg); margin-bottom: 32px;">
                    <h2 style="font-size: 28px; margin-bottom: 8px;">💰 Bonjour, <?= htmlspecialchars($_SESSION['user_prenom']) ?> !</h2>
                    <p style="font-size: 16px; opacity: 0.9;">Gérez efficacement la paie mensuelle du personnel de l'ISMK.</p>
                </div>

                <!-- Stats Cards -->
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-icon primary">
                            <i class="fas fa-file-invoice"></i>
                        </div>
                        <div class="stat-info">
                            <h3>Bulletins Brouillon</h3>
                            <p><?= $stats['bulletins_brouillon'] ?? 0 ?></p>
                            <div class="stat-trend">
                                <span>À valider</span>
                            </div>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-icon success">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <div class="stat-info">
                            <h3>Bulletins Validés</h3>
                            <p><?= $stats['bulletins_valides'] ?? 0 ?></p>
                            <div class="stat-trend positive">
                                <i class="fas fa-arrow-up"></i>
                                <span>+5 ce mois</span>
                            </div>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-icon warning">
                            <i class="fas fa-money-bill-wave"></i>
                        </div>
                        <div class="stat-info">
                            <h3>Total à Payer</h3>
                            <p style="font-size: 22px;"><?= number_format($stats['total_a_payer'] ?? 0, 0, ',', ' ') ?> Ar</p>
                            <div class="stat-trend">
                                <span>Mois en cours</span>
                            </div>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-icon danger">
                            <i class="fas fa-users"></i>
                        </div>
                        <div class="stat-info">
                            <h3>Vacataires Ce Mois</h3>
                            <p><?= $stats['vacataires_ce_mois'] ?? 0 ?></p>
                            <div class="stat-trend">
                                <span>Heures à saisir</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Dashboard Grid -->
                <div class="dashboard-grid">
                    <!-- Bulletins en Attente -->
                    <div class="dashboard-card">
                        <div class="dashboard-card-header">
                            <h2><i class="fas fa-clock"></i> Bulletins en Attente de Validation</h2>
                            <a href="<?= $baseUrl ?>/app/controllers/ComptableController.php?action=listBulletins">
                                <i class="fas fa-list"></i> Voir tout
                            </a>
                        </div>
                        
                        <?php if (!empty($bulletinsEnAttente)): ?>
                            <div style="overflow-x: auto;">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>Employé</th>
                                            <th>Matricule</th>
                                            <th>Mois/Année</th>
                                            <th>Salaire Net</th>
                                            <th>Statut</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($bulletinsEnAttente as $bulletin): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($bulletin['prenom'] . ' ' . $bulletin['nom']) ?></td>
                                            <td><?= htmlspecialchars($bulletin['matricule']) ?></td>
                                            <td><?= $bulletin['mois'] ?>/<?= $bulletin['annee'] ?></td>
                                            <td><strong><?= number_format($bulletin['salaire_net'], 0, ',', ' ') ?> Ar</strong></td>
                                            <td><span class="badge badge-brouillon">Brouillon</span></td>
                                            <td>
                                                <a href="<?= $baseUrl ?>/app/controllers/ComptableController.php?action=validerBulletin&id=<?= $bulletin['id_bulletin'] ?>" 
                                                   class="action-btn action-btn-validate" 
                                                   onclick="return confirm('Valider ce bulletin ?')">
                                                    <i class="fas fa-check"></i> Valider
                                                </a>
                                                <a href="<?= $baseUrl ?>/app/controllers/ComptableController.php?action=genererPDF&id=<?= $bulletin['id_bulletin'] ?>" 
                                                   class="action-btn action-btn-pdf">
                                                    <i class="fas fa-file-pdf"></i> PDF
                                                </a>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <p style="text-align: center; color: var(--gray); padding: 40px 0;">
                                <i class="fas fa-check-circle" style="font-size: 48px; color: var(--success); margin-bottom: 16px;"></i><br>
                                <strong>Aucun bulletin en attente de validation</strong><br>
                                <span style="font-size: 14px;">Tous les bulletins sont à jour</span>
                            </p>
                        <?php endif; ?>
                    </div>

                    <!-- Actions Rapides & Activité Récente -->
                    <div style="display: flex; flex-direction: column; gap: 24px;">
                        <!-- Actions Rapides -->
                        <div class="dashboard-card">
                            <div class="dashboard-card-header">
                                <h2><i class="fas fa-bolt"></i> Actions Rapides</h2>
                            </div>
                            <div class="quick-actions">
                                <a href="<?= $baseUrl ?>/app/controllers/ComptableController.php?action=showGenererBulletin" class="quick-action-btn">
                                    <i class="fas fa-calculator"></i>
                                    <span>Générer Bulletin</span>
                                </a>
                                <a href="<?= $baseUrl ?>/app/controllers/ComptableController.php?action=showSaisieHeures" class="quick-action-btn">
                                    <i class="fas fa-clock"></i>
                                    <span>Saisie Heures</span>
                                </a>
                                <a href="<?= $baseUrl ?>/app/controllers/ComptableController.php?action=listBulletins" class="quick-action-btn">
                                    <i class="fas fa-list"></i>
                                    <span>Liste Bulletins</span>
                                </a>
                                <a href="<?= $baseUrl ?>/app/controllers/ComptableController.php?action=rapports" class="quick-action-btn">
                                    <i class="fas fa-chart-bar"></i>
                                    <span>Rapports</span>
                                </a>
                            </div>
                        </div>

                        <!-- Activité Récente -->
                        <div class="dashboard-card">
                            <div class="dashboard-card-header">
                                <h2><i class="fas fa-history"></i> Activité Récente</h2>
                            </div>
                            <div class="activity-list">
                                <?php foreach ($recentActivity as $activity): ?>
                                <div class="activity-item">
                                    <div class="activity-icon <?= $activity['type'] ?>">
                                        <?php if ($activity['type'] === 'bulletin'): ?>
                                            <i class="fas fa-file-invoice"></i>
                                        <?php elseif ($activity['type'] === 'validation'): ?>
                                            <i class="fas fa-check-circle"></i>
                                        <?php else: ?>
                                            <i class="fas fa-umbrella-beach"></i>
                                        <?php endif; ?>
                                    </div>
                                    <div class="activity-content">
                                        <h4><?= htmlspecialchars($activity['description']) ?></h4>
                                        <div class="activity-time"><?= htmlspecialchars($activity['date']) ?></div>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</body>
</html>