-- Script pour insérer des produits pour chaque type de catégorie
-- Exécutez ce script dans phpMyAdmin après avoir inséré les types de catégories

-- ============================================
-- BUREAU - Chaise de bureau
-- ============================================
INSERT INTO products (name, description, price, image, category, category_id, type_category_id, stock) 
SELECT 
    'Chaise de bureau ergonomique noire',
    'Chaise de bureau ergonomique noire – Confort optimal avec dossier réglable',
    129.00,
    'images/Chaise de bureau ergonomique Fauteuil pivotant Hauteur réglable, accoudoirs inclinables pour bureau - Marron 65x68x94_104 cm.jpeg',
    'Bureau',
    c.id,
    tc.id,
    10
FROM categories c
INNER JOIN types_categories tc ON c.id = tc.category_id
WHERE c.name = 'Bureau' AND tc.name = 'Chaise de bureau'
LIMIT 1;

INSERT INTO products (name, description, price, image, category, category_id, type_category_id, stock) 
SELECT 
    'Chaise de bureau en tissu gris',
    'Chaise de bureau en tissu gris – Design moderne et assise confortable',
    99.00,
    'images/1765286166_Chaise de bureau ergonomique Fauteuil pivotant Hauteur réglable, accoudoirs inclinables pour bureau - Marron 65x68x94_104 cm.jpeg',
    'Bureau',
    c.id,
    tc.id,
    12
FROM categories c
INNER JOIN types_categories tc ON c.id = tc.category_id
WHERE c.name = 'Bureau' AND tc.name = 'Chaise de bureau'
LIMIT 1;

-- ============================================
-- BUREAU - Bureau droit
-- ============================================
INSERT INTO products (name, description, price, image, category, category_id, type_category_id, stock) 
SELECT 
    'Bureau droit en bois chêne',
    'Bureau droit en bois chêne – Surface large et solide',
    189.00,
    'images/1765286150_study table ideas study motivation study aesthetic study study tips study note study table study har.jpeg',
    'Bureau',
    c.id,
    tc.id,
    8
FROM categories c
INNER JOIN types_categories tc ON c.id = tc.category_id
WHERE c.name = 'Bureau' AND tc.name = 'Bureau droit'
LIMIT 1;

INSERT INTO products (name, description, price, image, category, category_id, type_category_id, stock) 
SELECT 
    'Bureau droit moderne blanc',
    'Bureau droit moderne blanc – Minimaliste et élégant',
    159.00,
    'images/1765286150_study table ideas study motivation study aesthetic study study tips study note study table study har.jpeg',
    'Bureau',
    c.id,
    tc.id,
    9
FROM categories c
INNER JOIN types_categories tc ON c.id = tc.category_id
WHERE c.name = 'Bureau' AND tc.name = 'Bureau droit'
LIMIT 1;

-- ============================================
-- BUREAU - Bureau d'angle
-- ============================================
INSERT INTO products (name, description, price, image, category, category_id, type_category_id, stock) 
SELECT 
    'Bureau d''angle professionnel',
    'Bureau d''angle professionnel – Gain d''espace et grande surface',
    249.00,
    'images/1765373147_Bureau d''angle en mélamine coloris imitation chêne….jpeg',
    'Bureau',
    c.id,
    tc.id,
    6
FROM categories c
INNER JOIN types_categories tc ON c.id = tc.category_id
WHERE c.name = 'Bureau' AND tc.name = 'Bureau d''angle'
LIMIT 1;

INSERT INTO products (name, description, price, image, category, category_id, type_category_id, stock) 
SELECT 
    'Bureau d''angle compact',
    'Bureau d''angle compact – Idéal pour petits espaces',
    199.00,
    'images/1765373147_Bureau d''angle en mélamine coloris imitation chêne….jpeg',
    'Bureau',
    c.id,
    tc.id,
    7
FROM categories c
INNER JOIN types_categories tc ON c.id = tc.category_id
WHERE c.name = 'Bureau' AND tc.name = 'Bureau d''angle'
LIMIT 1;

