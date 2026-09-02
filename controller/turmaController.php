<?php

session_start();
require_once $_SESSION['diretorio_base'] . '/model/turmaModel.php';
require_once $_SESSION['diretorio_base'] . '/model/periodoModel.php';
require_once $_SESSION['diretorio_base'] . '/model/gradeModel.php';
require_once $_SESSION['diretorio_base'] . '/model/oferta_disciplinaModel.php';
require_once $_SESSION['diretorio_base'] . '/model/cursoModel.php';

class turmaController {

    private $periodoM;
    private $turmaM;
    private $msg;

    public function __construct() {
        $this->turmaM = new turmaModel();
    }

    public function listar() {

        if (trim($_POST['registros']) == '') {
            $registros = 10;
        } else {
            $registros = $_POST['registros'];
        }

        if (trim($_POST['pagina']) == '') {
            $pagina = 1;
        } else {
            $pagina = $_POST['pagina'];
        }

        if (trim($_POST['filtro']) != '') {
            $parametros = array('descricao' => $_POST['filtro'],
                'ano' => $_POST['filtro'],
                'semestre' => $_POST['filtro'],
                'data_inicio' => $_POST['filtro'],
                'data_fim' => $_POST['filtro'],
                'vagas' => $_POST['filtro'],
                'turno' => $_POST['filtro']
            );
        } else {
            $parametros = array();
        }

        $ordenacao = array('ano' => 'DESC', 'semestre' => 'DESC', 'data_inicio' => 'DESC', 'data_fim' => 'DESC');

        $inicio = ($pagina - 1) * $registros;
        $limit = array('inicio' => $inicio, 'quantidade' => $registros);

        $tabela = '';
        $result = $this->turmaM->listar($parametros, $ordenacao, $limit);
        $total_linhas = mysqli_num_rows($result);

        $tabela .= '<table class="table table-striped table-hover table-condensed">';
        $tabela .= '<thead>';
        $tabela .= '<tr>';
        $tabela .= '<th width="5%">ID</th>';
        $tabela .= '<th width="6%">Período</th>';
        $tabela .= '<th width="14%">Curso Principal</th>';
        $tabela .= '<th width="10%">Data Inicio</th>';
        $tabela .= '<th width="10%">Data Fim</th>';
        $tabela .= '<th width="25%">Turma</th>';
        $tabela .= '<th width="10%">Turno</th>';
        $tabela .= '<th width="5%">Vagas</th>';
        $tabela .= '<th width="5%">&nbsp;</th>';
        $tabela .= '<th width="5%">&nbsp;</th>';
        $tabela .= '<th width="5%">&nbsp;</th>';
        $tabela .= '</tr>';
        $tabela .= '</thead>';
        $tabela .= '<tbory>';
        if ($total_linhas > 0) {

            while ($linha = mysqli_fetch_assoc($result)) {

                $tabela .= '<tr>';
                $tabela .= '<td>' . $linha['id_turma'] . '</td>';
                $tabela .= '<td>' . $linha['ano'] .'/' .$linha['semestre']. '</td>';
                $tabela .= '<td>' . $linha['curso'] . '</td>';
                $tabela .= '<td>' . $linha['data_inicio'] . '</td>';
                $tabela .= '<td>' . $linha['data_fim'] . '</td>';
                $tabela .= '<td>' . $linha['descricao'] . '</td>';
                $tabela .= '<td>' . $linha['turno'] . '</td>';
                $tabela .= '<td>' . $linha['vagas'] . '</td>';

                $tabela .= '<td>';
                $tabela .= '<a href="#void" onclick="abrirModal(' . "'modal_formulario','atualizar'," . $linha['id_turma'] . ')" style="color:green">';
                $tabela .= '<span class="glyphicon glyphicon-edit"></span>';
                $tabela .= '</a>';
                $tabela .= '</td>';

                $tabela .= '<td>';
                $tabela .= '<a href="#void" onclick="abrirModal(' . "'modal_confirmacao','deletar'," . $linha['id_turma'] . ')" style="color:red">';
                $tabela .= '<span class="glyphicon glyphicon-remove"></span>';
                $tabela .= '</a>';
                $tabela .= '</td>';
                
                $tabela .= '<td>';
                $tabela .= '<a href="#void" onclick="abrirModal(' . "'modal_importar','importar'," . $linha['id_turma'] . ')" style="color:blue">';
                $tabela .= '<span class="glyphicon glyphicon-import"></span>';
                $tabela .= '</a>';
                $tabela .= '</td>';                

                $tabela .= '</tr>';
            }
        }
        $tabela .= '</tbory>';
        $tabela .= '</table>';
        
        $resultado = $this->turmaM->listar($parametros, $ordenacao);
        $total_registros = mysqli_num_rows($resultado);
        $total_paginas = ceil($total_registros / $registros);

        $resposta = array('tabela' => $tabela, 'total_paginas' => $total_paginas, 'pagina' => $pagina, 'registros' => $registros, 'filtro' => $_POST['filtro']);
        return json_encode($resposta);
    }

