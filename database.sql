-- Banco de dados da plataforma Infodin
-- Compatível com MySQL 8+ e MariaDB 10.4+
-- Execute este arquivo uma vez no seu servidor MySQL/MariaDB.
-- IMPORTANTE: este script não exclui dados existentes; ele cria o banco e as tabelas ausentes.

CREATE DATABASE IF NOT EXISTS infodin
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE infodin;

CREATE TABLE IF NOT EXISTS usuarios (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    tipo ENUM('candidato', 'empresa') NOT NULL,
    email VARCHAR(255) NOT NULL,
    senha VARCHAR(255) NOT NULL,
    ativo BOOLEAN NOT NULL DEFAULT TRUE,
    criado_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    atualizado_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_usuarios_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS candidatos (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    usuario_id BIGINT UNSIGNED NULL,
    nome VARCHAR(150) NOT NULL,
    email VARCHAR(255) NOT NULL,
    senha VARCHAR(255) NOT NULL,
    telefone VARCHAR(30) NULL,
    cidade VARCHAR(120) NULL,
    data_nascimento DATE NULL,
    foto_perfil VARCHAR(255) NULL,
    cargo VARCHAR(150) NULL,
    area VARCHAR(100) NULL,
    experiencia VARCHAR(50) NULL,
    escolaridade VARCHAR(100) NULL,
    regime_preferido VARCHAR(255) NULL,
    linkedin VARCHAR(255) NULL,
    sobre TEXT NULL,
    notificacoes BOOLEAN NOT NULL DEFAULT TRUE,
    criado_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    atualizado_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_candidatos_email (email),
    UNIQUE KEY uq_candidatos_usuario (usuario_id),
    KEY idx_candidatos_cidade (cidade),
    KEY idx_candidatos_area (area),
    CONSTRAINT fk_candidatos_usuario
        FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
        ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS empresas (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    usuario_id BIGINT UNSIGNED NULL,
    cnpj VARCHAR(18) NULL,
    nome VARCHAR(150) NOT NULL,
    razao_social VARCHAR(180) NULL,
    setor VARCHAR(100) NULL,
    porte VARCHAR(50) NULL,
    email_corporativo VARCHAR(255) NULL,
    telefone VARCHAR(30) NULL,
    site VARCHAR(255) NULL,
    cidade_sede VARCHAR(120) NULL,
    descricao TEXT NULL,
    logo VARCHAR(255) NULL,
    criado_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    atualizado_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_empresas_cnpj (cnpj),
    UNIQUE KEY uq_empresas_usuario (usuario_id),
    KEY idx_empresas_cidade (cidade_sede),
    CONSTRAINT fk_empresas_usuario
        FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
        ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS vagas (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    empresa_id BIGINT UNSIGNED NOT NULL,
    titulo VARCHAR(150) NOT NULL,
    area VARCHAR(100) NOT NULL,
    regime ENUM('Presencial', 'Híbrido', 'Remoto') NOT NULL,
    localizacao VARCHAR(150) NULL,
    salario_min DECIMAL(12,2) NULL,
    salario_max DECIMAL(12,2) NULL,
    exibir_salario BOOLEAN NOT NULL DEFAULT FALSE,
    descricao TEXT NOT NULL,
    requisitos TEXT NULL,
    beneficios TEXT NULL,
    status ENUM('rascunho', 'ativa', 'pausada', 'encerrada') NOT NULL DEFAULT 'rascunho',
    criado_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    atualizado_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_vagas_empresa (empresa_id),
    KEY idx_vagas_status (status),
    KEY idx_vagas_area (area),
    KEY idx_vagas_regime (regime),
    CONSTRAINT fk_vagas_empresa
        FOREIGN KEY (empresa_id) REFERENCES empresas(id)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS candidaturas (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    candidato_id BIGINT UNSIGNED NOT NULL,
    vaga_id BIGINT UNSIGNED NOT NULL,
    portifolio VARCHAR(255) NULL,
    mensagem TEXT NULL,
    status ENUM('recebida', 'em_analise', 'aprovada', 'recusada') NOT NULL DEFAULT 'recebida',
    criado_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    atualizado_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_candidatura_candidato_vaga (candidato_id, vaga_id),
    KEY idx_candidaturas_vaga_status (vaga_id, status),
    KEY idx_candidaturas_candidato (candidato_id),
    CONSTRAINT fk_candidaturas_candidato
        FOREIGN KEY (candidato_id) REFERENCES candidatos(id)
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_candidaturas_vaga
        FOREIGN KEY (vaga_id) REFERENCES vagas(id)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS vagas_salvas (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    candidato_id BIGINT UNSIGNED NOT NULL,
    vaga_id BIGINT UNSIGNED NOT NULL,
    criado_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_vaga_salva (candidato_id, vaga_id),
    KEY idx_vagas_salvas_vaga (vaga_id),
    CONSTRAINT fk_vagas_salvas_candidato
        FOREIGN KEY (candidato_id) REFERENCES candidatos(id)
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_vagas_salvas_vaga
        FOREIGN KEY (vaga_id) REFERENCES vagas(id)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
