-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Tempo de geração: 21/07/2025 às 13:26
-- Versão do servidor: 10.4.32-MariaDB
-- Versão do PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Banco de dados: `projeto`
--
CREATE DATABASE IF NOT EXISTS `projeto` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `projeto`;

-- --------------------------------------------------------

--
-- Estrutura para tabela `cartoes_credito`
--

DROP TABLE IF EXISTS `cartoes_credito`;
CREATE TABLE `cartoes_credito` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `nome_cartao` varchar(100) NOT NULL,
  `limite` decimal(10,2) NOT NULL,
  `fechamento_dia` int(11) NOT NULL,
  `vencimento_dia` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `categorias`
--

DROP TABLE IF EXISTS `categorias`;
CREATE TABLE `categorias` (
  `id` int(11) NOT NULL,
  `nome` varchar(50) NOT NULL,
  `ativo` varchar(2) DEFAULT 'S'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `gastos`
--

DROP TABLE IF EXISTS `gastos`;
CREATE TABLE `gastos` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `categoria_id` int(11) DEFAULT NULL,
  `descricao` varchar(255) DEFAULT NULL,
  `valor` decimal(10,2) NOT NULL,
  `data_gasto` date NOT NULL,
  `metodo_pagamento` enum('Dinheiro','Débito','Crédito','Pix','Outro') NOT NULL,
  `cartao_id` int(11) DEFAULT NULL,
  `parcelado` varchar(1) DEFAULT 'N',
  `dataVencimento` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `gastos_recorrentes`
--

DROP TABLE IF EXISTS `gastos_recorrentes`;
CREATE TABLE `gastos_recorrentes` (
  `id` int(11) NOT NULL,
  `nome` varchar(255) NOT NULL,
  `categoria_id` int(11) NOT NULL,
  `cartao_id` int(11) DEFAULT NULL,
  `usuario_id` int(11) NOT NULL,
  `valor` decimal(10,2) NOT NULL,
  `ativo` enum('S','N') DEFAULT 'S'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `gastos_recorrentes_lancamentos`
--

DROP TABLE IF EXISTS `gastos_recorrentes_lancamentos`;
CREATE TABLE `gastos_recorrentes_lancamentos` (
  `id` int(11) NOT NULL,
  `gasto_recorrente_id` int(11) DEFAULT NULL,
  `mes_referencia` date DEFAULT NULL,
  `valor` decimal(10,2) DEFAULT NULL,
  `nome` varchar(255) DEFAULT NULL,
  `categoria_id` int(11) DEFAULT NULL,
  `cartao_id` int(11) DEFAULT NULL,
  `usuario_id` int(11) DEFAULT NULL,
  `criado_em` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `parcelas`
--

DROP TABLE IF EXISTS `parcelas`;
CREATE TABLE `parcelas` (
  `id` int(11) NOT NULL,
  `gasto_id` int(11) NOT NULL,
  `numero_parcela` int(11) NOT NULL,
  `valor_parcela` decimal(10,2) NOT NULL,
  `data_vencimento` date NOT NULL,
  `ativo` varchar(1) DEFAULT 'S',
  `parcelas_total` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `renda_mensal`
--

DROP TABLE IF EXISTS `renda_mensal`;
CREATE TABLE `renda_mensal` (
  `id` int(11) NOT NULL,
  `valor` decimal(10,2) NOT NULL,
  `descricao` varchar(255) NOT NULL,
  `data_registro` date NOT NULL DEFAULT curdate(),
  `usuario_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `usuarios`
--

DROP TABLE IF EXISTS `usuarios`;
CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL,
  `nome` varchar(100) NOT NULL,
  `senha` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Índices para tabelas despejadas
--

--
-- Índices de tabela `cartoes_credito`
--
ALTER TABLE `cartoes_credito`
  ADD PRIMARY KEY (`id`),
  ADD KEY `usuario_id` (`usuario_id`);

--
-- Índices de tabela `categorias`
--
ALTER TABLE `categorias`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `gastos`
--
ALTER TABLE `gastos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `usuario_id` (`usuario_id`),
  ADD KEY `categoria_id` (`categoria_id`),
  ADD KEY `cartao_id` (`cartao_id`);

--
-- Índices de tabela `gastos_recorrentes`
--
ALTER TABLE `gastos_recorrentes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_grec_categoria` (`categoria_id`),
  ADD KEY `fk_grec_cartao` (`cartao_id`),
  ADD KEY `fk_grec_usuario` (`usuario_id`);

--
-- Índices de tabela `gastos_recorrentes_lancamentos`
--
ALTER TABLE `gastos_recorrentes_lancamentos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `gasto_recorrente_id` (`gasto_recorrente_id`);

--
-- Índices de tabela `parcelas`
--
ALTER TABLE `parcelas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `gasto_id` (`gasto_id`);

--
-- Índices de tabela `renda_mensal`
--
ALTER TABLE `renda_mensal`
  ADD PRIMARY KEY (`id`),
  ADD KEY `usuario_id` (`usuario_id`);

--
-- Índices de tabela `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT para tabelas despejadas
--

--
-- AUTO_INCREMENT de tabela `cartoes_credito`
--
ALTER TABLE `cartoes_credito`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `categorias`
--
ALTER TABLE `categorias`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `gastos`
--
ALTER TABLE `gastos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `gastos_recorrentes`
--
ALTER TABLE `gastos_recorrentes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `gastos_recorrentes_lancamentos`
--
ALTER TABLE `gastos_recorrentes_lancamentos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `parcelas`
--
ALTER TABLE `parcelas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `renda_mensal`
--
ALTER TABLE `renda_mensal`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Restrições para tabelas despejadas
--

--
-- Restrições para tabelas `cartoes_credito`
--
ALTER TABLE `cartoes_credito`
  ADD CONSTRAINT `cartoes_credito_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`);

--
-- Restrições para tabelas `gastos`
--
ALTER TABLE `gastos`
  ADD CONSTRAINT `gastos_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`),
  ADD CONSTRAINT `gastos_ibfk_2` FOREIGN KEY (`categoria_id`) REFERENCES `categorias` (`id`),
  ADD CONSTRAINT `gastos_ibfk_3` FOREIGN KEY (`cartao_id`) REFERENCES `cartoes_credito` (`id`) ON DELETE SET NULL;

--
-- Restrições para tabelas `gastos_recorrentes`
--
ALTER TABLE `gastos_recorrentes`
  ADD CONSTRAINT `fk_grec_cartao` FOREIGN KEY (`cartao_id`) REFERENCES `cartoes_credito` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_grec_categoria` FOREIGN KEY (`categoria_id`) REFERENCES `categorias` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_grec_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `gastos_recorrentes_lancamentos`
--
ALTER TABLE `gastos_recorrentes_lancamentos`
  ADD CONSTRAINT `gastos_recorrentes_lancamentos_ibfk_1` FOREIGN KEY (`gasto_recorrente_id`) REFERENCES `gastos_recorrentes` (`id`);

--
-- Restrições para tabelas `parcelas`
--
ALTER TABLE `parcelas`
  ADD CONSTRAINT `parcelas_ibfk_1` FOREIGN KEY (`gasto_id`) REFERENCES `gastos` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `renda_mensal`
--
ALTER TABLE `renda_mensal`
  ADD CONSTRAINT `renda_mensal_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
