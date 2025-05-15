<?php
require_once 'functions.php';

if (!estConnecte()) {
    header('Location: login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_article = isset($_POST['id_article']) ? intval($_POST['id_article']) : 0;
    $quantite = isset($_POST['quantite']) ? intval($_POST['quantite']) : 1;

    if ($id_article <= 0 || $quantite <= 0) {
        $_SESSION['erreur'] = 'Données invalides.';
        header('Location: panier.php');
        exit;
    }

    $resultat = mettreAJourPanier($_SESSION['id_utilisateur'], $id_article, $quantite);

    if ($resultat) {
        $_SESSION['succes'] = 'La quantité a été mise à jour.';
    } else {
        $_SESSION['erreur'] = 'Une erreur est survenue lors de la mise à jour.';
    }

    header('Location: panier.php');
    exit;
} else {
    header('Location: panier.php');
    exit;
}
