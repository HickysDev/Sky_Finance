<?php
require_once __DIR__ . '/../../conn/conn.php';

class CofrinhoModel {

    private static function parseValor(string $raw): float {
        $clean = str_replace(['R$', ' ', '.'], '', $raw);
        return (float) str_replace(',', '.', $clean);
    }

    public static function listar(): array {
        $conn = Database::getConnection();
        $stmt = $conn->prepare("
            SELECT * FROM cofrinhos
            WHERE usuario_id = 1
            ORDER BY created_at DESC
        ");
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public static function salvar(array $data, int $id = 0): bool {
        $conn    = Database::getConnection();
        $meta    = self::parseValor($data['meta_valor'] ?? '0');
        $temCdi  = !empty($data['tem_cdi']) ? 1 : 0;
        $pctCdi  = $temCdi ? (float) ($data['cdi_percentual'] ?? 100) : null;
        $taxaCdi = $temCdi ? (float) ($data['cdi_taxa_anual'] ?? 10.5) : null;
        $dataLim = !empty($data['data_limite']) ? $data['data_limite'] : null;
        $img     = trim($data['imagem_url'] ?? '') ?: null;
        $cor     = $data['cor'] ?? '#3B82F6';
        $nome    = trim($data['nome'] ?? '');
        $desc    = trim($data['descricao'] ?? '') ?: null;

        if ($id > 0) {
            $stmt = $conn->prepare("
                UPDATE cofrinhos SET
                    nome = :nome, descricao = :desc, imagem_url = :img,
                    meta_valor = :meta, data_limite = :dl,
                    tem_cdi = :cdi, cdi_percentual = :pct, cdi_taxa_anual = :taxa, cor = :cor
                WHERE id = :id AND usuario_id = 1
            ");
            return $stmt->execute([
                ':nome' => $nome, ':desc' => $desc, ':img' => $img,
                ':meta' => $meta, ':dl'   => $dataLim,
                ':cdi'  => $temCdi, ':pct' => $pctCdi, ':taxa' => $taxaCdi,
                ':cor'  => $cor, ':id' => $id,
            ]);
        }

        $stmt = $conn->prepare("
            INSERT INTO cofrinhos (usuario_id, nome, descricao, imagem_url, meta_valor, data_limite, tem_cdi, cdi_percentual, cdi_taxa_anual, cor)
            VALUES (1, :nome, :desc, :img, :meta, :dl, :cdi, :pct, :taxa, :cor)
        ");
        return $stmt->execute([
            ':nome' => $nome, ':desc' => $desc, ':img' => $img,
            ':meta' => $meta, ':dl'   => $dataLim,
            ':cdi'  => $temCdi, ':pct' => $pctCdi, ':taxa' => $taxaCdi, ':cor' => $cor,
        ]);
    }

    public static function remover(int $id): bool {
        $conn = Database::getConnection();
        $stmt = $conn->prepare("DELETE FROM cofrinhos WHERE id = :id AND usuario_id = 1");
        return $stmt->execute([':id' => $id]);
    }

    public static function aporte(int $cofrinhoId, float $valor, string $data, string $obs = ''): bool {
        $conn = Database::getConnection();
        $conn->beginTransaction();
        try {
            $stmt = $conn->prepare("
                INSERT INTO cofrinho_aportes (cofrinho_id, valor, data_aporte, observacao)
                VALUES (:cid, :val, :dt, :obs)
            ");
            $stmt->execute([':cid' => $cofrinhoId, ':val' => $valor, ':dt' => $data, ':obs' => $obs ?: null]);

            $stmt2 = $conn->prepare("
                UPDATE cofrinhos SET valor_atual = valor_atual + :val WHERE id = :id AND usuario_id = 1
            ");
            $stmt2->execute([':val' => $valor, ':id' => $cofrinhoId]);

            $conn->commit();
            return true;
        } catch (Exception $e) {
            $conn->rollBack();
            return false;
        }
    }

    public static function resumoDashboard(int $mes, int $ano): array {
        $conn = Database::getConnection();
        $stmt = $conn->prepare("
            SELECT
                COUNT(*)                           AS total_cofrinhos,
                COALESCE(SUM(valor_atual), 0)      AS total_guardado,
                COALESCE(SUM(meta_valor),  0)      AS total_meta,
                COALESCE((
                    SELECT SUM(ca.valor)
                    FROM cofrinho_aportes ca
                    INNER JOIN cofrinhos cc ON cc.id = ca.cofrinho_id
                    WHERE cc.usuario_id = 1
                      AND MONTH(ca.data_aporte) = :mes
                      AND YEAR(ca.data_aporte)  = :ano
                ), 0) AS aportes_mes
            FROM cofrinhos WHERE usuario_id = 1
        ");
        $stmt->execute([':mes' => $mes, ':ano' => $ano]);
        $totais = $stmt->fetch(PDO::FETCH_ASSOC);

        $stmt2 = $conn->prepare("
            SELECT id, nome, cor, valor_atual, meta_valor, data_limite
            FROM cofrinhos WHERE usuario_id = 1 ORDER BY created_at DESC LIMIT 10
        ");
        $stmt2->execute();
        $totais['lista'] = $stmt2->fetchAll(PDO::FETCH_ASSOC);
        return $totais;
    }

    public static function totalAportesMes(int $mes, int $ano): float {
        $conn = Database::getConnection();
        $stmt = $conn->prepare("
            SELECT COALESCE(SUM(ca.valor), 0)
            FROM cofrinho_aportes ca
            JOIN cofrinhos c ON c.id = ca.cofrinho_id
            WHERE c.usuario_id = 1
              AND MONTH(ca.data_aporte) = :mes
              AND YEAR(ca.data_aporte)  = :ano
        ");
        $stmt->execute([':mes' => $mes, ':ano' => $ano]);
        return (float) $stmt->fetchColumn();
    }

    public static function retirar(int $cofrinhoId, float $valor, string $data, string $obs = ''): array {
        $conn = Database::getConnection();
        $stmt = $conn->prepare("SELECT valor_atual FROM cofrinhos WHERE id = :id AND usuario_id = 1");
        $stmt->execute([':id' => $cofrinhoId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) return ['ok' => false, 'erro' => 'Cofrinho não encontrado'];
        if ($valor > (float) $row['valor_atual']) return ['ok' => false, 'erro' => 'Saldo insuficiente'];

        $conn->beginTransaction();
        try {
            $stmt = $conn->prepare("
                INSERT INTO cofrinho_aportes (cofrinho_id, valor, data_aporte, observacao)
                VALUES (:cid, :val, :dt, :obs)
            ");
            $stmt->execute([':cid' => $cofrinhoId, ':val' => -$valor, ':dt' => $data, ':obs' => $obs ?: null]);

            $stmt2 = $conn->prepare("
                UPDATE cofrinhos SET valor_atual = valor_atual - :val WHERE id = :id AND usuario_id = 1
            ");
            $stmt2->execute([':val' => $valor, ':id' => $cofrinhoId]);

            $conn->commit();
            return ['ok' => true];
        } catch (Exception $e) {
            $conn->rollBack();
            return ['ok' => false, 'erro' => 'Erro interno'];
        }
    }

    public static function buscarAportes(int $id): array {
        $conn = Database::getConnection();
        $stmt = $conn->prepare("
            SELECT * FROM cofrinho_aportes WHERE cofrinho_id = :id ORDER BY data_aporte DESC LIMIT 20
        ");
        $stmt->execute([':id' => $id]);
        return $stmt->fetchAll();
    }
}
