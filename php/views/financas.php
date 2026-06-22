<?php
require_once __DIR__ . '/../templates/header.php';
require_once __DIR__ . '/../../conn/conn.php';

$conn = Database::getConnection();
?>

<div class="animate__animated animate__fadeIn">

    <!-- HEADER -->
    <div class="d-flex align-items-center justify-content-between titulo-pagina mb-4 flex-wrap gap-2">
        <h1 class="titulo mt-2 fs-titulo-pag">
            Finanças &nbsp;<i class="bi bi-bank titulo-azul"></i>
        </h1>
        <div class="d-flex align-items-center gap-2">
            <i class="bi bi-arrow-left-square-fill botao botaoEsquerda" id="finLeft"></i>
            <select class="form-select text-center" id="mesFin" style="width:140px;">
                <?php
                $mesesOrc = [1=>'Janeiro',2=>'Fevereiro',3=>'Março',4=>'Abril',
                             5=>'Maio',6=>'Junho',7=>'Julho',8=>'Agosto',
                             9=>'Setembro',10=>'Outubro',11=>'Novembro',12=>'Dezembro'];
                $mesAtualFin = (int) date('n');
                foreach ($mesesOrc as $n => $nome): ?>
                    <option value="<?= $n ?>" <?= (int)$n === $mesAtualFin ? 'selected' : '' ?>><?= $nome ?></option>
                <?php endforeach; ?>
            </select>
            <i class="bi bi-arrow-right-square-fill botao botaoDireita" id="finRight"></i>
            <span style="color:var(--cor-borda);font-size:1.1rem;">|</span>
            <i class="bi bi-arrow-left-square-fill botao" id="finYearLeft"></i>
            <span id="finAnoDisplay" style="font-size:0.95rem;font-weight:700;min-width:44px;text-align:center;"><?= date('Y') ?></span>
            <i class="bi bi-arrow-right-square-fill botao" id="finYearRight"></i>
        </div>
    </div>

    <div class="container-fluid px-0">

        <!-- FONTES DE RENDA -->
        <div class="painel mb-3">
            <div class="d-flex align-items-center justify-content-between mb-3 flex-wrap gap-2">
                <h5 class="titulo m-0 fs-secao-titulo">
                    <i class="bi bi-currency-dollar titulo-azul me-1"></i>Fontes de Renda
                </h5>
                <div class="d-flex align-items-center gap-2">
                    <span class="titulo text-success" id="totalRendaMes" style="font-size:1rem;">R$ 0,00</span>
                    <button class="btn btn-success btn-sm" id="btnAdicionarRenda">
                        <i class="bi bi-plus-lg"></i> Adicionar
                    </button>
                </div>
            </div>

            <div class="text-center py-3" id="loaderRendas">
                <div class="spinner-border spinner-border-sm" style="color:var(--cor-azul);" role="status"></div>
            </div>

            <div id="emptyRendas" style="display:none;" class="text-center py-4">
                <i class="bi bi-wallet2" style="font-size:2rem;color:var(--cor-borda);"></i>
                <p class="mt-2 mb-0" style="color:var(--cor-texto-off);">Nenhuma fonte de renda cadastrada.</p>
            </div>

            <div id="itensRendas"></div>
        </div>

        <!-- ORÇAMENTO POR CATEGORIA -->
        <div class="painel mb-3">
            <div class="d-flex align-items-center justify-content-between mb-3 flex-wrap gap-2">
                <h5 class="titulo m-0 fs-secao-titulo">
                    <i class="bi bi-bar-chart-steps titulo-azul me-1"></i>Orçamento por Categoria
                </h5>
                <div class="d-flex align-items-center gap-2">
                    <button class="btn btn-success btn-sm" id="btnAdicionarOrcamento">
                        <i class="bi bi-plus-lg"></i> Definir
                    </button>
                </div>
            </div>

            <div class="text-center py-3" id="loaderOrcamento">
                <div class="spinner-border spinner-border-sm" style="color:var(--cor-azul);" role="status"></div>
            </div>

            <div id="emptyOrcamento" style="display:none;" class="text-center py-4">
                <i class="bi bi-bar-chart" style="font-size:2rem;color:var(--cor-borda);"></i>
                <p class="mt-2 mb-0" style="color:var(--cor-texto-off);">Nenhum orçamento definido ainda.</p>
                <button class="btn btn-sm btn-outline-primary mt-2" id="btnAdicionarOrcamentoEmpty">
                    <i class="bi bi-plus-lg me-1"></i>Definir orçamento
                </button>
            </div>

            <div id="itensOrcamento"></div>
        </div>

        <!-- META DE ECONOMIA -->
        <div class="painel mb-3">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <h5 class="titulo m-0 fs-secao-titulo">
                    <i class="bi bi-bullseye titulo-azul me-1"></i>Meta de Economia
                </h5>
                <span id="metaMesLabel" style="font-size:0.8rem;color:var(--cor-texto-off);"></span>
            </div>

            <div class="text-center py-3" id="loaderMeta">
                <div class="spinner-border spinner-border-sm" style="color:var(--cor-azul);" role="status"></div>
            </div>

            <div id="conteudoMeta" style="display:none;">
                <div class="d-flex justify-content-between align-items-end mb-2 flex-wrap gap-2">
                    <div>
                        <div style="font-size:0.8rem;color:var(--cor-texto-off);">Meta (10% da renda)</div>
                        <div class="titulo" id="metaValorMeta" style="font-size:1.3rem;color:var(--cor-azul);">R$ 0,00</div>
                    </div>
                    <div class="text-end">
                        <div style="font-size:0.8rem;color:var(--cor-texto-off);">Guardado</div>
                        <div class="titulo" id="metaValorEco" style="font-size:1.3rem;">R$ 0,00</div>
                    </div>
                </div>

                <div class="progress orc-progress">
                    <div class="progress-bar" id="metaBar" style="width:0%;transition:width .6s ease;"></div>
                </div>

                <div class="d-flex justify-content-between mt-2" style="font-size:0.78rem;">
                    <span id="metaPct" style="font-weight:600;"></span>
                    <span id="metaInfo" style="color:var(--cor-texto-off);"></span>
                </div>
            </div>
        </div>

        <!-- COFRINHOS -->
        <div class="painel">
            <div class="d-flex align-items-center justify-content-between mb-3 flex-wrap gap-2">
                <h5 class="titulo m-0 fs-secao-titulo">
                    <i class="bi bi-piggy-bank-fill titulo-azul me-1"></i>Cofrinhos
                </h5>
                <button class="btn btn-success btn-sm" id="btnNovoCof">
                    <i class="bi bi-plus-lg"></i> Novo
                </button>
            </div>

            <div class="text-center py-3" id="loaderCof">
                <div class="spinner-border spinner-border-sm" style="color:var(--cor-azul);" role="status"></div>
            </div>

            <div id="emptyCof" style="display:none;" class="text-center py-4">
                <i class="bi bi-piggy-bank" style="font-size:2.5rem;color:var(--cor-borda);"></i>
                <p class="mt-2 mb-0" style="color:var(--cor-texto-off);">Nenhum cofrinho criado ainda.</p>
            </div>

            <div class="row g-3" id="gridCof"></div>
        </div>

    </div>
</div>

<!-- MODAL FONTE DE RENDA -->
<div class="modal fade" id="modalRenda" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-md">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-currency-dollar titulo-azul me-2"></i>
                    <span id="modalRendaTitulo">Nova Fonte de Renda</span>
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">

                <!-- Descrição -->
                <div class="mb-3">
                    <label class="form-label">Descrição</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-pencil-fill"></i></span>
                        <input type="text" class="form-control" id="rendaDescricao" placeholder="Ex: Salário empresa X">
                    </div>
                </div>

                <!-- Tipo -->
                <div class="mb-3">
                    <label class="form-label">Tipo</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-tag-fill"></i></span>
                        <select class="form-select" id="rendaTipo">
                            <option value="Salário">Salário</option>
                            <option value="Benefícios">Benefícios</option>
                            <option value="Freelance">Freelance</option>
                            <option value="Investimentos">Investimentos</option>
                            <option value="Outros">Outros</option>
                        </select>
                    </div>
                </div>

                <!-- Recorrente / Específico -->
                <div class="mb-3">
                    <label class="form-label">Aplicação</label>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-sm flex-fill renda-aplic-btn active" data-aplic="recorrente">
                            <i class="bi bi-arrow-repeat me-1"></i>Todo mês
                        </button>
                        <button type="button" class="btn btn-sm flex-fill renda-aplic-btn" data-aplic="especifico">
                            <i class="bi bi-calendar-event me-1"></i>Mês específico
                        </button>
                    </div>
                </div>

                <!-- Seletor de mês específico (oculto por padrão) -->
                <div class="mb-3 row g-2" id="rendaMesAnoWrap" style="display:none;">
                    <div class="col-8">
                        <label class="form-label">Mês</label>
                        <select class="form-select" id="rendaMes">
                            <option value="1">Janeiro</option><option value="2">Fevereiro</option>
                            <option value="3">Março</option><option value="4">Abril</option>
                            <option value="5">Maio</option><option value="6">Junho</option>
                            <option value="7">Julho</option><option value="8">Agosto</option>
                            <option value="9">Setembro</option><option value="10">Outubro</option>
                            <option value="11">Novembro</option><option value="12">Dezembro</option>
                        </select>
                    </div>
                    <div class="col-4">
                        <label class="form-label">Ano</label>
                        <input type="number" class="form-control" id="rendaAno" min="2020" max="2099" placeholder="2025">
                    </div>
                </div>

                <!-- Valor -->
                <div class="mb-3">
                    <label class="form-label">Valor</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-cash-coin"></i></span>
                        <input type="text" class="form-control" id="rendaValor" placeholder="R$ 0,00">
                    </div>
                </div>

            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Fechar</button>
                <button type="button" class="btn btn-success" id="btnSalvarRenda">
                    Salvar <i class="bi bi-floppy-fill"></i>
                </button>
            </div>

            <input type="hidden" id="rendaId">

        </div>
    </div>
