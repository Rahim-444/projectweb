-- Création des tables
CREATE TABLE Utilisateurs (
    id_utilisateur INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) NOT NULL,
    prenom VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    mot_de_passe VARCHAR(255) NOT NULL,
    adresse VARCHAR(255),
    ville VARCHAR(100),
    code_postal VARCHAR(10),
    telephone VARCHAR(15),
    est_admin BOOLEAN DEFAULT FALSE,
    date_inscription DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE Categories (
    id_categorie INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) NOT NULL,
    description TEXT
);

CREATE TABLE Livres (
    id_livre INT AUTO_INCREMENT PRIMARY KEY,
    titre VARCHAR(200) NOT NULL,
    auteur VARCHAR(100) NOT NULL,
    id_categorie INT,
    description TEXT,
    prix DECIMAL(10, 2) NOT NULL,
    annee_publication INT,
    editeur VARCHAR(100),
    stock INT NOT NULL DEFAULT 0,
    image VARCHAR(255),
    date_ajout DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_categorie) REFERENCES Categories(id_categorie)
);

CREATE TABLE Paniers (
    id_panier INT AUTO_INCREMENT PRIMARY KEY,
    id_utilisateur INT,
    date_creation DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_utilisateur) REFERENCES Utilisateurs(id_utilisateur)
);

CREATE TABLE Articles_Panier (
    id_article_panier INT AUTO_INCREMENT PRIMARY KEY,
    id_panier INT,
    id_livre INT,
    quantite INT NOT NULL,
    date_ajout DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_panier) REFERENCES Paniers(id_panier),
    FOREIGN KEY (id_livre) REFERENCES Livres(id_livre)
);

CREATE TABLE Commandes (
    id_commande INT AUTO_INCREMENT PRIMARY KEY,
    id_utilisateur INT,
    date_commande DATETIME DEFAULT CURRENT_TIMESTAMP,
    statut ENUM('En attente', 'Confirmée', 'Expédiée', 'Livrée', 'Annulée') DEFAULT 'En attente',
    total DECIMAL(10, 2) NOT NULL,
    adresse_livraison VARCHAR(255),
    ville_livraison VARCHAR(100),
    code_postal_livraison VARCHAR(10),
    FOREIGN KEY (id_utilisateur) REFERENCES Utilisateurs(id_utilisateur)
);

CREATE TABLE Details_Commande (
    id_detail INT AUTO_INCREMENT PRIMARY KEY,
    id_commande INT,
    id_livre INT,
    quantite INT NOT NULL,
    prix_unitaire DECIMAL(10, 2) NOT NULL,
    FOREIGN KEY (id_commande) REFERENCES Commandes(id_commande),
    FOREIGN KEY (id_livre) REFERENCES Livres(id_livre)
);

CREATE TABLE Commandes_Annulees (
    id_annulation INT AUTO_INCREMENT PRIMARY KEY,
    id_commande INT,
    date_annulation DATETIME DEFAULT CURRENT_TIMESTAMP,
    raison TEXT,
    FOREIGN KEY (id_commande) REFERENCES Commandes(id_commande)
);

-- Procédure stockée pour afficher les détails d'une commande
DELIMITER //
CREATE PROCEDURE AfficherDetailsCommande(IN p_id_commande INT, IN p_id_utilisateur INT)
BEGIN
    SELECT c.id_commande, c.date_commande, c.statut, c.total,
           l.titre, l.auteur, d.quantite, d.prix_unitaire, 
           (d.quantite * d.prix_unitaire) AS sous_total
    FROM Commandes c
    JOIN Details_Commande d ON c.id_commande = d.id_commande
    JOIN Livres l ON d.id_livre = l.id_livre
    WHERE c.id_commande = p_id_commande AND c.id_utilisateur = p_id_utilisateur;
    
    SELECT total FROM Commandes WHERE id_commande = p_id_commande;
END //
DELIMITER ;

-- Procédure stockée pour finaliser une commande
DELIMITER //
CREATE PROCEDURE FinaliserCommande(IN p_id_utilisateur INT, IN p_adresse VARCHAR(255), 
                                  IN p_ville VARCHAR(100), IN p_code_postal VARCHAR(10), 
                                  OUT p_id_commande INT)
BEGIN
    DECLARE v_total DECIMAL(10, 2) DEFAULT 0;
    DECLARE v_id_panier INT;
    
    -- Récupérer l'ID du panier de l'utilisateur
    SELECT id_panier INTO v_id_panier FROM Paniers WHERE id_utilisateur = p_id_utilisateur LIMIT 1;
    
    -- Calculer le total
    SELECT SUM(ap.quantite * l.prix) INTO v_total
    FROM Articles_Panier ap
    JOIN Livres l ON ap.id_livre = l.id_livre
    WHERE ap.id_panier = v_id_panier;
    
    -- Créer la commande
    INSERT INTO Commandes (id_utilisateur, total, adresse_livraison, ville_livraison, code_postal_livraison)
    VALUES (p_id_utilisateur, v_total, p_adresse, p_ville, p_code_postal);
    
    SET p_id_commande = LAST_INSERT_ID();
    
    -- Transférer les articles du panier vers la commande
    INSERT INTO Details_Commande (id_commande, id_livre, quantite, prix_unitaire)
    SELECT p_id_commande, ap.id_livre, ap.quantite, l.prix
    FROM Articles_Panier ap
    JOIN Livres l ON ap.id_livre = l.id_livre
    WHERE ap.id_panier = v_id_panier;
    
    -- Vider le panier
    DELETE FROM Articles_Panier WHERE id_panier = v_id_panier;
