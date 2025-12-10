<?php
$pageTitle = "Commande";
require_once 'includes/header.php';
?>
<div class="container">
    <div class="checkout-container">
        <h1 class="section-title">Finaliser votre commande</h1>
        
        <div class="checkout-content">
            <!-- Formulaire de commande -->
            <div class="checkout-form-section">
                <h2>Informations de livraison</h2>
                <form id="checkout-form" class="checkout-form">
                    <div class="form-group">
                        <label for="name">Nom complet *</label>
                        <input type="text" id="name" name="name" required 
                               placeholder="Votre nom complet">
                    </div>
                    
                    <div class="form-group">
                        <label for="email">Email *</label>
                        <input type="email" id="email" name="email" required 
                               placeholder="votre@email.com">
                    </div>
                    
                    <div class="form-group">
                        <label for="phone">Téléphone *</label>
                        <input type="tel" id="phone" name="phone" required 
                               placeholder="01 23 45 67 89">
                    </div>
                    
                    <div class="form-group">
                        <label for="address">Adresse complète *</label>
                        <textarea id="address" name="address" rows="3" required 
                                  placeholder="Numéro, rue, code postal, ville"></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="notes">Notes de livraison (optionnel)</label>
                        <textarea id="notes" name="notes" rows="2" 
                                  placeholder="Instructions spéciales pour la livraison"></textarea>
                    </div>
                    
                    <div id="checkout-error" class="error-message" style="display: none;"></div>
                    <div id="checkout-success" class="success-message" style="display: none;"></div>
                    
                    <button type="submit" class="btn checkout-btn" id="submit-btn">
                        <span id="btn-text">Confirmer la commande</span>
                        <span id="btn-loading" style="display: none;">Traitement en cours...</span>
                    </button>
                </form>
            </div>
            
            <!-- Résumé de la commande -->
            <div class="checkout-summary-section">
                <h2>Résumé de votre commande</h2>
                <div id="checkout-items"></div>
                <div id="checkout-summary"></div>
            </div>
        </div>
    </div>
</div>

<style>
.checkout-container {
    max-width: 1200px;
    margin: 3rem auto;
    padding: 0 2rem;
}

.checkout-content {
    display: grid;
    grid-template-columns: 1.5fr 1fr;
    gap: 3rem;
    margin-top: 2rem;
}

.checkout-form-section,
.checkout-summary-section {
    background: var(--bg-white);
    padding: 2.5rem;
    border-radius: 20px;
    box-shadow: 0 8px 30px rgba(107, 78, 61, 0.1);
    border: 2px solid var(--border-light);
}

.checkout-form-section h2,
.checkout-summary-section h2 {
    color: var(--primary-color);
    font-size: 1.8rem;
    margin-bottom: 2rem;
    padding-bottom: 1rem;
    border-bottom: 3px solid var(--secondary-color);
}

.checkout-form .form-group {
    margin-bottom: 1.5rem;
}

.checkout-form label {
    display: block;
    color: var(--primary-color);
    font-weight: 600;
    margin-bottom: 0.5rem;
    font-size: 1rem;
}

.checkout-form input,
.checkout-form textarea {
    width: 100%;
    padding: 1rem 1.5rem;
    border: 2px solid var(--border-color);
    border-radius: 12px;
    font-size: 1rem;
    transition: all 0.3s ease;
    background: var(--bg-white);
    color: var(--text-dark);
    font-family: inherit;
}

.checkout-form input:focus,
.checkout-form textarea:focus {
    outline: none;
    border-color: var(--secondary-color);
    box-shadow: 0 0 0 4px rgba(184, 149, 106, 0.1);
    background: var(--bg-light);
}

.checkout-btn {
    width: 100%;
    padding: 1.2rem 2rem;
    background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
    color: white;
    border: none;
    border-radius: 12px;
    font-size: 1.2rem;
    font-weight: 700;
    cursor: pointer;
    transition: all 0.3s ease;
    margin-top: 1rem;
    box-shadow: 0 6px 20px rgba(107, 78, 61, 0.3);
    text-transform: uppercase;
    letter-spacing: 1px;
}

.checkout-btn:hover:not(:disabled) {
    background: linear-gradient(135deg, var(--secondary-color), var(--primary-color));
    transform: translateY(-3px);
    box-shadow: 0 10px 30px rgba(184, 149, 106, 0.4);
}

.checkout-btn:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

#checkout-items {
    margin-bottom: 2rem;
}

.checkout-item {
    display: flex;
    gap: 1rem;
    padding: 1rem 0;
    border-bottom: 1px solid var(--border-light);
}

.checkout-item:last-child {
    border-bottom: none;
}

.checkout-item-image {
    width: 80px;
    height: 80px;
    object-fit: cover;
    border-radius: 10px;
    border: 2px solid var(--border-color);
}

