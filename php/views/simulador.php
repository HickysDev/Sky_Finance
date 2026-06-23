<?php require_once __DIR__ . '/../templates/header.php'; ?>

<div class="animate__animated animate__fadeIn">

    <!-- HEADER -->
    <div class="d-flex align-items-center justify-content-between titulo-pagina mb-4 flex-wrap gap-2">
        <h1 class="titulo mt-2 fs-titulo-pag">
            Simulador &nbsp;<i class="bi bi-calculator-fill titulo-azul"></i>
        </h1>
        <p class="mb-0" style="font-size:0.83rem;color:var(--cor-texto-off);">
            Veja o impacto nas faturas antes de confirmar a compra
        </p>
    </div>

    <div class="row g-4 align-items-start">

        <!-- ── FORMULÁRIO ──────────────────────────────────────────── -->
        <div class="col-12 col-lg-4">
            <div class="painel">
                <h6 class="titulo mb-4"><i class="bi bi-pencil-square titulo-azul me-2"></i>Dados da Compra</h6>

                <!-- Descrição -->
                <div class="mb-3">
                    <label class="form-label">Descrição</label>
                    <input type="text" id="simDescricao" class="form-control" placeholder="Ex: iPhone 16, Geladeira...">
                </div>

                <!-- Valor -->
                <div class="mb-3">
                    <label class="form-label">Valor total</label>
                    <input type="text" id="simValor" class="form-control" placeholder="R$ 0,00">
                </div>

                <!-- Categoria -->
                <div class="mb-3">
                    <label class="form-label">Categoria</label>
                    <div class="dropdown">
                        <button class="form-control text-start d-flex align-items-center gap-2 cat-sel-btn" type="button"
                                id="simCatSelBtn" data-bs-toggle="dropdown" aria-expanded="false"
                                style="background:var(--cor-input,#23243a);border:1px solid var(--cor-borda);">
                            <span class="cat-sel-preview sim-cat-preview"><span class="text-muted">Selecione</span></span>
                            <i class="bi bi-chevron-down ms-auto" style="font-size:0.75rem;opacity:0.5;"></i>
                        </button>
                        <ul class="dropdown-menu w-100 cat-sel-menu" id="simCatSelMenu"
                            style="background:#2B2C3B;max-height:220px;overflow-y:auto;"></ul>
                    </div>
                    <input type="hidden" id="simCategoria">
                </div>

                <!-- Cartão -->
                <div class="mb-3">
                    <label class="form-label">Cartão</label>
                    <div id="simCartoesWrap" class="d-flex flex-wrap gap-2">
                        <div class="text-center py-2" style="color:var(--cor-texto-off);font-size:0.82rem;">
                            <div class="spinner-border spinner-border-sm me-1" style="color:var(--cor-azul);"></div>
                            Carregando cartões...
                        </div>
                    </div>
                    <input type="hidden" id="simCartaoId">
                    <input type="hidden" id="simCartaoFechamento">
                </div>

                <!-- Tipo -->
                <div class="mb-3">
                    <label class="form-label">Forma de pagamento</label>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-outline-secondary sim-tipo-btn active flex-fill" data-tipo="avista">
                            <i class="bi bi-cash-coin me-1"></i>À Vista
                        </button>
                        <button type="button" class="btn btn-outline-secondary sim-tipo-btn flex-fill" data-tipo="parcelado">
                            <i class="bi bi-layout-split me-1"></i>Parcelado
                        </button>
                    </div>
                </div>

                <!-- Nº parcelas (só se parcelado) -->
                <div class="mb-3" id="simParceladoWrap" style="display:none;">
                    <label class="form-label">Cenário A — Parcelas</label>
                    <div class="d-flex align-items-center gap-3">
                        <button type="button" class="btn btn-outline-secondary btn-sm px-3" id="simParcelaMenos">
                            <i class="bi bi-dash-lg"></i>
                        </button>
                        <span id="simNumParcelas" class="fs-5 fw-bold" style="min-width:2rem;text-align:center;">2</span>
                        <button type="button" class="btn btn-outline-secondary btn-sm px-3" id="simParcelaMais">
                            <i class="bi bi-plus-lg"></i>
                        </button>
                        <span style="font-size:0.82rem;color:var(--cor-texto-off);">parcelas</span>
                    </div>
                    <div id="simValorParcela" class="mt-2" style="font-size:0.82rem;color:var(--cor-texto-off);"></div>

                    <!-- Toggle comparação -->
                    <div class="mt-3">
                        <button type="button" class="btn btn-outline-secondary btn-sm w-100" id="btnToggleCompar">
                            <i class="bi bi-layout-split me-1"></i>Comparar com outro cenário
                        </button>
                    </div>

                    <!-- Cenário B -->
                    <div id="simCenarioBWrap" style="display:none;margin-top:1rem;padding-top:1rem;border-top:1px solid var(--cor-borda);">
                        <label class="form-label">Cenário B — Parcelas</label>
                        <div class="d-flex align-items-center gap-3">
                            <button type="button" class="btn btn-outline-secondary btn-sm px-3" id="simParcelaMenosB">
                                <i class="bi bi-dash-lg"></i>
                            </button>
                            <span id="simNumParcelasB" class="fs-5 fw-bold" style="min-width:2rem;text-align:center;">12</span>
                            <button type="button" class="btn btn-outline-secondary btn-sm px-3" id="simParcelaMaisB">
                                <i class="bi bi-plus-lg"></i>
                            </button>
                            <span style="font-size:0.82rem;color:var(--cor-texto-off);">parcelas</span>
                        </div>
                        <div id="simValorParcelaB" class="mt-2" style="font-size:0.82rem;color:var(--cor-texto-off);"></div>
                    </div>
                </div>

                <!-- Data da compra -->
                <div class="mb-4">
                    <label class="form-label">Data da compra</label>
                    <input type="date" id="simData" class="form-control">
                </div>

                <!-- Botão simular -->
                <button type="button" class="btn btn-primary w-100" id="btnSimular">
                    <i class="bi bi-play-fill me-1"></i>Simular
                </button>
            </div>
        </div>

        <!-- ── PROJEÇÃO ─────────────────────────────────────────────── -->
        <div class="col-12 col-lg-8" id="secaoProjecao" style="display:none;">

            <!-- Cards resumo -->
            <div class="row g-3 mb-4" id="simResumoCards"></div>

            <!-- Loading -->
            <div id="simLoadingFaturas" class="painel text-center py-4 mb-4" style="display:none;">
                <div class="spinner-border spinner-border-sm me-2" style="color:var(--cor-azul);"></div>
                <span style="color:var(--cor-texto-off);">Buscando faturas...</span>
            </div>

            <!-- Tabela simples (um cenário) -->
            <div class="painel mb-4" id="simTabelaWrap" style="display:none;">
                <h6 class="titulo mb-3"><i class="bi bi-calendar3 titulo-azul me-2"></i>Impacto nas Faturas</h6>
                <div class="table-responsive">
                    <table class="table table-borderless align-middle">
                        <thead>
                            <tr style="font-size:0.78rem;color:var(--cor-texto-off);border-bottom:1px solid var(--cor-borda);">
                                <th>Mês</th>
                                <th class="text-end">Fatura Atual</th>
                                <th class="text-end" style="color:var(--cor-azul);">+ Esta Compra</th>
                                <th class="text-end">Fatura Projetada</th>
                                <th class="text-end" style="color:#10B981;">Gasto Total do Mês</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody id="simTabelaBody"></tbody>
                    </table>
                </div>
            </div>

            <!-- Comparação (dois cenários) -->
            <div id="simComparWrap" style="display:none;" class="mb-4">
                <div class="row g-3">
                    <div class="col-12 col-md-6">
                        <div class="painel h-100" style="border-top:3px solid #3B82F6;">
                            <h6 class="titulo mb-3" style="color:#3B82F6;"><i class="bi bi-calendar3 me-2"></i>Cenário A</h6>
                            <div id="simComparLabelA" class="mb-2" style="font-size:0.8rem;color:var(--cor-texto-off);"></div>
                            <div class="table-responsive">
                                <table class="table table-borderless align-middle" style="font-size:0.82rem;">
                                    <thead>
                                        <tr style="font-size:0.75rem;color:var(--cor-texto-off);border-bottom:1px solid var(--cor-borda);">
                                            <th>Mês</th><th class="text-end">Atual</th><th class="text-end" style="color:#3B82F6;">+ Parcela</th><th class="text-end">Total</th>
                                        </tr>
                                    </thead>
                                    <tbody id="simComparBodyA"></tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-md-6">
                        <div class="painel h-100" style="border-top:3px solid #10B981;">
                            <h6 class="titulo mb-3" style="color:#10B981;"><i class="bi bi-calendar3 me-2"></i>Cenário B</h6>
                            <div id="simComparLabelB" class="mb-2" style="font-size:0.8rem;color:var(--cor-texto-off);"></div>
                            <div class="table-responsive">
                                <table class="table table-borderless align-middle" style="font-size:0.82rem;">
                                    <thead>
                                        <tr style="font-size:0.75rem;color:var(--cor-texto-off);border-bottom:1px solid var(--cor-borda);">
                                            <th>Mês</th><th class="text-end">Atual</th><th class="text-end" style="color:#10B981;">+ Parcela</th><th class="text-end">Total</th>
                                        </tr>
                                    </thead>
                                    <tbody id="simComparBodyB"></tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Cards diferença -->
                <div class="painel mt-3" id="simComparDiff"></div>
            </div>

            <!-- Botão confirmar -->
            <div class="d-flex gap-3 justify-content-end flex-wrap" id="simBotoesConfirmar">
                <button type="button" class="btn btn-outline-secondary" id="btnCancelarSim">
                    <i class="bi bi-x-lg me-1"></i>Descartar
                </button>
                <button type="button" class="btn btn-success" id="btnConfirmarSim" style="display:none;">
                    <i class="bi bi-check-lg me-1"></i>Confirmar e Salvar
                </button>
                <button type="button" class="btn btn-primary" id="btnConfirmarA" style="display:none;">
                    <i class="bi bi-check-lg me-1"></i>Salvar Cenário A
                </button>
                <button type="button" class="btn btn-success" id="btnConfirmarB" style="display:none;">
                    <i class="bi bi-check-lg me-1"></i>Salvar Cenário B
                </button>
            </div>
        </div>

    </div>
