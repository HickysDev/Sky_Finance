<?php
require_once __DIR__ . '/../templates/header.php';

$meses = [
    1=>'Janeiro', 2=>'Fevereiro', 3=>'Março',    4=>'Abril',
    5=>'Maio',    6=>'Junho',     7=>'Julho',     8=>'Agosto',
    9=>'Setembro',10=>'Outubro',  11=>'Novembro', 12=>'Dezembro'
];
$mesAtual = date('n');
?>

<div class="animate__animated animate__fadeIn">

    <!-- HEADER -->
    <div class="d-flex align-items-center justify-content-between titulo-pagina mb-4 flex-wrap gap-2">
        <h1 class="titulo mt-2 fs-titulo-pag">
            Contas Fixas &nbsp;<i class="bi bi-receipt-cutoff titulo-azul"></i>
        </h1>
        <div class="d-flex align-items-center gap-2 flex-wrap">
            <i class="bi bi-arrow-left-square-fill botao" id="mesEsquerda"></i>
            <select class="form-select text-center" id="mes" style="width:140px;">
                <?php foreach ($meses as $n => $m): ?>
                    <option value="<?= $n ?>" <?= $n == $mesAtual ? 'selected' : '' ?>><?= $m ?></option>
                <?php endforeach ?>
            </select>
            <i class="bi bi-arrow-right-square-fill botao" id="mesDireita"></i>
            <span style="color:var(--cor-borda);font-size:1.1rem;">|</span>
            <i class="bi bi-arrow-left-square-fill botao" id="anoEsquerda"></i>
            <span id="anoDisplay" style="font-size:0.95rem;font-weight:700;min-width:44px;text-align:center;"><?= date('Y') ?></span>
            <i class="bi bi-arrow-right-square-fill botao" id="anoDireita"></i>
            <a href="gerenciamento.php?tab=ContasFixas" class="btn btn-outline-secondary btn-sm ms-1">
                <i class="bi bi-gear-fill me-1"></i>Gerenciar
            </a>
        </div>
    </div>

    <!-- RESUMO PILLS -->
    <div class="d-flex gap-3 flex-wrap mb-4" id="resumoContas"></div>

    <!-- LISTA -->
    <div id="listaContas">
        <div class="text-center py-5">
            <div class="spinner-border" style="color:var(--cor-azul);" role="status"></div>
        </div>
    </div>

</div>

