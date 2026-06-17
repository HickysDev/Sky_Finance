<?php
require_once "php/templates/header.php";

$meses = [
    1 => "Janeiro",  2 => "Fevereiro", 3 => "Março",    4 => "Abril",
    5 => "Maio",     6 => "Junho",     7 => "Julho",    8 => "Agosto",
    9 => "Setembro", 10 => "Outubro",  11 => "Novembro", 12 => "Dezembro"
];
$mesAtual = date('n');
?>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

<div class="animate__animated animate__fadeIn">

    <!-- HEADER -->
    <div class="d-flex align-items-center justify-content-between titulo-pagina mb-4 flex-wrap gap-2">
        <div class="mt-2">
            <?php
                $hora      = (int) date('H');
                $saudacao  = $hora < 12 ? 'Bom dia' : ($hora < 18 ? 'Boa tarde' : 'Boa noite');
                $primeiroNome = explode(' ', trim($_SESSION['usuario_nome'] ?? 'você'))[0];
            ?>
            <div style="font-size:0.8rem;color:var(--cor-texto-off);font-weight:500;letter-spacing:0.04em;">
                <?= $saudacao ?>, <span style="color:var(--cor-texto);"><?= htmlspecialchars($primeiroNome) ?></span> 👋
            </div>
            <h1 class="titulo fs-titulo-pag mb-0">
                Dashboard &nbsp;<i class="bi bi-bar-chart-fill titulo-azul"></i>
            </h1>
        </div>
        <div class="d-flex align-items-center gap-2">
            <i class="bi bi-arrow-left-square-fill botao botaoEsquerda"></i>
            <select class="form-select text-center" id="mes" style="width:140px;">
                <?php foreach ($meses as $num => $nome): ?>
                    <option value="<?= $num ?>" <?= ($num == $mesAtual) ? 'selected' : '' ?>><?= $nome ?></option>
                <?php endforeach ?>
            </select>
            <i class="bi bi-arrow-right-square-fill botao botaoDireita"></i>
            <span style="color:var(--cor-borda);font-size:1.1rem;">|</span>
            <i class="bi bi-arrow-left-square-fill botao" id="anoEsquerda"></i>
            <span id="anoDisplay" style="font-size:0.95rem;font-weight:700;min-width:44px;text-align:center;"><?= date('Y') ?></span>
            <i class="bi bi-arrow-right-square-fill botao" id="anoDireita"></i>
        </div>
    </div>

    <!-- KPI CARDS -->
    <div class="row g-3 mb-4" id="kpiRow">
        <div class="col-12 text-center py-4">
            <div class="spinner-border" style="color:var(--cor-azul);" role="status"></div>
        </div>
    </div>

    <!-- FATURAS DOS CARTÕES -->
    <div class="mb-3" id="faturasDashSection" style="display:none;">
        <h6 class="titulo fs-secao-titulo mb-2">
            <i class="bi bi-credit-card-fill titulo-azul me-2"></i>Faturas do Mês
        </h6>
        <div id="faturasDashList" class="d-flex flex-column gap-2"></div>
    </div>

    <!-- LINHA 2: Gráfico+Categorias | Últimas Despesas -->
    <div class="row g-3 mb-3">

        <!-- Gráfico de rosca + lista de categorias combinados -->
        <div class="col-xl-5">
            <div class="painel h-100 d-flex flex-column">
                <h6 class="titulo fs-secao-titulo mb-3">
                    <i class="bi bi-pie-chart-fill titulo-azul me-2"></i>Gastos por Categoria
                </h6>

                <!-- área visível quando há dados -->
                <div id="graficoChartArea" class="d-flex flex-column align-items-center gap-3">
                    <!-- donut com total no centro -->
                    <div id="graficoWrapper" style="width:180px;height:180px;position:relative;">
                        <canvas id="graficoCategorias"></canvas>
                        <div id="graficoCenter" style="position:absolute;inset:0;display:flex;flex-direction:column;align-items:center;justify-content:center;pointer-events:none;gap:1px;">
                            <span style="font-size:0.6rem;color:var(--cor-texto-off);text-transform:uppercase;letter-spacing:.05em;">Total</span>
                            <span id="graficoCenterVal" class="titulo" style="font-size:0.95rem;color:var(--cor-texto);letter-spacing:.02em;"></span>
                        </div>
                    </div>
                    <!-- tabela abaixo -->
                    <div id="listaCategorias" style="width:100%;max-height:160px;overflow-y:auto;display:flex;justify-content:center;"></div>
                </div>

                <!-- estado vazio -->
                <div id="graficoVazio" style="display:none;" class="text-center py-4">
                    <i class="bi bi-pie-chart" style="font-size:2.5rem;color:var(--cor-borda);"></i>
                    <p class="mt-2 mb-0" style="color:var(--cor-texto-off);">Sem gastos neste mês</p>
                </div>
            </div>
        </div>

        <!-- Últimas despesas — agora com mais espaço -->
        <div class="col-xl-7">
            <div class="painel h-100">
                <h6 class="titulo fs-secao-titulo mb-3">
                    <i class="bi bi-clock-history titulo-azul me-2"></i>Últimas Despesas
                </h6>
                <div class="text-center py-4" id="loaderRecentes">
                    <div class="spinner-border spinner-border-sm" style="color:var(--cor-azul);" role="status"></div>
                </div>
                <div id="listaRecentes"></div>
                <div id="recentesVazio" style="display:none;" class="text-center py-4">
                    <i class="bi bi-inbox" style="font-size:2.5rem;color:var(--cor-borda);"></i>
                    <p class="mt-2 mb-0" style="color:var(--cor-texto-off);">Nenhuma despesa neste mês</p>
                </div>
            </div>
        </div>

    </div>

    <!-- LINHA 3: Comprometimento | Cofrinhos -->
    <div class="row g-3">

        <!-- Barra comprometimento -->
        <div class="col-xl-4" id="colProgresso" style="display:none;">
            <div class="painel h-100" id="painelProgresso">
                <h6 class="titulo fs-secao-titulo mb-3">
                    <i class="bi bi-activity titulo-azul me-2"></i>Comprometimento da Renda
                </h6>
                <div class="d-flex justify-content-between align-items-end mb-2">
                    <div>
                        <div style="font-size:0.78rem;color:var(--cor-texto-off);">Total gasto</div>
                        <div class="titulo" id="lblTotalGasto" style="font-size:1.4rem;"></div>
                    </div>
                    <div class="text-end">
                        <div style="font-size:0.78rem;color:var(--cor-texto-off);">Renda estimada</div>
                        <div class="titulo" id="lblTotalRenda" style="font-size:1.4rem;color:var(--cor-azul);"></div>
                    </div>
                </div>
                <div class="progress" style="height:14px;background:var(--cor-input);border-radius:20px;">
                    <div id="barraProgresso" class="progress-bar" role="progressbar"
                         style="border-radius:20px;transition:width 0.8s ease;"></div>
                </div>
                <div class="d-flex justify-content-between mt-2" style="font-size:0.8rem;">
                    <span style="color:var(--cor-texto-off);">R$ 0</span>
                    <span id="pctRenda" style="font-weight:700;"></span>
                </div>
                <!-- Meta economia -->
                <div id="metaEcoRow" class="mt-3 pt-3" style="border-top:1px solid var(--cor-borda);display:none;">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <span style="font-size:0.8rem;color:var(--cor-texto-off);">
                            <i class="bi bi-piggy-bank me-1" style="color:#22C55E;"></i>Guardado em cofrinhos (mês)
                        </span>
                        <span id="metaEcoVal" style="font-size:0.85rem;font-weight:600;color:#22C55E;"></span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <span style="font-size:0.8rem;color:var(--cor-texto-off);">
                            <i class="bi bi-bullseye me-1" style="color:var(--cor-azul);"></i>Meta de economia (10%)
                        </span>
                        <span id="metaEcoMeta" style="font-size:0.85rem;color:var(--cor-azul);"></span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Cofrinhos -->
        <div class="col-xl-8" id="colCofrinhos">
            <div class="painel h-100">
                <div class="d-flex align-items-center justify-content-between mb-3 flex-wrap gap-2">
                    <h6 class="titulo fs-secao-titulo mb-0">
                        <i class="bi bi-piggy-bank-fill titulo-azul me-2"></i>Cofrinhos
                    </h6>
                    <div class="d-flex align-items-center gap-3">
                        <span style="font-size:0.8rem;color:var(--cor-texto-off);">
                            Guardado este mês: <strong id="dashAportesMes" style="color:#22C55E;">—</strong>
                        </span>
                        <a href="<?= BASE_URL ?>/php/views/financas.php" class="btn btn-outline-primary btn-sm">
                            Ver todos <i class="bi bi-arrow-right ms-1"></i>
                        </a>
                    </div>
                </div>

                <div class="text-center py-3" id="loaderCofDash">
                    <div class="spinner-border spinner-border-sm" style="color:var(--cor-azul);" role="status"></div>
                </div>
                <div id="emptyCofDash" style="display:none;" class="text-center py-4">
                    <i class="bi bi-piggy-bank" style="font-size:2rem;color:var(--cor-borda);"></i>
                    <p class="mt-2 mb-0" style="color:var(--cor-texto-off);">Nenhum cofrinho criado ainda.</p>
                </div>
                <div id="listaCofDash"></div>
                <div id="totalCofDash" style="display:none;border-top:1px solid var(--cor-borda);" class="d-flex justify-content-between align-items-center mt-3 pt-2">
                    <span style="font-size:0.8rem;color:var(--cor-texto-off);">Total guardado em todos os cofrinhos</span>
                    <span class="titulo" id="dashTotalGuardado" style="color:var(--cor-azul);font-size:1.1rem;">R$ 0,00</span>
                </div>
            </div>
        </div>

    </div>