    public function formularioValido() {

        $valido = true;

        if (trim($_POST['id_periodo']) == '') {
            $this->msg = 'O preenchimento do campo periodo é obrigatório!';
            $valido = false;
        } else if (trim($_POST['id_curso']) == '') {
            $this->msg = 'O preenchimento do campo curso é obrigatório!';
            $valido = false;            
        } else if (trim($_POST['descricao']) == '') {
            $this->msg = 'O preenchimento da descrição da turma é obrigatório!';
            $valido = false;
        } else if (trim($_POST['vagas']) == '') {
            $this->msg = 'O preenchimento da quantidade de vagas é obrigatório!';
            $valido = false;
        } else if (trim($_POST['turno']) == '') {
            $this->msg = 'O preenchimento do turno é obrigatório!';
            $valido = false;
        }

        if (!$valido) {
            $this->msg = '<div class="alert alert-danger">' . $this->msg . '</div>';
        }
        return $valido;
    }

    public function inserir() {
        $resultado = false;
        $id_turma = 0;
        if ($this->formularioValido()) {
            $res = $this->turmaM->inserir($_POST);
            if ($res) {
                $this->msg .= '<div class="alert alert-success">';
                $this->msg .= 'Turma cadastrada com sucesso!';
                $this->msg .= '</div>';
                $id_turma = $res;
                $resultado = true;
            } else {
                $this->msg .= '<div class="alert alert-danger">';
                $this->msg .= 'Erro ao inserir - Contactar o administrador do sistema';
                $this->msg .= '</div>';
                ;
            }
        }
        $resposta = array('resultado' => $resultado, 'msg' => $this->msg, 'id_turma' => $id_turma);
        return json_encode($resposta);
    }

    public function atualizar() {
        $resultado = false;
        if ($this->formularioValido()) {
            $res = $this->turmaM->atualizar($_POST);
            if ($res) {
                $this->msg .= '<div class="alert alert-success">';
                $this->msg .= 'Turma atualizada com sucesso!';
                $this->msg .= '</div>';
                $resultado = true;
            } else {
                $this->msg .= '<div class="alert alert-danger">';
                $this->msg .= 'Erro ao atualizar - Contactar o administrador do sistema';
                $this->msg .= '</div>';
            }
        }
        $resposta = array('resultado' => $resultado, 'msg' => $this->msg);
        return json_encode($resposta);
    }

    public function deletar() {
        $resultado = false;
        if (!$this->turmaM->existeVinculo($_POST['id_turma'])) {
            $res = $this->turmaM->deletar($_POST['id_turma']);
            if ($res) {
                $this->msg .= '<div class="alert alert-success">';
                $this->msg .= 'Turma deletada com sucesso!';
                $this->msg .= '</div>';
                $resultado = true;
            } else {
                $this->msg .= '<div class="alert alert-danger">';
                $this->msg .= 'Erro ao deletar - Contactar o administrador do sistema';
                $this->msg .= '</div>';
            }
        } else {
            $this->msg .= '<div class="alert alert-danger">';
            $this->msg .= 'Não é possível deletar este registro! Este usuário já está associado a um usuário!';
            $this->msg .= '</div>';
        }
        $resposta = array('resultado' => $resultado, 'msg' => $this->msg);
        return json_encode($resposta);
    }

    public function getTurma() {
        $res = $this->turmaM->getTurma($_POST['id_turma']);
        $linha = mysqli_fetch_assoc($res);
        return json_encode($linha);
    }