</div>

<!-- MODAL ORÇAMENTO -->
<div class="modal fade" id="modalOrcamento" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-md">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-bar-chart-steps titulo-azul me-2"></i>
                    <span id="orcModalTitulo">Definir Orçamento</span>
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="orcId">
                <div class="mb-3" id="orcCatWrapper">
                    <label class="form-label">Categoria</label>
                    <select class="form-select" id="orcCategoria"></select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Limite mensal</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-cash-coin"></i></span>
                        <input type="text" class="form-control" id="orcLimite" placeholder="R$ 0,00">
                    </div>
                </div>
                <div class="mb-3">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <label class="form-label mb-0" style="font-size:0.9rem;">Meses aplicáveis</label>
                        <button type="button" class="btn btn-link p-0" id="btnTodosMeses"
                                style="font-size:0.8rem;text-decoration:none;color:var(--cor-azul);">
                            Selecionar todos
                        </button>
                    </div>
                    <div id="orcMesesGrid">
                        <button type="button" class="btn btn-sm orc-mes-btn" data-mes="1">Jan</button>
                        <button type="button" class="btn btn-sm orc-mes-btn" data-mes="2">Fev</button>
                        <button type="button" class="btn btn-sm orc-mes-btn" data-mes="3">Mar</button>
                        <button type="button" class="btn btn-sm orc-mes-btn" data-mes="4">Abr</button>
                        <button type="button" class="btn btn-sm orc-mes-btn" data-mes="5">Mai</button>
                        <button type="button" class="btn btn-sm orc-mes-btn" data-mes="6">Jun</button>
                        <button type="button" class="btn btn-sm orc-mes-btn" data-mes="7">Jul</button>
                        <button type="button" class="btn btn-sm orc-mes-btn" data-mes="8">Ago</button>
                        <button type="button" class="btn btn-sm orc-mes-btn" data-mes="9">Set</button>
                        <button type="button" class="btn btn-sm orc-mes-btn" data-mes="10">Out</button>
                        <button type="button" class="btn btn-sm orc-mes-btn" data-mes="11">Nov</button>
                        <button type="button" class="btn btn-sm orc-mes-btn" data-mes="12">Dez</button>
                    </div>
                    <div class="mt-2" style="font-size:0.75rem;color:var(--cor-texto-off);">
                        Nenhum selecionado = todos os meses
                    </div>
                </div>
                <div class="mb-1">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <label class="form-label mb-0" style="font-size:0.9rem;">Anos aplicáveis</label>
                        <button type="button" class="btn btn-link p-0" id="btnTodosAnos"
                                style="font-size:0.8rem;text-decoration:none;color:var(--cor-azul);">
                            Selecionar todos
                        </button>
                    </div>
                    <div id="orcAnosGrid"><!-- preenchido via JS --></div>
                    <div class="mt-2" style="font-size:0.75rem;color:var(--cor-texto-off);">
                        Nenhum selecionado = todos os anos
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Fechar</button>
                <button type="button" class="btn btn-success" id="btnSalvarOrcamento">
                    Salvar <i class="bi bi-floppy-fill"></i>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- MODAL COFRINHO -->
<div class="modal fade" id="modalCofrinho" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-piggy-bank-fill titulo-azul me-2"></i>
                    <span id="cofModalTitulo">Novo Cofrinho</span>
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="cofId">
                <div class="row g-3">
                    <div class="col-12 col-md-8">
                        <label class="form-label">Nome</label>
                        <input type="text" class="form-control" id="cofNome" placeholder="Ex: Viagem Europa">
                    </div>
                    <div class="col-4 col-md-2">
                        <label class="form-label">Cor</label>
                        <input type="color" class="form-control form-control-color w-100" id="cofCor" value="#3B82F6">
                    </div>
                    <div class="col-8 col-md-2 d-flex flex-column justify-content-end">
                        <div id="cofCoverPreview" style="height:38px;border-radius:8px;background:linear-gradient(135deg,#3B82F6,#1D4ED8);transition:background .2s;"></div>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Descrição <span class="text-muted" style="font-size:0.8rem;">(opcional)</span></label>
                        <input type="text" class="form-control" id="cofDescricao" placeholder="Para o quê é?">
                    </div>
                    <div class="col-12">
                        <label class="form-label">URL da imagem de capa <span class="text-muted" style="font-size:0.8rem;">(opcional)</span></label>
                        <input type="url" class="form-control" id="cofImagem" placeholder="https://...">
                        <div id="cofImagemPreview" class="mt-2" style="display:none;">
                            <img id="cofImagemImg" src="" style="width:100%;height:130px;object-fit:cover;border-radius:8px;">
                        </div>
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="form-label">Meta (valor)</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-cash-coin"></i></span>
                            <input type="text" class="form-control" id="cofMeta" placeholder="R$ 0,00">
                        </div>
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="form-label">Prazo <span class="text-muted" style="font-size:0.8rem;">(opcional)</span></label>
                        <input type="date" class="form-control" id="cofDataLimite">
                    </div>
                    <div class="col-12">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="cofTemCDI">
                            <label class="form-check-label" for="cofTemCDI">Possui rendimento (CDI)</label>
                        </div>
                    </div>
                    <div class="col-6" id="cofCDIPctWrap" style="display:none;">
                        <label class="form-label">% do CDI</label>
                        <div class="input-group">
                            <input type="number" class="form-control" id="cofCDIPct" value="100" min="1" max="200" step="0.01">
                            <span class="input-group-text">%</span>
                        </div>
                    </div>
                    <div class="col-6" id="cofTaxaWrap" style="display:none;">
                        <label class="form-label">Taxa CDI atual (% a.a.)</label>
                        <div class="input-group">
                            <input type="number" class="form-control" id="cofCDITaxa" value="13.15" step="0.01">
                            <span class="input-group-text">% a.a.</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Fechar</button>
                <button type="button" class="btn btn-success" id="btnSalvarCofrinho">
                    Salvar <i class="bi bi-floppy-fill"></i>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- MODAL APORTE / RETIRADA -->
<div class="modal fade" id="modalAporte" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header" id="modalAporteHeader">
                <h5 class="modal-title">
                    <i id="modalAporteIcone" class="bi bi-piggy-bank-fill titulo-azul me-2"></i>
                    <span id="modalAporteTituloTxt">Guardar no Cofrinho</span>
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="aporteCofId">
                <input type="hidden" id="aporteAcao" value="aporte">
                <div class="mb-3">
                    <div class="fw-bold" id="aporteNomeCof" style="font-size:1.1rem;"></div>
                </div>
                <div class="mb-3" id="aporteAtualWrap" style="display:none;">
                    <div style="font-size:0.8rem;color:var(--cor-texto-off);">Saldo disponível</div>
                    <div id="aporteAtualVal" class="titulo" style="font-size:1.2rem;color:#22C55E;"></div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Valor</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-cash-coin"></i></span>
                        <input type="text" class="form-control" id="aporteValor" placeholder="R$ 0,00">
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Data</label>
                    <input type="date" class="form-control" id="aporteData">
                </div>
                <div class="mb-0">
                    <label class="form-label">Observação <span class="text-muted" style="font-size:0.8rem;">(opcional)</span></label>
                    <input type="text" class="form-control" id="aporteObs" placeholder="">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Fechar</button>
                <button type="button" class="btn btn-success" id="btnSalvarAporte">
                    Confirmar <i class="bi bi-check-lg"></i>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- MODAL HISTÓRICO -->
