-- إنشاء جدول types_categories
CREATE TABLE IF NOT EXISTS types_categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    category_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE,
    INDEX idx_category_id (category_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- إضافة الأعمدة الجديدة إلى جدول products (MySQL ne supporte pas IF NOT EXISTS pour ALTER TABLE)
-- Vérifier d'abord si les colonnes existent avant de les ajouter
ALTER TABLE products 
ADD COLUMN category_id INT NULL,
ADD COLUMN type_category_id INT NULL;

-- Ajouter les index
ALTER TABLE products 
ADD INDEX idx_category_id (category_id),
ADD INDEX idx_type_category_id (type_category_id);

-- Ajouter les clés étrangères (après création de la table types_categories)
ALTER TABLE products 
ADD FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL,
ADD FOREIGN KEY (type_category_id) REFERENCES types_categories(id) ON DELETE SET NULL;

-- إدراج بعض أنواع الفئات كمثال
-- Note: Exécutez cette partie après avoir vérifié que les catégories existent
INSERT IGNORE INTO types_categories (name, category_id) 
SELECT 'Canapé', id FROM categories WHERE name = 'Salon' LIMIT 1
UNION ALL
SELECT 'Table basse', id FROM categories WHERE name = 'Salon' LIMIT 1
UNION ALL
SELECT 'Armoire', id FROM categories WHERE name = 'Chambre' LIMIT 1
UNION ALL
SELECT 'Lit', id FROM categories WHERE name = 'Chambre' LIMIT 1
UNION ALL
SELECT 'Table à manger', id FROM categories WHERE name = 'Salle à manger' LIMIT 1
UNION ALL
SELECT 'Chaise', id FROM categories WHERE name = 'Salle à manger' LIMIT 1
UNION ALL
SELECT 'Bureau en bois', id FROM categories WHERE name = 'Bureau' LIMIT 1
UNION ALL
SELECT 'Chaise de bureau', id FROM categories WHERE name = 'Bureau' LIMIT 1
UNION ALL
SELECT 'Tableau', id FROM categories WHERE name = 'Décoration' LIMIT 1
UNION ALL
SELECT 'Vase', id FROM categories WHERE name = 'Décoration' LIMIT 1;

