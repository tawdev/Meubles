"""
Image Similarity Search Service using OpenCV
Service Python pour la recherche de similarité d'images de produits
"""

import cv2
import numpy as np
import os
import json
import base64
from flask import Flask, request, jsonify
from flask_cors import CORS
import pickle
from pathlib import Path
import mysql.connector
from datetime import datetime

app = Flask(__name__)
CORS(app)  # Enable CORS for PHP requests

# Configuration
UPLOAD_FOLDER = 'uploads'
FEATURES_FOLDER = 'features_cache'
IMAGES_FOLDER = '../images'  # Relative to Python script location

# Create necessary directories
os.makedirs(UPLOAD_FOLDER, exist_ok=True)
os.makedirs(FEATURES_FOLDER, exist_ok=True)

# Database configuration (adjust according to your db.php)
DB_CONFIG = {
    'host': 'localhost',
    'user': 'root',
    'password': '',
    'database': 'meubles_db'
}

# Initialize ORB detector
ORB_DETECTOR = cv2.ORB_create(nfeatures=1000)
# Alternative: SIFT (requires opencv-contrib-python)
# SIFT_DETECTOR = cv2.SIFT_create(nfeatures=1000)

def get_db_connection():
    """Create database connection"""
    try:
        conn = mysql.connector.connect(**DB_CONFIG)
        return conn
    except mysql.connector.Error as e:
        print(f"Database connection error: {e}")
        return None

def extract_features(image_path):
    """
    Extract features from an image using ORB detector
    Returns: keypoints and descriptors
    """
    try:
        # Read image
        img = cv2.imread(image_path)
        if img is None:
            return None, None
        
        # Convert to grayscale
        gray = cv2.cvtColor(img, cv2.COLOR_BGR2GRAY)
        
        # Detect keypoints and compute descriptors
        keypoints, descriptors = ORB_DETECTOR.detectAndCompute(gray, None)
        
        return keypoints, descriptors
    except Exception as e:
        print(f"Error extracting features: {e}")
        return None, None

def calculate_similarity(descriptors1, descriptors2):
    """
    Calculate similarity between two sets of descriptors
    Returns: similarity score (0-100)
    """
    if descriptors1 is None or descriptors2 is None:
        return 0.0
    
    if len(descriptors1) == 0 or len(descriptors2) == 0:
        return 0.0
    
    # Use Brute Force Matcher with Hamming distance (for ORB)
    bf = cv2.BFMatcher(cv2.NORM_HAMMING, crossCheck=False)
    
    # Match descriptors
    matches = bf.knnMatch(descriptors1, descriptors2, k=2)
    
    # Apply ratio test (Lowe's ratio test)
    good_matches = []
    for match_pair in matches:
        if len(match_pair) == 2:
            m, n = match_pair
            if m.distance < 0.75 * n.distance:
                good_matches.append(m)
    
    # Calculate similarity percentage
    if len(descriptors1) > 0 and len(descriptors2) > 0:
        # Normalize by the minimum number of features
        min_features = min(len(descriptors1), len(descriptors2))
        similarity = (len(good_matches) / min_features) * 100 if min_features > 0 else 0
    else:
        similarity = 0.0
    
    return min(100.0, max(0.0, similarity))

def get_product_images_from_db():
    """Get all product images from database"""
    conn = get_db_connection()
    if not conn:
        return []
    
    try:
        cursor = conn.cursor(dictionary=True)
        # Try to get products with category name (supports both old and new schema)
        cursor.execute("""
            SELECT p.id, p.name, p.image, p.price, 
                   COALESCE(c.name, p.category, '') AS category
            FROM products p
            LEFT JOIN categories c ON p.category_id = c.id
            WHERE p.image IS NOT NULL AND p.image != ''
        """)
        products = cursor.fetchall()
        cursor.close()
        conn.close()
        return products
    except Exception as e:
        # Fallback to simple query if JOIN fails (old schema)
        try:
            cursor = conn.cursor(dictionary=True)
            cursor.execute("SELECT id, name, image, price, category FROM products WHERE image IS NOT NULL AND image != ''")
            products = cursor.fetchall()
            cursor.close()
            conn.close()
            return products
        except Exception as e2:
            print(f"Error fetching products: {e2}")
            if conn:
                conn.close()
            return []