.checkout-item-info {
    flex: 1;
}

.checkout-item-info h4 {
    color: var(--primary-color);
    margin-bottom: 0.5rem;
    font-size: 1rem;
}

.checkout-item-info p {
    color: var(--text-light);
    font-size: 0.9rem;
}

.checkout-item-price {
    color: var(--primary-color);
    font-weight: 700;
    font-size: 1.1rem;
}

.checkout-summary-details {
    margin-top: 1.5rem;
    padding-top: 1.5rem;
    border-top: 2px solid var(--border-color);
}

.summary-row {
    display: flex;
    justify-content: space-between;
    padding: 0.75rem 0;
    color: var(--text-medium);
}

.summary-row.total {
    font-size: 1.5rem;
    font-weight: 800;
    color: var(--primary-color);
    border-top: 3px solid var(--primary-color);
    padding-top: 1rem;
    margin-top: 1rem;
}

.error-message {
    background: linear-gradient(135deg, #8B6F47, #6B4E3D);
    color: white;
    padding: 1rem;
    border-radius: 10px;
    margin-bottom: 1rem;
    text-align: center;
    font-weight: 600;
}

.success-message {
    background: linear-gradient(135deg, var(--wood-dark), var(--secondary-color));
    color: white;
    padding: 1rem;
    border-radius: 10px;
    margin-bottom: 1rem;
    text-align: center;
    font-weight: 600;
}

@media (max-width: 968px) {
    .checkout-content {
        grid-template-columns: 1fr;
        gap: 2rem;
    }
    
    .checkout-form-section,
    .checkout-summary-section {
        padding: 2rem 1.5rem;
    }
}

/* ===== MODAL DE SUCCÈS ===== */
.success-modal {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.7);
    backdrop-filter: blur(5px);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 10000;
    opacity: 0;
    transition: opacity 0.3s ease;
}

.success-modal.show {
    opacity: 1;
}

.success-modal-content {
    background: linear-gradient(135deg, var(--bg-white), var(--bg-light));
    padding: 3rem;
    border-radius: 25px;
    box-shadow: 0 20px 60px rgba(107, 78, 61, 0.3);
    border: 3px solid var(--secondary-color);
    max-width: 500px;
    width: 90%;
    text-align: center;
    transform: scale(0.8);
    transition: transform 0.3s ease;
}

.success-modal.show .success-modal-content {
    transform: scale(1);
}

.success-icon {
    font-size: 5rem;
    margin-bottom: 1.5rem;
    animation: bounceIn 0.6s ease;
}

@keyframes bounceIn {
    0% {
        transform: scale(0);
    }
    50% {
        transform: scale(1.2);
    }
    100% {
        transform: scale(1);
    }
}

.success-modal-content h2 {
    color: var(--primary-color);
    font-size: 2rem;
    margin-bottom: 1rem;
}

.success-message-text {
    color: var(--text-medium);
    font-size: 1.1rem;
    line-height: 1.6;
    margin-bottom: 2rem;
}

.order-details {
    background: var(--bg-light);
    padding: 1.5rem;
    border-radius: 15px;
    margin-bottom: 1.5rem;
    border: 2px solid var(--border-light);
}

.order-info {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.75rem 0;
    border-bottom: 1px solid var(--border-color);
}

.order-info:last-child {
    border-bottom: none;
}

.order-info strong {
    color: var(--primary-color);
    font-size: 1rem;
}

.order-number {
    color: var(--secondary-color);
    font-weight: 700;
    font-size: 1.2rem;
}

.order-total {
    color: var(--primary-color);
    font-weight: 800;
    font-size: 1.3rem;
}

.success-note {
    color: var(--text-light);
    font-size: 0.95rem;
    font-style: italic;
    margin-bottom: 2rem;
}

.success-actions {
    display: flex;
    gap: 1rem;
    justify-content: center;
    flex-wrap: wrap;
}

.success-actions .btn {
    padding: 1rem 2rem;
    font-size: 1rem;
    font-weight: 600;
    border-radius: 12px;
    text-decoration: none;
    display: inline-block;
    transition: all 0.3s ease;
}

.success-actions .btn-secondary {
    background: var(--bg-light);
    color: var(--primary-color);
    border: 2px solid var(--primary-color);
}

.success-actions .btn-secondary:hover {
    background: var(--primary-color);
    color: white;
    transform: translateY(-2px);
}

@media (max-width: 480px) {
    .success-modal-content {
        padding: 2rem 1.5rem;
    }
    
    .success-icon {
        font-size: 4rem;
    }
    
    .success-modal-content h2 {
        font-size: 1.5rem;
    }
    
    .success-actions {
        flex-direction: column;
    }
    
    .success-actions .btn {
        width: 100%;
    }
}

