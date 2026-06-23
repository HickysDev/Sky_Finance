<?php
require_once __DIR__ . '/../templates/header.php';
require_once __DIR__ . '/../../conn/conn.php';

$conn = Database::getConnection();
$buscaCartao = $conn->prepare("SELECT * FROM cartoes_credito");
$buscaCartao->execute();
$cartoes = $buscaCartao->fetchAll(PDO::FETCH_ASSOC);

$tipoDespesa = 'recorrente';
?>

<div class="animate__animated animate__fadeIn">

    <!-- HEADER -->
    <div class="d-flex align-items-center titulo-pagina mb-4">
        <h1 class="titulo mt-2 fs-titulo-pag">
            Configurações &nbsp;<i class="bi bi-gear-fill titulo-azul"></i>
        </h1>
    </div>

    <!-- TABS -->
    <div class="gerenciar-tabs mb-4">
        <button class="gtab-btn active" data-tab="Categorias">
            <i class="bi bi-list-columns"></i> Categorias
        </button>
        <button class="gtab-btn" data-tab="Cartoes">
            <i class="bi bi-credit-card-2-front-fill"></i> Cartões
        </button>
        <button class="gtab-btn" data-tab="Recorrentes">
            <i class="bi bi-arrow-clockwise"></i> Recorrentes
        </button>
        <button class="gtab-btn" data-tab="Responsaveis">
            <i class="bi bi-people-fill"></i> Responsáveis
        </button>
        <button class="gtab-btn" data-tab="Conta">
            <i class="bi bi-person-gear"></i> Conta
        </button>
    </div>

    <!-- ── CARTÕES ──────────────────────────────────────────────── -->
    <div id="tabCartoes" class="tab-section" style="display:none;">
        <div class="painel">
            <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
                <h5 class="titulo fs-secao-titulo m-0">
                    <i class="bi bi-credit-card-2-front-fill titulo-azul me-2"></i>Cartões Cadastrados
                </h5>
                <button class="btn btn-success btn-sm" id="adicionarCartao">
                    <i class="bi bi-plus-lg me-1"></i>Novo
                </button>
            </div>
            <div class="row g-3" id="listagemCartoes">
                <div class="col-12 text-center py-4">
                    <div class="spinner-border spinner-border-sm" style="color:var(--cor-azul);" role="status"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- ── CATEGORIAS ───────────────────────────────────────────── -->
    <div id="tabCategorias" class="tab-section">
        <div class="painel">
            <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
                <h5 class="titulo fs-secao-titulo m-0">
                    <i class="bi bi-list-columns titulo-azul me-2"></i>Categorias
                </h5>
                <button class="btn btn-success btn-sm adicionarNovo">
                    <i class="bi bi-plus-lg me-1"></i>Nova
                </button>
            </div>
            <div id="listaCatGrid" class="row g-2"></div>
        </div>
    </div>

    <!-- ── RECORRENTES ──────────────────────────────────────────── -->
    <div id="tabRecorrentes" class="tab-section" style="display:none;">
        <div class="painel">
            <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
                <h5 class="titulo fs-secao-titulo m-0">
                    <i class="bi bi-arrow-clockwise titulo-azul me-2"></i>Gastos Recorrentes
                </h5>
                <button class="btn btn-success btn-sm" id="adicionarGasto">
                    <i class="bi bi-plus-lg me-1"></i>Novo
                </button>
            </div>
            <div class="table-responsive">
                <table class="table table-hover mb-0" id="recorrentesTable" style="font-size:0.88rem;">
                    <thead>
                        <tr>
                            <th>Nome</th>
                            <th>Valor</th>
                            <th>Categoria</th>
                            <th>Cartão</th>
                            <th>Status</th>
                            <th class="text-center">Ações</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
            <div id="emptyRecorrentes" style="display:none;" class="text-center py-5">
                <i class="bi bi-arrow-clockwise" style="font-size:2.5rem;color:var(--cor-borda);"></i>
                <p class="mt-2 mb-0" style="color:var(--cor-texto-off);">Nenhum gasto recorrente cadastrado.</p>
            </div>
        </div>
    </div>

    <!-- ── RESPONSÁVEIS ────────────────────────────────────────── -->
    <div id="tabResponsaveis" class="tab-section" style="display:none;">
        <div class="painel">
            <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
                <h5 class="titulo fs-secao-titulo m-0">
                    <i class="bi bi-people-fill titulo-azul me-2"></i>Responsáveis
                </h5>
                <button class="btn btn-success btn-sm" id="adicionarResponsavel">
                    <i class="bi bi-plus-lg me-1"></i>Novo
                </button>
            </div>
            <div class="row g-2" id="listaResponsaveis">
                <div class="col-12 text-center py-4">
                    <div class="spinner-border spinner-border-sm" style="color:var(--cor-azul);" role="status"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- ── CONTAS FIXAS ──────────────────────────────────────────── -->
    <div id="tabContasFixas" class="tab-section" style="display:none;">
        <div class="painel">
            <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
                <h5 class="titulo fs-secao-titulo m-0">
                    <i class="bi bi-receipt-cutoff titulo-azul me-2"></i>Contas Fixas
                </h5>
                <div class="d-flex gap-2">
                    <a href="contas_fixas.php" class="btn btn-outline-secondary btn-sm">
                        <i class="bi bi-eye me-1"></i>Ver mensal
                    </a>
                    <button class="btn btn-success btn-sm" id="adicionarContaFixa">
                        <i class="bi bi-plus-lg me-1"></i>Nova
                    </button>
                </div>
            </div>
            <div id="listaContasFixas">
                <div class="text-center py-4">
                    <div class="spinner-border spinner-border-sm" style="color:var(--cor-azul);" role="status"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- ── CONTA ────────────────────────────────────────────────── -->
    <div id="tabConta" class="tab-section" style="display:none;">

        <!-- Perfil -->
        <div class="painel mb-3">
            <h6 class="titulo mb-3"><i class="bi bi-person-circle titulo-azul me-2"></i>Meu perfil</h6>
            <div class="d-flex align-items-center gap-4 flex-wrap">

                <!-- Avatar -->
                <div class="perfil-avatar-wrap" id="avatarWrap">
                    <div class="perfil-avatar" id="avatarDisplay"></div>
                    <label class="perfil-avatar-btn" for="inputFoto" title="Trocar foto">
                        <i class="bi bi-camera-fill"></i>
                    </label>
                    <input type="file" id="inputFoto" accept="image/jpeg,image/png,image/webp" style="display:none;">
                </div>

                <!-- Nome e e-mail -->
                <div class="flex-grow-1" style="max-width:360px;">
                    <div class="mb-3">
                        <label class="form-label">Nome</label>
                        <input type="text" class="form-control" id="perfilNome" placeholder="Seu nome">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">E-mail</label>
                        <input type="email" class="form-control" id="perfilEmail" placeholder="seu@email.com">
                    </div>
                    <button class="btn btn-primary btn-sm" id="btnSalvarPerfil">
                        <i class="bi bi-check-lg me-1"></i>Salvar
                    </button>
                </div>
            </div>
        </div>

        <!-- Início do controle (marco) -->
        <div class="painel mb-3">
            <h6 class="titulo mb-2"><i class="bi bi-flag-fill titulo-azul me-2"></i>Início do controle</h6>
            <p class="mb-3" style="font-size:0.82rem;color:var(--cor-texto-off);">
                Defina a partir de qual mês o sistema começa a contar. Tudo antes desse mês aparece
                zerado em todas as telas (útil para ignorar dados antigos importados).
            </p>
            <div class="d-flex align-items-end gap-2 flex-wrap" style="max-width:440px;">
                <div class="flex-grow-1">
                    <label class="form-label">Mês inicial</label>
                    <input type="month" class="form-control" id="marcoInput">
                </div>
                <button class="btn btn-primary btn-sm" id="btnSalvarMarco">
                    <i class="bi bi-check-lg me-1"></i>Salvar
                </button>
                <button class="btn btn-outline-secondary btn-sm" id="btnLimparMarco">
                    <i class="bi bi-x-lg me-1"></i>Limpar
                </button>
            </div>
            <div id="marcoStatus" class="mt-2" style="font-size:0.8rem;color:var(--cor-texto-off);"></div>
        </div>

        <!-- Trocar senha -->
        <div class="painel mb-3">
            <h6 class="titulo mb-3"><i class="bi bi-key-fill titulo-azul me-2"></i>Alterar senha</h6>
            <div class="row g-3" style="max-width:360px;">
                <div class="col-12">
                    <label class="form-label">Senha atual</label>
                    <input type="password" class="form-control" id="senhaAtual" placeholder="••••••••">
                </div>
                <div class="col-12">
                    <label class="form-label">Nova senha <span style="color:var(--cor-texto-off);font-size:0.75rem;">(mín. 8 caracteres)</span></label>
                    <input type="password" class="form-control" id="novaSenha" placeholder="••••••••">
                </div>
                <div class="col-12">
                    <label class="form-label">Confirmar nova senha</label>
                    <input type="password" class="form-control" id="confirmaSenha" placeholder="••••••••">
                </div>
                <div class="col-12">
                    <button class="btn btn-primary btn-sm" id="btnTrocarSenha">
                        <i class="bi bi-check-lg me-1"></i>Atualizar senha
                    </button>
                </div>
            </div>
        </div>

        <?php if (($_SESSION['usuario_id'] ?? 0) == 1): ?>
        <!-- Zona de perigo -->
        <div class="painel mb-3" style="border-color:rgba(239,68,68,0.3);">
            <h6 class="titulo mb-1" style="color:#EF4444;">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>Zona de perigo
            </h6>
            <p style="font-size:0.82rem;color:var(--cor-texto-off);margin-bottom:1rem;">
                Apaga todos os dados financeiros do sistema (gastos, cartões, categorias, etc.). Sua conta de acesso é preservada.
            </p>
            <button class="btn btn-sm btn-outline-danger" id="btnResetDados">
                <i class="bi bi-trash3-fill me-1"></i>Apagar todos os dados
            </button>
        </div>
        <?php endif; ?>

        <!-- Usuários -->
        <div class="painel">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <h6 class="titulo mb-0"><i class="bi bi-people-fill titulo-azul me-2"></i>Usuários</h6>
                <button class="btn btn-success btn-sm" id="btnNovoUsuario">
                    <i class="bi bi-person-plus-fill me-1"></i>Novo usuário
                </button>
            </div>
            <div id="listaUsuarios">
                <div class="text-center py-3"><div class="spinner-border spinner-border-sm" style="color:var(--cor-azul);"></div></div>
            </div>
        </div>

    </div>

