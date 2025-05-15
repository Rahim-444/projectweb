<?php
require_once 'functions.php';

if (!estConnecte()) {
    header('Location: login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_article = isset($_POST['id_article']) ? intval($_POST['id_article']) : 0;

    if ($id_article <= 0) {
        $_SESSION['erreur'] = 'Données invalides.';
        header('Location: panier.php');
        exit;
    }

    $resultat = supprimerDuPanier($_SESSION['id_utilisateur'], $id_article);

    if ($resultat) {
        $_SESSION['succes'] = 'L\'article a été supprimé du panier.';
    } else {
        $_SESSION['erreur'] = 'Une erreur est survenue lors de la suppression.';
    }

    header('Location: panier.php');
    exit;
} else {
    header('Location: panier.php');
    exit;
}
