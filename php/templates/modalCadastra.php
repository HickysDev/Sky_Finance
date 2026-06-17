<div class="modal fade" id="modalAdiciona" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">

            <div class="modal-header" style="background:#2C2C44;border-bottom:1px solid #3F3F46;">
                <h5 class="modal-title" style="color:#F0F0F5;">
                    <i class="bi bi-cart-plus-fill titulo-azul me-2"></i>
                    <span class="criaDespesaForm">Adicionar Despesa</span>
                    <span class="criaCategoriaForm" style="display:none;">Nova Categoria</span>
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body" style="padding:1.5rem;">

                <!-- VOLTAR (criar categoria) -->
                <div class="criaCategoriaForm mb-3" style="display:none;">
                    <button type="button" class="btn btn-sm btn-outline-secondary voltarBtn">
                        <i class="bi bi-arrow-left me-1"></i>Voltar
                    </button>
                </div>

                <!-- FORM PRINCIPAL -->
                <div class="criaDespesaForm">

                    <!-- Linha 1: Descrição + Valor -->
                    <div class="row g-3 mb-3">
                        <div class="col-7">
                            <label for="descricao" class="form-label">Descrição</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-pencil-fill"></i></span>
                                <input type="text" class="form-control" id="descricao" placeholder="Ex: Mercado">
                            </div>
                        </div>
                        <div class="col-5">
                            <label for="valor" class="form-label">Valor</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-cash-coin"></i></span>
                                <input type="text" class="form-control" id="valor" placeholder="R$ 0,00">
                            </div>
                        </div>
                    </div>

                    <!-- Linha 2: Categoria + Data -->
                    <div class="row g-3 mb-3">
                        <div class="col-7">
                            <label for="categoria" class="form-label">Categoria</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-tag-fill"></i></span>
                                <div class="form-control p-0 cat-sel-container dropdown" id="catSelWrapper">
                                    <button type="button" id="catSelBtn"
                                            class="btn cat-sel-btn w-100 h-100 d-flex align-items-center gap-2"
                                            data-bs-toggle="dropdown" aria-expanded="false">
                                        <span class="cat-sel-preview text-muted">Selecione</span>
                                        <i class="bi bi-chevron-down ms-auto" style="font-size:0.75rem;opacity:0.6;"></i>
                                    </button>
                                    <ul class="dropdown-menu cat-sel-menu" id="catSelMenu"></ul>
                                </div>
                                <input type="hidden" id="categoria" name="categoria">
                                <button class="btn" id="enviaCriarCategoria" type="button"
                                    style="background:#2C2C44;border-color:#3F3F46;color:#3B82F6;"
                                    title="Nova categoria">
                                    <i class="bi bi-plus-lg"></i>
                                </button>
                            </div>
                        </div>
                        <div class="col-5" id="dataWrapper">
                            <label for="data" class="form-label">Data</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-calendar3"></i></span>
                                <input type="date" class="form-control" id="data">
                            </div>
                        </div>
                    </div>

                    <!-- Método de pagamento (visível apenas em débito) -->
                    <div class="mb-3" id="metodoWrapper">
                        <label for="metodo" class="form-label">Método de pagamento</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-wallet2"></i></span>
                            <select class="form-select" id="metodo">
                                <option value="">Selecione</option>
                                <option value="Débito">Débito</option>
                                <option value="Dinheiro">Dinheiro</option>
                                <option value="Pix">Pix</option>
                            </select>
                        </div>
                    </div>

                    <!-- Cartão (thumbnails para crédito, select para débito) -->
                    <div class="mb-3" id="cartaoWrapper">
                        <label class="form-label">Cartão</label>
                        <!-- Thumbnails (crédito e débito com cartão) -->
                        <div class="d-flex gap-2 flex-wrap" id="cartaoSelectorModal"></div>
                        <!-- Select fallback (preenchido via AJAX) -->
                        <select class="form-select mt-2" id="cartaoSelect" style="display:none;">
                            <option value="">Selecione</option>
                        </select>
                        <input type="hidden" id="cartao" name="cartao">
                    </div>

                    <!-- Responsável -->
                    <div class="mb-3" id="responsavelWrapper">
                        <label class="form-label">
                            <i class="bi bi-person-fill me-1" style="color:var(--cor-azul);"></i>
                            Responsável pelo pagamento
                        </label>
                        <input type="hidden" id="responsavel" name="responsavel">
                        <div class="d-flex gap-2 flex-wrap" id="responsavelSelector"></div>
                    </div>

                    <!-- Switches -->
                    <div class="d-flex gap-4 mb-2">
                        <div class="form-check form-switch" id="parceladoWrapper">
                            <input class="form-check-input" type="checkbox" id="parcelado" name="parcelado">
                            <label class="form-check-label" for="parcelado">Parcelado</label>
                        </div>
                        <div class="form-check form-switch" id="recorrenteWrapper">
                            <input class="form-check-input" type="checkbox" id="recorrente" name="recorrente">
                            <label class="form-check-label" for="recorrente">Recorrente</label>
                        </div>
                    </div>

                    <!-- Nº parcelas (aparece quando parcelado ativo) -->
                    <div class="mb-2 border-parcelado" style="display:none;">
                        <label for="num_parcelas" class="form-label">Nº de parcelas</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-list-ol"></i></span>
                            <input type="number" class="form-control" id="num_parcelas"
                                placeholder="Ex: 6" min="2" max="48">
                        </div>
                    </div>

                </div><!-- /criaDespesaForm -->

                <!-- FORM CRIAR CATEGORIA -->
                <div class="criaCategoriaForm" style="display:none;">
                    <label for="nomeCategoria" class="form-label">Nome da nova categoria</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-tag-fill"></i></span>
                        <input type="text" class="form-control" id="nomeCategoria" placeholder="Ex: Alimentação">
                    </div>
                </div>

            </div><!-- /modal-body -->

            <div class="modal-footer" style="border-top:1px solid #3F3F46;">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Fechar</button>

                <div class="criaDespesaForm">
                    <button type="button" class="btn btn-success" id="adicionarDespesa">
                        Adicionar <i class="bi bi-cart-plus-fill"></i>
                    </button>
                </div>

                <div class="editaDespesaForm">
                    <button type="button" class="btn btn-warning" id="editarDespesa" style="display:none;">
                        Modificar <i class="bi bi-pencil-fill"></i>
                    </button>
                </div>

                <input type="hidden" id="gastoId">

                <div class="criaCategoriaForm" style="display:none;">
                    <button type="button" class="btn btn-success" id="criarCategoria">
                        Criar <i class="bi bi-clipboard-plus-fill"></i>
                    </button>
                </div>
            </div>

        </div>
    </div>
</div>
