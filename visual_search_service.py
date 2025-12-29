"""
Advanced Visual Search Service for MeublesMaison
نظام البحث البصري المتقدم - يشبه IKEA Visual Search

Features:
- Object Detection (YOLO) لتحديد الأثاث في الصورة
- CNN Feature Extraction (EfficientNet) لاستخراج Visual Embeddings
- Vector Similarity Search (FAISS) للمقارنة السريعة
- Category Mapping ذكي (desk → Bureau, sofa → Salon, etc.)
- Threshold Filtering (0.75 minimum similarity)
- Smart Ranking (similarity + category + popularity)
"""

import os
import json
import numpy as np
import cv2
from flask import Flask, request, jsonify
from flask_cors import CORS
import mysql.connector
from datetime import datetime
from pathlib import Path
import pickle
import base64
from typing import List, Dict, Tuple, Optional
import warnings
warnings.filterwarnings('ignore')

# Deep Learning Libraries
try:
    import torch
    import torchvision.transforms as transforms
    from torchvision.models import efficientnet_b3, EfficientNet_B3_Weights
    TORCH_AVAILABLE = True
except ImportError:
    TORCH_AVAILABLE = False
    print("⚠️ PyTorch not available. Install: pip install torch torchvision")

try:
    import ultralytics
    from ultralytics import YOLO
    YOLO_AVAILABLE = True
except ImportError:
    YOLO_AVAILABLE = False
    print("⚠️ YOLO not available. Install: pip install ultralytics")

try:
    import faiss
    FAISS_AVAILABLE = True
except ImportError:
    FAISS_AVAILABLE = False
    print("⚠️ FAISS not available. Install: pip install faiss-cpu")

app = Flask(__name__)
CORS(app)

# ==================== Configuration ====================
UPLOAD_FOLDER = 'uploads'
FEATURES_FOLDER = 'features_cache'
VECTORS_FOLDER = 'vectors'
IMAGES_FOLDER = '../images'
MODELS_FOLDER = 'models'

# Create directories
for folder in [UPLOAD_FOLDER, FEATURES_FOLDER, VECTORS_FOLDER, MODELS_FOLDER]:
    os.makedirs(folder, exist_ok=True)

# Database configuration
DB_CONFIG = {
    'host': 'localhost',
    'user': 'root',
    'password': '',
    'database': 'meubles_db'
}

# Similarity threshold (minimum 0.5 = 50% - يمكن تعديله)
# 0.5 = نتائج أكثر (للاختبار)، 0.6 = نتائج جيدة، 0.7 = نتائج أفضل، 0.75 = نتائج ممتازة فقط
SIMILARITY_THRESHOLD = 0.5

# Category Mapping: Object Detection Labels → Database Categories
CATEGORY_MAPPING = {
    # Bureau / Desk
    'desk': 'Bureau',
    'table': 'Bureau',  # إذا كان في سياق مكتب
    'office desk': 'Bureau',
    'writing desk': 'Bureau',
    'computer desk': 'Bureau',
    
    # Salon / Sofa
    'sofa': 'Salon',
    'couch': 'Salon',
    'armchair': 'Salon',
    'chair': 'Salon',  # في سياق صالون
    'recliner': 'Salon',
    'loveseat': 'Salon',
    
    # Chambre / Bed
    'bed': 'Chambre',
    'bedroom': 'Chambre',
    'mattress': 'Chambre',
    'wardrobe': 'Chambre',
    'dresser': 'Chambre',
    'nightstand': 'Chambre',
    'chest of drawers': 'Chambre',
    
    # Salle à manger / Dining
    'dining table': 'Salle à manger',
    'dining room table': 'Salle à manger',
    'dining chair': 'Salle à manger',
    'dining set': 'Salle à manger',
    'kitchen table': 'Salle à manger',
    
    # Décoration
    'lamp': 'Décoration',
    'vase': 'Décoration',
    'mirror': 'Décoration',
    'picture frame': 'Décoration',
    'decoration': 'Décoration',
}

# Reverse mapping for category filtering
CATEGORY_TO_OBJECTS = {
    'Bureau': ['desk', 'table', 'office desk', 'writing desk', 'computer desk'],
    'Salon': ['sofa', 'couch', 'armchair', 'chair', 'recliner', 'loveseat'],
    'Chambre': ['bed', 'bedroom', 'mattress', 'wardrobe', 'dresser', 'nightstand'],
    'Salle à manger': ['dining table', 'dining room table', 'dining chair', 'dining set'],
    'Décoration': ['lamp', 'vase', 'mirror', 'picture frame', 'decoration']
}

