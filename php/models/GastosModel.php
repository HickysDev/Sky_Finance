<?php

include_once("../../conn/conn.php");

class GastosModel {
    public static function buscarGastosPorMes($mes, $cartaoId, $tipo) {
        $conn = Database::getConnection();

        $condicao = "";

        if ($tipo == 'debito') {
            $condicao .= " MONTH(data_gasto) = ?  AND metodo_pagamento IN ('Dinheiro', 'Débito', 'Pix' )";
        } else {
            $condicao .= " MONTH(dataVencimento) = ?  AND metodo_pagamento = 'Crédito' AND parcelado = 'N'";
        }

        if ($cartaoId) {
            $condicao .= " AND cartao_id = '$cartaoId'";
        }

        $ano_atual = date("Y");

        $stmt = $conn->prepare(
            "SELECT 
        g.id, g.descricao, g.valor, c.nome, g.metodo_pagamento, g.data_gasto, g.parcelado, cc.nome_cartao
        FROM gastos g
        INNER JOIN categorias c ON c.id = g.categoria_id 
        LEFT JOIN cartoes_credito cc ON cc.id = g.cartao_id
        WHERE $condicao AND YEAR(data_gasto) = $ano_atual"
        );
        $stmt->execute([$mes]);
        $gastos = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // $stmt->debugDumpParams();

        $valorTotal = 0;

        foreach ($gastos as &$gasto) {
            $valorTotal += $gasto['valor'];
            $gasto['valor'] = number_format($gasto['valor'], 2, ',', '.');
        }

        $gastos['valortotal'] = number_format($valorTotal, 2, ',', '.');

        // // Debug da query preparada (opcional)
        // $stmt->debugDumpParams();

        return $gastos;
    }

