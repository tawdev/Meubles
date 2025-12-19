// ===== GESTION DU PANIER =====
class CartManager {
    constructor() {
        this.cart = this.loadCart();
        this.updateCartUI();
    }

    loadCart() {
        const cart = localStorage.getItem('cart');
        return cart ? JSON.parse(cart) : [];
    }

    saveCart() {
        localStorage.setItem('cart', JSON.stringify(this.cart));
        this.updateCartUI();
    }

    addToCart(productId, productName, productPrice, productImage, quantity = 1, dimensions = null) {
        try {
            // Validation des paramètres
            if (!productId || !productName || !productPrice) {
                console.error('Paramètres invalides pour addToCart:', { productId, productName, productPrice });
                throw new Error('Paramètres invalides pour ajouter au panier');
            }

            // Convertir productId en string pour la cohérence
            const idToFind = String(productId).trim();
            const price = parseFloat(String(productPrice).replace(/[^\d.,]/g, '').replace(',', '.'));
            
            if (isNaN(price) || price <= 0) {
                console.error('Prix invalide:', productPrice);
                throw new Error('Prix invalide');
            }

            const qty = parseInt(quantity) || 1;
            if (qty <= 0) {
                throw new Error('Quantité invalide');
            }
            
            // Pour les produits sur mesure avec dimensions, créer un ID unique incluant les dimensions
            const itemId = dimensions && (dimensions.length || dimensions.width) 
                ? `${idToFind}_${dimensions.length || 0}_${dimensions.width || 0}` 
                : idToFind;
            
            const existingItem = this.cart.find(item => {
                // Si c'est un produit avec dimensions, comparer par dimensions
                if (dimensions && (dimensions.length || dimensions.width)) {
                    const itemDimensions = item.dimensions || {};
                    return String(item.id) === idToFind && 
                           itemDimensions.length === dimensions.length && 
                           itemDimensions.width === dimensions.width;
                }
                // Sinon, comparer normalement (sans dimensions)
                return String(item.id) === idToFind && !item.dimensions;
            });
            
            if (existingItem) {
                existingItem.quantity += qty;
            } else {
                const newItem = {
                    id: idToFind,
                    name: String(productName).trim(),
                    price: price,
                    image: String(productImage || '').trim(),
                    quantity: qty
                };
                
                // Ajouter les dimensions si présentes
                if (dimensions && (dimensions.length || dimensions.width)) {
                    const surface = dimensions.surface || ((parseFloat(dimensions.length) * parseFloat(dimensions.width)) / 10000);
                    newItem.dimensions = {
                        length: parseFloat(dimensions.length) || 0,
                        width: parseFloat(dimensions.width) || 0,
                        maxLength: parseFloat(dimensions.maxLength) || 0,
                        maxWidth: parseFloat(dimensions.maxWidth) || 0,
                        basePrice: parseFloat(dimensions.basePrice) || 0,
                        surface: surface
                    };
                    // Modifier le nom pour inclure les dimensions seulement si pas déjà présent
                    if (!productName.includes('×') && !productName.includes('cm')) {
                        newItem.name = `${productName} (${dimensions.length}×${dimensions.width} cm)`;
                    }
                }
                
                this.cart.push(newItem);
            }
            
            this.saveCart();
            this.showNotification('Produit ajouté au panier !');
        } catch (error) {
            console.error('Erreur dans addToCart:', error);
            this.showNotification('Erreur: ' + (error.message || 'Impossible d\'ajouter au panier'), 'error');
            throw error;
        }
    }

