-- Registro de faturas de cartão de crédito marcadas como pagas
CREATE TABLE IF NOT EXISTS faturas_pagas (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id      INT          NOT NULL DEFAULT 1,
    cartao_id       INT          NOT NULL,
    mes             TINYINT      NOT NULL,
    ano             SMALLINT     NOT NULL,
    data_pagamento  DATE         NOT NULL,
    UNIQUE KEY uk_cartao_mes_ano (cartao_id, mes, ano),
    FOREIGN KEY (cartao_id) REFERENCES cartoes_credito(id) ON DELETE CASCADE
);
