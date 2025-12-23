<?php
// Configuration SEO pour la page À propos
$siteUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://" . $_SERVER['HTTP_HOST'];
$pageTitle = "À propos";
$pageMetaDescription = "Découvrez l'histoire de Frachdark, votre partenaire de confiance pour transformer votre intérieur avec des meubles de qualité premium. Mission, vision, valeurs et engagement envers l'excellence.";
$pageKeywords = "à propos, histoire, frachdark, meubles qualité, mobilier premium, mission, vision, valeurs, service client";
$pageImage = $siteUrl . '/images/logo.jpg';

require_once 'includes/header.php';
?>

<!-- Structured Data pour Organisation -->
<script type="application/ld+json">
{
    "@context": "https://schema.org",
    "@type": "AboutPage",
    "mainEntity": {
        "@type": "Organization",
        "name": "Frachdark - Meubles de Maison",
        "description": "Boutique en ligne de meubles modernes et élégants",
        "url": "<?php echo $siteUrl; ?>",
        "logo": "<?php echo $siteUrl; ?>/images/logo.jpg"
    }
}
</script>


<div class="container">
    <!-- Bouton de retour -->
    <div style="margin-top: 1.5rem; margin-bottom: 1rem;">
        <a href="<?php echo isset($_SERVER['HTTP_REFERER']) ? htmlspecialchars($_SERVER['HTTP_REFERER']) : 'index.php'; ?>" 
           class="btn" 
           style="display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.5rem 1rem; text-decoration: none;">
            ← Retour
        </a>
    </div>

    <!-- Section Notre Histoire -->
    <section style="max-width: 900px; margin: 0 auto 4rem;">
        <div style="text-align: center; margin-bottom: 3rem;">
            <h2 class="section-title">Notre Histoire</h2>
            <div style="max-width: 700px; margin: 0 auto;">
                <p style="font-size: 1.2rem; line-height: 1.9; color: var(--text-light); margin-bottom: 2rem;">
                    Meubles de Maison est né d'une passion pour l'aménagement intérieur et le design. 
                    Depuis notre création, nous nous engageons à offrir des meubles de qualité qui 
                    allient esthétique moderne et fonctionnalité.
                </p>
                <p style="font-size: 1.1rem; line-height: 1.8; color: var(--text-light);">
                    Nous croyons que chaque maison mérite d'être un foyer chaleureux et accueillant. 
                    C'est pourquoi nous sélectionnons avec soin chaque meuble de notre collection, 
                    en privilégiant la qualité, le design et l'accessibilité.
                </p>
            </div>
        </div>
    </section>

    <!-- Section Mission, Vision, Valeurs -->
    <section style="margin-bottom: 4rem;">
        <h2 class="section-title">Qui Sommes-Nous ?</h2>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 2rem; margin-top: 3rem;">
            <div class="category-card" style="text-align: center; padding: 2.5rem 2rem;">
                <div style="font-size: 4rem; margin-bottom: 1.5rem;">🎯</div>
                <h3 style="color: var(--primary-color); margin-bottom: 1rem; font-size: 1.5rem;">Notre Mission</h3>
                <p style="line-height: 1.8; color: var(--text-light);">
                    Transformer chaque maison en un foyer chaleureux et élégant grâce à nos meubles 
                    soigneusement sélectionnés, en offrant qualité et style à des prix accessibles.
                </p>
            </div>
            <div class="category-card" style="text-align: center; padding: 2.5rem 2rem;">
                <div style="font-size: 4rem; margin-bottom: 1.5rem;">👁️</div>
                <h3 style="color: var(--primary-color); margin-bottom: 1rem; font-size: 1.5rem;">Notre Vision</h3>
                <p style="line-height: 1.8; color: var(--text-light);">
                    Devenir la référence en matière de meubles modernes et accessibles pour tous les budgets, 
                    tout en maintenant notre engagement envers la qualité et le service client exceptionnel.
                </p>
            </div>
            <div class="category-card" style="text-align: center; padding: 2.5rem 2rem;">
                <div style="font-size: 4rem; margin-bottom: 1.5rem;">💎</div>
                <h3 style="color: var(--primary-color); margin-bottom: 1rem; font-size: 1.5rem;">Nos Valeurs</h3>
                <p style="line-height: 1.8; color: var(--text-light);">
                    Qualité, durabilité, service client et satisfaction garantie sont au cœur de tout 
                    ce que nous faisons. Votre bonheur est notre priorité.
                </p>
            </div>
        </div>
    </section>

    <!-- Section Pourquoi Nous Choisir -->
    <section style="margin-bottom: 4rem;">
        <h2 class="section-title">Pourquoi Nous Choisir ?</h2>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 2rem; margin-top: 3rem;">
            <div class="category-card" style="text-align: center; padding: 2rem;">
                <div style="font-size: 3.5rem; margin-bottom: 1rem;">✨</div>
                <h3 style="color: var(--primary-color); margin-bottom: 1rem;">Qualité Premium</h3>
                <p style="color: var(--text-light); line-height: 1.7;">
                    Tous nos meubles sont sélectionnés pour leur qualité exceptionnelle et leur durabilité.
                </p>
            </div>
            <div class="category-card" style="text-align: center; padding: 2rem;">
                <div style="font-size: 3.5rem; margin-bottom: 1rem;">💰</div>
                <h3 style="color: var(--primary-color); margin-bottom: 1rem;">Prix Compétitifs</h3>
                <p style="color: var(--text-light); line-height: 1.7;">
                    Meilleur rapport qualité/prix du marché sans compromis sur la qualité.
                </p>
            </div>
            <div class="category-card" style="text-align: center; padding: 2rem;">
                <div style="font-size: 3.5rem; margin-bottom: 1rem;">🚚</div>
                <h3 style="color: var(--primary-color); margin-bottom: 1rem;">Livraison Rapide</h3>
                <p style="color: var(--text-light); line-height: 1.7;">
                    Expédition sous 48h pour la plupart des produits. Livraison gratuite à partir de 1000 DH.
                </p>
            </div>
            <div class="category-card" style="text-align: center; padding: 2rem;">
                <div style="font-size: 3.5rem; margin-bottom: 1rem;">👥</div>
                <h3 style="color: var(--primary-color); margin-bottom: 1rem;">Service Client</h3>
                <p style="color: var(--text-light); line-height: 1.7;">
                    Équipe dédiée à votre service 7j/7 pour répondre à toutes vos questions.
                </p>
            </div>
            <div class="category-card" style="text-align: center; padding: 2rem;">
                <div style="font-size: 3.5rem; margin-bottom: 1rem;">🛡️</div>
                <h3 style="color: var(--primary-color); margin-bottom: 1rem;">Garantie</h3>
                <p style="color: var(--text-light); line-height: 1.7;">
                    Tous nos produits sont garantis minimum 2 ans pour votre tranquillité d'esprit.
                </p>
            </div>
            <div class="category-card" style="text-align: center; padding: 2rem;">
                <div style="font-size: 3.5rem; margin-bottom: 1rem;">🔄</div>
                <h3 style="color: var(--primary-color); margin-bottom: 1rem;">Retour Facile</h3>
                <p style="color: var(--text-light); line-height: 1.7;">
                    30 jours pour changer d'avis. Politique de retour simple et transparente.
                </p>
            </div>
        </div>
    </section>

    <!-- Section Statistiques -->
    <section style="background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%); 
                     color: white; padding: 3rem 2rem; border-radius: 15px; margin-bottom: 4rem;">
        <h2 class="section-title" style="text-align: center; margin-bottom: 3rem; font-size: 2rem; color: white;">Nos Chiffres</h2>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 2rem; text-align: center;">
            <div>
                <div style="font-size: 3rem; font-weight: bold; margin-bottom: 0.5rem;">500+</div>
                <div style="font-size: 1.1rem; opacity: 0.9;">Clients satisfaits</div>
            </div>
            <div>
                <div style="font-size: 3rem; font-weight: bold; margin-bottom: 0.5rem;">1000+</div>
                <div style="font-size: 1.1rem; opacity: 0.9;">Produits disponibles</div>
            </div>
            <div>
                <div style="font-size: 3rem; font-weight: bold; margin-bottom: 0.5rem;">10+</div>
                <div style="font-size: 1.1rem; opacity: 0.9;">Années d'expérience</div>
            </div>
            <div>
                <div style="font-size: 3rem; font-weight: bold; margin-bottom: 0.5rem;">98%</div>
                <div style="font-size: 1.1rem; opacity: 0.9;">Satisfaction client</div>
            </div>
        </div>
    </section>

</div>

<?php require_once 'includes/footer.php'; ?>
