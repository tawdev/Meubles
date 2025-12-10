<?php
$pageTitle = "Contact";
require_once 'includes/header.php';

$success = false;
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $message = trim($_POST['message'] ?? '');
    
    if (empty($name) || empty($email) || empty($message)) {
        $error = 'Veuillez remplir tous les champs obligatoires.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Adresse email invalide.';
    } else {
        try {
            $stmt = $pdo->prepare("INSERT INTO contact_messages (name, email, phone, message) VALUES (?, ?, ?, ?)");
            $stmt->execute([$name, $email, $phone ?: null, $message]);
            $success = true;
            
            // Optionnel : Envoyer un email
            // mail($email, 'Confirmation de contact', 'Merci pour votre message...');
        } catch (PDOException $e) {
            $error = 'Erreur lors de l\'envoi du message. Veuillez réessayer.';
        }
    }
}
?>

<section class="hero" style="padding: 4rem 2rem;">
    <div class="hero-content">
        <h1>Contactez-nous</h1>
        <p>Nous sommes là pour répondre à toutes vos questions</p>
    </div>
</section>

<div class="container">
    <div style="max-width: 1200px; margin: 0 auto;">
        
        <?php if ($success): ?>
            <div class="success-message" style="margin-bottom: 2rem;">
                <div style="font-size: 3rem; margin-bottom: 1rem;">✅</div>
                <h3 style="margin-bottom: 0.5rem;">Message envoyé avec succès !</h3>
                <p>Merci pour votre message ! Nous vous répondrons dans les plus brefs délais.</p>
            </div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div style="background: #e74c3c; color: white; padding: 1.5rem; border-radius: 10px; margin-bottom: 2rem; text-align: center;">
                <div style="font-size: 2rem; margin-bottom: 0.5rem;">❌</div>
                <strong><?php echo htmlspecialchars($error); ?></strong>
            </div>
        <?php endif; ?>
        
        <!-- Informations de Contact -->
        <section style="margin-bottom: 4rem;">
            <h2 class="section-title">Nos Coordonnées</h2>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 2rem; margin-top: 2rem;">
                <div class="category-card" style="text-align: center; padding: 2rem;">
                    <div style="font-size: 3.5rem; margin-bottom: 1rem;">📧</div>
                    <h3 style="color: var(--primary-color); margin-bottom: 0.5rem;">Email</h3>
                    <p style="color: var(--text-light);">
                        <a href="mailto:contact@meublesmaison.com" style="color: var(--secondary-color); text-decoration: none;">
                            contact@meublesmaison.com
                        </a>
                    </p>
                </div>
                
                <div class="category-card" style="text-align: center; padding: 2rem;">
                    <div style="font-size: 3.5rem; margin-bottom: 1rem;">📞</div>
                    <h3 style="color: var(--primary-color); margin-bottom: 0.5rem;">Téléphone</h3>
                    <p style="color: var(--text-light);">
                        <a href="tel:+33123456789" style="color: var(--secondary-color); text-decoration: none;">
                            01 23 45 67 89
                        </a>
                    </p>
                </div>
                
                <div class="category-card" style="text-align: center; padding: 2rem;">
                    <div style="font-size: 3.5rem; margin-bottom: 1rem;">📍</div>
                    <h3 style="color: var(--primary-color); margin-bottom: 0.5rem;">Adresse</h3>
                    <p style="color: var(--text-light); line-height: 1.6;">
                        123 Rue du Commerce<br>
                        75001 Paris, France
                    </p>
                </div>
                
                <div class="category-card" style="text-align: center; padding: 2rem;">
                    <div style="font-size: 3.5rem; margin-bottom: 1rem;">🕒</div>
                    <h3 style="color: var(--primary-color); margin-bottom: 0.5rem;">Horaires</h3>
                    <p style="color: var(--text-light); line-height: 1.6;">
                        Lun - Ven : 9h - 18h<br>
                        Sam : 10h - 16h<br>
                        Dim : Fermé
                    </p>
                </div>
            </div>
        </section>

        <!-- Formulaire de Contact -->
        <section>
            <h2 class="section-title">Envoyez-nous un Message</h2>
            <div style="max-width: 700px; margin: 0 auto;">
                <div class="form-container" style="box-shadow: var(--shadow-hover);">
                    <form id="contact-form" method="POST" action="contact.php" onsubmit="return validateForm('contact-form')">
                        <div class="form-group">
                            <label for="name">
                                <span style="color: var(--primary-color);">👤</span> Nom complet *
                            </label>
                            <input type="text" id="name" name="name" required 
                                   placeholder="Votre nom complet"
                                   value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="email">
                                <span style="color: var(--primary-color);">📧</span> Email *
                            </label>
                            <input type="email" id="email" name="email" required
                                   placeholder="votre.email@exemple.com"
                                   value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="phone">
                                <span style="color: var(--primary-color);">📞</span> Téléphone
                            </label>
                            <input type="tel" id="phone" name="phone"
                                   placeholder="01 23 45 67 89 (optionnel)"
                                   value="<?php echo isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : ''; ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="message">
                                <span style="color: var(--primary-color);">💬</span> Message *
                            </label>
                            <textarea id="message" name="message" required rows="8" 
                                      placeholder="Écrivez votre message ici..."><?php echo isset($_POST['message']) ? htmlspecialchars($_POST['message']) : ''; ?></textarea>
                        </div>
                        
                        <button type="submit" class="btn" style="width: 100%; padding: 1rem; font-size: 1.1rem;">
                            📤 Envoyer le message
                        </button>
                    </form>
                </div>
            </div>
        </section>

        <!-- Section Informations supplémentaires -->
        <section style="margin-top: 4rem; background: var(--bg-light); padding: 3rem 2rem; border-radius: 15px; text-align: center;">
            <h3 style="color: var(--primary-color); margin-bottom: 1rem; font-size: 1.5rem;">Besoin d'aide ?</h3>
            <p style="color: var(--text-light); font-size: 1.1rem; line-height: 1.8; max-width: 600px; margin: 0 auto;">
                Notre équipe est disponible pour répondre à toutes vos questions concernant nos produits, 
                les délais de livraison, les retours ou toute autre demande. N'hésitez pas à nous contacter !
            </p>
        </section>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
