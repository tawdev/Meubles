<?php
$pageTitle = "Accueil";
require_once 'includes/header.php';

// Récupérer les catégories
try {
    $categoriesStmt = $pdo->query("SELECT * FROM categories ORDER BY name");
    $categoriesList = $categoriesStmt->fetchAll();

    // Harmoniser l'icône de la catégorie Bureau
    foreach ($categoriesList as &$cat) {
        if (isset($cat['name']) && $cat['name'] === 'Bureau') {
            $cat['icon'] = '🖥️';
        }
    }
    unset($cat);
} catch (PDOException $e) {
    // Si la table categories n'existe pas, utiliser les catégories par défaut
    $categoriesList = [
        ['name' => 'Salon', 'icon' => '🛋️'],
        ['name' => 'Chambre', 'icon' => '🛏️'],
        ['name' => 'Salle à manger', 'icon' => '🍽️'],
        ['name' => 'Bureau', 'icon' => '🖥️'],
        ['name' => 'Décoration', 'icon' => '🖼️']
    ];
}

// Récupérer les produits en vedette
$stmt = $pdo->query("SELECT * FROM products ORDER BY id DESC LIMIT 8");
$featuredProducts = $stmt->fetchAll();
?>

<section class="hero">
    <div class="hero-content">
        <h1 class="animated-title">
            <span class="title-word">frach</span>
            <span class="title-word">dark</span>
            
        </h1>
        <p class="animated-subtitle">Découvrez notre collection exclusive de meubles modernes et élégants pour transformer votre intérieur</p>
        <a href="products.php" class="btn animated-btn">Découvrir nos produits</a>
    </div>
</section>

<div class="container">
    <!-- Section Catégories -->
    <section id="categories" style="min-height: 95vh; display: flex; flex-direction: column; justify-content: center; padding: 4rem 0;">
        <div class="categories-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
            <h2 class="section-title" style="margin-bottom: 0; margin-left: 1rem;">Nos Catégories</h2>
            <a href="categories.php" class="btn view-all-categories-btn view-all-desktop" style="padding: 0.75rem 1.5rem;">Voir toutes les catégories →</a>
        </div>
        <div class="categories">
            <?php foreach (array_slice($categoriesList, 0, 5) as $category): ?>
                <a href="products.php?category=<?php echo urlencode($category['name']); ?>" class="category-card" style="text-decoration: none; color: inherit;">
                    <div style="font-size: 4rem; margin-bottom: 1rem;"><?php echo htmlspecialchars($category['icon'] ?? '📦'); ?></div>
                    <h3><?php echo htmlspecialchars($category['name']); ?></h3>
                </a>
            <?php endforeach; ?>
        </div>
        <div class="view-all-mobile" style="text-align: center; margin-top: 2rem; display: none;">
            <a href="categories.php" class="btn" style="padding: 0.75rem 1.5rem;">Voir toutes les catégories →</a>
        </div>
    </section>

    <!-- Section galerie -->
    <section id="gallery" style="padding: 5rem 0; margin: 4rem 0; background: linear-gradient(180deg, var(--bg-white) 0%, var(--bg-light) 100%);">
        <div class="container">
            <h2 class="section-title" style="text-align: center; margin-bottom: 3rem; color: var(--primary-color); font-size: 2.5rem;">
                Notre Galerie
            </h2>
            <div class="parent">
                <div class="div1 gallery-card">
                    <img src="images/gpt-image-1-mini_b_efacer_le_text_dans_.png" alt="Ambiance salon" onerror="this.src='images/placeholder.jpg'">
                </div>
                <div class="div2 gallery-card">
                    <img src="images/hunyuan-image-3.0_b_Crée_une_image_de_fo.png" alt="Coin lecture" onerror="this.src='images/placeholder.jpg'">
                </div>
                <div class="div3 gallery-card">
                    <img src="images/wan2.5-t2i-preview_b_Crée_une_image_de_fo.png" alt="Salle à manger" onerror="this.src='images/placeholder.jpg'">
                </div>
                <div class="div4 gallery-card">
                    <img src="images/a_Crée_une_image_de_fo.png" alt="Décor mural" onerror="this.src='images/placeholder.jpg'">
                </div>
                <div class="div5 gallery-card">
                    <img src="images/Gemini_Generated_Image_nhd19rnhd19rnhd1.png" alt="Bureau design" onerror="this.src='images/placeholder.jpg'">
                </div>
            </div>
        </div>
    </section>

    <!-- Section Pourquoi nous choisir -->
    <section id="why-us" style="margin-top: 4rem;">
        <h2 class="section-title">Pourquoi Nous Choisir ?</h2>
        <div class="categories">
            <div class="category-card">
                <div style="font-size: 3rem; margin-bottom: 1rem;">✨</div>
                <h3>Qualité Premium</h3>
                <p>Des matériaux de première qualité pour une durabilité exceptionnelle</p>
            </div>
            <div class="category-card">
                <div style="font-size: 3rem; margin-bottom: 1rem;">🚚</div>
                <h3>Livraison Rapide</h3>
                <p>Livraison gratuite à partir de 1000 DH d'achat</p>
            </div>
            <div class="category-card">
                <div style="font-size: 3rem; margin-bottom: 1rem;">💳</div>
                <h3>Paiement Sécurisé</h3>
                <p>Transactions sécurisées et garanties</p>
            </div>
            <div class="category-card">
                <div style="font-size: 3rem; margin-bottom: 1rem;">🔄</div>
                <h3>Retour Facile</h3>
                <p>30 jours pour changer d'avis</p>
            </div>
        </div>
    </section>