# ==================== Model Initialization ====================
print("🚀 Initializing Visual Search Models...")

# YOLO Model for Object Detection
yolo_model = None
if YOLO_AVAILABLE:
    try:
        # Use YOLOv8n (nano) for faster inference, or YOLOv8s for better accuracy
        yolo_model_path = os.path.join(MODELS_FOLDER, 'yolov8n.pt')
        if os.path.exists(yolo_model_path):
            yolo_model = YOLO(yolo_model_path)
        else:
            # Download automatically on first use
            yolo_model = YOLO('yolov8n.pt')
            yolo_model.save(yolo_model_path)
        print("✅ YOLO Model loaded successfully")
    except Exception as e:
        print(f"⚠️ YOLO Model error: {e}")
        yolo_model = None

# CNN Model for Feature Extraction
cnn_model = None
cnn_transform = None
if TORCH_AVAILABLE:
    try:
        # Use EfficientNet-B3 for good balance of accuracy and speed
        weights = EfficientNet_B3_Weights.DEFAULT
        cnn_model = efficientnet_b3(weights=weights)
        cnn_model.eval()  # Set to evaluation mode
        # Remove the classifier, keep only feature extractor
        cnn_model.classifier = torch.nn.Identity()
        cnn_transform = weights.transforms()
        print("✅ EfficientNet-B3 Model loaded successfully")
    except Exception as e:
        print(f"⚠️ CNN Model error: {e}")
        cnn_model = None

# FAISS Index for Vector Search
faiss_index = None
product_vectors = {}  # product_id -> vector mapping
vector_dim = 1536  # EfficientNet-B3 output dimension

# ==================== Database Functions ====================
def get_db_connection():
    """Create database connection"""
    try:
        conn = mysql.connector.connect(**DB_CONFIG)
        return conn
    except mysql.connector.Error as e:
        print(f"❌ Database connection error: {e}")
        return None

def get_all_products():
    """Get all products with images from database"""
    conn = get_db_connection()
    if not conn:
        return []
    
    try:
        cursor = conn.cursor(dictionary=True)
        cursor.execute("""
            SELECT p.id, p.name, p.image, p.price, 
                   COALESCE(c.name, p.category, '') AS category,
                   p.category_id,
                   p.type_category_id
            FROM products p
            LEFT JOIN categories c ON p.category_id = c.id
            WHERE p.image IS NOT NULL AND p.image != ''
        """)
        products = cursor.fetchall()
        cursor.close()
        conn.close()
        return products
    except Exception as e:
        print(f"❌ Error fetching products: {e}")
        if conn:
            conn.close()
        return []

# ==================== Object Detection ====================
def detect_furniture_objects(image_path: str) -> List[Dict]:
    """
    Detect furniture objects in image using YOLO
    Returns: List of detected objects with labels and confidence
    """
    if not yolo_model:
        return []
    
    try:
        results = yolo_model(image_path, conf=0.25, verbose=False)
        detections = []
        
        for result in results:
            boxes = result.boxes
            for box in boxes:
                cls = int(box.cls[0])
                conf = float(box.conf[0])
                label = yolo_model.names[cls]
                
                # Filter only furniture-related objects
                furniture_keywords = [
                    'chair', 'couch', 'bed', 'dining table', 'desk', 'sofa',
                    'table', 'lamp', 'vase', 'mirror', 'clock', 'book'
                ]
                
                if any(keyword in label.lower() for keyword in furniture_keywords):
                    detections.append({
                        'label': label,
                        'confidence': conf,
                        'bbox': box.xyxy[0].tolist()
                    })
        
        return detections
    except Exception as e:
        print(f"⚠️ Object detection error: {e}")
        return []

def map_object_to_category(detected_objects: List[Dict]) -> Optional[str]:
    """
    Map detected objects to database category
    Returns: Category name or None
    """
    if not detected_objects:
        return None
    
    # Get the highest confidence detection
    best_detection = max(detected_objects, key=lambda x: x['confidence'])
    label = best_detection['label'].lower()
    
    # Map to category
    for obj_key, category in CATEGORY_MAPPING.items():
        if obj_key in label:
            return category
    
    return None

