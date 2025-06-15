<?php

include_once("../../conn/conn.php");

class CartaoModel {

    private $id;

    // Setters
    public function setId($id)
    {
        $this->id = $id;
    }

    // Getters
    public function getId()
    {
        return $this->id;
    }

    public function adicionaCartao($cartao) {
        $conn = Database::getConnection();

        //TRATANDO O VALOR
        $limite = str_replace(['R$', ' ', '.'], '', $cartao['limite']); // Remove "R$" e espaÃ§os
        $limite = str_replace(',', '.', $limite);   // Substitui a vÃ­rgula por ponto

        //Converte para float
        $limite = (float)$limite;

        $sql = $conn->prepare("INSERT INTO cartoes_credito (usuario_id, nome_cartao, limite, fechamento_dia, vencimento_dia) VALUES (:id, :nome, :limite, :fechamento, :vencimento)");

        $query = $sql->execute([
            ':id' => 1,
            ':nome' => $cartao['nomeCartao'],
            ':limite' => $limite,
            ':fechamento' => $cartao['dataFechamento'],
            ':vencimento' => $cartao['dataVencimento']
        ]);

        return $query;
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
        $cartoes = $buscaCartaos->fetchAll(PDO::FETCH_ASSOC);

        return $cartoes;
    }
}
