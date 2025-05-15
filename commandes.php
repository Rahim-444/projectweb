<?php
require_once 'functions.php';

// Rediriger si non connecté
if (!estConnecte()) {
    header('Location: login.php');
    exit;
}

$commandes = getHistoriqueCommandes($_SESSION['id_utilisateur']);

include 'header.php';
?>

<section class="page-header">
    <h2>Mes Commandes</h2>
</section>

<section class="commandes">
    <?php if (empty($commandes)): ?>
        <div class="no-commandes">
            <i class="fas fa-shopping-bag fa-3x"></i>
            <h3>Vous n'avez pas encore passé de commande</h3>
            <p>Parcourez notre catalogue pour découvrir nos livres rares et anciens.</p>
            <a href="catalogue.php" class="btn btn-primary">Voir le catalogue</a>
        </div>
    <?php else: ?>
        <div class="commandes-list">
            <table class="commandes-table">
                <thead>
                    <tr>
                        <th>Commande</th>
                        <th>Date</th>
                        <th>Articles</th>
                        <th>Statut</th>
                        <th>Total</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($commandes as $commande): ?>
                        <tr>
                            <td>#<?php echo $commande['id_commande']; ?></td>
                            <td><?php echo date('d/m/Y H:i', strtotime($commande['date_commande'])); ?></td>
                            <td><?php echo $commande['nombre_articles']; ?> article(s)</td>
                            <td>
                                <span class="statut-badge statut-<?php echo strtolower(str_replace(' ', '-', $commande['statut'])); ?>">
                                    <?php echo $commande['statut']; ?>
                                </span>
                            </td>
                            <td><?php echo number_format($commande['total'], 2, ',', ' '); ?> DZD</td>
                            <td class="actions">
                                <a href="details_commande.php?id=<?php echo $commande['id_commande']; ?>" class="btn btn-xs btn-secondary"><i class="fas fa-eye"></i> Détails</a>
                                <?php if ($commande['statut'] === 'En attente'): ?>
                                    <a href="annuler_commande.php?id=<?php echo $commande['id_commande']; ?>" class="btn btn-xs btn-danger" onclick="return confirm('Êtes-vous sûr de vouloir annuler cette commande ?')"><i class="fas fa-times"></i> Annuler</a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div class="commandes-export">
            <button id="export-commandes-pdf" class="btn btn-secondary"><i class="fas fa-file-pdf"></i> Exporter l'historique en PDF</button>
        </div>
    <?php endif; ?>
</section>

<script>
    document.getElementById('export-commandes-pdf').addEventListener('click', function() {
        const {
            jsPDF
        } = window.jspdf;

        const pdf = new jsPDF();

        pdf.setFontSize(18);
        pdf.text("Historique des Commandes - Bibliothèque Vintage", 105, 20, null, null, "center");

        pdf.setFontSize(10);
        pdf.text("Date d'impression: " + new Date().toLocaleDateString(), 20, 30);

        pdf.setFontSize(12);
        pdf.text("Commande", 20, 40);
        pdf.text("Date", 60, 40);
        pdf.text("Statut", 100, 40);
        pdf.text("Total", 150, 40);

        pdf.line(20, 45, 190, 45);

        pdf.setFontSize(10);
        let y = 55;

        <?php foreach ($commandes as $commande): ?>
            pdf.text("#<?php echo $commande['id_commande']; ?>", 20, y);
            pdf.text("<?php echo date('d/m/Y', strtotime($commande['date_commande'])); ?>", 60, y);
            pdf.text("<?php echo $commande['statut']; ?>", 100, y);
            pdf.text("<?php echo number_format($commande['total'], 2, ',', ' '); ?> DZD", 150, y);
            y += 10;

            if (y > 250) {
                pdf.addPage();
                y = 20;
            }
        <?php endforeach; ?>

        // Pied de page
        pdf.setFontSize(10);
        pdf.text("Bibliothèque Vintage - Bouzareah Alger", 105, 280, null, null, "center");

        // Télécharger le PDF
        pdf.save("historique-commandes.pdf");
    });
</script>

<?php include 'footer.php'; ?>
