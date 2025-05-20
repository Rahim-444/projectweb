<?php
require_once 'functions.php';

if (!estConnecte() || !estAdmin()) {
    header('Location: index.php');
    exit;
}

$id_commande = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id_commande <= 0) {
    $_SESSION['erreur'] = 'ID de commande invalide.';
    header('Location: admin.php#gerer-commandes');
    exit;
}

// On récupère les détails de la commande sans vérifier l'id_utilisateur (car admin)
$conn = connectDB();
$query = "SELECT c.*, u.nom, u.prenom, u.email, u.telephone 
          FROM Commandes c
          JOIN Utilisateurs u ON c.id_utilisateur = u.id_utilisateur
          WHERE c.id_commande = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $id_commande);
$stmt->execute();
$result = $stmt->get_result();
$commande = $result->fetch_assoc();
$stmt->close();

if (!$commande) {
    $_SESSION['erreur'] = 'Commande introuvable.';
    header('Location: admin.php#gerer-commandes');
    exit;
}

$query = "SELECT d.*, l.titre, l.auteur, l.image
          FROM Details_Commande d
          JOIN Livres l ON d.id_livre = l.id_livre
          WHERE d.id_commande = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $id_commande);
$stmt->execute();
$result = $stmt->get_result();
$articles = [];
while ($row = $result->fetch_assoc()) {
    $articles[] = $row;
}
$stmt->close();
$conn->close();

include 'header.php';
?>

<section class="page-header">
    <h2>Détails de la commande #<?php echo $id_commande; ?></h2>
    <a href="admin.php#gerer-commandes" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Retour aux commandes</a>
</section>

<section class="commande-details">
    <div class="commande-info">
        <div class="card">
            <div class="card-header">
                <h3>Informations de la commande</h3>
            </div>
            <div class="card-body">
                <div class="info-group">
                    <p><strong>Statut:</strong> <span class="status-<?php echo strtolower(str_replace(' ', '-', $commande['statut'])); ?>"><?php echo ucfirst($commande['statut']); ?></span></p>
                    <p><strong>Date:</strong> <?php echo date('d/m/Y H:i', strtotime($commande['date_commande'])); ?></p>
                    <p><strong>Total:</strong> <?php echo number_format($commande['total'], 2, ',', ' '); ?> DZD</p>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h3>Informations du client</h3>
            </div>
            <div class="card-body">
                <div class="info-group">
                    <p><strong>Nom:</strong> <?php echo $commande['prenom'] . ' ' . $commande['nom']; ?></p>
                    <p><strong>Email:</strong> <?php echo $commande['email']; ?></p>
                    <p><strong>Téléphone:</strong> <?php echo $commande['telephone'] ?: 'Non renseigné'; ?></p>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h3>Adresse de livraison</h3>
            </div>
            <div class="card-body">
                <div class="info-group">
                    <p><?php echo $commande['adresse_livraison']; ?></p>
                    <p><?php echo $commande['code_postal_livraison'] . ' ' . $commande['ville_livraison']; ?></p>
                </div>
            </div>
        </div>

        <?php if ($commande['statut'] === 'En attente'): ?>
            <div class="card">
                <div class="card-header">
                    <h3>Actions</h3>
                </div>
                <div class="card-body">
                    <div class="commande-actions">
                        <form action="update_commande.php" method="post" style="display: inline-block; margin-right: 10px;">
                            <input type="hidden" name="id_commande" value="<?php echo $id_commande; ?>">
                            <input type="hidden" name="statut" value="confirmee">
                            <button type="submit" class="btn btn-success" onclick="return confirm('Confirmer cette commande ?')">
                                <i class="fas fa-check"></i> Confirmer la commande
                            </button>
                        </form>
                        <form action="update_commande.php" method="post" style="display: inline-block;">
                            <input type="hidden" name="id_commande" value="<?php echo $id_commande; ?>">
                            <input type="hidden" name="statut" value="annulee">
                            <button type="submit" class="btn btn-danger" onclick="return confirm('Annuler cette commande ?')">
                                <i class="fas fa-times"></i> Annuler la commande
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        <?php elseif ($commande['statut'] === 'confirmee'): ?>
            <div class="card">
                <div class="card-header">
                    <h3>Actions</h3>
                </div>
                <div class="card-body">
                    <div class="commande-actions">
                        <form action="update_commande.php" method="post">
                            <input type="hidden" name="id_commande" value="<?php echo $id_commande; ?>">
                            <input type="hidden" name="statut" value="expediee">
                            <button type="submit" class="btn btn-primary" onclick="return confirm('Marquer cette commande comme expédiée ?')">
                                <i class="fas fa-shipping-fast"></i> Marquer comme expédiée
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        <?php elseif ($commande['statut'] === 'expediee'): ?>
            <div class="card">
                <div class="card-header">
                    <h3>Actions</h3>
                </div>
                <div class="card-body">
                    <div class="commande-actions">
                        <form action="update_commande.php" method="post">
                            <input type="hidden" name="id_commande" value="<?php echo $id_commande; ?>">
                            <input type="hidden" name="statut" value="livree">
                            <button type="submit" class="btn btn-info" onclick="return confirm('Marquer cette commande comme livrée ?')">
                                <i class="fas fa-box-open"></i> Marquer comme livrée
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <div class="commande-articles">
        <div class="card">
            <div class="card-header">
                <h3>Articles commandés</h3>
            </div>
            <div class="card-body">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Livre</th>
                            <th>Prix unitaire</th>
                            <th>Quantité</th>
                            <th>Sous-total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($articles as $article): ?>
                            <tr>
                                <td class="livre-info">
                                    <img src="images/<?php echo $article['image']; ?>" alt="<?php echo $article['titre']; ?>" width="50">
                                    <div>
                                        <h4><?php echo $article['titre']; ?></h4>
                                        <p>par <?php echo $article['auteur']; ?></p>
                                    </div>
                                </td>
                                <td class="prix"><?php echo number_format($article['prix_unitaire'], 2, ',', ' '); ?> DZD</td>
                                <td class="quantite"><?php echo $article['quantite']; ?></td>
                                <td class="sous-total"><?php echo number_format($article['prix_unitaire'] * $article['quantite'], 2, ',', ' '); ?> DZD</td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="3" class="total-label">Total</td>
                            <td class="total-amount"><?php echo number_format($commande['total'], 2, ',', ' '); ?> DZD</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</section>

<?php include 'footer.php'; ?>
