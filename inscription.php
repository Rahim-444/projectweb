<?php
require_once 'functions.php';

if (estConnecte()) {
    header('Location: index.php');
    exit;
}

$erreur = '';
$succes = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = trim($_POST['nom']);
    $prenom = trim($_POST['prenom']);
    $email = trim($_POST['email']);
    $mot_de_passe = $_POST['mot_de_passe'];
    $confirmation = $_POST['confirmation'];
    $adresse = trim($_POST['adresse']);
    $ville = trim($_POST['ville']);
    $code_postal = trim($_POST['code_postal']);
    $telephone = trim($_POST['telephone']);

    // Validation des champs
    if (empty($nom) || empty($prenom) || empty($email) || empty($mot_de_passe)) {
        $erreur = 'Les champs Nom, Prénom, Email et Mot de passe sont obligatoires.';
    } elseif ($mot_de_passe !== $confirmation) {
        $erreur = 'Les mots de passe ne correspondent pas.';
    } elseif (strlen($mot_de_passe) < 8) {
        $erreur = 'Le mot de passe doit contenir au moins 8 caractères.';
    } else {
        $conn = connectDB();

        // he shouldn't exist in db before
        $query = "SELECT id_utilisateur FROM Utilisateurs WHERE email = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $erreur = 'Cette adresse email est déjà utilisée.';
        } else {
            $hash = password_hash($mot_de_passe, PASSWORD_DEFAULT);

            $query = "INSERT INTO Utilisateurs (nom, prenom, email, mot_de_passe, adresse, ville, code_postal, telephone)
                     VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("ssssssss", $nom, $prenom, $email, $hash, $adresse, $ville, $code_postal, $telephone);

            if ($stmt->execute()) {
                $succes = 'Compte créé avec succès. Vous pouvez maintenant vous connecter.';
            } else {
                $erreur = 'Une erreur est survenue lors de la création du compte.';
            }
        }

        $stmt->close();
        $conn->close();
    }
}

include 'header.php';
?>

<section class="page-header">
    <h2>Inscription</h2>
</section>

<section class="auth-form">
    <?php if (!empty($erreur)): ?>
        <div class="alert alert-danger">
            <?php echo $erreur; ?>
        </div>
    <?php endif; ?>

    <?php if (!empty($succes)): ?>
        <div class="alert alert-success">
            <?php echo $succes; ?>
            <p><a href="login.php">Se connecter</a></p>
        </div>
    <?php else: ?>
        <form method="post" action="inscription.php">
            <div class="form-row">
                <div class="form-group">
                    <label for="nom">Nom :</label>
                    <input type="text" id="nom" name="nom" required>
                </div>
                <div class="form-group">
                    <label for="prenom">Prénom :</label>
                    <input type="text" id="prenom" name="prenom" required>
                </div>
            </div>
            <div class="form-group">
                <label for="email">Email :</label>
                <input type="email" id="email" name="email" required>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label for="mot_de_passe">Mot de passe :</label>
                    <input type="password" id="mot_de_passe" name="mot_de_passe" required minlength="8">
                </div>
                <div class="form-group">
                    <label for="confirmation">Confirmer le mot de passe :</label>
                    <input type="password" id="confirmation" name="confirmation" required minlength="8">
                </div>
            </div>
            <div class="form-group">
                <label for="adresse">Adresse :</label>
                <input type="text" id="adresse" name="adresse">
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label for="ville">Ville :</label>
                    <input type="text" id="ville" name="ville">
                </div>
                <div class="form-group">
                    <label for="code_postal">Code postal :</label>
                    <input type="text" id="code_postal" name="code_postal">
                </div>
            </div>
            <div class="form-group">
                <label for="telephone">Téléphone :</label>
                <input type="tel" id="telephone" name="telephone">
            </div>
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">S'inscrire</button>
                <p>Déjà inscrit ? <a href="login.php">Se connecter</a></p>
            </div>
        </form>
    <?php endif; ?>
</section>

<?php include 'footer.php'; ?>