    public static function adicionarGasto($desc, $valor, $categoria, $pagamento = NULL, $cartao = NULL, $data = NULL, $parcelado = NULL, $num_parcelas = NULL, $tipo, $recorrente = NULL) {
        $conn = Database::getConnection();

        if ($cartao == "") {
            $cartao = NULL;
        }

        if ($recorrente == "S" && $tipo == "recorrente") {
            $adicionar = $conn->prepare("
                INSERT INTO `gastos_recorrentes` 
                (`nome`, `categoria_id`, `cartao_id`, `usuario_id`, `valor`, `ativo`) 
                VALUES 
                (:desc, :categoria, :cartao, '1', :valor, 'S');
        ");

            $queryAdicionar = $adicionar->execute([
                ':categoria' => $categoria,
                ':desc' => $desc,
                ':valor' => $valor,
                ':cartao' => $cartao,
            ]);

            if ($queryAdicionar) {
                $novoId = $conn->lastInsertId(); // Pega o ID recém-inserido
                $mesReferencia = date('Y-m-01'); // Ex: '2025-06-01' para junho

                // 2. Inserir lançamento congelado
                $lancamento = $conn->prepare("
                    INSERT INTO gastos_recorrentes_lancamentos 
                    (gasto_recorrente_id, mes_referencia, valor, nome, categoria_id, cartao_id, usuario_id) 
                    VALUES 
                    (:gasto_id, :mes, :valor, :nome, :categoria, :cartao, '1')
                ");

                $lancamento->execute([
                    ':gasto_id' => $novoId,
                    ':mes' => $mesReferencia,
                    ':valor' => $valor,
                    ':nome' => $desc,
                    ':categoria' => $categoria,
                    ':cartao' => $cartao
                ]);
            }
        } else {
            $adicionar = $conn->prepare("
        INSERT INTO gastos (usuario_id, categoria_id, descricao, valor, data_gasto, metodo_pagamento, cartao_id, parcelado) 
            VALUES ( '1', :categoria, :desc, :valor, :data, :pagamento, :cartao, :parcelado);
        ");

            $queryAdicionar = $adicionar->execute([
                ':categoria' => $categoria,
                ':desc' => $desc,
                ':valor' => $valor,
                ':data' => $data,
                ':pagamento' => $pagamento,
                ':cartao' => $cartao,
                ':parcelado' => $parcelado
            ]);

            $gastoId = $conn->lastInsertId();
        }

        if ($tipo == 'credito' && $parcelado == "N") {

            $buscaCartao = $conn->prepare("SELECT * FROM cartoes_credito WHERE id = ?");
            $buscaCartao->execute([$cartao]);
            $cartoes = $buscaCartao->fetchAll(PDO::FETCH_ASSOC);


            // Extrai o dia da data original
            $dia = (int) date('d', strtotime($data));

            if ($dia >= 4) {
                // Se o dia estiver entre 4 e 10, coloca para o próximo mês
                $data = date('Y-m-d', strtotime('+1 month', strtotime(date('Y-m', strtotime($data)) . '-' . $cartoes[0]['fechamento_dia'])));
            } else {
                // Caso contrário, apenas mantém o fechamento no mês atual
                $data = date('Y-m-d', strtotime(date('Y-m', strtotime($data)) . '-' . $cartoes[0]['fechamento_dia']));
            }

            $update = $conn->prepare("UPDATE gastos SET dataVencimento = '$data' WHERE id = ?");

            $queryAdicionar = $update->execute([$gastoId]);
        }

        // // Debug da query preparada (opcional)
        // $adicionar->debugDumpParams();

        if ($tipo == 'credito' && $parcelado != "N") {

            $buscaCartao = $conn->prepare("SELECT * FROM cartoes_credito WHERE id = ?");
            $buscaCartao->execute([$cartao]);
            $cartaoDados = $buscaCartao->fetch(PDO::FETCH_ASSOC);

            // Dia do fechamento do cartão
            $fechamentoDia = (int) $cartaoDados['fechamento_dia'];

            // Dia da compra
            $diaCompra = (int) date('d', strtotime($data));

            // Base da data (data da compra)
            $anoMes = date('Y-m', strtotime($data));

            // Se a compra for no dia do fechamento ou depois → joga pro mês seguinte
            if ($diaCompra >= $fechamentoDia) {
                $data = date('Y-m-d', strtotime("+1 month", strtotime("{$anoMes}-{$fechamentoDia}")));
            } else {
                $data = date('Y-m-d', strtotime("{$anoMes}-{$fechamentoDia}"));
            }


            $valor_parcela = $valor / $num_parcelas;

            for ($parcela = 1; $parcela <= $num_parcelas; $parcela++) {

                $adicionarParcelado = $conn->prepare("
        INSERT INTO parcelas (gasto_id, numero_parcela, valor_parcela, data_vencimento, parcelas_total) 
            VALUES (:gastoId, :parcela, :valor_parcela, :data_vencimento, :num_parcelas);
        ");

                $queryAdicionarParcelado = $adicionarParcelado->execute([
                    ':gastoId' => $gastoId,
                    ':parcela' => $parcela,
                    ':valor_parcela' => $valor_parcela,
                    ':data_vencimento' => $data,
                    ':num_parcelas' => $num_parcelas
                ]);

                $data = date('Y-m-d', strtotime("+1 months", strtotime($data)));
            }
        }


        return $queryAdicionar ? 1 : 2;
    }

    public static function excluirGastos($ids, $tipo) {
        $conn = Database::getConnection();

        foreach ($ids as $id) {
            // Se for cartão de crédito, primeiro remove as parcelas
            if ($tipo == 'credito' && $id['parcelado'] == 'S') {
                $removerParcelas = $conn->prepare("DELETE FROM parcelas WHERE gasto_id = ?");
                $removerParcelas->execute([$id['id']]);

                // // Debug da query preparada (opcional)
                /* $removerParcelas->debugDumpParams(); */
            }

            // Depois remove o gasto
            $removerGasto = $conn->prepare("DELETE FROM gastos WHERE id = ?");
            $queryRemover = $removerGasto->execute([$id['id']]);
        }

        return $queryRemover ? 1 : 2;
    }

    public static function buscarFatura($mes, $cartaoId) {
        $conn = Database::getConnection();
        $ano_atual = date("Y");

        $condicao = "";
        if ($cartaoId) {
            $condicao = " AND g.cartao_id = $cartaoId ";
        }

        $stmt = $conn->prepare(
            "( SELECT
                        g.cartao_id,
                        g.descricao,
                        g.valor,
                        c.nome AS categoria,
                        p.numero_parcela,
                        p.parcelas_total,
                        p.valor_parcela,
                        g.data_gasto,
                        g.parcelado,
                        g.id,
                        cc.nome_cartao,
                        'NORMAL' as tipo
                    FROM gastos g
                    LEFT JOIN parcelas p ON p.gasto_id = g.id
                    INNER JOIN categorias c ON c.id = g.categoria_id
                    INNER JOIN cartoes_credito cc ON cc.id = g.cartao_id
                    WHERE g.metodo_pagamento = 'Crédito'
                    $condicao
                    AND (
                        (g.parcelado = 'S' AND MONTH(p.data_vencimento) = :mes AND YEAR(p.data_vencimento) = $ano_atual)
                        OR
                        (g.parcelado = 'N' AND MONTH(g.dataVencimento) = :mes AND YEAR(g.dataVencimento) = $ano_atual)
                    )
                )
                UNION
                (
                    SELECT
                        g.cartao_id,
                        g.nome AS descricao,
                        g.valor,
                        cat.nome AS categoria,
                        NULL AS numero_parcela,
                        NULL AS parcelas_total,
                        NULL AS valor_parcela,
                        NULL AS data_gasto,
                        'N' AS parcelado,
                        g.id,
                        cc.nome_cartao,
                        'RECORRENTE' as tipo
                    FROM gastos_recorrentes g
                    INNER JOIN categorias cat ON cat.id = g.categoria_id
                    LEFT JOIN cartoes_credito cc ON cc.id = g.cartao_id
                    WHERE g.ativo = 'S'
                    $condicao
                )
                ORDER BY nome_cartao, data_gasto IS NULL, data_gasto");

        $stmt->execute([":mes" => $mes]);

        // $stmt->debugDumpParams();

        // Agrupar pelo cartao_id
        $faturas = $stmt->fetchAll(PDO::FETCH_GROUP | PDO::FETCH_ASSOC);

        // Formatar valores e calcular totais
        foreach ($faturas as $cartaoId => &$gastos) {
            $total = 0;
            foreach ($gastos as &$gasto) {
                $valor = $gasto['valor_parcela'] ? $gasto['valor_parcela'] : $gasto['valor'];
                $total += $valor;
                $gasto['valor_parcela'] = number_format($valor, 2, ',', '.');
            }
            $gastos['valortotal'] = number_format($total, 2, ',', '.');
        }

        return $faturas;
    }


    public static function buscarCredito($mes, $cartaoId) {
        $conn = Database::getConnection();

        $ano_atual = date("Y");

        $condicao = "";

        if ($cartaoId) {
            $condicao .= " AND cartao_id = '$cartaoId'";
        }

        $stmt = $conn->prepare(
            "SELECT
                g.descricao,
                g.valor,
                c.nome AS categoria,
                p.numero_parcela,
                p.parcelas_total,
                p.valor_parcela,
                g.data_gasto,
                g.parcelado,
                g.id,
                cc.nome_cartao
            FROM gastos g
            INNER JOIN parcelas p ON p.gasto_id = g.id
            INNER JOIN categorias c ON c.id = g.categoria_id
            LEFT JOIN cartoes_credito cc ON cc.id = g.cartao_id
            WHERE MONTH(p.data_vencimento) = ? $condicao AND YEAR(p.data_vencimento) = $ano_atual"
        );
        $stmt->execute([$mes]);
        $gastosRecorrente = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $valorTotalGasto = 0;

        foreach ($gastosRecorrente as &$gasto) {
            $valorTotalGasto += $gasto['valor_parcela'];
            $gasto['valor'] = number_format($gasto['valor'], 2, ',', '.');
            $gasto['valor_parcela'] = number_format($gasto['valor_parcela'], 2, ',', '.');
        }

        $gastosRecorrente['valortotal'] = number_format($valorTotalGasto, 2, ',', '.');

        return $gastosRecorrente;
    }

    public static function buscarRecorrentes($cartaoId) {
        $conn = Database::getConnection();

        $condicao = "";

        if ($cartaoId) {
            $condicao .= " AND cartao_id = '$cartaoId'";
        }

        $stmt = $conn->prepare(
            "SELECT
                    gr.nome,
                    gr.valor,
                    c.nome AS categoria,
                    gr.id,
                    cc.nome_cartao,
                    cc.id as id_cartao,
                    c.id as id_categoria,
                    gr.ativo
                FROM gastos_recorrentes gr
                INNER JOIN categorias c ON c.id = gr.categoria_id
                LEFT JOIN cartoes_credito cc ON cc.id = gr.cartao_id
                WHERE gr.id IN (
                    SELECT MAX(id)
                    FROM gastos_recorrentes
                    GROUP BY id
                )
                $condicao"
        );
        $stmt->execute();
        $gastosRecorrente = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($gastosRecorrente as $gasto) {
            $id = $gasto['id'];
            $gasto['valor'] = number_format($gasto['valor'], 2, ',', '.');
            $retorno[$id] = $gasto;
        }

                // $stmt->debugDumpParams();

        return $retorno;
    }

    public static function inativaRecorrentes($id) {
        $conn = Database::getConnection();

        $sql = $conn->prepare("UPDATE gastos_recorrentes SET ativo= 'N' WHERE id = :id");

        $query = $sql->execute([
            ':id' => $id
        ]);

        return $query;
    }

    public static function ativaRecorrentes($id) {
        $conn = Database::getConnection();

        $sql = $conn->prepare("UPDATE gastos_recorrentes SET ativo= 'S' WHERE id = :id");

        $query = $sql->execute([
            ':id' => $id
        ]);

        return $query;
    }

    public static function editaRecorrentes($id, $nome, $valor, $categoria, $cartao) {
        $conn = Database::getConnection();

        $stmt = $conn->prepare(
            "SELECT gr.cartao_id, gr.valor
                        FROM gastos_recorrentes gr
                        WHERE gr.id IN (
                            SELECT MAX(id)
                            FROM gastos_recorrentes
                            GROUP BY nome
                        ) AND gr.id = :id"
        );

        $stmt->execute([':id' => $id]);
        $cartaoId = $stmt->fetch(PDO::FETCH_ASSOC);

        // $stmt->debugDumpParams();

        if ($cartaoId["cartao_id"] != $cartao || floatval($cartaoId["valor"]) != $valor) {
            $sql = $conn->prepare("UPDATE gastos_recorrentes SET ativo = 'N' WHERE id = :id");
            $sucessoInativar = $sql->execute([':id' => $id]);

            // Só insere o novo se o update deu certo
            if ($sucessoInativar) {
                $adicionar = $conn->prepare("
                    INSERT INTO gastos_recorrentes 
                    (nome, categoria_id, cartao_id, usuario_id, valor, ativo) 
                    VALUES 
                    (:desc, :categoria, :cartao, '1', :valor, 'S')
                ");

                $queryAdicionar = $adicionar->execute([
                    ':categoria' => $categoria,
                    ':desc' => $nome,
                    ':valor' => $valor,
                    ':cartao' => $cartao,
                ]);

                if ($queryAdicionar) {
                    $novoId = $conn->lastInsertId();

                    $mesReferencia = date('Y-m-01');

                    $lancamento = $conn->prepare("
                        INSERT INTO gastos_recorrentes_lancamentos 
                        (gasto_recorrente_id, mes_referencia, valor, nome, categoria_id, cartao_id, usuario_id) 
                        VALUES 
                        (:gasto_id, :mes, :valor, :nome, :categoria, :cartao, '1')
                    ");

                    $lancamento->execute([
                        ':gasto_id' => $novoId,
                        ':mes' => $mesReferencia,
                        ':valor' => $valor,
                        ':nome' => $nome,
                        ':categoria' => $categoria,
                        ':cartao' => $cartao
                    ]);
                }

                return $lancamento;
            } else {
                return false;
            }
        } else {
            $sql = $conn->prepare("UPDATE gastos_recorrentes SET categoria = :categoria, nome = :desc, ativo= 'S' WHERE id = :id");

            $query = $sql->execute([
                ':categoria' => $categoria,
                ':desc' => $nome,
                ':id' => $id
            ]);
        }

        return $query;
    }
}
