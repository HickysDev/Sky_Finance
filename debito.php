<?php
require_once("php/header.php");

include_once("conn/conn.php");

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

<div class="text-center titulo-pagina">
    <h1 style="font-size: 4vw;"><i class="bi bi-currency-dollar"></i></h1>
    <h1 class="titulo" style="font-size: 2.3vw;">À vista</h1>
</div>

<div class="d-flex justify-content-around">
    <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#exampleModal">Adicionar Despesa <i class="bi bi-bag-plus-fill"></i></button>

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

<!-- MODAL -->

<div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary">
                <h5 class="modal-title">Adicionar Despesa</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body bg-body">
                <div style="width: 50%; margin: 0 auto;">
                    <div class="form-floating mb-3 mt-3">
                        <input type="text" class="form-control" id="descricao" placeholder="Escreva a descrição" name="descricao">
                        <label for="descricao">Descrição</label>
                    </div>
                    <div class="form-floating mb-3 mt-3">
                        <input type="text" class="form-control" id="valor" placeholder="Escreva o valor" name="valor">
                        <label for="valor">Valor (R$)</label>
                    </div>
                    <div class="form-floating mb-3 mt-3">
                        <select class="form-select" id="categoria" name="categoria">
                            <option value="">Selecione</option>
                            <?php foreach ($categorias as $categoria): ?>
                                <option value="<?= $categoria['id'] ?>"><?= $categoria['nome'] ?></option>
                            <?php endforeach ?>
                        </select>
                        <label for="categoria" class="form-label">Selecione a categoria:</label>
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
                        <label for="cartao" class="form-label">Selecione o cartão: (se houver)</label>
                    </div>
                    <div class="form-floating mb-3 mt-3">
                        <input type="date" class="form-control" id="data" placeholder="Informe a data" name="data">
                        <label for="data">Data</label>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                <button type="button" class="btn btn-primary" id="adicionarDespesa">Adicionar <i class="bi bi-cart-plus-fill"></i></button>
            </div>
        </div>
    </div>
</div>



<script>
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
                url: "php/views/GastosController.php",
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
                ids.push($(this).data("id")); // Usa $(this) para pegar o ID correto
            });

            if (ids.length > 0) {
                $.ajax({
                    type: "POST",
                    url: "php/views/GastosController.php",
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
            $.ajax({
                type: "POST",
                url: "php/views/GastosController.php",
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
            delimiter: '.', // Separador de milhar
            decimal: ',', // Separador decimal
            numeralDecimalMark: ',', // Define a vírgula como separador decimal
            stripLeadingZeroes: true
        });
    });
</script>

<?php
require_once("php/footer.php");
?>

<?php
require_once("php/footer.php");
?>