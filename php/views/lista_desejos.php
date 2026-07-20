<?php
require_once __DIR__ . '/../templates/header.php';
?>

<div class="animate__animated animate__fadeIn">

    <!-- HEADER -->
    <div class="d-flex align-items-center justify-content-between titulo-pagina mb-4 flex-wrap gap-2">
        <h1 class="titulo mt-2 fs-titulo-pag">
            Lista de Desejos &nbsp;<i class="bi bi-bag-heart-fill titulo-azul"></i>
        </h1>
        <button class="btn btn-primary btn-sm" id="btnNovoDesejo">
            <i class="bi bi-plus-lg me-1"></i>Adicionar
        </button>
    </div>

    <!-- RESUMO -->
    <div class="d-flex gap-3 flex-wrap mb-4" id="resumoDesejos"></div>

    <!-- LISTA -->
    <div id="gridDesejos" class="row g-3">
        <div class="text-center py-5 w-100">
            <div class="spinner-border" style="color:var(--cor-azul);" role="status"></div>
        </div>
    </div>

</div>

<!-- MODAL ADICIONAR / EDITAR -->
<div class="modal fade" id="modalDesejo" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header" style="background:#2C2C44;border-bottom:1px solid #3F3F46;">
        <h5 class="modal-title" style="color:#F0F0F5;"><i class="bi bi-bag-heart-fill titulo-azul me-2"></i><span id="modalDesejoTitulo">Novo desejo</span></h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body" style="padding:1.5rem;">
        <input type="hidden" id="desejoId">
        <input type="hidden" id="desejoImagemUrl">

        <div class="mb-3">
          <label class="form-label">Produto</label>
          <input type="text" class="form-control" id="desejoNome" placeholder="Ex.: Fone Bluetooth" maxlength="200">
        </div>

        <div class="mb-3">
          <label class="form-label">Link</label>
          <div class="input-group">
            <span class="input-group-text"><i class="bi bi-link-45deg"></i></span>
            <input type="url" class="form-control" id="desejoLink" placeholder="https://loja.com/produto">
            <button class="btn btn-outline-secondary" type="button" id="btnBuscarImagem" title="Buscar imagem do link">
              <i class="bi bi-image"></i>
            </button>
          </div>
          <div class="form-text">Ao salvar, a imagem do link é buscada automaticamente. Use o botão para pré-visualizar.</div>
        </div>

        <div class="mb-3">
          <label class="form-label">Imagem</label>
          <div class="d-flex gap-3 align-items-start">
            <div id="desejoPreviaWrap" style="width:88px;height:88px;border-radius:10px;overflow:hidden;flex-shrink:0;border:1px solid var(--cor-borda);background:rgba(0,0,0,.15);display:flex;align-items:center;justify-content:center;">
              <i class="bi bi-image text-muted" id="desejoPreviaVazia" style="font-size:1.6rem;"></i>
              <img id="desejoPrevia" src="" alt="" style="width:100%;height:100%;object-fit:cover;display:none;">
            </div>
            <div class="flex-grow-1">
              <input type="text" class="form-control form-control-sm mb-2" id="desejoUrlManual" placeholder="Colar URL da imagem">
              <input type="file" class="form-control form-control-sm" id="desejoArquivo" accept="image/jpeg,image/png,image/webp,image/gif">
              <div class="form-text">Se o link não trouxer imagem, cole uma URL ou envie um arquivo (até 5 MB).</div>
            </div>
          </div>
        </div>

        <div class="row g-2">
          <div class="col-7">
            <label class="form-label">Valor</label>
            <div class="input-group">
              <span class="input-group-text">R$</span>
              <input type="text" class="form-control" id="desejoValor" placeholder="0,00">
            </div>
          </div>
          <div class="col-5">
            <label class="form-label">Prioridade</label>
            <select class="form-select" id="desejoPrioridade">
              <option value="alta">Alta</option>
              <option value="media" selected>Média</option>
              <option value="baixa">Baixa</option>
            </select>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
        <button type="button" class="btn btn-primary" id="btnSalvarDesejo">
          <span class="spinner-border spinner-border-sm me-1 d-none" id="spinnerSalvarDesejo"></span>Salvar
        </button>
      </div>
    </div>
  </div>
