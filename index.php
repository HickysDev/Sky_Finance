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

    <!-- AVISOS / ALERTAS FINANCEIROS -->
    <div id="avisosSection" class="mb-3" style="display:none;"></div>

    <!-- KPI CARDS — 3 principais -->
    <div class="row g-3 mb-2" id="kpiRow">
        <div class="col-12 text-center py-4">
            <div class="spinner-border" style="color:var(--cor-azul);" role="status"></div>
        </div>
    </div>

    <!-- KPI BREAKDOWN — detalhamento compacto -->
    <div id="kpiBreakdown" class="mb-4" style="display:none;"></div>

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

    <!-- LINHA 4: Ações Rápidas -->
    <div class="row g-3 mt-1">
        <div class="col-12">
            <div class="painel">
                <div class="d-flex align-items-center justify-content-between mb-3 flex-wrap gap-2">
                    <h6 class="titulo fs-secao-titulo mb-0">
                        <i class="bi bi-lightning-charge-fill titulo-azul me-2"></i>Ações Rápidas
                    </h6>
                    <span id="arLoader" style="display:none;">
                        <div class="spinner-border spinner-border-sm" style="color:var(--cor-azul);" role="status"></div>
                    </span>
                </div>
                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="ar-subtitulo">
                            <i class="bi bi-house-fill me-1" style="color:#F97316;"></i>Contas Fixas
                        </div>
                        <div id="arContasFixas"><div class="ar-empty">Carregando…</div></div>
                    </div>
                    <div class="col-md-6">
                        <div class="ar-subtitulo">
                            <i class="bi bi-credit-card-fill me-1" style="color:#3B82F6;"></i>Faturas do Mês
                        </div>
                        <div id="arFaturas"><div class="ar-empty">Carregando…</div></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>