-- ============================================
-- BUREAU - Caisson de rangement
-- ============================================
INSERT INTO products (name, description, price, image, category, category_id, type_category_id, stock) 
SELECT 
    'Caisson de bureau 3 tiroirs',
    'Caisson de bureau 3 tiroirs – Pratique et mobile',
    89.00,
    'images/1765373576_Rehaussez l''organisation de votre maison avec ce….jpeg',
    'Bureau',
    c.id,
    tc.id,
    15
FROM categories c
INNER JOIN types_categories tc ON c.id = tc.category_id
WHERE c.name = 'Bureau' AND tc.name = 'Caisson de rangement'
LIMIT 1;

-- ============================================
-- CHAMBRE - Lit double
-- ============================================
INSERT INTO products (name, description, price, image, category, category_id, type_category_id, stock) 
SELECT 
    'Lit double en bois massif',
    'Lit double en bois massif – Robuste et élégant',
    399.00,
    'images/Lit double MARIUS 160x200 tissu beige sommier inclus.jpeg',
    'Chambre',
    c.id,
    tc.id,
    5
FROM categories c
INNER JOIN types_categories tc ON c.id = tc.category_id
WHERE c.name = 'Chambre' AND tc.name = 'Lit double'
LIMIT 1;

INSERT INTO products (name, description, price, image, category, category_id, type_category_id, stock) 
SELECT 
    'Lit double avec rangement',
    'Lit double avec rangement – Tiroirs intégrés',
    459.00,
    'images/Lit double MARIUS 160x200 tissu beige sommier inclus.jpeg',
    'Chambre',
    c.id,
    tc.id,
    4
FROM categories c
INNER JOIN types_categories tc ON c.id = tc.category_id
WHERE c.name = 'Chambre' AND tc.name = 'Lit double'
LIMIT 1;

-- ============================================
-- CHAMBRE - Armoire
-- ============================================
INSERT INTO products (name, description, price, image, category, category_id, type_category_id, stock) 
SELECT 
    'Armoire 3 portes blanche',
    'Armoire 3 portes blanche – Grande capacité de rangement',
    499.00,
    'images/Willa Arlo™ Interiors Armoire 3 portes 70 _Monchat.jpeg',
    'Chambre',
    c.id,
    tc.id,
    3
FROM categories c
INNER JOIN types_categories tc ON c.id = tc.category_id
WHERE c.name = 'Chambre' AND tc.name = 'Armoire'
LIMIT 1;

INSERT INTO products (name, description, price, image, category, category_id, type_category_id, stock) 
SELECT 
    'Armoire coulissante miroir',
    'Armoire coulissante miroir – Moderne et fonctionnelle',
    549.00,
    'images/1765373186_L''armoire Max 2 à portes coulissantes est une….jpeg',
    'Chambre',
    c.id,
    tc.id,
    2
FROM categories c
INNER JOIN types_categories tc ON c.id = tc.category_id
WHERE c.name = 'Chambre' AND tc.name = 'Armoire'
LIMIT 1;

-- ============================================
-- CHAMBRE - Commode
-- ============================================
INSERT INTO products (name, description, price, image, category, category_id, type_category_id, stock) 
SELECT 
    'Commode 4 tiroirs en bois',
    'Commode 4 tiroirs en bois – Style naturel',
    229.00,
    'images/Commode 4 Tiroirs Pricy.jpeg',
    'Chambre',
    c.id,
    tc.id,
    8
FROM categories c
INNER JOIN types_categories tc ON c.id = tc.category_id
WHERE c.name = 'Chambre' AND tc.name = 'Commode'
LIMIT 1;

-- ============================================
-- CHAMBRE - Table de chevet
-- ============================================
INSERT INTO products (name, description, price, image, category, category_id, type_category_id, stock) 
SELECT 
    'Table de chevet moderne',
    'Table de chevet moderne – 2 tiroirs',
    89.00,
    'images/1765373611_- 2-drawers nightstand in your bedroom due in part….jpeg',
    'Chambre',
    c.id,
    tc.id,
    12
