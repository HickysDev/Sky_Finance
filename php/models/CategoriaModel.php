<?php

include_once("../../conn/conn.php");

class CategoriaModel
{
    private $descricao;

    // Setters
    public function setDescricao($descricao)
    {
        $this->descricao = $descricao;
    }

    // Getters
    public function getDescricao()
    {
        return $this->descricao;
    }

    public function adicionaCategoria()
    {
        $conn = Database::getConnection();

        $nomeCategoria = $this->getDescricao();

        $sql = $conn->prepare("INSERT INTO categorias (nome) VALUES (:nome)");

        $query = $sql->execute([
            ':nome' => $nomeCategoria
        ]);

        return $query;
    }

    public function buscaCategorias()
    {
        $conn = Database::getConnection();

        $buscaCategorias = $conn->prepare("SELECT * FROM categorias");
        $buscaCategorias->execute();
        $categorias = $buscaCategorias->fetchAll(PDO::FETCH_ASSOC);

        return $categorias;
    }
}