    removeFromCart(productId) {
        // Convertir productId en string pour la comparaison
        const idToRemove = String(productId).replace(/['"]/g, ''); // Enlever les quotes si présentes
        this.cart = this.cart.filter(item => String(item.id) !== idToRemove);
        this.saveCart();
        this.showNotification('Produit retiré du panier');
        // Réafficher le panier après suppression
        this.renderCart();
    }

    updateQuantity(productId, quantity) {
        // Convertir productId en string pour la comparaison
        const idToFind = String(productId).replace(/['"]/g, ''); // Enlever les quotes si présentes
        const item = this.cart.find(item => String(item.id) === idToFind);
        if (item) {
            if (quantity <= 0) {
                this.removeFromCart(productId);
            } else {
                item.quantity = quantity;
                this.saveCart();
                // Réafficher le panier après mise à jour
                this.renderCart();
            }
        }
    }

    getTotal() {
        return this.cart.reduce((total, item) => total + (item.price * item.quantity), 0);
    }

    getItemCount() {
        return this.cart.reduce((count, item) => count + item.quantity, 0);
    }

    updateCartUI() {
        const cartCount = document.getElementById('cart-count');
        if (cartCount) {
            const count = this.getItemCount();
            cartCount.textContent = count;
            cartCount.style.display = count > 0 ? 'flex' : 'none';
        }

        // Mettre à jour la page panier si elle existe
        if (window.location.pathname.includes('cart.php')) {
            this.renderCart();
        }
    }

    renderCart() {
        const cartItemsContainer = document.getElementById('cart-items');
        const cartSummary = document.getElementById('cart-summary');
        
        if (!cartItemsContainer) return;

        if (this.cart.length === 0) {
            cartItemsContainer.innerHTML = `
                <div class="empty-cart">
                    <div class="empty-cart-icon">🛒</div>
                    <h2>Votre panier est vide</h2>
                    <p>Découvrez nos produits et ajoutez-les à votre panier !</p>
                    <a href="products.php" class="btn">Continuer les achats</a>
                </div>
            `;
            if (cartSummary) cartSummary.innerHTML = '';
            return;
        }

        let html = '<div class="cart-items" style="display: flex; flex-direction: column; gap: 1.5rem;">';
        this.cart.forEach(item => {
            const hasDimensions = item.dimensions && (item.dimensions.length || item.dimensions.width);
            const dimensionsInfo = hasDimensions 
                ? `<div class="dimensions-info">
                    <strong>📏 Dimensions:</strong> ${item.dimensions.length} cm × ${item.dimensions.width} cm
                    ${item.dimensions.surface ? ` (${item.dimensions.surface.toFixed(2)} m²)` : ''}
                    ${item.dimensions.basePrice ? `<br><span>Prix de base: ${item.dimensions.basePrice.toFixed(2)} DH/m²</span>` : ''}
                   </div>`
                : '';
            
            html += `
                <div class="cart-item" data-id="${item.id}">
                    <div class="cart-item-left">
                        <img src="${item.image}" alt="${item.name}" class="cart-item-image" 
                             onerror="this.src='https://via.placeholder.com/150x150?text=Produit'">
                    </div>
                    <div class="cart-item-center">
                        <h3 class="cart-item-name">${item.name}</h3>
                        <div class="cart-item-price">
                            ${item.price.toFixed(2)} DH${hasDimensions ? ' <span class="calculated-price-badge">(prix calculé)</span>' : ''}
                        </div>
                        ${hasDimensions ? dimensionsInfo : ''}
                    </div>
                    <div class="cart-item-right">
                        <div class="cart-item-quantity">
                            <button class="quantity-btn" onclick="cartManager.updateQuantity('${item.id}', ${item.quantity - 1})">-</button>
                            <input type="number" value="${item.quantity}" min="1" 
                                   onchange="cartManager.updateQuantity('${item.id}', parseInt(this.value))">
                            <button class="quantity-btn" onclick="cartManager.updateQuantity('${item.id}', ${item.quantity + 1})">+</button>
                        </div>
                        <div class="cart-item-total">
                            <div class="total-price">${(item.price * item.quantity).toFixed(2)} DH</div>
                            <div class="total-detail">${item.quantity} × ${item.price.toFixed(2)} DH</div>
                        </div>
                        <button class="remove-item" onclick="cartManager.removeFromCart('${item.id}')" title="Supprimer cet article">
                            🗑️
                        </button>
                    </div>
                </div>
            `;
        });
        html += '</div>';

        cartItemsContainer.innerHTML = html;

        if (cartSummary) {
            const subtotal = this.getTotal();
            const shipping = subtotal > 1000 ? 0 : 150;
            const total = subtotal + shipping;
            const itemCount = this.cart.reduce((sum, item) => sum + item.quantity, 0);

            cartSummary.innerHTML = `
                <div>
                    <h2>📋 Résumé de la commande</h2>
                    <div class="summary-item-count">
                        <strong>${itemCount}</strong> article${itemCount > 1 ? 's' : ''} dans votre panier
                    </div>
                    <div class="summary-row">
                        <span>Sous-total</span>
                        <span>${subtotal.toFixed(2)} DH</span>
                    </div>
                    <div class="summary-row">
                        <span>Livraison</span>
                        <span style="color: ${shipping > 0 ? 'var(--text-dark)' : '#2e7d32'};">
                            ${shipping > 0 ? shipping.toFixed(2) + ' DH' : '✓ Gratuite'}
                        </span>
                    </div>
                    ${subtotal < 1000 ? `<div class="free-shipping-banner">
                        💡 Ajoutez ${(1000 - subtotal).toFixed(2)} DH pour la livraison gratuite !
                    </div>` : ''}
                    <div class="summary-row total">
                        <span>Total</span>
                        <span>${total.toFixed(2)} DH</span>
                    </div>
                    <button class="checkout-btn" onclick="checkout()">
                        🛒 Passer la commande
                    </button>
                    <a href="products.php" class="continue-shopping">
                        ← Continuer les achats
                    </a>
                </div>
            `;
        }
    }

    showNotification(message, type = 'success') {
        // Créer une notification toast
        const notification = document.createElement('div');
        const bgColor = type === 'error' ? '#e74c3c' : '#27ae60';
        const icon = type === 'error' ? '❌' : '✅';
        
        notification.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            background: ${bgColor};
            color: white;
            padding: 1rem 2rem;
            border-radius: 5px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.2);
            z-index: 10000;
            animation: slideIn 0.3s ease;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-weight: 600;
            max-width: 400px;
        `;
        notification.innerHTML = `<span>${icon}</span> <span>${message}</span>`;
        document.body.appendChild(notification);

        setTimeout(() => {
            notification.style.animation = 'slideOut 0.3s ease';
            setTimeout(() => notification.remove(), 300);
        }, 3000);
    }
}

// Initialiser le gestionnaire de panier
const cartManager = new CartManager();
// Exposer aussi sur window pour les scripts inline (ex: products.php)
window.cartManager = cartManager;

// ===== FILTRAGE DES PRODUITS =====
function filterProducts() {
    const category = document.getElementById('filter-category')?.value || 'all';
    const priceRange = document.getElementById('filter-price')?.value || 'all';
    const searchTerm = document.getElementById('search-products')?.value.toLowerCase() || '';

    const productCards = document.querySelectorAll('.product-card');
    
    productCards.forEach(card => {
        const productCategory = card.dataset.category || '';
        const productPrice = parseFloat(card.dataset.price || 0);
        
        let show = true;

        // Filtre par catégorie
        if (category !== 'all' && productCategory !== category) {
            show = false;
        }

        // Filtre par prix
        if (priceRange !== 'all') {
            const [min, max] = priceRange.split('-').map(p => p === 'max' ? Infinity : parseFloat(p));
            if (productPrice < min || productPrice > max) {
                show = false;
            }
        }

        // Filtre par recherche
        if (searchTerm) {
            const productName = card.querySelector('.product-name')?.textContent.toLowerCase() || '';
            const productDesc = card.querySelector('.product-description')?.textContent.toLowerCase() || '';
            if (!productName.includes(searchTerm) && !productDesc.includes(searchTerm)) {
                show = false;
            }
        }

        card.style.display = show ? 'flex' : 'none';
    });
}

// ===== GALLERIE D'IMAGES PRODUIT =====
function initProductGallery() {
    const thumbnails = document.querySelectorAll('.thumbnail');
    const mainImage = document.querySelector('.main-image');

    if (!mainImage || thumbnails.length === 0) return;

    thumbnails.forEach(thumbnail => {
        thumbnail.addEventListener('click', () => {
            mainImage.src = thumbnail.src;
            thumbnails.forEach(t => t.classList.remove('active'));
            thumbnail.classList.add('active');
        });
    });
}

// ===== VALIDATION DE FORMULAIRE =====
function validateForm(formId) {
    const form = document.getElementById(formId);
    if (!form) return false;

    const inputs = form.querySelectorAll('input[required], textarea[required]');
    let isValid = true;

    inputs.forEach(input => {
        const errorDiv = input.parentElement.querySelector('.error-message');
        if (errorDiv) errorDiv.remove();

        if (!input.value.trim()) {
            isValid = false;
            const error = document.createElement('div');
            error.className = 'error-message';
            error.textContent = 'Ce champ est requis';
            input.parentElement.appendChild(error);
            input.style.borderColor = '#e74c3c';
        } else {
            input.style.borderColor = '#ddd';
            
            // Validation email
            if (input.type === 'email' && !isValidEmail(input.value)) {
                isValid = false;
                const error = document.createElement('div');
                error.className = 'error-message';
                error.textContent = 'Email invalide';
                input.parentElement.appendChild(error);
                input.style.borderColor = '#e74c3c';
            }
        }
    });

    return isValid;
}

function isValidEmail(email) {
    return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
}

// ===== CHECKOUT =====
function checkout() {
    if (cartManager.cart.length === 0) {
        alert('Votre panier est vide !');
        return;
    }

    // Rediriger vers la page de checkout
    window.location.href = 'checkout.php';
}

// ===== ANIMATIONS AU SCROLL =====
function initScrollAnimations() {
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('fade-in');
            }
        });
    }, { threshold: 0.1 });

    document.querySelectorAll('.product-card, .category-card').forEach(card => {
        observer.observe(card);
    });
}

// ===== CARROUSEL PRODUITS EN VEDETTE =====
function initCarousel() {
    const carousel = document.querySelector('.products-carousel');
    if (!carousel) return;

    let currentIndex = 0;
    const items = carousel.querySelectorAll('.product-card');
    const totalItems = items.length;
    const itemsPerView = 4;

    function showItems() {
        items.forEach((item, index) => {
            item.style.display = 
                (index >= currentIndex && index < currentIndex + itemsPerView) ? 'flex' : 'none';
        });
    }

    function next() {
        currentIndex = (currentIndex + itemsPerView) % totalItems;
        showItems();
    }

    function prev() {
        currentIndex = (currentIndex - itemsPerView + totalItems) % totalItems;
        showItems();
    }

    const nextBtn = document.querySelector('.carousel-next');
    const prevBtn = document.querySelector('.carousel-prev');

    if (nextBtn) nextBtn.addEventListener('click', next);
    if (prevBtn) prevBtn.addEventListener('click', prev);

    showItems();
}

// ===== INITIALISATION =====
document.addEventListener('DOMContentLoaded', () => {
    initProductGallery();
    initScrollAnimations();
    initCarousel();

    // Menu burger mobile
    const menuToggle = document.querySelector('.menu-toggle');
    const mainNav = document.querySelector('.main-nav');

    if (menuToggle && mainNav) {
        // Toggle menu on burger click
        menuToggle.addEventListener('click', (e) => {
            e.stopPropagation();
            const isOpen = mainNav.classList.toggle('is-open');
            menuToggle.classList.toggle('active', isOpen);
            menuToggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
        });

        // Fermer le menu après un clic sur un lien
        mainNav.querySelectorAll('a').forEach(link => {
            link.addEventListener('click', () => {
                mainNav.classList.remove('is-open');
                menuToggle.classList.remove('active');
                menuToggle.setAttribute('aria-expanded', 'false');
            });
        });

        // Fermer le menu en cliquant en dehors
        document.addEventListener('click', (e) => {
            if (mainNav.classList.contains('is-open')) {
                if (!mainNav.contains(e.target) && !menuToggle.contains(e.target)) {
                    mainNav.classList.remove('is-open');
                    menuToggle.classList.remove('active');
                    menuToggle.setAttribute('aria-expanded', 'false');
                }
            }
        });

        // Fermer le menu avec la touche Escape
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && mainNav.classList.contains('is-open')) {
                mainNav.classList.remove('is-open');
                menuToggle.classList.remove('active');
                menuToggle.setAttribute('aria-expanded', 'false');
            }
        });
    }

    // Ajouter les écouteurs de filtres
    const filterCategory = document.getElementById('filter-category');
    const filterPrice = document.getElementById('filter-price');
    const searchProducts = document.getElementById('search-products');

    if (filterCategory) filterCategory.addEventListener('change', filterProducts);
    if (filterPrice) filterPrice.addEventListener('change', filterProducts);
    if (searchProducts) searchProducts.addEventListener('input', filterProducts);

    // Gestion des boutons "Ajouter au panier" - Version robuste
    function initAddToCartButtons() {
        // Ne pas cloner les boutons qui ont déjà un listener (gérés par products.php ou product.php)
        // Ajouter les event listeners seulement aux boutons qui n'en ont pas
        document.querySelectorAll('.btn-add-cart').forEach(btn => {
            // Vérifier si le bouton a déjà un listener spécifique (pour produits sur mesure)
            if (btn.hasAttribute('data-listener-attached')) {
                // Ne pas ajouter de listener si déjà géré par products.php ou product.php
                return;
            }
            
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                
                try {
                    // Vérifier si le bouton est géré par une modal (produits sur mesure)
                    const typeName = String(this.dataset.typeName || '').toLowerCase();
                    const maxLength = String(this.dataset.maxLength || '').trim();
                    const maxWidth = String(this.dataset.maxWidth || '').trim();
                    
                    // Si c'est un produit "Sur mesure" avec des dimensions maximales, 
                    // vérifier si openDimensionsModal existe et l'utiliser
                    if (typeName.includes('sur mesure') && (maxLength || maxWidth)) {
                        // Vérifier si la fonction openDimensionsModal existe (définie dans products.php ou product.php)
                        if (typeof window.openDimensionsModal === 'function') {
                            window.openDimensionsModal({
                                id: this.dataset.id,
                                name: this.dataset.name,
                                price: this.dataset.price,
                                image: this.dataset.image,
                                maxLength: maxLength,
                                maxWidth: maxWidth
                            });
                            return false;
                        } else {
                            // Si la fonction n'existe pas, essayer d'ajouter normalement
                            console.warn('openDimensionsModal non disponible, ajout direct au panier');
                        }
                    }
                    
                    // Vérifier que cartManager est disponible
                    const manager = window.cartManager || cartManager;
                    if (!manager) {
                        console.error('cartManager n\'est pas disponible');
                        alert('Erreur: Impossible d\'ajouter au panier. Veuillez rafraîchir la page.');
                        return false;
                    }

                    // Vérifier si le bouton a des data-attributes
                    if (this.dataset.id && this.dataset.name && this.dataset.price) {
                        const productId = String(this.dataset.id).trim();
                        const productName = String(this.dataset.name).trim();
                        const productPrice = parseFloat(String(this.dataset.price).replace(/[^\d.,]/g, '').replace(',', '.')) || 0;
                        const productImage = String(this.dataset.image || '').trim();
                        
                        if (!productId || !productName || productPrice <= 0) {
                            console.error('Données produit invalides:', { productId, productName, productPrice });
                            alert('Erreur: Données produit invalides.');
                            return false;
                        }
                        
                        manager.addToCart(productId, productName, productPrice, productImage);
                    } else {
                        // Sinon, chercher dans la carte produit
                        const card = this.closest('.product-card');
                        if (card && card.dataset.id) {
                            const productId = String(card.dataset.id).trim();
                            const productName = card.querySelector('.product-name')?.textContent?.trim() || '';
                            const productPriceText = card.querySelector('.product-price')?.textContent || '0';
                            const productPrice = parseFloat(productPriceText.replace(/[^\d.,]/g, '').replace(',', '.')) || 0;
                            const productImage = card.querySelector('.product-image')?.src || card.querySelector('.product-image')?.getAttribute('src') || '';

                            if (!productId || !productName || productPrice <= 0) {
                                console.error('Données produit invalides depuis la carte:', { productId, productName, productPrice });
                                alert('Erreur: Impossible de récupérer les informations du produit.');
                                return false;
                            }

                            manager.addToCart(productId, productName, productPrice, productImage);
                        } else {
                            console.error('Impossible de trouver les données du produit');
                            alert('Erreur: Impossible de trouver les informations du produit.');
                            return false;
                        }
                    }
                } catch (error) {
                    console.error('Erreur lors de l\'ajout au panier:', error);
                    alert('Erreur: ' + (error.message || 'Une erreur est survenue lors de l\'ajout au panier.'));
                    return false;
                }
                
                return false;
            });
        });
    }

    // Initialiser les boutons au chargement
    initAddToCartButtons();
    
    // Réinitialiser après un délai pour les éléments chargés dynamiquement
    setTimeout(initAddToCartButtons, 500);
});

// Ajouter les styles d'animation pour les notifications
const style = document.createElement('style');
style.textContent = `
    @keyframes slideIn {
        from {
            transform: translateX(400px);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }
    @keyframes slideOut {
        from {
            transform: translateX(0);
            opacity: 1;
        }
        to {
            transform: translateX(400px);
            opacity: 0;
        }
    }
`;
document.head.appendChild(style);

