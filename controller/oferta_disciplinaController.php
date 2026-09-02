<?php

session_start();
require_once $_SESSION['diretorio_base'] . '/model/oferta_disciplinaModel.php';
require_once $_SESSION['diretorio_base'] . '/model/turmaModel.php';
require_once $_SESSION['diretorio_base'] . '/model/disciplinaModel.php';
require_once $_SESSION['diretorio_base'] . '/model/cursoModel.php';
require_once $_SESSION['diretorio_base'] . '/model/usuarioModel.php';
require_once $_SESSION['diretorio_base'] . '/model/periodoModel.php';
require_once $_SESSION['diretorio_base'] . '/model/horarioModel.php';

class oferta_disciplinaController {

    private $oferta_disciplinaM;
    private $usuarioM;
    private $disciplinaM;
    private $turmaM;
    private $msg;

    public function __construct() {
        $this->oferta_disciplinaM = new oferta_disciplinaModel();
    }

    public function listar() {

        $periodo = explode("/",$_POST['periodo']);
        $semestre = $periodo[1];
        
        $ordenacao = array('turma.descricao' => 'ASC',
            'disciplina.descricao' => 'ASC',
            'usuario.nome' => 'ASC');

        $tabela = '';
        $result = $this->oferta_disciplinaM->listar($_POST['id_periodo'], $_POST['id_nucleo_busca'], $_POST['id_turma_busca'],$semestre, array(), $ordenacao);
        $total_linhas = mysqli_num_rows($result);
        $turma = '';

        if ($total_linhas > 0) {

            $array_professor = array();
            $usuarioM = new usuarioModel();
            $result_professor = $usuarioM->listar(11,array(),array("nome"=>"ASC"));
            while ($linha_professor = mysqli_fetch_assoc($result_professor)) {
                $array_professor[$linha_professor['id_usuario']] = $linha_professor['nome'];
            }

            $tabela .= '<div class="container-fluid">';
            $tabela .= '<div class="col-sm-7">';

            $i = 0;
            while ($linha = mysqli_fetch_assoc($result)) {

                if ($turma != $linha['turma']) {

                    if ($turma != '') {
                        $tabela .= '</tbory>';
                        $tabela .= '</table>';
                        $tabela .= '</div>';
                        $tabela .= '</div>';
                    }

                    $i++;
                    $tabela .= '<div class="panel panel-success" id="painel_turma_' . $i . '">';
                    $tabela .= '<div class="panel panel-heading">' . $linha['turma'] . '</div>';
                    $tabela .= '<div class="panel panel-body">';

                    $tabela .= '<button type="button" class="btn btn-success form-control" id="btn_adicionar" style="width: 170px;text-align:center; margin-bottom:20px" onClick="abrirModal(' . "'modal_formulario', 'inserir', 0, {$linha['id_turma']})" . '">';
                    $tabela .= '<span class="glyphicon glyphicon-plus"></span> Adicionar oferta';
                    $tabela .= '</button>';
                    $tabela .= '<div id="msg_' . $linha['id_turma'] . '"></div>';

                    $tabela .= '<table class="table table-striped table-hover table-condensed" id="turma_' . $linha['id_turma'] . '">';
                    $tabela .= '<thead>';
                    $tabela .= '<tr>';
                    $tabela .= '<th width="5%">ID</th>';
                    $tabela .= '<th width="38%">Disciplina</th>';
                    $tabela .= '<th width="5%">CHS</th>';
                    $tabela .= '<th width="5%">EAD</th>';
                    $tabela .= '<th width="5%">CHT</th>';
                    $tabela .= '<th width="38%">Professor</th>';
                    $tabela .= '<th width="1%">&nbsp;</th>';
                    $tabela .= '<th width="3%">&nbsp;</th>';
                    $tabela .= '</tr>';
                    $tabela .= '</thead>';
                    $tabela .= '<tbory>';
                    $turma = $linha['turma'];
                }
                if ($linha['id_oferta_disciplina'] != '') {
                    $tabela .= '<tr>';
                    $tabela .= '<td>' . $linha['id_oferta_disciplina'] . '</td>';
                    $tabela .= '<td>' . $linha['disciplina'] . '<br><span style="font-size:9pt;background-color:#FFF8DC">';
                    $tabela .= '<b>CHS: </b><span style="color:blue;font-weight:bold">' . $linha['chs_disciplina'] . '</span> &nbsp;&nbsp;<b>CHS EAD: </b><span style="color:green;font-weight:bold">' . $linha['chs_ead_disciplina'] . '</span>&nbsp;&nbsp;&nbsp;<b>CHT: </b><span style="color:green;font-weight:bold">' . $linha['cht_disciplina'] . '</span>';
                    $tabela .= '</span></td>';
                    $tabela .= '<td align="center"><input onChange="atualizar_chs(' . $linha['id_oferta_disciplina'] . ',' . $linha['cht_disciplina'] / $linha['chs_disciplina'] . ')" class="form-control" style="width:40px" type="text" name="chs_' . $linha['id_oferta_disciplina'] . '" id="chs_' . $linha['id_oferta_disciplina'] . '" value="' . $linha['chs'] . '"></td>';
                    $tabela .= '<td align="center"><input onChange="atualizar_chs_ead(' . $linha['id_oferta_disciplina'] . ')" class="form-control" style="width:40px" type="text" name="chs_ead_' . $linha['id_oferta_disciplina'] . '" id="chs_ead_' . $linha['id_oferta_disciplina'] . '" value="' . $linha['chs_ead'] . '"></td>';
                    $tabela .= '<td align="center"><input readonly class="form-control" style="width:50px" type="text" name="cht_' . $linha['id_oferta_disciplina'] . '" id="cht_' . $linha['id_oferta_disciplina'] . '" value="' . $linha['cht'] . '"></td>';

                    $tabela .= '<td>';
                    $count_professor = 0;
                    foreach ($array_professor as $id_usuario => $nome) {
                        if ($count_professor == 0) {
                            $tabela .= '<select class="form-control form-control" style="width:95%" id="professor_' . $linha['id_oferta_disciplina'] . '" name="professor_' . $linha['id_oferta_disciplina'] . '" onfocus="this.oldvalue = this.value;" onChange="choques_horario(' . $linha['id_oferta_disciplina'] . ',this);this.oldvalue = this.value;">';
                            $tabela .= '<option value=""></option>';
                        }
                        if ($id_usuario == $linha['id_usuario']) {
                            $tabela .= '<option selected="selected" value="' . $id_usuario . '">' . $nome . '</option>';
                        } else {
                            $tabela .= '<option value="' . $id_usuario . '">' . $nome . '</option>';
                        }
                        $count_professor++;
                    }
                    $tabela .= '</select>';
                    $tabela .= '</td>';

                    $tabela .= '<td>';
                    $tabela .= '<a href="#void" onclick="abrirModal(' . "'modal_confirmacao','deletar'," . $linha['id_oferta_disciplina'] . ',' . $linha['id_turma'] . ')" style="color:red">';
                    $tabela .= '<span class="glyphicon glyphicon-remove"></span>';
                    $tabela .= '</a>';
                    $tabela .= '</td>';

                    $tabela .= '<td>';
                    if ($linha['tipo'] == 'Aula') {
                        $tabela .= '<a id="tipo_cor_'.$linha['id_oferta_disciplina'].'" onclick="replaceClass('."'{$linha['id_oferta_disciplina']}','glyphicon glyphicon-education','glyphicon glyphicon-wrench'".')" href="#void" style="color:blue">';
                        $tabela .= '<span id="tipo_'.$linha['id_oferta_disciplina'].'" class="glyphicon glyphicon-education"></span>';
                    } else {
                        $tabela .= '<a id="tipo_cor_'.$linha['id_oferta_disciplina'].'" onclick="replaceClass('."'{$linha['id_oferta_disciplina']}','glyphicon glyphicon-wrench','glyphicon glyphicon-education'".')" href="#void" style="color:orange">';
                        $tabela .= '<span id="tipo_'.$linha['id_oferta_disciplina'].'" class="glyphicon glyphicon-wrench"></span>';
                    }
                    $tabela .= '</a>';
                    $tabela .= '</td>';

                    $tabela .= '</tr>';
                }
            }

            $tabela .= '</tbory>';
            $tabela .= '</table>';
            $tabela .= '</div>';
            $tabela .= '</div>';
        }
        $tabela .= '</div>';

        $tabela .= '<div class="col-sm-5" id="div_painel_carga_horaria">';
        $tabela .= '<div class="panel panel-info" id="painel_carga_horaria" style="position:fixed">';
        $tabela .= '<div class="panel panel-heading">Carga Horária</div>';
        $tabela .= '<div class="panel panel-body" style="overflow-y: auto;" id="corpo_painel_ch">';

        $tabela .= '<table class="table table-striped table-hover table-condensed">';
        $tabela .= '<thead>';
        $tabela .= '<tr>';
        $tabela .= '<th width="80%">Professor</th>';
        $tabela .= '<th width="10%">CHS</th>';
        $tabela .= '<th width="10%">EAD</th>';
        $tabela .= '</tr>';
        $tabela .= '</thead>';
        $tabela .= '<tbory>';

        $result_ch = $this->oferta_disciplinaM->getCargaHoraria($_POST['id_periodo'],$semestre);
        while ($linha_ch = mysqli_fetch_assoc($result_ch)) {
            $tabela .= '<tr>';
            $tabela .= '<td>' . $linha_ch['nome'] . '</td>';
            $tabela .= '<td align="center">' . $linha_ch['chs'] . '</td>';
            $tabela .= '<td align="center">' . $linha_ch['chs_ead'] . '</td>';
            $tabela .= '</tr>';
        }
        $tabela .= '</div>';
        $tabela .= '</div>';
        $tabela .= '</div>';

        $resposta = array('tabela' => $tabela);
        return json_encode($resposta);
    }

