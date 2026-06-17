-- Tabela de responsáveis
CREATE TABLE IF NOT EXISTS responsaveis (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT         NOT NULL DEFAULT 1,
    nome       VARCHAR(100) NOT NULL,
    cor        VARCHAR(7)   NOT NULL DEFAULT '#3B82F6',
    created_at TIMESTAMP   DEFAULT CURRENT_TIMESTAMP
);

-- Coluna na tabela de despesas avulsas (crédito / débito)
ALTER TABLE gastos
    ADD COLUMN responsavel_id INT NULL DEFAULT NULL AFTER cartao_id;

-- Coluna nos recorrentes (template)
ALTER TABLE gastos_recorrentes
    ADD COLUMN responsavel_id INT NULL DEFAULT NULL AFTER cartao_id;

-- Itens manuais de dívida por responsável
CREATE TABLE IF NOT EXISTS contas_pessoa (
    id             INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id     INT          NOT NULL DEFAULT 1,
    responsavel_id INT          NOT NULL,
    descricao      VARCHAR(200) NOT NULL,
    valor          DECIMAL(10,2) NOT NULL,
    data           DATE         NOT NULL,
    pago           TINYINT(1)   NOT NULL DEFAULT 0,
    created_at     TIMESTAMP    DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (responsavel_id) REFERENCES responsaveis(id) ON DELETE CASCADE
);
