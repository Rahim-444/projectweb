<?php
require_once 'functions.php';

// Rediriger si non connecté
if (!estConnecte()) {
    header('Location: login.php');
    exit;
}

$id_commande = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id_commande <= 0) {
    header('Location: commandes.php');
    exit;
}

$details = getDetailsCommande($_SESSION['id_utilisateur'], $id_commande);

if (empty($details)) {
    header('Location: commandes.php');
    exit;
}

include 'header.php';
?>

<section class="page-header">
    <h2>Détails de la Commande #<?php echo $id_commande; ?></h2>
</section>

<section class="details-commande">
    <div class="commande-info">
        <h3>Informations</h3>
        <ul>
            <li><strong>Commande :</strong> #<?php echo $id_commande; ?></li>
            <li><strong>Date :</strong> <?php echo date('d/m/Y H:i', strtotime($details['articles'][0]['date_commande'])); ?></li>
            <li><strong>Statut :</strong>
                <span class="statut-badge statut-<?php echo strtolower(str_replace(' ', '-', $details['articles'][0]['statut'])); ?>">
                    <?php echo $details['articles'][0]['statut']; ?>
                </span>
            </li>
            <li><strong>Total :</strong> <?php echo number_format($details['total'], 2, ',', ' '); ?> DZD</li>
        </ul>

        <?php if ($details['articles'][0]['statut'] === 'En attente'): ?>
            <div class="commande-actions">
                <a href="annuler_commande.php?id=<?php echo $id_commande; ?>" class="btn btn-danger" onclick="return confirm('Êtes-vous sûr de vouloir annuler cette commande ?')"><i class="fas fa-times"></i> Annuler la commande</a>
            </div>
        <?php endif; ?>
    </div>

    <div class="commande-articles">
        <h3>Articles</h3>
        <table class="articles-table">
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

    <div class="commande-actions">
        <button id="imprimer-details" class="btn btn-secondary"><i class="fas fa-file-pdf"></i> Imprimer les détails</button>
        <a href="commandes.php" class="btn btn-primary">Retour aux commandes</a>
    </div>
</section>

<script>
    // Script pour imprimer les détails de la commande en PDF
    document.getElementById('imprimer-details').addEventListener('click', function() {
        const {
            jsPDF
        } = window.jspdf;

        // Initialiser le PDF
        const pdf = new jsPDF();

        // Définir le titre
        pdf.setFontSize(18);
        pdf.text("Détails de la Commande #<?php echo $id_commande; ?>", 105, 20, null, null, "center");

        // Informations commande
        pdf.setFontSize(12);
        pdf.text("Commande: #<?php echo $id_commande; ?>", 20, 40);
        pdf.text("Date: <?php echo date('d/m/Y H:i', strtotime($details['articles'][0]['date_commande'])); ?>", 20, 50);
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

        // Pied de page
        pdf.setFontSize(10);
        pdf.text("Bibliothèque Vintage - Bouzareah Alger", 105, 280, null, null, "center");

        // Télécharger le PDF
        pdf.save("details-commande-<?php echo $id_commande; ?>.pdf");
    });
</script>

<?php include 'footer.php'; ?>
