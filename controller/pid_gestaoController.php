<?php

session_start();
require_once $_SESSION['diretorio_base'] . '/model/pidModel.php';
require_once $_SESSION['diretorio_base'] . '/model/historico_pidModel.php';
require_once $_SESSION['diretorio_base'] . '/model/periodoModel.php';
require_once $_SESSION['diretorio_base'] . '/model/usuarioModel.php';
require_once $_SESSION['diretorio_base'] . '/model/tipo_atividadeModel.php';
require_once $_SESSION['diretorio_base'] . '/model/atividadeModel.php';
require_once $_SESSION['diretorio_base'] . '/model/atividade_docenteModel.php';
require_once $_SESSION['diretorio_base'] . '/model/historico_atividadeModel.php';

class pid_gestaoController {

    private $pidM;
    private $historico_pidM;
    private $periodoM;
    private $usuarioM;
    private $tipo_atividadeM;
    private $atividade_docenteM;
    private $historico_atividadeM;
    private $atividadeM;
    private $msg;

    public function __construct() {
        $this->pidM = new pidModel();
        $this->historico_pidM = new historico_pidModel();
        $this->periodoM = new periodoModel();
        $this->usuarioM = new usuarioModel();        
        $this->tipo_atividadeM = new tipo_atividadeModel(); 
        $this->atividadeM = new atividadeModel();
        $this->atividade_docenteM = new atividade_docenteModel();
        $this->historico_atividadeM = new historico_atividadeModel();
    }

