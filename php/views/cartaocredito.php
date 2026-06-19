<?php
require_once __DIR__ . '/../templates/header.php';

$meses = [
    1 => "Janeiro",  2 => "Fevereiro", 3 => "Março",    4 => "Abril",
    5 => "Maio",     6 => "Junho",     7 => "Julho",    8 => "Agosto",
    9 => "Setembro", 10 => "Outubro",  11 => "Novembro", 12 => "Dezembro"
];

$mesAtual = date('n');
?>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>var tipoDespesa = 'credito';</script>

<div class="animate__animated animate__fadeIn">

    <!-- HEADER -->
    <div class="d-flex align-items-center justify-content-between titulo-pagina mb-4">
        <h1 class="titulo mt-2 fs-titulo-pag">
            Cartão de Crédito &nbsp;<i class="bi bi-credit-card-fill titulo-azul"></i>
        </h1>
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

    <!-- CARTÕES SELETORES -->
    <div class="d-flex gap-3 flex-wrap mb-4" id="cartoesRow">
        <div class="text-center py-2" style="color:#6B7280;">
            <div class="spinner-border spinner-border-sm" role="status"></div>
        </div>
    </div>

    <!-- BARRA RESUMO -->
    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
        <div class="d-flex align-items-center gap-3 flex-wrap">
            <div>
                <span class="titulo cor-am me-2 fs-total-label">Total do mês:</span>
                <span class="titulo titulo-azul fs-total-valor" id="totalGeral">—</span>
            </div>
            <div id="faturaStatusWrapper" style="display:none;">
                <button class="btn btn-outline-secondary btn-sm" id="btnFaturaPaga" data-pago="0">
                    <i class="bi bi-circle me-1"></i>Marcar fatura como paga
                </button>
            </div>
        </div>
        <div class="d-flex gap-2">
            <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modalAdiciona">
                <i class="bi bi-plus-lg"></i> Adicionar Despesa
            </button>
        </div>
    </div>

    <!-- BARRA DE SOMA -->
    <div id="barraSelecao" style="display:none;" class="barra-selecao mb-3">
        <i class="bi bi-calculator-fill me-2" style="color:var(--cor-azul);"></i>
        <span id="selCount" class="me-1"></span>
        <span style="color:var(--cor-texto-off);">·</span>
        <span class="ms-1 me-1" style="color:var(--cor-texto-off);">Total:</span>
        <strong id="selTotal" class="titulo-azul"></strong>
        <button class="btn btn-sm ms-3 py-0 px-2" id="limparSelecao"
            style="background:transparent;border:1px solid var(--cor-borda);color:var(--cor-texto-off);">
            <i class="bi bi-x"></i>
        </button>
    </div>

    <!-- GRÁFICO POR CATEGORIA -->
    <div id="faturaGraficoRow" class="row g-3 mb-4" style="display:none;">
        <div class="col-12 col-md-5 col-lg-4">
            <div class="painel text-center" style="position:relative;padding:1rem;">
                <div style="max-width:200px;margin:0 auto;position:relative;">
                    <canvas id="faturaChart"></canvas>
                    <div id="faturaChartCentro" style="position:absolute;top:50%;left:50%;transform:translate(-50%,-50%);pointer-events:none;text-align:center;line-height:1.2;">
                        <div style="font-size:0.7rem;color:var(--cor-texto-off);">Total</div>
                        <div id="faturaChartTotal" style="font-size:0.95rem;font-weight:700;color:var(--cor-azul);"></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-7 col-lg-8">
            <div class="painel h-100" style="overflow-y:auto;max-height:260px;">
                <div id="faturaChartLegenda"></div>
            </div>
        </div>
    </div>

    <!-- FATURAS -->
    <div id="faturasDiv"></div>

    <input type="hidden" id="cartaoAtual" value="">

</div>

<!-- MODAL -->
<?php include '../templates/modalCadastra.php'; ?>
<?php include '../templates/modalCategoria.php'; ?>

<!-- MODAL EDIÇÃO SIMPLES (parcelados) -->
<div class="modal fade" id="modalEditaSimples" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header" style="background:#2C2C44;border-bottom:1px solid #3F3F46;">
                <h5 class="modal-title" style="color:#F0F0F5;">
                    <i class="bi bi-pencil-fill titulo-azul me-2"></i>Editar Despesa
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" style="padding:1.5rem;">
                <div class="mb-3">
                    <label class="form-label">Descrição</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-pencil-fill"></i></span>
                        <input type="text" class="form-control" id="esDescricao">
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Categoria</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-tag-fill"></i></span>
                        <div class="form-control p-0 cat-sel-container dropdown" id="esCatSelWrapper">
                            <button type="button" id="esCatSelBtn"
                                    class="btn cat-sel-btn w-100 h-100 d-flex align-items-center gap-2"
                                    data-bs-toggle="dropdown" aria-expanded="false">
                                <span class="es-cat-preview text-muted">Selecione</span>
                                <i class="bi bi-chevron-down ms-auto" style="font-size:0.75rem;opacity:0.6;"></i>
                            </button>
                            <ul class="dropdown-menu cat-sel-menu" id="esCatSelMenu"></ul>
                        </div>
                        <input type="hidden" id="esCategoria">
                    </div>
                </div>
            </div>
            <div class="modal-footer" style="border-top:1px solid #3F3F46;">
                <input type="hidden" id="esGastoId">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Fechar</button>
                <button type="button" class="btn btn-warning" id="btnSalvarSimples">
                    Salvar <i class="bi bi-pencil-fill"></i>
                </button>
            </div>
        </div>
    </div>