</div>

<style>
.sim-tipo-btn.active {
    background: var(--cor-azul);
    border-color: var(--cor-azul);
    color: #fff;
}
.sim-cartao-chip {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.4rem 0.85rem;
    border-radius: 50px;
    border: 2px solid transparent;
    background: var(--cor-painel-hover, rgba(255,255,255,0.06));
    cursor: pointer;
    font-size: 0.82rem;
    font-weight: 500;
    transition: all 0.15s;
}
.sim-cartao-chip .sim-chip-dot {
    width: 10px; height: 10px; border-radius: 50%; flex-shrink: 0;
}
.sim-cartao-chip.selecionado {
    border-color: var(--chip-cor, var(--cor-azul));
    background: color-mix(in srgb, var(--chip-cor, var(--cor-azul)) 15%, transparent);
}
.sim-barra {
    display: inline-block;
    height: 8px;
    border-radius: 4px;
    background: linear-gradient(90deg, rgba(59,130,246,0.5), var(--cor-azul));
    vertical-align: middle;
    margin-left: 0.5rem;
    min-width: 4px;
    transition: width 0.4s ease;
}
.sim-acrescimo {
    color: var(--cor-azul);
    font-weight: 600;
}
.sim-total-projetado {
    font-weight: 700;
}
.sim-ultima-parcela td {
    background: rgba(59,130,246,0.06);
    border-radius: 4px;
}
tr.sim-ultima-parcela > td:first-child {
    border-left: 3px solid var(--cor-azul);
    padding-left: 0.75rem;
}
</style>