<div class="modal fade" id="modalHistorico" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-md">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-clock-history titulo-azul me-2"></i>
                    <span id="histNomeCof"></span>
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-0">
                <div class="text-center py-4" id="loaderHist">
                    <div class="spinner-border spinner-border-sm" style="color:var(--cor-azul);" role="status"></div>
                </div>
                <div id="emptyHist" style="display:none;" class="text-center py-4">
                    <i class="bi bi-inbox" style="font-size:2rem;color:var(--cor-borda);"></i>
                    <p class="mt-2 mb-0" style="color:var(--cor-texto-off);">Nenhum depósito ainda.</p>
                </div>
                <div id="listaHist" style="display:none;">
                    <table class="table table-hover mb-0" style="font-size:0.85rem;">
                        <thead>
                            <tr>
                                <th style="padding:0.6rem 1rem;">Data</th>
                                <th style="padding:0.6rem 1rem;">Valor</th>
                                <th style="padding:0.6rem 1rem;">Observação</th>
                            </tr>
                        </thead>
                        <tbody id="tbodyHist"></tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer justify-content-between">
                <span style="font-size:0.85rem;color:var(--cor-texto-off);">
                    Total guardado: <strong id="histTotal" style="color:var(--cor-azul);">R$ 0,00</strong>
                </span>
                <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Fechar</button>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function () {

    // ─── ÍCONES E CORES POR TIPO ─────────────────────────────────────────
    const tipoConfig = {
        'Salário':       { icon: 'bi-briefcase-fill',    cor: '#3B82F6' },
        'Benefícios':    { icon: 'bi-shield-check',      cor: '#10B981' },
        'Freelance':     { icon: 'bi-laptop-fill',       cor: '#8B5CF6' },
        'Investimentos': { icon: 'bi-graph-up-arrow',    cor: '#F59E0B' },
        'Outros':        { icon: 'bi-wallet2',            cor: '#9CA3AF' },
    };

    var mNomesRenda = ['','Janeiro','Fevereiro','Março','Abril','Maio','Junho',
                       'Julho','Agosto','Setembro','Outubro','Novembro','Dezembro'];

    function getFinMes() { return parseInt($('#mesFin').val()); }
    function getFinAno() { return parseInt($('#finAnoDisplay').text()); }

    // aliases usados pelas funções existentes
    function getRendaMes()  { return getFinMes(); }
    function getRendaAno()  { return getFinAno(); }
    function getAnoAtual()  { return getFinAno(); }

    // ─── CARREGAR RENDAS ─────────────────────────────────────────────────
    function buscaRendas() {
        $('#loaderRendas').show();
        $('#itensRendas').hide();
        $('#emptyRendas').hide();

        $.ajax({
            type: 'POST',
            url: App.ctrl.financas,
            data: { acao: 'buscarRendas', mes: getRendaMes(), ano: getRendaAno() },
            dataType: 'json',
            success: function (data) {
                $('#loaderRendas').hide();

                if (!data || data.length === 0) {
                    $('#emptyRendas').show();
                    _rendaMensal = window._rendaMensal = 0;
                    $('#totalRendaMes').text('R$ 0,00');
                    atualizaMeta();
                    $(document).trigger('rendas:carregadas');
                    return;
                }

                let html = '';
                let totalMes = 0;

                $.each(data, function (i, r) {
                    const cfg     = tipoConfig[r.tipo] || tipoConfig['Outros'];
                    const inativo = r.ativo === 'N';
                    const isRec   = parseInt(r.recorrente) === 1;

                    if (!inativo) totalMes += parseFloat(r.valor);

                    const aplicBadge = isRec
                        ? '<span class="badge" style="background:#3B82F622;color:#3B82F6;"><i class="bi bi-arrow-repeat me-1"></i>Todo mês</span>'
                        : '<span class="badge" style="background:#8B5CF622;color:#8B5CF6;"><i class="bi bi-calendar-event me-1"></i>' + mNomesRenda[parseInt(r.mes)] + '/' + r.ano + '</span>';

                    html += `
                    <div class="renda-item d-flex align-items-center justify-content-between py-3 ${i > 0 ? 'renda-sep' : ''}"
                         style="${inativo ? 'opacity:0.45;' : ''}">
                        <div class="d-flex align-items-center gap-3">
                            <div class="renda-icon-wrap" style="background:${cfg.cor}22;color:${cfg.cor};">
                                <i class="bi ${cfg.icon}"></i>
                            </div>
                            <div>
                                <div class="renda-desc">${r.descricao}</div>
                                <div class="d-flex gap-2 mt-1 flex-wrap">
                                    <span class="badge renda-badge-tipo" style="background:${cfg.cor}22;color:${cfg.cor};">${r.tipo}</span>
                                    ${aplicBadge}
                                    ${inativo ? '<span class="badge bg-secondary">Inativo</span>' : ''}
                                </div>
                            </div>
                        </div>
                        <div class="d-flex align-items-center gap-2 flex-wrap justify-content-end">
                            <span class="renda-valor ${inativo ? '' : 'text-success'}">
                                + R$ ${formatarBR(r.valor)}
                            </span>
                            <div class="d-flex gap-1">
                                <button class="btn btn-sm btn-outline-secondary btnToggleRenda"
                                        data-id="${r.id}" title="${inativo ? 'Ativar' : 'Inativar'}">
                                    <i class="bi ${inativo ? 'bi-play-fill' : 'bi-pause-fill'}"></i>
                                </button>
                                <button class="btn btn-sm btn-outline-warning btnEditaRenda"
                                        data-id="${r.id}"
                                        data-descricao="${r.descricao}"
                                        data-tipo="${r.tipo}"
                                        data-recorrente="${r.recorrente}"
                                        data-mes="${r.mes || ''}"
                                        data-ano="${r.ano || ''}"
                                        data-valor="${r.valor}" title="Editar">
                                    <i class="bi bi-pencil-fill"></i>
                                </button>
                                <button class="btn btn-sm btn-outline-danger btnRemoveRenda"
                                        data-id="${r.id}" title="Remover">
                                    <i class="bi bi-trash-fill"></i>
                                </button>
                            </div>
                        </div>
                    </div>`;
                });

                _rendaMensal = window._rendaMensal = totalMes;
                $('#itensRendas').html(html).show();
                $('#totalRendaMes').text('R$ ' + formatarBR(totalMes.toFixed(2)));
                atualizaMeta();
                $(document).trigger('rendas:carregadas');
            },
            error: function () { toastr.error('Erro ao buscar fontes de renda!'); }
        });
    }

    function resetModalRenda() {
        $('#rendaId').val('');
        $('#rendaDescricao').val('');
        $('#rendaTipo').val('Salário');
        $('#rendaValor').val('');
        $('.renda-aplic-btn[data-aplic="recorrente"]').addClass('active');
        $('.renda-aplic-btn[data-aplic="especifico"]').removeClass('active');
        $('#rendaMesAnoWrap').hide();
        $('#rendaMes').val(getFinMes());
        $('#rendaAno').val(getFinAno());
    }

    buscaRendas();

    // ─── TOGGLE RECORRENTE / ESPECÍFICO ──────────────────────────────────
    $(document).on('click', '.renda-aplic-btn', function () {
        $('.renda-aplic-btn').removeClass('active');
        $(this).addClass('active');
        var especifico = $(this).data('aplic') === 'especifico';
        $('#rendaMesAnoWrap').toggle(especifico);
    });

    // ─── ABRIR MODAL (CRIAR) ──────────────────────────────────────────────
    $('#btnAdicionarRenda').click(function () {
        $('#modalRendaTitulo').text('Nova Fonte de Renda');
        resetModalRenda();
        $('#modalRenda').modal('show');
    });

    // ─── ABRIR MODAL (EDITAR) ─────────────────────────────────────────────
    $(document).on('click', '.btnEditaRenda', function () {
        const $btn    = $(this);
        const isRec   = parseInt($btn.data('recorrente')) === 1;
        $('#modalRendaTitulo').text('Editar Fonte de Renda');
        resetModalRenda();
        $('#rendaId').val($btn.data('id'));
        $('#rendaDescricao').val($btn.data('descricao'));
        $('#rendaTipo').val($btn.data('tipo'));
        $('#rendaValor').val($btn.data('valor'));

        if (!isRec) {
            $('.renda-aplic-btn[data-aplic="recorrente"]').removeClass('active');
            $('.renda-aplic-btn[data-aplic="especifico"]').addClass('active');
            $('#rendaMesAnoWrap').show();
            $('#rendaMes').val($btn.data('mes'));
            $('#rendaAno').val($btn.data('ano'));
        }
        $('#modalRenda').modal('show');
    });

    // ─── SALVAR (CRIAR OU EDITAR) ─────────────────────────────────────────
    $('#btnSalvarRenda').click(function () {
        const id        = $('#rendaId').val();
        const descricao = $('#rendaDescricao').val().trim();
        const tipo      = $('#rendaTipo').val();
        const valor     = $('#rendaValor').val();
        const especifico = $('.renda-aplic-btn[data-aplic="especifico"]').hasClass('active');

        if (!descricao || !valor) { toastr.warning('Preencha descrição e valor.'); return; }

        const mes = especifico ? $('#rendaMes').val() : '';
        const ano = especifico ? $('#rendaAno').val() : '';

        if (especifico && (!mes || !ano)) { toastr.warning('Selecione o mês e ano.'); return; }

        const acao = id ? 'editarRenda' : 'adicionarRenda';

        $.ajax({
            type: 'POST',
            url: App.ctrl.financas,
            data: { acao, id, descricao, tipo, recorrencia: especifico ? 'Único' : 'Mensal', valor, mes, ano },
            dataType: 'json',
            success: function () {
                toastr.success(id ? 'Renda atualizada!' : 'Renda adicionada!');
                $('#modalRenda').modal('hide');
                buscaRendas();
            },
            error: function () { toastr.error('Erro ao salvar renda!'); }
        });
    });

    // ─── TOGGLE ATIVO / INATIVO ───────────────────────────────────────────
    $(document).on('click', '.btnToggleRenda', function () {
        const id = $(this).data('id');
        $.ajax({
            type: 'POST',
            url: '../controllers/FinancasController.php',
            data: { acao: 'toggleAtivo', id },
            dataType: 'json',
            success: function () { buscaRendas(); },
            error: function () { toastr.error('Erro ao alterar status!'); }
        });
    });

    // ─── REMOVER ──────────────────────────────────────────────────────────
    $(document).on('click', '.btnRemoveRenda', function () {
        const id = $(this).data('id');
        Swal.fire({
            title: 'Remover fonte de renda?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#EF4444',
            cancelButtonColor: '#6B7280',
            confirmButtonText: 'Sim, remover',
            cancelButtonText: 'Cancelar'
        }).then(function (result) {
            if (result.isConfirmed) {
                $.ajax({
                    type: 'POST',
                    url: '../controllers/FinancasController.php',
                    data: { acao: 'removerRenda', id },
                    dataType: 'json',
                    success: function () {
                        toastr.success('Renda removida!');
                        buscaRendas();
                    },
                    error: function () { toastr.error('Erro ao remover!'); }
                });
            }
        });
    });

    // ─── META DE ECONOMIA ────────────────────────────────────────────────

    var _rendaMensal = 0; // atualizado em buscaRendas()

    function atualizaMeta() {
        var mes = getFinMes();
        var ano = getFinAno();
        $('#metaMesLabel').text(mNomesRenda[mes] + ' / ' + ano);
        $('#loaderMeta').show();
        $('#conteudoMeta').hide();

        $.ajax({
            type: 'POST',
            url: App.ctrl.cofrinho,
            data: { acao: 'totalAportesMes', mes: mes, ano: ano },
            dataType: 'json',
            success: function (totalAportes) {
                $('#loaderMeta').hide();
                var renda   = _rendaMensal;
                var meta    = renda * 0.10;
                var eco     = parseFloat(totalAportes) || 0;
                var pct     = meta > 0 ? Math.min((eco / meta) * 100, 100) : 0;
                var pctReal = meta > 0 ? (eco / meta) * 100 : 0;
                var cor     = pctReal < 50 ? '#EF4444' : pctReal < 100 ? '#F59E0B' : '#22C55E';

                $('#metaValorMeta').text('R$ ' + formatarBR(meta));
                $('#metaValorEco').text('R$ ' + formatarBR(eco)).css('color', cor);
                $('#metaBar').css({ width: Math.max(0, pct).toFixed(1) + '%', background: cor });
                $('#metaPct').text(pctReal.toFixed(0) + '%').css('color', cor);

                var infoTxt = pctReal >= 100
                    ? '<i class="bi bi-check-circle-fill me-1" style="color:#22C55E;"></i>Meta atingida!'
                    : (eco === 0
                        ? '<i class="bi bi-piggy-bank me-1"></i>Adicione aportes nos cofrinhos'
                        : 'Faltam R$ ' + formatarBR(meta - eco) + ' para a meta');
                $('#metaInfo').html(infoTxt);

                $('#conteudoMeta').show();
            },
            error: function () {
                $('#loaderMeta').hide();
                $('#conteudoMeta').show();
            }
        });
    }

    // ─── ORÇAMENTO POR CATEGORIA ─────────────────────────────────────────

    var mAbrev = ['','Jan','Fev','Mar','Abr','Mai','Jun','Jul','Ago','Set','Out','Nov','Dez'];

    function mesesLabel(meses) {
        if (!meses) return 'Todos os meses';
        var nums = meses.split(',').map(Number).filter(function(n){ return n >= 1 && n <= 12; });
        if (!nums.length) return 'Todos os meses';
        return nums.map(function(n){ return mAbrev[n]; }).join(', ');
    }

    function anosLabel(anos) {
        if (!anos) return 'Todos os anos';
        return anos.split(',').filter(Boolean).join(', ');
    }

    function buscaOrcamentos() {
        $('#loaderOrcamento').show();
        $('#itensOrcamento').hide();
        $('#emptyOrcamento').hide();

        $.ajax({
            type: 'POST',
            url: App.ctrl.orcamento,
            data: { acao: 'buscar', mes: getFinMes(), ano: getFinAno() },
            dataType: 'json',
            success: function (data) {
                $('#loaderOrcamento').hide();

                if (!data || data.length === 0) {
                    $('#emptyOrcamento').show();
                    return;
                }

                var html = '';
                $.each(data, function (i, o) {
                    var limite  = parseFloat(o.valor_limite);
                    var gasto   = parseFloat(o.gasto_mes);
                    var pct     = limite > 0 ? Math.min((gasto / limite) * 100, 100) : 0;
                    var excedeu = gasto > limite;
                    var cor     = pct < 70 ? '#22C55E' : pct < 90 ? '#F59E0B' : '#EF4444';
                    var catCor  = o.cor || '#6B7280';
                    var icone   = o.icone ? '<span class="me-1">' + o.icone + '</span>' : '';
                    var restante = limite - gasto;
                    var meses    = o.meses || '';
                    var anos     = o.anos  || '';

                    var infoCor  = excedeu ? '#EF4444' : 'var(--cor-texto-off)';
                    var infoTxt  = excedeu
                        ? '<i class="bi bi-exclamation-triangle-fill me-1"></i>Excedeu R$ ' + formatarBR(Math.abs(restante))
                        : 'Restam R$ ' + formatarBR(restante);

                    html += '<div class="orc-item ' + (i > 0 ? 'orc-sep' : '') + '">' +
                        '<div class="d-flex justify-content-between align-items-center mb-2">' +
                            '<span style="background:' + catCor + '22;color:' + catCor + ';border:1px solid ' + catCor + '55;' +
                                'font-size:0.82rem;font-weight:500;padding:3px 10px;border-radius:20px;white-space:nowrap;">' +
                                icone + o.nome +
                            '</span>' +
                            '<div class="d-flex align-items-center gap-2">' +
                                '<span style="font-size:0.85rem;color:var(--cor-texto-off);">' +
                                    'R$ ' + formatarBR(gasto) + ' <span style="color:var(--cor-borda);">/</span> R$ ' + formatarBR(limite) +
                                '</span>' +
                                '<button class="btn btn-sm btn-outline-warning btnEditaOrc p-1 lh-1" ' +
                                    'data-id="' + o.orcamento_id + '" data-cat="' + o.categoria_id + '" ' +
                                    'data-limite="' + o.valor_limite + '" data-meses="' + meses + '" data-anos="' + anos + '" title="Editar">' +
                                    '<i class="bi bi-pencil-fill" style="font-size:0.7rem;"></i>' +
                                '</button>' +
                                '<button class="btn btn-sm btn-outline-danger btnRemoveOrc p-1 lh-1" ' +
                                    'data-id="' + o.orcamento_id + '" title="Remover">' +
                                    '<i class="bi bi-trash-fill" style="font-size:0.7rem;"></i>' +
                                '</button>' +
                            '</div>' +
                        '</div>' +
                        '<div class="progress orc-progress">' +
                            '<div class="progress-bar" style="width:' + pct.toFixed(1) + '%;background:' + cor + ';transition:width .6s ease;"></div>' +
                        '</div>' +
                        '<div class="d-flex justify-content-between mt-1" style="font-size:0.75rem;">' +
                            '<span style="color:' + cor + ';font-weight:500;">' + pct.toFixed(0) + '%</span>' +
                            '<span style="color:' + infoCor + ';">' + infoTxt + '</span>' +
                        '</div>' +
                        '<div class="mt-1" style="font-size:0.72rem;color:var(--cor-texto-off);">' +
                            '<i class="bi bi-calendar3 me-1"></i>' + mesesLabel(meses) +
                            ' &nbsp;·&nbsp; <i class="bi bi-calendar-range me-1"></i>' + anosLabel(anos) +
                        '</div>' +
                    '</div>';
                });

                $('#itensOrcamento').html(html).show();
            },
            error: function () { toastr.error('Erro ao buscar orçamentos!'); }
        });
    }

    function gerarBotoesAnos() {
        var base = getAnoAtual();
        var html = '';
        for (var y = base; y <= base + 2; y++) {
            html += '<button type="button" class="btn btn-sm orc-ano-btn" data-ano="' + y + '">' + y + '</button>';
        }
        $('#orcAnosGrid').html(html);
    }

    function abrirModalOrcamento(id, catId, limite, meses, anos) {
        $('#orcId').val(id || '');
        $('#orcLimite').val(limite || '');
        $('#orcModalTitulo').text(id ? 'Editar Orçamento' : 'Definir Orçamento');

        // Pre-seleciona meses
        $('.orc-mes-btn').removeClass('active');
        if (meses) {
            meses.split(',').forEach(function(m) {
                $('.orc-mes-btn[data-mes="' + parseInt(m) + '"]').addClass('active');
            });
        }
        atualizaBtnTodosMeses();

        // Gera e pre-seleciona anos
        gerarBotoesAnos();
        if (anos) {
            anos.split(',').forEach(function(a) {
                $('.orc-ano-btn[data-ano="' + parseInt(a) + '"]').addClass('active');
            });
        }
        atualizaBtnTodosAnos();

        // Carrega categorias no select
        $.ajax({
            type: 'POST',
            url: App.ctrl.categoria,
            data: { acao: 'busca' },
            dataType: 'json',
            success: function (cats) {
                var opts = '';
                $.each(cats, function (_, c) {
                    var sel = String(c.id) === String(catId) ? ' selected' : '';
                    opts += '<option value="' + c.id + '"' + sel + '>' +
                            (c.icone ? c.icone + ' ' : '') + c.nome + '</option>';
                });
                $('#orcCategoria').html(opts);
                $('#orcCategoria').prop('disabled', !!id);
                $('#modalOrcamento').modal('show');
            }
        });
    }

    function atualizaBtnTodosMeses() {
        var todosOk = $('.orc-mes-btn.active').length === $('.orc-mes-btn').length;
        $('#btnTodosMeses').text(todosOk ? 'Desmarcar todos' : 'Selecionar todos');
    }

    function atualizaBtnTodosAnos() {
        var todosOk = $('.orc-ano-btn').length > 0 && $('.orc-ano-btn.active').length === $('.orc-ano-btn').length;
        $('#btnTodosAnos').text(todosOk ? 'Desmarcar todos' : 'Selecionar todos');
    }

    buscaOrcamentos();

    // ─── NAVEGAÇÃO GLOBAL ────────────────────────────────────────────────
    function atualizarTudo() { buscaRendas(); buscaOrcamentos(); }

    $('#mesFin').change(function () { atualizarTudo(); });
    $('#finLeft').click(function () {
        var v = getFinMes();
        if (v > 1) { $('#mesFin').val(v - 1).trigger('change'); }
        else { $('#mesFin').val(12); $('#finAnoDisplay').text(getFinAno() - 1); atualizarTudo(); }
    });
    $('#finRight').click(function () {
        var v = getFinMes();
        if (v < 12) { $('#mesFin').val(v + 1).trigger('change'); }
        else { $('#mesFin').val(1); $('#finAnoDisplay').text(getFinAno() + 1); atualizarTudo(); }
    });
    $('#finYearLeft').click(function ()  { $('#finAnoDisplay').text(getFinAno() - 1); atualizarTudo(); });
    $('#finYearRight').click(function () { $('#finAnoDisplay').text(getFinAno() + 1); atualizarTudo(); });


    $('#btnAdicionarOrcamento, #btnAdicionarOrcamentoEmpty').click(function () {
        abrirModalOrcamento(null, null, null, '', '');
    });

    $(document).on('click', '.btnEditaOrc', function () {
        abrirModalOrcamento(
            $(this).data('id'), $(this).data('cat'), $(this).data('limite'),
            $(this).data('meses') || '', $(this).data('anos') || ''
        );
    });

    // ─── TOGGLE MESES ────────────────────────────────────────────────────
    $(document).on('click', '.orc-mes-btn', function () {
        $(this).toggleClass('active');
        atualizaBtnTodosMeses();
    });

    $('#btnTodosMeses').click(function () {
        var todosAtivos = $('.orc-mes-btn.active').length === $('.orc-mes-btn').length;
        $('.orc-mes-btn').toggleClass('active', !todosAtivos);
        atualizaBtnTodosMeses();
    });

    // ─── TOGGLE ANOS ─────────────────────────────────────────────────────
    $(document).on('click', '.orc-ano-btn', function () {
        $(this).toggleClass('active');
        atualizaBtnTodosAnos();
    });

    $('#btnTodosAnos').click(function () {
        var todosAtivos = $('.orc-ano-btn.active').length === $('.orc-ano-btn').length;
        $('.orc-ano-btn').toggleClass('active', !todosAtivos);
        atualizaBtnTodosAnos();
    });

    $(document).on('click', '.btnRemoveOrc', function () {
        var id = $(this).data('id');
        Swal.fire({
            title: 'Remover orçamento?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#EF4444',
            cancelButtonColor: '#6B7280',
            confirmButtonText: 'Sim, remover',
            cancelButtonText: 'Cancelar'
        }).then(function (r) {
            if (r.isConfirmed) {
                $.ajax({
                    type: 'POST', url: App.ctrl.orcamento,
                    data: { acao: 'remover', id: id }, dataType: 'json',
                    success: function () { toastr.success('Orçamento removido!'); buscaOrcamentos(); },
                    error:   function () { toastr.error('Erro ao remover!'); }
                });
            }
        });
    });

    $('#btnSalvarOrcamento').click(function () {
        var cat    = $('#orcCategoria').val();
        var limite = $('#orcLimite').val();
        if (!cat || !limite) { toastr.warning('Preencha todos os campos.'); return; }

        var mesesSel = [];
        $('.orc-mes-btn.active').each(function () { mesesSel.push($(this).data('mes')); });
        var mesesStr = (mesesSel.length === 0 || mesesSel.length === 12) ? '' : mesesSel.join(',');

        var anosSel = [];
        $('.orc-ano-btn.active').each(function () { anosSel.push($(this).data('ano')); });
        var anosStr = (anosSel.length === 0 || anosSel.length === $('.orc-ano-btn').length) ? '' : anosSel.join(',');

        $.ajax({
            type: 'POST', url: App.ctrl.orcamento,
            data: { acao: 'salvar', categoria_id: cat, valor_limite: limite, meses: mesesStr, anos: anosStr, id: $('#orcId').val() },
            dataType: 'json',
            success: function (ok) {
                if (ok) {
                    toastr.success('Orçamento salvo!');
                    $('#modalOrcamento').modal('hide');
                    buscaOrcamentos();
                } else { toastr.error('Erro ao salvar!'); }
            },
            error: function () { toastr.error('Erro ao salvar!'); }
        });
    });

    new Cleave('#orcLimite', {
        numeral: true, numeralThousandsGroupStyle: 'thousand',
        prefix: 'R$ ', noImmediatePrefix: true,
        delimiter: '.', decimal: ',', numeralDecimalMark: ',',
        stripLeadingZeroes: true
    });

    // ─── UTILITÁRIO ───────────────────────────────────────────────────────
    function formatarBR(n) {
        return parseFloat(n).toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    // Máscara monetária no campo do modal
    new Cleave('#rendaValor', {
        numeral: true,
        numeralThousandsGroupStyle: 'thousand',
        prefix: 'R$ ',
        noImmediatePrefix: true,
        delimiter: '.',
        decimal: ',',
        numeralDecimalMark: ',',
        stripLeadingZeroes: true
    });

});
</script>

