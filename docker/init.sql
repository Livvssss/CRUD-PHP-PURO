-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: mysql
-- Tempo de geração: 09/06/2026 às 19:08
-- Versão do servidor: 8.0.46
-- Versão do PHP: 8.3.26

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Banco de dados: `biblioteca_pw`
--

-- --------------------------------------------------------

--
-- Estrutura para tabela `authors`
--

CREATE TABLE `authors` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `name` varchar(255) NOT NULL,
  `bio` text NOT NULL,
  `photo` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Despejando dados para a tabela `authors`
--

INSERT INTO `authors` (`id`, `user_id`, `name`, `bio`, `photo`, `created_at`) VALUES
(2, 1, 'Chloe Walsh', 'Chloe Walsh é uma autora de romances best-seller internacional, com dez livros publicados, incluindo a série de grande sucesso \"Broken\", uma série de suspense romântico em quatro volumes. Apesar de ser uma romântica incurável, Chloe gosta de fazer com que seus personagens lutem por seus finais felizes, abordando questões da vida real com emoção e personagens cativantes. Nascida em Cork, Irlanda, Chloe reside atualmente lá com o marido e dois filhos. Sinta-se à vontade para entrar em contato com Chloe nas redes sociais, onde ela adora interagir com seus leitores.', '6a138e6ea8362.jpg', '2026-05-24 23:49:02'),
(4, 1, 'Colleen Hoover', 'Margaret Colleen Hoover é uma autora americana que escreve principalmente romances e livros para jovens adultos. Ela é mais conhecida por seu romance de 2016, \"It Ends with Us\" (É Assim Que Acaba). Muitas de suas obras foram autopublicadas antes de serem publicadas por uma editora.', '6a184cc0d8db2.jpg', '2026-05-28 14:10:08'),
(8, 3, 'Colleen Hoover', 'DIVAAAAAAAAAAAA', '6a20e1d4160ca.jpg', '2026-06-04 02:24:20'),
(13, 1, 'Lynn Painter', 'Lynn Painter é uma autora americana de romances contemporâneos. Ela escreve principalmente para o público jovem adulto e adulto. Ganhou popularidade através do BookTok com seu romance de estreia de 2021, Better Than the Movies. Seus romances são categorizados por sua semelhança com filmes de comédia romântica.', '6a26f2eae0e52.jpg', '2026-06-08 16:50:50'),
(14, 1, 'Holly Black', 'Holly Black é uma escritora e editora americana mais conhecida por seus livros infantis e juvenis. Seu trabalho mais recente é a série juvenil Folk of the Air, best-seller do New York Times.', '6a26f4811ac0a.jpg', '2026-06-08 16:57:37'),
(15, 1, 'Tahereh Mafi', 'Tahereh Mafi é uma autora iraniana-americana que reside em Santa Monica, Califórnia. Ela é conhecida por escrever ficção para jovens adultos.', '6a26f4d8841d3.webp', '2026-06-08 16:59:04'),
(16, 1, 'Elle Kennedy', 'Elle Kennedy é uma autora canadense de romances contemporâneos e romances de suspense. Ela é vencedora do prêmio RITA da Romance Writers of America.', '6a26f5a195478.jpg', '2026-06-08 17:02:25'),
(17, 1, 'Jenny Han', 'Jenny Han é uma autora, roteirista, produtora executiva e showrunner americana. Ela é mais conhecida por escrever a trilogia \"The Summer I Turned Pretty\", que adaptou para uma série de TV do Prime Video. Ela também escreveu a trilogia \"To All the Boys\", que foi adaptada para uma série de filmes da Netflix .', '6a26f611db538.jpg', '2026-06-08 17:03:08'),
(18, 1, 'Jane Austen', 'Jane Austen (1775-1817) foi uma escritora inglesa, considerada uma das maiores romancistas da literatura inglesas do século XIX. É autora de clássicos como \"Orgulho e Preconceito\" e \"Razão e Sensibilidade\".', '6a26f66d32643.jpg', '2026-06-08 17:05:49'),
(19, 1, 'Ali Hazelwood', 'Ali Hazelwood é o pseudônimo de uma escritora italiana de romances e professora de neurociência radicada nos Estados Unidos. Muitas de suas obras abordam o tema da mulher nas áreas de ciência, tecnologia, engenharia e matemática (STEM, na sigla em inglês) e no meio acadêmico. Seu romance de estreia, A Hipótese do Amor, foi um best-seller do New York Times e está sendo adaptado para o cinema.', '6a26f6934c43f.webp', '2026-06-08 17:06:27'),
(20, 1, 'Holly Jackson', 'Holly Jackson é uma autora britânica de romances policiais. Ela é mais conhecida pela sua série \"A Good Girl\'s Guide to Murder\" (Manual de Assassinato para Boas Garotas).', '6a26f70b2074a.jpg', '2026-06-08 17:08:27'),
(21, 1, 'Stephanie Garber', 'Stephanie Garber é uma autora americana de ficção para jovens adultos, conhecida pelas trilogias interligadas Caraval e Once Upon a Broken Heart.', '6a26f73f8c7ef.jpg', '2026-06-08 17:09:19'),
(22, 1, 'Stephanie Archer', 'Stephanie Archer escreve comédias românticas picantes com diálogos afiados, muitas risadas e finais felizes garantidos. Ela acredita no poder das melhores amigas, mulheres teimosas, um corte de cabelo novo e amor. Ela mora em Vancouver com um homem, um cachorro e um bebê.', '6a26f7737caaf.jpg', '2026-06-08 17:10:11'),
(23, 1, 'Lauren Roberts', 'Lauren Roberts é a autora best-seller número 1 do New York Times e internacionalmente aclamada por seus livros \"Powerless\", \"Powerful\", \"Reckless\", \"Fearless\" e \"Fearful\". Seus livros venderam mais de cinco milhões de cópias em todo o mundo.', '6a26f7a61cf49.jpg', '2026-06-08 17:11:02'),
(24, 1, 'Raphael Montes', 'Raphael Montes de Carvalho é um romancista policial e advogado brasileiro. Foi duas vezes finalista do Prêmio Jabuti, vencendo uma vez.', '6a26f7e139324.webp', '2026-06-08 17:12:01'),
(25, 1, 'Pedro Rhuas', 'Pedro Rhuas é escritor, jornalista, cantor e compositor. Crescido em cidades do interior do Rio Grande do Norte e do litoral do Ceará, ele traz representatividade nordestina e LGBTQIA+ para suas obras.', '6a26f80155bc3.jpg', '2026-06-08 17:12:33');