FROM categories c
INNER JOIN types_categories tc ON c.id = tc.category_id
WHERE c.name = 'Chambre' AND tc.name = 'Table de chevet'
LIMIT 1;

-- ============================================
-- CUISINE - Meuble bas de cuisine
-- ============================================
INSERT INTO products (name, description, price, image, category, category_id, type_category_id, stock) 
SELECT 
    'Meuble bas 2 portes blanc',
    'Meuble bas 2 portes blanc – Résistant à l''humidité',
    149.00,
    'images/1765373455_✨ Cuisine blanche et bois moderne avec îlot _ 7….jpeg',
    'Cuisine',
    c.id,
    tc.id,
    10
FROM categories c
INNER JOIN types_categories tc ON c.id = tc.category_id
WHERE c.name = 'Cuisine' AND tc.name = 'Meuble bas de cuisine'
LIMIT 1;

-- ============================================
-- CUISINE - Meuble haut de cuisine
-- ============================================
INSERT INTO products (name, description, price, image, category, category_id, type_category_id, stock) 
SELECT 
    'Meuble haut mural',
    'Meuble haut mural – Gain de place',
    129.00,
    'images/1765373455_✨ Cuisine blanche et bois moderne avec îlot _ 7….jpeg',
    'Cuisine',
    c.id,
    tc.id,
    11
FROM categories c
INNER JOIN types_categories tc ON c.id = tc.category_id
WHERE c.name = 'Cuisine' AND tc.name = 'Meuble haut de cuisine'
LIMIT 1;

-- ============================================
-- CUISINE - Table de cuisine
-- ============================================
INSERT INTO products (name, description, price, image, category, category_id, type_category_id, stock) 
SELECT 
    'Table de cuisine compacte',
    'Table de cuisine compacte – Idéale pour 4 personnes',
    199.00,
    'images/1765372682_Amari 4 Seater Round Dining Table _ Dunelm.jpeg',
    'Cuisine',
    c.id,
    tc.id,
    7
FROM categories c
INNER JOIN types_categories tc ON c.id = tc.category_id
WHERE c.name = 'Cuisine' AND tc.name = 'Table de cuisine'
LIMIT 1;

-- ============================================
-- CUISINE - Chaise de cuisine
-- ============================================
INSERT INTO products (name, description, price, image, category, category_id, type_category_id, stock) 
SELECT 
    'Chaise de cuisine en plastique renforcé',
    'Chaise de cuisine en plastique renforcé – Facile à nettoyer',
    49.00,
    'images/1765373515_Creative Retro Wood Dining Chair for Living Room….jpeg',
    'Cuisine',
    c.id,
    tc.id,
    20
FROM categories c
INNER JOIN types_categories tc ON c.id = tc.category_id
WHERE c.name = 'Cuisine' AND tc.name = 'Chaise de cuisine'
LIMIT 1;

-- ============================================
-- CUISINE - Îlot de cuisine
-- ============================================
INSERT INTO products (name, description, price, image, category, category_id, type_category_id, stock) 
SELECT 
    'Îlot de cuisine avec rangement',
    'Îlot de cuisine avec rangement – Moderne et pratique',
    349.00,
    'images/1765373455_✨ Cuisine blanche et bois moderne avec îlot _ 7….jpeg',
    'Cuisine',
    c.id,
    tc.id,
    4
FROM categories c
INNER JOIN types_categories tc ON c.id = tc.category_id
WHERE c.name = 'Cuisine' AND tc.name = 'Îlot de cuisine'
LIMIT 1;

-- ============================================
-- DÉCORATION - Étagère murale
-- ============================================
INSERT INTO products (name, description, price, image, category, category_id, type_category_id, stock) 
SELECT 
    'Étagère murale en bois',
    'Étagère murale en bois – Design naturel',
    59.00,
    'images/Étagère blanche murale avec 8 cubes.jpeg',
    'Décoration',
    c.id,
    tc.id,
    15
