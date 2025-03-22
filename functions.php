<?php
// functions.php - Fonctions utilitaires pour le site
session_start();
require_once 'config.php';

// Fonction pour vérifier si l'utilisateur est connecté
function estConnecte() {
    return isset($_SESSION['id_utilisateur']);
}

// Fonction pour vérifier si l'utilisateur est administrateur
function estAdmin() {
    return isset($_SESSION['est_admin']) && $_SESSION['est_admin'] === true;
}

// Fonction pour récupérer les informations d'un utilisateur
function getUtilisateur($id) {
    $conn = connectDB();
    $query = "SELECT * FROM Utilisateurs WHERE id_utilisateur = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $stmt->close();
    $conn->close();
    return $user;
}

// Fonction pour récupérer tous les livres
function getTousLivres() {
    $conn = connectDB();
    $query = "SELECT l.*, c.nom as categorie_nom FROM Livres l 
              LEFT JOIN Categories c ON l.id_categorie = c.id_categorie 
              ORDER BY l.date_ajout DESC";
    $result = $conn->query($query);
    $livres = [];
    
    while ($row = $result->fetch_assoc()) {
        $livres[] = $row;
    }
    
    $conn->close();
    return $livres;
}

// Fonction pour récupérer les livres par catégorie
function getLivresParCategorie($id_categorie) {
    $conn = connectDB();
    $query = "SELECT l.*, c.nom as categorie_nom FROM Livres l 
              LEFT JOIN Categories c ON l.id_categorie = c.id_categorie 
              WHERE l.id_categorie = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $id_categorie);
    $stmt->execute();
    $result = $stmt->get_result();
    $livres = [];
    
    while ($row = $result->fetch_assoc()) {
        $livres[] = $row;
    }
    
    $stmt->close();
    $conn->close();
    return $livres;
}

// Fonction pour récupérer un livre par son ID
function getLivreParId($id_livre) {
    $conn = connectDB();
    $query = "SELECT l.*, c.nom as categorie_nom FROM Livres l 
              LEFT JOIN Categories c ON l.id_categorie = c.id_categorie 
              WHERE l.id_livre = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $id_livre);
    $stmt->execute();
    $result = $stmt->get_result();
    $livre = $result->fetch_assoc();
    $stmt->close();
    $conn->close();
    return $livre;
}

// Fonction pour rechercher des livres
function rechercherLivres($terme, $categorie = null, $prix_min = null, $prix_max = null) {
    $conn = connectDB();
    $conditions = [];
    $params = [];
    $types = "";
    
    // Recherche par terme
    if (!empty($terme)) {
        $conditions[] = "(l.titre LIKE ? OR l.auteur LIKE ? OR l.description LIKE ?)";
        $terme = "%$terme%";
        $params[] = $terme;
        $params[] = $terme;
        $params[] = $terme;
        $types .= "sss";
    }
    
    // Filtrage par catégorie
    if (!empty($categorie)) {
        $conditions[] = "l.id_categorie = ?";
        $params[] = $categorie;
        $types .= "i";
    }
    
    // Filtrage par prix minimum
    if (!empty($prix_min)) {
        $conditions[] = "l.prix >= ?";
        $params[] = $prix_min;
        $types .= "d";
    }
    
    // Filtrage par prix maximum
    if (!empty($prix_max)) {
        $conditions[] = "l.prix <= ?";
        $params[] = $prix_max;
        $types .= "d";
    }
    
    $whereClause = empty($conditions) ? "" : "WHERE " . implode(" AND ", $conditions);
    
    $query = "SELECT l.*, c.nom as categorie_nom FROM Livres l 
              LEFT JOIN Categories c ON l.id_categorie = c.id_categorie 
              $whereClause 
              ORDER BY l.date_ajout DESC";
    
    $stmt = $conn->prepare($query);
    
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    $livres = [];
    
    while ($row = $result->fetch_assoc()) {
        $livres[] = $row;
    }
    
    $stmt->close();
    $conn->close();
    return $livres;
}

// Fonction pour récupérer toutes les catégories
function getToutesCategories() {
    $conn = connectDB();
    $query = "SELECT * FROM Categories ORDER BY nom";
    $result = $conn->query($query);
    $categories = [];
    
    while ($row = $result->fetch_assoc()) {
        $categories[] = $row;
    }
    
    $conn->close();
    return $categories;
}

// Fonction pour ajouter un livre au panier
function ajouterAuPanier($id_utilisateur, $id_livre, $quantite) {
    $conn = connectDB();
    
    // Vérifier si le panier existe déjà
    $query = "SELECT id_panier FROM Paniers WHERE id_utilisateur = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $id_utilisateur);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $id_panier = $row['id_panier'];
    } else {
        // Créer un nouveau panier
        $query = "INSERT INTO Paniers (id_utilisateur) VALUES (?)";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $id_utilisateur);
        $stmt->execute();
        $id_panier = $conn->insert_id;
    }
    
    // Vérifier si l'article existe déjà dans le panier
    $query = "SELECT id_article_panier, quantite FROM Articles_Panier 
              WHERE id_panier = ? AND id_livre = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ii", $id_panier, $id_livre);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        // Mettre à jour la quantité
        $row = $result->fetch_assoc();
        $nouvelle_quantite = $row['quantite'] + $quantite;
        
        $query = "UPDATE Articles_Panier SET quantite = ? 
                  WHERE id_article_panier = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ii", $nouvelle_quantite, $row['id_article_panier']);
        $stmt->execute();
    } else {
        // Ajouter un nouvel article
        $query = "INSERT INTO Articles_Panier (id_panier, id_livre, quantite) 
                  VALUES (?, ?, ?)";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("iii", $id_panier, $id_livre, $quantite);
        $stmt->execute();
    }
    
    $stmt->close();
    $conn->close();
    return true;
}

