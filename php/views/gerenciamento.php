<?php
require_once(__DIR__ . '/../templates/header.php');
require_once(__DIR__ . '/../../conn/conn.php');

$conn = Database::getConnection();

?>

<div class="animate__animated animate__fadeIn">
    <div class="text-center titulo-pagina">
        <h1 style="font-size: 4vw;"><i class="bi bi-gear-fill"></i></h1>
        <h1 class="titulo" style="font-size: 2.3vw;">Gerenciamento</h1>
    </div>

    <div class="row" id="botoesGerenciamento">
        <div class="col-md-4">
            <div class="painel quadrado" id="gerenciarCartoesBtn">
                <div class="conteudo-centralizado text-center">
                    <div style="font-size: 3.5vw;">
                        <i class="bi bi-credit-card-2-front-fill"></i>
                    </div>
                    <div>
                        <span class="titulo" style="font-size: 2vw;">Cartões</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="painel quadrado" id="gerenciarCategoriasBtn">
                <div class="conteudo-centralizado text-center">
                    <div style="font-size: 3.5vw;">
                        <i class="bi bi-list-columns"></i>
                    </div>
                    <div>
                        <span class="titulo" style="font-size: 2vw;">Categorias</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="painel quadrado" id="gerenciarUsuarioBtn">
                <div class="conteudo-centralizado text-center">
                    <div style="font-size: 3.5vw;">
                        <i class="bi bi-person-gear"></i>
                    </div>
                    <div>
                        <span class="titulo" style="font-size: 2vw;">Conta</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div id="gerenciarCartoesDiv" style="display: none;">
        <div class="painel">
            <div class="d-flex align-items-center justify-content-between mb-4">
                <!-- Seta de voltar -->
                <div class="d-flex align-items-center voltarBtn" style="font-size: 30px;">
                    <i class="bi bi-arrow-left"></i>
                </div>

                <!-- Título centralizado -->
                <h2 class="cor-am titulo text-center flex-grow-1 m-0 tituloCartoes" style="font-size: 2vw;">
                    Cartões Cadastrados
                </h2>

                <!-- Espaço vazio do tamanho do botão pra manter o título centralizado -->
                <div style="width: 40px;"></div>
            </div>


            <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-4 mt-3" id="listagemCartoes">

            </div>

            <div id="editarCartaoDiv" style="display: none;">
                <div style="width: 80%; margin: 0 auto;">
                    <div class="form-floating my-3">
                        <input type="text" class="form-control" id="nomeCartao" placeholder="Escreva o nome"
                            name="nomeCartao">
                        <label for="nomeCartao">Nome do cartão:</label>
                    </div>

                    <div class="form-floating my-3">
                        <input type="text" class="form-control real" id="limite" placeholder="Digite o limite"
                            name="limite">
                        <label for="limite">Limite:</label>
                    </div>

                    <div class="form-floating my-3">
                        <input type="date" class="form-control" id="dataFechamento"
                            placeholder="Informe a data de fechamento" name="dataFechamento">
                        <label for="dataFechamento">Data de fechamento da fatura</label>
                    </div>

                    <div class="form-floating my-3">
                        <input type="date" class="form-control" id="dataVencimento" placeholder="Informe a data"
                            name="dataVencimento">
                        <label for="dataVencimento">Data Vencimento da fatura</label>
                    </div>
                </div>
                <div class="text-center mt-3 mb-2">
                    <input type="hidden" id="tipoAlteracao" value="">
                    <button class="btn btn-success" id="salvaAlteracao">Salvar alterações &nbsp; <i
                            class="bi bi-floppy-fill"></i></button>
                </div>
            </div>

        </div>
    </div>


</div>