</div>

<script>
$(document).ready(function () {

    const chartColors = [
        '#3B82F6','#10B981','#F59E0B','#EF4444','#8B5CF6',
        '#EC4899','#F97316','#06B6D4','#84CC16','#9CA3AF'
    ];

    const metodoCfg = {
        'Dinheiro':   { bg: '#6B7280', cor: '#fff' },
        'Débito':     { bg: '#F59E0B', cor: '#111' },
        'Pix':        { bg: '#10B981', cor: '#fff' },
        'Crédito':    { bg: '#3B82F6', cor: '#fff' },
        'Recorrente': { bg: '#8B5CF6', cor: '#fff' },
    };

    const mAbrev = ['','Jan','Fev','Mar','Abr','Mai','Jun','Jul','Ago','Set','Out','Nov','Dez'];

    let graficoInstance = null;

    // ─── NAVEGAÇÃO MÊS / ANO ─────────────────────────────────────────────
    function getAno() { return parseInt($('#anoDisplay').text()); }

    $('.botaoEsquerda').click(function () {
        let v = parseInt($('#mes').val());
        if (v > 1) { $('#mes').val(v - 1).trigger('change'); }
        else { $('#mes').val(12); $('#anoDisplay').text(getAno() - 1); carregaDashboard(12, getAno()); }
    });
    $('.botaoDireita').click(function () {
        let v = parseInt($('#mes').val());
        if (v < 12) { $('#mes').val(v + 1).trigger('change'); }
        else { $('#mes').val(1); $('#anoDisplay').text(getAno() + 1); carregaDashboard(1, getAno()); }
    });
    $('#anoEsquerda').click(function () { $('#anoDisplay').text(getAno() - 1); carregaDashboard($('#mes').val(), getAno()); });
    $('#anoDireita').click(function ()  { $('#anoDisplay').text(getAno() + 1); carregaDashboard($('#mes').val(), getAno()); });
    $('#mes').change(function () { carregaDashboard($(this).val(), getAno()); });

    // ─── CARREGA TUDO ─────────────────────────────────────────────────────
    function carregaDashboard(mes, ano) {
        $('#kpiRow').html('<div class="col-12 text-center py-4"><div class="spinner-border" style="color:var(--cor-azul);" role="status"></div></div>');
        $('#loaderRecentes').show();
        $('#listaRecentes').empty();
        $('#recentesVazio, #graficoVazio').hide();
        $('#graficoChartArea').show();
        $('#colProgresso').hide();
        $('#colCofrinhos').removeClass('col-xl-8').addClass('col-xl-12');
        $('#loaderCofDash').show();
        $('#listaCofDash, #emptyCofDash, #totalCofDash, #metaEcoRow').hide();
        $('#faturasDashSection').hide();
        $('#faturasDashList').html('');

        $.ajax({
            type: 'POST', url: 'php/controllers/GastosController.php',
            data: { acao: 'dashboard', mes: mes, ano: ano }, dataType: 'json',
            success: function (d) {
                renderKPI(d);
                renderGrafico(d.porCategoria);
                renderRecentes(d.recentes);
                renderProgresso(d);
            },
            error: function () { toastr.error('Erro ao carregar dashboard!'); }
        });

        $.ajax({
            type: 'POST', url: 'php/controllers/CofrinhoController.php',
            data: { acao: 'dashboard', mes: mes, ano: ano }, dataType: 'json',
            success: function (c) { renderCofrinhos(c); },
            error: function () { $('#loaderCofDash').hide(); $('#emptyCofDash').show(); }
        });

        $.when(
            $.ajax({ type: 'POST', url: 'php/controllers/CartoesController.php', data: { acao: 'busca' }, dataType: 'json' }),
            $.ajax({ type: 'POST', url: 'php/controllers/GastosController.php',  data: { acao: 'buscaFatura', mes: mes, ano: ano, cartaoId: '' }, dataType: 'json' })
        ).done(function (resCartoes, resFaturas) {
            var cartoes = resCartoes[0];
            var faturas = resFaturas[0];
            if (!faturas || $.isEmptyObject(faturas)) return;
            window.cartoesArray = cartoes;
            renderFaturasDash(faturas, cartoes);
        });
    }

    // ─── KPI CARDS ───────────────────────────────────────────────────────
    function renderKPI(d) {
        const pos = d.saldo >= 0;
        var cards = [
            { icon: 'bi-arrow-down-circle-fill', cor: '#22C55E', label: 'Renda estimada',   sub: 'fontes de renda ativas',  valor: formatBR(d.totalRenda) },
            { icon: 'bi-wallet-fill',             cor: '#F59E0B', label: 'À vista',          sub: 'débito · pix · dinheiro', valor: formatBR(d.totalDebito) },
            { icon: 'bi-credit-card-fill',        cor: '#3B82F6', label: 'Fatura crédito',   sub: 'vencimento no mês',       valor: formatBR(d.totalCredito) },
            { icon: 'bi-arrow-clockwise',         cor: '#8B5CF6', label: 'Recorrentes',      sub: 'gastos fixos do mês',     valor: formatBR(d.totalRecorrente) },
            {
                icon:  pos ? 'bi-graph-up-arrow' : 'bi-graph-down-arrow',
                cor:   pos ? '#22C55E' : '#EF4444',
                label: 'Saldo estimado',
                sub:   'renda − total gasto',
                valor: (pos ? '' : '− ') + formatBR(Math.abs(d.saldo)),
            },
        ];

        if (d.totalContas > 0) {
            cards.splice(4, 0, {
                icon:  'bi-people-fill',
                cor:   '#EC4899',
                label: 'Contas a pagar',
                sub:   'o que devo a responsáveis',
                valor: formatBR(d.totalContas),
            });
        }

        const html = cards.map(c => `
            <div class="col-6 col-lg-4 col-xl kpi-col">
                <div class="painel kpi-card" style="--kpi-accent:${c.cor};">
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <div class="kpi-icon" style="background:${c.cor}22;color:${c.cor};">
                            <i class="bi ${c.icon}"></i>
                        </div>
                        <span class="kpi-label">${c.label}</span>
                    </div>
                    <div class="kpi-valor" style="color:${c.cor};">R$ ${c.valor}</div>
                    <div class="kpi-sub">${c.sub}</div>
                </div>
            </div>`).join('');

        $('#kpiRow').html(html);
    }

    // ─── GRÁFICO DE ROSCA + LISTA ─────────────────────────────────────────
    function renderGrafico(dados) {
        if (graficoInstance) { graficoInstance.destroy(); graficoInstance = null; }
        $('#listaCategorias').empty();
        $('#graficoCenterVal').text('');

        if (!dados || dados.length === 0) {
            $('#graficoChartArea').hide();
            $('#graficoVazio').show();
            return;
        }

        $('#graficoVazio').hide();
        $('#graficoChartArea').show();

        const total    = dados.reduce((s, d) => s + parseFloat(d.total), 0);
        const catCores = dados.map((d, i) => (window.categoriaNomes[d.nome] || {}).cor || chartColors[i % chartColors.length]);

        $('#graficoCenterVal').text('R$ ' + formatBR(total));

        const ctx = document.getElementById('graficoCategorias').getContext('2d');
        graficoInstance = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: dados.map(d => d.nome),
                datasets: [{ data: dados.map(d => parseFloat(d.total)), backgroundColor: catCores, borderColor: '#1E1E2F', borderWidth: 3, hoverOffset: 8 }]
            },
            options: {
                responsive: true, maintainAspectRatio: false, cutout: '68%',
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: { label: ctx => ` R$ ${formatBR(ctx.parsed)}` },
                        backgroundColor: '#2B2C3B', titleColor: '#F0F0F5', bodyColor: '#9CA3AF',
                        borderColor: '#3F3F46', borderWidth: 1, padding: 10,
                    }
                }
            }
        });

        // ── Lista lateral ──
        let listHtml = '<table class="cat-list-table">';
        dados.forEach(function (d, i) {
            const val  = parseFloat(d.total);
            const pct  = total > 0 ? (val / total * 100) : 0;
            const cor  = catCores[i];
            const cat  = window.categoriaNomes[d.nome] || {};
            const icon = cat.icone ? '<span class="me-1">' + cat.icone + '</span>' : '';

            listHtml += '<tr>' +
                '<td class="cat-list-td-dot"><div class="cat-list-dot" style="background:' + cor + ';"></div></td>' +
                '<td class="cat-list-td-nome" style="color:' + cor + ';">' + icon + d.nome + '</td>' +
                '<td class="cat-list-td-pct">' + pct.toFixed(0) + '%</td>' +
                '<td class="cat-list-td-val">R$ ' + formatBR(val) + '</td>' +
            '</tr>';
        });
        listHtml += '</table>';
        $('#listaCategorias').html(listHtml);
    }

    // ─── COFRINHOS ───────────────────────────────────────────────────────
    var _dashAportesMes = null;

    function renderCofrinhos(c) {
        $('#loaderCofDash').hide();
        _dashAportesMes = parseFloat(c.aportes_mes || 0);
        $('#dashAportesMes').text('R$ ' + formatBR(_dashAportesMes));
        atualizaMetaRow();

        if (!c.lista || c.lista.length === 0) { $('#emptyCofDash').show(); return; }

        var html = '';
        $.each(c.lista, function (i, cf) {
            var meta  = parseFloat(cf.meta_valor)  || 0;
            var atual = parseFloat(cf.valor_atual) || 0;
            var pct   = meta > 0 ? Math.min((atual / meta) * 100, 100) : 0;
            var cor   = cf.cor || '#3B82F6';
            var corBar = pct < 40 ? '#3B82F6' : pct < 70 ? '#F59E0B' : '#22C55E';

            var prazoHtml = '';
            if (cf.data_limite) {
                var ym  = cf.data_limite.substring(0, 7);
                var mes = parseInt(ym.substring(5, 7));
                var ano = ym.substring(0, 4);
                var hoje = new Date();
                var diff = (parseInt(ano) - hoje.getFullYear()) * 12 + (mes - (hoje.getMonth() + 1));
                prazoHtml = '<span style="font-size:0.72rem;color:var(--cor-texto-off);">' +
                    mAbrev[mes] + '/' + ano +
                    (diff > 0 ? ' · ' + diff + ' meses' : '') +
                    '</span>';
            }

            html += '<div class="cof-dash-item' + (i > 0 ? ' cof-dash-sep' : '') + '">' +
                '<div class="d-flex justify-content-between align-items-center mb-1">' +
                    '<div class="d-flex align-items-center gap-2">' +
                        '<div class="cof-dash-dot" style="background:' + cor + ';"></div>' +
                        '<span class="cof-dash-nome">' + escHtml(cf.nome) + '</span>' +
                        prazoHtml +
                    '</div>' +
                    '<div class="d-flex align-items-center gap-2">' +
                        '<span style="font-size:0.78rem;color:var(--cor-texto-off);">R$ ' + formatBR(atual) + ' / R$ ' + formatBR(meta) + '</span>' +
                        '<span style="font-size:0.78rem;font-weight:600;color:' + corBar + ';min-width:32px;text-align:right;">' + pct.toFixed(0) + '%</span>' +
                    '</div>' +
                '</div>' +
                '<div class="progress cof-dash-progress">' +
                    '<div class="progress-bar" style="width:' + pct.toFixed(1) + '%;background:' + corBar + ';border-radius:20px;transition:width .6s ease;"></div>' +
                '</div>' +
            '</div>';
        });

        $('#listaCofDash').html(html).show();
        $('#dashTotalGuardado').text('R$ ' + formatBR(c.total_guardado || 0));
        $('#totalCofDash').show();
    }

    // ─── COMPROMETIMENTO + META ───────────────────────────────────────────
    var _dashRenda = 0;

    function renderProgresso(d) {
        if (d.totalRenda <= 0) return;

        _dashRenda = d.totalRenda;

        const pct = Math.min((d.totalGasto / d.totalRenda) * 100, 100);
        const cor = pct < 70 ? '#22C55E' : pct < 90 ? '#F59E0B' : '#EF4444';

        $('#barraProgresso').css({ width: pct.toFixed(1) + '%', background: cor });
        $('#pctRenda').text(pct.toFixed(1) + '%').css('color', cor);
        $('#lblTotalGasto').text('R$ ' + formatBR(d.totalGasto)).css('color', cor);
        $('#lblTotalRenda').text('R$ ' + formatBR(d.totalRenda));
        $('#colProgresso').show();
        $('#colCofrinhos').removeClass('col-xl-12').addClass('col-xl-8');
        $('#painelProgresso').fadeIn();

        atualizaMetaRow();
    }

    function atualizaMetaRow() {
        if (!_dashRenda || _dashAportesMes === null) return;
        $('#metaEcoMeta').text('R$ ' + formatBR(_dashRenda * 0.10));
        $('#metaEcoVal').text('R$ ' + formatBR(_dashAportesMes));
        $('#metaEcoRow').show();
    }

    // ─── ÚLTIMAS DESPESAS ─────────────────────────────────────────────────
    function renderRecentes(dados) {
        $('#loaderRecentes').hide();
        if (!dados || dados.length === 0) { $('#recentesVazio').show(); return; }

        const html = dados.map((g, i) => {
            const m    = metodoCfg[g.metodo_pagamento] || { bg: '#6B7280', cor: '#fff' };
            const data = moment(g.data_gasto).format('DD/MM');
            return `
            <div class="recente-item ${i > 0 ? 'recente-sep' : ''}">
                <div class="d-flex justify-content-between align-items-center gap-2">
                    <div class="overflow-hidden">
                        <div class="recente-desc text-truncate">${g.descricao}</div>
                        <div class="d-flex gap-2 align-items-center mt-1">
                            ${catInlineHtml(g.categoria)}
                            <span class="badge" style="background:${m.bg};color:${m.cor};font-size:0.72rem;padding:2px 7px;">${g.metodo_pagamento}</span>
                        </div>
                    </div>
                    <div class="text-end flex-shrink-0">
                        <div class="recente-valor">R$ ${formatBR(g.valor)}</div>
                        <div class="recente-data">${data}</div>
                    </div>
                </div>
            </div>`;
        }).join('');

        $('#listaRecentes').html(html);
    }

    // ─── FATURAS DOS CARTÕES ─────────────────────────────────────────────
    function renderFaturasDash(faturas, cartoes) {
        var html = '';

        $.each(faturas, function (idCartao, items) {
            var nome  = (items[0] && items[0].nome_cartao) ? items[0].nome_cartao : 'Cartão';
            var cor   = (cartoes && cartoes[idCartao] && cartoes[idCartao].cor) ? cartoes[idCartao].cor : '#3B82F6';
            var total = items.valortotal || '0,00';
            var collapseId = 'fatDash_' + idCartao;

            var rows = '';
            $.each(items, function (i, gasto) {
                if (i === 'valortotal') return;
                var dataExib    = gasto.tipo === 'NORMAL' ? moment(gasto.data_gasto).format('DD/MM') : '<i class="bi bi-arrow-clockwise"></i>';
                var infoParcela = gasto.tipo === 'NORMAL'
                    ? ((gasto.numero_parcela || 1) + '/' + (gasto.parcelas_total || 1))
                    : '<i class="bi bi-arrow-clockwise"></i>';
                rows +=
                    '<tr>' +
                        '<td>' + escHtml(gasto.descricao) + '</td>' +
                        '<td>' + catBadgeHtml(gasto.categoria) + '</td>' +
                        '<td style="color:var(--cor-texto-off);">' + infoParcela + '</td>' +
                        '<td style="color:' + cor + ';font-weight:600;white-space:nowrap;">R$ ' + gasto.valor_parcela + '</td>' +
                        '<td style="color:var(--cor-texto-off);">' + dataExib + '</td>' +
                    '</tr>';
            });

            html +=
                '<div class="fdc-card" style="--fdc:' + cor + ';">' +
                    '<div class="fdc-header collapsed" data-bs-toggle="collapse" data-bs-target="#' + collapseId + '" aria-expanded="false">' +
                        '<div class="d-flex align-items-center gap-2">' +
                            '<i class="bi bi-credit-card-fill" style="color:' + cor + ';font-size:1rem;"></i>' +
                            '<span class="fdc-nome">' + escHtml(nome) + '</span>' +
                        '</div>' +
                        '<div class="d-flex align-items-center gap-3">' +
                            '<span class="fdc-total" style="color:' + cor + ';">R$ ' + total + '</span>' +
                            '<i class="bi bi-chevron-down fdc-caret"></i>' +
                        '</div>' +
                    '</div>' +
                    '<div class="collapse" id="' + collapseId + '">' +
                        '<div class="fdc-body">' +
                            '<div class="table-responsive">' +
                                '<table class="table table-hover mb-0" style="font-size:.82rem;">' +
                                    '<thead><tr>' +
                                        '<th>Descrição</th><th>Categoria</th><th>Parcela</th><th>Valor</th><th>Data</th>' +
                                    '</tr></thead>' +
                                    '<tbody>' + rows + '</tbody>' +
                                '</table>' +
                            '</div>' +
                        '</div>' +
                    '</div>' +
                '</div>';
        });

        $('#faturasDashList').html(html);
        $('#faturasDashSection').show();
    }

    // Rotaciona caret ao abrir/fechar collapse
    $(document).on('show.bs.collapse', '#faturasDashList .collapse', function () {
        $(this).closest('.fdc-card').find('.fdc-header').removeClass('collapsed');
        $(this).closest('.fdc-card').find('.fdc-caret').css('transform', 'rotate(180deg)');
    });
    $(document).on('hide.bs.collapse', '#faturasDashList .collapse', function () {
        $(this).closest('.fdc-card').find('.fdc-header').addClass('collapsed');
        $(this).closest('.fdc-card').find('.fdc-caret').css('transform', 'rotate(0deg)');
    });

    // ─── UTILITÁRIOS ─────────────────────────────────────────────────────
    function formatBR(n) {
        return parseFloat(n).toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }
    function escHtml(str) {
        return String(str).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
    }

    // Carrega categorias primeiro para o gráfico ter as cores corretas
    $.ajax({
        type: 'POST', url: 'php/controllers/CategoriaController.php',
        data: { acao: 'busca' }, dataType: 'json',
        success: function (data) {
            popularCatSelect(data);
            carregaDashboard($('#mes').val(), getAno());
        },
        error: function () { carregaDashboard($('#mes').val(), getAno()); }
    });

});
</script>

