-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1
-- Généré le : mar. 13 oct. 2026 à 11:24
-- Version du serveur : 10.4.32-MariaDB
-- Version de PHP : 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `gestion_commandes`
--

-- --------------------------------------------------------

--
-- Structure de la table `client`
--

CREATE TABLE `client` (
  `idCli` int(11) NOT NULL,
  `nomCli` varchar(100) NOT NULL,
  `prenomCli` varchar(100) NOT NULL,
  `email` varchar(190) NOT NULL,
  `photo` varchar(255) DEFAULT NULL,
  `idRegion` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `client`
--

INSERT INTO `client` (`idCli`, `nomCli`, `prenomCli`, `email`, `photo`, `idRegion`) VALUES
(1, 'Mahamat', 'Ali', 'mahamat@example.com', NULL, 1),
(2, 'Amina', 'Saleh', 'amina@example.com', NULL, 2),
(3, 'Ganota Anaiko', 'kenan', 'kenananaiko869@gmail.com', NULL, 1);

-- --------------------------------------------------------

--
-- Structure de la table `commande`
--

CREATE TABLE `commande` (
  `idCmd` int(11) NOT NULL,
  `dateCmd` datetime NOT NULL DEFAULT current_timestamp(),
  `idCli` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `commande`
--

INSERT INTO `commande` (`idCmd`, `dateCmd`, `idCli`) VALUES
(1, '2026-10-12 15:05:09', 1),
(2, '2026-10-12 15:05:09', 2);

-- --------------------------------------------------------

--
-- Structure de la table `département`
--

CREATE TABLE `département` (
  `idDep` int(11) NOT NULL,
  `nomDep` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `département`
--

INSERT INTO `département` (`idDep`, `nomDep`) VALUES
(1, 'Logone Occidental'),
(2, 'N\'Djamena');

-- --------------------------------------------------------

--
-- Structure de la table `lignecommande`
--

CREATE TABLE `lignecommande` (
  `idCmd` int(11) NOT NULL,
  `idProd` int(11) NOT NULL,
  `qteCmd` int(10) UNSIGNED NOT NULL,
  `prixUCommande` decimal(12,2) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `lignecommande`
--

INSERT INTO `lignecommande` (`idCmd`, `idProd`, `qteCmd`, `prixUCommande`) VALUES
(1, 1, 2, 5000.00),
(1, 2, 1, 3500.00),
(2, 3, 1, 8000.00);

-- --------------------------------------------------------

--
-- Structure de la table `produit`
--

CREATE TABLE `produit` (
  `idProd` int(11) NOT NULL,
  `designProd` varchar(160) NOT NULL,
  `qteStock` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `prixUProd` decimal(12,2) UNSIGNED NOT NULL DEFAULT 0.00,
  `photoProd` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `produit`
--

INSERT INTO `produit` (`idProd`, `designProd`, `qteStock`, `prixUProd`, `photoProd`) VALUES
(1, 'Clé USB 32 Go', 40, 5000.00, NULL),
(2, 'Souris USB', 25, 3500.00, NULL),
(3, 'Clavier', 15, 8000.00, NULL),
(4, 'telephone samsung s8', 85, 37500.00, NULL);

-- --------------------------------------------------------

--
-- Structure de la table `region`
--

CREATE TABLE `region` (
  `idRegion` int(11) NOT NULL,
  `nomRegion` varchar(100) NOT NULL,
  `idDep` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `region`
--

INSERT INTO `region` (`idRegion`, `nomRegion`, `idDep`) VALUES
(1, 'Moundou', 1),
(2, 'Dembé', 2);

-- --------------------------------------------------------

--
-- Structure de la table `users`
--

CREATE TABLE `users` (
  `idUser` int(11) NOT NULL,
  `nom` varchar(100) NOT NULL,
  `email` varchar(190) NOT NULL,
  `motDePasse` varchar(255) NOT NULL,
  `role` enum('admin','user') NOT NULL DEFAULT 'user',
  `dateCreation` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `users`
--

INSERT INTO `users` (`idUser`, `nom`, `email`, `motDePasse`, `role`, `dateCreation`) VALUES
(1, 'kens', 'commandegestion@gmail.com', '$2y$10$2zdKoL/lt34AO32dPzj6xuzE/INH1sxRfFmpa3mJyZRH4tcwqKNwG', 'admin', '2026-10-12 14:09:55'),
(2, 'denamdji', 'dene@gmail.com', '$2y$10$6PMaDsSQGW1o4Dc3iOkVJ.ianWFnWGhgn9/L89zJ1HmauRJd2pds6', 'user', '2026-10-12 14:13:56');

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `client`
--
ALTER TABLE `client`
  ADD PRIMARY KEY (`idCli`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `fk_client_region` (`idRegion`);

--
-- Index pour la table `commande`
--
ALTER TABLE `commande`
  ADD PRIMARY KEY (`idCmd`),
  ADD KEY `fk_commande_client` (`idCli`);

--
-- Index pour la table `département`
--
ALTER TABLE `département`
  ADD PRIMARY KEY (`idDep`);

--
-- Index pour la table `lignecommande`
--
ALTER TABLE `lignecommande`
  ADD PRIMARY KEY (`idCmd`,`idProd`),
  ADD KEY `fk_ligne_produit` (`idProd`);

--
-- Index pour la table `produit`
--
ALTER TABLE `produit`
  ADD PRIMARY KEY (`idProd`);

--
-- Index pour la table `region`
--
ALTER TABLE `region`
  ADD PRIMARY KEY (`idRegion`),
  ADD KEY `fk_region_dep` (`idDep`);

--
-- Index pour la table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`idUser`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `client`
--
ALTER TABLE `client`
  MODIFY `idCli` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT pour la table `commande`
--
ALTER TABLE `commande`
  MODIFY `idCmd` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT pour la table `département`
--
ALTER TABLE `département`
  MODIFY `idDep` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT pour la table `produit`
--
ALTER TABLE `produit`
  MODIFY `idProd` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT pour la table `region`
--
ALTER TABLE `region`
  MODIFY `idRegion` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT pour la table `users`
--
ALTER TABLE `users`
  MODIFY `idUser` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `client`
--
ALTER TABLE `client`
  ADD CONSTRAINT `fk_client_region` FOREIGN KEY (`idRegion`) REFERENCES `region` (`idRegion`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Contraintes pour la table `commande`
--
ALTER TABLE `commande`
  ADD CONSTRAINT `fk_commande_client` FOREIGN KEY (`idCli`) REFERENCES `client` (`idCli`) ON UPDATE CASCADE;

--
-- Contraintes pour la table `lignecommande`
--
ALTER TABLE `lignecommande`
  ADD CONSTRAINT `fk_ligne_commande` FOREIGN KEY (`idCmd`) REFERENCES `commande` (`idCmd`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_ligne_produit` FOREIGN KEY (`idProd`) REFERENCES `produit` (`idProd`) ON UPDATE CASCADE;

--
-- Contraintes pour la table `region`
--
ALTER TABLE `region`
  ADD CONSTRAINT `fk_region_dep` FOREIGN KEY (`idDep`) REFERENCES `département` (`idDep`) ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