/* ===== MODAL DE SUCCÈS ===== */
.success-modal {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.7);
    backdrop-filter: blur(5px);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 10000;
    opacity: 0;
    transition: opacity 0.3s ease;
}

.success-modal.show {
    opacity: 1;
}

.success-modal-content {
    background: linear-gradient(135deg, var(--bg-white), var(--bg-light));
    padding: 3rem;
    border-radius: 25px;
    box-shadow: 0 20px 60px rgba(107, 78, 61, 0.3);
    border: 3px solid var(--secondary-color);
    max-width: 500px;
    width: 90%;
    text-align: center;
    transform: scale(0.8);
    transition: transform 0.3s ease;
}

.success-modal.show .success-modal-content {
    transform: scale(1);
}

.success-icon {
    font-size: 5rem;
    margin-bottom: 1.5rem;
    animation: bounceIn 0.6s ease;
}

@keyframes bounceIn {
    0% {
        transform: scale(0);
    }
    50% {
        transform: scale(1.2);
    }
    100% {
        transform: scale(1);
    }
}

.success-modal-content h2 {
    color: var(--primary-color);
    font-size: 2rem;
    margin-bottom: 1rem;
}

.success-message-text {
    color: var(--text-medium);
    font-size: 1.1rem;
    line-height: 1.6;
    margin-bottom: 2rem;
}

.order-details {
    background: var(--bg-light);
    padding: 1.5rem;
    border-radius: 15px;
    margin-bottom: 1.5rem;
    border: 2px solid var(--border-light);
}

.order-info {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.75rem 0;
    border-bottom: 1px solid var(--border-color);
}

.order-info:last-child {
    border-bottom: none;
}

.order-info strong {
    color: var(--primary-color);
    font-size: 1rem;
}

.order-number {
    color: var(--secondary-color);
    font-weight: 700;
    font-size: 1.2rem;
}

.order-total {
    color: var(--primary-color);
    font-weight: 800;
    font-size: 1.3rem;
}

.success-note {
    color: var(--text-light);
    font-size: 0.95rem;
    font-style: italic;
    margin-bottom: 2rem;
}

.success-actions {
    display: flex;
    gap: 1rem;
    justify-content: center;
    flex-wrap: wrap;
}

.success-actions .btn {
    padding: 1rem 2rem;
    font-size: 1rem;
    font-weight: 600;
    border-radius: 12px;
    text-decoration: none;
    display: inline-block;
    transition: all 0.3s ease;
}

.success-actions .btn-secondary {
    background: var(--bg-light);
    color: var(--primary-color);
    border: 2px solid var(--primary-color);
}

.success-actions .btn-secondary:hover {
    background: var(--primary-color);
    color: white;
    transform: translateY(-2px);
}

@media (max-width: 480px) {
    .success-modal-content {
        padding: 2rem 1.5rem;
    }
    
    .success-icon {
        font-size: 4rem;
    }
    
    .success-modal-content h2 {
        font-size: 1.5rem;
    }
    
    .success-actions {
        flex-direction: column;
    }
    
    .success-actions .btn {
        width: 100%;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', () => {
    // Vérifier si le panier est vide
    if (!cartManager || !cartManager.cart || cartManager.cart.length === 0) {
        document.querySelector('.checkout-container').innerHTML = `
            <div style="text-align: center; padding: 4rem 2rem;">
                <div style="font-size: 5rem; margin-bottom: 2rem;">🛒</div>
                <h2 style="color: var(--primary-color); margin-bottom: 1rem;">Votre panier est vide</h2>
                <p style="color: var(--text-light); margin-bottom: 2rem;">Ajoutez des produits à votre panier avant de passer commande.</p>
                <a href="products.php" class="btn" style="display: inline-block;">Continuer les achats</a>
            </div>
        `;
        return;
    }
    
    // Afficher les articles du panier
    renderCheckoutItems();
    
    // Gérer la soumission du formulaire
    const form = document.getElementById('checkout-form');
    if (form) {
        form.addEventListener('submit', handleCheckout);
    }
});

