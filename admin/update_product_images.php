<?php
// Script pour mettre à jour les images des produits
// Accédez à ce fichier via: http://localhost/MeublesMaison/admin/update_product_images.php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

require_once __DIR__ . '/../../db.php';

$pageTitle = "Mise à jour des images produits";
require_once 'includes/header.php';

$results = [];
$errors = [];
$success = false;

// Mapping des produits vers les images
$imageMapping = [
    // BUREAU
    ['pattern' => '%Chaise de bureau ergonomique%', 'category' => 'Bureau', 'image' => 'images/Chaise de bureau ergonomique Fauteuil pivotant Hauteur réglable, accoudoirs inclinables pour bureau - Marron 65x68x94_104 cm.jpeg'],
    ['pattern' => '%Chaise de bureau en tissu%', 'category' => 'Bureau', 'image' => 'images/1765286166_Chaise de bureau ergonomique Fauteuil pivotant Hauteur réglable, accoudoirs inclinables pour bureau - Marron 65x68x94_104 cm.jpeg'],
    ['pattern' => '%Bureau droit%', 'category' => 'Bureau', 'image' => 'images/1765286150_study table ideas study motivation study aesthetic study study tips study note study table study har.jpeg'],
    ['pattern' => '%Bureau d\'angle%', 'category' => 'Bureau', 'image' => 'images/1765373147_Bureau d\'angle en mélamine coloris imitation chêne….jpeg'],
    ['pattern' => '%Caisson%', 'category' => 'Bureau', 'image' => 'images/1765373576_Rehaussez l\'organisation de votre maison avec ce….jpeg'],
    
    // CHAMBRE
    ['pattern' => '%Lit double%', 'category' => 'Chambre', 'image' => 'images/Lit double MARIUS 160x200 tissu beige sommier inclus.jpeg'],
    ['pattern' => '%Armoire 3 portes%', 'category' => 'Chambre', 'image' => 'images/Willa Arlo™ Interiors Armoire 3 portes 70 _Monchat.jpeg'],
    ['pattern' => '%Armoire coulissante%', 'category' => 'Chambre', 'image' => 'images/1765373186_L\'armoire Max 2 à portes coulissantes est une….jpeg'],
    ['pattern' => '%Commode%', 'category' => 'Chambre', 'image' => 'images/Commode 4 Tiroirs Pricy.jpeg'],
    ['pattern' => '%Table de chevet%', 'category' => 'Chambre', 'image' => 'images/1765373611_- 2-drawers nightstand in your bedroom due in part….jpeg'],
    
    // CUISINE
    ['pattern' => '%Meuble bas%', 'category' => 'Cuisine', 'image' => 'images/1765373455_✨ Cuisine blanche et bois moderne avec îlot _ 7….jpeg'],
    ['pattern' => '%Meuble haut%', 'category' => 'Cuisine', 'image' => 'images/1765373455_✨ Cuisine blanche et bois moderne avec îlot _ 7….jpeg'],
    ['pattern' => '%Table de cuisine%', 'category' => 'Cuisine', 'image' => 'images/1765372682_Amari 4 Seater Round Dining Table _ Dunelm.jpeg'],
    ['pattern' => '%Chaise de cuisine%', 'category' => 'Cuisine', 'image' => 'images/1765373515_Creative Retro Wood Dining Chair for Living Room….jpeg'],
    ['pattern' => '%Îlot%', 'category' => 'Cuisine', 'image' => 'images/1765373455_✨ Cuisine blanche et bois moderne avec îlot _ 7….jpeg'],
    
    // DÉCORATION
    ['pattern' => '%Étagère murale%', 'category' => 'Décoration', 'image' => 'images/Étagère blanche murale avec 8 cubes.jpeg'],
    ['pattern' => '%Miroir%', 'category' => 'Décoration', 'image' => 'images/1765373339_✓ Design Ovale Sophistiqué _ Ajoute une touche de….jpeg'],
    ['pattern' => '%Cadre mural%', 'category' => 'Décoration', 'image' => 'images/1765373220_Panneau mural à lattes larges en placage de chêne….jpeg'],
    ['pattern' => '%Lampe%', 'category' => 'Décoration', 'image' => 'images/1765373370_Lampe de bureau à éclairage LED pour les yeux….jpeg'],
    ['pattern' => '%Vase%', 'category' => 'Décoration', 'image' => 'images/1765373274_Ensemble de 2 vases en céramique creux, vases….jpeg'],
    
    // PLACARD
    ['pattern' => '%Placard coulissant%', 'category' => 'Placard', 'image' => 'images/1765373422_The versatile design of our 3-door mirrored….jpeg'],
    ['pattern' => '%Dressing%', 'category' => 'Placard', 'image' => 'images/1765373092_[Grand espace] Cette penderie (43 x 182 x 182 cm)….jpeg'],
    ['pattern' => '%chaussures%', 'category' => 'Placard', 'image' => 'images/1765373576_Rehaussez l\'organisation de votre maison avec ce….jpeg'],
    ['pattern' => '%Étagère de rangement%', 'category' => 'Placard', 'image' => 'images/1765373576_Rehaussez l\'organisation de votre maison avec ce….jpeg'],
    
    // SALLE À MANGER
    ['pattern' => '%Table à manger%', 'category' => 'Salle à manger', 'image' => 'images/Table à manger design en bois d\'acacia et métal – Élégance brute & moderne.jpeg'],
    ['pattern' => '%Chaise salle à manger%', 'category' => 'Salle à manger', 'image' => 'images/1765373515_Creative Retro Wood Dining Chair for Living Room….jpeg'],
    ['pattern' => '%Buffet%', 'category' => 'Salle à manger', 'image' => 'images/1765372717_Buffet salle à manger blanc cachemire 1 niche….jpeg'],
    ['pattern' => '%Vaisselier%', 'category' => 'Salle à manger', 'image' => 'images/1765372658_Optez pour le vaisselier vitré H_ 200 cm décor….jpeg'],
    
    // SALON
    ['pattern' => '%Canapé 3 places%', 'category' => 'Salon', 'image' => 'images/vidaXL 3-Sitzer-Sofa XT6927 Grau_Dunkelgrau Material_ Textil 198cm.jpeg'],
    ['pattern' => '%Canapé d\'angle%', 'category' => 'Salon', 'image' => 'images/1765372973_Un canapé 3 places convertible au design moderne….jpeg'],
    ['pattern' => '%Table basse%', 'category' => 'Salon', 'image' => 'images/1765373245_Table Basse Travertin Bois Moderne Pour Salon….jpeg'],
    ['pattern' => '%Fauteuil%', 'category' => 'Salon', 'image' => 'images/1765372906_Vente en ligne Fauteuil Molto sable de la….jpeg'],
    ['pattern' => '%Meuble TV%', 'category' => 'Salon', 'image' => 'images/1765373480_Description Design Élégant Le buffet est conçu….jpeg'],
];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update') {
    try {
        $pdo->beginTransaction();
        
        $updatedCount = 0;
        $skippedCount = 0;
        
        // Récupérer tous les produits
        $productsStmt = $pdo->query("SELECT id, name, category, image FROM products");
        $allProducts = $productsStmt->fetchAll();
        
        foreach ($allProducts as $product) {
            $updated = false;
            
            // Chercher une correspondance dans le mapping
            foreach ($imageMapping as $mapping) {
                if ($product['category'] === $mapping['category'] && 
                    preg_match('/' . str_replace('%', '.*', preg_quote($mapping['pattern'], '/')) . '/i', $product['name'])) {
                    
                    // Vérifier si l'image existe
                    $imagePath = '../' . $mapping['image'];
                    if (file_exists($imagePath) || file_exists($mapping['image'])) {
                        // Mettre à jour l'image
                        $updateStmt = $pdo->prepare("UPDATE products SET image = ? WHERE id = ?");
                        $updateStmt->execute([$mapping['image'], $product['id']]);
                        
                        $results[] = "✅ Mis à jour: {$product['name']} → {$mapping['image']}";
                        $updated = true;
                        $updatedCount++;
                        break; // Sortir de la boucle mapping une fois trouvé
                    } else {
                        $errors[] = "⚠️ Image non trouvée: {$mapping['image']} pour {$product['name']}";
                    }
                }
            }
            
            if (!$updated && $product['image'] === 'images/placeholder.jpg') {
                $skippedCount++;
                $results[] = "⏭️ Aucune correspondance trouvée: {$product['name']}";
            }
        }
        
        $pdo->commit();
        $success = true;
        $results[] = "<strong>✅ Total: $updatedCount produits mis à jour avec succès!</strong>";
        if ($skippedCount > 0) {
            $results[] = "<strong>⏭️ $skippedCount produits sans correspondance.</strong>";
        }
        
    } catch (PDOException $e) {
        $pdo->rollBack();
        $errors[] = "❌ Erreur: " . $e->getMessage();
    }
}

