<?php require_once __DIR__ . '/../templates/header.php'; ?>

<div class="animate__animated animate__fadeIn">

    <!-- HEADER -->
    <div class="d-flex align-items-center justify-content-between titulo-pagina mb-4 flex-wrap gap-2">
        <h1 class="titulo mt-2 fs-titulo-pag">
            Responsáveis &nbsp;<i class="bi bi-people-fill titulo-azul"></i>
        </h1>
        <div class="d-flex align-items-center gap-2 flex-wrap">
            <select id="ordemPessoas" class="form-select form-select-sm" style="width:auto;" title="Ordenar pessoas">
                <option value="nome">Nome (A–Z)</option>
                <option value="medeve">Quem me deve mais</option>
                <option value="eudevo">A quem devo mais</option>
            </select>
            <div class="d-flex align-items-center gap-1 grupo-mes">
                <button class="btn btn-outline-secondary btn-sm" id="btnMesAnterior" aria-label="Mês anterior"><i class="bi bi-chevron-left"></i></button>
                <span class="titulo fs-5" id="mesAnoDisplay" style="min-width:130px;text-align:center;"></span>
                <button class="btn btn-outline-secondary btn-sm" id="btnMesSeguinte" aria-label="Próximo mês"><i class="bi bi-chevron-right"></i></button>
            </div>
            <a href="gerenciamento.php?tab=Responsaveis" class="btn btn-gerenciar btn-sm ms-2">
                <i class="bi bi-gear me-1"></i>Gerenciar
            </a>
        </div>
    </div>

    <div id="listaContas"></div>

</div>

<!-- MODAL NOVO ITEM (eu devo) -->
<div class="modal fade" id="modalNovoItem" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-plus-circle-fill titulo-azul me-2"></i>
                    Novo item — <span id="modalItemNome"></span>
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="itemRespId">
                <div class="mb-3">
                    <label class="form-label">Descrição</label>
                    <input type="text" class="form-control" id="itemDescricao" placeholder="Ex: Mercado, Conta de Luz">
                </div>
                <div class="mb-3">
                    <div class="item-tipo-toggle">
                        <button type="button" class="item-tipo-btn active" id="btnTipoAvista" data-tipo="avista">
                            <i class="bi bi-cash me-1"></i>À vista
                        </button>
                        <button type="button" class="item-tipo-btn" id="btnTipoParcelado" data-tipo="parcelado">
                            <i class="bi bi-list-ol me-1"></i>Parcelado
                        </button>
                    </div>
                </div>
                <div id="wrapperAvista">
                    <div class="mb-3">
                        <label class="form-label">Valor</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-cash-coin"></i></span>
                            <input type="text" class="form-control" id="itemValor" placeholder="R$ 0,00">
                        </div>
                    </div>
                </div>
                <div id="wrapperParcelado" style="display:none;">
                    <div class="row g-2 mb-2">
                        <div class="col-7">
                            <label class="form-label">Valor total</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-cash-coin"></i></span>
                                <input type="text" class="form-control" id="itemValorTotal" placeholder="R$ 0,00">
                            </div>
                        </div>
                        <div class="col-5">
                            <label class="form-label">Nº parcelas</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-list-ol"></i></span>
                                <input type="number" class="form-control" id="itemParcelas" placeholder="12" min="2" max="120" value="12">
                            </div>
                        </div>
                    </div>
                    <div id="itemParcelaPreview" class="parcela-preview" style="display:none;"></div>
                </div>
                <div class="mb-2">
                    <label class="form-label">Data <span id="itemDataLabel">da compra</span></label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-calendar3"></i></span>
                        <input type="date" class="form-control" id="itemData">
                    </div>
                    <div id="itemDataHint" class="form-text" style="display:none;color:var(--cor-texto-off);font-size:0.75rem;">
                        As parcelas serão lançadas mês a mês a partir desta data.
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Categoria <span style="color:var(--cor-texto-off);font-size:0.75rem;">(opcional)</span></label>
                    <select class="form-select" id="itemCategoria">
                        <option value="">— Sem categoria —</option>
                    </select>
                </div>
                <div class="mb-1">
                    <label class="form-label">Pagamento</label>
                    <div class="item-tipo-toggle" id="metodoToggle">
                        <button type="button" class="item-tipo-btn active" data-metodo="Dinheiro"><i class="bi bi-cash me-1"></i>Dinheiro</button>
                        <button type="button" class="item-tipo-btn" data-metodo="Pix"><i class="bi bi-qr-code me-1"></i>Pix</button>
                        <button type="button" class="item-tipo-btn" data-metodo="Débito"><i class="bi bi-credit-card me-1"></i>Débito</button>
                    </div>
                    <input type="hidden" id="itemMetodo" value="Dinheiro">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Fechar</button>
                <button type="button" class="btn btn-success btn-sm" id="salvarItem">
                    Adicionar <i class="bi bi-plus-lg ms-1"></i>
                </button>
            </div>
        </div>
    </div>
</div>

<style>
/* ─── Card de pessoa ─── */
.pessoa-card {
    background: var(--cor-painel);
    border: 1px solid var(--cor-borda);
    border-radius: var(--radius-lg);
    overflow: hidden;
    margin-bottom: 1rem;
    transition: box-shadow var(--trans);
}
.pessoa-card:hover { box-shadow: var(--sombra-sm); }

.pessoa-card-header {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 1rem 1.25rem;
    cursor: pointer;
    border-left: 5px solid var(--pessoa-cor, var(--cor-azul));
}
.pessoa-avatar {
    width: 44px; height: 44px; border-radius: 50%; flex-shrink: 0;
    background: color-mix(in srgb, var(--pessoa-cor, var(--cor-azul)) 20%, transparent);
    color: var(--pessoa-cor, var(--cor-azul));
    display: flex; align-items: center; justify-content: center;
    font-size: 1.15rem; font-weight: 700;
}
.pessoa-nome { font-weight: 700; font-size: 1rem; color: var(--cor-texto); }
.pessoa-meta { font-size: 0.75rem; color: var(--cor-texto-off); }

