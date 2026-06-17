<?php
require_once __DIR__ . '/../templates/header.php';
$anoAtual = (int) date('Y');
?>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

<div class="animate__animated animate__fadeIn">

    <!-- HEADER -->
    <div class="d-flex align-items-center justify-content-between titulo-pagina mb-4 flex-wrap gap-2">
        <h1 class="titulo mt-2 fs-titulo-pag">
            Resumo Anual &nbsp;<i class="bi bi-calendar-range titulo-azul"></i>
        </h1>
        <div class="d-flex align-items-center gap-2">
            <i class="bi bi-arrow-left-square-fill botao" id="anoEsq"></i>
            <span id="anoDisplay" class="titulo" style="font-size:1.6rem;min-width:64px;text-align:center;"><?= $anoAtual ?></span>
            <i class="bi bi-arrow-right-square-fill botao" id="anoDir"></i>
        </div>
    </div>

    <!-- KPI CARDS -->
    <div class="row g-3 mb-4" id="kpiAnual">
        <div class="col-12 text-center py-4">
            <div class="spinner-border" style="color:var(--cor-azul);" role="status"></div>
        </div>
    </div>

    <!-- LINHA 2: Barras mensais | Categorias -->
    <div class="row g-3 mb-3">

        <div class="col-xl-8">
            <div class="painel h-100">
                <h6 class="titulo fs-secao-titulo mb-3">
                    <i class="bi bi-bar-chart-fill titulo-azul me-2"></i>Receitas vs Despesas por Mês
                </h6>
                <div style="position:relative;height:260px;">
                    <canvas id="chartBarras"></canvas>
                </div>
            </div>
        </div>

        <div class="col-xl-4">
            <div class="painel h-100 d-flex flex-column">
                <h6 class="titulo fs-secao-titulo mb-3">
                    <i class="bi bi-pie-chart-fill titulo-azul me-2"></i>Gastos por Categoria
                </h6>
                <div id="catChartArea" class="d-flex flex-column align-items-center gap-3 flex-grow-1">
                    <div style="width:160px;height:160px;position:relative;flex-shrink:0;">
                        <canvas id="chartCat"></canvas>
                        <div style="position:absolute;inset:0;display:flex;flex-direction:column;align-items:center;justify-content:center;pointer-events:none;gap:1px;">
                            <span style="font-size:0.6rem;color:var(--cor-texto-off);text-transform:uppercase;letter-spacing:.05em;">Total</span>
                            <span id="catTotal" class="titulo" style="font-size:0.9rem;color:var(--cor-texto);"></span>
                        </div>
                    </div>
                    <div id="catLista" style="width:100%;overflow-y:auto;max-height:160px;display:flex;justify-content:center;"></div>
                </div>
                <div id="catVazio" style="display:none;" class="text-center py-4">
                    <i class="bi bi-pie-chart" style="font-size:2rem;color:var(--cor-borda);"></i>
                    <p class="mt-2 mb-0" style="color:var(--cor-texto-off);">Sem gastos no ano</p>
                </div>
            </div>
        </div>

    </div>

    <!-- LINHA 3: Saldo mensal | Tabela -->
    <div class="row g-3">

        <div class="col-xl-5">
            <div class="painel h-100">
                <h6 class="titulo fs-secao-titulo mb-3">
                    <i class="bi bi-graph-up titulo-azul me-2"></i>Evolução do Saldo
                </h6>
                <div style="position:relative;height:220px;">
                    <canvas id="chartSaldo"></canvas>
                </div>
            </div>
        </div>

        <div class="col-xl-7">
            <div class="painel h-100">
                <h6 class="titulo fs-secao-titulo mb-3">
                    <i class="bi bi-table titulo-azul me-2"></i>Resumo Mensal
                </h6>
                <div class="table-responsive">
                    <table class="table table-hover mb-0" style="font-size:0.83rem;" id="tabelaMensal">
                        <thead>
                            <tr>
                                <th>Mês</th>
                                <th class="text-end">Renda</th>
                                <th class="text-end">À Vista</th>
                                <th class="text-end">Crédito</th>
                                <th class="text-end">Recorrente</th>
                                <th class="text-end">Saldo</th>
                            </tr>
                        </thead>
                        <tbody id="tbodyMensal">
                            <tr><td colspan="6" class="text-center py-3">
                                <div class="spinner-border spinner-border-sm" style="color:var(--cor-azul);" role="status"></div>
                            </td></tr>
                        </tbody>
                        <tfoot id="tfootMensal" style="display:none;">
                            <tr style="border-top:2px solid var(--cor-borda);font-weight:700;">
                                <td>Total</td>
                                <td class="text-end" id="footRenda"></td>
                                <td class="text-end" id="footDebito"></td>
                                <td class="text-end" id="footCredito"></td>
                                <td class="text-end" id="footRecorrente"></td>
                                <td class="text-end" id="footSaldo"></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>

    </div>