    public function listar() {

        $tabela = '';
        
        $result_periodo = $this->periodoM->getPeriodo($_POST['id_periodo']);
        $linha_periodo = mysqli_fetch_assoc($result_periodo);
        $tabela .= '<div class="panel panel-info" id="painel_dados">';
        $tabela .= '<div class="panel panel-heading">Informaçãoes sobre o preenchimento</div>';
        $tabela .= '<div class="panel panel-body">';
        $tabela .= '<div class="container-fluid">';
        $tabela .= '<table class="table table-bordered">';
        $tabela .= '<tr>';
        $tabela .= '<th>Ano/Semestre</th>';
        $tabela .= '<td>'.$linha_periodo['ano'].'/'.$linha_periodo['semestre'].'</td>';
        $tabela .= '<th>Período</th>';
        $tabela .= '<td>'.$linha_periodo['data_inicio_formatado'].' à '.$linha_periodo['data_fim_formatado'].'</td>';        
        $tabela .= '</tr>';
        $tabela .= '<th>Período PID</th>';
        $tabela .= '<td>'.$linha_periodo['pid_inicio_formatado'].' à '.$linha_periodo['pid_fim_formatado'].'</td>';
        $tabela .= '<th>Período RID</th>';
        $tabela .= '<td>'.$linha_periodo['rid_inicio_formatado'].' à '.$linha_periodo['rid_fim_formatado'].'</td>';
        $tabela .= '</tr>';        
        $tabela .= '</table>';
        $tabela .= '</div>';
        
        $ordenacao = array('usuario.nome' => 'ASC');
        $result = $this->pidM->listar($_POST['id_periodo'], 'PID', array(), $ordenacao);
        $total_linhas = mysqli_num_rows($result);
        //echo 'Total de linhas: '.$total_linhas;
        if ($total_linhas > 0) {

            $tabela .= '<table class="table table-striped table-hover table-condensed" id="tabela_pid">';
            $tabela .= '<thead>';

            $tabela .= '<tr>';
            $tabela .= '<th colspan="2" style="text-align:center;background-color:#F8F8FF"></th>';
            $tabela .= '<th colspan="6" style="text-align:center;background-color:#d1ecf1">Situação</th>';
            $tabela .= '<th colspan="2" style="text-align:center;background-color:#cce5ff">Correção PID</th>';
            $tabela .= '<th colspan="2" style="text-align:center;background-color:#fff3cd">Correção RID</th>';
            
            $tabela .= '</tr>';

            $tabela .= '<tr>';
            $tabela .= '<th width="5%" style="text-align:left;background-color:#F8F8FF">ID</th>';
            $tabela .= '<th width="25%" style="text-align:left;background-color:#F8F8FF">Professor</th>';
            $tabela .= '<th width="5%" style="text-align:center;background-color:#d1ecf1">PID</th>';
            $tabela .= '<th width="3%" style="text-align:center;background-color:#d1ecf1"></th>';
            $tabela .= '<th width="2%" style="text-align:center;background-color:#d1ecf1"></th>';
            $tabela .= '<th width="5%" style="text-align:center;background-color:#d1ecf1">RID</th>';
            $tabela .= '<th width="3%" style="text-align:center;background-color:#d1ecf1"></th>';
            $tabela .= '<th width="2%" style="text-align:center;background-color:#d1ecf1"></th>';
            $tabela .= '<th width="10%" style="text-align:center;background-color:#cce5ff">Início</th>';
            $tabela .= '<th width="10%" style="text-align:center;background-color:#cce5ff">Fim</th>';
            $tabela .= '<th width="10%" style="text-align:center;background-color:#fff3cd">Início</th>';
            $tabela .= '<th width="10%" style="text-align:center;background-color:#fff3cd">Fim</th>';

            $tabela .= '</tr>';
            $tabela .= '</thead>';
            $tabela .= '<tbory>';

            while ($linha = mysqli_fetch_assoc($result)) {

                $tabela .= '<tr>';
                $tabela .= '<td>' . $linha['id_pid'] . '</td>';
                $tabela .= '<td>' . $linha['professor'] . '</td>';

                $tabela .= '<td style="text-align:center">';

                $result_historico = $this->historico_pidM->getSituacao($linha['id_pid'], 'PID');
                $linha_historico = mysqli_fetch_assoc($result_historico);
                if ($result_historico) {
                    if ($linha_historico['situacao'] == 'AGUARDANDO ENVIO') {
                        $tabela .= '<span class="glyphicon glyphicon-time" style="color:orange" title="AGUARDANDO ENVIO"></span>';
                    } else if ($linha_historico['situacao'] == 'APROVADO') {
                        $tabela .= '<span class="glyphicon glyphicon-thumbs-up" style="color:green" title="APROVADO"></span>';
                    } else if ($linha_historico['situacao'] == 'REPROVADO') {
                        $tabela .= '<span class="glyphicon glyphicon-thumbs-down" style="color:red" title="REPROVADO"></span>';
                    } else if ($linha_historico['situacao'] == 'RETORNADO PARA CORREÇÃO') {
                        $tabela .= '<span class="glyphicon glyphicon-circle-arrow-left" style="color:orange" title="RETORNADO PARA CORREÇÃO"></span>';
                    } else if ($linha_historico['situacao'] == 'ENVIADO') {
                        $tabela .= '<span class="glyphicon glyphicon-circle-arrow-right" style="color:blue" title="ENVIADO"></span>';
                    } else {
                        $tabela .= 'ERRO';
                    }
                } else {
                    $tabela .= '<span class="glyphicon glyphicon-cloud" style="color:pink" title="FORA DO PERÍODO"></span>';
                }
                $tabela .= '</td>';
                
                $tabela .= '<td>';
                $tabela .= '<a href="pid_avaliacao.php?id_periodo='.$_POST['id_periodo'].'&id_usuario='.$linha['id_usuario'].'" style="color:green">';
                $tabela .= '<span title="Acessar" class="glyphicon glyphicon-edit"></span>';
                $tabela .= '</a>';
                $tabela .= '</td>';

                $tabela .= '<td>';
                $tabela .= '<span onclick="imprimir('.$linha['id_pid'].','.$linha['id_usuario'].')" style="color:gray;cursor:pointer" title="Imprimir" class="glyphicon glyphicon-print"></span>';
                $tabela .= '</td>';                
                
                $tabela .= '<td style="text-align:center">';

                $result_historico = $this->historico_pidM->getSituacao($linha['id_pid'], 'RID');
                if ($result_historico) {
                    $linha_historico = mysqli_fetch_assoc($result_historico);
                    //$tabela .= $linha_historico['situacao'];
                    if ($linha_historico['situacao'] == 'AGUARDANDO ENVIO') {
                        $tabela .= '<span class="glyphicon glyphicon-time" style="color:orange" title="AGUARDANDO ENVIO"></span>';
                    } else if ($linha_historico['situacao'] == 'APROVADO') {
                        $tabela .= '<span class="glyphicon glyphicon-thumbs-up" style="color:green" title="APROVADO"></span>';
                    } else if ($linha_historico['situacao'] == 'REPROVADO') {
                        $tabela .= '<span class="glyphicon glyphicon-thumbs-down" style="color:red" title="REPROVADO"></span>';
                    } else if ($linha_historico['situacao'] == 'RETORNADO PARA CORREÇÃO') {
                        $tabela .= '<span class="glyphicon glyphicon-circle-arrow-left" style="color:orange" title="RETORNADO PARA CORREÇÃO"></span>';
                    } else if ($linha_historico['situacao'] == 'ENVIADO') {
                        $tabela .= '<span class="glyphicon glyphicon-circle-arrow-right" style="color:blue" title="ENVIADO"></span>';
                    } else {
                        $tabela .= 'ERRO';
                    }
                } else {
                    $tabela .= '<span class="glyphicon glyphicon-cloud" style="color:pink" title="FORA DO PERÍODO"></span>';
                }
                $tabela .= '</td>';
                
                $tabela .= '<td>';
                $tabela .= '<a href="rid_avaliacao.php?id_periodo='.$_POST['id_periodo'].'&id_usuario='.$linha['id_usuario'].'" style="color:green">';
                $tabela .= '<span title="Acessar" class="glyphicon glyphicon-edit"></span>';
                $tabela .= '</a>';
                $tabela .= '</td>';  
                
                $tabela .= '<td>';
                $tabela .= '<span onclick="imprimir_rid('.$linha['id_pid'].','.$linha['id_usuario'].')" style="color:gray;cursor:pointer" title="Imprimir" class="glyphicon glyphicon-print"></span>';
                $tabela .= '</td>';                  
                
                $tabela .= '<td align="center">' . $linha['pid_correcao_inicio_formatado'] . '</td>';
                $tabela .= '<td align="center">' . $linha['pid_correcao_fim_formatado'] . '</td>';
                $tabela .= '<td align="center">' . $linha['rid_correcao_inicio_formatado'] . '</td>';
                $tabela .= '<td align="center">' . $linha['rid_correcao_fim_formatado'] . '</td>';                
                $tabela .= '</tr>';
            }

            $tabela .= '</tbory>';
            $tabela .= '</table>';
        }



        $resposta = array('tabela' => $tabela);
        return json_encode($resposta);
    }

