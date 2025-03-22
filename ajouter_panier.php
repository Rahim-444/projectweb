<?php
// ajouter_panier.php - Script pour ajouter un livre au panier
require_once 'functions.php';

// Rediriger si non connecté
if (!estConnecte()) {
    header('Location: login.php');
    exit;
}

// Vérifier si le formulaire a été soumis
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_livre = isset($_POST['id_livre']) ? intval($_POST['id_livre']) : 0;
    $quantite = isset($_POST['quantite']) ? intval($_POST['quantite']) : 1;
    
    // Vérifier que l'ID et la quantité sont valides
    if ($id_livre <= 0 || $quantite <= 0) {
        $_SESSION['erreur'] = 'Données invalides.';
        header('Location: catalogue.php');
        exit;
    }
    
    // Vérifier que le livre existe et qu'il y a assez de stock
    $livre = getLivreParId($id_livre);
    if (!$livre) {
        $_SESSION['erreur'] = 'Ce livre n\'existe pas.';
        header('Location: catalogue.php');
        exit;
    }
    
    if ($quantite > $livre['stock']) {
        $_SESSION['erreur'] = 'Stock insuffisant. Il ne reste que ' . $livre['stock'] . ' exemplaire(s).';
        header('Location: livre.php?id=' . $id_livre);
        exit;
    }
    
    // Ajouter au panier
    $resultat = ajouterAuPanier($_SESSION['id_utilisateur'], $id_livre, $quantite);
    
    if ($resultat) {
        $_SESSION['succes'] = 'Le livre a été ajouté à votre panier.';
    } else {
        $_SESSION['erreur'] = 'Une erreur est survenue lors de l\'ajout au panier.';
    }
    
    // Rediriger vers la page précédente ou le panier
    if (isset($_SERVER['HTTP_REFERER'])) {
        header('Location: ' . $_SERVER['HTTP_REFERER']);
    } else {
        header('Location: panier.php');
    }
    exit;
} else {
    // Si accès direct sans POST, rediriger vers le catalogue
    header('Location: catalogue.php');
    exit;
}