</div>

<script>
$(document).ready(function () {

    const mAbrev  = ['','Jan','Fev','Mar','Abr','Mai','Jun','Jul','Ago','Set','Out','Nov','Dez'];
    const mNomes  = ['','Janeiro','Fevereiro','Março','Abril','Maio','Junho','Julho','Agosto','Setembro','Outubro','Novembro','Dezembro'];
    const catCores = ['#3B82F6','#10B981','#F59E0B','#EF4444','#8B5CF6','#EC4899','#F97316','#06B6D4','#84CC16','#9CA3AF'];

    let chartBarras = null;
    let chartCat    = null;
    let chartSaldo  = null;

    function getAno() { return parseInt($('#anoDisplay').text()); }

    $('#anoEsq').click(function () { $('#anoDisplay').text(getAno() - 1); carregar(); });
    $('#anoDir').click(function () { $('#anoDisplay').text(getAno() + 1); carregar(); });

    // ─── CARREGAR ────────────────────────────────────────────────────────────
    function carregar() {
        $('#kpiAnual').html('<div class="col-12 text-center py-4"><div class="spinner-border" style="color:var(--cor-azul);" role="status"></div></div>');
        $('#tbodyMensal').html('<tr><td colspan="6" class="text-center py-3"><div class="spinner-border spinner-border-sm" style="color:var(--cor-azul);" role="status"></div></td></tr>');
        $('#tfootMensal').hide();
        $('#catVazio').hide();
        $('#catChartArea').show();

        $.ajax({
            type: 'POST', url: App.ctrl.gastos,
            data: { acao: 'resumoAnual', ano: getAno() }, dataType: 'json',
            success: function (d) {
                renderKPI(d.totais, d.melhorMes, d.piorMes);
                renderBarras(d.meses);
                renderCategorias(d.porCategoria);
                renderSaldo(d.meses);
                renderTabela(d.meses, d.totais);
            },
            error: function () { toastr.error('Erro ao carregar resumo anual!'); }
        });
    }

    // ─── KPI CARDS ───────────────────────────────────────────────────────────
    function renderKPI(t, melhor, pior) {
        const pos     = t.saldo >= 0;
        const poupPct = t.renda > 0 ? Math.max(0, (t.saldo / t.renda) * 100) : 0;
        const cards   = [
            { icon: 'bi-arrow-down-circle-fill', cor: '#22C55E', label: 'Renda Total',      sub: 'estimativa anual',           valor: formatBR(t.renda) },
            { icon: 'bi-receipt',                cor: '#EF4444', label: 'Total Gasto',       sub: 'à vista + crédito + fixos',  valor: formatBR(t.gasto) },
            {
                icon:  pos ? 'bi-graph-up-arrow' : 'bi-graph-down-arrow',
                cor:   pos ? '#22C55E' : '#EF4444',
                label: 'Saldo Anual',
                sub:   'renda − gastos no ano',
                valor: (pos ? '' : '− ') + formatBR(Math.abs(t.saldo)),
            },
            { icon: 'bi-piggy-bank-fill', cor: '#8B5CF6', label: 'Taxa de Poupança', sub: 'percentual da renda guardado', valor: poupPct.toFixed(1) + '%' },
            melhor ? {
                icon: 'bi-trophy-fill', cor: '#F59E0B',
                label: 'Melhor Mês',
                sub:  'maior saldo positivo',
                valor: mNomes[melhor.mes],
                raw: true
            } : null,
            pior ? {
                icon: 'bi-exclamation-triangle-fill', cor: '#EF4444',
                label: 'Pior Mês',
                sub:  'menor saldo',
                valor: mNomes[pior.mes],
                raw: true
            } : null,
        ].filter(Boolean);

        const html = cards.map(c => `
            <div class="col-6 col-lg-4 col-xl-2 kpi-col">
                <div class="painel kpi-card" style="--kpi-accent:${c.cor};">
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <div class="kpi-icon" style="background:${c.cor}22;color:${c.cor};">
                            <i class="bi ${c.icon}"></i>
                        </div>
                        <span class="kpi-label">${c.label}</span>
                    </div>
                    <div class="kpi-valor ${c.raw ? 'kpi-valor-sm' : ''}" style="color:${c.cor};">${c.raw ? c.valor : 'R$ ' + c.valor}</div>
                    <div class="kpi-sub">${c.sub}</div>
                </div>
            </div>`).join('');

        $('#kpiAnual').html(html);
    }

    // ─── GRÁFICO DE BARRAS ───────────────────────────────────────────────────
    function renderBarras(meses) {
        if (chartBarras) { chartBarras.destroy(); chartBarras = null; }

        const labels  = meses.map(m => mAbrev[m.mes]);
        const renda   = meses.map(m => +m.renda.toFixed(2));
        const gasto   = meses.map(m => +m.gasto.toFixed(2));

        chartBarras = new Chart(document.getElementById('chartBarras').getContext('2d'), {
            type: 'bar',
            data: {
                labels,
                datasets: [
                    {
                        label: 'Renda',
                        data: renda,
                        backgroundColor: '#22C55E44',
                        borderColor: '#22C55E',
                        borderWidth: 2,
                        borderRadius: 4,
                    },
                    {
                        label: 'Gasto',
                        data: gasto,
                        backgroundColor: '#EF444444',
                        borderColor: '#EF4444',
                        borderWidth: 2,
                        borderRadius: 4,
                    },
                ]
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                plugins: {
                    legend: {
                        labels: { color: '#9CA3AF', font: { size: 12 }, boxWidth: 14, padding: 16 }
                    },
                    tooltip: {
                        backgroundColor: '#2B2C3B', titleColor: '#F0F0F5', bodyColor: '#9CA3AF',
                        borderColor: '#3F3F46', borderWidth: 1, padding: 10,
                        callbacks: { label: ctx => ' R$ ' + formatBR(ctx.parsed.y) }
                    }
                },
                scales: {
                    x: { ticks: { color: '#9CA3AF' }, grid: { color: '#3F3F4622' } },
                    y: {
                        ticks: { color: '#9CA3AF', callback: v => 'R$ ' + formatBR(v) },
                        grid: { color: '#3F3F4644' },
                        beginAtZero: true,
                    }
                }
            }
        });
    }

    // ─── GRÁFICO CATEGORIAS ──────────────────────────────────────────────────
    function renderCategorias(dados) {
        if (chartCat) { chartCat.destroy(); chartCat = null; }
        $('#catLista').empty();
        $('#catTotal').text('');

        if (!dados || !dados.length) {
            $('#catChartArea').hide();
            $('#catVazio').show();
            return;
        }

        const total = dados.reduce((s, d) => s + parseFloat(d.total), 0);
        const cores = dados.map((d, i) => d.cor || catCores[i % catCores.length]);

        $('#catTotal').text('R$ ' + formatBR(total));

        chartCat = new Chart(document.getElementById('chartCat').getContext('2d'), {
            type: 'doughnut',
            data: {
                labels: dados.map(d => d.nome),
                datasets: [{ data: dados.map(d => parseFloat(d.total)), backgroundColor: cores, borderColor: '#1E1E2F', borderWidth: 3, hoverOffset: 8 }]
            },
            options: {
                responsive: true, maintainAspectRatio: false, cutout: '68%',
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: { label: ctx => ' R$ ' + formatBR(ctx.parsed) },
                        backgroundColor: '#2B2C3B', titleColor: '#F0F0F5', bodyColor: '#9CA3AF',
                        borderColor: '#3F3F46', borderWidth: 1, padding: 10,
                    }
                }
            }
        });

        let rows = '<table class="cat-list-table">';
        dados.forEach(function (d, i) {
            const val = parseFloat(d.total);
            const pct = (val / total * 100).toFixed(0);
            const cor = cores[i];
            const icon = d.icone ? '<span class="me-1">' + d.icone + '</span>' : '';
            rows += '<tr>' +
                '<td class="cat-list-td-dot"><div class="cat-list-dot" style="background:' + cor + ';"></div></td>' +
                '<td class="cat-list-td-nome" style="color:' + cor + ';">' + icon + d.nome + '</td>' +
                '<td class="cat-list-td-pct">' + pct + '%</td>' +
                '<td class="cat-list-td-val">R$ ' + formatBR(val) + '</td>' +
            '</tr>';
        });
        rows += '</table>';
        $('#catLista').html(rows);
    }

    // ─── GRÁFICO SALDO ───────────────────────────────────────────────────────
    function renderSaldo(meses) {
        if (chartSaldo) { chartSaldo.destroy(); chartSaldo = null; }

        const labels = meses.map(m => mAbrev[m.mes]);
        const saldos = meses.map(m => +m.saldo.toFixed(2));
        const cores  = saldos.map(s => s >= 0 ? '#22C55E' : '#EF4444');

        chartSaldo = new Chart(document.getElementById('chartSaldo').getContext('2d'), {
            type: 'line',
            data: {
                labels,
                datasets: [{
                    label: 'Saldo',
                    data: saldos,
                    borderColor: '#3B82F6',
                    backgroundColor: 'rgba(59,130,246,0.1)',
                    borderWidth: 2,
                    pointBackgroundColor: cores,
                    pointBorderColor: cores,
                    pointRadius: 5,
                    pointHoverRadius: 7,
                    fill: true,
                    tension: 0.3,
                }]
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: '#2B2C3B', titleColor: '#F0F0F5', bodyColor: '#9CA3AF',
                        borderColor: '#3F3F46', borderWidth: 1, padding: 10,
                        callbacks: {
                            label: ctx => {
                                const v = ctx.parsed.y;
                                return (v >= 0 ? ' + R$ ' : ' − R$ ') + formatBR(Math.abs(v));
                            }
                        }
                    }
                },
                scales: {
                    x: { ticks: { color: '#9CA3AF' }, grid: { color: '#3F3F4622' } },
                    y: {
                        ticks: { color: '#9CA3AF', callback: v => 'R$ ' + formatBR(v) },
                        grid: { color: '#3F3F4644' },
                    }
                }
            }
        });
    }

    // ─── TABELA MENSAL ───────────────────────────────────────────────────────
    function renderTabela(meses, totais) {
        let rows = '';
        meses.forEach(function (m) {
            const pos   = m.saldo >= 0;
            const cor   = pos ? '#22C55E' : '#EF4444';
            const temDados = m.gasto > 0 || m.renda > 0;
            rows += '<tr style="' + (!temDados ? 'opacity:0.4;' : '') + '">' +
                '<td><strong>' + mNomes[m.mes] + '</strong></td>' +
                '<td class="text-end" style="color:#22C55E;">R$ ' + formatBR(m.renda) + '</td>' +
                '<td class="text-end">R$ ' + formatBR(m.debito) + '</td>' +
                '<td class="text-end">R$ ' + formatBR(m.credito) + '</td>' +
                '<td class="text-end">R$ ' + formatBR(m.recorrente) + '</td>' +
                '<td class="text-end" style="color:' + cor + ';font-weight:600;">' +
                    (pos ? '' : '− ') + 'R$ ' + formatBR(Math.abs(m.saldo)) +
                '</td>' +
            '</tr>';
        });

        $('#tbodyMensal').html(rows);

        const posTotal = totais.saldo >= 0;
        const corTotal = posTotal ? '#22C55E' : '#EF4444';
        $('#footRenda').html('<span style="color:#22C55E;">R$ ' + formatBR(totais.renda) + '</span>');
        $('#footDebito').text('R$ ' + formatBR(totais.debito));
        $('#footCredito').text('R$ ' + formatBR(totais.credito));
        $('#footRecorrente').text('R$ ' + formatBR(totais.recorrente));
        $('#footSaldo').html('<span style="color:' + corTotal + ';">' + (posTotal ? '' : '− ') + 'R$ ' + formatBR(Math.abs(totais.saldo)) + '</span>');
        $('#tfootMensal').show();
    }

    // ─── UTILITÁRIOS ─────────────────────────────────────────────────────────
    function formatBR(n) {
        return parseFloat(n || 0).toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    carregar();
});
</script>

