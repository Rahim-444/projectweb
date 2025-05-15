<?php
require_once 'functions.php';

// Rediriger si non connecté
if (!estConnecte()) {
    header('Location: login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_livre = isset($_POST['id_livre']) ? intval($_POST['id_livre']) : 0;
    $quantite = isset($_POST['quantite']) ? intval($_POST['quantite']) : 1;

    if ($id_livre <= 0 || $quantite <= 0) {
        $_SESSION['erreur'] = 'Données invalides.';
        header('Location: catalogue.php');
        exit;
    }

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

    $resultat = ajouterAuPanier($_SESSION['id_utilisateur'], $id_livre, $quantite);

    if ($resultat) {
        $_SESSION['succes'] = 'Le livre a été ajouté à votre panier.';
    } else {
        $_SESSION['erreur'] = 'Une erreur est survenue lors de l\'ajout au panier.';
    }

    if (isset($_SERVER['HTTP_REFERER'])) {
        header('Location: ' . $_SERVER['HTTP_REFERER']);
    } else {
        header('Location: panier.php');
    }
    exit;
} else {
    header('Location: catalogue.php');
    exit;
}