-- --------------------------------------------------------

--
-- Estrutura para tabela `posts`
--

CREATE TABLE `posts` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `content` text NOT NULL,
  `image` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `reading_progress` varchar(255) DEFAULT NULL,
  `book_id` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Despejando dados para a tabela `posts`
--

INSERT INTO `posts` (`id`, `user_id`, `content`, `image`, `created_at`, `reading_progress`, `book_id`) VALUES
(1, 1, 'MUIIITOOOO BOMMM', '6a1751f38ac20.jpg', '2026-05-27 20:20:03', NULL, 0),
(2, 1, 'Boa', NULL, '2026-05-27 21:34:55', NULL, 0),
(3, 1, 'oi', NULL, '2026-05-27 21:37:57', '120', 0),
(4, 1, 'POsso estar querendo o Johnny Kavanagh pra mim?', NULL, '2026-05-27 21:45:39', '120', 2),
(5, 1, 'GOSTOSA DE MAIS', NULL, '2026-05-28 11:37:03', '67', 3);

-- --------------------------------------------------------

--
-- Estrutura para tabela `reviews`
--

CREATE TABLE `reviews` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `book_id` int NOT NULL,
  `review_title` varchar(255) NOT NULL,
  `review_text` text NOT NULL,
  `rating` decimal(2,1) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Despejando dados para a tabela `reviews`
--

INSERT INTO `reviews` (`id`, `user_id`, `book_id`, `review_title`, `review_text`, `rating`, `created_at`) VALUES
(1, 1, 2, 'AMEI ESSE LIVROOOOOOOOOOOO', 'MELHOR LIVRO DA VIDAAAAAAAAAAAA', 5.0, '2026-05-27 21:23:05'),
(3, 1, 5, 'AMEI ESSE LIVROOOOOOOOOOOO', 'FODAAAAAAAAAAAAAAAAAAAAAAA MILES ARCHER EU TE AMO SEU LINDOOOOOOOOOOOOOOOOOOOOOOOO', 5.0, '2026-05-28 11:36:15');

-- --------------------------------------------------------

--
-- Estrutura para tabela `shelves`
--

CREATE TABLE `shelves` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `author_id` int NOT NULL,
  `title` varchar(255) NOT NULL,
  `cover` varchar(255) DEFAULT NULL,
  `release_date` date DEFAULT NULL,
  `reading_status` varchar(50) DEFAULT 'Lendo',
  `rating` decimal(2,1) DEFAULT NULL,
  `reading_date` date DEFAULT NULL,
  `reading_start` date DEFAULT NULL,
  `reading_end` date DEFAULT NULL,
  `reading_goal` varchar(255) DEFAULT NULL,
  `reading_time` varchar(255) DEFAULT NULL,
  `tags` varchar(255) DEFAULT NULL,
  `review` text,
  `quotes` text,
  `reading_history` text,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `reading_progress` varchar(255) DEFAULT NULL,
  `current_page` int DEFAULT NULL,
  `total_pages` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Despejando dados para a tabela `shelves`
