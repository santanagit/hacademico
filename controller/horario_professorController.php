<?php

session_start();
require_once $_SESSION['diretorio_base'] . '/model/horarioModel.php';
require_once $_SESSION['diretorio_base'] . '/model/usuarioModel.php';
require_once $_SESSION['diretorio_base'] . '/model/periodoModel.php';
require_once $_SESSION['diretorio_base'] . '/model/log_acaoModel.php';

class horario_professorController {

    private $horarioM;
    private $logM;

    public function __construct() {
        $this->horarioM = new horarioModel();
    }

    public function listar() {
        
        $periodo = explode("/",$_POST['periodo']);
        $semestre = $periodo[1];        

        $id_usuario = $_SESSION['id_usuario'];
        if (isset($_POST['id_usuario']) && trim($_POST['id_usuario']) != '') {
            $id_usuario = $_POST['id_usuario'];
        }
        
        $vet_horario[7] = '<center>07:30 - 08:30</center>';
        $vet_horario[8] = '<center>08:30 - 09:30</center>';
        $vet_horario[9] = '<center>09:50 - 10:50</center>';
        $vet_horario[10] = '<center>10:50 - 11:50</center>';
        $vet_horario[13] = '<center>Integrado<br>13:20 - 14:20<br><br>Concomitante<br>13:40 - 14:30</center>';
        $vet_horario[14] = '<center>Integrado<br>14:20 - 15:20<br><br>Concomitante<br>14:30 - 15:20</center>';
        $vet_horario[15] = '<center>Integrado<br>15:35 - 16:35<br><br>Concomitante<br>15:35 - 16:25</center>';
        $vet_horario[16] = '<center>Integrado<br>16:35 - 17:35<br><br>Concomitante<br>16:25 - 17:15</center>';
        $vet_horario[17] = '<center>Concomitante<br>17:10 - 18:00</center>';        
        $vet_horario[18] = '<center>18:20 - 19:20</center>';
        $vet_horario[19] = '<center>19:20 - 20:20</center>';
        $vet_horario[20] = '<center>20:30 - 21:30</center>';
        $vet_horario[21] = '<center>21:30 - 22:30</center>';        
        
        $result = $this->horarioM->horarioProfessor($id_usuario,$_POST['id_periodo'],$semestre);

        $tabela = '';
        $tabela .= '<table class="table table-responsive table-bordered">' . "\n";
        $tabela .= '<thead>' . "\n";
        $tabela .= '<tr class="info">' . "\n";
        $tabela .= '<th width="10%" style="background-color:#D9EDF7 !important; text-align:center">Horário</th>' . "\n";
        $tabela .= '<th width="18%" style="background-color:#D9EDF7 !important">Segunda</th>' . "\n";
        $tabela .= '<th width="18%" style="background-color:#D9EDF7 !important">Terça</th>' . "\n";
        $tabela .= '<th width="18%" style="background-color:#D9EDF7 !important">Quarta</th>' . "\n";
        $tabela .= '<th width="18%" style="background-color:#D9EDF7 !important">Quinta</th>' . "\n";
        $tabela .= '<th width="18%" style="background-color:#D9EDF7 !important">Sexta</th>' . "\n";
        $tabela .= '</tr>' . "\n";
        $tabela .= '</thead>' . "\n";
        $tabela .= '<tbody>' . "\n";

        $total_linhas = mysqli_num_rows($result);
        $chs = 0;
        $cht_vet = array();
        
        if ($total_linhas > 0) {
            
            $linha = mysqli_fetch_assoc($result);
            
            if (isset($linha['id_horario'])) {
                $chs = 1;
            }
            $i = 2;
            $horario = '';
            $linha_atual = 0;
            $loops = 0;

            $id_hora = 0;
            if ($linha['turno'] == 'Vespertino') {
                $id_hora = 12;
            } else {
                $id_hora = 17;
            }
            while (($linha_atual < $total_linhas) && ($loops < 100)) {
                             
                $loops++;

                if ($i == 2) {
                    
                    if (($linha['id_hora'] - $id_hora) > 1) {
                        $add = $linha['id_hora'] - $id_hora;
                        while ($add > 1) {
                            $tabela .= '<tr>';
                            if (($id_hora == 10)|| ($id_hora == 11)) {
                                $id_hora = 12;
                                $tabela .= '<td><b>' . $vet_horario[$id_hora + 1] . '</b></td>';
                            } else {
                                $tabela .= '<td><b>' . $vet_horario[$id_hora + 1] . '</b></td>';
                            }
                            
                            $tabela .= "<td>&nbsp;</td>";
                            $tabela .= "<td>&nbsp;</td>";
                            $tabela .= "<td>&nbsp;</td>";
                            $tabela .= "<td>&nbsp;</td>";
                            $tabela .= "<td>&nbsp;</td>";
                            
                            $tabela .= '</tr>';
                            $id_hora++;
                            $add = $linha['id_hora'] - $id_hora;
                        }
                    }
                    $id_hora = $linha['id_hora'];

                    $tabela .= '<tr>';
                    $tabela .= '<td align="center"><b>' . $linha['horario'] . '</b></td>' . "\n";
                    $horario = $linha['horario'];
                }

                if (($linha['id_dia'] == $i) && ($horario == $linha['horario'])) {

                    if ($linha['sala'] == 'EAD') {
                        $tabela .= "<td style='background-color:#FFF5EE !important' id='td_" . $linha['id_horario'] . "' class=\"marca\" onmouseover=\"this.className='marca_over'; return true\" onmouseout=\"this.className='marca';return true\" title='" . $linha['id_horario'] . "'>";
                    } else {
                        $tabela .= "<td id='td_" . $linha['id_horario'] . "' class=\"marca\" onmouseover=\"this.className='marca_over'; return true\" onmouseout=\"this.className='marca';return true\" title='" . $linha['id_horario'] . "'>";
                    }
                    $tabela .= "<div>";
                    $tabela .= '<div style="color:blue !important" title="' . $linha['id_disciplina'] . '">' . $linha['disciplina'] . '</div>';
                    $tabela .= '<div style="color:green !important">' . $linha['turma'] . '</div>';
                    $tabela .= '<div style="color:brown !important">' . $linha['sala'] . '</div>';
                    /*
                     * Consulta para recuperar o código do SIGAA para exibir no horário.
                     */
                    $result_grade = $this->horarioM->getGrade($linha['id_disciplina'], $linha['id_curso']);
                    if (mysqli_num_rows($result_grade) > 0) {
                        while ($linha_grade = mysqli_fetch_assoc($result_grade)) {
                            $tabela .= '<div style="color:SlateBlue !important">COD SIGAA: ' . $linha_grade['cod_sigaa'] . '</div>';
                        }
                    } 
                    $tabela .= '</div>' . "\n";
                    $tabela .= '</td>' . "\n";
                    $linha = mysqli_fetch_assoc($result);
                    if (isset($linha['id_horario'])) {
                        $chs++;
                        if (!array_key_exists($linha['id_disciplina'].'_'.$linha['id_turma'], $cht_vet)) {
                            $cht_vet[$linha['id_disciplina'].'_'.$linha['id_turma']] = $linha['cht'];
                        }
                    }
                    $linha_atual++;
                } else {
                    $tabela .= "<td>&nbsp;</td>";
                }
                $i++;

                if ($i > 6) {
                    $i = 2;
                    $tabela .= '</tr>';
                }
            }
            if ($i > 2) {
                for ($j = $i; $j < 6; $j++) {
                    $tabela .= "<td>&nbsp;</td>";
                }
                $tabela .= '</tr>';
            }
        }
        $tabela .= '</tbody>' . "\n";
        $tabela .= '</table>' . "\n";
        
        $result_ead = $this->horarioM->getCargaHorariaEadDocente($_POST['id_periodo'],$id_usuario);
        $total_linhas_ead = mysqli_num_rows($result_ead);
        
        if ($total_linhas_ead > 0) {        
            $tabela .= '<table class="table table-responsive table-bordered">' . "\n";
            $tabela .= '<thead>' . "\n";
            $tabela .= '<tr class="success">' . "\n";
            $tabela .= '<th colspan="4" style="text-align:center">Disciplinas dos cursos a distância</th>' . "\n";
            $tabela .= '</tr>' . "\n";
            $tabela .= '<tr class="info">' . "\n";
            $tabela .= '<th width="30%">Curso</th>' . "\n";
            $tabela .= '<th width="30%">Turma</th>' . "\n";
            $tabela .= '<th width="60%">Disciplina</th>' . "\n";
            $tabela .= '<th width="20%">CHS</th>' . "\n";
            $tabela .= '</tr>' . "\n";
            $tabela .= '</thead>' . "\n";
            $tabela .= '<tbody>' . "\n";
            while ($linha_ead = mysqli_fetch_assoc($result_ead)) {
                $tabela .= '<tr>';
                $tabela .= '<td>'.$linha_ead['curso'].'</td>';
                $tabela .= '<td>'.$linha_ead['turma'].'</td>';
                
                /*
                 * Consulta para recuperar o código do SIGAA para exibir no horário.
                 */
                $codigos = '';
                $result_grade = $this->horarioM->getGrade($linha_ead['id_disciplina'], $linha_ead['id_curso']);
                if (mysqli_num_rows($result_grade) > 0) {
                    while ($linha_grade = mysqli_fetch_assoc($result_grade)) {
                        $codigos .= $linha_grade['cod_sigaa'] . ' ';
                    }
                }                
                
                $tabela .= '<td>'.$linha_ead['disciplina'].' ('.trim($codigos).')</td>';
                $tabela .= '<td>'.$linha_ead['chs'].'</td>';
                $tabela .= '</tr>';
                $chs = $chs + $linha_ead['chs'];
                $cht_vet[$linha_ead['id_disciplina'].'_'.$linha_ead['id_turma']] = $linha_ead['cht'];
            }
            $tabela .= '</tbody>' . "\n";
            $tabela .= '<table>' . "\n";
        }

        $this->logM = new log_acaoModel();
        $this->logM->inserir(array('id_usuario'=>$_SESSION['id_usuario'],'acao'=>'Consulta Horário Professor','data_hora'=>date('Y-m-d H:i:s')));
        
        $cht = array_sum($cht_vet) / 20;
        $resposta = array('tabela' => $tabela, 'chs' => $chs, 'cht' => number_format($cht, 2));
        return json_encode($resposta);
    }

    public function carregarProfessor() {

        $select = '';
        $usuarioM = new usuarioModel();
        $ordem = array('nome' => 'ASC');

        $select .= "<option value=''>&nbsp;</option>";
        $result = $usuarioM->listar(11, array(), $ordem);
        while ($linha = mysqli_fetch_assoc($result)) {
            if ($_SESSION['id_usuario'] == $linha['id_usuario']) {
                $select .= "<option selected='selected' value='{$linha['id_usuario']}'>";
            } else {
                $select .= "<option value='{$linha['id_usuario']}'>";
            }
            $select .= $linha['nome'];
            $select .= '</option>';
        }
        $resposta = array('select' => $select);
        return json_encode($resposta);
    }
    
    public function carregarPeriodo() {
        $select = '<label for="id_periodo">Periodo:</label>';
        $select .= '<select id="id_periodo" name="id_periodo" class="form-control" style="width:100%;">';
        $periodoM = new periodoModel();
        $resultado_periodos = null;
        
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

}

// Callback
if (isset($_POST['metodo'])) {
    $metodo = $_POST['metodo'];
    $objeto = new horario_professorController();
    echo $objeto->$metodo();
}