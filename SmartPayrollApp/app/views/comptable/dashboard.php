<?php
/**
 * ============================================================================
 * SMARTPAYROLL - Dashboard Comptable
 * ============================================================================
 * 
 * Tableau de bord du comptable avec gestion des bulletins de paie
 * 
 * @author [Ton nom] - Étudiante BTS Génie Logiciel
 * @establishment Institut Supérieur Mony Keng (ISMK)
 * @project Stage BTS - Digitalisation de la gestion des salaires
 * @version 1.0
 * @date Février 2026
 * ============================================================================
 */

// Vérifier l'authentification et les permissions
require_once __DIR__ . '/../../core/Session.php';
\App\Core\Session::start();
\App\Core\Session::requireRole(['admin', 'comptable']);

// Définir BASE_URL
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'];
$scriptDir = dirname($_SERVER['SCRIPT_NAME']);
$baseUrl = rtrim($protocol . '://' . $host . $scriptDir, '/');
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
        /* VARIABLES CSS - Identiques au dashboard Admin */
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
            border-radius: 8px;
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

        /* Stats Cards */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
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

        /* Bulletins Table */
        .dashboard-card {
            background: white;
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow);
            padding: 24px;
            margin-bottom: 24px;
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
        }

        .btn-primary {
            background: var(--primary);
            color: white;
        }

        .btn-primary:hover {
            background: var(--primary-dark);
        }

        .btn-success {
            background: var(--success);
            color: white;
        }

        .btn-success:hover {
            background: #0da271;
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
            background: #e0e7ff;
            color: var(--primary);
        }

        .badge-valide {
            background: #dcfce7;
            color: var(--success);
        }

        .badge-paye {
            background: #dbeafe;
            color: var(--primary);
        }

        .badge-annule {
            background: #fee2e2;
            color: var(--danger);
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
        }

        .action-btn-view {
            background: var(--primary-light);
            color: var(--primary);
        }

        .action-btn-view:hover {
            background: var(--primary);
            color: white;
        }

        .action-btn-generate {
            background: #dcfce7;
            color: var(--success);
        }

        .action-btn-generate:hover {
            background: var(--success);
            color: white;
        }

        .action-btn-validate {
            background: var(--primary-light);
            color: var(--primary);
        }

        .action-btn-validate:hover {
            background: var(--primary);
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
                    <a href="<?= $baseUrl ?>/../app/controllers/AuthController.php?action=logout" 
                       style="background: var(--danger); color: white; padding: 8px 16px; border-radius: var(--radius); font-weight: 600;">
                        <i class="fas fa-sign-out-alt"></i> Déconnexion
                    </a>
                </div>
            </header>

            <!-- Container -->
            <div class="container">
                <!-- Welcome Message -->
                <div style="background: linear-gradient(135deg, #047857, #065f46); color: white; padding: 24px; border-radius: var(--radius-lg); margin-bottom: 32px;">
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
                            <p>12</p>
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
                            <p>28</p>
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
                            <p style="font-size: 22px;">12 450 000 Ar</p>
                            <div class="stat-trend">
                                <span>Mois en cours</span>
                            </div>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-icon danger">
                            <i class="fas fa-times-circle"></i>
                        </div>
                        <div class="stat-info">
                            <h3>Bulletins Annulés</h3>
                            <p>2</p>
                            <div class="stat-trend negative">
                                <i class="fas fa-arrow-down"></i>
                                <span>-1 vs mois dernier</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Bulletins en Attente -->
                <div class="dashboard-card">
                    <div class="dashboard-card-header">
                        <h2><i class="fas fa-clock"></i> Bulletins en Attente de Validation</h2>
                        <button class="btn btn-primary" onclick="window.location.href='#'">
                            <i class="fas fa-plus"></i> Générer Nouveau Bulletin
                        </button>
                    </div>
                    
                    <div style="overflow-x: auto;">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Employé</th>
                                    <th>Matricule</th>
                                    <th>Mois/Année</th>
                                    <th>Salaire Net</th>
                                    <th>Statut</th>
                                    <th>Date Création</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Claire Rakotomalala</td>
                                    <td>EMP005</td>
                                    <td>Décembre 2024</td>
                                    <td>485 000 Ar</td>
                                    <td><span class="badge badge-brouillon">Brouillon</span></td>
                                    <td>15/12/2024</td>
                                    <td>
                                        <button class="action-btn action-btn-view">
                                            <i class="fas fa-eye"></i> Voir
                                        </button>
                                        <button class="action-btn action-btn-validate">
                                            <i class="fas fa-check"></i> Valider
                                        </button>
                                    </td>
                                </tr>
                                <tr>
                                    <td>Paul Tiana</td>
                                    <td>EMP004</td>
                                    <td>Décembre 2024</td>
                                    <td>512 000 Ar</td>
                                    <td><span class="badge badge-brouillon">Brouillon</span></td>
                                    <td>15/12/2024</td>
                                    <td>
                                        <button class="action-btn action-btn-view">
                                            <i class="fas fa-eye"></i> Voir
                                        </button>
                                        <button class="action-btn action-btn-validate">
                                            <i class="fas fa-check"></i> Valider
                                        </button>
                                    </td>
                                </tr>
                                <tr>
                                    <td>Marie Andry</td>
                                    <td>EMP003</td>
                                    <td>Décembre 2024</td>
                                    <td>750 000 Ar</td>
                                    <td><span class="badge badge-brouillon">Brouillon</span></td>
                                    <td>14/12/2024</td>
                                    <td>
                                        <button class="action-btn action-btn-view">
                                            <i class="fas fa-eye"></i> Voir
                                        </button>
                                        <button class="action-btn action-btn-validate">
                                            <i class="fas fa-check"></i> Valider
                                        </button>
                                    </td>
                                </tr>
                                <tr>
                                    <td>Tiana Razafy (Vacataire)</td>
                                    <td>VAC001</td>
                                    <td>Décembre 2024</td>
                                    <td>810 000 Ar</td>
                                    <td><span class="badge badge-brouillon">Brouillon</span></td>
                                    <td>16/12/2024</td>
                                    <td>
                                        <button class="action-btn action-btn-view">
                                            <i class="fas fa-eye"></i> Voir
                                        </button>
                                        <button class="action-btn action-btn-validate">
                                            <i class="fas fa-check"></i> Valider
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="dashboard-card">
                    <div class="dashboard-card-header">
                        <h2><i class="fas fa-bolt"></i> Actions Rapides</h2>
                    </div>
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 16px;">
                        <button class="btn btn-primary" style="width: 100%;" onclick="window.location.href='#'">
                            <i class="fas fa-calculator"></i> Calcul Paie
                        </button>
                        <button class="btn btn-success" style="width: 100%;" onclick="window.location.href='#'">
                            <i class="fas fa-clock"></i> Saisie Heures
                        </button>
                        <button class="btn btn-primary" style="width: 100%;" onclick="window.location.href='#'">
                            <i class="fas fa-file-pdf"></i> Générer PDF
                        </button>
                        <button class="btn" style="width: 100%; background: #fef3c7; color: #92400e;" onclick="window.location.href='#'">
                            <i class="fas fa-chart-bar"></i> Rapports
                        </button>
                    </div>
                </div>
            </div>
        </main>
    </div>
</body>
</html>