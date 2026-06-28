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
        $sql  = $conn->prepare("INSERT INTO categorias (usuario_id, nome, cor, icone) VALUES (@uid, :nome, :cor, :icone)");
        return $sql->execute([
            ':nome'  => $this->descricao,
            ':cor'   => $this->cor   ?? '#6B7280',
            ':icone' => $this->icone ?: null,
        ]);
    }

    public function editaCategoria() {
        $conn = Database::getConnection();
        $sql  = $conn->prepare("UPDATE categorias SET nome = :nome, cor = :cor, icone = :icone WHERE id = :id AND usuario_id = @uid");
        return $sql->execute([
            ':nome'  => $this->descricao,
            ':cor'   => $this->cor   ?? '#6B7280',
            ':icone' => $this->icone ?: null,
            ':id'    => $this->id,
        ]);
    }

    public function excluiCategoria() {
        $conn = Database::getConnection();
        $sql  = $conn->prepare("UPDATE categorias SET ativo = 'N' WHERE id = :id AND usuario_id = @uid");
        return $sql->execute([':id' => $this->id]);
    }

    public function buscaCategorias() {
        $conn = Database::getConnection();
        $stmt = $conn->prepare("SELECT id, nome, cor, icone FROM categorias WHERE ativo = 'S' AND usuario_id = @uid ORDER BY nome");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Cria o conjunto padrão de categorias para um novo usuário.
     * Usa usuario_id explícito (e não @uid) por ser chamado no momento do cadastro.
     */
    public static function seedPadrao(int $usuarioId): void {
        if ($usuarioId <= 0) return;
        $padrao = [
            ['Alimentação', '#14B8A6', '🍽️'],
            ['Transporte',  '#8B5CF6', '🚖'],
            ['Moradia',     '#F59E0B', '🏠'],
            ['Saúde',       '#EF4444', '💊'],
            ['Educação',    '#3B82F6', '👨‍🎓'],
            ['Lazer',       '#F97316', '🎮️'],
            ['Assinaturas', '#6B7280', '♾️'],
            ['Presentes',   '#22C55E', '🎁'],
            ['Outros',      '#84CC16', '👽️'],
        ];
        $conn = Database::getConnection();
        $stmt = $conn->prepare("INSERT INTO categorias (usuario_id, nome, cor, icone) VALUES (?, ?, ?, ?)");
        foreach ($padrao as [$nome, $cor, $icone]) {
            try { $stmt->execute([$usuarioId, $nome, $cor, $icone]); } catch (Exception $e) {}
        }
    }
}
