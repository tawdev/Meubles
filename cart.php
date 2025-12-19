<?php
$pageTitle = "Panier";
require_once 'includes/header.php';
?>

<div class="container">
    <div class="cart-container" style="max-width: 1200px; margin: 0 auto; padding: 2rem 1rem;">
        <div style="margin-bottom: 2rem;">
            <a href="index.php" style="display: inline-flex; align-items: center; gap: 0.5rem; color: var(--primary-color); text-decoration: none; padding: 0.75rem 1.25rem; background: rgba(107, 78, 61, 0.1); border-radius: 8px; transition: all 0.3s ease; font-size: 0.95rem; font-weight: 500;" onmouseover="this.style.background='rgba(107, 78, 61, 0.2)'; this.style.transform='translateX(-3px)';" onmouseout="this.style.background='rgba(107, 78, 61, 0.1)'; this.style.transform='translateX(0)';">
                ← Retour à l'accueil
            </a>
        </div>
        <div style="text-align: center; margin-bottom: 3rem;">
            <h1 class="section-title" style="margin-bottom: 0.5rem; color: var(--primary-color); font-size: 2.5rem; font-weight: 700;">
                🛒 Mon Panier
            </h1>
            <p style="color: var(--text-light); font-size: 1.1rem;">Gérez vos articles et passez votre commande</p>
        </div>
        <div style="display: grid; grid-template-columns: 1fr 420px; gap: 2.5rem; align-items: start;">
            <div id="cart-items"></div>
            <div id="cart-summary"></div>
        </div>
    </div>
</div>

<style>
/* Styles pour la page panier */
.cart-container {
    padding: 2rem 1rem;
}

#cart-items {
    min-height: 200px;
}

.cart-item {
    background: white;
    border-radius: 16px;
    padding: 1.5rem;
    box-shadow: 0 2px 12px rgba(107, 78, 61, 0.08);
    display: flex;
    align-items: flex-start;
    gap: 1.5rem;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    border: 1px solid #f0f0f0;
    margin-bottom: 1.5rem;
    position: relative;
}

.cart-item-left {
    flex-shrink: 0;
}

.cart-item-center {
    flex: 1;
    min-width: 0;
}

.cart-item-right {
    display: flex;
    flex-direction: column;
    align-items: flex-end;
    gap: 1rem;
    flex-shrink: 0;
}

.cart-item:hover {
    transform: translateY(-4px);
    box-shadow: 0 8px 24px rgba(107, 78, 61, 0.15);
    border-color: var(--primary-color);
}

.cart-item-image {
    width: 150px;
    height: 150px;
    object-fit: cover;
    border-radius: 12px;
    border: 2px solid #f5f5f5;
    transition: transform 0.3s ease;
}

.cart-item:hover .cart-item-image {
    transform: scale(1.05);
}

.cart-item-name {
    margin: 0 0 0.75rem 0;
    font-size: 1.2rem;
    color: var(--text-dark);
    font-weight: 600;
    line-height: 1.4;
}

.cart-item-price {
    color: var(--primary-color);
    font-size: 1.1rem;
    font-weight: 600;
    margin-bottom: 0.75rem;
}

.calculated-price-badge {
    font-size: 0.85rem;
    color: #666;
    font-weight: 400;
    margin-left: 0.5rem;
}

.cart-item-quantity {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    background: #f8f9fa;
    padding: 0.5rem;
    border-radius: 10px;
    border: 1px solid #e0e0e0;
    width: fit-content;
}

.quantity-btn {
    width: 38px;
    height: 38px;
    border: none;
    background: white;
    border-radius: 8px;
    cursor: pointer;
    font-weight: bold;
    font-size: 1.2rem;
    color: var(--primary-color);
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    transition: all 0.2s ease;
    display: flex;
    align-items: center;
    justify-content: center;
}

.quantity-btn:hover {
    background: var(--primary-color);
    color: white;
    transform: scale(1.1);
    box-shadow: 0 4px 8px rgba(107, 78, 61, 0.2);
}

.quantity-btn:active {
    transform: scale(0.95);
}

.cart-item-quantity input {
    width: 60px;
    text-align: center;
    border: none;
    background: transparent;
    font-weight: 700;
    font-size: 1.1rem;
    color: var(--text-dark);
}

.cart-item-total {
    text-align: right;
    min-width: 120px;
}

.total-price {
    font-size: 1.4rem;
    font-weight: 700;
    color: var(--accent-color);
    margin-bottom: 0.25rem;
}

.total-detail {
    font-size: 0.9rem;
    color: var(--text-light);
}

.remove-item {
    width: 45px;
    height: 45px;
    border: none;
    background: #fee;
    border-radius: 10px;
    cursor: pointer;
    font-size: 1.2rem;
    color: #e74c3c;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    justify-content: center;
    border: 1px solid #fcc;
}

.remove-item:hover {
    background: #fcc;
    transform: scale(1.1);
    box-shadow: 0 2px 8px rgba(231, 76, 60, 0.3);
}

.remove-item:active {
    transform: scale(0.95);
}

/* Dimensions info */
.dimensions-info {
    margin-top: 0.75rem;
    padding: 0.75rem 1rem;
    background: #e8f5e9;
    border-radius: 8px;
    font-size: 0.9rem;
    border-left: 3px solid #2e7d32;
}

.dimensions-info strong {
    color: #2e7d32;
    display: block;
    margin-bottom: 0.25rem;
    font-size: 0.95rem;
}

