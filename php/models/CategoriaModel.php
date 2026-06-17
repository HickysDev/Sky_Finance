<?php

include_once __DIR__ . '/../../conn/conn.php';

class CategoriaModel {
    private $descricao;
    private $id;
    private $cor;
    private $icone;

    public function setDescricao($descricao) { $this->descricao = $descricao; }
    public function setId($id)               { $this->id        = $id; }
    public function setCor($cor)             { $this->cor       = $cor; }
    public function setIcone($icone)         { $this->icone     = $icone; }

    public function adicionaCategoria() {
        $conn = Database::getConnection();
        $sql  = $conn->prepare("INSERT INTO categorias (nome, cor, icone) VALUES (:nome, :cor, :icone)");
        return $sql->execute([
            ':nome'  => $this->descricao,
            ':cor'   => $this->cor   ?? '#6B7280',
            ':icone' => $this->icone ?: null,
        ]);
    }

    public function editaCategoria() {
        $conn = Database::getConnection();
        $sql  = $conn->prepare("UPDATE categorias SET nome = :nome, cor = :cor, icone = :icone WHERE id = :id");
        return $sql->execute([
            ':nome'  => $this->descricao,
            ':cor'   => $this->cor   ?? '#6B7280',
            ':icone' => $this->icone ?: null,
            ':id'    => $this->id,
        ]);
    }

    public function excluiCategoria() {
        $conn = Database::getConnection();
        $sql  = $conn->prepare("UPDATE categorias SET ativo = 'N' WHERE id = :id");
        return $sql->execute([':id' => $this->id]);
    }

    public function buscaCategorias() {
        $conn = Database::getConnection();
        $stmt = $conn->prepare("SELECT id, nome, cor, icone FROM categorias WHERE ativo = 'S' ORDER BY nome");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