    public function formularioValido() {

        $valido = true;

        if (trim($_POST['id_usuario']) == '') {
            $this->msg = 'O preenchimento do campo usuario é obrigatório!';
            $valido = false;
        }

        if (!$valido) {
            $this->msg = '<div class="alert alert-danger">' . $this->msg . '</div>';
        }
        return $valido;
    }

    public function inserir() {
        $resultado = false;
        if ($this->formularioValido()) {
            $res = $this->pidM->inserir($_POST);
            if ($res) {
                $this->msg .= '<div class="alert alert-success">';
                $this->msg .= 'Registro cadastrado com sucesso!';
                $this->msg .= '</div>';
                $id_oferta_disciplina = $res;
                $resultado = true;
            } else {
                $this->msg .= '<div class="alert alert-danger">';
                $this->msg .= 'Erro ao inserir - Contactar o administrador do sistema';
                $this->msg .= '</div>';
            }
        }
        $resposta = array('resultado' => $resultado, 'msg' => $this->msg);
        return json_encode($resposta);
    }

    public function atualizar() {
        $resultado = false;
        $res = $this->oferta_disciplinaM->atualizar($_POST);
        if ($res) {
            $this->msg .= '<div class="alert alert-success">';
            $this->msg .= 'Registro atualizado com sucesso!';
            $this->msg .= '</div>';
            $resultado = true;
        } else {
            $this->msg .= '<div class="alert alert-danger">';
            $this->msg .= 'Erro ao atualizar - Contactar o administrador do sistema';
            $this->msg .= '</div>';
        }
        $resposta = array('resultado' => $resultado, 'msg' => $this->msg);
        return json_encode($resposta);
    }

    public function deletar() {
        $resultado = false;
        if (!$this->oferta_disciplinaM->existeVinculo($_POST['id_pid'])) {
            $res = $this->oferta_disciplinaM->deletar($_POST['id_pid']);
            if ($res) {
                $this->msg .= '<div class="alert alert-success">';
                $this->msg .= 'Registro deletado com sucesso!';
                $this->msg .= '</div>';
                $resultado = true;
            } else {
                $this->msg .= '<div class="alert alert-danger">';
                $this->msg .= 'Erro ao deletar - Contactar o administrador do sistema';
                $this->msg .= '</div>';
            }
        } else {
            $this->msg .= '<div class="alert alert-danger">';
            $this->msg .= 'Não é possível deletar o PID do professor! Já existe registros associados a este PID!';
            $this->msg .= '</div>';
        }
        $resposta = array('resultado' => $resultado, 'msg' => $this->msg);
        return json_encode($resposta);
    }

    public function getOfertaDisciplina() {
        $res = $this->pidM->getPid($_POST['id_pid']);
        $linha = mysqli_fetch_assoc($res);
        return json_encode($linha);
    }

    public function carregarProfessor() {

        $select = '<label for="id_usuario">Professor:</label>';
        $select .= '<select id="id_usuario" name="id_usuario" class="form-control">';
        $select .= "<option value='NULL' selected='selected'></option>";

        $this->usuarioM = new usuarioModel();
        $ordem = array('nome' => 'ASC');
        $parametro = array();

        $result = $this->usuarioM->listar(11, $parametro, $ordem);
        while ($linha = mysqli_fetch_assoc($result)) {
            $select .= "<option value='{$linha['id_usuario']}'>";
            $select .= $linha['nome'];
            $select .= '</option>';
        }
        $select .= '</select>';
        $resposta = array('select' => $select);

        return json_encode($resposta);
    }

    public function carregarPeriodo() {
        
        $result_periodo_atual = $this->periodoM->getPeriodoAtual();
        $linha_periodo_atual = mysqli_fetch_assoc($result_periodo_atual);
        
        $select = '<label for="id_periodo">Periodo:</label>';
        $select .= '<select id="id_periodo" name="id_periodo" class="form-control" style="width:100%">';
        $periodoM = new periodoModel();
        $resultado_periodos = $periodoM->listar(array(), array('id_periodo' => 'DESC'));

        $parametros = explode("=",$_SERVER['HTTP_REFERER']);

        while ($linha = mysqli_fetch_assoc($resultado_periodos)) {
            if (count($parametros) > 1) {
                if ($parametros[1] == $linha['id_periodo']) {
                    $select .= "<option selected='selected' value='{$linha['id_periodo']}'>";
                } else {
                    $select .= "<option value='{$linha['id_periodo']}'>";
                }
            } else {             
                if ($linha_periodo_atual['id_periodo'] == $linha['id_periodo']) {
                    $select .= "<option selected='selected' value='{$linha['id_periodo']}'>";    
                } else {
                    $select .= "<option value='{$linha['id_periodo']}'>";
                }
            }
            
            $select .= $linha['ano'] . '/' . $linha['semestre'];
            $select .= '</option>';
        }
        $select .= '</select>';
        $resposta = array('select' => $select);
        return json_encode($resposta);
    }

