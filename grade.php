<?php
require_once('controller/sessao.php');
sessao::validar(array('Coordenador de Ensino','Coordenador de Curso','Registro Escolar'));
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
        <script src="js/grade.js"></script>
    </head>
    <body>

        <?php include $_SESSION['topo']; ?>

        <form id="formulario">

            <input type="hidden" name="metodo" id="metodo">

            <!-- Alterar aqui o ID da tabela -->
            <input type="hidden" name="id_grade" id="id_grade">
            <input type="hidden" name="id_curso" id="id_curso" value="<?=$_GET['id_curso']?>">

            <div class="container-fluid">
                <div class="col-md-12">
                    <div class="panel panel-default">
                        <div class="panel-heading text-center">
                            <!-- Alterar título do painel -->
                            Grade curricular
                        </div>
                        <div class="panel-body">    
                            <br>
                            <div id="msg"></div>

                            <nav class="nav navbar-form" style="padding-left: 0px">
                                <span class="navbar-left">
                                    <button type="button" class="btn btn-info form-control" id="btn_voltar" onclick="location.href='curso.php'">
                                        <span class="glyphicon glyphicon-arrow-left"></span> Voltar
                                    </button>

                                    <button type="button" class="btn btn-success form-control" id="btn_adicionar" >
                                        <span class="glyphicon glyphicon-plus"></span> Adicionar
                                    </button>
                                </span>
                              
                                <span class="navbar-right">
                                    <input type="text" name="filtro" id="filtro" class="form-control input-sm" size="35" placeholder="Buscar">
                                    <button type="button" class="btn btn-default form-control input-sm" id="btn_buscar" >
                                        <span class="glyphicon glyphicon-search"></span>
                                    </button>                                                                         
                                </span>
                            </nav>

                            <div id="tabela"></div>

                            <nav class="nav navbar-form" style="padding-left: 0px">
                                <div class="navbar-left">
                                    <label for="registros">Registros por Página</label>
                                    <input class="form-control input-sm" type="text" name="registros" id="registros" size="1">
                                </div>
                                <div class="navbar-right">
                                    <button type="button" class="form-control input-sm" id="btn_anterior">Anterior</button>
                                    <input class="form-control input-sm" type="text" name="pagina" id="pagina" size="1">
                                    DE
                                    <input class="form-control input-sm" type="text" name="total_paginas" id="total_paginas" size="1" disabled>
                                    <button type="button" class="form-control input-sm" id="btn_proximo">Próximo</button>                              
                                </div>
                            </nav>
                        </div>
                    </div>       
                </div>


                <!--
                Modal para inserir e atualizar a tabela grade
                -->
                <div id="modal_formulario" class="modal fade" role="dialog">
                    <div class="modal-dialog modal-lg">
                        <!-- Modal content-->
                        <div class="modal-content">
                            <div class="modal-header">
                                <button type="button" class="close" data-dismiss="modal">&times;</button>
                                <h4 class="modal-title">Informações da grade</h4>
                                <div id="modal_formulario_msg"></div>
                            </div>
                            <div class="modal-body">                                                                                          
                                <div id="matriz"></div>
                                <div class="form-group">
                                    <label for="modulo">Módulo:</label>
                                    <input type="text" class="form-control" name="modulo" id="modulo">
                                </div>
                                <div class="form-group" id="div_disciplina"></div>                             
                                <div class="form-group">
                                    <label for="cod_sigaa">Código da disciplina no SIGAA:</label>
                                    <input type="text" class="form-control" name="cod_sigaa" id="cod_sigaa">
                                </div>
                                <div class="form-group">
                                    <label for="ementa">Ementa:</label>
                                    <textarea rows="5" class="form-control" name="ementa" id="ementa"></textarea>
                                </div>                            
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-danger" data-dismiss="modal">Cancelar</button>
                                <button type="button" class="btn btn-success" id="btn_gravar">Gravar</button>
                            </div>
                        </div>
                    </div>   
                </div>


                <!--
                Modal para confirmação de exclusão
                -->
                <div id="modal_confirmacao" class="modal fade" role="dialog">
                    <div class="modal-dialog modal-sm">
                        <!-- Modal content-->
                        <div class="modal-content">
                            <div class="modal-header">
                                <button type="button" class="close" data-dismiss="modal">&times;</button>
                                <h4 class="modal-title">Exclusão de grade</h4>
                                <div id="modal_confirmacao_msg"></div>
                            </div>
                            <div class="modal-body">
                                <div class="alert alert-warning">
                                    Deseja realmente excluir este registro?
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-danger" data-dismiss="modal">Cancelar</button>
                                <button type="button" class="btn btn-success" data-dismiss="modal" id="btn_sim">Sim</button>
                            </div>
                        </div>
                    </div>   
                </div>
            </div>
        </form>
    </body>
</html>