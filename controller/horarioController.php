<?php

session_start();
require_once $_SESSION['diretorio_base'] . '/model/horarioModel.php';
require_once $_SESSION['diretorio_base'] . '/model/oferta_disciplinaModel.php';
require_once $_SESSION['diretorio_base'] . '/model/salaModel.php';
require_once $_SESSION['diretorio_base'] . '/model/periodoModel.php';
require_once $_SESSION['diretorio_base'] . '/model/log_acaoModel.php';

class horarioController {

    private $horarioM;
    private $tabela;

    public function __construct() {
        $this->horarioM = new horarioModel();
    }

    public function carregarPeriodo() {
        $select = '<label for="id_periodo">Periodo:</label>';
        $select .= '<select id="id_periodo" name="id_periodo" class="form-control">';
        $periodoM = new periodoModel();
        $resultado_periodos = $periodoM->listar(array(), array('id_periodo' => 'DESC'));

        while ($linha = mysqli_fetch_assoc($resultado_periodos)) {
            $select .= "<option value='{$linha['id_periodo']}'>";
            $select .= $linha['ano'] . '/' . $linha['semestre'];
            $select .= '</option>';
        }
        $select .= '</select>';
        $resposta = array('select' => $select);
        return json_encode($resposta);
    }

    public function getMoldura() {

        $periodo = explode("/",$_POST['periodo']);
        $semestre = $periodo[1];        
        
        $this->tabela = '';

        $salaM = new salaModel();
        $resultado_salas = $salaM->listar();
        $vet_salas = array();
        while ($linhs_salas = mysqli_fetch_assoc($resultado_salas)) {
            $vet_salas[$linhs_salas['id_sala']] = $linhs_salas['descricao'];
        }

        $ofertaM = new oferta_disciplinaModel();
        $result_turma = $ofertaM->getTurmasAtivas($_POST['id_periodo'],$semestre);

        while ($linha_turma = mysqli_fetch_assoc($result_turma)) {

            $ofertaM = new oferta_disciplinaModel();
            $resultado_disciplinas = $ofertaM->getDisciplinasOfertadas($linha_turma['id_turma']);
            $vet_disciplinas = array();
            $vet_chs = array();
            $vet_chs_ead = array();
            $vet_chsd = array();
            $vet_ead = array();
            $vet_nome = array();
            $vet_professor = array();
            while ($linha_disciplinas = mysqli_fetch_assoc($resultado_disciplinas)) {
                $vet_disciplinas[$linha_disciplinas['id_oferta_disciplina']] = $linha_disciplinas;
                $vet_chs[$linha_disciplinas['id_turma'] . '_' . $linha_disciplinas['id_disciplina']] = $linha_disciplinas['chs'];
                $vet_chs_ead[$linha_disciplinas['id_turma'] . '_' . $linha_disciplinas['id_disciplina']] = $linha_disciplinas['chs_ead'];
                $vet_nome[$linha_disciplinas['id_turma'] . '_' . $linha_disciplinas['id_disciplina']] = $linha_disciplinas['disciplina'];
                $vet_chsd[$linha_disciplinas['id_turma'] . '_' . $linha_disciplinas['id_disciplina']] = 0;
                $vet_ead[$linha_disciplinas['id_turma'] . '_' . $linha_disciplinas['id_disciplina']] = 0;
                $vet_professor[$linha_disciplinas['id_turma'] . '_' . $linha_disciplinas['id_disciplina']] = $linha_disciplinas['professor'];
            }
            $this->tabela .= '<div class="container-fluid">';
            $this->tabela .= '<div class="col-md-12">';
            $this->tabela .= '<div class="panel panel-success">';
            $this->tabela .= '<div class="panel-heading">Turma: ' . $linha_turma['turma'] . '</div>';
            $this->tabela .= '<div class="panel-body">';

            $id_hora = '';
            $result = $this->horarioM->getMoldura($linha_turma['turno'], $linha_turma['nivel']);
            if ($result) {
                $this->tabela .= '<div class="col-md-8">';
                $this->tabela .= '<table class="table table-striped table-hover">';
                $this->tabela .= '<thead>';
                $this->tabela .= '<tr>';
                $this->tabela .= '<th>&nbsp;</th>';
                $this->tabela .= '<th>Segunda</th>';
                $this->tabela .= '<th>Terça</th>';
                $this->tabela .= '<th>Quarta</th>';
                $this->tabela .= '<th>Quinta</th>';
                $this->tabela .= '<th>Sexta</th>';
                $this->tabela .= '<th>Sábado</th>';
                $this->tabela .= '</tr>';
                $this->tabela .= '</thead>';
                $this->tabela .= '<tbody>';
                while ($linha = mysqli_fetch_assoc($result)) {

                    if ($id_hora != $linha['id_hora']) {

                        if ($id_hora != '') {
                            $this->tabela .= '</tr>';
                        }
                        $this->tabela .= '<tr>';
                        $this->tabela .= '<td style="vertical-align:middle;text-align:center;width:10%"><b>' . substr($linha['inicio'], 0, 5) . '<br> as <br>' . substr($linha['fim'], 0, 5) . '</b></td>';
                    }

                    $this->tabela .= '<td>';
                    $this->tabela .= '<div id="m_' . $linha_turma['id_turma'] . '_' . $linha['id_dia'] . '_' . $linha['id_hora'] . '"></div>';
                    $this->tabela .= 'Disciplina: <br>';
                    $this->tabela .= $this->getHorario($vet_disciplinas, $vet_salas, $linha_turma['id_turma'], $linha['id_dia'], $linha['id_hora'], $vet_chsd, $vet_ead);

                    $this->tabela .= '</td>';
                    $id_hora = $linha['id_hora'];
                }
                $this->tabela .= '</tr>';
                $this->tabela .= '</tbody>';
                $this->tabela .= '</table>';
                $this->tabela .= '</div>';

                $this->tabela .= '<div class="col-md-4">';
                $this->tabela .= '<table class="table table-striped table-hover" style="margin-top:30px">';
                $this->tabela .= '<thead>';
                $this->tabela .= '<tr style="font-size:14px; background-color:#F0FFF0">';
                $this->tabela .= '<th>&nbsp</th>';
                $this->tabela .= '<th colspan="2" style="text-align:left">Carga Horária</th>';
                $this->tabela .= '<th colspan="2" style="text-align:center">Carga Horária <br>Distribuída</th>';
                $this->tabela .= '</tr>';
                $this->tabela .= '<tr style="font-size:14px">';
                $this->tabela .= '<th>Disciplina</th>';
                $this->tabela .= '<th>CHS</th>';
                $this->tabela .= '<th>EAD</th>';
                $this->tabela .= '<th style="background-color:#FFFFF0">CHS</th>';
                $this->tabela .= '<th style="background-color:#FFFFF0">EAD</th>';
                $this->tabela .= '</tr>';
                $this->tabela .= '</thead>';
                $this->tabela .= '<tbody>';
                foreach ($vet_chs as $id_disciplina => $chs) {
                    $cor = 'red';
                    if ($vet_chsd[$id_disciplina] == $chs) {
                        $cor = 'blue';
                    }
                    $cor_ead = 'red';
                    if ($vet_ead[$id_disciplina] == $vet_chs_ead[$id_disciplina]) {
                        $cor_ead = 'green';
                    }

                    $this->tabela .= '<tr>';
                    $this->tabela .= '<td>' . $vet_nome[$id_disciplina] . '</td>';
                    $this->tabela .= '<td id="chs_disciplina_' . $id_disciplina . '" style="font-weight:bold">' . $chs . '</td>';
                    $this->tabela .= '<td id="chs_ead_disciplina_' . $id_disciplina . '" style="font-weight:bold">' . $vet_chs_ead[$id_disciplina] . '</td>';
                    $this->tabela .= '<td style="background-color:#FFFFF0;font-weight:bold;color:' . $cor . '" id="' . $id_disciplina . '">' . $vet_chsd[$id_disciplina] . '</td>';
                    $this->tabela .= '<td style="background-color:#FFFFF0;font-weight:bold;color:' . $cor_ead . '" title="ead_' . $id_disciplina . '" id="ead_' . $id_disciplina . '">' . $vet_ead[$id_disciplina] . '</td>';
                    $this->tabela .= '</tr>';
                }
                $this->tabela .= '</tbody>';
                $this->tabela .= '</table>';
                $this->tabela .= '</div>';
            } else {
                $this->tabela .= '<div class="col-md-12">';
                $this->tabela .= '<table class="table table-striped table-hover" style="margin-top:30px">';
                $this->tabela .= '<thead>';
                $this->tabela .= '<tr style="font-size:14px; background-color:#F0FFF0">';
                $this->tabela .= '<th>&nbsp</th>';
                $this->tabela .= '<th>&nbsp</th>';
                $this->tabela .= '<th colspan="2" style="text-align:left">Carga Horária</th>';
                $this->tabela .= '</tr>';
                $this->tabela .= '<tr style="font-size:14px">';
                $this->tabela .= '<th>Disciplina</th>';
                $this->tabela .= '<th>Professor</th>';
                $this->tabela .= '<th>CHS</th>';
                $this->tabela .= '<th>EAD</th>';
                $this->tabela .= '</tr>';
                $this->tabela .= '</thead>';
                $this->tabela .= '<tbody>';
                foreach ($vet_chs as $id_disciplina => $chs) {
                    $cor = 'red';
                    if ($vet_chsd[$id_disciplina] == $chs) {
                        $cor = 'blue';
                    }
                    $cor_ead = 'red';
                    if ($vet_ead[$id_disciplina] == $vet_chs_ead[$id_disciplina]) {
                        $cor_ead = 'green';
                    }

                    $this->tabela .= '<tr>';
                    $this->tabela .= '<td>' . $vet_nome[$id_disciplina] . '</td>';
                    $this->tabela .= '<td>' . $vet_professor[$id_disciplina] . '</td>';
                    $this->tabela .= '<td id="chs_disciplina_' . $id_disciplina . '" style="font-weight:bold">' . $chs . '</td>';
                    $this->tabela .= '<td id="chs_ead_disciplina_' . $id_disciplina . '" style="font-weight:bold">' . $vet_chs_ead[$id_disciplina] . '</td>';
                    $this->tabela .= '</tr>';
                }
                $this->tabela .= '</tbody>';
                $this->tabela .= '</table>';
                $this->tabela .= '</div>';                
            }
  
            $this->tabela .= '</div>';
            $this->tabela .= '</div>';
            $this->tabela .= '</div>';
            $this->tabela .= '</div>';
            $this->tabela .= '</div>';
        }

        $resposta = array('moldura' => $this->tabela);
        return json_encode($resposta);
    }

