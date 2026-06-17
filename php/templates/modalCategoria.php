<style>
emoji-picker {
    --background: #2B2C3B;
    --border-color: var(--cor-borda);
    --input-background: var(--cor-input);
    --outline-color: #3B82F6;
    --text-color: #F0F0F5;
    --secondary-text-color: #9CA3AF;
    --category-font-color: #9CA3AF;
    --num-columns: 7;
    width: 260px;
    height: 340px;
    border-radius: 10px;
    box-shadow: 0 8px 28px rgba(0,0,0,.55);
}
.emoji-btn-pick {
    background: var(--cor-input);
    border: 1px solid var(--cor-borda);
    border-radius: var(--radius-md);
    width: 54px;
    height: 54px;
    font-size: 1.7rem;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: border-color var(--trans), transform var(--trans);
    flex-shrink: 0;
}
.emoji-btn-pick:hover {
    border-color: var(--cor-azul);
    transform: scale(1.08);
}
</style>

<script type="module" src="https://esm.sh/emoji-picker-element@1"></script>

<!-- Picker fora de qualquer modal para não ser cortado pelo overflow -->
<div id="emojiPickerWrap" style="display:none; position:fixed; z-index:9999;">
    <emoji-picker id="emojiPickerEl" class="dark"></emoji-picker>
</div>

<!-- MODAL CATEGORIA -->
<div class="modal fade" id="modalCategoria" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-tag-fill titulo-azul me-2"></i>
                    <span id="catModalTitulo">Nova Categoria</span>
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="catId" value="0">

                <div class="mb-3">
                    <label class="form-label">Nome</label>
                    <input type="text" class="form-control" id="catNome" placeholder="Ex: Alimentação">
                </div>

                <div class="mb-3">
                    <label class="form-label">Emoji <small style="color:var(--cor-texto-sec);">— opcional</small></label>
                    <input type="hidden" id="catIcone">
                    <div class="d-flex align-items-center gap-3">
                        <button type="button" id="btnEmojiPicker" title="Escolher emoji" class="emoji-btn-pick">
                            <span id="catIconePreview">🏷️</span>
                        </button>
                        <div>
                            <div style="color:var(--cor-texto-sec);font-size:0.82rem;">Clique para escolher</div>
                            <button type="button" class="btn btn-link btn-sm p-0 text-danger" id="btnClearIcone" style="font-size:0.78rem;">
                                <i class="bi bi-x-circle me-1"></i>Remover
                            </button>
                        </div>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Cor</label>
                    <div class="cor-swatch-group" id="catCorSwatches"></div>
                    <input type="hidden" id="catCor" value="#6B7280">
                </div>

                <div class="p-3 rounded text-center" id="catPreviewBox" style="background:var(--cor-input);">
                    <span id="catPreviewBadge" class="badge" style="font-size:1rem;padding:6px 16px;border-radius:20px;">
                        <span id="catPreviewIcone"></span><span id="catPreviewNome">Categoria</span>
                    </span>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Fechar</button>
                <button type="button" class="btn btn-success btn-sm" id="salvarCategoria">
                    Salvar <i class="bi bi-floppy-fill"></i>
                </button>
            </div>
        </div>
    </div>
</div>

