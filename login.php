<?php
ini_set('session.cookie_httponly', '1');
ini_set('session.cookie_samesite', 'Strict');
session_start();

require_once __DIR__ . '/conn/conn.php';
require_once __DIR__ . '/conn/config.php';
require_once __DIR__ . '/php/models/CategoriaModel.php';

if (!empty($_SESSION['usuario_id'])) {
    header('Location: ' . BASE_URL . '/index.php');
    exit;
}

$conn = Database::getConnection();

// Verifica se já existe algum usuário cadastrado
$semUsuario = false;
try {
    $semUsuario = (int) $conn->query("SELECT COUNT(*) FROM usuarios")->fetchColumn() === 0;
} catch (Exception $e) {}

$erro    = '';
$sucesso = '';
$aba     = $_POST['aba'] ?? 'login';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'] ?? '')) {
        $erro = 'Requisição inválida. Tente novamente.';
    } elseif ($aba === 'cadastro') {
        // ── Cadastro ──────────────────────────────────────────────
        if (!$semUsuario) {
            $erro = 'Cadastro não permitido.';
        } else {
            $nome  = trim($_POST['nome']        ?? '');
            $email = trim($_POST['reg_email']   ?? '');
            $senha = $_POST['reg_senha']        ?? '';
            $conf  = $_POST['reg_confirmacao']  ?? '';

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
                    $novoId = (int) $conn->lastInsertId();
                    CategoriaModel::seedPadrao($novoId);
                    session_regenerate_id(true);
                    $_SESSION['usuario_id']   = $novoId;
                    $_SESSION['usuario_nome'] = $nome;
                    unset($_SESSION['csrf_token']);
                    header('Location: ' . BASE_URL . '/index.php');
                    exit;
                } else {
                    $erro = 'Erro ao criar conta.';
                }
            }
        }
    } else {
        // ── Login ─────────────────────────────────────────────────
        $ip        = $_SERVER['REMOTE_ADDR'];
        $bloqueado = false;

        try {
            $s = $conn->prepare("SELECT bloqueado_ate FROM login_tentativas WHERE ip = ?");
            $s->execute([$ip]);
            $reg = $s->fetch(PDO::FETCH_ASSOC);
            if ($reg && $reg['bloqueado_ate'] && new DateTime() < new DateTime($reg['bloqueado_ate'])) {
                $bloqueado = true;
                $erro = 'Muitas tentativas incorretas. Aguarde 15 minutos.';
            }
        } catch (Exception $e) {}

        if (!$bloqueado) {
            $email = trim($_POST['email'] ?? '');
            $senha = $_POST['senha']      ?? '';

            $s = $conn->prepare("SELECT id, nome, senha_hash, ativo FROM usuarios WHERE email = ? LIMIT 1");
            $s->execute([$email]);
            $usuario = $s->fetch(PDO::FETCH_ASSOC);

            if ($usuario && $usuario['ativo'] === 'S' && password_verify($senha, $usuario['senha_hash'])) {
                try { $conn->prepare("DELETE FROM login_tentativas WHERE ip = ?")->execute([$ip]); } catch (Exception $e) {}

                session_regenerate_id(true);
                $_SESSION['usuario_id']   = (int) $usuario['id'];
                $_SESSION['usuario_nome'] = $usuario['nome'];
                unset($_SESSION['csrf_token']);

                try { $conn->prepare("UPDATE usuarios SET ultimo_login = NOW() WHERE id = ?")->execute([$usuario['id']]); } catch (Exception $e) {}

                header('Location: ' . BASE_URL . '/index.php');
                exit;
            } else {
                try {
                    $conn->prepare("
                        INSERT INTO login_tentativas (ip, tentativas, ultima_tentativa)
                        VALUES (?, 1, NOW())
                        ON DUPLICATE KEY UPDATE
                            tentativas       = tentativas + 1,
                            ultima_tentativa = NOW(),
                            bloqueado_ate    = IF(tentativas + 1 >= 5, DATE_ADD(NOW(), INTERVAL 15 MINUTE), NULL)
                    ")->execute([$ip]);
                } catch (Exception $e) {}

                $erro = 'E-mail ou senha incorretos.';
            }
        }
    }
}

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>
<!DOCTYPE html>
<html lang="pt-br" data-bs-theme="dark">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Login — Sky Finance</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
  <link href="styles/style.css" rel="stylesheet">
  <style>
    html, body { height: 100%; }

    .login-wrap {
      min-height: 100vh;
      display: flex; align-items: center; justify-content: center;
      padding: 2rem 1rem;
    }

    .login-card {
      width: 100%; max-width: 400px;
      background: rgba(43,44,59,0.6);
      backdrop-filter: blur(24px); -webkit-backdrop-filter: blur(24px);
      border: 1px solid rgba(255,255,255,0.09);
      border-radius: 18px;
      padding: 2.5rem 2rem;
      box-shadow: 0 8px 40px rgba(0,0,0,0.45);
    }

    .login-logo {
      display: flex; flex-direction: column;
      align-items: center; gap: 8px; margin-bottom: 1.75rem;
    }
    .login-logo img   { height: 52px; }
    .login-logo span  { font-family:"Bebas Neue",sans-serif; font-size:1.6rem; letter-spacing:2px; color:#F0F0F5; line-height:1; }
    .login-logo small { font-size:0.72rem; color:#6B7280; letter-spacing:.1em; text-transform:uppercase; }

    /* Abas */
    .login-tabs {
      display: flex; gap: 4px;
      background: rgba(0,0,0,0.2);
      border-radius: 10px; padding: 4px;
      margin-bottom: 1.5rem;
    }
    .login-tab {
      flex: 1; padding: 0.45rem;
      border: none; border-radius: 8px;
      background: transparent; color: #6B7280;
      font-size: 0.82rem; font-weight: 600;
      cursor: pointer; transition: background 0.2s, color 0.2s;
    }
    .login-tab.active { background: rgba(59,130,246,0.2); color: #93C5FD; }
    .login-tab:hover:not(.active) { color: #9CA3AF; }

    .login-label { font-size:.8rem; font-weight:500; color:#9CA3AF; margin-bottom:6px; display:block; }

    .login-input {
      background: rgba(44,44,68,0.7) !important;
      border: 1px solid rgba(255,255,255,0.1) !important;
      border-radius: 10px !important;
      color: #F0F0F5 !important;
      padding: 0.65rem 1rem !important;
      font-size: 0.92rem;
    }
    .login-input:focus {
      border-color: #3B82F6 !important;
      box-shadow: 0 0 0 3px rgba(59,130,246,0.2) !important;
      background: rgba(44,44,68,0.85) !important;
    }
    .login-input::placeholder { color: #6B7280; }

    .btn-login {
      width: 100%; padding: 0.7rem;
      background: #3B82F6; border: none; border-radius: 10px;
      color: #fff; font-weight: 600; font-size: 0.95rem;
      transition: background 0.2s, transform 0.1s; margin-top: 0.5rem;
    }
    .btn-login:hover  { background: #2563EB; }
    .btn-login:active { transform: scale(0.98); }

    .login-erro {
      background: rgba(239,68,68,0.12); border: 1px solid rgba(239,68,68,0.35);
      border-radius: 10px; color: #FCA5A5; font-size: 0.84rem;
      padding: 0.65rem 1rem; margin-bottom: 1.25rem;
      display: flex; align-items: center; gap: 8px;
    }

    .password-wrap { position: relative; }
    .toggle-senha {
      position: absolute; right: 12px; top: 50%; transform: translateY(-50%);
      background: none; border: none; color: #6B7280;
      cursor: pointer; font-size: 1rem; padding: 0; transition: color 0.2s;
    }
    .toggle-senha:hover { color: #9CA3AF; }

    .login-panel { display: none; }
    .login-panel.active { display: block; }
  </style>
</head>
<body>

<div class="aurora" aria-hidden="true">
  <div class="aurora-blob aurora-blob-1"></div>
  <div class="aurora-blob aurora-blob-2"></div>
  <div class="aurora-blob aurora-blob-3"></div>
  <div class="aurora-blob aurora-blob-4"></div>
</div>

<div class="login-wrap">
  <div class="login-card">

    <div class="login-logo">
      <img src="src/img/logo.png" alt="Sky Finance">
      <span>Sky Finance</span>
      <small>Gestão Financeira Pessoal</small>
    </div>

    <?php if ($semUsuario): ?>
    <div class="login-tabs">
      <button type="button" class="login-tab <?= $aba !== 'cadastro' ? 'active' : '' ?>" data-tab="login">
        <i class="bi bi-box-arrow-in-right me-1"></i>Entrar
      </button>
      <button type="button" class="login-tab <?= $aba === 'cadastro' ? 'active' : '' ?>" data-tab="cadastro">
        <i class="bi bi-person-plus me-1"></i>Criar conta
      </button>
    </div>
    <?php endif; ?>

    <?php if ($erro): ?>
    <div class="login-erro">
      <i class="bi bi-exclamation-circle-fill"></i>
      <?= htmlspecialchars($erro) ?>
    </div>
    <?php endif; ?>

    <!-- ABA: Entrar -->
    <div class="login-panel <?= $aba !== 'cadastro' ? 'active' : '' ?>" id="panel-login">
      <form method="POST" action="login.php" autocomplete="on">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
        <input type="hidden" name="aba" value="login">

        <div class="mb-3">
          <label class="login-label" for="email">E-mail</label>
          <input type="email" id="email" name="email" class="form-control login-input"
                 placeholder="seu@email.com" required autofocus
                 value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
        </div>
        <div class="mb-4">
          <label class="login-label" for="senha">Senha</label>
          <div class="password-wrap">
            <input type="password" id="senha" name="senha" class="form-control login-input"
                   placeholder="••••••••" required style="padding-right:2.5rem!important;">
            <button type="button" class="toggle-senha" data-alvo="senha" data-icone="olhoLogin">
              <i class="bi bi-eye" id="olhoLogin"></i>
            </button>
          </div>
        </div>
        <button type="submit" class="btn-login">
          <i class="bi bi-box-arrow-in-right me-2"></i>Entrar
        </button>
      </form>
    </div>

    <!-- ABA: Criar conta (só aparece sem usuários) -->
    <?php if ($semUsuario): ?>
    <div class="login-panel <?= $aba === 'cadastro' ? 'active' : '' ?>" id="panel-cadastro">
      <form method="POST" action="login.php" autocomplete="off">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
        <input type="hidden" name="aba" value="cadastro">

        <div class="mb-3">
          <label class="login-label" for="nome">Nome</label>
          <input type="text" id="nome" name="nome" class="form-control login-input"
                 placeholder="Seu nome" required
                 value="<?= htmlspecialchars($_POST['nome'] ?? '') ?>">
        </div>
        <div class="mb-3">
          <label class="login-label" for="reg_email">E-mail</label>
          <input type="email" id="reg_email" name="reg_email" class="form-control login-input"
                 placeholder="seu@email.com" required
                 value="<?= htmlspecialchars($_POST['reg_email'] ?? '') ?>">
        </div>
        <div class="mb-3">
          <label class="login-label" for="reg_senha">Senha <span style="color:#6B7280;">(mín. 8 caracteres)</span></label>
          <div class="password-wrap">
            <input type="password" id="reg_senha" name="reg_senha" class="form-control login-input"
                   placeholder="••••••••" required style="padding-right:2.5rem!important;">
            <button type="button" class="toggle-senha" data-alvo="reg_senha" data-icone="olhoCad">
              <i class="bi bi-eye" id="olhoCad"></i>
            </button>
          </div>
        </div>
        <div class="mb-4">
          <label class="login-label" for="reg_confirmacao">Confirmar senha</label>
          <input type="password" id="reg_confirmacao" name="reg_confirmacao"
                 class="form-control login-input" placeholder="••••••••" required>
        </div>
        <button type="submit" class="btn-login" style="background:#22C55E;">
          <i class="bi bi-person-plus-fill me-2"></i>Criar conta
        </button>
      </form>
    </div>
    <?php endif; ?>

  </div>
</div>

<script>
// Troca de aba
document.querySelectorAll('.login-tab').forEach(function (btn) {
    btn.addEventListener('click', function () {
        var tab = this.dataset.tab;
        document.querySelectorAll('.login-tab').forEach(function (b) { b.classList.remove('active'); });
        document.querySelectorAll('.login-panel').forEach(function (p) { p.classList.remove('active'); });
        this.classList.add('active');
        document.getElementById('panel-' + tab).classList.add('active');
    });
});

// Mostrar/ocultar senha
document.querySelectorAll('.toggle-senha').forEach(function (btn) {
    btn.addEventListener('click', function () {
        var inp  = document.getElementById(this.dataset.alvo);
        var icon = document.getElementById(this.dataset.icone);
        if (inp.type === 'password') {
            inp.type = 'text';
            icon.className = 'bi bi-eye-slash';
        } else {
            inp.type = 'password';
            icon.className = 'bi bi-eye';
        }
    });
});
</script>
</body>
</html>