</div>

<style>
#gallery {
    padding: 5rem 0;
    background: linear-gradient(180deg, var(--bg-white) 0%, var(--bg-light) 100%);
}

.parent {
    display: grid;
    grid-template-columns: repeat(12, 1fr);
    grid-template-rows: repeat(4, 1fr);
    grid-column-gap: 0px;
    grid-row-gap: 0px;
    max-width: 1400px;
    margin: 0 auto;
}

.div1 { grid-area: 1 / 1 / 3 / 4; }
.div2 { grid-area: 1 / 10 / 3 / 13; }
.div3 { grid-area: 3 / 1 / 5 / 4; }
.div4 { grid-area: 3 / 10 / 5 / 13; }
.div5 { grid-area: 1 / 5 / 5 / 9; }

.gallery-card {
    position: relative;
    overflow: hidden;
    border-radius: 25px;
    box-shadow: 0 10px 30px rgba(107, 78, 61, 0.12);
    border: 2px solid var(--border-light);
    background: var(--bg-light);
    width: 100%;
    height: 100%;
}

.gallery-card img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
    border-radius: inherit;
    filter: brightness(1) contrast(1.05) saturate(1.08);
    transition: transform 0.4s ease, filter 0.4s ease, box-shadow 0.4s ease;
}

.gallery-card:hover img {
    transform: scale(1.04);
    filter: brightness(1.08) contrast(1.08) saturate(1.12);
}

.gallery-card::after {
    content: '';
    position: absolute;
    inset: 0;
    border-radius: inherit;
    background: linear-gradient(180deg, transparent 0%, rgba(0,0,0,0.08) 100%);
    opacity: 0;
    transition: opacity 0.3s ease;
}

.gallery-card:hover::after {
    opacity: 1;
}

@media (max-width: 1024px) {
    .parent {
        grid-template-columns: repeat(2, 1fr);
        grid-template-rows: repeat(4, minmax(200px, 1fr));
        grid-column-gap: 16px;
        grid-row-gap: 16px;
    }
    .div1, .div2, .div3, .div4, .div5 {
        grid-area: auto !important;
    }
    .div1 { grid-area: 1 / 1 / 3 / 2; }
    .div2 { grid-area: 1 / 2 / 3 / 3; }
    .div3 { grid-area: 3 / 1 / 5 / 2; }
    .div4 { grid-area: 3 / 2 / 5 / 3; }
    .div5 { grid-area: 1 / 1 / 5 / 3; }
}

@media (max-width: 768px) {
    #gallery {
        padding: 3rem 0 !important;
        margin: 2rem 0 !important;
        width: 100%;
        max-width: 100vw;
        box-sizing: border-box;
        overflow-x: hidden;
    }

    #gallery .container {
        padding: 0 0.75rem;
        max-width: 100%;
        box-sizing: border-box;
    }

    .parent {
        grid-template-columns: 1fr;
        grid-template-rows: repeat(5, minmax(250px, 1fr));
        grid-column-gap: 0px;
        grid-row-gap: 16px;
        width: 100%;
        max-width: 100%;
        box-sizing: border-box;
    }
    .div1, .div2, .div3, .div4, .div5 {
        grid-area: auto !important;
        grid-row: span 1;
        width: 100%;
        max-width: 100%;
        box-sizing: border-box;
    }
}

