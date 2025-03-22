<?php
// livre.php - Page détaillée d'un livre
require_once 'functions.php';

// Récupérer l'ID du livre
$id_livre = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Vérifier que l'ID est valide
if ($id_livre <= 0) {
    header('Location: index.php');
    exit;
}

// Récupérer les informations du livre
$livre = getLivreParId($id_livre);

// Si le livre n'existe pas, rediriger vers l'accueil
if (!$livre) {
    header('Location: index.php');
    exit;
}

include 'header.php';
?>

<section class="page-header">
    <h2><?php echo $livre['titre']; ?></h2>
</section>

<section class="livre-details">
    <div class="livre-image">
        <img src="images/<?php echo $livre['image']; ?>" alt="<?php echo $livre['titre']; ?>">
    </div>
    <div class="livre-info">
        <h2><?php echo $livre['titre']; ?></h2>
        <p class="auteur">Par <strong><?php echo $livre['auteur']; ?></strong></p>
        <p class="categorie">Catégorie: <a href="catalogue.php?categorie=<?php echo $livre['id_categorie']; ?>"><?php echo $livre['categorie_nom']; ?></a></p>
        <p class="prix"><?php echo number_format($livre['prix'], 2, ',', ' '); ?> €</p>
        <p class="stock <?php echo $livre['stock'] > 0 ? 'en-stock' : 'rupture-stock'; ?>">
            <?php echo $livre['stock'] > 0 ? 'En stock (' . $livre['stock'] . ' exemplaire(s))' : 'Rupture de stock'; ?>
        </p>
        <div class="livre-meta">
            <p><strong>Année de publication:</strong> <?php echo $livre['annee_publication']; ?></p>
            <p><strong>Éditeur:</strong> <?php echo $livre['editeur']; ?></p>
        </div>
        <div class="description">
            <h3>Description</h3>
            <p><?php echo nl2br($livre['description']); ?></p>
        </div>
        <?php if (estConnecte() && $livre['stock'] > 0): ?>
        <form action="ajouter_panier.php" method="post" class="ajout-panier-form">
            <div class="form-group">
                <label for="quantite">Quantité:</label>
                <input type="number" id="quantite" name="quantite" value="1" min="1" max="<?php echo $livre['stock']; ?>">
            </div>
            <input type="hidden" name="id_livre" value="<?php echo $livre['id_livre']; ?>">
            <button type="submit" class="btn btn-primary"><i class="fas fa-shopping-cart"></i> Ajouter au panier</button>
        </form>
        <?php elseif (!estConnecte() && $livre['stock'] > 0): ?>
        <a href="login.php" class="btn btn-primary"><i class="fas fa-shopping-cart"></i> Connectez-vous pour commander</a>
        <?php endif; ?>
        
        <div class="livre-actions">
            <button id="genererPDF" class="btn btn-secondary"><i class="fas fa-file-pdf"></i> Télécharger les détails en PDF</button>
            <button id="partager" class="btn btn-secondary"><i class="fas fa-share-alt"></i> Partager</button>
        </div>
    </div>
</section>

<section class="livres-similaires">
    <h3>Vous pourriez aussi aimer</h3>
    <?php
    // Récupérer d'autres livres de la même catégorie
    $livres_similaires = getLivresParCategorie($livre['id_categorie']);
    // Exclure le livre actuel et limiter à 4 livres
    $livres_a_afficher = [];
    $count = 0;
    foreach ($livres_similaires as $livre_sim) {
        if ($livre_sim['id_livre'] != $id_livre) {
            $livres_a_afficher[] = $livre_sim;
            $count++;
        }
        if ($count >= 4) break;
    }
    ?>
    
    <?php if (empty($livres_a_afficher)): ?>
    <p>Aucun livre similaire disponible.</p>
    <?php else: ?>
    <div class="livres-grid">
        <?php foreach ($livres_a_afficher as $livre_sim): ?>
        <div class="livre-card">
            <div class="livre-image">
                <a href="livre.php?id=<?php echo $livre_sim['id_livre']; ?>">
                    <img src="images/<?php echo $livre_sim['image']; ?>" alt="<?php echo $livre_sim['titre']; ?>">
                </a>
            </div>
            <div class="livre-info">
                <h3><?php echo $livre_sim['titre']; ?></h3>
                <p class="auteur"><?php echo $livre_sim['auteur']; ?></p>
                <p class="prix"><?php echo number_format($livre_sim['prix'], 2, ',', ' '); ?> €</p>
                <div class="livre-actions">
                    <a href="livre.php?id=<?php echo $livre_sim['id_livre']; ?>" class="btn btn-secondary">Détails</a>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</section>

<script>
// Script pour générer le PDF
document.getElementById('genererPDF').addEventListener('click', function() {
    const { jsPDF } = window.jspdf;
    
    // Initialiser le PDF
    const pdf = new jsPDF();
    
    // Définir le titre
    pdf.setFontSize(18);
    pdf.text("Détails du livre", 105, 20, null, null, "center");
    
    // Ajouter les informations du livre
    pdf.setFontSize(14);
    pdf.text("<?php echo addslashes($livre['titre']); ?>", 20, 40);
    
    pdf.setFontSize(12);
    pdf.text("Auteur: <?php echo addslashes($livre['auteur']); ?>", 20, 50);
    pdf.text("Catégorie: <?php echo addslashes($livre['categorie_nom']); ?>", 20, 60);
    pdf.text("Prix: <?php echo number_format($livre['prix'], 2, ',', ' '); ?> €", 20, 70);
    pdf.text("Année de publication: <?php echo $livre['annee_publication']; ?>", 20, 80);
    pdf.text("Éditeur: <?php echo addslashes($livre['editeur']); ?>", 20, 90);
    
    // Ajouter la description
    pdf.text("Description:", 20, 105);
    const splitDescription = pdf.splitTextToSize("<?php echo addslashes(str_replace("\n", " ", $livre['description'])); ?>", 170);
    pdf.text(splitDescription, 20, 115);
    
    // Ajouter les informations de la librairie
    pdf.setFontSize(10);
    pdf.text("Bibliothèque Vintage - 12 Rue des Livres, 75006 Paris - contact@bibliotheque-vintage.fr", 105, 280, null, null, "center");
    
    // Télécharger le PDF
    pdf.save("livre_<?php echo $livre['id_livre']; ?>.pdf");
});

// Script pour partager le livre
document.getElementById('partager').addEventListener('click', function() {
    if (navigator.share) {
        navigator.share({
            title: "<?php echo addslashes($livre['titre']); ?> - Bibliothèque Vintage",
            text: "Découvrez ce livre: <?php echo addslashes($livre['titre']); ?> par <?php echo addslashes($livre['auteur']); ?>",
            url: window.location.href
        }).then(() => {
            console.log('Partage réussi');
        }).catch((error) => {
            console.log('Erreur lors du partage', error);
            alert('Lien copié dans le presse-papier!');
        });
    } else {
        // Fallback pour les navigateurs qui ne supportent pas l'API Web Share
        const dummy = document.createElement('input');
        document.body.appendChild(dummy);
        dummy.value = window.location.href;
        dummy.select();
        document.execCommand('copy');
        document.body.removeChild(dummy);
        alert('Lien copié dans le presse-papier!');
    }
});
</script>

<?php include 'footer.php'; ?>