// Récupérer les statistiques actuelles
$stats = [];
try {
    $stmt = $pdo->query("
        SELECT 
            COUNT(*) as total,
            SUM(CASE WHEN image = 'images/placeholder.jpg' THEN 1 ELSE 0 END) as avec_placeholder,
            SUM(CASE WHEN image != 'images/placeholder.jpg' THEN 1 ELSE 0 END) as avec_image
        FROM products
    ");
    $stats = $stmt->fetch();
} catch (PDOException $e) {
    $errors[] = "Erreur lors de la récupération des statistiques: " . $e->getMessage();
}

// Récupérer les produits avec placeholder
$productsWithPlaceholder = [];
try {
    $stmt = $pdo->query("
        SELECT id, name, category, image 
        FROM products 
        WHERE image = 'images/placeholder.jpg'
        ORDER BY category, name
    ");
    $productsWithPlaceholder = $stmt->fetchAll();
} catch (PDOException $e) {
    // Ignorer l'erreur
}
?>

<div class="admin-container">
    <div class="admin-header">
        <h1>Mise à jour des images produits</h1>
    </div>

    <?php if ($success): ?>
        <div class="success-message">
            ✅ Mise à jour terminée avec succès!
        </div>
    <?php endif; ?>

    <?php if (!empty($errors)): ?>
        <div style="background: #e74c3c; color: white; padding: 1rem; border-radius: 5px; margin-bottom: 1rem;">
            <?php foreach ($errors as $error): ?>
                <p><?php echo htmlspecialchars($error); ?></p>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <!-- Statistiques actuelles -->
    <div style="background: var(--bg-light); padding: 1.5rem; border-radius: 10px; margin-bottom: 2rem;">
        <h2 style="margin-bottom: 1rem; color: var(--primary-color);">📊 Statistiques actuelles</h2>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem;">
            <div style="background: white; padding: 1rem; border-radius: 8px; border-left: 4px solid var(--primary-color);">
                <div style="font-size: 1.5rem; margin-bottom: 0.5rem;">📦</div>
                <div style="font-size: 1.2rem; color: var(--primary-color); font-weight: bold;">
                    <?php echo $stats['total'] ?? 0; ?> produits
                </div>
                <div style="font-size: 0.9rem; color: var(--text-light);">Total</div>
            </div>
            <div style="background: white; padding: 1rem; border-radius: 8px; border-left: 4px solid var(--secondary-color);">
                <div style="font-size: 1.5rem; margin-bottom: 0.5rem;">✅</div>
                <div style="font-size: 1.2rem; color: var(--secondary-color); font-weight: bold;">
                    <?php echo $stats['avec_image'] ?? 0; ?> avec image
                </div>
                <div style="font-size: 0.9rem; color: var(--text-light);">Images assignées</div>
            </div>
            <div style="background: white; padding: 1rem; border-radius: 8px; border-left: 4px solid #e74c3c;">
                <div style="font-size: 1.5rem; margin-bottom: 0.5rem;">⚠️</div>
                <div style="font-size: 1.2rem; color: #e74c3c; font-weight: bold;">
                    <?php echo $stats['avec_placeholder'] ?? 0; ?> placeholder
                </div>
                <div style="font-size: 0.9rem; color: var(--text-light);">À mettre à jour</div>
            </div>
        </div>
    </div>

    <!-- Produits avec placeholder -->
    <?php if (!empty($productsWithPlaceholder)): ?>
        <div style="background: var(--bg-light); padding: 1.5rem; border-radius: 10px; margin-bottom: 2rem;">
            <h2 style="margin-bottom: 1rem; color: var(--primary-color);">⚠️ Produits avec image placeholder (<?php echo count($productsWithPlaceholder); ?>)</h2>
            <div style="max-height: 300px; overflow-y: auto; background: white; padding: 1rem; border-radius: 8px;">
                <?php foreach ($productsWithPlaceholder as $product): ?>
                    <div style="padding: 0.5rem; border-bottom: 1px solid var(--border-light);">
                        <strong><?php echo htmlspecialchars($product['name']); ?></strong> 
                        <span style="color: var(--text-light);">(<?php echo htmlspecialchars($product['category']); ?>)</span>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>

    <!-- Formulaire de mise à jour -->
    <div style="background: var(--bg-light); padding: 1.5rem; border-radius: 10px; margin-bottom: 2rem;">
        <h2 style="margin-bottom: 1rem; color: var(--primary-color);">🖼️ Mettre à jour les images</h2>
        <p style="margin-bottom: 1rem; color: var(--text-light);">
            Ce script associera automatiquement les images disponibles aux produits correspondants 
            en fonction de leur nom et catégorie.
        </p>
        <form method="POST" action="update_product_images.php">
            <input type="hidden" name="action" value="update">
            <button type="submit" class="btn" style="font-size: 1.1rem; padding: 1rem 2rem;">
                ✅ Mettre à jour toutes les images
            </button>
        </form>
    </div>

    <!-- Résultats -->
    <?php if (!empty($results)): ?>
        <div style="background: var(--bg-light); padding: 1.5rem; border-radius: 10px; margin-bottom: 2rem;">
            <h2 style="margin-bottom: 1rem; color: var(--primary-color);">📝 Résultats</h2>
            <div style="background: white; padding: 1rem; border-radius: 8px; max-height: 400px; overflow-y: auto;">
                <?php foreach ($results as $result): ?>
                    <div style="padding: 0.5rem 0; border-bottom: 1px solid var(--border-light);">
                        <?php echo $result; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>

    <!-- Lien retour -->
    <div style="text-align: center; margin-top: 2rem;">
        <a href="dashboard.php" class="btn" style="background: var(--text-light);">
            ← Retour au tableau de bord
        </a>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>

