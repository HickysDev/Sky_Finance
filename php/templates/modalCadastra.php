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
                        <label class="form-label">Método de pagamento</label>
                        <div class="d-flex gap-2" id="metodoBtnsWrap">
                            <button type="button" class="btn btn-outline-secondary metodo-btn flex-fill" data-metodo="Débito">
                                <i class="bi bi-credit-card me-1"></i>Débito
                            </button>
                            <button type="button" class="btn btn-outline-secondary metodo-btn flex-fill" data-metodo="Dinheiro">
                                <i class="bi bi-cash me-1"></i>Dinheiro
                            </button>
                            <button type="button" class="btn btn-outline-secondary metodo-btn flex-fill" data-metodo="Pix">
                                <i class="bi bi-qr-code me-1"></i>Pix
                            </button>
                        </div>
                        <select id="metodo" style="display:none;">
                            <option value="">Selecione</option>
                            <option value="Débito">Débito</option>
                            <option value="Dinheiro">Dinheiro</option>
                            <option value="Pix">Pix</option>
                        </select>
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

                    <!-- Tipo de lançamento -->
                    <div class="mb-3" id="tipoLancamentoWrapper">
                        <label class="form-label">Tipo de lançamento</label>
                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-outline-secondary tipo-lanc-btn active flex-fill" data-tipo="normal">
                                <i class="bi bi-check2 me-1"></i>Normal
                            </button>
                            <button type="button" class="btn btn-outline-secondary tipo-lanc-btn flex-fill" data-tipo="parcelado">
                                <i class="bi bi-layout-split me-1"></i>Parcelado
                            </button>
                            <button type="button" class="btn btn-outline-secondary tipo-lanc-btn flex-fill" data-tipo="recorrente">
                                <i class="bi bi-arrow-repeat me-1"></i>Recorrente
                            </button>
                        </div>
                        <input type="checkbox" id="parcelado" name="parcelado" style="display:none;">
                        <input type="checkbox" id="recorrente" name="recorrente" style="display:none;">
                    </div>

                    <!-- Nº parcelas (aparece quando parcelado ativo) -->
                    <div class="mb-2 border-parcelado" style="display:none;">
                        <label class="form-label">Nº de parcelas</label>
                        <div class="d-flex align-items-center gap-3" id="numParcelasControl">
                            <button type="button" class="btn btn-outline-secondary btn-sm px-3" id="btnParcelasMenos">
                                <i class="bi bi-dash-lg"></i>
                            </button>
                            <span id="numParcelasDisplay" class="fs-5 fw-bold" style="min-width:2rem;text-align:center;">2</span>
                            <button type="button" class="btn btn-outline-secondary btn-sm px-3" id="btnParcelasMais">
                                <i class="bi bi-plus-lg"></i>
                            </button>
                            <span style="font-size:0.82rem;color:var(--cor-texto-off);">parcelas</span>
                        </div>
                        <div id="numParcelasPreview" class="mt-2" style="font-size:0.82rem;color:var(--cor-texto-off);"></div>
                        <input type="hidden" id="num_parcelas" value="2">
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
