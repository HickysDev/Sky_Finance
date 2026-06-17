<?php

include_once __DIR__ . '/../../conn/conn.php';

class CartaoModel {

    private $id;

    // Setters
    public function setId($id) {
        $this->id = $id;
    }

    // Getters
    public function getId() {
        return $this->id;
    }

    private function parseLimite(string $raw): float {
        $clean = str_replace(['R$', ' ', '.'], '', $raw);
        return (float) str_replace(',', '.', $clean);
    }

    private function parseCor(string $cor): string {
        return preg_match('/^#[0-9A-Fa-f]{6}$/', $cor) ? $cor : '#3B82F6';
    }

    public function adicionaCartao($cartao) {
        $conn = Database::getConnection();

        $sql = $conn->prepare("
            INSERT INTO cartoes_credito (usuario_id, nome_cartao, limite, fechamento_dia, vencimento_dia, cor)
            VALUES (:id, :nome, :limite, :fechamento, :vencimento, :cor)
        ");

        return $sql->execute([
            ':id'        => 1,
            ':nome'      => $cartao['nomeCartao'],
            ':limite'    => $this->parseLimite($cartao['limite'] ?? ''),
            ':fechamento'=> (int) $cartao['dataFechamento'],
            ':vencimento'=> (int) $cartao['dataVencimento'],
            ':cor'       => $this->parseCor($cartao['cor'] ?? '#3B82F6'),
        ]);
    }

    public function alterarCartao($cartao) {
        $conn = Database::getConnection();

        $sql = $conn->prepare("
            UPDATE cartoes_credito
            SET nome_cartao = :nome, limite = :limite, fechamento_dia = :fechamento, vencimento_dia = :vencimento, cor = :cor
            WHERE id = :cartaoId
        ");

        return $sql->execute([
            ':nome'      => $cartao['nomeCartao'],
            ':limite'    => $this->parseLimite($cartao['limite'] ?? ''),
            ':fechamento'=> (int) $cartao['dataFechamento'],
            ':vencimento'=> (int) $cartao['dataVencimento'],
            ':cor'       => $this->parseCor($cartao['cor'] ?? '#3B82F6'),
            ':cartaoId'  => $this->getId(),
        ]);
    }

    public function excluiCartao() {
        $conn = Database::getConnection();

        $sql = $conn->prepare("DELETE FROM cartoes_credito WHERE id = ?");

        $query = $sql->execute([$this->getId()]);

        return $query;
    }

    public function buscaCartaos() {
        $conn = Database::getConnection();

        $buscaCartaos = $conn->prepare("SELECT * FROM cartoes_credito");
        $buscaCartaos->execute();
        $resultados = $buscaCartaos->fetchAll(PDO::FETCH_ASSOC);

        $cartoes = [];
        foreach ($resultados as $cartao) {
            $cartoes[$cartao['id']] = $cartao;
        }

        return $cartoes;
    }

    public static function getFaturaPaga(int $cartaoId, int $mes, int $ano): ?string {
        $conn = Database::getConnection();
        $stmt = $conn->prepare("
            SELECT data_pagamento FROM faturas_pagas
            WHERE cartao_id = :cid AND mes = :mes AND ano = :ano AND usuario_id = 1
        ");
        $stmt->execute([':cid' => $cartaoId, ':mes' => $mes, ':ano' => $ano]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? $row['data_pagamento'] : null;
    }

    public static function marcarFaturaPaga(int $cartaoId, int $mes, int $ano, string $data): bool {
        $conn = Database::getConnection();
        $stmt = $conn->prepare("
            INSERT INTO faturas_pagas (usuario_id, cartao_id, mes, ano, data_pagamento)
            VALUES (1, :cid, :mes, :ano, :data)
            ON DUPLICATE KEY UPDATE data_pagamento = :data2
        ");
        return $stmt->execute([':cid' => $cartaoId, ':mes' => $mes, ':ano' => $ano, ':data' => $data, ':data2' => $data]);
    }

    public static function desmarcarFaturaPaga(int $cartaoId, int $mes, int $ano): bool {
        $conn = Database::getConnection();
        $stmt = $conn->prepare("
            DELETE FROM faturas_pagas
            WHERE cartao_id = :cid AND mes = :mes AND ano = :ano AND usuario_id = 1
        ");
        return $stmt->execute([':cid' => $cartaoId, ':mes' => $mes, ':ano' => $ano]);
    }
}
