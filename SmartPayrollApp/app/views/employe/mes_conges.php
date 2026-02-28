<?php
require_once __DIR__ . '/../../core/Session.php';
\App\Core\Session::start();
\App\Core\Session::requireRole('employe');

$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'];
$scriptName = $_SERVER['SCRIPT_NAME'];
$projectPath = preg_replace('#^(/[^/]+).*#', '$1', $scriptName);
$baseUrl = $protocol . '://' . $host . $projectPath;

$error = $_GET['error'] ?? null;
$success = $_GET['success'] ?? null;

// Stats (passées par le contrôleur)
$stats = $stats ?? [
    'en_attente' => 0,
    'approuve' => 0,
    'refuse' => 0,
    'annule' => 0,
    'solde' => 15
];

// Mes congés (passés par le contrôleur)
$mesConges = $mesConges ?? [];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mes Congés | SmartPayroll - ISMK</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        /* ... (MÊME CSS QUE dashboard.php - copie le CSS complet de dashboard.php ici) ... */
        /* Pour gagner de l'espace, je ne répète pas tout le CSS - utilise le même que dashboard.php */
        /* Tu peux copier-coller le CSS de dashboard.php dans ce fichier */
    </style>
</head>
<body>
    <div class="dashboard-layout">
        <!-- Sidebar -->
        <aside class="sidebar">
            <!-- ... (MÊME SIDEBAR QUE dashboard.php) ... -->
            <div class="sidebar-header">
                <div class="sidebar-logo">
                    <div class="sidebar-logo-badge">ISMK</div>
                    <div class="sidebar-logo-text">
                        <div class="establishment">Institut Supérieur</div>
                        <div class="app-name">SmartPayroll</div>
                    </div>
                </div>
            </div>
            
            <div class="sidebar-user">
                <div class="sidebar-user-info">
                    <div class="sidebar-user-avatar"><?= strtoupper(substr($_SESSION['user_prenom'] ?? 'E', 0, 1)) ?></div>
                    <div class="sidebar-user-details">
                        <h4><?= htmlspecialchars($_SESSION['user_prenom'] ?? 'Employé') . ' ' . htmlspecialchars($_SESSION['user_nom'] ?? '') ?></h4>
                        <div class="role-badge">
                            <i class="fas fa-user"></i> Employé
                        </div>
                    </div>
                </div>
            </div>
            
            <nav class="sidebar-menu">
                <div class="menu-section">
                    <div class="menu-section-title">MON ESPACE</div>
                    <a href="<?= $baseUrl ?>/app/controllers/EmployeController.php?action=dashboard" class="sidebar-menu-item">
                        <i class="fas fa-home"></i>
                        <span>Dashboard</span>
                    </a>
                    <a href="<?= $baseUrl ?>/app/controllers/EmployeController.php?action=mesBulletins" class="sidebar-menu-item">
                        <i class="fas fa-file-invoice"></i>
                        <span>Mes Bulletins</span>
                    </a>
                    <a href="<?= $baseUrl ?>/app/controllers/EmployeController.php?action=monProfil" class="sidebar-menu-item">
                        <i class="fas fa-user"></i>
                        <span>Mon Profil</span>
                    </a>
                </div>
                
                <div class="menu-section">
                    <div class="menu-section-title">CONGÉS</div>
                    <a href="<?= $baseUrl ?>/app/controllers/EmployeController.php?action=mesConges" class="sidebar-menu-item active">
                        <i class="fas fa-umbrella-beach"></i>
                        <span>Mes Congés</span>
                    </a>
                    <a href="<?= $baseUrl ?>/app/controllers/EmployeController.php?action=demanderConge" class="sidebar-menu-item">
                        <i class="fas fa-plus-circle"></i>
                        <span>Demande de Congé</span>
                    </a>
                </div>
                
                <div class="menu-section">
                    <div class="menu-section-title">DOCUMENTS</div>
                    <a href="<?= $baseUrl ?>/app/controllers/EmployeController.php?action=mesDocuments" class="sidebar-menu-item">
                        <i class="fas fa-download"></i>
                        <span>Mes Documents</span>
                    </a>
                </div>
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <!-- Header -->
            <header class="main-header">
                <div class="header-title">
                    <i class="fas fa-umbrella-beach"></i>
                    <h1>Mes Congés</h1>
                </div>
                <div class="header-actions">
                    <div class="user-menu">
                        <div class="user-avatar"><?= strtoupper(substr($_SESSION['user_prenom'] ?? 'E', 0, 1)) ?></div>
                        <div class="user-info">
                            <div class="user-name"><?= htmlspecialchars($_SESSION['user_prenom'] ?? 'Employé') ?></div>
                            <div class="user-role">
                                <i class="fas fa-user"></i> Employé
                            </div>
                        </div>
                    </div>
                    <a href="<?= $baseUrl ?>/app/controllers/AuthController.php?action=logout" class="btn-logout">
                        <i class="fas fa-sign-out-alt"></i> Déconnexion
                    </a>
                </div>
            </header>

            <!-- Page Container -->
            <div class="page-container">
                <!-- Messages Système -->
                <?php if ($error): ?>
                    <div class="system-message message-error">
                        <i class="fas fa-exclamation-triangle message-icon"></i>
                        <div>
                            <?php
                            $messages = [
                                'conge_invalide' => 'Congé invalide.',
                                'erreur_annulation' => 'Erreur lors de l\'annulation.',
                                'conge_annule' => 'Congé annulé avec succès.'
                            ];
                            echo htmlspecialchars($messages[$error] ?? 'Une erreur est survenue.');
                            ?>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if ($success): ?>
                    <div class="system-message message-success">
                        <i class="fas fa-check-circle message-icon"></i>
                        <div>
                            <?= htmlspecialchars($success === 'conge_demande' ? 'Demande de congé soumise avec succès.' : ($success === 'conge_annule' ? 'Congé annulé avec succès.' : 'Opération réussie.')); ?>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Statistiques -->
                <section class="stats-section">
                    <div class="section-header">
                        <h2 class="section-title">
                            <i class="fas fa-chart-pie"></i>
                            Statistiques de Mes Congés
                        </h2>
                    </div>
                    
                    <div class="stats-grid">
                        <div class="stat-card stat-orange">
                            <div class="stat-content">
                                <div class="stat-text">
                                    <div class="stat-label">En Attente</div>
                                    <div class="stat-value"><?= $stats['en_attente'] ?? 0 ?></div>
                                    <div class="stat-trend">
                                        <i class="fas fa-clock"></i>
                                        <span>En cours de traitement</span>
                                    </div>
                                </div>
                                <div class="stat-icon-container" style="background: #fffbeb; color: var(--ismk-orange);">
                                    <i class="fas fa-hourglass-half stat-icon orange"></i>
                                </div>
                            </div>
                        </div>

                        <div class="stat-card stat-green">
                            <div class="stat-content">
                                <div class="stat-text">
                                    <div class="stat-label">Approuvés</div>
                                    <div class="stat-value"><?= $stats['approuve'] ?? 0 ?></div>
                                    <div class="stat-trend positive">
                                        <i class="fas fa-check"></i>
                                        <span>Congés validés</span>
                                    </div>
                                </div>
                                <div class="stat-icon-container" style="background: #ecfdf5; color: var(--ismk-green);">
                                    <i class="fas fa-check-circle stat-icon success"></i>
                                </div>
                            </div>
                        </div>

                        <div class="stat-card stat-red">
                            <div class="stat-content">
                                <div class="stat-text">
                                    <div class="stat-label">Refusés</div>
                                    <div class="stat-value"><?= $stats['refuse'] ?? 0 ?></div>
                                    <div class="stat-trend negative">
                                        <i class="fas fa-times"></i>
                                        <span>Demandes rejetées</span>
                                    </div>
                                </div>
                                <div class="stat-icon-container" style="background: #fef2f2; color: var(--ismk-red);">
                                    <i class="fas fa-times-circle stat-icon danger"></i>
                                </div>
                            </div>
                        </div>

                        <div class="stat-card">
                            <div class="stat-content">
                                <div class="stat-text">
                                    <div class="stat-label">Solde Congés</div>
                                    <div class="stat-value"><?= $stats['solde'] ?? 0 ?> j</div>
                                    <div class="stat-trend">
                                        <i class="fas fa-umbrella-beach"></i>
                                        <span>Disponibles cette année</span>
                                    </div>
                                </div>
                                <div class="stat-icon-container" style="background: var(--ismk-blue-xlight); color: var(--ismk-blue-light);">
                                    <i class="fas fa-calendar-alt stat-icon primary"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- Liste des Congés -->
                <section class="main-card">
                    <div class="card-header">
                        <h2 class="card-title">
                            <i class="fas fa-list"></i>
                            Historique de Mes Congés
                        </h2>
                        <a href="<?= $baseUrl ?>/app/controllers/EmployeController.php?action=demanderConge" class="view-all-link">
                            <i class="fas fa-plus-circle"></i> Demander un Congé
                        </a>
                    </div>
                    
                    <?php if (!empty($mesConges)): ?>
                        <div class="table-container">
                            <table class="data-table">
                                <thead>
                                    <tr>
                                        <th>Date Demande</th>
                                        <th>Période</th>
                                        <th>Type</th>
                                        <th>Nombre de Jours</th>
                                        <th>Motif</th>
                                        <th>Statut</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($mesConges as $conge): ?>
                                    <tr>
                                        <td><?= date('d/m/Y', strtotime($conge['date_demande'])) ?></td>
                                        <td><?= date('d/m/Y', strtotime($conge['date_debut'])) ?> → <?= date('d/m/Y', strtotime($conge['date_fin'])) ?></td>
                                        <td><?= ucfirst($conge['type_conge']) ?></td>
                                        <td><strong><?= $conge['nb_jours'] ?> j</strong></td>
                                        <td><?= htmlspecialchars(substr($conge['motif'], 0, 30)) ?>...</td>
                                        <td>
                                            <?php
                                            $statutClass = '';
                                            $statutText = '';
                                            switch($conge['statut']) {
                                                case 'en_attente':
                                                    $statutClass = 'badge-en-attente';
                                                    $statutText = 'En attente';
                                                    break;
                                                case 'approuve':
                                                    $statutClass = 'badge-approuve';
                                                    $statutText = 'Approuvé';
                                                    break;
                                                case 'refuse':
                                                    $statutClass = 'badge-refuse';
                                                    $statutText = 'Refusé';
                                                    break;
                                                case 'annule':
                                                    $statutClass = 'badge-annule';
                                                    $statutText = 'Annulé';
                                                    break;
                                            }
                                            ?>
                                            <span class="badge <?= $statutClass ?>"><?= $statutText ?></span>
                                        </td>
                                        <td>
                                            <?php if ($conge['statut'] === 'en_attente'): ?>
                                                <a href="<?= $baseUrl ?>/app/controllers/EmployeController.php?action=annulerConge&id=<?= $conge['id_conge'] ?>" 
                                                   class="action-btn pdf" 
                                                   onclick="return confirm('Annuler cette demande de congé ?')">
                                                    <i class="fas fa-times"></i> Annuler
                                                </a>
                                            <?php else: ?>
                                                <span style="color: var(--neutral-500); font-style: italic;">Aucune action</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="empty-state">
                            <i class="fas fa-umbrella-beach empty-icon"></i>
                            <h3 class="empty-title">Aucun congé trouvé</h3>
                            <p class="empty-description">
                                Vous n'avez pas encore effectué de demande de congé. 
                                Cliquez sur le bouton ci-dessous pour en demander un.
                            </p>
                            <a href="<?= $baseUrl ?>/app/controllers/EmployeController.php?action=demanderConge" class="empty-action">
                                <i class="fas fa-plus-circle"></i> Demander un Congé
                            </a>
                        </div>
                    <?php endif; ?>
                </section>

                <!-- Footer -->
                <footer class="main-footer">
                    <div class="footer-line">
                        <i class="fas fa-shield-alt"></i>
                        Application interne de gestion des salaires - 
                        <span class="footer-highlight">Institut Supérieur Mony Keng (ISMK)</span>
                        © <?= date('Y') ?>
                    </div>
                    <div class="footer-subline">
                        <i class="fas fa-lock"></i>
                        <span>Vos données sont strictement confidentielles et personnelles</span>
                    </div>
                </footer>
            </div>
        </main>
    </div>

    <style>
        /* Styles spécifiques pour les badges de statut */
        .badge-en-attente { background: #fef3c7; color: var(--ismk-orange); border: 1px solid #fde68a; }
        .badge-approuve { background: #dcfce7; color: var(--ismk-green); border: 1px solid #bbf7d0; }
        .badge-refuse { background: #fee2e2; color: var(--ismk-red); border: 1px solid #fecaca; }
        .badge-annule { background: #e2e8f0; color: var(--neutral-600); border: 1px solid #cbd5e1; }
    </style>
</body>
</html>