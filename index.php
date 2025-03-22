<?php
// index.php - Page d'accueil
require_once 'functions.php';

// Récupérer les 6 derniers livres ajoutés
$livres_recents = getTousLivres();
$livres_recents = array_slice($livres_recents, 0, 6);

// Récupérer toutes les catégories
$categories = getToutesCategories();

include 'header.php';
?>

<section class="hero">
    <div class="hero-content">
        <h2>Découvrez notre collection de livres rares et anciens</h2>
        <p>Des éditions originales aux ouvrages vintage, plongez dans l'univers fascinant de la littérature d'exception.</p>
        <a href="catalogue.php" class="btn btn-primary">Parcourir le catalogue</a>
    </div>
</section>

<section class="nouveautes">
    <h2>Nouveautés</h2>
    <div class="livres-grid">
        <?php foreach ($livres_recents as $livre): ?>
        <div class="livre-card">
            <div class="livre-image">
                <a href="livre.php?id=<?php echo $livre['id_livre']; ?>">
                    <img src="images/<?php echo $livre['image']; ?>" alt="<?php echo $livre['titre']; ?>">
                </a>
            </div>
            <div class="livre-info">
                <h3><?php echo $livre['titre']; ?></h3>
                <p class="auteur"><?php echo $livre['auteur']; ?></p>
                <p class="categorie"><?php echo $livre['categorie_nom']; ?></p>
                <p class="prix"><?php echo number_format($livre['prix'], 2, ',', ' '); ?> €</p>
                <div class="livre-actions">
                    <a href="livre.php?id=<?php echo $livre['id_livre']; ?>" class="btn btn-secondary">Détails</a>
                    <?php if (estConnecte()): ?>
                    <form action="ajouter_panier.php" method="post">
                        <input type="hidden" name="id_livre" value="<?php echo $livre['id_livre']; ?>">
                        <input type="hidden" name="quantite" value="1">
                        <button type="submit" class="btn btn-primary"><i class="fas fa-shopping-cart"></i> Ajouter</button>
                    </form>
                    <?php else: ?>
                    <a href="login.php" class="btn btn-primary"><i class="fas fa-shopping-cart"></i> Ajouter</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</section>

<section class="categories">
    <h2>Explorez nos catégories</h2>
    <div class="categories-grid">
        <?php foreach ($categories as $categorie): ?>
        <a href="catalogue.php?categorie=<?php echo $categorie['id_categorie']; ?>" class="categorie-card">
            <h3><?php echo $categorie['nom']; ?></h3>
            <p><?php echo $categorie['description']; ?></p>
        </a>
        <?php endforeach; ?>
    </div>
</section>

<section class="about">
    <div class="about-content">
        <div class="about-text">
            <h2>Notre passion pour les livres anciens</h2>
            <p>Depuis 1985, Bibliothèque Vintage s'efforce de dénicher les plus beaux ouvrages anciens et de les proposer aux amateurs de littérature et aux collectionneurs.</p>
            <p>Chaque livre a sa propre histoire, et nous sommes fiers de pouvoir les partager avec vous.</p>
            <a href="#" class="btn btn-secondary">En savoir plus</a>
        </div>
        <div class="about-image">
            <img src="images/bookstore.jpg" alt="Notre librairie">
        </div>
    </div>
</section>

<?php include 'footer.php'; ?>
