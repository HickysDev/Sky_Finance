ALTER TABLE contas_pessoa
  ADD COLUMN categoria_id     INT NULL DEFAULT NULL      AFTER valor,
  ADD COLUMN metodo_pagamento VARCHAR(20) NOT NULL DEFAULT 'Dinheiro' AFTER categoria_id;
