<?php
// annuler_commande.php - Script pour annuler une commande
require_once 'functions.php';

// Rediriger si non connecté
if (!estConnecte()) {
    header('Location: login.php');
    exit;
}

// Récupérer l'ID de la commande
$id_commande = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Vérifier que l'ID est valide
if ($id_commande <= 0) {
    $_SESSION['erreur'] = 'ID de commande invalide.';
    header('Location: commandes.php');
    exit;
}

// Annuler la commande
$resultat = annulerCommande($id_commande, $_SESSION['id_utilisateur']);

if ($resultat) {
    $_SESSION['succes'] = 'La commande a été annulée avec succès.';
} else {
    $_SESSION['erreur'] = 'Une erreur est survenue lors de l\'annulation de la commande.';
}

// Rediriger vers la page des commandes
header('Location: commandes.php');
exit;
?>
