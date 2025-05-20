create table utilisateurs (
    id_utilisateur int auto_increment primary key,
    nom varchar(100) not null,
    prenom varchar(100) not null,
    email varchar(100) unique not null,
    mot_de_passe varchar(255) not null,
    adresse varchar(255),
    ville varchar(100),
    code_postal varchar(10),
    telephone varchar(15),
    est_admin boolean default false,
    date_inscription datetime default current_timestamp
);

create table categories (
    id_categorie int auto_increment primary key,
    nom varchar(100) not null,
    description text
);

create table livres (
    id_livre int auto_increment primary key,
    titre varchar(200) not null,
    auteur varchar(100) not null,
    id_categorie int,
    description text,
    prix decimal(10, 2) not null,
    annee_publication int,
    editeur varchar(100),
    stock int not null default 0,
    image varchar(255),
    date_ajout datetime default current_timestamp,
    foreign key (id_categorie) references categories(id_categorie)
);

create table paniers (
    id_panier int auto_increment primary key,
    id_utilisateur int,
    date_creation datetime default current_timestamp,
    foreign key (id_utilisateur) references utilisateurs(id_utilisateur)
);

create table articles_panier (
    id_article_panier int auto_increment primary key,
    id_panier int,
    id_livre int,
    quantite int not null,
    date_ajout datetime default current_timestamp,
    foreign key (id_panier) references paniers(id_panier),
    foreign key (id_livre) references livres(id_livre)
);

create table commandes (
    id_commande int auto_increment primary key,
    id_utilisateur int,
    date_commande datetime default current_timestamp,
    statut enum('en attente', 'confirmee', 'expediee', 'livree', 'annulee') default 'en attente',
    total decimal(10, 2) not null,
    adresse_livraison varchar(255),
    ville_livraison varchar(100),
    code_postal_livraison varchar(10),
    foreign key (id_utilisateur) references utilisateurs(id_utilisateur)
);

create table details_commande (
    id_detail int auto_increment primary key,
    id_commande int,
    id_livre int,
    quantite int not null,
    prix_unitaire decimal(10, 2) not null,
    foreign key (id_commande) references commandes(id_commande),
    foreign key (id_livre) references livres(id_livre)
);

create table commandes_annulees (
    id_annulation int auto_increment primary key,
    id_commande int,
    date_annulation datetime default current_timestamp,
    raison text,
    foreign key (id_commande) references commandes(id_commande)
);


delimiter //
create procedure afficherdetailscommande(in p_id_commande int, in p_id_utilisateur int)
begin
    -- si p_id_utilisateur est null alors ( admin )
    if p_id_utilisateur is null then
        select c.id_commande, c.date_commande, c.statut, c.total,
               l.titre, l.auteur, d.quantite, d.prix_unitaire, 
               (d.quantite * d.prix_unitaire) as sous_total
        from commandes c
        join details_commande d on c.id_commande = d.id_commande
        join livres l on d.id_livre = l.id_livre
        where c.id_commande = p_id_commande;
    else
        -- if p_id_utilisateur is not null then ( client )
        select c.id_commande, c.date_commande, c.statut, c.total,
               l.titre, l.auteur, d.quantite, d.prix_unitaire, 
               (d.quantite * d.prix_unitaire) as sous_total
        from commandes c
        join details_commande d on c.id_commande = d.id_commande
        join livres l on d.id_livre = l.id_livre
        where c.id_commande = p_id_commande and c.id_utilisateur = p_id_utilisateur;
    end if;
    
    select total from commandes where id_commande = p_id_commande;
end //
delimiter ;

-- procedure pour finaliser commande
delimiter //
create procedure finalisercommande(in p_id_utilisateur int, in p_adresse varchar(255), 
                                  in p_ville varchar(100), in p_code_postal varchar(10), 
                                  out p_id_commande int)
