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
  <?php
    // Título da aba = tela atual (heurística "reconhecer em vez de lembrar":
    // com várias abas abertas dá para saber qual é qual)
    $__titulos = ['index.php' => 'Dashboard', 'cartaocredito.php' => 'Crédito', 'debito.php' => 'À Vista',
      'contas_fixas.php' => 'Contas fixas', 'financas.php' => 'Finanças', 'resumo_anual.php' => 'Resumo anual',
      'responsaveis.php' => 'Pessoas', 'simulador.php' => 'Simulador', 'lista_desejos.php' => 'Lista de desejos',
      'gerenciamento.php' => 'Configurações', 'backup.php' => 'Backup'];
    $__tela = $__titulos[basename(parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH))] ?? '';
  ?>
  <title><?= $__tela ? htmlspecialchars($__tela) . ' · Sky Finance' : 'Sky Finance' ?></title>

  <!-- Favicon -->
  <link rel="icon" type="image/png" sizes="32x32" href="<?= BASE_URL ?>/src/img/favicon-32.png" />
  <link rel="icon" type="image/png" sizes="16x16" href="<?= BASE_URL ?>/src/img/favicon-16.png" />
  <link rel="apple-touch-icon" sizes="180x180" href="<?= BASE_URL ?>/src/img/favicon-180.png" />

  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />

  <!-- DataTables CSS -->
  <link href="https://cdn.datatables.net/2.2.2/css/dataTables.dataTables.min.css" rel="stylesheet" />

  <!-- Tippy.js CSS -->
  <link href="https://unpkg.com/tippy.js@6.3.1/dist/tippy.css" rel="stylesheet" />

  <!-- Notyf (avisos) — substitui o toastr; o adaptador abaixo mantém a API toastr.* -->
  <link href="https://cdn.jsdelivr.net/npm/notyf@3/notyf.min.css" rel="stylesheet" />

  <!-- SweetAlert2 CSS -->
  <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet" />

  <!-- Animate.css -->
  <link href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" rel="stylesheet" />

  <!-- Google Fonts -->
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&display=swap" rel="stylesheet" />

  <!-- Bootstrap Icons -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet" />

  <!-- Custom CSS -->
  <!-- ?v= muda quando o arquivo muda: sem isso o navegador segue com o CSS antigo em cache -->
  <link rel="stylesheet" href="<?= BASE_URL ?>/styles/style.css?v=<?= @filemtime(BASE_PATH . '/styles/style.css') ?>" />
  <!-- Repaginação (camada de design por cima do style.css) -->
  <link rel="stylesheet" href="<?= BASE_URL ?>/styles/ui.css?v=<?= @filemtime(BASE_PATH . '/styles/ui.css') ?>" />

  <?php
    // Tema da CONTA (não do navegador): cada usuário tem o seu.
    $temaUsuario = 'dark';
    try {
      $sTema = $conn->prepare("SELECT tema FROM usuarios WHERE id = ?");
      $sTema->execute([(int) ($_SESSION['usuario_id'] ?? 0)]);
      $t = $sTema->fetchColumn();
      if (in_array($t, ['light', 'gatos', 'astronautas'], true)) $temaUsuario = $t;
    } catch (Exception $e) {}
  ?>
  <!-- Tema: aplica antes do render para evitar flash -->
  <script>
    window.__skyTema = <?= json_encode($temaUsuario) ?>;
    (function () {
      var t = window.__skyTema;
      var h = document.documentElement;
      if (t === 'light') {
        h.classList.add('light-mode');
        h.setAttribute('data-bs-theme', 'light');
      } else if (t === 'gatos') {
        h.classList.add('tema-gatos');
      } else if (t === 'astronautas') {
        h.classList.add('tema-astronautas');
      }
    })();
  </script>

  <!-- jQuery -->
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

  <!-- Projeção de faturas (compartilhado entre Simulador e Lista de Desejos) -->
  <script src="<?= BASE_URL ?>/src/js/faturas.js?v=<?= @filemtime(BASE_PATH . '/src/js/faturas.js') ?>"></script>
  <!-- Movimento como resposta a ações (pulso no clique, contagem dos valores) -->
  <script src="<?= BASE_URL ?>/src/js/ui.js?v=<?= @filemtime(BASE_PATH . '/src/js/ui.js') ?>"></script>

  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

  <!-- DataTables JS -->
  <script src="https://cdn.datatables.net/2.2.2/js/dataTables.min.js"></script>

  <!-- Cleave.js -->
  <script src="https://cdn.jsdelivr.net/npm/cleave.js/dist/cleave.min.js"></script>

  <!-- Popper + Tippy -->
  <script src="https://unpkg.com/@popperjs/core@2/dist/umd/popper.min.js"></script>
  <script src="https://unpkg.com/tippy.js@6/dist/tippy-bundle.umd.js"></script>

  <!-- Moment.js -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.4/moment.min.js"></script>

  <!-- Notyf JS + adaptador "toastr": o sistema chama toastr.success/error/warning/info
       em ~180 lugares; o adaptador direciona para o Notyf sem mexer nessas chamadas. -->
  <script src="https://cdn.jsdelivr.net/npm/notyf@3/notyf.min.js"></script>
  <script>
  (function () {
    var notyf = null;
    function n() {
      if (notyf) return notyf;
      notyf = new Notyf({
        duration: 4200,
        ripple: false,
        dismissible: true,
        position: { x: 'right', y: 'bottom' },
        types: [
          { type: 'success', background: '#16A34A', icon: { className: 'bi bi-check-circle-fill', tagName: 'i', color: '#fff' } },
          { type: 'error',   background: '#DC2626', icon: { className: 'bi bi-x-octagon-fill', tagName: 'i', color: '#fff' }, duration: 6000 },
          { type: 'warning', background: '#D97706', icon: { className: 'bi bi-exclamation-triangle-fill', tagName: 'i', color: '#fff' } },
          { type: 'info',    background: '#2563EB', icon: { className: 'bi bi-info-circle-fill', tagName: 'i', color: '#fff' } }
        ]
      });
      return notyf;
    }
    function abre(tipo) { return function (msg) { return n().open({ type: tipo, message: String(msg == null ? '' : msg) }); }; }
    window.toastr = { success: abre('success'), error: abre('error'), warning: abre('warning'), info: abre('info'),
                      clear: function () { if (notyf) notyf.dismissAll(); }, options: {} };
  })();
  </script>

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
          usuarios:      '<?= CTRL_USUARIOS ?>',
          desejos:       '<?= CTRL_DESEJOS ?>'
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

  // ── Mês padrão de abertura: nunca anterior ao marco inicial ──
  // Recebe o mês/ano que a tela usaria por padrão (atual ou ajustado pela fatura)
  // e devolve o marco caso seja anterior — assim a tela abre onde há dados.
  window.mesInicialPadrao = function (mes, ano) {
      if (!window.App || !App.marcoInicio) return { mes: mes, ano: ano };
      var alvo = ('0000' + ano).slice(-4) + '-' + ('00' + mes).slice(-2) + '-01';
      if (alvo < App.marcoInicio) {
          return {
              mes: parseInt(App.marcoInicio.substring(5, 7), 10),
              ano: parseInt(App.marcoInicio.substring(0, 4), 10)
          };
      }
      return { mes: mes, ano: ano };
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

  // ── Seletor de cor padrão (paleta única + cor personalizada) ──────
  // Mesma paleta em todas as telas; o último swatch abre o seletor nativo.
  window.CORES_PADRAO = [
      '#3B82F6', '#8B5CF6', '#EC4899', '#EF4444', '#F97316', '#F59E0B',
      '#22C55E', '#10B981', '#06B6D4', '#14B8A6', '#84CC16', '#6B7280'
  ];

  // Monta o seletor dentro de `containerSel`, gravando a cor em `hiddenSel`.
  // Retorna (e guarda em .data('setCor')) a função setCor(cor) para abrir/editar.
  window.montaSeletorCor = function (containerSel, hiddenSel, onChange) {
      var $c = $(containerSel);
      if (!$c.length) return function () {};

      var html = window.CORES_PADRAO.map(function (cor) {
          return '<button type="button" class="cor-swatch" data-cor="' + cor +
                 '" style="background:' + cor + ';" title="' + cor + '"></button>';
      }).join('');
      html += '<label class="cor-swatch cor-swatch-custom" title="Cor personalizada">' +
              '<i class="bi bi-eyedropper"></i>' +
              '<input type="color">' +
              '</label>';
      $c.html(html);

      function setCor(cor) {
          cor = cor || '#3B82F6';
          $(hiddenSel).val(cor);
          $c.find('.cor-swatch').removeClass('selecionado');
          var $custom = $c.find('.cor-swatch-custom');
          var $preset = $c.find('.cor-swatch[data-cor="' + cor + '"]');
          if ($preset.length) {
              $preset.addClass('selecionado');
              $custom.css('background', '');           // volta ao gradiente
          } else {
              $custom.addClass('selecionado').css('background', cor);
              $custom.find('input[type="color"]').val(cor);
          }
          if (typeof onChange === 'function') onChange(cor);
      }

      $c.on('click', '.cor-swatch[data-cor]', function () { setCor($(this).data('cor')); });
      $c.on('input', '.cor-swatch-custom input[type="color"]', function () { setCor(this.value); });

      $c.data('setCor', setCor);
      return setCor;
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

      // Com texto selecionado, digitar substitui o valor (em vez de continuar dele)
      function temSelecao() {
          try { return el.selectionStart !== el.selectionEnd; } catch (_) { return false; }
      }

      el.addEventListener('keydown', function (e) {
          if (e.ctrlKey || e.metaKey || e.altKey) return; // Ctrl+A, Ctrl+C etc.
          if (e.key >= '0' && e.key <= '9') {
              e.preventDefault();
              if (temSelecao()) digits = '';
              if (digits.length < 13) digits += e.key;
              render();
          } else if (e.key === 'Backspace') {
              e.preventDefault();
              digits = temSelecao() ? '' : digits.slice(0, -1);
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

      var api = {
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
      // Expõe a API no próprio elemento para reset genérico ao fechar modais
      el._banc = api;
      el.classList.add('js-banc');
      // Celular abre o teclado numérico (os dígitos entram pelos centavos)
      el.setAttribute('inputmode', 'numeric');
      el.setAttribute('autocomplete', 'off');
      return api;
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

  // Ao fechar qualquer modal, zera todos os campos de valor (bancInput) dentro dela.
  // Assim, ao reabrir para um novo lançamento, o campo começa em "R$ 0,00" e não
  // mantém o valor digitado antes. Fluxos de edição preenchem o valor ao abrir
  // (depois deste reset), então não são afetados.
  $(document).on('hidden.bs.modal', '.modal', function () {
      this.querySelectorAll('.js-banc').forEach(function (el) {
          if (el._banc) el._banc.setValue(0);
      });
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
                  '<span class="resp-dot"></span>' + escHtml(r.nome) + '</div>';
      });
      $('#responsavelSelector').html(html);
  }

  // Marca a pessoa no modal de lançamento (edição/repetição). Chamar no
  // 'shown.bs.modal': o 'show' abaixo zera o campo para "Eu" a cada abertura.
  window.setResponsavelModal = function (id) {
      $('#responsavel').val(id ? String(id) : '');
      renderResponsaveisMiniModal();
      atualizaDivisao();
  };

  $(document).on('click', '.resp-chip', function () {
      $('.resp-chip').removeClass('selecionado');
      $(this).addClass('selecionado');
      $('#responsavel').val($(this).data('id'));
      atualizaDivisao();
  });

  // ── Despesa dividida ("Quanto é dela?") ──────────────────────────
  // modo: tudo (100 %) | metade (50 %) | outro (valor em R$ → % do total).
  // Gravado como % (responsavel_parte); vazio = tudo da pessoa.
  var _divModo = 'tudo', _divValor = null;
  function totalModal() {
      var el = document.getElementById('valor');
      return el && el._banc ? el._banc.getValue() : 0;
  }
  function parteDaPessoaValor() {
      var total = totalModal();
      if (_divModo === 'metade') return total / 2;
      if (_divModo === 'outro')  return _divValor ? _divValor.getValue() : 0;
      return total;
  }
  window.parteResponsavel = function () {
      if (!$('#responsavel').val() || $('#recorrente').is(':checked')) return '';
      if (_divModo === 'metade') return '50';
      if (_divModo === 'outro') {
          var total = totalModal(), v = parteDaPessoaValor();
          if (total > 0 && v > 0 && v < total) return String(Math.round(v / total * 1000000) / 10000);
      }
      return '';
  };
  function fmt(v) { return 'R$ ' + v.toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 }); }
  function atualizaResumoDivisao() {
      // Mesmo arredondamento do servidor (centavos); a sua parte é o resto
      var total = totalModal(), dela = Math.round(Math.min(parteDaPessoaValor(), total) * 100) / 100;
      var $r = $('#divisaoResumo');
      if (_divModo === 'tudo' || total <= 0) { $r.text(''); return; }
      if (_divModo === 'outro' && (dela <= 0 || dela >= total)) {
          $r.html('<span class="text-warning">Informe um valor menor que o total (' + fmt(total) + ').</span>');
          return;
      }
      $r.html('Dela: <strong>' + fmt(dela) + '</strong> · Sua parte: <strong>' + fmt(total - dela) + '</strong>');
  }
  function atualizaDivisao() {
      var id = $('#responsavel').val();
      var mostra = !!id && !$('#recorrente').is(':checked');
      var r = (window.responsaveisArray || []).find(function (x) { return String(x.id) === String(id); });
      $('#divisaoNome').text(r ? r.nome : 'da pessoa');
      $('#divisaoWrapper').toggle(mostra);
      atualizaResumoDivisao();
  }
  function setModoDivisao(modo) {
      _divModo = modo;
      $('.divisao-opcao').removeClass('selecionado').filter('[data-modo="' + modo + '"]').addClass('selecionado');
      $('#divisaoValorWrap').toggle(modo === 'outro');
      if (modo === 'outro') {
          if (!_divValor) _divValor = bancInput(document.getElementById('divisaoValor'));
          setTimeout(function () { $('#divisaoValor').trigger('focus'); }, 30);
      }
      atualizaResumoDivisao();
  }
  // Edição: recebe a % gravada e escolhe o modo (50 → Metade; outro → valor em R$)
  window.setParteModal = function (pct) {
      pct = parseFloat(pct);
      if (!pct || pct >= 100) { setModoDivisao('tudo'); }
      else if (Math.abs(pct - 50) < 0.001) { setModoDivisao('metade'); }
      else {
          setModoDivisao('outro');
          _divValor.setValue(Math.round(totalModal() * pct) / 100);
      }
      atualizaDivisao();
  };
  $(document).on('click', '.divisao-opcao', function () { setModoDivisao($(this).data('modo')); });
  $(document).on('keyup paste input', '#valor, #divisaoValor', function () { setTimeout(atualizaResumoDivisao, 0); });
  $(document).on('click', '.tipo-lanc-btn', function () { setTimeout(atualizaDivisao, 0); });

  // Sempre que o modal de lançamento abre: reset responsável + renderiza chips
  $(document).on('show.bs.modal', '#modalAdiciona', function () {
      $('#responsavel').val('');
      setModoDivisao('tudo');
      $('#divisaoWrapper').hide();
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
        ['href' => BASE_URL . '/php/views/lista_desejos.php',                      'label' => 'Desejos',      'icon' => 'bi-bag-heart-fill',      'match' => 'lista_desejos.php',  'match_q' => ''],
        ['href' => BASE_URL . '/php/views/gerenciamento.php',                      'label' => 'Config.',      'icon' => 'bi-gear-fill',           'match' => 'gerenciamento.php',  'match_q' => ''],
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
      <img src="<?= BASE_URL ?>/src/img/logo.png" alt="" class="brand-logo">
      <span>Sky Finance</span>
    </a>

    <!-- Links: barra no desktop; gaveta lateral no celular/tablet (< 992px) -->
    <div class="nav-drawer" id="navbarNav" aria-label="Menu principal">
      <div class="nav-drawer-topo">
        <span class="titulo">Menu</span>
        <button type="button" class="nav-drawer-fechar" id="navDrawerFechar" aria-label="Fechar menu">
          <i class="bi bi-x-lg"></i>
        </button>
      </div>
      <ul class="nav-sky">

        <?php $navRotulos = ['principal' => 'Visão geral', 'despesas' => 'Despesas', 'gestao' => 'Planejamento']; ?>
        <?php foreach ($navGrupos as $grupo => $links): ?>
          <li class="nav-grupo" aria-hidden="true"><?= $navRotulos[$grupo] ?? '' ?></li>

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

    </div>
    <!-- Usuário -->
    <?php
      $navFoto = null;
      try {
        $sNav = $conn->prepare("SELECT foto FROM usuarios WHERE id = ?");
        $sNav->execute([(int)($_SESSION['usuario_id'] ?? 0)]);
        $navFoto = $sNav->fetchColumn() ?: null;
      } catch (Exception $e) {}
    ?>
    <div class="dropdown nav-user-dropdown">
      <button class="nav-avatar nav-avatar-toggle" id="navUserDropdown"
              data-bs-toggle="dropdown" aria-expanded="false">
        <?php if ($navFoto): ?>
          <img src="<?= BASE_URL ?>/src/img/avatars/<?= htmlspecialchars($navFoto) ?>" alt="avatar" class="nav-avatar-img">
        <?php else: ?>
          <span><?= strtoupper(substr($_SESSION['usuario_nome'] ?? 'U', 0, 1)) ?></span>
        <?php endif; ?>
      </button>
      <!-- Nome ao lado do avatar (só na barra lateral); clicar abre o mesmo menu -->
      <button type="button" class="nav-user-nome"
              onclick="event.stopPropagation(); bootstrap.Dropdown.getOrCreateInstance(document.getElementById('navUserDropdown')).toggle();">
        <span><?= htmlspecialchars($_SESSION['usuario_nome'] ?? 'Usuário') ?></span>
        <i class="bi bi-three-dots-vertical"></i>
      </button>
      <ul class="dropdown-menu dropdown-menu-end nav-user-menu">
        <li class="nav-user-menu-header">
          <strong><?= htmlspecialchars($_SESSION['usuario_nome'] ?? 'Usuário') ?></strong>
        </li>
        <li><hr class="dropdown-divider"></li>
        <li>
          <a class="dropdown-item" href="<?= BASE_URL ?>/php/views/gerenciamento.php?tab=Conta">
            <i class="bi bi-person-gear me-2"></i>Minha conta
          </a>
        </li>
        <li class="tema-secao">
          <div class="tema-secao-titulo" id="temaSecaoTitulo">Aparência</div>
          <div class="tema-opcoes" role="radiogroup" aria-labelledby="temaSecaoTitulo">
            <button type="button" class="tema-opcao" role="radio" data-tema="dark" aria-checked="false">
              <span class="tema-amostra tema-amostra-dark"><i class="bi bi-moon-stars-fill"></i></span>
              <span class="tema-nome">Escuro</span>
            </button>
            <button type="button" class="tema-opcao" role="radio" data-tema="light" aria-checked="false">
              <span class="tema-amostra tema-amostra-light"><i class="bi bi-sun-fill"></i></span>
              <span class="tema-nome">Claro</span>
            </button>
            <button type="button" class="tema-opcao" role="radio" data-tema="gatos" aria-checked="false">
              <span class="tema-amostra tema-amostra-gatos"></span>
              <span class="tema-nome">Gatos espaciais</span>
            </button>
            <button type="button" class="tema-opcao" role="radio" data-tema="astronautas" aria-checked="false">
              <span class="tema-amostra tema-amostra-astronautas"></span>
              <span class="tema-nome">Gatos astronautas</span>
            </button>
          </div>
        </li>
        <li><hr class="dropdown-divider"></li>
        <li>
          <a class="dropdown-item text-danger" href="<?= BASE_URL ?>/logout.php">
            <i class="bi bi-box-arrow-right me-2"></i>Sair
          </a>
        </li>
      </ul>
    </div>
    <!-- Botão do menu (só < 992px) -->
    <button class="navbar-toggler-sky" type="button" id="navDrawerAbrir"
      aria-controls="navbarNav" aria-expanded="false" aria-label="Abrir menu">
      <i class="bi bi-list"></i>
    </button>
  </nav>
  <div class="nav-drawer-fundo" id="navDrawerFundo" aria-hidden="true"></div>
</header>

<script>
(function () {
  var html       = document.documentElement;
  var opcoes     = document.querySelectorAll('.tema-opcao');

  // Temas mutuamente exclusivos: 'dark' | 'light' | 'gatos' | 'astronautas'.
  // Os temas com padrão são escuros, então não combinam com o modo claro.
  function aplicaTema(tema) {
    html.classList.toggle('light-mode', tema === 'light');
    html.classList.toggle('tema-gatos', tema === 'gatos');
    html.classList.toggle('tema-astronautas', tema === 'astronautas');
    html.setAttribute('data-bs-theme', tema === 'light' ? 'light' : 'dark');

    // Miniatura do tema ativo fica marcada (✓ + borda)
    opcoes.forEach(function (o) {
      var ativo = o.getAttribute('data-tema') === tema;
      o.classList.toggle('ativo', ativo);
      o.setAttribute('aria-checked', ativo ? 'true' : 'false');
    });
    var cred = document.getElementById('creditoTema');
    if (cred) cred.style.display = tema === 'astronautas' ? '' : 'none';
  }

  function temaAtual() {
    var t = window.__skyTema;
    return (t === 'light' || t === 'gatos' || t === 'astronautas') ? t : 'dark';
  }

  // Salva na conta do usuário (servidor), não no navegador
  function salva(tema) {
    window.__skyTema = tema;
    aplicaTema(tema);
    try { localStorage.removeItem('skyTheme'); } catch (e) {}   // chave antiga, compartilhada entre contas
    if (window.jQuery && window.App) {
      jQuery.post(App.ctrl.usuarios, { acao: 'salvar_tema', tema: tema })
        .fail(function () { toastr.error('Não foi possível salvar o tema na sua conta. Tente de novo.'); });
    }
  }

  aplicaTema(temaAtual());

  // Clicar numa miniatura aplica na hora; o menu continua aberto para comparar
  opcoes.forEach(function (o) {
    o.addEventListener('click', function (e) {
      e.stopPropagation();
      var tema = o.getAttribute('data-tema');
      if (tema !== temaAtual()) salva(tema);
    });
  });
})();
</script>

<script>
// Gaveta do menu (celular/tablet): abre no ☰, fecha no X, no fundo, no Esc ou ao navegar
(function () {
  var body = document.body;
  var abrir = document.getElementById('navDrawerAbrir');
  function set(aberta) {
    body.classList.toggle('nav-aberta', aberta);
    if (abrir) abrir.setAttribute('aria-expanded', aberta ? 'true' : 'false');
  }
  if (abrir) abrir.addEventListener('click', function () { set(!body.classList.contains('nav-aberta')); });
  ['navDrawerFechar', 'navDrawerFundo'].forEach(function (id) {
    var el = document.getElementById(id);
    if (el) el.addEventListener('click', function () { set(false); });
  });
  document.addEventListener('keydown', function (e) { if (e.key === 'Escape') set(false); });
  document.querySelectorAll('#navbarNav .nav-link-sky').forEach(function (a) {
    a.addEventListener('click', function () { set(false); });
  });
  window.addEventListener('resize', function () { if (window.innerWidth >= 992) set(false); });
})();
</script>
<script>
// ── Calculadora para campos de valor ─────────────────────────────
// Botão .btn-calc[data-calc-alvo="#campo"] ao lado de um campo bancInput abre um
// painel com teclado. A expressão é avaliada por um parser próprio (sem eval):
// números no formato brasileiro (1.234,56), + − × ÷ e parênteses.
(function () {
  function calcula(expr) {
    var s = String(expr).replace(/\s+/g, '').replace(/×/g, '*').replace(/÷/g, '/').replace(/−/g, '-');
    var i = 0;
    function numero() {
      var m = s.slice(i).match(/^[\d.]*,?\d*/);
      var txt = m ? m[0] : '';
      if (!txt || txt === ',' ) throw 0;
      i += txt.length;
      var v = parseFloat(txt.replace(/\./g, '').replace(',', '.'));
      if (isNaN(v)) throw 0;
      return v;
    }
    function fator() {
      if (s[i] === '-') { i++; return -fator(); }
      if (s[i] === '+') { i++; return fator(); }
      if (s[i] === '(') { i++; var v = soma(); if (s[i] !== ')') throw 0; i++; return v; }
      return numero();
    }
    function produto() {
      var v = fator();
      while (s[i] === '*' || s[i] === '/') {
        var op = s[i++], d = fator();
        if (op === '/') { if (d === 0) throw 0; v /= d; } else v *= d;
      }
      return v;
    }
    function soma() {
      var v = produto();
      while (s[i] === '+' || s[i] === '-') { var op = s[i++]; v = op === '+' ? v + produto() : v - produto(); }
      return v;
    }
    if (!s) return null;
    try { var r = soma(); return i === s.length && isFinite(r) ? Math.round(r * 100) / 100 : null; }
    catch (e) { return null; }
  }
  window.calculaExpressao = calcula;

  function brl(v) { return v.toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 }); }

  var teclas = [
    ['C', 'limpa'], ['(', '('], [')', ')'], ['÷', '÷'],
    ['7', '7'], ['8', '8'], ['9', '9'], ['×', '×'],
    ['4', '4'], ['5', '5'], ['6', '6'], ['−', '−'],
    ['1', '1'], ['2', '2'], ['3', '3'], ['+', '+'],
    ['0', '0'], [',', ','], ['⌫', 'apaga'], ['=', 'igual']
  ];

  function monta(btn) {
    if (btn._calc) return btn._calc;
    var painel = document.createElement('div');
    painel.className = 'dropdown-menu dropdown-menu-end calc-painel';
    painel.innerHTML =
      '<input type="text" class="form-control calc-expr" inputmode="decimal" autocomplete="off" placeholder="Ex: 120 / 3">' +
      '<div class="calc-resultado"><span class="calc-res-label">=</span> <strong class="calc-res-valor">—</strong></div>' +
      '<div class="calc-teclas">' + teclas.map(function (t) {
        var cls = /^[÷×−+]$/.test(t[0]) ? ' calc-op' : (t[1] === 'igual' ? ' calc-igual' : (t[1] === 'limpa' || t[1] === 'apaga' ? ' calc-fn' : ''));
        return '<button type="button" class="calc-tecla' + cls + '" data-t="' + t[1] + '">' + t[0] + '</button>';
      }).join('') + '</div>' +
      '<button type="button" class="btn btn-success w-100 calc-usar"><i class="bi bi-check-lg me-1"></i>Usar valor</button>';
    btn.setAttribute('data-bs-toggle', 'dropdown');
    btn.setAttribute('data-bs-auto-close', 'outside');
    btn.insertAdjacentElement('afterend', painel);

    var expr = painel.querySelector('.calc-expr');
    var res  = painel.querySelector('.calc-res-valor');
    function atualiza() {
      var v = calcula(expr.value);
      res.textContent = v === null ? '—' : 'R$ ' + brl(v);
      res.classList.toggle('text-danger', v !== null && v < 0);
      return v;
    }
    function usa() {
      var v = atualiza();
      if (v === null) { toastr.warning('Conta inválida.'); return; }
      if (v <= 0) { toastr.warning('O resultado precisa ser maior que zero.'); return; }
      var alvo = document.querySelector(btn.getAttribute('data-calc-alvo'));
      if (alvo && alvo._banc) alvo._banc.setValue(v);
      bootstrap.Dropdown.getOrCreateInstance(btn).hide();
    }
    painel.addEventListener('click', function (e) {
      var t = e.target.closest('.calc-tecla');
      if (t) {
        var k = t.getAttribute('data-t');
        if (k === 'limpa') expr.value = '';
        else if (k === 'apaga') expr.value = expr.value.slice(0, -1);
        else if (k === 'igual') { var v = atualiza(); if (v !== null) expr.value = brl(v).replace(/\./g, ''); }
        else expr.value += k;
        atualiza();
        return;
      }
      if (e.target.closest('.calc-usar')) usa();
    });
    expr.addEventListener('input', atualiza);
    expr.addEventListener('keydown', function (e) {
      if (e.key === 'Enter') { e.preventDefault(); usa(); }
    });
    // Ao abrir: começa com o valor atual do campo
    btn.addEventListener('shown.bs.dropdown', function () {
      var alvo = document.querySelector(btn.getAttribute('data-calc-alvo'));
      var atual = alvo && alvo._banc ? alvo._banc.getValue() : 0;
      expr.value = atual > 0 ? brl(atual).replace(/\./g, '') : '';
      atualiza();
      if (window.matchMedia('(pointer: fine)').matches) { expr.focus(); expr.select(); }
    });
    btn._calc = painel;
    return painel;
  }

  // Monta antes do 1º clique (toque ou foco pelo teclado); daí em diante o
  // próprio Bootstrap abre/fecha pelo data-bs-toggle="dropdown".
  function prepara(e) {
    var b = e.target.closest && e.target.closest('.btn-calc');
    if (b && !b._calc) monta(b);
  }
  document.addEventListener('pointerdown', prepara, true);
  document.addEventListener('focusin', prepara, true);
})();
</script>
<script>
// Tabelas responsivas:
//  - table.tabela-cards: no celular cada linha vira um cartão; o rótulo de cada
//    célula (data-label) vem do cabeçalho da coluna. Preenchido automaticamente,
//    inclusive em tabelas montadas depois via AJAX/DataTables.
//  - .tabela-rolagem: rolagem horizontal com sombra indicando que há mais conteúdo.
(function () {
  function rotula(tabela) {
    var ths = tabela.querySelectorAll('thead th');
    if (!ths.length) return;
    var nomes = Array.prototype.map.call(ths, function (th) { return th.textContent.trim(); });
    tabela.querySelectorAll('tbody tr').forEach(function (tr) {
      Array.prototype.forEach.call(tr.children, function (td, i) {
        if (!td.hasAttribute('data-label')) td.setAttribute('data-label', nomes[i] || '');
      });
    });
  }
  // A sombra fica no pai (não rola junto com a tabela)
  function sombra(w) {
    var pai = w.parentElement;
    if (!pai) return;
    pai.classList.add('tabela-rolagem-pai');
    pai.classList.toggle('tem-mais-dir', w.scrollLeft + w.clientWidth < w.scrollWidth - 2);
  }
  function varre() {
    document.querySelectorAll('table.tabela-cards').forEach(rotula);
    document.querySelectorAll('.tabela-rolagem').forEach(function (w) {
      if (!w._rolagem) { w._rolagem = true; w.addEventListener('scroll', function () { sombra(w); }, { passive: true }); }
      sombra(w);
    });
  }
  var t = null;
  function agenda() { clearTimeout(t); t = setTimeout(varre, 60); }
  document.addEventListener('DOMContentLoaded', function () {
    varre();
    new MutationObserver(agenda).observe(document.body, { childList: true, subtree: true });
    window.addEventListener('resize', agenda);
  });
})();
</script>
<main>
  <div class="corpo-site">
