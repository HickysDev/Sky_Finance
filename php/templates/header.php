<?php
include_once(__DIR__ . '/../../conn/conn.php');

require_once(__DIR__ . '/../../conn/config.php');
?>

<!DOCTYPE html>
<html lang="pt-br" data-bs-theme="dark">

<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Bootstrap demo</title>

  <!-- CSS do Bootstrap -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-rbsA2VBKQhggwzxH7pPCaAqO46MgnOM80zW1RWuH61DGLwZJEdK2Kadq2F9CUG65" crossorigin="anonymous" />

  <!-- CSS do Bootstrap-Select -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-select@1.13.1/dist/css/bootstrap-select.min.css" rel="stylesheet" />

  <!-- CSS do Data Tables -->
  <link href="https://cdn.datatables.net/2.2.2/css/dataTables.dataTables.min.css" rel="stylesheet" />

  <!-- Tippy.js CSS -->
  <link href="https://unpkg.com/tippy.js@6.3.1/dist/tippy.css" rel="stylesheet">


  <!-- Fontes do Google -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&display=swap" rel="stylesheet">

  <!-- Seu estilo customizado -->
  <link rel="stylesheet" href="../../styles/style.css" />

  <!-- �cones do Bootstrap -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">

  <!-- jQuery (necess�rio para o Bootstrap-Select) -->
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

  <!-- JS do Bootstrap 5 -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>

  <!-- JS do dataTable -->
  <script src="https://cdn.datatables.net/2.2.2/js/dataTables.min.js"></script>

  <!-- JS CLEAVE -->
  <script src="https://cdn.jsdelivr.net/npm/cleave.js/dist/cleave.min.js"></script>

  <!-- Development -->
  <script src="https://unpkg.com/@popperjs/core@2/dist/umd/popper.min.js"></script>
  <script src="https://unpkg.com/tippy.js@6/dist/tippy-bundle.umd.js"></script>

  <!-- Production -->
  <script src="https://unpkg.com/@popperjs/core@2"></script>
  <script src="https://unpkg.com/tippy.js@6"></script>

  <!-- Moment.js -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.4/moment.min.js"></script>

  <!-- Toastr -->
  <link href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css" rel="stylesheet" />
  <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>


  <!-- Animate.css -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" />

</head>

<body>

  <header>
    <nav class="navbar navbar-expand-lg bg-primary">
      <div class="container-fluid">
        <!-- Ícone e Nome -->
        <a class="navbar-brand tituloNav" href="index.php">
          <i class="bi bi-cash-stack"></i>
        </a>

        <!-- Botão para o menu em telas pequenas -->
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
          <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarNav">
          <!-- Itens do Menu -->
          <ul class="navbar-nav">
            <li class="nav-item">
              <a class="nav-link titulo" href="<?= BASE_URL ?>php/views/cartaocredito.php">Cartão de Crédito</a>
            </li>
            <li class="nav-item">
              <a class="nav-link titulo" href="<?= BASE_URL ?>php/views/debito.php">Débito</a>
            </li>
            <li class="nav-item">
              <a class="nav-link titulo" href="#">Finanças</a>
            </li>
            <li class="nav-item">
              <a class="nav-link titulo" href="<?= BASE_URL ?>php/views/gerenciamento.php">Gerenciar</a>
            </li>

          </ul>

          <!-- Área de Login (Empurrado para a direita) -->
          <div class="ms-auto" style="margin-right: 10px;">
            <a class="nav-link titulo" href="telalogin.php">Login</a>
          </div>
        </div>
      </div>
    </nav>
  </header>

  <main>
    <div class="corpo-site">