<script>
(function () {
    var CAT_CORES = [
        '#3B82F6', '#8B5CF6', '#EC4899', '#EF4444', '#F97316',
        '#F59E0B', '#22C55E', '#10B981', '#06B6D4', '#14B8A6',
        '#84CC16', '#6B7280'
    ];

    $('#catCorSwatches').html(CAT_CORES.map(function (c) {
        return '<button type="button" class="cor-swatch cat-cor-swatch" data-cor="' + c +
               '" style="background:' + c + ';" title="' + c + '"></button>';
    }).join(''));

    window.atualizarPreviewCategoria = function () {
        var cor   = $('#catCor').val() || '#6B7280';
        var icone = $('#catIcone').val().trim();
        var nome  = $('#catNome').val().trim() || 'Categoria';
        $('#catIconePreview').text(icone || '🏷️');
        $('#catPreviewBadge').css({ background: cor + '33', color: cor, border: '1px solid ' + cor + '66' });
        $('#catPreviewIcone').text(icone ? icone + ' ' : '');
        $('#catPreviewNome').text(nome);
    };

    window.abrirModalCategoria = function (id, nome, cor, icone) {
        $('#catId').val(id || 0);
        $('#catNome').val(nome || '');
        $('#catIcone').val(icone || '');
        $('#catCor').val(cor || '#6B7280');
        $('#catModalTitulo').text(parseInt(id) > 0 ? 'Editar Categoria' : 'Nova Categoria');
        $('.cat-cor-swatch').removeClass('selecionado');
        $('.cat-cor-swatch[data-cor="' + (cor || '#6B7280') + '"]').addClass('selecionado');
        atualizarPreviewCategoria();
        $('#modalCategoria').modal('show');
    };

    $('#catNome').on('input', function () { atualizarPreviewCategoria(); });

    window.customElements.whenDefined('emoji-picker').then(function () {
        var picker = document.getElementById('emojiPickerEl');
        picker.addEventListener('emoji-click', function (e) {
            $('#catIcone').val(e.detail.unicode);
            atualizarPreviewCategoria();
            $('#emojiPickerWrap').hide();
        });
    });

    $('#btnEmojiPicker').click(function (e) {
        e.stopPropagation();
        var wrap = $('#emojiPickerWrap');
        if (wrap.is(':visible')) { wrap.hide(); return; }

        var pw = 260, ph = 280;
        var btn = this.getBoundingClientRect();
        var dialog = document.querySelector('#modalCategoria .modal-dialog');
        var dlg = dialog ? dialog.getBoundingClientRect() : btn;

        var left = dlg.right + 8;
        var top  = btn.top;
        if (left + pw > window.innerWidth - 8)  { left = dlg.left - pw - 8; }
        if (left < 8) { left = Math.max(8, btn.left); top = btn.bottom + 4; }
        if (top + ph > window.innerHeight - 8) top = window.innerHeight - ph - 8;
        if (top < 8) top = 8;

        wrap.css({ top: top + 'px', left: left + 'px' }).show();
    });

    $('#btnClearIcone').click(function () {
        $('#catIcone').val('');
        atualizarPreviewCategoria();
    });

    $(document).on('click', function (e) {
        if (!$(e.target).closest('#emojiPickerWrap, #btnEmojiPicker').length) {
            $('#emojiPickerWrap').hide();
        }
    });

    $('#modalCategoria').on('hide.bs.modal', function () {
        $('#emojiPickerWrap').hide();
    });

    document.addEventListener('focusin', function (e) {
        if ($('#emojiPickerWrap').is(':visible')) {
            e.stopImmediatePropagation();
        }
    }, true);

    $(document).on('click', '.cat-cor-swatch', function () {
        $('.cat-cor-swatch').removeClass('selecionado');
        $(this).addClass('selecionado');
        $('#catCor').val($(this).data('cor'));
        atualizarPreviewCategoria();
    });

    $(document).on('click', '#salvarCategoria', function () {
        var id    = parseInt($('#catId').val());
        var nome  = $('#catNome').val().trim();
        var cor   = $('#catCor').val() || '#6B7280';
        var icone = $('#catIcone').val().trim();

        if (!nome) { toastr.warning('Informe o nome da categoria!'); return; }

        var postData = id > 0
            ? { acao: 'editar', nome: nome, id: id, cor: cor, icone: icone }
            : { acao: 'adicionar', descricao: nome, cor: cor, icone: icone };

        $.ajax({
            type: 'POST',
            url: App.ctrl.categoria,
            data: postData,
            dataType: 'json',
            success: function (res) {
                if (res == true) {
                    toastr.success(id > 0 ? 'Categoria alterada!' : 'Categoria criada!');
                    $('#modalCategoria').modal('hide');
                    $(document).trigger('cat:salva');
                } else {
                    toastr.error('Erro ao salvar categoria!');
                }
            },
            error: function () { toastr.error('Erro ao salvar categoria!'); }
        });
    });

    // Botão + ao lado do select de categoria → abre modal completo
    $(document).on('click', '#enviaCriarCategoria', function () {
        abrirModalCategoria(0, '', '#6B7280', '');
    });
})();
</script>
