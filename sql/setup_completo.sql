-- ============================================================
--  Sky Finance — Setup Completo do Banco de Dados
--  Execute este arquivo no phpMyAdmin para criar tudo do zero.
--  Seguro para rodar em bancos já existentes (IF NOT EXISTS).
-- ============================================================

CREATE DATABASE IF NOT EXISTS `projeto`
  DEFAULT CHARACTER SET utf8mb4
  COLLATE utf8mb4_general_ci;

USE `projeto`;

SET FOREIGN_KEY_CHECKS = 0;

-- ------------------------------------------------------------
-- USUÁRIOS
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `usuarios` (
  `id`           INT          NOT NULL AUTO_INCREMENT,
  `nome`         VARCHAR(100) NOT NULL,
  `foto`         VARCHAR(255) NULL DEFAULT NULL,
  `email`        VARCHAR(150) NOT NULL,
  `senha_hash`   VARCHAR(255) NOT NULL,
  `ativo`        TINYINT(1)   NOT NULL DEFAULT 1,
  `ultimo_login` DATETIME     NULL,
  `created_at`   DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ------------------------------------------------------------
-- TENTATIVAS DE LOGIN (rate limiting)
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `login_tentativas` (
  `id`               INT         NOT NULL AUTO_INCREMENT,
  `ip`               VARCHAR(45) NOT NULL,
  `tentativas`       INT         NOT NULL DEFAULT 1,
  `bloqueado_ate`    DATETIME    NULL,
  `ultima_tentativa` DATETIME    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_ip` (`ip`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ------------------------------------------------------------
-- CATEGORIAS
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `categorias` (
  `id`    INT         NOT NULL AUTO_INCREMENT,
  `nome`  VARCHAR(50) NOT NULL,
  `cor`   VARCHAR(7)  NOT NULL DEFAULT '#6B7280',
  `icone` VARCHAR(50) NULL DEFAULT NULL,
  `ativo` VARCHAR(2)  NOT NULL DEFAULT 'S',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ------------------------------------------------------------
-- CARTÕES DE CRÉDITO
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `cartoes_credito` (
  `id`             INT          NOT NULL AUTO_INCREMENT,
  `usuario_id`     INT          NOT NULL,
  `nome_cartao`    VARCHAR(100) NOT NULL,
  `limite`         DECIMAL(10,2) NOT NULL,
  `fechamento_dia` INT          NOT NULL,
  `vencimento_dia` INT          NOT NULL,
  PRIMARY KEY (`id`),
  KEY `usuario_id` (`usuario_id`),
  CONSTRAINT `cartoes_credito_ibfk_1`
    FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ------------------------------------------------------------
-- RESPONSÁVEIS (quem deve a você)
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `responsaveis` (
  `id`         INT          NOT NULL AUTO_INCREMENT,
  `usuario_id` INT          NOT NULL DEFAULT 1,
  `nome`       VARCHAR(100) NOT NULL,
  `cor`        VARCHAR(7)   NOT NULL DEFAULT '#3B82F6',
  `created_at` TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ------------------------------------------------------------
-- GASTOS (débito / dinheiro / pix / crédito)
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `gastos` (
  `id`               INT          NOT NULL AUTO_INCREMENT,
  `usuario_id`       INT          NOT NULL,
  `categoria_id`     INT          NULL DEFAULT NULL,
  `descricao`        VARCHAR(255) NULL DEFAULT NULL,
  `valor`            DECIMAL(10,2) NOT NULL,
  `data_gasto`       DATE         NOT NULL,
  `metodo_pagamento` ENUM('Dinheiro','Débito','Crédito','Pix','Outro') NOT NULL,
  `cartao_id`        INT          NULL DEFAULT NULL,
  `responsavel_id`   INT          NULL DEFAULT NULL,
  `parcelado`        VARCHAR(1)   NOT NULL DEFAULT 'N',
  `dataVencimento`   DATE         NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `usuario_id`   (`usuario_id`),
  KEY `categoria_id` (`categoria_id`),
  KEY `cartao_id`    (`cartao_id`),
  CONSTRAINT `gastos_ibfk_1` FOREIGN KEY (`usuario_id`)   REFERENCES `usuarios`       (`id`),
  CONSTRAINT `gastos_ibfk_2` FOREIGN KEY (`categoria_id`) REFERENCES `categorias`     (`id`),
  CONSTRAINT `gastos_ibfk_3` FOREIGN KEY (`cartao_id`)    REFERENCES `cartoes_credito`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ------------------------------------------------------------
-- PARCELAS
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `parcelas` (
  `id`             INT          NOT NULL AUTO_INCREMENT,
  `gasto_id`       INT          NOT NULL,
  `numero_parcela` INT          NOT NULL,
  `valor_parcela`  DECIMAL(10,2) NOT NULL,
  `data_vencimento` DATE        NOT NULL,
  `ativo`          VARCHAR(1)   NOT NULL DEFAULT 'S',
  `parcelas_total` INT          NOT NULL,
  PRIMARY KEY (`id`),
  KEY `gasto_id` (`gasto_id`),
  CONSTRAINT `parcelas_ibfk_1` FOREIGN KEY (`gasto_id`) REFERENCES `gastos`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ------------------------------------------------------------
-- GASTOS RECORRENTES (templates mensais)
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `gastos_recorrentes` (
  `id`             INT          NOT NULL AUTO_INCREMENT,
  `nome`           VARCHAR(255) NOT NULL,
  `categoria_id`   INT          NOT NULL,
  `cartao_id`      INT          NULL DEFAULT NULL,
  `responsavel_id` INT          NULL DEFAULT NULL,
  `usuario_id`     INT          NOT NULL,
  `valor`          DECIMAL(10,2) NOT NULL,
  `ativo`          ENUM('S','N') NOT NULL DEFAULT 'S',
  PRIMARY KEY (`id`),
  KEY `fk_grec_categoria` (`categoria_id`),
  KEY `fk_grec_cartao`    (`cartao_id`),
  KEY `fk_grec_usuario`   (`usuario_id`),
  CONSTRAINT `fk_grec_categoria` FOREIGN KEY (`categoria_id`) REFERENCES `categorias`     (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_grec_cartao`    FOREIGN KEY (`cartao_id`)    REFERENCES `cartoes_credito`(`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_grec_usuario`   FOREIGN KEY (`usuario_id`)   REFERENCES `usuarios`       (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ------------------------------------------------------------
-- LANÇAMENTOS DOS RECORRENTES (gerados automaticamente)
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `gastos_recorrentes_lancamentos` (
  `id`                  INT          NOT NULL AUTO_INCREMENT,
  `gasto_recorrente_id` INT          NULL DEFAULT NULL,
  `mes_referencia`      DATE         NULL DEFAULT NULL,
  `valor`               DECIMAL(10,2) NULL DEFAULT NULL,
  `nome`                VARCHAR(255) NULL DEFAULT NULL,
  `categoria_id`        INT          NULL DEFAULT NULL,
  `cartao_id`           INT          NULL DEFAULT NULL,
  `usuario_id`          INT          NULL DEFAULT NULL,
  `criado_em`           DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `gasto_recorrente_id` (`gasto_recorrente_id`),
  CONSTRAINT `gastos_recorrentes_lancamentos_ibfk_1`
    FOREIGN KEY (`gasto_recorrente_id`) REFERENCES `gastos_recorrentes`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ------------------------------------------------------------
-- RENDA MENSAL
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `renda_mensal` (
  `id`             INT          NOT NULL AUTO_INCREMENT,
  `valor`          DECIMAL(10,2) NOT NULL,
  `descricao`      VARCHAR(255) NOT NULL,
  `data_registro`  DATE         NOT NULL DEFAULT (CURDATE()),
  `usuario_id`     INT          NOT NULL,
  PRIMARY KEY (`id`),
  KEY `usuario_id` (`usuario_id`),
  CONSTRAINT `renda_mensal_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ------------------------------------------------------------
-- CONTAS DE RESPONSÁVEIS (dívidas por pessoa)
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `contas_pessoa` (
  `id`               INT          NOT NULL AUTO_INCREMENT,
  `usuario_id`       INT          NOT NULL DEFAULT 1,
  `responsavel_id`   INT          NOT NULL,
  `descricao`        VARCHAR(200) NOT NULL,
  `valor`            DECIMAL(10,2) NOT NULL,
  `categoria_id`     INT          NULL DEFAULT NULL,
  `metodo_pagamento` VARCHAR(20)  NOT NULL DEFAULT 'Dinheiro',
  `data`             DATE         NOT NULL,
  `pago`             TINYINT(1)   NOT NULL DEFAULT 0,
  `created_at`       TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  CONSTRAINT `contas_pessoa_ibfk_1`
    FOREIGN KEY (`responsavel_id`) REFERENCES `responsaveis`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ------------------------------------------------------------
-- CONTAS FIXAS MENSAIS (aluguel, internet, etc.)
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `contas_fixas` (
  `id`             INT          NOT NULL AUTO_INCREMENT,
  `usuario_id`     INT          NOT NULL DEFAULT 1,
  `nome`           VARCHAR(100) NOT NULL,
  `valor`          DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `dia_vencimento` TINYINT      NOT NULL DEFAULT 1,
  `cor`            VARCHAR(7)   NOT NULL DEFAULT '#3B82F6',
  `ativo`          TINYINT(1)   NOT NULL DEFAULT 1,
  `created_at`     TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ------------------------------------------------------------
-- PAGAMENTOS DAS CONTAS FIXAS
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `contas_fixas_pagamentos` (
  `id`             INT          NOT NULL AUTO_INCREMENT,
  `conta_fixa_id`  INT          NOT NULL,
  `usuario_id`     INT          NOT NULL DEFAULT 1,
  `mes`            TINYINT      NOT NULL,
  `ano`            SMALLINT     NOT NULL,
  `data_pagamento` DATE         NOT NULL,
  `valor_pago`     DECIMAL(10,2) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_conta_mes_ano` (`conta_fixa_id`, `mes`, `ano`),
  CONSTRAINT `cfp_ibfk_1`
    FOREIGN KEY (`conta_fixa_id`) REFERENCES `contas_fixas`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ------------------------------------------------------------
-- FATURAS PAGAS (cartão de crédito)
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `faturas_pagas` (
  `id`             INT      NOT NULL AUTO_INCREMENT,
  `usuario_id`     INT      NOT NULL DEFAULT 1,
  `cartao_id`      INT      NOT NULL,
  `mes`            TINYINT  NOT NULL,
  `ano`            SMALLINT NOT NULL,
  `data_pagamento` DATE     NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_cartao_mes_ano` (`cartao_id`, `mes`, `ano`),
  CONSTRAINT `faturas_pagas_ibfk_1`
    FOREIGN KEY (`cartao_id`) REFERENCES `cartoes_credito`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ------------------------------------------------------------
-- COFRINHOS (metas de poupança)
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `cofrinhos` (
  `id`             INT          NOT NULL AUTO_INCREMENT,
  `usuario_id`     INT          NOT NULL DEFAULT 1,
  `nome`           VARCHAR(100) NOT NULL,
  `descricao`      VARCHAR(255) NULL DEFAULT NULL,
  `imagem_url`     VARCHAR(255) NULL DEFAULT NULL,
  `meta_valor`     DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `valor_atual`    DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `data_limite`    DATE         NULL DEFAULT NULL,
  `tem_cdi`        TINYINT(1)   NOT NULL DEFAULT 0,
  `cdi_percentual` DECIMAL(5,2) NULL DEFAULT NULL,
  `cdi_taxa_anual` DECIMAL(5,2) NULL DEFAULT NULL,
  `cor`            VARCHAR(7)   NOT NULL DEFAULT '#3B82F6',
  `created_at`     TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ------------------------------------------------------------
-- APORTES DOS COFRINHOS
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `cofrinho_aportes` (
  `id`          INT          NOT NULL AUTO_INCREMENT,
  `cofrinho_id` INT          NOT NULL,
  `valor`       DECIMAL(10,2) NOT NULL,
  `data_aporte` DATE         NOT NULL,
  `observacao`  VARCHAR(255) NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `cofrinho_id` (`cofrinho_id`),
  CONSTRAINT `cofrinho_aportes_ibfk_1`
    FOREIGN KEY (`cofrinho_id`) REFERENCES `cofrinhos`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ------------------------------------------------------------
-- ORÇAMENTOS POR CATEGORIA
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `orcamentos` (
  `id`           INT          NOT NULL AUTO_INCREMENT,
  `categoria_id` INT          NOT NULL,
  `usuario_id`   INT          NOT NULL DEFAULT 1,
  `valor_limite` DECIMAL(10,2) NOT NULL,
  `meses`        VARCHAR(50)  NULL DEFAULT NULL,
  `anos`         VARCHAR(50)  NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `categoria_id` (`categoria_id`),
  CONSTRAINT `orcamentos_ibfk_1`
    FOREIGN KEY (`categoria_id`) REFERENCES `categorias`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ------------------------------------------------------------

SET FOREIGN_KEY_CHECKS = 1;

-- ============================================================
--  Pronto! Acesse o sistema e crie o primeiro usuário no login.
-- ============================================================
