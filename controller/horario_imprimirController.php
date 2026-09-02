<?php

session_start();
require_once $_SESSION['diretorio_base'] . '/model/horarioModel.php';
require_once $_SESSION['diretorio_base'] . '/model/oferta_disciplinaModel.php';
require_once $_SESSION['diretorio_base'] . '/model/periodoModel.php';
require_once $_SESSION['diretorio_base'] . '/model/log_acaoModel.php';

class horario_imprimirController {

    private $msg;
    private $logM;

    public function __construct() {
        $this->msg = '';
    }

    public function getTurmasAtivas() {

        $periodo = explode("/",$_POST['periodo']);
        $semestre = $periodo[1];
        
        $options = '';
        $ofertaM = new oferta_disciplinaModel();
        $result = $ofertaM->getTurmasAtivas($_POST['id_periodo'],$semestre);
        if ($result) {
            while ($linha = $result->fetch_assoc()) {
                $options .= '<option value="' . $linha['id_turma'] . '">';
                $options .= $linha['turma'];
                $options .= '</option>';
            }
        }

        $resposta = array('options' => $options);
        return json_encode($resposta);
    }

    public function carregarPeriodo() {
        $select = '<label for="id_periodo">Periodo:</label>';
        $select .= '<select id="id_periodo" name="id_periodo" class="form-control" style="width:100%" onChange="getTurmasAtivas()">';
        $periodoM = new periodoModel();
        
        if ($_SESSION['perfil'] == 'Professor') {
            $resultado_periodos = $periodoM->listar(array("publicado"=>1), array('id_periodo'=>'DESC'));
        } else {
            $resultado_periodos = $periodoM->listar(array(), array('id_periodo'=>'DESC'));
        }
        
        while ($linha = mysqli_fetch_assoc($resultado_periodos)) {
            $select .= "<option value='{$linha['id_periodo']}'>";
            $select .= $linha['ano'].'/'.$linha['semestre'];
            $select .= '</option>';
        }
        $select .= '</select>';
        $resposta = array('select' => $select);
        return json_encode($resposta);
    }      
    
    public function getHorarioEmail() {

        $horarios = array();
        $horarioM = new horarioModel();
        $result = $horarioM->getHorarioEmail($_SESSION['email']);
        if ($result) {
            while ($linha = $result->fetch_assoc()) {
                $horarios[] = $linha['id_horario'];
            }
        }

        $resposta = array('horarios' => $horarios);
        return json_encode($resposta);
    }

