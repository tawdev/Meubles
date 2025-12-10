<?php
$pageTitle = "Panier";
require_once 'includes/header.php';
?>

<div class="container">
    <div class="cart-container">
        <h1 class="section-title">Mon Panier</h1>
        <div id="cart-items"></div>
        <div id="cart-summary"></div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    cartManager.renderCart();
});
</script>

<?php require_once 'includes/footer.php'; ?>

