<?php
session_start();
require_once 'functions.php';

if (!estConnecte() || !estAdmin()) {
    header('Location: index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_commande']) && isset($_POST['statut'])) {
    $id_commande = intval($_POST['id_commande']);
    $nouveau_statut = $_POST['statut'];

    // Vérifier que le statut est soit "confirmee" ou "annulee"
    if ($nouveau_statut !== 'confirmee' && $nouveau_statut !== 'annulee') {
        $_SESSION['erreur'] = 'Statut invalide.';
        header('Location: admin.php#gerer-commandes');
        exit;
    }

    $result = changerStatutCommande($id_commande, $nouveau_statut);

    if ($result) {
        $message = ($nouveau_statut === 'confirmee') ?
            'La commande a été acceptée avec succès.' :
            'La commande a été rejetée.';

        $_SESSION['succes'] = $message;
    } else {
        $_SESSION['erreur'] = 'Une erreur est survenue lors de la mise à jour du statut.';
    }
}

header('Location: admin.php#gerer-commandes');
exit;
