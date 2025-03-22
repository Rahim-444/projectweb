<?php
// modifier_panier.php - Script pour modifier la quantité d'un article dans le panier
require_once 'functions.php';

// Rediriger si non connecté
if (!estConnecte()) {
    header('Location: login.php');
    exit;
}

// Vérifier si le formulaire a été soumis
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_article = isset($_POST['id_article']) ? intval($_POST['id_article']) : 0;
    $quantite = isset($_POST['quantite']) ? intval($_POST['quantite']) : 1;
    
    // Vérifier que l'ID et la quantité sont valides
    if ($id_article <= 0 || $quantite <= 0) {
        $_SESSION['erreur'] = 'Données invalides.';
        header('Location: panier.php');
        exit;
    }
    
    // Mettre à jour la quantité
    $resultat = mettreAJourPanier($_SESSION['id_utilisateur'], $id_article, $quantite);
    
    if ($resultat) {
        $_SESSION['succes'] = 'La quantité a été mise à jour.';
    } else {
        $_SESSION['erreur'] = 'Une erreur est survenue lors de la mise à jour.';
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