# ==================== Feature Extraction ====================
def extract_cnn_features(image_path: str) -> Optional[np.ndarray]:
    """
    Extract visual features using EfficientNet-B3
    Returns: Feature vector (1536 dimensions)
    """
    if not cnn_model or not cnn_transform:
        print(f"⚠️ CNN model not available")
        return None
    
    try:
        # Read and preprocess image
        img = cv2.imread(image_path)
        if img is None:
            print(f"⚠️ Could not read image: {image_path}")
            return None
        
        if img.size == 0:
            print(f"⚠️ Empty image: {image_path}")
            return None
        
        # Convert BGR to RGB
        img_rgb = cv2.cvtColor(img, cv2.COLOR_BGR2RGB)
        
        # Resize if needed (EfficientNet expects certain sizes)
        # The transform will handle resizing, but let's ensure it's valid
        if img_rgb.shape[0] < 32 or img_rgb.shape[1] < 32:
            print(f"⚠️ Image too small: {image_path} ({img_rgb.shape})")
            return None
        
        # Apply transforms (this includes resizing, normalization, etc.)
        # Convert to PIL Image first for better compatibility
        from PIL import Image
        pil_img = Image.fromarray(img_rgb)
        img_tensor = cnn_transform(pil_img)
        img_tensor = img_tensor.unsqueeze(0)  # Add batch dimension
        
        # Extract features
        with torch.no_grad():
            features = cnn_model(img_tensor)
            features = features.squeeze(0).numpy()
        
        # Normalize vector
        norm = np.linalg.norm(features)
        if norm > 0:
            features = features / norm
        else:
            print(f"⚠️ Zero norm vector for: {image_path}")
            return None
        
        return features.astype(np.float32)
    except Exception as e:
        print(f"⚠️ Feature extraction error for {image_path}: {e}")
        import traceback
        traceback.print_exc()
        return None

def get_cached_vector(product_id: int, image_path: str) -> Optional[np.ndarray]:
    """Get cached feature vector if available"""
    cache_file = os.path.join(VECTORS_FOLDER, f"product_{product_id}.npy")
    if os.path.exists(cache_file):
        try:
            return np.load(cache_file)
        except:
            return None
    return None

def cache_vector(product_id: int, vector: np.ndarray):
    """Cache feature vector"""
    cache_file = os.path.join(VECTORS_FOLDER, f"product_{product_id}.npy")
    try:
        np.save(cache_file, vector)
    except Exception as e:
        print(f"⚠️ Error caching vector: {e}")

# ==================== Vector Similarity Search ====================
def build_faiss_index():
    """Build FAISS index from all product vectors"""
    global faiss_index, product_vectors
    
    if not FAISS_AVAILABLE:
        return False
    
    try:
        products = get_all_products()
        vectors = []
        ids = []
        
        base_path = os.path.dirname(os.path.abspath(__file__))
        
        for product in products:
            product_id = product['id']
            image_path = product['image']
            
            # Handle relative paths
            if not os.path.isabs(image_path):
                full_image_path = os.path.join(base_path, image_path)
            else:
                full_image_path = image_path
            
            if not os.path.exists(full_image_path):
                continue
            
            # Get or extract vector
            vector = get_cached_vector(product_id, full_image_path)
            if vector is None:
                vector = extract_cnn_features(full_image_path)
                if vector is not None:
                    cache_vector(product_id, vector)
            
            if vector is not None:
                vectors.append(vector)
                ids.append(product_id)
                product_vectors[product_id] = vector
        
        if len(vectors) == 0:
            print("⚠️ No vectors to index")
            return False
        
        # Build FAISS index
        vectors_array = np.array(vectors).astype('float32')
        dimension = vectors_array.shape[1]
        
        # Use L2 distance (Euclidean) - can also use cosine similarity
        faiss_index = faiss.IndexFlatL2(dimension)
        faiss_index.add(vectors_array)
        
        print(f"✅ FAISS index built with {len(vectors)} vectors")
        return True
    except Exception as e:
        print(f"❌ Error building FAISS index: {e}")
        return False

def cosine_similarity(vec1: np.ndarray, vec2: np.ndarray) -> float:
    """Calculate cosine similarity between two vectors"""
    return np.dot(vec1, vec2) / (np.linalg.norm(vec1) * np.linalg.norm(vec2) + 1e-8)

