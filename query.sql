CREATE DATABASE IF NOT EXISTS don_sang;
USE don_sang;

CREATE TABLE IF NOT EXISTS donneurs(
    id_donneur INT PRIMARY KEY AUTO_INCREMENT,
    cin varchar(50) UNIQUE NOT NULL,
    groupe_sanguin enum('A', 'B', 'AB', 'O'),
    rhesus enum('-', '+')
)

CREATE TABLE IF NOT EXISTS centres_collecte (
    id_centre INT PRIMARY KEY AUTO_INCREMENT
)

CREATE TABLE IF NOT EXISTS utilisateurs (
    id_utilisateur INT PRIMARY KEY AUTO_INCREMENT,
    role enum('Admin', 'Médecin', 'Secrétaire') NOT NULL,
    FOREIGN KEY (id_centre) REFERENCES centres_collecte(id_centre),
    password varchar(200) NOT NULL,
)

CREATE TABLE IF NOT EXISTS dons (
    id_don INT PRIMARY KEY AUTO_INCREMENT,
    FOREIGN KEY (id_donneur) REFERENCES donneurs(id_donneur),
    FOREIGN KEY (id_centre) REFERENCES centres_collecte(id_centre),
    status enum('EN STOCK', 'UTILISÉ', 'REJETÉ');
)

CREATE TABLE IF NOT EXISTS tests_don (
    id_test INT PRIMARY KEY AUTOINCREMENT,
    FOREIGN KEY (id_don) REFERENCES dons(id_don) UNIQUE,
    FOREIGN KEY (id_centre) REFERENCES centres_collecte(id_centre),
    est_conforme BOOL;
)

CREATE TABLE IF NOT EXISTS transfusions (
    id_transfusion INT PRIMARY KEY AUTOINCREMENT,
    FOREIGN KEY (id_don) REFERENCES dons(id_don) UNIQUE,
    date_trnasfusion DATE,
    hopital_recepteur varchar(150)
)

CREATE TABLE IF NOT EXISTS besoins (
    id_besoin INT PRIMARY KEY AUTO_INCREMENT,
    groupe_sanguin enum('A', 'B', 'AB', 'O'),
    rhesus enum('-', '+'),
    niveau_alerte enum('URGENT', 'CRITIQUE', 'NORMALE')
)
