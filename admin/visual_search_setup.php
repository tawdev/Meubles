<?php
/**
 * Visual Search Setup Page
 * صفحة إعداد نظام البحث البصري
 */

$pageTitle = "إعداد Visual Search";
require_once 'includes/header.php';

// Python service URL
$PYTHON_SERVICE_URL = 'http://localhost:5000';

// Function to call Python service
function callPythonService($endpoint, $usePost = true) {
    global $PYTHON_SERVICE_URL;
    
    $url = $PYTHON_SERVICE_URL . $endpoint;
    $ch = curl_init($url);
    
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 300);
    
    if ($usePost) {
        curl_setopt($ch, CURLOPT_POST, true);
    }
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    if ($error) {
        return ['error' => 'CURL Error: ' . $error, 'http_code' => 500];
    }
    
    $decoded = json_decode($response, true);
    return [
        'http_code' => $httpCode,
        'data' => $decoded ?: $response
    ];
}

// Handle actions
$action = $_GET['action'] ?? '';
$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];
    
    switch ($action) {
        case 'extract_vectors':
            $result = callPythonService('/extract_all_vectors', true);
            if ($result['http_code'] === 200 && isset($result['data']['success'])) {
                $message = "تم استخراج " . $result['data']['extracted'] . " vectors بنجاح!";
                $messageType = 'success';
            } else {
                $message = "خطأ: " . ($result['data']['error'] ?? 'Unknown error');
                $messageType = 'error';
            }
            break;
            
        case 'rebuild_index':
            $result = callPythonService('/rebuild_index', true);
            if ($result['http_code'] === 200 && isset($result['data']['success'])) {
                $message = "تم بناء FAISS index بنجاح!";
                $messageType = 'success';
            } else {
                $message = "خطأ: " . ($result['data']['error'] ?? 'Unknown error');
                $messageType = 'error';
            }
            break;
    }
}

// Check service health
$healthResult = callPythonService('/health', false);
$serviceStatus = $healthResult['http_code'] === 200 && isset($healthResult['data']['status']) && $healthResult['data']['status'] === 'ok';
?>

<div class="container" style="max-width: 1000px; margin: 2rem auto; padding: 0 1rem;">
    <h1 style="color: var(--primary-color); margin-bottom: 2rem;">🔍 إعداد Visual Search</h1>
    
    <?php if ($message): ?>
        <div style="padding: 1rem; margin-bottom: 2rem; border-radius: 8px; background: <?php echo $messageType === 'success' ? '#d4edda' : '#f8d7da'; ?>; color: <?php echo $messageType === 'success' ? '#155724' : '#721c24'; ?>; border: 1px solid <?php echo $messageType === 'success' ? '#c3e6cb' : '#f5c6cb'; ?>;">
            <?php echo htmlspecialchars($message); ?>
        </div>
    <?php endif; ?>
    
    <!-- Service Status -->
    <div style="background: white; border-radius: 12px; padding: 2rem; margin-bottom: 2rem; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
        <h2 style="color: var(--primary-color); margin-bottom: 1rem;">حالة الخدمة</h2>
        
        <?php if ($serviceStatus): ?>
            <div style="padding: 1rem; background: #d4edda; border-radius: 8px; color: #155724; margin-bottom: 1rem;">
                ✅ <strong>Python Service يعمل</strong>
            </div>
            
            <?php if (isset($healthResult['data'])): ?>
                <div style="background: #f8f9fa; padding: 1rem; border-radius: 8px; font-family: monospace; font-size: 0.9rem;">
                    <strong>تفاصيل:</strong><br>
                    - YOLO: <?php echo $healthResult['data']['yolo_available'] ? '✅' : '❌'; ?><br>
                    - CNN: <?php echo $healthResult['data']['cnn_available'] ? '✅' : '❌'; ?><br>
                    - FAISS: <?php echo $healthResult['data']['faiss_available'] ? '✅' : '❌'; ?>
                </div>
            <?php endif; ?>
        <?php else: ?>
            <div style="padding: 1rem; background: #f8d7da; border-radius: 8px; color: #721c24;">
                ❌ <strong>Python Service غير متاح</strong><br>
                <small>تأكد من تشغيل: <code>python visual_search_service.py</code></small>
            </div>
        <?php endif; ?>
    </div>
    
    <!-- Actions -->
    <div style="background: white; border-radius: 12px; padding: 2rem; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
        <h2 style="color: var(--primary-color); margin-bottom: 1.5rem;">الإجراءات</h2>
        
        <div style="display: grid; gap: 1.5rem;">
            <!-- Extract Vectors -->
            <div style="border: 1px solid #ddd; border-radius: 8px; padding: 1.5rem;">
                <h3 style="margin-bottom: 0.5rem;">1. استخراج Vectors</h3>
                <p style="color: #666; margin-bottom: 1rem; font-size: 0.95rem;">
                    استخراج visual embeddings لجميع المنتجات. هذا قد يستغرق وقتاً حسب عدد المنتجات.
                </p>
                <form method="POST" style="margin: 0;">
                    <input type="hidden" name="action" value="extract_vectors">
                    <button type="submit" 
                            style="padding: 0.875rem 2rem; background: var(--primary-color); color: white; border: none; border-radius: 8px; font-weight: 600; cursor: pointer;"
                            <?php echo !$serviceStatus ? 'disabled' : ''; ?>>
                        🔄 استخراج Vectors
                    </button>
                </form>
            </div>
            
            <!-- Rebuild Index -->
            <div style="border: 1px solid #ddd; border-radius: 8px; padding: 1.5rem;">
                <h3 style="margin-bottom: 0.5rem;">2. بناء FAISS Index</h3>
                <p style="color: #666; margin-bottom: 1rem; font-size: 0.95rem;">
                    بناء FAISS index للبحث السريع. يجب تنفيذ هذا بعد استخراج vectors.
                </p>
                <form method="POST" style="margin: 0;">
                    <input type="hidden" name="action" value="rebuild_index">
                    <button type="submit" 
                            style="padding: 0.875rem 2rem; background: #28a745; color: white; border: none; border-radius: 8px; font-weight: 600; cursor: pointer;"
                            <?php echo !$serviceStatus ? 'disabled' : ''; ?>>
                        🔨 بناء Index
                    </button>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Instructions -->
    <div style="background: #e7f3ff; border-left: 4px solid #2196F3; padding: 1.5rem; border-radius: 8px; margin-top: 2rem;">
        <h3 style="color: #1976D2; margin-bottom: 1rem;">📝 تعليمات</h3>
        <ol style="color: #333; line-height: 1.8;">
            <li>تأكد من تشغيل Python service: <code>python visual_search_service.py</code></li>
            <li>اضغط "استخراج Vectors" لاستخراج embeddings لجميع المنتجات</li>
            <li>اضغط "بناء Index" لبناء FAISS index</li>
            <li>الآن يمكنك استخدام <a href="../visual_search.php" style="color: #1976D2;">Visual Search</a></li>
        </ol>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>