@media (max-width: 480px) {
    #gallery {
        padding: 2rem 0 !important;
        margin: 1.5rem 0 !important;
    }

    .parent {
        grid-row-gap: 12px;
        padding: 0;
    }
    .gallery-card {
        border-radius: 18px;
        width: 100%;
        max-width: 100%;
        box-sizing: border-box;
    }
    .gallery-card img {
        border-radius: 18px;
        width: 100%;
        max-width: 100%;
        box-sizing: border-box;
    }
}

/* ===== SECTION CATÉGORIES - 100VH ===== */
#categories {
    min-height: 100vh;
    display: flex;
    flex-direction: column;
    justify-content: center;
    padding: 4rem 0;
    background: var(--bg-light);
    margin: 0 -2rem;
    padding-left: 2rem;
    padding-right: 2rem;
    box-sizing: border-box;
    width: calc(100% + 4rem);
    max-width: 100vw;
}

@media (max-width: 768px) {
    #categories {
        min-height: auto;
        padding: 3rem 0;
        margin: 0;
        padding-left: 0.75rem;
        padding-right: 0.75rem;
        width: 100%;
    }
}

/* ===== OPTIMISATION DE L'IMAGE DE FOND ===== */
.hero {
    /* Amélioration de la qualité d'affichage pour HD/4K */
    image-rendering: -webkit-optimize-contrast;
    image-rendering: crisp-edges;
    -webkit-font-smoothing: antialiased;
    -moz-osx-font-smoothing: grayscale;
}

/* Amélioration des couleurs et du contraste via CSS */
.hero {
    filter: brightness(1.05) contrast(1.1) saturate(1.15);
    -webkit-filter: brightness(1.05) contrast(1.1) saturate(1.15);
}

/* Responsive pour différentes résolutions */
@media (min-width: 1920px) {
    .hero {
        background-size: cover;
        background-position: center center;
    }
}

@media (min-width: 2560px) {
    .hero {
        background-size: cover;
        background-position: center center;
    }
}

@media (min-width: 3840px) {
    .hero {
        background-size: cover;
        background-position: center center;
    }
}

/* ===== ANIMATIONS PROFESSIONNELLES POUR LE TITRE ===== */
.animated-title {
    font-size: 3.5rem !important;
    font-weight: 700 !important;
    margin-bottom: 1.5rem !important;
    display: flex !important;
    flex-wrap: wrap;
    justify-content: center;
    align-items: center;
    gap: 0.5rem;
    line-height: 1.2;
    position: relative;
    color: white !important;
    visibility: visible !important;
    opacity: 1 !important;
    animation: fadeInUp 1s ease-out 0.3s forwards;
}

.animated-title::after {
    content: '';
    position: absolute;
    bottom: -10px;
    left: 50%;
    transform: translateX(-50%);
    width: 0;
    height: 3px;
    background: linear-gradient(90deg, transparent, var(--secondary-color), transparent);
    animation: underlineExpand 1.5s ease-out 1.2s forwards;
    box-shadow: 0 0 20px rgba(184, 149, 106, 0.5);
}

.title-word {
    display: inline-block !important;
    opacity: 1 !important;
    transform: translateY(0) rotateX(0deg) !important;
    animation: wordReveal 0.8s ease-out forwards;
    position: relative;
    transition: all 0.3s ease;
    text-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    visibility: visible !important;
    color: inherit !important;
}

.title-word:nth-child(1) {
    animation-delay: 0.5s;
    color: white !important;
}

.title-word:nth-child(2) {
    animation-delay: 0.7s;
    color: var(--secondary-color) !important;
}

.title-word:nth-child(3) {
    animation-delay: 0.9s;
    color: white !important;
}

/* Effet hover sur les mots */
.animated-title:hover .title-word {
    transform: translateY(-5px);
    text-shadow: 0 5px 20px rgba(184, 149, 106, 0.4);
}

.animated-title:hover .title-word:nth-child(2) {
    color: var(--accent-color);
}

