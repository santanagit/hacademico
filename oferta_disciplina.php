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
        <script src="js/oferta_disciplina.js"></script>
        <script src="js/jquery.mask.min.js"></script>
    </head>
    <body>

        <?php include $_SESSION['topo']; ?>

        <form id="formulario">

            <input type="hidden" name="metodo" id="metodo">

            <!-- Alterar aqui o ID da tabela -->
            <input type="hidden" name="id_oferta_disciplina" id="id_oferta_disciplina">
            <input type="hidden" name="id_usuario_antigo" id="id_usuario_antigo">
            <input type="hidden" name="professor_novo" id="professor_novo">

            <div class="container-fluid">
                <div class="col-md-12">
                    <div class="panel panel-info" style="padding-bottom: 0px" id="painel_busca">
                        <div class="panel panel-heading">
                            Informações da turma e semestre
                        </div>
                        <div class="panel panel-body" style="padding-bottom: 0px">
                            <div class="container-fluid">
                                <div class="col-md-2">
                                    <div class="form-group" id="div_periodo"></div>
                                </div>
                                <div class="col-md-2">
                                    <label for="id_nucleo_busca">Núcleo</label>
                                    <select name="id_nucleo_busca" id="id_nucleo_busca" class="form-control" style="width:100%"></select>                         
                                </div>                                 
                                <div class="col-md-2">
                                    <label for="id_turma_busca">Turma</label>
                                    <select name="id_turma_busca" id="id_turma_busca" class="form-control" style="width:100%"></select>                         
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


                    
            <div id="tabela"></div>

            <!--
            Modal para inserir 
            -->
            <div id="modal_formulario" class="modal fade" role="dialog">
                <div class="modal-dialog modal-lg">
                    <!-- Modal content-->
                    <div class="modal-content">
                        <div class="modal-header bg-info">
                            <button type="button" class="close" data-dismiss="modal">&times;</button>
                            <h4 class="modal-title">Informações da oferta de disciplina</h4>
                            <div id="modal_formulario_msg"></div>
                        </div>
                        <div class="modal-body">                                                                                          
                            <div class="form-group" id="div_turma"></div>
                            <div class="form-group" id="div_disciplina"></div>
                            <div class="form-group">
                                <label for="chs">CHS - Carga horária semanal (Encontros Semanais - Presenciais + EAD)</label>
                                <input type="text" class="form-control" id="chs" name="chs">
                            </div>
                            <div class="form-group">
                                <label for="chs_ead">CHS EAD - Carga horária semanal EAD (Encontros Semanais EAD)</label>
                                <input type="text" class="form-control" id="chs_ead" name="chs_ead">
                            </div>
                            <div class="form-group">
                                <label for="cht">CHT - Carga horária total da disciplina (Carga Horária Real)</label>
                                <input type="number" readonly min="0" max="200" step=".01" class="form-control" id="cht" name="cht">
                            </div>
                            <div class="form-group" id="div_professor"></div>                          
                            <div class="form-group" id="div_tipo"></div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-danger" data-dismiss="modal">Cancelar</button>
                            <button type="button" class="btn btn-success" id="btn_gravar">Gravar</button>
                        </div>
                    </div>
                </div>   
            </div>

            <!--
            Modal choque de disciplinas
            -->
            <div id="modal_choques" class="modal fade" role="dialog">
                <div class="modal-dialog modal-lg">
                    <!-- Modal content-->
                    <div class="modal-content">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal">&times;</button>
                            <h4 class="modal-title text-center">Relatório de choques de horário</h4>
                            <div id="modal_formulario_msg"></div>
                        </div>
                        <div class="modal-body">                                                                                          
                            <div class="form-group" id="div_choques"></div>  
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
                            <h4 class="modal-title">Exclusão ds oferta de disciplina</h4>
                            <div id="modal_confirmacao_msg"></div>
                        </div>
                        <div class="modal-body">
                            <div class="alert alert-warning">
                                Deseja realmente excluir esta oferta de disciplina?
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