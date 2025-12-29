<?php
/**
 * Visual Search Page - صفحة البحث البصري
 * واجهة المستخدم للبحث بالصور
 */

$pageTitle = "Recherche Visuelle - Recherchez par Image";
$pageMetaDescription = "Trouvez des meubles similaires en téléchargeant une photo. Recherche visuelle intelligente comme IKEA.";
require_once 'includes/header.php';
?>

<div class="container" style="max-width: 1200px; margin: 2rem auto; padding: 0 1rem;">
    <!-- Header -->
    <div style="text-align: center; margin-bottom: 3rem;">
        <h1 style="color: var(--primary-color); font-size: 2.5rem; margin-bottom: 1rem;">
            🔍 Recherche Visuelle
        </h1>
        <p style="color: var(--text-light); font-size: 1.1rem; max-width: 600px; margin: 0 auto;">
            Téléchargez une photo de meuble et trouvez des produits similaires dans notre catalogue
        </p>
    </div>

    <!-- Upload Section -->
    <div id="upload-section" style="background: white; border-radius: 16px; padding: 3rem; box-shadow: 0 4px 20px rgba(0,0,0,0.1); margin-bottom: 2rem;">
        <div style="text-align: center;">
            <!-- Upload Area -->
            <div id="upload-area" 
                 style="border: 3px dashed #8B4513; border-radius: 12px; padding: 3rem; background: #f8f9fa; cursor: pointer; transition: all 0.3s ease;"
                 onmouseover="this.style.borderColor='#6B3410'; this.style.background='#e9ecef';"
                 onmouseout="this.style.borderColor='#8B4513'; this.style.background='#f8f9fa';">
                <input type="file" 
                       id="image-input" 
                       accept="image/jpeg,image/jpg,image/png,image/webp" 
                       style="display: none;"
                       onchange="handleImageSelect(event)">
                <div onclick="document.getElementById('image-input').click()" style="cursor: pointer;">
                    <div style="font-size: 4rem; margin-bottom: 1rem;">📸</div>
                    <h3 style="color: var(--primary-color); margin-bottom: 0.5rem;">
                        Cliquez pour télécharger une image
                    </h3>
                    <p style="color: var(--text-light); font-size: 0.95rem;">
                        Formats acceptés: JPEG, PNG, WebP (max 10MB)
                    </p>
                </div>
            </div>

            <!-- Preview -->
            <div id="image-preview" style="display: none; margin-top: 2rem;">
                <img id="preview-img" 
                     src="" 
                     alt="Preview" 
                     style="max-width: 100%; max-height: 400px; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.2);">
                <div style="margin-top: 1rem;">
                    <button onclick="startSearch()" 
                            id="search-btn"
                            style="padding: 1rem 2.5rem; background: var(--primary-color); color: white; border: none; border-radius: 8px; font-size: 1.1rem; font-weight: 600; cursor: pointer; transition: all 0.3s ease;"
                            onmouseover="this.style.background='#6B3410'; this.style.transform='scale(1.05)';"
                            onmouseout="this.style.background='var(--primary-color)'; this.style.transform='scale(1)';">
                        🔍 Rechercher
                    </button>
                    <button onclick="resetSearch()" 
                            style="padding: 1rem 2rem; background: #95a5a6; color: white; border: none; border-radius: 8px; font-size: 1rem; margin-left: 1rem; cursor: pointer;">
                        ✖️ Annuler
                    </button>
                </div>
            </div>

            <!-- Loading -->
            <div id="loading" style="display: none; margin-top: 2rem;">
                <div style="display: flex; flex-direction: column; align-items: center; gap: 1rem;">
                    <div class="spinner" style="width: 50px; height: 50px; border: 4px solid #f3f3f3; border-top: 4px solid var(--primary-color); border-radius: 50%; animation: spin 1s linear infinite;"></div>
                    <p style="color: var(--text-light);">Analyse de l'image en cours...</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Detection Info -->
    <div id="detection-info" style="display: none; background: #e8f5e9; border-left: 4px solid #4caf50; padding: 1.5rem; border-radius: 8px; margin-bottom: 2rem;">
        <h3 style="color: #2e7d32; margin-bottom: 0.5rem; font-size: 1.2rem;">
            ✅ Objets détectés
        </h3>
        <div id="detected-objects" style="color: #1b5e20;"></div>
        <div id="detected-category" style="margin-top: 0.5rem; font-weight: 600; color: #2e7d32;"></div>
    </div>

    <!-- Results Section -->
    <div id="results-section" style="display: none;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
            <h2 style="color: var(--primary-color); font-size: 2rem;">
                Résultats de recherche
            </h2>
            <div id="results-count" style="background: var(--bg-light); padding: 0.75rem 1.5rem; border-radius: 25px; color: var(--primary-color); font-weight: 600;">
                <span id="results-number">0</span> produit(s) trouvé(s)
            </div>
        </div>

        <!-- Results Grid -->
        <div id="results-grid" class="products-grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 2rem;">
            <!-- Results will be inserted here -->
        </div>

        <!-- No Results -->
        <div id="no-results" style="display: none; text-align: center; padding: 4rem 2rem; background: var(--bg-light); border-radius: 15px;">
            <div style="font-size: 5rem; margin-bottom: 1.5rem;">😔</div>
            <h3 style="color: var(--primary-color); margin-bottom: 1rem; font-size: 1.5rem;">
                Aucun produit similaire trouvé
            </h3>
            <p style="color: var(--text-light); font-size: 1.1rem;">
                Essayez avec une autre image ou vérifiez que l'image contient bien un meuble
            </p>
        </div>
    </div>

    <!-- Error Message -->
    <div id="error-message" style="display: none; background: #ffebee; border-left: 4px solid #f44336; padding: 1.5rem; border-radius: 8px; margin-top: 2rem;">
        <h3 style="color: #c62828; margin-bottom: 0.5rem;">❌ Erreur</h3>
        <p id="error-text" style="color: #b71c1c;"></p>
    </div>
