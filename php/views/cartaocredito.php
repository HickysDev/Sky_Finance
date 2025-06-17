<?php
require_once(__DIR__ . '/../templates/header.php');
require_once(__DIR__ . '/../../conn/conn.php');



$conn = Database::getConnection();

$buscaCartao = $conn->prepare("SELECT * FROM cartoes_credito");
$buscaCartao->execute();
$cartoes = $buscaCartao->fetchAll(PDO::FETCH_ASSOC);

$meses = [
    1 => "Janeiro",
    2 => "Fevereiro",
    3 => "Março",
    4 => "Abril",
    5 => "Maio",
    6 => "Junho",
    7 => "Julho",
    8 => "Agosto",
    9 => "Setembro",
    10 => "Outubro",
    11 => "Novembro",
    12 => "Dezembro"
];

$mesAtual = date('n');
?>
<script>
    var tipoDespesa = 'credito';
</script>

<div class="animate__animated animate__fadeIn">
    <div class="text-center titulo-pagina">
        <h1 class="titulo mt-3" style="font-size: 2.3vw;">Cartão de Crédito</h1>

        <div class="cartao-bloco mx-auto text-center">
            <div class="d-flex justify-content-center align-items-center gap-2">
                <i class="bi bi-arrow-left-circle-fill anterior botoesCartao" id="btn-anterior"></i>

                <h1 class="mb-0" id="cartao-container">
                    <i class="bi bi-credit-card-2-back-fill cartao-icon"></i>
                </h1>

                <i class="bi bi-arrow-right-circle-fill proximo botoesCartao" id="btn-proximo"></i>
            </div>

            <div class="cartao-label titulo" id="cartao-nome">Todos</div>
            <input type="hidden" id="cartaoAtual">
        </div>

    </div>




    <div class="d-flex justify-content-around">
        <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modalAdiciona">Adicionar Despesa <i
                class="bi bi-bag-plus-fill"></i></button>

        <div class="d-flex" style="gap: 10px;">
            <i class="bi bi-arrow-left-square-fill botao botaoEsquerda"></i>

            <select class="form-select text-center" id="mes" style="width: 11vw;">
                <?php
                foreach ($meses as $mes => $nomeMes): ?>
                    <option value="<?= $mes ?>" <?= ($mes == $mesAtual) ? "selected" : "" ?>><?= $nomeMes ?></option>
                <?php endforeach ?>
            </select>

            <i class="bi bi-arrow-right-square-fill botao botaoDireita"></i>
        </div>

        <button class="btn btn-danger" id="removerDespesa">Remover Despesa <i class="bi bi-bag-x-fill"></i></button>
    </div>

    <div class="painel mt-2">
        <div class="row responsivo1600">
            <div class="col-lg-6 col-esquerda">
                <h2 class="cor-am titulo text-center" style="font-size: 1.8vw;">GASTOS DESSE MÊS - R$ <span
                        class="cor-am titulo text-center" id="gastoTotalMes"></span></h2>

                <div class="table-responsive">
                    <div id="loaderGastosMes" class="text-center my-3">
                        <div class="spinner-border cor-am" role="status">
                            <span class="visually-hidden">Carregando...</span>
                        </div>
                    </div>
                    <table id="gastosMes" class="table table-condensed table-centro">
                        <thead class="bg-secundary">
                            <tr>
                                <th>Produto</th>
                                <th>Valor</th>
                                <th>Categoria</th>
                                <th>Tipo</th>
                                <th>Data</th>
                                <th><input type="checkbox" name="marcaTodosMesNormal" class="dark-checkbox"
                                        id="marcaTodosMesNormal"></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>Calça</td>
                                <td>R$ 32,50</td>
                                <td>Roupa</td>
                                <td>22/01/2001</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

            </div>
            <div class="col-lg-6">
                <h2 class="cor-am titulo text-center" style="font-size: 1.8vw;">GASTOS RECORRENTES - R$ <span
                        class="cor-am titulo text-center" id="gastoRecorrenteTotalMes"></span></h2>

                <div class="table-responsive">
                    <div id="loaderRecorrentes" class="text-center my-3">
                        <div class="spinner-border cor-am" role="status">
                            <span class="visually-hidden">Carregando...</span>
                        </div>
                    </div>

                    <table id="gastosRecorrentes" class="table table-hover table-condensed table-centro">
                        <thead class="bg-secundary">
                            <tr>
                                <th>Produto</th>
                                <th>Total</th>
                                <th>Categoria</th>
                                <th>Parcela</th>
                                <th>Valor Parcela</th>
                                <th>Data Compra</th>
                                <th><input type="checkbox" name="marcaTodosMesRecorrente" class="dark-checkbox"
                                        id="marcaTodosMesRecorrente"></th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>

            </div>
        </div>
        <hr>
        <div class="mt-2">
            <h2 class="cor-am titulo text-center titulo-azul" style="font-size: 1.8vw;">GASTOS TOTAIS - R$ <span
                    class="cor-am titulo text-center titulo-azul" id="gastoTotal"></span></h2>
        </div>
    </div>
