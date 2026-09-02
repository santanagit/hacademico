<?php
require_once('controller/sessao.php');
sessao::validar(array('Diretor', 'Professor', 'Coordenador de Ensino', 'Coordenador de Curso', 'Assistência Estudantil','Registro Escolar'));
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
        <script src="js/lattes.js"></script>

    </head>
    <body>
        <?php include $_SESSION['topo']; ?>
        <form id="formulario">
            <input type="hidden" name="metodo" id="metodo">
            <input type="hidden" name="usuario" id="usuario">

            <div class="container-fluid">
                <div class="col-md-12">
                    <div class="panel panel-info">
                        <div class="panel panel-heading">
                            Informações do currículo lattes extraído da Plataforma Lattes CNPq
                        </div>
                        <div class="panel panel-body" style="margin-bottom: 0px; padding-bottom: 0px">
                            <div class="container-fluid">
                                <div class="col-md-4">
                                    <label for="id_usuario">Professor</label>
                                    <select name="id_usuario" id="id_usuario" class="form-control" style="width:100%"></select>                         
                                </div>                                    
                                <div class="col-md-6 form-group">
                                    <div class="form-group" style="padding-top:24px">
                                    <button type="button" class="btn btn-default form-control" id="btn_buscar" style="width: 100px">Buscar</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>            
            
            <div class="container-fluid">
                <div class="col-md-12">
                    <div class="panel panel-default">
                        <div class="panel-heading text-center text-uppercase">Lattes do Professor</div>            
                        <div class="panel-body">
                            <div id="lattes_professor"></div>
                        </div>            
                    </div>
                </div>     
            </div>

        </form>
    </body>
</html>
