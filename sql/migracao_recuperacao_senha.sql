-- SafeRoute: executar uma única vez no phpMyAdmin com o banco EXISTENTE selecionado.
-- Não apaga nem altera contas, ocorrências ou rotas já cadastradas.
CREATE TABLE IF NOT EXISTS recuperacao_senha (
    id_usuario INT NOT NULL PRIMARY KEY,
    codigo_hash CHAR(64) NOT NULL,
    expira_em DATETIME NOT NULL,
    tentativas TINYINT UNSIGNED NOT NULL DEFAULT 0,
    ultima_solicitacao DATETIME NOT NULL,
    janela_inicio DATETIME NOT NULL,
    envios_na_janela TINYINT UNSIGNED NOT NULL DEFAULT 1,
    CONSTRAINT fk_recuperacao_usuario FOREIGN KEY (id_usuario)
        REFERENCES usuario(id_usuario) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS recuperacao_limite_ip (
    ip_hash CHAR(64) NOT NULL PRIMARY KEY,
    janela_inicio DATETIME NOT NULL,
    quantidade INT UNSIGNED NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