</div>

<style>
@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

.products-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: 2rem;
}

.product-card {
    background: white;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    transition: all 0.3s ease;
}

.product-card:hover {
    transform: translateY(-8px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.15);
}

.similarity-badge {
    position: absolute;
    top: 1rem;
    right: 1rem;
    background: #4caf50;
    color: white;
    padding: 0.5rem 1rem;
    border-radius: 20px;
    font-size: 0.85rem;
    font-weight: 600;
    z-index: 10;
}

@media (max-width: 768px) {
    .products-grid {
        grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
        gap: 1.5rem;
    }
}
</style>

<script>
let selectedFile = null;

function handleImageSelect(event) {
    const file = event.target.files[0];
    if (!file) return;

    // Validate file type
    const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp'];
    if (!allowedTypes.includes(file.type)) {
        showError('Format non supporté. Utilisez JPEG, PNG ou WebP.');
        return;
    }

    // Validate file size (10MB)
    if (file.size > 10 * 1024 * 1024) {
        showError('Image trop volumineuse. Taille maximale: 10MB');
        return;
    }

    selectedFile = file;

    // Show preview
    const reader = new FileReader();
    reader.onload = function(e) {
        document.getElementById('preview-img').src = e.target.result;
        document.getElementById('image-preview').style.display = 'block';
        document.getElementById('upload-area').style.display = 'none';
        hideError();
    };
    reader.readAsDataURL(file);
}

function resetSearch() {
    selectedFile = null;
    document.getElementById('image-input').value = '';
    document.getElementById('image-preview').style.display = 'none';
    document.getElementById('upload-area').style.display = 'block';
    document.getElementById('results-section').style.display = 'none';
    document.getElementById('detection-info').style.display = 'none';
    hideError();
}

function showError(message) {
    document.getElementById('error-text').textContent = message;
    document.getElementById('error-message').style.display = 'block';
}

function hideError() {
    document.getElementById('error-message').style.display = 'none';
}

async function startSearch() {
    if (!selectedFile) {
        showError('Veuillez sélectionner une image');
        return;
    }

    // Show loading
    document.getElementById('loading').style.display = 'block';
    document.getElementById('results-section').style.display = 'none';
    document.getElementById('detection-info').style.display = 'none';
    hideError();

    // Prepare form data
    const formData = new FormData();
    formData.append('image', selectedFile);

    try {
        const response = await fetch('api/visual_search.php?action=search', {
            method: 'POST',
            body: formData
        });

        const data = await response.json();

        // Hide loading
        document.getElementById('loading').style.display = 'none';

        if (!response.ok || !data.success) {
            showError(data.error || 'Erreur lors de la recherche');
            return;
        }

        // Show detection info
        if (data.detected_objects && data.detected_objects.length > 0) {
            const objectsList = data.detected_objects.map(obj => 
                `${obj.label} (${(obj.confidence * 100).toFixed(1)}%)`
            ).join(', ');
            document.getElementById('detected-objects').textContent = objectsList;
            
            if (data.detected_category) {
                document.getElementById('detected-category').textContent = 
                    `Catégorie détectée: ${data.detected_category}`;
            }
            document.getElementById('detection-info').style.display = 'block';
        }

        // Display results
        displayResults(data.results || []);

    } catch (error) {
        console.error('Search error:', error);
        document.getElementById('loading').style.display = 'none';
        showError('Erreur de connexion. Vérifiez que le service Python est démarré.');
    }
}

function displayResults(results) {
    const resultsGrid = document.getElementById('results-grid');
    const noResults = document.getElementById('no-results');
    const resultsSection = document.getElementById('results-section');
    const resultsNumber = document.getElementById('results-number');

    resultsNumber.textContent = results.length;
    resultsSection.style.display = 'block';

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
        productCard.style.cssText = 'overflow: hidden; position: relative;';

        productCard.innerHTML = `
            <div style="position: relative; overflow: hidden; height: 280px; background: var(--bg-light);">
                <img src="${product.image}" 
                     alt="${product.name}" 
                     style="width: 100%; height: 100%; object-fit: cover; transition: transform 0.3s ease;"
                     onerror="this.src='https://via.placeholder.com/300x280?text=Produit'">
                <div class="similarity-badge">
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

// Check service health on load
window.addEventListener('load', async function() {
    try {
        const response = await fetch('api/visual_search.php?action=health');
        const data = await response.json();
        
        if (!data.status || data.status !== 'ok') {
            console.warn('⚠️ Visual Search service may not be running');
        }
    } catch (error) {
        console.warn('⚠️ Cannot connect to Visual Search service:', error);
    }
});
</script>

<?php require_once 'includes/footer.php'; ?>

