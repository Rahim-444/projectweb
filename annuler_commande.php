<?php
require_once 'functions.php';

if (!estConnecte()) {
    header('Location: login.php');
    exit;
}

$id_commande = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id_commande <= 0) {
    $_SESSION['erreur'] = 'ID de commande invalide.';
    header('Location: commandes.php');
    exit;
}

$resultat = annulerCommande($id_commande, $_SESSION['id_utilisateur']);

if ($resultat) {
    $_SESSION['succes'] = 'La commande a été annulée avec succès.';
} else {
    $_SESSION['erreur'] = 'Une erreur est survenue lors de l\'annulation de la commande.';
}

header('Location: commandes.php');
exit;