<script>
var simTipo         = 'avista';
var simNumParcelas  = 2;
var simNumParcelasB = 12;
var simComparando   = false;
var simCartoesMap   = {};
var _simProjecao    = null;
var _simProjecaoB   = null;

// ── VALOR CLEAVE ────────────────────────────────────────────────
bancInput(document.getElementById('simValor'));

// ── DATA padrão = hoje ──────────────────────────────────────────
(function () {
    var hoje = new Date();
    var pad = function (n) { return String(n).padStart(2, '0'); };
    $('#simData').val(hoje.getFullYear() + '-' + pad(hoje.getMonth() + 1) + '-' + pad(hoje.getDate()));
})();

// ── TIPO toggle ─────────────────────────────────────────────────
$(document).on('click', '.sim-tipo-btn', function () {
    $('.sim-tipo-btn').removeClass('active');
    $(this).addClass('active');
    simTipo = $(this).data('tipo');
    $('#simParceladoWrap').toggle(simTipo === 'parcelado');
    atualizaValorParcela();
});

// ── PARCELAS +/- ────────────────────────────────────────────────
$('#simParcelaMenos').on('click', function () {
    if (simNumParcelas > 2) { simNumParcelas--; $('#simNumParcelas').text(simNumParcelas); atualizaValorParcela(); }
});
$('#simParcelaMais').on('click', function () {
    if (simNumParcelas < 48) { simNumParcelas++; $('#simNumParcelas').text(simNumParcelas); atualizaValorParcela(); }
});
$('#simParcelaMenosB').on('click', function () {
    if (simNumParcelasB > 2) { simNumParcelasB--; $('#simNumParcelasB').text(simNumParcelasB); atualizaValorParcelaB(); }
});
$('#simParcelaMaisB').on('click', function () {
    if (simNumParcelasB < 48) { simNumParcelasB++; $('#simNumParcelasB').text(simNumParcelasB); atualizaValorParcelaB(); }
});
$('#simValor').on('input', function () { atualizaValorParcela(); atualizaValorParcelaB(); });

