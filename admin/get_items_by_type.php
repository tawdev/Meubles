<?php
require_once __DIR__ . '/../db.php';

header('Content-Type: application/json');

if (!isset($_GET['type_category_id']) || empty($_GET['type_category_id'])) {
    echo json_encode(['success' => false, 'message' => 'Type Category ID is required']);
    exit;
}

$typeCategoryId = intval($_GET['type_category_id']);

try {
    $stmt = $pdo->prepare("SELECT id, name, image FROM types_categories_items WHERE types_categories_id = ? ORDER BY name");
    $stmt->execute([$typeCategoryId]);
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['success' => true, 'items' => $items]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
?>