<script>

    var estado = "inicial";
    var cartoesArray = [];


    $('#gerenciarCartoesBtn').click(function () {
        buscaCartoes();
        $('#botoesGerenciamento').fadeOut('slow', function () {
            $('#gerenciarCartoesDiv').fadeIn('slow');
            estado = "gerenciarCartao";
        })
    })

    $(document).on("click", ".editarCartao", function () {
        $('#listagemCartoes, .tituloCartoes').fadeOut('slow', function () {
            $('.tituloCartoes').fadeIn('slow').html("Santander")

            $('#editarCartaoDiv').fadeIn('slow');

            estado = "editaCartao";

            $("#salvaAlteracao").html('Salvar alterações &nbsp; <i class="bi bi-floppy-fill"></i>');
            $("#tipoAlteracao").val("alteracao");
        })
    })

    $(".voltarBtn").click(function () {
        if (estado == "editaCartao") {
            $('#editarCartaoDiv, .tituloCartoes').fadeOut('slow', function () {
                $('.tituloCartoes').fadeIn('slow').html("Cartões Cadastrados")
                $('#listagemCartoes').fadeIn('slow');
                estado = "gerenciarCartao";
            })
        } else if (estado == "gerenciarCartao") {
            $('#gerenciarCartoesDiv').fadeOut('slow', function () {
                $('#botoesGerenciamento').fadeIn('slow');
                estado = "inicial";
            })
        }
    })

    function buscaCartoes() {
        $.ajax({
            type: "POST",
            url: "../controllers/CartoesController.php",
            data: {
                acao: "busca",
            },
            dataType: "json",
            success: function (data) {
                window.cartoesArray = data;

                var listagemCartoes = "";

                $.each(data, function (chave, valoresCartoes) {
                    listagemCartoes += `<div class="col">
                                        <div class="cartao-box position-relative">
                                            <!-- Botões de ação -->
                                            <div class="botoes-cartao">
                                                <button class="btn btn-sm btn-outline-warning me-1 editarCartao" data-id="${valoresCartoes.id}" title="Editar">
                                                    <i class="bi bi-pencil-fill"></i>
                                                </button>
                                                <button class="btn btn-sm btn-outline-danger excluirCartao" data-id="${valoresCartoes.id}" title="Excluir">
                                                    <i class="bi bi-trash-fill"></i>
                                                </button>
                                            </div>

                                            <!-- Conteúdo do cartão -->
                                            <div class="cartao-conteudo">
                                                <i class="bi bi-credit-card-fill"></i>
                                                <div class="cartao-nome">${valoresCartoes.nome_cartao}</div>
                                            </div>
                                        </div>
                                    </div>`
                })

                listagemCartoes += `<div class="col">
                                        <div class="cartao-box position-relative">
                                            <div class="cartao-conteudo" id="adicionarCartao">
                                                <i class="bi bi-plus-circle"></i>
                                            </div>
                                        </div>
                                    </div>`

                $("#tipoAlteracao").val("criacao");
                $("#listagemCartoes").html(listagemCartoes)
            },
            error(error) {

            }
        })
    }

    $(document).on("click", "#adicionarCartao", function () {
        $('#listagemCartoes, .tituloCartoes').fadeOut('slow', function () {
            $('.tituloCartoes').fadeIn('slow').html("Novo cartão")

            $('#editarCartaoDiv').fadeIn('slow');

            estado = "editaCartao";

            $("#salvaAlteracao").html('Criar <i class="bi bi-plus-lg"></i>');
            $("#tipoAlteracao").val("criar");
        })
    })

    $(document).on("click", ".excluirCartao", function () {
        Swal.fire({
            title: 'Tem certeza?',
            text: "Você quer remover esse cartão?",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Sim, excluir!',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire(
                    'Cartão Removido!',
                    'O cartão foi removido com sucesso!',
                    'success'
                );
            }
        });
    });


    $(document).on("mouseenter", "#adicionarCartao", function () {
        $(this).html('<i class="bi bi-plus-circle-fill"></i>');
    })

    $(document).on("mouseleave", "#adicionarCartao", function () {
        $(this).html('<i class="bi bi-plus-circle"></i>');
    })

    tippy('#gerenciarCartoesBtn', {
        content: 'Crie ou edite os seus cartões', // O conteúdo do tooltip
        animation: 'fade', // Animação do tooltip
        theme: 'dark', // Tema de cor
        placement: 'top', // Onde o tooltip será exibido (pode ser 'top', 'bottom', 'left', 'right')
        arrow: true, // Adiciona uma seta
        duration: 300, // Duração da animação
    });

    tippy('#gerenciarCategoriasBtn', {
        content: 'Crie ou edite os suas categorias de compra', // O conteúdo do tooltip
        animation: 'fade', // Animação do tooltip
        theme: 'dark', // Tema de cor
        placement: 'top', // Onde o tooltip será exibido (pode ser 'top', 'bottom', 'left', 'right')
        arrow: true, // Adiciona uma seta
        duration: 300, // Duração da animação
    });

    tippy('#gerenciarUsuarioBtn', {
        content: 'Edite suas informações pessoais', // O conteúdo do tooltip
        animation: 'fade', // Animação do tooltip
        theme: 'dark', // Tema de cor
        placement: 'top', // Onde o tooltip será exibido (pode ser 'top', 'bottom', 'left', 'right')
        arrow: true, // Adiciona uma seta
        duration: 300, // Duração da animação
    });

    var cleave = new Cleave('.real', {
        numeral: true,
        numeralThousandsGroupStyle: 'thousand',
        prefix: 'R$ ',
        noImmediatePrefix: true,
        delimiter: '.',
        decimal: ',',
        numeralDecimalMark: ',',
        stripLeadingZeroes: true
    });

</script>
<?php
require_once("../templates/footer.php");
?>