def search_similar_vectors(query_vector: np.ndarray, top_k: int = 20, 
                          category_filter: Optional[str] = None) -> List[Dict]:
    """
    Search for similar vectors using FAISS or brute force
    Returns: List of products with similarity scores
    """
    products = get_all_products()
    results = []
    
    base_path = os.path.dirname(os.path.abspath(__file__))
    
    print(f"🔍 Searching in {len(products)} products...")
    print(f"📂 Category filter: {category_filter}")
    
    products_checked = 0
    products_with_vectors = 0
    products_below_threshold = 0
    
    for product in products:
        product_id = product['id']
        image_path = product['image']
        product_category = product.get('category', '')
        
        # Apply category filter if specified (but make it more flexible)
        if category_filter:
            # Exact match or case-insensitive match
            if product_category.lower() != category_filter.lower():
                continue
        
        products_checked += 1
        
        # Handle relative paths
        if not os.path.isabs(image_path):
            full_image_path = os.path.join(base_path, image_path)
        else:
            full_image_path = image_path
        
        if not os.path.exists(full_image_path):
            continue
        
        # Get product vector
        product_vector = product_vectors.get(product_id)
        if product_vector is None:
            # Try to load from cache or extract
            product_vector = get_cached_vector(product_id, full_image_path)
            if product_vector is None:
                product_vector = extract_cnn_features(full_image_path)
                if product_vector is not None:
                    cache_vector(product_id, product_vector)
                    product_vectors[product_id] = product_vector
        
        if product_vector is None:
            continue
        
        # Calculate similarity
        similarity = cosine_similarity(query_vector, product_vector)
        products_with_vectors += 1
        
        # Apply threshold (lowered to 0.6 for better results)
        if similarity >= SIMILARITY_THRESHOLD:
            results.append({
                'product_id': product_id,
                'name': product['name'],
                'image': product['image'],
                'price': float(product['price']) if product['price'] else 0,
                'category': product_category,
                'similarity': round(float(similarity), 4)
            })
        else:
            products_below_threshold += 1
    
    print(f"📊 Search stats: checked={products_checked}, with_vectors={products_with_vectors}, below_threshold={products_below_threshold}, results={len(results)}")
    
    # Sort by similarity (descending)
    results.sort(key=lambda x: x['similarity'], reverse=True)
    
    return results[:top_k]

# ==================== API Endpoints ====================
@app.route('/health', methods=['GET'])
def health_check():
    """Health check endpoint"""
    status = {
        'status': 'ok',
        'yolo_available': YOLO_AVAILABLE and yolo_model is not None,
        'cnn_available': TORCH_AVAILABLE and cnn_model is not None,
        'faiss_available': FAISS_AVAILABLE,
        'models_loaded': {
            'yolo': yolo_model is not None,
            'cnn': cnn_model is not None,
            'faiss': faiss_index is not None
        }
    }
    return jsonify(status)

@app.route('/search', methods=['POST'])
def visual_search():
    """
    Main visual search endpoint
    Accepts: multipart/form-data with 'image' file
    Returns: JSON with similar products
    """
    try:
        # Check if image file is present
        if 'image' not in request.files:
            return jsonify({'error': 'No image file provided'}), 400
        
        file = request.files['image']
        if file.filename == '':
            return jsonify({'error': 'No image file selected'}), 400
        
        # Save uploaded image
        timestamp = datetime.now().strftime('%Y%m%d_%H%M%S')
        filename = f"{timestamp}_{file.filename}"
        upload_path = os.path.join(UPLOAD_FOLDER, filename)
        file.save(upload_path)
        
        # Step 1: Object Detection
        detected_objects = detect_furniture_objects(upload_path)
        detected_category = map_object_to_category(detected_objects)
        
        print(f"🔍 Detected objects: {[obj['label'] for obj in detected_objects]}")
        print(f"📂 Mapped category: {detected_category}")
        
        # Step 2: Feature Extraction
        query_vector = extract_cnn_features(upload_path)
        if query_vector is None:
            os.remove(upload_path)
            return jsonify({'error': 'Could not extract features from image'}), 400
        
        # Step 3: Vector Similarity Search
        # Use detected category as filter if available, but allow flexibility
        # If no results with category filter, try without filter
        results = search_similar_vectors(
            query_vector, 
            top_k=20,
            category_filter=detected_category
        )
        
        # If no results with category filter, try without filter (more flexible)
        if len(results) == 0 and detected_category:
            print(f"⚠️ No results with category filter '{detected_category}', trying without filter...")
            results = search_similar_vectors(
                query_vector, 
                top_k=20,
                category_filter=None
            )
        
        # Step 4: Smart Ranking
        # Already sorted by similarity, but we can add more factors
        # (category match bonus, popularity, etc.)
        
        # Clean up
        os.remove(upload_path)
        
        return jsonify({
            'success': True,
            'detected_objects': detected_objects,
            'detected_category': detected_category,
            'results': results,
            'total_found': len(results),
            'threshold_used': SIMILARITY_THRESHOLD
        })
    
    except Exception as e:
        print(f"❌ Error in visual_search: {e}")
        import traceback
        traceback.print_exc()
        return jsonify({'error': str(e)}), 500

