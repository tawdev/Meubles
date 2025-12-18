-- Script pour insérer tous les types de catégories
-- Exécutez ce script dans phpMyAdmin après avoir créé la table types_categories

-- S'assurer que toutes les catégories existent
INSERT INTO categories (name, description, icon) VALUES
('Bureau', 'Meubles de bureau : bureaux, chaises, étagères', '🗄️'),
('Chambre', 'Meubles pour la chambre : lits, armoires, commodes', '🛏️'),
('Cuisine', 'Meubles pour la cuisine : tables, chaises, buffets, îlots', '🍳'),
('Décoration', 'Éléments de décoration : étagères, miroirs, accessoires', '🖼️'),
('Placard', 'Meubles de rangement : placards, dressings, penderies', '🗄️'),
('Salle à manger', 'Meubles pour la salle à manger : tables, chaises, buffets', '🍽️'),
('Salon', 'Meubles pour le salon : canapés, tables basses, fauteuils', '🛋️')
ON DUPLICATE KEY UPDATE name=name;

INSERT IGNORE INTO types_categories (name, category_id) 
SELECT 'Bureau droit', id FROM categories WHERE name = 'Bureau' LIMIT 1;

INSERT IGNORE INTO types_categories (name, category_id) 
SELECT 'Bureau d''angle', id FROM categories WHERE name = 'Bureau' LIMIT 1;

INSERT IGNORE INTO types_categories (name, category_id) 
SELECT 'Chaise de bureau', id FROM categories WHERE name = 'Bureau' LIMIT 1;

INSERT IGNORE INTO types_categories (name, category_id) 
SELECT 'Fauteuil de bureau', id FROM categories WHERE name = 'Bureau' LIMIT 1;

INSERT IGNORE INTO types_categories (name, category_id) 
SELECT 'Étagère de bureau', id FROM categories WHERE name = 'Bureau' LIMIT 1;

INSERT IGNORE INTO types_categories (name, category_id) 
SELECT 'Caisson de rangement', id FROM categories WHERE name = 'Bureau' LIMIT 1;

-- Nouveaux types Bureau
INSERT IGNORE INTO types_categories (name, category_id) 
SELECT 'Bureau sur mesure', id FROM categories WHERE name = 'Bureau' LIMIT 1;

INSERT IGNORE INTO types_categories (name, category_id) 
SELECT 'Bureau moderne', id FROM categories WHERE name = 'Bureau' LIMIT 1;

INSERT IGNORE INTO types_categories (name, category_id) 
SELECT 'Bureau classique', id FROM categories WHERE name = 'Bureau' LIMIT 1;

-- Insérer les types de catégories pour Chambre
INSERT IGNORE INTO types_categories (name, category_id) 
SELECT 'Lit simple', id FROM categories WHERE name = 'Chambre' LIMIT 1;

INSERT IGNORE INTO types_categories (name, category_id) 
SELECT 'Lit double', id FROM categories WHERE name = 'Chambre' LIMIT 1;

INSERT IGNORE INTO types_categories (name, category_id) 
SELECT 'Armoire', id FROM categories WHERE name = 'Chambre' LIMIT 1;

INSERT IGNORE INTO types_categories (name, category_id) 
SELECT 'Commode', id FROM categories WHERE name = 'Chambre' LIMIT 1;

INSERT IGNORE INTO types_categories (name, category_id) 
SELECT 'Table de chevet', id FROM categories WHERE name = 'Chambre' LIMIT 1;

INSERT IGNORE INTO types_categories (name, category_id) 
SELECT 'Coiffeuse', id FROM categories WHERE name = 'Chambre' LIMIT 1;

INSERT IGNORE INTO types_categories (name, category_id) 
SELECT 'Tête de lit', id FROM categories WHERE name = 'Chambre' LIMIT 1;

-- Insérer les types de catégories pour Cuisine
INSERT IGNORE INTO types_categories (name, category_id) 
SELECT 'Meuble bas de cuisine', id FROM categories WHERE name = 'Cuisine' LIMIT 1;

INSERT IGNORE INTO types_categories (name, category_id) 
SELECT 'Meuble haut de cuisine', id FROM categories WHERE name = 'Cuisine' LIMIT 1;

INSERT IGNORE INTO types_categories (name, category_id) 
SELECT 'Table de cuisine', id FROM categories WHERE name = 'Cuisine' LIMIT 1;

INSERT IGNORE INTO types_categories (name, category_id) 
SELECT 'Chaise de cuisine', id FROM categories WHERE name = 'Cuisine' LIMIT 1;

