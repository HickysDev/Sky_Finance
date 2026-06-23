<?php
include_once __DIR__ . '/../../conn/conn.php';
include_once __DIR__ . '/../../conn/config.php';
require_once __DIR__ . '/../middleware/auth.php';
include_once __DIR__ . '/../services/RecorrentesService.php';
include_once __DIR__ . '/../models/ConfigModel.php';

RecorrentesService::lancarRecorrentesDoMes();

$conn = Database::getConnection();
?>
<!DOCTYPE html>
<html lang="pt-br" data-bs-theme="dark">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Sky Finance</title>

  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet"
    integrity="sha384-rbsA2VBKQhggwzxH7pPCaAqO46MgnOM80zW1RWuH61DGLwZJEdK2Kadq2F9CUG65" crossorigin="anonymous" />

  <!-- DataTables CSS -->
  <link href="https://cdn.datatables.net/2.2.2/css/dataTables.dataTables.min.css" rel="stylesheet" />

  <!-- Tippy.js CSS -->
  <link href="https://unpkg.com/tippy.js@6.3.1/dist/tippy.css" rel="stylesheet" />

  <!-- Toastr CSS -->
  <link href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css" rel="stylesheet" />

  <!-- SweetAlert2 CSS -->
  <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet" />

  <!-- Animate.css -->
  <link href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" rel="stylesheet" />

  <!-- Google Fonts -->
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&family=Bebas+Neue&display=swap" rel="stylesheet" />

  <!-- Bootstrap Icons -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet" />

  <!-- Custom CSS -->
  <link rel="stylesheet" href="<?= BASE_URL ?>/styles/style.css" />

  <!-- jQuery -->
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>

  <!-- DataTables JS -->
  <script src="https://cdn.datatables.net/2.2.2/js/dataTables.min.js"></script>

  <!-- Cleave.js -->
  <script src="https://cdn.jsdelivr.net/npm/cleave.js/dist/cleave.min.js"></script>

  <!-- Popper + Tippy -->
  <script src="https://unpkg.com/@popperjs/core@2/dist/umd/popper.min.js"></script>
  <script src="https://unpkg.com/tippy.js@6/dist/tippy-bundle.umd.js"></script>

  <!-- Moment.js -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.4/moment.min.js"></script>

  <!-- Toastr JS -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>

  <!-- SweetAlert2 JS -->
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

  <!-- Configuração global — use App.ctrl.* em qualquer página -->
  <script>
  window.App = {
      base: '<?= BASE_URL ?>',
      marcoInicio: '<?= ConfigModel::getMesInicio() ?? '' ?>',
      ctrl: {
          gastos:    '<?= CTRL_GASTOS ?>',
          categoria: '<?= CTRL_CATEGORIA ?>',
          cartoes:   '<?= CTRL_CARTOES ?>',
          financas:  '<?= CTRL_FINANCAS ?>',
          orcamento: '<?= CTRL_ORCAMENTO ?>',
          cofrinho:      '<?= CTRL_COFRINHO ?>',
          responsaveis:  '<?= CTRL_RESPONSAVEIS ?>',
          contasFixas:   '<?= CTRL_CONTAS_FIXAS ?>',
          usuarios:      '<?= CTRL_USUARIOS ?>'
      }
  };
  </script>

  <!-- Escape de HTML global (previne XSS ao injetar dados do servidor) -->
  <script>
  function escHtml(str) {
      if (str === null || str === undefined) return '';
      return String(str)
          .replace(/&/g, '&amp;')
          .replace(/</g, '&lt;')
          .replace(/>/g, '&gt;')
          .replace(/"/g, '&quot;')
          .replace(/'/g, '&#39;');
  }
  window.escHtml = escHtml;

  // ── Marco inicial: avisa quando o mês visto é anterior ao início do controle ──
  window.antesDoMarco = function (mes, ano) {
      if (!window.App || !App.marcoInicio) return false;
      var alvo = ('0000' + ano).slice(-4) + '-' + ('00' + mes).slice(-2) + '-01';
      return alvo < App.marcoInicio;
  };

  window.atualizaAvisoMarco = function (mes, ano) {
      var $b = $('#avisoMarcoBanner');
      if (window.antesDoMarco(mes, ano)) {
          if (!$b.length) {
              $('.corpo-site').first().prepend(
                  '<div id="avisoMarcoBanner" class="aviso-marco animate__animated animate__fadeIn">' +
                  '<i class="bi bi-flag-fill me-2"></i>' +
                  'Mês anterior ao início do controle — os dados aparecem zerados.</div>'
              );
          }
      } else {
          $b.remove();
      }
  };

  // ── Input monetário estilo app de banco ──────────────────────────
  // Os dígitos entram pelos centavos e "empurram" para a esquerda.
  // Retorna { getValue(), setValue(v) } como substituto do Cleave.
  window.bancInput = function (el, valorInicial) {
      if (typeof el === 'string') el = document.querySelector(el);
      if (!el) return { getValue: function () { return 0; }, setValue: function () {} };

      var digits = '';

      function parseToDigits(v) {
          if (v === null || v === undefined || v === '') return '';
          if (typeof v === 'number') {
              if (isNaN(v) || v <= 0) return '';
              return String(Math.round(v * 100)).replace(/^0+/, '') || '';
          }
          var s = String(v).replace(/R\$\s?/g, '').trim();
          if (s.indexOf(',') !== -1) {
              return s.replace(/[^\d]/g, '').replace(/^0+/, '') || '';
          }
          if (s.indexOf('.') !== -1) {
              var p = s.split('.');
              var intPart = p[0].replace(/\D/g, '');
              var decPart = (p[1] || '00').substring(0, 2).padEnd(2, '0');
              return (intPart + decPart).replace(/^0+/, '') || '';
          }
          return s.replace(/\D/g, '').replace(/^0+/, '') || '';
      }

      function render() {
          var n = digits || '';
          while (n.length < 3) n = '0' + n;
          var intPart = n.slice(0, -2).replace(/^0+/, '') || '0';
          var decPart = n.slice(-2);
          el.value = 'R$ ' + intPart.replace(/\B(?=(\d{3})+(?!\d))/g, '.') + ',' + decPart;
      }

      digits = parseToDigits(valorInicial);
      render();

      el.addEventListener('keydown', function (e) {
          if (e.key >= '0' && e.key <= '9') {
              e.preventDefault();
              if (digits.length < 13) digits += e.key;
              render();
          } else if (e.key === 'Backspace') {
              e.preventDefault();
              digits = digits.slice(0, -1);
              render();
          } else if (e.key === 'Delete') {
              e.preventDefault();
              digits = '';
              render();
          }
      });

      el.addEventListener('focus', function () {
          setTimeout(function () { el.select(); }, 10);
      });

      el.addEventListener('paste', function (e) {
          e.preventDefault();
          var text = (e.clipboardData || window.clipboardData).getData('text');
          digits = parseToDigits(text);
          render();
      });

      return {
          getValue: function () {
              if (!digits) return 0;
              var n = digits;
              while (n.length < 3) n = '0' + n;
              return parseFloat(n.slice(0, -2) + '.' + n.slice(-2));
          },
          setValue: function (v) {
              digits = parseToDigits(v);
              render();
          }
      };
  };
  </script>

  <!-- Fix: modais empilhadas (modal dentro de modal) -->
  <script>
  // Cada nova modal que abre enquanto outra já está aberta recebe z-index maior,
  // e seu backdrop fica entre a modal anterior e a nova.
  $(document).on('show.bs.modal', '.modal', function () {
      var abertas = $('.modal.show').length;
      if (abertas === 0) return;
      var baseZ = 1055 + (abertas * 15);
      $(this).css('z-index', baseZ + 5);
      setTimeout(function () {
          $('.modal-backdrop').last().css('z-index', baseZ);
      }, 10);
  });
  // Quando uma modal fecha mas outra ainda está aberta, mantém modal-open no body
  // (sem isso Bootstrap remove o classe e o scroll volta antes da hora).
  $(document).on('hidden.bs.modal', '.modal', function () {
      if ($('.modal.show').length) $('body').addClass('modal-open');
  });
  </script>

  <!-- Responsáveis global -->
  <script>
  window.responsaveisArray = [];

  function carregarResponsaveis(cb) {
      $.ajax({
          type: 'POST', url: App.ctrl.responsaveis,
          data: { acao: 'buscar' }, dataType: 'json',
          success: function (data) {
              window.responsaveisArray = data || [];
              renderResponsaveisMiniModal();
              if (typeof cb === 'function') cb(data);
          }
      });
  }

  function renderResponsaveisMiniModal() {
      var selected = String($('#responsavel').val() || '');
      var html = '<div class="resp-chip' + (!selected ? ' selecionado' : '') + '" data-id="">' +
                 '<i class="bi bi-person-fill me-1"></i>Eu</div>';
      $.each(window.responsaveisArray, function (_, r) {
          var cor = r.cor || '#6B7280';
          var sel = selected && String(r.id) === selected ? ' selecionado' : '';
          html += '<div class="resp-chip' + sel + '" data-id="' + r.id + '" style="--resp-cor:' + cor + ';">' +
                  '<span class="resp-dot"></span>' + r.nome + '</div>';
      });
      $('#responsavelSelector').html(html);
  }

  $(document).on('click', '.resp-chip', function () {
      $('.resp-chip').removeClass('selecionado');
      $(this).addClass('selecionado');
      $('#responsavel').val($(this).data('id'));
  });

  // Sempre que o modal de lançamento abre: reset responsável + renderiza chips
  $(document).on('show.bs.modal', '#modalAdiciona', function () {
      $('#responsavel').val('');
      if (window.responsaveisArray && window.responsaveisArray.length) {
          renderResponsaveisMiniModal();
      } else {
          carregarResponsaveis();
      }
  });
  </script>

  <!-- Utilitários globais de categoria -->
  <script>
  window.categoriaMap   = {};
  window.categoriaNomes = {};

  function popularCatSelect(data) {
      window.categoriaMap   = {};
      window.categoriaNomes = {};
      var menuHtml = '<li><a class="dropdown-item text-muted py-2" href="#" data-id="">Selecione</a></li>' +
                     '<li><hr class="dropdown-divider m-0"></li>';
      $.each(data, function (_, cat) {
          var cor    = cat.cor   || '#6B7280';
          var icone  = cat.icone || '';
          var iconeH = icone ? '<span class="me-1">' + escHtml(icone) + '</span>' : '';
          window.categoriaMap[String(cat.id)] = { nome: cat.nome, cor: cor, icone: icone };
          window.categoriaNomes[cat.nome]     = window.categoriaMap[String(cat.id)];
          menuHtml += '<li><a class="dropdown-item d-flex align-items-center gap-2" href="#" data-id="' + cat.id + '">' +
              '<span class="cat-dot" style="background:' + cor + ';flex-shrink:0;"></span>' +
              iconeH + '<span style="color:' + cor + ';">' + escHtml(cat.nome) + '</span></a></li>';
      });
      $('#catSelMenu').html(menuHtml);
  }

  function resetCatSelect() {
      $('#categoria').val('');
      $('#catSelBtn .cat-sel-preview').html('<span class="text-muted">Selecione</span>');
  }

  function catInlineHtml(key) {
      var cat = window.categoriaMap[String(key)] || window.categoriaNomes[key];
      var fs  = 'font-size:0.78rem;';
      if (!cat) return key ? '<span style="' + fs + 'color:var(--cor-texto-off);">' + escHtml(key) + '</span>' : '—';
      var cor   = cat.cor   || '#6B7280';
      var icone = cat.icone ? '<span class="me-1" style="' + fs + '">' + escHtml(cat.icone) + '</span>' : '';
      return '<span class="cat-dot me-1" style="background:' + cor + ';"></span>' +
             icone + '<span style="' + fs + 'color:' + cor + ';">' + escHtml(cat.nome) + '</span>';
  }

  function catBadgeHtml(key) {
      var cat = window.categoriaMap[String(key)] || window.categoriaNomes[key];
      if (!cat) return key ? escHtml(key) : '—';
      var cor   = cat.cor   || '#6B7280';
      var icone = cat.icone ? '<span class="me-1">' + escHtml(cat.icone) + '</span>' : '';
      return '<span style="background:' + cor + '22;color:' + cor + ';border:1px solid ' + cor + '55;' +
          'font-size:0.78rem;font-weight:500;padding:3px 10px;border-radius:20px;white-space:nowrap;display:inline-block;">' +
          icone + escHtml(cat.nome) + '</span>';
  }

  $(document).on('click', '#catSelMenu a', function (e) {
      e.preventDefault();
      e.stopPropagation();
      var id  = String($(this).data('id') || '');
      var cat = window.categoriaMap[id];
      $('#categoria').val(id);
      if (cat) {
          var cor   = cat.cor   || '#6B7280';
          var icone = cat.icone ? '<span class="me-1">' + escHtml(cat.icone) + '</span>' : '';
          $('#catSelBtn .cat-sel-preview').html(
              '<span class="cat-dot" style="background:' + cor + ';flex-shrink:0;"></span>' +
              icone + '<span class="ms-1" style="color:' + cor + ';">' + escHtml(cat.nome) + '</span>'
          );
      } else {
          $('#catSelBtn .cat-sel-preview').html('<span class="text-muted">Selecione</span>');
      }
      var el = document.getElementById('catSelBtn');
      if (el) { var dd = bootstrap.Dropdown.getInstance(el); if (dd) dd.hide(); }
  });
  </script>
</head>
<?php
$paginaAtual = $_SERVER['REQUEST_URI'] ?? '';

$navGrupos = [
    'principal' => [
        ['href' => BASE_URL . '/index.php',                                        'label' => 'Dashboard',    'icon' => 'bi-speedometer2',        'match' => 'index.php',          'match_q' => ''],
    ],
    'despesas' => [
        ['href' => BASE_URL . '/php/views/cartaocredito.php',                      'label' => 'Crédito',      'icon' => 'bi-credit-card-fill',    'match' => 'cartaocredito.php',  'match_q' => ''],
        ['href' => BASE_URL . '/php/views/debito.php',                             'label' => 'À Vista',      'icon' => 'bi-cash-coin',           'match' => 'debito.php',         'match_q' => ''],
        ['href' => BASE_URL . '/php/views/contas_fixas.php',                       'label' => 'Fixas',        'icon' => 'bi-receipt-cutoff',      'match' => 'contas_fixas.php',   'match_q' => ''],
    ],
    'gestao' => [
        ['href' => BASE_URL . '/php/views/financas.php',                           'label' => 'Finanças',     'icon' => 'bi-piggy-bank-fill',     'match' => 'financas.php',       'match_q' => ''],
        ['href' => BASE_URL . '/php/views/resumo_anual.php',                       'label' => 'Resumo',       'icon' => 'bi-bar-chart-line-fill', 'match' => 'resumo_anual.php',   'match_q' => ''],
        ['href' => BASE_URL . '/php/views/responsaveis.php',                       'label' => 'Pessoas',      'icon' => 'bi-people-fill',         'match' => 'responsaveis.php',   'match_q' => ''],
        ['href' => BASE_URL . '/php/views/simulador.php',                          'label' => 'Simulador',    'icon' => 'bi-calculator-fill',     'match' => 'simulador.php',      'match_q' => ''],
        ['href' => BASE_URL . '/php/views/gerenciamento.php',                      'label' => 'Config.',      'icon' => 'bi-gear-fill',           'match' => 'gerenciamento.php',  'match_q' => ''],
        ['href' => BASE_URL . '/php/views/backup.php',                              'label' => 'Backup',       'icon' => 'bi-database-down',       'match' => 'backup.php',         'match_q' => ''],
    ],
];
?>
<body>

<!-- Aurora background -->
<div class="aurora" aria-hidden="true">
  <div class="aurora-blob aurora-blob-1"></div>
  <div class="aurora-blob aurora-blob-2"></div>
  <div class="aurora-blob aurora-blob-3"></div>
  <div class="aurora-blob aurora-blob-4"></div>
</div>

<header>
  <nav class="navbar-sky">
    <!-- Logo -->
    <a class="navbar-brand-sky" href="<?= BASE_URL ?>/index.php">
      <img src="<?= BASE_URL ?>/src/img/logo.png" alt="Sky Finance" class="brand-logo">
      <span>Sky Finance</span>
    </a>

    <!-- Toggler mobile -->
    <button class="navbar-toggler-sky ms-auto" type="button"
      data-bs-toggle="collapse" data-bs-target="#navbarNav"
      aria-controls="navbarNav" aria-expanded="false">
      <i class="bi bi-list"></i>
    </button>

    <!-- Links -->
    <div class="navbar-collapse collapse" id="navbarNav" style="flex:1;display:flex;align-items:center;">
      <ul class="nav-sky">

        <?php foreach ($navGrupos as $grupo => $links): ?>

          <?php foreach ($links as $link):
            $matchQ  = $link['match_q'] ?? '';
            $matched = strpos($paginaAtual, $link['match']) !== false;
            if ($matched && $matchQ !== '') {
                $matched = strpos($paginaAtual, $matchQ) !== false;
            } elseif ($matched && $matchQ === '' && strpos($link['match'], 'gerenciamento.php') !== false) {
                // Config. só fica ativo se não estiver numa tab com link próprio na navbar
                $matched = strpos($paginaAtual, 'tab=Recorrentes') === false
                        && strpos($paginaAtual, 'tab=Cartoes') === false;
            }
            $ativo = $matched ? ' ativo' : '';
          ?>
            <li>
              <a class="nav-link-sky<?= $ativo ?>" href="<?= $link['href'] ?>">
                <i class="bi <?= $link['icon'] ?> nav-icon"></i>
                <span class="nav-label"><?= $link['label'] ?></span>
              </a>
            </li>
          <?php endforeach; ?>

          <?php if ($grupo !== 'gestao'): ?>
            <li class="nav-sep" aria-hidden="true"></li>
          <?php endif; ?>

        <?php endforeach; ?>

      </ul>

      <!-- Usuário -->
      <?php
        $navFoto = null;
        try {
          $sNav = $conn->prepare("SELECT foto FROM usuarios WHERE id = ?");
          $sNav->execute([(int)($_SESSION['usuario_id'] ?? 0)]);
          $navFoto = $sNav->fetchColumn() ?: null;
        } catch (Exception $e) {}
      ?>
      <div class="nav-sky-user">
        <div class="nav-avatar">
          <?php if ($navFoto): ?>
            <img src="<?= BASE_URL ?>/src/img/avatars/<?= htmlspecialchars($navFoto) ?>" alt="avatar" class="nav-avatar-img">
          <?php else: ?>
            <span><?= strtoupper(substr($_SESSION['usuario_nome'] ?? 'U', 0, 1)) ?></span>
          <?php endif; ?>
        </div>
        <span class="nav-username"><?= htmlspecialchars($_SESSION['usuario_nome'] ?? 'Usuário') ?></span>
        <a href="<?= BASE_URL ?>/logout.php" class="btn-logout" title="Sair">
          <i class="bi bi-box-arrow-right"></i>
        </a>
      </div>
    </div>
  </nav>
</header>

<main>
  <div class="corpo-site">
