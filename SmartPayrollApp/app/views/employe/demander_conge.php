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
$soldeConges = $soldeConges ?? 15;
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Demander un Congé | SmartPayroll - ISMK</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --ismk-blue: #1e3a8a; --ismk-blue-light: #2563eb; --ismk-green: #047857;
            --ismk-orange: #f59e0b; --ismk-red: #ef4444;
            --neutral-900: #0f172a; --neutral-800: #1e293b; --neutral-700: #334155;
            --neutral-600: #475569; --neutral-500: #64748b; --neutral-400: #94a3b8;
            --neutral-300: #cbd5e1; --neutral-200: #e2e8f0; --neutral-100: #f1f5f9;
            --neutral-50: #f8fafc; --white: #ffffff;
            --shadow-sm: 0 1px 3px 0 rgba(0,0,0,0.1);
            --shadow-md: 0 4px 6px -1px rgba(0,0,0,0.1);
            --shadow-lg: 0 10px 15px -3px rgba(0,0,0,0.1);
            --radius-md: 8px; --radius-lg: 12px; --radius-xl: 16px;
            --space-2: 0.5rem; --space-3: 0.75rem; --space-4: 1rem; --space-5: 1.25rem;
            --space-6: 1.5rem; --space-8: 2rem;
            --text-sm: 0.875rem; --text-base: 1rem; --text-lg: 1.125rem;
            --text-xl: 1.25rem; --text-2xl: 1.5rem; --text-3xl: 1.875rem;
        }
        * { margin:0; padding:0; box-sizing:border-box; }
        body { font-family:'Inter',sans-serif; background:var(--neutral-50); color:var(--neutral-800); }
        .dashboard-layout { display:grid; grid-template-columns:280px 1fr; min-height:100vh; }
        .sidebar { background:linear-gradient(180deg,var(--ismk-blue) 0%,var(--neutral-900) 100%); color:white; position:fixed; height:100vh; width:280px; overflow-y:auto; z-index:100; border-right:1px solid rgba(255,255,255,0.1); }
        .sidebar-header { padding:var(--space-6) var(--space-5) var(--space-5); text-align:center; border-bottom:1px solid rgba(255,255,255,0.1); }
        .sidebar-logo-badge { width:64px; height:64px; background:white; color:var(--ismk-blue); border-radius:var(--radius-xl); display:flex; align-items:center; justify-content:center; font-weight:800; font-size:28px; box-shadow:0 4px 6px rgba(0,0,0,0.1); }
        .sidebar-logo-text .establishment { font-size:var(--text-sm); color:var(--ismk-blue-xlight); font-weight:500; }
        .sidebar-logo-text .app-name { font-size:var(--text-2xl); font-weight:800; background:linear-gradient(90deg,white,var(--ismk-blue-xlight)); -webkit-background-clip:text; -webkit-text-fill-color:transparent; }
        .sidebar-user { padding:var(--space-5) var(--space-5) var(--space-6); background:rgba(30,41,59,0.4); margin-top:var(--space-4); border-radius:var(--radius-xl); }
        .sidebar-user-avatar { width:56px; height:56px; background:var(--ismk-blue-xlight); color:var(--ismk-blue); border-radius:50%; display:flex; align-items:center; justify-content:center; font-weight:700; font-size:22px; border:3px solid white; box-shadow:0 4px 6px rgba(0,0,0,0.15); }
        .sidebar-user-details h4 { font-size:var(--text-lg); font-weight:700; margin-bottom:var(--space-1); color:white; }
        .sidebar-user-details .role-badge { display:inline-flex; align-items:center; gap:var(--space-1); background:rgba(255,255,255,0.2); color:var(--ismk-blue-xlight); padding:var(--space-1) var(--space-2); border-radius:50px; font-size:var(--text-xs); font-weight:600; }
        .sidebar-menu { padding:var(--space-6) 0 var(--space-8); }
        .menu-section { margin-bottom:var(--space-6); }
        .menu-section-title { padding:0 var(--space-6) var(--space-3); font-size:var(--text-xs); text-transform:uppercase; letter-spacing:1.5px; color:rgba(255,255,255,0.5); font-weight:700; }
        .sidebar-menu-item { padding:var(--space-3) var(--space-6); display:flex; align-items:center; gap:var(--space-4); color:rgba(255,255,255,0.85); transition:all 0.2s; font-weight:500; font-size:var(--text-base); position:relative; border-radius:var(--radius-md); margin:0 var(--space-2); }
        .sidebar-menu-item:hover { background:rgba(255,255,255,0.08); color:white; }
        .sidebar-menu-item.active { background:rgba(37,99,235,0.25); color:white; font-weight:600; }
        .sidebar-menu-item.active::before { content:''; position:absolute; left:0; top:50%; transform:translateY(-50%); width:4px; height:70%; background:var(--ismk-blue-light); border-radius:50px; }
        .sidebar-menu-item i { width:24px; font-size:20px; min-width:24px; text-align:center; }
        .sidebar-menu-item.active i { color:var(--ismk-blue-light); }
        .main-content { grid-column:2; display:flex; flex-direction:column; min-height:100vh; }
        .main-header { background:white; box-shadow:var(--shadow-sm); padding:var(--space-4) var(--space-8); display:flex; justify-content:space-between; align-items:center; position:sticky; top:0; z-index:90; }
        .header-title h1 { font-size:var(--text-3xl); font-weight:800; color:var(--neutral-900); display:flex; align-items:center; gap:var(--space-3); }
        .header-title h1 i { color:var(--ismk-orange); font-size:2.25rem; }
        .header-actions { display:flex; align-items:center; gap:var(--space-5); }
        .user-menu { display:flex; align-items:center; gap:var(--space-3); padding:var(--space-2) var(--space-4); border-radius:var(--radius-lg); transition:all 0.2s; cursor:pointer; }
        .user-menu:hover { background:var(--ismk-blue-xlight); }
        .user-avatar { width:48px; height:48px; background:var(--ismk-blue-xlight); color:var(--ismk-blue); border-radius:50%; display:flex; align-items:center; justify-content:center; font-weight:700; font-size:20px; border:2px solid white; box-shadow:var(--shadow-sm); }
        .user-info { display:flex; flex-direction:column; min-width:130px; }
        .user-name { font-size:var(--text-lg); font-weight:700; color:var(--neutral-900); }
        .user-role { font-size:var(--text-sm); color:var(--ismk-orange); font-weight:600; display:flex; align-items:center; gap:var(--space-1); }
        .btn-logout { background:var(--ismk-red); color:white; border:none; padding:var(--space-3) var(--space-5); border-radius:var(--radius-lg); font-weight:600; font-size:var(--text-base); display:flex; align-items:center; gap:var(--space-2); transition:all 0.2s; box-shadow:var(--shadow-sm); }
        .btn-logout:hover { background:#dc2626; transform:translateY(-1px); box-shadow:var(--shadow-md); }
        .page-container { padding:var(--space-8) var(--space-8) var(--space-12); max-width:900px; margin:0 auto; width:100%; }
        .system-message { padding:var(--space-5) var(--space-6); border-radius:var(--radius-xl); margin-bottom:var(--space-8); display:flex; align-items:flex-start; gap:var(--space-4); font-size:var(--text-lg); font-weight:500; animation:fadeIn 0.35s ease-out; border:1px solid transparent; }
        @keyframes fadeIn { from { opacity:0; transform:translateY(8px); } to { opacity:1; transform:translateY(0); } }
        .message-error { background:#fef2f2; color:#b91c1c; border-color:#fecaca; }
        .message-success { background:#ecfdf5; color:#065f46; border-color:#bbf7d0; }
        .message-icon { font-size:1.75rem; min-width:28px; margin-top:2px; }
        .form-section { background:white; border-radius:var(--radius-xl); box-shadow:var(--shadow-lg); padding:var(--space-8); margin-bottom:var(--space-8); }
        .section-header { display:flex; justify-content:space-between; align-items:center; margin-bottom:var(--space-6); padding-bottom:var(--space-4); border-bottom:2px solid var(--neutral-200); }
        .section-title { font-size:var(--text-2xl); font-weight:800; color:var(--neutral-900); display:flex; align-items:center; gap:var(--space-3); }
        .section-title i { color:var(--ismk-orange); font-size:1.875rem; }
        .form-grid { display:grid; grid-template-columns:repeat(auto-fit, minmax(300px, 1fr)); gap:var(--space-6); }
        .form-group { margin-bottom:var(--space-4); }
        .form-group label { display:block; margin-bottom:var(--space-2); font-size:var(--text-sm); font-weight:600; color:var(--neutral-700); }
        .form-control { width:100%; padding:var(--space-3) var(--space-4); border:2px solid var(--neutral-300); border-radius:var(--radius-md); font-size:var(--text-base); font-family:'Inter',sans-serif; transition:all 0.2s; }
        .form-control:focus { outline:none; border-color:var(--ismk-orange); box-shadow:0 0 0 3px rgba(245, 158, 11, 0.1); }
        .form-control[type="date"] { font-family:'Inter',sans-serif; }
        .btn { padding:var(--space-3) var(--space-5); border-radius:var(--radius-md); font-weight:600; cursor:pointer; transition:all 0.2s; border:none; display:inline-flex; align-items:center; gap:var(--space-2); font-family:'Inter',sans-serif; font-size:var(--text-base); }
        .btn-primary { background:var(--ismk-orange); color:white; }
        .btn-primary:hover { background:#d97706; transform:translateY(-2px); box-shadow:0 4px 12px rgba(245, 158, 11, 0.3); }
        .btn-secondary { background:white; color:var(--ismk-orange); border:2px solid var(--ismk-orange); }
        .btn-secondary:hover { background:var(--ismk-orange); color:white; }
        .info-box { background:#fffbeb; border-left:4px solid var(--ismk-orange); padding:var(--space-5); border-radius:var(--radius-md); margin-bottom:var(--space-6); }
        .info-box h4 { font-size:var(--text-lg); font-weight:700; color:var(--neutral-900); margin-bottom:var(--space-2); display:flex; align-items:center; gap:var(--space-2); }
        .info-box h4 i { color:var(--ismk-orange); }
        .info-box p { font-size:var(--text-base); color:var(--neutral-700); line-height:1.6; }
        .main-footer { text-align:center; padding:var(--space-10) var(--space-8) var(--space-6); color:var(--neutral-600); font-size:var(--text-base); border-top:1px solid var(--neutral-200); margin-top:var(--space-8); background:white; border-radius:var(--radius-xl); box-shadow:var(--shadow); }
        .footer-line { display:flex; align-items:center; justify-content:center; gap:var(--space-2); margin-bottom:var(--space-3); }
        .footer-highlight { color:var(--ismk-orange); font-weight:700; }
        @media (max-width:992px) { .dashboard-layout { grid-template-columns:1fr; } .sidebar { width:100%; height:auto; max-height:85vh; position:relative; } .main-content { grid-column:1; } .user-info { display:none; } }
        @media (max-width:768px) { .page-container { padding:var(--space-6) var(--space-4) var(--space-10); } .form-grid { grid-template-columns:1fr; } .btn { width:100%; justify-content:center; } }
    </style>
</head>
<body>
    <div class="dashboard-layout">
        <!-- Sidebar -->
        <aside class="sidebar">
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
                    <a href="<?= $baseUrl ?>/app/controllers/EmployeController.php?action=mesConges" class="sidebar-menu-item">
                        <i class="fas fa-umbrella-beach"></i>
                        <span>Mes Congés</span>
                    </a>
                    <a href="<?= $baseUrl ?>/app/controllers/EmployeController.php?action=demanderConge" class="sidebar-menu-item active">
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
                    <i class="fas fa-plus-circle"></i>
                    <h1>Demander un Congé</h1>
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
                                'champs_vides' => 'Veuillez remplir tous les champs obligatoires.',
                                'date_invalide' => 'La date de fin doit être postérieure à la date de début.',
                                'solde_insuffisant' => 'Solde de congés insuffisant pour cette demande.',
                                'erreur_creation' => 'Erreur lors de la création de la demande.'
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
                            <?= htmlspecialchars($success === 'conge_demande' ? 'Demande de congé soumise avec succès.' : 'Opération réussie.'); ?>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Informations sur le Solde -->
                <section class="info-box">
                    <h4><i class="fas fa-info-circle"></i> Votre Solde de Congés</h4>
                    <p>Vous disposez actuellement de <strong><?= $soldeConges ?> jours</strong> de congés restants pour cette année. Planifiez vos congés en conséquence.</p>
                </section>

                <!-- Formulaire de Demande -->
                <section class="form-section">
                    <div class="section-header">
                        <h2 class="section-title">
                            <i class="fas fa-edit"></i>
                            Formulaire de Demande
                        </h2>
                    </div>
                    
                    <form method="POST" action="<?= $baseUrl ?>/app/controllers/EmployeController.php?action=soumettreDemandeConge" class="form-grid">
                        <div class="form-group">
                            <label for="date_debut"><i class="fas fa-calendar-alt"></i> Date de Début *</label>
                            <input type="date" id="date_debut" name="date_debut" class="form-control" 
                                   value="<?= date('Y-m-d', strtotime('+7 days')) ?>" required
                                   min="<?= date('Y-m-d', strtotime('+7 days')) ?>">
                            <small style="color:var(--neutral-600); font-size:var(--text-sm); margin-top:var(--space-1); display:block;">
                                Au moins 7 jours avant la date de début
                            </small>
                        </div>
                        
                        <div class="form-group">
                            <label for="date_fin"><i class="fas fa-calendar-alt"></i> Date de Fin *</label>
                            <input type="date" id="date_fin" name="date_fin" class="form-control" 
                                   value="<?= date('Y-m-d', strtotime('+14 days')) ?>" required
                                   min="<?= date('Y-m-d', strtotime('+8 days')) ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="type_conge"><i class="fas fa-umbrella-beach"></i> Type de Congé *</label>
                            <select id="type_conge" name="type_conge" class="form-control" required>
                                <option value="annuel">Congé Annuel</option>
                                <option value="maladie">Congé Maladie</option>
                                <option value="maternite">Congé Maternité</option>
                                <option value="sans_solde">Congé Sans Solde</option>
                                <option value="exceptionnel">Congé Exceptionnel</option>
                            </select>
                        </div>
                        
                        <div class="form-group" style="grid-column: span 2;">
                            <label for="motif"><i class="fas fa-sticky-note"></i> Motif de la Demande *</label>
                            <textarea id="motif" name="motif" class="form-control" rows="4" 
                                      placeholder="Décrivez brièvement le motif de votre demande de congé..." required></textarea>
                            <small style="color:var(--neutral-600); font-size:var(--text-sm); margin-top:var(--space-1); display:block;">
                                Soyez précis dans votre explication pour faciliter le traitement de votre demande
                            </small>
                        </div>
                        
                        <div style="grid-column: span 2; display:flex; gap:var(--space-3); justify-content:flex-end; flex-wrap:wrap;">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-paper-plane"></i> Soumettre la Demande
                            </button>
                            <a href="<?= $baseUrl ?>/app/controllers/EmployeController.php?action=mesConges" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Annuler
                            </a>
                        </div>
                    </form>
                </section>

                <!-- Footer -->
                <footer class="main-footer">
                    <div class="footer-line">
                        <i class="fas fa-shield-alt"></i>
                        Application interne de gestion des salaires - 
                        <span class="footer-highlight">Institut Supérieur Mony Keng (ISMK)</span>
                        © <?= date('Y') ?>
                    </div>
                </footer>
            </div>
        </main>
    </div>

    <script>
        // Calcul automatique du nombre de jours
        document.addEventListener('DOMContentLoaded', function() {
            const dateDebut = document.getElementById('date_debut');
            const dateFin = document.getElementById('date_fin');
            
            function calculerJours() {
                if (dateDebut.value && dateFin.value) {
                    const debut = new Date(dateDebut.value);
                    const fin = new Date(dateFin.value);
                    
                    if (fin >= debut) {
                        const diffTime = Math.abs(fin - debut);
                        const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24)) + 1;
                        
                        console.log(`Nombre de jours: ${diffDays}`);
                    }
                }
            }
            
            dateDebut.addEventListener('change', calculerJours);
            dateFin.addEventListener('change', calculerJours);
        });
    </script>
</body>
</html>