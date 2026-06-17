<?php
require_once __DIR__ . '/../templates/header.php';

$meses = [
    1 => "Janeiro",  2 => "Fevereiro", 3 => "Março",    4 => "Abril",
    5 => "Maio",     6 => "Junho",     7 => "Julho",    8 => "Agosto",
    9 => "Setembro", 10 => "Outubro",  11 => "Novembro", 12 => "Dezembro"
];

$mesAtual = date('n');
?>
<script>var tipoDespesa = 'debito';</script>

<div class="animate__animated animate__fadeIn">

    <!-- HEADER -->
    <div class="d-flex align-items-center justify-content-between titulo-pagina mb-4">
        <h1 class="titulo mt-2 fs-titulo-pag">
            À Vista &nbsp;<i class="bi bi-currency-dollar titulo-azul"></i>
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

    <!-- BARRA RESUMO -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <span class="titulo cor-am me-2 fs-total-label">Total do mês:</span>
            <span class="titulo titulo-azul fs-total-valor" id="totalMes">—</span>
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

    <!-- PAINEL DE GASTOS -->
    <div class="painel">

        <div class="text-center py-5" id="loaderGasto">
            <div class="spinner-border" style="color:#3B82F6;" role="status">
                <span class="visually-hidden">Carregando...</span>
            </div>
        </div>

        <div class="table-responsive" id="tabelaWrapper" style="display:none;">
            <table id="gastosMes" class="table table-hover table-centro" style="width:100%;">
                <thead class="bg-secundary">
                    <tr>
                        <th>Produto</th>
                        <th>Valor</th>
                        <th>Categoria</th>
                        <th>Método</th>
                        <th>Data</th>
                        <th><input type="checkbox" id="marcaTodos"></th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>

        <div id="estadoVazio" style="display:none;" class="text-center py-5">
            <i class="bi bi-inbox" style="font-size:3rem;color:#3F3F46;"></i>
            <p class="mt-3" style="color:#6B7280;">Nenhum gasto encontrado para este mês.</p>
        </div>

    </div>

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
            $('#metodoWrapper').show();
            $('#cartaoWrapper').hide();
            $('#parceladoWrapper').hide();
            $('#recorrenteWrapper').hide();
            $('.border-parcelado').hide();
            $('#metodo').val('');
            $('#cartao').val('');
            $('#cartaoSelectorModal').html('');
            resetCatSelect();
        });

        // ─── SELETOR DE MÊS / ANO ────────────────────────────────────────────
        function getAno() { return parseInt($('#anoDisplay').text()); }

        $('.botaoEsquerda').click(function () {
            let val = parseInt($('#mes').val());
            if (val > 1) { $('#mes').val(val - 1).trigger('change'); }
            else { $('#mes').val(12); $('#anoDisplay').text(getAno() - 1); buscaTabela(12); }
        });

        $('.botaoDireita').click(function () {
            let val = parseInt($('#mes').val());
            if (val < 12) { $('#mes').val(val + 1).trigger('change'); }
            else { $('#mes').val(1); $('#anoDisplay').text(getAno() + 1); buscaTabela(1); }
        });

        $('#anoEsquerda').click(function () { $('#anoDisplay').text(getAno() - 1); buscaTabela($('#mes').val()); });
        $('#anoDireita').click(function ()  { $('#anoDisplay').text(getAno() + 1); buscaTabela($('#mes').val()); });

        $('#mes').change(function () {
            buscaTabela($(this).val());
        });

        // ─── MÉTODO → MOSTRAR CARTÃO SE DÉBITO ───────────────────────────────
        $('#metodo').change(function () {
            if ($(this).val() === 'Débito') {
                $('#cartaoWrapper').slideDown();
                renderCartoesMiniModal();
            } else {
                $('#cartaoWrapper').slideUp();
                $('#cartao').val('');
            }
        });

        $(document).on('click', '.cartao-mini-modal', function () {
            $('.cartao-mini-modal').removeClass('selecionado');
            $(this).addClass('selecionado');
            $('#cartao').val($(this).data('id'));
        });

        // ─── MARCAR TODOS ─────────────────────────────────────────────────────
        $(document).on('change', '#marcaTodos', function () {
            let checked = $(this).prop('checked');
            $('.marcagasto').prop('checked', checked);
            $('#removerSelecionados').toggle(checked && $('.marcagasto').length > 0);
        });

        $(document).on('change', '.marcagasto', function () {
            let total   = $('.marcagasto').length;
            let marcados = $('.marcagasto:checked').length;
            $('#marcaTodos').prop('checked', total === marcados);
            $('#removerSelecionados').toggle(marcados > 0);
        });

        // ─── REMOVER SELECIONADOS ─────────────────────────────────────────────
        $('#removerSelecionados').click(function () {
            var ids = [];
            $('.marcagasto:checked').each(function () {
                ids.push({ id: $(this).data('id'), parcelado: 'N' });
            });

            if (!ids.length) return;

            Swal.fire({
                title: 'Remover despesas?',
                text: ids.length + ' despesa(s) selecionada(s)',
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
                        url: '../controllers/GastosController.php',
                        data: { acao: 'remover', ids: ids, tipo: 'debito' },
                        dataType: 'json',
                        success: function () {
                            toastr.success('Despesa(s) removida(s)!');
                            $('#removerSelecionados').hide();
                            $('#marcaTodos').prop('checked', false);
                            buscaTabela($('#mes').val());
                        },
                        error: function () { toastr.error('Erro ao remover!'); }
                    });
                }
            });
        });

        // ─── ADICIONAR DESPESA ────────────────────────────────────────────────
        $('#adicionarDespesa').click(function () {
            let cartao = $('#cartao').val() || null;
            $.ajax({
                type: 'POST',
                url: '../controllers/GastosController.php',
                data: {
                    acao:        'adicionar',
                    descricao:   $('#descricao').val(),
                    valor:       $('#valor').val(),
                    categoria:   $('#categoria').val(),
                    metodo:      $('#metodo').val(),
                    cartao:      cartao,
                    data:        $('#data').val(),
                    tipo:        'debito',
                    responsavel: $('#responsavel').val() || '',
                },
                dataType: 'json',
                success: function () {
                    toastr.success('Despesa adicionada com sucesso!');
                    $('#modalAdiciona').modal('hide');
                    buscaTabela($('#mes').val());
                },
                error: function () { toastr.error('Erro ao adicionar despesa!'); }
            });
        });

        $(document).on('cat:salva', function () { buscaCategorias(); });

        // ─── FUNÇÕES ──────────────────────────────────────────────────────────

        function buscaCartoes() {
            $.ajax({
                type: 'POST',
                url: '../controllers/CartoesController.php',
                data: { acao: 'busca' },
                dataType: 'json',
                success: function (data) { window.cartoesArray = data; }
            });
        }

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

        function buscaTabela(mes) {
            if ($.fn.DataTable.isDataTable('#gastosMes')) {
                $('#gastosMes').DataTable().destroy();
            }

            $('#tabelaWrapper').hide();
            $('#estadoVazio').hide();
            $('#loaderGasto').show();
            $('#totalMes').text('—');

            $.ajax({
                type: 'POST',
                url: '../controllers/GastosController.php',
                data: { acao: 'buscar', mes: mes, ano: getAno(), cartaoId: null, tipo: 'debito' },
                dataType: 'json',
                success: function (data) {
                    $('#loaderGasto').hide();

                    let tbody    = $('#gastosMes tbody');
                    let temDados = false;
                    tbody.empty();

                    $.each(data, function (index, gasto) {
                        if (index === 'valortotal') {
                            $('#totalMes').text('R$ ' + gasto);
                            return;
                        }

                        temDados = true;

                        let badge;
                        switch (gasto.metodo_pagamento) {
                            case 'Pix':
                                badge = '<span class="badge" style="background:#10B981;">Pix</span>';
                                break;
                            case 'Débito':
                                badge = '<span class="badge" style="background:#F59E0B;color:#111;">Débito</span>';
                                break;
                            default:
                                badge = '<span class="badge bg-secondary">Dinheiro</span>';
                        }

                        tbody.append(`<tr>
                            <td>${gasto.descricao}</td>
                            <td>R$ ${gasto.valor}</td>
                            <td>${catBadgeHtml(gasto.nome)}</td>
                            <td>${badge}</td>
                            <td>${moment(gasto.data_gasto).format('DD/MM/YYYY')}</td>
                            <td><input type="checkbox" class="marcagasto" data-id="${gasto.id}"></td>
                        </tr>`);
                    });

                    if (!temDados) {
                        $('#estadoVazio').show();
                        $('#totalMes').text('R$ 0,00');
                        return;
                    }

                    $('#tabelaWrapper').show();
                    $('#gastosMes').DataTable({
                        paging: false,
                        info: false,
                        lengthChange: false,
                        searching: true,
                        ordering: true,
                        columnDefs: [{ orderable: false, targets: 5 }],
                        language: {
                            search: 'Pesquisar:',
                            zeroRecords: 'Nenhum registro encontrado',
                            emptyTable: 'Nenhum dado disponível'
                        }
                    });
                },
                error: function () { toastr.error('Erro ao buscar dados!'); }
            });
        }

        function buscaCategorias() {
            $.ajax({
                type: 'POST',
                url: '../controllers/CategoriaController.php',
                data: { acao: 'busca' },
                dataType: 'json',
                success: function (data) {
                    popularCatSelect(data);
                    buscaTabela($('#mes').val());
                }
            });
        }

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
