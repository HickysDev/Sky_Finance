<?php
require_once __DIR__ . '/../../conn/conn.php';
require_once __DIR__ . '/../middleware/auth.php';

header('Content-Type: application/json; charset=utf-8');

$acao = $_POST['acao'] ?? $_GET['acao'] ?? '';
$conn = Database::getConnection();
$meuId = (int) $_SESSION['usuario_id'];

switch ($acao) {

    case 'meu_perfil':
        $stmt = $conn->prepare("SELECT id, nome, email, foto FROM usuarios WHERE id = ?");
        $stmt->execute([$meuId]);
        echo json_encode($stmt->fetch(PDO::FETCH_ASSOC));
        break;

    case 'atualizar_perfil':
        $nome  = trim($_POST['nome']  ?? '');
        $email = trim($_POST['email'] ?? '');
        if (!$nome || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            http_response_code(400);
            echo json_encode(['erro' => 'Nome ou e-mail inválido.']);
            break;
        }
        $stmt = $conn->prepare("UPDATE usuarios SET nome = ?, email = ? WHERE id = ?");
        if ($stmt->execute([$nome, $email, $meuId])) {
            $_SESSION['usuario_nome'] = $nome;
            echo json_encode(['ok' => true, 'nome' => $nome]);
        } else {
            http_response_code(500);
            echo json_encode(['erro' => 'E-mail já em uso por outro usuário.']);
        }
        break;

    case 'upload_foto':
        $upload = $_FILES['foto'] ?? null;
        if (!$upload || $upload['error'] !== UPLOAD_ERR_OK) {
            http_response_code(400);
            echo json_encode(['erro' => 'Falha no upload.']);
            break;
        }

        $tipos = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
        $mime  = mime_content_type($upload['tmp_name']);
        if (!in_array($mime, $tipos)) {
            http_response_code(400);
            echo json_encode(['erro' => 'Formato inválido. Use JPG, PNG ou WEBP.']);
            break;
        }
        if ($upload['size'] > 3 * 1024 * 1024) {
            http_response_code(400);
            echo json_encode(['erro' => 'Imagem muito grande. Máximo 3 MB.']);
            break;
        }

        $ext     = pathinfo($upload['name'], PATHINFO_EXTENSION) ?: 'jpg';
        $dir     = __DIR__ . '/../../src/img/avatars/';
        if (!is_dir($dir)) mkdir($dir, 0755, true);

        // Remove foto anterior
        $old = $conn->prepare("SELECT foto FROM usuarios WHERE id = ?");
        $old->execute([$meuId]);
        $oldFoto = $old->fetchColumn();
        if ($oldFoto && file_exists($dir . $oldFoto)) unlink($dir . $oldFoto);

        $filename = $meuId . '_' . time() . '.' . $ext;
        if (move_uploaded_file($upload['tmp_name'], $dir . $filename)) {
            $conn->prepare("UPDATE usuarios SET foto = ? WHERE id = ?")->execute([$filename, $meuId]);
            echo json_encode(['ok' => true, 'foto' => $filename]);
        } else {
            http_response_code(500);
            echo json_encode(['erro' => 'Erro ao salvar imagem.']);
        }
        break;

    case 'listar':
        $stmt = $conn->prepare("SELECT id, nome, email, ativo, ultimo_login, created_at FROM usuarios ORDER BY id");
        $stmt->execute();
        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
        break;

    case 'adicionar':
        $nome  = trim($_POST['nome']  ?? '');
        $email = trim($_POST['email'] ?? '');
        $senha = $_POST['senha']      ?? '';

        if (!$nome || !$email || strlen($senha) < 8) {
            http_response_code(400);
            echo json_encode(['erro' => 'Dados inválidos. Senha mínima: 8 caracteres.']);
            break;
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            http_response_code(400);
            echo json_encode(['erro' => 'E-mail inválido.']);
            break;
        }

        $hash = password_hash($senha, PASSWORD_BCRYPT, ['cost' => 12]);
        $stmt = $conn->prepare("INSERT INTO usuarios (nome, email, senha_hash) VALUES (?, ?, ?)");
        if ($stmt->execute([$nome, $email, $hash])) {
            echo json_encode(['ok' => true]);
        } else {
            http_response_code(500);
            echo json_encode(['erro' => 'E-mail já cadastrado ou erro interno.']);
        }
        break;

    case 'remover':
        $id = (int) ($_POST['id'] ?? 0);
        if (!$id || $id === $meuId) {
            http_response_code(400);
            echo json_encode(['erro' => 'Não é possível remover sua própria conta.']);
            break;
        }
        $stmt = $conn->prepare("DELETE FROM usuarios WHERE id = ?");
        echo json_encode($stmt->execute([$id]));
        break;

    case 'trocar_senha':
        $senhaAtual = $_POST['senha_atual'] ?? '';
        $novaSenha  = $_POST['nova_senha']  ?? '';
        $confirma   = $_POST['confirma']    ?? '';

        if (strlen($novaSenha) < 8) {
            http_response_code(400);
            echo json_encode(['erro' => 'Nova senha deve ter pelo menos 8 caracteres.']);
            break;
        }
        if ($novaSenha !== $confirma) {
            http_response_code(400);
            echo json_encode(['erro' => 'As senhas não conferem.']);
            break;
        }

        $stmt = $conn->prepare("SELECT senha_hash FROM usuarios WHERE id = ?");
        $stmt->execute([$meuId]);
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$usuario || !password_verify($senhaAtual, $usuario['senha_hash'])) {
            http_response_code(400);
            echo json_encode(['erro' => 'Senha atual incorreta.']);
            break;
        }

        $hash = password_hash($novaSenha, PASSWORD_BCRYPT, ['cost' => 12]);
        $stmt = $conn->prepare("UPDATE usuarios SET senha_hash = ? WHERE id = ?");
        echo json_encode($stmt->execute([$hash, $meuId]) ? ['ok' => true] : ['erro' => 'Erro ao atualizar senha.']);
        break;

    case 'reset_dados':
        if ($meuId !== 1) {
            http_response_code(403);
            echo json_encode(['erro' => 'Acesso negado.']);
            break;
        }

        $tabelas = [
            // filhos primeiro
            'parcelas',
            'gastos_recorrentes_lancamentos',
            'faturas_pagas',
            'contas_fixas_pagamentos',
            'contas_pessoa',
            'cofrinho_aportes',
            'orcamentos',
            'gastos',
            'gastos_recorrentes',
            'cofrinhos',
            'cartoes_credito',
            'categorias',
            'responsaveis',
            'contas_fixas',
            'renda_mensal',
            'login_tentativas',
        ];

        try {
            $conn->exec("SET FOREIGN_KEY_CHECKS = 0");
            foreach ($tabelas as $tabela) {
                try { $conn->exec("DELETE FROM `$tabela`"); } catch (Exception $e) {}
                try { $conn->exec("ALTER TABLE `$tabela` AUTO_INCREMENT = 1"); } catch (Exception $e) {}
            }
            $conn->exec("SET FOREIGN_KEY_CHECKS = 1");
            echo json_encode(['ok' => true]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['erro' => 'Erro ao limpar dados: ' . $e->getMessage()]);
        }
        break;

    default:
        http_response_code(400);
        echo json_encode(['erro' => 'Ação inválida.']);
}
