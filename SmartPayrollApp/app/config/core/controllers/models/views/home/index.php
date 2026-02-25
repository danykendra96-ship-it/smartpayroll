<?php
/**
 * ============================================================================
 * SMARTPAYROLL - Page d'Accueil Intranet
 * Institut Supérieur Mony Keng (ISMK)
 * ============================================================================
 * 
 * Application interne de gestion des salaires
 * Déployée exclusivement au sein de l'Institut Supérieur Mony Keng
 * 
 * @author [Ton nom] - Étudiante BTS Génie Logiciel
 * @establishment Institut Supérieur Mony Keng (ISMK)
 * @project Stage BTS - Digitalisation de la gestion des salaires
 * @version 1.0
 * @date Février 2026
 * 
 * @var string $baseUrl URL de base de l'application (passée par le routeur)
 * ============================================================================
 */
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="SmartPayroll - Application interne de gestion des salaires de l'Institut Supérieur Mony Keng">
    <meta name="robots" content="noindex, nofollow">
    <title>SmartPayroll | Institut Supérieur Mony Keng</title>
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Google Fonts - Professionnel -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        /* ============================================================================
           VARIABLES CSS - Palette Professionnelle Monochrome
           ============================================================================ */
        :root {
            /* Couleurs principales - sobre et élégant */
            --primary: #2563eb;
            --primary-dark: #1d4ed8;
            --primary-light: #dbeafe;
            
            /* Neutres - design épuré */
            --dark: #1e293b;
            --dark-light: #334155;
            --light: #f8fafc;
            --gray: #64748b;
            --gray-light: #e2e8f0;
            --border: #cbd5e1;
            
            /* Ombres subtiles */
            --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
            --shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06);
            --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
            
            /* Transitions fluides */
            --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            
            /* Bordures */
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

        html {
            scroll-behavior: smooth;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            line-height: 1.6;
            color: var(--dark);
            background-color: var(--light);
            overflow-x: hidden;
        }

        a {
            text-decoration: none;
            color: inherit;
            transition: var(--transition);
        }

        img {
            max-width: 100%;
            height: auto;
        }

        .container {
            width: 90%;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 12px 28px;
            border-radius: var(--radius);
            font-weight: 600;
            font-size: 16px;
            cursor: pointer;
            transition: var(--transition);
            border: none;
            font-family: 'Inter', sans-serif;
        }

        .btn-primary {
            background: var(--primary);
            color: white;
            box-shadow: var(--shadow);
        }

        .btn-primary:hover {
            background: var(--primary-dark);
            transform: translateY(-2px);
            box-shadow: var(--shadow-md);
        }

        .btn-outline {
            background: transparent;
            color: var(--primary);
            border: 2px solid var(--primary);
        }

        .btn-outline:hover {
            background: var(--primary);
            color: white;
        }

        .section {
            padding: 80px 0;
        }

        .section-title {
            text-align: center;
            margin-bottom: 50px;
        }

        .section-title h1,
        .section-title h2 {
            font-size: 32px;
            font-weight: 700;
            color: var(--dark);
            margin-bottom: 12px;
        }

        .section-title p {
            font-size: 17px;
            color: var(--gray);
            max-width: 600px;
            margin: 0 auto;
            line-height: 1.7;
        }

        /* ============================================================================
           HEADER - Minimaliste et Professionnel
           ============================================================================ */
        header {
            background: white;
            box-shadow: var(--shadow-sm);
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            z-index: 1000;
            transition: var(--transition);
        }

        header.scrolled {
            box-shadow: var(--shadow);
        }

        .navbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 16px 0;
        }

        .logo {
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .logo-badge {
            width: 48px;
            height: 48px;
            background: var(--primary);
            border-radius: var(--radius);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 700;
            font-size: 18px;
            flex-shrink: 0;
        }

        .logo-text {
            display: flex;
            flex-direction: column;
            gap: 2px;
        }

        .logo-text .establishment {
            font-size: 13px;
            color: var(--gray);
            font-weight: 500;
            letter-spacing: 0.3px;
        }

        .logo-text .app-name {
            font-size: 22px;
            font-weight: 700;
            color: var(--dark);
        }

        .nav-links {
            display: flex;
            gap: 32px;
            margin-left: 48px;
        }

        .nav-links a {
            font-weight: 500;
            color: var(--gray);
            font-size: 15px;
            position: relative;
            padding: 4px 0;
        }

        .nav-links a::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 0;
            height: 2px;
            background: var(--primary);
            transition: var(--transition);
        }

        .nav-links a:hover::after,
        .nav-links a.active::after {
            width: 100%;
        }

        .nav-links a:hover,
        .nav-links a.active {
            color: var(--primary);
        }

        .mobile-menu-btn {
            display: none;
            background: none;
            border: none;
            font-size: 24px;
            color: var(--primary);
            cursor: pointer;
        }

        /* ============================================================================
           HERO - Épuré et Direct
           ============================================================================ */
        .hero {
            background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
            padding: 140px 0 80px;
        }

        .hero-content {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 60px;
        }

        .hero-text {
            flex: 1;
            max-width: 580px;
        }

        .hero-text h1 {
            font-size: 40px;
            font-weight: 700;
            color: var(--dark);
            margin-bottom: 20px;
            line-height: 1.2;
        }

        .hero-text p {
            font-size: 18px;
            color: var(--gray);
            margin-bottom: 32px;
            line-height: 1.7;
        }

        .hero-buttons {
            display: flex;
            gap: 16px;
        }

        .hero-image {
            flex: 1;
            display: flex;
            justify-content: center;
            min-width: 320px;
        }

        .hero-image-inner {
            background: white;
            padding: 30px;
            border-radius: var(--radius-xl);
            box-shadow: var(--shadow-lg);
            max-width: 420px;
            border: 1px solid var(--border);
        }

        /* ============================================================================
           FEATURES - Cartes Épurées
           ============================================================================ */
        .features {
            background: white;
            padding: 70px 0;
        }

        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
            gap: 28px;
            margin-top: 40px;
        }

        .feature-card {
            background: white;
            padding: 32px 24px;
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-sm);
            text-align: center;
            transition: var(--transition);
            border: 1px solid var(--border);
            height: 100%;
        }

        .feature-card:hover {
            transform: translateY(-4px);
            box-shadow: var(--shadow);
            border-color: var(--primary-light);
        }

        .feature-icon {
            width: 60px;
            height: 60px;
            background: var(--primary-light);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            color: var(--primary);
            font-size: 24px;
        }

        .feature-card h3 {
            font-size: 19px;
            font-weight: 600;
            margin-bottom: 14px;
            color: var(--dark);
        }

        .feature-card p {
            color: var(--gray);
            font-size: 15px;
            line-height: 1.6;
        }

        /* ============================================================================
           ABOUT - Contexte Institutionnel
           ============================================================================ */
        .about {
            background: var(--light);
            padding: 80px 0;
        }

        .about-content {
            display: flex;
            align-items: center;
            gap: 60px;
        }

        .about-text {
            flex: 1;
        }

        .about-text h2 {
            font-size: 28px;
            font-weight: 700;
            color: var(--dark);
            margin-bottom: 20px;
        }

        .about-text p {
            font-size: 16px;
            color: var(--gray);
            margin-bottom: 16px;
            line-height: 1.7;
        }

        .about-highlight {
            background: var(--primary-light);
            padding: 3px 10px;
            border-radius: 4px;
            font-weight: 600;
            color: var(--primary-dark);
        }

        .about-list {
            margin: 24px 0;
        }

        .about-list li {
            margin-bottom: 12px;
            display: flex;
            align-items: flex-start;
            gap: 10px;
            color: var(--dark);
            font-size: 15px;
        }

        .about-list li i {
            color: var(--primary);
            font-size: 16px;
            margin-top: 3px;
            flex-shrink: 0;
        }

        .about-image {
            flex: 1;
            display: flex;
            justify-content: center;
            min-width: 280px;
        }

        .about-image-inner {
            background: white;
            padding: 20px;
            border-radius: var(--radius-xl);
            box-shadow: var(--shadow);
            max-width: 360px;
            border: 1px solid var(--border);
        }

        /* ============================================================================
           FOOTER - Identité Institutionnelle
           ============================================================================ */
        footer {
            background: var(--dark);
            color: white;
            padding: 50px 0 25px;
        }

        .footer-content {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 32px;
            margin-bottom: 30px;
        }

        .footer-column h3 {
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 20px;
            color: white;
        }

        .footer-links {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .footer-links a {
            color: #94a3b8;
            font-size: 15px;
            transition: var(--transition);
        }

        .footer-links a:hover {
            color: white;
            transform: translateX(4px);
        }

        .footer-contact p {
            color: #94a3b8;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 15px;
            line-height: 1.6;
        }

        .footer-contact i {
            color: var(--primary);
            font-size: 16px;
            flex-shrink: 0;
        }

        .footer-bottom {
            text-align: center;
            padding-top: 24px;
            border-top: 1px solid #334155;
            color: #64748b;
            font-size: 14px;
            line-height: 1.6;
        }

        /* ============================================================================
           RESPONSIVE DESIGN
           ============================================================================ */
        @media (max-width: 992px) {
            .hero-content,
            .about-content {
                flex-direction: column;
            }

            .nav-links {
                display: none;
            }

            .mobile-menu-btn {
                display: block;
            }
        }

        @media (max-width: 768px) {
            .hero {
                padding: 120px 0 60px;
            }

            .hero-text h1 {
                font-size: 32px;
            }

            .hero-text p {
                font-size: 16px;
            }

            .section {
                padding: 60px 0;
            }

            .section-title h1,
            .section-title h2 {
                font-size: 26px;
            }

            .hero-buttons {
                flex-direction: column;
                align-items: center;
            }

            .hero-buttons .btn {
                width: 100%;
                max-width: 260px;
            }

            .about-text h2 {
                font-size: 24px;
            }
        }

        @media (max-width: 480px) {
            .logo-text .app-name {
                font-size: 20px;
            }

            .logo-text .establishment {
                font-size: 12px;
            }

            .hero-text h1 {
                font-size: 28px;
            }

            .feature-card {
                padding: 28px 20px;
            }

            .feature-card h3 {
                font-size: 17px;
            }
        }

        /* ============================================================================
           UTILITIES
           ============================================================================ */
        .text-center {
            text-align: center;
        }

        .text-primary {
            color: var(--primary);
        }

        .mb-20 {
            margin-bottom: 20px;
        }

        .mb-30 {
            margin-bottom: 30px;
        }

        .mt-30 {
            margin-top: 30px;
        }

        /* ============================================================================
           ALERTS
           ============================================================================ */
        .alert {
            padding: 14px 20px;
            border-radius: var(--radius);
            margin-bottom: 20px;
            display: inline-block;
            font-weight: 500;
            font-size: 15px;
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
    </style>
</head>
<body>

    <!-- ============================================================================
         HEADER
         ============================================================================ -->
    <header id="header">
        <div class="container">
            <nav class="navbar">
                <div class="logo">
                    <div class="logo-badge" aria-label="Logo ISMK">ISMK</div>
                    <div class="logo-text">
                        <div class="establishment">Institut Supérieur Mony Keng</div>
                        <div class="app-name">SmartPayroll</div>
                    </div>
                </div>

                <button class="mobile-menu-btn" id="mobileMenuBtn" aria-label="Menu">
                    <i class="fas fa-bars"></i>
                </button>

                <div class="nav-links" id="navLinks">
                    <a href="#home" class="active">Accueil</a>
                    <a href="#features">Fonctionnalités</a>
                    <a href="#about">À propos</a>
                    <a href="#contact">Contact</a>
                </div>

                <a href="<?= $baseUrl ?>/public/login.php" class="btn btn-primary">
                    <i class="fas fa-sign-in-alt"></i> Se Connecter
                </a>
            </nav>
        </div>
    </header>

    <!-- ============================================================================
         HERO SECTION
         ============================================================================ -->
    <section class="hero" id="home">
        <div class="container">
            <div class="hero-content">
                <div class="hero-text">
                    <h1>Gestion des Salaires <span class="about-highlight">Intelligente</span></h1>
                    <p>SmartPayroll est l'application interne de gestion des salaires déployée au sein de l'<strong>Institut Supérieur Mony Keng</strong>. Accédez à vos bulletins, consultez vos informations et gérez vos congés en toute sécurité.</p>
                    
                    <div class="hero-buttons">
                        <a href="<?= $baseUrl ?>/public/login.php" class="btn btn-primary">
                            <i class="fas fa-door-open"></i> Accéder à mon espace
                        </a>
                        <a href="#features" class="btn btn-outline">
                            <i class="fas fa-info-circle"></i> En savoir plus
                        </a>
                    </div>
                </div>
                <div class="hero-image">
                    <div class="hero-image-inner">
                        <img src="https://img.freepik.com/free-vector/payroll-concept-illustration_114360-6262.jpg" alt="Gestion des salaires" width="380">
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================================
         FEATURES SECTION
         ============================================================================ -->
    <section class="features" id="features">
        <div class="container">
            <div class="section-title">
                <h2>Fonctionnalités</h2>
                <p>Solutions dédiées à la gestion des salaires du personnel ISMK</p>
            </div>

            <div class="features-grid">
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-file-invoice"></i>
                    </div>
                    <h3>Bulletins de Paie</h3>
                    <p>Consultation sécurisée de vos bulletins mensuels avec détail complet des calculs.</p>
                </div>

                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-calculator"></i>
                    </div>
                    <h3>Calculs Automatisés</h3>
                    <p>Calculs précis conformes à la réglementation malgache : CNSS, IRSA, heures supplémentaires.</p>
                </div>

                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-user-shield"></i>
                    </div>
                    <h3>Confidentialité</h3>
                    <p>Accès strictement personnel : chaque agent ne visualise que ses propres données.</p>
                </div>

                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-umbrella-beach"></i>
                    </div>
                    <h3>Gestion des Congés</h3>
                    <p>Demande en ligne de congés et suivi de votre solde restant.</p>
                </div>

                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <h3>Tableaux de Bord</h3>
                    <p>Espaces personnalisés selon votre rôle avec statistiques pertinentes.</p>
                </div>

                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-file-pdf"></i>
                    </div>
                    <h3>Documents Officiels</h3>
                    <p>Génération et téléchargement de bulletins au format PDF conforme.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================================
         ABOUT SECTION
         ============================================================================ -->
    <section class="about" id="about">
        <div class="container">
            <div class="about-content">
                <div class="about-text">
                    <h2>SmartPayroll - ISMK</h2>
                    <p><strong>SmartPayroll</strong> est une application web interne développée spécifiquement pour l'<strong>Institut Supérieur Mony Keng</strong> dans le cadre de la digitalisation de sa gestion administrative du personnel.</p>
                    
                    <p>Cette application a été conçue et développée par une étudiante en <span class="about-highlight">BTS Génie Logiciel</span> dans le cadre de son stage de fin d'études au sein de l'ISMK.</p>
                    
                    <p><strong>Objectifs principaux :</strong></p>
                    
                    <ul class="about-list">
                        <li><i class="fas fa-check"></i> Automatiser les calculs salariaux selon la réglementation malgache</li>
                        <li><i class="fas fa-check"></i> Centraliser la gestion des employés (CDI, CDD, vacataires)</li>
                        <li><i class="fas fa-check"></i> Garantir la confidentialité des données personnelles</li>
                        <li><i class="fas fa-check"></i> Faciliter le travail du service comptabilité et RH</li>
                    </ul>
                    
                    <div class="mt-30">
                        <a href="<?= $baseUrl ?>/public/login.php" class="btn btn-primary">
                            <i class="fas fa-sign-in-alt"></i> Accéder à l'application
                        </a>
                    </div>
                </div>
                <div class="about-image">
                    <div class="about-image-inner">
                        <img src="https://img.freepik.com/free-vector/online-learning-concept-illustration_114360-8374.jpg" alt="Institut Supérieur Mony Keng" width="340">
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================================
         FOOTER
         ============================================================================ -->
    <footer>
        <div class="container">
            <div class="footer-content">
                <div class="footer-column">
                    <h3>Institut Supérieur Mony Keng</h3>
                    <div class="footer-contact">
                        <p><i class="fas fa-map-marker-alt"></i> Lot IV G 45 Bis, Ivandry</p>
                        <p>Antananarivo, Madagascar</p>
                        <p><i class="fas fa-phone"></i> +261 20 22 456 78</p>
                        <p><i class="fas fa-envelope"></i> contact@ismk.mg</p>
                    </div>
                </div>

                <div class="footer-column">
                    <h3>Application</h3>
                    <div class="footer-links">
                        <a href="#home"><i class="fas fa-chevron-right"></i> Accueil</a>
                        <a href="#features"><i class="fas fa-chevron-right"></i> Fonctionnalités</a>
                        <a href="#about"><i class="fas fa-chevron-right"></i> À propos</a>
                        <a href="<?= $baseUrl ?>/public/login.php"><i class="fas fa-chevron-right"></i> Se Connecter</a>
                    </div>
                </div>

                <div class="footer-column">
                    <h3>Support</h3>
                    <div class="footer-links">
                        <a href="mailto:support.informatique@ismk.mg">
                            <i class="fas fa-chevron-right"></i> Support Informatique
                        </a>
                        <a href="mailto:drh@ismk.mg">
                            <i class="fas fa-chevron-right"></i> Direction RH
                        </a>
                        <a href="mailto:stage@ismk.mg">
                            <i class="fas fa-chevron-right"></i> Encadrement Stage
                        </a>
                    </div>
                </div>
            </div>

            <div class="footer-bottom">
                <p>
                    © 2026 Institut Supérieur Mony Keng (ISMK) | 
                    Application SmartPayroll - Gestion des Salaires | 
                    Développée dans le cadre d'un stage BTS Génie Logiciel
                </p>
                <p style="margin-top: 8px;">
                    <i class="fas fa-lock"></i> Accès strictement réservé au personnel autorisé de l'ISMK
                </p>
            </div>
        </div>
    </footer>

    <!-- ============================================================================
         JAVASCRIPT
         ============================================================================ -->
    <script>
        // Smooth scroll
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    window.scrollTo({
                        top: target.offsetTop - 80,
                        behavior: 'smooth'
                    });
                    const navLinks = document.getElementById('navLinks');
                    if (navLinks && navLinks.classList.contains('active')) {
                        navLinks.classList.remove('active');
                    }
                }
            });
        });

        // Sticky header
        const header = document.getElementById('header');
        window.addEventListener('scroll', () => {
            header.classList.toggle('scrolled', window.scrollY > 50);
        });

        // Menu mobile
        const mobileMenuBtn = document.getElementById('mobileMenuBtn');
        const navLinks = document.getElementById('navLinks');
        if (mobileMenuBtn && navLinks) {
            mobileMenuBtn.addEventListener('click', () => {
                navLinks.classList.toggle('active');
            });
        }

        // Active link
        const sections = document.querySelectorAll('section');
        const navItems = document.querySelectorAll('.nav-links a');
        window.addEventListener('scroll', () => {
            let current = '';
            sections.forEach(section => {
                const sectionTop = section.offsetTop - 150;
                if (pageYOffset >= sectionTop) {
                    current = section.getAttribute('id');
                }
            });
            navItems.forEach(item => {
                item.classList.remove('active');
                if (item.getAttribute('href').slice(1) === current) {
                    item.classList.add('active');
                }
            });
        });

        // Alert messages
        window.addEventListener('DOMContentLoaded', () => {
            const urlParams = new URLSearchParams(window.location.search);
            const error = urlParams.get('error');
            const success = urlParams.get('success');
            
            if (error || success) {
                const alertDiv = document.createElement('div');
                alertDiv.className = error ? 'alert alert-error' : 'alert alert-success';
                alertDiv.style.position = 'fixed';
                alertDiv.style.top = '20px';
                alertDiv.style.right = '20px';
                alertDiv.style.zIndex = '9999';
                alertDiv.style.maxWidth = '380px';
                alertDiv.style.boxShadow = '0 4px 6px rgba(0,0,0,0.1)';
                
                if (error) {
                    switch(error) {
                        case 'non_connecte':
                            alertDiv.innerHTML = '<i class="fas fa-exclamation-triangle"></i> Veuillez vous connecter.';
                            break;
                        case 'credentials_invalides':
                            alertDiv.innerHTML = '<i class="fas fa-times-circle"></i> Identifiants incorrects.';
                            break;
                        case 'session_expiree':
                            alertDiv.innerHTML = '<i class="fas fa-clock"></i> Session expirée.';
                            break;
                        case 'acces_interdit':
                            alertDiv.innerHTML = '<i class="fas fa-ban"></i> Accès refusé.';
                            break;
                        default:
                            alertDiv.innerHTML = '<i class="fas fa-exclamation-triangle"></i> Erreur. Contactez le support.';
                    }
                } else if (success) {
                    alertDiv.innerHTML = '<i class="fas fa-check-circle"></i> ' + 
                        (success === 'deconnecte' ? 'Déconnexion réussie.' : 'Opération réussie.');
                }
                
                document.body.appendChild(alertDiv);
                setTimeout(() => {
                    alertDiv.style.opacity = '0';
                    alertDiv.style.transform = 'translateY(-20px)';
                    setTimeout(() => alertDiv.remove(), 300);
                }, 5000);
            }
        });
    </script>

</body>
</html>