<!-- MODAL GASTOS POR CATEGORIA -->
<div class="modal fade" id="modalCatDetalhe" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header" style="background:#2C2C44;border-bottom:1px solid #3F3F46;">
                <h5 class="modal-title" id="modalCatDetalheTitle" style="color:#F0F0F5;">
                    <i class="bi bi-tag-fill titulo-azul me-2"></i>
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="modalCatDetalheBody" style="padding:1.5rem;min-height:160px;">
                <div class="text-center py-4">
                    <div class="spinner-border" style="color:var(--cor-azul);" role="status"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function () {

    // Marco inicial: se o mês atual for anterior ao início do controle,
    // abre direto no mês do marco (antes dele não há dados).
    if (window.mesInicialPadrao) {
        var _mp = window.mesInicialPadrao(parseInt($('#mes').val(), 10), parseInt($('#anoDisplay').text(), 10));
        $('#mes').val(_mp.mes);
        $('#anoDisplay').text(_mp.ano);
    }

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

    // ─── ALERTAS FINANCEIROS ──────────────────────────────────────────────
    function alertasDashboard(mes, ano) {
        var alertas = [];
        var done = 0;

        function onDone() {
            done++;
            if (done < 2) return;
            if (!alertas.length) return;
            var count = alertas.length;
            var html = '<div class="alertas-fin-section">' +
                '<div class="alertas-fin-header">' +
                    '<i class="bi bi-bell-fill" style="color:#F59E0B;"></i>' +
                    'Alertas e Lembretes' +
                    '<span class="ms-auto" style="font-size:0.72rem;color:var(--cor-texto-off);">' + count + ' aviso' + (count !== 1 ? 's' : '') + '</span>' +
                '</div>' +
                alertas.join('') +
            '</div>';
            $('#avisosSection').html(html).show();
        }

        // Alertas de orçamento para o mês visualizado
        $.ajax({
            type: 'POST', url: 'php/controllers/OrcamentoController.php',
            data: { acao: 'buscar', mes: mes, ano: ano }, dataType: 'json',
            success: function(data) {
                if (data) $.each(data, function(_, o) {
                    var limite = parseFloat(o.valor_limite);
                    var gasto  = parseFloat(o.gasto_mes);
                    var pct    = limite > 0 ? (gasto / limite) * 100 : 0;
                    if (pct < 80) return;
                    var cor   = pct >= 100 ? '#EF4444' : '#F59E0B';
                    var icone = pct >= 100 ? 'bi-exclamation-triangle-fill' : 'bi-exclamation-circle-fill';
                    var msg   = pct >= 100
                        ? 'Limite excedido! ' + pct.toFixed(0) + '% — gastou R$ ' + formatBR(gasto) + ' / R$ ' + formatBR(limite)
                        : pct.toFixed(0) + '% do limite usado — R$ ' + formatBR(gasto) + ' / R$ ' + formatBR(limite);
                    alertas.push(
                        '<div class="alerta-fin" style="border-left:3px solid ' + cor + ';">' +
                            '<div class="alerta-fin-icon"><i class="bi ' + icone + '" style="color:' + cor + ';font-size:1.1rem;"></i></div>' +
                            '<div class="alerta-fin-body">' +
                                '<div class="alerta-fin-titulo" style="color:' + cor + ';">Orçamento: ' + escHtml(o.nome) + '</div>' +
                                '<div class="alerta-fin-msg">' + msg + '</div>' +
                            '</div>' +
                            '<a href="' + App.base + '/php/views/financas.php" class="alerta-fin-btn" style="color:' + cor + ';border-color:' + cor + '44;">Ver</a>' +
                        '</div>'
                    );
                });
            },
            complete: onDone, error: onDone
        });

        // Lembretes de contas fixas baseados na data real de hoje
        $.ajax({
            type: 'POST', url: 'php/controllers/ContasFixasController.php',
            data: { acao: 'proximosVencimentos', dias: 7 }, dataType: 'json',
            success: function(data) {
                if (data && data.length) $.each(data, function(_, cf) {
                    var diff  = cf.dias_restantes;
                    var cor   = diff < 0 ? '#EF4444' : (diff <= 2 ? '#F59E0B' : '#3B82F6');
                    var icone = diff < 0 ? 'bi-exclamation-triangle-fill' : 'bi-bell-fill';
                    var msgDia = diff < 0
                        ? 'Venceu há ' + Math.abs(diff) + ' dia' + (Math.abs(diff) !== 1 ? 's' : '') + ' (em atraso!)'
                        : diff === 0 ? 'Vence hoje!'
                        : 'Vence em ' + diff + ' dia' + (diff !== 1 ? 's' : '');
                    alertas.push(
                        '<div class="alerta-fin" style="border-left:3px solid ' + cor + ';">' +
                            '<div class="alerta-fin-icon"><i class="bi ' + icone + '" style="color:' + cor + ';font-size:1.1rem;"></i></div>' +
                            '<div class="alerta-fin-body">' +
                                '<div class="alerta-fin-titulo" style="color:' + cor + ';">' + escHtml(cf.nome) + '</div>' +
                                '<div class="alerta-fin-msg">' + msgDia + ' — R$ ' + formatBR(cf.valor) + '</div>' +
                            '</div>' +
                            '<a href="' + App.base + '/php/views/contas_fixas.php" class="alerta-fin-btn" style="color:' + cor + ';border-color:' + cor + '44;">Pagar</a>' +
                        '</div>'
                    );
                });
            },
            complete: onDone, error: onDone
        });
    }

    // ─── CARREGA TUDO ─────────────────────────────────────────────────────
    function carregaDashboard(mes, ano) {
        if (window.atualizaAvisoMarco) atualizaAvisoMarco(mes, ano);
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

        $('#arContasFixas, #arFaturas').html('<div class="ar-empty">Carregando…</div>');
        $('#avisosSection').hide().empty();

        alertasDashboard(mes, ano);

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
            window.cartoesArray = cartoes;
            if (faturas && !$.isEmptyObject(faturas)) renderFaturasDash(faturas, cartoes);
            carregaAcoesRapidas(mes, ano);
        });
    }

    // ─── KPI CARDS ───────────────────────────────────────────────────────
    function renderKPI(d) {
        const pos      = d.saldo >= 0;
        const saldoCor = pos ? '#22C55E' : '#EF4444';

        // ── Linha 1: 3 cards grandes ──
        const main = [
            { icon: 'bi-arrow-down-circle-fill',                          cor: '#22C55E', label: 'Renda estimada', sub: 'fontes de renda ativas',   valor: formatBR(d.totalRenda) },
            { icon: 'bi-arrow-up-circle-fill',                            cor: '#EF4444', label: 'Total gasto',    sub: 'todas as despesas do mês', valor: formatBR(d.totalGasto) },
            { icon: pos ? 'bi-graph-up-arrow' : 'bi-graph-down-arrow',   cor: saldoCor,  label: 'Saldo estimado', sub: 'renda − total gasto',
              valor: (pos ? '' : '− ') + formatBR(Math.abs(d.saldo)) },
        ];

        $('#kpiRow').html(main.map(c => `
            <div class="col-12 col-md-4">
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
            </div>`).join(''));

        // ── Linha 2: todos os cards menores ──
        const base = App.base + '/php/views/';
        // Recorrentes: card mostra o total de TODOS (com + sem cartão), só informativo.
        // Os que estão no cartão também aparecem em "Fatura crédito" (não somam 2x no total).
        const recorrTodos    = d.totalRecorrenteTodos != null ? d.totalRecorrenteTodos : d.totalRecorrente;
        const recorrNoCredito = recorrTodos > (d.totalRecorrente || 0) + 0.005;
        const recorrSub      = recorrNoCredito ? 'serviços · inclusos no crédito' : 'serviços e assinaturas';
        const creditoSub     = recorrNoCredito ? 'vencimento no mês · inclui recorrentes' : 'vencimento no mês';

        const sub = [
            { icon: 'bi-wallet-fill',      cor: '#F59E0B', label: 'À vista',        sub: 'débito · pix · dinheiro', valor: d.totalDebito,        href: base + 'debito.php' },
            { icon: 'bi-credit-card-fill', cor: '#3B82F6', label: 'Fatura crédito', sub: creditoSub,               valor: d.totalCredito,       href: base + 'cartaocredito.php' },
            { icon: 'bi-arrow-clockwise',  cor: '#8B5CF6', label: 'Recorrentes',    sub: recorrSub,                valor: recorrTodos,          href: base + 'gerenciamento.php?tab=Recorrentes' },
            { icon: 'bi-house-fill',       cor: '#F97316', label: 'Contas fixas',   sub: 'luz · água · internet',   valor: d.totalContasFixas || 0, href: base + 'contas_fixas.php' },
            { icon: 'bi-people-fill',      cor: '#EC4899', label: 'A pagar',        sub: 'devo a responsáveis',     valor: d.totalContas || 0,   href: base + 'responsaveis.php' },
        ];

        $('#kpiBreakdown').html(`<div class="row g-2">${sub.map(c => `
            <div class="col-6 col-sm-4 col-lg kpi-col">
                <a href="${c.href}" class="painel kpi-sm text-decoration-none d-block" style="--kpi-accent:${c.cor};">
                    <div class="d-flex align-items-center gap-2 mb-1">
                        <div class="kpi-sm-icon" style="background:${c.cor}1a;color:${c.cor};">
                            <i class="bi ${c.icon}"></i>
                        </div>
                        <span class="kpi-sm-label">${c.label}</span>
                    </div>
                    <div class="kpi-sm-valor" style="color:${c.cor};">R$ ${formatBR(c.valor)}</div>
                    <div class="kpi-sm-sub">${c.sub}</div>
                </a>
            </div>`).join('')}</div>`).show();
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

            listHtml += '<tr class="cat-list-row" data-cat="' + escHtml(d.nome) + '" style="cursor:pointer;">' +
                '<td class="cat-list-td-dot"><div class="cat-list-dot" style="background:' + cor + ';"></div></td>' +
                '<td class="cat-list-td-nome" style="color:' + cor + ';">' + icon + d.nome + '</td>' +
                '<td class="cat-list-td-pct">' + pct.toFixed(0) + '%</td>' +
                '<td class="cat-list-td-val">R$ ' + formatBR(val) + '</td>' +
            '</tr>';
        });
        listHtml += '</table>';
        $('#listaCategorias').html(listHtml);
    }

    // ─── MODAL DETALHE CATEGORIA ──────────────────────────────────────────
    var _mesesNome = ['','Janeiro','Fevereiro','Março','Abril','Maio','Junho','Julho','Agosto','Setembro','Outubro','Novembro','Dezembro'];
    var _metodoCor = { 'Dinheiro':'#6B7280','Débito':'#F59E0B','Pix':'#10B981','Crédito':'#3B82F6','Recorrente':'#8B5CF6' };

    $(document).on('click', '.cat-list-row', function () {
        abreModalCategoria($(this).data('cat'), parseInt($('#mes').val()), getAno());
    });

    function abreModalCategoria(catNome, mes, ano) {
        var catObj = window.categoriaNomes ? (window.categoriaNomes[catNome] || {}) : {};
        var cor    = catObj.cor || 'var(--cor-azul)';
        var icon   = catObj.icone ? catObj.icone + ' ' : '';
        $('#modalCatDetalheTitle').html(
            '<span style="color:' + cor + ';">' + icon + escHtml(catNome) + '</span>' +
            '<span style="font-size:0.8rem;color:var(--cor-texto-off);font-weight:400;margin-left:10px;">' + _mesesNome[mes] + '/' + ano + '</span>'
        );
        $('#modalCatDetalheBody').html('<div class="text-center py-4"><div class="spinner-border" style="color:var(--cor-azul);" role="status"></div></div>');
        $('#modalCatDetalhe').modal('show');

        $.ajax({
            type: 'POST', url: 'php/controllers/GastosController.php',
            data: { acao: 'gastosPorCategoria', categoria: catNome, mes: mes, ano: ano },
            dataType: 'json',
            success: function (data) {
                if (!data || !data.length) {
                    $('#modalCatDetalheBody').html('<div class="text-center py-5" style="color:var(--cor-texto-off);"><i class="bi bi-inbox" style="font-size:2rem;display:block;margin-bottom:8px;"></i>Nenhum lançamento encontrado.</div>');
                    return;
                }
                var total = data.reduce(function (s, g) { return s + g.valor; }, 0);
                var rows  = '';
                data.forEach(function (g) {
                    var dtFmt = g.data ? moment(g.data.substring(0, 10)).format('DD/MM') : '—';
                    var metodoBadge, parcelaHtml = '';
                    if (g.tipo === 'EU_DEVO') {
                        var label = g.parcela_info ? 'Eu devo · ' + escHtml(g.parcela_info) : 'Eu devo';
                        metodoBadge = '<span class="badge" style="background:#EF444422;color:#EF4444;border:1px solid #EF444444;font-size:0.75rem;">' + label + '</span>';
                    } else {
                        var mc = _metodoCor[g.metodo] || '#6B7280';
                        metodoBadge = '<span class="badge" style="background:' + mc + '22;color:' + mc + ';border:1px solid ' + mc + '44;font-size:0.75rem;">' + escHtml(g.metodo) + '</span>';
                        if (g.parcela_info) {
                            parcelaHtml = '<span style="font-size:0.75rem;color:var(--cor-texto-off);margin-left:5px;">' + escHtml(g.parcela_info) + '</span>';
                        }
                    }
                    rows += '<tr>' +
                        '<td>' + escHtml(g.descricao) + parcelaHtml + '</td>' +
                        '<td>' + metodoBadge + '</td>' +
                        '<td class="text-end" style="font-weight:600;color:var(--cor-azul);white-space:nowrap;">R$ ' + formatBR(g.valor) + '</td>' +
                        '<td style="color:var(--cor-texto-off);white-space:nowrap;">' + dtFmt + '</td>' +
                    '</tr>';
                });
                $('#modalCatDetalheBody').html(
                    '<div class="d-flex justify-content-between align-items-center mb-3">' +
                        '<span style="font-size:0.82rem;color:var(--cor-texto-off);">' + data.length + ' lançamento' + (data.length !== 1 ? 's' : '') + '</span>' +
                        '<span class="titulo" style="color:var(--cor-azul);font-size:1.05rem;">Total: R$ ' + formatBR(total) + '</span>' +
                    '</div>' +
                    '<div class="table-responsive">' +
                    '<table class="table table-hover mb-0" style="font-size:0.85rem;">' +
                        '<thead><tr><th>Descrição</th><th>Método</th><th class="text-end">Valor</th><th>Data</th></tr></thead>' +
                        '<tbody>' + rows + '</tbody>' +
                    '</table></div>'
                );
            },
            error: function () {
                $('#modalCatDetalheBody').html('<div class="text-center py-4 text-danger">Erro ao carregar lançamentos.</div>');
            }
        });
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
        window.faturasTotais = {};
        $.each(faturas, function (idCartao, items) {
            window.faturasTotais[idCartao] = items.valortotal || '0,00';
        });
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

    // ─── AÇÕES RÁPIDAS ───────────────────────────────────────────────────
    function carregaAcoesRapidas(mes, ano) {
        $('#arLoader').show();

        var cfItems     = null;
        var fatResults  = null;
        var cfDone      = false;
        var fatDone     = false;

        function tentaRenderAvisos() {
            if (cfDone && fatDone) renderAvisos(cfItems, fatResults, mes, ano);
        }

        // Contas Fixas pendentes
        $.ajax({
            type: 'POST', url: 'php/controllers/ContasFixasController.php',
            data: { acao: 'resumoMes', mes: mes, ano: ano }, dataType: 'json',
            success: function (data) {
                cfItems = data;
                renderArContasFixas(data, mes, ano);
            },
            error: function () { $('#arContasFixas').html('<div class="ar-empty text-danger">Erro ao carregar.</div>'); },
            complete: function () { cfDone = true; tentaRenderAvisos(); }
        });

        // Fatura status por cartão
        var cartoes = window.cartoesArray || {};
        var lista = [];
        $.each(cartoes, function (_, c) { lista.push(c); });

        if (!lista.length) {
            $('#arFaturas').html('<div class="ar-empty">Nenhum cartão cadastrado.</div>');
            fatResults = [];
            fatDone = true;
            $('#arLoader').hide();
            tentaRenderAvisos();
            return;
        }

        var pendente = lista.length;
        var resultados = [];
        lista.forEach(function (cartao) {
            $.ajax({
                type: 'POST', url: 'php/controllers/CartoesController.php',
                data: { acao: 'faturaPaga', cartaoId: cartao.id, mes: mes, ano: ano },
                dataType: 'json',
                success: function (r) {
                    resultados.push({ cartao: cartao, pago: r.pago, dataPago: r.data_pagamento });
                },
                complete: function () {
                    pendente--;
                    if (pendente === 0) {
                        fatResults = resultados;
                        fatDone = true;
                        renderArFaturas(resultados, mes, ano);
                        $('#arLoader').hide();
                        tentaRenderAvisos();
                    }
                }
            });
        });
    }

    function renderArContasFixas(items, mes, ano) {
        if (!items || !items.length) {
            $('#arContasFixas').html('<div class="ar-empty">Nenhuma conta fixa cadastrada.</div>');
            return;
        }
        var html = '';
        items.forEach(function (cf) {
            var pago = cf.pago;
            html += '<div class="ar-item" data-id="' + cf.id + '">' +
                '<div class="ar-item-info">' +
                    '<span class="ar-item-dot" style="background:' + (cf.cor || '#F97316') + ';"></span>' +
                    '<div>' +
                        '<div class="ar-item-nome">' + escHtml(cf.nome) + '</div>' +
                        '<div class="ar-item-val">R$ ' + formatBR(cf.valor) + '</div>' +
                    '</div>' +
                '</div>' +
                '<button class="ar-btn ' + (pago ? 'ar-btn-pago' : 'ar-btn-pagar') + '" ' +
                    'data-tipo="cf" data-id="' + cf.id + '" data-mes="' + mes + '" data-ano="' + ano + '" ' +
                    'data-valor="' + cf.valor + '" data-pago="' + (pago ? 1 : 0) + '">' +
                    (pago ? '<i class="bi bi-check-circle-fill me-1"></i>Pago' : '<i class="bi bi-check-circle me-1"></i>Pagar') +
                '</button>' +
            '</div>';
        });
        $('#arContasFixas').html(html);
    }

    function renderArFaturas(resultados, mes, ano) {
        if (!resultados.length) {
            $('#arFaturas').html('<div class="ar-empty">Nenhum cartão.</div>');
            return;
        }
        resultados.sort(function (a, b) { return a.pago - b.pago; });
        var html = '';
        resultados.forEach(function (r) {
            var cor   = r.cartao.cor || '#3B82F6';
            var total = (window.faturasTotais || {})[r.cartao.id] || '0,00';
            html += '<div class="ar-item" data-id="' + r.cartao.id + '">' +
                '<div class="ar-item-info">' +
                    '<i class="bi bi-credit-card-fill" style="color:' + cor + ';font-size:1rem;flex-shrink:0;"></i>' +
                    '<div>' +
                        '<div class="ar-item-nome">' + escHtml(r.cartao.nome_cartao) + '</div>' +
                        '<div class="ar-item-val">R$ ' + total + '</div>' +
                    '</div>' +
                '</div>' +
                '<button class="ar-btn ' + (r.pago ? 'ar-btn-pago' : 'ar-btn-pagar') + '" ' +
                    'data-tipo="fat" data-id="' + r.cartao.id + '" data-mes="' + mes + '" data-ano="' + ano + '" ' +
                    'data-pago="' + (r.pago ? 1 : 0) + '">' +
                    (r.pago ? '<i class="bi bi-check-circle-fill me-1"></i>Paga' : '<i class="bi bi-check-circle me-1"></i>Marcar paga') +
                '</button>' +
            '</div>';
        });
        $('#arFaturas').html(html);
    }

    function renderAvisos(cfItems, fatResults, mes, ano) {
        var hoje     = new Date();
        var diaHoje  = hoje.getDate();
        var mesHoje  = hoje.getMonth() + 1;
        var anoHoje  = hoje.getFullYear();

        // Só calcula avisos para o mês atual
        if (parseInt(mes) !== mesHoje || parseInt(ano) !== anoHoje) return;

        var avisos = [];

        // ── Contas Fixas ──
        (cfItems || []).forEach(function (cf) {
            if (cf.pago) return;
            var dias = parseInt(cf.dia_vencimento) - diaHoje;
            if (dias < 0) {
                avisos.push({ nivel: 'danger', icon: 'bi-exclamation-triangle-fill',
                    msg: 'Vencida há ' + Math.abs(dias) + ' dia(s): <strong>' + escHtml(cf.nome) + '</strong> (dia ' + cf.dia_vencimento + ')', dias: dias });
            } else if (dias === 0) {
                avisos.push({ nivel: 'danger', icon: 'bi-exclamation-triangle-fill',
                    msg: 'Vence <strong>hoje</strong>: <strong>' + escHtml(cf.nome) + '</strong>', dias: dias });
            } else if (dias <= 7) {
                var nivel = dias <= 3 ? 'warning' : 'info';
                avisos.push({ nivel: nivel, icon: 'bi-clock-fill',
                    msg: escHtml(cf.nome) + ' vence em <strong>' + dias + ' dia(s)</strong> (dia ' + cf.dia_vencimento + ')', dias: dias });
            }
        });

        // ── Faturas de Cartão ──
        (fatResults || []).forEach(function (r) {
            if (r.pago) return;
            var venc = parseInt(r.cartao.vencimento_dia);
            if (!venc) return;
            var dias = venc - diaHoje;
            if (dias < 0) {
                avisos.push({ nivel: 'danger', icon: 'bi-credit-card-fill',
                    msg: 'Fatura vencida há ' + Math.abs(dias) + ' dia(s): <strong>' + escHtml(r.cartao.nome_cartao) + '</strong> (dia ' + venc + ')', dias: dias });
            } else if (dias === 0) {
                avisos.push({ nivel: 'danger', icon: 'bi-credit-card-fill',
                    msg: 'Fatura vence <strong>hoje</strong>: <strong>' + escHtml(r.cartao.nome_cartao) + '</strong>', dias: dias });
            } else if (dias <= 7) {
                var nivel = dias <= 3 ? 'warning' : 'info';
                avisos.push({ nivel: nivel, icon: 'bi-credit-card-fill',
                    msg: 'Fatura ' + escHtml(r.cartao.nome_cartao) + ' vence em <strong>' + dias + ' dia(s)</strong> (dia ' + venc + ')', dias: dias });
            }
        });

        if (!avisos.length) { $('#avisosSection').hide(); return; }

        avisos.sort(function (a, b) { return a.dias - b.dias; });

        var corMap   = { danger: '#EF4444', warning: '#F59E0B', info: '#3B82F6' };
        var bgMap    = { danger: '#EF444415', warning: '#F59E0B15', info: '#3B82F615' };
        var html = '<div class="avisos-wrap">';
        avisos.forEach(function (av) {
            var cor = corMap[av.nivel];
            var bg  = bgMap[av.nivel];
            html += '<div class="aviso-item" style="background:' + bg + ';border-left:3px solid ' + cor + ';">' +
                '<i class="bi ' + av.icon + '" style="color:' + cor + ';font-size:0.95rem;flex-shrink:0;"></i>' +
                '<span class="aviso-msg">' + av.msg + '</span>' +
            '</div>';
        });
        html += '</div>';

        $('#avisosSection').html(html).show();
    }

    // Handlers dos botões de ação rápida
    $(document).on('click', '.ar-btn', function () {
        var $btn  = $(this);
        var tipo  = $btn.data('tipo');
        var id    = $btn.data('id');
        var mes   = $btn.data('mes');
        var ano   = $btn.data('ano');
        var pago  = parseInt($btn.data('pago'));
        var novoPago = pago ? 0 : 1;

        $btn.prop('disabled', true);

        if (tipo === 'cf') {
            var acao = novoPago ? 'marcarPago' : 'desmarcarPago';
            var payload = { acao: acao, id: id, mes: mes, ano: ano, data: new Date().toISOString().slice(0,10), valor: $btn.data('valor') };
            $.ajax({
                type: 'POST', url: 'php/controllers/ContasFixasController.php',
                data: payload, dataType: 'json',
                success: function () {
                    $btn.data('pago', novoPago)
                        .removeClass('ar-btn-pagar ar-btn-pago')
                        .addClass(novoPago ? 'ar-btn-pago' : 'ar-btn-pagar')
                        .html(novoPago
                            ? '<i class="bi bi-check-circle-fill me-1"></i>Pago'
                            : '<i class="bi bi-check-circle me-1"></i>Pagar');
                    toastr.success(novoPago ? 'Conta marcada como paga!' : 'Pagamento desfeito.');
                },
                error: function () { toastr.error('Erro ao atualizar.'); },
                complete: function () { $btn.prop('disabled', false); }
            });
        } else {
            $.ajax({
                type: 'POST', url: 'php/controllers/CartoesController.php',
                data: { acao: 'marcarFaturaPaga', cartaoId: id, mes: mes, ano: ano, data: new Date().toISOString().slice(0,10), pago: novoPago },
                dataType: 'json',
                success: function () {
                    $btn.data('pago', novoPago)
                        .removeClass('ar-btn-pagar ar-btn-pago')
                        .addClass(novoPago ? 'ar-btn-pago' : 'ar-btn-pagar')
                        .html(novoPago
                            ? '<i class="bi bi-check-circle-fill me-1"></i>Paga'
                            : '<i class="bi bi-check-circle me-1"></i>Marcar paga');
                    toastr.success(novoPago ? 'Fatura marcada como paga!' : 'Fatura desmarcada.');
                },
                error: function () { toastr.error('Erro ao atualizar.'); },
                complete: function () { $btn.prop('disabled', false); }
            });
        }
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
/* ── KPI Cards — 3 principais ── */
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

/* ── kpi-col: stretch igual em telas grandes ── */
.kpi-col { flex: 1 1 0; min-width: 0; }

/* ── Cards menores (linha 2) ── */
.kpi-sm {
    border-left: 3px solid var(--kpi-accent, var(--cor-azul));
    padding: 0.6rem 0.85rem;
    transition: transform var(--trans), box-shadow var(--trans);
}
.kpi-sm:hover { transform: translateY(-2px); box-shadow: var(--sombra-md); }
.kpi-sm-icon {
    width: 26px; height: 26px; border-radius: 7px;
    display: flex; align-items: center; justify-content: center;
    font-size: 0.78rem; flex-shrink: 0;
}
.kpi-sm-label { font-size: 0.72rem; font-weight: 600; color: var(--cor-texto-sec); line-height: 1.2; }
.kpi-sm-valor {
    font-family: "Bebas Neue", sans-serif;
    font-size: 1.2rem; line-height: 1;
    margin-bottom: 0.08rem; letter-spacing: 0.02em;
}
.kpi-sm-sub { font-size: 0.65rem; color: var(--cor-texto-off); }

/* ── Avisos ── */
.avisos-wrap  { display: flex; flex-direction: column; gap: 0.4rem; }
.aviso-item   { display: flex; align-items: center; gap: 0.65rem; padding: 0.55rem 0.85rem; border-radius: var(--radius-sm); font-size: 0.82rem; }
.aviso-msg    { color: var(--cor-texto); line-height: 1.3; }
.aviso-msg strong { color: inherit; }

/* ── Ações Rápidas ── */
.ar-subtitulo { font-size: 0.78rem; font-weight: 700; color: var(--cor-texto-off); text-transform: uppercase; letter-spacing: .05em; margin-bottom: 0.5rem; }
.ar-empty     { font-size: 0.82rem; color: var(--cor-texto-off); padding: 0.4rem 0; }
.ar-item      { display: flex; align-items: center; justify-content: space-between; gap: 0.75rem; padding: 0.5rem 0; border-bottom: 1px solid var(--cor-borda); }
.ar-item:last-child { border-bottom: none; }
.ar-item-info { display: flex; align-items: center; gap: 0.6rem; min-width: 0; }
.ar-item-dot  { width: 8px; height: 8px; border-radius: 50%; flex-shrink: 0; }
.ar-item-nome { font-size: 0.85rem; font-weight: 500; color: var(--cor-texto); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 160px; }
.ar-item-val  { font-size: 0.75rem; color: var(--cor-texto-off); }
.ar-btn { border: none; border-radius: 20px; padding: 0.25rem 0.75rem; font-size: 0.75rem; font-weight: 600; cursor: pointer; white-space: nowrap; transition: all .2s; flex-shrink: 0; }
.ar-btn-pagar { background: #EF444422; color: #EF4444; }
.ar-btn-pagar:hover { background: #EF4444; color: #fff; }
.ar-btn-pago  { background: #22C55E22; color: #22C55E; }
.ar-btn-pago:hover { background: #22C55E33; }

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
.cat-list-row:hover td { background: rgba(255,255,255,.04); }

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