FROM categories c
INNER JOIN types_categories tc ON c.id = tc.category_id
WHERE c.name = 'Décoration' AND tc.name = 'Étagère murale'
LIMIT 1;

-- ============================================
-- DÉCORATION - Miroir décoratif
-- ============================================
INSERT INTO products (name, description, price, image, category, category_id, type_category_id, stock) 
SELECT 
    'Miroir mural rond doré',
    'Miroir mural rond doré – Style élégant',
    89.00,
    'images/1765373339_✓ Design Ovale Sophistiqué _ Ajoute une touche de….jpeg',
    'Décoration',
    c.id,
    tc.id,
    10
FROM categories c
INNER JOIN types_categories tc ON c.id = tc.category_id
WHERE c.name = 'Décoration' AND tc.name = 'Miroir décoratif'
LIMIT 1;

-- ============================================
-- DÉCORATION - Cadre mural
-- ============================================
INSERT INTO products (name, description, price, image, category, category_id, type_category_id, stock) 
SELECT 
    'Cadre mural décoratif',
    'Cadre mural décoratif – Style moderne',
    39.00,
    'images/1765373220_Panneau mural à lattes larges en placage de chêne….jpeg',
    'Décoration',
    c.id,
    tc.id,
    18
FROM categories c
INNER JOIN types_categories tc ON c.id = tc.category_id
WHERE c.name = 'Décoration' AND tc.name = 'Cadre mural'
LIMIT 1;

-- ============================================
-- DÉCORATION - Lampe décorative
-- ============================================
INSERT INTO products (name, description, price, image, category, category_id, type_category_id, stock) 
SELECT 
    'Lampe décorative LED',
    'Lampe décorative LED – Lumière chaude',
    69.00,
    'images/1765373370_Lampe de bureau à éclairage LED pour les yeux….jpeg',
    'Décoration',
    c.id,
    tc.id,
    12
FROM categories c
INNER JOIN types_categories tc ON c.id = tc.category_id
WHERE c.name = 'Décoration' AND tc.name = 'Lampe décorative'
LIMIT 1;

-- ============================================
-- DÉCORATION - Accessoire décoratif
-- ============================================
INSERT INTO products (name, description, price, image, category, category_id, type_category_id, stock) 
SELECT 
    'Vase décoratif moderne',
    'Vase décoratif moderne – Finition mate',
    45.00,
    'images/1765373274_Ensemble de 2 vases en céramique creux, vases….jpeg',
    'Décoration',
    c.id,
    tc.id,
    20
FROM categories c
INNER JOIN types_categories tc ON c.id = tc.category_id
WHERE c.name = 'Décoration' AND tc.name = 'Accessoire décoratif'
LIMIT 1;

-- ============================================
-- PLACARD - Placard coulissant
-- ============================================
INSERT INTO products (name, description, price, image, category, category_id, type_category_id, stock) 
SELECT 
    'Placard coulissant miroir',
    'Placard coulissant miroir – Gain de place',
    599.00,
    'images/1765373422_The versatile design of our 3-door mirrored….jpeg',
    'Placard',
    c.id,
    tc.id,
    3
FROM categories c
INNER JOIN types_categories tc ON c.id = tc.category_id
WHERE c.name = 'Placard' AND tc.name = 'Placard coulissant'
LIMIT 1;

-- ============================================
-- PLACARD - Dressing
-- ============================================
INSERT INTO products (name, description, price, image, category, category_id, type_category_id, stock) 
SELECT 
    'Dressing modulable',
    'Dressing modulable – Adaptable à tous les espaces',
    699.00,
    'images/1765373092_[Grand espace] Cette penderie (43 x 182 x 182 cm)….jpeg',
    'Placard',
    c.id,
    tc.id,
    2
