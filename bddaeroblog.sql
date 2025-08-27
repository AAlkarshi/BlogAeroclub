-- phpMyAdmin SQL Dump
-- version 4.9.7
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1:3306
-- Généré le : Dim 15 juin 2025 à 19:21
-- Version du serveur :  10.6.5-MariaDB
-- Version de PHP : 7.4.26

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `bddaeroblog`
--

-- --------------------------------------------------------

--
-- Structure de la table `article`
--

DROP TABLE IF EXISTS `article`;
CREATE TABLE IF NOT EXISTS `article` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `categorie_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `creation_date` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_23A0E66BCF5E72D` (`categorie_id`),
  KEY `IDX_23A0E66A76ED395` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=108 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `article`
--

INSERT INTO `article` (`id`, `categorie_id`, `user_id`, `title`, `creation_date`) VALUES
(29, 8, 2, '1er vol', '2024-06-02 16:28:00'),
(34, 46, 2, 'Je veux apprendre à piloter un avion', '2024-06-03 11:51:00'),
(36, 48, 6, 'Faut t-il être majeur pour piloter en avion ?', '2024-06-09 12:17:00'),
(47, 45, 6, 'J\'ai le vertige', '2024-06-23 12:22:00'),
(84, 46, 51, 'C\'est si chère que ça ?', '2024-07-13 15:39:55'),
(105, 154, 58, 'exemple', '2024-07-24 18:35:29'),
(106, 45, 60, 'J\'ai le vertige et des maux de tete', '2024-07-25 11:23:30');

-- --------------------------------------------------------

--
-- Structure de la table `categorie`
--

DROP TABLE IF EXISTS `categorie`;
CREATE TABLE IF NOT EXISTS `categorie` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=158 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `categorie`
--

INSERT INTO `categorie` (`id`, `name`) VALUES
(1, 'Vol en solitaire'),
(5, 'Pilotage'),
(8, 'Un premier vol en avion'),
(45, 'Vertige'),
(46, 'Apprendre à piloter'),
(48, 'Majeur pour piloter ?'),
(55, 'Emotions'),
(152, 'test2'),
(153, 'test3'),
(154, 'test4'),
(155, 'test5'),
(156, 'test5');

-- --------------------------------------------------------

--
-- Structure de la table `doctrine_migration_versions`
--

