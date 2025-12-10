<?php
require_once __DIR__ . '/../db.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

if (!isset($_GET['id'])) {
    header('Location: dashboard.php');
    exit;
}

$productId = $_GET['id'];

try {
    // Récupérer l'image pour la supprimer si nécessaire
    $stmt = $pdo->prepare("SELECT image FROM products WHERE id = ?");
    $stmt->execute([$productId]);
    $product = $stmt->fetch();
    
    if ($product) {
        // Supprimer le produit
        $stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
        $stmt->execute([$productId]);
        
        // Optionnel : supprimer l'image du serveur
        if ($product['image'] && file_exists('../' . $product['image'])) {
            unlink('../' . $product['image']);
        }
    }
    
    header('Location: dashboard.php?deleted=1');
    exit;
} catch (PDOException $e) {
    header('Location: dashboard.php?error=' . urlencode('Erreur lors de la suppression'));
    exit;
}
?>

