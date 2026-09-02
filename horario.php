<?php
require_once('controller/sessao.php');
sessao::validar(array('Coordenador de Ensino', 'Coordenador de Curso'));
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
        <script src="js/horario.js"></script>
        <script src="js/jquery.mask.min.js"></script></head>

    <style>
        .glyphicon {
            font-family: Verdana;
        }
        .glyphicon:before{
            font-family:'Glyphicons Halflings';
        }            
    </style>
    <body>

        <?php include $_SESSION['topo']; ?>

        <form id="formulario">

            <input type="hidden" name="metodo" id="metodo">
            <input type="hidden" name="id_dia" id="id_dia">
            <input type="hidden" name="id_hora" id="id_hora">
            <input type="hidden" name="id_oferta_disciplina" id="id_oferta_disciplina">
            <input type="hidden" name="id_usuario" id="id_usuario">
            <input type="hidden" name="id_turma" id="id_turma">
            <input type="hidden" name="id_sala" id="id_sala">
            <input type="hidden" name="id_sala_antiga" id="id_sala_antiga">
            <input type="hidden" name="disciplina_antiga" id="disciplina_antiga">

            <div class="container-fluid">
                <div class="col-md-12">
                    <div class="panel panel-info">
                        <div class="panel panel-heading">
                            Horário das Disciplinas por Turma
                        </div>
                        <div class="panel panel-body">
                            <div class="container-fluid">
                                <div class="col-md-10">
                                    <div class="form-group" id="div_periodo"></div>
                                </div>
                                <div class="col-md-2">
                                    <label for="id">&nbsp;</label>
                                    <button type="button" class="btn btn-success form-control" id="btn_buscar">Buscar</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div id="msg"></div>
            <div id="moldura"></div>

        </form>
    </body>
</html>