<script>
$(document).ready(function () {

    function getAno() { return parseInt($('#anoDisplay').text()); }

    // ── NAVEGAÇÃO ────────────────────────────────────────────────────
    $('#mesEsquerda').click(function () {
        var v = parseInt($('#mes').val());
        if (v > 1) { $('#mes').val(v - 1).trigger('change'); }
        else { $('#mes').val(12); $('#anoDisplay').text(getAno() - 1); carregar(); }
    });
    $('#mesDireita').click(function () {
        var v = parseInt($('#mes').val());
        if (v < 12) { $('#mes').val(v + 1).trigger('change'); }
        else { $('#mes').val(1); $('#anoDisplay').text(getAno() + 1); carregar(); }
    });
    $('#anoEsquerda').click(function () { $('#anoDisplay').text(getAno() - 1); carregar(); });
    $('#anoDireita').click(function ()  { $('#anoDisplay').text(getAno() + 1); carregar(); });
    $('#mes').change(function () { carregar(); });

    // ── CARREGAR ─────────────────────────────────────────────────────
    function carregar() {
        if (window.atualizaAvisoMarco) atualizaAvisoMarco($('#mes').val(), getAno());
        $('#listaContas').html('<div class="text-center py-5"><div class="spinner-border" style="color:var(--cor-azul);" role="status"></div></div>');
        $.ajax({
            type: 'POST',
            url: App.ctrl.contasFixas,
            data: { acao: 'resumoMes', mes: $('#mes').val(), ano: getAno() },
            dataType: 'json',
            success: function (data) { renderLista(data); },
            error: function (xhr) {
                $('#listaContas').html('<div class="alert alert-danger mt-3">Erro: ' + xhr.responseText + '</div>');
            }
        });
    }

    // ── RENDER ───────────────────────────────────────────────────────
    function fmtR(v) {
        return 'R$ ' + parseFloat(v || 0).toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    function renderLista(data) {
        if (!data || !data.length) {
            $('#resumoContas').html('');
            $('#listaContas').html(
                '<div class="painel text-center py-5">' +
                '<i class="bi bi-receipt-cutoff" style="font-size:3rem;color:var(--cor-borda);"></i>' +
                '<p class="mt-3 mb-3" style="color:var(--cor-texto-off);">Nenhuma conta fixa cadastrada.</p>' +
                '<a href="gerenciamento.php?tab=ContasFixas" class="btn btn-outline-primary btn-sm">Cadastrar contas fixas</a>' +
                '</div>');
            return;
        }

        var hoje      = new Date();
        var mesAtual  = parseInt($('#mes').val());
        var anoAtual  = getAno();
        var ehMesAtual = mesAtual === hoje.getMonth() + 1 && anoAtual === hoje.getFullYear();

        var totalPago = 0, totalAberto = 0, totalVencido = 0;
        var html = '<div class="d-flex flex-column gap-2">';

        $.each(data, function (_, c) {
            var pago    = c.pago;
            var vencido = !pago && ehMesAtual && hoje.getDate() > c.dia_vencimento;
            var cor     = c.cor || '#3B82F6';

            var statusHtml, actionHtml;

            if (pago) {
                totalPago += c.valor_pago || c.valor;
                var dataPago = c.data_pagamento ? moment(c.data_pagamento).format('DD/MM') : '';
                statusHtml = '<span class="cfi-badge pago"><i class="bi bi-check-circle-fill me-1"></i>Pago' + (dataPago ? ' em ' + dataPago : '') + '</span>';
                actionHtml = '<button class="btn btn-sm btn-outline-secondary cfi-btn-desmarcar" data-id="' + c.id + '"><i class="bi bi-x-lg me-1"></i>Desmarcar</button>';
            } else if (vencido) {
                totalVencido += c.valor;
                statusHtml = '<span class="cfi-badge vencido"><i class="bi bi-exclamation-circle-fill me-1"></i>Vencido</span>';
                actionHtml = '<button class="btn btn-sm btn-danger cfi-btn-pagar" data-id="' + c.id + '" data-valor="' + c.valor + '"><i class="bi bi-check-lg me-1"></i>Pagar</button>';
            } else {
                totalAberto += c.valor;
                statusHtml = '<span class="cfi-badge aberto"><i class="bi bi-clock me-1"></i>A pagar</span>';
                actionHtml = '<button class="btn btn-sm btn-outline-success cfi-btn-pagar" data-id="' + c.id + '" data-valor="' + c.valor + '"><i class="bi bi-check-lg me-1"></i>Pagar</button>';
            }

            html +=
                '<div class="cfi-row" style="border-left-color:' + cor + ';">' +
                    '<div class="cfi-left">' +
                        '<span class="cfi-dot" style="background:' + cor + ';"></span>' +
                        '<div>' +
                            '<div class="cfi-nome">' + c.nome + '</div>' +
                            '<div class="cfi-detalhe"><i class="bi bi-calendar3 me-1"></i>Vence dia ' + c.dia_vencimento + '</div>' +
                        '</div>' +
                    '</div>' +
                    '<div class="cfi-center">' + statusHtml + '</div>' +
                    '<div class="cfi-right">' +
                        '<span class="cfi-valor">' + fmtR(pago ? (c.valor_pago || c.valor) : c.valor) + '</span>' +
                        actionHtml +
                    '</div>' +
                '</div>';
        });

        html += '</div>';

        var resumoHtml =
            '<div class="resumo-cf pago"><i class="bi bi-check-circle-fill me-2"></i><span>Pago</span><strong>' + fmtR(totalPago) + '</strong></div>' +
            '<div class="resumo-cf aberto"><i class="bi bi-clock me-2"></i><span>A pagar</span><strong>' + fmtR(totalAberto) + '</strong></div>' +
            (totalVencido > 0
                ? '<div class="resumo-cf vencido"><i class="bi bi-exclamation-circle-fill me-2"></i><span>Vencido</span><strong>' + fmtR(totalVencido) + '</strong></div>'
                : '');

        $('#resumoContas').html(resumoHtml);
        $('#listaContas').html(html);
    }

    // ── MARCAR PAGO ──────────────────────────────────────────────────
    $(document).on('click', '.cfi-btn-pagar', function () {
        var id         = $(this).data('id');
        var valorPadrao = parseFloat($(this).data('valor') || 0).toLocaleString('pt-BR', { minimumFractionDigits: 2 });
        var mes        = $('#mes').val();
        var ano        = getAno();

        Swal.fire({
            title: 'Registrar pagamento',
            html:
                '<div class="mb-3 text-start">' +
                    '<label class="form-label">Valor pago</label>' +
                    '<input id="swalValorPago" class="form-control" value="' + valorPadrao + '">' +
                '</div>' +
                '<div class="text-start">' +
                    '<label class="form-label">Data do pagamento</label>' +
                    '<input id="swalDataPago" type="date" class="form-control" value="' + moment().format('YYYY-MM-DD') + '">' +
                '</div>',
            showCancelButton: true,
            confirmButtonText: '<i class="bi bi-check-lg me-1"></i>Confirmar',
            cancelButtonText: 'Cancelar',
            confirmButtonColor: '#22C55E',
            cancelButtonColor: '#6B7280',
            didOpen: function () {
                bancInput(document.getElementById('swalValorPago'), valorPadrao);
            },
            preConfirm: function () {
                return {
                    valor: $('#swalValorPago').val(),
                    data:  $('#swalDataPago').val()
                };
            }
        }).then(function (result) {
            if (!result.isConfirmed) return;
            $.ajax({
                type: 'POST', url: App.ctrl.contasFixas,
                data: { acao: 'marcarPago', id: id, mes: mes, ano: ano, data: result.value.data, valor_pago: result.value.valor },
                dataType: 'json',
                success: function () { toastr.success('Pagamento registrado!'); carregar(); },
                error: function () { toastr.error('Erro ao registrar pagamento!'); }
            });
        });
    });

    // ── DESMARCAR PAGO ───────────────────────────────────────────────
    $(document).on('click', '.cfi-btn-desmarcar', function () {
        var id  = $(this).data('id');
        var mes = $('#mes').val();
        var ano = getAno();

        Swal.fire({
            title: 'Remover pagamento?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Remover',
            cancelButtonText: 'Cancelar',
            confirmButtonColor: '#EF4444',
            cancelButtonColor: '#6B7280'
        }).then(function (result) {
            if (!result.isConfirmed) return;
            $.ajax({
                type: 'POST', url: App.ctrl.contasFixas,
                data: { acao: 'desmarcarPago', id: id, mes: mes, ano: ano },
                dataType: 'json',
                success: function () { toastr.success('Pagamento removido!'); carregar(); },
                error: function () { toastr.error('Erro!'); }
            });
        });
    });

    carregar();
});
</script>