$('#btnToggleCompar').on('click', function () {
    simComparando = !simComparando;
    if (simComparando) {
        $('#simCenarioBWrap').slideDown(180);
        $(this).html('<i class="bi bi-x-lg me-1"></i>Cancelar comparação').removeClass('btn-outline-secondary').addClass('btn-outline-danger');
        $('[class*="form-label"]:first', '#simParceladoWrap').text('Cenário A — Parcelas');
    } else {
        $('#simCenarioBWrap').slideUp(180);
        $(this).html('<i class="bi bi-layout-split me-1"></i>Comparar com outro cenário').removeClass('btn-outline-danger').addClass('btn-outline-secondary');
    }
});

function atualizaValorParcela() {
    if (simTipo !== 'parcelado') return;
    var raw = $('#simValor').val().replace(/R\$\s?/g, '').replace(/\./g, '').replace(',', '.');
    var v   = parseFloat(raw) || 0;
    if (v > 0) {
        $('#simValorParcela').html(simNumParcelas + 'x de <strong>' + fmtBRL(v / simNumParcelas) + '</strong>');
    } else { $('#simValorParcela').html(''); }
}

function atualizaValorParcelaB() {
    if (simTipo !== 'parcelado') return;
    var raw = $('#simValor').val().replace(/R\$\s?/g, '').replace(/\./g, '').replace(',', '.');
    var v   = parseFloat(raw) || 0;
    if (v > 0) {
        $('#simValorParcelaB').html(simNumParcelasB + 'x de <strong>' + fmtBRL(v / simNumParcelasB) + '</strong>');
    } else { $('#simValorParcelaB').html(''); }
}

// ── CARTÕES ─────────────────────────────────────────────────────
function carregaCartoesSim() {
    $.ajax({
        type: 'POST', url: App.ctrl.cartoes,
        data: { acao: 'busca' }, dataType: 'json',
        success: function (data) {
            simCartoesMap = data || {};
            var keys = Object.keys(simCartoesMap);
            if (!keys.length) {
                $('#simCartoesWrap').html('<span style="color:var(--cor-texto-off);font-size:0.82rem;">Nenhum cartão cadastrado.</span>');
                return;
            }
            var html = '';
            keys.forEach(function (id) {
                var c   = simCartoesMap[id];
                var cor = c.cor || '#3B82F6';
                html += '<div class="sim-cartao-chip" data-id="' + c.id + '" data-fechamento="' + (c.fechamento_dia || 1) + '" style="--chip-cor:' + cor + ';">' +
                        '<span class="sim-chip-dot" style="background:' + cor + ';"></span>' +
                        c.nome_cartao + '</div>';
            });
            $('#simCartoesWrap').html(html);
            // seleciona o primeiro por padrão
            $('#simCartoesWrap .sim-cartao-chip').first().trigger('click');
        },
        error: function () {
            $('#simCartoesWrap').html('<span style="color:#EF4444;font-size:0.82rem;">Erro ao carregar cartões.</span>');
        }
    });
}

$(document).on('click', '.sim-cartao-chip', function () {
    $('.sim-cartao-chip').removeClass('selecionado');
    $(this).addClass('selecionado');
    $('#simCartaoId').val($(this).data('id'));
    $('#simCartaoFechamento').val($(this).data('fechamento'));
});