<style>
/* ── KPI Cards ── */
.kpi-col { flex: 1 1 0; min-width: 0; }
.kpi-card {
    height: 100%;
    border-left: 3px solid var(--kpi-accent, var(--cor-azul));
    transition: transform var(--trans), box-shadow var(--trans);
}
.kpi-card:hover {
    transform: translateY(-3px);
    box-shadow: var(--sombra-md);
}
.kpi-icon {
    width: 40px; height: 40px;
    border-radius: var(--radius-md);
    display: flex; align-items: center; justify-content: center;
    font-size: 1.2rem; flex-shrink: 0;
}
.kpi-label {
    color: var(--cor-texto-sec);
    font-size: 0.82rem; font-weight: 500; line-height: 1.2;
}
.kpi-valor {
    font-family: "Bebas Neue", sans-serif;
    font-size: 1.75rem; line-height: 1;
    margin-bottom: 0.15rem; letter-spacing: 0.02em;
}
.kpi-sub { color: var(--cor-texto-off); font-size: 0.74rem; }

/* ── Últimas despesas ── */
.recente-item  { padding: 0.55rem 0; }
.recente-sep   { border-top: 1px solid var(--cor-borda); }
.recente-desc  { color: var(--cor-texto); font-weight: 500; font-size: 0.88rem; max-width: 300px; }
.recente-valor { color: var(--cor-texto); font-weight: 600; font-size: 0.9rem; white-space: nowrap; }
.recente-data  { color: var(--cor-texto-off); font-size: 0.74rem; }