INSERT IGNORE INTO types_categories (name, category_id) 
SELECT 'Îlot de cuisine', id FROM categories WHERE name = 'Cuisine' LIMIT 1;

-- Insérer les types de catégories pour Décoration
INSERT IGNORE INTO types_categories (name, category_id) 
SELECT 'Étagère murale', id FROM categories WHERE name = 'Décoration' LIMIT 1;

INSERT IGNORE INTO types_categories (name, category_id) 
SELECT 'Miroir décoratif', id FROM categories WHERE name = 'Décoration' LIMIT 1;

INSERT IGNORE INTO types_categories (name, category_id) 
SELECT 'Cadre mural', id FROM categories WHERE name = 'Décoration' LIMIT 1;

INSERT IGNORE INTO types_categories (name, category_id) 
SELECT 'Lampe décorative', id FROM categories WHERE name = 'Décoration' LIMIT 1;

INSERT IGNORE INTO types_categories (name, category_id) 
SELECT 'Accessoire décoratif', id FROM categories WHERE name = 'Décoration' LIMIT 1;

-- Insérer les types de catégories pour Placard
INSERT IGNORE INTO types_categories (name, category_id) 
SELECT 'Placard encastré', id FROM categories WHERE name = 'Placard' LIMIT 1;

INSERT IGNORE INTO types_categories (name, category_id) 
SELECT 'Placard coulissant', id FROM categories WHERE name = 'Placard' LIMIT 1;

INSERT IGNORE INTO types_categories (name, category_id) 
SELECT 'Dressing', id FROM categories WHERE name = 'Placard' LIMIT 1;

INSERT IGNORE INTO types_categories (name, category_id) 
SELECT 'Étagère de rangement', id FROM categories WHERE name = 'Placard' LIMIT 1;

INSERT IGNORE INTO types_categories (name, category_id) 
SELECT 'Meuble à chaussures', id FROM categories WHERE name = 'Placard' LIMIT 1;

-- Insérer les types de catégories pour Salle à manger
INSERT IGNORE INTO types_categories (name, category_id) 
SELECT 'Table à manger', id FROM categories WHERE name = 'Salle à manger' LIMIT 1;

INSERT IGNORE INTO types_categories (name, category_id) 
SELECT 'Chaise de salle à manger', id FROM categories WHERE name = 'Salle à manger' LIMIT 1;

INSERT IGNORE INTO types_categories (name, category_id) 
SELECT 'Buffet', id FROM categories WHERE name = 'Salle à manger' LIMIT 1;

INSERT IGNORE INTO types_categories (name, category_id) 
SELECT 'Vaisselier', id FROM categories WHERE name = 'Salle à manger' LIMIT 1;

INSERT IGNORE INTO types_categories (name, category_id) 
SELECT 'Table extensible', id FROM categories WHERE name = 'Salle à manger' LIMIT 1;

INSERT IGNORE INTO types_categories (name, category_id) 
SELECT 'Banc de salle à manger', id FROM categories WHERE name = 'Salle à manger' LIMIT 1;

-- Insérer les types de catégories pour Salon
INSERT IGNORE INTO types_categories (name, category_id) 
SELECT 'Canapé', id FROM categories WHERE name = 'Salon' LIMIT 1;

INSERT IGNORE INTO types_categories (name, category_id) 
SELECT 'Canapé d''angle', id FROM categories WHERE name = 'Salon' LIMIT 1;

INSERT IGNORE INTO types_categories (name, category_id) 
SELECT 'Table basse', id FROM categories WHERE name = 'Salon' LIMIT 1;

INSERT IGNORE INTO types_categories (name, category_id) 
SELECT 'Fauteuil', id FROM categories WHERE name = 'Salon' LIMIT 1;

INSERT IGNORE INTO types_categories (name, category_id) 
SELECT 'Meuble TV', id FROM categories WHERE name = 'Salon' LIMIT 1;

INSERT IGNORE INTO types_categories (name, category_id) 
SELECT 'Bibliothèque', id FROM categories WHERE name = 'Salon' LIMIT 1;

-- Vérification : Afficher le nombre de types insérés par catégorie
SELECT 
    c.name as 'Catégorie',
    c.icon as 'Icône',
    COUNT(tc.id) as 'Nombre de types'
FROM categories c
LEFT JOIN types_categories tc ON c.id = tc.category_id
WHERE c.name IN ('Bureau', 'Chambre', 'Cuisine', 'Décoration', 'Placard', 'Salle à manger', 'Salon')
GROUP BY c.id, c.name, c.icon
ORDER BY c.name;
