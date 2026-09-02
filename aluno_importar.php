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
        <script src="js/aluno_importar.js"></script>
    </head>
    <body>

        <?php include $_SESSION['topo']; ?>

        <form id="formulario" class="form-inline">
            <input type="hidden" name="metodo" id="metodo">                 

            <div class="container-fluid">
                <div class="col-md-12">
                    <div class="panel panel-info">
                        <div class="panel panel-heading">
                            Importação de alunos do SIGAA
                        </div>
                        <div class="panel panel-body">
                            <div class="container-fluid">
                                <div id="div_curso" class="col-md-6" style="margin-top:10px"></div>                                    
                                <div class="col-md-4" style="margin-top:10px">
                                    <div class="form-group">
                                        <label for="arquivo">Documento(CSV):</label>
                                        <input type="file" accept=".CSV" class="form-control" style="width:100%" name="arquivo" id="arquivo">
                                    </div>
                                </div>
                                <div class="col-md-2" style="margin-top:10px">
                                    <br>
                                    <div class="form-group">
                                        <button type="button" class="btn btn-success form-control" id="btn_importar" style="width: 100px">Importar</button>
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
                        <div class="panel-heading text-center text-uppercase">Resultado da importação dos alunos</div>            
                        <div class="panel-body">            
                            <div style="margin-top:20px;overflow-x: auto" id="tabela"></div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </body>
</html>