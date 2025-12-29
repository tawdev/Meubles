<?php
// Configuration SEO pour la page À propos
$siteUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://" . $_SERVER['HTTP_HOST'];
$currentUrl = $siteUrl . $_SERVER['REQUEST_URI'];
$pageTitle = "À Propos de Frachdark - Notre Histoire";
$pageMetaDescription = "Découvrez l'histoire de Frachdark, votre partenaire de confiance au Maroc pour transformer votre intérieur avec des meubles de qualité premium. Mission, vision, valeurs et engagement envers l'excellence. Plus de 10 ans d'expérience dans le mobilier moderne.";
$pageKeywords = "à propos frachdark, histoire frachdark maroc, meubles qualité maroc, mobilier premium maroc, mission frachdark, vision frachdark, valeurs frachdark, service client maroc";
$pageImage = $siteUrl . '/images/logo.jpg';

require_once 'includes/header.php';
?>

<!-- Structured Data pour Organisation -->
<script type="application/ld+json">
{
    "@context": "https://schema.org",
    "@type": "AboutPage",
    "name": "À Propos de Frachdark",
    "url": "<?php echo htmlspecialchars($currentUrl); ?>",
    "mainEntity": {
        "@type": "Organization",
        "name": "Frachdark - Meubles de Maison",
        "alternateName": "Frachdark Maroc",
        "description": "Boutique en ligne de meubles modernes et élégants au Maroc. Plus de 10 ans d'expérience dans le mobilier intérieur.",
        "url": "<?php echo $siteUrl; ?>",
        "logo": "<?php echo $siteUrl; ?>/images/logo.jpg",
        "address": {
            "@type": "PostalAddress",
            "addressCountry": "MA"
        },
        "foundingDate": "2014",
        "numberOfEmployees": {
            "@type": "QuantitativeValue",
            "value": "10-50"
        },
        "areaServed": {
            "@type": "Country",
            "name": "Morocco"
        }
    },
    "breadcrumb": {
        "@type": "BreadcrumbList",
        "itemListElement": [
            {
                "@type": "ListItem",
                "position": 1,
                "name": "Accueil",
                "item": "<?php echo $siteUrl; ?>/index.php"
            },
            {
                "@type": "ListItem",
                "position": 2,
                "name": "À Propos",
                "item": "<?php echo htmlspecialchars($currentUrl); ?>"
            }
        ]
    }
}
</script>


<div class="container">
    <!-- Bouton de retour -->
    <div class="about-back-btn">
        <a href="<?php echo isset($_SERVER['HTTP_REFERER']) ? htmlspecialchars($_SERVER['HTTP_REFERER']) : 'index.php'; ?>" 
           class="btn btn-back">
            ← Retour
        </a>
    </div>

    <!-- Section Notre Histoire -->
    <section class="about-section about-hero-section">
        <div class="about-hero-content">
            <h1 class="section-title">Notre Histoire</h1>
            <div class="about-hero-text">
                <p class="about-hero-lead">
                    Meubles de Maison est né d'une passion pour l'aménagement intérieur et le design. 
                    Depuis notre création, nous nous engageons à offrir des meubles de qualité qui 
                    allient esthétique moderne et fonctionnalité.
                </p>
                <p class="about-hero-description">
                    Nous croyons que chaque maison mérite d'être un foyer chaleureux et accueillant. 
                    C'est pourquoi nous sélectionnons avec soin chaque meuble de notre collection, 
                    en privilégiant la qualité, le design et l'accessibilité.
                </p>
            </div>
        </div>
    </section>

    <!-- Section Mission, Vision, Valeurs -->
    <section class="about-section about-values-section">
        <h2 class="section-title">Qui Sommes-Nous ?</h2>
        <div class="values-grid">
            <div class="value-card">
                <div class="value-icon">🎯</div>
                <h3>Notre Mission</h3>
                <p>
                    Transformer chaque maison en un foyer chaleureux et élégant grâce à nos meubles 
                    soigneusement sélectionnés, en offrant qualité et style à des prix accessibles.
                </p>
            </div>
            <div class="value-card">
                <div class="value-icon">👁️</div>
                <h3>Notre Vision</h3>
                <p>
                    Devenir la référence en matière de meubles modernes et accessibles pour tous les budgets, 
                    tout en maintenant notre engagement envers la qualité et le service client exceptionnel.
                </p>
            </div>
            <div class="value-card">
                <div class="value-icon">💎</div>
                <h3>Nos Valeurs</h3>
                <p>
                    Qualité, durabilité, service client et satisfaction garantie sont au cœur de tout 
                    ce que nous faisons. Votre bonheur est notre priorité.
                </p>
            </div>
        </div>
    </section>

    <!-- Section Pourquoi Nous Choisir -->
    <section class="about-section about-features-section">
        <h2 class="section-title">Pourquoi Nous Choisir ?</h2>
        <div class="features-grid">
            <div class="feature-card">
                <div class="feature-icon">✨</div>
                <h3>Qualité Premium</h3>
                <p>
                    Tous nos meubles sont sélectionnés pour leur qualité exceptionnelle et leur durabilité.
                </p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">💰</div>
                <h3>Prix Compétitifs</h3>
                <p>
                    Meilleur rapport qualité/prix du marché sans compromis sur la qualité.
                </p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">🚚</div>
                <h3>Livraison Rapide</h3>
                <p>
                    Expédition sous 48h pour la plupart des produits. Livraison gratuite à partir de 1000 DH.
                </p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">👥</div>
                <h3>Service Client</h3>
                <p>
                    Équipe dédiée à votre service 7j/7 pour répondre à toutes vos questions.
                </p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">🛡️</div>
                <h3>Garantie</h3>
                <p>
                    Tous nos produits sont garantis minimum 2 ans pour votre tranquillité d'esprit.
                </p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">🔄</div>
                <h3>Retour Facile</h3>
                <p>
                    30 jours pour changer d'avis. Politique de retour simple et transparente.
                </p>
            </div>
        </div>
    </section>

    <!-- Section Statistiques -->
    <section class="stats-section">
        <h2 class="stats-title">Nos Chiffres</h2>
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-number">500+</div>
                <div class="stat-label">Clients satisfaits</div>
            </div>
            <div class="stat-card">
                <div class="stat-number">1000+</div>
                <div class="stat-label">Produits disponibles</div>
            </div>
            <div class="stat-card">
                <div class="stat-number">10+</div>
                <div class="stat-label">Années d'expérience</div>
            </div>
            <div class="stat-card">
                <div class="stat-number">98%</div>
                <div class="stat-label">Satisfaction client</div>
            </div>
        </div>
    </section>

</div>

<?php require_once 'includes/footer.php'; ?>