// ── SIMULAR ─────────────────────────────────────────────────────
$('#btnSimular').on('click', function () {
    var descricao = $.trim($('#simDescricao').val());
    var rawValor  = $('#simValor').val().replace(/R\$\s?/g, '').replace(/\./g, '').replace(',', '.');
    var valor     = parseFloat(rawValor) || 0;
    var cartaoId  = $('#simCartaoId').val();
    var dataStr   = $('#simData').val();
    var fechamento = parseInt($('#simCartaoFechamento').val()) || 1;

    // Validação
    var categoriaId = $('#simCategoria').val();

    var erros = [];
    if (!descricao)   erros.push('Informe a descrição.');
    if (valor <= 0)   erros.push('Informe o valor.');
    if (!categoriaId) erros.push('Selecione uma categoria.');
    if (!cartaoId)    erros.push('Selecione um cartão.');
    if (!dataStr)     erros.push('Informe a data da compra.');
    if (simTipo === 'parcelado' && simNumParcelas < 2) erros.push('Mínimo 2 parcelas.');

    if (erros.length) {
        toastr.warning(erros.join('<br>'), '', { escapeHtml: false });
        return;
    }

    var parcelas = calcularParcelas(valor, simTipo, simNumParcelas, dataStr, fechamento);

    _simProjecao = { descricao, valor, cartaoId, categoriaId, dataStr, tipo: simTipo, numParcelas: simNumParcelas, parcelas };
    _simProjecaoB = null;

    if (simComparando && simTipo === 'parcelado') {
        var parcelasB = calcularParcelas(valor, 'parcelado', simNumParcelasB, dataStr, fechamento);
        _simProjecaoB = { descricao, valor, cartaoId, categoriaId, dataStr, tipo: 'parcelado', numParcelas: simNumParcelasB, parcelas: parcelasB };

        renderResumoCardsCompar(parcelas, parcelasB, valor);
        buscarFaturasEProjetarCompar(parcelas, parcelasB, cartaoId);

        $('#simTabelaWrap').hide();
        $('#simComparWrap').show();
        $('#btnConfirmarSim').hide();
        $('#btnConfirmarA').show();
        $('#btnConfirmarB').show();
    } else {
        renderResumoCards(parcelas, valor);
        buscarFaturasEProjetar(parcelas, cartaoId);

        $('#simComparWrap').hide();
        $('#simTabelaWrap').show();
        $('#btnConfirmarA').hide();
        $('#btnConfirmarB').hide();
        $('#btnConfirmarSim').show();
    }

    $('#secaoProjecao').fadeIn(200);
    $('html, body').animate({ scrollTop: $('#secaoProjecao').offset().top - 80 }, 400);
});

function renderResumoCardsCompar(parcelasA, parcelasB, valor) {
    var mesesNomes = ['Jan','Fev','Mar','Abr','Mai','Jun','Jul','Ago','Set','Out','Nov','Dez'];
    var ultA = parcelasA[parcelasA.length - 1];
    var ultB = parcelasB[parcelasB.length - 1];
    var cards = [
        { icon: 'bi-cash-stack',     label: 'Total da Compra',     value: fmtBRL(valor),                                   cor: '#10B981' },
        { icon: 'bi-layout-split',   label: 'Parcela A',           value: parcelasA.length + 'x de ' + fmtBRL(parcelasA[0].parcela), cor: '#3B82F6' },
        { icon: 'bi-layout-split',   label: 'Parcela B',           value: parcelasB.length + 'x de ' + fmtBRL(parcelasB[0].parcela), cor: '#10B981' },
        { icon: 'bi-calendar-check', label: 'Última parcela A',    value: mesesNomes[ultA.mes-1] + '/' + ultA.ano,         cor: '#3B82F6' },
        { icon: 'bi-calendar-check', label: 'Última parcela B',    value: mesesNomes[ultB.mes-1] + '/' + ultB.ano,         cor: '#10B981' },
    ];
    var html = '';
    cards.forEach(function(c) {
        html += '<div class="col-6 col-sm-4 col-lg">' +
                '<div class="painel text-center py-3">' +
                '<i class="bi ' + c.icon + ' mb-2" style="font-size:1.4rem;color:' + c.cor + ';"></i>' +
                '<div class="fw-bold" style="font-size:0.95rem;">' + c.value + '</div>' +
                '<div style="font-size:0.72rem;color:var(--cor-texto-off);">' + c.label + '</div>' +
                '</div></div>';
    });
    $('#simResumoCards').html(html);
}

function buscarFaturasEProjetarCompar(parcelasA, parcelasB, cartaoId) {
    $('#simLoadingFaturas').show();
    $('#simComparBodyA').html('');
    $('#simComparBodyB').html('');

    var mesesUnicos = {};
    parcelasA.concat(parcelasB).forEach(function(p) {
        mesesUnicos[p.ano + '-' + String(p.mes).padStart(2,'0')] = { mes: p.mes, ano: p.ano };
    });

    var keys = Object.keys(mesesUnicos).sort();
    var totais = {};
    var pending = keys.length;

    function onDone() {
        $('#simLoadingFaturas').hide();
        renderTabelaCompar('A', parcelasA, totais, '#3B82F6');
        renderTabelaCompar('B', parcelasB, totais, '#10B981');
        renderDiffCard(parcelasA, parcelasB);
    }

    keys.forEach(function(key) {
        var m = mesesUnicos[key];
        $.ajax({
            type: 'POST', url: App.ctrl.gastos,
            data: { acao: 'buscaFatura', mes: m.mes, ano: m.ano, cartaoId: cartaoId },
            dataType: 'json',
            success: function(data) {
                var t = 0;
                if (data && !$.isEmptyObject(data)) {
                    $.each(data, function(_, gastos) {
                        var vt = parseFloat(String(gastos.valortotal || '0').replace(/\./g, '').replace(',', '.'));
                        t += isNaN(vt) ? 0 : vt;
                    });
                }
                totais[key] = t;
            },
            error: function() { totais[key] = 0; },
            complete: function() { if (--pending === 0) onDone(); }
        });
    });
}

