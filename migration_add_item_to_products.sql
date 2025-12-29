-- Ajouter la colonne types_categories_items_id à la table products
ALTER TABLE products ADD COLUMN types_categories_items_id INT DEFAULT NULL AFTER type_category_id;

-- Ajouter la contrainte de clé étrangère
ALTER TABLE products 
ADD CONSTRAINT fk_product_item 
FOREIGN KEY (types_categories_items_id) 
REFERENCES types_categories_items(id) 
ON DELETE SET NULL 
ON UPDATE CASCADE;