    public function imprimir() {

        /*
         * Variável globais importantes
         */
        $tabela = '';
        $id_pid = '';
        $grupos = array();

        /*
         * Recupera dados do professor
         */
        $result_usuario = $this->usuarioM->getUsuarioId($_POST['id_usuario']);
        $linha_usuario = mysqli_fetch_assoc($result_usuario);

        /*
         * Recupera dados do periodo
         */
        $result_periodo = $this->periodoM->getPeriodo($_POST['id_periodo']);
        $linha_periodo = mysqli_fetch_assoc($result_periodo);

        /*
         * Recupera dados do PID
         */
        $result_pid = $this->pidM->getPidPeriodoProfessor($_POST['id_periodo'], $_POST['id_usuario']);
        $linha_pid = mysqli_fetch_assoc($result_pid);
        $id_pid = $linha_pid['id_pid'];

        $result_historico_pid = $this->historico_pidM->getSituacao($id_pid, 'PID');
        $linha_historico_pid = mysqli_fetch_assoc($result_historico_pid);

        /*
         * Informações sobre o docente
         */
        $tabela .= '<table cellspacing="0" style="border-width:1px;border-color:black;border-style:solid;font-family:verdana;width:100%;background-color:#6bba70;color:white">';
       
        $tabela .= '<tr>
                        <td style="width:15%;text-align:right">
                            <img style="max-width:280px;max-height:120px;width:auto;height:auto;margin:8px;margin-top:20px" src="https://www.ifsudestemg.edu.br/comunicacao-social/logos/if-sudeste-mg/logo_vertical_ifsudestemg-%282%29.png">
                        </td>
                        <td style="width:85%;text-align:center;font-size:14px">
                            MINISTÉRIO DA EDUCAÇÃO <br>
                            SECRETARIA DE EDUCAÇÃO PROFISSIONAL E TECNOLOGICA <br>
                            INSTITUTO FEDERAL DE EDUCAÇÃO, CIÊNCIA E TECNOLOGIA DO SUDESTE DE MINAS GERAIS <br>
                            CAMPUS AVANÇADO BOM SUCESSO
                        </td>
                    </tr>
                    <tr>
                        <td></td>
                        <td style="padding-bottom:7px;font-size:16px;font-weight:bold;text-align:center">PID - PLANO INDIVIDUAL DOCENTE </td>
                    </tr>';
        $tabela .= '</table>';

        $tabela .= '<table style="margin-top:10px;font-family:verdana;font-size:12px;width:100%;text-align:left" cellpadding="5" border="1" cellspacing="0">';
        
        $tabela .= '<tr>';
        $tabela .= '<td colspan="4" style="background-color:#D3D3D3">Informações do docente</td>';
        $tabela .= '</tr>';
        
        $tabela .= '<tr>';
        $tabela .= '<th style="width:20%">Docente:</th>';
        $tabela .= '<td style="width:40%">' . $linha_usuario['nome'] . '</td>';
        $tabela .= '<th style="width:20%">SIAPE:</th>';
        $tabela .= '<td style="width:40%">' . $linha_usuario['matricula'] . '</td>';
        $tabela .= '</tr>';

        $tabela .= '<tr>';
        $tabela .= '<th>Departamento:</th>';
        $tabela .= '<td>Ensino</td>';
        $tabela .= '<th>Núcleo:</th>';
        $tabela .= '<td>' . $linha_usuario['area'] . '</td>';
        $tabela .= '</tr>';

        $tabela .= '<tr>';
        $tabela .= '<th>Ano/Semestre:</th>';
        $tabela .= '<td>' . $linha_periodo['ano'] . '/' . $linha_periodo['semestre'] . '</td>';
        $tabela .= '<th>Regime de trabalho:</th>';
        $tabela .= '<td>40h - DE</td>';
        $tabela .= '</tr>';

        $tabela .= '<tr>';
        $tabela .= '<th>Email:</th>';
        $tabela .= '<td colspan="3">' . $linha_usuario['email'] . '</td>';
        $tabela .= '</tr>';
        
        $tabela .= '</tbody>';        
        $tabela .= '</table>';

        $ordenacao = array('id_tipo_atividade' => 'ASC');
        $result_tipo_atividade = $this->tipo_atividadeM->listar(array(), $ordenacao);
        while ($linha_tipo_atividade = mysqli_fetch_assoc($result_tipo_atividade)) {

            $id_tipo_atividade = $linha_tipo_atividade['id_tipo_atividade'];
            $descricao = $linha_tipo_atividade['descricao'];

            $tabela_temp = '<table style="margin-top:10px;font-family:verdana;font-size:12px;width:100%;text-align:left" cellpadding="5" border="1" cellspacing="0">';
            $tabela_temp .= '<thead>';
            $tabela_temp .= '<tr>';
            $tabela_temp .= '<td colspan="3" style="background-color:#D3D3D3;">'.$descricao.'</td>';
            $tabela_temp .= '</tr>';             
            $tabela_temp .= '<tr>';
            if ($id_tipo_atividade == 1) {
                $tabela_temp .= '<th style="width:80%">Descricação</th>';
                $tabela_temp .= '<th style="width:20%;text-align:center">Horas Planejadas</th>';                
            } else if ($id_tipo_atividade == 2) {
                $tabela_temp .= '<th style="width:80%">Atividade</th>';
                $tabela_temp .= '<th style="width:20%;text-align:center">Horas Planejadas</th>';
            } else {
                $tabela_temp .= '<th style="width:40%">Atividade</th>';
                $tabela_temp .= '<th style="width:40%">Descricação</th>';
                $tabela_temp .= '<th style="width:20%;text-align:center">Horas Planejadas</th>';
            }
            $tabela_temp .= '</tr>';
            $tabela_temp .= '</thead>';
            $tabela_temp .= '<tbody>';

            $ordenacao = array('atividade.id_tipo_atividade' => 'ASC', 'atividade.id_atividade' => 'ASC');
            $parametros = array('atividade.id_tipo_atividade' => $id_tipo_atividade);
            $result_atividade_docente = $this->atividade_docenteM->listar($id_pid, $parametros, $ordenacao);
            
            $soma_grupo = 0;
            while ($linha_atividade_docente = mysqli_fetch_assoc($result_atividade_docente)) {

                /*
                 * Recupera a situação da atividade
                 */
                $result_historico_atividade = $this->historico_atividadeM->getSituacaoAtividade($linha_atividade_docente['id_atividade_docente'], 'PID');
                
                /*
                 *  Só gera relatório do PID para atividades cadastradas no PID.
                 *  As cadastradas diretamente no RID não entram.
                 */
                if ($result_historico_atividade) {
                    $linha_historico_atividade = mysqli_fetch_assoc($result_historico_atividade);

                    if (($linha_historico_atividade['situacao'] == 'AGUARDANDO AVALIAÇÃO') || (($linha_historico_atividade['situacao'] == 'APROVADA'))) { 
                        if ($soma_grupo == 0) {
                            $tabela .= $tabela_temp;
                        }
                        $soma_grupo = $soma_grupo + $linha_atividade_docente['horas_planejadas'];

                        $tabela .= '<tr>';
                         if ($id_tipo_atividade == 1) {
                            $tabela .= '<td>' . $linha_atividade_docente['descricao'] . '</td>';
                            $tabela .= '<td align="center">'.$linha_atividade_docente['horas_planejadas'].'</td>';
                         } else  if ($id_tipo_atividade == 2) {
                            $tabela .= '<td>' . $linha_atividade_docente['atividade'] . '</td>';
                            $tabela .= '<td align="center">'.$linha_atividade_docente['horas_planejadas'].'</td>';
                         } else {
                            $tabela .= '<td>' . $linha_atividade_docente['atividade'] . '</td>';
                            $tabela .= '<td>' . $linha_atividade_docente['descricao'] . '</td>';
                            $tabela .= '<td align="center">'.$linha_atividade_docente['horas_planejadas'].'</td>';
                         }
                        $tabela .= '</tr>';
                    }
                }
            }
           
            if ($soma_grupo > 0) {
                $grupos[$id_tipo_atividade] = $soma_grupo;
                $tabela .= '<tr style="font-weight:bold">';
                if ($id_tipo_atividade < 3) {
                    $tabela .= '<td style="text-align:right;padding-right:20px">Total</td>';
                } else {
                    $tabela .= '<td colspan="2" style="text-align:right;padding-right:20px">Total</td>';
                }
                $tabela .= '<td align="center">'.$soma_grupo.'</td>';
                $tabela .= '</tr>';
                $tabela .= '</tbody>';
                $tabela .= '</table>';
            }
        }
        //print_r($grupos);

        $tabela .= '<table style="margin-top:10px;font-family:verdana;font-size:12px;width:100%;text-align:left" cellpadding="5" border="1" cellspacing="0">';
        $tabela .= '<thead>';
        $tabela .= '<tr>';
        $tabela .= '<td colspan="3" style="background-color:#D3D3D3;">Resumo</td>';
        $tabela .= '</tr>';
        $tabela .= '<tr>';
        $tabela .= '<th style="width:80%;text-align:left">Grupo</th>';
        $tabela .= '<th style="width:20%;text-align:center">Horas planejadas</th>';
        $tabela .= '</tr>';
        $tabela .= '</thead>';
        $tabela .= '<tbody>';
        $result_tipo_atividade = $this->tipo_atividadeM->listar(array(), array('id_tipo_atividade' => 'ASC'));
        while ($linha_tipo_atividade = mysqli_fetch_assoc($result_tipo_atividade)) {
            $tabela .= '<tr>';
            if ($linha_tipo_atividade['id_tipo_atividade'] == 1) {
                $tabela .= '<td style="width:80%;text-align:left">Disciplinas</td>';
            } else {
                $tabela .= '<td style="width:80%;text-align:left">' . $linha_tipo_atividade['descricao'] . '</td>';
            }
            if (isset($grupos[$linha_tipo_atividade['id_tipo_atividade']])) {
                $tabela .= '<td style="width:20%;text-align:center">' . $grupos[$linha_tipo_atividade['id_tipo_atividade']] . '</td>';
            } else {
                $tabela .= '<td style="width:20%;text-align:center">0</td>'; 
            }
            $tabela .= '</tr>';
        }
        $tabela .= '<tr>';
        $tabela .= '<th style="width:80%;text-align:right">TOTAL:</th>';
        $tabela .= '<th style="width:20%;text-align:center">'.round(array_sum($grupos), 2) .'</th>';
        $tabela .= '</tr>';
        $tabela .= '</tbody>';
        $tabela .= '</table>';
            
        $tabela .= '<table style="margin-top:10px;font-family:verdana;font-size:12px;width:100%;text-align:left" cellpadding="5" border="1" cellspacing="0">';
        $tabela .= '<tr>';
        $tabela .= '<td style="background-color:#FCF8E3;text-align:center;width:100%">'.$linha_historico_pid['situacao'].'</td>';
        $tabela .= '</tr>';
        $tabela .= '</table>';

        $resposta = array('tabela' => $tabela);
        return json_encode($resposta);
    }    
    