</div>

<!-- MODAL SIMULAR FATURA -->
<div class="modal fade" id="modalSimular" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content">
      <div class="modal-header" style="background:#2C2C44;border-bottom:1px solid #3F3F46;">
        <h5 class="modal-title" style="color:#F0F0F5;"><i class="bi bi-calculator titulo-azul me-2"></i>Em qual fatura entra?</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body" style="padding:1.5rem;">
        <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
          <div>
            <div style="font-weight:600;font-size:1.05rem;" id="simDesejoNome"></div>
            <div class="titulo-azul" style="font-weight:700;font-size:1.2rem;" id="simDesejoValor"></div>
          </div>
          <div class="text-end">
            <label class="form-label mb-1" style="font-size:.78rem;">Mês da compra</label>
            <input type="month" class="form-control form-control-sm" id="simDesejoData" style="width:170px;">
          </div>
        </div>

        <div class="mb-3">
          <label class="form-label">Cartão</label>
          <div id="simDesejoCartoes" class="d-flex flex-wrap gap-2">
            <div class="text-center py-2" style="color:var(--cor-texto-off);font-size:.82rem;">
              <div class="spinner-border spinner-border-sm me-1" style="color:var(--cor-azul);"></div>Carregando cartões...
            </div>
          </div>
          <input type="hidden" id="simDesejoCartaoId">
          <input type="hidden" id="simDesejoFechamento">
        </div>

        <!-- Forma (igual ao modal de despesa) -->
        <div class="mb-3">
          <label class="form-label">Forma</label>
          <div class="d-flex gap-2">
            <button type="button" class="btn btn-outline-secondary tipo-lanc-btn active flex-fill" data-tipo="avista">
              <i class="bi bi-cash me-1"></i>À vista
            </button>
            <button type="button" class="btn btn-outline-secondary tipo-lanc-btn flex-fill" data-tipo="parcelado">
              <i class="bi bi-layout-split me-1"></i>Parcelado
            </button>
          </div>
          <input type="hidden" id="simDesejoTipo" value="avista">
        </div>

        <!-- Nº de parcelas (aparece quando parcelado) -->
        <div class="mb-3 sim-parcelas-wrap" style="display:none;">
          <label class="form-label">Nº de parcelas</label>
          <div class="d-flex align-items-center gap-3">
            <button type="button" class="btn btn-outline-secondary btn-sm px-3" id="simParcelasMenos"><i class="bi bi-dash-lg"></i></button>
            <span id="simParcelasDisplay" class="fs-5 fw-bold" style="min-width:2rem;text-align:center;">2</span>
            <button type="button" class="btn btn-outline-secondary btn-sm px-3" id="simParcelasMais"><i class="bi bi-plus-lg"></i></button>
            <span style="font-size:.82rem;color:var(--cor-texto-off);">parcelas</span>
          </div>
          <input type="hidden" id="simDesejoParcelas" value="2">
        </div>

        <div id="simDesejoResultado" style="display:none;">
          <hr>
          <div class="d-flex gap-3 flex-wrap mb-3" id="simDesejoResumo"></div>
          <div class="table-responsive">
            <table class="table table-sm align-middle mb-0">
              <thead>
                <tr style="font-size:.78rem;color:var(--cor-texto-off);">
                  <th>Fatura</th>
                  <th class="text-end">Fatura atual</th>
                  <th class="text-end">+ Esta compra</th>
                  <th class="text-end">Fatura projetada</th>
                  <th class="text-end" style="color:#10B981;">Gasto total do mês</th>
                </tr>
              </thead>
              <tbody id="simDesejoTabela"></tbody>
            </table>
          </div>
        </div>
        <div id="simDesejoLoading" class="text-center py-3" style="display:none;">
          <div class="spinner-border spinner-border-sm" style="color:var(--cor-azul);"></div>
          <span class="ms-2" style="color:var(--cor-texto-off);">Consultando faturas...</span>
        </div>
        <div id="simDesejoSemCartao" class="text-center py-3" style="display:none;color:var(--cor-texto-off);">
          <i class="bi bi-credit-card"></i> Cadastre um cartão de crédito para simular.
        </div>
      </div>
    </div>
  </div>
