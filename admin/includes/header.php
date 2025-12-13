<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

require_once __DIR__ . '/../../db.php';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? $pageTitle . ' - ' : ''; ?>Admin - Meubles de Maison</title>
    <link rel="stylesheet" href="../styles.css">
    <link rel="stylesheet" href="../css/pages/admin.css">
    <style>
        .admin-sidebar {
            background: var(--primary-color);
            color: white;
            padding: 2rem;
            min-height: 100vh;
            width: 250px;
            position: fixed;
            left: 0;
            top: 0;
            z-index: 1000;
        }
        
        /* Mobile menu toggle - caché par défaut sur desktop */
        .mobile-menu-toggle {
            display: none;
        }
        
        .sidebar-overlay {
            display: none;
        }
        
        /* S'assurer que la sidebar est visible sur desktop */
        @media (min-width: 769px) {
            .admin-sidebar {
                left: 0 !important;
            }
        }
        .admin-sidebar h2 {
            margin-bottom: 2rem;
            border-bottom: 2px solid rgba(255,255,255,0.2);
            padding-bottom: 1rem;
        }
        .admin-sidebar ul {
            list-style: none;
        }
        .admin-sidebar ul li {
            margin-bottom: 0.5rem;
        }
        .admin-sidebar a {
            color: rgba(255,255,255,0.8);
            display: block;
            padding: 0.75rem;
            border-radius: 5px;
            transition: all 0.3s ease;
        }
        .admin-sidebar a:hover,
        .admin-sidebar a.active {
            background: rgba(255,255,255,0.1);
            color: white;
        }
        .admin-main {
            margin-left: 250px;
            padding: 2rem;
        }
        .admin-logout {
            margin-top: 2rem;
            padding-top: 2rem;
            border-top: 1px solid rgba(255,255,255,0.2);
        }
        .admin-logout a {
            color: var(--accent-color);
        }
    </style>
</head>
<body>
    <!-- Hamburger menu button (mobile only) - lié à .admin-sidebar -->
    <button class="mobile-menu-toggle" onclick="toggleSidebar()" aria-label="Ouvrir le menu" type="button">
        <span class="hamburger-icon">☰</span>
    </button>
    
    <!-- Overlay pour mobile -->
    <div class="sidebar-overlay" onclick="toggleSidebar()"></div>
    
    <div class="admin-sidebar">
        <h2>🛠️ Administration</h2>
        <ul>
            <li><a href="dashboard.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : ''; ?>">📊 Tableau de bord</a></li>
            <li><a href="add.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'add.php' ? 'active' : ''; ?>">➕ Ajouter un produit</a></li>
            <li><a href="categories.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'categories.php' ? 'active' : ''; ?>">📁 Catégories</a></li>
            <li><a href="types_categories.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'types_categories.php' ? 'active' : ''; ?>">🏷️ Types de catégories</a></li>
            <li><a href="orders.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'orders.php' ? 'active' : ''; ?>">📦 Commandes</a></li>
            <li><a href="contacts.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'contacts.php' ? 'active' : ''; ?>">📧 Messages</a></li>
            <li><a href="../index.php" target="_blank">🌐 Voir le site</a></li>
        </ul>
        <div class="admin-logout">
            <a href="logout.php">🚪 Déconnexion</a>
        </div>
    </div>
    <div class="admin-main">
    
    <script>
    // Fonction pour toggle la sidebar - liée directement à .admin-sidebar
    function toggleSidebar() {
        // Sélectionner la sidebar par sa classe
        const sidebar = document.querySelector('.admin-sidebar');
        const overlay = document.querySelector('.sidebar-overlay');
        const toggleBtn = document.querySelector('.mobile-menu-toggle');
        
        // Toggle la classe 'active' sur la sidebar
        if (sidebar) {
            sidebar.classList.toggle('active');
        }
        
        // Toggle l'overlay
        if (overlay) {
            overlay.classList.toggle('active');
        }
        
        // Changer l'icône du bouton hamburger
        if (toggleBtn && sidebar) {
            const iconSpan = toggleBtn.querySelector('.hamburger-icon');
            if (sidebar.classList.contains('active')) {
                // Menu ouvert - afficher X
                if (iconSpan) iconSpan.textContent = '✕';
                toggleBtn.setAttribute('aria-label', 'Fermer le menu');
            } else {
                // Menu fermé - afficher hamburger
                if (iconSpan) iconSpan.textContent = '☰';
                toggleBtn.setAttribute('aria-label', 'Ouvrir le menu');
            }
        }
    }
    
    // Fermer le menu quand on clique sur un lien (mobile)
    document.addEventListener('DOMContentLoaded', function() {
        const sidebar = document.querySelector('.admin-sidebar');
        const overlay = document.querySelector('.sidebar-overlay');
        const toggleBtn = document.querySelector('.mobile-menu-toggle');
        const sidebarLinks = document.querySelectorAll('.admin-sidebar a');
        
        // Fonction pour fermer le menu
        function closeSidebar() {
            if (sidebar) sidebar.classList.remove('active');
            if (overlay) overlay.classList.remove('active');
            if (toggleBtn) {
                const iconSpan = toggleBtn.querySelector('.hamburger-icon');
                if (iconSpan) iconSpan.textContent = '☰';
                toggleBtn.setAttribute('aria-label', 'Ouvrir le menu');
            }
        }
        
        // Fermer le menu quand on clique sur un lien (mobile)
        sidebarLinks.forEach(link => {
            link.addEventListener('click', function() {
                if (window.innerWidth <= 768) {
                    closeSidebar();
                }
            });
        });
        
        // Fermer le menu quand on redimensionne la fenêtre
        window.addEventListener('resize', function() {
            if (window.innerWidth > 768) {
                closeSidebar();
            }
        });
        
        // Fermer le menu avec la touche Escape
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && sidebar && sidebar.classList.contains('active')) {
                closeSidebar();
            }
        });
    });
    </script>