    public function carregarPeriodo() {

        $select = '<label for="id_periodo">Periodo Letivo:</label>';
        $select .= '<select id="id_periodo" name="id_periodo" class="form-control">';
        $select .= "<option value=''></option>";

        $this->periodoM = new periodoModel();
        $ordem = array('ano' => 'DESC', 'semestre' => 'DESC', 'data_inicio' => 'DESC', 'data_fim' => 'DESC');

        $result = $this->periodoM->listar(array(), $ordem);
        while ($linha = mysqli_fetch_assoc($result)) {
            $select .= "<option value='{$linha['id_periodo']}'>";
            $select .= $linha['ano'] . '/' . $linha['semestre'] . ' (' . $linha['data_inicio'] . ' - ' . $linha['data_fim'] . ')';
            $select .= '</option>';
        }
        $select .= '</select>';
        $resposta = array('select' => $select);

        return json_encode($resposta);
    }
    
    public function carregarCurso() {

        $select = '<label for="id_curso">Curso preferencial da turma</label>';
        $select .= '<select id="id_curso" name="id_curso" class="form-control">';
        $select .= "<option value=''></option>";

        $cursoM = new cursoModel();
        $result = $cursoM->listar(array(),array('nome'=>'ASC'),array());
        while ($linha = mysqli_fetch_assoc($result)) {
            $select .= "<option value='{$linha['id_curso']}'>";
            $select .= "{$linha['nome']} - {$linha['turno']}  - {$linha['matriz']}";
            $select .= '</option>';
        }
        $select .= '</select>';
        $resposta = array('select' => $select);

        return json_encode($resposta);
    }    
    
    public function carregarGrade() {

        $select = '<label for="id_grade">Grade curricular por módulo:</label>';
        $select .= '<select id="id_grade" name="id_grade" class="form-control">';
        $select .= "<option value=''></option>";

        $this->gradeM = new gradeModel();
        $result = $this->gradeM->getCursoModulo();
        while ($linha = mysqli_fetch_assoc($result)) {
            $select .= "<option value='{$linha['id_curso']}-{$linha['modulo']}'>";
            $select .= $linha['nome'].' - Regime: ' .$linha['regime'].' Módulo: '.$linha['modulo'].' Turno: '.$linha['turno'].' Matriz: '.$linha['matriz'];
            $select .= '</option>';
        }
        $select .= '</select>';
        $resposta = array('select' => $select);

        return json_encode($resposta);
    }
    
    public function carregarTurno(){
        $select = '<label for="turno">Turno:</label>';
        $select .= '<select id="turno" name="turno" class="form-control">';
        $select .= "<option value=''></option>";
        
        $result = $this->turmaM->getTurno();
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

    public function importar() {
        
        $parametros = explode('-', $_POST['id_grade']);
        $id_curso = $parametros[0];
        $modulo = $parametros[1];
        $this->gradeM = new gradeModel();
        $this->ofertaM = new oferta_disciplinaModel();
        $result = $this->gradeM->getDisciplinas($id_curso,$modulo);
        $msg = '<div class="alert alert-info">';
        $msg .= '<h4>Importando disciplinas para turma: '.$_POST['id_turma'].'</h4>';
        while ($linha = mysqli_fetch_assoc($result)) {
            if ($this->ofertaM->existeOfertaDisciplina($linha['id_disciplina'],$_POST['id_turma'],'NULL')) {
                $msg .= '<p style="color:red">'.$linha['descricao'].' já cadastrado!';
            } else {
                $campos['id_disciplina'] = $linha['id_disciplina'];
                $campos['chs'] = $linha['chs'];
                $campos['chs_ead'] = $linha['chs_ead'];
                $campos['cht'] = $linha['cht'];
                $campos['id_turma'] = $_POST['id_turma'];
                $campos['tipo'] = 'Aula';
                $campos['id_usuario'] = 'NULL';
                $this->ofertaM->inserir($campos);
                $msg .= '<p style="color:green">'.$linha['descricao'].' importado!';
            }
        }
        $msg .= '</div>';
        $resposta = array('msg' => $msg);
        return json_encode($resposta);        
    }

}

// Callback
if (isset($_POST['metodo'])) {
    $metodo = $_POST['metodo'];
    $objeto = new turmaController();
    echo $objeto->$metodo();
}