</div>

<!-- MODAL NOVO USUÁRIO -->
<div class="modal fade" id="modalNovoUsuario" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-person-plus-fill titulo-azul me-2"></i>Novo usuário</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Nome</label>
                    <input type="text" class="form-control" id="novoUsuNome" placeholder="Nome completo">
                </div>
                <div class="mb-3">
                    <label class="form-label">E-mail</label>
                    <input type="email" class="form-control" id="novoUsuEmail" placeholder="email@exemplo.com">
                </div>
                <div class="mb-3">
                    <label class="form-label">Senha <span style="color:var(--cor-texto-off);font-size:0.75rem;">(mín. 8 caracteres)</span></label>
                    <input type="password" class="form-control" id="novoUsuSenha" placeholder="••••••••">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-success btn-sm" id="salvarNovoUsuario">
                    <i class="bi bi-person-check-fill me-1"></i>Criar
                </button>
            </div>
        </div>
    </div>
</div>

<!-- MODAL CARTÃO -->
<div class="modal fade" id="modalCartao" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-md">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-credit-card-fill titulo-azul me-2"></i>
                    <span id="modalCartaoTitulo">Novo Cartão</span>
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="tipoAlteracao" value="criar">
                <input type="hidden" id="idCartao" value="">

                <div class="mb-3">
                    <label class="form-label">Nome do cartão</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-credit-card-fill"></i></span>
                        <input type="text" class="form-control" id="nomeCartao" placeholder="Ex: Nubank">
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Limite</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-cash-coin"></i></span>
                        <input type="text" class="form-control real" id="limite" placeholder="R$ 0,00">
                    </div>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-6">
                        <label class="form-label">Dia de fechamento</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-calendar-x-fill"></i></span>
                            <input type="number" class="form-control" id="dataFechamento" placeholder="Ex: 10" min="1" max="31">
                        </div>
                    </div>
                    <div class="col-6">
                        <label class="form-label">Dia de vencimento</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-calendar-check-fill"></i></span>
                            <input type="number" class="form-control" id="dataVencimento" placeholder="Ex: 5" min="1" max="31">
                        </div>
                    </div>
                </div>

                <div class="mb-2">
                    <label class="form-label">Cor do cartão</label>
                    <div class="cor-swatch-group">
                        <button type="button" class="cor-swatch" data-cor="#8B5CF6" style="background:#8B5CF6" title="Roxo"></button>
                        <button type="button" class="cor-swatch selecionado" data-cor="#3B82F6" style="background:#3B82F6" title="Azul"></button>
                        <button type="button" class="cor-swatch" data-cor="#10B981" style="background:#10B981" title="Verde"></button>
                        <button type="button" class="cor-swatch" data-cor="#EF4444" style="background:#EF4444" title="Vermelho"></button>
                        <button type="button" class="cor-swatch" data-cor="#F59E0B" style="background:#F59E0B" title="Âmbar"></button>
                        <button type="button" class="cor-swatch" data-cor="#F97316" style="background:#F97316" title="Laranja"></button>
                        <button type="button" class="cor-swatch" data-cor="#EC4899" style="background:#EC4899" title="Rosa"></button>
                        <button type="button" class="cor-swatch" data-cor="#374151" style="background:#374151" title="Chumbo"></button>
                    </div>
                    <input type="hidden" id="corCartao" value="#3B82F6">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Fechar</button>
                <button type="button" class="btn btn-success" id="salvaAlteracao" onclick="salvaCartaoHandler()">
                    Salvar <i class="bi bi-floppy-fill ms-1"></i>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- MODAL RESPONSÁVEL -->
<div class="modal fade" id="modalResponsavel" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-person-fill titulo-azul me-2"></i>
                    <span id="modalRespTitulo">Novo Responsável</span>
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="respId" value="0">
                <div class="mb-3">
                    <label class="form-label">Nome</label>
                    <input type="text" class="form-control" id="respNome" placeholder="Ex: Mãe">
                </div>
                <div class="mb-2">
                    <label class="form-label">Cor</label>
                    <div class="cor-swatch-group" id="respCorSwatches"></div>
                    <input type="hidden" id="respCor" value="#3B82F6">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Fechar</button>
                <button type="button" class="btn btn-success btn-sm" id="salvarResponsavel">
                    Salvar <i class="bi bi-floppy-fill ms-1"></i>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- MODAL CONTA FIXA -->
<div class="modal fade" id="modalContaFixa" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-md">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-receipt-cutoff titulo-azul me-2"></i>
                    <span id="modalCFTitulo">Nova Conta Fixa</span>
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="cfId" value="0">

                <div class="mb-3">
                    <label class="form-label">Nome da conta</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-receipt-cutoff"></i></span>
                        <input type="text" class="form-control" id="cfNome" placeholder="Ex: Claro, Água, Internet">
                    </div>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-7">
                        <label class="form-label">Valor</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-cash-coin"></i></span>
                            <input type="text" class="form-control cf-real" id="cfValor" placeholder="R$ 0,00">
                        </div>
                    </div>
                    <div class="col-5">
                        <label class="form-label">Dia de vencimento</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-calendar-check-fill"></i></span>
                            <input type="number" class="form-control" id="cfDia" placeholder="Ex: 10" min="1" max="31">
                        </div>
                    </div>
                </div>

                <div class="mb-2">
                    <label class="form-label">Cor</label>
                    <div class="cor-swatch-group" id="cfCorSwatches"></div>
                    <input type="hidden" id="cfCor" value="#3B82F6">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Fechar</button>
                <button type="button" class="btn btn-success" id="salvarContaFixa">
                    Salvar <i class="bi bi-floppy-fill ms-1"></i>
                </button>
            </div>
        </div>
    </div>
</div>

<?php include '../templates/modalCategoria.php'; ?>
<?php include '../templates/modalCadastra.php'; ?>

<script>
var tipoDespesa = 'recorrente';
var cartoesArray = [];
var recorrentesArray = [];
var modoAtual = 'criar';

// ── TABS ────────────────────────────────────────────────────────
$(document).on('click', '.gtab-btn', function () {
    const tab = $(this).data('tab');
    $('.gtab-btn').removeClass('active');
    $(this).addClass('active');
    $('.tab-section').hide();
    $('#tab' + tab).fadeIn(180);
    if (tab === 'Recorrentes')  buscaRecorrentes();
    if (tab === 'Categorias')   buscaCategorias();
    if (tab === 'Cartoes')      buscaCartoes();
    if (tab === 'Responsaveis') buscaResponsaveis();
    if (tab === 'ContasFixas')  buscaContasFixas();
    if (tab === 'Conta')        { buscaUsuarios(); carregaPerfil(); }
});

