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
        <script src="js/pid_gestao.js"></script>
        <script src="js/jquery.mask.min.js"></script>
    </head>
    <body>

        <?php include $_SESSION['topo']; ?>

        <form id="formulario">

            <input type="hidden" name="metodo" id="metodo">

            <!-- Alterar aqui o ID da tabela -->
            <input type="hidden" name="id_pid" id="id_pid">
            <input type="hidden" name="id_usuario" id="id_usuario">
            <input type="hidden" name="pid_correcao_inicio" id="pid_correcao_inicio">
            <input type="hidden" name="pid_correcao_fim" id="pid_correcao_fim">
            <input type="hidden" name="rid_correcao_inicio" id="rid_correcao_inicio">
            <input type="hidden" name="rid_correcao_fim" id="rid_correcao_fim">

            <div class="container-fluid">
                <div class="col-md-12">
                    <div class="panel panel-info" style="padding-bottom: 0px" id="painel_busca">
                        <div class="panel panel-heading">
                            Planejamento e relatório docente por semestre (PID/RID)
                        </div>
                        <div class="panel panel-body" style="padding-bottom: 0px">
                            <div class="container-fluid">
                                <div class="col-md-6">
                                    <div class="form-group" id="div_periodo"></div>
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
                    <div class="panel panel-success" id="painel_pid">
                        <div class="panel panel-heading"> Professores incluídos no PID/RID do semestre</div>
                        <div class="panel panel-body">        

                            <button type="button" class="btn btn-success form-control" id="btn_adicionar" style="width: 170px;text-align:center; margin-bottom:20px" onClick="abrirModal('modal_formulario','inserir',0)">
                            <span class="glyphicon glyphicon-plus"></span> Adicionar professor
                            </button>

                            <div id="msg"></div>                     
                            <div id="tabela"></div>
                        </div>
                    </div>
                </div>
            </div>          
        </form>
    </body>
</html>