-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Nov 18, 2026 at 09:47 AM
-- Server version: 8.4.8
-- PHP Version: 8.5.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `infodin`
--

-- --------------------------------------------------------

--
-- Table structure for table `candidaturas`
--

CREATE TABLE `candidaturas` (
  `id` int NOT NULL,
  `id_usuario` int NOT NULL,
  `id_vaga` int NOT NULL,
  `data_candidatura` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `status` enum('novo','em_analise','entrevista','rejeitado') CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT 'novo'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `empresas`
--

CREATE TABLE `empresas` (
  `id_empresa` int NOT NULL,
  `nome` varchar(100) DEFAULT NULL,
  `setor` varchar(50) DEFAULT NULL,
  `porte_empresa` varchar(50) DEFAULT NULL,
  `email_corporativo` varchar(100) NOT NULL,
  `site_empresa` varchar(100) DEFAULT NULL,
  `telefone` varchar(20) DEFAULT NULL,
  `senha_empresa` varchar(255) NOT NULL,
  `cidade_sede` varchar(100) DEFAULT NULL,
  `cnpj` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `usuarios`
--

CREATE TABLE `usuarios` (
  `id` int NOT NULL,
  `nome` varchar(100) NOT NULL,
  `telefone` varchar(20) DEFAULT NULL,
  `senha` varchar(255) NOT NULL,
  `email` varchar(100) NOT NULL,
  `cidade` varchar(100) DEFAULT NULL,
  `data_nascimento` date DEFAULT NULL,
  `foto_perfil` varchar(100) DEFAULT NULL,
  `cargo_atual` varchar(100) DEFAULT NULL,
  `area_atuacao` varchar(50) DEFAULT NULL,
  `experiencia` varchar(50) DEFAULT NULL,
  `escolaridade` varchar(50) DEFAULT NULL,
  `regime_preferido` varchar(50) DEFAULT NULL,
  `sobre` text,
  `linkedin` varchar(255) DEFAULT NULL,
  `criado_em` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `vagas`
--

CREATE TABLE `vagas` (
  `id` int NOT NULL,
  `titulo` varchar(150) NOT NULL,
  `descricao` text NOT NULL,
  `requisito` text NOT NULL,
  `salario_min` decimal(10,2) DEFAULT NULL,
  `salario_max` decimal(10,2) DEFAULT NULL,
  `exibir_salario` enum('sim','nao','combinar') DEFAULT 'sim',
  `localizacao` varchar(100) DEFAULT NULL,
  `regime` enum('presencial','hibrido','remoto') DEFAULT NULL,
  `beneficio` text,
  `data_publicacao` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `status` enum('aberta','pausada','encerrada') DEFAULT 'aberta'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `candidaturas`
--
ALTER TABLE `candidaturas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_usuario` (`id_usuario`),
  ADD KEY `id_vaga` (`id_vaga`);

--
-- Indexes for table `empresas`
--
ALTER TABLE `empresas`
  ADD PRIMARY KEY (`id_empresa`);

--
-- Indexes for table `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `vagas`
--
ALTER TABLE `vagas`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `candidaturas`
--
ALTER TABLE `candidaturas`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `empresas`
--
ALTER TABLE `empresas`
  MODIFY `id_empresa` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `vagas`
--
ALTER TABLE `vagas`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `candidaturas`
--
ALTER TABLE `candidaturas`
  ADD CONSTRAINT `candidaturas_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `candidaturas_ibfk_2` FOREIGN KEY (`id_vaga`) REFERENCES `vagas` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
