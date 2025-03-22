<?php
// supprimer_panier.php - Script pour supprimer un article du panier
require_once 'functions.php';

// Rediriger si non connecté
if (!estConnecte()) {
    header('Location: login.php');
    exit;
}

// Vérifier si le formulaire a été soumis
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_article = isset($_POST['id_article']) ? intval($_POST['id_article']) : 0;
    
    // Vérifier que l'ID est valide
    if ($id_article <= 0) {
        $_SESSION['erreur'] = 'Données invalides.';
        header('Location: panier.php');
        exit;
    }
    
    // Supprimer l'article
    $resultat = supprimerDuPanier($_SESSION['id_utilisateur'], $id_article);
    
    if ($resultat) {
        $_SESSION['succes'] = 'L\'article a été supprimé du panier.';
    } else {
        $_SESSION['erreur'] = 'Une erreur est survenue lors de la suppression.';
    }
    
    // Rediriger vers le panier
    header('Location: panier.php');
    exit;
} else {
    // Si accès direct sans POST, rediriger vers le panier
    header('Location: panier.php');
    exit;
}
?>
