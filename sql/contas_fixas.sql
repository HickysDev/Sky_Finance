-- Contas fixas mensais (Claro, água, internet, etc.)
CREATE TABLE IF NOT EXISTS contas_fixas (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id      INT NOT NULL DEFAULT 1,
    nome            VARCHAR(100) NOT NULL,
    valor           DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    dia_vencimento  TINYINT NOT NULL DEFAULT 1,
    cor             VARCHAR(7) NOT NULL DEFAULT '#3B82F6',
    ativo           TINYINT(1) NOT NULL DEFAULT 1,
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Registro de pagamentos mensais por conta fixa
CREATE TABLE IF NOT EXISTS contas_fixas_pagamentos (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    conta_fixa_id   INT NOT NULL,
    usuario_id      INT NOT NULL DEFAULT 1,
    mes             TINYINT NOT NULL,
    ano             SMALLINT NOT NULL,
    data_pagamento  DATE NOT NULL,
    valor_pago      DECIMAL(10,2) NOT NULL,
    UNIQUE KEY uk_conta_mes_ano (conta_fixa_id, mes, ano),
    FOREIGN KEY (conta_fixa_id) REFERENCES contas_fixas(id) ON DELETE CASCADE
);