.pessoa-balances {
    margin-left: auto;
    display: flex;
    gap: 1rem;
    align-items: center;
}
.balance-pill {
    display: flex;
    flex-direction: column;
    align-items: flex-end;
    gap: 1px;
}
.balance-pill .bp-val {
    font-size: 0.95rem;
    font-weight: 700;
    line-height: 1;
}
.balance-pill .bp-label {
    font-size: 0.65rem;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    color: var(--cor-texto-off);
}
.bp-medeve  { color: var(--cor-sucesso); }
.bp-eudevo  { color: var(--cor-perigo); }
.bp-zero    { color: var(--cor-texto-off) !important; opacity: 0.5; }

.balance-sep {
    width: 1px; height: 32px;
    background: var(--cor-borda);
}

.pessoa-chevron {
    color: var(--cor-texto-off);
    transition: transform var(--trans);
    font-size: 0.85rem;
    margin-left: 0.75rem;
}
.pessoa-card.aberto .pessoa-chevron { transform: rotate(180deg); }

/* ─── Corpo expandido ─── */
.pessoa-body { display: none; }
.pessoa-card.aberto .pessoa-body { display: block; }

/* ─── Abas ─── */
.pessoa-tabs {
    display: flex;
    border-bottom: 1px solid var(--cor-borda);
    background: rgba(0,0,0,0.12);
}
.pessoa-tab-btn {
    flex: 1;
    padding: 0.65rem 0.5rem;
    border: none;
    border-bottom: 3px solid transparent;
    background: transparent;
    color: var(--cor-texto-sec);
    font-size: 0.82rem;
    font-weight: 600;
    cursor: pointer;
    transition: color var(--trans), border-color var(--trans);
    display: flex; align-items: center; justify-content: center; gap: 6px;
}
.pessoa-tab-btn:hover { color: var(--cor-texto); }
.pessoa-tab-btn.active {
    color: var(--cor-texto);
    border-bottom-color: var(--pessoa-cor, var(--cor-azul));
}
.pessoa-tab-btn .tab-badge {
    font-size: 0.68rem;
    padding: 1px 6px;
    border-radius: 10px;
    font-weight: 700;
}

/* ─── Painéis de aba ─── */
.pessoa-tab-panel { display: none; }
.pessoa-tab-panel.active { display: block; }