    private function disciplinaCadastrada($id_turma, $id_dia, $id_hora, $id_disciplina, $id_usuario) {
        $result = $this->horarioM->disciplinaCadastrada($id_turma, $id_dia, $id_hora, $id_disciplina, $id_usuario);
        $linha = mysqli_fetch_assoc($result);
        return $linha;
    }

    private function getHorario($vet_disciplina, $vet_salas, $id_turma, $id_dia, $id_hora, &$vet_chsd, &$vet_ead) {

        $disciplinas = '';
        $id_sala_horario = '';

        if (count($vet_disciplina) > 0) {

            $disciplinas = '<select style="width:100%" id="d_' . $id_turma . '_' . $id_dia . '_' . $id_hora . '" onchange="gravarOferta(this)" onfocus="setDisciplina(this)" onmouseover="' . "this.title = $('#d_{$id_turma}_{$id_dia}_{$id_hora} option:selected').text()" . '">';
            $disciplinas .= '<option value=""></option>';

            foreach ($vet_disciplina as $id_oferta_disciplina => $linha) {

                // Busca a existencia de horário cadastrado para disciplina.
                $linha_horario = $this->horarioM->getHorario($id_dia, $id_hora, $id_turma);

                if ($linha_horario != 0) {
                    if ($linha_horario['id_oferta_disciplina'] == $id_oferta_disciplina) {
                        $disciplinas .= '<option selected="selected" value="' . $id_oferta_disciplina . '_' . $linha['id_usuario'] . '_' . $linha['id_disciplina'] . '">';
                        $id_sala_horario = $linha_horario['id_sala'];
                        $vet_chsd[$linha['id_turma'] . '_' . $linha['id_disciplina']] = $vet_chsd[$linha['id_turma'] . '_' . $linha['id_disciplina']] + 1;
                        if ($vet_salas[$id_sala_horario] == 'EAD') {
                            $vet_ead[$linha['id_turma'] . '_' . $linha['id_disciplina']] = $vet_ead[$linha['id_turma'] . '_' . $linha['id_disciplina']] + 1;
                        }
                    } else {
                        $disciplinas .= '<option value="' . $id_oferta_disciplina . '_' . $linha['id_usuario'] . '_' . $linha['id_disciplina'] . '">';
                    }
                } else {

                    $disciplinas .= '<option value="' . $id_oferta_disciplina . '_' . $linha['id_usuario'] . '_' . $linha['id_disciplina'] . '">';
                }

                $disciplinas .= $linha['disciplina'] . ' (' . $linha['professor'] . ')';
                $disciplinas .= '</option>';
            }
            $disciplinas .= '</select>';

            $disciplinas .= '<br><br>Sala:<br>';

            $disciplinas .= '<select style="width:100%" id="s_' . $id_turma . '_' . $id_dia . '_' . $id_hora . '" id_sala_antiga="' . $id_sala_horario . '" onFocus="document.forms[0].id_sala_antiga.value=this.value" onChange="gravarSala(this);document.forms[0].id_sala_antiga.value=this.value">';
            //$disciplinas .= '<option value=""></option>';
            foreach ($vet_salas as $id_sala => $sala) {
                if ($id_sala == $id_sala_horario) {
                    $disciplinas .= '<option selected="selected" value="' . $id_sala . '">' . $sala . '</option>';
                } else {
                    $disciplinas .= '<option value="' . $id_sala . '">' . $sala . '</option>';
                }
            }
            $disciplinas .= '</select>';
        }
        return $disciplinas;
    }

