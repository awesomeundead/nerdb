-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 193.203.175.58
-- Tempo de geração: 05/08/2025 às 16:41
-- Versão do servidor: 10.11.10-MariaDB-log
-- Versão do PHP: 8.4.10

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Banco de dados: `u390583303_abigo`
--

-- --------------------------------------------------------

--
-- Estrutura para tabela `games`
--

CREATE TABLE `games` (
  `id` int(10) UNSIGNED NOT NULL,
  `title` varchar(255) NOT NULL,
  `developer` varchar(255) NOT NULL,
  `genres` varchar(255) NOT NULL,
  `release_year` year(4) NOT NULL,
  `steam` varchar(255) NOT NULL,
  `media` char(32) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `login_log`
--

CREATE TABLE `login_log` (
  `id` int(10) UNSIGNED NOT NULL,
  `selector` varchar(255) NOT NULL,
  `hashed_validator` varchar(255) NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `login_date` datetime NOT NULL,
  `expire_date` datetime NOT NULL,
  `user_ip` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `movies`
--

CREATE TABLE `movies` (
  `id` int(10) UNSIGNED NOT NULL,
  `title_br` varchar(255) NOT NULL,
  `title_us` varchar(255) NOT NULL,
  `director` varchar(255) NOT NULL,
  `cast` text NOT NULL,
  `genres` varchar(255) NOT NULL,
  `release_year` year(4) NOT NULL,
  `media` char(32) NOT NULL,
  `imdb` varchar(255) NOT NULL,
  `first_user_id` int(10) UNSIGNED NOT NULL,
  `last_user_id` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `movie_platforms`
--

CREATE TABLE `movie_platforms` (
  `id` int(10) UNSIGNED NOT NULL,
  `movie_id` int(10) UNSIGNED NOT NULL,
  `platform_name` enum('Prime Video','HBO Max') NOT NULL,
  `platform_link` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `users`
--

CREATE TABLE `users` (
  `id` int(10) UNSIGNED NOT NULL,
  `steamid` bigint(20) UNSIGNED NOT NULL,
  `personaname` varchar(255) NOT NULL,
  `avatarhash` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `created_date` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `user_game_list`
--

CREATE TABLE `user_game_list` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `game_id` int(10) UNSIGNED NOT NULL,
  `playlist` tinyint(1) NOT NULL,
  `played` tinyint(1) NOT NULL,
  `rating` tinyint(2) NOT NULL,
  `liked` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `user_movie_list`
--

CREATE TABLE `user_movie_list` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `movie_id` int(10) UNSIGNED NOT NULL,
  `watchlist` tinyint(1) NOT NULL,
  `watched` tinyint(1) NOT NULL,
  `rating` tinyint(2) NOT NULL,
  `liked` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Índices para tabelas despejadas
--

--
-- Índices de tabela `games`
--
ALTER TABLE `games`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `login_log`
--
ALTER TABLE `login_log`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `movies`
--
ALTER TABLE `movies`
  ADD PRIMARY KEY (`id`);
ALTER TABLE `movies` ADD FULLTEXT KEY `title_br` (`title_br`,`title_us`,`director`);

--
-- Índices de tabela `movie_platforms`
--
ALTER TABLE `movie_platforms`
  ADD PRIMARY KEY (`id`),
  ADD KEY `movie_id` (`movie_id`) USING BTREE;

--
-- Índices de tabela `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `user_game_list`
--
ALTER TABLE `user_game_list`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `game_id` (`game_id`);

--
-- Índices de tabela `user_movie_list`
--
ALTER TABLE `user_movie_list`
  ADD PRIMARY KEY (`id`),
  ADD KEY `movie_id` (`movie_id`) USING BTREE,
  ADD KEY `user_id` (`user_id`) USING BTREE;

--
-- AUTO_INCREMENT para tabelas despejadas
--

--
-- AUTO_INCREMENT de tabela `games`
--
ALTER TABLE `games`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `login_log`
--
ALTER TABLE `login_log`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `movies`
--
ALTER TABLE `movies`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `movie_platforms`
--
ALTER TABLE `movie_platforms`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `users`
--
ALTER TABLE `users`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `user_game_list`
--
ALTER TABLE `user_game_list`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `user_movie_list`
--
ALTER TABLE `user_movie_list`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- Restrições para tabelas despejadas
--

--
-- Restrições para tabelas `movie_platforms`
--
ALTER TABLE `movie_platforms`
  ADD CONSTRAINT `movie_platforms_ibfk_1` FOREIGN KEY (`movie_id`) REFERENCES `movies` (`id`);

--
-- Restrições para tabelas `user_game_list`
--
ALTER TABLE `user_game_list`
  ADD CONSTRAINT `user_game_list_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `user_game_list_ibfk_2` FOREIGN KEY (`game_id`) REFERENCES `games` (`id`);

--
-- Restrições para tabelas `user_movie_list`
--
ALTER TABLE `user_movie_list`
  ADD CONSTRAINT `user_movie_list_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `user_movie_list_ibfk_2` FOREIGN KEY (`movie_id`) REFERENCES `movies` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
