<?php
/**
 * ============================================================================
 * SMARTPAYROLL - Dashboard Administrateur
 * ============================================================================
 * 
 * Tableau de bord de l'administrateur avec statistiques et gestion globale
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
\App\Core\Session::requireRole('admin');

// Récupérer les données du dashboard
require_once __DIR__ . '/../../models/Employe.php';
$employeModel = new \App\Models\Employe();

// Statistiques
$totalEmployes = $employeModel->countAll();
$employesActifs = $employeModel->countActive();
$statsByRole = $employeModel->getStatsByRole();

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
    <title>Dashboard Admin | SmartPayroll - ISMK</title>
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        /* ============================================================================
           VARIABLES CSS
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

        .header-notification {
            position: relative;
            cursor: pointer;
        }

        .header-notification-badge {
            position: absolute;
            top: -4px;
            right: -4px;
            width: 16px;
            height: 16px;
            background: var(--danger);
            border-radius: 50%;
            font-size: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
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

        /* Charts & Tables */
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

        .chart-container {
            height: 280px;
            position: relative;
        }

        .chart-placeholder {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 8px;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
        }

        .role-stats {
            display: flex;
            flex-direction: column;
            gap: 16px;
        }

        .role-stat-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 12px;
            border-radius: var(--radius);
            background: var(--light);
        }

        .role-stat-item.admin {
            background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%);
            border-left: 4px solid var(--primary);
        }

        .role-stat-item.comptable {
            background: linear-gradient(135deg, #dcfce7 0%, #bbf7d0 100%);
            border-left: 4px solid var(--success);
        }

        .role-stat-item.employe {
            background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
            border-left: 4px solid var(--warning);
        }

        .role-stat-label {
            font-size: 15px;
            font-weight: 600;
        }

        .role-stat-count {
            font-size: 24px;
            font-weight: 700;
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
                <div style="font-size: 12px; color: var(--gray); margin-top: 4px;">Admin Panel</div>
            </div>
            
            <div class="sidebar-user">
                <div class="sidebar-user-info">
                    <div class="sidebar-user-avatar"><?= strtoupper(substr($_SESSION['user_prenom'], 0, 1)) ?></div>
                    <div class="sidebar-user-details">
                        <h4><?= htmlspecialchars($_SESSION['user_prenom'] . ' ' . $_SESSION['user_nom']) ?></h4>
                        <p>Administrateur</p>
                    </div>
                </div>
            </div>
            
            <nav class="sidebar-menu">
                <a href="/SmartPayrollApp/app/controllers/AdminController.php?action=dashboard" class="sidebar-menu-item active" style="text-decoration: none; color: inherit;">
                    <i class="fas fa-home"></i>
                    <span>Dashboard</span>
                </a>
                <a href="/SmartPayrollApp/app/controllers/AdminController.php?action=listEmployes" class="sidebar-menu-item" style="text-decoration: none; color: inherit;">
                    <i class="fas fa-users"></i>
                    <span>Gestion Employés</span>
                </a>
                <a href="/SmartPayrollApp/app/controllers/AdminController.php?action=gestionEntreprise" class="sidebar-menu-item" style="text-decoration: none; color: inherit;">
                    <i class="fas fa-building"></i>
                    <span>Entreprise</span>
                </a>
                <a href="/SmartPayrollApp/app/controllers/AdminController.php?action=gestionDepartements" class="sidebar-menu-item" style="text-decoration: none; color: inherit;">
                    <i class="fas fa-sitemap"></i>
                    <span>Départements</span>
                </a>
                <a href="/SmartPayrollApp/app/controllers/AdminController.php?action=gestionPostes" class="sidebar-menu-item" style="text-decoration: none; color: inherit;">
                    <i class="fas fa-briefcase"></i>
                    <span>Postes</span>
                </a>
                <a href="/SmartPayrollApp/app/controllers/AdminController.php?action=parametres" class="sidebar-menu-item" style="text-decoration: none; color: inherit;">
                    <i class="fas fa-cog"></i>
                    <span>Paramètres</span>
                </a>
                <a href="/SmartPayrollApp/app/controllers/AdminController.php?action=rapports" class="sidebar-menu-item" style="text-decoration: none; color: inherit;">
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
                    <h1>Tableau de Bord Administrateur</h1>
                </div>
                <div class="header-right">
                    <div class="header-notification">
                        <i class="fas fa-bell" style="font-size: 20px; color: var(--gray);"></i>
                        <span class="header-notification-badge">3</span>
                    </div>
                    <div class="header-user">
                        <div class="header-user-avatar"><?= strtoupper(substr($_SESSION['user_prenom'], 0, 1)) ?></div>
                        <div class="header-user-info">
                            <div class="header-user-name"><?= htmlspecialchars($_SESSION['user_prenom']) ?></div>
                            <div class="header-user-role">Admin</div>
                        </div>
                    </div>
                    <a href="/SmartPayrollApp/app/controllers/AuthController.php?action=logout" 
                       style="background: var(--danger); color: white; padding: 8px 16px; border-radius: var(--radius); font-weight: 600; text-decoration: none;">
                        <i class="fas fa-sign-out-alt"></i> Déconnexion
                    </a>
                </div>
            </header>

            <!-- Container -->
            <div class="container">
                <!-- Welcome Message -->
                <div style="background: linear-gradient(135deg, var(--primary), var(--primary-dark)); color: white; padding: 24px; border-radius: var(--radius-lg); margin-bottom: 32px;">
                    <h2 style="font-size: 28px; margin-bottom: 8px;">👋 Bonjour, <?= htmlspecialchars($_SESSION['user_prenom']) ?> !</h2>
                    <p style="font-size: 16px; opacity: 0.9;">Bienvenue sur votre tableau de bord d'administration. Gérez efficacement les ressources humaines de l'ISMK.</p>
                </div>

                <!-- Stats Cards -->
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-icon primary">
                            <i class="fas fa-users"></i>
                        </div>
                        <div class="stat-info">
                            <h3>Total Employés</h3>
                            <p><?= $totalEmployes ?></p>
                            <div class="stat-trend positive">
                                <i class="fas fa-arrow-up"></i>
                                <span>+5 ce mois</span>
                            </div>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-icon success">
                            <i class="fas fa-user-check"></i>
                        </div>
                        <div class="stat-info">
                            <h3>Employés Actifs</h3>
                            <p><?= $employesActifs ?></p>
                            <div class="stat-trend positive">
                                <i class="fas fa-arrow-up"></i>
                                <span>+3 ce mois</span>
                            </div>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-icon warning">
                            <i class="fas fa-file-invoice-dollar"></i>
                        </div>
                        <div class="stat-info">
                            <h3>Bulletins Ce Mois</h3>
                            <p>45</p>
                            <div class="stat-trend positive">
                                <i class="fas fa-arrow-up"></i>
                                <span>+12 vs mois dernier</span>
                            </div>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-icon danger">
                            <i class="fas fa-user-times"></i>
                        </div>
                        <div class="stat-info">
                            <h3>Employés Inactifs</h3>
                            <p><?= $totalEmployes - $employesActifs ?></p>
                            <div class="stat-trend negative">
                                <i class="fas fa-arrow-down"></i>
                                <span>-2 ce mois</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Dashboard Grid -->
                <div class="dashboard-grid">
                    <!-- Recent Activity -->
                    <div class="dashboard-card">
                        <div class="dashboard-card-header">
                            <h2><i class="fas fa-history"></i> Activité Récente</h2>
                            <a href="#">Voir tout</a>
                        </div>
                        <div class="activity-list">
                            <div class="activity-item">
                                <div class="activity-icon create">
                                    <i class="fas fa-user-plus"></i>
                                </div>
                                <div class="activity-content">
                                    <h4>Nouvel employé ajouté</h4>
                                    <p>Claire Rakotomalala (Commercial)</p>
                                    <div class="activity-time">Il y a 2 heures</div>
                                </div>
                            </div>
                            <div class="activity-item">
                                <div class="activity-icon update">
                                    <i class="fas fa-user-edit"></i>
                                </div>
                                <div class="activity-content">
                                    <h4>Profil mis à jour</h4>
                                    <p>Paul Tiana a modifié ses informations</p>
                                    <div class="activity-time">Il y a 5 heures</div>
                                </div>
                            </div>
                            <div class="activity-item">
                                <div class="activity-icon create">
                                    <i class="fas fa-briefcase"></i>
                                </div>
                                <div class="activity-content">
                                    <h4>Nouveau poste créé</h4>
                                    <p>Développeur Full-Stack</p>
                                    <div class="activity-time">Hier</div>
                                </div>
                            </div>
                            <div class="activity-item">
                                <div class="activity-icon delete">
                                    <i class="fas fa-user-minus"></i>
                                </div>
                                <div class="activity-content">
                                    <h4>Employé désactivé</h4>
                                    <p>Jean Dupont (CDI)</p>
                                    <div class="activity-time">2 jours ago</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Role Statistics & Quick Actions -->
                    <div style="display: flex; flex-direction: column; gap: 24px;">
                        <!-- Role Stats -->
                        <div class="dashboard-card">
                            <div class="dashboard-card-header">
                                <h2><i class="fas fa-chart-pie"></i> Répartition par Rôle</h2>
                            </div>
                            <div class="role-stats">
                                <?php foreach ($statsByRole as $stat): ?>
                                    <div class="role-stat-item <?= $stat['role'] ?>">
                                        <div class="role-stat-label">
                                            <i class="fas fa-user-shield"></i>
                                            <?= ucfirst($stat['role']) ?>
                                        </div>
                                        <div class="role-stat-count"><?= $stat['count'] ?></div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <!-- Quick Actions -->
                        <div class="dashboard-card">
                            <div class="dashboard-card-header">
                                <h2><i class="fas fa-bolt"></i> Actions Rapides</h2>
                            </div>
                            <div class="quick-actions">
                                <div class="quick-action-btn" onclick="window.location.href='/SmartPayrollApp/app/controllers/AdminController.php?action=showAddEmploye'">
                                    <i class="fas fa-user-plus"></i>
                                    <span>Ajouter Employé</span>
                                </div>
                                <div class="quick-action-btn" onclick="window.location.href='#'">
                                    <i class="fas fa-briefcase"></i>
                                    <span>Nouveau Poste</span>
                                </div>
                                <div class="quick-action-btn" onclick="window.location.href='#'">
                                    <i class="fas fa-sitemap"></i>
                                    <span>Nouveau Département</span>
                                </div>
                                <div class="quick-action-btn" onclick="window.location.href='#'">
                                    <i class="fas fa-cog"></i>
                                    <span>Paramètres</span>
                                </div>
                                
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</body>
</html>