# 🚀 Guide d'Installation Rapide

## Installation en 5 minutes

### 1. Prérequis
- XAMPP installé et démarré (Apache + MySQL)
- Navigateur web

### 2. Étapes

#### Étape 1 : Importer la base de données
1. Ouvrir http://localhost/phpmyadmin
2. Cliquer sur "Nouvelle base de données"
3. Nom : `meubles_db`
4. Cliquer sur "Importer"
5. Sélectionner le fichier `database.sql`
6. Cliquer sur "Exécuter"

#### Étape 2 : Vérifier la configuration
Ouvrir `db.php` et vérifier :
```php
$host = 'localhost';
$dbname = 'meubles_db';
$username = 'root';
$password = '';  // Vide par défaut avec XAMPP
```

#### Étape 3 : Accéder au site
- Frontend : http://localhost/MeublesMaison
- Admin : http://localhost/MeublesMaison/admin/login.php

#### Étape 4 : Se connecter à l'admin
- Username : `admin`
- Password : `admin123`

### 3. Test rapide

1. ✅ Vérifier que la page d'accueil s'affiche
2. ✅ Cliquer sur un produit pour voir les détails
3. ✅ Ajouter un produit au panier
4. ✅ Se connecter à l'admin
5. ✅ Ajouter un nouveau produit

### 4. Problèmes courants

**Erreur de connexion à la base de données**
- Vérifier que MySQL est démarré dans XAMPP
- Vérifier que la base `meubles_db` existe

**Images ne s'affichent pas**
- Les produits d'exemple utilisent des placeholders
- Ajoutez de vraies images via l'interface admin

**Page blanche**
- Activer l'affichage des erreurs PHP dans `php.ini` :
  ```ini
  display_errors = On
  error_reporting = E_ALL
  ```

### 5. Prochaines étapes

- [ ] Changer le mot de passe admin
- [ ] Ajouter de vraies images de produits
- [ ] Personnaliser les couleurs dans `styles.css`
- [ ] Configurer l'envoi d'emails pour le formulaire de contact

---

**Besoin d'aide ?** Consultez le fichier `README.md` pour plus de détails.