    public function existeChoque() {

        $periodo = explode("/",$_POST['periodo']);
        $semestre = $periodo[1];          
        
        $linha = $this->horarioM->getHorario($_POST['id_dia'], $_POST['id_hora'], $_POST['id_turma']);

        if ($linha != 0) {
            if ($linha['id_usuario'] == $_POST['id_usuario']) {
                $resposta = array('resultado' => false, 'msg' => 'Sem colisão!');
            } else {
                $resultado = $this->horarioM->existeChoque($_POST['id_usuario'], $_POST['id_dia'], $_POST['id_hora'], $_POST['id_periodo'], $semestre);
                if ($resultado->num_rows > 0) {
                    $linha = mysqli_fetch_assoc($resultado);
                    $msg = '<span class="glyphicon glyphicon glyphicon-warning-sign alert-warning btn-sm" style="width:100%; text-align:center">&nbsp;Conflito de horário:<br>';
                    $msg .= 'Turma: ' . $linha['turma'] . '<br>';
                    $msg .= 'Disciplina: ' . $linha['disciplina'] . '<br>';
                    $msg .= '</span>';
                    $resposta = array('resultado' => true, 'msg' => $msg);
                } else {
                    $resposta = array('resultado' => false, 'msg' => 'Sem colisão!');
                }
            }
        } else {

            $resultado = $this->horarioM->existeChoque($_POST['id_usuario'], $_POST['id_dia'], $_POST['id_hora'], $_POST['id_periodo'], $semestre);
            if ($resultado->num_rows > 0) {
                $linha = mysqli_fetch_assoc($resultado);
                $msg = '<span class="glyphicon glyphicon glyphicon-warning-sign alert-warning btn-sm" style="width:100%; text-align:center">&nbsp;Conflito de horário:<br>';
                $msg .= 'Turma: ' . $linha['turma'] . '<br>';
                $msg .= 'Disciplina: ' . $linha['disciplina'] . '<br>';
                $msg .= '</span>';
                $resposta = array('resultado' => true, 'msg' => $msg);
            } else {
                $resposta = array('resultado' => false, 'msg' => 'Sem colisão!');
            }
        }

        return json_encode($resposta);
    }

