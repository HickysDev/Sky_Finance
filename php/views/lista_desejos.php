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
    <div class="d-flex gap-3 flex-wrap mb-3" id="resumoDesejos"></div>

    <!-- VITRINE: busca, ordenação e filtros -->
    <div class="vitrine-barra painel mb-3">
        <div class="vitrine-busca">
            <i class="bi bi-search"></i>
            <input type="search" class="form-control" id="vitBusca" placeholder="Buscar na lista de desejos..." autocomplete="off">
        </div>
        <div class="d-flex align-items-center gap-2 flex-wrap vitrine-controles">
            <select class="form-select form-select-sm" id="vitOrdem" title="Ordenar">
                <option value="prioridade">Prioridade</option>
                <option value="menor">Menor preço</option>
                <option value="maior">Maior preço</option>
                <option value="recentes">Mais recentes</option>
                <option value="nome">Nome (A–Z)</option>
            </select>
            <div class="form-check form-switch mb-0" title="Mostrar os já comprados">
                <input class="form-check-input" type="checkbox" id="vitComprados" checked>
                <label class="form-check-label" for="vitComprados" style="font-size:.82rem;">Comprados</label>
            </div>
            <button type="button" class="btn btn-sm btn-outline-secondary" id="btnSelTodos">
                <i class="bi bi-check2-all me-1"></i>Selecionar todos
            </button>
        </div>
    </div>

    <!-- Categorias (chips roláveis) -->
    <div class="vitrine-categorias mb-3" id="vitCategorias"></div>

    <div class="d-flex justify-content-between align-items-center mb-2" id="vitInfo">
        <span id="vitContagem" style="font-size:.8rem;color:var(--cor-texto-off);"></span>
    </div>

    <div id="gridDesejos" class="row g-3">
        <div class="text-center py-5 w-100">
            <div class="spinner-border" style="color:var(--cor-azul);" role="status"></div>
        </div>
    </div>

    <!-- Barra de ações da seleção múltipla -->
    <div id="barraSelDesejos" class="barra-selecao barra-sel-desejos" style="display:none;">
        <div class="d-flex align-items-center gap-2 me-2">
            <i class="bi bi-check2-square" style="color:var(--cor-azul);"></i>
            <span><strong id="selQtd">0</strong> <span id="selQtdLabel">selecionado</span> · <strong id="selTotal">R$ 0,00</strong></span>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <button type="button" class="btn btn-sm btn-outline-info" id="btnSelSimular"><i class="bi bi-calculator me-1"></i>Simular juntos</button>
            <button type="button" class="btn btn-sm btn-success" id="btnSelComprar"><i class="bi bi-bag-check me-1"></i>Comprar</button>
            <button type="button" class="btn btn-sm btn-outline-danger" id="btnSelRemover"><i class="bi bi-trash3 me-1"></i>Remover</button>
            <button type="button" class="btn btn-sm btn-outline-secondary" id="btnSelLimpar" title="Limpar seleção"><i class="bi bi-x-lg"></i></button>
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

        <div class="mb-3">
          <label class="form-label">Categoria</label>
          <div class="input-group">
            <span class="input-group-text"><i class="bi bi-tag-fill"></i></span>
            <div class="form-control p-0 cat-sel-container dropdown">
              <button type="button" id="desejoCatSelBtn"
                      class="btn cat-sel-btn w-100 h-100 d-flex align-items-center gap-2"
                      data-bs-toggle="dropdown" aria-expanded="false">
                <span class="cat-sel-preview text-muted">Sem categoria</span>
                <i class="bi bi-chevron-down ms-auto" style="font-size:0.75rem;opacity:0.6;"></i>
              </button>
              <ul class="dropdown-menu cat-sel-menu" id="desejoCatSelMenu"></ul>
            </div>
          </div>
          <input type="hidden" id="desejoCategoria">
          <div class="form-text">As mesmas categorias das despesas (gerencie em Config. → Categorias).</div>
        </div>

        <div class="row g-2">
          <div class="col-7">
            <label class="form-label">Valor</label>
            <input type="text" class="form-control" id="desejoValor" inputmode="numeric">
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

