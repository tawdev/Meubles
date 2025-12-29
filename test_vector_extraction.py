"""
Test script to debug vector extraction
"""
import os
import sys

# Add current directory to path
sys.path.insert(0, os.path.dirname(os.path.abspath(__file__)))

from visual_search_service import get_all_products, extract_cnn_features, get_cached_vector, cache_vector

# Get first product
products = get_all_products()
print(f"Total products: {len(products)}")

if products:
    product = products[0]
    print(f"\nTesting product: {product['name']}")
    print(f"Image path: {product['image']}")
    
    # Build full path
    base_path = os.path.dirname(os.path.abspath(__file__))
    image_path = product['image']
    
    if not os.path.isabs(image_path):
        full_image_path = os.path.join(base_path, image_path)
    else:
        full_image_path = image_path
    
    full_image_path = os.path.normpath(full_image_path)
    
    print(f"Full path: {full_image_path}")
    print(f"Exists: {os.path.exists(full_image_path)}")
    
    if os.path.exists(full_image_path):
        print("\n🔍 Testing feature extraction...")
        vector = extract_cnn_features(full_image_path)
        
        if vector is not None:
            print(f"✅ Vector extracted successfully!")
            print(f"   Shape: {vector.shape}")
            print(f"   Norm: {sum(vector**2)**0.5}")
        else:
            print("❌ Failed to extract vector")
    else:
        print(f"❌ Image file not found!")
        
        # Try to find similar files
        images_dir = os.path.join(base_path, 'images')
        if os.path.exists(images_dir):
            print(f"\n📁 Checking images directory: {images_dir}")
            files = os.listdir(images_dir)
            print(f"   Found {len(files)} files")
            if files:
                print(f"   First 5 files: {files[:5]}")
else:
    print("No products found!")