// ── MODAL RECORRENTES (modalAdiciona) ───────────────────────────
$('#modalAdiciona').on('show.bs.modal', function () {
    $('#metodoWrapper').hide();
    $('#parceladoWrapper').hide();
    $('#dataWrapper').hide();
    $('.border-parcelado').hide();
    $('#recorrenteWrapper').show();
    $('#recorrente').prop('checked', true).prop('disabled', true);
    resetCatSelect();
    $('#cartaoWrapper').show();
    $('#cartaoWrapper > label').first().text('Cartão (opcional)');
    $('#cartao').val('');
    $('.cartao-mini-modal').removeClass('selecionado');
    renderCartoesMiniModal();
    if (modoAtual === 'criar') {
        $('#descricao').val('');
        $('#valor').val('');
        $('#adicionarDespesa').show();
        $('#editarDespesa').hide();
    } else {
        $('#adicionarDespesa').hide();
        $('#editarDespesa').show();
    }
});

// ── CARTÕES ─────────────────────────────────────────────────────
$(document).on('click', '#adicionarCartao', function () {
    $('#tipoAlteracao').val('criar');
    $('#idCartao').val('');
    $('#nomeCartao').val('');
    $('#limite').val('');
    $('#dataFechamento').val('');
    $('#dataVencimento').val('');
    $('#corCartao').val('#3B82F6');
    $('.cor-swatch:not(.cat-cor-swatch)').removeClass('selecionado');
    $('.cor-swatch:not(.cat-cor-swatch)[data-cor="#3B82F6"]').addClass('selecionado');
    $('#modalCartaoTitulo').text('Novo Cartão');
    $('#modalCartao').modal('show');
});

$(document).on('click', '.editarCartao', function () {
    const id = $(this).data('id');
    const c  = window.cartoesArray[id];
    if (!c) { toastr.error('Cartão não encontrado!'); return; }

    $('#tipoAlteracao').val('alteracao');
    $('#idCartao').val(id);
    $('#nomeCartao').val(c.nome_cartao);
    $('#limite').val(c.limite.replace('.', ','));
    $('#dataFechamento').val(c.fechamento_dia);
    $('#dataVencimento').val(c.vencimento_dia);
    const cor = c.cor || '#3B82F6';
    $('#corCartao').val(cor);
    $('.cor-swatch:not(.cat-cor-swatch)').removeClass('selecionado');
    $(`.cor-swatch:not(.cat-cor-swatch)[data-cor="${cor}"]`).addClass('selecionado');
    $('#modalCartaoTitulo').text(c.nome_cartao);
    $('#modalCartao').modal('show');
});

$(document).on('click', '.cor-swatch:not(.cat-cor-swatch)', function () {
    $('.cor-swatch:not(.cat-cor-swatch)').removeClass('selecionado');
    $(this).addClass('selecionado');
    $('#corCartao').val($(this).data('cor'));
});

function salvaCartaoHandler() {
    const cartaoArray = {
        nomeCartao:    $('#nomeCartao').val(),
        limite:        $('#limite').val(),
        dataFechamento:$('#dataFechamento').val(),
        dataVencimento:$('#dataVencimento').val(),
        cor:           $('#corCartao').val() || '#3B82F6'
    };
    const tipo = $('#tipoAlteracao').val();
    const id   = $('#idCartao').val();

    if (tipo === 'criar') {
        criaCartao(cartaoArray);
    } else {
        alteraCartao(cartaoArray, id);
    }
}

function buscaCartoes() {
    $('#listagemCartoes').html('<div class="col-12 text-center py-4"><div class="spinner-border spinner-border-sm" style="color:var(--cor-azul);" role="status"></div></div>');
    $.ajax({
        type: 'POST', url: '../controllers/CartoesController.php',
        data: { acao: 'busca' }, dataType: 'json',
        success: function (data) {
            window.cartoesArray = data;
            if (!data || Object.keys(data).length === 0) {
                $('#listagemCartoes').html('<div class="col-12 text-center py-4" style="color:var(--cor-texto-off);">Nenhum cartão cadastrado.</div>');
                return;
            }
            let html = '';
            $.each(data, function (_, c) {
                const cor    = c.cor || '#3B82F6';
                const limite = parseFloat(c.limite).toLocaleString('pt-BR', {minimumFractionDigits:2});
                html += `
                <div class="col-12 col-sm-6 col-xl-4">
                    <div class="crt-card" style="--crt-cor:${cor};">
                        <div class="crt-card-top">
                            <div class="crt-chip"><i class="bi bi-cpu-fill"></i></div>
                            <div class="crt-card-actions">
                                <button class="crt-action-btn editarCartao" data-id="${c.id}" title="Editar">
                                    <i class="bi bi-pencil-fill"></i>
                                </button>
                                <button class="crt-action-btn crt-action-del excluirCartao" data-id="${c.id}" title="Excluir">
                                    <i class="bi bi-trash-fill"></i>
                                </button>
                            </div>
                        </div>
                        <div class="crt-card-nome">${escHtml(c.nome_cartao)}</div>
                        <div class="crt-card-limite">R$ ${limite}</div>
                        <div class="crt-card-footer">
                            <div class="crt-card-info">
                                <span class="crt-label">Fechamento</span>
                                <span class="crt-val">dia ${c.fechamento_dia}</span>
                            </div>
                            <div class="crt-card-info">
                                <span class="crt-label">Vencimento</span>
                                <span class="crt-val">dia ${c.vencimento_dia}</span>
                            </div>
                        </div>
                    </div>
                </div>`;
            });
            $('#listagemCartoes').html(html);
        }
    });
}

function renderCartoesMiniModal() {
    var selected = String($('#cartao').val() || '');
    var html = '<div class="cartao-mini-modal' + (!selected ? ' selecionado' : '') +
               '" data-id="" style="--cartao-cor:#6B7280;"><i class="bi bi-x-circle" style="color:#6B7280;"></i> Nenhum</div>';
    if (window.cartoesArray) {
        $.each(window.cartoesArray, function (_, cartao) {
            var cor = cartao.cor || '#3B82F6';
            var sel = selected && String(cartao.id) === selected ? ' selecionado' : '';
            html += '<div class="cartao-mini-modal' + sel + '" data-id="' + cartao.id + '" style="--cartao-cor:' + cor + ';">' +
                    '<i class="bi bi-credit-card-fill" style="color:' + cor + ';"></i> ' + escHtml(cartao.nome_cartao) + '</div>';
        });
    }
    $('#cartaoSelectorModal').html(html);
}

$(document).on('click', '.cartao-mini-modal', function () {
    $('.cartao-mini-modal').removeClass('selecionado');
    $(this).addClass('selecionado');
    $('#cartao').val($(this).data('id'));
});

function alteraCartao(cartaoArray, idCartao) {
    $.ajax({
        type: 'POST', url: '../controllers/CartoesController.php',
        data: { acao: 'alterar', cartao: cartaoArray, idCartao: idCartao }, dataType: 'json',
        success: function (ok) {
            if (ok) {
                toastr.success('Cartão atualizado!');
                $('#modalCartao').modal('hide');
                buscaCartoes();
            } else { toastr.error('Erro ao atualizar o cartão!'); }
        },
        error: function () { toastr.error('Erro ao atualizar o cartão!'); }
    });
}

function criaCartao(cartaoArray) {
    $.ajax({
        type: 'POST', url: '../controllers/CartoesController.php',
        data: { acao: 'adicionar', cartao: cartaoArray }, dataType: 'json',
        success: function (ok) {
            if (ok) {
                toastr.success('Cartão criado!');
                $('#modalCartao').modal('hide');
                buscaCartoes();
            } else { toastr.error('Erro ao criar o cartão!'); }
        },
        error: function () { toastr.error('Erro ao criar o cartão!'); }
    });
}

$(document).on('click', '.excluirCartao', function () {
    const id = $(this).data('id');
    Swal.fire({
        title: 'Remover cartão?',
        icon: 'warning', showCancelButton: true,
        confirmButtonColor: '#EF4444', cancelButtonColor: '#6B7280',
        confirmButtonText: 'Sim, remover', cancelButtonText: 'Cancelar'
    }).then(function (r) {
        if (r.isConfirmed) {
            $.ajax({
                type: 'POST', url: '../controllers/CartoesController.php',
                data: { acao: 'excluir', id: id }, dataType: 'json',
                success: function (ok) {
                    if (ok) { toastr.success('Cartão removido!'); buscaCartoes(); }
                    else    { toastr.error('Erro ao remover!'); }
                }
            });
        }
    });
});

