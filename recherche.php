<?php
require_once 'functions.php';

$terme = isset($_GET['terme']) ? trim($_GET['terme']) : '';
$id_categorie = isset($_GET['categorie']) ? intval($_GET['categorie']) : null;
$prix_min = isset($_GET['prix_min']) && $_GET['prix_min'] !== '' ? floatval($_GET['prix_min']) : null;
$prix_max = isset($_GET['prix_max']) && $_GET['prix_max'] !== '' ? floatval($_GET['prix_max']) : null;

$livres = rechercherLivres($terme, $id_categorie, $prix_min, $prix_max);

$categories = getToutesCategories();

include 'header.php';
?>

<section class="page-header">
    <h2>Recherche avancée</h2>
</section>

<section class="recherche">
    <div class="filters">
        <h3>Filtres</h3>
        <form action="recherche.php" method="get">
            <div class="form-group">
                <label for="terme">Recherche :</label>
                <input type="text" id="terme" name="terme" placeholder="Titre, auteur, description..." value="<?php echo htmlspecialchars($terme); ?>">
            </div>
            <div class="form-group">
                <label for="categorie">Catégorie :</label>
                <select id="categorie" name="categorie">
                    <option value="">Toutes les catégories</option>
                    <?php foreach ($categories as $categorie): ?>
                        <option value="<?php echo $categorie['id_categorie']; ?>" <?php echo ($id_categorie == $categorie['id_categorie']) ? 'selected' : ''; ?>>
                            <?php echo $categorie['nom']; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="prix_min">Prix minimum :</label>
                <input type="number" id="prix_min" name="prix_min" min="0" step="0.01" value="<?php echo $prix_min !== null ? $prix_min : ''; ?>">
            </div>
            <div class="form-group">
                <label for="prix_max">Prix maximum :</label>
                <input type="number" id="prix_max" name="prix_max" min="0" step="0.01" value="<?php echo $prix_max !== null ? $prix_max : ''; ?>">
            </div>
            <button type="submit" class="btn btn-primary">Rechercher</button>
        </form>
    </div>
    <div class="resultats">
        <h3>Résultats</h3>
        <?php if (empty($livres)): ?>
            <p class="no-results">Aucun livre ne correspond à votre recherche.</p>
        <?php else: ?>
            <p class="results-count"><?php echo count($livres); ?> livre(s) trouvé(s)</p>
            <div class="livres-grid">
                <?php foreach ($livres as $livre): ?>
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
                            <p class="prix"><?php echo number_format($livre['prix'], 2, ',', ' '); ?> DZD</p>
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
        <?php endif; ?>
    </div>
</section>

<?php include 'footer.php'; ?>