function renderTabelaCompar(cenario, parcelas, totais, cor) {
    var mesesNomes = ['Jan','Fev','Mar','Abr','Mai','Jun','Jul','Ago','Set','Out','Nov','Dez'];
    var html = '';
    parcelas.forEach(function(p, i) {
        var key   = p.ano + '-' + String(p.mes).padStart(2,'0');
        var atual = totais[key] || 0;
        var proj  = atual + p.parcela;
        html += '<tr>' +
                '<td><strong>' + mesesNomes[p.mes-1] + '/' + p.ano + '</strong><br>' +
                '<small style="color:var(--cor-texto-off);">' + (i+1) + '/' + parcelas.length + '</small></td>' +
                '<td class="text-end" style="color:var(--cor-texto-off);">' + fmtBRL(atual) + '</td>' +
                '<td class="text-end" style="color:' + cor + ';font-weight:600;">+' + fmtBRL(p.parcela) + '</td>' +
                '<td class="text-end fw-bold">' + fmtBRL(proj) + '</td></tr>';
    });
    $('#simComparBody' + cenario).html(html);
    $('#simComparLabel' + cenario).html(
        parcelas.length + 'x de <strong>' + fmtBRL(parcelas[0].parcela) + '</strong>'
    );
}

function renderDiffCard(parcelasA, parcelasB) {
    var parcelaA = parcelasA[0].parcela;
    var parcelaB = parcelasB[0].parcela;
    var diff = parcelaA - parcelaB;
    var sinal = diff > 0 ? 'A custa' : 'B custa';
    var mesesNomes = ['Jan','Fev','Mar','Abr','Mai','Jun','Jul','Ago','Set','Out','Nov','Dez'];
    var ultA = parcelasA[parcelasA.length-1];
    var ultB = parcelasB[parcelasB.length-1];

    var html = '<div class="d-flex flex-wrap gap-4 align-items-center justify-content-center">' +
        '<div class="text-center"><div style="font-size:0.75rem;color:var(--cor-texto-off);">Diferença por mês</div>' +
        '<div class="fw-bold" style="font-size:1.1rem;color:' + (diff > 0 ? '#3B82F6' : '#10B981') + ';">' +
        sinal + ' ' + fmtBRL(Math.abs(diff)) + ' a mais/mês</div></div>' +
        '<div class="text-center"><div style="font-size:0.75rem;color:var(--cor-texto-off);">Quita em A</div>' +
        '<div class="fw-bold" style="color:#3B82F6;">' + mesesNomes[ultA.mes-1] + '/' + ultA.ano + '</div></div>' +
        '<div class="text-center"><div style="font-size:0.75rem;color:var(--cor-texto-off);">Quita em B</div>' +
        '<div class="fw-bold" style="color:#10B981;">' + mesesNomes[ultB.mes-1] + '/' + ultB.ano + '</div></div>' +
        '</div>';
    $('#simComparDiff').html(html);
}

function calcularParcelas(valor, tipo, numParcelas, dataStr, fechamento) {
    var d    = new Date(dataStr + 'T12:00:00');
    var dia  = d.getDate();
    var mes  = d.getMonth() + 1;
    var ano  = d.getFullYear();

    // Se compra depois do fechamento, cai na fatura do próximo mês
    if (dia > fechamento) {
        mes++;
        if (mes > 12) { mes = 1; ano++; }
    }

    var n = tipo === 'avista' ? 1 : numParcelas;
    var vlrParcela = valor / n;
    var result = [];

    for (var i = 0; i < n; i++) {
        var m = mes + i;
        var a = ano;
        while (m > 12) { m -= 12; a++; }
        result.push({ mes: m, ano: a, parcela: vlrParcela, idx: i + 1 });
    }
    return result;
}

function renderResumoCards(parcelas, valor) {
    var ultima   = parcelas[parcelas.length - 1];
    var mesesNomes = ['Jan','Fev','Mar','Abr','Mai','Jun','Jul','Ago','Set','Out','Nov','Dez'];
    var ultimaLabel = mesesNomes[ultima.mes - 1] + '/' + ultima.ano;
    var vlrParcela = parcelas[0].parcela;

    var cards = [
        { icon: 'bi-cash-stack',    label: 'Total da Compra',  value: fmtBRL(valor),               cor: '#10B981' },
        { icon: 'bi-layout-split',  label: 'Parcelas',         value: parcelas.length + 'x de ' + fmtBRL(vlrParcela), cor: '#3B82F6' },
        { icon: 'bi-calendar-check',label: 'Última Parcela',   value: ultimaLabel,                  cor: '#F59E0B' },
    ];

    var html = '';
    cards.forEach(function (c) {
        html += '<div class="col-12 col-sm-4">' +
                '<div class="painel text-center py-3">' +
                '<i class="bi ' + c.icon + ' mb-2" style="font-size:1.6rem;color:' + c.cor + ';"></i>' +
                '<div class="fw-bold" style="font-size:1.05rem;">' + c.value + '</div>' +
                '<div style="font-size:0.75rem;color:var(--cor-texto-off);">' + c.label + '</div>' +
                '</div></div>';
    });
    $('#simResumoCards').html(html);
}