// ── CATEGORIAS ──────────────────────────────────────────────────
$(document).on('click', '.editaCategoriaBtn', function () {
    abrirModalCategoria($(this).data('codigo'), $(this).data('nome'), $(this).data('cor'), $(this).data('icone'));
});

$(document).on('click', '.adicionarNovo', function () {
    abrirModalCategoria(0, '', '#6B7280', '');
});

$(document).on('cat:salva', function () { buscaCategorias(); });

$(document).on('click', '.excluiCategoriaBtn', function () {
    const id  = $(this).data('codigo');
    const $el = $(this).closest('.cat-item-card');
    Swal.fire({
        title: 'Remover categoria?',
        icon: 'warning', showCancelButton: true,
        confirmButtonColor: '#EF4444', cancelButtonColor: '#6B7280',
        confirmButtonText: 'Sim, remover', cancelButtonText: 'Cancelar'
    }).then(function (r) {
        if (r.isConfirmed) {
            $.ajax({
                type: 'POST', url: '../controllers/CategoriaController.php',
                data: { acao: 'excluir', id: id }, dataType: 'json',
                success: function (ok) {
                    if (ok) { toastr.success('Categoria removida!'); $el.fadeOut(300, function(){ $(this).remove(); }); }
                    else    { toastr.error('Erro ao remover!'); }
                }
            });
        }
    });
});

function buscaCategorias() {
    $('#listaCatGrid').html('<div class="col-12 text-center py-4"><div class="spinner-border spinner-border-sm" style="color:var(--cor-azul);" role="status"></div></div>');
    $.ajax({
        type: 'POST', url: '../controllers/CategoriaController.php',
        data: { acao: 'busca' }, dataType: 'json',
        success: function (data) {
            if (!data || !data.length) {
                $('#listaCatGrid').html('<div class="col-12 text-center py-4" style="color:var(--cor-texto-off);">Nenhuma categoria cadastrada.</div>');
                popularCatSelect([]);
                return;
            }
            let html = '';
            $.each(data, function (_, cat) {
                const cor      = cat.cor   || '#6B7280';
                const icone    = cat.icone || '';
                const iconeHtml = icone ? `<span class="me-1" style="font-size:1.1rem;">${escHtml(icone)}</span>` : '';
                const nomeEsc  = escHtml(cat.nome);
                const iconeEsc = escHtml(icone);
                html += `
                <div class="col-12 col-sm-6 col-lg-4 col-xl-3">
                    <div class="cat-item-card" style="border-left:4px solid ${cor};">
                        <div class="d-flex align-items-center gap-2 flex-grow-1 min-w-0">
                            <div class="cat-item-icon" style="background:${cor}22;color:${cor};">${iconeHtml || `<span class="cat-dot" style="background:${cor};width:12px;height:12px;"></span>`}</div>
                            <span class="cat-item-nome" style="color:${cor};">${nomeEsc}</span>
                        </div>
                        <div class="d-flex gap-1 flex-shrink-0">
                            <button class="btn btn-sm btn-outline-warning editaCategoriaBtn"
                                data-codigo="${cat.id}" data-nome="${nomeEsc}"
                                data-cor="${cor}" data-icone="${iconeEsc}" title="Editar">
                                <i class="bi bi-pencil-fill"></i>
                            </button>
                            <button class="btn btn-sm btn-outline-danger excluiCategoriaBtn" data-codigo="${cat.id}" title="Remover">
                                <i class="bi bi-trash-fill"></i>
                            </button>
                        </div>
                    </div>
                </div>`;
            });
            $('#listaCatGrid').html(html);
            popularCatSelect(data);
        }
    });
}

// ── RECORRENTES ─────────────────────────────────────────────────
$('#adicionarGasto').click(function () {
    modoAtual = 'criar';
    $('#gastoId').val('');
    $('#modalAdiciona').modal('show');
});

$('#adicionarDespesa').click(function () {
    $.ajax({
        type: 'POST', url: '../controllers/GastosController.php',
        data: {
            acao:        'adicionar',
            descricao:   $('#descricao').val(),
            valor:       $('#valor').val(),
            categoria:   $('#categoria').val(),
            metodo:      'Crédito',
            cartao:      $('#cartao').val(),
            data:        $('#data').val(),
            recorrente:  'S',
            tipo:        'recorrente',
            responsavel: $('#responsavel').val() || '',
        },
        dataType: 'json',
        success: function () { buscaRecorrentes(); toastr.success('Recorrente criado!'); $('#modalAdiciona').modal('hide'); },
        error:   function () { toastr.error('Erro ao criar recorrente!'); }
    });
});

$('#editarDespesa').click(function () {
    editaGasto($('#gastoId').val());
});

function editaGasto(id) {
    $.ajax({
        type: 'POST', url: '../controllers/GastosController.php',
        data: {
            acao: 'editaGasto', id: id,
            nome:      $('#descricao').val(),
            valor:     $('#valor').val(),
            categoria: $('#categoria').val(),
            cartao:    $('#cartao').val()
        },
        dataType: 'json',
        success: function () { buscaRecorrentes(); toastr.success('Recorrente atualizado!'); $('#modalAdiciona').modal('hide'); },
        error:   function () { toastr.error('Erro ao salvar.'); }
    });
}

$(document).on('click', '.editaRecorrenteBtn', function () {
    modoAtual = 'editar';
    $('#gastoId').val($(this).data('codigo'));
    $('#modalAdiciona').modal('show');
    preencheRecorrente(recorrentesArray);
});

$(document).on('click', '.inativaRecorrenteBtn', function () {
    inativaRecorrente($(this).data('codigo'));
});

$(document).on('click', '.ativaRecorrenteBtn', function () {
    const id = $(this).data('codigo');
    const hoje = new Date().toISOString().split('T')[0];
    Swal.fire({
        title: 'Reativar recorrente',
        html: `<label class="swal2-label" style="margin-bottom:6px;">Data de reinício</label>
               <input type="date" id="swalDataReativacao" class="swal2-input" value="${hoje}">`,
        showCancelButton: true,
        confirmButtonText: 'Reativar', cancelButtonText: 'Cancelar',
        confirmButtonColor: '#22C55E', cancelButtonColor: '#6B7280',
        preConfirm: () => {
            const v = document.getElementById('swalDataReativacao').value;
            if (!v) Swal.showValidationMessage('Selecione uma data');
            return v;
        }
    }).then(function (r) {
        if (r.isConfirmed) reativaRecorrente(id, r.value);
    });
});

$(document).on('change', '#marcaTodosMesRecorrente', function () {
    $('.marcagastoRecorrente').prop('checked', $(this).prop('checked'));
});

function buscaRecorrentes() {
    $('#recorrentesTable tbody').html('<tr><td colspan="6" class="text-center py-3"><div class="spinner-border spinner-border-sm" style="color:var(--cor-azul);" role="status"></div></td></tr>');
    $('#emptyRecorrentes').hide();
    $.ajax({
        type: 'POST', url: '../controllers/GastosController.php',
        data: { acao: 'buscarRecorrentes' }, dataType: 'json',
        success: function (data) {
            recorrentesArray = data;
            window.recorrentesArray = data;
            const arr = Object.values(data).sort((a, b) => a.ativo === 'N' ? 1 : -1);
            if (!arr.length) {
                $('#recorrentesTable tbody').html('');
                $('#emptyRecorrentes').show();
                return;
            }
            let html = '';
            $.each(arr, function (_, r) {
                const ativo = r.ativo === 'S';
                let rastro = '';
                if (ativo && r.mes_inicio) {
                    rastro = `<small style="color:var(--cor-texto-off);font-size:0.7rem;"> desde ${moment(r.mes_inicio).format('MM/YYYY')}</small>`;
                } else if (!ativo && r.inativado_em) {
                    rastro = `<small style="color:#EF4444;font-size:0.7rem;"> inativ. ${moment(r.inativado_em).format('DD/MM/YY')}</small>`;
                }
                const statusBadge = ativo
                    ? `<span style="background:#22C55E22;color:#22C55E;border:1px solid #22C55E44;font-size:0.72rem;padding:2px 8px;border-radius:20px;white-space:nowrap;">● Ativo${rastro}</span>`
                    : `<span style="background:#EF444422;color:#EF4444;border:1px solid #EF444444;font-size:0.72rem;padding:2px 8px;border-radius:20px;white-space:nowrap;">● Inativo${rastro}</span>`;

                const botaoToggle = ativo
                    ? `<button data-codigo="${r.id}" class="btn inativaRecorrenteBtn btn-sm btn-outline-danger px-2" title="Inativar"><i class="bi bi-ban"></i></button>`
                    : `<button data-codigo="${r.id}" class="btn ativaRecorrenteBtn btn-sm btn-outline-success px-2" title="Reativar"><i class="bi bi-check-circle-fill"></i></button>`;

                html += `<tr style="${!ativo ? 'opacity:.55;' : ''}">
                    <td style="font-weight:500;">${escHtml(r.nome)}</td>
                    <td style="color:#22C55E;font-weight:600;">R$ ${r.valor}</td>
                    <td>${catBadgeHtml(r.categoria)}</td>
                    <td style="color:var(--cor-texto-off);">${escHtml(r.nome_cartao ?? '—')}</td>
                    <td>${statusBadge}</td>
                    <td class="text-center">
                        <div class="d-flex justify-content-center gap-1">
                            <button data-codigo="${r.id}" class="btn editaRecorrenteBtn btn-sm btn-outline-warning px-2" title="Editar"><i class="bi bi-pencil-fill"></i></button>
                            ${botaoToggle}
                        </div>
                    </td>
                </tr>`;
            });
            $('#recorrentesTable tbody').html(html);
        }
    });
}

