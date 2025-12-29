<?php
/**
 * Test Visual Search with existing product images
 * اختبار Visual Search باستخدام صور المنتجات الموجودة
 */

$pageTitle = "Test Visual Search - Images Réelles";
require_once 'includes/header.php';

require_once 'db.php';

// Get products with images
try {
    $stmt = $pdo->query("
        SELECT p.id, p.name, p.image, p.price, 
               COALESCE(c.name, p.category, '') AS category
        FROM products p
        LEFT JOIN categories c ON p.category_id = c.id
        WHERE p.image IS NOT NULL AND p.image != ''
        ORDER BY p.id
        LIMIT 20
    ");
    $products = $stmt->fetchAll();
} catch (PDOException $e) {
    $products = [];
}
?>

<div class="container" style="max-width: 1400px; margin: 2rem auto; padding: 0 1rem;">
    <h1 style="color: var(--primary-color); margin-bottom: 2rem;">
        🧪 Test Visual Search - Images Réelles
    </h1>
    
    <div style="background: #e7f3ff; border-left: 4px solid #2196F3; padding: 1.5rem; border-radius: 8px; margin-bottom: 2rem;">
        <h3 style="color: #1976D2; margin-bottom: 0.5rem;">📝 Instructions</h3>
        <p style="color: #333; margin: 0;">
            Cliquez sur une image ci-dessous pour tester la recherche visuelle avec cette image réelle d'un produit.
        </p>
    </div>

    <!-- Products Grid for Testing -->
    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); gap: 1.5rem; margin-bottom: 3rem;">
        <?php foreach ($products as $product): ?>
            <div class="test-product-card" 
                 style="background: white; border-radius: 12px; overflow: hidden; box-shadow: 0 2px 10px rgba(0,0,0,0.1); cursor: pointer; transition: all 0.3s ease;"
                 onclick="testSearchWithImage('<?php echo htmlspecialchars($product['image'], ENT_QUOTES); ?>', '<?php echo htmlspecialchars($product['name'], ENT_QUOTES); ?>')"
                 onmouseover="this.style.transform='translateY(-5px)'; this.style.boxShadow='0 8px 20px rgba(0,0,0,0.15)';"
                 onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 2px 10px rgba(0,0,0,0.1)';">
                <div style="position: relative; height: 200px; overflow: hidden; background: #f8f9fa;">
                    <img src="<?php echo htmlspecialchars($product['image']); ?>" 
                         alt="<?php echo htmlspecialchars($product['name']); ?>"
                         style="width: 100%; height: 100%; object-fit: cover;"
                         onerror="this.src='https://via.placeholder.com/250x200?text=Image'">
                    <div style="position: absolute; top: 0.5rem; right: 0.5rem; background: rgba(0,0,0,0.7); color: white; padding: 0.25rem 0.5rem; border-radius: 4px; font-size: 0.75rem;">
                        <?php echo htmlspecialchars($product['category']); ?>
                    </div>
                </div>
                <div style="padding: 1rem;">
                    <h4 style="margin: 0 0 0.5rem 0; font-size: 0.95rem; color: var(--text-dark);">
                        <?php echo htmlspecialchars($product['name']); ?>
                    </h4>
                    <div style="color: var(--primary-color); font-weight: 600;">
                        <?php echo number_format($product['price'], 2, ',', ' '); ?> DH
                    </div>
                    <div style="margin-top: 0.5rem; font-size: 0.85rem; color: #666;">
                        Cliquez pour tester
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Results Section -->
    <div id="test-results" style="display: none;">
        <h2 style="color: var(--primary-color); margin-bottom: 1.5rem;">
            Résultats de recherche pour: <span id="test-product-name"></span>
        </h2>
        
        <div id="test-detection-info" style="background: #e8f5e9; border-left: 4px solid #4caf50; padding: 1.5rem; border-radius: 8px; margin-bottom: 2rem;">
            <h3 style="color: #2e7d32; margin-bottom: 0.5rem; font-size: 1.1rem;">
                ✅ Objets détectés
            </h3>
            <div id="test-detected-objects" style="color: #1b5e20;"></div>
            <div id="test-detected-category" style="margin-top: 0.5rem; font-weight: 600; color: #2e7d32;"></div>
        </div>

        <div id="test-results-grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 2rem;">
            <!-- Results will be inserted here -->
        </div>

        <div id="test-no-results" style="display: none; text-align: center; padding: 4rem 2rem; background: var(--bg-light); border-radius: 15px;">
            <div style="font-size: 5rem; margin-bottom: 1.5rem;">😔</div>
            <h3 style="color: var(--primary-color); margin-bottom: 1rem; font-size: 1.5rem;">
                Aucun produit similaire trouvé
            </h3>
            <p style="color: var(--text-light); font-size: 1.1rem;">
                Essayez avec une autre image
            </p>
        </div>
    </div>

    <!-- Loading -->
    <div id="test-loading" style="display: none; text-align: center; padding: 3rem;">
        <div class="spinner" style="width: 50px; height: 50px; border: 4px solid #f3f3f3; border-top: 4px solid var(--primary-color); border-radius: 50%; animation: spin 1s linear infinite; margin: 0 auto;"></div>
        <p style="color: var(--text-light); margin-top: 1rem;">Analyse en cours...</p>
    </div>