END //
DELIMITER ;

-- Procédure stockée pour afficher l'historique des commandes
DELIMITER //
CREATE PROCEDURE HistoriqueCommandes(IN p_id_utilisateur INT)
BEGIN
    SELECT c.id_commande, c.date_commande, c.statut, c.total,
           COUNT(d.id_detail) AS nombre_articles
    FROM Commandes c
    LEFT JOIN Details_Commande d ON c.id_commande = d.id_commande
    WHERE c.id_utilisateur = p_id_utilisateur
    GROUP BY c.id_commande
    ORDER BY c.date_commande DESC;
END //
DELIMITER ;

-- Trigger pour mettre à jour le stock après validation de commande
DELIMITER //
CREATE TRIGGER after_commande_confirmed
AFTER UPDATE ON Commandes
FOR EACH ROW
BEGIN
    IF NEW.statut = 'Confirmée' AND OLD.statut = 'En attente' THEN
        UPDATE Livres l
        JOIN Details_Commande d ON l.id_livre = d.id_livre
        SET l.stock = l.stock - d.quantite
        WHERE d.id_commande = NEW.id_commande;
    END IF;
END //
DELIMITER ;

-- Trigger pour empêcher l'insertion si quantité > stock
DELIMITER //
CREATE TRIGGER before_details_commande_insert
BEFORE INSERT ON Details_Commande
FOR EACH ROW
BEGIN
    DECLARE stock_disponible INT;
    
    SELECT stock INTO stock_disponible
    FROM Livres WHERE id_livre = NEW.id_livre;
    
    IF NEW.quantite > stock_disponible THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Stock insuffisant pour cette commande';
    END IF;
END //
DELIMITER ;

-- Trigger pour restaurer le stock après annulation
DELIMITER //
CREATE TRIGGER after_commande_canceled
AFTER UPDATE ON Commandes
FOR EACH ROW
BEGIN
    IF NEW.statut = 'Annulée' AND OLD.statut != 'Annulée' THEN
        UPDATE Livres l
        JOIN Details_Commande d ON l.id_livre = d.id_livre
        SET l.stock = l.stock + d.quantite
        WHERE d.id_commande = NEW.id_commande;
    END IF;
END //
DELIMITER ;

-- Trigger pour garder trace des commandes annulées
DELIMITER //
CREATE TRIGGER after_commande_canceled_history
AFTER UPDATE ON Commandes
FOR EACH ROW
BEGIN
    IF NEW.statut = 'Annulée' AND OLD.statut != 'Annulée' THEN
        INSERT INTO Commandes_Annulees (id_commande, raison)
        VALUES (NEW.id_commande, 'Annulation par client ou administrateur');
    END IF;
END //
DELIMITER ;

-- Insertion de données d'exemple
INSERT INTO Categories (nom, description) VALUES
('Classiques', 'Œuvres littéraires classiques'),
('Science-Fiction', 'Romans et nouvelles de science-fiction'),
('Fantastique', 'Romans et nouvelles fantastiques'),
('Poésie', 'Recueils de poèmes'),
('Histoire', 'Livres historiques');

INSERT INTO Livres (titre, auteur, id_categorie, description, prix, annee_publication, editeur, stock, image) VALUES
('Les Misérables', 'Victor Hugo', 1, 'Un chef d\'œuvre de la littérature française', 59.99, 1862, 'Albert Lacroix et Cie', 15, 'les_miserables.jpg'),
('Germinal', 'Émile Zola', 1, 'Roman sur la condition ouvrière au XIXe siècle', 45.50, 1885, 'Gil Blas', 8, 'germinal.jpg'),
('Dune', 'Frank Herbert', 2, 'Épopée spatiale culte de science-fiction', 38.75, 1965, 'Robert Laffont', 20, 'dune.jpg'),
('Le Seigneur des Anneaux', 'J.R.R. Tolkien', 3, 'Trilogie fondatrice de la fantasy moderne', 89.99, 1954, 'Christian Bourgois', 10, 'seigneur_anneaux.jpg'),
('Les Fleurs du Mal', 'Charles Baudelaire', 4, 'Recueil de poèmes emblématique', 32.50, 1857, 'Poulet-Malassis', 12, 'fleurs_mal.jpg'),
('Mémoires de guerre', 'Charles de Gaulle', 5, 'Témoignage historique de la Seconde Guerre mondiale', 65.00, 1954, 'Plon', 5, 'memoires_guerre.jpg');

INSERT INTO Utilisateurs (nom, prenom, email, mot_de_passe, est_admin) VALUES
('Admin', 'System', 'admin@bibliotheque-vintage.fr', '$2a$10$pUWUYeDl0yD.ZAxC7Vcg4eS53qEX7h2Nvk7h3x1LqdIfEGDBAzRKm', TRUE), -- mot de passe: admin123
('Dupont', 'Jean', 'jean.dupont@example.com', '$2y$10$KJV7nVKHYKHS20QJA3ASjOWLyuruUzGmjviP4maVYFcmXJu5Eouiq', FALSE); -- mot de passe: user123