<!-- MODAL CHECKOUT: carrinho → pagamento. Cada item vira uma despesa. -->
<div class="modal fade" id="modalComprar" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title"><i class="bi bi-bag-check-fill titulo-azul me-2"></i>Finalizar compra</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <!-- Etapas -->
        <div class="checkout-etapas mb-3">
          <div class="checkout-etapa ativa" data-etapa="1"><span>1</span>Carrinho</div>
          <div class="checkout-linha"></div>
          <div class="checkout-etapa" data-etapa="2"><span>2</span>Pagamento</div>
        </div>

        <!-- ETAPA 1: CARRINHO -->
        <div id="checkoutPasso1">
          <div id="checkoutItens" class="checkout-itens"></div>
          <div class="checkout-subtotal">
            <span>Subtotal (<span id="checkoutQtd">0</span> <span id="checkoutQtdLabel">item</span>)</span>
            <strong id="checkoutSubtotal">R$ 0,00</strong>
          </div>

          <!-- Frete e cupom (opcionais) -->
          <div class="row g-2 mt-1 checkout-extras">
            <div class="col-6">
              <label class="form-label mb-1" style="font-size:.8rem;"><i class="bi bi-truck me-1"></i>Frete</label>
              <input type="text" class="form-control" id="checkoutFrete" inputmode="numeric">
            </div>
            <div class="col-6">
              <label class="form-label mb-1" style="font-size:.8rem;"><i class="bi bi-ticket-perforated me-1"></i>Cupom de desconto</label>
              <div class="input-group">
                <input type="text" class="form-control" id="checkoutDesconto" inputmode="numeric">
                <button type="button" class="btn btn-outline-secondary" id="checkoutDescModo" title="Alternar entre R$ e %">R$</button>
              </div>
            </div>
          </div>
          <div class="checkout-total-final">
            <span>Total a pagar</span>
            <strong id="checkoutTotalFinal">R$ 0,00</strong>
          </div>
          <div class="form-text" id="checkoutDica">Ajuste nome, categoria e o valor que você realmente pagou em cada item. Cada um vira uma despesa.</div>

          <!-- Só com 2+ itens: lançar separado ou numa despesa só -->
          <div id="checkoutAgruparWrap" class="checkout-agrupar mt-3" style="display:none;">
            <label class="form-label mb-2">Como lançar nas despesas?</label>
            <div class="d-flex gap-2 flex-wrap">
              <button type="button" class="divisao-opcao checkout-modo selecionado" data-modo="separado"><i class="bi bi-list-ul me-1"></i>Uma despesa por item</button>
              <button type="button" class="divisao-opcao checkout-modo" data-modo="agrupado"><i class="bi bi-box-seam me-1"></i>Tudo numa despesa só</button>
            </div>
            <div id="checkoutAgrupadoCampos" class="row g-2 mt-1" style="display:none;">
              <div class="col-12 col-sm-7">
                <label class="form-label mb-1" style="font-size:.8rem;">Nome da despesa</label>
                <input type="text" class="form-control" id="checkoutNomeGrupo" maxlength="255" placeholder="Ex.: Compras Shopee">
              </div>
              <div class="col-12 col-sm-5">
                <label class="form-label mb-1" style="font-size:.8rem;">Categoria</label>
                <div class="dropdown checkout-cat" id="checkoutCatGrupo">
                  <button type="button" class="btn cat-sel-btn w-100 d-flex align-items-center gap-1" data-bs-toggle="dropdown" aria-expanded="false" style="min-height:44px;">
                    <span class="cat-sel-preview d-flex align-items-center text-truncate"></span>
                    <i class="bi bi-chevron-down ms-auto" style="font-size:.7rem;opacity:.6;"></i></button>
                  <ul class="dropdown-menu cat-sel-menu"></ul>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- ETAPA 2: PAGAMENTO -->
        <div id="checkoutPasso2" style="display:none;">
          <div class="row g-3">
            <div class="col-12 col-md-7">
              <div class="mb-3">
                <label class="form-label">Forma de pagamento</label>
                <div class="d-flex gap-2 flex-wrap">
                  <button type="button" class="btn btn-outline-secondary compra-forma flex-fill active" data-forma="Crédito"><i class="bi bi-credit-card me-1"></i>Crédito</button>
                  <button type="button" class="btn btn-outline-secondary compra-forma flex-fill" data-forma="Pix"><i class="bi bi-qr-code me-1"></i>Pix</button>
                  <button type="button" class="btn btn-outline-secondary compra-forma flex-fill" data-forma="Débito"><i class="bi bi-credit-card-2-front me-1"></i>Débito</button>
                  <button type="button" class="btn btn-outline-secondary compra-forma flex-fill" data-forma="Dinheiro"><i class="bi bi-cash me-1"></i>Dinheiro</button>
                </div>
              </div>

              <div id="compraCreditoWrap">
                <div class="mb-3">
                  <label class="form-label">Cartão</label>
                  <div id="compraCartoes" class="d-flex flex-wrap gap-2"></div>
                </div>
                <div class="mb-3">
                  <label class="form-label">Pagamento no cartão</label>
                  <div class="d-flex gap-2 flex-wrap align-items-center">
                    <button type="button" class="btn btn-outline-secondary compra-parc flex-fill active" data-parc="N"><i class="bi bi-cash me-1"></i>À vista</button>
                    <button type="button" class="btn btn-outline-secondary compra-parc flex-fill" data-parc="S"><i class="bi bi-layout-split me-1"></i>Parcelado</button>
                  </div>
                  <div class="d-flex align-items-center gap-3 mt-2" id="compraParcelasWrap" style="display:none !important;">
                    <button type="button" class="btn btn-outline-secondary btn-sm px-3" id="compraParcMenos"><i class="bi bi-dash-lg"></i></button>
                    <span id="compraParcDisplay" class="fs-5 fw-bold" style="min-width:2rem;text-align:center;">2</span>
                    <button type="button" class="btn btn-outline-secondary btn-sm px-3" id="compraParcMais"><i class="bi bi-plus-lg"></i></button>
                    <span style="font-size:.82rem;color:var(--cor-texto-off);" id="compraParcInfo"></span>
                  </div>
                </div>
              </div>

              <div class="mb-3">
                <label class="form-label">Data da compra</label>
                <input type="date" class="form-control" id="compraData">
              </div>

              <div class="mb-1">
                <label class="form-label"><i class="bi bi-person-fill me-1" style="color:var(--cor-azul);"></i>Dividir com alguém?</label>
                <div class="d-flex gap-2 flex-wrap" id="compraPessoas"></div>
                <div id="compraDivisaoWrap" class="divisao-wrap mt-2" style="display:none;">
                  <div class="divisao-titulo">Quanto é <span id="compraDivNome"></span>?</div>
                  <div class="d-flex gap-2 flex-wrap align-items-center">
                    <button type="button" class="divisao-opcao compra-div selecionado" data-modo="tudo">Tudo</button>
                    <button type="button" class="divisao-opcao compra-div" data-modo="metade">Metade</button>
                    <button type="button" class="divisao-opcao compra-div" data-modo="outro">Outro valor</button>
                    <div class="input-group divisao-valor" id="compraDivValorWrap" style="display:none;">
                      <span class="input-group-text">dela</span>
                      <input type="text" class="form-control" id="compraDivValor">
                    </div>
                  </div>
                  <div class="divisao-resumo" id="compraDivResumo"></div>
                </div>
              </div>
            </div>

            <!-- Resumo do pedido -->
            <div class="col-12 col-md-5">
              <div class="checkout-resumo">
                <div class="checkout-resumo-titulo">Resumo do pedido</div>
                <div id="checkoutResumoItens"></div>
                <div class="checkout-resumo-linha total"><span>Total</span><strong id="checkoutResumoTotal">R$ 0,00</strong></div>
                <div class="checkout-resumo-pag" id="checkoutResumoPag"></div>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline-secondary" id="btnCheckoutVoltar" style="display:none;"><i class="bi bi-arrow-left me-1"></i>Voltar</button>
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal" id="btnCheckoutCancelar">Cancelar</button>
        <button type="button" class="btn btn-primary" id="btnCheckoutContinuar">Continuar para pagamento<i class="bi bi-arrow-right ms-1"></i></button>
        <button type="button" class="btn btn-success" id="btnConfirmarCompra" style="display:none;">
          <span class="spinner-border spinner-border-sm me-1 d-none" id="spinnerCompra"></span><i class="bi bi-bag-check me-1"></i>Confirmar compra
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
            <div id="simDesejoItens" style="font-size:.78rem;color:var(--cor-texto-off);display:none;"></div>
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
            <table class="table table-sm align-middle mb-0 tabela-cards">
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

  // Mesma máscara monetária das outras telas: dígitos entram pelos centavos.
  var valorDesejo = bancInput('#desejoValor');

  function moeda(v) {
    return (parseFloat(v) || 0).toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
  }

  // Resolve a imagem salva: URL externa (http) usa direto; caminho local ganha o prefixo.
  function urlImagem(img) {
    if (!img) return '';
    return /^https?:\/\//i.test(img) ? img : IMG_BASE + img;
  }

  // ─── LISTAR ──────────────────────────────────────────────
  // Categorias do sistema (para o cadastro e para os chips)
  var _categorias = [];
  function carregarCategorias() {
    $.ajax({
      type: 'POST', url: App.ctrl.categoria, data: { acao: 'busca' }, dataType: 'json',
      success: function (data) {
        _categorias = data || [];
        // Mesmo seletor dos outros modais: bolinha de cor + ícone + nome
        $('#desejoCatSelMenu').html(
          '<li><a class="dropdown-item text-muted py-2" href="#" data-id="">Sem categoria</a></li>' +
          '<li><hr class="dropdown-divider m-0"></li>' +
          _categorias.map(function (c) {
            var cor = c.cor || '#6B7280';
            return '<li><a class="dropdown-item d-flex align-items-center gap-2" href="#" data-id="' + c.id + '">' +
              '<span class="cat-dot" style="background:' + cor + ';flex-shrink:0;"></span>' +
              (c.icone ? '<span class="me-1">' + escHtml(c.icone) + '</span>' : '') +
              '<span style="color:' + cor + ';">' + escHtml(c.nome) + '</span></a></li>';
          }).join(''));
        setCatDesejo($('#desejoCategoria').val());
      }
    });
  }

  // Define a categoria do modal (hidden + prévia no botão)
  function setCatDesejo(id) {
    id = id ? String(id) : '';
    $('#desejoCategoria').val(id);
    var c = _categorias.find(function (x) { return String(x.id) === id; });
    $('#desejoCatSelBtn .cat-sel-preview').html(c
      ? '<span class="cat-dot" style="background:' + (c.cor || '#6B7280') + ';flex-shrink:0;"></span>' +
        (c.icone ? '<span class="ms-1">' + escHtml(c.icone) + '</span>' : '') +
        '<span class="ms-1" style="color:' + (c.cor || '#6B7280') + ';">' + escHtml(c.nome) + '</span>'
      : '<span class="text-muted">Sem categoria</span>');
  }
  $(document).on('click', '#desejoCatSelMenu a', function (e) {
    e.preventDefault();
    setCatDesejo($(this).data('id'));
    var dd = bootstrap.Dropdown.getInstance(document.getElementById('desejoCatSelBtn'));
    if (dd) dd.hide();
  });

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

  // ─── VITRINE ─────────────────────────────────────────────
  var _filtroCat = '';          // '' = todas, '0' = sem categoria, id
  var _busca = '';
  var ORDEM_KEY = 'skyOrdemDesejos';
  try { $('#vitOrdem').val(localStorage.getItem(ORDEM_KEY) || 'prioridade'); } catch (e) {}
  var PESO_PRIO = { alta: 0, media: 1, baixa: 2 };

  function semAcento(t) { return String(t || '').normalize('NFD').replace(/[\u0300-\u036f]/g, '').toLowerCase(); }

  function itensVisiveis() {
    var todos = window._desejos || [];
    var mostraComprados = $('#vitComprados').is(':checked');
    var busca = semAcento(_busca.trim());
    var lista = todos.filter(function (it) {
      if (!mostraComprados && it.comprado === 'S') return false;
      if (_filtroCat === '0' && it.categoria_id) return false;
      if (_filtroCat && _filtroCat !== '0' && String(it.categoria_id) !== _filtroCat) return false;
      if (busca && semAcento(it.nome + ' ' + (it.categoria || '')).indexOf(busca) === -1) return false;
      return true;
    });
    var ordem = $('#vitOrdem').val();
    var v = function (x) { return parseFloat(x.valor) || 0; };
    lista.sort(function (a, b) {
      // Comprados sempre no fim
      if (a.comprado !== b.comprado) return a.comprado === 'S' ? 1 : -1;
      if (ordem === 'menor') return v(a) - v(b);
      if (ordem === 'maior') return v(b) - v(a);
      if (ordem === 'recentes') return String(b.created_at).localeCompare(String(a.created_at));
      if (ordem === 'nome') return a.nome.localeCompare(b.nome, 'pt-BR', { sensitivity: 'base' });
      return (PESO_PRIO[a.prioridade] - PESO_PRIO[b.prioridade]) || String(b.created_at).localeCompare(String(a.created_at));
    });
    return lista;
  }

  function renderCategorias() {
    var todos = window._desejos || [];
    var cont = {}, semCat = 0;
    todos.forEach(function (it) { if (it.categoria_id) cont[it.categoria_id] = (cont[it.categoria_id] || 0) + 1; else semCat++; });
    var chips = [{ id: '', nome: 'Todas', icone: '', cor: '#3B82F6', n: todos.length }];
    // Só categorias com itens, na ordem de quantidade
    Object.keys(cont).sort(function (a, b) { return cont[b] - cont[a]; }).forEach(function (id) {
      var it = todos.find(function (x) { return String(x.categoria_id) === id; });
      chips.push({ id: id, nome: it.categoria || 'Categoria', icone: it.categoria_icone || '', cor: it.categoria_cor || '#6B7280', n: cont[id] });
    });
    if (semCat && Object.keys(cont).length) chips.push({ id: '0', nome: 'Sem categoria', icone: '', cor: '#6B7280', n: semCat });
    if (_filtroCat && !chips.some(function (c) { return c.id === _filtroCat; })) _filtroCat = '';
    $('#vitCategorias').html(chips.map(function (c) {
      return '<button type="button" class="vit-chip' + (c.id === _filtroCat ? ' ativo' : '') + '" data-cat="' + c.id + '" style="--chip:' + c.cor + ';">' +
        (c.icone ? '<span>' + escHtml(c.icone) + '</span>' : (c.id === '' ? '<i class="bi bi-grid-3x3-gap-fill"></i>' : '')) +
        escHtml(c.nome) + '<span class="vit-chip-n">' + c.n + '</span></button>';
    }).join(''));
    $('#vitCategorias').toggle(todos.length > 0);
  }

  function cardProduto(it) {
    var comprado = it.comprado === 'S';
    var img = urlImagem(it.imagem);
    var imgHtml = img
      ? '<img class="vit-img" src="' + escHtml(img) + '" alt="" loading="lazy" onerror="this.style.display=\'none\';this.nextElementSibling.style.display=\'flex\';">' +
        '<div class="vit-img-vazia" style="display:none;"><i class="bi bi-image"></i></div>'
      : '<div class="vit-img-vazia"><i class="bi bi-bag"></i></div>';
    var sel = _selecionados.has(String(it.id));
    var valor = parseFloat(it.valor) || 0;
    // "ou 10x de ..." como nas lojas (só informativo)
    var parcelado = valor >= 100 ? '<div class="vit-parcela">ou 10x de R$ ' + moeda(valor / 10) + '</div>' : '<div class="vit-parcela">&nbsp;</div>';
    var cat = it.categoria
      ? '<div class="vit-cat" style="color:' + (it.categoria_cor || 'var(--cor-texto-off)') + ';">' + (it.categoria_icone ? escHtml(it.categoria_icone) + ' ' : '') + escHtml(it.categoria) + '</div>'
      : '<div class="vit-cat">&nbsp;</div>';
    return '<div class="col-6 col-md-4 col-lg-3 col-xl-2">' +
      '<div class="vit-card desejo-card' + (sel ? ' selecionado' : '') + (comprado ? ' comprado' : '') + '" data-id="' + it.id + '">' +
        '<div class="vit-foto">' + imgHtml +
          '<span class="vit-prio vit-prio-' + it.prioridade + '">' + NOME_PRIO[it.prioridade] + '</span>' +
          '<button type="button" class="desejo-check" data-id="' + it.id + '" aria-pressed="' + sel + '" title="Selecionar"><i class="bi bi-check-lg"></i></button>' +
          (comprado ? '<div class="vit-selo-comprado"><i class="bi bi-bag-check-fill me-1"></i>Comprado</div>' : '') +
        '</div>' +
        '<div class="vit-corpo">' +
          cat +
          '<div class="vit-nome" title="' + escHtml(it.nome) + '">' + escHtml(it.nome) + '</div>' +
          '<div class="vit-preco">R$ ' + moeda(valor) + '</div>' +
          parcelado +
          '<button class="btn btn-primary btn-sm w-100 vit-simular btn-simular-desejo" data-id="' + it.id + '"><i class="bi bi-calculator me-1"></i>Simular fatura</button>' +
          '<div class="vit-acoes">' +
            (it.link ? '<a href="' + escHtml(it.link) + '" target="_blank" rel="noopener" class="vit-acao" title="Ver na loja"><i class="bi bi-shop"></i></a>' : '') +
            (comprado
              ? '<button class="vit-acao vit-acao-perigo btn-reverter-compra" data-id="' + it.id + '" title="Reverter compra' + (it.gasto_id ? ' (apaga a despesa lançada)' : '') + '"><i class="bi bi-arrow-counterclockwise"></i></button>'
              : '<button class="vit-acao btn-comprar" data-id="' + it.id + '" title="Comprar: lançar a despesa"><i class="bi bi-bag-check"></i></button>') +
            '<button class="vit-acao btn-editar-desejo" data-id="' + it.id + '" title="Editar"><i class="bi bi-pencil"></i></button>' +
            '<button class="vit-acao vit-acao-perigo btn-remover-desejo" data-id="' + it.id + '" title="Remover"><i class="bi bi-trash3"></i></button>' +
          '</div>' +
        '</div>' +
      '</div>' +
    '</div>';
  }

  function renderVitrine() {
    var todos = window._desejos || [];
    renderCategorias();
    if (!todos.length) {
      $('#gridDesejos').html('<div class="text-center py-5 w-100" style="color:var(--cor-texto-off);">' +
        '<i class="bi bi-bag-heart" style="font-size:2.4rem;"></i><p class="mt-2 mb-0">Nenhum desejo ainda. Clique em <strong>Adicionar</strong>.</p></div>');
      $('#vitContagem').text('');
      atualizaBarraSel();
      return;
    }
    var lista = itensVisiveis();
    $('#vitContagem').text(lista.length + ' de ' + todos.length + ' ' + (todos.length === 1 ? 'produto' : 'produtos'));
    $('#gridDesejos').html(lista.length ? lista.map(cardProduto).join('') :
      '<div class="text-center py-5 w-100" style="color:var(--cor-texto-off);"><i class="bi bi-search" style="font-size:2rem;"></i>' +
      '<p class="mt-2 mb-0">Nada encontrado com esses filtros.</p></div>');
    atualizaBarraSel();
  }

  function renderGrid(itens) {
    window._desejos = itens;
    // Remove da seleção o que não existe mais (ex.: removido)
    var ids = new Set(itens.map(function (x) { return String(x.id); }));
    _selecionados.forEach(function (id) { if (!ids.has(id)) _selecionados.delete(id); });
    renderVitrine();
  }

  $('#vitCategorias').on('click', '.vit-chip', function () { _filtroCat = String($(this).data('cat')); renderVitrine(); });
  $('#vitBusca').on('input', function () { _busca = this.value; renderVitrine(); });
  $('#vitOrdem').on('change', function () { try { localStorage.setItem(ORDEM_KEY, this.value); } catch (e) {} renderVitrine(); });
  $('#vitComprados').on('change', renderVitrine);

  // ─── SELEÇÃO MÚLTIPLA ────────────────────────────────────
  var _selecionados = new Set();
  function itensSelecionados() {
    return (window._desejos || []).filter(function (x) { return _selecionados.has(String(x.id)); });
  }
  function atualizaBarraSel() {
    var itens = itensSelecionados();
    var total = itens.reduce(function (s, x) { return s + (parseFloat(x.valor) || 0); }, 0);
    $('#selQtd').text(itens.length);
    $('#selQtdLabel').text(itens.length === 1 ? 'selecionado' : 'selecionados');
    $('#selTotal').text('R$ ' + moeda(total));
    $('#barraSelDesejos').toggle(itens.length > 0);
    // "Selecionar todos" age sobre o que está visível (filtro/busca)
    var vis = itensVisiveis();
    var todosVis = vis.length > 0 && vis.every(function (x) { return _selecionados.has(String(x.id)); });
    $('#btnSelTodos').prop('disabled', !vis.length)
      .html(todosVis ? '<i class="bi bi-x-lg me-1"></i>Limpar seleção' : '<i class="bi bi-check2-all me-1"></i>Selecionar todos');
    // "Marcar comprados" só faz sentido se há algum ainda não comprado
    var algumPendente = itens.some(function (x) { return x.comprado !== 'S'; });
    $('#btnSelComprar').prop('disabled', !algumPendente);
  }
  function toggleSel(id, on) {
    id = String(id);
    if (on === undefined) on = !_selecionados.has(id);
    if (on) _selecionados.add(id); else _selecionados.delete(id);
    var $c = $('.desejo-card[data-id="' + id + '"]');
    $c.toggleClass('selecionado', on);
    $c.find('.desejo-check').attr('aria-pressed', on);
    atualizaBarraSel();
  }
  $('#gridDesejos').on('click', '.desejo-check', function (e) {
    e.preventDefault(); e.stopPropagation();
    toggleSel($(this).data('id'));
  });
  $('#btnSelTodos').click(function () {
    var vis = itensVisiveis();
    var marcar = !vis.every(function (x) { return _selecionados.has(String(x.id)); });
    vis.forEach(function (x) { toggleSel(x.id, marcar); });
  });
  $('#btnSelLimpar').click(function () {
    Array.from(_selecionados).forEach(function (id) { toggleSel(id, false); });
  });

  // Executa a mesma ação para vários ids (o controller trata um por vez)
  function emLote(acao, ids, fim) {
    var erros = 0;
    $.when.apply($, ids.map(function (id) {
      return $.ajax({ type: 'POST', url: URL_CTRL, data: { acao: acao, id: id }, dataType: 'json' })
        .then(null, function () { erros++; return $.Deferred().resolve(); });
    })).always(function () { fim(erros); });
  }

  $('#btnSelRemover').click(function () {
    var itens = itensSelecionados();
    if (!itens.length) return;
    Swal.fire({
      title: 'Remover ' + itens.length + ' ' + (itens.length === 1 ? 'item' : 'itens') + '?',
      html: '<div style="font-size:.85rem;text-align:left;max-height:180px;overflow:auto;">' +
            itens.map(function (x) { return '• ' + escHtml(x.nome); }).join('<br>') + '</div>',
      icon: 'warning', showCancelButton: true,
      confirmButtonColor: '#EF4444', confirmButtonText: 'Remover', cancelButtonText: 'Cancelar'
    }).then(function (r) {
      if (!r.isConfirmed) return;
      emLote('remover', itens.map(function (x) { return x.id; }), function (erros) {
        if (erros) toastr.error(erros + ' não puderam ser removidos.'); else toastr.success('Removidos.');
        itens.forEach(function (x) { _selecionados.delete(String(x.id)); });
        carregar();
      });
    });
  });

  $('#btnSelSimular').click(function () {
    var itens = itensSelecionados().filter(function (x) { return parseFloat(x.valor) > 0; });
    if (!itens.length) { toastr.info('Os itens selecionados não têm valor.'); return; }
    abrirSimulacao(itens);
  });

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
      valorDesejo.setValue(parseFloat(item.valor) || 0);
      $('#desejoPrioridade').val(item.prioridade);
      setCatDesejo(item.categoria_id);
      $('#desejoUrlManual').val('');
      var img = urlImagem(item.imagem);
      setPrevia(img);
      // Mantém a imagem existente no hidden mesmo que seja local.
      $('#desejoImagemUrl').val(item.imagem || '');
    } else {
      $('#modalDesejoTitulo').text('Novo desejo');
      $('#desejoId').val('');
      $('#desejoNome, #desejoLink, #desejoUrlManual').val('');
      valorDesejo.setValue(0);
      $('#desejoPrioridade').val('media');
      // Novo item herda a categoria filtrada no momento (atalho)
      setCatDesejo(_filtroCat && _filtroCat !== '0' ? _filtroCat : '');
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
    fd.append('categoria_id', $('#desejoCategoria').val() || '');
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

  // ─── CHECKOUT (carrinho → pagamento) ──────────────────────
  // Cada item do carrinho vira uma despesa própria (mantém a categoria e
  // permite reverter item a item). Forma, cartão, parcelas, data e divisão
  // valem para o pedido todo.
  var compraModal = new bootstrap.Modal(document.getElementById('modalComprar'));
  var compraDivValor = null;
  var _compra = { forma: 'Crédito', cartao: '', parcelado: 'N', parcelas: 2, pessoa: '', divModo: 'tudo' };
  var _carrinho = [];   // { id, nome, categoria_id, banc }
  var _grupo = { modo: 'separado', nome: '', categoria_id: '' };   // compra agrupada
  // Frete (R$) e desconto do cupom (R$ ou % sobre o subtotal)
  var bancFrete = bancInput('#checkoutFrete');
  var _descModo = 'R$';
  var bancDesc = bancInput('#checkoutDesconto');
  var _pessoas = null;

  function hojeISO() {
    var d = new Date();
    return d.getFullYear() + '-' + String(d.getMonth() + 1).padStart(2, '0') + '-' + String(d.getDate()).padStart(2, '0');
  }
  function fmtR(v) { return 'R$ ' + moeda(v); }
  function catInfo(id) { return _categorias.find(function (x) { return String(x.id) === String(id); }); }
  function catPreview(id) {
    var c = catInfo(id);
    return c
      ? '<span class="cat-dot" style="background:' + (c.cor || '#6B7280') + ';flex-shrink:0;"></span>' +
        (c.icone ? '<span class="ms-1">' + escHtml(c.icone) + '</span>' : '') +
        '<span class="ms-1 text-truncate" style="color:' + (c.cor || '#6B7280') + ';">' + escHtml(c.nome) + '</span>'
      : '<span class="text-muted">Categoria</span>';
  }
  function menuCategorias() {
    return _categorias.map(function (c) {
      var cor = c.cor || '#6B7280';
      return '<li><a class="dropdown-item d-flex align-items-center gap-2" href="#" data-id="' + c.id + '">' +
        '<span class="cat-dot" style="background:' + cor + ';flex-shrink:0;"></span>' +
        (c.icone ? '<span class="me-1">' + escHtml(c.icone) + '</span>' : '') +
        '<span style="color:' + cor + ';">' + escHtml(c.nome) + '</span></a></li>';
    }).join('');
  }
  function subtotalCarrinho() {
    return _carrinho.reduce(function (s, it) { return s + it.banc.getValue(); }, 0);
  }
  function freteValor() { return bancFrete.getValue(); }
  function descontoValor() {
    var sub = subtotalCarrinho();
    if (_descModo === '%') {
      // No modo %, o campo guarda o número (ex.: 10,00 = 10%)
      var pct = Math.min(bancDesc.getValue(), 100);
      return Math.round(sub * pct) / 100;
    }
    return bancDesc.getValue();
  }
  // Total a pagar = subtotal + frete − desconto
  function totalCarrinho() {
    return Math.round((subtotalCarrinho() + freteValor() - descontoValor()) * 100) / 100;
  }
  /**
   * Valor final de cada item: frete e desconto repartidos na proporção do valor
   * do item. Centavos de arredondamento vão para o último, para a soma bater
   * exatamente com o total pago.
   */
  function valoresFinais() {
    var sub = subtotalCarrinho(), total = totalCarrinho(), acum = 0;
    return _carrinho.map(function (it, i) {
      var v;
      if (i === _carrinho.length - 1) v = Math.round((total - acum) * 100) / 100;
      else {
        var peso = sub > 0 ? it.banc.getValue() / sub : 1 / _carrinho.length;
        v = Math.round((it.banc.getValue() + (freteValor() - descontoValor()) * peso) * 100) / 100;
      }
      acum += v;
      return v;
    });
  }

  // ── Etapa 1: carrinho ──
  function renderCarrinho() {
    var $l = $('#checkoutItens').empty();
    if (!_carrinho.length) {
      $l.html('<div class="text-center py-4" style="color:var(--cor-texto-off);"><i class="bi bi-cart-x" style="font-size:2rem;"></i><p class="mt-2 mb-0">Carrinho vazio.</p></div>');
    }
    _carrinho.forEach(function (it, i) {
      var img = urlImagem(it.imagem);
      var $row = $(
        '<div class="checkout-item" data-i="' + i + '">' +
          '<div class="checkout-thumb">' + (img ? '<img src="' + escHtml(img) + '" alt="" onerror="this.remove()">' : '<i class="bi bi-bag"></i>') + '</div>' +
          '<div class="checkout-campos">' +
            '<input type="text" class="form-control form-control-sm checkout-nome" maxlength="255">' +
            '<div class="d-flex gap-2 mt-2 flex-wrap">' +
              '<div class="dropdown checkout-cat flex-grow-1">' +
                '<button type="button" class="btn cat-sel-btn btn-sm w-100 d-flex align-items-center gap-1" data-bs-toggle="dropdown" aria-expanded="false">' +
                  '<span class="cat-sel-preview d-flex align-items-center text-truncate">' + catPreview(it.categoria_id) + '</span>' +
                  '<i class="bi bi-chevron-down ms-auto" style="font-size:.7rem;opacity:.6;"></i></button>' +
                '<ul class="dropdown-menu cat-sel-menu">' + menuCategorias() + '</ul>' +
              '</div>' +
              '<input type="text" class="form-control form-control-sm checkout-valor" inputmode="numeric">' +
            '</div>' +
          '</div>' +
          '<button type="button" class="checkout-remover" title="Tirar do carrinho"><i class="bi bi-x-lg"></i></button>' +
        '</div>');
      $row.find('.checkout-nome').val(it.nome);
      $l.append($row);
      var valorAtual = it.banc ? it.banc.getValue() : (parseFloat(it.valor) || 0);
      it.banc = bancInput($row.find('.checkout-valor')[0], valorAtual);
    });
    atualizaSubtotal();
    renderAgrupar();
  }
  // "Tudo numa despesa só": nome + categoria do pedido (itens só somam o valor)
  function renderAgrupar() {
    var multi = _carrinho.length > 1;
    if (!multi) _grupo.modo = 'separado';
    $('#checkoutAgruparWrap').toggle(multi);
    $('.checkout-modo').removeClass('selecionado').filter('[data-modo="' + _grupo.modo + '"]').addClass('selecionado');
    var agr = _grupo.modo === 'agrupado';
    $('#checkoutAgrupadoCampos').toggle(agr);
    $('#checkoutItens').toggleClass('modo-agrupado', agr);
    $('#checkoutDica').text(agr
      ? 'Ajuste o valor pago em cada item; tudo vira uma única despesa com o nome e a categoria abaixo.'
      : 'Ajuste nome, categoria e o valor que você realmente pagou em cada item. Cada um vira uma despesa.');
    $('#checkoutCatGrupo .cat-sel-menu').html(menuCategorias());
    $('#checkoutCatGrupo .cat-sel-preview').html(catPreview(_grupo.categoria_id));
    $('#checkoutNomeGrupo').val(_grupo.nome);
  }
  $('#modalComprar').on('click', '.checkout-modo', function () {
    _grupo.modo = $(this).data('modo');
    if (_grupo.modo === 'agrupado') {
      if (!_grupo.nome) _grupo.nome = 'Compra (' + _carrinho.length + ' itens)';
      // Categoria padrão: a mais comum entre os itens
      if (!_grupo.categoria_id) {
        var cont = {};
        _carrinho.forEach(function (it) { if (it.categoria_id) cont[it.categoria_id] = (cont[it.categoria_id] || 0) + 1; });
        _grupo.categoria_id = Object.keys(cont).sort(function (a, b) { return cont[b] - cont[a]; })[0] || '';
      }
    }
    renderAgrupar();
    resumoPedido();
  });
  $('#checkoutNomeGrupo').on('input', function () { _grupo.nome = this.value; $(this).removeClass('borda-erro'); });
  $('#checkoutCatGrupo').on('click', '.dropdown-item', function (e) {
    e.preventDefault();
    _grupo.categoria_id = String($(this).data('id'));
    $('#checkoutCatGrupo .cat-sel-preview').html(catPreview(_grupo.categoria_id));
    $('#checkoutCatGrupo').removeClass('borda-erro');
    var dd = bootstrap.Dropdown.getInstance($('#checkoutCatGrupo [data-bs-toggle="dropdown"]')[0]);
    if (dd) dd.hide();
  });

  function atualizaSubtotal() {
    $('#checkoutQtd').text(_carrinho.length);
    $('#checkoutQtdLabel').text(_carrinho.length === 1 ? 'item' : 'itens');
    $('#checkoutSubtotal').text(fmtR(subtotalCarrinho()));
    var desc = descontoValor();
    $('#checkoutTotalFinal').text(fmtR(totalCarrinho()))
      .closest('.checkout-total-final').toggleClass('com-ajuste', freteValor() > 0 || desc > 0);
    $('#btnCheckoutContinuar').prop('disabled', !_carrinho.length);
  }
  $('#checkoutItens').on('input', '.checkout-nome', function () {
    _carrinho[$(this).closest('.checkout-item').data('i')].nome = this.value;
  });
  $('#checkoutItens').on('keyup input paste', '.checkout-valor', function () { setTimeout(atualizaSubtotal, 0); });
  // No modo %, troca o "R$" da máscara por "%" no que aparece no campo
  function exibeDescontoPct() {
    if (_descModo !== '%') return;
    var el = document.getElementById('checkoutDesconto');
    el.value = el.value.replace(/^R\$\s?/, '') + ' %';
  }
  $('#checkoutFrete, #checkoutDesconto').on('keyup input paste', function () {
    $(this).removeClass('borda-erro');
    setTimeout(function () { exibeDescontoPct(); atualizaSubtotal(); }, 0);
  });
  // Cupom em R$ ou %: no modo %, o campo mostra "10,00 %" (mesma máscara de dígitos)
  $('#checkoutDescModo').click(function () {
    _descModo = _descModo === 'R$' ? '%' : 'R$';
    $(this).text(_descModo);
    bancDesc.setValue(0);
    exibeDescontoPct();
    atualizaSubtotal();
  });
  $('#checkoutItens').on('click', '.checkout-cat .dropdown-item', function (e) {
    e.preventDefault();
    var $row = $(this).closest('.checkout-item');
    var it = _carrinho[$row.data('i')];
    it.categoria_id = String($(this).data('id'));
    $row.find('.cat-sel-preview').html(catPreview(it.categoria_id));
    $row.find('.checkout-cat').removeClass('borda-erro');
    var dd = bootstrap.Dropdown.getInstance($row.find('[data-bs-toggle="dropdown"]')[0]);
    if (dd) dd.hide();
  });
  $('#checkoutItens').on('click', '.checkout-remover', function () {
    _carrinho.splice($(this).closest('.checkout-item').data('i'), 1);
    renderCarrinho();
  });

  // ── Etapa 2: pagamento ──
  function renderCartoesCompra() {
    var ids = Object.keys(simCartoes || {});
    if (!ids.length) {
      $('#compraCartoes').html('<span style="color:var(--cor-texto-off);font-size:.82rem;">Nenhum cartão cadastrado.</span>');
      _compra.cartao = '';
      return;
    }
    if (!_compra.cartao || !simCartoes[_compra.cartao]) _compra.cartao = String(simCartoes[ids[0]].id);
    $('#compraCartoes').html(ids.map(function (id) {
      var c = simCartoes[id], cor = c.cor || '#3B82F6';
      return '<div class="sim-cartao-chip compra-cartao' + (String(c.id) === _compra.cartao ? ' selecionado' : '') + '" data-id="' + c.id +
        '" style="--chip-cor:' + cor + ';cursor:pointer;"><span class="sim-chip-dot" style="background:' + cor + ';"></span>' + escHtml(c.nome_cartao) + '</div>';
    }).join(''));
  }
  function renderPessoasCompra() {
    var html = '<div class="resp-chip compra-pessoa' + (!_compra.pessoa ? ' selecionado' : '') + '" data-id=""><i class="bi bi-person-fill me-1"></i>Só eu</div>';
    (_pessoas || []).forEach(function (r) {
      html += '<div class="resp-chip compra-pessoa' + (String(r.id) === _compra.pessoa ? ' selecionado' : '') + '" data-id="' + r.id +
        '" style="--resp-cor:' + (r.cor || '#6B7280') + ';"><span class="resp-dot"></span>' + escHtml(r.nome) + '</div>';
    });
    $('#compraPessoas').html(html);
    var p = (_pessoas || []).find(function (x) { return String(x.id) === _compra.pessoa; });
    $('#compraDivNome').text(p ? p.nome : '');
    $('#compraDivisaoWrap').toggle(!!_compra.pessoa);
    resumoPedido();
  }
  function parteDelaValor(total) {
    if (_compra.divModo === 'metade') return total / 2;
    if (_compra.divModo === 'outro') return compraDivValor ? compraDivValor.getValue() : 0;
    return total;
  }
  function atualizaFormaCompra() {
    $('.compra-forma').removeClass('active').filter('[data-forma="' + _compra.forma + '"]').addClass('active');
    $('#compraCreditoWrap').toggle(_compra.forma === 'Crédito');
    $('.compra-parc').removeClass('active').filter('[data-parc="' + _compra.parcelado + '"]').addClass('active');
    $('#compraParcelasWrap').attr('style', _compra.parcelado === 'S' ? '' : 'display:none !important;');
    resumoPedido();
  }
  function resumoPedido() {
    var total = totalCarrinho();
    $('#checkoutResumoItens').html(_carrinho.map(function (it) {
      return '<div class="checkout-resumo-linha"><span class="text-truncate">' + escHtml(it.nome || '—') + '</span><span>' + fmtR(it.banc.getValue()) + '</span></div>';
    }).join(''));
    var fr = freteValor(), ds = descontoValor();
    if (fr > 0 || ds > 0) {
      $('#checkoutResumoItens').append(
        '<div class="checkout-resumo-linha sub"><span>Subtotal</span><span>' + fmtR(subtotalCarrinho()) + '</span></div>' +
        (fr > 0 ? '<div class="checkout-resumo-linha"><span><i class="bi bi-truck me-1"></i>Frete</span><span>+ ' + fmtR(fr) + '</span></div>' : '') +
        (ds > 0 ? '<div class="checkout-resumo-linha desconto"><span><i class="bi bi-ticket-perforated me-1"></i>Cupom' +
                  (_descModo === '%' ? ' (' + moeda(bancDesc.getValue()) + '%)' : '') + '</span><span>− ' + fmtR(ds) + '</span></div>' : ''));
    }
    $('#checkoutResumoTotal').text(fmtR(total));
    if (_grupo.modo === 'agrupado') {
      var cg = catInfo(_grupo.categoria_id);
      $('#checkoutResumoItens').append('<div class="checkout-resumo-grupo"><i class="bi bi-box-seam me-1"></i>Lançado como <strong>' +
        escHtml(_grupo.nome || '—') + '</strong>' + (cg ? ' · ' + escHtml(cg.nome) : '') + '</div>');
    }
    // Forma de pagamento em texto
    var pag = _compra.forma;
    if (_compra.forma === 'Crédito') {
      var c = simCartoes && simCartoes[_compra.cartao];
      pag = 'Crédito' + (c ? ' · ' + escHtml(c.nome_cartao) : '') +
        (_compra.parcelado === 'S' ? ' · ' + _compra.parcelas + 'x de ' + fmtR(total / _compra.parcelas) : ' · à vista');
    }
    var div = '';
    if (_compra.pessoa) {
      var p = (_pessoas || []).find(function (x) { return String(x.id) === _compra.pessoa; });
      var dela = Math.round(Math.min(parteDelaValor(total), total) * 100) / 100;
      div = '<div class="mt-1"><i class="bi bi-people me-1"></i>' + escHtml(p ? p.nome : '') + ': ' + fmtR(dela) +
            ' · Sua parte: ' + fmtR(total - dela) + '</div>';
    }
    $('#checkoutResumoPag').html('<div><i class="bi bi-wallet2 me-1"></i>' + pag + '</div>' + div);
    $('#compraParcDisplay').text(_compra.parcelas);
    $('#compraParcInfo').text(total > 0 ? _compra.parcelas + 'x de ' + fmtR(total / _compra.parcelas) : 'parcelas');
    // Prévia da divisão
    var $r = $('#compraDivResumo');
    if (!_compra.pessoa || total <= 0) { $r.text(''); }
    else if (_compra.divModo === 'tudo') { $r.text('A compra inteira é dela.'); }
    else {
      var d2 = Math.round(Math.min(parteDelaValor(total), total) * 100) / 100;
      if (_compra.divModo === 'outro' && (d2 <= 0 || d2 >= total)) $r.html('<span class="text-warning">Informe um valor menor que o total (' + fmtR(total) + ').</span>');
      else $r.html('Dela: <strong>' + fmtR(d2) + '</strong> · Sua parte: <strong>' + fmtR(total - d2) + '</strong>' +
                   (_carrinho.length > 1 ? ' <span style="opacity:.8;">(proporcional em cada item)</span>' : ''));
    }
  }

  function irEtapa(n) {
    $('#checkoutPasso1').toggle(n === 1);
    $('#checkoutPasso2').toggle(n === 2);
    $('.checkout-etapa').each(function () {
      var e = parseInt($(this).data('etapa'), 10);
      $(this).toggleClass('ativa', e === n).toggleClass('feita', e < n);
    });
    $('#btnCheckoutVoltar').toggle(n === 2);
    $('#btnCheckoutCancelar').toggle(n === 1);
    $('#btnCheckoutContinuar').toggle(n === 1);
    $('#btnConfirmarCompra').toggle(n === 2);
    if (n === 2) { renderCartoesCompra(); renderPessoasCompra(); atualizaFormaCompra(); }
  }
  $('#btnCheckoutContinuar').click(function () {
    var ok = true;
    var agr = _grupo.modo === 'agrupado';
    if (agr) {
      if (!(_grupo.nome || '').trim()) { ok = false; $('#checkoutNomeGrupo').addClass('borda-erro'); }
      if (!_grupo.categoria_id)        { ok = false; $('#checkoutCatGrupo').addClass('borda-erro'); }
    }
    _carrinho.forEach(function (it, i) {
      var $row = $('#checkoutItens .checkout-item[data-i="' + i + '"]');
      // Agrupado: nome/categoria dos itens não importam, só o valor
      if (!agr && !(it.nome || '').trim()) { ok = false; $row.find('.checkout-nome').addClass('borda-erro'); }
      else $row.find('.checkout-nome').removeClass('borda-erro');
      if (!agr && !it.categoria_id)        { ok = false; $row.find('.checkout-cat').addClass('borda-erro'); }
      if (!(it.banc.getValue() > 0))   { ok = false; $row.find('.checkout-valor').addClass('borda-erro'); }
      else $row.find('.checkout-valor').removeClass('borda-erro');
    });
    if (!ok) { toastr.warning(agr ? 'Confira o nome, a categoria e os valores.' : 'Confira nome, categoria e valor de cada item.'); return; }
    if (!(totalCarrinho() > 0)) {
      $('#checkoutDesconto').addClass('borda-erro');
      toastr.warning('O desconto não pode ser maior que o valor da compra.');
      return;
    }
    irEtapa(2);
  });
  $('#btnCheckoutVoltar').click(function () { irEtapa(1); });

  function abrirCheckout(itens) {
    _carrinho = itens.map(function (x) {
      return { id: x.id, nome: x.nome, imagem: x.imagem, valor: x.valor, categoria_id: x.categoria_id ? String(x.categoria_id) : '' };
    });
    $('#compraData').val(hojeISO());
    _grupo = { modo: 'separado', nome: '', categoria_id: '' };
    bancFrete.setValue(0);
    _descModo = 'R$'; $('#checkoutDescModo').text('R$'); bancDesc.setValue(0);
    $('#checkoutFrete, #checkoutDesconto').removeClass('borda-erro');
    _compra = { forma: 'Crédito', cartao: _compra.cartao, parcelado: 'N', parcelas: 2, pessoa: '', divModo: 'tudo' };
    $('.compra-div').removeClass('selecionado').filter('[data-modo="tudo"]').addClass('selecionado');
    $('#compraDivValorWrap').hide();
    var prontos = 0;
    function pronto() {
      if (++prontos < 2) return;
      renderCarrinho();
      irEtapa(1);
      compraModal.show();
    }
    carregarCartoesSim(pronto);
    if (_pessoas) pronto();
    else $.ajax({ type: 'POST', url: App.ctrl.responsaveis, data: { acao: 'buscar' }, dataType: 'json',
      success: function (d) { _pessoas = d || []; }, complete: pronto });
  }

  $('#gridDesejos').on('click', '.btn-comprar', function () {
    var id = String($(this).data('id'));
    var item = (window._desejos || []).find(function (x) { return String(x.id) === id; });
    if (item) abrirCheckout([item]);
  });
  $('#btnSelComprar').click(function () {
    var pend = itensSelecionados().filter(function (x) { return x.comprado !== 'S'; });
    if (!pend.length) { toastr.info('Os itens selecionados já foram comprados.'); return; }
    abrirCheckout(pend);
  });

  $('#modalComprar').on('click', '.compra-forma', function () { _compra.forma = $(this).data('forma'); atualizaFormaCompra(); });
  $('#modalComprar').on('click', '.compra-cartao', function () { _compra.cartao = String($(this).data('id')); renderCartoesCompra(); resumoPedido(); });
  $('#modalComprar').on('click', '.compra-parc', function () { _compra.parcelado = String($(this).data('parc')); atualizaFormaCompra(); });
  $('#compraParcMenos').click(function () { _compra.parcelas = Math.max(2, _compra.parcelas - 1); resumoPedido(); });
  $('#compraParcMais').click(function () { _compra.parcelas = Math.min(48, _compra.parcelas + 1); resumoPedido(); });
  $('#modalComprar').on('click', '.compra-pessoa', function (e) {
    e.stopPropagation();
    _compra.pessoa = String($(this).data('id') || '');
    renderPessoasCompra();
  });
  $('#modalComprar').on('click', '.compra-div', function () {
    _compra.divModo = $(this).data('modo');
    $('.compra-div').removeClass('selecionado').filter('[data-modo="' + _compra.divModo + '"]').addClass('selecionado');
    $('#compraDivValorWrap').toggle(_compra.divModo === 'outro');
    if (_compra.divModo === 'outro' && !compraDivValor) compraDivValor = bancInput('#compraDivValor');
    resumoPedido();
  });
  $('#compraDivValor').on('keyup input paste', function () { setTimeout(resumoPedido, 0); });

  // ── Confirmar: lança uma despesa por item, em sequência ──
  $('#btnConfirmarCompra').click(function () {
    var total = totalCarrinho();
    if (!$('#compraData').val()) { toastr.warning('Informe a data da compra.'); return; }
    var credito = _compra.forma === 'Crédito';
    if (credito && !_compra.cartao) { toastr.warning('Escolha o cartão.'); return; }

    // Divisão como % do pedido, aplicada igual em cada item
    var parte = '';
    if (_compra.pessoa && _compra.divModo === 'metade') parte = '50';
    if (_compra.pessoa && _compra.divModo === 'outro') {
      var dela = parteDelaValor(total);
      if (!(dela > 0 && dela < total)) { toastr.warning('O valor dela precisa ser menor que o total.'); return; }
      parte = String(Math.round(dela / total * 1000000) / 10000);
    }

    // Agrupado: uma despesa só com o total; os itens viram uma "entrada" da fila
    var agrupado = _grupo.modo === 'agrupado' && _carrinho.length > 1;
    var fila = agrupado
      ? [{ grupo: true, nome: _grupo.nome, categoria_id: _grupo.categoria_id, total: total, itens: _carrinho.slice() }]
      : _carrinho.slice();
    var finais = valoresFinais();
    _carrinho.forEach(function (it, i) { it.valorFinal = finais[i]; });
    var feitos = 0, falhas = [];
    $('#spinnerCompra').removeClass('d-none');
    $('#btnConfirmarCompra, #btnCheckoutVoltar').prop('disabled', true);

    function proximo() {
      var it = fila.shift();
      if (!it) return fim();
      // Já com frete e desconto repartidos (agrupado: total final)
      var valorIt = it.grupo ? it.total : it.valorFinal;
      $.ajax({
        type: 'POST', url: App.ctrl.gastos, dataType: 'json',
        data: {
          acao: 'adicionar', descricao: it.nome.trim(), valor: valorIt.toFixed(2).replace('.', ','),
          categoria: it.categoria_id, metodo: _compra.forma, cartao: credito ? _compra.cartao : '',
          data: $('#compraData').val(), tipo: credito ? 'credito' : 'debito',
          parcelado: credito ? _compra.parcelado : 'N',
          num_parcelas: credito && _compra.parcelado === 'S' ? _compra.parcelas : '',
          responsavel: _compra.pessoa, responsavel_parte: parte, retornar_id: 1
        }
      }).then(function (r) {
        if (!r || !r.ok) return $.Deferred().reject();
        // Todos os itens (1, ou os do grupo) ficam ligados à mesma despesa
        var ids = it.grupo ? it.itens.map(function (x) { return x.id; }) : [it.id];
        return $.when.apply($, ids.map(function (iid) {
          return $.ajax({ type: 'POST', url: URL_CTRL, dataType: 'json',
            data: { acao: 'registrarCompra', id: iid, gasto_id: r.gasto_id || '' } });
        }));
      }).done(function () {
        feitos++;
        (it.grupo ? it.itens : [it]).forEach(function (x) { _selecionados.delete(String(x.id)); });
      }).fail(function () {
        falhas.push(it.nome);
      }).always(proximo);
    }
    function fim() {
      $('#spinnerCompra').addClass('d-none');
      $('#btnConfirmarCompra, #btnCheckoutVoltar').prop('disabled', false);
      if (falhas.length) toastr.error('Não foi possível lançar: ' + falhas.map(escHtml).join(', '));
      if (feitos) {
        toastr.success(agrupado
          ? 'Compra registrada! Despesa "' + escHtml(_grupo.nome) + '" lançada.'
          : (feitos === 1 ? 'Compra registrada! Despesa lançada.' : 'Compra registrada! ' + feitos + ' despesas lançadas.'));
        compraModal.hide();
      }
      carregar();
    }
    proximo();
  });

  // ─── REVERTER COMPRA ─────────────────────────────────────
  $('#gridDesejos').on('click', '.btn-reverter-compra', function () {
    var id = String($(this).data('id'));
    var item = (window._desejos || []).find(function (x) { return String(x.id) === id; });
    if (!item) return;
    var temDespesa = !!item.gasto_id;
    var doGrupo = temDespesa ? (window._desejos || []).filter(function (x) { return String(x.gasto_id) === String(item.gasto_id); }) : [item];
    Swal.fire({
      title: 'Reverter a compra?',
      html: '<div style="font-size:.9rem;">' + escHtml(item.nome) + '</div>' +
        '<div style="font-size:.82rem;color:var(--cor-texto-off);margin-top:.5rem;">' +
        (temDespesa
          ? (doGrupo.length > 1
              ? 'Este item foi comprado junto com outros numa despesa só. A despesa será <strong>apagada</strong> e estes ' + doGrupo.length + ' itens voltam para a lista:<br>' +
                doGrupo.map(function (x) { return '• ' + escHtml(x.nome); }).join('<br>')
              : 'A despesa lançada na compra (e as parcelas, se houver) será <strong>apagada</strong>, e o item volta para a lista.')
          : 'Esta compra foi marcada antes do registro de despesa: o item só volta para a lista. Se lançou a despesa à mão, remova-a na fatura.') +
        '</div>',
      icon: 'warning', showCancelButton: true,
      confirmButtonColor: '#EF4444', confirmButtonText: 'Reverter', cancelButtonText: 'Cancelar'
    }).then(function (r) {
      if (!r.isConfirmed) return;
      $.ajax({
        type: 'POST', url: URL_CTRL, data: { acao: 'reverterCompra', id: id }, dataType: 'json',
        success: function (res) {
          if (!res || !res.ok) { toastr.error('Não foi possível reverter.'); return; }
          toastr.success(res.despesa_apagada ? 'Compra revertida e despesa apagada.' : 'Item voltou para a lista.');
          carregar();
        },
        error: function () { toastr.error('Erro ao reverter.'); }
      });
    });
  });

  // ─── COMPRADO ────────────────────────────────────────────
  $('#gridDesejos').on('click', '.btn-comprado', function () {
    var id = $(this).data('id');
    toastr.info('Item voltou para a lista. A despesa lançada na compra continua; remova-a na fatura se precisar.');
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
  // adicionar a despesa de verdade; aqui assumimos hoje (mês atual) ou dia 1 (meses futuros).
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

    // No mês atual o dia 1 já passou: usar hoje, senão uma compra feita depois do
    // fechamento aparecia na fatura deste mês em vez da seguinte.
    var mesSim = $('#simDesejoData').val() || mesAtualISO();
    var diaSim = mesSim === mesAtualISO() ? String(new Date().getDate()).padStart(2, '0') : '01';

    FaturasSim.projetar({
      valor: simValorAtual, tipo: tipo, numParcelas: n,
      dataStr: mesSim + '-' + diaSim,
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
    if (!(parseFloat(item.valor) > 0)) { toastr.info('Defina um valor no item para simular.'); return; }
    abrirSimulacao([item]);
  });

  // Vários itens = uma compra só com o valor somado (mesmo cartão e forma)
  function abrirSimulacao(itens) {
    simValorAtual = itens.reduce(function (s, x) { return s + (parseFloat(x.valor) || 0); }, 0);
    if (itens.length === 1) {
      $('#simDesejoNome').text(itens[0].nome);
      $('#simDesejoItens').hide().empty();
    } else {
      $('#simDesejoNome').text(itens.length + ' itens juntos');
      $('#simDesejoItens').html(itens.map(function (x) {
        return escHtml(x.nome) + ' (R$ ' + moeda(x.valor) + ')';
      }).join(' · ')).show();
    }
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
  }

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

  carregarCategorias();
  carregar();
});
</script>

<style>
/* ─── Vitrine (Lista de desejos) ─── */
.vitrine-barra {
  display: flex; align-items: center; gap: .75rem; flex-wrap: wrap;
  padding: .65rem .8rem;
}
.vitrine-busca { position: relative; flex: 1 1 260px; }
.vitrine-busca i { position: absolute; left: .8rem; top: 50%; transform: translateY(-50%); color: var(--cor-texto-off); }
.vitrine-busca input { padding-left: 2.2rem; border-radius: 30px; }
.vitrine-controles #vitOrdem { width: auto; }

.vitrine-categorias {
  display: flex; gap: .5rem; overflow-x: auto; padding-bottom: .25rem;
  scrollbar-width: thin;
}
.vit-chip {
  flex-shrink: 0; display: inline-flex; align-items: center; gap: .4rem;
  padding: .35rem .85rem; border-radius: 30px;
  border: 1px solid var(--cor-borda); background: rgba(255,255,255,.03);
  color: var(--cor-texto-sec); font-size: .82rem; font-weight: 500; white-space: nowrap;
  transition: all var(--trans);
}
.vit-chip:hover { border-color: var(--chip); color: var(--cor-texto); }
.vit-chip.ativo { background: color-mix(in srgb, var(--chip) 18%, transparent); border-color: var(--chip); color: var(--cor-texto); }
.vit-chip-n {
  font-size: .7rem; font-weight: 700; min-width: 20px; padding: 0 6px; border-radius: 10px;
  background: rgba(255,255,255,.08); color: var(--cor-texto-off); text-align: center;
}
.vit-chip.ativo .vit-chip-n { background: var(--chip); color: #fff; }

/* Card de produto */
.vit-card {
  height: 100%; display: flex; flex-direction: column;
  background: var(--cor-painel); border: 1px solid rgba(255,255,255,.07);
  border-radius: 14px; overflow: hidden;
  transition: transform .18s ease, box-shadow .18s ease, border-color .18s ease;
}
.vit-card:hover { transform: translateY(-4px); box-shadow: 0 12px 28px rgba(0,0,0,.35); border-color: rgba(59,130,246,.35); }
.vit-foto {
  position: relative; aspect-ratio: 1 / 1;
  background: #fff;                       /* fundo branco de loja */
  display: flex; align-items: center; justify-content: center;
  overflow: hidden;
}
/* A foto fica no tamanho dela (até 78% do quadro): imagem pequena não estoura */
.vit-img { max-width: 78%; max-height: 78%; object-fit: contain; transition: transform .25s ease; }
.vit-card:hover .vit-img { transform: scale(1.05); }
.vit-img-vazia {
  width: 100%; height: 100%; display: flex; align-items: center; justify-content: center;
  background: linear-gradient(135deg, #2F3148, #24253A); color: rgba(255,255,255,.25); font-size: 2.4rem;
}
.vit-prio {
  position: absolute; top: 8px; left: 8px;
  font-size: .64rem; font-weight: 700; text-transform: uppercase; letter-spacing: .03em;
  padding: 2px 8px; border-radius: 4px; color: #fff;
}
.vit-prio-alta  { background: #EF4444; }
.vit-prio-media { background: #F59E0B; }
.vit-prio-baixa { background: #22C55E; }
.vit-selo-comprado {
  position: absolute; left: 0; right: 0; bottom: 0;
  background: rgba(34,197,94,.92); color: #fff; text-align: center;
  font-size: .75rem; font-weight: 700; padding: 4px 0;
}
.vit-card.comprado .vit-img, .vit-card.comprado .vit-img-vazia { filter: grayscale(1); opacity: .55; }
.vit-card.comprado .vit-preco { color: var(--cor-texto-off); text-decoration: line-through; }

.vit-corpo { padding: .65rem .75rem .75rem; display: flex; flex-direction: column; flex: 1; }
.vit-cat { font-size: .7rem; font-weight: 600; text-transform: uppercase; letter-spacing: .03em; color: var(--cor-texto-off); margin-bottom: .15rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.vit-nome {
  font-size: .86rem; font-weight: 500; color: var(--cor-texto); line-height: 1.3;
  display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;
  min-height: 2.6em;
}
.vit-preco { font-size: 1.25rem; font-weight: 700; color: var(--cor-texto); margin-top: .35rem; line-height: 1.1; }
.vit-parcela { font-size: .72rem; color: #22C55E; margin-bottom: .6rem; }
.vit-simular { margin-top: auto; border-radius: 8px; font-weight: 600; }
.vit-acoes { display: flex; gap: 4px; margin-top: .45rem; }
.vit-acao {
  flex: 1 1 0; height: 30px; min-width: 0;
  display: flex; align-items: center; justify-content: center;
  border: 1px solid var(--cor-borda); border-radius: 8px;
  background: transparent; color: var(--cor-texto-sec); font-size: .85rem; text-decoration: none;
  transition: all var(--trans);
}
.vit-acao:hover { border-color: var(--cor-azul); color: var(--cor-azul); background: rgba(59,130,246,.08); }
.vit-acao-perigo:hover { border-color: #EF4444; color: #EF4444; background: rgba(239,68,68,.08); }

/* Checkout */
.compra-forma.active, .compra-parc.active { background: var(--cor-azul); border-color: var(--cor-azul); color: #fff; }
.checkout-etapas { display: flex; align-items: center; gap: .6rem; }
.checkout-etapa { display: flex; align-items: center; gap: .45rem; font-size: .85rem; font-weight: 600; color: var(--cor-texto-off); }
.checkout-etapa span {
  width: 26px; height: 26px; border-radius: 50%; display: flex; align-items: center; justify-content: center;
  border: 2px solid var(--cor-borda); font-size: .8rem;
}
.checkout-etapa.ativa { color: var(--cor-texto); }
.checkout-etapa.ativa span { background: var(--cor-azul); border-color: var(--cor-azul); color: #fff; }
.checkout-etapa.feita span { background: #22C55E; border-color: #22C55E; color: #fff; }
.checkout-linha { flex: 1; height: 2px; background: var(--cor-borda); border-radius: 2px; }
.checkout-itens { display: flex; flex-direction: column; gap: .6rem; max-height: 52vh; overflow-y: auto; padding-right: 2px; }
.checkout-item {
  display: flex; gap: .75rem; align-items: flex-start;
  padding: .65rem; border: 1px solid var(--cor-borda); border-radius: 12px; background: rgba(255,255,255,.02);
}
.checkout-thumb {
  width: 64px; height: 64px; flex-shrink: 0; border-radius: 10px; overflow: hidden;
  background: #fff; display: flex; align-items: center; justify-content: center; color: #9CA3AF; font-size: 1.4rem;
}
.checkout-thumb img { max-width: 90%; max-height: 90%; object-fit: contain; }
.checkout-campos { flex: 1; min-width: 0; }
.checkout-cat { min-width: 0; }
.checkout-cat .cat-sel-btn { min-height: 31px; border: 1px solid var(--cor-borda); border-radius: 6px; background: var(--cor-input); }
.checkout-valor { width: 130px; flex-shrink: 0; text-align: right; font-weight: 600; }
.checkout-remover {
  border: none; background: transparent; color: var(--cor-texto-off);
  width: 30px; height: 30px; border-radius: 8px; flex-shrink: 0;
}
.checkout-remover:hover { background: rgba(239,68,68,.12); color: #EF4444; }
.checkout-subtotal {
  display: flex; justify-content: space-between; align-items: center;
  margin-top: .8rem; padding-top: .7rem; border-top: 1px solid var(--cor-borda);
  font-size: .95rem; color: var(--cor-texto-sec);
}
.checkout-subtotal strong { font-size: 1.25rem; color: var(--cor-texto); }
.checkout-resumo {
  border: 1px solid var(--cor-borda); border-radius: 12px; padding: .85rem; background: rgba(255,255,255,.02);
  position: sticky; top: 0;
}
.checkout-resumo-titulo { font-weight: 700; color: var(--cor-texto); margin-bottom: .5rem; }
.checkout-resumo-linha { display: flex; justify-content: space-between; gap: .75rem; font-size: .84rem; padding: .2rem 0; color: var(--cor-texto-sec); }
.checkout-resumo-linha span:last-child { white-space: nowrap; }
.checkout-resumo-linha.total { border-top: 1px solid var(--cor-borda); margin-top: .4rem; padding-top: .5rem; font-size: .95rem; color: var(--cor-texto); }
.checkout-resumo-linha.total strong { font-size: 1.2rem; }
.checkout-resumo-pag { font-size: .8rem; color: var(--cor-texto-off); margin-top: .5rem; }
.checkout-item .borda-erro, .checkout-cat.borda-erro .cat-sel-btn, #checkoutNomeGrupo.borda-erro { border-color: #EF4444 !important; }
.checkout-agrupar { padding: .75rem; border: 1px dashed var(--cor-borda); border-radius: 12px; }
.checkout-extras .form-control { text-align: right; }
.checkout-total-final {
  display: none; justify-content: space-between; align-items: center;
  margin-top: .6rem; padding: .55rem .75rem; border-radius: 10px;
  background: rgba(34,197,94,.08); border: 1px solid rgba(34,197,94,.25); color: var(--cor-texto-sec);
}
.checkout-total-final.com-ajuste { display: flex; }
.checkout-total-final strong { font-size: 1.2rem; color: #22C55E; }
.checkout-resumo-linha.sub { border-top: 1px dashed var(--cor-borda); margin-top: .3rem; padding-top: .4rem; }
.checkout-resumo-linha.desconto span:last-child { color: #22C55E; }
/* Agrupado: nome e categoria de cada item não são usados */
.checkout-itens.modo-agrupado .checkout-nome,
.checkout-itens.modo-agrupado .checkout-cat { opacity: .45; pointer-events: none; }
.checkout-resumo-grupo { font-size: .8rem; color: var(--cor-texto-sec); margin-top: .4rem; padding: .4rem .5rem; border-radius: 8px; background: rgba(59,130,246,.08); }
@media (max-width: 575.98px) {
  .checkout-thumb { width: 48px; height: 48px; }
  .checkout-valor { width: 110px; }
}

/* Seleção múltipla */
.desejo-check {
  position: absolute; top: 6px; right: 6px;
  width: 28px; height: 28px; border-radius: 8px;
  border: 2px solid rgba(0,0,0,.25);
  background: rgba(255,255,255,.9);
  color: transparent; display: flex; align-items: center; justify-content: center;
  font-size: .95rem; padding: 0; cursor: pointer;
  transition: background var(--trans), border-color var(--trans);
}
.desejo-check:hover { border-color: var(--cor-azul); }
.vit-card.selecionado { border-color: var(--cor-azul); box-shadow: 0 0 0 2px var(--cor-azul); }
.vit-card.selecionado .desejo-check { background: var(--cor-azul); border-color: var(--cor-azul); color: #fff; }
.barra-sel-desejos { flex-wrap: wrap; gap: .5rem; border-radius: 16px; max-width: calc(100vw - 2rem); }

html.light-mode .vit-card { background: #fff; border-color: #E5E7EB; }

@media (max-width: 575.98px) {
  .vitrine-controles { width: 100%; justify-content: space-between; }
  .vitrine-controles #vitOrdem { flex: 1 1 auto; }
  .vit-preco { font-size: 1.1rem; }
  .vit-acao { height: 34px; }
  .desejo-check { width: 32px; height: 32px; }
  .barra-sel-desejos .btn { flex: 1 1 auto; }
}
</style>

<?php require_once __DIR__ . '/../templates/footer.php'; ?>