<style>
.renda-sep {
    border-top: 1px solid var(--cor-borda);
}

.renda-icon-wrap {
    width: 42px;
    height: 42px;
    border-radius: var(--radius-md);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.2rem;
    flex-shrink: 0;
}

.renda-desc {
    color: var(--cor-texto);
    font-weight: 500;
    font-size: 1rem;
}

.renda-valor {
    font-weight: 600;
    font-size: 1.05rem;
    white-space: nowrap;
}

.renda-badge-tipo,
.renda-badge-rec {
    font-size: 0.8rem;
    font-weight: 500;
    padding: 3px 9px;
}

@media (max-width: 576px) {
    .renda-item {
        flex-direction: column;
        align-items: flex-start;
        gap: 0.75rem;
    }
    .renda-item > div:last-child {
        width: 100%;
        justify-content: space-between;
    }
}

/* ── Renda: botões de aplicação ── */
.renda-aplic-btn {
    background: var(--cor-input);
    color: var(--cor-texto-off);
    border: 1px solid var(--cor-borda);
    transition: background .15s, color .15s;
}
.renda-aplic-btn.active {
    background: var(--cor-azul);
    color: #fff;
    border-color: var(--cor-azul);
}

/* ── Orçamento ── */
.orc-sep  { border-top: 1px solid var(--cor-borda); }
.orc-item { padding: 0.85rem 0; }
.orc-progress {
    height: 8px;
    background: var(--cor-input);
    border-radius: 20px;
    overflow: hidden;
}
.orc-progress .progress-bar { border-radius: 20px; }