--

INSERT INTO `shelves` (`id`, `user_id`, `author_id`, `title`, `cover`, `release_date`, `reading_status`, `rating`, `reading_date`, `reading_start`, `reading_end`, `reading_goal`, `reading_time`, `tags`, `review`, `quotes`, `reading_history`, `created_at`, `reading_progress`, `current_page`, `total_pages`) VALUES
(1, 1, 1, 'Powerless', '6a1375784c08b.jpg', '2010-03-22', 'Lendo', 5.0, '2026-05-29', '2026-05-12', '2026-05-29', '', '', '', '', '', '', '2026-05-24 22:02:32', NULL, NULL, NULL),
(3, 1, 1, 'livvmatoso', '6a176aca6422b.jpg', '2010-03-02', 'Lendo', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-05-27 22:06:02', NULL, NULL, NULL),
(5, 1, 3, 'O lado feio do amor', '6a182871c0757.jpg', '2026-05-12', 'Lendo', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-05-28 11:35:13', NULL, NULL, NULL),
(7, 1, 7, 'Com Amor, Atena', '6a20d44257f6f.jpg', '2026-01-08', 'Lendo', NULL, NULL, NULL, NULL, '', '', '', '', '', '', '2026-06-04 01:26:26', NULL, NULL, NULL),
(8, 3, 8, 'Se não fosse você', '6a20e1f3813c8.jpg', '4222-02-01', 'Lendo', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-06-04 02:24:51', NULL, NULL, NULL),
(9, 1, 23, 'Powerless', '6a26f8af6f6f7.jpg', '2023-01-31', 'Quero Ler', NULL, NULL, NULL, NULL, '2026', '', '', '', '', '', '2026-06-08 17:15:27', NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Estrutura para tabela `users`
--

CREATE TABLE `users` (
  `id` int NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Despejando dados para a tabela `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password`, `created_at`) VALUES
(1, 'Liv', 'livvsmroberto@gmail.com', '$2y$12$C9Q9d8N8WcOTLI.rTGa7KuV/DrggkQu07v8hHnxRnEA9oDPs2xoVq', '2026-05-24 17:02:21'),
(2, 'isa', 'livVvsmroberto@gmail.com', '$2y$12$JqyR/fJJKFnOucsnDCTsKOgbom3HHCXeOs6i5pGDezIf9WR5uB/qS', '2026-05-24 19:51:02'),
(3, 'Liz', 'livsmroberto@gmail.com', '$2y$12$7B1oWYwA28cKz9JfS.RCnunpd00AONj6Fux6d0wO2PYrey2g60xLC', '2026-06-04 02:05:53'),
(4, 'Livia', 'liv.matoso@gmail.com', '$2y$12$ebcnAxLikEhah2Bgr7ueJOZXNjy7edz87S.l0SLkuDx5.3XWmC8Te', '2026-06-04 02:07:42'),
(5, 'Nicolas Davi da Silva', 'nicolaszz7681@gmail.com', '$2y$12$OjLFh7kH2Sy7fKw2i0J4V.OKPsKHAGBcU2isj2GBLHn.zhtwxZ64S', '2026-06-04 02:12:12'),
(6, 'Tetano do greu', 'tetanozinhodograuzinho@gmail.com', '$2y$12$tU5.5m85wVgZN6.cm/V22ec6VRdK3MOW4NifENymPt/mLBlbnKsda', '2026-06-09 13:44:51'),
(7, 'isa', 'isadora@gmail.com', '$2y$12$a19TNPBtsJtGF4Ne4rnBueYtM0La51B32X4v9woYTyCOKhc9WepzG', '2026-06-09 14:28:07');

--
-- Índices para tabelas despejadas
--

--
-- Índices de tabela `authors`
--
ALTER TABLE `authors`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `posts`
--
ALTER TABLE `posts`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `reviews`
--
ALTER TABLE `reviews`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `shelves`
--
ALTER TABLE `shelves`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT para tabelas despejadas
--

--
-- AUTO_INCREMENT de tabela `authors`
--
ALTER TABLE `authors`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT de tabela `posts`
--
ALTER TABLE `posts`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de tabela `reviews`
--
ALTER TABLE `reviews`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de tabela `shelves`
--
ALTER TABLE `shelves`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT de tabela `users`
--
ALTER TABLE `users`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
