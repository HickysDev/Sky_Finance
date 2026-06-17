<?php
require_once __DIR__ . '/../templates/header.php';

$meses = [
    1 => "Janeiro",  2 => "Fevereiro", 3 => "Março",    4 => "Abril",
    5 => "Maio",     6 => "Junho",     7 => "Julho",    8 => "Agosto",
    9 => "Setembro", 10 => "Outubro",  11 => "Novembro", 12 => "Dezembro"
];

$mesAtual = date('n');
?>
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
            <button class="btn btn-outline-danger btn-sm" id="removerSelecionados" style="display:none;">
                <i class="bi bi-trash-fill"></i> Remover selecionados
            </button>
            <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modalAdiciona">
                <i class="bi bi-plus-lg"></i> Adicionar Despesa
            </button>
        </div>
    </div>

    <!-- FATURAS -->
    <div id="faturasDiv"></div>

    <input type="hidden" id="cartaoAtual" value="">

</div>

<!-- MODAL -->
<?php include '../templates/modalCadastra.php'; ?>
<?php include '../templates/modalCategoria.php'; ?>

<script>

    $(document).ready(function () {

        buscaCartoes();
        buscaCategorias();

        // ─── MODAL ───────────────────────────────────────────────────────────────
        $('#modalAdiciona').on('show.bs.modal', function () {
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
            buscaFatura($('#mes').val());
        });

        // ─── MARCAR TODOS (por tabela) ────────────────────────────────────────
        $(document).on('change', '.marcaTodosCartao', function () {
            let checked = $(this).prop('checked');
            $(this).closest('table').find('.marcagasto').prop('checked', checked);
            $('#removerSelecionados').toggle($('.marcagasto:checked').length > 0);
        });

        $(document).on('change', '.marcagasto', function () {
            $('#removerSelecionados').toggle($('.marcagasto:checked').length > 0);
        });

        // ─── REMOVER SELECIONADOS ─────────────────────────────────────────────
        $('#removerSelecionados').click(function () {
            var idsNormais     = [];
            var idsRecorrentes = [];

            $('.marcagasto:checked').each(function () {
                if ($(this).data('tipo') === 'RECORRENTE') {
                    idsRecorrentes.push($(this).data('id'));
                } else {
                    idsNormais.push({ id: $(this).data('id'), parcelado: $(this).data('parcelado') });
                }
            });

            if (!idsNormais.length && !idsRecorrentes.length) return;

            let partes = [];
            if (idsNormais.length)     partes.push(idsNormais.length + ' despesa(s) removida(s) permanentemente');
            if (idsRecorrentes.length) partes.push(idsRecorrentes.length + ' recorrente(s) inativado(s)');

            Swal.fire({
                title: 'Confirmar ação?',
                text: partes.join(' e ') + '.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#EF4444',
                cancelButtonColor: '#6B7280',
                confirmButtonText: 'Confirmar',
                cancelButtonText: 'Cancelar'
            }).then(function (result) {
                if (!result.isConfirmed) return;

                var promises = [];

                if (idsNormais.length) {
                    promises.push($.ajax({
                        type: 'POST',
                        url: '../controllers/GastosController.php',
                        data: { acao: 'remover', ids: idsNormais, tipo: 'credito' },
                        dataType: 'json'
                    }));
                }

                $.each(idsRecorrentes, function (i, recId) {
                    promises.push($.ajax({
                        type: 'POST',
                        url: '../controllers/GastosController.php',
                        data: { acao: 'inativaRecorrentes', id: recId },
                        dataType: 'json'
                    }));
                });

                $.when.apply($, promises).always(function () {
                    toastr.success('Ação realizada com sucesso!');
                    $('#removerSelecionados').hide();
                    buscaFatura($('#mes').val());
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

        // ─── ADICIONAR DESPESA ────────────────────────────────────────────────
        $('#adicionarDespesa').click(function () {
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

                    $.each(data, function (id, cartao) {
                        let cor = cartao.cor || '#3B82F6';
                        html += `<div class="cartao-mini" data-id="${cartao.id}" style="--cartao-cor:${cor};">
                            <i class="bi bi-credit-card-fill" style="color:${cor};font-size:1.6rem;"></i>
                            <span>${cartao.nome_cartao}</span>
                        </div>`;
                    });

                    $('#cartoesRow').html(html);
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
                                        <th><input type="checkbox" class="marcaTodosCartao"></th>
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

                            html += `<tr>
                                <td>${gasto.descricao}</td>
                                <td>${catBadgeHtml(gasto.categoria)}</td>
                                <td>${infoParcela}</td>
                                <td>R$ ${gasto.valor_parcela}</td>
                                <td>${dataExib}</td>
                                <td><input type="checkbox" class="marcagasto"
                                    data-id="${gasto.id}" data-parcelado="${gasto.parcelado}" data-tipo="${gasto.tipo}"></td>
                            </tr>`;
                        });

                        html += `</tbody></table></div></div>`;
                    });

                    $('#faturasDiv').html(html);
                    $('#totalGeral').text('R$ ' + formatarNumeroBrasileiro(totalGeral));
                    atualizaStatusFatura();

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

        new Cleave('#valor', {
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

<?php require_once __DIR__ . '/../templates/footer.php'; ?>