    public function imprimir_rid() {

        /*
         * Variável globais importantes
         */
        $tabela = '';
        $id_pid = $_POST['id_pid'];
        $grupos_planejadas = array();
        $grupos_executadas = array();

        /*
         * Recupera dados do professor
         */
        $result_usuario = $this->usuarioM->getUsuarioId($_POST['id_usuario']);
        $linha_usuario = mysqli_fetch_assoc($result_usuario);

        /*
         * Recupera dados do periodo
         */
        $result_periodo = $this->periodoM->getPeriodo($_POST['id_periodo']);
        $linha_periodo = mysqli_fetch_assoc($result_periodo);

        /*
         * Informações sobre o docente
         */
        $tabela .= '<table cellspacing="0" style="border-width:1px;border-color:black;border-style:solid;font-family:verdana;width:100%;background-color:#6bba70;color:white">';

        $tabela .= '<tr>
                        <td style="width:15%;text-align:right">
                            <img style="max-width:280px;max-height:120px;width:auto;height:auto;margin:8px;margin-top:20px" src="https://www.ifsudestemg.edu.br/comunicacao-social/logos/if-sudeste-mg/logo_vertical_ifsudestemg-%282%29.png">
                        </td>
                        <td style="width:85%;text-align:center;font-size:14px">
                            MINISTÉRIO DA EDUCAÇÃO <br>
                            SECRETARIA DE EDUCAÇÃO PROFISSIONAL E TECNOLOGICA <br>
                            INSTITUTO FEDERAL DE EDUCAÇÃO, CIÊNCIA E TECNOLOGIA DO SUDESTE DE MINAS GERAIS <br>
                            CAMPUS AVANÇADO BOM SUCESSO
                        </td>
                    </tr>
                    <tr>
                        <td></td>
                        <td style="padding-bottom:7px;font-size:16px;font-weight:bold;text-align:center">RID - RELATÓRIO INDIVIDUAL DOCENTE </td>
                    </tr>';
        $tabela .= '</table>';

        $tabela .= '<table style="margin-top:10px;font-family:verdana;font-size:12px;width:100%;text-align:left" cellpadding="5" border="1" cellspacing="0">';

        $tabela .= '<tr>';
        $tabela .= '<td colspan="4" style="background-color:#D3D3D3">Informações do docente</td>';
        $tabela .= '</tr>';

        $tabela .= '<tr>';
        $tabela .= '<th style="width:20%">Docente:</th>';
        $tabela .= '<td style="width:40%">' . $linha_usuario['nome'] . '</td>';
        $tabela .= '<th style="width:20%">SIAPE:</th>';
        $tabela .= '<td style="width:40%">' . $linha_usuario['matricula'] . '</td>';
        $tabela .= '</tr>';

        $tabela .= '<tr>';
        $tabela .= '<th>Departamento:</th>';
        $tabela .= '<td>Ensino</td>';
        $tabela .= '<th>Núcleo:</th>';
        $tabela .= '<td>' . $linha_usuario['area'] . '</td>';
        $tabela .= '</tr>';

        $tabela .= '<tr>';
        $tabela .= '<th>Ano/Semestre:</th>';
        $tabela .= '<td>' . $linha_periodo['ano'] . '/' . $linha_periodo['semestre'] . '</td>';
        $tabela .= '<th>Regime de trabalho:</th>';
        $tabela .= '<td>40h - DE</td>';
        $tabela .= '</tr>';

        $tabela .= '<tr>';
        $tabela .= '<th>Email:</th>';
        $tabela .= '<td colspan="3">' . $linha_usuario['email'] . '</td>';
        $tabela .= '</tr>';

        $tabela .= '</tbody>';        
        $tabela .= '</table>';        
        
        /*
         * Recupera dados do RID
         */
        $result_historico_pid = $this->historico_pidM->getSituacao($_POST['id_pid'], 'RID');
        if ($result_historico_pid) {
            $linha_historico_pid = mysqli_fetch_assoc($result_historico_pid);

            $ordenacao = array('id_tipo_atividade' => 'ASC');
            $result_tipo_atividade = $this->tipo_atividadeM->listar(array(), $ordenacao);
            while ($linha_tipo_atividade = mysqli_fetch_assoc($result_tipo_atividade)) {

                $id_tipo_atividade = $linha_tipo_atividade['id_tipo_atividade'];
                $descricao = $linha_tipo_atividade['descricao'];

                $tabela_temp = '<table style="margin-top:10px;font-family:verdana;font-size:12px;width:100%;text-align:left" cellpadding="5" border="1" cellspacing="0">';
                $tabela_temp .= '<thead>';
                $tabela_temp .= '<tr>';
                $tabela_temp .= '<td colspan="4" style="background-color:#D3D3D3;">'.$descricao.'</td>';
                $tabela_temp .= '</tr>';             
                $tabela_temp .= '<tr>';
                if ($id_tipo_atividade == 1) {
                    $tabela_temp .= '<th style="width:70%">Descricação</th>';
                    $tabela_temp .= '<th style="width:15%;text-align:center">Horas Planejadas</th>';
                    $tabela_temp .= '<th style="width:15%;text-align:center">Horas Executadas</th>';
                } else if ($id_tipo_atividade == 2) {
                    $tabela_temp .= '<th style="width:70%">Atividade</th>';
                    $tabela_temp .= '<th style="width:15%;text-align:center">Horas Planejadas</th>';
                    $tabela_temp .= '<th style="width:15%;text-align:center">Horas Executadas</th>';
                } else {
                    $tabela_temp .= '<th style="width:35%">Atividade</th>';
                    $tabela_temp .= '<th style="width:35%">Descricação</th>';
                    $tabela_temp .= '<th style="width:15%;text-align:center">Horas Planejadas</th>';
                    $tabela_temp .= '<th style="width:15%;text-align:center">Horas Executadas</th>';
                }
                $tabela_temp .= '</tr>';
                $tabela_temp .= '</thead>';
                $tabela_temp .= '<tbody>';

                $ordenacao = array('atividade.id_tipo_atividade' => 'ASC', 'atividade.id_atividade' => 'ASC');
                $parametros = array('atividade.id_tipo_atividade' => $id_tipo_atividade);
                $result_atividade_docente = $this->atividade_docenteM->listar($id_pid, $parametros, $ordenacao);

                $soma_grupo_planejadas = 0;
                $soma_grupo_executadas = 0;
                while ($linha_atividade_docente = mysqli_fetch_assoc($result_atividade_docente)) {

                    /*
                     * Recupera a situação da atividade
                     */
                    $result_historico_atividade = $this->historico_atividadeM->getSituacaoAtividade($linha_atividade_docente['id_atividade_docente'], 'RID');
                    $linha_historico_atividade = mysqli_fetch_assoc($result_historico_atividade);

                    if (
                            ($linha_historico_atividade['situacao'] == 'AGUARDANDO AVALIAÇÃO') || 
                            ($linha_historico_atividade['situacao'] == 'NÃO EXECUTADA') || 
                            ($linha_historico_atividade['situacao'] == 'APROVADA')
                        ) { 
                        if (($soma_grupo_planejadas == 0) && ($soma_grupo_executadas == 0)) {
                            $tabela .= $tabela_temp;
                        }
                        $soma_grupo_executadas = $soma_grupo_executadas + $linha_atividade_docente['horas_executadas'];
                        $soma_grupo_planejadas = $soma_grupo_planejadas + $linha_atividade_docente['horas_planejadas'];

                        $tabela .= '<tr>';
                         if ($id_tipo_atividade == 1) {
                            $tabela .= '<td>' . $linha_atividade_docente['descricao'] . '</td>';
                            $tabela .= '<td align="center">'.$linha_atividade_docente['horas_planejadas'].'</td>';
                            $tabela .= '<td align="center">'.$linha_atividade_docente['horas_executadas'].'</td>';
                         } else  if ($id_tipo_atividade == 2) {
                            $tabela .= '<td>' . $linha_atividade_docente['atividade'] . '</td>';
                            $tabela .= '<td align="center">'.$linha_atividade_docente['horas_planejadas'].'</td>';
                            $tabela .= '<td align="center">'.$linha_atividade_docente['horas_executadas'].'</td>';
                         } else {
                            $tabela .= '<td>' . $linha_atividade_docente['atividade'] . '</td>';
                            $tabela .= '<td>' . $linha_atividade_docente['descricao'] . '</td>';
                            $tabela .= '<td align="center">'.$linha_atividade_docente['horas_planejadas'].'</td>';
                            $tabela .= '<td align="center">'.$linha_atividade_docente['horas_executadas'].'</td>';
                         }
                        $tabela .= '</tr>';
                    }
                }

                if (($soma_grupo_planejadas > 0) || ($soma_grupo_executadas > 0)) {
                    $grupos_planejadas[$id_tipo_atividade] = $soma_grupo_planejadas;
                    $grupos_executadas[$id_tipo_atividade] = $soma_grupo_executadas;
                    $tabela .= '<tr style="font-weight:bold">';
                    if ($id_tipo_atividade < 3) {
                        $tabela .= '<td style="text-align:right;padding-right:20px">Total</td>';
                    } else {
                        $tabela .= '<td colspan="2" style="text-align:right;padding-right:20px">Total</td>';
                    }
                    $tabela .= '<td align="center">'.$soma_grupo_planejadas.'</td>';
                    $tabela .= '<td align="center">'.$soma_grupo_executadas.'</td>';
                    $tabela .= '</tr>';
                    $tabela .= '</tbody>';
                    $tabela .= '</table>';
                }
            }
            //print_r($grupos);

            $tabela .= '<table style="margin-top:10px;font-family:verdana;font-size:12px;width:100%;text-align:left" cellpadding="5" border="1" cellspacing="0">';
            $tabela .= '<thead>';
            $tabela .= '<tr>';
            $tabela .= '<td colspan="3" style="background-color:#D3D3D3;">Resumo</td>';
            $tabela .= '</tr>';
            $tabela .= '<tr>';
            $tabela .= '<th style="width:70%;text-align:left">Grupo</th>';
            $tabela .= '<th style="width:15%;text-align:center">Horas planejadas</th>';
            $tabela .= '<th style="width:15%;text-align:center">Horas executadas</th>';
            $tabela .= '</tr>';
            $tabela .= '</thead>';
            $tabela .= '<tbody>';
            $result_tipo_atividade = $this->tipo_atividadeM->listar(array(), array('id_tipo_atividade' => 'ASC'));
            while ($linha_tipo_atividade = mysqli_fetch_assoc($result_tipo_atividade)) {
                $tabela .= '<tr>';
                if ($linha_tipo_atividade['id_tipo_atividade'] == 1) {
                    $tabela .= '<td style="width:70%;text-align:left">Disciplinas</td>';
                } else {
                    $tabela .= '<td style="width:70%;text-align:left">' . $linha_tipo_atividade['descricao'] . '</td>';
                }
                if (isset($grupos_planejadas[$linha_tipo_atividade['id_tipo_atividade']])) {
                    $tabela .= '<td style="width:15%;text-align:center">' . $grupos_planejadas[$linha_tipo_atividade['id_tipo_atividade']] . '</td>';
                } else {
                    $tabela .= '<td style="width:15%;text-align:center">0</td>'; 
                }
                if (isset($grupos_executadas[$linha_tipo_atividade['id_tipo_atividade']])) {
                    $tabela .= '<td style="width:15%;text-align:center">' . $grupos_executadas[$linha_tipo_atividade['id_tipo_atividade']] . '</td>';
                } else {
                    $tabela .= '<td style="width:15%;text-align:center">0</td>'; 
                }

                $tabela .= '</tr>';
            }
            $tabela .= '<tr>';
            $tabela .= '<th style="width:70%;text-align:right">TOTAL:</th>';
            $tabela .= '<th style="width:15%;text-align:center">'.round(array_sum($grupos_planejadas), 2) .'</th>';
            $tabela .= '<th style="width:15%;text-align:center">'.round(array_sum($grupos_executadas), 2) .'</th>';
            $tabela .= '</tr>';
            $tabela .= '</tbody>';
            $tabela .= '</table>';

            $tabela .= '<table style="margin-top:10px;font-family:verdana;font-size:12px;width:100%;text-align:left" cellpadding="5" border="1" cellspacing="0">';
            $tabela .= '<tr>';
            $tabela .= '<td style="background-color:#FCF8E3;text-align:center;width:100%">'.$linha_historico_pid['situacao'].'</td>';
            $tabela .= '</tr>';
            $tabela .= '</table>';
        } else {
            $tabela .= '<table style="margin-top:10px;font-family:verdana;font-size:12px;width:100%;text-align:left" cellpadding="5" border="1" cellspacing="0">';
            $tabela .= '<thead>';
            $tabela .= '<tr>';
            $tabela .= '<td colspan="4" style="background-color:#FF6347;text-align:center"> RID NÃO CADASTRADO</td>';
            $tabela .= '</tr>';             
            $tabela .= '<tr>';            
            $tabela .= '</thead>';
            $tabela .= '</table>';
        }
        $resposta = array('tabela' => $tabela);
        return json_encode($resposta);        
    }    
    
}

// Callback
if (isset($_POST['metodo'])) {
    $metodo = $_POST['metodo'];
    $objeto = new pid_gestaoController();
    echo $objeto->$metodo();
}