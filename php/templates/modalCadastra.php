<div class="modal fade" id="modalAdiciona" tabindex="-1" aria-labelledby="modalAdicionaLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary">
                <h5 class="modal-title">Adicionar Despesa</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body bg-body">
                <div class="criaCategoriaForm" style="font-size: 30px; display: none;">
                    <i class="bi bi-arrow-left voltarBtn"></i>
                </div>
                <div style="width: 50%; margin: 0 auto;">
                    <div class="criaDespesaForm">
                        <div class="form-floating mb-3 mt-3">
                            <input type="text" class="form-control" id="descricao" placeholder="Escreva a descrição"
                                name="descricao">
                            <label for="descricao">Descrição</label>
                        </div>
                        <div class="form-floating mb-3 mt-3">
                            <input type="text" class="form-control" id="valor" placeholder="Escreva o valor"
                                name="valor">
                            <label for="valor">Valor (R$)</label>
                        </div>
                        <div class="d-flex align-items-center mb-3 mt-3" style="gap: 10px;">
                            <div class="form-floating flex-grow-1">
                                <select class="form-select" id="categoria" name="categoria">
                                    <option value="">Selecione</option>
                                    <?php foreach ($categorias as $categoria): ?>
                                        <option value="<?= $categoria['id'] ?>"><?= $categoria['nome'] ?></option>
                                    <?php endforeach ?>
                                </select>
                                <label for="categoria" class="form-label">Selecione a categoria:</label>
                            </div>
                            <button class="btn btn-success" id="enviaCriarCategoria" type="button">
                                <i class="bi bi-plus-lg"></i>
                            </button>
                        </div>

                        <div class="form-floating mb-3 mt-3">
                            <select class="form-select" id="metodo" name="metodo">
                                <option value="">Selecione</option>
                                <option value="Débito">Débito</option>
                                <option value="Dinheiro">Dinheiro</option>
                                <option value="Pix">Pix</option>
                            </select>
                            <label for="metodo" class="form-label">Selecione método de pagamento:</label>
                        </div>

                        <div class="form-floating mb-3 mt-3">
                            <select class="form-select" id="cartao" name="cartao">
                                <option value="">Selecione</option>
                                <?php foreach ($cartoes as $cartao): ?>
                                    <option value="<?= $cartao['id'] ?>"><?= $cartao['nome_cartao'] ?></option>
                                <?php endforeach ?>
                            </select>
                            <label for="cartao" class="form-label">Selecione o cartão:</label>
                        </div>
                        <div class="form-floating mb-3 mt-3">
                            <input type="date" class="form-control" id="data" placeholder="Informe a data" name="data">
                            <label for="data">Data</label>
                        </div>
                        <div class="form-check form-switch">
                            <label class="form-check-label" for="parcelado">Parcelado</label>
                            <input class="form-check-input" type="checkbox" id="parcelado" name="parcelado">
                        </div>

                        <div class="form-check form-switch">
                            <label class="form-check-label" for="recorrente">Recorrente</label>
                            <input class="form-check-input" type="checkbox" id="recorrente" name="recorrente">
                        </div>

                        <div class="form-floating mb-3 mt-3 border-parcelado">
                            <input type="text" class="form-control" id="num_parcelas" placeholder="Escreva o valor"
                                name="num_parcelas">
                            <label for="num_parcelas">Nº de parcelas:</label>
                        </div>
                    </div>

                    <div class="criaCategoriaForm" style="display: none;">
                        <div class="form-floating mb-3 mt-3">
                            <input type="text" class="form-control" id="nomeCategoria"
                                placeholder="Escreva o nome da categoria" name="nomeCategoria">
                            <label for="nomeCategoria">Nome da categoria</label>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Fechar</button>

                <div class="criaDespesaForm">
                    <button type="button" class="btn btn-success" id="adicionarDespesa">Adicionar <i
                            class="bi bi-cart-plus-fill"></i></button>
                </div>

                <div class="editaDespesaForm">
                    <button type="button" style="display: none;" class="btn btn-success" id="editarDespesa">Modificar <i class="bi bi-pencil-fill"></i></button>
                </div>

                <input type="hidden" id="gastoId">

                <div class="criaCategoriaForm" style="display: none;">
                    <button type="button" class="btn btn-success" id="criarCategoria">Criar <i
                            class="bi bi-clipboard-plus-fill"></i></button>
                </div>
            </div>
        </div>
    </div>
</div>