-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1
-- Généré le : jeu. 21 mai 2026 à 10:28
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
-- Base de données : `touba_lyon`
--

-- --------------------------------------------------------

--
-- Structure de la table `category`
--

CREATE TABLE `category` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `color` varchar(7) DEFAULT NULL,
  `icon` varchar(50) DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `category`
--

INSERT INTO `category` (`id`, `name`, `description`, `color`, `icon`, `created_at`, `updated_at`) VALUES
(1, 'Sonorisation', '', '#0EA5E9', 'build', '2026-05-14 20:04:19', '2026-05-16 22:39:06');

-- --------------------------------------------------------

--
-- Structure de la table `doctrine_migration_versions`
--

CREATE TABLE `doctrine_migration_versions` (
  `version` varchar(191) NOT NULL,
  `executed_at` datetime DEFAULT NULL,
  `execution_time` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `loan`
--

CREATE TABLE `loan` (
  `id` int(11) NOT NULL,
  `borrower_name` varchar(150) NOT NULL,
  `borrower_organization` varchar(100) DEFAULT NULL,
  `borrower_phone` varchar(20) DEFAULT NULL,
  `borrower_email` varchar(100) DEFAULT NULL,
  `loan_date` date NOT NULL,
  `expected_return_date` date DEFAULT NULL,
  `actual_return_date` date DEFAULT NULL,
  `status` varchar(20) NOT NULL,
  `purpose` varchar(100) DEFAULT NULL,
  `notes` longtext DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `loan`
--

INSERT INTO `loan` (`id`, `borrower_name`, `borrower_organization`, `borrower_phone`, `borrower_email`, `loan_date`, `expected_return_date`, `actual_return_date`, `status`, `purpose`, `notes`, `created_at`, `updated_at`) VALUES
(2, 'Papa Amadou GNING', '', '', 'papagning4@gmail.com', '2026-05-12', '2026-05-13', '2026-05-16', 'returned', '', '', '2026-05-14 21:22:28', '2026-05-16 14:06:29');

-- --------------------------------------------------------

--
-- Structure de la table `loan_item`
--

CREATE TABLE `loan_item` (
  `id` int(11) NOT NULL,
  `loan_id` int(11) NOT NULL,
  `material_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `returned_quantity` int(11) DEFAULT NULL,
  `notes` longtext DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `loan_item`
--

INSERT INTO `loan_item` (`id`, `loan_id`, `material_id`, `quantity`, `returned_quantity`, `notes`) VALUES
(2, 2, 3, 1, 1, '');

-- --------------------------------------------------------

--
-- Structure de la table `material`
--

CREATE TABLE `material` (
  `id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL,
  `name` varchar(150) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `reference` varchar(50) DEFAULT NULL,
  `total_stock` int(11) NOT NULL,
  `available_stock` int(11) NOT NULL,
  `unit` varchar(50) DEFAULT NULL,
  `notes` varchar(255) DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `material`
--

INSERT INTO `material` (`id`, `category_id`, `name`, `description`, `reference`, `total_stock`, `available_stock`, `unit`, `notes`, `created_at`, `updated_at`) VALUES
(1, 1, 'Baffle', NULL, NULL, 7, 7, NULL, NULL, '2026-05-14 20:05:08', '2026-05-17 15:00:41'),
(3, 1, 'Micro', NULL, NULL, 12, 12, NULL, NULL, '2026-05-14 21:21:46', '2026-05-16 14:06:29');

-- --------------------------------------------------------

--
-- Structure de la table `material_condition`
--

CREATE TABLE `material_condition` (
  `id` int(11) NOT NULL,
  `label` varchar(100) NOT NULL,
  `color` varchar(7) NOT NULL,
  `is_available` tinyint(4) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `position` int(11) NOT NULL,
  `created_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `material_condition`
--

INSERT INTO `material_condition` (`id`, `label`, `color`, `is_available`, `description`, `position`, `created_at`) VALUES
(1, 'Bon état', '#10B981', 1, 'Matériel opérationnel et disponible', 1, '2026-05-16 13:43:33'),
(2, 'À réparer', '#F59E0B', 0, 'Nécessite une intervention avant utilisation', 2, '2026-05-16 13:43:33'),
(3, 'En réparation', '#3B82F6', 0, 'En cours de réparation', 3, '2026-05-16 13:43:33'),
(4, 'Hors service', '#EF4444', 0, 'Inutilisable, à remplacer', 4, '2026-05-16 13:43:33');

-- --------------------------------------------------------

--
-- Structure de la table `material_condition_history`
--

CREATE TABLE `material_condition_history` (
  `id` int(11) NOT NULL,
  `material_id` int(11) NOT NULL,
  `condition_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `comment` text DEFAULT NULL,
  `changed_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `material_condition_history`
--

INSERT INTO `material_condition_history` (`id`, `material_id`, `condition_id`, `quantity`, `comment`, `changed_at`) VALUES
(1, 1, 1, 3, 'État initial', '2026-05-16 13:43:33'),
(2, 3, 1, 12, 'État initial', '2026-05-16 13:43:33'),
(4, 1, 1, 2, NULL, '2026-05-16 15:08:56'),
(5, 1, 3, 1, NULL, '2026-05-16 15:08:56'),
(6, 3, 1, 12, NULL, '2026-05-16 19:39:45'),
(7, 3, 2, 0, NULL, '2026-05-16 19:39:45'),
(8, 3, 3, 0, NULL, '2026-05-16 19:39:45'),
(9, 3, 4, 0, NULL, '2026-05-16 19:39:45'),
(10, 1, 1, 3, NULL, '2026-05-16 21:28:21'),
(11, 1, 2, 0, NULL, '2026-05-16 21:28:21'),
(12, 1, 3, 0, NULL, '2026-05-16 21:28:21'),
(13, 1, 4, 0, NULL, '2026-05-16 21:28:21'),
(14, 1, 1, 7, NULL, '2026-05-17 15:00:42'),
(15, 1, 2, 0, NULL, '2026-05-17 15:00:42'),
(16, 1, 3, 0, NULL, '2026-05-17 15:00:42'),
(17, 1, 4, 0, NULL, '2026-05-17 15:00:42'),
(18, 1, 1, 7, NULL, '2026-05-17 22:33:59'),
(19, 1, 2, 0, NULL, '2026-05-17 22:33:59'),
(20, 1, 3, 0, NULL, '2026-05-17 22:33:59'),
(21, 1, 4, 0, NULL, '2026-05-17 22:33:59');

-- --------------------------------------------------------

--
-- Structure de la table `material_condition_stock`
--

CREATE TABLE `material_condition_stock` (
  `id` int(11) NOT NULL,
  `material_id` int(11) NOT NULL,
  `condition_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `material_condition_stock`
--

INSERT INTO `material_condition_stock` (`id`, `material_id`, `condition_id`, `quantity`) VALUES
(1, 1, 1, 7),
(2, 3, 1, 12),
(4, 1, 3, 0),
(5, 3, 2, 0),
(6, 3, 3, 0),
(7, 3, 4, 0),
(8, 1, 2, 0),
(9, 1, 4, 0);

-- --------------------------------------------------------

--
-- Structure de la table `material_photo`
--

CREATE TABLE `material_photo` (
  `id` int(11) NOT NULL,
  `material_id` int(11) NOT NULL,
  `filename` varchar(255) NOT NULL,
  `original_name` varchar(255) DEFAULT NULL,
  `file_size` int(11) DEFAULT NULL,
  `created_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `material_photo`
--

INSERT INTO `material_photo` (`id`, `material_id`, `filename`, `original_name`, `file_size`, `created_at`) VALUES
(1, 3, 'pngtree-black-mic-png-image-6521970-6a06e963126d9.png', 'pngtree-black-mic-png-image_6521970.png', 9937, '2026-05-15 11:37:39'),
(2, 3, 'Studio-Mic-Download-PNG-Image-6a06e963a66a1.png', 'Studio-Mic-Download-PNG-Image.png', 671643, '2026-05-15 11:37:39'),
(3, 1, 'Baffle2-6a0869cace701.png', 'Baffle2.png', 65706, '2026-05-16 14:57:46'),
(4, 1, 'Baffle-6a0869cb69941.png', 'Baffle.png', 196342, '2026-05-16 14:57:47');

-- --------------------------------------------------------

--
-- Structure de la table `stock_movement`
--

CREATE TABLE `stock_movement` (
  `id` int(11) NOT NULL,
  `material_id` int(11) NOT NULL,
  `loan_id` int(11) DEFAULT NULL,
  `type` varchar(20) NOT NULL COMMENT 'in|out|adjustment',
  `quantity` int(11) NOT NULL,
  `stock_before` int(11) NOT NULL,
  `stock_after` int(11) NOT NULL,
  `reason` text DEFAULT NULL,
  `borrower_name` varchar(150) DEFAULT NULL,
  `borrower_organization` varchar(100) DEFAULT NULL,
  `created_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `stock_movement`
--

INSERT INTO `stock_movement` (`id`, `material_id`, `loan_id`, `type`, `quantity`, `stock_before`, `stock_after`, `reason`, `borrower_name`, `borrower_organization`, `created_at`) VALUES
(1, 1, NULL, 'in', 3, 0, 3, 'Stock initial', NULL, NULL, '2026-05-14 20:05:08'),
(3, 1, NULL, 'out', 2, 3, 1, 'Emprunt - ', 'Ibrahim Ndiaye', '', '2026-05-14 20:06:54'),
(4, 1, NULL, 'in', 2, 1, 3, 'Retour emprunt - ', 'Ibrahim Ndiaye', '', '2026-05-14 21:20:31'),
(5, 3, NULL, 'in', 12, 0, 12, 'Stock initial', NULL, NULL, '2026-05-14 21:21:46'),
(6, 3, 2, 'out', 1, 12, 11, 'Emprunt - ', 'Papa Amadou GNING', '', '2026-05-14 21:22:28'),
(7, 3, NULL, 'adjustment', 0, 11, 11, 'Ajustement de stock', NULL, NULL, '2026-05-15 11:33:37'),
(8, 3, NULL, 'adjustment', 0, 11, 11, 'Ajustement de stock', NULL, NULL, '2026-05-15 11:35:09'),
(9, 3, NULL, 'adjustment', 0, 11, 11, 'Ajustement de stock', NULL, NULL, '2026-05-15 11:37:38'),
(10, 3, 2, 'in', 1, 11, 12, 'Retour emprunt - ', 'Papa Amadou GNING', '', '2026-05-16 14:06:29'),
(11, 1, NULL, 'adjustment', 0, 3, 3, 'Ajustement de stock', NULL, NULL, '2026-05-16 14:57:44'),
(12, 1, NULL, 'adjustment', 0, 3, 3, 'Ajustement de stock', NULL, NULL, '2026-05-16 14:58:08'),
(13, 1, NULL, 'adjustment', 0, 3, 3, 'Ajustement de stock', NULL, NULL, '2026-05-16 15:08:54'),
(14, 1, NULL, 'adjustment', 0, 2, 2, 'Ajustement de stock', NULL, NULL, '2026-05-16 15:10:20'),
(15, 1, NULL, 'adjustment', 0, 2, 2, 'Ajustement de stock', NULL, NULL, '2026-05-16 21:28:20'),
(16, 1, NULL, 'adjustment', 4, 3, 7, 'Achat de nouveau micros', NULL, NULL, '2026-05-17 15:00:40'),
(17, 1, NULL, 'adjustment', 0, 7, 7, 'Ajustement de stock', NULL, NULL, '2026-05-17 22:33:57');

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `category`
--
ALTER TABLE `category`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `doctrine_migration_versions`
--
ALTER TABLE `doctrine_migration_versions`
  ADD PRIMARY KEY (`version`);

--
-- Index pour la table `loan`
--
ALTER TABLE `loan`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `loan_item`
--
ALTER TABLE `loan_item`
  ADD PRIMARY KEY (`id`),
  ADD KEY `IDX_CEB65F6AE308AC6F` (`material_id`),
  ADD KEY `IDX_CEB65F6ACE73868F` (`loan_id`);

--
-- Index pour la table `material`
--
ALTER TABLE `material`
  ADD PRIMARY KEY (`id`),
  ADD KEY `IDX_7CBE759512469DE2` (`category_id`);

--
-- Index pour la table `material_condition`
--
ALTER TABLE `material_condition`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `material_condition_history`
--
ALTER TABLE `material_condition_history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_mch_material` (`material_id`),
  ADD KEY `fk_mch_condition` (`condition_id`);

--
-- Index pour la table `material_condition_stock`
--
ALTER TABLE `material_condition_stock`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_material_condition` (`material_id`,`condition_id`),
  ADD KEY `fk_mcs_condition` (`condition_id`);

--
-- Index pour la table `material_photo`
--
ALTER TABLE `material_photo`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_photo_material` (`material_id`);

--
-- Index pour la table `stock_movement`
--
ALTER TABLE `stock_movement`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_movement_material` (`material_id`),
  ADD KEY `idx_movement_loan` (`loan_id`),
  ADD KEY `idx_movement_created` (`created_at`),
  ADD KEY `idx_movement_type` (`type`);

--
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `category`
--
ALTER TABLE `category`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT pour la table `loan`
--
ALTER TABLE `loan`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT pour la table `loan_item`
--
ALTER TABLE `loan_item`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT pour la table `material`
--
ALTER TABLE `material`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT pour la table `material_condition`
--
ALTER TABLE `material_condition`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT pour la table `material_condition_history`
--
ALTER TABLE `material_condition_history`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT pour la table `material_condition_stock`
--
ALTER TABLE `material_condition_stock`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT pour la table `material_photo`
--
ALTER TABLE `material_photo`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT pour la table `stock_movement`
--
ALTER TABLE `stock_movement`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `loan_item`
--
ALTER TABLE `loan_item`
  ADD CONSTRAINT `fk_loan_item_loan` FOREIGN KEY (`loan_id`) REFERENCES `loan` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_loan_item_material` FOREIGN KEY (`material_id`) REFERENCES `material` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `material`
--
ALTER TABLE `material`
  ADD CONSTRAINT `fk_material_category` FOREIGN KEY (`category_id`) REFERENCES `category` (`id`);

--
-- Contraintes pour la table `material_condition_stock`
--
ALTER TABLE `material_condition_stock`
  ADD CONSTRAINT `fk_mcs_condition` FOREIGN KEY (`condition_id`) REFERENCES `material_condition` (`id`),
  ADD CONSTRAINT `fk_mcs_material` FOREIGN KEY (`material_id`) REFERENCES `material` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `material_photo`
--
ALTER TABLE `material_photo`
  ADD CONSTRAINT `fk_photo_material` FOREIGN KEY (`material_id`) REFERENCES `material` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `stock_movement`
--
ALTER TABLE `stock_movement`
  ADD CONSTRAINT `fk_movement_loan` FOREIGN KEY (`loan_id`) REFERENCES `loan` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_movement_material` FOREIGN KEY (`material_id`) REFERENCES `material` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