function buscarFaturasEProjetar(parcelas, cartaoId) {
    $('#simTabelaWrap').hide();
    $('#simLoadingFaturas').show();

    // Meses únicos afetados
    var mesesUnicos = {};
    parcelas.forEach(function (p) {
        mesesUnicos[p.ano + '-' + String(p.mes).padStart(2,'0')] = { mes: p.mes, ano: p.ano };
    });

    var keys      = Object.keys(mesesUnicos).sort();
    var totais    = {};
    var totaisMes = {};
    var pending   = keys.length * 2;

    function onDone() {
        $('#simLoadingFaturas').hide();
        renderTabelaProjecao(parcelas, totais, totaisMes);
        $('#simTabelaWrap').fadeIn(200);
    }

    keys.forEach(function (key) {
        var m = mesesUnicos[key];
        $.ajax({
            type: 'POST', url: App.ctrl.gastos,
            data: { acao: 'buscaFatura', mes: m.mes, ano: m.ano, cartaoId: cartaoId },
            dataType: 'json',
            success: function (data) {
                var total = 0;
                if (data && !$.isEmptyObject(data)) {
                    $.each(data, function (_, gastos) {
                        var vt = parseFloat(String(gastos.valortotal || '0').replace(/\./g, '').replace(',', '.'));
                        total += isNaN(vt) ? 0 : vt;
                    });
                }
                totais[key] = total;
            },
            error: function () { totais[key] = 0; },
            complete: function () { if (--pending === 0) onDone(); }
        });
        $.ajax({
            type: 'POST', url: App.ctrl.gastos,
            data: { acao: 'dashboard', mes: m.mes, ano: m.ano },
            dataType: 'json',
            success: function (data) {
                totaisMes[key] = data ? (parseFloat(data.totalGasto) || 0) : 0;
            },
            error: function () { totaisMes[key] = 0; },
            complete: function () { if (--pending === 0) onDone(); }
        });
    });
}

function renderTabelaProjecao(parcelas, totais, totaisMes) {
    var mesesNomes = ['Janeiro','Fevereiro','Março','Abril','Maio','Junho',
                      'Julho','Agosto','Setembro','Outubro','Novembro','Dezembro'];
    totaisMes = totaisMes || {};

    // Calcula maior total projetado (para barra proporcional)
    var maxProjetado = 0;
    parcelas.forEach(function (p) {
        var key = p.ano + '-' + String(p.mes).padStart(2,'0');
        var projetado = (totais[key] || 0) + p.parcela;
        if (projetado > maxProjetado) maxProjetado = projetado;
    });

    var ultimaIdx = parcelas.length - 1;
    var html = '';

    parcelas.forEach(function (p, i) {
        var key        = p.ano + '-' + String(p.mes).padStart(2,'0');
        var atual      = totais[key] || 0;
        var projetado  = atual + p.parcela;
        var gastoTotal = (totaisMes[key] || 0) + p.parcela;
        var largura    = maxProjetado > 0 ? Math.round((projetado / maxProjetado) * 120) : 20;
        var isUltima   = (i === ultimaIdx && parcelas.length > 1);
        var label      = parcelas.length > 1 ? 'Parcela ' + p.idx + '/' + parcelas.length : 'À Vista';

        html += '<tr class="' + (isUltima ? 'sim-ultima-parcela' : '') + '">' +
                '<td>' +
                  '<div style="font-weight:600;">' + mesesNomes[p.mes - 1] + ' ' + p.ano + '</div>' +
                  '<div style="font-size:0.75rem;color:var(--cor-texto-off);">' + label + '</div>' +
                '</td>' +
                '<td class="text-end" style="color:var(--cor-texto-off);">' + fmtBRL(atual) + '</td>' +
                '<td class="text-end sim-acrescimo">+ ' + fmtBRL(p.parcela) + '</td>' +
                '<td class="text-end sim-total-projetado">' +
                  fmtBRL(projetado) +
                  '<span class="sim-barra" style="width:' + largura + 'px;"></span>' +
                '</td>' +
                '<td class="text-end" style="color:#10B981;font-weight:600;">' + fmtBRL(gastoTotal) + '</td>' +
                '<td style="width:24px;">' +
                  (isUltima ? '<i class="bi bi-flag-fill" style="color:var(--cor-azul);font-size:0.85rem;" title="Última parcela"></i>' : '') +
                '</td>' +
                '</tr>';
    });

    $('#simTabelaBody').html(html);
}

