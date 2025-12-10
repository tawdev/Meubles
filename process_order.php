<?php
require_once 'db.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Méthode non autorisée']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['name']) || !isset($data['email']) || !isset($data['phone']) || !isset($data['address']) || !isset($data['items'])) {
    echo json_encode(['success' => false, 'message' => 'Données manquantes']);
    exit;
}

try {
    $pdo->beginTransaction();
    
    // Créer la commande
    $phone = $data['phone'] ?? '';
    $notes = $data['notes'] ?? '';
    $subtotal = $data['subtotal'] ?? $data['total'];
    $shipping = $data['shipping'] ?? 0;
    $total = $data['total'] ?? ($subtotal + $shipping);
    
    // Vérifier si la colonne phone existe dans orders
    try {
        $checkPhone = $pdo->query("SHOW COLUMNS FROM orders LIKE 'customer_phone'");
        $hasPhone = $checkPhone->rowCount() > 0;
        
        if ($hasPhone) {
            $stmt = $pdo->prepare("INSERT INTO orders (customer_name, customer_email, customer_phone, customer_address, total_amount, status, notes) VALUES (?, ?, ?, ?, ?, 'En attente', ?)");
            $stmt->execute([
                $data['name'],
                $data['email'],
                $phone,
                $data['address'],
                $total,
                $notes
            ]);
        } else {
            $stmt = $pdo->prepare("INSERT INTO orders (customer_name, customer_email, customer_address, total_amount, status) VALUES (?, ?, ?, ?, 'En attente')");
            $stmt->execute([
                $data['name'],
                $data['email'],
                $data['address'],
                $total
            ]);
        }
    } catch (PDOException $e) {
        // Si erreur, utiliser la version sans phone
        $stmt = $pdo->prepare("INSERT INTO orders (customer_name, customer_email, customer_address, total_amount, status) VALUES (?, ?, ?, ?, 'En attente')");
        $stmt->execute([
            $data['name'],
            $data['email'],
            $data['address'],
            $total
        ]);
    }
    
    $orderId = $pdo->lastInsertId();
    
    // Ajouter les articles de commande
    $stmt = $pdo->prepare("INSERT INTO order_items (order_id, product_id, product_name, quantity, price) VALUES (?, ?, ?, ?, ?)");
    
    foreach ($data['items'] as $item) {
        $stmt->execute([
            $orderId,
            $item['id'],
            $item['name'],
            $item['quantity'],
            $item['price']
        ]);
        
        // Mettre à jour le stock
        $updateStmt = $pdo->prepare("UPDATE products SET stock = stock - ? WHERE id = ?");
        $updateStmt->execute([$item['quantity'], $item['id']]);
    }
    
    $pdo->commit();
    echo json_encode(['success' => true, 'message' => 'Commande créée avec succès', 'order_id' => $orderId]);
    
} catch (Exception $e) {
    $pdo->rollBack();
    echo json_encode(['success' => false, 'message' => 'Erreur : ' . $e->getMessage()]);
}
?>