DROP TABLE IF EXISTS `doctrine_migration_versions`;
CREATE TABLE IF NOT EXISTS `doctrine_migration_versions` (
  `version` varchar(191) COLLATE utf8mb3_unicode_ci NOT NULL,
  `executed_at` datetime DEFAULT NULL,
  `execution_time` int(11) DEFAULT NULL,
  PRIMARY KEY (`version`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

--
-- Déchargement des données de la table `doctrine_migration_versions`
--

INSERT INTO `doctrine_migration_versions` (`version`, `executed_at`, `execution_time`) VALUES
('DoctrineMigrations\\Version20240529144758', '2024-05-29 14:48:30', 1242),
('DoctrineMigrations\\Version20240716083411', '2024-07-16 08:45:29', 744),
('DoctrineMigrations\\Version20240716184807', '2024-07-16 18:48:20', 627),
('DoctrineMigrations\\Version20240716200534', '2024-07-16 20:05:39', 346),
('DoctrineMigrations\\Version20240719084540', '2024-07-19 08:45:47', 74),
('DoctrineMigrations\\Version20240719085718', '2024-07-19 08:57:59', 70);

-- --------------------------------------------------------

--
-- Structure de la table `message`
--

DROP TABLE IF EXISTS `message`;
CREATE TABLE IF NOT EXISTS `message` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `expediteur_id` int(11) NOT NULL,
  `destinataire_id` int(11) NOT NULL,
  `content` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` datetime NOT NULL COMMENT '(DC2Type:datetime_immutable)',
  PRIMARY KEY (`id`),
  KEY `IDX_B6BD307F10335F61` (`expediteur_id`),
  KEY `IDX_B6BD307FA4F84F6E` (`destinataire_id`)
) ENGINE=InnoDB AUTO_INCREMENT=56 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `message`
--

INSERT INTO `message` (`id`, `expediteur_id`, `destinataire_id`, `content`, `created_at`) VALUES
(11, 51, 6, 'cela fonctionne', '2024-07-16 11:29:14'),
(12, 51, 2, 'Tu as déjà piloter un avion ?', '2024-07-16 12:47:01'),
(13, 2, 51, 'oui, j\'ai déjà piloté un avion.', '2024-07-16 13:10:59'),
(14, 2, 51, 'et toi ?', '2024-07-16 13:13:06'),
(15, 2, 51, '?', '2024-07-16 13:37:13'),
(16, 2, 6, 'Tu as une question concernant les permis ?', '2024-07-16 13:42:35'),
(17, 6, 2, 'Oui, j\'ai différentes questions à te poser si c\'est possible', '2024-07-16 13:45:32'),
(20, 6, 51, 'exactement', '2024-07-16 13:58:26'),
(27, 6, 2, 'Je t\'écoute ', '2024-07-16 14:24:15'),
(28, 6, 51, 'exemple', '2024-07-16 16:31:18'),
(29, 6, 51, 'exemple\r\n', '2024-07-16 16:33:48'),
(30, 6, 51, 'a', '2024-07-16 16:33:54'),
(31, 6, 51, 's', '2024-07-16 16:33:58'),
(32, 6, 51, '5', '2024-07-16 16:34:11'),
(33, 6, 51, 'b', '2024-07-16 20:20:36'),
(42, 46, 6, 'Salut', '2024-07-19 10:25:44'),
(45, 46, 6, 'Tu vas bien ?', '2024-07-21 07:21:30'),
(54, 58, 6, 'test', '2024-07-24 18:36:23'),
(55, 60, 51, 'Salut', '2024-07-25 11:25:18');

-- --------------------------------------------------------

--
-- Structure de la table `messenger_messages`
--

DROP TABLE IF EXISTS `messenger_messages`;
CREATE TABLE IF NOT EXISTS `messenger_messages` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `body` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `headers` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue_name` varchar(190) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` datetime NOT NULL,
  `available_at` datetime NOT NULL,
  `delivered_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_75EA56E0FB7336F0` (`queue_name`),
  KEY `IDX_75EA56E0E3BD61CE` (`available_at`),
  KEY `IDX_75EA56E016BA31DB` (`delivered_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `post`
--

DROP TABLE IF EXISTS `post`;
CREATE TABLE IF NOT EXISTS `post` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `article_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `content` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `image` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_5A8A6C8D7294869C` (`article_id`),
  KEY `IDX_5A8A6C8DA76ED395` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=71 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `post`
--

INSERT INTO `post` (`id`, `article_id`, `user_id`, `content`, `image`) VALUES
(44, 29, 6, 'C\'est mon 1er vol\ndrago:Raconte moi comment cela s\'est passé', 'avion-6676d0fed5662.jpg'),
(47, 47, 6, 'J\'ai peur', 'avionpose-667813ffdbba6.jpg'),
(64, 84, 51, 'Quel est le prix moyen du permis PPL (Permis Pilote Privé) ?\nInscriptionTest:9000€\nexemple4:C\'est très chere.', 'petitavion-6692a125896d4.jpg'),
(68, 105, 58, 'test', NULL),
(69, 106, 60, 'J\'ai le vertige', NULL);

-- --------------------------------------------------------

--
-- Structure de la table `user`
--

DROP TABLE IF EXISTS `user`;
CREATE TABLE IF NOT EXISTS `user` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(180) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `roles` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL COMMENT '(DC2Type:json)' CHECK (json_valid(`roles`)),
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_verified` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_8D93D649F85E0677` (`username`)
) ENGINE=InnoDB AUTO_INCREMENT=64 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `user`
--

INSERT INTO `user` (`id`, `username`, `email`, `roles`, `password`, `is_verified`) VALUES
(2, 'testtest1', 'testtest@gmail.com', '[\"ROLE_MOD\"]', '$2y$13$NRGm9RydFNPhSrKuhRLFzOJNZjIKfbvA0g99vnWcpeVOXCtMnNN6a', 1),
(3, 'Abdullrahman', 'alkarshi.abdullrahman@gmail.com', '[\"ROLE_ADMIN\"]', '$2y$13$tSWy1B6kUxkIjG/qTX7uPuZEC4ZEK/EZWgHoGPB2/KPSjbABUv.Na', 1),
(6, 'InscriptionTest', 'InscriptionTest@gmail.com', '[\"ROLE_MOD\"]', '$2y$13$HvmQerXXSeHOWJql25AqGe.LyLUVioaB6YfL.VMTR2k49KjoQYIAa', 1),
(44, 'testentrainement', 'testeurexemple@gmail.com', '[\"ROLE_MOD\"]', '$2y$13$hvYHuhKwzMC68MQLHGcwdOerbwtUb4lfrf1EQJJu3WglH19teiUBm', 1),
(46, 'test', 'test@gmail.com', '[\"ROLE_MOD\"]', '$2y$13$88eXLp7hzqqv7m/CVWXMaex477r3VSMbLVyZNfVDMWSXqqMV8beKy', 1),
(51, 'bravotesttest', 'bravotesttest@gmail.com', '[\"ROLE_MOD\"]', '$2y$13$0EtmIWjLJmyqKGyvdhgcsuVLil.tZWVQ139Ami6UFd3qEdeb83TwS', 1),
(52, 'ExemplePseudo', 'ExPseudo@gmail.com', '[\"ROLE_MOD\"]', '$2y$13$lwIq.CXBN03KK8yPAD7Ywuczk5wVQC9Htjz.ZzICMLO5C7C1AQOXa', 1),
(58, 'exemple', 'exemple@gmail.com', '[\"ROLE_MOD\"]', '$2y$13$drrzxg7aG7WLcyzOaNqSluNpS13Y5YgwRy4LS/EH3te7LNIso2cfe', 1),
(60, 'exemple4', 'exemple@gmail.com', '[\"ROLE_MOD\"]', '$2y$13$Qk9fRTGuF0Lu.Jy/EvzhSOrWf4Sq3Fv6lkULW5eFfmiv.sEvbW1m2', 1),
(62, 'albert', 'albert@gmail.com', '[\"ROLE_MOD\"]', '$2y$13$8PuQkPO1j/AdU8AWb2Bc0eLH4FBGnPnx3EQkJTYqLFKfch.8iFc1e', 1);

-- --------------------------------------------------------

--
-- Structure de la table `user_friends`
--

DROP TABLE IF EXISTS `user_friends`;
CREATE TABLE IF NOT EXISTS `user_friends` (
  `user_id` int(11) NOT NULL,
  `friend_user_id` int(11) NOT NULL,
  PRIMARY KEY (`user_id`,`friend_user_id`),
  KEY `IDX_79E36E63A76ED395` (`user_id`),
  KEY `IDX_79E36E6393D1119E` (`friend_user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `user_friends`
--

INSERT INTO `user_friends` (`user_id`, `friend_user_id`) VALUES
(6, 46),
(6, 51),
(6, 58),
(46, 6),
(51, 6),
(51, 60),
(58, 6),
(60, 51);

-- --------------------------------------------------------

--
-- Structure de la table `user_friend_requests`
--

DROP TABLE IF EXISTS `user_friend_requests`;
CREATE TABLE IF NOT EXISTS `user_friend_requests` (
  `sender_id` int(11) NOT NULL,
  `receiver_id` int(11) NOT NULL,
  PRIMARY KEY (`sender_id`,`receiver_id`),
  KEY `IDX_FEBFDC94F624B39D` (`sender_id`),
  KEY `IDX_FEBFDC94CD53EDB6` (`receiver_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `article`
--
ALTER TABLE `article`
  ADD CONSTRAINT `FK_23A0E66A76ED395` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`),
  ADD CONSTRAINT `FK_23A0E66BCF5E72D` FOREIGN KEY (`categorie_id`) REFERENCES `categorie` (`id`);

--
-- Contraintes pour la table `message`
--
ALTER TABLE `message`
  ADD CONSTRAINT `FK_B6BD307F10335F61` FOREIGN KEY (`expediteur_id`) REFERENCES `user` (`id`),
  ADD CONSTRAINT `FK_B6BD307FA4F84F6E` FOREIGN KEY (`destinataire_id`) REFERENCES `user` (`id`);

--
-- Contraintes pour la table `post`
--
ALTER TABLE `post`
  ADD CONSTRAINT `FK_5A8A6C8D7294869C` FOREIGN KEY (`article_id`) REFERENCES `article` (`id`),
  ADD CONSTRAINT `FK_5A8A6C8DA76ED395` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`);

--
-- Contraintes pour la table `user_friends`
--
ALTER TABLE `user_friends`
  ADD CONSTRAINT `FK_79E36E6393D1119E` FOREIGN KEY (`friend_user_id`) REFERENCES `user` (`id`),
  ADD CONSTRAINT `FK_79E36E63A76ED395` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`);

--
-- Contraintes pour la table `user_friend_requests`
--
ALTER TABLE `user_friend_requests`
  ADD CONSTRAINT `FK_FEBFDC94CD53EDB6` FOREIGN KEY (`receiver_id`) REFERENCES `user` (`id`),
  ADD CONSTRAINT `FK_FEBFDC94F624B39D` FOREIGN KEY (`sender_id`) REFERENCES `user` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