def get_cached_features(image_path):
    """Get cached features if available"""
    cache_file = os.path.join(FEATURES_FOLDER, f"{os.path.basename(image_path)}.pkl")
    if os.path.exists(cache_file):
        try:
            with open(cache_file, 'rb') as f:
                return pickle.load(f)
        except:
            return None
    return None

def cache_features(image_path, descriptors):
    """Cache features for faster future access"""
    cache_file = os.path.join(FEATURES_FOLDER, f"{os.path.basename(image_path)}.pkl")
    try:
        with open(cache_file, 'wb') as f:
            pickle.dump(descriptors, f)
    except Exception as e:
        print(f"Error caching features: {e}")

@app.route('/health', methods=['GET'])
def health_check():
    """Health check endpoint"""
    return jsonify({'status': 'ok', 'message': 'Image Similarity Service is running'})

@app.route('/search', methods=['POST'])
def search_similar_images():
    """
    Main endpoint for image similarity search
    Accepts: multipart/form-data with 'image' file
    Returns: JSON with similar products and similarity scores
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
        
        # Extract features from uploaded image
        print(f"Extracting features from uploaded image: {upload_path}")
        query_keypoints, query_descriptors = extract_features(upload_path)
        
        if query_descriptors is None or len(query_descriptors) == 0:
            os.remove(upload_path)  # Clean up
            return jsonify({'error': 'Could not extract features from image'}), 400
        
        # Get all product images from database
        products = get_product_images_from_db()
        
        if not products:
            os.remove(upload_path)  # Clean up
            return jsonify({'error': 'No products found in database'}), 404
        
        # Compare with each product image
        results = []
        base_path = os.path.dirname(os.path.abspath(__file__))
        
        for product in products:
            image_path = product['image']
            
            # Handle relative paths
            if not os.path.isabs(image_path):
                full_image_path = os.path.join(base_path, image_path)
            else:
                full_image_path = image_path
            
            # Check if image file exists
            if not os.path.exists(full_image_path):
                continue
            
            # Try to get cached features
            cached_descriptors = get_cached_features(full_image_path)
            
            if cached_descriptors is not None:
                product_descriptors = cached_descriptors
            else:
                # Extract features
                product_keypoints, product_descriptors = extract_features(full_image_path)
                if product_descriptors is not None and len(product_descriptors) > 0:
                    # Cache the features
                    cache_features(full_image_path, product_descriptors)
                else:
                    continue
            
            # Calculate similarity
            similarity = calculate_similarity(query_descriptors, product_descriptors)
            
            if similarity > 0:  # Only include products with some similarity
                results.append({
                    'product_id': product['id'],
                    'name': product['name'],
                    'image': product['image'],
                    'price': float(product['price']) if product['price'] else 0,
                    'category': product.get('category', ''),
                    'similarity': round(similarity, 2)
                })
        
        # Sort by similarity (descending)
        results.sort(key=lambda x: x['similarity'], reverse=True)
        
        # Limit results (top 10)
        results = results[:10]
        
        # Clean up uploaded file
        os.remove(upload_path)
        
        return jsonify({
            'success': True,
            'results': results,
            'total_found': len(results)
        })
    
    except Exception as e:
        print(f"Error in search_similar_images: {e}")
        return jsonify({'error': str(e)}), 500

@app.route('/rebuild_cache', methods=['POST'])
def rebuild_cache():
    """
    Rebuild feature cache for all product images
    Useful for initial setup or after adding new products
    """
    try:
        products = get_product_images_from_db()
        base_path = os.path.dirname(os.path.abspath(__file__))
        
        cached_count = 0
        error_count = 0
        
        for product in products:
            image_path = product['image']
            
            if not os.path.isabs(image_path):
                full_image_path = os.path.join(base_path, image_path)
            else:
                full_image_path = image_path
            
            if not os.path.exists(full_image_path):
                error_count += 1
                continue
            
            # Extract and cache features
            keypoints, descriptors = extract_features(full_image_path)
            if descriptors is not None and len(descriptors) > 0:
                cache_features(full_image_path, descriptors)
                cached_count += 1
            else:
                error_count += 1
        
        return jsonify({
            'success': True,
            'cached': cached_count,
            'errors': error_count,
            'total': len(products)
        })
    
    except Exception as e:
        return jsonify({'error': str(e)}), 500

if __name__ == '__main__':
    print("Starting Image Similarity Search Service...")
    print("Make sure you have installed all requirements: pip install -r requirements.txt")
    app.run(host='0.0.0.0', port=5000, debug=True)

