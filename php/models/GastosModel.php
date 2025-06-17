<?php

include_once("../../conn/conn.php");

class GastosModel
{
    public static function buscarGastosPorMes($mes, $cartaoId, $tipo)
    {
        $conn = Database::getConnection();

        $condicao = "";

        if ($tipo == 'debito') {
            $condicao .= " AND metodo_pagamento IN ('Dinheiro', 'Débito', 'Pix' )";
        } else {
            $condicao .= " AND metodo_pagamento = 'Crédito' AND parcelado = 'N'";
        }

        if ($cartaoId) {
            $condicao .= " AND cartao_id = '$cartaoId'";
        }

        $ano_atual = date("Y");

        $stmt = $conn->prepare(
            "SELECT 
        g.id, g.descricao, g.valor, c.nome, g.metodo_pagamento, g.data_gasto, g.parcelado
        FROM gastos g
        INNER JOIN categorias c ON c.id = g.categoria_id 
        WHERE MONTH(data_gasto) = ? $condicao AND YEAR(data_gasto) = $ano_atual"
        );
        $stmt->execute([$mes]);
        $gastos = $stmt->fetchAll(PDO::FETCH_ASSOC);

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

    public static function adicionarGasto($desc, $valor, $categoria, $pagamento, $cartao = NULL, $data, $parcelado, $num_parcelas, $tipo)
    {
        $conn = Database::getConnection();

        if ($cartao == "") {
            $cartao = NULL;
        }

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

        if ($tipo == 'credito' && $parcelado == "N") {

            $buscaCartao = $conn->prepare("SELECT * FROM cartoes_credito WHERE id = ?");
            $buscaCartao->execute([$cartao]);
            $cartoes = $buscaCartao->fetchAll(PDO::FETCH_ASSOC);


            // Extrai o dia da data original
            $dia = (int)date('d', strtotime($data));

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
            $cartoes = $buscaCartao->fetchAll(PDO::FETCH_ASSOC);

            // Extrai o dia da data original
            $dia = (int)date('d', strtotime($data));

            if ($dia >= 4) {
                // Se o dia estiver entre 4 e 10, coloca para o próximo mês
                $data = date('Y-m-d', strtotime('+1 month', strtotime(date('Y-m', strtotime($data)) . '-' . $cartoes[0]['fechamento_dia'])));
            } else {
                // Caso contrário, apenas mantém o fechamento no mês atual
                $data = date('Y-m-d', strtotime(date('Y-m', strtotime($data)) . '-' . $cartoes[0]['fechamento_dia']));
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

    public static function excluirGastos($ids, $tipo)
    {
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

    public static function buscarFatura($mes, $cartaoId)
    {
        $conn = Database::getConnection();

        $ano_atual = date("Y");

        $condicao = "";

        if($cartaoId){
            $condicao = " AND g.cartao_id = $cartaoId ";
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
                g.id
            FROM gastos g
            LEFT JOIN parcelas p ON p.gasto_id = g.id
            INNER JOIN categorias c ON c.id = g.categoria_id
            WHERE g.metodo_pagamento = 'Crédito'
            $condicao
            AND (
                (g.parcelado = 'S' AND MONTH(p.data_vencimento) = :mes AND YEAR(p.data_vencimento) = $ano_atual)
                OR
                (g.parcelado = 'N' AND MONTH(g.dataVencimento) = :mes AND YEAR(g.dataVencimento) = $ano_atual)
            )
            ORDER BY data_vencimento;"
        );
        $stmt->execute([":mes" => $mes]);
        /* $stmt->debugDumpParams(); */

        $fatura = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $valorTotalGasto = 0;

        foreach ($fatura as &$gasto) {
            $valorTotalGasto += $gasto['valor_parcela'] ? $gasto['valor_parcela'] : $gasto['valor'];
            $gasto['valor_parcela'] = $gasto['valor_parcela'] ? number_format($gasto['valor_parcela'], 2, ',', '.') : number_format($gasto['valor'], 2, ',', '.');
        }

        $fatura['valortotal'] = number_format($valorTotalGasto, 2, ',', '.');

        return $fatura;
    }

    public static function buscarRecorrentes($mes)
    {
        $conn = Database::getConnection();

        $ano_atual = date("Y");

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
                g.id
            FROM gastos g
            INNER JOIN parcelas p ON p.gasto_id = g.id
            INNER JOIN categorias c ON c.id = g.categoria_id
            WHERE MONTH(p.data_vencimento) = ? AND YEAR(p.data_vencimento) = $ano_atual"
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
}
