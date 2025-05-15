<?php
require_once 'functions.php';

// Rediriger si non connecté
if (!estConnecte()) {
    header('Location: login.php');
    exit;
}

$id_commande = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id_commande <= 0) {
    header('Location: index.php');
    exit;
}

$details = getDetailsCommande($_SESSION['id_utilisateur'], $id_commande);

if (empty($details)) {
    header('Location: index.php');
    exit;
}

include 'header.php';
?>

<section class="page-header">
    <h2>Confirmation de commande</h2>
</section>

<section class="confirmation">
    <div class="confirmation-message">
        <i class="fas fa-check-circle fa-3x"></i>
        <h3>Votre commande a été confirmée !</h3>
        <p>Merci pour votre achat chez Bibliothèque Vintage.</p>
        <p>Numéro de commande : <strong><?php echo $id_commande; ?></strong></p>
    </div>

    <div class="confirmation-details">
        <h3>Détails de la commande</h3>
        <table class="commande-table">
            <thead>
                <tr>
                    <th>Livre</th>
                    <th>Prix unitaire</th>
                    <th>Quantité</th>
                    <th>Sous-total</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($details['articles'] as $article): ?>
                    <tr>
                        <td class="livre-info">
                            <h4><?php echo $article['titre']; ?></h4>
                            <p>par <?php echo $article['auteur']; ?></p>
                        </td>
                        <td class="prix"><?php echo number_format($article['prix_unitaire'], 2, ',', ' '); ?> DZD</td>
                        <td class="quantite"><?php echo $article['quantite']; ?></td>
                        <td class="sous-total"><?php echo number_format($article['sous_total'], 2, ',', ' '); ?> DZD</td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="3" class="total-label">Total</td>
                    <td class="total-amount"><?php echo number_format($details['total'], 2, ',', ' '); ?> DZD</td>
                </tr>
            </tfoot>
        </table>
    </div>

    <div class="confirmation-actions">
        <button id="imprimer-facture" class="btn btn-secondary"><i class="fas fa-file-pdf"></i> Imprimer la facture</button>
        <a href="commandes.php" class="btn btn-secondary">Voir mes commandes</a>
        <a href="index.php" class="btn btn-primary">Retour à l'accueil</a>
    </div>
</section>

<script>
    // Script pour générer la facture en PDF
    document.getElementById('imprimer-facture').addEventListener('click', function() {
        const {
            jsPDF
        } = window.jspdf;

        // Initialiser le PDF
        const pdf = new jsPDF();

        // Définir le titre
        pdf.setFontSize(18);
        pdf.text("Facture - Bibliothèque Vintage", 105, 20, null, null, "center");

        // Informations client et commande
        pdf.setFontSize(12);
        pdf.text("Numéro de commande: <?php echo $id_commande; ?>", 20, 40);
        pdf.text("Date: <?php echo $details['articles'][0]['date_commande']; ?>", 20, 50);
        pdf.text("Statut: <?php echo $details['articles'][0]['statut']; ?>", 20, 60);

        // En-têtes de tableau
        pdf.text("Livre", 20, 80);
        pdf.text("Prix unitaire", 100, 80);
        pdf.text("Quantité", 140, 80);
        pdf.text("Sous-total", 170, 80);

        // Ligne de séparation
        pdf.line(20, 85, 190, 85);

        // Données des articles
        pdf.setFontSize(10);
        let y = 95;

        <?php foreach ($details['articles'] as $article): ?>
            pdf.text("<?php echo addslashes($article['titre']); ?>", 20, y);
            pdf.text("<?php echo number_format($article['prix_unitaire'], 2, ',', ' '); ?> DZD", 100, y);
            pdf.text("<?php echo $article['quantite']; ?>", 140, y);
            pdf.text("<?php echo number_format($article['sous_total'], 2, ',', ' '); ?> DZD", 170, y);
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
        pdf.text("<?php echo number_format($details['total'], 2, ',', ' '); ?> DZD", 170, y);

        // Informations de contact
        y += 30;
        pdf.text("Pour toute question concernant votre commande :", 20, y);
        pdf.text("Bibliothèque Vintage", 20, y + 10);
        pdf.text("Bouzareah Alger", 20, y + 20);
        pdf.text("Tél: +213 791 73 11 16", 20, y + 30);
        pdf.text("Email: contact@bibliotheque-vintage.dz", 20, y + 40);

        // Pied de page
        pdf.setFontSize(10);
        pdf.text("Merci pour votre achat chez Bibliothèque Vintage !", 105, 280, null, null, "center");

        // Télécharger le PDF
        pdf.save("facture-commande-<?php echo $id_commande; ?>.pdf");
    });
</script>

<?php include 'footer.php'; ?>