    public function choques_horario() {

        $msg = '';
        $num_choques = 0;
        $tabela = '';
        $resultado = false;

        // SE O ANTERIOR FOR VAZIO NAO HA CHOQUE 
        if ($_POST['id_usuario_antigo'] == '') {
            $tabela .= '<div class="alert alert-info">Deseja realmente realizar essa operação? </div>';
            $tabela .= '<button type="button" style="margin:5px" class="btn btn-success" onclick="atualizar()">Sim</button>';
            $tabela .= '<button type="button" style="margin:5px" class="btn btn-danger" data-dismiss="modal">Não</button>';
        } else {

            // Horários da oferta de disciplina
            $horarioM = new horarioModel();
            $result_horarios_oferta = $horarioM->horariosOfertaDisciplina($_POST['id_oferta_disciplina']);

            // SE NÃO HOUVER HORÁRIO(S) PARA OFERTA DE DISCIPLINA NÃO HAVERÀ CHOQUE
            if (mysqli_num_rows($result_horarios_oferta) == 0) {
                $tabela .= '<div class="alert alert-info">Não existe horário associado a esta oferta de disicplina! Deseja realmente realizar essa operação? </div>';
                $tabela .= '<button type="button" style="margin:5px" class="btn btn-success" onclick="atualizar()">Sim</button>';
                $tabela .= '<button type="button" style="margin:5px" class="btn btn-danger" data-dismiss="modal">Não</button>';
            } else {

                // SE O NOVO PROFESSOR FOR VAZIO OS HORÁRIOS ASSOCIADOS SERÃO APAGADOS
                // NESTE CASO NÃO HÁ CHOQUE
                if ($_POST['professor_' . $_POST['id_oferta_disciplina']] == "") {

                    $i = 0;
                    while ($linhaH = mysqli_fetch_assoc($result_horarios_oferta)) {
                        if ($i == 0) {
                            $tabela .= '<div class="panel panel-info">';
                            $tabela .= '<div class="panel-heading">Horários que serão excluídos</div>';
                            $tabela .= '<div class="panel-boby">';

                            $tabela .= '<div class="row">';
                            $tabela .= '<div class="col-sm-6">';
                            $tabela .= '<table class="table table-condensed table-striped table-bordered table-hover" style="margin:10px;margin-right:-5px; width:97%">';
                            $tabela .= '<tr>';
                            $tabela .= '<th colspan="2">Informações atuais</th>';
                            $tabela .= '</tr>';
                            $tabela .= '<tr>';
                            $tabela .= '<th>Disciplina </th><td>' . $linhaH['disciplina'] . '</td>';
                            $tabela .= '</tr>';
                            $tabela .= '<tr>';
                            $tabela .= '<th>Professor </th><td>' . $linhaH['professor'] . '</td>';
                            $tabela .= '</tr>';
                            $tabela .= '<tr>';
                            $tabela .= '<th>Turma </th><td>' . $linhaH['turma'] . '</td>';
                            $tabela .= '</tr>';
                            $tabela .= '</table>';
                            $tabela .= '</div>';

                            $tabela .= '<div class="col-sm-6">';
                            $tabela .= '<table class="table table-striped table-hover table-condensed table-bordered" style="margin:10px;margin-left:-5px; width:97%">';
                            $tabela .= '<tr>';
                            $tabela .= '<td colspan="2">Horários</td>';
                            $tabela .= '</tr>';
                            $tabela .= '<tr>';
                            $tabela .= '<th>Dia</th>';
                            $tabela .= '<th>Horário</th>';
                            $tabela .= '</tr>';
                        }
                        $i++;

                        $tabela .= '<tr>';
                        $tabela .= '<td>' . $linhaH['dia'] . '</td>';
                        $tabela .= '<td>' . $linhaH['horario'] . '</td>';
                        $tabela .= '</tr>';
                    }
                    $tabela .= '</table>';
                    $tabela .= '</div>';
                    $tabela .= '</div>';

                    $tabela .= '<div class="alert alert-danger" style="margin:10px;margin-top:30px; width:97%"> Esta operação irá apagar os horários atribuídos a essa disciplina! Deseja continuar?</div>';
                    $tabela .= '<button type="button" style="margin:5px;margin-left:10px;margin-bottom:15px" class="btn btn-success" onclick="atualizar2()">Sim</button>';
                    $tabela .= '<button type="button" style="margin:5px;margin-bottom:15px"  class="btn btn-danger" data-dismiss="modal">Não</button>';

                    // Será verificado os horários da disciplina ofertada com o professor antigo
                    // chocam com os horários das disicplinas do professor novo.
                } else {

                    $i = 0;
                    while ($linhaH = mysqli_fetch_assoc($result_horarios_oferta)) {

                        if ($i == 0) {
                            $tabela .= '<div class="panel panel-info">';
                            $tabela .= '<div class="panel-heading">Verificação de choques de horário com o novo professor</div>';
                            $tabela .= '<div class="panel-boby">';

                            $tabela .= '<table class="table table-condensed table-striped table-bordered table-hover" style="margin:10px; width:97%">';
                            $tabela .= '<tr>';
                            $tabela .= '<th colspan="2">Informações atuais</th>';
                            $tabela .= '</tr>';
                            $tabela .= '<tr>';
                            $tabela .= '<th>Disciplina </th><td>' . $linhaH['disciplina'] . '</td>';
                            $tabela .= '</tr>';
                            $tabela .= '<tr>';
                            $tabela .= '<th>Professor </th><td>' . $linhaH['professor'] . '</td>';
                            $tabela .= '</tr>';
                            $tabela .= '<tr>';
                            $tabela .= '<th>Turma </th><td>' . $linhaH['turma'] . '</td>';
                            $tabela .= '</tr>';
                            $tabela .= '</table>';

                            $tabela .= '<table class="table table-striped table-hover table-condensed table-bordered" style="margin:10px;margin-top:30px; width:97%">';
                            $tabela .= '<tr>';
                            $tabela .= '<td colspan="3">Horário atual da disciplina VS horário do professor (' . $_POST['professor_novo'] . ')</td>';
                            $tabela .= '</tr>';
                            $tabela .= '<tr>';
                            $tabela .= '<th>Dia</th>';
                            $tabela .= '<th>Horario</th>';
                            $tabela .= '<th>Horário novo professor</th>';
                            $tabela .= '</tr>';
                        }
                        $i++;

                        $tabela .= '<tr>';
                        $tabela .= '<td>' . $linhaH['dia'] . '</td>';
                        $tabela .= '<td>' . $linhaH['horario'] . '</td>';

                        $result_existe_choque = $horarioM->existeChoque($_POST['id_usuario'], $linhaH['id_dia'], $linhaH['id_hora'], $_POST['id_periodo']);
                        $total_choques = mysqli_num_rows($result_existe_choque);
                        if ($total_choques > 0) {
                            $linhaChoque = mysqli_fetch_assoc($result_existe_choque);
                            $tabela .= '<td class="warning"><span style="color:red">Choque: </span>' . $linhaChoque['disciplina'] . ' <b>(' . $linhaChoque['turma'] . ')</b></td>';
                            $num_choques++;
                        } else {
                            $tabela .= '<td class="success"> Livre </td>';
                        }
                        $tabela .= '</tr>';
                    }

                    $tabela .= '</table>';
                    $tabela .= '</div>';
                    $tabela .= '</div>';

                    if ($num_choques > 0) {
                        $tabela .= '<div class="alert alert-danger"> O professor ' . $_POST['professor_novo'] . ' possui ' . $num_choques . ' disciplina(s) no mesmo horário! Ajuste o horário antes de realizar esta operação!</div>';
                        $tabela .= '<button type="button" class="btn btn-danger" data-dismiss="modal">Fechar</button>';
                        $resultado = true;
                    } else {
                        if ($_POST['id_usuario_antigo'] == "") {
                            $tabela .= '<div class="alert alert-info"> Não existe horário associado a esta disciplina! Deseja realmente realizar essa operação? </div>';
                        } else {
                            $tabela .= '<div class="alert alert-info"> O professor ' . $_POST['professor_novo'] . ' não possui disciplinas no mesmo horário! Deseja realmente realizar essa alteração? </div>';
                        }
                        $tabela .= '<button type="button" style="margin:5px" class="btn btn-success" onclick="atualizar()">Sim</button>';
                        $tabela .= '<button type="button" style="margin:5px" class="btn btn-danger" data-dismiss="modal">Não</button>';
                        $resultado = false;
                    }
                }
            }
        }

        $resposta = array('resultado' => $resultado, 'tabela' => $tabela, 'msg' => $msg);
        return json_encode($resposta);
    }