</div>

<script>
$(document).ready(function () {
  var URL_CTRL   = App.ctrl.desejos;
  var IMG_BASE   = App.base + '/src/img/';
  var modal      = new bootstrap.Modal(document.getElementById('modalDesejo'));
  var CORES_PRIO = { alta: '#EF4444', media: '#F59E0B', baixa: '#22C55E' };
  var NOME_PRIO  = { alta: 'Alta', media: 'Média', baixa: 'Baixa' };

  function moeda(v) {
    return (parseFloat(v) || 0).toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
  }

  // Resolve a imagem salva: URL externa (http) usa direto; caminho local ganha o prefixo.
  function urlImagem(img) {
    if (!img) return '';
    return /^https?:\/\//i.test(img) ? img : IMG_BASE + img;
  }

  // ─── LISTAR ──────────────────────────────────────────────
  function carregar() {
    $.ajax({
      type: 'POST', url: URL_CTRL, data: { acao: 'listar' }, dataType: 'json',
      success: function (r) { renderResumo(r.resumo); renderGrid(r.itens || []); },
      error: function () { $('#gridDesejos').html('<div class="text-center text-danger py-4 w-100">Erro ao carregar.</div>'); }
    });
  }

  function renderResumo(r) {
    if (!r) { $('#resumoDesejos').empty(); return; }
    var pills = [
      { icon: 'bi-hourglass-split', cor: '#F59E0B', label: 'A comprar',       valor: r.pendentes + ' item(ns)' },
      { icon: 'bi-cash-stack',      cor: '#3B82F6', label: 'Total pendente',  valor: 'R$ ' + moeda(r.total_pendente) },
      { icon: 'bi-check2-circle',   cor: '#22C55E', label: 'Já comprados',    valor: r.comprados + ' item(ns)' }
    ];
    $('#resumoDesejos').html(pills.map(function (p) {
      return '<div class="painel d-flex align-items-center gap-2" style="padding:.6rem 1rem;">' +
        '<i class="bi ' + p.icon + '" style="color:' + p.cor + ';font-size:1.2rem;"></i>' +
        '<div><div style="font-size:.72rem;color:var(--cor-texto-off);">' + p.label + '</div>' +
        '<div style="font-weight:700;">' + p.valor + '</div></div></div>';
    }).join(''));
  }

  function renderGrid(itens) {
    if (!itens.length) {
      $('#gridDesejos').html('<div class="text-center py-5 w-100" style="color:var(--cor-texto-off);">' +
        '<i class="bi bi-bag-heart" style="font-size:2.4rem;"></i><p class="mt-2 mb-0">Nenhum desejo ainda. Clique em <strong>Adicionar</strong>.</p></div>');
      return;
    }
    $('#gridDesejos').html(itens.map(function (it) {
      var comprado = it.comprado === 'S';
      var img = urlImagem(it.imagem);
      var imgHtml = img
        ? '<img src="' + escHtml(img) + '" alt="" style="width:100%;height:150px;object-fit:cover;" onerror="this.style.display=\'none\';this.nextElementSibling.style.display=\'flex\';">' +
          '<div style="width:100%;height:150px;align-items:center;justify-content:center;color:var(--cor-texto-off);display:none;"><i class="bi bi-image" style="font-size:2rem;"></i></div>'
        : '<div style="width:100%;height:150px;display:flex;align-items:center;justify-content:center;color:var(--cor-texto-off);"><i class="bi bi-image" style="font-size:2rem;"></i></div>';

      var linkBtn = it.link
        ? '<a href="' + escHtml(it.link) + '" target="_blank" rel="noopener" class="btn btn-sm btn-outline-primary" title="Abrir link"><i class="bi bi-box-arrow-up-right"></i></a>'
        : '';

      return '<div class="col-12 col-sm-6 col-lg-4 col-xl-3">' +
        '<div class="painel h-100 p-0" style="overflow:hidden;' + (comprado ? 'opacity:.6;' : '') + '">' +
          '<div style="position:relative;">' + imgHtml +
            '<span style="position:absolute;top:8px;left:8px;background:' + CORES_PRIO[it.prioridade] + '22;color:' + CORES_PRIO[it.prioridade] +
              ';border:1px solid ' + CORES_PRIO[it.prioridade] + '55;font-size:.7rem;font-weight:600;padding:2px 8px;border-radius:20px;">' + NOME_PRIO[it.prioridade] + '</span>' +
            (comprado ? '<span style="position:absolute;top:8px;right:8px;background:#22C55E;color:#fff;font-size:.7rem;font-weight:600;padding:2px 8px;border-radius:20px;"><i class="bi bi-check2 me-1"></i>Comprado</span>' : '') +
          '</div>' +
          '<div class="p-3">' +
            '<div style="font-weight:600;' + (comprado ? 'text-decoration:line-through;' : '') + '">' + escHtml(it.nome) + '</div>' +
            '<div class="titulo-azul" style="font-size:1.15rem;font-weight:700;margin:.25rem 0 .6rem;">R$ ' + moeda(it.valor) + '</div>' +
            '<div class="d-flex gap-1 flex-wrap">' +
              linkBtn +
              '<button class="btn btn-sm btn-outline-info btn-simular-desejo" data-id="' + it.id + '" title="Simular em qual fatura entra"><i class="bi bi-calculator"></i></button>' +
              '<button class="btn btn-sm btn-outline-success btn-comprado" data-id="' + it.id + '" title="' + (comprado ? 'Desmarcar' : 'Marcar como comprado') + '"><i class="bi ' + (comprado ? 'bi-arrow-counterclockwise' : 'bi-check2') + '"></i></button>' +
              '<button class="btn btn-sm btn-outline-secondary btn-editar-desejo" data-id="' + it.id + '" title="Editar"><i class="bi bi-pencil"></i></button>' +
              '<button class="btn btn-sm btn-outline-danger btn-remover-desejo" data-id="' + it.id + '" title="Remover"><i class="bi bi-trash3"></i></button>' +
            '</div>' +
          '</div>' +
        '</div>' +
      '</div>';
    }).join(''));

    window._desejos = itens;
  }

  // ─── PRÉVIA DE IMAGEM ────────────────────────────────────
  function setPrevia(url) {
    if (url) {
      $('#desejoPrevia').attr('src', url).show();
      $('#desejoPreviaVazia').hide();
      $('#desejoImagemUrl').val(url);
    } else {
      $('#desejoPrevia').attr('src', '').hide();
      $('#desejoPreviaVazia').show();
      $('#desejoImagemUrl').val('');
    }
  }

  // ─── NOVO / EDITAR ───────────────────────────────────────
  function abrirModal(item) {
    $('#desejoArquivo').val('');
    if (item) {
      $('#modalDesejoTitulo').text('Editar desejo');
      $('#desejoId').val(item.id);
      $('#desejoNome').val(item.nome);
      $('#desejoLink').val(item.link || '');
      $('#desejoValor').val(moeda(item.valor));
      $('#desejoPrioridade').val(item.prioridade);
      $('#desejoUrlManual').val('');
      var img = urlImagem(item.imagem);
      setPrevia(img);
      // Mantém a imagem existente no hidden mesmo que seja local.
      $('#desejoImagemUrl').val(item.imagem || '');
    } else {
      $('#modalDesejoTitulo').text('Novo desejo');
      $('#desejoId').val('');
      $('#desejoNome, #desejoLink, #desejoValor, #desejoUrlManual').val('');
      $('#desejoPrioridade').val('media');
      setPrevia('');
    }
    modal.show();
  }

  $('#btnNovoDesejo').click(function () { abrirModal(null); });

  $('#gridDesejos').on('click', '.btn-editar-desejo', function () {
    var id = String($(this).data('id'));
    var item = (window._desejos || []).find(function (x) { return String(x.id) === id; });
    if (item) abrirModal(item);
  });

  // Buscar imagem do link (prévia)
  $('#btnBuscarImagem').click(function () {
    var link = $('#desejoLink').val().trim();
    if (!link) { toastr.info('Cole o link primeiro.'); return; }
    var $b = $(this); $b.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span>');
    $.ajax({
      type: 'POST', url: URL_CTRL, data: { acao: 'buscarImagem', link: link }, dataType: 'json',
      success: function (r) {
        if (r.imagem) { setPrevia(r.imagem); toastr.success('Imagem encontrada!'); }
        else { toastr.warning('Não achei imagem nesse link. Cole uma URL ou envie um arquivo.'); }
      },
      error: function () { toastr.error('Erro ao buscar a imagem.'); },
      complete: function () { $b.prop('disabled', false).html('<i class="bi bi-image"></i>'); }
    });
  });

  // URL manual colada → prévia
  $('#desejoUrlManual').on('change', function () {
    var u = $(this).val().trim();
    if (u) setPrevia(u);
  });

  // Arquivo escolhido → prévia local
  $('#desejoArquivo').on('change', function () {
    var f = this.files && this.files[0];
    if (f) {
      var reader = new FileReader();
      reader.onload = function (e) {
        $('#desejoPrevia').attr('src', e.target.result).show();
        $('#desejoPreviaVazia').hide();
      };
      reader.readAsDataURL(f);
    }
  });

  // ─── SALVAR ──────────────────────────────────────────────
  $('#btnSalvarDesejo').click(function () {
    var nome = $('#desejoNome').val().trim();
    if (!nome) { toastr.warning('Informe o nome do produto.'); return; }

    var fd = new FormData();
    fd.append('acao', 'salvar');
    fd.append('id', $('#desejoId').val() || '0');
    fd.append('nome', nome);
    fd.append('link', $('#desejoLink').val().trim());
    fd.append('valor', $('#desejoValor').val().trim());
    fd.append('prioridade', $('#desejoPrioridade').val());
    // URL manual colada tem prioridade sobre a prévia buscada.
    var urlManual = $('#desejoUrlManual').val().trim();
    fd.append('imagem_url', urlManual || $('#desejoImagemUrl').val());
    var arq = $('#desejoArquivo')[0].files[0];
    if (arq) fd.append('imagem_arquivo', arq);

    $('#spinnerSalvarDesejo').removeClass('d-none');
    $('#btnSalvarDesejo').prop('disabled', true);
    $.ajax({
      type: 'POST', url: URL_CTRL, data: fd, processData: false, contentType: false, dataType: 'json',
      success: function (r) {
        if (r.ok) {
          if (r.aviso) toastr.warning(r.aviso); else toastr.success('Salvo!');
          modal.hide(); carregar();
        } else { toastr.error(r.erro || 'Erro ao salvar.'); }
      },
      error: function (xhr) { toastr.error((xhr.responseJSON && xhr.responseJSON.erro) || 'Erro ao salvar.'); },
      complete: function () {
        $('#spinnerSalvarDesejo').addClass('d-none');
        $('#btnSalvarDesejo').prop('disabled', false);
      }
    });
  });

  // ─── COMPRADO ────────────────────────────────────────────
  $('#gridDesejos').on('click', '.btn-comprado', function () {
    var id = $(this).data('id');
    $.ajax({
      type: 'POST', url: URL_CTRL, data: { acao: 'comprado', id: id }, dataType: 'json',
      success: function () { carregar(); },
      error: function () { toastr.error('Erro.'); }
    });
  });

  // ─── REMOVER ─────────────────────────────────────────────
  $('#gridDesejos').on('click', '.btn-remover-desejo', function () {
    var id = $(this).data('id');
    Swal.fire({
      title: 'Remover da lista?', icon: 'warning', showCancelButton: true,
      confirmButtonColor: '#EF4444', confirmButtonText: 'Remover', cancelButtonText: 'Cancelar'
    }).then(function (r) {
      if (!r.isConfirmed) return;
      $.ajax({
        type: 'POST', url: URL_CTRL, data: { acao: 'remover', id: id }, dataType: 'json',
        success: function () { toastr.success('Removido.'); carregar(); },
        error: function () { toastr.error('Erro ao remover.'); }
      });
    });
  });

  // ─── SIMULAR FATURA ──────────────────────────────────────
  var simModal   = new bootstrap.Modal(document.getElementById('modalSimular'));
  var simCartoes = null;   // cache dos cartões
  var simValorAtual = 0;

  // Só o mês importa na simulação (YYYY-MM). O dia exato é escolhido na hora de
  // adicionar a despesa de verdade; aqui assumimos o dia 1 para o cálculo.
  function mesAtualISO() {
    var d = new Date();
    return d.getFullYear() + '-' + String(d.getMonth() + 1).padStart(2, '0');
  }

  function carregarCartoesSim(cb) {
    if (simCartoes) { cb(); return; }
    $.ajax({
      type: 'POST', url: App.ctrl.cartoes, data: { acao: 'busca' }, dataType: 'json',
      success: function (data) { simCartoes = data || {}; cb(); },
      error: function () { simCartoes = {}; cb(); }
    });
  }

  function renderChipsSim() {
    var ids = Object.keys(simCartoes || {});
    if (!ids.length) {
      $('#simDesejoCartoes').empty();
      $('#simDesejoSemCartao').show();
      return;
    }
    $('#simDesejoSemCartao').hide();
    $('#simDesejoCartoes').html(ids.map(function (id, i) {
      var c = simCartoes[id];
      var cor = c.cor || '#3B82F6';
      return '<div class="sim-cartao-chip' + (i === 0 ? ' selecionado' : '') + '" data-id="' + c.id +
        '" data-fechamento="' + (c.fechamento_dia || 1) + '" style="--chip-cor:' + cor + ';cursor:pointer;">' +
        '<span class="sim-chip-dot" style="background:' + cor + ';"></span>' + escHtml(c.nome_cartao) + '</div>';
    }).join(''));
    // seleciona o primeiro por padrão
    var first = simCartoes[ids[0]];
    $('#simDesejoCartaoId').val(first.id);
    $('#simDesejoFechamento').val(first.fechamento_dia || 1);
  }

  function projetarSim() {
    var cartaoId = $('#simDesejoCartaoId').val();
    if (!cartaoId || !simValorAtual) return;
    var tipo = $('#simDesejoTipo').val();
    var n    = tipo === 'avista' ? 1 : (parseInt($('#simDesejoParcelas').val(), 10) || 1);

    $('#simDesejoResultado').hide();
    $('#simDesejoLoading').show();

    FaturasSim.projetar({
      valor: simValorAtual, tipo: tipo, numParcelas: n,
      dataStr: ($('#simDesejoData').val() || mesAtualISO()) + '-01',
      fechamento: parseInt($('#simDesejoFechamento').val(), 10) || 1,
      cartaoId: cartaoId
    }, function (linhas) {
      $('#simDesejoLoading').hide();
      renderProjecaoSim(linhas);
      $('#simDesejoResultado').fadeIn(150);
    });
  }

  function renderProjecaoSim(linhas) {
    var mesesNomes = ['Jan','Fev','Mar','Abr','Mai','Jun','Jul','Ago','Set','Out','Nov','Dez'];
    var ultima = linhas[linhas.length - 1];
    var parcelaVlr = linhas[0].parcela;

    var resumo = [
      { icon: 'bi-layout-split',   cor: '#3B82F6', label: 'Parcelas',       valor: linhas.length + 'x de R$ ' + moeda(parcelaVlr) },
      { icon: 'bi-calendar-check', cor: '#F59E0B', label: 'Quita em',       valor: mesesNomes[ultima.mes - 1] + '/' + ultima.ano }
    ];
    $('#simDesejoResumo').html(resumo.map(function (p) {
      return '<div class="painel d-flex align-items-center gap-2" style="padding:.5rem .9rem;">' +
        '<i class="bi ' + p.icon + '" style="color:' + p.cor + ';font-size:1.1rem;"></i>' +
        '<div><div style="font-size:.7rem;color:var(--cor-texto-off);">' + p.label + '</div>' +
        '<div style="font-weight:700;font-size:.9rem;">' + p.valor + '</div></div></div>';
    }).join(''));

    $('#simDesejoTabela').html(linhas.map(function (l) {
      var label = l.total > 1 ? 'Parcela ' + l.idx + '/' + l.total : 'À vista';
      return '<tr' + (l.ultima ? ' style="background:rgba(59,130,246,.08);"' : '') + '>' +
        '<td><div style="font-weight:600;">' + mesesNomes[l.mes - 1] + '/' + l.ano + '</div>' +
          '<div style="font-size:.72rem;color:var(--cor-texto-off);">' + label +
          (l.ultima ? ' <i class="bi bi-flag-fill" style="color:var(--cor-azul);"></i>' : '') + '</div></td>' +
        '<td class="text-end" style="color:var(--cor-texto-off);">R$ ' + moeda(l.faturaAtual) + '</td>' +
        '<td class="text-end" style="color:#F59E0B;">+ R$ ' + moeda(l.parcela) + '</td>' +
        '<td class="text-end" style="font-weight:700;">R$ ' + moeda(l.novoTotal) + '</td>' +
        '<td class="text-end" style="font-weight:700;color:#10B981;">R$ ' + moeda(l.gastoTotalMes) + '</td>' +
        '</tr>';
    }).join(''));
  }

  $('#gridDesejos').on('click', '.btn-simular-desejo', function () {
    var id = String($(this).data('id'));
    var item = (window._desejos || []).find(function (x) { return String(x.id) === id; });
    if (!item) return;
    simValorAtual = parseFloat(item.valor) || 0;
    if (!simValorAtual) { toastr.info('Defina um valor no item para simular.'); return; }

    $('#simDesejoNome').text(item.nome);
    $('#simDesejoValor').text('R$ ' + moeda(simValorAtual));
    $('#simDesejoData').val(mesAtualISO());
    // reseta a forma para "à vista"
    $('#modalSimular .tipo-lanc-btn').removeClass('active');
    $('#modalSimular .tipo-lanc-btn[data-tipo="avista"]').addClass('active');
    $('#simDesejoTipo').val('avista');
    $('#simDesejoParcelas').val(2);
    $('#simParcelasDisplay').text(2);
    $('.sim-parcelas-wrap').hide();
    $('#simDesejoResultado').hide();

    carregarCartoesSim(function () {
      renderChipsSim();
      simModal.show();
      if ($('#simDesejoCartaoId').val()) projetarSim();
    });
  });

  // trocar cartão
  $('#simDesejoCartoes').on('click', '.sim-cartao-chip', function () {
    $('#simDesejoCartoes .sim-cartao-chip').removeClass('selecionado');
    $(this).addClass('selecionado');
    $('#simDesejoCartaoId').val($(this).data('id'));
    $('#simDesejoFechamento').val($(this).data('fechamento'));
    projetarSim();
  });

  // trocar forma (à vista / parcelado) — botões estilo modal de despesa
  $('#modalSimular').on('click', '.tipo-lanc-btn', function () {
    $('#modalSimular .tipo-lanc-btn').removeClass('active');
    $(this).addClass('active');
    var tipo = $(this).data('tipo');
    $('#simDesejoTipo').val(tipo);
    $('.sim-parcelas-wrap').toggle(tipo === 'parcelado');
    projetarSim();
  });

  // stepper de parcelas (mín. 2 quando parcelado)
  function ajustaParcelas(n) {
    n = Math.max(2, Math.min(48, n));
    $('#simDesejoParcelas').val(n);
    $('#simParcelasDisplay').text(n);
    projetarSim();
  }
  $('#simParcelasMenos').click(function () { ajustaParcelas((parseInt($('#simDesejoParcelas').val(), 10) || 2) - 1); });
  $('#simParcelasMais').click(function () { ajustaParcelas((parseInt($('#simDesejoParcelas').val(), 10) || 2) + 1); });

  $('#simDesejoData').on('change', projetarSim);

  carregar();
});
</script>

<?php require_once __DIR__ . '/../templates/footer.php'; ?>