function renderCheckoutItems() {
    const cart = cartManager.cart;
    const checkoutItems = document.getElementById('checkout-items');
    const checkoutSummary = document.getElementById('checkout-summary');
    
    if (cart.length === 0) {
        checkoutItems.innerHTML = '<p>Votre panier est vide. <a href="products.php">Continuer les achats</a></p>';
        checkoutSummary.innerHTML = '';
        return;
    }
    
    let itemsHTML = '';
    cart.forEach(item => {
        itemsHTML += `
            <div class="checkout-item">
                <img src="${item.image || 'images/placeholder.jpg'}" alt="${item.name}" class="checkout-item-image">
                <div class="checkout-item-info">
                    <h4>${item.name}</h4>
                    <p>Quantité: ${item.quantity}</p>
                </div>
                <div class="checkout-item-price">
                    ${(item.price * item.quantity).toFixed(2)} DH
                </div>
            </div>
        `;
    });
    
    checkoutItems.innerHTML = itemsHTML;
    
    // Calculer le résumé
    const subtotal = cartManager.getTotal();
    const shipping = subtotal > 1000 ? 0 : 150;
    const total = subtotal + shipping;
    
    checkoutSummary.innerHTML = `
        <div class="checkout-summary-details">
            <div class="summary-row">
                <span>Sous-total</span>
                <span>${subtotal.toFixed(2)} DH</span>
            </div>
            <div class="summary-row">
                <span>Livraison</span>
                <span>${shipping > 0 ? shipping.toFixed(2) + ' DH' : 'Gratuite'}</span>
            </div>
            <div class="summary-row total">
                <span>Total</span>
                <span>${total.toFixed(2)} DH</span>
            </div>
        </div>
    `;
}

function handleCheckout(e) {
    e.preventDefault();
    
    const cart = cartManager.cart;
    if (cart.length === 0) {
        showError('Votre panier est vide !');
        return;
    }
    
    const form = e.target;
    const formData = new FormData(form);
    const name = formData.get('name');
    const email = formData.get('email');
    const phone = formData.get('phone');
    const address = formData.get('address');
    const notes = formData.get('notes') || '';
    
    // Validation
    if (!name || !email || !phone || !address) {
        showError('Veuillez remplir tous les champs obligatoires.');
        return;
    }
    
    if (!isValidEmail(email)) {
        showError('Veuillez entrer une adresse email valide.');
        return;
    }
    
    // Désactiver le bouton
    const submitBtn = document.getElementById('submit-btn');
    const btnText = document.getElementById('btn-text');
    const btnLoading = document.getElementById('btn-loading');
    
    submitBtn.disabled = true;
    btnText.style.display = 'none';
    btnLoading.style.display = 'inline';
    
    // Calculer le total
    const subtotal = cartManager.getTotal();
    const shipping = subtotal > 100 ? 0 : 15;
    const total = subtotal + shipping;
    
    // Envoyer les données
    fetch('process_order.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            name: name,
            email: email,
            phone: phone,
            address: address,
            notes: notes,
            items: cart,
            subtotal: subtotal,
            shipping: shipping,
            total: total
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Afficher une belle modal de succès
            showSuccessModal(data.order_id, total);
            
            // Vider le panier
            cartManager.cart = [];
            cartManager.saveCart();
            
            // Masquer le formulaire
            document.querySelector('.checkout-content').style.display = 'none';
        } else {
            showError('Erreur lors de la commande : ' + data.message);
            submitBtn.disabled = false;
            btnText.style.display = 'inline';
            btnLoading.style.display = 'none';
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showError('Une erreur est survenue. Veuillez réessayer.');
        submitBtn.disabled = false;
    btnText.style.display = 'inline';
        btnLoading.style.display = 'none';
    });
}

function showError(message) {
    const errorDiv = document.getElementById('checkout-error');
    const successDiv = document.getElementById('checkout-success');
    
    successDiv.style.display = 'none';
    errorDiv.textContent = message;
    errorDiv.style.display = 'block';
    
    // Scroll to error
    errorDiv.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
}

function showSuccess(message) {
    const errorDiv = document.getElementById('checkout-error');
    const successDiv = document.getElementById('checkout-success');
    
    errorDiv.style.display = 'none';
    successDiv.textContent = message;
    successDiv.style.display = 'block';
    
    // Scroll to success
    successDiv.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
}

function showSuccessModal(orderId, total) {
    // Créer une modal de succès élégante
    const modal = document.createElement('div');
    modal.className = 'success-modal';
    modal.innerHTML = `
        <div class="success-modal-content">
            <div class="success-icon">✅</div>
            <h2>Commande confirmée !</h2>
            <p class="success-message-text">Merci pour votre commande. Nous avons bien reçu votre demande.</p>
            <div class="order-details">
                <div class="order-info">
                    <strong>Numéro de commande :</strong>
                    <span class="order-number">#${orderId}</span>
                </div>
                <div class="order-info">
                    <strong>Montant total :</strong>
                    <span class="order-total">${total.toFixed(2)} DH</span>
                </div>
            </div>
            <p class="success-note">Vous recevrez un email de confirmation sous peu.</p>
            <div class="success-actions">
                <a href="index.php" class="btn">Retour à l'accueil</a>
                <a href="products.php" class="btn btn-secondary">Continuer les achats</a>
            </div>
        </div>
    `;
    
    document.body.appendChild(modal);
    
    // Animation d'apparition
    setTimeout(() => {
        modal.classList.add('show');
    }, 10);
}

function isValidEmail(email) {
    return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
}
</script>

<?php require_once 'includes/footer.php'; ?>