</div>

<script>

    $(document).ready(function () {

        buscaCartoes();
        buscaCategorias();

        var _modoEditCC = false;
        var _pendingRepetirCC = null;
        var _faturaChart = null;

        // ─── MODAL ───────────────────────────────────────────────────────────────
        $('#modalAdiciona').on('show.bs.modal', function () {
            if (_modoEditCC) return;
            $('#metodoWrapper').hide();
            $('#cartaoWrapper').show();
            $('#cartaoSelect').hide();
            $('#parceladoWrapper').show();
            $('#recorrenteWrapper').show();
            $('.border-parcelado').hide();
            $('#parcelado').prop('checked', false);
            $('#recorrente').prop('checked', false);
            $('#cartao').val('');
            $('.cartao-mini-modal').removeClass('selecionado');
            renderCartoesMiniModal();
            resetCatSelect();
        });

        $('#modalAdiciona').on('hidden.bs.modal', function () { _modoEditCC = false; _pendingRepetirCC = null; limpaErrosModalCC(); });

        $('#modalAdiciona').on('shown.bs.modal', function () {
            if (!_pendingRepetirCC) return;
            var d = _pendingRepetirCC;
            _pendingRepetirCC = null;
            $('#descricao').val(d.descricao);
            valorCleaveCC.setRawValue(parseFloat(d.valor));
            var hoje = new Date();
            var pad = function(n){ return String(n).padStart(2,'0'); };
            $('#data').val(hoje.getFullYear() + '-' + pad(hoje.getMonth()+1) + '-' + pad(hoje.getDate()));
            setCatSelecionadaCC(d.categoria);
            $('.cartao-mini-modal').removeClass('selecionado');
            $('.cartao-mini-modal[data-id="' + d.cartao + '"]').addClass('selecionado');
            $('#cartao').val(d.cartao);
            $('#adicionarDespesa').show();
            $('#editarDespesa').hide();
        });

        $(document).on('click', '.cartao-mini-modal', function () {
            $('.cartao-mini-modal').removeClass('selecionado');
            $(this).addClass('selecionado');
            $('#cartao').val($(this).data('id'));
        });

        // ─── SELETOR DE MÊS / ANO ────────────────────────────────────────────
        function getAno() { return parseInt($('#anoDisplay').text()); }

        $('.botaoEsquerda').click(function () {
            let val = parseInt($('#mes').val());
            if (val > 1) { $('#mes').val(val - 1).trigger('change'); }
            else { $('#mes').val(12); $('#anoDisplay').text(getAno() - 1); buscaFatura(12); }
        });

        $('.botaoDireita').click(function () {
            let val = parseInt($('#mes').val());
            if (val < 12) { $('#mes').val(val + 1).trigger('change'); }
            else { $('#mes').val(1); $('#anoDisplay').text(getAno() + 1); buscaFatura(1); }
        });

        $('#anoEsquerda').click(function () { $('#anoDisplay').text(getAno() - 1); buscaFatura($('#mes').val()); });
        $('#anoDireita').click(function ()  { $('#anoDisplay').text(getAno() + 1); buscaFatura($('#mes').val()); });

        $('#mes').change(function () {
            buscaFatura($(this).val());
        });

        // ─── SELEÇÃO DE CARTÃO ────────────────────────────────────────────────
        $(document).on('click', '.cartao-mini', function () {
            $('.cartao-mini').removeClass('selecionado');
            $(this).addClass('selecionado');
            $('#cartaoAtual').val($(this).data('id') ?? '');

            // Recalcula mês baseado no vencimento do cartão selecionado
            const venc = $(this).data('vencimento');
            // Reseta para mês atual antes de recalcular
            $('#mes').val(<?= date('n') ?>);
            $('#anoDisplay').text(<?= date('Y') ?>);
            if (venc) {
                ajustaMesPorVencimento(venc);
            } else {
                // "Todos" — usa menor vencimento entre os cartões
                ajustaMesPorVencimento(vencimentoMinimo(window.cartoesArray || {}));
            }

            buscaFatura($('#mes').val());
        });

        // ─── SELEÇÃO PARA SOMA ───────────────────────────────────────────────
        $(document).on('click', '.linha-clicavel', function (e) {
            if ($(e.target).closest('button, input').length) return;
            $(this).toggleClass('linha-selecionada');
            atualizaBarraSelecao();
        });

        $('#limparSelecao').on('click', function () {
            $('.linha-selecionada').removeClass('linha-selecionada');
            $('#barraSelecao').hide();
        });

        function atualizaBarraSelecao() {
            var $sel = $('.linha-selecionada');
            if (!$sel.length) { $('#barraSelecao').hide(); return; }
            var total = 0;
            $sel.each(function () {
                var v = String($(this).data('valor')).replace(/\./g, '').replace(',', '.');
                total += parseFloat(v) || 0;
            });
            var fmt = total.toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            $('#selCount').text($sel.length + ' selecionada(s)');
            $('#selTotal').text('R$ ' + fmt);
            $('#barraSelecao').show();
        }

        // ─── REMOVER POR LINHA ────────────────────────────────────────────────
        $(document).on('click', '.btn-remover-gasto', function () {
            var $btn      = $(this);
            var id        = $btn.data('id');
            var tipo      = $btn.data('tipo');
            var parcelado = $btn.data('parcelado');

            var msg = tipo === 'RECORRENTE'
                ? 'O recorrente será inativado e não gerará mais lançamentos futuros.'
                : (parcelado === 'S' ? 'Todas as parcelas serão removidas.' : '');

            Swal.fire({
                title: 'Remover despesa?',
                text: msg || undefined,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#EF4444',
                cancelButtonColor: '#6B7280',
                confirmButtonText: 'Remover',
                cancelButtonText: 'Cancelar'
            }).then(function (result) {
                if (!result.isConfirmed) return;

                var acao = tipo === 'RECORRENTE' ? 'inativaRecorrentes' : 'remover';
                var payload = tipo === 'RECORRENTE'
                    ? { acao: acao, id: id }
                    : { acao: acao, ids: [{ id: id, parcelado: parcelado }], tipo: 'credito' };

                $.ajax({
                    type: 'POST',
                    url: '../controllers/GastosController.php',
                    data: payload,
                    dataType: 'json',
                    success: function () {
                        toastr.success('Despesa removida!');
                        buscaFatura($('#mes').val());
                    },
                    error: function () { toastr.error('Erro ao remover!'); }
                });
            });
        });

        // ─── PARCELADO / RECORRENTE TOGGLE ───────────────────────────────────
        $('#parcelado').change(function () {
            if ($(this).prop('checked')) {
                $('.border-parcelado').slideDown();
                $('#recorrente').prop('checked', false);
            } else {
                $('.border-parcelado').slideUp();
            }
        });

        $('#recorrente').change(function () {
            if ($(this).prop('checked')) {
                $('#parcelado').prop('checked', false);
                $('.border-parcelado').slideUp();
            }
        });

        function limpaErrosModalCC() {
            $('#descricao, #valor, #data').removeClass('is-invalid');
            $('#catSelWrapper, #cartaoSelectorModal, #num_parcelas').removeClass('borda-erro');
            $('#num_parcelas').removeClass('is-invalid');
        }

        function validaFormCredito() {
            limpaErrosModalCC();
            var erros = [];
            const isRecorrente = $('#recorrente').is(':checked');
            const isParcelado  = $('#parcelado').is(':checked');

            if (!$('#descricao').val().trim()) {
                $('#descricao').addClass('is-invalid');
                erros.push('Descrição');
            }
            var vr = parseFloat($('#valor').val().replace(/R\$\s?/g,'').replace(/\./g,'').replace(',','.'));
            if (!vr || vr <= 0) {
                $('#valor').addClass('is-invalid');
                erros.push('Valor');
            }
            if (!$('#categoria').val()) {
                $('#catSelWrapper').addClass('borda-erro');
                erros.push('Categoria');
            }
            if (!$('#cartao').val()) {
                $('#cartaoSelectorModal').addClass('borda-erro');
                erros.push('Cartão');
            }
            if (!$('#data').val()) {
                $('#data').addClass('is-invalid');
                erros.push('Data');
            }
            if (isParcelado && !isRecorrente) {
                var np = parseInt($('#num_parcelas').val());
                if (!np || np < 2) {
                    $('#num_parcelas').addClass('is-invalid');
                    erros.push('Nº de parcelas (mínimo 2)');
                }
            }

            if (erros.length) {
                toastr.warning('Preencha: ' + erros.join(', '));
                return false;
            }
            return true;
        }

        // ─── ADICIONAR DESPESA ────────────────────────────────────────────────
        $('#adicionarDespesa').click(function () {
            if (!validaFormCredito()) return;
            const isRecorrente = $('#recorrente').is(':checked');

            const payload = {
                acao:         'adicionar',
                descricao:    $('#descricao').val(),
                valor:        $('#valor').val(),
                categoria:    $('#categoria').val(),
                metodo:       'Crédito',
                cartao:       $('#cartao').val(),
                data:         $('#data').val(),
                responsavel:  $('#responsavel').val() || '',
            };

            if (isRecorrente) {
                payload.tipo       = 'recorrente';
                payload.recorrente = 'S';
            } else {
                payload.tipo        = 'credito';
                payload.parcelado   = $('#parcelado').is(':checked') ? 'S' : 'N';
                payload.num_parcelas = $('#num_parcelas').val();
            }

            $.ajax({
                type: 'POST',
                url: '../controllers/GastosController.php',
                data: payload,
                dataType: 'json',
                success: function () {
                    toastr.success('Despesa criada com sucesso!');
                    $('#modalAdiciona').modal('hide');
                    buscaFatura($('#mes').val());
                },
                error: function () { toastr.error('Erro ao criar despesa!'); }
            });
        });

        $(document).on('cat:salva', function () { buscaCategorias(); });

        // ─── FUNÇÕES ──────────────────────────────────────────────────────────

        function renderCartoesMiniModal() {
            let html = '';
            if (window.cartoesArray) {
                $.each(window.cartoesArray, function (id, cartao) {
                    let cor = cartao.cor || '#3B82F6';
                    html += `<div class="cartao-mini-modal" data-id="${cartao.id}" style="--cartao-cor:${cor};">
                        <i class="bi bi-credit-card-fill" style="color:${cor};"></i>
                        ${cartao.nome_cartao}
                    </div>`;
                });
            }
            $('#cartaoSelectorModal').html(html || '<span style="color:#6B7280;font-size:.85rem;">Nenhum cartão cadastrado</span>');
        }

        // Ajusta mês/ano se já passou do vencimento do cartão
        function ajustaMesPorVencimento(vencimentoDia) {
            if (!vencimentoDia) return;
            const diaHoje = new Date().getDate();
            if (diaHoje >= parseInt(vencimentoDia)) {
                let mes = parseInt($('#mes').val()) + 1;
                let ano = parseInt($('#anoDisplay').text());
                if (mes > 12) { mes = 1; ano++; }
                $('#mes').val(mes);
                $('#anoDisplay').text(ano);
            }
        }

        function vencimentoMinimo(cartoesObj) {
            let min = null;
            $.each(cartoesObj, function (_, c) {
                const v = parseInt(c.vencimento_dia);
                if (!isNaN(v) && (min === null || v < min)) min = v;
            });
            return min;
        }

        function buscaCartoes() {
            $.ajax({
                type: 'POST',
                url: '../controllers/CartoesController.php',
                data: { acao: 'busca' },
                dataType: 'json',
                success: function (data) {
                    window.cartoesArray = data;

                    let html = `<div class="cartao-mini selecionado" data-id="" style="--cartao-cor:#9CA3AF;">
                        <i class="bi bi-credit-card-fill" style="color:#9CA3AF;font-size:1.6rem;"></i>
                        <span>Todos</span>
                    </div>`;

                    $.each(data, function (idx, cartao) {
                        let cor = cartao.cor || '#3B82F6';
                        html += `<div class="cartao-mini" data-id="${cartao.id}" data-vencimento="${cartao.vencimento_dia}" style="--cartao-cor:${cor};">
                            <i class="bi bi-credit-card-fill" style="color:${cor};font-size:1.6rem;"></i>
                            <span>${cartao.nome_cartao}</span>
                        </div>`;
                    });

                    $('#cartoesRow').html(html);

                    // Ajusta para próximo mês se já passou do menor vencimento
                    ajustaMesPorVencimento(vencimentoMinimo(data));
                    buscaFatura($('#mes').val());
                },
                error: function () { toastr.error('Erro ao buscar cartões!'); }
            });
        }

        function buscaFatura(mes) {
            $('#faturasDiv').html(
                '<div class="text-center py-5"><div class="spinner-border" role="status" style="color:#3B82F6;"></div></div>'
            );

            $.ajax({
                type: 'POST',
                url: '../controllers/GastosController.php',
                data: { acao: 'buscaFatura', mes: mes, ano: getAno(), cartaoId: $('#cartaoAtual').val() },
                dataType: 'json',
                success: function (data) {
                    if ($.isEmptyObject(data)) {
                        $('#faturasDiv').html(`
                            <div class="painel text-center py-5">
                                <i class="bi bi-inbox" style="font-size:3rem;color:#3F3F46;"></i>
                                <p class="mt-3" style="color:#6B7280;">Nenhum gasto encontrado para este mês.</p>
                            </div>`);
                        $('#totalGeral').text('R$ 0,00');
                        $('#faturaGraficoRow').hide();
                        if (_faturaChart) { _faturaChart.destroy(); _faturaChart = null; }
                        atualizaStatusFatura();
                        return;
                    }

                    var html = '';
                    var totalGeral = 0;

                    $.each(data, function (idCartao, valoresCartao) {
                        let nomeCartao = valoresCartao[0]?.nome_cartao || 'Cartão';
                        let cor = (window.cartoesArray && window.cartoesArray[idCartao])
                            ? (window.cartoesArray[idCartao].cor || '#3B82F6')
                            : '#3B82F6';

                        let totalCartao = valoresCartao.valortotal || '0,00';
                        totalGeral += parseFloat(totalCartao.replace(/\./g, '').replace(',', '.'));

                        html += `
                        <div class="painel mb-4" style="border-left:4px solid ${cor};">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h2 class="titulo m-0 fs-fatura-nome" style="color:${cor};">
                                    <i class="bi bi-credit-card-fill me-2"></i>${nomeCartao}
                                </h2>
                                <span class="titulo fs-fatura-val" style="color:${cor};">R$ ${totalCartao}</span>
                            </div>
                            <div class="table-responsive">
                            <table id="faturaTabela_${idCartao}" class="table table-hover table-centro" style="width:100%;">
                                <thead class="bg-secundary">
                                    <tr>
                                        <th>Produto</th>
                                        <th>Categoria</th>
                                        <th>Parcela</th>
                                        <th>Valor</th>
                                        <th>Data</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>`;

                        $.each(valoresCartao, function (i, gasto) {
                            if (i === 'valortotal') return;

                            let dataExib, infoParcela;
                            if (gasto.tipo === 'NORMAL') {
                                dataExib    = moment(gasto.data_gasto).format('DD/MM/YYYY');
                                infoParcela = (gasto.numero_parcela ?? 1) + '/' + (gasto.parcelas_total ?? 1);
                            } else {
                                dataExib    = '<i class="bi bi-arrow-clockwise" title="Recorrente"></i>';
                                infoParcela = '<i class="bi bi-arrow-clockwise" title="Recorrente"></i>';
                            }

                            var btnEditar = '';
                            if (gasto.tipo === 'NORMAL') {
                                if (gasto.parcelado === 'N') {
                                    btnEditar = `<button class="btn btn-sm btn-outline-secondary btn-editar-gasto-cc py-0 px-1 me-1"
                                        data-id="${gasto.id}"
                                        data-descricao="${gasto.descricao}"
                                        data-valor="${gasto.valor_parcela}"
                                        data-categoria="${gasto.categoria_id || ''}"
                                        data-cartao="${gasto.cartao_id || ''}"
                                        data-data="${gasto.data_gasto || ''}"
                                        title="Editar"><i class="bi bi-pencil-fill" style="font-size:.75rem;"></i></button>
                                    <button class="btn btn-sm btn-outline-secondary btn-repetir-gasto-cc py-0 px-1 me-1"
                                        data-descricao="${gasto.descricao}"
                                        data-valor="${gasto.valor_parcela}"
                                        data-categoria="${gasto.categoria_id || ''}"
                                        data-cartao="${gasto.cartao_id || ''}"
                                        title="Repetir no mês atual"><i class="bi bi-arrow-repeat" style="font-size:.75rem;"></i></button>`;
                                } else {
                                    btnEditar = `<button class="btn btn-sm btn-outline-secondary btn-editar-simples py-0 px-1 me-1"
                                        data-id="${gasto.id}" data-tipo="gasto"
                                        data-descricao="${gasto.descricao}"
                                        data-categoria="${gasto.categoria_id || ''}"
                                        title="Editar descrição/categoria"><i class="bi bi-pencil-fill" style="font-size:.75rem;"></i></button>`;
                                }
                            } else if (gasto.tipo === 'RECORRENTE') {
                                btnEditar = `<button class="btn btn-sm btn-outline-secondary btn-editar-simples py-0 px-1 me-1"
                                    data-id="${gasto.id}" data-tipo="recorrente"
                                    data-descricao="${gasto.descricao}"
                                    data-categoria="${gasto.categoria_id || ''}"
                                    title="Editar nome/categoria"><i class="bi bi-pencil-fill" style="font-size:.75rem;"></i></button>`;
                            }
                            html += `<tr class="linha-clicavel" data-valor="${gasto.valor_parcela}">
                                <td><span class="linha-check"><i class="bi bi-check-circle-fill"></i></span>${gasto.descricao}</td>
                                <td>${catBadgeHtml(gasto.categoria)}</td>
                                <td>${infoParcela}</td>
                                <td>R$ ${gasto.valor_parcela}</td>
                                <td>${dataExib}</td>
                                <td>
                                    <div class="d-flex align-items-center gap-1 justify-content-end">
                                        ${btnEditar}
                                        <button class="btn btn-sm btn-outline-danger btn-remover-gasto py-0 px-1"
                                            data-id="${gasto.id}" data-parcelado="${gasto.parcelado}" data-tipo="${gasto.tipo}"
                                            title="Remover">
                                            <i class="bi bi-trash-fill" style="font-size:.75rem;"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>`;
                        });

                        html += `</tbody></table></div></div>`;
                    });

                    $('#faturasDiv').html(html);
                    $('#totalGeral').text('R$ ' + formatarNumeroBrasileiro(totalGeral));
                    atualizaStatusFatura();
                    renderFaturaChart(data);

                    $.each(data, function (idCartao) {
                        $('#faturaTabela_' + idCartao).DataTable({
                            paging: false,
                            info: false,
                            lengthChange: false,
                            searching: true,
                            ordering: true,
                            language: {
                                search: 'Pesquisar:',
                                zeroRecords: 'Nenhum registro encontrado',
                                emptyTable: 'Nenhum dado disponível'
                            }
                        });
                    });
                },
                error: function () { toastr.error('Erro ao buscar fatura!'); }
            });
        }

        function renderFaturaChart(data) {
            // Agrega valor_parcela por categoria
            var catTotais = {};
            $.each(data, function (idCartao, valoresCartao) {
                $.each(valoresCartao, function (i, gasto) {
                    if (i === 'valortotal') return;
                    var catId  = String(gasto.categoria_id || '');
                    var catObj = window.categoriaMap ? window.categoriaMap[catId] : null;
                    var nome   = catObj ? catObj.nome : (gasto.categoria || 'Outros');
                    var cor    = catObj ? (catObj.cor || '#6B7280') : '#6B7280';
                    var val    = parseFloat(gasto.valor_parcela || 0);
                    if (!catTotais[nome]) catTotais[nome] = { total: 0, cor: cor };
                    catTotais[nome].total += val;
                });
            });

            var nomes  = Object.keys(catTotais);
            if (!nomes.length) { $('#faturaGraficoRow').hide(); return; }

            var valores = nomes.map(function(n){ return catTotais[n].total; });
            var cores   = nomes.map(function(n){ return catTotais[n].cor; });
            var total   = valores.reduce(function(a,b){ return a+b; }, 0);

            if (_faturaChart) { _faturaChart.destroy(); _faturaChart = null; }

            var ctx = document.getElementById('faturaChart');
            _faturaChart = new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: nomes,
                    datasets: [{ data: valores, backgroundColor: cores, borderWidth: 2, borderColor: 'transparent' }]
                },
                options: {
                    cutout: '68%',
                    plugins: { legend: { display: false }, tooltip: {
                        callbacks: {
                            label: function(ctx) {
                                var pct = ((ctx.parsed / total) * 100).toFixed(1);
                                return ' R$ ' + ctx.parsed.toLocaleString('pt-BR', {minimumFractionDigits:2}) + ' (' + pct + '%)';
                            }
                        }
                    }}
                }
            });

            $('#faturaChartTotal').text('R$ ' + total.toLocaleString('pt-BR', {minimumFractionDigits:2,maximumFractionDigits:2}));

            // Legenda manual
            var legHtml = '';
            nomes.forEach(function (nome, i) {
                var pct = ((valores[i] / total) * 100).toFixed(1);
                var vlr = valores[i].toLocaleString('pt-BR', {minimumFractionDigits:2,maximumFractionDigits:2});
                legHtml += '<div class="d-flex align-items-center justify-content-between py-1" style="border-bottom:1px solid var(--cor-borda);font-size:0.82rem;">' +
                    '<div class="d-flex align-items-center gap-2">' +
                    '<span style="width:10px;height:10px;border-radius:50%;background:' + cores[i] + ';flex-shrink:0;display:inline-block;"></span>' +
                    '<span>' + nome + '</span></div>' +
                    '<div class="d-flex align-items-center gap-3">' +
                    '<span style="color:var(--cor-texto-off);">' + pct + '%</span>' +
                    '<span class="fw-600">R$ ' + vlr + '</span>' +
                    '</div></div>';
            });
            $('#faturaChartLegenda').html(legHtml);
            $('#faturaGraficoRow').fadeIn(200);
        }

        function buscaCategorias() {
            $.ajax({
                type: 'POST',
                url: '../controllers/CategoriaController.php',
                data: { acao: 'busca' },
                dataType: 'json',
                success: function (data) { popularCatSelect(data); }
            });
        }

        // ─── STATUS FATURA PAGA ───────────────────────────────────────────────
        function atualizaStatusFatura() {
            var cartaoId = $('#cartaoAtual').val();
            if (!cartaoId) {
                $('#faturaStatusWrapper').hide();
                return;
            }
            $.ajax({
                type: 'POST',
                url: '../controllers/CartoesController.php',
                data: { acao: 'faturaPaga', cartaoId: cartaoId, mes: $('#mes').val(), ano: getAno() },
                dataType: 'json',
                success: function (data) {
                    $('#faturaStatusWrapper').show();
                    var $btn = $('#btnFaturaPaga');
                    if (data.pago) {
                        var dataFmt = data.data_pagamento
                            ? moment(data.data_pagamento).format('DD/MM/YYYY')
                            : '';
                        $btn.removeClass('btn-outline-secondary')
                            .addClass('btn-success')
                            .attr('data-pago', '1')
                            .html('<i class="bi bi-check-circle-fill me-1"></i>Fatura paga' + (dataFmt ? ' em ' + dataFmt : ''));
                    } else {
                        $btn.removeClass('btn-success')
                            .addClass('btn-outline-secondary')
                            .attr('data-pago', '0')
                            .html('<i class="bi bi-circle me-1"></i>Marcar fatura como paga');
                    }
                }
            });
        }

        $('#btnFaturaPaga').click(function () {
            var cartaoId = $('#cartaoAtual').val();
            if (!cartaoId) return;
            var jaPago = $(this).attr('data-pago') === '1' ? 1 : 0;
            var novoPago = jaPago ? 0 : 1;
            $.ajax({
                type: 'POST',
                url: '../controllers/CartoesController.php',
                data: {
                    acao:     'marcarFaturaPaga',
                    cartaoId: cartaoId,
                    mes:      $('#mes').val(),
                    ano:      getAno(),
                    pago:     novoPago,
                    data:     moment().format('YYYY-MM-DD')
                },
                dataType: 'json',
                success: function () {
                    toastr.success(novoPago ? 'Fatura marcada como paga!' : 'Marcação de pagamento removida!');
                    atualizaStatusFatura();
                },
                error: function () { toastr.error('Erro ao atualizar status da fatura!'); }
            });
        });

        function formatarNumeroBrasileiro(n) {
            return n.toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        }

        tippy('#removerSelecionados', {
            content: 'Selecione despesas nas tabelas e clique para remover',
            theme: 'dark', placement: 'top', arrow: true, duration: 300
        });

        // ─── MODAL SIMPLES (parcelados) ───────────────────────────────────────
        function popularEsCatMenu() {
            if (!window.categoriaMap) return;
            var html = '<li><a class="dropdown-item text-muted py-2" href="#" data-id="">Selecione</a></li>';
            $.each(window.categoriaMap, function (id, cat) {
                var cor   = cat.cor || '#6B7280';
                var icone = cat.icone ? '<span class="me-1">' + cat.icone + '</span>' : '';
                html += '<li><a class="dropdown-item es-cat-item py-2" href="#" data-id="' + id + '">' +
                    '<span class="cat-dot me-2" style="background:' + cor + ';flex-shrink:0;"></span>' +
                    icone + '<span style="color:' + cor + ';">' + cat.nome + '</span></a></li>';
            });
            $('#esCatSelMenu').html(html);
        }

        function setEsCat(catId) {
            var id  = String(catId || '');
            var cat = window.categoriaMap ? window.categoriaMap[id] : null;
            $('#esCategoria').val(id);
            if (cat) {
                var cor   = cat.cor || '#6B7280';
                var icone = cat.icone ? '<span class="me-1">' + cat.icone + '</span>' : '';
                $('#esCatSelBtn .es-cat-preview').html(
                    '<span class="cat-dot" style="background:' + cor + ';flex-shrink:0;"></span>' +
                    icone + '<span class="ms-1" style="color:' + cor + ';">' + cat.nome + '</span>'
                );
            } else {
                $('#esCatSelBtn .es-cat-preview').html('<span class="text-muted">Selecione</span>');
            }
        }

        $(document).on('click', '#esCatSelMenu a', function (e) {
            e.preventDefault(); e.stopPropagation();
            setEsCat($(this).data('id'));
            var dd = bootstrap.Dropdown.getInstance(document.getElementById('esCatSelBtn'));
            if (dd) dd.hide();
        });

        var _estipoAtual = 'gasto';

        $(document).on('click', '.btn-editar-simples', function () {
            var $btn = $(this);
            _estipoAtual = $btn.data('tipo') || 'gasto';
            $('#esGastoId').val($btn.data('id'));
            $('#esDescricao').val($btn.data('descricao'));
            popularEsCatMenu();
            setEsCat($btn.data('categoria'));
            bootstrap.Modal.getOrCreateInstance(document.getElementById('modalEditaSimples')).show();
        });

        $('#btnSalvarSimples').on('click', function () {
            var id = $('#esGastoId').val();
            if (!id) return;
            var acao = _estipoAtual === 'recorrente' ? 'editarRecorrenteSimples' : 'editarSimples';
            $(this).prop('disabled', true);
            $.ajax({
                type: 'POST',
                url: '../controllers/GastosController.php',
                data: { acao: acao, id: id, descricao: $('#esDescricao').val(), categoria: $('#esCategoria').val() },
                dataType: 'json',
                success: function () {
                    toastr.success('Despesa atualizada!');
                    bootstrap.Modal.getInstance(document.getElementById('modalEditaSimples')).hide();
                    buscaFatura($('#mes').val());
                },
                error: function () { toastr.error('Erro ao salvar!'); },
                complete: function () { $('#btnSalvarSimples').prop('disabled', false); }
            });
        });

        var valorCleaveCC = new Cleave('#valor', {
            numeral: true,
            numeralThousandsGroupStyle: 'thousand',
            prefix: 'R$ ',
            noImmediatePrefix: true,
            delimiter: '.',
            decimal: ',',
            numeralDecimalMark: ',',
            stripLeadingZeroes: true
        });

        // ─── EDITAR DESPESA DE CRÉDITO ────────────────────────────────────────
        function setCatSelecionadaCC(catId) {
            var id  = String(catId || '');
            var cat = window.categoriaMap ? window.categoriaMap[id] : null;
            $('#categoria').val(id);
            if (cat) {
                var cor   = cat.cor || '#6B7280';
                var icone = cat.icone ? '<span class="me-1">' + cat.icone + '</span>' : '';
                $('#catSelBtn .cat-sel-preview').html(
                    '<span class="cat-dot" style="background:' + cor + ';flex-shrink:0;"></span>' +
                    icone + '<span class="ms-1" style="color:' + cor + ';">' + cat.nome + '</span>'
                );
            } else {
                $('#catSelBtn .cat-sel-preview').html('<span class="text-muted">Selecione</span>');
            }
        }

        $(document).on('click', '.btn-repetir-gasto-cc', function () {
            _pendingRepetirCC = {
                descricao: $(this).data('descricao'),
                valor:     $(this).data('valor'),
                categoria: $(this).data('categoria'),
                cartao:    $(this).data('cartao')
            };
            bootstrap.Modal.getOrCreateInstance(document.getElementById('modalAdiciona')).show();
        });

        $(document).on('click', '.btn-editar-gasto-cc', function () {
            var $btn = $(this);
            _modoEditCC = true;
            $('#adicionarDespesa').hide();
            $('#editarDespesa').show();
            $('#gastoId').val($btn.data('id'));

            $('#descricao').val($btn.data('descricao'));
            var dataVal = $btn.data('data');
            $('#data').val(dataVal ? String(dataVal).substring(0, 10) : '');

            var valorNum = parseFloat(String($btn.data('valor')).replace('.', '').replace(',', '.'));
            valorCleaveCC.setRawValue(valorNum);

            $('#metodoWrapper').hide();
            $('#cartaoWrapper').hide();

            setCatSelecionadaCC($btn.data('categoria'));

            bootstrap.Modal.getOrCreateInstance(document.getElementById('modalAdiciona')).show();
        });

        $('#editarDespesa').on('click', function () {
            var id = $('#gastoId').val();
            if (!id) return;
            limpaErrosModalCC();
            var erros = [];
            if (!$('#descricao').val().trim()) { $('#descricao').addClass('is-invalid'); erros.push('Descrição'); }
            var vr = parseFloat($('#valor').val().replace(/R\$\s?/g,'').replace(/\./g,'').replace(',','.'));
            if (!vr || vr <= 0) { $('#valor').addClass('is-invalid'); erros.push('Valor'); }
            if (!$('#categoria').val()) { $('#catSelWrapper').addClass('borda-erro'); erros.push('Categoria'); }
            if (!$('#data').val()) { $('#data').addClass('is-invalid'); erros.push('Data'); }
            if (erros.length) { toastr.warning('Preencha: ' + erros.join(', ')); return; }
            $.ajax({
                type: 'POST',
                url: '../controllers/GastosController.php',
                data: {
                    acao:      'editar',
                    id:        id,
                    descricao: $('#descricao').val(),
                    valor:     $('#valor').val(),
                    categoria: $('#categoria').val(),
                    metodo:    'Crédito',
                    cartao:    '',
                    data:      $('#data').val(),
                },
                dataType: 'json',
                success: function () {
                    toastr.success('Despesa atualizada!');
                    bootstrap.Modal.getInstance(document.getElementById('modalAdiciona')).hide();
                    buscaFatura($('#mes').val());
                },
                error: function () { toastr.error('Erro ao salvar!'); }
            });
        });

    });
</script>

<?php require_once __DIR__ . '/../templates/footer.php'; ?>