    public function gravar() {
        $msg = '';

        $linha = $this->horarioM->getHorario($_POST['id_dia'], $_POST['id_hora'], $_POST['id_turma']);
        if ($linha != 0) {
            if ((trim($_POST['id_oferta_disciplina']) == '') && ($linha['id_horario'] != 0)) {

                $this->horarioM->excluir($linha['id_horario']);
                $msg = '<span class="glyphicon glyphicon glyphicon-ok alert-success btn-sm" style="width:100%; text-align:center">&nbsp;Excluído!</span>';
            } else {

                if ($linha['id_horario'] != 0) {
                    $_POST['id_horario'] = $linha['id_horario'];

                    $this->horarioM->atualizar($_POST);
                    $msg = '<span class="glyphicon glyphicon glyphicon-ok alert-success btn-sm" style="width:100%; text-align:center">&nbsp;Atualizado!</span>';
                } else {

                    $this->horarioM->inserir($_POST);
                    $msg = '<span class="glyphicon glyphicon glyphicon-ok alert-success btn-sm" style="width:100%; text-align:center">&nbsp;Inserido!</span>';
                }
            }
        } else {
            $this->horarioM->inserir($_POST);
            $msg = '<span class="glyphicon glyphicon glyphicon-ok alert-success btn-sm" style="width:100%; text-align:center">&nbsp;Inserido!</span>';
        }

        $this->logM = new log_acaoModel();
        $this->logM->inserir(array('id_usuario' => $_SESSION['id_usuario'], 'acao' => 'Administrativo', 'data_hora' => date('Y-m-d H:i:s')));

        $resposta = array('resultado' => 'OK', 'msg' => $msg);
        return json_encode($resposta);
    }

}

// Callback
if (isset($_POST['metodo'])) {
    $metodo = $_POST['metodo'];
    $objeto = new horarioController();
    echo $objeto->$metodo();
}