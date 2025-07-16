<?php
require_once(__DIR__ . '/../templates/header.php');
require_once(__DIR__ . '/../../conn/conn.php');

$conn = Database::getConnection();

?>
<style>
    .modal-lg, .modal-xl {
        --bs-modal-width: 75% !important;
    }
</style>

<div class="animate__animated animate__fadeIn">
    <div class="text-center titulo-pagina">
        <h1 class="titulo" style="font-size: 2.3vw;">Finanças</h1>
        <h1 style="font-size: 4vw;"><i class="bi bi-bank"></i></h1>
    </div>

    <div class="container-fluid px-0 py-4">
        <!-- Renda mensal (continua 100%) -->
        <div class="mx-0 mb-3">
            <div class="painel shadow card-renda">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center gap-4">
                        <h4 class="card-title titulo" style="font-size: 1.8vw;">Renda mensal:</h4>

                        <div class="d-flex flex-column text-center text-success">
                            <span>(Salário)</span>
                            <span style="font-size: 1.5vw;">R$10.000,00</span>
                        </div>
                    </div>

                    <div>
                        <button class="btn btn-sm btn-warning btn-expansivo" id="editaRenda"><i
                                class="bi bi-pencil-fill"></i></button>
                    </div>
                </div>
            </div>
        </div>

        <div class="mx-0 mb-3">
            <div class="painel shadow card-renda">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center gap-3">
                        <h4 class="card-title titulo" style="font-size: 1.8vw;">META DE ECONOMIA:</h4>
                        <span style="font-size: 1.5vw;">R$10.000,00</span>
                    </div>

                    <div>
                        <button class="btn btn-sm btn-warning btn-expansivo"><i class="bi bi-pencil-fill"></i></button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Cofrinhos e Meta de economia com altura proporcional -->
        <div class="mx-0 gx-3">
            <div class="painel">
                <h4 class="text-center titulo mb-1" style="font-size: 1.8vw;">Cofrinhos &nbsp; <i
                        class="bi bi-piggy-bank-fill"></i></h4>

                <div class="container-fluid px-0 py-2">
                    <div class="text-center">
                        <h2>EM BREVE...</h2>
                    </div>
                    <div class="row mx-0" style="display: none;">
                        <!-- Card 1 -->
                        <div class="col-12 col-sm-6 col-md-3 mb-4">
                            <div class="card bg-dark text-white h-100 shadow">
                                <img src="https://lumiere-a.akamaihd.net/v1/images/iron_man_marvel_d9ce0209.jpeg?region=36,0,713,399"
                                    class="card-img-top" alt="...">
                                <div class="card-body">
                                    <h5 class="card-title">Card 1</h5>
                                    <p class="card-text">Texto de exemplo para o card 1.</p>
                                    <div class="text-end">
                                        <a href="#" class="btn btn-primary">Acessar</a>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Card 2 -->
                        <div class="col-12 col-sm-6 col-md-3 mb-4">
                            <div class="card bg-dark text-white h-100 shadow">
                                <img src="https://cdn.motor1.com/images/mgl/AkB8vL/s3/fiat-mobi-2023.jpg"
                                    class="card-img-top" alt="...">
                                <div class="card-body">
                                    <h5 class="card-title">Card 2</h5>
                                    <p class="card-text">Texto de exemplo para o card 2.</p>
                                    <a href="#" class="btn btn-primary">Acessar</a>
                                </div>
                            </div>
                        </div>

                        <!-- Card 3 -->
                        <div class="col-12 col-sm-6 col-md-3 mb-4">
                            <div class="card bg-dark text-white h-100 shadow">
                                <img src="https://m.media-amazon.com/images/I/715zrA5cmLL._AC_SL1500_.jpg"
                                    class="card-img-top" alt="...">
                                <div class="card-body">
                                    <h5 class="card-title">Card 3</h5>
                                    <p class="card-text">Texto de exemplo para o card 3.</p>
                                    <a href="#" class="btn btn-primary">Acessar</a>
                                </div>
                            </div>
                        </div>

                        <!-- Card 4 -->
                        <div class="col-12 col-sm-6 col-md-3 mb-4">
                            <div class="card bg-dark text-white h-100 shadow">
                                <img src="https://clcfernandes.adv.br/wp-content/uploads/2022/08/img-conheca-os-diferentes-tipos-de-regimes-de-bens-do-casamento-civil-1060x600.jpg"
                                    class="card-img-top" alt="...">
                                <div class="card-body">
                                    <h5 class="card-title">Card 4</h5>
                                    <p class="card-text">Texto de exemplo para o card 4.</p>
                                    <a href="#" class="btn btn-primary">Acessar</a>
                                </div>
                            </div>
                        </div>

                        <!-- Card 5 (nova linha automaticamente em telas ≥ md) -->
                        <div class="col-12 col-sm-6 col-md-3 mb-4">
                            <div class="card bg-dark text-white h-100 shadow">
                                <img src="https://i0.wp.com/60mais.com.br/wp-content/uploads/2020/08/60-mais-dicas-para-viagem-ao-redor-do-mundo-03.jpg?fit=1000%2C664&ssl=1"
                                    class="card-img-top" alt="...">
                                <div class="card-body">
                                    <h5 class="card-title">Card 5</h5>
                                    <p class="card-text">Texto de exemplo para o card 5.</p>
                                    <a href="#" class="btn btn-primary">Acessar</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalRenda" tabindex="-1" aria-labelledby="modalRendaLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary">
                <h5 class="modal-title">Gerenciar Despesas</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body bg-body">

            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Fechar</button>

                <div class="criaDespesaForm">
                    <button type="button" class="btn btn-success" id="adicionarDespesa">Adicionar <i
                            class="bi bi-cart-plus-fill"></i></button>
                </div>

                <div class="editaDespesaForm">
                    <button type="button" style="display: none;" class="btn btn-success" id="editarDespesa">Modificar <i
                            class="bi bi-pencil-fill"></i></button>
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
<script>
    $(document).ready(function () {
        $("#editaRenda").click(function () {
            $("#modalRenda").modal("show");
        })

        function buscaRenda() {
            $.ajax({
                type: "POST",
                url: "../controllers/FinancasController.php",
                data: {
                    acao: "adicionar",

                },
                dataType: "json",
                success: function (data) {
                    console.log(data)

                    buscaTabela($('#mes').val())
                },
                error: function () {
                    alert("Erro ao buscar dados.");
                }
            });
        }
    })
</script>