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
                        <th></th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>

        <div id="barraSelecao" style="display:none;" class="barra-selecao mt-3">
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

        // Marco inicial: abre no mês do marco se o atual for anterior (antes não há dados).
        if (window.mesInicialPadrao) {
            var _mp = window.mesInicialPadrao(parseInt($('#mes').val(), 10), parseInt($('#anoDisplay').text(), 10));
            $('#mes').val(_mp.mes);
            $('#anoDisplay').text(_mp.ano);
        }

        buscaCartoes();
        buscaCategorias();

        var _modoEditDeb = false;
        var _pendingRepetirDeb = null;

        // ─── MODAL ───────────────────────────────────────────────────────────────
        $('#modalAdiciona').on('show.bs.modal', function () {
            if (_modoEditDeb) return;
            $('#metodoWrapper').show();
            $('#tipoLancamentoWrapper').hide();
            $('#cartaoWrapper').hide();
            $('.border-parcelado').hide();
            $('#metodo').val('');
            $('.metodo-btn').removeClass('active');
            $('#cartao').val('');
            $('#cartaoSelectorModal').html('');
            resetCatSelect();
        });

        $('#modalAdiciona').on('hidden.bs.modal', function () { _modoEditDeb = false; _pendingRepetirDeb = null; limpaErrosModal(); });

        $('#modalAdiciona').on('shown.bs.modal', function () {
            if (!_pendingRepetirDeb) return;
            var d = _pendingRepetirDeb;
            _pendingRepetirDeb = null;
            $('#descricao').val(d.descricao);
            valorCleaveDeb.setValue(parseFloat(String(d.valor).replace(/\./g, '').replace(',', '.')));
            var hoje = new Date();
            var pad = function(n){ return String(n).padStart(2,'0'); };
            $('#data').val(hoje.getFullYear() + '-' + pad(hoje.getMonth()+1) + '-' + pad(hoje.getDate()));
            setCatSelecionada(d.categoria);
            $('.metodo-btn').removeClass('active');
            $('.metodo-btn[data-metodo="' + d.metodo + '"]').addClass('active');
            $('#metodo').val(d.metodo);
            if (d.metodo === 'Débito') { $('#cartaoWrapper').show(); renderCartoesMiniModal(); }
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
        $(document).on('click', '.metodo-btn', function () {
            $('.metodo-btn').removeClass('active');
            $(this).addClass('active');
            var metodo = $(this).data('metodo');
            $('#metodo').val(metodo);
            if (metodo === 'Débito') {
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
            var id = $(this).data('id');
            Swal.fire({
                title: 'Remover despesa?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#EF4444',
                cancelButtonColor: '#6B7280',
                confirmButtonText: 'Remover',
                cancelButtonText: 'Cancelar'
            }).then(function (result) {
                if (result.isConfirmed) {
                    $.ajax({
                        type: 'POST',
                        url: '../controllers/GastosController.php',
                        data: { acao: 'remover', ids: [{ id: id, parcelado: 'N' }], tipo: 'debito' },
                        dataType: 'json',
                        success: function () {
                            toastr.success('Despesa removida!');
                            buscaTabela($('#mes').val());
                        },
                        error: function () { toastr.error('Erro ao remover!'); }
                    });
                }
            });
        });

        // ─── ADICIONAR DESPESA ────────────────────────────────────────────────
        function limpaErrosModal() {
            $('#descricao, #valor, #data').removeClass('is-invalid');
            $('#catSelWrapper, #cartaoSelectorModal, #metodoBtnsWrap').removeClass('borda-erro');
        }

        function validaFormDebito() {
            limpaErrosModal();
            var erros = [];

            if (!$('#descricao').val().trim()) {
                $('#descricao').addClass('is-invalid');
                erros.push('Descrição');
            }
            var valorRaw = parseFloat($('#valor').val().replace(/R\$\s?/g,'').replace(/\./g,'').replace(',','.'));
            if (!valorRaw || valorRaw <= 0) {
                $('#valor').addClass('is-invalid');
                erros.push('Valor');
            }
            if (!$('#categoria').val()) {
                $('#catSelWrapper').addClass('borda-erro');
                erros.push('Categoria');
            }
            if (!$('#metodo').val()) {
                $('#metodoBtnsWrap').addClass('borda-erro');
                erros.push('Método de pagamento');
            }
            if ($('#metodo').val() === 'Débito' && !$('#cartao').val()) {
                $('#cartaoSelectorModal').addClass('borda-erro');
                erros.push('Cartão (para método Débito)');
            }
            if (!$('#data').val()) {
                $('#data').addClass('is-invalid');
                erros.push('Data');
            }

            if (erros.length) {
                toastr.warning('Preencha: ' + erros.join(', '));
                return false;
            }
            return true;
        }

        $('#adicionarDespesa').click(function () {
            if (!validaFormDebito()) return;
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

        // ─── REPETIR GASTO ───────────────────────────────────────────────────
        $(document).on('click', '.btn-repetir-gasto-deb', function () {
            _pendingRepetirDeb = {
                descricao: $(this).data('descricao'),
                valor:     $(this).data('valor'),
                categoria: $(this).data('categoria'),
                metodo:    $(this).data('metodo')
            };
            bootstrap.Modal.getOrCreateInstance(document.getElementById('modalAdiciona')).show();
        });

        // ─── EDITAR DESPESA ──────────────────────────────────────────────────
        $(document).on('click', '.btn-editar-gasto', function () {
            var $btn = $(this);
            _modoEditDeb = true;

            // Alterna botões no modal
            $('#adicionarDespesa').hide();
            $('#editarDespesa').show();
            $('#gastoId').val($btn.data('id'));

            // Preenche campos
            $('#descricao').val($btn.data('descricao'));
            $('#data').val($btn.data('data').substring(0, 10));

            // Valor formatado
            var valorNum = parseFloat(String($btn.data('valor')).replace(/\./g, '').replace(',', '.'));
            valorCleaveDeb.setValue(valorNum);

            // Método
            var metodo = $btn.data('metodo');
            $('#metodo').val(metodo);
            $('.metodo-btn').removeClass('active');
            $('.metodo-btn[data-metodo="' + metodo + '"]').addClass('active');
            if (metodo === 'Débito') {
                $('#cartaoWrapper').show();
                renderCartoesMiniModal();
                var cartaoId = $btn.data('cartao');
                if (cartaoId) {
                    setTimeout(function () {
                        $('.cartao-mini-modal[data-id="' + cartaoId + '"]').addClass('selecionado');
                        $('#cartao').val(cartaoId);
                    }, 100);
                }
            } else {
                $('#cartaoWrapper').hide();
                $('#cartao').val('');
            }

            // Categoria
            setCatSelecionada($btn.data('categoria'));

            // Abre modal
            var modal = bootstrap.Modal.getOrCreateInstance(document.getElementById('modalAdiciona'));
            modal.show();
        });

        $('#editarDespesa').on('click', function () {
            var id = $('#gastoId').val();
            if (!id) return;
            limpaErrosModal();
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
                    metodo:    $('#metodo').val(),
                    cartao:    $('#cartao').val() || '',
                    data:      $('#data').val(),
                },
                dataType: 'json',
                success: function () {
                    toastr.success('Despesa atualizada!');
                    bootstrap.Modal.getInstance(document.getElementById('modalAdiciona')).hide();
                    buscaTabela($('#mes').val());
                },
                error: function () { toastr.error('Erro ao salvar!'); }
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
            if (window.atualizaAvisoMarco) atualizaAvisoMarco(mes, getAno());
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

                        var descEsc = escHtml(gasto.descricao);
                        var _vOrdDeb = parseFloat(String(gasto.valor).replace(/\./g,'').replace(',','.')) || 0;
                        tbody.append(`<tr class="linha-clicavel" data-valor="${gasto.valor}">
                            <td><span class="linha-check"><i class="bi bi-check-circle-fill"></i></span>${descEsc}</td>
                            <td data-order="${_vOrdDeb}">R$ ${gasto.valor}</td>
                            <td>${catBadgeHtml(gasto.nome)}</td>
                            <td>${badge}</td>
                            <td data-order="${gasto.data_gasto}">${moment(gasto.data_gasto).format('DD/MM/YYYY')}</td>
                            <td>
                                <div class="d-flex align-items-center gap-1 justify-content-end">
                                    <button class="btn btn-sm btn-outline-secondary btn-editar-gasto py-0 px-1"
                                        data-id="${gasto.id}"
                                        data-descricao="${descEsc}"
                                        data-valor="${gasto.valor}"
                                        data-categoria="${gasto.categoria_id}"
                                        data-metodo="${gasto.metodo_pagamento}"
                                        data-cartao="${gasto.cartao_id || ''}"
                                        data-data="${gasto.data_gasto}"
                                        title="Editar">
                                        <i class="bi bi-pencil-fill" style="font-size:.75rem;"></i>
                                    </button>
                                    <button class="btn btn-sm btn-outline-secondary btn-repetir-gasto-deb py-0 px-1"
                                        data-descricao="${descEsc}"
                                        data-valor="${gasto.valor}"
                                        data-categoria="${gasto.categoria_id}"
                                        data-metodo="${gasto.metodo_pagamento}"
                                        title="Repetir no mês atual">
                                        <i class="bi bi-arrow-repeat" style="font-size:.75rem;"></i>
                                    </button>
                                    <button class="btn btn-sm btn-outline-danger btn-remover-gasto py-0 px-1"
                                        data-id="${gasto.id}" title="Remover">
                                        <i class="bi bi-trash-fill" style="font-size:.75rem;"></i>
                                    </button>
                                </div>
                            </td>
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
                        order: [[4, 'asc']],
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

        var valorCleaveDeb = bancInput(document.getElementById('valor'));

        function setCatSelecionada(catId) {
            var id  = String(catId || '');
            var cat = window.categoriaMap ? window.categoriaMap[id] : null;
            $('#categoria').val(id);
            if (cat) {
                var cor   = cat.cor || '#6B7280';
                var icone = cat.icone ? '<span class="me-1">' + escHtml(cat.icone) + '</span>' : '';
                $('#catSelBtn .cat-sel-preview').html(
                    '<span class="cat-dot" style="background:' + cor + ';flex-shrink:0;"></span>' +
                    icone + '<span class="ms-1" style="color:' + cor + ';">' + escHtml(cat.nome) + '</span>'
                );
            } else {
                $('#catSelBtn .cat-sel-preview').html('<span class="text-muted">Selecione</span>');
            }
        }

    });
</script>

<?php require_once __DIR__ . '/../templates/footer.php'; ?>
