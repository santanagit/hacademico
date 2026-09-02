<?php

session_start();
require_once $_SESSION['diretorio_base'] . '/model/gradeModel.php';
require_once $_SESSION['diretorio_base'] . '/model/disciplinaModel.php';

class gradeController {

    private $gradeM;
    private $disciplinaM;
    private $msg;

    public function __construct() {
        $this->gradeM = new gradeModel();
        $this->disciplinaM = new disciplinaModel();
    }

    public function listar() {

        if (trim($_POST['registros']) == '') {
            $registros = 100;
        } else {
            $registros = $_POST['registros'];
        }

        if (trim($_POST['pagina']) == '') {
            $pagina = 1;
        } else {
            $pagina = $_POST['pagina'];
        }

        if (trim($_POST['filtro']) != '') {
            $parametros = array('id_grade' => $_POST['filtro'],
                'modulo' => $_POST['filtro'],
                'disciplina.descricao' => $_POST['filtro'],
                'id_grade' => $_POST['filtro']
            );
        } else {
            $parametros = array();
        }

        $ordenacao = array('grade.modulo' => 'ASC', 'disciplina.descricao' => 'ASC');

        $inicio = ($pagina - 1) * $registros;
        $limit = array('inicio' => $inicio, 'quantidade' => $registros);

        $tabela = '';
        $curso = '';
        $result = $this->gradeM->listar($_POST['id_curso'], $parametros, $ordenacao, $limit);
        $total_linhas = mysqli_num_rows($result);
        $tabela .= '<h4 id="titulo_tabela" class="text-primary" style="margin-top:20px">Curso </h4>';
        $tabela .= '<table class="table table-striped table-hover table-condensed">';
        $tabela .= '<thead>';
        $tabela .= '<tr>';
        $tabela .= '<th width="5%">ID</th>';
        $tabela .= '<th width="5%">SIGAA</th>';
        $tabela .= '<th width="5%" align="center">Módulo</th>';
        $tabela .= '<th width="70%">Disciplina</th>';
        $tabela .= '<th width="3%">CHS</th>';
        $tabela .= '<th width="3%">EAD</th>';
        $tabela .= '<th width="3%">CHT</th>';        
        $tabela .= '<th width="3%">&nbsp;</th>';
        $tabela .= '<th width="3%">&nbsp;</th>';
        $tabela .= '</tr>';
        $tabela .= '</thead>';
        $tabela .= '<tbory>';
        if ($total_linhas > 0) {
            while ($linha = mysqli_fetch_assoc($result)) {
                
                $curso = $linha['curso']." (Regime {$linha['regime']})";

                $tabela .= '<tr>';
                $tabela .= '<td>' . $linha['id_grade'] . '</td>';
                $tabela .= '<td>' . $linha['cod_sigaa'] . '</td>';
                $tabela .= '<td align="center">' . $linha['modulo'] . '</td>';
                $tabela .= '<td>' . $linha['disciplina'] . '</td>';
                $tabela .= '<td>' . $linha['chs'] . '</td>';
                $tabela .= '<td>' . $linha['chs_ead'] . '</td>';
                $tabela .= '<td>' . $linha['cht'] . '</td>';

                $tabela .= '<td>';
                $tabela .= '<a href="#void" onclick="abrirModal(' . "'modal_formulario','atualizar'," . $linha['id_grade'] . ')" style="color:green">';
                $tabela .= '<span class="glyphicon glyphicon-edit"></span>';
                $tabela .= '</a>';
                $tabela .= '</td>';

                $tabela .= '<td>';
                $tabela .= '<a href="#void" onclick="abrirModal(' . "'modal_confirmacao','deletar'," . $linha['id_grade'] . ')" style="color:red">';
                $tabela .= '<span class="glyphicon glyphicon-remove"></span>';
                $tabela .= '</a>';
                $tabela .= '</td>';

                $tabela .= '</tr>';
            }
        }
        $tabela .= '</tbory>';
        $tabela .= '</table>';
        $resultado = $this->gradeM->listar($_POST['id_curso'], $parametros, $ordenacao);
        $total_registros = mysqli_num_rows($resultado);
        $total_paginas = ceil($total_registros / $registros);

        $resposta = array('curso' => $curso . '', 'tabela' => $tabela, 'total_paginas' => $total_paginas, 'pagina' => $pagina, 'registros' => $registros, 'filtro' => $_POST['filtro']);

        return json_encode($resposta);
    }

    public function formularioValido() {

        $valido = true;

        if (trim($_POST['modulo']) == '') {
            $this->msg = 'O preenchimento do campo módulo é obrigatório!';
            $valido = false;
        } else if (trim($_POST['id_disciplina']) == '') {
            $this->msg = 'O preenchimento do campo id_disciplina é obrigatório!';
            $valido = false;
        } else if ($this->gradeM->existeGrade($_POST)) {
            $this->msg = 'Disciplina já cadastrada para este módulo!';
            $valido = false;
        }

        if (!$valido) {
            $this->msg = '<div class="alert alert-danger">' . $this->msg . '</div>';
        }
        return $valido;
    }
    
