-- phpMyAdmin SQL Dump
-- version 5.2.1deb3
-- https://www.phpmyadmin.net/
--
-- Hôte : localhost:3306
-- Généré le : lun. 10 nov. 2025 à 13:50
-- Version du serveur : 8.0.43-0ubuntu0.24.04.1
-- Version de PHP : 8.3.6

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `don_sang`
--

-- --------------------------------------------------------

--
-- Structure de la table `besoins`
--

CREATE TABLE `besoins` (
  `id_besoin` int NOT NULL,
  `groupe_sanguin` enum('A','B','AB','O') NOT NULL,
  `niveau_alerte` enum('URGENT','CRITIQUE','NORMAL') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Structure de la table `centres_collecte`
--

CREATE TABLE `centres_collecte` (
  `id_centre` int NOT NULL,
  `nom_centre` varchar(100) NOT NULL,
  `adresse_centre` varchar(150) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Structure de la table `donneurs`
--

CREATE TABLE `donneurs` (
  `id_donneur` int NOT NULL,
  `cin` varchar(20) NOT NULL,
  `nom` varchar(50) NOT NULL,
  `prenom` varchar(50) NOT NULL,
  `groupe_sanguin` enum('A','B','AB','O') NOT NULL,
  `rhesus` enum('+','-') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Structure de la table `dons`
--

CREATE TABLE `dons` (
  `id_don` int NOT NULL,
  `id_donneur` int NOT NULL,
  `id_centre` int NOT NULL,
  `date_don` date NOT NULL,
  `quantite_ml` int NOT NULL,
  `statut` enum('EN STOCK','UTILISE','REJETE') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Structure de la table `test_don`
--

CREATE TABLE `test_don` (
  `id_test` int NOT NULL,
  `id_don` int NOT NULL,
  `est_conforme` enum('OUI','NON') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Structure de la table `transfusions`
--

CREATE TABLE `transfusions` (
  `id_transfusion` int NOT NULL,
  `id_don` int NOT NULL,
  `hopital_recepteur` varchar(150) NOT NULL,
  `date_transfusion` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Structure de la table `utilisateurs`
--

CREATE TABLE `utilisateurs` (
  `id_utilisateur` int NOT NULL,
  `role` enum('Admin','Medecin','Secretaire') NOT NULL,
  `id_centre` int NOT NULL,
  `mot_de_passe` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `besoins`
--
ALTER TABLE `besoins`
  ADD PRIMARY KEY (`id_besoin`);

--
-- Index pour la table `centres_collecte`
--
ALTER TABLE `centres_collecte`
  ADD PRIMARY KEY (`id_centre`);

--
-- Index pour la table `donneurs`
--
ALTER TABLE `donneurs`
  ADD PRIMARY KEY (`id_donneur`),
  ADD UNIQUE KEY `cin` (`cin`(10));

--
-- Index pour la table `dons`
--
ALTER TABLE `dons`
  ADD PRIMARY KEY (`id_don`),
  ADD KEY `fk_dons_id_donneur` (`id_donneur`),
  ADD KEY `fk_utilisateurs_id_centre` (`id_centre`);

--
-- Index pour la table `test_don`
--
ALTER TABLE `test_don`
  ADD PRIMARY KEY (`id_test`),
  ADD UNIQUE KEY `id_don` (`id_don`);

--
-- Index pour la table `transfusions`
--
ALTER TABLE `transfusions`
  ADD PRIMARY KEY (`id_transfusion`),
  ADD UNIQUE KEY `id_don` (`id_don`);

--
-- Index pour la table `utilisateurs`
--
ALTER TABLE `utilisateurs`
  ADD PRIMARY KEY (`id_utilisateur`),
  ADD KEY `fk_utilisateurs_id_centre` (`id_centre`);

--
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `besoins`
--
ALTER TABLE `besoins`
  MODIFY `id_besoin` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `centres_collecte`
--
ALTER TABLE `centres_collecte`
  MODIFY `id_centre` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `donneurs`
--
ALTER TABLE `donneurs`
  MODIFY `id_donneur` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `dons`
--
ALTER TABLE `dons`
  MODIFY `id_don` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `test_don`
--
ALTER TABLE `test_don`
  MODIFY `id_test` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `transfusions`
--
ALTER TABLE `transfusions`
  MODIFY `id_transfusion` int NOT NULL AUTO_INCREMENT;

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `dons`
--
ALTER TABLE `dons`
  ADD CONSTRAINT `fk_dons_id_donneur` FOREIGN KEY (`id_donneur`) REFERENCES `donneurs` (`id_donneur`) ON DELETE RESTRICT ON UPDATE RESTRICT;

--
-- Contraintes pour la table `test_don`
--
ALTER TABLE `test_don`
  ADD CONSTRAINT `fk_tests_don_id_don` FOREIGN KEY (`id_don`) REFERENCES `dons` (`id_don`) ON DELETE RESTRICT ON UPDATE RESTRICT;

--
-- Contraintes pour la table `transfusions`
--
ALTER TABLE `transfusions`
  ADD CONSTRAINT `fk_transfusions_id_don` FOREIGN KEY (`id_don`) REFERENCES `dons` (`id_don`) ON DELETE RESTRICT ON UPDATE RESTRICT;

--
-- Contraintes pour la table `utilisateurs`
--
ALTER TABLE `utilisateurs`
  ADD CONSTRAINT `fk_utilisateurs_id_centre` FOREIGN KEY (`id_centre`) REFERENCES `centres_collecte` (`id_centre`) ON DELETE RESTRICT ON UPDATE RESTRICT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