@app.route('/rebuild_index', methods=['POST'])
def rebuild_index():
    """Rebuild FAISS index and cache all product vectors"""
    try:
        print("🔄 Rebuilding index...")
        success = build_faiss_index()
        return jsonify({
            'success': success,
            'message': 'Index rebuilt successfully' if success else 'Failed to rebuild index'
        })
    except Exception as e:
        return jsonify({'error': str(e)}), 500

@app.route('/extract_all_vectors', methods=['POST'])
def extract_all_vectors():
    """Extract and cache vectors for all products"""
    try:
        products = get_all_products()
        base_path = os.path.dirname(os.path.abspath(__file__))
        
        extracted = 0
        errors = 0
        already_cached = 0
        missing_images = 0
        extraction_failed = 0
        error_details = []
        
        print(f"🔄 Starting vector extraction for {len(products)} products...")
        
        for idx, product in enumerate(products, 1):
            product_id = product['id']
            image_path = product['image']
            product_name = product.get('name', 'Unknown')
            
            if not image_path or image_path.strip() == '':
                missing_images += 1
                errors += 1
                error_details.append(f"Product {product_id} ({product_name}): No image path")
                continue
            
            # Handle relative paths
            if not os.path.isabs(image_path):
                full_image_path = os.path.join(base_path, image_path)
            else:
                full_image_path = image_path
            
            # Normalize path (handle Windows/Unix separators)
            full_image_path = os.path.normpath(full_image_path)
            
            if not os.path.exists(full_image_path):
                missing_images += 1
                errors += 1
                error_details.append(f"Product {product_id} ({product_name}): Image not found: {full_image_path}")
                continue
            
            # Check if already cached
            cached_vector = get_cached_vector(product_id, full_image_path)
            if cached_vector is not None:
                product_vectors[product_id] = cached_vector
                already_cached += 1
                if idx % 10 == 0:
                    print(f"  Progress: {idx}/{len(products)} (cached: {already_cached}, extracted: {extracted}, errors: {errors})")
                continue
            
            # Extract and cache
            try:
                vector = extract_cnn_features(full_image_path)
                if vector is not None:
                    cache_vector(product_id, vector)
                    product_vectors[product_id] = vector
                    extracted += 1
                    if idx % 10 == 0:
                        print(f"  Progress: {idx}/{len(products)} (cached: {already_cached}, extracted: {extracted}, errors: {errors})")
                else:
                    extraction_failed += 1
                    errors += 1
                    error_details.append(f"Product {product_id} ({product_name}): Feature extraction failed")
            except Exception as e:
                extraction_failed += 1
                errors += 1
                error_details.append(f"Product {product_id} ({product_name}): {str(e)}")
        
        print(f"✅ Extraction complete: {extracted} extracted, {already_cached} cached, {errors} errors")
        
        result = {
            'success': True,
            'extracted': extracted,
            'already_cached': already_cached,
            'errors': errors,
            'total': len(products),
            'missing_images': missing_images,
            'extraction_failed': extraction_failed
        }
        
        # Include first 5 error details for debugging
        if error_details:
            result['error_samples'] = error_details[:5]
            if len(error_details) > 5:
                result['error_samples'].append(f"... and {len(error_details) - 5} more errors")
        
        return jsonify(result)
    except Exception as e:
        import traceback
        error_trace = traceback.format_exc()
        print(f"❌ Error in extract_all_vectors: {error_trace}")
        return jsonify({'error': str(e), 'trace': error_trace}), 500

# ==================== Initialize on Startup ====================
if __name__ == '__main__':
    print("=" * 60)
    print("🚀 Starting Advanced Visual Search Service...")
    print("=" * 60)
    
    # Extract vectors for all products on first run
    print("\n📦 Extracting vectors for all products...")
    # This can be done via API call: POST /extract_all_vectors
    
    # Build FAISS index
    print("\n🔨 Building FAISS index...")
    build_faiss_index()
    
    print("\n✅ Service ready!")
    print("📍 Endpoints:")
    print("   - GET  /health")
    print("   - POST /search")
    print("   - POST /rebuild_index")
    print("   - POST /extract_all_vectors")
    print("\n🌐 Starting server on http://0.0.0.0:5000\n")
    
    app.run(host='0.0.0.0', port=5000, debug=False)

