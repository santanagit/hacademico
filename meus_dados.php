<?php
    require_once('controller/sessao.php');
    sessao::validar(array('Coordenador de Ensino', 'Coordenador de Curso', 'Professor', 'Registro Escolar', 'Assistência Estudantil'));
?>
<!DOCTYPE html>
<html lang="pt-BR">
    <head>    
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta name="description" content="Horário Acadêmico">
        <meta name="author" content="Antonio Rafael Santana">

        <title>hAcademico</title>

        <!-- Bootstrap CSS file -->
        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">

        <!-- Jquery and Bootstrap Script files -->
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
        <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
        
        <!-- Others JS Files -->
        <script src="js/meus_dados.js"></script>
    </head>
    <body>

        <?php include $_SESSION['topo']; ?>
        <form id="formulario">

        <input type="hidden" name="metodo" id="metodo">
        <input type="hidden" name="id_usuario" id="id_usuario">

        <div class="container">
            <div class="col-md-12">
                <div class="panel panel-default">
                    <div class="panel-heading text-center">Informações do usuário</div>
                    <div class="panel-body">    

                        <div id="msg" style="margin-top: 20px"></div>

                         <div class="form-group">
                            <label for="perfil">Perfil do usuário:</label>
                            <div class="form-control" id="perfil" readonly></div>                                                                
                         </div>
                        <div class="form-group">
                            <label for="nome">Nome do usuário:</label>
                            <input type="text" class="form-control" name="nome" id="nome">
                        </div>
                        <div class="form-group">
                            <label for="matricula">Matrícula:</label>
                            <input type="text" class="form-control" name="matricula" id="matricula">
                        </div>
                        <div class="form-group">
                            <label for="email">Email:</label>
                            <input type="text" class="form-control" name="email" id="email">
                        </div>
                        <div class="form-group">
                            <label for="senha">Senha:</label>
                            <input type="password" class="form-control" name="senha" id="senha">
                        </div>
                        <div class="form-group">
                            <label for="confirmar_senha">Confirmar senha:</label>
                            <input type="password" class="form-control" name="confirmar_senha" id="confirmar_senha">
                        </div>
                        
                        <button type="button" class="btn btn-success" id="btn_gravar">Atualizar</button>
                    </div>
                </div>       
            </div>
        </div>
        </form>
    </body>
</html>