function preencheRecorrente(arr) {
    const id  = $('#gastoId').val();
    const rec = arr[id];
    if (!rec) return;
    $('#descricao').val(rec.nome);
    if (bancValorGest) bancValorGest.setValue(rec.valor); else $('#valor').val(rec.valor);
    const cat = window.categoriaMap[String(rec.id_categoria || '')];
    $('#categoria').val(rec.id_categoria || '');
    if (cat) {
        const cor   = cat.cor   || '#6B7280';
        const icone = cat.icone ? `<span class="me-1">${escHtml(cat.icone)}</span>` : '';
        $('#catSelBtn .cat-sel-preview').html(
            `<span class="cat-dot" style="background:${cor};flex-shrink:0;"></span>${icone}<span class="ms-1" style="color:${cor};">${escHtml(cat.nome)}</span>`
        );
    }
    $('#cartao').val(rec.id_cartao || '');
    $('.cartao-mini-modal').removeClass('selecionado');
    if (rec.id_cartao) {
        $('.cartao-mini-modal[data-id="' + rec.id_cartao + '"]').addClass('selecionado');
    } else {
        $('.cartao-mini-modal[data-id=""]').addClass('selecionado');
    }
}

function reativaRecorrente(id, data) {
    $.ajax({
        type: 'POST', url: '../controllers/GastosController.php',
        data: { acao: 'reativarRecorrente', id: id, data: data }, dataType: 'json',
        success: function () { toastr.success('Recorrente reativado!'); buscaRecorrentes(); },
        error:   function () { toastr.error('Erro ao reativar!'); }
    });
}

function inativaRecorrente(id) {
    $.ajax({
        type: 'POST', url: '../controllers/GastosController.php',
        data: { acao: 'inativaRecorrentes', id: id }, dataType: 'json',
        success: function () { buscaRecorrentes(); },
        error:   function () { toastr.error('Erro ao inativar!'); }
    });
}

// ── RESPONSÁVEIS ────────────────────────────────────────────────
(function () {
    var RESP_CORES = ['#3B82F6','#8B5CF6','#EC4899','#EF4444','#F97316','#F59E0B','#22C55E','#10B981','#6B7280'];

    $('#respCorSwatches').html(RESP_CORES.map(function (c) {
        return '<button type="button" class="cor-swatch resp-cor-swatch" data-cor="' + c +
               '" style="background:' + c + ';"></button>';
    }).join(''));

    $(document).on('click', '.resp-cor-swatch', function () {
        $('.resp-cor-swatch').removeClass('selecionado');
        $(this).addClass('selecionado');
        $('#respCor').val($(this).data('cor'));
    });

    $('#adicionarResponsavel').click(function () {
        $('#respId').val(0);
        $('#respNome').val('');
        $('#respCor').val('#3B82F6');
        $('.resp-cor-swatch').removeClass('selecionado');
        $('.resp-cor-swatch[data-cor="#3B82F6"]').addClass('selecionado');
        $('#modalRespTitulo').text('Novo Responsável');
        $('#modalResponsavel').modal('show');
    });

    $(document).on('click', '.editarResponsavel', function () {
        var id   = $(this).data('id');
        var nome = $(this).data('nome');
        var cor  = $(this).data('cor') || '#3B82F6';
        $('#respId').val(id);
        $('#respNome').val(nome);
        $('#respCor').val(cor);
        $('.resp-cor-swatch').removeClass('selecionado');
        $('.resp-cor-swatch[data-cor="' + cor + '"]').addClass('selecionado');
        $('#modalRespTitulo').text(nome);
        $('#modalResponsavel').modal('show');
    });

    $('#salvarResponsavel').click(function () {
        var id   = parseInt($('#respId').val());
        var nome = $('#respNome').val().trim();
        var cor  = $('#respCor').val() || '#3B82F6';
        if (!nome) { toastr.warning('Informe o nome!'); return; }

        $.ajax({
            type: 'POST', url: App.ctrl.responsaveis,
            data: { acao: id > 0 ? 'editar' : 'adicionar', id: id, nome: nome, cor: cor },
            dataType: 'json',
            success: function (ok) {
                if (ok) {
                    toastr.success(id > 0 ? 'Responsável atualizado!' : 'Responsável criado!');
                    $('#modalResponsavel').modal('hide');
                    buscaResponsaveis();
                    carregarResponsaveis();
                } else { toastr.error('Erro ao salvar!'); }
            },
            error: function () { toastr.error('Erro ao salvar!'); }
        });
    });

    $(document).on('click', '.excluirResponsavel', function () {
        var id   = $(this).data('id');
        var nome = $(this).data('nome');
        Swal.fire({
            title: 'Remover "' + nome + '"?',
            text: 'As despesas vinculadas ficarão sem responsável.',
            icon: 'warning', showCancelButton: true,
            confirmButtonColor: '#EF4444', cancelButtonColor: '#6B7280',
            confirmButtonText: 'Sim, remover', cancelButtonText: 'Cancelar'
        }).then(function (r) {
            if (r.isConfirmed) {
                $.ajax({
                    type: 'POST', url: App.ctrl.responsaveis,
                    data: { acao: 'excluir', id: id }, dataType: 'json',
                    success: function (ok) {
                        if (ok) { toastr.success('Removido!'); buscaResponsaveis(); carregarResponsaveis(); }
                        else    { toastr.error('Erro ao remover!'); }
                    }
                });
            }
        });
    });
})();

function buscaResponsaveis() {
    $('#listaResponsaveis').html('<div class="col-12 text-center py-4"><div class="spinner-border spinner-border-sm" style="color:var(--cor-azul);" role="status"></div></div>');
    $.ajax({
        type: 'POST', url: App.ctrl.responsaveis,
        data: { acao: 'buscar' }, dataType: 'json',
        success: function (data) {
            if (!data || !data.length) {
                $('#listaResponsaveis').html(
                    '<div class="col-12 text-center py-5">' +
                    '<i class="bi bi-people" style="font-size:2.5rem;color:var(--cor-borda);"></i>' +
                    '<p class="mt-2 mb-0" style="color:var(--cor-texto-off);">Nenhum responsável cadastrado.</p></div>');
                return;
            }
            var html = '';
            $.each(data, function (_, r) {
                var cor = r.cor || '#6B7280';
                html += '<div class="col-12 col-sm-6 col-md-4 col-lg-3">' +
                    '<div class="resp-gerenciar-card" style="border-left:4px solid ' + cor + ';">' +
                        '<div class="d-flex align-items-center gap-3">' +
                            '<div class="resp-avatar" style="background:' + cor + '22;color:' + cor + ';">' +
                                escHtml(r.nome.charAt(0).toUpperCase()) +
                            '</div>' +
                            '<span class="resp-card-nome" style="color:' + cor + ';">' + escHtml(r.nome) + '</span>' +
                        '</div>' +
                        '<div class="resp-card-actions">' +
                            '<button class="btn btn-sm btn-outline-warning editarResponsavel px-2" ' +
                                'data-id="' + r.id + '" data-nome="' + escHtml(r.nome) + '" data-cor="' + cor + '" title="Editar">' +
                                '<i class="bi bi-pencil-fill"></i></button>' +
                            '<button class="btn btn-sm btn-outline-danger excluirResponsavel px-2" ' +
                                'data-id="' + r.id + '" data-nome="' + escHtml(r.nome) + '" title="Remover">' +
                                '<i class="bi bi-trash-fill"></i></button>' +
                        '</div>' +
                    '</div></div>';
            });
            $('#listaResponsaveis').html(html);
        }
    });
}

