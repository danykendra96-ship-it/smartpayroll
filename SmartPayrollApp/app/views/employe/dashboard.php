<?php
/**
 * ============================================================================
 * SMARTPAYROLL - Dashboard Employé Ultime
 * ============================================================================
 * 
 * Espace personnel sécurisé de l'employé avec :
 * - Confidentialité absolue (SEULEMENT ses données)
 * - Statistiques personnelles claires
 * - Accès rapide à ses bulletins
 * - Gestion de ses congés
 * 
 * @author [Ton nom] - Étudiante BTS Génie Logiciel
 * @establishment Institut Supérieur Mony Keng (ISMK)
 * @project Stage BTS - Digitalisation de la gestion des salaires
 * @version 3.0 - ULTIME
 * @date Février 2026
 * ============================================================================
 */

require_once __DIR__ . '/../../core/Session.php';
\App\Core\Session::start();
\App\Core\Session::requireRole('employe');

// Définir BASE_URL
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'];
$scriptName = $_SERVER['SCRIPT_NAME'];
$projectPath = preg_replace('#^(/[^/]+).*#', '$1', $scriptName);
$baseUrl = $protocol . '://' . $host . $projectPath;

// Messages système
$error = $_GET['error'] ?? null;
$success = $_GET['success'] ?? null;
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Mon Espace Personnel - SmartPayroll ISMK">
    <title>Mon Espace | SmartPayroll - ISMK</title>
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Google Fonts - Inter (Lisibilité Maximale) -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <style>
        /* ============================================================================
           SYSTÈME DE DESIGN - Fondations Professionnelles
           ============================================================================ */
        :root {
            /* Couleurs Institutionnelles ISMK */
            --ismk-blue: #1e3a8a;      /* Bleu foncé institutionnel */
            --ismk-blue-light: #2563eb; /* Bleu principal */
            --ismk-blue-xlight: #dbeafe; /* Bleu très clair */
            --ismk-green: #047857;     /* Vert éducatif */
            --ismk-green-light: #dcfce7; /* Vert clair */
            --ismk-orange: #f59e0b;    /* Orange action */
            --ismk-red: #ef4444;       /* Rouge alerte */
            
            /* Neutres Professionnels */
            --neutral-900: #0f172a;    /* Noir profond */
            --neutral-800: #1e293b;    /* Gris très foncé */
            --neutral-700: #334155;    /* Gris foncé */
            --neutral-600: #475569;    /* Gris moyen-foncé */
            --neutral-500: #64748b;    /* Gris moyen */
            --neutral-400: #94a3b8;    /* Gris clair */
            --neutral-300: #cbd5e1;    /* Gris très clair */
            --neutral-200: #e2e8f0;    /* Gris extrêmement clair */
            --neutral-100: #f1f5f9;    /* Fond très clair */
            --neutral-50: #f8fafc;     /* Blanc cassé */
            --white: #ffffff;          /* Blanc pur */
            
            /* Ombres Subtiles */
            --shadow-xs: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
            --shadow-sm: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06);
            --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
            --shadow-xl: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
            
            /* Transitions Fluides */
            --transition-fast: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
            --transition-normal: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            --transition-slow: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            
            /* Rayons (Consistance Visuelle) */
            --radius-xs: 4px;
            --radius-sm: 6px;
            --radius-md: 8px;
            --radius-lg: 12px;
            --radius-xl: 16px;
            --radius-2xl: 24px;
            --radius-full: 9999px;
            
            /* Espacement Systématique (8px baseline) */
            --space-1: 0.25rem;   /* 4px */
            --space-2: 0.5rem;    /* 8px */
            --space-3: 0.75rem;   /* 12px */
            --space-4: 1rem;      /* 16px */
            --space-5: 1.25rem;   /* 20px */
            --space-6: 1.5rem;    /* 24px */
            --space-8: 2rem;      /* 32px */
            --space-10: 2.5rem;   /* 40px */
            --space-12: 3rem;     /* 48px */
            
            /* Typographie Hiérarchique */
            --text-xs: 0.75rem;   /* 12px */
            --text-sm: 0.875rem;  /* 14px */
            --text-base: 1rem;    /* 16px */
            --text-lg: 1.125rem;  /* 18px */
            --text-xl: 1.25rem;   /* 20px */
            --text-2xl: 1.5rem;   /* 24px */
            --text-3xl: 1.875rem; /* 30px */
            --text-4xl: 2.25rem;  /* 36px */
            --text-5xl: 3rem;     /* 48px */
        }

        /* ============================================================================
           RESET & BASE
           ============================================================================ */
        *,
        *::before,
        *::after {
            box-sizing: border-box;
        }

        html {
            scroll-behavior: smooth;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            line-height: 1.5;
            color: var(--neutral-800);
            background-color: var(--neutral-50);
            min-height: 100vh;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }

        /* ============================================================================
           LAYOUT - Architecture Visuelle Claire
           ============================================================================ */
        .dashboard-layout {
            display: grid;
            grid-template-columns: 280px 1fr;
            min-height: 100vh;
        }

        /* Sidebar - Navigation Personnelle */
        .sidebar {
            background: linear-gradient(180deg, var(--ismk-blue) 0%, var(--neutral-900) 100%);
            color: var(--white);
            position: fixed;
            height: 100vh;
            width: 280px;
            overflow-y: auto;
            transition: var(--transition-normal);
            z-index: 100;
            border-right: 1px solid rgba(255, 255, 255, 0.1);
        }

        .sidebar-header {
            padding: var(--space-6) var(--space-5) var(--space-5);
            text-align: center;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            position: relative;
        }

        .sidebar-logo {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: var(--space-2);
            margin-bottom: var(--space-5);
        }

        .sidebar-logo-badge {
            width: 64px;
            height: 64px;
            background: var(--white);
            color: var(--ismk-blue);
            border-radius: var(--radius-xl);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 800;
            font-size: 28px;
            box-shadow: var(--shadow-lg);
        }

        .sidebar-logo-text {
            text-align: center;
        }

        .sidebar-logo-text .establishment {
            font-size: var(--text-sm);
            color: var(--ismk-blue-xlight);
            font-weight: 500;
            letter-spacing: 0.5px;
        }

        .sidebar-logo-text .app-name {
            font-size: var(--text-2xl);
            font-weight: 800;
            background: linear-gradient(90deg, var(--white), var(--ismk-blue-xlight));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            letter-spacing: -0.5px;
        }

        .sidebar-user {
            padding: var(--space-5) var(--space-5) var(--space-6);
            background: rgba(30, 41, 59, 0.4);
            margin-top: var(--space-4);
            border-radius: var(--radius-xl);
            border: 1px solid rgba(255, 255, 255, 0.08);
        }

        .sidebar-user-info {
            display: flex;
            align-items: center;
            gap: var(--space-4);
        }

        .sidebar-user-avatar {
            width: 56px;
            height: 56px;
            background: var(--ismk-blue-xlight);
            color: var(--ismk-blue);
            border-radius: var(--radius-full);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 22px;
            flex-shrink: 0;
            border: 3px solid var(--white);
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.15);
        }

        .sidebar-user-details h4 {
            font-size: var(--text-lg);
            font-weight: 700;
            margin-bottom: var(--space-1);
            color: var(--white);
        }

        .sidebar-user-details .role-badge {
            display: inline-flex;
            align-items: center;
            gap: var(--space-1);
            background: rgba(255, 255, 255, 0.2);
            color: var(--ismk-blue-xlight);
            padding: var(--space-1) var(--space-2);
            border-radius: var(--radius-full);
            font-size: var(--text-xs);
            font-weight: 600;
            letter-spacing: 0.5px;
        }

        .sidebar-menu {
            padding: var(--space-6) var(--space-0) var(--space-8);
        }

        .menu-section {
            margin-bottom: var(--space-6);
        }

        .menu-section-title {
            padding: 0 var(--space-6) var(--space-3);
            font-size: var(--text-xs);
            text-transform: uppercase;
            letter-spacing: 1.5px;
            color: rgba(255, 255, 255, 0.5);
            font-weight: 700;
            margin-top: var(--space-2);
        }

        .sidebar-menu-item {
            padding: var(--space-3) var(--space-6);
            display: flex;
            align-items: center;
            gap: var(--space-4);
            color: rgba(255, 255, 255, 0.85);
            transition: var(--transition-fast);
            font-weight: 500;
            font-size: var(--text-base);
            position: relative;
            border-radius: var(--radius-md);
            margin: 0 var(--space-2);
        }

        .sidebar-menu-item:hover {
            background: rgba(255, 255, 255, 0.08);
            color: var(--white);
        }

        .sidebar-menu-item.active {
            background: rgba(37, 99, 235, 0.25);
            color: var(--white);
            font-weight: 600;
        }

        .sidebar-menu-item.active::before {
            content: '';
            position: absolute;
            left: 0;
            top: 50%;
            transform: translateY(-50%);
            width: 4px;
            height: 70%;
            background: var(--ismk-blue-light);
            border-radius: var(--radius-full);
        }

        .sidebar-menu-item i {
            width: 24px;
            font-size: 20px;
            min-width: 24px;
            text-align: center;
        }

        .sidebar-menu-item.active i {
            color: var(--ismk-blue-light);
        }

        /* Main Content */
        .main-content {
            grid-column: 2;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        /* Header */
        .main-header {
            background: var(--white);
            box-shadow: var(--shadow-sm);
            padding: var(--space-4) var(--space-8);
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: sticky;
            top: 0;
            z-index: 90;
        }

        .header-title {
            display: flex;
            align-items: center;
            gap: var(--space-4);
        }

        .header-title h1 {
            font-size: var(--text-3xl);
            font-weight: 800;
            color: var(--neutral-900);
            display: flex;
            align-items: center;
            gap: var(--space-3);
        }

        .header-title h1 i {
            color: var(--ismk-green);
            font-size: 2.25rem;
        }

        .header-actions {
            display: flex;
            align-items: center;
            gap: var(--space-5);
        }

        .user-menu {
            display: flex;
            align-items: center;
            gap: var(--space-3);
            padding: var(--space-2) var(--space-4);
            border-radius: var(--radius-lg);
            transition: var(--transition-fast);
            cursor: pointer;
        }

        .user-menu:hover {
            background: var(--ismk-blue-xlight);
        }

        .user-avatar {
            width: 48px;
            height: 48px;
            background: var(--ismk-blue-xlight);
            color: var(--ismk-blue);
            border-radius: var(--radius-full);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 20px;
            flex-shrink: 0;
            border: 2px solid var(--white);
            box-shadow: var(--shadow-sm);
        }

        .user-info {
            display: flex;
            flex-direction: column;
            min-width: 130px;
        }

        .user-name {
            font-size: var(--text-lg);
            font-weight: 700;
            color: var(--neutral-900);
            line-height: 1.3;
        }

        .user-role {
            font-size: var(--text-sm);
            color: var(--ismk-green);
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: var(--space-1);
        }

        .btn-logout {
            background: var(--ismk-red);
            color: var(--white);
            border: none;
            padding: var(--space-3) var(--space-5);
            border-radius: var(--radius-lg);
            font-weight: 600;
            font-size: var(--text-base);
            display: flex;
            align-items: center;
            gap: var(--space-2);
            transition: var(--transition-fast);
            box-shadow: var(--shadow-sm);
        }

        .btn-logout:hover {
            background: #dc2626;
            transform: translateY(-1px);
            box-shadow: var(--shadow-md);
        }

        /* Page Container */
        .page-container {
            padding: var(--space-8) var(--space-8) var(--space-12);
            max-width: 1500px;
            margin: 0 auto;
            width: 100%;
        }

        /* System Messages */
        .system-message {
            padding: var(--space-5) var(--space-6);
            border-radius: var(--radius-xl);
            margin-bottom: var(--space-8);
            display: flex;
            align-items: flex-start;
            gap: var(--space-4);
            font-size: var(--text-lg);
            font-weight: 500;
            animation: fadeIn 0.35s ease-out;
            border: 1px solid transparent;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(8px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .message-error {
            background: #fef2f2;
            color: #b91c1c;
            border-color: #fecaca;
        }

        .message-success {
            background: #ecfdf5;
            color: #065f46;
            border-color: #bbf7d0;
        }

        .message-icon {
            font-size: 1.75rem;
            min-width: 28px;
            margin-top: 2px;
        }

        /* Profile Card - Carte de Profil Personnelle */
        .profile-card {
            background: linear-gradient(135deg, var(--white), var(--ismk-blue-xlight));
            border-radius: var(--radius-2xl);
            padding: var(--space-8);
            text-align: center;
            margin-bottom: var(--space-10);
            box-shadow: var(--shadow-xl);
            position: relative;
            overflow: hidden;
        }

        .profile-card::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -20%;
            width: 100%;
            height: 200%;
            background: radial-gradient(circle, rgba(37, 99, 235, 0.1) 0%, rgba(37, 99, 235, 0) 70%);
            transform: rotate(15deg);
        }

        .profile-avatar {
            width: 120px;
            height: 120px;
            background: var(--ismk-blue-xlight);
            color: var(--ismk-blue);
            border-radius: var(--radius-full);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 800;
            font-size: 48px;
            margin: 0 auto var(--space-6);
            border: 6px solid var(--white);
            box-shadow: var(--shadow-xl);
        }

        .profile-name {
            font-size: var(--text-4xl);
            font-weight: 800;
            color: var(--neutral-900);
            margin-bottom: var(--space-2);
            position: relative;
            z-index: 2;
        }

        .profile-matricule {
            font-size: var(--text-xl);
            color: var(--ismk-blue);
            font-weight: 700;
            letter-spacing: 1px;
            margin-bottom: var(--space-4);
            position: relative;
            z-index: 2;
        }

        .profile-details {
            display: flex;
            justify-content: center;
            gap: var(--space-8);
            margin-top: var(--space-6);
            flex-wrap: wrap;
            position: relative;
            z-index: 2;
        }

        .profile-detail-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            min-width: 140px;
        }

        .profile-detail-value {
            font-size: var(--text-3xl);
            font-weight: 800;
            color: var(--ismk-blue);
        }

        .profile-detail-label {
            font-size: var(--text-sm);
            color: var(--neutral-600);
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        /* Stats Grid */
        .stats-section {
            margin-bottom: var(--space-10);
        }

        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            margin-bottom: var(--space-6);
            padding-bottom: var(--space-4);
            border-bottom: 2px solid var(--neutral-200);
        }

        .section-title {
            font-size: var(--text-3xl);
            font-weight: 800;
            color: var(--neutral-900);
            display: flex;
            align-items: center;
            gap: var(--space-3);
        }

        .section-title i {
            color: var(--ismk-green);
            font-size: 2rem;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: var(--space-6);
        }

        .stat-card {
            background: var(--white);
            border-radius: var(--radius-2xl);
            padding: var(--space-6);
            box-shadow: var(--shadow-lg);
            transition: var(--transition-normal);
            position: relative;
            overflow: hidden;
            border: 1px solid var(--neutral-200);
        }

        .stat-card:hover {
            transform: translateY(-4px);
            box-shadow: var(--shadow-xl);
            border-color: var(--neutral-300);
        }

        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 6px;
            height: 100%;
            background: var(--ismk-green);
        }

        .stat-card.stat-orange::before { background: var(--ismk-orange); }
        .stat-card.stat-blue::before { background: var(--ismk-blue-light); }
        .stat-card.stat-purple::before { background: #8b5cf6; }

        .stat-content {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            position: relative;
            z-index: 2;
        }

        .stat-text {
            flex: 1;
        }

        .stat-label {
            font-size: var(--text-sm);
            color: var(--neutral-600);
            margin-bottom: var(--space-2);
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .stat-value {
            font-size: var(--text-4xl);
            font-weight: 800;
            color: var(--neutral-900);
            line-height: 1.2;
        }

        .stat-value.currency {
            font-size: var(--text-3xl);
        }

        .stat-icon-container {
            width: 72px;
            height: 72px;
            border-radius: var(--radius-xl);
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            margin-left: var(--space-4);
            background: var(--ismk-green-light);
            color: var(--ismk-green);
        }

        .stat-icon {
            font-size: 2.25rem;
            width: 100%;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .stat-icon.orange {
            color: var(--ismk-orange);
        }

        .stat-icon.blue {
            color: var(--ismk-blue-light);
        }

        .stat-icon.purple {
            color: #8b5cf6;
        }

        /* Main Grid */
        .main-grid {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: var(--space-8);
            margin-bottom: var(--space-10);
        }

        .main-card {
            background: var(--white);
            border-radius: var(--radius-2xl);
            box-shadow: var(--shadow-lg);
            padding: var(--space-8);
            transition: var(--transition-normal);
        }

        .main-card:hover {
            box-shadow: var(--shadow-xl);
        }

        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: var(--space-6);
            padding-bottom: var(--space-5);
            border-bottom: 1px solid var(--neutral-200);
        }

        .card-title {
            font-size: var(--text-2xl);
            font-weight: 800;
            color: var(--neutral-900);
            display: flex;
            align-items: center;
            gap: var(--space-3);
        }

        .card-title i {
            color: var(--ismk-green);
            font-size: 1.875rem;
        }

        .view-all-link {
            color: var(--ismk-green);
            font-size: var(--text-base);
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: var(--space-1);
            transition: var(--transition-fast);
            text-decoration: none;
        }

        .view-all-link:hover {
            color: #065f46;
            transform: translateX(4px);
        }

        .view-all-link i {
            font-size: 1.125rem;
            transition: var(--transition-fast);
        }

        .view-all-link:hover i {
            transform: translateX(4px);
        }

        /* Mes Bulletins - Tableau Personnalisé */
        .table-container {
            overflow-x: auto;
            border-radius: var(--radius-xl);
            border: 1px solid var(--neutral-200);
            background: var(--white);
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
        }

        .data-table thead {
            background: var(--neutral-50);
        }

        .data-table th {
            padding: var(--space-4) var(--space-5);
            text-align: left;
            font-weight: 700;
            color: var(--neutral-700);
            font-size: var(--text-sm);
            text-transform: uppercase;
            letter-spacing: 0.8px;
            border-bottom: 2px solid var(--neutral-300);
        }

        .data-table td {
            padding: var(--space-4) var(--space-5);
            border-bottom: 1px solid var(--neutral-200);
            font-size: var(--text-base);
            vertical-align: middle;
            color: var(--neutral-800);
        }

        .data-table tbody tr:last-child td {
            border-bottom: none;
        }

        .data-table tbody tr:hover {
            background: var(--ismk-green-light);
            background-color: rgba(16, 185, 129, 0.04);
        }

        .badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: var(--space-1) var(--space-3);
            border-radius: var(--radius-full);
            font-size: var(--text-sm);
            font-weight: 600;
            text-align: center;
            min-width: 90px;
            letter-spacing: 0.3px;
        }

        .badge-paye {
            background: #dbeafe;
            color: var(--ismk-blue);
            border: 1px solid #93c5fd;
        }

        .action-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: var(--space-2) var(--space-4);
            border-radius: var(--radius-lg);
            font-size: var(--text-sm);
            font-weight: 600;
            cursor: pointer;
            transition: var(--transition-fast);
            border: none;
            text-decoration: none;
            gap: var(--space-2);
            min-width: 100px;
        }

        .action-btn i {
            font-size: 1.125rem;
            min-width: 18px;
            text-align: center;
        }

        .action-btn.pdf {
            background: #f5f3ff;
            color: #7e22ce;
            border: 1px solid #d8b4fe;
        }

        .action-btn.pdf:hover {
            background: #7e22ce;
            color: var(--white);
            border-color: #7e22ce;
        }

        .action-btn.view {
            background: var(--ismk-green-light);
            color: var(--ismk-green);
            border: 1px solid #bbf7d0;
        }

        .action-btn.view:hover {
            background: var(--ismk-green);
            color: var(--white);
            border-color: var(--ismk-green);
        }

        /* Mes Congés */
        .conges-list {
            display: flex;
            flex-direction: column;
            gap: var(--space-4);
        }

        .conge-item {
            display: flex;
            align-items: center;
            gap: var(--space-4);
            padding: var(--space-4);
            border-radius: var(--radius-lg);
            background: var(--neutral-50);
            border-left: 4px solid var(--ismk-orange);
        }

        .conge-item.approuve {
            border-left-color: var(--ismk-green);
            background: var(--ismk-green-light);
        }

        .conge-icon {
            width: 48px;
            height: 48px;
            border-radius: var(--radius-xl);
            display: flex;
            align-items: center;
            justify-content: center;
            background: var(--ismk-orange);
            color: var(--white);
            font-size: 1.5rem;
        }

        .conge-item.approuve .conge-icon {
            background: var(--ismk-green);
        }

        .conge-content h4 {
            font-size: var(--text-lg);
            font-weight: 700;
            color: var(--neutral-900);
            margin-bottom: var(--space-1);
        }

        .conge-content p {
            font-size: var(--text-sm);
            color: var(--neutral-600);
        }

        .conge-badge {
            display: inline-block;
            padding: var(--space-1) var(--space-2);
            border-radius: var(--radius-full);
            font-size: var(--text-xs);
            font-weight: 600;
            letter-spacing: 0.5px;
        }

        .conge-badge.approuve {
            background: #dcfce7;
            color: var(--ismk-green);
        }

        .conge-badge.en-attente {
            background: #fef3c7;
            color: var(--ismk-orange);
        }

        /* Quick Links - Liens Personnels */
        .quick-links-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: var(--space-5);
        }

        .quick-link-card {
            background: var(--white);
            border-radius: var(--radius-xl);
            padding: var(--space-6) var(--space-4);
            text-align: center;
            transition: var(--transition-normal);
            border: 2px solid var(--neutral-200);
            cursor: pointer;
            box-shadow: var(--shadow-md);
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: var(--space-4);
        }

        .quick-link-card:hover {
            transform: translateY(-4px);
            border-color: var(--ismk-green);
            box-shadow: 0 10px 25px -5px rgba(16, 185, 129, 0.25);
        }

        .quick-link-icon {
            width: 64px;
            height: 64px;
            border-radius: var(--radius-2xl);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2.25rem;
            color: var(--ismk-green);
            background: var(--ismk-green-light);
            transition: var(--transition-normal);
            flex-shrink: 0;
            box-shadow: var(--shadow-sm);
        }

        .quick-link-card:hover .quick-link-icon {
            transform: scale(1.15) rotate(3deg);
            background: var(--ismk-green);
            color: var(--white);
        }

        .quick-link-card.documents .quick-link-icon {
            background: #dbeafe;
            color: var(--ismk-blue);
        }

        .quick-link-card.documents:hover .quick-link-icon {
            background: var(--ismk-blue);
            color: var(--white);
        }

        .quick-link-card.settings .quick-link-icon {
            background: #f5f3ff;
            color: #7e22ce;
        }

        .quick-link-card.settings:hover .quick-link-icon {
            background: #7e22ce;
            color: var(--white);
        }

        .quick-link-title {
            font-size: var(--text-xl);
            font-weight: 700;
            color: var(--neutral-900);
            margin: 0;
            line-height: 1.3;
        }

        .quick-link-description {
            font-size: var(--text-sm);
            color: var(--neutral-600);
            line-height: 1.6;
            margin: 0;
        }

        /* Footer */
        .main-footer {
            text-align: center;
            padding: var(--space-10) var(--space-8) var(--space-6);
            color: var(--neutral-600);
            font-size: var(--text-base);
            border-top: 1px solid var(--neutral-200);
            margin-top: var(--space-8);
            background: var(--white);
            border-radius: var(--radius-2xl);
            box-shadow: var(--shadow);
        }

        .footer-line {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: var(--space-2);
            margin-bottom: var(--space-3);
        }

        .footer-highlight {
            color: var(--ismk-green);
            font-weight: 700;
        }

        .footer-subline {
            font-size: var(--text-sm);
            color: var(--neutral-500);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: var(--space-2);
            margin-top: var(--space-2);
        }

        /* Responsive */
        @media (max-width: 1200px) {
            .main-grid {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 992px) {
            .dashboard-layout {
                grid-template-columns: 1fr;
            }
            
            .sidebar {
                width: 100%;
                height: auto;
                max-height: 85vh;
                position: relative;
            }
            
            .main-content {
                grid-column: 1;
            }
            
            .user-info {
                display: none;
            }
        }

        @media (max-width: 768px) {
            .page-container {
                padding: var(--space-6) var(--space-4) var(--space-10);
            }
            
            .profile-card {
                padding: var(--space-6);
            }
            
            .profile-avatar {
                width: 100px;
                height: 100px;
                font-size: 40px;
            }
            
            .profile-name {
                font-size: var(--text-3xl);
            }
            
            .profile-matricule {
                font-size: var(--text-lg);
            }
            
            .profile-details {
                flex-direction: column;
                gap: var(--space-4);
            }
            
            .stats-grid {
                grid-template-columns: 1fr;
            }
            
            .stat-value {
                font-size: var(--text-3xl);
            }
            
            .quick-links-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 480px) {
            .sidebar-logo-badge {
                width: 56px;
                height: 56px;
                font-size: 24px;
            }
            
            .sidebar-user-avatar {
                width: 48px;
                height: 48px;
                font-size: 20px;
            }
            
            .profile-avatar {
                width: 80px;
                height: 80px;
                font-size: 32px;
            }
            
            .profile-name {
                font-size: var(--text-2xl);
            }
            
            .quick-links-grid {
                grid-template-columns: 1fr;
            }
            
            .quick-link-icon {
                width: 56px;
                height: 56px;
                font-size: 2rem;
            }
            
            .btn-logout {
                width: 100%;
                justify-content: center;
            }
        }
    </style>
</head>
<body>
    <div class="dashboard-layout">
        <!-- Sidebar - Navigation Personnelle -->
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
                    <div class="sidebar-menu-item active">
                        <i class="fas fa-home"></i>
                        <span>Dashboard</span>
                    </div>
                    <div class="sidebar-menu-item">
                        <i class="fas fa-file-invoice"></i>
                        <span>Mes Bulletins</span>
                    </div>
                    <div class="sidebar-menu-item">
                        <i class="fas fa-user"></i>
                        <span>Mon Profil</span>
                    </div>
                </div>
                
                <div class="menu-section">
                    <div class="menu-section-title">CONGÉS</div>
                    <div class="sidebar-menu-item">
                        <i class="fas fa-umbrella-beach"></i>
                        <span>Mes Congés</span>
                    </div>
                    <div class="sidebar-menu-item">
                        <i class="fas fa-plus-circle"></i>
                        <span>Demande de Congé</span>
                    </div>
                </div>
                
                <div class="menu-section">
                    <div class="menu-section-title">DOCUMENTS</div>
                    <div class="sidebar-menu-item">
                        <i class="fas fa-download"></i>
                        <span>Mes Documents</span>
                    </div>
                </div>
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <!-- Header -->
            <header class="main-header">
                <div class="header-title">
                    <i class="fas fa-user-shield"></i>
                    <h1>Mon Espace Personnel</h1>
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
                                'acces_interdit' => 'Accès refusé. Vous ne pouvez consulter que vos propres données.',
                                'bulletin_invalide' => 'Bulletin invalide.',
                                'session_expiree' => 'Votre session a expiré. Veuillez vous reconnecter.'
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

                <!-- Profile Card - Carte de Profil Personnelle -->
                <section class="profile-card">
                    <div class="profile-avatar">
                        <?= strtoupper(substr($_SESSION['user_prenom'] ?? 'E', 0, 1)) ?>
                    </div>
                    <h1 class="profile-name"><?= htmlspecialchars($_SESSION['user_prenom'] ?? 'Employé') . ' ' . htmlspecialchars($_SESSION['user_nom'] ?? '') ?></h1>
                    <div class="profile-matricule"><?= htmlspecialchars($_SESSION['user_matricule'] ?? 'EMP001') ?></div>
                    
                    <div class="profile-details">
                        <div class="profile-detail-item">
                            <div class="profile-detail-value"><?= htmlspecialchars($_SESSION['user_poste'] ?? 'Poste non défini') ?></div>
                            <div class="profile-detail-label">Poste</div>
                        </div>
                        <div class="profile-detail-item">
                            <div class="profile-detail-value"><?= htmlspecialchars($_SESSION['user_departement'] ?? 'Département non défini') ?></div>
                            <div class="profile-detail-label">Département</div>
                        </div>
                        <div class="profile-detail-item">
                            <div class="profile-detail-value"><?= $_SESSION['user_solde_conges'] ?? 15 ?> j</div>
                            <div class="profile-detail-label">Solde Congés</div>
                        </div>
                    </div>
                </section>

                <!-- Stats Section -->
                <section class="stats-section">
                    <div class="section-header">
                        <h2 class="section-title">
                            <i class="fas fa-chart-pie"></i>
                            Mes Statistiques Personnelles
                        </h2>
                    </div>
                    
                    <div class="stats-grid">
                        <div class="stat-card">
                            <div class="stat-content">
                                <div class="stat-text">
                                    <div class="stat-label">Dernier Salaire Net</div>
                                    <div class="stat-value currency">485 000 Ar</div>
                                    <div class="stat-trend positive">
                                        <i class="fas fa-arrow-up"></i>
                                        <span>+2% vs mois dernier</span>
                                    </div>
                                </div>
                                <div class="stat-icon-container">
                                    <i class="fas fa-money-bill-wave stat-icon"></i>
                                </div>
                            </div>
                        </div>

                        <div class="stat-card stat-orange">
                            <div class="stat-content">
                                <div class="stat-text">
                                    <div class="stat-label">Solde de Congés</div>
                                    <div class="stat-value">15 jours</div>
                                    <div class="stat-trend">
                                        <i class="fas fa-umbrella-beach"></i>
                                        <span>Disponibles cette année</span>
                                    </div>
                                </div>
                                <div class="stat-icon-container" style="background: #fffbeb; color: var(--ismk-orange);">
                                    <i class="fas fa-calendar-alt stat-icon orange"></i>
                                </div>
                            </div>
                        </div>

                        <div class="stat-card stat-blue">
                            <div class="stat-content">
                                <div class="stat-text">
                                    <div class="stat-label">Bulletins Consultés</div>
                                    <div class="stat-value">12</div>
                                    <div class="stat-trend">
                                        <i class="fas fa-file-invoice"></i>
                                        <span>Ce dernier trimestre</span>
                                    </div>
                                </div>
                                <div class="stat-icon-container" style="background: var(--ismk-blue-xlight); color: var(--ismk-blue-light);">
                                    <i class="fas fa-list stat-icon blue"></i>
                                </div>
                            </div>
                        </div>

                        <div class="stat-card stat-purple">
                            <div class="stat-content">
                                <div class="stat-text">
                                    <div class="stat-label">Ancienneté</div>
                                    <div class="stat-value">2 ans</div>
                                    <div class="stat-trend">
                                        <i class="fas fa-award"></i>
                                        <span>Depuis janvier 2022</span>
                                    </div>
                                </div>
                                <div class="stat-icon-container" style="background: #f5f3ff; color: #8b5cf6;">
                                    <i class="fas fa-clock stat-icon purple"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- Main Grid -->
                <div class="main-grid">
                    <!-- Mes Bulletins -->
                    <div class="main-card">
                        <div class="card-header">
                            <h2 class="card-title">
                                <i class="fas fa-file-invoice"></i>
                                Mes Bulletins de Paie
                            </h2>
                            <a href="<?= $baseUrl ?>/app/controllers/EmployeController.php?action=mesBulletins" class="view-all-link">
                                <span>Voir tous mes bulletins</span>
                                <i class="fas fa-arrow-right"></i>
                            </a>
                        </div>
                        
                        <?php if (!empty($mesBulletins)): ?>
                            <div class="table-container">
                                <table class="data-table">
                                    <thead>
                                        <tr>
                                            <th>Période</th>
                                            <th>Salaire de Base</th>
                                            <th>Primes</th>
                                            <th>Retenues</th>
                                            <th>Salaire Net</th>
                                            <th>Statut</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($mesBulletins as $bulletin): ?>
                                        <tr>
                                            <td><?= $bulletin['mois'] ?>/<?= $bulletin['annee'] ?></td>
                                            <td><?= number_format($bulletin['salaire_base'], 0, ' ', ' ') ?> Ar</td>
                                            <td><?= number_format($bulletin['primes_total'], 0, ' ', ' ') ?> Ar</td>
                                            <td><?= number_format($bulletin['retenues_total'], 0, ' ', ' ') ?> Ar</td>
                                            <td><strong><?= number_format($bulletin['salaire_net'], 0, ' ', ' ') ?> Ar</strong></td>
                                            <td><span class="badge badge-paye">Payé</span></td>
                                            <td class="action-group">
                                                <a href="<?= $baseUrl ?>/app/controllers/EmployeController.php?action=voirBulletin&id=<?= $bulletin['id_bulletin'] ?>" 
                                                   class="action-btn view">
                                                    <i class="fas fa-eye"></i> Voir
                                                </a>
                                                <a href="<?= $baseUrl ?>/app/controllers/EmployeController.php?action=telechargerPDF&id=<?= $bulletin['id_bulletin'] ?>" 
                                                   class="action-btn pdf" target="_blank">
                                                    <i class="fas fa-file-pdf"></i> PDF
                                                </a>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div style="text-align: center; padding: var(--space-8); color: var(--neutral-500);">
                                <i class="fas fa-file-invoice" style="font-size: 4rem; color: var(--ismk-green-light); margin-bottom: var(--space-4);"></i>
                                <h3 style="font-size: var(--text-2xl); color: var(--neutral-800); margin-bottom: var(--space-3);">Aucun bulletin disponible</h3>
                                <p style="max-width: 500px; margin: 0 auto; color: var(--neutral-600); line-height: 1.7;">
                                    Vos bulletins de paie seront disponibles dès que le service comptabilité les aura générés et validés.
                                </p>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Mes Congés + Liens Rapides -->
                    <div style="display: flex; flex-direction: column; gap: var(--space-8);">
                        <!-- Mes Congés -->
                        <div class="main-card">
                            <div class="card-header">
                                <h2 class="card-title">
                                    <i class="fas fa-umbrella-beach"></i>
                                    Mes Congés Récents
                                </h2>
                            </div>
                            
                            <?php if (!empty($mesConges)): ?>
                                <div class="conges-list">
                                    <?php foreach (array_slice($mesConges, 0, 4) as $conge): ?>
                                    <div class="conge-item <?= $conge['statut'] === 'approuve' ? 'approuve' : '' ?>">
                                        <div class="conge-icon">
                                            <i class="fas fa-umbrella-beach"></i>
                                        </div>
                                        <div class="conge-content">
                                            <h4>Du <?= date('d/m/Y', strtotime($conge['date_debut'])) ?> au <?= date('d/m/Y', strtotime($conge['date_fin'])) ?></h4>
                                            <p><?= $conge['nb_jours'] ?> jours - <?= htmlspecialchars($conge['type_conge']) ?></p>
                                        </div>
                                        <div class="conge-badge <?= $conge['statut'] === 'approuve' ? 'approuve' : 'en-attente' ?>">
                                            <?= ucfirst($conge['statut']) ?>
                                        </div>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php else: ?>
                                <div style="text-align: center; padding: var(--space-6); color: var(--neutral-500);">
                                    <i class="fas fa-umbrella-beach" style="font-size: 3rem; color: var(--ismk-orange); opacity: 0.7; margin-bottom: var(--space-4);"></i>
                                    <p>Aucun congé récent</p>
                                </div>
                            <?php endif; ?>
                            
                            <div style="text-align: center; margin-top: var(--space-6);">
                                <a href="#" class="view-all-link" style="color: var(--ismk-orange);">
                                    <i class="fas fa-plus-circle"></i> Demander un congé
                                </a>
                            </div>
                        </div>

                        <!-- Liens Rapides -->
                        <div class="main-card">
                            <div class="card-header">
                                <h2 class="card-title">
                                    <i class="fas fa-link"></i>
                                    Accès Rapides
                                </h2>
                            </div>
                            
                            <div class="quick-links-grid">
                                <a href="<?= $baseUrl ?>/app/controllers/EmployeController.php?action=monProfil" class="quick-link-card">
                                    <div class="quick-link-icon">
                                        <i class="fas fa-user"></i>
                                    </div>
                                    <h3 class="quick-link-title">Mon Profil</h3>
                                    <p class="quick-link-description">Mes informations personnelles</p>
                                </a>
                                
                                <a href="#" class="quick-link-card documents">
                                    <div class="quick-link-icon">
                                        <i class="fas fa-download"></i>
                                    </div>
                                    <h3 class="quick-link-title">Documents</h3>
                                    <p class="quick-link-description">Mes documents personnels</p>
                                </a>
                                
                                <a href="#" class="quick-link-card settings">
                                    <div class="quick-link-icon">
                                        <i class="fas fa-cog"></i>
                                    </div>
                                    <h3 class="quick-link-title">Paramètres</h3>
                                    <p class="quick-link-description">Sécurité & confidentialité</p>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

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

    <script>
        // Micro-interactions pour une expérience fluide
        document.addEventListener('DOMContentLoaded', function() {
            // Animation d'entrée progressive des cartes
            const statCards = document.querySelectorAll('.stat-card');
            statCards.forEach((card, index) => {
                card.style.opacity = '0';
                card.style.transform = 'translateY(20px)';
                setTimeout(() => {
                    card.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
                    card.style.opacity = '1';
                    card.style.transform = 'translateY(0)';
                }, 400 + (index * 150));
            });
            
            // Animation du profil
            const profileCard = document.querySelector('.profile-card');
            if (profileCard) {
                profileCard.style.opacity = '0';
                profileCard.style.transform = 'scale(0.95)';
                setTimeout(() => {
                    profileCard.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
                    profileCard.style.opacity = '1';
                    profileCard.style.transform = 'scale(1)';
                }, 200);
            }
        });
    </script>
</body>
</html>