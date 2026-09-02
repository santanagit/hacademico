<?php
require_once('controller/sessao.php');
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
        <script src="js/mapa_sala.js"></script>
    </head>
    <body>

        <?php include $_SESSION['topo']; ?>

        <form id="formulario" name="form_troca" class="form-inline">
            <input type="hidden" name="metodo" id="metodo">                 

            <div class="container-fluid">
                <div class="col-md-12">
                    <div class="panel panel-info">
                        <div class="panel panel-heading">
                            Informações da turma e semestre
                        </div>
                        <div class="panel panel-body">
                            <div class="container-fluid">
                                <div class="col-md-2">
                                    <div class="form-group" id="div_periodo"></div>
                                </div>
                                <div class="col-md-4">
                                    <label for="id_turma">Sala</label>
                                    <select name="id_sala" id="id_sala" class="form-control" style="width:100%"></select>                         
                                </div>                                    
                                <div class="col-md-6 form-group">
                                    <div class="form-group" style="padding-top:24px">
                                        <button type="button" class="btn btn-default form-control" id="btn_buscar" style="width: 100px">Buscar</button>
                                        <button type="button" class="btn btn-default" aria-label="Left Align" id="btn_imprimir">
                                            <span class="glyphicon glyphicon-print" aria-hidden="true"></span>
                                        </button>
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
                        <div class="panel-heading text-center text-uppercase">Mapas das Salas de Aula</div>            
                        <div class="panel-body">            
                            <div style="margin-top:20px;overflow-x: auto" id="tabela"></div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </body>
</html>