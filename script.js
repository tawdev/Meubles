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

    addToCart(productId, productName, productPrice, productImage, quantity = 1) {
        // Convertir productId en string pour la cohérence
        const idToFind = String(productId);
        const existingItem = this.cart.find(item => String(item.id) === idToFind);
        
        if (existingItem) {
            existingItem.quantity += quantity;
        } else {
            this.cart.push({
                id: idToFind,
                name: productName,
                price: parseFloat(productPrice),
                image: productImage,
                quantity: quantity
            });
        }
        
        this.saveCart();
        this.showNotification('Produit ajouté au panier !');
    }

    removeFromCart(productId) {
        // Convertir productId en string pour la comparaison
        const idToRemove = String(productId);
        this.cart = this.cart.filter(item => String(item.id) !== idToRemove);
        this.saveCart();
        this.showNotification('Produit retiré du panier');
        // Réafficher le panier après suppression
        this.renderCart();
    }

    updateQuantity(productId, quantity) {
        // Convertir productId en string pour la comparaison
        const idToFind = String(productId);
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
                    <a href="index.php" class="btn">Continuer les achats</a>
                </div>
            `;
            if (cartSummary) cartSummary.innerHTML = '';
            return;
        }

        let html = '<div class="cart-items">';
        this.cart.forEach(item => {
            html += `
                <div class="cart-item" data-id="${item.id}">
                    <img src="${item.image}" alt="${item.name}" class="cart-item-image">
                    <div class="cart-item-info">
                        <h3>${item.name}</h3>
                        <div class="cart-item-price">${item.price.toFixed(2)} €</div>
                    </div>
                    <div class="cart-item-quantity">
                        <button class="quantity-btn" onclick="cartManager.updateQuantity(${item.id}, ${item.quantity - 1})">-</button>
                        <input type="number" value="${item.quantity}" min="1" 
                               onchange="cartManager.updateQuantity(${item.id}, parseInt(this.value))">
                        <button class="quantity-btn" onclick="cartManager.updateQuantity(${item.id}, ${item.quantity + 1})">+</button>
                    </div>
                    <div class="cart-item-total">${(item.price * item.quantity).toFixed(2)} €</div>
                    <button class="remove-item" onclick="cartManager.removeFromCart(${item.id})">Supprimer</button>
                </div>
            `;
        });
        html += '</div>';

        cartItemsContainer.innerHTML = html;

        if (cartSummary) {
            const subtotal = this.getTotal();
            const shipping = subtotal > 100 ? 0 : 15;
            const total = subtotal + shipping;

            cartSummary.innerHTML = `
                <h2>Résumé de la commande</h2>
                <div class="summary-row">
                    <span>Sous-total</span>
                    <span>${subtotal.toFixed(2)} €</span>
                </div>
                <div class="summary-row">
                    <span>Livraison</span>
                    <span>${shipping > 0 ? shipping.toFixed(2) + ' €' : 'Gratuite'}</span>
                </div>
                <div class="summary-row total">
                    <span>Total</span>
                    <span>${total.toFixed(2)} €</span>
                </div>
                <button class="btn" onclick="checkout()" style="width: 100%; margin-top: 1rem;">Passer la commande</button>
            `;
        }
    }

    showNotification(message) {
        // Créer une notification toast
        const notification = document.createElement('div');
        notification.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            background: #27ae60;
            color: white;
            padding: 1rem 2rem;
            border-radius: 5px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.2);
            z-index: 10000;
            animation: slideIn 0.3s ease;
        `;
        notification.textContent = message;
        document.body.appendChild(notification);

        setTimeout(() => {
            notification.style.animation = 'slideOut 0.3s ease';
            setTimeout(() => notification.remove(), 300);
        }, 3000);
    }
}

// Initialiser le gestionnaire de panier
const cartManager = new CartManager();

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

    // Ajouter les écouteurs de filtres
    const filterCategory = document.getElementById('filter-category');
    const filterPrice = document.getElementById('filter-price');
    const searchProducts = document.getElementById('search-products');

    if (filterCategory) filterCategory.addEventListener('change', filterProducts);
    if (filterPrice) filterPrice.addEventListener('change', filterProducts);
    if (searchProducts) searchProducts.addEventListener('input', filterProducts);

    // Gestion des boutons "Ajouter au panier"
    document.querySelectorAll('.btn-add-cart').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            
            // Vérifier si le bouton a des data-attributes
            if (this.dataset.id) {
                const productId = this.dataset.id;
                const productName = this.dataset.name;
                const productPrice = this.dataset.price;
                const productImage = this.dataset.image || '';
                
                cartManager.addToCart(productId, productName, productPrice, productImage);
            } else {
                // Sinon, chercher dans la carte produit
                const card = this.closest('.product-card');
                if (card) {
                    const productId = card.dataset.id;
                    const productName = card.querySelector('.product-name')?.textContent || '';
                    const productPrice = card.querySelector('.product-price')?.textContent.replace(/[^\d.,]/g, '').replace(',', '.') || '0';
                    const productImage = card.querySelector('.product-image')?.src || '';

                    cartManager.addToCart(productId, productName, productPrice, productImage);
                }
            }
        });
    });
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

