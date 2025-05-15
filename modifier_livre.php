<?php
require_once 'functions.php';

if (!estConnecte() || !estAdmin()) {
    header('Location: index.php');
    exit;
}

$message = '';
$livre = null;

if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $id_livre = intval($_GET['id']);
    $livre = getLivreParId($id_livre);

    if (!$livre) {
        header('Location: admin.php');
        exit;
    }
} else {
    header('Location: admin.php');
    exit;
}

$categories = getToutesCategories();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titre = trim($_POST['titre']);
    $auteur = trim($_POST['auteur']);
    $id_categorie = intval($_POST['categorie']);
    $description = trim($_POST['description']);
    $prix = floatval($_POST['prix']);
    $annee = !empty($_POST['annee']) ? intval($_POST['annee']) : 0;
    $editeur = trim($_POST['editeur']);
    $stock = intval($_POST['stock']);

    $image = !empty($livre['image']) ? $livre['image'] : 'default.jpg'; // Conserver l'image existante ou utiliser celle par défaut

    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK && $_FILES['image']['size'] > 0) {
        $upload_dir = 'images/';
        $temp_name = $_FILES['image']['tmp_name'];
        $image_name = basename($_FILES['image']['name']);
        $image_name = time() . '_' . $image_name; // Préfixer avec un timestamp pour éviter les doublons

        // Vérifier que le dossier d'upload existe
        if (!file_exists($upload_dir) && !is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }

        // S'assurer que le dossier est accessible en écriture
        if (is_writable($upload_dir)) {
            if (move_uploaded_file($temp_name, $upload_dir . $image_name)) {
                // Si une nouvelle image est téléchargée avec succès, la définir comme image du livre
                $image = $image_name;
            } else {
                $message .= '<div class="alert alert-warning">Échec du téléchargement de l\'image. L\'image existante sera conservée.</div>';
            }
        } else {
            $message .= '<div class="alert alert-warning">Le répertoire de téléchargement n\'est pas accessible en écriture. L\'image existante sera conservée.</div>';
        }
    }

    if (empty($titre) || empty($auteur) || $prix <= 0 || $stock < 0) {
        $message = '<div class="alert alert-danger">Veuillez remplir tous les champs obligatoires.</div>';
    } else {
        $resultat = modifierLivre($id_livre, $titre, $auteur, $id_categorie, $description, $prix, $annee, $editeur, $stock, $image);

        if ($resultat) {
            $message = '<div class="alert alert-success">Le livre a été modifié avec succès.</div>';
            $livre = getLivreParId($id_livre);
        } else {
            $message = '<div class="alert alert-danger">Une erreur est survenue lors de la modification du livre.</div>';
        }
    }
}

include 'header.php';
?>

<section class="page-header bg-light py-4">
    <div class="container">
        <h2 class="mb-0">Modifier un livre</h2>
        <nav class="breadcrumb bg-transparent p-0 mb-0">
            <a class="breadcrumb-item" href="index.php">Accueil</a>
            <a class="breadcrumb-item" href="admin.php">Administration</a>
            <span class="breadcrumb-item active">Modifier un livre</span>
        </nav>
    </div>
</section>

