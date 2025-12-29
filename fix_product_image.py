"""
Script to fix Product 38 image path
"""
import mysql.connector
import os

DB_CONFIG = {
    'host': 'localhost',
    'user': 'root',
    'password': '',
    'database': 'meubles_db'
}

# Connect to database
conn = mysql.connector.connect(**DB_CONFIG)
cursor = conn.cursor(dictionary=True)

# Get product 38
cursor.execute("SELECT id, name, image FROM products WHERE id = 38")
product = cursor.fetchone()

if product:
    print(f"Product 38: {product['name']}")
    print(f"Current image path: {product['image']}")
    
    # Search for similar file
    images_dir = r'C:\xampp\htdocs\MeublesMaison\images'
    if os.path.exists(images_dir):
        files = os.listdir(images_dir)
        # Look for files with "Buffet" in name
        buffet_files = [f for f in files if 'buffet' in f.lower() or 'Buffet' in f]
        
        if buffet_files:
            print(f"\nFound {len(buffet_files)} file(s) with 'Buffet':")
            for f in buffet_files:
                print(f"  - {f}")
            
            # Use the first matching file
            new_image_path = f"images/{buffet_files[0]}"
            full_path = os.path.join(images_dir, buffet_files[0])
            
            if os.path.exists(full_path):
                print(f"\n[OK] Updating image path to: {new_image_path}")
                cursor.execute("UPDATE products SET image = %s WHERE id = 38", (new_image_path,))
                conn.commit()
                print("[OK] Updated successfully!")
            else:
                print(f"[ERROR] File not found: {full_path}")
        else:
            print("\n[WARNING] No files with 'Buffet' found")
    else:
        print(f"[ERROR] Images directory not found: {images_dir}")
else:
    print("[ERROR] Product 38 not found")

cursor.close()
conn.close()

