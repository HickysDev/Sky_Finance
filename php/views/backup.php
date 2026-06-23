<?php require_once __DIR__ . '/../templates/header.php'; ?>

<div class="animate__animated animate__fadeIn">

    <div class="d-flex align-items-center justify-content-between titulo-pagina mb-4 flex-wrap gap-2">
        <h1 class="titulo mt-2 fs-titulo-pag">
            Backup &nbsp;<i class="bi bi-database-down titulo-azul"></i>
        </h1>
        <p class="mb-0" style="font-size:0.83rem;color:var(--cor-texto-off);">
            Exporte seus dados para levar entre dispositivos
        </p>
    </div>

    <div class="row g-4">

        <!-- ── EXPORTAR ──────────────────────────────────────────── -->
        <div class="col-12 col-md-6">
            <div class="painel h-100 d-flex flex-column">
                <div class="d-flex align-items-center gap-3 mb-3">
                    <div style="width:48px;height:48px;border-radius:12px;background:rgba(59,130,246,0.15);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                        <i class="bi bi-cloud-download-fill" style="font-size:1.4rem;color:var(--cor-azul);"></i>
                    </div>
                    <div>
                        <h6 class="titulo mb-0">Exportar dados</h6>
                        <small style="color:var(--cor-texto-off);">Gera um arquivo .sql com todos os seus registros</small>
                    </div>
                </div>

                <ul style="font-size:0.82rem;color:var(--cor-texto-off);padding-left:1.2rem;line-height:2;">
                    <li>Gastos, parcelas e recorrentes</li>
                    <li>Cartões, categorias e orçamentos</li>
                    <li>Renda, contas fixas e cofrinhos</li>
                    <li>Responsáveis e contas de pessoas</li>
                </ul>

                <div class="mt-auto pt-3">
                    <form method="POST" action="../controllers/BackupController.php">
                        <input type="hidden" name="acao" value="exportar">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-download me-2"></i>Baixar backup (.sql)
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- ── IMPORTAR ──────────────────────────────────────────── -->
        <div class="col-12 col-md-6">
            <div class="painel h-100 d-flex flex-column">
                <div class="d-flex align-items-center gap-3 mb-3">
                    <div style="width:48px;height:48px;border-radius:12px;background:rgba(16,185,129,0.15);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                        <i class="bi bi-cloud-upload-fill" style="font-size:1.4rem;color:#10B981;"></i>
                    </div>
                    <div>
                        <h6 class="titulo mb-0">Importar dados</h6>
                        <small style="color:var(--cor-texto-off);">Restaura um backup exportado por este sistema</small>
                    </div>
                </div>

                <div class="alert" style="background:rgba(245,158,11,0.1);border:1px solid rgba(245,158,11,0.3);border-radius:8px;font-size:0.8rem;color:#F59E0B;padding:0.65rem 1rem;">
                    <i class="bi bi-exclamation-triangle-fill me-1"></i>
                    Isso <strong>substitui todos os dados</strong> do banco atual. Use apenas em uma instalação limpa ou para sincronizar.
                </div>

                <div class="mt-2 flex-fill d-flex flex-column justify-content-end">
                    <label class="form-label" style="font-size:0.85rem;">Selecione o arquivo .sql</label>
                    <div class="input-group mb-3">
                        <input type="file" class="form-control" id="arquivoImport" accept=".sql">
                    </div>
                    <button type="button" class="btn btn-success w-100" id="btnImportar">
                        <i class="bi bi-upload me-2"></i>Importar backup
                    </button>
                </div>
            </div>
        </div>

    </div>

    <!-- ── ESTRUTURA ─────────────────────────────────────────── -->
    <div class="row g-4 mt-1">
        <div class="col-12">
            <div class="painel">
                <div class="d-flex align-items-center gap-3 mb-3">
                    <div style="width:48px;height:48px;border-radius:12px;background:rgba(139,92,246,0.15);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                        <i class="bi bi-database-fill-gear" style="font-size:1.4rem;color:#8B5CF6;"></i>
                    </div>
                    <div>
                        <h6 class="titulo mb-0">Criar banco do zero</h6>
                        <small style="color:var(--cor-texto-off);">Baixa o script SQL completo com a criação do banco e de todas as tabelas</small>
                    </div>
                </div>
                <p style="font-size:0.82rem;color:var(--cor-texto-off);margin-bottom:1rem;">
                    Use em uma instalação nova. Execute no <strong>phpMyAdmin</strong> antes de importar um backup de dados.
                    O script usa <code>CREATE DATABASE IF NOT EXISTS</code> e <code>CREATE TABLE IF NOT EXISTS</code>, sendo seguro rodar mesmo em bancos já existentes.
                </p>
                <form method="POST" action="../controllers/BackupController.php" style="max-width:260px;">
                    <input type="hidden" name="acao" value="estrutura">
                    <button type="submit" class="btn w-100" style="background:rgba(139,92,246,0.15);border:1px solid #8B5CF6;color:#8B5CF6;">
                        <i class="bi bi-download me-2"></i>Baixar setup_completo.sql
                    </button>
                </form>
            </div>
        </div>
    </div>

</div>

<script>
$('#btnImportar').on('click', function () {
    var arquivo = $('#arquivoImport')[0].files[0];
    if (!arquivo) { toastr.warning('Selecione um arquivo .sql primeiro.'); return; }

    var $btn = $(this);
    $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span>Importando...');

    var fd = new FormData();
    fd.append('acao', 'importar');
    fd.append('arquivo', arquivo);

    $.ajax({
        type: 'POST',
        url: '../controllers/BackupController.php',
        data: fd,
        processData: false,
        contentType: false,
        dataType: 'json',
        success: function (res) {
            if (res.ok) {
                toastr.success(res.msg, 'Importação concluída', { timeOut: 5000 });
                $('#arquivoImport').val('');
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Erro na importação',
                    html: res.msg,
                    confirmButtonColor: '#3B82F6'
                });
            }
        },
        error: function () { toastr.error('Erro na requisição.'); },
        complete: function () {
            $btn.prop('disabled', false).html('<i class="bi bi-upload me-2"></i>Importar backup');
        }
    });
});
</script>

<?php include '../templates/footer.php'; ?>
