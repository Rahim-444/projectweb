<?php
require_once 'functions.php';

if (!estConnecte() || !estAdmin()) {
    header('Location: index.php');
    exit;
}

$categories = getToutesCategories();
$livres = getTousLivres();
$commandes = getAllCommandes();

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titre = trim($_POST['titre']);
    $auteur = trim($_POST['auteur']);
    $id_categorie = intval($_POST['categorie']);
    $description = trim($_POST['description']);
    $prix = floatval($_POST['prix']);
    $annee = intval($_POST['annee']);
    $editeur = trim($_POST['editeur']);
    $stock = intval($_POST['stock']);

    $image = 'default.jpg';
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = 'images/';
        $temp_name = $_FILES['image']['tmp_name'];
        $image_name = basename($_FILES['image']['name']);
        $image_name = time() . '_' . $image_name;

        if (move_uploaded_file($temp_name, $upload_dir . $image_name)) {
            $image = $image_name;
        }
    }

    if (empty($titre) || empty($auteur) || $prix <= 0 || $stock < 0) {
        $message = '<div class="alert alert-danger">Veuillez remplir tous les champs obligatoires.</div>';
    } else {
        $id_livre = ajouterLivre($titre, $auteur, $id_categorie, $description, $prix, $annee, $editeur, $stock, $image);

        if ($id_livre) {
            $message = '<div class="alert alert-success">Le livre a été ajouté avec succès.</div>';
            $livres = getTousLivres();
        } else {
            $message = '<div class="alert alert-danger">Une erreur est survenue lors de l\'ajout du livre.</div>';
        }
    }
}

include 'header.php';
?>

<section class="page-header">
    <h2>Administration</h2>
</section>

<section class="admin">
    <div class="admin-sidebar">
        <ul class="admin-menu">
            <li class="active"><a href="#ajouter-livre">Ajouter un livre</a></li>
            <li><a href="#gerer-livres">Gérer les livres</a></li>
            <li><a href="#gerer-commandes">Gérer les commandes</a></li>
        </ul>
    </div>

    <div class="admin-content">
        <?php
        if (isset($_SESSION['succes'])) {
            echo '<div class="alert alert-success">' . $_SESSION['succes'] . '</div>';
            unset($_SESSION['succes']);
        }
        if (isset($_SESSION['erreur'])) {
            echo '<div class="alert alert-danger">' . $_SESSION['erreur'] . '</div>';
            unset($_SESSION['erreur']);
        }
        echo $message;
        ?>

        <div id="ajouter-livre" class="admin-section active">
            <h3>Ajouter un nouveau livre</h3>
            <form method="post" action="admin.php" enctype="multipart/form-data">
                <div class="form-row">
                    <div class="form-group">
                        <label for="titre">Titre :</label>
                        <input type="text" id="titre" name="titre" required>
                    </div>
                    <div class="form-group">
                        <label for="auteur">Auteur :</label>
                        <input type="text" id="auteur" name="auteur" required>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="categorie">Catégorie :</label>
                        <select id="categorie" name="categorie" required>
                            <option value="">-- Choisir une catégorie --</option>
                            <?php foreach ($categories as $categorie): ?>
                                <option value="<?php echo $categorie['id_categorie']; ?>"><?php echo $categorie['nom']; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="prix">Prix (DZD) :</label>
                        <input type="number" id="prix" name="prix" step="0.01" min="0" required>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="annee">Année de publication :</label>
                        <input type="number" id="annee" name="annee" min="1400" max="<?php echo date('Y'); ?>">
                    </div>
                    <div class="form-group">
                        <label for="editeur">Éditeur :</label>
                        <input type="text" id="editeur" name="editeur">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="stock">Stock :</label>
                        <input type="number" id="stock" name="stock" min="0" required>
                    </div>
                    <div class="form-group">
                        <label for="image">Image :</label>
                        <input type="file" id="image" name="image" accept="image/*">
                    </div>
                </div>
                <div class="form-group">
                    <label for="description">Description :</label>
                    <textarea id="description" name="description" rows="5"></textarea>
                </div>
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Ajouter le livre</button>
                </div>
            </form>
        </div>

        <div id="gerer-livres" class="admin-section">
            <h3>Gérer les livres</h3>
            <div class="livres-list">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Image</th>
                            <th>Titre</th>
                            <th>Auteur</th>
                            <th>Catégorie</th>
                            <th>Prix</th>
                            <th>Stock</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($livres as $livre): ?>
                            <tr>
                                <td><?php echo $livre['id_livre']; ?></td>
                                <td><img src="images/<?php echo $livre['image']; ?>" alt="<?php echo $livre['titre']; ?>" width="50"></td>
                                <td><?php echo $livre['titre']; ?></td>
                                <td><?php echo $livre['auteur']; ?></td>
                                <td><?php echo $livre['categorie_nom']; ?></td>
                                <td><?php echo number_format($livre['prix'], 2, ',', ' '); ?> DZD</td>
                                <td><?php echo $livre['stock']; ?></td>
                                <td class="actions">
                                    <a href="modifier_livre.php?id=<?php echo $livre['id_livre']; ?>" class="btn btn-xs btn-secondary"><i class="fas fa-edit"></i> Modifier</a>
                                    <a href="supprimer_livre.php?id=<?php echo $livre['id_livre']; ?>" class="btn btn-xs btn-danger" onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce livre ?')"><i class="fas fa-trash"></i> Supprimer</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div id="gerer-commandes" class="admin-section">
            <h3>Gérer les commandes</h3>
            <div class="commandes-list">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Client</th>
                            <th>Date</th>
                            <th>Statut</th>
                            <th>Total</th>
                            <th>Articles</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($commandes)): ?>
                            <tr>
                                <td colspan="7" class="no-data">Aucune commande n'a été trouvée.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($commandes as $commande): ?>
                                <tr
                                    class="commande-row"
                                    onclick="window.location.href='details_commande.php?id=<?php echo $commande['id_commande']; ?>'">
                                    <td><?php echo $commande['id_commande']; ?></td>
                                    <td>
                                        <?php echo $commande['prenom'] . ' ' . $commande['nom']; ?><br>
                                        <small><?php echo $commande['email']; ?></small>
                                    </td>
                                    <td><?php echo date('d/m/Y H:i', strtotime($commande['date_commande'])); ?></td>
                                    <td class="status-<?php echo strtolower(str_replace(' ', '-', $commande['statut'])); ?>">
                                        <?php echo ucfirst($commande['statut']); ?>
                                    </td>
                                    <td><?php echo number_format($commande['total'], 2, ',', ' '); ?> DZD</td>
                                    <td><?php echo $commande['nombre_articles']; ?></td>
                                    <td class="actions">
                                        <?php if ($commande['statut'] === 'En attente'): ?>
                                            <form action="update_commande.php" method="post" style="display: inline;">
                                                <input type="hidden" name="id_commande" value="<?php echo $commande['id_commande']; ?>">
                                                <input type="hidden" name="statut" value="confirmee">
                                                <button type="submit" class="btn btn-xs btn-success" onclick="return confirm('Accepter cette commande ?')">
                                                    <i class="fas fa-check"></i> Accepter
                                                </button>
                                            </form>
                                            <form action="update_commande.php" method="post" style="display: inline;">
                                                <input type="hidden" name="id_commande" value="<?php echo $commande['id_commande']; ?>">
                                                <input type="hidden" name="statut" value="annulee">
                                                <button type="submit" class="btn btn-xs btn-danger" onclick="return confirm('Rejeter cette commande ?')">
                                                    <i class="fas fa-times"></i> Rejeter
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</section>

<?php include 'footer.php'; ?>
