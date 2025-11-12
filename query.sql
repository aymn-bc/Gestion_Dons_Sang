CREATE DATABASE IF NOT EXISTS don_sang;
USE don_sang;

CREATE TABLE IF NOT EXISTS donneurs(
    id_donneur INT PRIMARY KEY AUTO_INCREMENT,
    cin varchar(50) UNIQUE NOT NULL,
    groupe_sanguin enum('A', 'B', 'AB', 'O') NOT NULL,
    rhesus enum('-', '+') NOT NULL
)

CREATE TABLE IF NOT EXISTS centres_collecte (
    id_centre INT PRIMARY KEY AUTO_INCREMENT
)

CREATE TABLE IF NOT EXISTS utilisateurs (
    id_utilisateur INT PRIMARY KEY AUTO_INCREMENT,
    role enum('Admin', 'Médecin', 'Secrétaire') NOT NULL,
    FOREIGN KEY (id_centre) REFERENCES centres_collecte(id_centre) NOT NULL,
    password varchar(200) NOT NULL,
)

CREATE TABLE IF NOT EXISTS dons (
    id_don INT PRIMARY KEY AUTO_INCREMENT,
    FOREIGN KEY (id_donneur) REFERENCES donneurs(id_donneur) NOT NULL,
    FOREIGN KEY (id_centre) REFERENCES centres_collecte(id_centre) NOT NULL,
    status enum('EN STOCK', 'UTILISÉ', 'REJETÉ') NOT NULL;
)

CREATE TABLE IF NOT EXISTS tests_don (
    id_test INT PRIMARY KEY AUTOINCREMENT ,
    FOREIGN KEY (id_don) REFERENCES dons(id_don) UNIQUE NOT NULL,
    FOREIGN KEY (id_centre) REFERENCES centres_collecte(id_centre) NOT NULL,
    est_conforme BOOL NOT NULL;
)

CREATE TABLE IF NOT EXISTS transfusions (
    id_transfusion INT PRIMARY KEY AUTOINCREMENT,
    FOREIGN KEY (id_don) REFERENCES dons(id_don) UNIQUE NOT NULL,
    date_trnasfusion DATE NOT NULL,
    hopital_recepteur varchar(150) NOT NULL
)

CREATE TABLE IF NOT EXISTS besoins (
    id_besoin INT PRIMARY KEY AUTO_INCREMENT,
    groupe_sanguin enum('A', 'B', 'AB', 'O') NOT NULL,
    rhesus enum('-', '+') NOT NULL,
    niveau_alerte enum('URGENT', 'CRITIQUE', 'NORMALE') NOT NULL
)
