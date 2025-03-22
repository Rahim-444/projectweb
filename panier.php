<?php
// panier.php - Page du panier
require_once 'functions.php';

// Rediriger si non connecté
if (!estConnecte()) {
    header('Location: login.php');
    exit;
}

// Récupérer le contenu du panier
$panier = getPanier($_SESSION['id_utilisateur']);

include 'header.php';
?>

<section class="page-header">
    <h2>Votre Panier</h2>
</section>

<section class="panier">
    <?php if (empty($panier) || !isset($panier['articles']) || empty($panier['articles'])): ?>
    <div class="panier-vide">
        <i class="fas fa-shopping-cart fa-3x"></i>
        <h3>Votre panier est vide</h3>
        <p>Parcourez notre catalogue pour ajouter des livres à votre panier.</p>
        <a href="catalogue.php" class="btn btn-primary">Voir le catalogue</a>
    </div>
    <?php else: ?>
    <div class="panier-contenu">
        <table class="panier-table">
            <thead>
                <tr>
                    <th>Livre</th>
                    <th>Prix unitaire</th>
                    <th>Quantité</th>
                    <th>Sous-total</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($panier['articles'] as $article): ?>
                <tr>
                    <td class="livre-info">
                        <img src="images/<?php echo $article['image']; ?>" alt="<?php echo $article['titre']; ?>">
                        <div>
                            <h4><?php echo $article['titre']; ?></h4>
                            <p>par <?php echo $article['auteur']; ?></p>
                        </div>
                    </td>
                    <td class="prix"><?php echo number_format($article['prix'], 2, ',', ' '); ?> €</td>
                    <td class="quantite">
                        <form action="modifier_panier.php" method="post" class="quantite-form">
                            <input type="hidden" name="id_article" value="<?php echo $article['id_article_panier']; ?>">
                            <input type="number" name="quantite" value="<?php echo $article['quantite']; ?>" min="1" max="<?php echo $article['stock']; ?>" data-prix="<?php echo $article['prix']; ?>" data-id="<?php echo $article['id_article_panier']; ?>">
                            <button type="submit" class="btn btn-xs btn-secondary">Modifier</button>
                        </form>
                    </td>
                    <td class="sous-total"><?php echo number_format($article['sous_total'], 2, ',', ' '); ?> €</td>
                    <td class="actions">
                        <form action="supprimer_panier.php" method="post">
                            <input type="hidden" name="id_article" value="<?php echo $article['id_article_panier']; ?>">
                            <button type="submit" class="btn btn-xs btn-danger"><i class="fas fa-trash"></i> Supprimer</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="3" class="total-label">Total</td>
                    <td class="total-amount" id="total-amount"><?php echo number_format($panier['total'], 2, ',', ' '); ?> €</td>
                    <td></td>
                </tr>
            </tfoot>
        </table>
        
        <div class="panier-actions">
            <a href="catalogue.php" class="btn btn-secondary">Continuer vos achats</a>
            <a href="commande.php" class="btn btn-primary">Finaliser la commande</a>
        </div>
        
        <div class="panier-export">
            <button id="export-pdf" class="btn btn-secondary"><i class="fas fa-file-pdf"></i> Exporter en PDF</button>
        </div>
    </div>
    <?php endif; ?>
</section>

<script>
// Script pour exporter le panier en PDF
document.getElementById('export-pdf').addEventListener('click', function() {
    const { jsPDF } = window.jspdf;
    
    // Initialiser le PDF
    const pdf = new jsPDF();
    
    // Définir le titre
    pdf.setFontSize(18);
    pdf.text("Votre Panier - Bibliothèque Vintage", 105, 20, null, null, "center");
    
    // Date d'impression
    pdf.setFontSize(10);
    pdf.text("Date: " + new Date().toLocaleDateString(), 20, 30);
    
    // En-têtes de tableau
    pdf.setFontSize(12);
    pdf.text("Livre", 20, 40);
    pdf.text("Prix unitaire", 100, 40);
    pdf.text("Quantité", 140, 40);
    pdf.text("Sous-total", 170, 40);
    
    // Ligne de séparation
    pdf.line(20, 45, 190, 45);
    
    // Données du panier
    pdf.setFontSize(10);
    let y = 55;
    
    <?php if (isset($panier['articles'])): ?>
    <?php foreach ($panier['articles'] as $article): ?>
    pdf.text("<?php echo addslashes($article['titre']); ?>", 20, y);
    pdf.text("<?php echo number_format($article['prix'], 2, ',', ' '); ?> €", 100, y);
    pdf.text("<?php echo $article['quantite']; ?>", 140, y);
    pdf.text("<?php echo number_format($article['sous_total'], 2, ',', ' '); ?> €", 170, y);
    y += 10;
    
    if (y > 250) {
        pdf.addPage();
        y = 20;
    }
    <?php endforeach; ?>
    
    // Total
    pdf.line(20, y, 190, y);
    y += 10;
    pdf.setFontSize(12);
    pdf.text("Total:", 140, y);
    pdf.text("<?php echo number_format($panier['total'], 2, ',', ' '); ?> €", 170, y);
    <?php endif; ?>
    
    // Pied de page
    pdf.setFontSize(10);
    pdf.text("Bibliothèque Vintage - 12 Rue des Livres, 75006 Paris - contact@bibliotheque-vintage.fr", 105, 280, null, null, "center");
    
    // Télécharger le PDF
    pdf.save("panier-bibliotheque-vintage.pdf");
});
</script>

<?php include 'footer.php'; ?>

