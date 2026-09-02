<?php
require_once('controller/sessao.php');
sessao::validar(array('Coordenador de Ensino','Coordenador de Curso'));
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
        <script src="js/comprovante_associar.js"></script>
        <script src="js/jquery.mask.min.js"></script>
    </head>
    <body>

        <?php include $_SESSION['topo']; ?>

        <form id="formulario">

            <input type="hidden" name="metodo" id="metodo">
            
            <!-- Alterar aqui o ID da tabela -->
            <input type="hidden" name="id_comprovante_docente" id="id_comprovante_docente">
            <input type="hidden" name="id_comprovante" id="id_comprovante">

            <div class="container-fluid">
                <div class="col-md-12">
                    <div class="panel panel-default">
                        <div class="panel-heading text-center">
                            <!-- Alterar título do painel -->
                            Associar comprovante a um professor e a uma atividade
                        </div>
                        <div class="panel-body">    

                            <nav class="nav navbar-form" style="padding-left: 0px">
                                <span class="navbar-left">
                                    <button type="button" class="btn btn-success form-control" id="btn_adicionar" >
                                        <span class="glyphicon glyphicon-plus"></span> Adicionar
                                    </button>
                                </span>
                                <span class="navbar-left" style="margin-left: 10px">
                                    <button type="button" class="btn btn-danger form-control" id="btn_voltar" onclick="location.href='comprovante.php'">
                                        <span class="glyphicon glyphicon-arrow-left" style="padding-right: 5px"></span> Voltar
                                    </button>
                                </span>                                
                            </nav>
                            
                            <div id="msg"></div>

                            <div id="div_comprovante"></div>
                            <div id="tabela"></div>

                        </div>
                    </div>       
                </div>

                <!--
                Modal para inserir e atualizar a tabela perfil
                -->
                <div id="modal_formulario" class="modal fade" role="dialog">
                    <div class="modal-dialog modal-lg">
                        <!-- Modal content-->
                        <div class="modal-content">
                            <div class="modal-header">
                                <button type="button" class="close" data-dismiss="modal">&times;</button>
                                <h4 class="modal-title">Informações sobre a disciplina</h4>
                                <div id="modal_formulario_msg"></div>
                            </div>
                            <div class="modal-body">
                                <div class="form-group" id="div_atividade"></div>
                                <div class="form-group" id="div_professor"></div>
                                <div class="form-group">
                                    <label for="horas">CHS - Carga horário semanal:</label>
                                    <input type="text" class="form-control" name="horas" id="horas">
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
                                <h4 class="modal-title">Exclusão da associação de comprovante</h4>
                                <div id="modal_confirmacao_msg"></div>
                            </div>
                            <div class="modal-body">
                                <div class="alert alert-warning">
                                    Deseja realmente excluir esta associação de comprovante?
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