/* ── Cofrinhos dashboard ── */
.cof-dash-item  { padding: 0.55rem 0; }
.cof-dash-sep   { border-top: 1px solid var(--cor-borda); }
.cof-dash-dot   { width: 10px; height: 10px; border-radius: 50%; flex-shrink: 0; }
.cof-dash-nome  { font-size: 0.88rem; font-weight: 500; color: var(--cor-texto); }
.cof-dash-progress {
    height: 6px; background: var(--cor-input);
    border-radius: 20px; overflow: hidden; margin-top: 4px;
}

/* ── Lista de categorias ── */
.cat-list-table { border-collapse: collapse; }
.cat-list-table tr td { padding: 2px 4px; border: none; background: transparent !important; vertical-align: middle; }
.cat-list-td-dot { width: 14px; }
.cat-list-dot   { width: 8px; height: 8px; border-radius: 50%; }
.cat-list-td-nome { font-size: 0.76rem; font-weight: 500; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 100px; }
.cat-list-td-pct  { font-size: 0.7rem; color: var(--cor-texto-off); white-space: nowrap; padding-left: 8px !important; }
.cat-list-td-val  { font-size: 0.76rem; font-weight: 600; color: var(--cor-texto); white-space: nowrap; padding-left: 8px !important; }

/* scrollbar fina na lista de categorias */
#listaCategorias::-webkit-scrollbar { width: 3px; }
#listaCategorias::-webkit-scrollbar-track { background: transparent; }
#listaCategorias::-webkit-scrollbar-thumb { background: var(--cor-borda); border-radius: 10px; }

/* ── Faturas Dashboard ── */
.fdc-card {
    background: var(--cor-painel);
    border: 1px solid var(--cor-borda);
    border-left: 4px solid var(--fdc, var(--cor-azul));
    border-radius: var(--radius-md);
    overflow: hidden;
}
.fdc-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 12px 16px;
    cursor: pointer;
    user-select: none;
    transition: background var(--trans);
}
.fdc-header:hover { background: rgba(255,255,255,0.04); }
.fdc-nome  { font-weight: 600; font-size: 0.9rem; }
.fdc-total { font-family: "Bebas Neue", sans-serif; font-size: 1.1rem; letter-spacing: 0.03em; }
.fdc-caret { color: var(--cor-texto-off); font-size: 0.8rem; transition: transform 0.2s ease; }
.fdc-body  { border-top: 1px solid var(--cor-borda); padding: 4px 0; }

/* ── Responsivo ── */
@media (max-width: 576px) {
    .kpi-valor { font-size: 1.4rem; }
    .kpi-icon  { width: 34px; height: 34px; font-size: 1rem; }
    .recente-desc { max-width: 160px; }
    #graficoWrapper { width: 130px !important; height: 130px !important; }
}
</style>

<?php require_once "php/templates/footer.php"; ?>
