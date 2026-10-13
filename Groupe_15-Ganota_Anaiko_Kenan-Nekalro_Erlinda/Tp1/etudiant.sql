-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1
-- Généré le : mar. 13 oct. 2026 à 11:29
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
-- Base de données : `universite`
--

-- --------------------------------------------------------

--
-- Structure de la table `etudiant`
--

CREATE TABLE `etudiant` (
  `idEtu` int(11) NOT NULL,
  `nomEtu` varchar(50) NOT NULL,
  `prenomEtu` varchar(50) NOT NULL,
  `dateNaiss` date NOT NULL,
  `emailEtu` varchar(100) NOT NULL,
  `numTelEtu` varchar(20) DEFAULT NULL,
  `regEtu` varchar(50) DEFAULT NULL,
  `departEtu` varchar(50) DEFAULT NULL,
  `arrondisEtu` varchar(50) DEFAULT NULL,
  `mentionEtu` varchar(50) DEFAULT NULL,
  `cycleEtu` varchar(30) DEFAULT NULL,
  `ParcoursEtu` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `etudiant`
--

INSERT INTO `etudiant` (`idEtu`, `nomEtu`, `prenomEtu`, `dateNaiss`, `emailEtu`, `numTelEtu`, `regEtu`, `departEtu`, `arrondisEtu`, `mentionEtu`, `cycleEtu`, `ParcoursEtu`) VALUES
(21, 'ken', 'Angelo', '2000-02-05', 'ken@g.fr', '61626364', 'logone occidental', 'lac', '3', 'Genie Informatique', 'Master', 'DI'),
(25, 'Ganota Anaiko', 'kenan', '1999-08-09', 'kenananaiko869@gmail.com', '68648276', 'logone occidental', 'lac', '4', 'Genie Informatique', 'Master', 'DI');

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `etudiant`
--
ALTER TABLE `etudiant`
  ADD PRIMARY KEY (`idEtu`),
  ADD UNIQUE KEY `emailEtu` (`emailEtu`);

--
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `etudiant`
--
ALTER TABLE `etudiant`
  MODIFY `idEtu` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