    public function formularioValido() {

        $valido = true;

        if (!isset($_POST['id_usuario'])) {
            $_POST['id_usuario'] = 'NULL';
        }

        if (trim($_POST['id_disciplina']) == '') {
            $this->msg = 'O preenchimento do campo disciplina é obrigatório!';
            $valido = false;
        } else if (trim($_POST['id_turma']) == '') {
            $this->msg = 'O preenchimento do campo turma é obrigatório!';
            $valido = false;
        } else if ($this->oferta_disciplinaM->existeOfertaDisciplina($_POST['id_disciplina'], $_POST['id_turma'], $_POST['id_usuario'])) {
            $this->msg = 'Oferta de disciplina já cadastrada!';
            $valido = false;
        }

        if (!$valido) {
            $this->msg = '<div class="alert alert-danger">' . $this->msg . '</div>';
        }
        return $valido;
    }

    public function inserir() {
        $resultado = false;
        $id_oferta_disciplina = 0;
        if ($this->formularioValido()) {
            $res = $this->oferta_disciplinaM->inserir($_POST);
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
        $resposta = array('resultado' => $resultado, 'msg' => $this->msg, 'id_oferta_disciplina' => $id_oferta_disciplina);
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

    public function atualizar2() {
        $resultado = false;

        $horarioM = new horarioModel();
        $msg = '';
        $res_horario = $horarioM->excluirOfertaDisciplinaProfessor($_POST['id_oferta_disciplina'], $_POST['id_usuario_antigo']);
        if ($res_horario) {
            $msg = 'Os horários associados a esta disciplina foram apagados!<br>';
        }

        $res = $this->oferta_disciplinaM->atualizar($_POST);
        if ($res) {
            $this->msg .= '<div class="alert alert-success">';
            $this->msg .= $msg;
            $this->msg .= 'Oferta de disicplina atualizada com sucesso!';
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
        if (!$this->oferta_disciplinaM->existeVinculo($_POST['id_oferta_disciplina'])) {
            $res = $this->oferta_disciplinaM->deletar($_POST['id_oferta_disciplina']);
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
            $this->msg .= 'Não é possível deletar esta oferta_disciplina! Já existe registros associados a esta oferta de disciplina!';
            $this->msg .= '</div>';
        }
        $resposta = array('resultado' => $resultado, 'msg' => $this->msg);
        return json_encode($resposta);
    }

    public function getOfertaDisciplina() {
        $res = $this->oferta_disciplinaM->getOfertaDisciplina($_POST['id_oferta_disciplina']);
        $linha = mysqli_fetch_assoc($res);
        return json_encode($linha);
    }

    public function setCH() {
        $this->disciplinaM = new disciplinaModel();
        $result = $this->disciplinaM->getDisciplina($_POST['id_disciplina']);
        $linha = mysqli_fetch_assoc($result);
        return json_encode($linha);
    }

    public function atualizar_chs() {
        $resultado = false;
        $res = $this->oferta_disciplinaM->atualizar_chs($_POST);
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

    public function atualizar_chs_ead() {
        $resultado = false;
        $res = $this->oferta_disciplinaM->atualizar_chs_ead($_POST);
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

    public function atualizar_tipo() {
        $resultado = false;
        $res = $this->oferta_disciplinaM->atualizar_tipo($_POST);
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
    
    public function carregarDisciplina() {

        $select = '<label for="id_disciplina">Disciplina:</label>';
        $select .= '<select id="id_disciplina" name="id_disciplina" class="form-control" onChange="setCH()">';
        $select .= "<option value=''></option>";

        $this->disciplinaM = new disciplinaModel();
        $ordem = array('descricao' => 'ASC');

        $result = $this->disciplinaM->listar(array(), $ordem);
        while ($linha = mysqli_fetch_assoc($result)) {
            $select .= "<option value='{$linha['id_disciplina']}'>";
            $select .= "{$linha['descricao']} - CHS: {$linha['chs']}, CHT: {$linha['cht']}";
            $select .= '</option>';
        }
        $select .= '</select>';
        $resposta = array('select' => $select);

        return json_encode($resposta);
    }

    public function carregarTurma() {
        
        $periodo = explode("/",$_POST['periodo']);
        $semestre = $periodo[1]; 
        
        $select = '<label for="id_turma">Turma:</label>';
        $select .= '<select id="id_turma" name="id_turma" class="form-control">';
        $select .= "<option value=''></option>";

        $this->turmaM = new turmaModel();
        $ordem = array('descricao' => 'ASC');

        $result = $this->oferta_disciplinaM->getTurmasAtivas($_POST['id_periodo'],$semestre);
        while ($linha = mysqli_fetch_assoc($result)) {
            $select .= "<option value='{$linha['id_turma']}'>";
            $select .= $linha['turma'];
            $select .= '</option>';
        }
        $select .= '</select>';
        $resposta = array('select' => $select);

        return json_encode($resposta);
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
    
    public function carregarTipo() {
        $select = '<label for="tipo">Tipo:</label>';
        $select .= '<select id="tipo" name="tipo" class="form-control">';
        
        $result = $this->oferta_disciplinaM->getTipo();
        $linha = mysqli_fetch_assoc($result);
        $enum = str_replace('enum(', '', $linha['Type']);
        $enum = str_replace(')', '', $enum);
        $enum = str_replace("'", "", $enum);
        $nivel = explode(",", $enum);
        
        foreach ($nivel as $valor) {
            $select .= "<option value='{$valor}'>"; 
            $select .= $valor;
            $select .= '</option>';
        }
        $select .= '</select>';        
        $resposta = array('select'=>$select);
        
        return json_encode($resposta);          
    }

    public function getTurmasAtivas() {

        $periodo = explode("/",$_POST['periodo']);
        $semestre = $periodo[1];        
        
        $options = '';
        $result = $this->oferta_disciplinaM->getTurmasAtivas($_POST['id_periodo'],$semestre);
        $options .= '<option selected="selected" value="0">Todas</option>';
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

    public function carregarNucleo() {
        $options = '';
        $cursoM = new cursoModel();
        $result = $cursoM->getNucleos();
        $options .= '<option selected="selected" value="0">Todos</option>';
        if ($result) {
            while ($linha = $result->fetch_assoc()) {
                $options .= '<option value="' . $linha['nucleo'] . '">';
                $options .= $linha['nucleo'];
                $options .= '</option>';
            }
        }

        $resposta = array('options' => $options);
        return json_encode($resposta);
    }
}

// Callback
if (isset($_POST['metodo'])) {
    $metodo = $_POST['metodo'];
    $objeto = new oferta_disciplinaController();
    echo $objeto->$metodo();
}