</div>

<style>
@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}
</style>

<script>
async function testSearchWithImage(imagePath, productName) {
    console.log('Testing with image:', imagePath);
    
    // Show loading
    document.getElementById('test-loading').style.display = 'block';
    document.getElementById('test-results').style.display = 'none';
    
    // Scroll to results
    setTimeout(() => {
        document.getElementById('test-results').scrollIntoView({ behavior: 'smooth' });
    }, 100);
    
    try {
        // Fetch the image
        const imageResponse = await fetch(imagePath);
        const imageBlob = await imageResponse.blob();
        
        // Create form data
        const formData = new FormData();
        formData.append('image', imageBlob, 'test.jpg');
        
        // Call search API
        const response = await fetch('api/visual_search.php?action=search', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        // Hide loading
        document.getElementById('test-loading').style.display = 'none';
        document.getElementById('test-results').style.display = 'block';
        document.getElementById('test-product-name').textContent = productName;
        
        // Show detection info
        if (data.detected_objects && data.detected_objects.length > 0) {
            const objectsList = data.detected_objects.map(obj => 
                `${obj.label} (${(obj.confidence * 100).toFixed(1)}%)`
            ).join(', ');
            document.getElementById('test-detected-objects').textContent = objectsList;
            
            if (data.detected_category) {
                document.getElementById('test-detected-category').textContent = 
                    `Catégorie détectée: ${data.detected_category}`;
            }
            document.getElementById('test-detection-info').style.display = 'block';
        } else {
            document.getElementById('test-detection-info').style.display = 'none';
        }
        
        // Display results
        displayTestResults(data.results || []);
        
    } catch (error) {
        console.error('Search error:', error);
        document.getElementById('test-loading').style.display = 'none';
        alert('Erreur: ' + error.message);
    }
}

function displayTestResults(results) {
    const resultsGrid = document.getElementById('test-results-grid');
    const noResults = document.getElementById('test-no-results');
    
    if (results.length === 0) {
        resultsGrid.style.display = 'none';
        noResults.style.display = 'block';
        return;
    }
    
    resultsGrid.style.display = 'grid';
    noResults.style.display = 'none';
    resultsGrid.innerHTML = '';
    
    results.forEach(product => {
        const similarityPercent = (product.similarity * 100).toFixed(1);
        const productCard = document.createElement('div');
        productCard.className = 'product-card';
        productCard.style.cssText = 'overflow: hidden; position: relative; background: white; border-radius: 12px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);';
        
        productCard.innerHTML = `
            <div style="position: relative; overflow: hidden; height: 280px; background: var(--bg-light);">
                <img src="${product.image}" 
                     alt="${product.name}" 
                     style="width: 100%; height: 100%; object-fit: cover; transition: transform 0.3s ease;"
                     onerror="this.src='https://via.placeholder.com/300x280?text=Produit'">
                <div style="position: absolute; top: 1rem; right: 1rem; background: #4caf50; color: white; padding: 0.5rem 1rem; border-radius: 20px; font-size: 0.85rem; font-weight: 600; z-index: 10;">
                    ${similarityPercent}% similaire
                </div>
            </div>
            <div style="padding: 1.5rem;">
                <h3 style="color: var(--text-dark); margin-bottom: 0.5rem; font-size: 1.1rem; font-weight: 600;">
                    ${escapeHtml(product.name)}
                </h3>
                <div style="color: var(--primary-color); font-size: 1.3rem; font-weight: bold; margin-bottom: 1rem;">
                    ${parseFloat(product.price).toLocaleString('fr-FR', {minimumFractionDigits: 2})} DH
                </div>
                <div style="display: flex; gap: 0.5rem;">
                    <a href="product.php?id=${product.product_id}" 
                       class="btn" 
                       style="flex: 1; text-align: center; padding: 0.875rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; font-size: 0.9rem;">
                        👁️ Voir détails
                    </a>
                </div>
            </div>
        `;
        
        resultsGrid.appendChild(productCard);
    });
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}
</script>

<?php require_once 'includes/footer.php'; ?>

