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
        <script src="js/pid_avaliacao.js"></script>
        <script src="js/jquery.mask.min.js"></script>
    </head>
    <body>
        <?php include $_SESSION['topo']; ?>
        <form id="formulario">
            <input type="hidden" name="metodo" id="metodo">
            <input type="hidden" name="id_atividade_docente" id="id_atividade_docente">
            <input type="hidden" name="id_tipo_atividade" id="id_tipo_atividade">
            <input type="hidden" name="etapa" id="etapa">
            <input type="hidden" name="id_usuario" id="id_usuario">
            <input type="hidden" name="id_periodo" id="id_periodo">
                 
            <div class="container-fluid">
                <div class="col-md-12">
                    <div class="panel panel-default">
                        <div class="panel-heading text-center text-uppercase" style="background-color: #6bba70; color: white">
                            <span class="col-sm-1 col-xs-12">
                                <img style="max-width:280px;max-height:120px;width: auto;height: auto;" style="margin: 8px" src="https://www.ifsudestemg.edu.br/comunicacao-social/logos/if-sudeste-mg/logo_vertical_ifsudestemg-%282%29.png">
                            </span>
                            <span class="col-sm-11 col-xs-12" style="text-center">
                                MINISTÉRIO DA EDUCAÇÃO <br>
                                SECRETARIA DE EDUCAÇÃO PROFISSIONAL E TECNOLOGICA <br>
                                INSTITUTO FEDERAL DE EDUCAÇÃO, CIÊNCIA E TECNOLOGIA DO SUDESTE DE MINAS GERAIS <br>
                                CAMPUS AVANÇADO BOM SUCESSO<br><br>
                            </span>
                            <div style="font-size: 18px;font-weight: bold">Plano individual docente - PID</div>
                        </div>            
                        <div class="panel-body">
                            <div id="pid_professor"></div>
                        </div>            
                    </div>
                </div>     
            </div>
            
            <!--
            Modal para inserir 
            -->
            <div id="modal_formulario" class="modal fade" role="dialog">
                <div class="modal-dialog modal-lg">
                    <!-- Modal content-->
                    <div class="modal-content">
                        <div class="modal-header bg-info">
                            <button type="button" class="close" data-dismiss="modal">&times;</button>
                            <h4 class="modal-title">Informações sobre a atividade</h4>
                        </div>
                        <div class="modal-body">

                            <div id="div_historico_atividade"></div>
                            
                            <div id="modal_formulario_msg"></div>
                            
                            <div class="form-group" id="div_atividade"></div>
                            <div class="form-group">
                                <label for="descricao">Descricação</label>
                                <input readonly type="text" class="form-control" id="descricao" name="descricao">
                            </div>
                            <div class="form-group">
                                <label for="horas_planejadas">CHS - Carga horária semanal </label>
                                <input readonly type="text" class="form-control" id="horas_planejadas" name="horas_planejadas">
                            </div>
                            
                            <div class="form-group">
                                <label for="situacao">Situacao</label>
                                <select id="situacao" name="situacao" class="form-control" onchange="habilitar_campos(this)">
                                    <option value=""></option>
                                    <option value="APROVADA">APROVADA</option>
                                    <option value="APROVADA COM ALTERAÇÃO">APROVADA COM ALTERAÇÃO</option>
                                    <option value="REPROVADA">REPROVADA</option>
                                </select>    
                            </div>
                            
                            <div class="form-group">
                                <label for="observcao">Observação</label>
                                <textarea class="form-control" id="observacao" name="observacao"></textarea>
                            </div>                            
                        </div>
                        
                        <div class="modal-footer">
                            <button type="button" class="btn btn-danger" data-dismiss="modal">Cancelar</button>
                            <button type="button" class="btn btn-success" id="btn_gravar">Gravar</button>
                        </div>
                    </div>
                </div>   
            </div>
            
        </form>
    </body>
</html>