.dimensions-info span {
    color: #555;
    font-size: 0.85rem;
    display: block;
    margin-top: 0.25rem;
}

/* Résumé de commande */
#cart-summary > div {
    background: white;
    border-radius: 16px;
    padding: 2rem;
    box-shadow: 0 4px 16px rgba(107, 78, 61, 0.1);
    position: sticky;
    top: 2rem;
    border: 1px solid #f0f0f0;
}

#cart-summary h2 {
    margin: 0 0 1.5rem 0;
    color: var(--primary-color);
    font-size: 1.6rem;
    font-weight: 700;
    border-bottom: 3px solid var(--primary-color);
    padding-bottom: 1rem;
}

.summary-item-count {
    margin-bottom: 1rem;
    padding: 0.75rem 1rem;
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    border-radius: 10px;
    font-size: 0.95rem;
    color: var(--text-medium);
    text-align: center;
    border: 1px solid #e0e0e0;
}

.summary-row {
    display: flex;
    justify-content: space-between;
    padding: 1rem 0;
    border-bottom: 1px solid #f0f0f0;
    font-size: 1rem;
}

.summary-row span:first-child {
    color: var(--text-medium);
    font-weight: 500;
}

.summary-row span:last-child {
    font-weight: 600;
    color: var(--text-dark);
}

.summary-row.total {
    border-top: 3px solid var(--primary-color);
    border-bottom: none;
    margin-top: 1rem;
    padding-top: 1.5rem;
}

.summary-row.total span:first-child {
    font-size: 1.3rem;
    font-weight: 700;
    color: var(--primary-color);
}

.summary-row.total span:last-child {
    font-size: 1.8rem;
    font-weight: 700;
    color: var(--accent-color);
}

.free-shipping-banner {
    margin-top: 1rem;
    padding: 0.75rem 1rem;
    background: linear-gradient(135deg, #fff3cd 0%, #ffe69c 100%);
    border-radius: 8px;
    font-size: 0.9rem;
    color: #856404;
    border-left: 4px solid #ffc107;
    text-align: center;
}

.checkout-btn {
    width: 100%;
    margin-top: 1.5rem;
    padding: 1.1rem;
    font-size: 1.15rem;
    font-weight: 700;
    background: linear-gradient(135deg, var(--primary-color) 0%, #5a3f2f 100%);
    color: white;
    border: none;
    border-radius: 12px;
    cursor: pointer;
    transition: all 0.3s ease;
    box-shadow: 0 4px 12px rgba(107, 78, 61, 0.3);
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.checkout-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(107, 78, 61, 0.4);
    background: linear-gradient(135deg, #5a3f2f 0%, var(--primary-color) 100%);
}

.checkout-btn:active {
    transform: translateY(0);
}

.continue-shopping {
    display: block;
    text-align: center;
    margin-top: 1rem;
    color: var(--primary-color);
    text-decoration: none;
    font-weight: 600;
    font-size: 0.95rem;
    transition: all 0.2s ease;
}

.continue-shopping:hover {
    color: var(--accent-color);
    text-decoration: underline;
}

/* Panier vide */
.empty-cart {
    text-align: center;
    padding: 5rem 2rem;
    background: white;
    border-radius: 16px;
    box-shadow: 0 4px 16px rgba(107, 78, 61, 0.1);
    border: 2px dashed #e0e0e0;
}

.empty-cart-icon {
    font-size: 6rem;
    margin-bottom: 2rem;
    opacity: 0.5;
}

.empty-cart h2 {
    color: var(--primary-color);
    margin-bottom: 1rem;
    font-size: 2rem;
    font-weight: 700;
}

.empty-cart p {
    color: var(--text-light);
    font-size: 1.1rem;
    margin-bottom: 2rem;
}

.empty-cart .btn {
    display: inline-block;
    padding: 1rem 2.5rem;
    font-size: 1.1rem;
    text-decoration: none;
    background: var(--primary-color);
    color: white;
    border-radius: 10px;
    font-weight: 600;
    transition: all 0.3s ease;
}

.empty-cart .btn:hover {
    background: var(--accent-color);
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(107, 78, 61, 0.3);
}

/* Responsive */
@media (max-width: 968px) {
    .cart-container > div {
        grid-template-columns: 1fr !important;
    }
    
    #cart-summary > div {
        position: relative !important;
        top: 0 !important;
    }
    
    .cart-item {
        flex-direction: column;
        align-items: flex-start;
        padding: 1.25rem;
    }
    
    .cart-item-left {
        width: 100%;
        text-align: center;
    }
    
    .cart-item-image {
        width: 120px;
        height: 120px;
        margin: 0 auto;
    }
    
    .cart-item-center {
        width: 100%;
        margin-top: 1rem;
    }
    
    .cart-item-right {
        width: 100%;
        flex-direction: row;
        justify-content: space-between;
        align-items: center;
        margin-top: 1rem;
        padding-top: 1rem;
        border-top: 2px solid #f0f0f0;
    }
    
    .cart-item-total {
        text-align: center;
    }
    
    .remove-item {
        width: 40px;
        height: 40px;
    }
}

@media (max-width: 640px) {
    .cart-container {
        padding: 1rem 0.5rem;
    }
    
    .cart-item {
        padding: 1rem;
    }
    
    .cart-item-info h3 {
        font-size: 1.1rem;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', () => {
    cartManager.renderCart();
});
</script>

<?php require_once 'includes/footer.php'; ?>

