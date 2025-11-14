CREATE DATABASE IF NOT EXISTS don_sang;
USE don_sang;

CREATE TABLE IF NOT EXISTS donneurs(
    id_donneur INT PRIMARY KEY AUTO_INCREMENT,
    cin VARCHAR(50) UNIQUE NOT NULL,
    groupe_sanguin ENUM('A', 'B', 'AB', 'O') NOT NULL,
    rhesus ENUM('-', '+') NOT NULL
);

CREATE TABLE IF NOT EXISTS centres_collecte (
    id_centre INT PRIMARY KEY AUTO_INCREMENT
);

CREATE TABLE IF NOT EXISTS utilisateurs (
    id_utilisateur INT PRIMARY KEY AUTO_INCREMENT,
    role ENUM('Admin', 'Médecin', 'Secrétaire') NOT NULL,
    nom_utilisateur VARCHAR(55) NOT NULL,
    id_centre INT NOT NULL,
    FOREIGN KEY (id_centre) REFERENCES centres_collecte(id_centre),
    password VARCHAR(255) NOT NULL
);

CREATE TABLE IF NOT EXISTS dons (
    id_don INT PRIMARY KEY AUTO_INCREMENT,
    id_donneur INT NOT NULL,
    id_centre INT NOT NULL,
    FOREIGN KEY (id_donneur) REFERENCES donneurs(id_donneur),
    FOREIGN KEY (id_centre) REFERENCES centres_collecte(id_centre),
    status ENUM('EN STOCK', 'UTILISÉ', 'REJETÉ') NOT NULL
);

CREATE TABLE IF NOT EXISTS tests_don (
    id_test INT PRIMARY KEY AUTO_INCREMENT,
    id_don INT UNIQUE NOT NULL,
    id_centre INT NOT NULL,
    FOREIGN KEY (id_don) REFERENCES dons(id_don),
    FOREIGN KEY (id_centre) REFERENCES centres_collecte(id_centre),
    est_conforme BOOL NOT NULL
);

CREATE TABLE IF NOT EXISTS transfusions (
    id_transfusion INT PRIMARY KEY AUTO_INCREMENT,
    id_don INT UNIQUE NOT NULL,
    FOREIGN KEY (id_don) REFERENCES dons(id_don),
    date_transfusion DATE NOT NULL,
    hopital_recepteur VARCHAR(150) NOT NULL
);

CREATE TABLE IF NOT EXISTS besoins (
    id_besoin INT PRIMARY KEY AUTO_INCREMENT,
    groupe_sanguin ENUM('A', 'B', 'AB', 'O') NOT NULL,
    rhesus ENUM('-', '+') NOT NULL,
    niveau_alerte ENUM('URGENT', 'CRITIQUE', 'NORMALE') NOT NULL
);