// ── CONTAS FIXAS ────────────────────────────────────────────────
(function () {
    var CF_CORES = ['#3B82F6','#8B5CF6','#EC4899','#EF4444','#F97316','#F59E0B','#22C55E','#10B981','#6B7280'];

    $('#cfCorSwatches').html(CF_CORES.map(function (c) {
        return '<button type="button" class="cor-swatch cf-cor-swatch" data-cor="' + c +
               '" style="background:' + c + ';"></button>';
    }).join(''));

    $(document).on('click', '.cf-cor-swatch', function () {
        $('.cf-cor-swatch').removeClass('selecionado');
        $(this).addClass('selecionado');
        $('#cfCor').val($(this).data('cor'));
    });

    $('#adicionarContaFixa').click(function () {
        $('#cfId').val(0);
        $('#cfNome').val('');
        $('#cfValor').val('');
        $('#cfDia').val('');
        $('#cfCor').val('#3B82F6');
        $('.cf-cor-swatch').removeClass('selecionado');
        $('.cf-cor-swatch[data-cor="#3B82F6"]').addClass('selecionado');
        $('#modalCFTitulo').text('Nova Conta Fixa');
        $('#modalContaFixa').modal('show');
    });

    $(document).on('click', '.editarContaFixa', function () {
        var id  = $(this).data('id');
        var cf  = window.contasFixasArray && window.contasFixasArray[id];
        if (!cf) return;
        var cor = cf.cor || '#3B82F6';
        $('#cfId').val(id);
        $('#cfNome').val(cf.nome);
        $('#cfValor').val(parseFloat(cf.valor).toLocaleString('pt-BR', { minimumFractionDigits: 2 }));
        $('#cfDia').val(cf.dia_vencimento);
        $('#cfCor').val(cor);
        $('.cf-cor-swatch').removeClass('selecionado');
        $('.cf-cor-swatch[data-cor="' + cor + '"]').addClass('selecionado');
        $('#modalCFTitulo').text(cf.nome);
        $('#modalContaFixa').modal('show');
    });

    $('#salvarContaFixa').click(function () {
        var id   = parseInt($('#cfId').val());
        var nome = $('#cfNome').val().trim();
        var valor = $('#cfValor').val();
        var dia  = parseInt($('#cfDia').val());
        var cor  = $('#cfCor').val() || '#3B82F6';
        if (!nome || !valor || !dia) { toastr.warning('Preencha todos os campos!'); return; }
        $.ajax({
            type: 'POST', url: App.ctrl.contasFixas,
            data: { acao: id > 0 ? 'editar' : 'adicionar', id: id, nome: nome, valor: valor, dia_vencimento: dia, cor: cor },
            dataType: 'json',
            success: function (ok) {
                if (ok) {
                    toastr.success(id > 0 ? 'Conta atualizada!' : 'Conta criada!');
                    $('#modalContaFixa').modal('hide');
                    buscaContasFixas();
                } else { toastr.error('Erro ao salvar!'); }
            },
            error: function () { toastr.error('Erro ao salvar!'); }
        });
    });

    $(document).on('click', '.toggleContaFixa', function () {
        var id    = $(this).data('id');
        var ativo = $(this).data('ativo') == 1;
        $.ajax({
            type: 'POST', url: App.ctrl.contasFixas,
            data: { acao: 'toggleAtivo', id: id }, dataType: 'json',
            success: function () {
                toastr.success(ativo ? 'Conta desativada!' : 'Conta ativada!');
                buscaContasFixas();
            }
        });
    });

    $(document).on('click', '.excluirContaFixa', function () {
        var id   = $(this).data('id');
        var nome = $(this).data('nome');
        Swal.fire({
            title: 'Remover "' + nome + '"?',
            text: 'O histórico de pagamentos também será removido.',
            icon: 'warning', showCancelButton: true,
            confirmButtonColor: '#EF4444', cancelButtonColor: '#6B7280',
            confirmButtonText: 'Sim, remover', cancelButtonText: 'Cancelar'
        }).then(function (r) {
            if (r.isConfirmed) {
                $.ajax({
                    type: 'POST', url: App.ctrl.contasFixas,
                    data: { acao: 'excluir', id: id }, dataType: 'json',
                    success: function (ok) {
                        if (ok) { toastr.success('Removida!'); buscaContasFixas(); }
                        else    { toastr.error('Erro ao remover!'); }
                    }
                });
            }
        });
    });
})();

function buscaContasFixas() {
    $('#listaContasFixas').html('<div class="text-center py-4"><div class="spinner-border spinner-border-sm" style="color:var(--cor-azul);" role="status"></div></div>');
    $.ajax({
        type: 'POST', url: App.ctrl.contasFixas,
        data: { acao: 'listar' }, dataType: 'json',
        success: function (data) {
            window.contasFixasArray = {};
            if (!data || !data.length) {
                $('#listaContasFixas').html('<div class="text-center py-4" style="color:var(--cor-texto-off);">Nenhuma conta fixa cadastrada.</div>');
                return;
            }
            var html = '<div class="d-flex flex-column gap-2">';
            $.each(data, function (_, cf) {
                window.contasFixasArray[cf.id] = cf;
                var cor    = cf.cor || '#3B82F6';
                var ativo  = cf.ativo == 1;
                var valor  = parseFloat(cf.valor).toLocaleString('pt-BR', { minimumFractionDigits: 2 });
                var toggleBtn = ativo
                    ? '<button class="btn btn-sm btn-outline-warning toggleContaFixa px-2" data-id="' + cf.id + '" data-ativo="1" title="Desativar"><i class="bi bi-pause-fill"></i></button>'
                    : '<button class="btn btn-sm btn-outline-success toggleContaFixa px-2" data-id="' + cf.id + '" data-ativo="0" title="Ativar"><i class="bi bi-play-fill"></i></button>';
                html +=
                    '<div class="cfi-row" style="border-left-color:' + cor + ';' + (!ativo ? 'opacity:.5;' : '') + '">' +
                        '<div class="cfi-left">' +
                            '<span class="cfi-dot" style="background:' + cor + ';"></span>' +
                            '<div>' +
                                '<div class="cfi-nome">' + escHtml(cf.nome) + '</div>' +
                                '<div class="cfi-detalhe"><i class="bi bi-calendar3 me-1"></i>Vence dia ' + cf.dia_vencimento + '</div>' +
                            '</div>' +
                        '</div>' +
                        '<div class="cfi-center">' +
                            (ativo ? '<span class="cfi-badge aberto">Ativa</span>' : '<span class="cfi-badge" style="background:#37373755;color:#6B7280;border:1px solid #37373788;">Inativa</span>') +
                        '</div>' +
                        '<div class="cfi-right">' +
                            '<span class="cfi-valor">R$ ' + valor + '</span>' +
                            '<div class="d-flex gap-1">' +
                                '<button class="btn btn-sm btn-outline-warning editarContaFixa px-2" data-id="' + cf.id + '" title="Editar"><i class="bi bi-pencil-fill"></i></button>' +
                                toggleBtn +
                                '<button class="btn btn-sm btn-outline-danger excluirContaFixa px-2" data-id="' + cf.id + '" data-nome="' + escHtml(cf.nome) + '" title="Remover"><i class="bi bi-trash-fill"></i></button>' +
                            '</div>' +
                        '</div>' +
                    '</div>';
            });
            html += '</div>';
            $('#listaContasFixas').html(html);

            document.querySelectorAll('.cf-real').forEach(function (el) { bancInput(el, el.value); });
        },
        error: function (xhr) {
            $('#listaContasFixas').html('<div class="alert alert-danger">Erro: ' + xhr.responseText + '</div>');
        }
    });
}

// ── INIT ────────────────────────────────────────────────────────
(function () {
    var params = new URLSearchParams(window.location.search);
    var tab = params.get('tab') || 'Categorias';

    $('.gtab-btn').removeClass('active');
    $('.gtab-btn[data-tab="' + tab + '"]').addClass('active');
    $('.tab-section').hide();
    $('#tab' + tab).show();

    if (tab === 'Categorias')   buscaCategorias();
    if (tab === 'Recorrentes')  buscaRecorrentes();
    if (tab === 'Cartoes')      buscaCartoes();
    if (tab === 'ContasFixas')  buscaContasFixas();
    if (tab === 'Responsaveis') buscaResponsaveis();
    if (tab === 'Conta')        { buscaUsuarios(); carregaPerfil(); }
})();