<style>
/* KPI Cards */
.kpi-col { flex: 1 1 0; min-width: 0; }
.kpi-card {
    height: 100%;
    border-left: 3px solid var(--kpi-accent, var(--cor-azul));
    transition: transform var(--trans), box-shadow var(--trans);
}
.kpi-card:hover { transform: translateY(-3px); box-shadow: var(--sombra-md); }
.kpi-icon {
    width: 38px; height: 38px;
    border-radius: var(--radius-md);
    display: flex; align-items: center; justify-content: center;
    font-size: 1.1rem; flex-shrink: 0;
}
.kpi-label { color: var(--cor-texto-sec); font-size: 0.78rem; font-weight: 500; line-height: 1.2; }
.kpi-valor {
    font-family: "Bebas Neue", sans-serif;
    font-size: 1.55rem; line-height: 1;
    margin-bottom: 0.15rem; letter-spacing: 0.02em;
}
.kpi-valor-sm { font-size: 1.2rem; }
.kpi-sub { color: var(--cor-texto-off); font-size: 0.72rem; }

/* Categoria lista */
.cat-list-table { border-collapse: collapse; }
.cat-list-table tr td { padding: 2px 4px; border: none; background: transparent !important; vertical-align: middle; }
.cat-list-td-dot  { width: 14px; }
.cat-list-dot     { width: 8px; height: 8px; border-radius: 50%; }
.cat-list-td-nome { font-size: 0.76rem; font-weight: 500; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 100px; }
.cat-list-td-pct  { font-size: 0.7rem; color: var(--cor-texto-off); white-space: nowrap; padding-left: 8px !important; }
.cat-list-td-val  { font-size: 0.76rem; font-weight: 600; color: var(--cor-texto); white-space: nowrap; padding-left: 8px !important; }

/* Tabela */
#tabelaMensal th { font-size: 0.75rem; }
#tabelaMensal td { vertical-align: middle; }

@media (max-width: 576px) {
    .kpi-valor { font-size: 1.2rem; }
    .kpi-valor-sm { font-size: 1rem; }
}
</style>

<?php require_once __DIR__ . '/../templates/footer.php'; ?>
