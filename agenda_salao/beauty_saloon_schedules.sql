-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Tempo de geração: 29/06/2025 às 17:55
-- Versão do servidor: 10.4.28-MariaDB
-- Versão do PHP: 8.0.28

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Banco de dados: `beauty_saloon_schedules`
--

-- --------------------------------------------------------

--
-- Estrutura para tabela `agendamentos`
--

CREATE TABLE `agendamentos` (
  `id` int(11) NOT NULL,
  `id_cliente` int(11) NOT NULL,
  `id_servico` int(11) NOT NULL,
  `data` date NOT NULL,
  `hora` time NOT NULL,
  `status` enum('agendado','concluido','cancelado') DEFAULT 'agendado'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `agendamentos`
--

INSERT INTO `agendamentos` (`id`, `id_cliente`, `id_servico`, `data`, `hora`, `status`) VALUES
(1, 3, 5, '2025-06-28', '09:00:00', 'cancelado'),
(2, 3, 3, '2025-06-28', '15:00:00', 'concluido'),
(3, 3, 5, '2025-06-30', '09:00:00', 'concluido'),
(4, 3, 3, '2025-06-30', '15:00:00', 'cancelado'),
(5, 3, 5, '2025-07-02', '13:00:00', 'cancelado');

-- --------------------------------------------------------

--
-- Estrutura para tabela `horarios_bloqueados`
--

CREATE TABLE `horarios_bloqueados` (
  `id` int(11) NOT NULL,
  `data` date NOT NULL,
  `hora_inicio` time NOT NULL,
  `hora_fim` time NOT NULL,
  `motivo` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `horarios_bloqueados`
--

INSERT INTO `horarios_bloqueados` (`id`, `data`, `hora_inicio`, `hora_fim`, `motivo`) VALUES
(8, '2025-06-30', '12:00:00', '13:30:00', 'Almoço');

-- --------------------------------------------------------

--
-- Estrutura para tabela `services`
--

CREATE TABLE `services` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` varchar(200) DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `duration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `services`
--

INSERT INTO `services` (`id`, `name`, `description`, `price`, `duration`) VALUES
(2, 'Barba e Sobrancelha Masculino', 'Somente Barba e Sobrancelha', 25.00, 30),
(3, 'Corte de Cabelo Feminino', 'Somente Corte', 40.00, 40),
(4, 'Hidratação', 'Hidratação do seu cabelo com os melhores produtos.', 60.00, 60),
(5, 'Alisamento', 'Alisamento do cabelo', 100.00, 120),
(6, 'Corte de Cabelo Masculino', 'Apenas corte de cabelo', 25.00, 20),
(7, 'Manicure', 'Manutenção de unha', 30.00, 30),
(8, 'Pedicure', 'Manutenção das unhas dos pés.', 30.00, 30),
(9, 'Depilação com Cera', 'Consultar preço, depende da área a ser depilada.', 50.00, 60),
(10, 'Pintar o Cabelo', 'Mude a cor do seu cabelo, de uma renovada no visual!', 100.00, 120),
(11, 'Luzes e Mechas', 'Dê um toque de coloração em algumas mechas do seu cabelo!', 80.00, 80),
(12, 'Maquiagem', 'Maquiagem simples e Maquiagem para festas e eventos!', 100.00, 90),
(13, 'Penteados', 'Penteados comuns e penteados para festas e eventos!', 120.00, 90);

-- --------------------------------------------------------

--
-- Estrutura para tabela `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(200) NOT NULL,
  `roles` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `users`
--

INSERT INTO `users` (`id`, `email`, `password`, `roles`) VALUES
(1, 'joao@paulo.com', '$2y$10$ectt3BCBHjHuTw7RHcVPrumawIjtJITFHe6mp77ez.QcX.fm8c/zC', 'admin'),
(3, 'mae@dete.com', '$2y$10$R27VPDB8RaLjkFSU1RSe5ugGoNwXiBb5HZcLCgDB/I9Xs.HUfFara', 'cliente'),
(4, 'brenda@venturi.com', '$2y$10$nKTbpe/uHUoB2CaTAtqZ8eTwcwa02VY29PelAqXYkTe9a5O9L5dOC', 'cliente');

--
-- Índices para tabelas despejadas
--

--
-- Índices de tabela `agendamentos`
--
ALTER TABLE `agendamentos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_cliente` (`id_cliente`),
  ADD KEY `id_servico` (`id_servico`);

--
-- Índices de tabela `horarios_bloqueados`
--
ALTER TABLE `horarios_bloqueados`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `services`
--
ALTER TABLE `services`
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
-- AUTO_INCREMENT de tabela `agendamentos`
--
ALTER TABLE `agendamentos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de tabela `horarios_bloqueados`
--
ALTER TABLE `horarios_bloqueados`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT de tabela `services`
--
ALTER TABLE `services`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT de tabela `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Restrições para tabelas despejadas
--

--
-- Restrições para tabelas `agendamentos`
--
ALTER TABLE `agendamentos`
  ADD CONSTRAINT `agendamentos_ibfk_1` FOREIGN KEY (`id_cliente`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `agendamentos_ibfk_2` FOREIGN KEY (`id_servico`) REFERENCES `services` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
