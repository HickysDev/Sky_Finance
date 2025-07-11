<?php

include_once("../../conn/conn.php");

class CategoriaModel {
    private $descricao;
    private $id;

    // Setters
    public function setDescricao($descricao) {
        $this->descricao = $descricao;
    }

    public function setId($id) {
        $this->id = $id;
    }

    // Getters
    public function getDescricao() {
        return $this->descricao;
    }

    public function getId() {
        return $this->id;
    }

    public function adicionaCategoria() {
        $conn = Database::getConnection();

        $nomeCategoria = $this->getDescricao();

        $sql = $conn->prepare("INSERT INTO categorias (nome) VALUES (:nome)");

        $query = $sql->execute([
            ':nome' => $nomeCategoria
        ]);

        return $query;
    }

    public function editaCategoria() {


        $conn = Database::getConnection();

        $nomeCategoria = $this->getDescricao();
        $id = $this->getId();

        $sql = $conn->prepare("UPDATE categorias SET nome= :nome WHERE id = :id");

        $query = $sql->execute([
            ':nome' => $nomeCategoria,
            ':id' => $id
        ]);

        return $query;
    }

    public function excluiCategoria() {

        $conn = Database::getConnection();

        $id = $this->getId();

        $sql = $conn->prepare("UPDATE categorias SET ativo= 'N' WHERE id = :id");

        $query = $sql->execute([
            ':id' => $id
        ]);

        return $query;
    }

    public function buscaCategorias() {
        $conn = Database::getConnection();

        $buscaCategorias = $conn->prepare("SELECT * FROM categorias WHERE ativo = 'S'");
        $buscaCategorias->execute();
        $categorias = $buscaCategorias->fetchAll(PDO::FETCH_ASSOC);

        return $categorias; 
    }
}