#orcMesesGrid {
    display: grid;
    grid-template-columns: repeat(6, 1fr);
    gap: 6px;
}
#orcAnosGrid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 6px;
}
.orc-mes-btn,
.orc-ano-btn {
    background: var(--cor-input);
    color: var(--cor-texto-off);
    border: 1px solid var(--cor-borda);
    padding: 5px 0;
    font-size: 0.78rem;
    border-radius: var(--radius-sm, 6px);
    transition: background .15s, color .15s, border-color .15s;
}
.orc-mes-btn:hover,
.orc-ano-btn:hover {
    background: var(--cor-azul);
    color: #fff;
    border-color: var(--cor-azul);
    opacity: 0.85;
}
.orc-mes-btn.active,
.orc-ano-btn.active {
    background: var(--cor-azul);
    color: #fff;
    border-color: var(--cor-azul);
    opacity: 1;
}
</style>

<script>
window._rendaMensal = window._rendaMensal || 0;
window._cofData     = [];

$(document).ready(function () {

    var mAbrevCof = ['','Jan','Fev','Mar','Abr','Mai','Jun','Jul','Ago','Set','Out','Nov','Dez'];
    var cleaveCofMeta  = null;
    var cleaveAporte   = null;

    // ─── CÁLCULOS ────────────────────────────────────────────────

    function calcMesesRestantes(dataLimite) {
        if (!dataLimite) return null;
        var ym    = dataLimite.substring(0, 7);
        var year  = parseInt(ym.substring(0, 4));
        var month = parseInt(ym.substring(5, 7));
        var hoje  = new Date();
        var diff  = (year - hoje.getFullYear()) * 12 + (month - (hoje.getMonth() + 1));
        return diff > 0 ? diff : 0;
    }

    function cdiRendimentoMensal(valorAtual, taxaAnual, pctCdi) {
        if (!valorAtual || valorAtual <= 0) return 0;
        var effRate   = (taxaAnual * pctCdi / 100) / 100;
        var dailyRate = Math.pow(1 + effRate, 1 / 252) - 1;
        return valorAtual * (Math.pow(1 + dailyRate, 22) - 1);
    }

    function pmtComCDI(meta, atual, taxaAnual, pctCdi, n) {
        if (n <= 0) return 0;
        var effRate    = (taxaAnual * pctCdi / 100) / 100;
        var r          = Math.pow(1 + effRate, 1 / 12) - 1;
        if (r <= 0)    return Math.max(0, (meta - atual) / n);
        var futuroAtual = atual * Math.pow(1 + r, n);
        if (futuroAtual >= meta) return 0;
        var fv         = meta - futuroAtual;
        return fv * r / (Math.pow(1 + r, n) - 1);
    }

    // ─── BUSCAR ──────────────────────────────────────────────────

    function buscaCofrinhos() {
        $('#loaderCof').show();
        $('#gridCof').hide();
        $('#emptyCof').hide();
        $.ajax({
            type: 'POST', url: App.ctrl.cofrinho,
            data: { acao: 'listar' }, dataType: 'json',
            success: function (data) {
                $('#loaderCof').hide();
                window._cofData = data || [];
                if (!window._cofData.length) { $('#emptyCof').show(); return; }
                renderCofrinhos(window._cofData);
            },
            error: function () { toastr.error('Erro ao buscar cofrinhos!'); }
        });
    }

    $(document).on('rendas:carregadas', function () {
        if (window._cofData && window._cofData.length) {
            renderCofrinhos(window._cofData);
        }
    });

    // ─── RENDERIZAR CARDS ─────────────────────────────────────────

    function renderCofrinhos(data) {
        var renda10 = (window._rendaMensal || 0) * 0.10;
        var html    = '';

        $.each(data, function (_, c) {
            var meta   = parseFloat(c.meta_valor)  || 0;
            var atual  = parseFloat(c.valor_atual) || 0;
            var pct    = meta > 0 ? Math.min((atual / meta) * 100, 100) : 0;
            var cor    = c.cor || '#3B82F6';
            var falta  = Math.max(0, meta - atual);
            var mesesR = calcMesesRestantes(c.data_limite);
            var concluido = pct >= 100;
            var corBar = pct < 40 ? '#3B82F6' : pct < 70 ? '#F59E0B' : '#22C55E';

            // Cover
            var coverHtml = c.imagem_url
                ? '<img src="' + escHtml(c.imagem_url) + '" class="cof-cover-img" alt="">'
                : '<div class="cof-cover-grad" style="background:linear-gradient(135deg,' + cor + ',' + shadeColor(cor, -35) + ');"></div>';

            // Prazo
            var prazoHtml = '';
            if (c.data_limite) {
                var ym  = c.data_limite.substring(0, 7);
                var mes = parseInt(ym.substring(5, 7));
                var ano = ym.substring(0, 4);
                prazoHtml = '<div class="cof-info-row">' +
                    '<i class="bi bi-calendar-event" style="color:' + cor + ';"></i>' +
                    '<span>Prazo: <strong>' + mAbrevCof[mes] + '/' + ano + '</strong>' +
                    (mesesR !== null ? ' <small class="text-muted">(' + mesesR + ' ' + (mesesR === 1 ? 'mês' : 'meses') + ')</small>' : '') +
                    '</span></div>';
            } else {
                prazoHtml = '<div class="cof-info-row"><i class="bi bi-infinity" style="color:var(--cor-texto-off);"></i>' +
                    '<span style="color:var(--cor-texto-off);">Sem prazo definido</span></div>';
            }

            // Quanto guardar por mês
            var needHtml = '';
            if (concluido) {
                needHtml = '<div class="cof-info-row"><i class="bi bi-check-circle-fill" style="color:#22C55E;"></i>' +
                    '<span style="color:#22C55E;font-weight:600;">Meta atingida!</span></div>';
            } else if (c.data_limite && mesesR !== null && mesesR > 0) {
                var nec = parseInt(c.tem_cdi) && parseFloat(c.cdi_taxa_anual) > 0
                    ? pmtComCDI(meta, atual, parseFloat(c.cdi_taxa_anual), parseFloat(c.cdi_percentual), mesesR)
                    : falta / mesesR;
                needHtml = '<div class="cof-info-row"><i class="bi bi-piggy-bank" style="color:#10B981;"></i>' +
                    '<span>Guardar: <strong>R$ ' + fmtBR(nec) + '/mês</strong></span></div>';
            }

            // CDI rendimento
            var cdiHtml = '';
            if (parseInt(c.tem_cdi) && parseFloat(c.cdi_taxa_anual) > 0 && atual > 0) {
                var rend = cdiRendimentoMensal(atual, parseFloat(c.cdi_taxa_anual), parseFloat(c.cdi_percentual));
                cdiHtml = '<div class="cof-info-row"><i class="bi bi-graph-up-arrow" style="color:#F59E0B;"></i>' +
                    '<span>CDI ' + parseFloat(c.cdi_percentual).toFixed(0) + '% &nbsp;·&nbsp; ~R$ ' + fmtBR(rend) + '/mês</span></div>';
            }

            // Insight 10% renda (só quando sem prazo ou quando prazo não consegue calcular nec)
            var insightHtml = '';
            if (renda10 > 0 && falta > 0 && !c.data_limite) {
                var mSemCDI = Math.ceil(falta / renda10);
                insightHtml = '<div class="cof-info-row"><i class="bi bi-lightbulb" style="color:#F59E0B;"></i>' +
                    '<small style="color:var(--cor-texto-off);">Com 10% da renda (R$ ' + fmtBR(renda10) + '/mês): ~' +
                    mSemCDI + ' ' + (mSemCDI === 1 ? 'mês' : 'meses') + '</small></div>';
            }

            var acaoBtns = (concluido ? '' :
                    '<button class="btn btn-success btn-sm flex-fill btnAporte" data-id="' + c.id + '" data-nome="' + escHtml(c.nome) + '">' +
                        '<i class="bi bi-piggy-bank me-1"></i>Guardar' +
                    '</button>'
                ) +
                (atual > 0 ?
                    '<button class="btn btn-outline-danger btn-sm flex-fill btnRetirar" data-id="' + c.id + '" data-nome="' + escHtml(c.nome) + '" data-atual="' + atual + '">' +
                        '<i class="bi bi-box-arrow-up me-1"></i>Retirar' +
                    '</button>'
                : '');

            html += '<div class="col-12 col-sm-6 col-xl-4">' +
                '<div class="card cof-card h-100">' +
                    coverHtml +
                    '<div class="card-body">' +
                        '<h5 class="card-title mb-1">' + escHtml(c.nome) + '</h5>' +
                        (c.descricao ? '<p class="card-text mb-2" style="font-size:0.85rem;color:var(--cor-texto-off);">' + escHtml(c.descricao) + '</p>' : '') +
                        '<div class="d-flex justify-content-between mb-1" style="font-size:0.82rem;">' +
                            '<span style="font-weight:600;color:' + corBar + ';">' + pct.toFixed(0) + '%</span>' +
                            '<span style="color:var(--cor-texto-off);">R$ ' + fmtBR(atual) + ' / R$ ' + fmtBR(meta) + '</span>' +
                        '</div>' +
                        '<div class="progress cof-progress mb-3">' +
                            '<div class="progress-bar" style="width:' + pct.toFixed(1) + '%;background:' + corBar + ';border-radius:20px;transition:width .6s ease;"></div>' +
                        '</div>' +
                        '<div class="cof-info">' + prazoHtml + needHtml + cdiHtml + insightHtml + '</div>' +
                    '</div>' +
                    '<div class="card-footer p-0">' +
                        (acaoBtns ?
                            '<div class="d-flex gap-2 p-2 pb-1">' + acaoBtns + '</div>'
                        : '') +
                        '<div class="d-flex gap-2 justify-content-end p-2 pt-1">' +
                            '<button class="btn btn-outline-secondary btn-sm btnHistCof flex-fill" data-id="' + c.id + '" data-nome="' + escHtml(c.nome) + '" title="Histórico">' +
                                '<i class="bi bi-clock-history me-1"></i>Histórico' +
                            '</button>' +
                            '<button class="btn btn-outline-warning btn-sm btnEditaCof px-3" ' +
                                'data-id="' + c.id + '" data-nome="' + escHtml(c.nome) + '" ' +
                                'data-descricao="' + escHtml(c.descricao || '') + '" ' +
                                'data-meta="' + meta + '" ' +
                                'data-imagem="' + escHtml(c.imagem_url || '') + '" ' +
                                'data-datalimite="' + (c.data_limite ? c.data_limite.substring(0, 10) : '') + '" ' +
                                'data-temcdi="' + (c.tem_cdi || 0) + '" ' +
                                'data-cdipct="' + (c.cdi_percentual || 100) + '" ' +
                                'data-cditaxa="' + (c.cdi_taxa_anual || 13.15) + '" ' +
                                'data-cor="' + cor + '" title="Editar">' +
                                '<i class="bi bi-pencil-fill"></i>' +
                            '</button>' +
                            '<button class="btn btn-outline-danger btn-sm btnRemoveCof px-3" data-id="' + c.id + '" title="Remover">' +
                                '<i class="bi bi-trash-fill"></i>' +
                            '</button>' +
                        '</div>' +
                    '</div>' +
                '</div>' +
            '</div>';
        });

        $('#gridCof').html(html).show();
    }

    buscaCofrinhos();

    // ─── MODAL COFRINHO ──────────────────────────────────────────

    function resetModalCof() {
        $('#cofId').val('');
        $('#cofNome').val('');
        $('#cofDescricao').val('');
        $('#cofImagem').val('');
        $('#cofImagemPreview').hide();
        $('#cofDataLimite').val('');
        $('#cofCor').val('#3B82F6');
        $('#cofCoverPreview').css('background', 'linear-gradient(135deg,#3B82F6,#1D4ED8)');
        $('#cofTemCDI').prop('checked', false);
        $('#cofCDIPctWrap, #cofTaxaWrap').hide();
        $('#cofCDIPct').val('100');
        $('#cofCDITaxa').val('13.15');
        if (cleaveCofMeta) cleaveCofMeta.setRawValue(''); else $('#cofMeta').val('');
    }

    $('#btnNovoCof').click(function () {
        resetModalCof();
        $('#cofModalTitulo').text('Novo Cofrinho');
        $('#modalCofrinho').modal('show');
    });

    $(document).on('click', '.btnEditaCof', function () {
        var $b = $(this);
        resetModalCof();
        $('#cofModalTitulo').text('Editar Cofrinho');
        $('#cofId').val($b.data('id'));
        $('#cofNome').val($b.data('nome'));
        $('#cofDescricao').val($b.data('descricao'));
        $('#cofImagem').val($b.data('imagem'));
        $('#cofDataLimite').val($b.data('datalimite'));
        var cor = $b.data('cor') || '#3B82F6';
        $('#cofCor').val(cor);
        $('#cofCoverPreview').css('background', 'linear-gradient(135deg,' + cor + ',' + shadeColor(cor, -35) + ')');

        if ($b.data('imagem')) {
            $('#cofImagemImg').attr('src', $b.data('imagem'));
            $('#cofImagemPreview').show();
        }
        if (parseInt($b.data('temcdi'))) {
            $('#cofTemCDI').prop('checked', true);
            $('#cofCDIPctWrap, #cofTaxaWrap').show();
            $('#cofCDIPct').val($b.data('cdipct'));
            $('#cofCDITaxa').val($b.data('cditaxa'));
        }
        if (cleaveCofMeta) cleaveCofMeta.setRawValue($b.data('meta'));
        else $('#cofMeta').val($b.data('meta'));

        $('#modalCofrinho').modal('show');
    });

    $('#cofImagem').on('input', function () {
        var url = $(this).val().trim();
        if (url) { $('#cofImagemImg').attr('src', url); $('#cofImagemPreview').show(); }
        else { $('#cofImagemPreview').hide(); }
    });

    $('#cofCor').on('input', function () {
        var cor = $(this).val();
        $('#cofCoverPreview').css('background', 'linear-gradient(135deg,' + cor + ',' + shadeColor(cor, -35) + ')');
    });

    $('#cofTemCDI').change(function () {
        $('#cofCDIPctWrap, #cofTaxaWrap').toggle(this.checked);
    });

    $('#btnSalvarCofrinho').click(function () {
        var nome = $('#cofNome').val().trim();
        var meta = $('#cofMeta').val();
        if (!nome || !meta) { toastr.warning('Nome e meta são obrigatórios.'); return; }

        $.ajax({
            type: 'POST', url: App.ctrl.cofrinho,
            data: {
                acao:           'salvar',
                id:             $('#cofId').val(),
                nome:           nome,
                descricao:      $('#cofDescricao').val().trim(),
                imagem_url:     $('#cofImagem').val().trim(),
                meta_valor:     meta,
                data_limite:    $('#cofDataLimite').val(),
                tem_cdi:        $('#cofTemCDI').is(':checked') ? 1 : 0,
                cdi_percentual: $('#cofCDIPct').val(),
                cdi_taxa_anual: $('#cofCDITaxa').val(),
                cor:            $('#cofCor').val()
            },
            dataType: 'json',
            success: function (ok) {
                if (ok) {
                    toastr.success($('#cofId').val() ? 'Cofrinho atualizado!' : 'Cofrinho criado!');
                    $('#modalCofrinho').modal('hide');
                    buscaCofrinhos();
                } else { toastr.error('Erro ao salvar!'); }
            },
            error: function () { toastr.error('Erro ao salvar!'); }
        });
    });

    $(document).on('click', '.btnRemoveCof', function () {
        var id = $(this).data('id');
        Swal.fire({
            title: 'Remover cofrinho?', text: 'Todos os aportes serão apagados.',
            icon: 'warning', showCancelButton: true,
            confirmButtonColor: '#EF4444', cancelButtonColor: '#6B7280',
            confirmButtonText: 'Sim, remover', cancelButtonText: 'Cancelar'
        }).then(function (r) {
            if (r.isConfirmed) {
                $.ajax({
                    type: 'POST', url: App.ctrl.cofrinho,
                    data: { acao: 'remover', id: id }, dataType: 'json',
                    success: function () { toastr.success('Cofrinho removido!'); buscaCofrinhos(); },
                    error:   function () { toastr.error('Erro ao remover!'); }
                });
            }
        });
    });

    // ─── MODAL APORTE / RETIRADA ─────────────────────────────────

    function abrirModalAporte(id, nome, tipo, atual) {
        var isRetirar = tipo === 'retirar';
        $('#aporteCofId').val(id);
        $('#aporteAcao').val(tipo);
        $('#aporteNomeCof').text(nome).css('color', isRetirar ? '#EF4444' : 'var(--cor-azul)');
        $('#modalAporteIcone')
            .removeClass('bi-piggy-bank-fill bi-box-arrow-up titulo-azul')
            .addClass(isRetirar ? 'bi-box-arrow-up' : 'bi-piggy-bank-fill')
            .css('color', isRetirar ? '#EF4444' : '');
        if (!isRetirar) $('#modalAporteIcone').addClass('titulo-azul');
        $('#modalAporteTituloTxt').text(isRetirar ? 'Retirar do Cofrinho' : 'Guardar no Cofrinho');
        $('#btnSalvarAporte')
            .removeClass('btn-success btn-danger')
            .addClass(isRetirar ? 'btn-danger' : 'btn-success');
        if (isRetirar) {
            $('#aporteAtualWrap').show();
            $('#aporteAtualVal').text('R$ ' + fmtBR(atual || 0));
        } else {
            $('#aporteAtualWrap').hide();
        }
        if (cleaveAporte) cleaveAporte.setRawValue(''); else $('#aporteValor').val('');
        var hoje = new Date();
        $('#aporteData').val(hoje.getFullYear() + '-' +
            String(hoje.getMonth() + 1).padStart(2, '0') + '-' +
            String(hoje.getDate()).padStart(2, '0'));
        $('#aporteObs').val('');
        $('#modalAporte').modal('show');
    }

    $(document).on('click', '.btnAporte', function () {
        var $b = $(this);
        abrirModalAporte($b.data('id'), $b.data('nome'), 'aporte', 0);
    });

    $(document).on('click', '.btnRetirar', function () {
        var $b = $(this);
        abrirModalAporte($b.data('id'), $b.data('nome'), 'retirar', $b.data('atual'));
    });

    $('#btnSalvarAporte').click(function () {
        var val   = $('#aporteValor').val();
        var data  = $('#aporteData').val();
        var acao  = $('#aporteAcao').val();
        if (!val || !data) { toastr.warning('Preencha o valor e a data.'); return; }
        $.ajax({
            type: 'POST', url: App.ctrl.cofrinho,
            data: { acao: acao, cofrinho_id: $('#aporteCofId').val(), valor: val, data_aporte: data, observacao: $('#aporteObs').val() },
            dataType: 'json',
            success: function (res) {
                var ok = (typeof res === 'object') ? res.ok : res;
                if (ok) {
                    toastr.success(acao === 'retirar' ? 'Retirada registrada!' : 'Aporte registrado!');
                    $('#modalAporte').modal('hide');
                    buscaCofrinhos();
                    atualizaMeta();
                } else {
                    toastr.error((typeof res === 'object' && res.erro) ? res.erro : 'Erro ao registrar!');
                }
            },
            error: function () { toastr.error('Erro ao registrar!'); }
        });
    });

    // ─── HISTÓRICO ───────────────────────────────────────────────

    $(document).on('click', '.btnHistCof', function () {
        var $b = $(this);
        $('#histNomeCof').text($b.data('nome'));
        $('#loaderHist').show();
        $('#listaHist, #emptyHist').hide();
        $('#histTotal').text('R$ 0,00');
        $('#modalHistorico').modal('show');

        $.ajax({
            type: 'POST', url: App.ctrl.cofrinho,
            data: { acao: 'buscarAportes', id: $b.data('id') },
            dataType: 'json',
            success: function (data) {
                $('#loaderHist').hide();
                if (!data || data.length === 0) { $('#emptyHist').show(); return; }

                var rows  = '';
                var total = 0;
                $.each(data, function (_, a) {
                    var val = parseFloat(a.valor);
                    total  += val;
                    var partes  = a.data_aporte.substring(0, 10).split('-');
                    var dataFmt = partes[2] + '/' + partes[1] + '/' + partes[0];
                    var isRet   = val < 0;
                    var valCor  = isRet ? '#EF4444' : '#22C55E';
                    var valTxt  = isRet
                        ? '− R$ ' + fmtBR(Math.abs(val))
                        : '+ R$ ' + fmtBR(val);
                    var tipoBadge = isRet
                        ? '<span style="font-size:0.7rem;background:#EF444422;color:#EF4444;border-radius:4px;padding:1px 5px;">Retirada</span>'
                        : '';
                    rows += '<tr>' +
                        '<td style="padding:0.5rem 1rem;white-space:nowrap;">' + dataFmt + '</td>' +
                        '<td style="padding:0.5rem 1rem;white-space:nowrap;color:' + valCor + ';font-weight:600;">' + valTxt + ' ' + tipoBadge + '</td>' +
                        '<td style="padding:0.5rem 1rem;color:var(--cor-texto-off);">' + escHtml(a.observacao || '—') + '</td>' +
                    '</tr>';
                });
                $('#tbodyHist').html(rows);
                $('#histTotal').text('R$ ' + fmtBR(total));
                $('#listaHist').show();
            },
            error: function () { toastr.error('Erro ao buscar histórico!'); }
        });
    });

    // ─── MÁSCARAS ────────────────────────────────────────────────

    cleaveCofMeta = new Cleave('#cofMeta', {
        numeral: true, numeralThousandsGroupStyle: 'thousand',
        prefix: 'R$ ', noImmediatePrefix: true,
        delimiter: '.', decimal: ',', numeralDecimalMark: ',', stripLeadingZeroes: true
    });

    cleaveAporte = new Cleave('#aporteValor', {
        numeral: true, numeralThousandsGroupStyle: 'thousand',
        prefix: 'R$ ', noImmediatePrefix: true,
        delimiter: '.', decimal: ',', numeralDecimalMark: ',', stripLeadingZeroes: true
    });

    // ─── UTILS ───────────────────────────────────────────────────

    function fmtBR(n) {
        return parseFloat(n).toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    function escHtml(str) {
        return String(str).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
    }

    function shadeColor(hex, pct) {
        var num = parseInt(hex.replace('#',''), 16);
        var r   = Math.min(255, Math.max(0, (num >> 16) + pct));
        var g   = Math.min(255, Math.max(0, ((num >> 8) & 0xFF) + pct));
        var b   = Math.min(255, Math.max(0, (num & 0xFF) + pct));
        return '#' + [r, g, b].map(function(v) { return ('0' + v.toString(16)).slice(-2); }).join('');
    }

});
</script>

<style>
/* ── Cofrinhos ── */
.cof-cover-img {
    width: 100%;
    height: 90px;
    object-fit: cover;
    border-radius: var(--radius-md, 12px) var(--radius-md, 12px) 0 0;
}
.cof-cover-grad {
    height: 90px;
    border-radius: var(--radius-md, 12px) var(--radius-md, 12px) 0 0;
}
.cof-card {
    background: var(--cor-painel);
    border: 1px solid var(--cor-borda);
    border-radius: var(--radius-md, 12px);
    overflow: hidden;
    transition: transform .15s, box-shadow .15s;
}
.cof-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 24px rgba(0,0,0,.3);
}
.cof-card .card-body {
    padding: 0.65rem 0.75rem;
}
.cof-card .card-title {
    font-size: 0.95rem;
    margin-bottom: 0.2rem;
}
.cof-card .card-footer {
    background: transparent;
    border-top: 1px solid var(--cor-borda);
    padding: 0.45rem 0.75rem;
}
.cof-progress {
    height: 6px;
    background: var(--cor-input);
    border-radius: 20px;
    overflow: hidden;
}
.cof-info-row {
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: 0.78rem;
    color: var(--cor-texto);
    margin-bottom: 3px;
}
.cof-info-row i {
    font-size: 0.8rem;
    flex-shrink: 0;
    width: 14px;
    text-align: center;
}
.cof-info { margin-top: 0.2rem; }
</style>

<?php require_once __DIR__ . '/../templates/footer.php'; ?>
