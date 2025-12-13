<?php
require_once __DIR__ . '/../db.php';

header('Content-Type: application/json');

if (!isset($_GET['category_id']) || empty($_GET['category_id'])) {
    echo json_encode(['success' => false, 'message' => 'Category ID is required']);
    exit;
}

$categoryId = intval($_GET['category_id']);

try {
    $stmt = $pdo->prepare("SELECT id, name FROM types_categories WHERE category_id = ? ORDER BY name");
    $stmt->execute([$categoryId]);
    $types = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode(['success' => true, 'types' => $types]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
?>

