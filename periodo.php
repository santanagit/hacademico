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

        <title>Perfil</title>

        <!-- Bootstrap CSS file -->
        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">

        <!-- Jquery and Bootstrap Script files -->
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
        <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>

        <!-- Others JS Files -->
        <script src="js/jquery.mask.min.js"></script>
        <script src="js/periodo.js"></script>
    </head>
    <body>

        <?php include $_SESSION['topo']; ?>

        <form id="formulario">

            <input type="hidden" name="metodo" id="metodo">

            <!-- Alterar aqui o ID da tabela -->
            <input type="hidden" name="id_periodo" id="id_periodo">

            <div class="container-fluid">
                <div class="col-md-12">
                    <div class="panel panel-default">
                        <div class="panel-heading text-center">
                            <!-- Alterar título do painel -->
                            Períodos Letivos
                        </div>
                        <div class="panel-body">    
                            <br>
                            <div id="msg"></div>

                            <nav class="nav navbar-form" style="padding-left: 0px">
                                <span class="navbar-left">
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
                Modal para inserir e atualizar a tabela periodo
                -->
                <div id="modal_formulario" class="modal fade" role="dialog">
                    <div class="modal-dialog modal-lg">
                        <!-- Modal content-->
                        <div class="modal-content">
                            <div class="modal-header">
                                <button type="button" class="close" data-dismiss="modal">&times;</button>
                                <h4 class="modal-title">Informações do período</h4>
                                <div id="modal_formulario_msg"></div>
                            </div>
                            <div class="modal-body">
                                <div class="form-group">
                                    <label for="ano">Ano:</label>
                                    <input type="text" class="form-control" name="ano" id="ano">
                                </div>
                            </div>
                            <div class="modal-body">
                                <div class="form-group">
                                    <label for="semestre">Semestre:</label>
                                    <input type="text" class="form-control" name="semestre" id="semestre">
                                </div>
                            </div>
                            <div class="modal-body">
                                <div class="form-group">
                                    <label for="data_inicio">Data início:</label>
                                    <input type="text" class="form-control" name="data_inicio" id="data_inicio">
                                </div>
                            </div>
                            <div class="modal-body">
                                <div class="form-group">
                                    <label for="data_fim">Data fim:</label>
                                    <input type="text" class="form-control" name="data_fim" id="data_fim">
                                </div>
                            </div>                            
                            <div class="modal-body">
                                <div class="form-group">
                                    <label for="pid_inicio">PID início:</label>
                                    <input type="text" class="form-control" name="pid_inicio" id="pid_inicio">
                                </div>
                            </div>
                            <div class="modal-body">
                                <div class="form-group">
                                    <label for="pid_fim">PID fim:</label>
                                    <input type="text" class="form-control" name="pid_fim" id="pid_fim">
                                </div>
                            </div> 
                            <div class="modal-body">
                                <div class="form-group">
                                    <label for="rid_inicio">RID início:</label>
                                    <input type="text" class="form-control" name="rid_inicio" id="rid_inicio">
                                </div>
                            </div>
                            <div class="modal-body">
                                <div class="form-group">
                                    <label for="rid_fim">RID fim:</label>
                                    <input type="text" class="form-control" name="rid_fim" id="rid_fim">
                                </div>
                            </div>
                            <div class="modal-body">
                                <div class="form-group form-inline">
                                    <label for="publicado" style="display: inline">Publicado:</label>
                                    <input type="checkbox" class="form-control" style="margin-top: 0px; padding-top: 0px;" name="publicado" id="publicado">
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
                                <h4 class="modal-title">Exclusão de período</h4>
                                <div id="modal_confirmacao_msg"></div>
                            </div>
                            <div class="modal-body">
                                <div class="alert alert-warning">
                                    Deseja realmente excluir este período?
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-danger" data-dismiss="modal">Cancelar</button>
                                <button type="button" class="btn btn-success" data-dismiss="modal" id="btn_sim">Sim</button>
                            </div>
                        </div>
                    </div>   
                </div>        
        </form>
    </body>
</html>