</div>

<div class="painel p-4 my-4">
    <h2 class="cor-am titulo text-center" style="font-size: 1.8vw;">
        Fatura Mensal
    </h2>
    <h3 style="font-size: 1.2vw;" class="cor-am titulo text-center">
        <span id="valorTotalFatura"></span>
    </h3>

    <table id="faturaMes" class="table table-hover table-condensed table-centro">
        <thead class="bg-secundary">
            <tr>
                <th>Produto</th>
                <th>Categoria</th>
                <th>Parcela</th>
                <th>Valor</th>
                <th>Data Compra</th>
            </tr>
        </thead>
        <tbody>
        </tbody>
    </table>
</div>

<!-- MODAL -->

<?php include '../templates/modalCadastra.php'; ?>

<script>
    $('#modalAdiciona').on('show.bs.modal', function() {
        if (tipoDespesa === 'credito') {
            $('#metodo').closest('.form-floating').hide(); // esconde método
            $('#parcelado').closest('.form-check').show(); // mostra switch
        } else {
            $('#metodo').closest('.form-floating').show(); // mostra método
            $('#parcelado').closest('.form-check').hide(); // esconde switch
            $('.border-parcelado').hide(); // esconde nº de parcelas
        }
    });

    $(document).ready(function() {

        // Chama a função automaticamente ao carregar a página com o mês atual
        buscaTabelaMes($('#mes').val(), function() {
            buscaTabelaRecorrente($('#mes').val());
        });

        buscaFatura($('#mes').val());

        // Busca Categorias
        buscaCategorias();

        var valorTotal = 0;
        var cartoesArray = [];

        buscaCartoes();

        const cartoes = [{
                icone: 'bi-credit-card-2-back-fill',
                nome: 'Todos'
            },
            {
                icone: 'bi-credit-card-fill',
                nome: 'Nubank'
            },
            {
                icone: 'bi-credit-card-2-front-fill',
                nome: 'Caixa'
            }
        ];

        $('#btn-anterior').on('click', function() {
            trocarCartao(-1, function() {
                buscaTabelaMes($('#mes').val(), function() {
                    buscaTabelaRecorrente($('#mes').val())
                }), buscaFatura($('#mes').val())
            });
        });

        $('#btn-proximo').on('click', function() {
            trocarCartao(1, function() {
                buscaTabelaMes($('#mes').val(), function() {
                    buscaTabelaRecorrente($('#mes').val())
                }), buscaFatura($('#mes').val())
            });
        });

        let indexAtual = 0;

        function trocarCartao(direcao, callback, callback2) {
            const $cartao = $('#cartao-container');
            const $nome = $('#cartao-nome');
            cartoesArray = window.cartoesArray;

            // Adiciona animação de saída
            $cartao.addClass('animate__animated animate__fadeOut');
            $nome.addClass('animate__animated animate__fadeOut');

            setTimeout(() => {
                // Atualiza índice
                indexAtual += direcao;
                if (indexAtual < 0) indexAtual = cartoesArray.length - 1;
                if (indexAtual >= cartoesArray.length) indexAtual = 0;

                // Atualiza conteúdo
                $cartao.html(`<i class="bi bi-credit-card-2-back-fill cartao-icon"></i>`);
                $nome.text(cartoesArray[indexAtual].nome_cartao);

                // Define o ID do cartão atual (IMPORTANTE: isso é síncrono)
                $("#cartaoAtual").val(cartoesArray[indexAtual].id);

                // Troca animação
                $cartao.removeClass('animate__fadeOut').addClass('animate__fadeIn');
                $nome.removeClass('animate__fadeOut').addClass('animate__fadeIn');

                setTimeout(() => {
                    $cartao.removeClass('animate__animated animate__fadeIn');
                    $nome.removeClass('animate__animated animate__fadeIn');

                    // AGORA chamamos o callback, quando tudo estiver pronto
                    if (typeof callback === 'function') {
                        callback();
                    }

                    if (typeof callback2 === 'function') {
                        callback2();
                    }
                }, 300);
            }, 200);
        }

        $('.botaoEsquerda').click(function() {
            let selectMes = $('#mes');
            let mesAtual = parseInt(selectMes.val());

            if (mesAtual > 1) { // Garante que não vá antes de janeiro (mês 1)
                selectMes.val(mesAtual - 1).change();
            }
        });

        $('.botaoDireita').click(function() {
            let selectMes = $('#mes');
            let mesAtual = parseInt(selectMes.val());

            if (mesAtual < 12) { // Garante que não vá depois de dezembro (mês 12)
                selectMes.val(mesAtual + 1).change();
            }
        });

        function buscaCartoes() {
            $.ajax({
                type: "POST",
                url: "../controllers/CartoesController.php",
                data: {
                    acao: "busca",
                },
                dataType: "json",
                success: function(data) {
                    window.cartoesArray = Object.values(data);

                    // Adiciona o elemento com nome_cartao: "Todos" no índice 0
                    window.cartoesArray.unshift({
                        id: null,
                        usuario_id: null,
                        nome_cartao: "Todos",
                        limite: null,
                        fechamento_dia: null
                    });
                },
                error(error) {

                }
            })
        }

        // Quando mudar o mês, chama a função
        $("#mes").change(function() {
            let mesSelecionado = $(this).val();
            buscaTabelaMes(mesSelecionado, function() {
                buscaTabelaRecorrente(mesSelecionado);
            });

            buscaFatura(mesSelecionado);
        });

        $('#parcelado').change(function() {
            if ($(this).prop("checked")) {
                $('.border-parcelado').slideDown()
                $(this).val("checked")
            } else {
                $('.border-parcelado').slideUp()
                $(this).val("")
            }
        })

        $('#enviaCriarCategoria').click(function() {
            $('.criaDespesaForm').fadeOut(function() {
                $('.criaCategoriaForm').fadeIn();
            });
        })

        $('.voltarBtn').click(function() {
            $('.criaCategoriaForm').fadeOut(function() {
                $('.criaDespesaForm').fadeIn();
            });
        })

        $('#criarCategoria').click(function() {
            let descricao = $('#nomeCategoria').val();

            $.ajax({
                type: "POST",
                url: "../controllers/CategoriaController.php",
                data: {
                    acao: "adicionar",
                    descricao: descricao
                },
                dataType: "json",
                success: function(data) {

                    if (data == true) {
                        toastr.success("Categoria criada com sucesso!");
                        buscaCategorias();
                    } else {
                        toastr.error("Erro ao criar categoria!");
                    }
                },
                error: function() {
                    toastr.error("Erro ao criar categoria!");
                }
            });
        })

        $('#adicionarDespesa').click(function() {
            let descricao = $('#descricao').val();
            let valor = $('#valor').val();
            let categoria = $('#categoria').val();
            let cartao = $('#cartao').val();
            let metodo = "Crédito";
            let data = $('#data').val();
            let num_parcelas = $('#num_parcelas').val();
            let parcelado = $('#parcelado').is(':checked') ? 'S' : 'N';

            $.ajax({
                type: "POST",
                url: "../controllers/GastosController.php",
                data: {
                    acao: "adicionar",
                    descricao: descricao,
                    valor: valor,
                    categoria: categoria,
                    metodo: metodo,
                    cartao: cartao,
                    data: data,
                    parcelado: parcelado,
                    num_parcelas: num_parcelas,
                    tipo: 'credito'
                },
                dataType: "json",
                success: function(data) {

                    buscaTabelaMes($('#mes').val(), function() {
                        buscaTabelaRecorrente($('#mes').val());
                    });

                    toastr.success("Despesa criada com sucesso!");
                },
                error: function() {
                    toastr.error("Erro ao criar despesas!");
                }
            });
        })

        //Quando selecionar o input que marca todos
        $(document).on("change", "#marcaTodosMesNormal", function() {
            if ($(this).prop("checked")) {
                $(".marcagastoNormal").each(function() {
                    $(this).prop("checked", true)
                })
            } else {
                $(".marcagastoNormal").each(function() {
                    $(this).prop("checked", false)
                })
            }
        })

        //Quando selecionar o input que marca todos
        $(document).on("change", "#marcaTodosMesRecorrente", function() {
            if ($(this).prop("checked")) {
                $(".marcagastoRecorrente").each(function() {
                    $(this).prop("checked", true)
                })
            } else {
                $(".marcagastoRecorrente").each(function() {
                    $(this).prop("checked", false)
                })
            }
        })

        $(document).on("click", "#removerDespesa", function() {
            var ids = [];

            $('.marcagasto:checked').each(function() {
                let id = $(this).data("id");
                let parcelado = $(this).data("parcelado");

                ids.push({
                    id: id,
                    parcelado: parcelado
                });
            });

            if (ids.length > 0) {
                $.ajax({
                    type: "POST",
                    url: "../controllers/GastosController.php",
                    data: {
                        acao: "remover",
                        ids: ids,
                        tipo: 'credito'
                    },
                    dataType: "json",
                    success: function(data) {
                        buscaTabelaMes($('#mes').val(), function() {
                            buscaTabelaRecorrente($('#mes').val());
                        });

                        toastr.success("Despesa removida com sucesso!");
                    },
                    error: function() {
                        toastr.error("Erro ao remover despesa!");
                    }
                });
            }
        });

        function buscaTabelaMes(mes, callback) {
            $('#gastosMes').hide();
            $('#loaderGastosMes').show();
            window.valorTotal = 0;

            var cartaoId = $("#cartaoAtual").val();

            $.ajax({
                type: "POST",
                url: "../controllers/GastosController.php",
                data: {
                    acao: "buscar",
                    mes: mes,
                    cartaoId: cartaoId,
                    tipo: 'credito'
                },
                dataType: "json",
                success: function(data) {
                    if ($.fn.DataTable.isDataTable('#gastosMes')) {
                        $('#gastosMes').DataTable().destroy();
                    }

                    let tbody = $("#gastosMes tbody");
                    tbody.empty();

                    $.each(data, function(index, gasto) {
                        if (index != "valortotal") {
                            let linha = `<tr>
                        <td>${gasto.descricao}</td>
                        <td>R$ ${gasto.valor}</td>
                        <td>${gasto.nome}</td>
                        <td>${gasto.metodo_pagamento}</td>
                        <td>${moment(gasto.data_gasto).format("DD/MM/YYYY")}</td>
                        <td><input type="checkbox" name="gasto" class="marcagasto marcagastoNormal" data-parcelado="${gasto.parcelado}" data-id="${gasto.id}"></td>
                    </tr>`;
                            tbody.append(linha);
                        } else if (index == "valortotal") {
                            $('#gastoTotalMes').html(gasto);

                            let valorFormatado = gasto.replace(/\./g, '').replace(',', '.');
                            let valorNumerico = parseFloat(valorFormatado);

                            window.valorTotal += valorNumerico;
                        }
                    });

                    $('#gastosMes').DataTable({
                        paging: false,
                        info: false,
                        lengthChange: false,
                        searching: true,
                        ordering: true,
                        columnDefs: [{
                            orderable: false,
                            targets: 5
                        }],
                        language: {
                            search: "Pesquisar:",
                            zeroRecords: "Nenhum registro encontrado",
                            emptyTable: "Nenhum dado disponível na tabela",
                        }
                    });

                    $('#gastosMes').show();
                    $('#loaderGastosMes').hide();

                    if (typeof callback === 'function') {
                        callback();
                    }
                },
                error: function() {
                    toastr.error("Erro ao buscar dados!");
                }
            });
        }

        function buscaFatura(mes) {
            $('#faturaMes').hide();
            $('#loaderRecorrentes').show();

            var cartaoId = $("#cartaoAtual").val();

            $.ajax({
                type: "POST",
                url: "../controllers/GastosController.php",
                data: {
                    acao: "buscaFatura",
                    mes: mes,
                    cartaoId: cartaoId
                },
                dataType: "json",
                success: function(data) {
                    // Verifica se existe dataTable, se existir ele destroi para construir outra
                    if ($.fn.DataTable.isDataTable('#faturaMes')) {
                        $('#faturaMes').DataTable().destroy();
                    }

                    let tbody = $("#faturaMes tbody");
                    tbody.empty(); // Limpa a tabela antes de adicionar novos dados

                    $.each(data, function(index, gasto) {
                        if (index != "valortotal") {
                            let linha = `<tr>
                            <td>${gasto.descricao}</td>
                            <td>${gasto.categoria}</td>
                            <td>${gasto.numero_parcela ? gasto.numero_parcela : 1}/${gasto.parcelas_total ? gasto.parcelas_total : 1}</td>
                            <td>R$ ${gasto.valor_parcela}</td>
                            <td>${moment(gasto.data_gasto).format("DD/MM/YYYY")}</td>
                        </tr>`;
                            tbody.append(linha);
                        } else if (index == "valortotal") {
                            $('#valorTotalFatura').html("R$ " + gasto)
                        }

                    });



                    $('#faturaMes').DataTable({
                        paging: false,
                        info: false,
                        lengthChange: false,
                        searching: true,
                        ordering: true,
                        language: {
                            search: "Pesquisar:",
                            zeroRecords: "Nenhum registro encontrado",
                            emptyTable: "Nenhum dado disponível na tabela"
                        }
                    });

                    $('#faturaMes').show();
                    $('#loaderRecorrentes').hide();
                },
                error: function() {
                    toastr.error("Erro ao buscar dados!");
                }
            });
        }

        //RECORRENTES
        function buscaTabelaRecorrente(mes) {
            $('#gastosRecorrentes').hide();
            $('#loaderRecorrentes').show();

            var cartaoId = $("#cartaoAtual").val();

            $.ajax({
                type: "POST",
                url: "../controllers/GastosController.php",
                data: {
                    acao: "buscarRecorrente",
                    mes: mes,
                    tipo: 'credito'
                },
                dataType: "json",
                success: function(data) {
                    // Verifica se existe dataTable, se existir ele destroi para construir outra
                    if ($.fn.DataTable.isDataTable('#gastosRecorrentes')) {
                        $('#gastosRecorrentes').DataTable().destroy();
                    }

                    let tbody = $("#gastosRecorrentes tbody");
                    tbody.empty(); // Limpa a tabela antes de adicionar novos dados

                    $.each(data, function(index, gasto) {
                        if (index != "valortotal") {
                            let linha = `<tr>
                            <td>${gasto.descricao}</td>
                            <td>R$ ${gasto.valor}</td>
                            <td>${gasto.categoria}</td>
                            <td>${gasto.numero_parcela}/${gasto.parcelas_total}</td>
                            <td>R$ ${gasto.valor_parcela}</td>
                            <td>${moment(gasto.data_gasto).format("DD/MM/YYYY")}</td>
                            <td><input type="checkbox" name="gasto" class="marcagasto marcagastoRecorrente" data-parcelado="${gasto.parcelado}" data-id="${gasto.id}"></td>
                        </tr>`;
                            tbody.append(linha);
                        } else if (index == "valortotal") {
                            $('#gastoRecorrenteTotalMes').html(gasto)
                        }

                        if (gasto.valor_parcela) {
                            let valorFormatado = gasto.valor_parcela.replace(/\./g, '').replace(',', '.');
                            let valorNumerico = parseFloat(valorFormatado);

                            window.valorTotal += valorNumerico;

                            $('#gastoTotal').html(formatarNumeroBrasileiro(window.valorTotal));
                        } else if (window.valorTotal >= 0) {
                            $('#gastoTotal').html(formatarNumeroBrasileiro(window.valorTotal));
                        }

                    });



                    $('#gastosRecorrentes').DataTable({
                        paging: false,
                        info: false,
                        lengthChange: false,
                        searching: true,
                        ordering: true,
                        language: {
                            search: "Pesquisar:",
                            zeroRecords: "Nenhum registro encontrado",
                            emptyTable: "Nenhum dado disponível na tabela"
                        }
                    });

                    $('#gastosRecorrentes').show();
                    $('#loaderRecorrentes').hide();
                },
                error: function() {
                    toastr.error("Erro ao buscar dados!");
                }
            });
        }

        function buscaCategorias() {
            $.ajax({
                type: "POST",
                url: "../controllers/CategoriaController.php",
                data: {
                    acao: "busca",
                },
                dataType: "json",
                success: function(data) {
                    let options = '<option value="">Selecione</option>';

                    $.each(data, function(index, categoria) {
                        options += `<option value="${categoria.id}">${categoria.nome}</option>`;
                    });

                    $('#categoria').html(options);
                },
                error(error) {

                }
            })
        }

        function dataFormatada() {
            var data = new Date(),
                dia = data.getDate().toString().padStart(2, '0'),
                mes = (data.getMonth() + 1).toString().padStart(2, '0'), //+1 pois no getMonth Janeiro começa com zero.
                ano = data.getFullYear();
            return dia + "/" + mes + "/" + ano;
        }

        // Aplica o Tippy ao botão
        tippy('#removerDespesa', {
            content: 'Selecione as despesas que quer remover e clique aqui', // O conteúdo do tooltip
            animation: 'fade', // Animação do tooltip
            theme: 'dark', // Tema de cor
            placement: 'top', // Onde o tooltip será exibido (pode ser 'top', 'bottom', 'left', 'right')
            arrow: true, // Adiciona uma seta
            duration: 300, // Duração da animação
        });

        tippy('#enviaCriarCategoria', {
            content: 'Crie uma nova categoria', // O conteúdo do tooltip
            animation: 'fade', // Animação do tooltip
            theme: 'dark', // Tema de cor
            placement: 'top', // Onde o tooltip será exibido (pode ser 'top', 'bottom', 'left', 'right')
            arrow: true, // Adiciona uma seta
            duration: 300, // Duração da animação
        });

        var cleave = new Cleave('#valor', {
            numeral: true,
            numeralThousandsGroupStyle: 'thousand',
            prefix: 'R$ ', // Adiciona o "R$" no início
            noImmediatePrefix: true,
            delimiter: '.', // Separador de milhar
            decimal: ',', // Separador decimal
            numeralDecimalMark: ',', // Define a vírgula como separador decimal
            stripLeadingZeroes: true
        });

        function formatarNumeroBrasileiro(numero) {
            return numero.toLocaleString('pt-BR', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });
        }

    })
</script>

<?php
require_once("../templates/footer.php");
?>