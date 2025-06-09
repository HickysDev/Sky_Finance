<?php
require_once(__DIR__ . '/../templates/header.php');
require_once(__DIR__ . '/../../conn/conn.php');
?>

<div class="painel mt-2" style="margin: 0 400px;">
    <div class="d-flex justify-content-center flex-column">
        <div class="d-flex justify-content-center align-items-center" style="font-size: 210px;">
            <i class="bi bi-person-circle"></i>
        </div>
        <div class="conteiner-login">
            <div>
                <label for="login"><b>Usuário:</b></label>
                <input type="text" name="login" id="login" class="form-control">
            </div>

            <div class="mt-2">
                <label for="senha"><b>Senha:</b></label>
                <input type="password" name="senha" id="senha" class="form-control">
            </div>

            <div class="my-3 text-end">
                <button class="btn btn-lg btn-primary btn-expansivo"><i class="bi bi-arrow-right"></i></button>
            </div>
        </div>
    </div>
</div>

<?php
require_once("../templates/footer.php");
?>