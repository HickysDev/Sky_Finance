<?php
require_once(__DIR__ . '/../templates/header.php');
require_once(__DIR__ . '/../../conn/conn.php');

$conn = Database::getConnection();

$buscaCategorias = $conn->prepare("SELECT * FROM categorias");
$buscaCategorias->execute();
$categorias = $buscaCategorias->fetchAll(PDO::FETCH_ASSOC);

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
    var tipoDespesa = 'debito';
</script>

<div class="animate__animated animate__fadeIn">
    <div class="text-center titulo-pagina">
        <h1 class="titulo" style="font-size: 2.3vw;">À vista</h1>
        <h1 style="font-size: 4vw;"><i class="bi bi-currency-dollar"></i></h1>
    </div>

    <div class="d-flex justify-content-around">
        <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modalAdiciona">Adicionar Despesa <i class="bi bi-bag-plus-fill"></i></button>

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
        <h2 class="cor-am titulo text-center" style="font-size: 1.8vw;">GASTOS DESSE MÊS - R$ <span class="cor-am titulo text-center" id="gastoTotalMes"></span></h2>

        <div>
            <div id="loaderGasto" class="text-center my-3">
                <div class="spinner-border cor-am" role="status">
                    <span class="visually-hidden">Carregando...</span>
                </div>
            </div>
            <table id="gastosMes" class="table table-hover table-condensed table-centro">
                <thead class="bg-secundary">
                    <tr>
                        <th>Produto</th>
                        <th>Valor</th>
                        <th>Categoria</th>
                        <th>Tipo</th>
                        <th>Data</th>
                        <th><input type="checkbox" name="marcaTodos" class="dark-checkbox" id="marcaTodos"></th>
                    </tr>
                </thead>
                <tbody>
                    <!-- ADICIONA AQUI -->
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- MODAL -->

<?php include '../templates/modalCadastra.php'; ?>



<script>
    $('#modalAdiciona').on('show.bs.modal', function() {
        if (tipoDespesa === 'credito') {
            $('#metodo').closest('.form-floating').hide(); // esconde método
            $('#parcelado').closest('.form-check').show(); // mostra switch
            $('.border-parcelado').show(); // mostra nº de parcelas
        } else {
            $('#metodo').closest('.form-floating').show(); // mostra método
            $('#parcelado').closest('.form-check').hide(); // esconde switch
            $('.border-parcelado').hide(); // esconde nº de parcelas
        }
    });

    $(document).ready(function() {

        // Chama a função automaticamente ao carregar a página com o mês atual
        buscaTabela($('#mes').val());


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

        // Quando mudar o mês, chama a função
        $("#mes").change(function() {
            let mesSelecionado = $(this).val();
            buscaTabela(mesSelecionado);
        });

        $('#adicionarDespesa').click(function() {
            let descricao = $('#descricao').val();
            let valor = $('#valor').val();
            let categoria = $('#categoria').val();
            let cartao = $('#cartao').val() == "" ? null : $('#cartao').val();
            let metodo = $('#metodo').val();
            let data = $('#data').val();


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
                    tipo: 'debito'
                },
                dataType: "json",
                success: function(data) {
                    console.log(data)

                    buscaTabela($('#mes').val())
                },
                error: function() {
                    alert("Erro ao buscar dados.");
                }
            });
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
                        tipo: 'debito'
                    },
                    dataType: "json",
                    success: function(data) {
                        console.log(data)

                        buscaTabela($('#mes').val())
                    },
                    error: function() {
                        alert("Erro ao buscar dados.");
                    }
                });
            }

            console.log(ids); // Agora vai exibir um array correto com os IDs marcados
        });


        //Quando selecionar o input que marca todos
        $(document).on("change", "#marcaTodos", function() {
            if ($(this).prop("checked")) {
                $(".marcagasto").each(function() {
                    $(this).prop("checked", true)
                })
            } else {
                $(".marcagasto").each(function() {
                    $(this).prop("checked", false)
                })
            }
        })




        function buscaTabela(mes) {
            $('#gastosMes').hide();
            $('#loaderGasto').show();
            $.ajax({
                type: "POST",
                url: "../controllers/GastosController.php",
                data: {
                    acao: "buscar",
                    mes: mes,
                    tipo: 'debito'
                },
                dataType: "json",
                success: function(data) {
                    // Verifica se existe dataTable, se existir ele destroi para construir outra
                    if ($.fn.DataTable.isDataTable('#gastosMes')) {
                        $('#gastosMes').DataTable().destroy();
                    }

                    let tbody = $("#gastosMes tbody");
                    tbody.empty(); // Limpa a tabela antes de adicionar novos dados

                    $.each(data, function(index, gasto) {
                        if (index != "valortotal") {
                            let linha = `<tr>
                            <td>${gasto.descricao}</td>
                            <td>R$ ${gasto.valor}</td>
                            <td>${gasto.nome}</td>
                            <td>${gasto.metodo_pagamento}</td>
                            <td>${moment(gasto.data_gasto).format("DD/MM/YYYY")}</td>
                            <td><input type="checkbox" name="gasto" class="marcagasto" data-id="${gasto.id}"></td>
                        </tr>`;
                            tbody.append(linha);
                        } else if (index == "valortotal") {
                            $('#gastoTotalMes').html(gasto)
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
                            } // Desativa a ordenação na última coluna
                        ],
                        language: {
                            search: "Pesquisar:",
                            zeroRecords: "Nenhum registro encontrado",
                            emptyTable: "Nenhum dado disponível na tabela"
                        }
                    });

                    $('#gastosMes').show();
                    $('#loaderGasto').hide();

                },
                error: function() {
                    alert("Erro ao buscar dados.");
                }
            });
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
    });
</script>

<?php
require_once("../templates/footer.php");
?>