carregarResponsaveis();

document.querySelectorAll('.real').forEach(function (el) { bancInput(el, el.value); });

var bancValorGest = bancInput(document.getElementById('valor'));

// ── Perfil ──────────────────────────────────────────────────────
function carregaPerfil() {
    $.ajax({
        type: 'POST', url: App.ctrl.usuarios,
        data: { acao: 'meu_perfil' }, dataType: 'json',
        success: function (u) {
            $('#perfilNome').val(u.nome || '');
            $('#perfilEmail').val(u.email || '');
            renderAvatar(u.foto, u.nome);
            // Marco inicial (mes_inicio_controle vem como YYYY-MM-DD)
            var marco = u.mes_inicio_controle ? String(u.mes_inicio_controle).substring(0, 7) : '';
            $('#marcoInput').val(marco);
            renderMarcoStatus(marco);
        }
    });
}

function renderMarcoStatus(marco) {
    if (!marco) {
        $('#marcoStatus').html('<i class="bi bi-info-circle me-1"></i>Sem marco definido — todos os dados são contados.');
        return;
    }
    var p = marco.split('-');
    var nomes = ['','Janeiro','Fevereiro','Março','Abril','Maio','Junho','Julho','Agosto','Setembro','Outubro','Novembro','Dezembro'];
    $('#marcoStatus').html('<i class="bi bi-flag-fill me-1" style="color:var(--cor-azul);"></i>Contando a partir de <strong>' +
        nomes[parseInt(p[1])] + '/' + p[0] + '</strong>. Meses anteriores aparecem zerados.');
}

$('#btnSalvarMarco').click(function () {
    var marco = $('#marcoInput').val();
    if (!marco) { toastr.warning('Selecione um mês.'); return; }
    $.ajax({
        type: 'POST', url: App.ctrl.usuarios,
        data: { acao: 'salvar_marco', marco: marco }, dataType: 'json',
        success: function (r) {
            toastr.success('Início do controle definido!');
            App.marcoInicio = r.marco || '';
            renderMarcoStatus(marco);
        },
        error: function () { toastr.error('Erro ao salvar.'); }
    });
});

$('#btnLimparMarco').click(function () {
    $.ajax({
        type: 'POST', url: App.ctrl.usuarios,
        data: { acao: 'salvar_marco', marco: '' }, dataType: 'json',
        success: function () {
            toastr.success('Marco removido.');
            App.marcoInicio = '';
            $('#marcoInput').val('');
            renderMarcoStatus('');
        },
        error: function () { toastr.error('Erro ao remover.'); }
    });
});

function renderAvatar(foto, nome) {
    var $el = $('#avatarDisplay');
    if (foto) {
        $el.html('<img src="' + App.base + '/src/img/avatars/' + foto + '" alt="avatar">');
    } else {
        var inicial = (nome || '?').charAt(0).toUpperCase();
        $el.html('<span>' + inicial + '</span>');
    }
}

$('#btnSalvarPerfil').click(function () {
    var nome  = $('#perfilNome').val().trim();
    var email = $('#perfilEmail').val().trim();
    if (!nome || !email) { toastr.warning('Preencha nome e e-mail!'); return; }
    $.ajax({
        type: 'POST', url: App.ctrl.usuarios,
        data: { acao: 'atualizar_perfil', nome: nome, email: email }, dataType: 'json',
        success: function (r) {
            if (r && r.ok) {
                toastr.success('Perfil atualizado!');
                // Atualiza nome exibido na navbar sem reload
                $('.nav-sky-user span').first().text(r.nome);
            } else {
                toastr.error((r && r.erro) || 'Erro ao salvar.');
            }
        },
        error: function (xhr) {
            toastr.error((xhr.responseJSON && xhr.responseJSON.erro) || 'Erro ao salvar.');
        }
    });
});

$('#inputFoto').on('change', function () {
    var file = this.files[0];
    if (!file) return;

    // Preview imediato
    var reader = new FileReader();
    reader.onload = function (e) {
        $('#avatarDisplay').html('<img src="' + e.target.result + '" alt="avatar">');
    };
    reader.readAsDataURL(file);

    // Upload
    var fd = new FormData();
    fd.append('acao', 'upload_foto');
    fd.append('foto', file);
    $.ajax({
        type: 'POST', url: App.ctrl.usuarios,
        data: fd, processData: false, contentType: false, dataType: 'json',
        success: function (r) {
            if (r && r.ok) {
                toastr.success('Foto atualizada!');
                // Atualiza avatar na navbar
                renderAvatarNavbar(r.foto);
            } else {
                toastr.error((r && r.erro) || 'Erro no upload.');
            }
        },
        error: function (xhr) {
            toastr.error((xhr.responseJSON && xhr.responseJSON.erro) || 'Erro no upload.');
        }
    });
});

function renderAvatarNavbar(foto) {
    var $nav = $('.nav-sky-user .nav-avatar');
    if ($nav.length) {
        $nav.html('<img src="' + App.base + '/src/img/avatars/' + foto + '" alt="avatar" class="nav-avatar-img">');
    }
}

// ── Usuários ────────────────────────────────────────────────────
function buscaUsuarios() {
    $.ajax({
        type: 'POST', url: App.ctrl.usuarios,
        data: { acao: 'listar' }, dataType: 'json',
        success: function (data) {
            if (!data || !data.length) {
                $('#listaUsuarios').html('<p style="color:var(--cor-texto-off);font-size:0.86rem;">Nenhum usuário.</p>');
                return;
            }
            var html = '<table class="table table-dark table-sm table-hover mb-0" style="font-size:0.84rem;">' +
                       '<thead><tr><th>Nome</th><th>E-mail</th><th>Último login</th><th></th></tr></thead><tbody>';
            $.each(data, function (_, u) {
                var login = u.ultimo_login ? moment(u.ultimo_login).format('DD/MM/YY HH:mm') : '—';
                html += '<tr>' +
                    '<td>' + escHtml(u.nome) + '</td>' +
                    '<td style="color:var(--cor-texto-off);">' + escHtml(u.email) + '</td>' +
                    '<td style="color:var(--cor-texto-off);">' + login + '</td>' +
                    '<td class="text-end">' +
                        '<button class="btn btn-sm btn-outline-danger py-0 px-2 btnRemoverUsuario" data-id="' + u.id + '" data-nome="' + escHtml(u.nome) + '">' +
                        '<i class="bi bi-trash3"></i></button>' +
                    '</td>' +
                '</tr>';
            });
            html += '</tbody></table>';
            $('#listaUsuarios').html(html);
        }
    });
}

$('#btnNovoUsuario').click(function () {
    $('#novoUsuNome, #novoUsuEmail, #novoUsuSenha').val('');
    $('#modalNovoUsuario').modal('show');
});

$('#salvarNovoUsuario').click(function () {
    var nome  = $('#novoUsuNome').val().trim();
    var email = $('#novoUsuEmail').val().trim();
    var senha = $('#novoUsuSenha').val();
    if (!nome || !email || !senha) { toastr.warning('Preencha todos os campos!'); return; }
    $.ajax({
        type: 'POST', url: App.ctrl.usuarios,
        data: { acao: 'adicionar', nome: nome, email: email, senha: senha },
        dataType: 'json',
        success: function (r) {
            if (r && r.ok) {
                toastr.success('Usuário criado!');
                $('#modalNovoUsuario').modal('hide');
                buscaUsuarios();
            } else {
                toastr.error((r && r.erro) || 'Erro ao criar usuário.');
            }
        },
        error: function (xhr) {
            var r = xhr.responseJSON;
            toastr.error((r && r.erro) || 'Erro ao criar usuário.');
        }
    });
});

$(document).on('click', '.btnRemoverUsuario', function () {
    var id   = $(this).data('id');
    var nome = $(this).data('nome');
    Swal.fire({
        title: 'Remover ' + nome + '?',
        text: 'O usuário perderá o acesso ao sistema.',
        icon: 'warning', showCancelButton: true,
        confirmButtonColor: '#EF4444', cancelButtonColor: '#6B7280',
        confirmButtonText: 'Remover', cancelButtonText: 'Cancelar'
    }).then(function (r) {
        if (r.isConfirmed) {
            $.ajax({
                type: 'POST', url: App.ctrl.usuarios,
                data: { acao: 'remover', id: id }, dataType: 'json',
                success: function () { toastr.success('Usuário removido.'); buscaUsuarios(); },
                error: function (xhr) {
                    var r = xhr.responseJSON;
                    toastr.error((r && r.erro) || 'Erro ao remover.');
                }
            });
        }
    });
});