/* Animation du sous-titre */
.animated-subtitle {
    opacity: 0;
    transform: translateY(20px);
    animation: fadeInUp 1s ease-out 1.3s forwards;
    font-size: 1.2rem;
    line-height: 1.6;
    margin-bottom: 2rem;
    color: rgba(255, 255, 255, 0.95);
    text-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
}

/* Animation du bouton */
.animated-btn {
    opacity: 0;
    transform: translateY(20px) scale(0.9);
    animation: fadeInScale 0.8s ease-out 1.6s forwards;
    transition: all 0.3s ease;
}

.animated-btn:hover {
    transform: translateY(-3px) scale(1.05);
    box-shadow: 0 8px 25px rgba(107, 78, 61, 0.4);
}

/* Keyframes */
@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(40px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

@keyframes wordReveal {
    0% {
        opacity: 0;
        transform: translateY(30px) rotateX(90deg);
        filter: blur(5px);
    }
    50% {
        opacity: 0.7;
        transform: translateY(10px) rotateX(45deg);
        filter: blur(2px);
    }
    100% {
        opacity: 1 !important;
        transform: translateY(0) rotateX(0deg);
        filter: blur(0);
        visibility: visible !important;
    }
}

@keyframes underlineExpand {
    from {
        width: 0;
        opacity: 0;
    }
    to {
        width: 200px;
        opacity: 1;
    }
}

@keyframes fadeInScale {
    from {
        opacity: 0;
        transform: translateY(20px) scale(0.9);
    }
    to {
        opacity: 1;
        transform: translateY(0) scale(1);
    }
}

/* Effet glow doux pour le titre */
.animated-title .title-word {
    position: relative;
}

/* Animation glow pulse subtil */
@keyframes glowPulse {
    0%, 100% {
        opacity: 0.3;
        text-shadow: 0 2px 10px rgba(184, 149, 106, 0.3);
    }
    50% {
        opacity: 0.6;
        text-shadow: 0 5px 25px rgba(184, 149, 106, 0.6);
    }
}

.animated-title .title-word:nth-child(2) {
    animation: wordReveal 0.8s ease-out forwards, glowPulse 3s ease-in-out infinite;
    animation-delay: 0.7s, 2s;
}

/* Responsive */
@media (max-width: 768px) {
    .animated-title {
        font-size: 2.5rem;
        flex-direction: column;
        gap: 0.3rem;
    }
    
    .title-word {
        display: block;
    }
    
    .animated-subtitle {
        font-size: 1rem;
    }
    
    .animated-title::after {
        width: 150px;
    }
}

@media (max-width: 480px) {
    .animated-title {
        font-size: 2rem;
    }
}
</style>

<script>
// Animation supplémentaire avec JavaScript pour effet interactif
document.addEventListener('DOMContentLoaded', function() {
    const titleWords = document.querySelectorAll('.title-word');
    
    // Ajouter un effet de brillance subtil au survol
    titleWords.forEach((word, index) => {
        word.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-5px) scale(1.05)';
            this.style.textShadow = '0 5px 20px rgba(184, 149, 106, 0.5)';
            this.style.transition = 'all 0.3s cubic-bezier(0.4, 0, 0.2, 1)';
        });
        
        word.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0) scale(1)';
            this.style.textShadow = '0 2px 10px rgba(0, 0, 0, 0.1)';
        });
    });
    
    // Effet parallax subtil au scroll (optionnel, léger)
    let ticking = false;
    window.addEventListener('scroll', function() {
        if (!ticking) {
            window.requestAnimationFrame(function() {
                const currentScroll = window.pageYOffset;
                const hero = document.querySelector('.hero');
                
                if (hero && currentScroll < hero.offsetHeight && currentScroll > 0) {
                    titleWords.forEach((word, index) => {
                        const speed = (index + 1) * 0.05;
                        const offset = currentScroll * speed * 0.1;
                        word.style.transform = `translateY(${offset}px)`;
                    });
                } else if (currentScroll === 0) {
                    titleWords.forEach((word) => {
                        word.style.transform = 'translateY(0)';
                    });
                }
                ticking = false;
            });
            ticking = true;
        }
    });
});
</script>

<script>
function filterProductsByCategory(category) {
    document.getElementById('filter-category').value = category;
    filterProducts();
}
</script>

<?php require_once 'includes/footer.php'; ?>

