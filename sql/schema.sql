-- =============================================
-- SafeRoute — Schema do Banco de Dados
-- =============================================
-- Versão: 1.0
-- Baseado na SPEC.md (Seção 8 — Modelo de dados)
-- =============================================

-- Criar banco de dados
CREATE DATABASE IF NOT EXISTS saferoute
    DEFAULT CHARACTER SET utf8mb4
    DEFAULT COLLATE utf8mb4_unicode_ci;

USE saferoute;

-- =============================================
-- Tabela: usuario
-- Armazena os dados dos usuários do sistema.
-- Relacionamentos: 1:N com ocorrencia, 1:N com rota
-- =============================================
CREATE TABLE IF NOT EXISTS usuario (
    id_usuario    INT            AUTO_INCREMENT PRIMARY KEY,
    nome          VARCHAR(100)   NOT NULL,
    email         VARCHAR(150)   NOT NULL,
    senha         VARCHAR(255)   NOT NULL COMMENT 'Hash bcrypt gerado com password_hash()',
    tipo_usuario  ENUM('comum', 'admin') NOT NULL DEFAULT 'comum',
    data_cadastro DATETIME       NOT NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT uq_usuario_email UNIQUE (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- Tabela: tipo_ocorrencia
-- Define os tipos possíveis de ocorrências.
-- Valores iniciais: Furto, Roubo, Vandalismo, Agressão, Outro
-- =============================================
CREATE TABLE IF NOT EXISTS tipo_ocorrencia (
    id_tipo   INT          AUTO_INCREMENT PRIMARY KEY,
    nome_tipo VARCHAR(50)  NOT NULL,

    CONSTRAINT uq_tipo_nome UNIQUE (nome_tipo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- Tabela: ocorrencia
-- Registra ocorrências de segurança com geolocalização.
-- FK: id_usuario → usuario, id_tipo → tipo_ocorrencia
-- =============================================
CREATE TABLE IF NOT EXISTS ocorrencia (
    id_ocorrencia INT             AUTO_INCREMENT PRIMARY KEY,
    id_usuario    INT             NOT NULL,
    id_tipo       INT             NOT NULL,
    descricao     TEXT            NULL,
    latitude      DECIMAL(10, 8)  NOT NULL,
    longitude     DECIMAL(11, 8)  NOT NULL,
    data          DATE            NOT NULL,
    horario       TIME            NOT NULL,
    status        ENUM('pendente', 'aprovada', 'rejeitada') NOT NULL DEFAULT 'pendente',
    fonte         ENUM('usuario', 'oficial')                NOT NULL DEFAULT 'usuario',

    CONSTRAINT fk_ocorrencia_usuario
        FOREIGN KEY (id_usuario) REFERENCES usuario (id_usuario)
        ON DELETE CASCADE,

    CONSTRAINT fk_ocorrencia_tipo
        FOREIGN KEY (id_tipo) REFERENCES tipo_ocorrencia (id_tipo)
        ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- Tabela: rota
-- Armazena consultas de rotas realizadas pelos usuários.
-- FK: id_usuario → usuario
-- =============================================
CREATE TABLE IF NOT EXISTS rota (
    id_rota        INT             AUTO_INCREMENT PRIMARY KEY,
    id_usuario     INT             NOT NULL,
    origem         VARCHAR(255)    NOT NULL,
    destino        VARCHAR(255)    NOT NULL,
    distancia      DECIMAL(10, 2)  NULL COMMENT 'Distância em km',
    risco_estimado DECIMAL(5, 2)   NULL COMMENT 'Score de risco 0-100',
    data_consulta  DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_rota_usuario
        FOREIGN KEY (id_usuario) REFERENCES usuario (id_usuario)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