// ── Reset de dados ──────────────────────────────────────────────
$('#btnResetDados').click(function () {
    Swal.fire({
        title: 'Apagar todos os dados?',
        html: 'Esta ação é <strong>irreversível</strong>.<br>Todos os gastos, cartões, categorias e demais dados serão excluídos.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#EF4444',
        cancelButtonColor: '#6B7280',
        confirmButtonText: 'Sim, continuar',
        cancelButtonText: 'Cancelar'
    }).then(function (r1) {
        if (!r1.isConfirmed) return;
        Swal.fire({
            title: 'Tem certeza absoluta?',
            input: 'text',
            inputPlaceholder: 'Digite CONFIRMAR para prosseguir',
            inputAttributes: { autocomplete: 'off' },
            icon: 'error',
            showCancelButton: true,
            confirmButtonColor: '#EF4444',
            cancelButtonColor: '#6B7280',
            confirmButtonText: 'Apagar tudo',
            cancelButtonText: 'Cancelar',
            preConfirm: function (val) {
                if (val !== 'CONFIRMAR') {
                    Swal.showValidationMessage('Digite exatamente: CONFIRMAR');
                }
            }
        }).then(function (r2) {
            if (!r2.isConfirmed) return;
            $.ajax({
                type: 'POST', url: App.ctrl.usuarios,
                data: { acao: 'reset_dados' }, dataType: 'json',
                success: function (r) {
                    if (r && r.ok) {
                        Swal.fire({
                            title: 'Dados apagados!',
                            text: 'O sistema foi resetado com sucesso.',
                            icon: 'success',
                            confirmButtonColor: '#3B82F6'
                        }).then(function () { window.location.href = App.base + '/index.php'; });
                    } else {
                        toastr.error((r && r.erro) || 'Erro ao apagar dados.');
                    }
                },
                error: function (xhr) {
                    toastr.error((xhr.responseJSON && xhr.responseJSON.erro) || 'Erro ao apagar dados.');
                }
            });
        });
    });
});

// ── Trocar senha ────────────────────────────────────────────────
$('#btnTrocarSenha').click(function () {
    var atual   = $('#senhaAtual').val();
    var nova    = $('#novaSenha').val();
    var confirma = $('#confirmaSenha').val();
    if (!atual || !nova || !confirma) { toastr.warning('Preencha todos os campos!'); return; }
    $.ajax({
        type: 'POST', url: App.ctrl.usuarios,
        data: { acao: 'trocar_senha', senha_atual: atual, nova_senha: nova, confirma: confirma },
        dataType: 'json',
        success: function (r) {
            if (r && r.ok) {
                toastr.success('Senha atualizada!');
                $('#senhaAtual, #novaSenha, #confirmaSenha').val('');
            } else {
                toastr.error((r && r.erro) || 'Erro ao atualizar senha.');
            }
        },
        error: function (xhr) {
            var r = xhr.responseJSON;
            toastr.error((r && r.erro) || 'Erro ao atualizar senha.');
        }
    });
});
</script>

<style>
/* ── Tabs ── */
.gerenciar-tabs {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
}
.gtab-btn {
    background: var(--cor-painel);
    color: var(--cor-texto-off);
    border: 1px solid var(--cor-borda);
    border-radius: var(--radius-md);
    padding: 0.5rem 1.25rem;
    font-size: 0.88rem;
    font-weight: 500;
    cursor: pointer;
    transition: background var(--trans), color var(--trans), border-color var(--trans);
    display: flex;
    align-items: center;
    gap: 6px;
}
.gtab-btn:hover {
    background: var(--cor-input);
    color: var(--cor-texto);
    border-color: var(--cor-azul);
}
.gtab-btn.active {
    background: var(--cor-azul);
    color: #fff;
    border-color: var(--cor-azul);
}

/* ── Cartão card ── */
.crt-card {
    --crt-cor: #3B82F6;
    background: linear-gradient(135deg, color-mix(in srgb, var(--crt-cor) 22%, var(--cor-painel)) 0%, var(--cor-painel) 70%);
    border: 1px solid color-mix(in srgb, var(--crt-cor) 35%, transparent);
    border-radius: var(--radius-lg);
    padding: 1.1rem 1.2rem 1rem;
    display: flex;
    flex-direction: column;
    gap: 6px;
    min-height: 155px;
    position: relative;
    transition: box-shadow var(--trans), transform var(--trans);
    overflow: hidden;
}
.crt-card::before {
    content: '';
    position: absolute;
    top: -30px; right: -30px;
    width: 110px; height: 110px;
    border-radius: 50%;
    background: color-mix(in srgb, var(--crt-cor) 15%, transparent);
    pointer-events: none;
}
.crt-card:hover { box-shadow: var(--sombra-md); transform: translateY(-3px); }

.crt-card-top {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 4px;
}
.crt-chip {
    width: 32px; height: 32px;
    background: color-mix(in srgb, var(--crt-cor) 30%, transparent);
    border: 1px solid color-mix(in srgb, var(--crt-cor) 50%, transparent);
    border-radius: 6px;
    display: flex; align-items: center; justify-content: center;
    color: var(--crt-cor);
    font-size: 1rem;
}
.crt-card-actions {
    display: flex;
    gap: 6px;
    opacity: 0;
    transition: opacity var(--trans);
}
.crt-card:hover .crt-card-actions { opacity: 1; }
.crt-action-btn {
    width: 28px; height: 28px;
    border: none;
    border-radius: var(--radius-sm);
    background: rgba(255,255,255,0.08);
    color: var(--cor-texto-sec);
    cursor: pointer;
    display: flex; align-items: center; justify-content: center;
    font-size: 0.75rem;
    transition: background var(--trans), color var(--trans);
}
.crt-action-btn:hover         { background: rgba(234,179,8,0.2);  color: #EAB308; }
.crt-action-btn.crt-action-del:hover { background: rgba(239,68,68,0.2); color: #EF4444; }

.crt-card-nome {
    font-family: "Bebas Neue", sans-serif;
    font-size: 1.25rem;
    letter-spacing: 1px;
    color: var(--cor-texto);
    line-height: 1;
}
.crt-card-limite {
    font-size: 0.82rem;
    font-weight: 600;
    color: color-mix(in srgb, var(--crt-cor) 90%, white);
}
.crt-card-footer {
    display: flex;
    gap: 1.5rem;
    margin-top: auto;
    padding-top: 8px;
    border-top: 1px solid color-mix(in srgb, var(--crt-cor) 20%, transparent);
}
.crt-card-info { display: flex; flex-direction: column; gap: 1px; }
.crt-label { font-size: 0.65rem; text-transform: uppercase; letter-spacing: 0.06em; color: var(--cor-texto-off); }
.crt-val   { font-size: 0.82rem; font-weight: 600; color: var(--cor-texto); }

/* ── Categoria card ── */
.cat-item-card {
    background: var(--cor-painel);
    border: 1px solid var(--cor-borda);
    border-radius: var(--radius-md);
    padding: 0.65rem 0.85rem;
    display: flex;
    align-items: center;
    gap: 0.75rem;
    transition: box-shadow var(--trans);
}
.cat-item-card:hover { box-shadow: var(--sombra-sm); }
.cat-item-icon {
    width: 34px; height: 34px;
    border-radius: var(--radius-sm);
    display: flex; align-items: center; justify-content: center;
    font-size: 1rem; flex-shrink: 0;
}
.cat-item-nome {
    font-weight: 500;
    font-size: 0.88rem;
    flex: 1; min-width: 0;
    overflow: hidden; text-overflow: ellipsis; white-space: nowrap;
}

/* ── Recorrentes table ── */
#recorrentesTable th { font-size: 0.75rem; }
#recorrentesTable td { vertical-align: middle; }

/* ── Responsáveis ── */
.resp-gerenciar-card {
    background: var(--cor-painel);
    border: 1px solid var(--cor-borda);
    border-radius: var(--radius-md);
    padding: 0.75rem 1rem;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 0.5rem;
    transition: box-shadow var(--trans);
}
.resp-gerenciar-card:hover { box-shadow: var(--sombra-sm); }
.resp-avatar {
    width: 38px; height: 38px;
    border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    font-weight: 700; font-size: 1rem; flex-shrink: 0;
}
.resp-card-nome { font-weight: 600; font-size: 0.9rem; flex: 1; }
.resp-card-actions { display: flex; gap: 4px; flex-shrink: 0; }
</style>

<?php require_once __DIR__ . '/../templates/footer.php'; ?>
