<?php
// commande.php - Page de finalisation de commande
require_once 'functions.php';

// Rediriger si non connecté
if (!estConnecte()) {
    header('Location: login.php');
    exit;
}

// Récupérer les informations de l'utilisateur
$utilisateur = getUtilisateur($_SESSION['id_utilisateur']);

// Récupérer le contenu du panier
$panier = getPanier($_SESSION['id_utilisateur']);

// Vérifier si le panier n'est pas vide
if (empty($panier) || !isset($panier['articles']) || empty($panier['articles'])) {
    $_SESSION['erreur'] = 'Votre panier est vide.';
    header('Location: panier.php');
    exit;
}

$erreur = '';
$id_commande = 0;

// Traitement du formulaire de commande
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $adresse = trim($_POST['adresse']);
    $ville = trim($_POST['ville']);
    $code_postal = trim($_POST['code_postal']);
    
    // Validation des champs
    if (empty($adresse) || empty($ville) || empty($code_postal)) {
        $erreur = 'Tous les champs d\'adresse sont obligatoires.';
    } else {
        // Finaliser la commande
        $id_commande = finaliserCommande($_SESSION['id_utilisateur'], $adresse, $ville, $code_postal);
        
        if ($id_commande) {
            // Rediriger vers la page de confirmation
            header('Location: confirmation.php?id=' . $id_commande);
            exit;
        } else {
            $erreur = 'Une erreur est survenue lors de la finalisation de la commande.';
        }
    }
}

include 'header.php';
?>

<section class="page-header">
    <h2>Finalisation de la commande</h2>
</section>

<section class="commande">
    <?php if (!empty($erreur)): ?>
    <div class="alert alert-danger">
        <?php echo $erreur; ?>
    </div>
    <?php endif; ?>
    
    <div class="commande-resume">
        <h3>Résumé de votre commande</h3>
        <div class="resume-contenu">
            <div class="resume-articles">
                <h4>Articles (<?php echo count($panier['articles']); ?>)</h4>
                <ul>
                    <?php foreach ($panier['articles'] as $article): ?>
                    <li>
                        <span class="article-titre"><?php echo $article['titre']; ?></span>
                        <span class="article-quantite">x<?php echo $article['quantite']; ?></span>
                        <span class="article-prix"><?php echo number_format($article['sous_total'], 2, ',', ' '); ?> €</span>
                    </li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <div class="resume-total">
                <h4>Total</h4>
                <p class="total-amount"><?php echo number_format($panier['total'], 2, ',', ' '); ?> €</p>
            </div>
        </div>
    </div>
    
    <div class="commande-form">
        <h3>Adresse de livraison</h3>
        <form method="post" action="commande.php">
            <div class="form-group">
                <label for="adresse">Adresse :</label>
                <input type="text" id="adresse" name="adresse" value="<?php echo htmlspecialchars($utilisateur['adresse'] ?? ''); ?>" required>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label for="ville">Ville :</label>
                    <input type="text" id="ville" name="ville" value="<?php echo htmlspecialchars($utilisateur['ville'] ?? ''); ?>" required>
                </div>
                <div class="form-group">
                    <label for="code_postal">Code postal :</label>
                    <input type="text" id="code_postal" name="code_postal" value="<?php echo htmlspecialchars($utilisateur['code_postal'] ?? ''); ?>" required>
                </div>
            </div>
            
            <h3>Mode de paiement</h3>
            <div class="form-group">
                <label for="mode_paiement">Sélectionnez un mode de paiement :</label>
                <select id="mode_paiement" name="mode_paiement" required>
                    <option value="">-- Choisir --</option>
                    <option value="carte">Carte bancaire</option>
                    <option value="paypal">PayPal</option>
                    <option value="virement">Virement bancaire</option>
                </select>
            </div>
            
            <div id="paiement-carte" class="paiement-details" style="display: none;">
                <div class="form-group">
                    <label for="numero_carte">Numéro de carte :</label>
                    <input type="text" id="numero_carte" placeholder="1234 5678 9012 3456">
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="expiration">Date d'expiration :</label>
                        <input type="text" id="expiration" placeholder="MM/AA">
                    </div>
                    <div class="form-group">
                        <label for="cvv">CVV :</label>
                        <input type="text" id="cvv" placeholder="123">
                    </div>
                </div>
            </div>
            
            <div id="paiement-paypal" class="paiement-details" style="display: none;">
                <p>Vous serez redirigé vers PayPal pour effectuer votre paiement.</p>
            </div>
            
            <div id="paiement-virement" class="paiement-details" style="display: none;">
                <p>Coordonnées bancaires pour le virement :</p>
                <p>IBAN : FR76 1234 5678 9012 3456 7890 123</p>
                <p>BIC : AGRIFRPP123</p>
                <p>Titulaire : Bibliothèque Vintage</p>
                <p>Veuillez indiquer votre numéro de commande en référence.</p>
            </div>
            
            <div class="form-actions">
                <a href="panier.php" class="btn btn-secondary">Retour au panier</a>
                <button type="submit" class="btn btn-primary">Confirmer la commande</button>
            </div>
        </form>
    </div>
</section>

<script>
// Script pour afficher les détails du mode de paiement
document.getElementById('mode_paiement').addEventListener('change', function() {
    // Cacher tous les détails de paiement
    document.querySelectorAll('.paiement-details').forEach(function(element) {
        element.style.display = 'none';
    });
    
    // Afficher les détails correspondant au mode de paiement sélectionné
    const mode = this.value;
    if (mode) {
        const element = document.getElementById('paiement-' + mode);
        if (element) {
            element.style.display = 'block';
        }
    }
});
</script>

<?php include 'footer.php'; ?>
