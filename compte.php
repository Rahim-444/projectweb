<?php
require_once 'functions.php';

// Rediriger si non connecté
if (!estConnecte()) {
    header('Location: login.php');
    exit;
}

$utilisateur = getUtilisateur($_SESSION['id_utilisateur']);

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = trim($_POST['nom']);
    $prenom = trim($_POST['prenom']);
    $adresse = trim($_POST['adresse']);
    $ville = trim($_POST['ville']);
    $code_postal = trim($_POST['code_postal']);
    $telephone = trim($_POST['telephone']);

    $nouveau_mdp = $_POST['nouveau_mdp'];
    $confirmation = $_POST['confirmation'];

    if (empty($nom) || empty($prenom)) {
        $message = '<div class="alert alert-danger">Les champs Nom et Prénom sont obligatoires.</div>';
    } elseif (!empty($nouveau_mdp) && $nouveau_mdp !== $confirmation) {
        $message = '<div class="alert alert-danger">Les mots de passe ne correspondent pas.</div>';
    } elseif (!empty($nouveau_mdp) && strlen($nouveau_mdp) < 8) {
        $message = '<div class="alert alert-danger">Le mot de passe doit contenir au moins 8 caractères.</div>';
    } else {
        $conn = connectDB();

        $query = "UPDATE Utilisateurs SET nom = ?, prenom = ?, adresse = ?, ville = ?, code_postal = ?, telephone = ?";
        $params = [$nom, $prenom, $adresse, $ville, $code_postal, $telephone];
        $types = "ssssss";

        // Ajouter la mise à jour du mot de passe si fourni
        if (!empty($nouveau_mdp)) {
            $hash = password_hash($nouveau_mdp, PASSWORD_DEFAULT);
            $query .= ", mot_de_passe = ?";
            $params[] = $hash;
            $types .= "s";
        }

        $query .= " WHERE id_utilisateur = ?";
        $params[] = $_SESSION['id_utilisateur'];
        $types .= "i";

        $stmt = $conn->prepare($query);
        $stmt->bind_param($types, ...$params);

        if ($stmt->execute()) {
            $message = '<div class="alert alert-success">Vos informations ont été mises à jour avec succès.</div>';
            $utilisateur = getUtilisateur($_SESSION['id_utilisateur']);
        } else {
            $message = '<div class="alert alert-danger">Une erreur est survenue lors de la mise à jour de vos informations.</div>';
        }

        $stmt->close();
        $conn->close();
    }
}

include 'header.php';
?>

<section class="page-header">
    <h2>Mon Compte</h2>
</section>

<section class="compte">
    <?php echo $message; ?>

    <div class="compte-info">
        <h3>Informations personnelles</h3>
        <form method="post" action="compte.php">
            <div class="form-row">
                <div class="form-group">
                    <label for="nom">Nom :</label>
                    <input type="text" id="nom" name="nom" value="<?php echo htmlspecialchars($utilisateur['nom']); ?>" required>
                </div>
                <div class="form-group">
                    <label for="prenom">Prénom :</label>
                    <input type="text" id="prenom" name="prenom" value="<?php echo htmlspecialchars($utilisateur['prenom']); ?>" required>
                </div>
            </div>
            <div class="form-group">
                <label for="email">Email :</label>
                <input type="email" id="email" value="<?php echo htmlspecialchars($utilisateur['email']); ?>" readonly>
                <p class="help-text">L'adresse email ne peut pas être modifiée.</p>
            </div>
            <div class="form-group">
                <label for="adresse">Adresse :</label>
                <input type="text" id="adresse" name="adresse" value="<?php echo htmlspecialchars($utilisateur['adresse'] ?? ''); ?>">
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label for="ville">Ville :</label>
                    <input type="text" id="ville" name="ville" value="<?php echo htmlspecialchars($utilisateur['ville'] ?? ''); ?>">
                </div>
                <div class="form-group">
                    <label for="code_postal">Code postal :</label>
                    <input type="text" id="code_postal" name="code_postal" value="<?php echo htmlspecialchars($utilisateur['code_postal'] ?? ''); ?>">
                </div>
            </div>
            <div class="form-group">
                <label for="telephone">Téléphone :</label>
                <input type="tel" id="telephone" name="telephone" value="<?php echo htmlspecialchars($utilisateur['telephone'] ?? ''); ?>">
            </div>

            <h3>Modifier le mot de passe</h3>
            <div class="form-row">
                <div class="form-group">
                    <label for="nouveau_mdp">Nouveau mot de passe :</label>
                    <input type="password" id="nouveau_mdp" name="nouveau_mdp" minlength="8">
                    <p class="help-text">Laissez vide pour conserver le mot de passe actuel.</p>
                </div>
                <div class="form-group">
                    <label for="confirmation">Confirmer le mot de passe :</label>
                    <input type="password" id="confirmation" name="confirmation" minlength="8">
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Mettre à jour</button>
            </div>
        </form>
    </div>

    <div class="compte-links">
        <h3>Liens rapides</h3>
        <ul>
            <li><a href="commandes.php"><i class="fas fa-shopping-bag"></i> Mes commandes</a></li>
            <li><a href="panier.php"><i class="fas fa-shopping-cart"></i> Mon panier</a></li>
        </ul>
    </div>
</section>

<?php include 'footer.php'; ?>
