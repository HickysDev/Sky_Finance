<?php
require_once(__DIR__ . '/../templates/header.php');
require_once(__DIR__ . '/../../conn/conn.php');

$conn = Database::getConnection();

?>

<div class="animate__animated animate__fadeIn">
    <div class="text-center titulo-pagina">
        <h1 class="titulo" style="font-size: 2.3vw;">Gerenciamento</h1>
        <h1 style="font-size: 4vw;"><i class="bi bi-gear-fill"></i></h1>
    </div>

    <div class="row" id="botoesGerenciamento">
        <div class="col-md-4">
            <div class="painel quadrado" id="gerenciarCartoesBtn">
                <div class="conteudo-centralizado text-center">
                    <div style="font-size: 3.5vw;">
                        <i class="bi bi-credit-card-2-front-fill"></i>
                    </div>
                    <div>
                        <span class="titulo" style="font-size: 2vw;">Cartões de crédito</span>
                    </div>
                </div>
            </div>

            <div class="painel quadrado mt-4" id="gerenciarRecorrentes">
                <div class="conteudo-centralizado text-center">
                    <div style="font-size: 3.5vw;">
                        <i class="bi bi-arrow-clockwise"></i>
                    </div>
                    <div>
                        <span class="titulo" style="font-size: 2vw;">Recorrentes</span>
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
                <!-- PREENCHE CARTOES -->
            </div>

            <div id="editarCartaoDiv" style="display: none;">
                <div style="width: 80%; margin: 0 auto;">
                    <div class="form-floating my-3">
                        <input type="text" class="form-control" id="nomeCartao" placeholder="Escreva o nome"
                            name="nomeCartao">
                        <label for="nomeCartao">Nome do cartão</label>
                    </div>

                    <div class="form-floating my-3">
                        <input type="text" class="form-control real" id="limite" placeholder="Digite o limite"
                            name="limite">
                        <label for="limite">Limite</label>
                    </div>

                    <div class="form-floating my-3">
                        <input type="number" class="form-control" id="dataFechamento"
                            placeholder="Informe a data de fechamento" name="dataFechamento">
                        <label for="dataFechamento">Dia de fechamento da fatura</label>
                    </div>

                    <div class="form-floating my-3">
                        <input type="number" class="form-control" id="dataVencimento" placeholder="Informe a data"
                            name="dataVencimento">
                        <label for="dataVencimento">Dia de vencimento da fatura</label>
                    </div>
                </div>
                <div class="text-center mt-3 mb-2">
                    <input type="hidden" id="tipoAlteracao" value="">
                    <input type="hidden" id="idCartao" value="">
                    <button class="btn btn-success" id="salvaAlteracao">Salvar alterações &nbsp; <i
                            class="bi bi-floppy-fill"></i></button>
                </div>
            </div>

        </div>
    </div>

    <div id="gerenciarCategoriasDiv" style="display: none;">
        <div class="painel">
            <div class="d-flex align-items-center justify-content-between mb-4">
                <!-- Seta de voltar -->
                <div class="d-flex align-items-center voltarBtn" style="font-size: 30px;">
                    <i class="bi bi-arrow-left"></i>
                </div>

                <!-- Título centralizado -->
                <h2 class="cor-am titulo text-center flex-grow-1 m-0 tituloCartoes" style="font-size: 2vw;">
                    Categorias Cadastradas
                </h2>

                <!-- Espaço vazio do tamanho do botão pra manter o título centralizado -->
                <div style="width: 40px;"></div>
            </div>

            <div id="mostraCategorias">
                <table class="table table-hover table-centro" id="mostraCategoriasTable">
                    <colgroup>
                        <col style="width: 86%;">
                        <col style="width: 14%">
                    </colgroup>
                    <thead class="bg-secundary">
                        <tr>
                            <th>Nome</th>
                            <th>Ação</th>
                        </tr>
                    </thead>
                    <tbody>

                    </tbody>
                </table>
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
            estado = "gerenciarCartoes";
        })
    })

    $('#gerenciarCategoriasBtn').click(function () {
        buscaCategorias();
        $('#botoesGerenciamento').fadeOut('slow', function () {
            $('#gerenciarCategoriasDiv').fadeIn('slow');
            estado = "gerenciarCategorias";
        })
    })

    $(".voltarBtn").click(function () {
        if (estado == "editaCartao") {
            $('#editarCartaoDiv, .tituloCartoes').fadeOut('slow', function () {
                $('.tituloCartoes').fadeIn('slow').html("Cartões Cadastrados")
                $('#listagemCartoes').fadeIn('slow');
                estado = "gerenciarCartoes";
            })
        } else if (estado == "gerenciarCartoes" || estado == "gerenciarCategorias") {
            console.log('#' + estado + 'Div')
            $('#' + estado + 'Div').fadeOut('slow', function () {
                $('#botoesGerenciamento').fadeIn('slow');
                estado = "inicial";
            })
        }
    })


    //! OPERACOES CARTAO
    $(document).on("click", ".editarCartao", function () {

        let id = $(this).data("id");

        editaCartao(id);

        $('#listagemCartoes, .tituloCartoes').fadeOut('slow', function () {
            $('.tituloCartoes').fadeIn('slow').html(cartoesArray[id].nome_cartao)

            $('#editarCartaoDiv').fadeIn('slow');

            estado = "editaCartao";

            $("#salvaAlteracao").html('Salvar alterações &nbsp; <i class="bi bi-floppy-fill"></i>');
            $("#tipoAlteracao").val("alteracao");
            $("#idCartao").val(id);
        })
    })

    $(document).on("click", "#salvaAlteracao", function () {
        let nomeCartao = $('#nomeCartao').val();
        let limite = $('#limite').val();
        let dataFechamento = $('#dataFechamento').val();
        let dataVencimento = $('#dataVencimento').val();
        let tipo = $("#tipoAlteracao").val();
        let idCartao = $("#idCartao").val()

        var cartaoArray = {
            "nomeCartao": nomeCartao,
            "limite": limite,
            "dataFechamento": dataFechamento,
            "dataVencimento": dataVencimento
        }

        if (tipo == "criacao") {
            criaCartao(cartaoArray, buscaCartoes);
        } else if (tipo == "alteracao") {
            alteraCartao(cartaoArray, idCartao, buscaCartoes);
        }

    })

    $(document).on("click", "#adicionarCartao", function () {
        $('#listagemCartoes, .tituloCartoes').fadeOut('slow', function () {
            $('.tituloCartoes').fadeIn('slow').html("Novo cartão")

            $('#editarCartaoDiv').fadeIn('slow');

            estado = "editaCartao";

            $("#salvaAlteracao").html('Criar <i class="bi bi-plus-lg"></i>');
            $("#tipoAlteracao").val("criar");

            $('#limite').val("");

            $('#nomeCartao').val("");
            $('#dataFechamento').val("");
            $('#dataVencimento').val("");
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

                let id = $(this).data("id");

                excluirCartao(id, buscaCartoes)

            }
        });
    });

    function editaCartao(id) {
        let cartaoSelecionado = window.cartoesArray[id];

        if (cartaoSelecionado) {
            let valorLimite = cartaoSelecionado.limite;
            let valorFormatado = valorLimite.replace('.', ',');
            $('#limite').val(valorFormatado);

            $('#nomeCartao').val(cartaoSelecionado.nome_cartao);
            $('#dataFechamento').val(cartaoSelecionado.fechamento_dia);
            $('#dataVencimento').val(cartaoSelecionado.vencimento_dia);

        } else {
            toastr.error("Cartão não encontrado!");
        }
    }

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

    function alteraCartao(cartaoArray, idCartao, callback) {
        $.ajax({
            type: "POST",
            url: "../controllers/CartoesController.php",
            data: {
                acao: "alterar",
                cartao: cartaoArray,
                idCartao: idCartao
            },
            dataType: "json",
            success: function (data) {
                if (data == true) {
                    toastr.success("Cartão criado com sucesso!");

                    if (typeof callback === 'function') {
                        callback();
                    }

                    $('#editarCartaoDiv, .tituloCartoes').fadeOut('slow', function () {
                        $('.tituloCartoes').fadeIn('slow').html("Cartões Cadastrados")
                        $('#listagemCartoes').fadeIn('slow');
                        estado = "gerenciarCartoes";
                    })

                } else {
                    toastr.error("Ocorreu um erro ao criar o cartão!");
                }
            }, error(error) {
                toastr.error("Ocorreu um erro ao criar o cartão ###!");
            }
        })
    }

    function criaCartao(cartaoArray, callback) {
        $.ajax({
            type: "POST",
            url: "../controllers/CartoesController.php",
            data: {
                acao: "adicionar",
                cartao: cartaoArray
            },
            dataType: "json",
            success: function (data) {
                if (data == true) {
                    toastr.success("Cartão criado com sucesso!");

                    if (typeof callback === 'function') {
                        callback();
                    }

                    $('#editarCartaoDiv, .tituloCartoes').fadeOut('slow', function () {
                        $('.tituloCartoes').fadeIn('slow').html("Cartões Cadastrados")
                        $('#listagemCartoes').fadeIn('slow');
                        estado = "gerenciarCartoes";
                    })

                } else {
                    toastr.error("Ocorreu um erro ao criar o cartão!");
                }
            }, error(error) {
                toastr.error("Ocorreu um erro ao criar o cartão ###!");
            }
        })
    }

    function excluirCartao(id, callback) {
        $.ajax({
            type: "POST",
            url: "../controllers/CartoesController.php",
            data: {
                acao: "excluir",
                id: id
            },
            dataType: "json",
            success: function (data) {
                if (data == true) {

                    Swal.fire(
                        'Cartão Removido!',
                        'O cartão foi removido com sucesso!',
                        'success'
                    );

                    if (typeof callback === 'function') {
                        callback();
                    }

                } else {
                    toastr.error("Ocorreu um erro ao excluir o cartão!");
                }
            }, error(error) {
                toastr.error("Ocorreu um erro ao excluir o cartão ###!");
            }
        })
    }

    //! OPERACOES CATEGORIAS

    $(document).on("click", ".editaCategoriaBtn", function () {
        const id = $(this).data("codigo");
        const $td = $(this).closest("tr").find(".nomeCategoriaTd");
        const nomeCategoria = $td.text().trim();

        var input = `<input type="text" data-codigo="${id}" class="categoriaEdicao text-center form-control w-50" style="margin:0 auto;" value="${nomeCategoria}">`;

        $td.html(input);
        $td.find('input.categoriaEdicao').focus();
        $(this).prop("disabled", true);
    });

    $(document).on("change", ".categoriaEdicao", function () {
        const id = $(this).data("codigo");
        const $input = $(this);
        const nome = $input.val().trim();
        const $tr = $input.closest("tr");
        const $td = $tr.find(".nomeCategoriaTd");
        const $btn = $tr.find(".editaCategoriaBtn");

        editaCategorias(id, nome, $td, $btn);
    });

    $(document).on("click", ".adicionarNovo", function () {
        const $tr = $(this).closest("tr");

        var conteudo = `<td colspan="2"><input type="text" class="categoriaNova text-center form-control w-50" style="margin:0 auto;"></td>`

        $tr.html(conteudo);
    });

    $(document).on("change", ".categoriaNova", function () {

        criaCategorias($(this).val(), buscaCategorias);
    });

    $(document).on("click", ".excluiCategoriaBtn", function () {
        const id = $(this).data("codigo");
        const $tr = $(this).closest("tr");

        Swal.fire({
            title: 'Tem certeza?',
            text: "Você quer remover essa categoria?",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Sim, excluir!',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {

                excluirCategorias(id, $tr)

            }
        });
    });

    function buscaCategorias() {
        $.ajax({
            type: "POST",
            url: "../controllers/CategoriaController.php",
            data: {
                acao: "busca",
            },
            dataType: "json",
            success: function (data) {

                var listagemCategorias = "";

                $.each(data, function (codigoCategoria, valoresCategoria) {
                    listagemCategorias += `<tr>
                                                <td class="nomeCategoriaTd">${valoresCategoria.nome}</td>
                                                <td>
                                                    <div class="d-flex justify-content-center align-items-center gap-3">
                                                        <button data-codigo="${valoresCategoria.id}" class="btn editaCategoriaBtn btn-sm btn-warning"><i class="bi bi-pencil-fill"></i></button>
                                                        <button data-codigo="${valoresCategoria.id}" class="btn excluiCategoriaBtn btn-sm btn-danger"><i class="bi bi-trash-fill"></i></button>
                                                    </div>
                                                </td>
                                            </tr>`
                })

                listagemCategorias += `<tr><td colspan="2"><i class="bi bi-plus-circle-fill adicionarNovo"></i></td></tr>`

                $("#mostraCategoriasTable tbody").html(listagemCategorias)
            },
            error(error) {

            }
        })
    }

    function editaCategorias(id, nome, $td, $btn) {
        $.ajax({
            type: "POST",
            url: "../controllers/CategoriaController.php",
            data: {
                acao: "editar",
                nome: nome,
                id: id
            },
            dataType: "json",
            success: function (data) {
                if (data == true) {
                    toastr.success("Categoria alterada com sucesso!");
                    $td.html(nome);
                } else {
                    toastr.error("Erro ao alterar categoria!");
                }
                $btn.prop("disabled", false);
            },
            error(error) {
                toastr.error("Erro ao alterar categoria!");
                $btn.prop("disabled", false);
            }
        });
    }

    function criaCategorias(nome, callback) {
        $.ajax({
            type: "POST",
            url: "../controllers/CategoriaController.php",
            data: {
                acao: "adicionar",
                descricao: nome
            },
            dataType: "json",
            success: function (data) {
                if (data == true) {
                    toastr.success("Categoria criada com sucesso!");

                    if (typeof callback === 'function') {
                        callback();
                    }
                } else {
                    toastr.error("Ocorreu um erro ao criar a categoria!");
                }
            }, error(error) {
                toastr.error("Ocorreu um erro ao criar a categoria ###!");
            }
        })
    }

    function excluirCategorias(id, $tr) {
        $.ajax({
            type: "POST",
            url: "../controllers/CategoriaController.php",
            data: {
                acao: "excluir",
                id: id
            },
            dataType: "json",
            success: function (data) {
                if (data == true) {

                    Swal.fire(
                        'Categoria removida!',
                        'A categoria foi removida com sucesso!',
                        'success'
                    );

                    $tr.fadeOut();
                } else {
                    toastr.error("Ocorreu um erro ao excluir a categoria!");
                }
            }, error(error) {
                toastr.error("Ocorreu um erro ao excluir a categoria ###!");
            }
        })
    }

    //! VISUAL
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
        delimiter: '.',
        decimal: ',',
        numeralDecimalMark: ',',
        stripLeadingZeroes: true
    });

</script>
<?php
require_once("../templates/footer.php");
?>