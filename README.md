# 🪑 Meubles de Maison - Site E-commerce

Site web complet pour un magasin de meubles en ligne, développé avec PHP, MySQL, CSS et JavaScript.

## 📋 Fonctionnalités

### Frontend
- ✅ Page d'accueil moderne avec hero section
- ✅ Catalogue de produits avec filtres (catégorie, prix, recherche)
- ✅ Page détaillée pour chaque produit
- ✅ Panier d'achat dynamique (localStorage)
- ✅ Page "À propos"
- ✅ Formulaire de contact fonctionnel
- ✅ Design responsive et moderne

### Backend / Admin
- ✅ Connexion sécurisée pour les administrateurs
- ✅ Tableau de bord avec statistiques
- ✅ Gestion complète des produits (CRUD)
- ✅ Upload d'images pour les produits
- ✅ Gestion des commandes avec changement de statut
- ✅ Interface d'administration intuitive

## 🚀 Installation

### Prérequis
- PHP 7.4 ou supérieur
- MySQL 5.7 ou supérieur
- Serveur web (Apache/Nginx) ou XAMPP/WAMP/MAMP
- Extension PHP PDO activée

### Étapes d'installation

1. **Cloner ou télécharger le projet**
   ```bash
   cd C:\xampp\htdocs\MeublesMaison
   ```

2. **Créer la base de données**
   - Ouvrir phpMyAdmin (http://localhost/phpmyadmin)
   - Importer le fichier `database.sql`
   - Ou exécuter les commandes SQL manuellement dans phpMyAdmin

3. **Configurer la connexion à la base de données**
   - Ouvrir le fichier `db.php`
   - Modifier si nécessaire les paramètres de connexion :
     ```php
     $host = 'localhost';
     $dbname = 'meubles_db';
     $username = 'root';
     $password = '';
     ```

4. **Créer le dossier pour les images**
   ```bash
   mkdir images
   ```
   - Assurez-vous que le dossier `images` a les permissions d'écriture (chmod 777 sur Linux/Mac)

5. **Démarrer le serveur**
   - Si vous utilisez XAMPP : démarrer Apache et MySQL depuis le panneau de contrôle
   - Accéder au site : http://localhost/MeublesMaison

## 🔐 Identifiants par défaut

**Administrateur :**
- Username: `admin`
- Password: `admin123`

⚠️ **Important :** Changez le mot de passe après la première connexion !

## 📁 Structure du projet

```
MeublesMaison/
├── admin/
│   ├── includes/
│   │   ├── header.php
│   │   └── footer.php
│   ├── login.php
│   ├── logout.php
│   ├── dashboard.php
│   ├── add.php
│   ├── edit.php
│   ├── delete.php
│   └── orders.php
├── includes/
│   ├── header.php
│   └── footer.php
├── images/              (à créer - pour les uploads)
├── db.php
├── database.sql
├── index.php
├── product.php
├── cart.php
├── about.php
├── contact.php
├── process_order.php
├── styles.css
├── script.js
└── README.md
```

## 🗄️ Base de données

### Tables principales

- **products** : Catalogue des produits
- **admins** : Comptes administrateurs
- **orders** : Commandes clients
- **order_items** : Articles de chaque commande
- **contact_messages** : Messages du formulaire de contact

## 🎨 Personnalisation

### Modifier les couleurs
Éditez le fichier `styles.css` et modifiez les variables CSS dans `:root` :
```css
:root {
    --primary-color: #2c3e50;
    --secondary-color: #3498db;
    --accent-color: #e74c3c;
    /* ... */
}
```

### Ajouter des catégories
1. Modifier les options dans `admin/add.php` et `admin/edit.php`
2. Ajouter les options dans les filtres de `index.php`

## 🔧 Fonctionnalités techniques

### Panier
- Utilise `localStorage` pour persister le panier
- Mise à jour en temps réel via JavaScript
- Synchronisation avec le backend lors du checkout

### Sécurité
- Protection contre les injections SQL (PDO avec requêtes préparées)
- Validation des données côté serveur et client
- Sessions sécurisées pour l'administration
- Hashage des mots de passe (bcrypt)

### Responsive Design
- Design adaptatif pour mobile, tablette et desktop
- Utilisation de CSS Grid et Flexbox
- Media queries pour différentes tailles d'écran

## 📝 Notes importantes

1. **Images** : Les produits d'exemple utilisent des placeholders. Remplacez-les par de vraies images dans la base de données.

2. **Email** : Le formulaire de contact stocke les messages en base de données. Pour envoyer des emails, configurez la fonction `mail()` de PHP ou utilisez un service comme PHPMailer.

3. **Paiement** : Le système de commande est fonctionnel mais ne gère pas les paiements réels. Intégrez un service de paiement (Stripe, PayPal, etc.) pour la production.

4. **Stock** : Le système gère automatiquement le stock lors des commandes.

## 🐛 Dépannage

### Erreur de connexion à la base de données
- Vérifiez que MySQL est démarré
- Vérifiez les identifiants dans `db.php`
- Assurez-vous que la base `meubles_db` existe

### Images ne s'affichent pas
- Vérifiez que le dossier `images` existe et est accessible
- Vérifiez les permissions du dossier (chmod 777)
- Vérifiez les chemins dans la base de données

### Panier ne fonctionne pas
- Vérifiez que JavaScript est activé dans le navigateur
- Ouvrez la console du navigateur pour voir les erreurs
- Vérifiez que `script.js` est bien chargé

## 📞 Support

Pour toute question ou problème, consultez la documentation PHP/MySQL ou contactez le développeur.

## 📄 Licence

Ce projet est fourni "tel quel" pour usage éducatif et commercial.

---

**Développé avec ❤️ pour Meubles de Maison**

