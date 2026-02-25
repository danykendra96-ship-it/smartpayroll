<?php
/**
 * ============================================================================
 * SMARTPAYROLL - Dashboard Employé
 * ============================================================================
 * 
 * Tableau de bord de l'employé avec consultation de ses bulletins personnels
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
\App\Core\Session::requireRole('employe');
$employe = $employe ?? [];
$mesBulletins = $mesBulletins ?? [];
$dernierSalaire = $dernierSalaire ?? 0;
$nbBulletins = $nbBulletins ?? 0;
$anciennete = $anciennete ?? '-';
$moisNoms = $moisNoms ?? [1=>'Janvier',2=>'Février',3=>'Mars',4=>'Avril',5=>'Mai',6=>'Juin',7=>'Juillet',8=>'Août',9=>'Septembre',10=>'Octobre',11=>'Novembre',12=>'Décembre'];
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
    <title>Mon Espace | SmartPayroll - ISMK</title>
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        /* VARIABLES CSS - Identiques */
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

        /* Profile Card */
        .profile-card {
            background: white;
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow);
            padding: 32px;
            margin-bottom: 24px;
            display: flex;
            align-items: center;
            gap: 32px;
        }

        .profile-avatar {
            width: 100px;
            height: 100px;
            background: var(--primary-light);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--primary);
            font-weight: 700;
            font-size: 36px;
        }

        .profile-info h2 {
            font-size: 28px;
            font-weight: 700;
            margin-bottom: 8px;
            color: var(--dark);
        }

        .profile-info p {
            font-size: 16px;
            color: var(--gray);
            margin-bottom: 4px;
        }

        .profile-badge {
            display: inline-block;
            padding: 6px 16px;
            background: var(--primary-light);
            color: var(--primary);
            border-radius: 20px;
            font-weight: 600;
            font-size: 14px;
            margin-top: 8px;
        }

        /* Stats Cards */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 24px;
            margin-bottom: 32px;
        }

        .stat-card {
            background: white;
            padding: 24px;
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow);
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
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
            margin-bottom: 16px;
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

        .stat-info h3 {
            font-size: 14px;
            color: var(--gray);
            margin-bottom: 8px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .stat-info p {
            font-size: 28px;
            font-weight: 700;
            color: var(--dark);
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

        .action-btn {
            padding: 8px 16px;
            border-radius: var(--radius);
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            transition: var(--transition);
            border: none;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .action-btn-download {
            background: var(--primary-light);
            color: var(--primary);
        }

        .action-btn-download:hover {
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
            
            .profile-card {
                flex-direction: column;
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
                <div style="font-size: 12px; color: var(--gray); margin-top: 4px;">Mon Espace</div>
            </div>
            
            <div class="sidebar-user">
                <div class="sidebar-user-info">
                    <div class="sidebar-user-avatar"><?= strtoupper(substr($_SESSION['user_prenom'], 0, 1)) ?></div>
                    <div class="sidebar-user-details">
                        <h4><?= htmlspecialchars($_SESSION['user_prenom'] . ' ' . $_SESSION['user_nom']) ?></h4>
                        <p><?= htmlspecialchars($_SESSION['user_poste']) ?></p>
                    </div>
                </div>
            </div>
            
            <nav class="sidebar-menu">
                <a href="/SmartPayrollApp/app/controllers/EmployeController.php?action=dashboard" class="sidebar-menu-item active" style="text-decoration:none;color:inherit;">
                    <i class="fas fa-home"></i><span>Dashboard</span>
                </a>
                <a href="/SmartPayrollApp/app/controllers/EmployeController.php?action=mesBulletins" class="sidebar-menu-item" style="text-decoration:none;color:inherit;">
                    <i class="fas fa-file-invoice"></i><span>Mes Bulletins</span>
                </a>
                <a href="/SmartPayrollApp/app/controllers/EmployeController.php?action=monProfil" class="sidebar-menu-item" style="text-decoration:none;color:inherit;">
                    <i class="fas fa-user"></i><span>Mon Profil</span>
                </a>
                <a href="/SmartPayrollApp/app/controllers/EmployeController.php?action=mesConges" class="sidebar-menu-item" style="text-decoration:none;color:inherit;">
                    <i class="fas fa-umbrella-beach"></i><span>Mes Congés</span>
                </a>
                <a href="/SmartPayrollApp/app/controllers/EmployeController.php?action=mesDocuments" class="sidebar-menu-item" style="text-decoration:none;color:inherit;">
                    <i class="fas fa-download"></i><span>Mes Documents</span>
                </a>
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <!-- Header -->
            <header class="header">
                <div class="header-left">
                    <h1>Mon Espace Personnel</h1>
                </div>
                <div class="header-right">
                    <div class="header-user">
                        <div class="header-user-avatar"><?= strtoupper(substr($_SESSION['user_prenom'], 0, 1)) ?></div>
                        <div class="header-user-info">
                            <div class="header-user-name"><?= htmlspecialchars($_SESSION['user_prenom']) ?></div>
                            <div class="header-user-role">Employé</div>
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
                <!-- Profile Card -->
                <div class="profile-card">
                    <div class="profile-avatar">
                        <?= strtoupper(substr($_SESSION['user_prenom'], 0, 1)) ?>
                    </div>
                    <div class="profile-info">
                        <h2><?= htmlspecialchars($_SESSION['user_prenom'] . ' ' . $_SESSION['user_nom']) ?></h2>
                        <p><strong>Matricule :</strong> <?= htmlspecialchars($_SESSION['user_matricule']) ?></p>
                        <p><strong>Poste :</strong> <?= htmlspecialchars($_SESSION['user_poste']) ?></p>
                        <p><strong>Département :</strong> <?= htmlspecialchars($_SESSION['user_departement']) ?></p>
                        <span class="profile-badge">
                            <i class="fas fa-id-badge"></i> <?= ucfirst($_SESSION['user_role']) ?>
                        </span>
                    </div>
                </div>

                <!-- Stats Cards -->
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-icon primary">
                            <i class="fas fa-file-invoice-dollar"></i>
                        </div>
                        <div class="stat-info">
                            <h3>Dernier Salaire</h3>
                            <p>485 000 Ar</p>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-icon success">
                            <i class="fas fa-umbrella-beach"></i>
                        </div>
                        <div class="stat-info">
                            <h3>Solde Congés</h3>
                            <p>15 jours</p>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-icon warning">
                            <i class="fas fa-file-pdf"></i>
                        </div>
                        <div class="stat-info">
                            <h3>Mes Bulletins</h3>
                            <p>12</p>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-icon primary">
                            <i class="fas fa-calendar-check"></i>
                        </div>
                        <div class="stat-info">
                            <h3>Ancienneté</h3>
                            <p>2 ans</p>
                        </div>
                    </div>
                </div>

                <!-- Mes Bulletins -->
                <div class="dashboard-card">
                    <div class="dashboard-card-header">
                        <h2><i class="fas fa-file-invoice"></i> Mes Bulletins de Paie</h2>
                        <a href="/SmartPayrollApp/app/controllers/EmployeController.php?action=mesBulletins" class="btn btn-primary">Voir tous</a>
                    </div>
                    <div style="overflow-x: auto;">
                        <table class="table">
                            <thead>
                                <tr><th>Mois/Année</th><th>Salaire de Base</th><th>Primes</th><th>Retenues</th><th>Salaire Net</th><th>Statut</th><th>Actions</th></tr>
                            </thead>
                            <tbody>
                                <?php foreach (array_slice($mesBulletins, 0, 5) as $b): 
                                    $mois = (int)($b['mois'] ?? 0);
                                    $annee = (int)($b['annee'] ?? 0);
                                    $libMois = ($moisNoms[$mois] ?? $mois) . ' ' . $annee;
                                    $statutClass = ['brouillon'=>'badge-brouillon','valide'=>'badge-valide','paye'=>'badge-paye','annule'=>'badge-brouillon'];
                                ?>
                                <tr>
                                    <td><?= htmlspecialchars($libMois) ?></td>
                                    <td><?= number_format((float)($b['salaire_base'] ?? 0), 0, ',', ' ') ?> Ar</td>
                                    <td><?= number_format((float)($b['primes_total'] ?? 0), 0, ',', ' ') ?> Ar</td>
                                    <td><?= number_format((float)($b['retenues_total'] ?? 0), 0, ',', ' ') ?> Ar</td>
                                    <td><strong><?= number_format((float)($b['salaire_net'] ?? 0), 0, ',', ' ') ?> Ar</strong></td>
                                    <td><span class="badge <?= $statutClass[$b['statut'] ?? 'brouillon'] ?? 'badge-brouillon' ?>"><?= ucfirst($b['statut'] ?? '') ?></span></td>
                                    <td>
                                        <a href="/SmartPayrollApp/app/controllers/EmployeController.php?action=telechargerPDF&id=<?= $b['id_bulletin'] ?>" class="action-btn action-btn-download"><i class="fas fa-download"></i> PDF</a>
                                        <a href="/SmartPayrollApp/app/controllers/EmployeController.php?action=voirBulletin&id=<?= $b['id_bulletin'] ?>" class="action-btn action-btn-download" style="background: #e0e7ff; color: var(--primary);"><i class="fas fa-eye"></i> Voir</a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                                <?php if (empty($mesBulletins)): ?><tr><td colspan="7" style="text-align:center;color:var(--gray);">Aucun bulletin.</td></tr><?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="dashboard-card">
                    <div class="dashboard-card-header"><h2><i class="fas fa-link"></i> Accès rapide</h2></div>
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(160px, 1fr)); gap: 16px;">
                        <a href="/SmartPayrollApp/app/controllers/EmployeController.php?action=monProfil" class="btn" style="background: var(--primary-light); color: var(--primary); width: 100%; justify-content: center; text-decoration: none;"><i class="fas fa-user"></i> Mon Profil</a>
                        <a href="/SmartPayrollApp/app/controllers/EmployeController.php?action=mesConges" class="btn" style="background: #dcfce7; color: var(--success); width: 100%; justify-content: center; text-decoration: none;"><i class="fas fa-umbrella-beach"></i> Mes Congés</a>
                        <a href="/SmartPayrollApp/app/controllers/EmployeController.php?action=mesDocuments" class="btn" style="background: #fef3c7; color: var(--warning); width: 100%; justify-content: center; text-decoration: none;"><i class="fas fa-download"></i> Documents</a>
                    </div>
                </div>
            </div>
        </main>
    </div>
</body>
</html>