// Fonction pour récupérer le contenu du panier
function getPanier($id_utilisateur) {
    $conn = connectDB();
    
    $query = "SELECT p.id_panier, ap.id_article_panier, ap.id_livre, ap.quantite,
              l.titre, l.auteur, l.prix, l.image, l.stock,
              (ap.quantite * l.prix) as sous_total
              FROM Paniers p
              JOIN Articles_Panier ap ON p.id_panier = ap.id_panier
              JOIN Livres l ON ap.id_livre = l.id_livre
              WHERE p.id_utilisateur = ?";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $id_utilisateur);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $panier = [];
    $total = 0;
    
    while ($row = $result->fetch_assoc()) {
        $panier['articles'][] = $row;
        $total += $row['sous_total'];
    }
    
    $panier['total'] = $total;
    
    $stmt->close();
    $conn->close();
    return $panier;
}

// Fonction pour supprimer un article du panier
function supprimerDuPanier($id_utilisateur, $id_article_panier) {
    $conn = connectDB();
    
    $query = "DELETE ap FROM Articles_Panier ap
              JOIN Paniers p ON ap.id_panier = p.id_panier
              WHERE p.id_utilisateur = ? AND ap.id_article_panier = ?";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ii", $id_utilisateur, $id_article_panier);
    $result = $stmt->execute();
    
    $stmt->close();
    $conn->close();
    return $result;
}

// Fonction pour mettre à jour la quantité d'un article dans le panier
function mettreAJourPanier($id_utilisateur, $id_article_panier, $quantite) {
    $conn = connectDB();
    
    $query = "UPDATE Articles_Panier ap
              JOIN Paniers p ON ap.id_panier = p.id_panier
              SET ap.quantite = ?
              WHERE p.id_utilisateur = ? AND ap.id_article_panier = ?";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("iii", $quantite, $id_utilisateur, $id_article_panier);
    $result = $stmt->execute();
    
    $stmt->close();
    $conn->close();
    return $result;
}

// Fonction pour finaliser une commande
function finaliserCommande($id_utilisateur, $adresse, $ville, $code_postal) {
    $conn = connectDB();
    
    // Appel de la procédure stockée pour finaliser la commande
    $query = "CALL FinaliserCommande(?, ?, ?, ?, @id_commande)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("isss", $id_utilisateur, $adresse, $ville, $code_postal);
    $stmt->execute();
    
    // Récupérer l'ID de la commande créée
    $result = $conn->query("SELECT @id_commande as id_commande");
    $row = $result->fetch_assoc();
    $id_commande = $row['id_commande'];
    
    $stmt->close();
    $conn->close();
    return $id_commande;
}

// Fonction pour récupérer l'historique des commandes
function getHistoriqueCommandes($id_utilisateur) {
    $conn = connectDB();
    
    // Appel de la procédure stockée pour récupérer l'historique
    $query = "CALL HistoriqueCommandes(?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $id_utilisateur);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $commandes = [];
    while ($row = $result->fetch_assoc()) {
        $commandes[] = $row;
    }
    
    $stmt->close();
    $conn->close();
    return $commandes;
}

// Fonction pour récupérer les détails d'une commande
function getDetailsCommande($id_utilisateur, $id_commande) {
    $conn = connectDB();
    
    // Appel de la procédure stockée pour récupérer les détails
    $query = "CALL AfficherDetailsCommande(?, ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ii", $id_commande, $id_utilisateur);
    $stmt->execute();
    
    $details = [];
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $details['articles'][] = $row;
    }
    
    // Récupérer le total
    $stmt->next_result();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $details['total'] = $row['total'];
    
    $stmt->close();
    $conn->close();
    return $details;
}

// Fonction pour ajouter un nouveau livre (admin)
function ajouterLivre($titre, $auteur, $id_categorie, $description, $prix, $annee, $editeur, $stock, $image) {
    $conn = connectDB();
    
    $query = "INSERT INTO Livres (titre, auteur, id_categorie, description, prix, annee_publication, editeur, stock, image) 
              VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ssississs", $titre, $auteur, $id_categorie, $description, $prix, $annee, $editeur, $stock, $image);
    $result = $stmt->execute();
    $id_livre = $conn->insert_id;
    
    $stmt->close();
    $conn->close();
    return $id_livre;
}

// Fonction pour annuler une commande
function annulerCommande($id_commande, $id_utilisateur) {
    $conn = connectDB();
    
    // Vérifier que l'utilisateur est propriétaire de la commande
    $query = "SELECT id_commande FROM Commandes 
              WHERE id_commande = ? AND id_utilisateur = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ii", $id_commande, $id_utilisateur);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        $stmt->close();
        $conn->close();
        return false;
    }
    
    // Mettre à jour le statut de la commande
    $query = "UPDATE Commandes SET statut = 'Annulée' 
              WHERE id_commande = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $id_commande);
    $result = $stmt->execute();
    
    $stmt->close();
    $conn->close();
    return $result;
}
?>
