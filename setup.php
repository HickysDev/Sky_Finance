<?php
/**
 * setup.php — Criação do usuário inicial.
 * Só funciona se a tabela usuarios estiver vazia.
 * Após criar o usuário, esse arquivo pode ser deletado.
 */
require_once __DIR__ . '/conn/conn.php';
require_once __DIR__ . '/conn/config.php';

$conn = Database::getConnection();

// Bloqueia se já existir usuário
try {
    $qtd = $conn->query("SELECT COUNT(*) FROM usuarios")->fetchColumn();
    if ($qtd > 0) {
        die('<p style="font-family:sans-serif;padding:2rem;">Usuário já cadastrado. Delete este arquivo (setup.php).</p>');
    }
} catch (Exception $e) {
    die('<p style="font-family:sans-serif;padding:2rem;color:red;">Tabela usuarios não encontrada. Execute o SQL em sql/usuarios.sql primeiro.</p>');
}

$sucesso = false;
$erro    = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome  = trim($_POST['nome']   ?? '');
    $email = trim($_POST['email']  ?? '');
    $senha = $_POST['senha']       ?? '';
    $conf  = $_POST['confirmacao'] ?? '';

    if (!$nome || !$email || !$senha) {
        $erro = 'Preencha todos os campos.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $erro = 'E-mail inválido.';
    } elseif (strlen($senha) < 8) {
        $erro = 'A senha deve ter pelo menos 8 caracteres.';
    } elseif ($senha !== $conf) {
        $erro = 'As senhas não conferem.';
    } else {
        $hash = password_hash($senha, PASSWORD_BCRYPT, ['cost' => 12]);
        $stmt = $conn->prepare("INSERT INTO usuarios (nome, email, senha_hash) VALUES (?, ?, ?)");
        if ($stmt->execute([$nome, $email, $hash])) {
            $sucesso = true;
        } else {
            $erro = 'Erro ao criar usuário.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br" data-bs-theme="dark">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Setup — Sky Finance</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
  <link href="styles/style.css" rel="stylesheet">
  <style>
    html, body { height: 100%; }
    .setup-wrap {
      min-height: 100vh; display: flex;
      align-items: center; justify-content: center; padding: 2rem 1rem;
    }
    .setup-card {
      width: 100%; max-width: 420px;
      background: rgba(43,44,59,0.6);
      backdrop-filter: blur(24px); -webkit-backdrop-filter: blur(24px);
      border: 1px solid rgba(255,255,255,0.09);
      border-radius: 18px; padding: 2.5rem 2rem;
      box-shadow: 0 8px 40px rgba(0,0,0,0.45);
    }
    .setup-titulo {
      font-family: "Bebas Neue", sans-serif;
      font-size: 1.5rem; letter-spacing: 2px;
      color: #F0F0F5; margin-bottom: 0.25rem;
    }
    .setup-sub { font-size: 0.8rem; color: #6B7280; margin-bottom: 1.75rem; }
    .form-control {
      background: rgba(44,44,68,0.7) !important;
      border: 1px solid rgba(255,255,255,0.1) !important;
      border-radius: 10px !important; color: #F0F0F5 !important;
    }
    .form-control:focus {
      border-color: #3B82F6 !important;
      box-shadow: 0 0 0 3px rgba(59,130,246,0.2) !important;
    }
    .form-label { font-size: 0.8rem; color: #9CA3AF; }
    .btn-setup {
      width: 100%; padding: 0.7rem; background: #3B82F6; border: none;
      border-radius: 10px; color: #fff; font-weight: 600; font-size: 0.95rem;
      margin-top: 0.5rem; transition: background 0.2s;
    }
    .btn-setup:hover { background: #2563EB; }
    .aviso {
      background: rgba(251,191,36,0.1); border: 1px solid rgba(251,191,36,0.3);
      border-radius: 10px; color: #FCD34D; font-size: 0.8rem;
      padding: 0.6rem 0.9rem; margin-top: 1.25rem;
      display: flex; align-items: flex-start; gap: 8px;
    }
  </style>
</head>
<body>
<div class="aurora" aria-hidden="true">
  <div class="aurora-blob aurora-blob-1"></div>
  <div class="aurora-blob aurora-blob-2"></div>
  <div class="aurora-blob aurora-blob-3"></div>
  <div class="aurora-blob aurora-blob-4"></div>
</div>

<div class="setup-wrap">
  <div class="setup-card">

    <?php if ($sucesso): ?>
      <div class="text-center">
        <i class="bi bi-check-circle-fill" style="font-size:3rem;color:#22C55E;"></i>
        <div class="setup-titulo mt-3">Usuário criado!</div>
        <p style="color:#9CA3AF;font-size:0.88rem;margin-top:0.5rem;">
          Agora delete o arquivo <code>setup.php</code> do servidor e faça seu login.
        </p>
        <a href="login.php" class="btn-setup d-block text-decoration-none text-white mt-3" style="padding:0.7rem;border-radius:10px;">
          <i class="bi bi-box-arrow-in-right me-2"></i>Ir para o Login
        </a>
      </div>
    <?php else: ?>
      <div class="setup-titulo">Configuração Inicial</div>
      <div class="setup-sub">Crie sua conta de acesso ao Sky Finance</div>

      <?php if ($erro): ?>
        <div class="alert alert-danger py-2 px-3 mb-3" style="font-size:0.84rem;border-radius:10px;">
          <i class="bi bi-exclamation-circle me-1"></i><?= htmlspecialchars($erro) ?>
        </div>
      <?php endif; ?>

      <form method="POST">
        <div class="mb-3">
          <label class="form-label">Nome</label>
          <input type="text" name="nome" class="form-control" placeholder="Seu nome" required
                 value="<?= htmlspecialchars($_POST['nome'] ?? '') ?>">
        </div>
        <div class="mb-3">
          <label class="form-label">E-mail</label>
          <input type="email" name="email" class="form-control" placeholder="seu@email.com" required
                 value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
        </div>
        <div class="mb-3">
          <label class="form-label">Senha <span style="color:#6B7280;">(mín. 8 caracteres)</span></label>
          <input type="password" name="senha" class="form-control" placeholder="••••••••" required>
        </div>
        <div class="mb-3">
          <label class="form-label">Confirmar senha</label>
          <input type="password" name="confirmacao" class="form-control" placeholder="••••••••" required>
        </div>
        <button type="submit" class="btn-setup">
          <i class="bi bi-person-plus-fill me-2"></i>Criar conta
        </button>
      </form>

      <div class="aviso">
        <i class="bi bi-exclamation-triangle-fill mt-1 flex-shrink-0"></i>
        <span>Após criar sua conta, <strong>delete este arquivo</strong> do servidor para evitar que outra pessoa o use.</span>
      </div>
    <?php endif; ?>

  </div>
</div>
</body>
</html>