    public function formularioDisciplinaValido() {

        $valido = true;
        $_POST['cht'] = str_replace(',', '.', $_POST['cht']);
        if (trim($_POST['descricao']) == '') {
            $this->msg = 'O preenchimento do campo descrição é obrigatório!';
            $valido = false;
        } else if (trim($_POST['chs']) == '') {
            $this->msg = 'O preenchimento do campo CHS é obrigatório!';
            $valido = false;
        } else if (trim($_POST['cht']) == '') {
            $this->msg = 'O preenchimento do campo CHT é obrigatório!';
            $valido = false;
        } 
        
        if (!$valido) {
            $this->msg = '<div class="alert alert-danger">' . $this->msg . '</div>';
        }

        return $valido;
    }    

    public function inserir() {
        $resultado = false;
        $id_grade = 0;
        if ($this->formularioValido()) {
            $res = $this->gradeM->inserir($_POST);
            if ($res) {
                $this->msg .= '<div class="alert alert-success">';
                $this->msg .= 'Registro cadastrado com sucesso!';
                $this->msg .= '</div>';
                $id_grade = $res;
                $resultado = true;
            } else {
                $this->msg .= '<div class="alert alert-danger">';
                $this->msg .= 'Erro ao inserir - Contactar o administrador do sistema';
                $this->msg .= '</div>';
                ;
            }
        }
        $resposta = array('resultado' => $resultado, 'msg' => $this->msg, 'id_grade' => $id_grade);
        return json_encode($resposta);
    }

    public function atualizar() {
        $resultado = false;
        if ($this->formularioValido()) {
            $res = $this->gradeM->atualizar($_POST);
            if ($res) {
                $this->msg .= '<div class="alert alert-success">';
                $this->msg .= 'Registro deletado com sucesso!';
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
    
    public function atualizar_disciplina() {
        $resultado = false;
        if ($this->formularioDisciplinaValido()) {
            $_POST['chs'] = str_replace(",", ".",$_POST['chs']);
            $_POST['chs_ead'] = str_replace( ",", ".",$_POST['chs_ead']);
            
            $res = $this->disciplinaM->atualizar($_POST);
            if ($res) {
                $this->msg = '<div class="alert alert-success">';
                $this->msg .= 'Disciplina atualizada com sucesso!';
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

        $res = $this->gradeM->deletar($_POST['id_grade']);
        if ($res) {
            $this->msg .= '<div class="alert alert-success">';
            $this->msg .= 'Grade deletado com sucesso!';
            $this->msg .= '</div>';
            $resultado = true;
        } else {
            $this->msg .= '<div class="alert alert-danger">';
            $this->msg .= 'Erro ao deletar - Contactar o administrador do sistema';
            $this->msg .= '</div>';
        }

        $resposta = array('resultado' => $resultado, 'msg' => $this->msg);
        return json_encode($resposta);
    }

    public function getDisciplina() {
        
        $tabela = '';
        
        $res_disciplina = $this->disciplinaM->getDisciplina($_POST['id_disciplina']);
        $linha_disciplina = mysqli_fetch_assoc($res_disciplina);

        $res_matriz_vinculada = $this->gradeM->getMatrizVinculada($_POST['id_disciplina']);
        if (mysqli_num_rows($res_matriz_vinculada) > 0) {
            $tabela = '<div class="form-group">';
            $tabela .= '<label for="matriz">Essa disciplina está sendo utilizada nas matrizes dos cursos:</label>';
            $tabela .= '<table class="table table-bordered">';
            $tabela .= "<tr><th>Curso</th><th>Matriz</th></tr>";
            while ($linha_matriz_vinculada = mysqli_fetch_assoc($res_matriz_vinculada)) {
                $tabela .= "<tr>";
                $tabela .= "<td>{$linha_matriz_vinculada['curso']}";
                $tabela .= "<td>{$linha_matriz_vinculada['matriz']}";
                $tabela .= "</tr>";
            }
            $tabela .= '</table>';
            $tabela .= '</div>';
        } 
        $linha_disciplina['matriz'] = $tabela;
        return json_encode($linha_disciplina);
    }
    
    public function getGrade() {
        $res = $this->gradeM->getGrade($_POST['id_grade']);
        $linha = mysqli_fetch_assoc($res);
        return json_encode($linha);
    }    
    

    public function carregarDisciplina() {

        $select = '<label for="id_disciplina">Disciplina:</label>';
        $select .= '<select id="id_disciplina" name="id_disciplina" class="form-control">';
        $select .= "<option value=''></option>";

        $this->disciplinaM = new disciplinaModel();

        $result = $this->disciplinaM->listar(array(),array('descricao'=>'ASC'));
        while ($linha = mysqli_fetch_assoc($result)) {
            $select .= "<option value='{$linha['id_disciplina']}'>";
            $select .= $linha['descricao'] .' - CHS: '.$linha['chs'].' - CHS_EAD: '.$linha['chs_ead'].' - CHT: '.$linha['cht'].' - ID: '.$linha['id_disciplina'];
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
    $objeto = new gradeController();
    echo $objeto->$metodo();
}