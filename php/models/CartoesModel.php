<?php

include_once("../../conn/conn.php");

class CartaoModel {
    private $descricao;

    // Setters
    public function setDescricao($descricao) {
        $this->descricao = $descricao;
    }

    // Getters
    public function getDescricao() {
        return $this->descricao;
    }

    public function adicionaCartao() {
        $conn = Database::getConnection();

        $nomeCartao = $this->getDescricao();

        $sql = $conn->prepare("INSERT INTO categorias (nome) VALUES (:nome)");

        $query = $sql->execute([
            ':nome' => $nomeCartao
        ]);

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