FROM categories c
INNER JOIN types_categories tc ON c.id = tc.category_id
WHERE c.name = 'Placard' AND tc.name = 'Dressing'
LIMIT 1;

-- ============================================
-- PLACARD - Meuble à chaussures
-- ============================================
INSERT INTO products (name, description, price, image, category, category_id, type_category_id, stock) 
SELECT 
    'Meuble à chaussures 3 niveaux',
    'Meuble à chaussures 3 niveaux – Pratique et élégant',
    119.00,
    'images/1765373576_Rehaussez l''organisation de votre maison avec ce….jpeg',
    'Placard',
    c.id,
    tc.id,
    8
FROM categories c
INNER JOIN types_categories tc ON c.id = tc.category_id
WHERE c.name = 'Placard' AND tc.name = 'Meuble à chaussures'
LIMIT 1;

-- ============================================
-- PLACARD - Étagère de rangement
-- ============================================
INSERT INTO products (name, description, price, image, category, category_id, type_category_id, stock) 
SELECT 
    'Étagère de rangement métallique',
    'Étagère de rangement métallique – Solide',
    79.00,
    'images/1765373576_Rehaussez l''organisation de votre maison avec ce….jpeg',
    'Placard',
    c.id,
    tc.id,
    12
FROM categories c
INNER JOIN types_categories tc ON c.id = tc.category_id
WHERE c.name = 'Placard' AND tc.name = 'Étagère de rangement'
LIMIT 1;

-- ============================================
-- SALLE À MANGER - Table à manger
-- ============================================
INSERT INTO products (name, description, price, image, category, category_id, type_category_id, stock) 
SELECT 
    'Table à manger en bois massif',
    'Table à manger en bois massif – 6 personnes',
    499.00,
    'images/Table à manger design en bois d''acacia et métal – Élégance brute & moderne.jpeg',
    'Salle à manger',
    c.id,
    tc.id,
    4
FROM categories c
INNER JOIN types_categories tc ON c.id = tc.category_id
WHERE c.name = 'Salle à manger' AND tc.name = 'Table à manger'
LIMIT 1;

-- ============================================
-- SALLE À MANGER - Chaise salle à manger
-- ============================================
INSERT INTO products (name, description, price, image, category, category_id, type_category_id, stock) 
SELECT 
    'Chaise salle à manger rembourrée',
    'Chaise salle à manger rembourrée – Confort premium',
    129.00,
    'images/1765373515_Creative Retro Wood Dining Chair for Living Room….jpeg',
    'Salle à manger',
    c.id,
    tc.id,
    10
FROM categories c
INNER JOIN types_categories tc ON c.id = tc.category_id
WHERE c.name = 'Salle à manger' AND tc.name = 'Chaise de salle à manger'
LIMIT 1;

-- ============================================
-- SALLE À MANGER - Buffet
-- ============================================
INSERT INTO products (name, description, price, image, category, category_id, type_category_id, stock) 
SELECT 
    'Buffet moderne 3 portes',
    'Buffet moderne 3 portes – Grand espace de rangement',
    399.00,
    'images/1765372717_Buffet salle à manger blanc cachemire 1 niche….jpeg',
    'Salle à manger',
    c.id,
    tc.id,
    5
FROM categories c
INNER JOIN types_categories tc ON c.id = tc.category_id
WHERE c.name = 'Salle à manger' AND tc.name = 'Buffet'
LIMIT 1;

-- ============================================
-- SALLE À MANGER - Vaisselier
-- ============================================
INSERT INTO products (name, description, price, image, category, category_id, type_category_id, stock) 
SELECT 
    'Vaisselier vitré',
    'Vaisselier vitré – Élégant',
    549.00,
    'images/1765372658_Optez pour le vaisselier vitré H_ 200 cm décor….jpeg',
    'Salle à manger',
    c.id,
    tc.id,
    3
FROM categories c
INNER JOIN types_categories tc ON c.id = tc.category_id
WHERE c.name = 'Salle à manger' AND tc.name = 'Vaisselier'
LIMIT 1;