/* ─── Itens "eu devo" ─── */
.item-row {
    display: flex; align-items: center; gap: 0.75rem;
    padding: 0.65rem 1.25rem;
    border-bottom: 1px solid rgba(255,255,255,0.04);
    transition: background var(--trans);
}
.item-row:hover { background: rgba(255,255,255,0.03); }
.item-row.pago  { opacity: 0.45; }
.item-check {
    width: 20px; height: 20px; border-radius: 50%; flex-shrink: 0;
    border: 2px solid var(--cor-borda);
    display: flex; align-items: center; justify-content: center;
    cursor: pointer; font-size: 0.7rem; color: transparent;
    transition: border-color var(--trans), background var(--trans);
}
.item-check:hover   { border-color: var(--cor-sucesso); color: var(--cor-sucesso); }
.item-row.pago .item-check { border-color: var(--cor-sucesso); background: var(--cor-sucesso); color: #fff; }
.item-desc  { flex: 1; font-size: 0.86rem; color: var(--cor-texto); }
.item-row.pago .item-desc { text-decoration: line-through; color: var(--cor-texto-off); }
.item-data  { font-size: 0.73rem; color: var(--cor-texto-off); white-space: nowrap; }
.item-val   { font-size: 0.86rem; font-weight: 600; color: var(--cor-perigo); white-space: nowrap; }
.item-row.pago .item-val { color: var(--cor-texto-off); }
.item-del {
    background: none; border: none; cursor: pointer; padding: 2px 4px;
    color: var(--cor-texto-off); font-size: 0.78rem; border-radius: var(--radius-sm);
    transition: color var(--trans), background var(--trans);
}
.item-del:hover { color: var(--cor-perigo); background: rgba(239,68,68,0.1); }

/* ─── Itens "me deve" ─── */
.medeve-row {
    display: flex; align-items: center; gap: 0.75rem;
    padding: 0.65rem 1.25rem;
    border-bottom: 1px solid rgba(255,255,255,0.04);
    font-size: 0.86rem;
}
.medeve-slot  { width: 20px; flex-shrink: 0; display: flex; justify-content: center; }
.medeve-slot-icon { color: var(--cor-texto-off); font-size: 0.85rem; opacity: .7; }
.medeve-desc  { flex: 1; min-width: 0; color: var(--cor-texto); }
.medeve-cat   { font-size: 0.75rem; width: 130px; flex-shrink: 0; display: flex; justify-content: center; }
.medeve-st    { width: 125px; margin-right: .5rem; flex-shrink: 0; display: flex; justify-content: center; }
.medeve-data  { font-size: 0.73rem; color: var(--cor-texto-off); white-space: nowrap; width: 58px; flex-shrink: 0; text-align: right; }
.medeve-val   { font-weight: 600; color: var(--cor-sucesso); white-space: nowrap; width: 95px; flex-shrink: 0; text-align: right; }
@media (max-width: 576px) {
    .medeve-cat, .medeve-st { display: none; }
}
.medeve-row.pago .medeve-desc { text-decoration: line-through; color: var(--cor-texto-off); }
.medeve-row.pago .medeve-val  { color: var(--cor-texto-off); }
.medeve-status {
    font-size: 0.68rem; padding: 1px 8px; border-radius: 10px; white-space: nowrap;
    color: var(--cor-texto-off); border: 1px solid var(--cor-borda);
}
.medeve-link-fatura { text-decoration: none; color: var(--cor-texto-off); display: inline-flex; align-items: center; gap: .35rem; font-size: 0.75rem; }
.medeve-link-fatura:hover .medeve-status, .medeve-link-fatura:hover { color: var(--cor-azul); border-color: var(--cor-azul); }
.medeve-status.ok { color: var(--cor-sucesso); border-color: var(--cor-sucesso); }
.medeve-origem {
    font-size: 0.67rem; padding: 1px 6px; border-radius: 10px;
    background: rgba(59,130,246,0.15); color: var(--cor-azul);
    border: 1px solid rgba(59,130,246,0.3);
    white-space: nowrap;
}

/* ─── Rodapé do card ─── */
.pessoa-footer {
    display: flex; align-items: center; justify-content: space-between;
    padding: 0.7rem 1.25rem;
    background: rgba(0,0,0,0.1);
    gap: 1rem; flex-wrap: wrap;
    border-top: 1px solid var(--cor-borda);
}
.ft-grupo { display: flex; gap: 1.25rem; }
.ft-item label { font-size: 0.65rem; text-transform: uppercase; letter-spacing: 0.06em; color: var(--cor-texto-off); display: block; }
.ft-item span  { font-size: 0.86rem; font-weight: 600; }

.empty-panel {
    padding: 1.5rem; text-align: center;
    color: var(--cor-texto-off); font-size: 0.86rem;
}

/* ─── Toggle modal ─── */
.item-tipo-toggle { display: flex; gap: 6px; }
.item-tipo-btn {
    flex: 1; padding: 0.42rem 0; text-align: center;
    border: 1px solid var(--cor-borda); border-radius: var(--radius-sm);
    background: var(--cor-input); color: var(--cor-texto-sec);
    font-size: 0.82rem; font-weight: 500; cursor: pointer;
    transition: background var(--trans), color var(--trans), border-color var(--trans);
}
.item-tipo-btn:hover { border-color: var(--cor-azul); color: var(--cor-texto); }
.item-tipo-btn.active { background: var(--cor-azul); border-color: var(--cor-azul); color: #fff; }

.parcela-preview {
    background: rgba(59,130,246,0.1); border: 1px solid rgba(59,130,246,0.3);
    border-radius: var(--radius-sm); padding: 0.45rem 0.85rem;
    font-size: 0.82rem; color: var(--cor-azul); margin-top: 6px;
}
</style>

<script>
var mesSel  = new Date().getMonth() + 1;
var anoSel  = new Date().getFullYear();
var hoje    = new Date().toISOString().split('T')[0];

// Marco inicial: abre no mês do marco se o atual for anterior (antes não há dados).
if (window.mesInicialPadrao) {
    var _mpResp = window.mesInicialPadrao(mesSel, anoSel);
    mesSel = _mpResp.mes;
    anoSel = _mpResp.ano;
}
var tipoItem = 'avista';
var MESES   = ['Janeiro','Fevereiro','Março','Abril','Maio','Junho',
               'Julho','Agosto','Setembro','Outubro','Novembro','Dezembro'];

function atualizaDisplay() {
    $('#mesAnoDisplay').text(MESES[mesSel - 1] + ' ' + anoSel);
}

// ── Ordenação ──────────────────────────────────────────────────
// Antes vinha por "devo a ela" (servidor), que muda a cada mês e embaralhava a
// lista. Padrão: nome; a escolha fica salva neste navegador.
var ORDEM_KEY = 'skyOrdemPessoas';
function ordemAtual() {
    try { return localStorage.getItem(ORDEM_KEY) || 'nome'; } catch (e) { return 'nome'; }
}
function ordenaPessoas(lista) {
    var ordem  = ordemAtual();
    var porNome = function (a, b) { return String(a.nome).localeCompare(String(b.nome), 'pt-BR', { sensitivity: 'base' }); };
    lista.sort(function (a, b) {
        if (ordem === 'medeve') return (parseFloat(b.me_deve_aberto) || 0) - (parseFloat(a.me_deve_aberto) || 0) || porNome(a, b);
        if (ordem === 'eudevo') return (parseFloat(b.eu_devo) || 0) - (parseFloat(a.eu_devo) || 0) || porNome(a, b);
        return porNome(a, b);
    });
    return lista;
}
$('#ordemPessoas').val(ordemAtual()).on('change', function () {
    try { localStorage.setItem(ORDEM_KEY, this.value); } catch (e) {}
    carregaContas();
});

// ── Carregar resumo ────────────────────────────────────────────
function carregaContas() {
    if (window.atualizaAvisoMarco) atualizaAvisoMarco(mesSel, anoSel);
    $('#listaContas').html('<div class="text-center py-5"><div class="spinner-border" style="color:var(--cor-azul);" role="status"></div></div>');
    $.ajax({
        type: 'POST', url: App.ctrl.responsaveis,
        data: { acao: 'contas.resumo', mes: mesSel, ano: anoSel }, dataType: 'json',
        error: function (xhr) {
            $('#listaContas').html(
                '<div class="painel text-center py-4">' +
                '<i class="bi bi-exclamation-triangle-fill" style="font-size:2rem;color:var(--cor-perigo);"></i>' +
                '<p class="mt-2 mb-1" style="color:var(--cor-texto);">Erro ao carregar responsáveis.</p>' +
                '<pre style="font-size:0.72rem;color:var(--cor-texto-off);text-align:left;max-height:200px;overflow:auto;background:var(--cor-input);padding:8px;border-radius:6px;">' +
                (xhr.responseText || 'Sem resposta do servidor') + '</pre>' +
                '</div>');
        },
        success: function (data) {
            if (!data || !data.length) {
                $('#listaContas').html(
                    '<div class="painel text-center py-5">' +
                    '<i class="bi bi-people" style="font-size:3rem;color:var(--cor-borda);"></i>' +
                    '<p class="mt-3 mb-1" style="color:var(--cor-texto-off);">Nenhuma pessoa cadastrada.</p>' +
                    '<a href="gerenciamento.php" class="btn btn-sm btn-outline-primary mt-2">Cadastrar no Gerenciar</a>' +
                    '</div>');
                return;
            }

            ordenaPessoas(data);

            var html = '';
            $.each(data, function (_, p) {
                var cor      = p.cor || '#6B7280';
                var euDevo   = parseFloat(p.eu_devo)  || 0;
                // Em aberto (igual ao "devo a ela", que só soma o não pago)
                var meDeve   = parseFloat(p.me_deve_aberto) || 0;
                var qtd      = parseInt(p.qtd_aberto) || 0;
                var fmtDevo  = 'R$ ' + euDevo.toLocaleString('pt-BR', {minimumFractionDigits:2});
                var fmtDeve  = 'R$ ' + meDeve.toLocaleString('pt-BR', {minimumFractionDigits:2});

                var badgeDevo = qtd > 0
                    ? '<span class="tab-badge" style="background:rgba(239,68,68,0.18);color:#EF4444;">' + qtd + '</span>'
                    : '';

                html +=
                '<div class="pessoa-card" id="card-' + p.id + '" data-id="' + p.id + '" data-arquivada="' + (p.arquivado_em ? 1 : 0) + '" style="--pessoa-cor:' + cor + ';">' +
                    '<div class="pessoa-card-header">' +
                        '<div class="pessoa-avatar">' + escHtml(p.nome.charAt(0).toUpperCase()) + '</div>' +
                        '<div class="flex-grow-1 min-w-0">' +
                            '<div class="pessoa-nome">' + escHtml(p.nome) + '</div>' +
                            '<div class="pessoa-meta">' + (p.arquivado_em
                                ? '<i class="bi bi-archive me-1"></i>Arquivada — só histórico'
                                : 'Clique para ver detalhes') + '</div>' +
                        '</div>' +
                        '<div class="pessoa-balances">' +
                            '<div class="balance-pill">' +
                                '<div class="bp-val bp-medeve' + (meDeve === 0 ? ' bp-zero' : '') + '">' + fmtDeve + '</div>' +
                                '<div class="bp-label">me deve</div>' +
                            '</div>' +
                            '<div class="balance-sep"></div>' +
                            '<div class="balance-pill">' +
                                '<div class="bp-val bp-eudevo' + (euDevo === 0 ? ' bp-zero' : '') + '">' + fmtDevo + '</div>' +
                                '<div class="bp-label">devo a ela</div>' +
                            '</div>' +
                            pillAbater(meDeve, euDevo) +
                        '</div>' +
                        '<i class="bi bi-chevron-down pessoa-chevron"></i>' +
                    '</div>' +

                    '<div class="pessoa-body">' +
                        '<div class="pessoa-tabs">' +
                            '<button class="pessoa-tab-btn active" data-tab="devo" data-pessoa="' + p.id + '">' +
                                '<i class="bi bi-arrow-up-circle-fill" style="color:var(--cor-perigo);"></i> Devo a ela ' + badgeDevo +
                            '</button>' +
                            '<button class="pessoa-tab-btn" data-tab="medeve" data-pessoa="' + p.id + '">' +
                                '<i class="bi bi-arrow-down-circle-fill" style="color:var(--cor-sucesso);"></i> Ela me deve' +
                            '</button>' +
                        '</div>' +
                        '<div class="pessoa-tab-panel active" id="panel-devo-' + p.id + '">' +
                            '<div class="text-center py-3"><div class="spinner-border spinner-border-sm" style="color:var(--cor-azul);" role="status"></div></div>' +
                        '</div>' +
                        '<div class="pessoa-tab-panel" id="panel-medeve-' + p.id + '">' +
                            '<div class="text-center py-3"><div class="spinner-border spinner-border-sm" style="color:var(--cor-azul);" role="status"></div></div>' +
                        '</div>' +
                    '</div>' +
                '</div>';
            });
            $('#listaContas').html(html);
        }
    });
}

// ── Toggle card ────────────────────────────────────────────────
$(document).on('click', '.pessoa-card-header', function () {
    var card     = $(this).closest('.pessoa-card');
    var id       = card.data('id');
    var jaAberto = card.hasClass('aberto');

    $('.pessoa-card').removeClass('aberto');

    if (!jaAberto) {
        card.addClass('aberto');
        carregaItensEuDevo(id);
        carregaItensMeDeve(id);
    }
});

// ── Troca de aba ────────────────────────────────────────────────
$(document).on('click', '.pessoa-tab-btn', function (e) {
    e.stopPropagation();
    var pessoaId = $(this).data('pessoa');
    var tab      = $(this).data('tab');
    var card     = $('#card-' + pessoaId);

    card.find('.pessoa-tab-btn').removeClass('active');
    $(this).addClass('active');
    card.find('.pessoa-tab-panel').removeClass('active');
    card.find('#panel-' + tab + '-' + pessoaId).addClass('active');
});

// ── ABA: Eu devo ────────────────────────────────────────────────
function carregaItensEuDevo(respId) {
    $.ajax({
        type: 'POST', url: App.ctrl.responsaveis,
        data: { acao: 'contas.listar', id: respId, mes: mesSel, ano: anoSel }, dataType: 'json',
        error: function (xhr) {
            $('#panel-devo-' + respId).html('<div class="empty-panel text-danger"><i class="bi bi-exclamation-triangle me-1"></i>' + (xhr.responseText || 'Erro') + '</div>');
        },
        success: function (data) { renderEuDevo(respId, data); }
    });
}

function renderEuDevo(respId, data) {
    var $panel = $('#panel-devo-' + respId);
    var totalAberto = 0, totalPago = 0, rows = '';

    if (!data || !data.length) {
        rows = '<div class="empty-panel"><i class="bi bi-inbox me-2"></i>Nenhum item registrado.</div>';
    } else {
        $.each(data, function (_, item) {
            var p = item.pago === 'S';
            totalAberto += p ? 0 : item.valor;
            totalPago   += p ? item.valor : 0;
            var dataFmt  = item.data ? moment(item.data).format('DD/MM/YY') : '';
            var catHtml  = item.categoria ? catBadgeHtml(item.categoria) : '';
            var ehCF     = item.origem === 'conta_fixa';
            // Conta fixa: pagar aqui = pagar a conta (mesmo registro da tela Contas Fixas).
            // Sem lixeira — para tirar da pessoa, edite a conta ou pague como "eu mesmo".
            var cfBadge  = ehCF ? ' <span class="medeve-origem ms-1" style="background:rgba(249,115,22,0.15);color:#F97316;border-color:rgba(249,115,22,0.3);">Conta fixa</span>' : '';
            rows +=
            '<div class="item-row' + (p ? ' pago' : '') + (ehCF ? ' cf-item' : '') + '" data-id="' + item.id + '"' +
                (ehCF ? ' data-valor="' + item.valor + '"' : '') + '>' +
                '<div class="item-check" title="' + (p ? 'Reabrir' : (ehCF ? 'Dei o dinheiro — marcar conta paga' : 'Marcar pago')) + '"><i class="bi bi-check-lg"></i></div>' +
                '<div class="item-desc">' + escHtml(item.descricao) + cfBadge + (catHtml ? '<br><span class="mt-1 d-inline-block">' + catHtml + '</span>' : '') + '</div>' +
                '<div class="item-data">' + dataFmt + '</div>' +
                '<div class="item-val">R$ ' + item.valor.toLocaleString('pt-BR',{minimumFractionDigits:2}) + '</div>' +
                (ehCF
                    ? '<span class="item-del" style="visibility:hidden;"><i class="bi bi-trash3"></i></span>'
                    : '<button class="item-del" data-id="' + item.id + '"><i class="bi bi-trash3"></i></button>') +
            '</div>';
        });
    }

    var fmtAberto = 'R$ ' + totalAberto.toLocaleString('pt-BR',{minimumFractionDigits:2});
    var fmtPago   = 'R$ ' + totalPago.toLocaleString('pt-BR',{minimumFractionDigits:2});
    var footer =
        '<div class="pessoa-footer">' +
            '<div class="ft-grupo">' +
                '<div class="ft-item"><label>Em aberto</label><span style="color:var(--cor-perigo);">' + fmtAberto + '</span></div>' +
                '<div class="ft-item"><label>Já pago</label><span style="color:var(--cor-sucesso);">' + fmtPago + '</span></div>' +
            '</div>' +
            (parseInt($('#card-' + respId).data('arquivada'), 10) === 1 ? '' :
            '<button class="btn btn-sm btn-success btnNovoItem" data-id="' + respId + '">' +
                '<i class="bi bi-plus-lg me-1"></i>Adicionar item' +
            '</button>') +
        '</div>';

    $panel.html(rows + footer);
}

// ── ABA: Ela me deve ────────────────────────────────────────────
function carregaItensMeDeve(respId) {
    $.ajax({
        type: 'POST', url: App.ctrl.responsaveis,
        data: { acao: 'contas.medeve', id: respId, mes: mesSel, ano: anoSel }, dataType: 'json',
        error: function (xhr) {
            $('#panel-medeve-' + respId).html('<div class="empty-panel text-danger"><i class="bi bi-exclamation-triangle me-1"></i>' + (xhr.responseText || 'Erro') + '</div>');
        },
        success: function (data) { renderMeDeve(respId, data); }
    });
}

function renderMeDeve(respId, data) {
    var $panel = $('#panel-medeve-' + respId);
    var aberto = 0, recebido = 0, rows = '';

    if (!data || !data.length) {
        rows = '<div class="empty-panel"><i class="bi bi-inbox me-2"></i>Nenhuma despesa no nome dela neste mês.</div>';
    } else {
        $.each(data, function (_, d) {
            if (d.recebido) recebido += d.valor; else aberto += d.valor;
            var dataFmt   = d.data ? moment(d.data).format('DD/MM/YY') : '';
            var catHtml   = catBadgeHtml(d.categoria);
            var recBadge  = d.origem === 'recorrente' ? '<span class="medeve-origem ms-1">Recorrente</span>' : '';
            var quando    = d.recebido_em ? moment(d.recebido_em).format('DD/MM/YY') : '';

            // Crédito: status vem da fatura paga (sem botão). Demais: check manual.
            var status;
            if (d.forma === 'fatura') {
                status = d.recebido
                    ? '<span class="medeve-status ok" title="Fatura paga em ' + quando + '"><i class="bi bi-credit-card-2-back-fill me-1"></i>Fatura paga</span>'
                    : '<span class="medeve-status" title="Fica quitado quando a fatura do cartão for marcada como paga"><i class="bi bi-credit-card-2-back me-1"></i>Na fatura</span>';
                // Clique no status abre a fatura do cartão no mês de vencimento
                var dv = moment(d.data);
                status = '<a class="medeve-link-fatura" title="Ver na fatura' + (d.nome_cartao ? ' — ' + escHtml(d.nome_cartao) : '') + '"' +
                         ' href="' + App.base + '/php/views/cartaocredito.php?mes=' + (dv.month() + 1) + '&ano=' + dv.year() +
                         (d.cartao_id ? '&cartao=' + d.cartao_id : '') +
                         '&item=' + (d.origem === 'recorrente' ? 'recorrente-' + d.recorrente_id : 'gasto-' + d.id) + '">' + status +
                         '<i class="bi bi-box-arrow-up-right"></i></a>';
            } else {
                status = '<div class="item-check medeve-check" data-alvo="' + d.alvo + '" data-id="' + d.id + '"' +
                         ' title="' + (d.recebido ? 'Recebido em ' + quando + ' — clique para reabrir' : 'Marcar como recebido') + '">' +
                         '<i class="bi bi-check-lg"></i></div>';
            }

            rows +=
            '<div class="medeve-row item-row' + (d.recebido ? ' pago' : '') + '">' +
                // Colunas fixas: slot do check (vazio no crédito) e slot de status
                // (vazio no Pix/dinheiro) — assim tudo fica alinhado.
                '<div class="medeve-slot">' + (d.forma === 'fatura' ? '<i class="bi bi-credit-card-2-back medeve-slot-icon" title="Crédito"></i>' : status) + '</div>' +
                '<div class="medeve-desc">' + escHtml(d.nome || '—') + recBadge + '</div>' +
                '<div class="medeve-st">' + (d.forma === 'fatura' ? status : '') + '</div>' +
                '<div class="medeve-cat">' + catHtml + '</div>' +
                '<div class="medeve-data">' + dataFmt + '</div>' +
                '<div class="medeve-val">R$ ' + d.valor.toLocaleString('pt-BR',{minimumFractionDigits:2}) + '</div>' +
                '<button class="item-del medeve-desvincular" title="Tirar desta pessoa (a despesa continua sua)"' +
                    ' data-tipo="' + (d.origem === 'recorrente' ? 'recorrente' : 'gasto') + '"' +
                    ' data-id="' + (d.origem === 'recorrente' ? d.recorrente_id : d.id) + '"' +
                    ' data-nome="' + escHtml(d.nome || '') + '"><i class="bi bi-person-dash"></i></button>' +
            '</div>';
        });
    }

    var fmt = function (v) { return 'R$ ' + v.toLocaleString('pt-BR',{minimumFractionDigits:2}); };
    var footer =
        '<div class="pessoa-footer">' +
            '<div class="ft-grupo">' +
                '<div class="ft-item"><label>Em aberto</label><span style="color:var(--cor-sucesso);">' + fmt(aberto) + '</span></div>' +
                '<div class="ft-item"><label>Já recebido</label><span style="color:var(--cor-texto-off);">' + fmt(recebido) + '</span></div>' +
            '</div>' +
            '<small style="color:var(--cor-texto-off);font-size:0.75rem;">Crédito é quitado ao pagar a fatura; Pix/débito, pelo check</small>' +
        '</div>';

    $panel.html(rows + footer);
}

// ── Tirar despesa da pessoa ────────────────────────────────────
$(document).on('click', '.medeve-desvincular', function (e) {
    e.stopPropagation();
    var $b     = $(this);
    var respId = $b.closest('.pessoa-card').data('id');
    var tipo   = $b.data('tipo');
    var aviso  = tipo === 'recorrente'
        ? 'É um recorrente: sai desta pessoa em todos os meses.'
        : 'Se for parcelado, todas as parcelas saem desta pessoa.';
    Swal.fire({
        title: 'Tirar "' + $b.data('nome') + '" desta pessoa?',
        text: 'A despesa continua sua, só deixa de ser cobrada dela. ' + aviso,
        icon: 'question', showCancelButton: true,
        confirmButtonText: 'Tirar', cancelButtonText: 'Cancelar'
    }).then(function (r) {
        if (!r.isConfirmed) return;
        $.ajax({
            type: 'POST', url: App.ctrl.responsaveis,
            data: { acao: 'contas.desvincular', tipo: tipo, id: $b.data('id') }, dataType: 'json',
            success: function (ok) {
                if (!ok) { toastr.error('Não foi possível alterar.'); return; }
                toastr.success('Despesa retirada da pessoa.');
                carregaItensMeDeve(respId);
                atualizaPillMeDeve(respId);
            },
            error: function () { toastr.error('Erro ao alterar.'); }
        });
    });
});

// ── Marcar recebido ("ela me deve", Pix/débito/dinheiro) ────────
$(document).on('click', '.medeve-check', function (e) {
    e.stopPropagation();
    var $c     = $(this);
    var respId = $c.closest('.pessoa-card').data('id');
    var marcar = $c.closest('.item-row').hasClass('pago') ? 0 : 1;
    $.ajax({
        type: 'POST', url: App.ctrl.responsaveis,
        data: { acao: 'contas.recebido', alvo: $c.data('alvo'), id: $c.data('id'), recebido: marcar }, dataType: 'json',
        success: function (ok) {
            if (!ok) { toastr.error('Não foi possível atualizar.'); return; }
            carregaItensMeDeve(respId);
            atualizaPillMeDeve(respId);
        },
        error: function () { toastr.error('Erro ao salvar.'); }
    });
});

// "Se abater": quando os dois lados têm valor, mostra o saldo líquido — quem paga
// quanto para o outro depois de compensar uma dívida com a outra.
function pillAbater(meDeve, euDevo) {
    if (!(meDeve > 0 && euDevo > 0)) return '';
    var liq = Math.round((meDeve - euDevo) * 100) / 100;
    var fmt = 'R$ ' + Math.abs(liq).toLocaleString('pt-BR', {minimumFractionDigits:2});
    var cls = liq > 0 ? 'bp-medeve' : (liq < 0 ? 'bp-eudevo' : 'bp-zero');
    var lbl = liq > 0 ? 'abatido: ela me paga' : (liq < 0 ? 'abatido: eu pago' : 'abatido: quites');
    return '<div class="balance-sep bp-abater-sep"></div>' +
        '<div class="balance-pill bp-abater" title="Se abater: me deve − devo a ela">' +
            '<div class="bp-val ' + cls + '">' + (liq === 0 ? 'R$ 0,00' : fmt) + '</div>' +
            '<div class="bp-label">' + lbl + '</div>' +
        '</div>';
}

// Atualiza só o valor "me deve" do cabeçalho, sem recarregar a lista (que fecharia o card)
function atualizaPillMeDeve(respId) {
    $.ajax({
        type: 'POST', url: App.ctrl.responsaveis,
        data: { acao: 'contas.resumo', mes: mesSel, ano: anoSel }, dataType: 'json',
        success: function (data) {
            var p = (data || []).find(function (x) { return String(x.id) === String(respId); });
            if (!p) return;
            var v = parseFloat(p.me_deve_aberto) || 0;
            $('#card-' + respId + ' .bp-medeve')
                .text('R$ ' + v.toLocaleString('pt-BR', {minimumFractionDigits:2}))
                .toggleClass('bp-zero', v === 0);
            var $bal = $('#card-' + respId + ' .pessoa-balances');
            $bal.find('.bp-abater, .bp-abater-sep').remove();
            $bal.append(pillAbater(v, parseFloat(p.eu_devo) || 0));
        }
    });
}

// ── Marcar pago / reabrir ───────────────────────────────────────
$(document).on('click', '.item-check', function () {
    if ($(this).hasClass('medeve-check')) return; // "ela me deve" tem handler próprio
    var row    = $(this).closest('.item-row');
    if (row.hasClass('cf-item')) {
        // Conta fixa paga por meio da pessoa: registra/desfaz o pagamento da conta
        var rId  = row.closest('.pessoa-card').data('id');
        var pagar = !row.hasClass('pago');
        $.ajax({
            type: 'POST', url: App.ctrl.contasFixas,
            data: pagar
                ? { acao: 'marcarPago', id: row.data('id'), mes: mesSel, ano: anoSel,
                    data: moment().format('YYYY-MM-DD'), valor_pago: String(row.data('valor')).replace('.', ','), responsavel: rId }
                : { acao: 'desmarcarPago', id: row.data('id'), mes: mesSel, ano: anoSel },
            dataType: 'json',
            success: function () { carregaItensEuDevo(rId); carregaContas(); },
            error: function () { toastr.error('Erro ao atualizar a conta fixa.'); }
        });
        return;
    }
    var id     = row.data('id');
    var isPago = row.hasClass('pago') ? 0 : 1;
    var respId = row.closest('.pessoa-card').data('id');
    $.ajax({
        type: 'POST', url: App.ctrl.responsaveis,
        data: { acao: 'contas.pago', id: id, pago: isPago }, dataType: 'json',
        success: function (ok) { if (ok) { carregaItensEuDevo(respId); carregaContas(); } }
    });
});

// ── Remover item ────────────────────────────────────────────────
$(document).on('click', '.item-del', function (e) {
    e.stopPropagation();
    if ($(this).hasClass('medeve-desvincular') || !$(this).data('id')) return;
    var id     = $(this).data('id');
    var respId = $(this).closest('.pessoa-card').data('id');
    Swal.fire({
        title: 'Remover item?', icon: 'warning', showCancelButton: true,
        confirmButtonColor: '#EF4444', cancelButtonColor: '#6B7280',
        confirmButtonText: 'Remover', cancelButtonText: 'Cancelar'
    }).then(function (r) {
        if (r.isConfirmed) {
            $.ajax({
                type: 'POST', url: App.ctrl.responsaveis,
                data: { acao: 'contas.remover', id: id }, dataType: 'json',
                success: function (ok) { if (ok) { carregaItensEuDevo(respId); carregaContas(); } }
            });
        }
    });
});

// ── Abrir modal novo item ───────────────────────────────────────
$(document).on('click', '.btnNovoItem', function (e) {
    e.stopPropagation();
    var id   = $(this).data('id');
    var nome = $('#card-' + id + ' .pessoa-nome').text();
    $('#itemRespId').val(id);
    $('#modalItemNome').text(nome);
    $('#itemDescricao').val('');
    $('#itemValor').val('');
    $('#itemValorTotal').val('');
    $('#itemParcelas').val('12');
    $('#itemData').val(hoje);
    $('#itemParcelaPreview').hide();
    tipoItem = 'avista';
    $('.item-tipo-btn').removeClass('active');
    $('#btnTipoAvista').addClass('active');
    $('#wrapperAvista').show();
    $('#wrapperParcelado').hide();
    $('#itemDataLabel').text('da compra');
    $('#itemDataHint').hide();

    // Categoria
    var $sel = $('#itemCategoria');
    $sel.html('<option value="">— Sem categoria —</option>');
    Object.keys(window.categoriaMap || {}).forEach(function (k) {
        var c = window.categoriaMap[k];
        $sel.append('<option value="' + k + '">' + (c.icone ? c.icone + ' ' : '') + escHtml(c.nome) + '</option>');
    });

    // Método
    $('#itemMetodo').val('Dinheiro');
    $('#metodoToggle .item-tipo-btn').removeClass('active');
    $('#metodoToggle .item-tipo-btn[data-metodo="Dinheiro"]').addClass('active');

    $('#modalNovoItem').modal('show');
});

// ── Toggle método pagamento ─────────────────────────────────────
$(document).on('click', '#metodoToggle .item-tipo-btn', function () {
    $('#metodoToggle .item-tipo-btn').removeClass('active');
    $(this).addClass('active');
    $('#itemMetodo').val($(this).data('metodo'));
});

// ── Toggle à vista / parcelado ──────────────────────────────────
$(document).on('click', '.item-tipo-btn', function () {
    tipoItem = $(this).data('tipo');
    $('.item-tipo-btn').removeClass('active');
    $(this).addClass('active');
    if (tipoItem === 'parcelado') {
        $('#wrapperAvista').hide();
        $('#wrapperParcelado').show();
        $('#itemDataLabel').text('da 1ª parcela');
        $('#itemDataHint').show();
    } else {
        $('#wrapperAvista').show();
        $('#wrapperParcelado').hide();
        $('#itemDataLabel').text('da compra');
        $('#itemDataHint').hide();
        $('#itemParcelaPreview').hide();
    }
});

function atualizaPreviewParcela() {
    var raw = $('#itemValorTotal').val();
    var n   = parseInt($('#itemParcelas').val()) || 0;
    if (!raw || !n) { $('#itemParcelaPreview').hide(); return; }
    var total = parseFloat(raw.replace(/R\$\s?/g,'').replace(/\./g,'').replace(',','.')) || 0;
    if (!total) { $('#itemParcelaPreview').hide(); return; }
    var parcela = total / n;
    $('#itemParcelaPreview')
        .html('<i class="bi bi-info-circle me-1"></i>' + n + 'x de <strong>R$ ' +
              parcela.toLocaleString('pt-BR',{minimumFractionDigits:2}) + '</strong>')
        .show();
}
$('#itemValorTotal').on('input', atualizaPreviewParcela);
$('#itemParcelas').on('input', atualizaPreviewParcela);

// ── Salvar item ─────────────────────────────────────────────────
$('#salvarItem').click(function () {
    var respId = $('#itemRespId').val();
    var desc   = $('#itemDescricao').val().trim();
    var data   = $('#itemData').val();
    if (!desc || !data) { toastr.warning('Preencha todos os campos!'); return; }

    if (tipoItem === 'parcelado') {
        var rawTotal = $('#itemValorTotal').val();
        var total    = parseFloat(rawTotal.replace(/R\$\s?/g,'').replace(/\./g,'').replace(',','.')) || 0;
        var n        = parseInt($('#itemParcelas').val()) || 0;
        if (total <= 0 || n < 2) { toastr.warning('Informe o valor total e parcelas (mín. 2)!'); return; }
        salvarParcelado(respId, desc, total, n, data);
    } else {
        var valor = $('#itemValor').val();
        if (!valor) { toastr.warning('Informe o valor!'); return; }
        $.ajax({
            type: 'POST', url: App.ctrl.responsaveis,
            data: {
                acao: 'contas.adicionar', id: respId, descricao: desc, valor: valor, data: data,
                categoria: $('#itemCategoria').val(),
                metodo_pagamento: $('#itemMetodo').val()
            },
            dataType: 'json',
            success: function (ok) {
                if (ok) {
                    toastr.success('Item adicionado!');
                    $('#modalNovoItem').modal('hide');
                    carregaItensEuDevo(respId);
                    carregaContas();
                } else { toastr.error('Erro ao salvar!'); }
            }
        });
    }
});

function salvarParcelado(respId, desc, total, n, dataInicio) {
    // Em centavos, com a última parcela absorvendo o resíduo (100/3 = 33,33 + 33,33 + 33,34),
    // mesma regra do parcelamento de crédito — antes a soma das parcelas ficava abaixo do total.
    var totalCent   = Math.round(total * 100);
    var parcelaCent = Math.floor(totalCent / n);
    var dataMoment  = moment(dataInicio);
    var catId       = $('#itemCategoria').val();
    var metodo      = $('#itemMetodo').val();
    var $btn        = $('#salvarItem').prop('disabled', true);

    function concluir() {
        $btn.prop('disabled', false);
        carregaItensEuDevo(respId);
        carregaContas();
    }

    // Uma parcela por vez: em paralelo elas eram gravadas fora de ordem, e uma
    // falha no meio não dizia quantas já tinham sido salvas.
    function salvar(i) {
        if (i > n) {
            toastr.success(n + ' parcelas adicionadas!');
            $('#modalNovoItem').modal('hide');
            concluir();
            return;
        }
        var cent = (i === n) ? totalCent - parcelaCent * (n - 1) : parcelaCent;
        $.ajax({
            type: 'POST', url: App.ctrl.responsaveis,
            data: {
                acao: 'contas.adicionar', id: respId,
                descricao: desc + ' (' + i + '/' + n + ')',
                valor: (cent / 100).toFixed(2).replace('.', ','),
                data:  dataMoment.clone().add(i - 1, 'months').format('YYYY-MM-DD'),
                categoria: catId,
                metodo_pagamento: metodo
            },
            dataType: 'json'
        }).then(function (ok) {
            if (ok) { salvar(i + 1); return; }
            toastr.error('Erro ao salvar a parcela ' + i + '/' + n + ' (' + (i - 1) + ' salvas).');
            concluir();
        }, function () {
            toastr.error('Erro ao salvar a parcela ' + i + '/' + n + ' (' + (i - 1) + ' salvas).');
            concluir();
        });
    }
    salvar(1);
}

// ── Navegação mês ───────────────────────────────────────────────
$('#btnMesAnterior').click(function () {
    mesSel--; if (mesSel < 1) { mesSel = 12; anoSel--; }
    atualizaDisplay(); carregaContas();
});
$('#btnMesSeguinte').click(function () {
    mesSel++; if (mesSel > 12) { mesSel = 1; anoSel++; }
    atualizaDisplay(); carregaContas();
});

bancInput(document.getElementById('itemValor'));
bancInput(document.getElementById('itemValorTotal'));

atualizaDisplay();
carregaContas();

$.ajax({
    type: 'POST', url: App.ctrl.categoria,
    data: { acao: 'busca' }, dataType: 'json',
    success: function (data) { popularCatSelect(data); }
});
</script>

<?php require_once __DIR__ . '/../templates/footer.php'; ?>