    public function listar() {

        $codigo_sigaa = true;
        $primeiro_nome_professor = false;
        if (!isset($_POST['codigo_sigaa'])) {
            $codigo_sigaa = false;
        } 
        if (isset($_POST['primeiro_nome_professor'])) {
            $primeiro_nome_professor = true;
        } 
        
        $horarioM = new horarioModel();
        $result = $horarioM->listar($_POST['id_turma']);
        $turmaAnterior = '';
        $i = 2;
        $tabela = '';
        $clinha = 0;
        
        if (mysqli_num_rows($result) > 0) {
            while ($linha = mysqli_fetch_assoc($result)) {

                if ($linha['turma'] != $turmaAnterior) {

                    if ($turmaAnterior != '') {
                        $tabela .= '</tbody>' . "\n";
                        $tabela .= '</table>' . "\n";
                    }
                    $turmaAnterior = $linha['turma'];

                    $tabela .= '<table class="table table-bordered">' . "\n";
                    $tabela .= '<thead>' . "\n";
                    $tabela .= '<tr>' . "\n";
                    $tabela .= '<th colspan="7" style="background-color:#D9EDF7 !important"><center>' . $linha['turma'] . '</center></th>' . "\n";
                    $tabela .= '</tr>' . "\n";
                    $tabela .= '<tr>' . "\n";
                    $tabela .= '<th width="10%" style="background-color:#DFF0D8 !important">Horário</th>' . "\n";
                    $tabela .= '<th width="18%" style="background-color:#DFF0D8 !important">Segunda</th>' . "\n";
                    $tabela .= '<th width="18%" style="background-color:#DFF0D8 !important">Terça</th>' . "\n";
                    $tabela .= '<th width="18%" style="background-color:#DFF0D8 !important">Quarta</th>' . "\n";
                    $tabela .= '<th width="18%" style="background-color:#DFF0D8 !important">Quinta</th>' . "\n";
                    $tabela .= '<th width="18%" style="background-color:#DFF0D8 !important">Sexta</th>' . "\n";
                    
                    $tabela .= '</tr>' . "\n";
                    $tabela .= '</thead>' . "\n";
                    $tabela .= '<tbody>' . "\n";
                    $i = 2;
                }

                if ($i <= 6) {

                    if ($i == 2) {
                        $clinha++;
                        if (
                            (($linha['id_hora'] == 9) || 
                            ($linha['id_hora'] == 13) || 
                            ($linha['id_hora'] == 15) || 
                            ($linha['id_hora'] == 18) || 
                            ($linha['id_hora'] == 20)) && ($clinha > 2)
                        ){                        
                            $tabela .= '<tr align="center">' . "\n";
                            $tabela .= '<td colspan="7"><b>Intervalo</b></td>' . "\n";
                            $tabela .= '</tr>' . "\n";
                        }
                        $tabela .= '<tr>' . "\n";
                        $tabela .= '<td><b>' . $linha['horario'] . '</b></td>' . "\n";
                    }

                    while ($i != $linha['id_dia']) {

                        $tabela .= '<td></td>' . "\n";
                        $i++;
                        if ($i > 6) {
                            $tabela .= '</tr>' . "\n";
                            $i = 2;
                            $clinha++;
                            //if ($clinha == 3){
                            if (
                                (($linha['id_hora'] == 9) || 
                                ($linha['id_hora'] == 13) || 
                                ($linha['id_hora'] == 15) || 
                                ($linha['id_hora'] == 18) || 
                                ($linha['id_hora'] == 20)) && ($clinha > 2)
                            ){                              
                                $tabela .= '<tr align="center">' . "\n";
                                $tabela .= '<td colspan="7"><b>Intervalo</b></td>' . "\n";
                                $tabela .= '</tr>' . "\n";
                            }                        
                            $tabela .= '<tr>' . "\n";
                            $tabela .= '<td><b>' . $linha['horario'] . '</b></td>' . "\n";
                        }
                    }

                    $tabela .= "<td style='background-color:{$linha['cor']} !important'>";
                    $tabela .= '<div style="color:blue; font-weight:bold !important">' . $linha['disciplina'] . '</div>';
                    if (!$primeiro_nome_professor) {
                        $tabela .= '<div style="color:green !important">' . $linha['professor'] . '</div>';
                    } else {
                        $vet_nome = explode(' ',$linha['professor']);
                        $tabela .= '<div style="color:green !important">' . $vet_nome[0] . '</div>';
                    }
                    $tabela .= '<div style="color:brown !important">' . $linha['sala'] . '</div>';
                   
                    /*
                     * Consulta para recuperar o código do SIGAA para exibir no horário.
                     */
                    if ($codigo_sigaa) {
                        $result_grade = $horarioM->getGrade($linha['id_disciplina'], $linha['id_curso']);
                        if (mysqli_num_rows($result_grade) > 0) {
                            while ($linha_grade = mysqli_fetch_assoc($result_grade)) {
                                $tabela .= '<div style="color:SlateBlue !important">COD SIGAA: ' . $linha_grade['cod_sigaa'] . '</div>';
                            }
                        } 
                    }
                    
                    
                    $tabela .= '</td>';

                    $i++;

                    if ($i > 6) {
                        $tabela .= '</tr>' . "\n";
                        $i = 2;
                    }
                }
            }
            while (($i <= 6) && ($i > 2)) {
                $tabela .= '<td></td>' . "\n";
                if ($i == 7) {
                    $tabela .= '</tr>' . "\n";
                }
                $i++;
            }
            $tabela .= '</tbody>' . "\n";
            $tabela .= '</table>' . "\n";
        } else {
            
            $result_ead = $horarioM->getDisciplinasTurmaEAD($_POST['id_turma']);
            $total_linhas_ead = mysqli_num_rows($result_ead);

            if ($total_linhas_ead > 0) {        
                $tabela .= '<table class="table table-responsive table-bordered">' . "\n";
                $tabela .= '<thead>' . "\n";
                $tabela .= '<tr class="success">' . "\n";
                $tabela .= '<th colspan="5" style="text-align:center">Disciplinas/Professor</th>' . "\n";
                $tabela .= '</tr>' . "\n";
                $tabela .= '<tr class="info">' . "\n";
                $tabela .= '<th width="10%">Módulo</th>' . "\n";
                $tabela .= '<th width="35%">Disciplina</th>' . "\n";
                $tabela .= '<th width="35%">Professor</th>' . "\n";
                $tabela .= '<th width="10%">CHS</th>' . "\n";
                $tabela .= '<th width="10%">CHT</th>' . "\n";
                $tabela .= '</tr>' . "\n";
                $tabela .= '</thead>' . "\n";
                $tabela .= '<tbody>' . "\n";
                while ($linha_ead = mysqli_fetch_assoc($result_ead)) {
                    $tabela .= '<tr>';
                    
                    /*
                     * Consulta para recuperar o código do SIGAA para exibir no horário.
                     */
                    $codigos = '';
                    $result_grade = $horarioM->getGrade($linha_ead['id_disciplina'], $linha_ead['id_curso']);
                    if (mysqli_num_rows($result_grade) > 0) {
                        while ($linha_grade = mysqli_fetch_assoc($result_grade)) {
                            $codigos .= $linha_grade['cod_sigaa'] . ' ';
                        }
                    }                     
                    
                    $tabela .= '<td>'.$linha_ead['modulo'].'</td>';
                    $tabela .= '<td>'.$linha_ead['disciplina'].' ('.trim($codigos).')</td>';
                    $tabela .= '<td>'.$linha_ead['professor'].'</td>';
                    $tabela .= '<td>'.$linha_ead['chs'].'</td>';
                    $tabela .= '<td>'.$linha_ead['cht'].'</td>';
                    $tabela .= '</tr>';
                }
                $tabela .= '</tbody>' . "\n";
                $tabela .= '<table>' . "\n";
            }
            
        }
        
        $this->logM = new log_acaoModel();
        $this->logM->inserir(array('id_usuario'=>$_SESSION['id_usuario'],'acao'=>'Consulta Horário Turma','data_hora'=>date('Y-m-d H:i:s')));
        
        $resposta = array('tabela' => $tabela);
        return json_encode($resposta);
    }
  
}

// Callback
if (isset($_POST['metodo'])) {
    $metodo = $_POST['metodo'];
    $objeto = new horario_imprimirController();
    echo $objeto->$metodo();
}