-- ============================================
-- SALON - Canapé
-- ============================================
INSERT INTO products (name, description, price, image, category, category_id, type_category_id, stock) 
SELECT 
    'Canapé 3 places en tissu',
    'Canapé 3 places en tissu – Confort et style',
    699.00,
    'images/vidaXL 3-Sitzer-Sofa XT6927 Grau_Dunkelgrau Material_ Textil 198cm.jpeg',
    'Salon',
    c.id,
    tc.id,
    3
FROM categories c
INNER JOIN types_categories tc ON c.id = tc.category_id
WHERE c.name = 'Salon' AND tc.name = 'Canapé'
LIMIT 1;

-- ============================================
-- SALON - Canapé d'angle
-- ============================================
INSERT INTO products (name, description, price, image, category, category_id, type_category_id, stock) 
SELECT 
    'Canapé d''angle moderne',
    'Canapé d''angle moderne – Idéal pour familles',
    999.00,
    'images/1765372973_Un canapé 3 places convertible au design moderne….jpeg',
    'Salon',
    c.id,
    tc.id,
    2
FROM categories c
INNER JOIN types_categories tc ON c.id = tc.category_id
WHERE c.name = 'Salon' AND tc.name = 'Canapé d''angle'
LIMIT 1;

-- ============================================
-- SALON - Table basse
-- ============================================
INSERT INTO products (name, description, price, image, category, category_id, type_category_id, stock) 
SELECT 
    'Table basse en bois et métal',
    'Table basse en bois et métal – Design industriel',
    179.00,
    'images/1765373245_Table Basse Travertin Bois Moderne Pour Salon….jpeg',
    'Salon',
    c.id,
    tc.id,
    8
FROM categories c
INNER JOIN types_categories tc ON c.id = tc.category_id
WHERE c.name = 'Salon' AND tc.name = 'Table basse'
LIMIT 1;

-- ============================================
-- SALON - Fauteuil
-- ============================================
INSERT INTO products (name, description, price, image, category, category_id, type_category_id, stock) 
SELECT 
    'Fauteuil confortable en velours',
    'Fauteuil confortable en velours – Style chic',
    299.00,
    'images/1765372906_Vente en ligne Fauteuil Molto sable de la….jpeg',
    'Salon',
    c.id,
    tc.id,
    6
FROM categories c
INNER JOIN types_categories tc ON c.id = tc.category_id
WHERE c.name = 'Salon' AND tc.name = 'Fauteuil'
LIMIT 1;

-- ============================================
-- SALON - Meuble TV
-- ============================================
INSERT INTO products (name, description, price, image, category, category_id, type_category_id, stock) 
SELECT 
    'Meuble TV moderne',
    'Meuble TV moderne – Avec rangement',
    249.00,
    'images/1765373480_Description Design Élégant Le buffet est conçu….jpeg',
    'Salon',
    c.id,
    tc.id,
    7
FROM categories c
INNER JOIN types_categories tc ON c.id = tc.category_id
WHERE c.name = 'Salon' AND tc.name = 'Meuble TV'
LIMIT 1;

-- ============================================
-- Vérification : Afficher le nombre de produits par catégorie et type
-- ============================================
SELECT 
    c.name as 'Catégorie',
    c.icon as 'Icône',
    tc.name as 'Type',
    COUNT(p.id) as 'Nombre de produits',
    SUM(p.stock) as 'Stock total'
FROM categories c
LEFT JOIN types_categories tc ON c.id = tc.category_id
LEFT JOIN products p ON tc.id = p.type_category_id
WHERE c.name IN ('Bureau', 'Chambre', 'Cuisine', 'Décoration', 'Placard', 'Salle à manger', 'Salon')
GROUP BY c.id, c.name, c.icon, tc.id, tc.name
HAVING COUNT(p.id) > 0
ORDER BY c.name, tc.name;

