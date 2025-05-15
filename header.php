<?php
require_once 'functions.php';
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bibliothèque Vintage - Livres rares et anciens</title>
    <link rel="stylesheet" href="css/style.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/js/all.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
</head>

<body>
    <header>
        <div class="logo">
            <h1><a href="index.php">Bibliothèque Vintage</a></h1>
            <p>Livres rares et éditions anciennes</p>
        </div>
        <nav>
            <ul>
                <li><a href="index.php">Accueil</a></li>
                <li><a href="catalogue.php">Catalogue</a></li>
                <li><a href="recherche.php">Recherche</a></li>
                <?php if (estConnecte()): ?>
                    <li><a href="compte.php">Mon Compte</a></li>
                    <li><a href="commandes.php">Mes Commandes</a></li>
                    <?php if (estAdmin()): ?>
                        <li><a href="admin.php">Administration</a></li>
                    <?php endif; ?>
                    <li><a href="logout.php">Déconnexion</a></li>
                <?php else: ?>
                    <li><a href="login.php">Connexion</a></li>
                    <li><a href="inscription.php">Inscription</a></li>
                <?php endif; ?>
                <li><a href="panier.php" class="panier-icon"><i class="fas fa-shopping-cart"></i>
                        <?php
                        if (estConnecte()) {
                            $panier = getPanier($_SESSION['id_utilisateur']);
                            $nbArticles = isset($panier['articles']) ? count($panier['articles']) : 0;
                            echo '<span class="badge">' . $nbArticles . '</span>';
                        }
                        ?>
                    </a></li>
            </ul>
        </nav>
    </header>
    <main>