// ── CONFIRMAR (cenário único) ─────────────────────────────────────
$('#btnConfirmarSim').on('click', function () {
    if (_simProjecao) confirmarProjecao(_simProjecao, $(this));
});

// ── CONFIRMAR A e B (modo comparação) ────────────────────────────
function confirmarProjecao(p, $btn) {
    $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span>Salvando...');
    var valorFmt = p.valor.toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    $.ajax({
        type: 'POST', url: App.ctrl.gastos,
        data: {
            acao: 'adicionar', tipo: 'credito',
            descricao: p.descricao, valor: valorFmt, categoria: p.categoriaId,
            cartao: p.cartaoId, data: p.dataStr, metodo: 'credito',
            parcelado: p.tipo === 'parcelado' ? 'S' : 'N',
            num_parcelas: p.tipo === 'parcelado' ? p.numParcelas : 1, recorrente: 'N'
        },
        dataType: 'json',
        success: function(ok) {
            if (ok) {
                toastr.success('Compra salva com sucesso!');
                setTimeout(function() { window.location.href = App.base + '/php/views/cartaocredito.php'; }, 1200);
            } else {
                toastr.error('Erro ao salvar a compra.');
                $btn.prop('disabled', false).html('<i class="bi bi-check-lg me-1"></i>Tentar novamente');
            }
        },
        error: function() {
            toastr.error('Erro na requisição.');
            $btn.prop('disabled', false).html('<i class="bi bi-check-lg me-1"></i>Tentar novamente');
        }
    });
}

$('#btnConfirmarA').on('click', function() { if (_simProjecao)  confirmarProjecao(_simProjecao,  $(this)); });
$('#btnConfirmarB').on('click', function() { if (_simProjecaoB) confirmarProjecao(_simProjecaoB, $(this)); });

// ── DESCARTAR ────────────────────────────────────────────────────
$('#btnCancelarSim').on('click', function () {
    _simProjecao  = null;
    _simProjecaoB = null;
    $('#secaoProjecao').fadeOut(150);
    $('html, body').animate({ scrollTop: 0 }, 300);
});

// ── HELPERS ──────────────────────────────────────────────────────
function fmtBRL(v) {
    return 'R$ ' + parseFloat(v || 0).toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}

// ── CATEGORIAS ───────────────────────────────────────────────────
function carregaCategoriasSim() {
    $.ajax({
        type: 'POST', url: App.ctrl.categoria,
        data: { acao: 'busca' }, dataType: 'json',
        success: function (data) {
            var html = '<li><a class="dropdown-item text-muted py-2" href="#" data-id="">Selecione</a></li>' +
                       '<li><hr class="dropdown-divider m-0"></li>';
            $.each(data || [], function (_, cat) {
                var cor   = cat.cor   || '#6B7280';
                var icone = cat.icone ? '<span class="me-1">' + escHtml(cat.icone) + '</span>' : '';
                html += '<li><a class="dropdown-item d-flex align-items-center gap-2" href="#" data-id="' + cat.id + '">' +
                        '<span class="cat-dot" style="background:' + cor + ';flex-shrink:0;"></span>' +
                        icone + '<span style="color:' + cor + ';">' + escHtml(cat.nome) + '</span></a></li>';
            });
            $('#simCatSelMenu').html(html);
        }
    });
}

$(document).on('click', '#simCatSelMenu a', function (e) {
    e.preventDefault();
    e.stopPropagation();
    var id  = String($(this).data('id') || '');
    $('#simCategoria').val(id);
    if (!id) {
        $('#simCatSelBtn .sim-cat-preview').html('<span class="text-muted">Selecione</span>');
        return;
    }
    var cor   = $(this).find('.cat-dot').css('background-color') || '#6B7280';
    var texto = $(this).text().trim();
    $('#simCatSelBtn .sim-cat-preview').html(
        '<span class="cat-dot" style="background:' + cor + ';flex-shrink:0;"></span>' +
        '<span style="margin-left:4px;">' + texto + '</span>'
    );
    var el = document.getElementById('simCatSelBtn');
    if (el) { var dd = bootstrap.Dropdown.getInstance(el); if (dd) dd.hide(); }
});

// ── INIT ─────────────────────────────────────────────────────────
carregaCartoesSim();
carregaCategoriasSim();
</script>

<?php include '../templates/footer.php'; ?>
