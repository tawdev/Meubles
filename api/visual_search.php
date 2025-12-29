<?php
/**
 * Visual Search API Endpoint
 * يتعامل مع طلبات البحث البصري من Frontend
 */

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once '../db.php';

// Python service URL
$PYTHON_SERVICE_URL = 'http://localhost:5000';

/**
 * Send request to Python service
 */
function callPythonService($endpoint, $data = null, $isFile = false, $usePost = true) {
    global $PYTHON_SERVICE_URL;
    
    $url = $PYTHON_SERVICE_URL . $endpoint;
    $ch = curl_init($url);
    
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 300); // Increase timeout for vector extraction (5 minutes)
    
    if ($isFile && $data) {
        // For file upload
        $cfile = new CURLFile($data['tmp_name'], $data['type'], $data['name']);
        $postData = ['image' => $cfile];
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
    } elseif ($usePost) {
        // For POST requests (even without data)
        curl_setopt($ch, CURLOPT_POST, true);
        if ($data) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        }
    }
    // If usePost is false, it will be a GET request (default curl behavior)
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    if ($error) {
        return ['error' => 'CURL Error: ' . $error];
    }
    
    $decoded = json_decode($response, true);
    return [
        'http_code' => $httpCode,
        'data' => $decoded ?: $response
    ];
}

// Handle different endpoints
$action = $_GET['action'] ?? '';

switch ($action) {
    case 'search':
        // Visual search
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
            exit;
        }
        
        if (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
            http_response_code(400);
            echo json_encode(['error' => 'No image file provided or upload error']);
            exit;
        }
        
        // Validate image
        $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp'];
        $fileType = $_FILES['image']['type'];
        
        if (!in_array($fileType, $allowedTypes)) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid image type. Allowed: JPEG, PNG, WebP']);
            exit;
        }
        
        // Check file size (max 10MB)
        if ($_FILES['image']['size'] > 10 * 1024 * 1024) {
            http_response_code(400);
            echo json_encode(['error' => 'Image too large. Maximum size: 10MB']);
            exit;
        }
        
        // Call Python service
        $result = callPythonService('/search', $_FILES['image'], true);
        
        if ($result['http_code'] !== 200) {
            http_response_code($result['http_code']);
            echo json_encode($result['data']);
            exit;
        }
        
        // Enhance results with database info
        if (isset($result['data']['results']) && is_array($result['data']['results'])) {
            foreach ($result['data']['results'] as &$product) {
                try {
                    $stmt = $pdo->prepare("
                        SELECT p.*, 
                               tc.name AS type_category_name,
                               c.name AS category_name
                        FROM products p
                        LEFT JOIN types_categories tc ON p.type_category_id = tc.id
                        LEFT JOIN categories c ON p.category_id = c.id
                        WHERE p.id = ?
                    ");
                    $stmt->execute([$product['product_id']]);
                    $dbProduct = $stmt->fetch();
                    
                    if ($dbProduct) {
                        $product['description'] = $dbProduct['description'] ?? '';
                        $product['type_category_name'] = $dbProduct['type_category_name'] ?? '';
                        $product['category_id'] = $dbProduct['category_id'] ?? null;
                        $product['type_category_id'] = $dbProduct['type_category_id'] ?? null;
                    }
                } catch (PDOException $e) {
                    // Continue without additional data
                }
            }
        }
        
        echo json_encode($result['data']);
        break;
        
    case 'health':
        // Health check
        $result = callPythonService('/health', null, false, false); // GET request
        http_response_code($result['http_code']);
        echo json_encode($result['data']);
        break;
        
    case 'rebuild_index':
        // Rebuild FAISS index (admin only - add auth check)
        // Allow both GET and POST for easy browser access
        $result = callPythonService('/rebuild_index', [], false, true); // POST request
        http_response_code($result['http_code']);
        echo json_encode($result['data']);
        break;
        
    case 'extract_vectors':
        // Extract vectors for all products (admin only - add auth check)
        // Allow both GET and POST for easy browser access
        $result = callPythonService('/extract_all_vectors', [], false, true); // POST request
        http_response_code($result['http_code']);
        echo json_encode($result['data']);
        break;
        
    default:
        http_response_code(404);
        echo json_encode(['error' => 'Endpoint not found']);
        break;
}

