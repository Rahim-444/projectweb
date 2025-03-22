<?php
// login.php - Page de connexion
require_once 'functions.php';

// Rediriger si déjà connecté
if (estConnecte()) {
    header('Location: index.php');
    exit;
}

$erreur = '';

// Traitement du formulaire de connexion
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $mot_de_passe = $_POST['mot_de_passe'];
    
    if (empty($email) || empty($mot_de_passe)) {
        $erreur = 'Tous les champs sont obligatoires.';
    } else {
        $conn = connectDB();
        $query = "SELECT id_utilisateur, mot_de_passe, est_admin FROM Utilisateurs WHERE email = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            
            if (password_verify($mot_de_passe, $user['mot_de_passe'])) {
                // Connexion réussie
                $_SESSION['id_utilisateur'] = $user['id_utilisateur'];
                $_SESSION['est_admin'] = $user['est_admin'] == 1;
                
                // Rediriger vers la page d'accueil
                header('Location: index.php');
                exit;
            } else {
                $erreur = 'Mot de passe incorrect.';
            }
        } else {
            $erreur = 'Aucun compte ne correspond à cette adresse email.';
        }
        
        $stmt->close();
        $conn->close();
    }
}

include 'header.php';
?>

<section class="page-header">
    <h2>Connexion</h2>
</section>

<section class="auth-form">
    <?php if (!empty($erreur)): ?>
    <div class="alert alert-danger">
        <?php echo $erreur; ?>
    </div>
    <?php endif; ?>
    
    <form method="post" action="login.php">
        <div class="form-group">
            <label for="email">Email :</label>
            <input type="email" id="email" name="email" required>
        </div>
        <div class="form-group">
            <label for="mot_de_passe">Mot de passe :</label>
            <input type="password" id="mot_de_passe" name="mot_de_passe" required>
        </div>
        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Se connecter</button>
            <p>Pas encore de compte ? <a href="inscription.php">S'inscrire</a></p>
        </div>
    </form>
</section>

<?php include 'footer.php'; ?>