<section class="modifier-livre py-5">
    <div class="container">
        <?php echo $message; ?>

        <div class="row mb-4">
            <div class="col-12">
                <a href="admin.php#gerer-livres" class="btn btn-secondary">
                    <i class="fas fa-arrow-left mr-2"></i> Retour à la gestion des livres
                </a>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-8">
                <div class="card shadow-sm">
                    <div class="card-header bg-primary text-white">
                        <h3 class="card-title mb-0">Modifier "<?php echo htmlspecialchars($livre['titre']); ?>"</h3>
                    </div>

                    <div class="card-body">
                        <form method="post" action="modifier_livre.php?id=<?php echo $id_livre; ?>" enctype="multipart/form-data">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="titre"><strong>Titre :</strong></label>
                                        <input type="text" id="titre" name="titre" class="form-control" value="<?php echo htmlspecialchars($livre['titre']); ?>" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="auteur"><strong>Auteur :</strong></label>
                                        <input type="text" id="auteur" name="auteur" class="form-control" value="<?php echo htmlspecialchars($livre['auteur']); ?>" required>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="categorie"><strong>Catégorie :</strong></label>
                                        <select id="categorie" name="categorie" class="form-control" required>
                                            <option value="">-- Choisir une catégorie --</option>
                                            <?php foreach ($categories as $categorie): ?>
                                                <option value="<?php echo $categorie['id_categorie']; ?>" <?php echo ($categorie['id_categorie'] == $livre['id_categorie']) ? 'selected' : ''; ?>>
                                                    <?php echo htmlspecialchars($categorie['nom']); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="prix"><strong>Prix (DZD) :</strong></label>
                                        <div class="input-group">
                                            <input type="number" id="prix" name="prix" class="form-control" step="0.01" min="0" value="<?php echo $livre['prix']; ?>" required>
                                            <div class="input-group-append">
                                                <span class="input-group-text">DZD</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="annee"><strong>Année de publication :</strong></label>
                                        <input type="number" id="annee" name="annee" class="form-control" min="1400" max="<?php echo date('Y'); ?>" value="<?php echo isset($livre['annee_publication']) ? $livre['annee_publication'] : ''; ?>">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="editeur"><strong>Éditeur :</strong></label>
                                        <input type="text" id="editeur" name="editeur" class="form-control" value="<?php echo htmlspecialchars($livre['editeur']); ?>">
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="stock"><strong>Stock :</strong></label>
                                        <input type="number" id="stock" name="stock" class="form-control" min="0" value="<?php echo $livre['stock']; ?>" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="image"><strong>Image :</strong></label>
                                        <input type="file" id="image" name="image" class="form-control-file" accept="image/*">
                                        <small class="form-text text-muted">Laissez vide pour conserver l'image actuelle</small>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="description"><strong>Description :</strong></label>
                                <textarea id="description" name="description" class="form-control" rows="5"><?php echo htmlspecialchars($livre['description']); ?></textarea>
                            </div>

                            <div class="form-actions text-center mt-4">
                                <button type="submit" class="btn btn-success btn-lg px-5">
                                    <i class="fas fa-save mr-2"></i> Enregistrer les modifications
                                </button>
                                <a href="admin.php#gerer-livres" class="btn btn-outline-secondary btn-lg ml-2">
                                    <i class="fas fa-times mr-2"></i> Annuler
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card shadow-sm">
                    <div class="card-header bg-secondary text-white">
                        <h4 class="card-title mb-0">Image actuelle</h4>
                    </div>
                    <div class="card-body text-center">
                        <?php if (!empty($livre['image']) && file_exists('images/' . $livre['image'])): ?>
                            <img src="images/<?php echo $livre['image']; ?>" alt="<?php echo htmlspecialchars($livre['titre']); ?>" class="img-fluid mb-3" style="max-height: 300px;">
                            <p class="mb-0 text-muted"><?php echo $livre['image']; ?></p>
                        <?php else: ?>
                            <img src="images/default.jpg" alt="Image par défaut" class="img-fluid mb-3" style="max-height: 300px;">
                            <p class="mb-0 text-muted">Image par défaut</p>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="card shadow-sm mt-4">
                    <div class="card-header bg-info text-white">
                        <h4 class="card-title mb-0">Informations</h4>
                    </div>
                    <div class="card-body">
                        <p><strong>ID du livre :</strong> <?php echo $livre['id_livre']; ?></p>
                        <p><strong>Nombre en stock :</strong> <?php echo $livre['stock']; ?> exemplaire(s)</p>
                        <p><strong>Prix actuel :</strong> <?php echo number_format($livre['prix'], 2, ',', ' '); ?> DZD</p>
                        <?php if (isset($livre['date_ajout']) && !empty($livre['date_ajout'])): ?>
                            <p><strong>Date d'ajout :</strong> <?php echo date('d/m/Y', strtotime($livre['date_ajout'])); ?></p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Preview de l'image quand une nouvelle est sélectionnée
        const imageInput = document.getElementById('image');
        if (imageInput) {
            imageInput.addEventListener('change', function() {
                if (this.files && this.files[0]) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        const previewContainer = document.querySelector('.card-body.text-center');
                        if (previewContainer) {
                            const img = previewContainer.querySelector('img');
                            if (img) {
                                img.src = e.target.result;
                                const caption = previewContainer.querySelector('p');
                                if (caption) {
                                    caption.textContent = 'Nouvelle image (non enregistrée)';
                                }
                            }
                        }
                    };
                    reader.readAsDataURL(this.files[0]);
                }
            });
        }
    });
</script>

<?php include 'footer.php'; ?>
