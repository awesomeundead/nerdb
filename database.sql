-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 193.203.175.58
-- Tempo de geração: 10/07/2025 às 15:48
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
  `steam` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `movies`
--

CREATE TABLE `movies` (
  `id` int(10) UNSIGNED NOT NULL,
  `title_br` varchar(255) NOT NULL,
  `title_us` varchar(255) NOT NULL,
  `release_year` year(4) NOT NULL,
  `media` char(32) NOT NULL,
  `imdb` varchar(255) NOT NULL,
  `first_user_id` int(10) UNSIGNED NOT NULL,
  `last_user_id` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `movies`
--

INSERT INTO `movies` (`id`, `title_br`, `title_us`, `release_year`, `media`, `imdb`, `first_user_id`, `last_user_id`) VALUES
(1, 'Constantine', 'Constantine', '2005', '292e1d876af81a261d705a78d1c3cd34', 'tt0360486', 1, 1),
(2, 'Amnésia', 'Memento', '2000', '7af3a99a75fec8906285fc133de5290e', 'tt0209144', 1, 1),
(3, 'Batman Begins', 'Batman Begins', '2005', '3b06faa313c0e3e7dd7045f48edd1505', 'tt0372784', 1, 1),
(4, 'Batman: O Cavaleiro das Trevas', 'The Dark Knight', '2008', 'db16d7995db09c206c99202a57c2c86e', 'tt0468569', 1, 1),
(5, 'Batman: O Cavaleiro das Trevas Ressurge', 'The Dark Knight Rises', '2012', 'cec2bf79de042d2296b35aa01fa1c255', 'tt1345836', 1, 1),
(6, 'A Origem', 'Inception', '2010', '9b136612aaa5c29597439aa5cf4d9543', 'tt1375666', 1, 1),
(7, 'Interestelar', 'Interstellar', '2014', 'bb320b0c7ad5c391634ee4f98c016215', 'tt0816692', 1, 1),
(8, 'A Chegada', 'Arrival', '2016', '148d1cc11ea0b26b581284b84ecef699', 'tt2543164', 1, 1),
(9, 'Não Fale o Mal', 'Speak No Evil', '2024', '5757bcda4b9855fd6efaa29cfcefd872', 'tt27534307', 2, 1),
(10, 'A Avaliação', 'The Assessment', '2024', 'f30d1eb1aa47122b6ff89dfaf5ad6ddf', 'tt32768323', 2, 1),
(11, 'O Primeiro Mentiroso', 'The Invention of Lying', '2009', '59aeb9e6c96efccd9d7bf5aa610a183e', 'tt1058017', 2, 1),
(12, 'Dois Caras Legais', 'The Nice Guys', '2016', '', 'tt3799694', 1, 1),
(13, 'Blade Runner 2049', 'Blade Runner 2049', '2017', '', 'tt1856101', 1, 1),
(14, 'Drive', 'Drive', '2011', '', 'tt0780504', 1, 1);

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

--
-- Despejando dados para a tabela `users`
--

INSERT INTO `users` (`id`, `steamid`, `personaname`, `avatarhash`, `name`, `created_date`) VALUES
(1, 76561198182810962, 'Undead', '89678e866b14b0bf85ac086e7bc62144d3b0a40b', 'Daniel', '2025-07-08'),
(2, 76561198000321269, 'Caio', '5fa521539cecc2217987e7dbc915dbcc02b5b7ec', 'Caio', '2025-07-08');

-- --------------------------------------------------------

--
-- Estrutura para tabela `user_movie_list`
--

CREATE TABLE `user_movie_list` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `movie_id` int(10) UNSIGNED NOT NULL,
  `watched` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `user_movie_list`
--

INSERT INTO `user_movie_list` (`id`, `user_id`, `movie_id`, `watched`) VALUES
(1, 1, 1, 1),
(2, 1, 2, 1),
(3, 1, 3, 1),
(4, 1, 4, 1),
(5, 1, 5, 1),
(6, 1, 6, 1),
(7, 1, 7, 1),
(8, 1, 8, 1),
(9, 2, 9, 1),
(10, 2, 10, 1),
(11, 2, 11, 1),
(12, 1, 10, 1);

--
-- Índices para tabelas despejadas
--

--
-- Índices de tabela `games`
--
ALTER TABLE `games`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `movies`
--
ALTER TABLE `movies`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `user_movie_list`
--
ALTER TABLE `user_movie_list`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT para tabelas despejadas
--

--
-- AUTO_INCREMENT de tabela `games`
--
ALTER TABLE `games`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `movies`
--
ALTER TABLE `movies`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT de tabela `users`
--
ALTER TABLE `users`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de tabela `user_movie_list`
--
ALTER TABLE `user_movie_list`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
