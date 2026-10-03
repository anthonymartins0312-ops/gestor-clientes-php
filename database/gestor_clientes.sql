-- =========================================================
--  Sistema de Gestão de Clientes
--  Banco de dados: gestor_clientes (MySQL / MariaDB - XAMPP)
--
--  ATENÇÃO: este script recria as tabelas. Ao importar
--  novamente, os dados existentes serão apagados.
--  Todos os dados de exemplo são FICTÍCIOS.
-- =========================================================

CREATE DATABASE IF NOT EXISTS gestor_clientes
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE gestor_clientes;

DROP TABLE IF EXISTS clientes;
DROP TABLE IF EXISTS usuarios;

-- ---------------------------------------------------------
-- Tabela: usuarios (quem acessa o sistema)
-- ---------------------------------------------------------
CREATE TABLE usuarios (
    id         INT UNSIGNED NOT NULL AUTO_INCREMENT,
    nome       VARCHAR(100) NOT NULL,
    email      VARCHAR(150) NOT NULL,
    senha      VARCHAR(255) NOT NULL COMMENT 'Hash gerado com password_hash()',
    created_at DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uk_usuarios_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------
-- Tabela: clientes (cada cliente pertence a um usuário)
-- ---------------------------------------------------------
CREATE TABLE clientes (
    id          INT UNSIGNED NOT NULL AUTO_INCREMENT,
    usuario_id  INT UNSIGNED NOT NULL COMMENT 'Dono do cadastro (usuarios.id)',
    nome        VARCHAR(150) NOT NULL,
    email       VARCHAR(150) NOT NULL,
    telefone    VARCHAR(20)  NULL,
    empresa     VARCHAR(150) NULL,
    status      ENUM('ativo', 'inativo') NOT NULL DEFAULT 'ativo',
    observacoes TEXT         NULL,
    created_at  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uk_clientes_usuario_email (usuario_id, email),
    KEY idx_clientes_nome (nome),
    KEY idx_clientes_status (status),
    KEY idx_clientes_created_at (created_at),
    CONSTRAINT fk_clientes_usuario
        FOREIGN KEY (usuario_id) REFERENCES usuarios (id)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------
-- Usuário de demonstração
-- E-mail: admin@example.com | Senha: admin123
-- ---------------------------------------------------------
INSERT INTO usuarios (nome, email, senha) VALUES
('Administrador Demo', 'admin@example.com', '$2y$10$apogA3xJXJ.3IEoLxHV4Fey1VoOEhsmVC8dHeP2zuH6B/hYT5Sf26');

SET @admin_id = LAST_INSERT_ID();

-- ---------------------------------------------------------
-- Clientes fictícios (pertencem somente ao usuário demo)
-- ---------------------------------------------------------
INSERT INTO clientes (usuario_id, nome, email, telefone, empresa, status, observacoes, created_at, updated_at) VALUES
(@admin_id, 'Diego Nascimento', 'diego.nascimento@example.com', '(71) 3222-1100',  'Nascimento Imóveis',        'ativo',   'Solicitou orçamento para site institucional.',            NOW() - INTERVAL 1 DAY,  NOW() - INTERVAL 1 DAY),
(@admin_id, 'Mariana Costa',    'mariana.costa@example.com',    '(11) 98765-4321', 'Studio Aurora Design',      'ativo',   'Prefere contato por e-mail no período da tarde.',         NOW() - INTERVAL 2 DAY,  NOW() - INTERVAL 2 DAY),
(@admin_id, 'Rafael Almeida',   'rafael.almeida@example.com',   '(21) 99876-5432', 'Almeida Consultoria',       'ativo',   NULL,                                                      NOW() - INTERVAL 5 DAY,  NOW() - INTERVAL 5 DAY),
(@admin_id, 'Juliana Ferreira', 'juliana.ferreira@example.com', '(31) 3456-7890',  'Ferreira & Lima Advocacia', 'inativo', 'Contrato encerrado. Retomar contato no próximo semestre.', NOW() - INTERVAL 12 DAY, NOW() - INTERVAL 12 DAY),
(@admin_id, 'Bruno Carvalho',   'bruno.carvalho@example.com',   '(41) 98123-4567', 'Carvalho Tech',             'ativo',   'Cliente recorrente: manutenção mensal.',                  NOW() - INTERVAL 20 DAY, NOW() - INTERVAL 20 DAY),
(@admin_id, 'Patrícia Gomes',   'patricia.gomes@example.com',   NULL,              NULL,                        'ativo',   'Autônoma, sem empresa vinculada.',                        NOW() - INTERVAL 35 DAY, NOW() - INTERVAL 35 DAY),
(@admin_id, 'Lucas Ribeiro',    'lucas.ribeiro@example.com',    '(51) 99654-3210', 'Ribeiro Alimentos',         'inativo', NULL,                                                      NOW() - INTERVAL 60 DAY, NOW() - INTERVAL 60 DAY),
(@admin_id, 'Camila Rocha',     'camila.rocha@example.com',     '(61) 98234-5678', 'Rocha Eventos',             'ativo',   'Indicada por outro cliente.',                             NOW() - INTERVAL 90 DAY, NOW() - INTERVAL 90 DAY);
