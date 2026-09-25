-- ============================================================
--  Contas fixas: data de inativação (histórico por mês)
--  Rode UMA vez em bancos criados antes desta coluna (ex.: a instalação
--  de casa). É seguro rodar de novo: o ADD COLUMN usa IF NOT EXISTS.
--
--  Regra: uma conta fixa conta no mês M se (foi criada até M e estava ativa
--  em M) ou se tem pagamento registrado em M. Antes, contava em TODOS os
--  meses enquanto ativa e sumia de todo o histórico ao ser inativada.
-- ============================================================

USE `projeto`;

ALTER TABLE `contas_fixas`
  ADD COLUMN IF NOT EXISTS `inativado_em` DATE NULL DEFAULT NULL AFTER `ativo`;

-- Contas já inativas, sem data: inativa a partir do mês seguinte ao último
-- pagamento (ou do mês de criação, se nunca foi paga).
UPDATE `contas_fixas` cf
LEFT JOIN (
    SELECT conta_fixa_id, MAX(STR_TO_DATE(CONCAT(ano, '-', mes, '-01'), '%Y-%c-%d')) AS ultimo
    FROM contas_fixas_pagamentos
    GROUP BY conta_fixa_id
) p ON p.conta_fixa_id = cf.id
SET cf.inativado_em = COALESCE(DATE_ADD(p.ultimo, INTERVAL 1 MONTH), DATE_FORMAT(cf.created_at, '%Y-%m-01'))
WHERE cf.ativo = 'N' AND cf.inativado_em IS NULL;

-- ------------------------------------------------------------
-- Renda: mesma ideia. Pausar uma renda recorrente vale a partir do mês da
-- pausa; os meses anteriores continuam somando. (Rendas já pausadas antes
-- desta coluna ficam sem data e continuam fora de todos os meses.)
-- ------------------------------------------------------------
ALTER TABLE `renda_mensal`
  ADD COLUMN IF NOT EXISTS `inativado_em` DATE NULL DEFAULT NULL AFTER `ativo`;

-- ------------------------------------------------------------
-- Pessoas → "Ela me deve": data em que a pessoa pagou de volta.
-- Só para Pix/débito/dinheiro e recorrentes sem cartão; no crédito o
-- status vem da fatura paga (faturas_pagas).
-- ------------------------------------------------------------
ALTER TABLE `gastos`
  ADD COLUMN IF NOT EXISTS `recebido_em` DATE NULL DEFAULT NULL AFTER `responsavel_id`;
ALTER TABLE `gastos_recorrentes_lancamentos`
  ADD COLUMN IF NOT EXISTS `recebido_em` DATE NULL DEFAULT NULL AFTER `usuario_id`;

-- ------------------------------------------------------------
-- "Excluir" pessoa, cofrinho e conta fixa passa a ARQUIVAR: some das listas,
-- mas o histórico (dívidas, aportes, pagamentos) continua nos meses passados.
-- ------------------------------------------------------------
ALTER TABLE `responsaveis` ADD COLUMN IF NOT EXISTS `arquivado_em` DATE NULL DEFAULT NULL;
ALTER TABLE `cofrinhos`    ADD COLUMN IF NOT EXISTS `arquivado_em` DATE NULL DEFAULT NULL;
ALTER TABLE `contas_fixas` ADD COLUMN IF NOT EXISTS `arquivado_em` DATE NULL DEFAULT NULL AFTER `inativado_em`;

-- ------------------------------------------------------------
-- Contas fixas: "não vou pagar este mês". Registro com valor_pago = 0 e
-- pulado = 'S' — os totais usam o valor pago, então o mês fica zerado.
-- ------------------------------------------------------------
ALTER TABLE `contas_fixas_pagamentos`
  ADD COLUMN IF NOT EXISTS `pulado` CHAR(1) NOT NULL DEFAULT 'N' AFTER `valor_pago`;

-- ------------------------------------------------------------
-- Contas fixas pagas por outra pessoa (ex.: dou o dinheiro para a mãe pagar).
--   contas_fixas.responsavel_id           → quem paga por padrão (NULL = eu)
--   contas_fixas_pagamentos.responsavel_id → para quem foi o dinheiro NAQUELE
--     mês (gravado no pagamento). Mudar o padrão não reescreve meses pagos.
-- ------------------------------------------------------------
ALTER TABLE `contas_fixas`
  ADD COLUMN IF NOT EXISTS `responsavel_id` INT NULL DEFAULT NULL AFTER `cor`;
ALTER TABLE `contas_fixas_pagamentos`
  ADD COLUMN IF NOT EXISTS `responsavel_id` INT NULL DEFAULT NULL AFTER `pulado`;
