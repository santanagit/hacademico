<?php
    session_start();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>    
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Horário Acadêmico">
    <meta name="author" content="Antonio Rafael Sant'Ana">
    
    <title>hAcademico</title>
    
    <!-- Bootstrap CSS file -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">

    <!-- Jquery and Bootstrap Script files -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
    
</head>
<body>
    <?php include $_SESSION['topo']; ?>
    <div class="container">
        <div class="col-md-12">
            <div class="panel panel-default">
                <div class="panel-heading text-center">
                    <span style="color:green; font-weight: bold;">Bem Vindo ao hAcademico - Sistema de Horário Acadêmico </span>
                </div>
                <div class="panel-body">
                    <h4 style="color:green"> Informações do usuário </h4>
                    <p><strong>Nome:</strong> <?=$_SESSION['nome']?> 
                    <p><strong>Login:</strong> <?=$_SESSION['email']?>
                    <p><strong>Perfil:</strong> <?=$_SESSION['perfil']?> 
                    <p><strong>Matricula:</strong> <?=$_SESSION['matricula']?>
                </div>
            </div>
        </div>       
    </div>        
</body>
</html>