begin
    declare v_total decimal(10, 2) default 0;
    declare v_id_panier int;
    
    -- recuperer panier
    select id_panier into v_id_panier from paniers where id_utilisateur = p_id_utilisateur limit 1;
    
    -- calculer total
    select sum(ap.quantite * l.prix) into v_total
    from articles_panier ap
    join livres l on ap.id_livre = l.id_livre
    where ap.id_panier = v_id_panier;
    
    -- creer commande
    insert into commandes (id_utilisateur, total, adresse_livraison, ville_livraison, code_postal_livraison)
    values (p_id_utilisateur, v_total, p_adresse, p_ville, p_code_postal);
    
    set p_id_commande = last_insert_id();
    
    -- transferer articles
    insert into details_commande (id_commande, id_livre, quantite, prix_unitaire)
    select p_id_commande, ap.id_livre, ap.quantite, l.prix
    from articles_panier ap
    join livres l on ap.id_livre = l.id_livre
    where ap.id_panier = v_id_panier;
    
    -- vider panier
    delete from articles_panier where id_panier = v_id_panier;
end //
delimiter ;

delimiter //
create procedure historiquecommandes(in p_id_utilisateur int)
begin
    select c.id_commande, c.date_commande, c.statut, c.total,
           count(d.id_detail) as nombre_articles
    from commandes c
    left join details_commande d on c.id_commande = d.id_commande
    where c.id_utilisateur = p_id_utilisateur
    group by c.id_commande
    order by c.date_commande desc;
end //
delimiter ;

delimiter //
create trigger after_commande_confirmed
after update on commandes
for each row
begin
    if new.statut = 'confirmee' and old.statut = 'en attente' then
        update livres l
        join details_commande d on l.id_livre = d.id_livre
        set l.stock = l.stock - d.quantite
        where d.id_commande = new.id_commande;
    end if;
end //
delimiter ;

delimiter //
create trigger before_details_commande_insert
before insert on details_commande
for each row
begin
    declare stock_disponible int;
    
    select stock into stock_disponible
    from livres where id_livre = new.id_livre;
    
    if new.quantite > stock_disponible then
        signal sqlstate '45000'
        set message_text = 'stock insuffisant pour cette commande';
    end if;
end //
delimiter ;

delimiter //
create trigger after_commande_canceled
after update on commandes
for each row
begin
    if new.statut = 'annulee' and old.statut != 'annulee' then
        update livres l
        join details_commande d on l.id_livre = d.id_livre
        set l.stock = l.stock + d.quantite
        where d.id_commande = new.id_commande;
    end if;
end //
delimiter ;

delimiter //
create trigger after_commande_canceled_history
after update on commandes
for each row
begin
    if new.statut = 'annulee' and old.statut != 'annulee' then
        insert into commandes_annulees (id_commande, raison)
        values (new.id_commande, 'annulation par client ou administrateur');
    end if;
end //
delimiter ;

-- donnees exemple
insert into categories (nom, description) values
('classiques', 'oeuvres litteraires classiques'),
('science-fiction', 'romans et nouvelles de science-fiction'),
('fantastique', 'romans et nouvelles fantastiques'),
('poesie', 'recueils de poemes'),
('histoire', 'livres historiques');

insert into livres (titre, auteur, id_categorie, description, prix, annee_publication, editeur, stock, image) values
('les miserables', 'victor hugo', 1, 'un chef d\'oeuvre de la litterature francaise', 59.99, 1862, 'albert lacroix et cie', 15, 'les_miserables.jpg'),
('germinal', 'emile zola', 1, 'roman sur la condition ouvriere au xixe siecle', 45.50, 1885, 'gil blas', 8, 'germinal.jpg'),
('dune', 'frank herbert', 2, 'epopee spatiale culte de science-fiction', 38.75, 1965, 'robert laffont', 20, 'dune.jpg'),
('le seigneur des anneaux', 'j.r.r. tolkien', 3, 'trilogie fondatrice de la fantasy moderne', 89.99, 1954, 'christian bourgois', 10, 'seigneur_anneaux.jpg'),
('les fleurs du mal', 'charles baudelaire', 4, 'recueil de poemes emblematique', 32.50, 1857, 'poulet-malassis', 12, 'fleurs_mal.jpg'),
('memoires de guerre', 'charles de gaulle', 5, 'temoignage historique de la seconde guerre mondiale', 65.00, 1954, 'plon', 5, 'memoires_guerre.jpg');

insert into utilisateurs (nom, prenom, email, mot_de_passe, est_admin) values
('admin', 'system', 'admin@bibliotheque-vintage.dz', '$2a$10$pUWUYeDl0yD.ZAxC7Vcg4eS53qEX7h2Nvk7h3x1LqdIfEGDBAzRKm', true);
-- mot de passe: admin123
