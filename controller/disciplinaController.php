<?php

session_start();
require_once $_SESSION['diretorio_base'] . '/model/disciplinaModel.php';
require_once $_SESSION['diretorio_base'] . '/model/gradeModel.php';

class disciplinaController {

    private $disciplinaM;
    private $gradeM;
    private $msg;

    public function __construct() {
        $this->disciplinaM = new disciplinaModel();
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
            $parametros = array('id_disciplina' => $_POST['filtro'],
                'descricao' => $_POST['filtro']
            );
        } else {
            $parametros = array();
        }

        $ordenacao = array('descricao' => 'ASC');

        $inicio = ($pagina - 1) * $registros;
        $limit = array('inicio' => $inicio, 'quantidade' => $registros);

        $tabela = '';
        $result = $this->disciplinaM->listar($parametros, $ordenacao, $limit);
        $total_linhas = mysqli_num_rows($result);
        if ($total_linhas > 0) {

            $tabela .= '<table class="table table-striped table-hover table-condensed">';
            $tabela .= '<thead>';
            $tabela .= '<tr>';
            $tabela .= '<th width="5%">ID</th>';
            $tabela .= '<th width="74%">Disciplina</th>';
            $tabela .= '<th width="5%">CHS</th>';
            $tabela .= '<th width="5%">CHT</th>';
            $tabela .= '<th width="5%">EAD</th>';
            $tabela .= '<th width="3%">&nbsp;</th>';
            $tabela .= '<th width="3%">&nbsp;</th>';
            $tabela .= '</tr>';
            $tabela .= '</thead>';
            $tabela .= '<tbory>';
            while ($linha = mysqli_fetch_assoc($result)) {

                $tabela .= '<tr>';
                $tabela .= '<td>' . $linha['id_disciplina'] . '</td>';
                $tabela .= '<td>' . $linha['descricao'] .' - CHS: '.$linha['chs'].' - CHT: '.$linha['cht']. '</td>';
                $tabela .= '<td>' . $linha['chs'] . '</td>';
                $tabela .= '<td>' . str_replace('.', ',', $linha['cht']) . '</td>';
                $tabela .= '<td>' . $linha['chs_ead'] . '</td>';
                
                $tabela .= '<td>';
                $tabela .= '<a href="#void" onclick="abrirModal(' . "'modal_formulario','atualizar'," . $linha['id_disciplina'] . ')" style="color:green">';
                $tabela .= '<span class="glyphicon glyphicon-edit"></span>';
                $tabela .= '</a>';
                $tabela .= '</td>';

                $tabela .= '<td>';
                $tabela .= '<a href="#void" onclick="abrirModal(' . "'modal_confirmacao','deletar'," . $linha['id_disciplina'] . ')" style="color:red">';
                $tabela .= '<span class="glyphicon glyphicon-remove"></span>';
                $tabela .= '</a>';
                $tabela .= '</td>';

                $tabela .= '</tr>';
            }
            $tabela .= '</tbory>';
            $tabela .= '</table>';
        }

        $resultado = $this->disciplinaM->listar($parametros, $ordenacao);
        $total_registros = mysqli_num_rows($resultado);
        $total_paginas = ceil($total_registros / $registros);

        $resposta = array('tabela' => $tabela, 'total_paginas' => $total_paginas, 'pagina' => $pagina, 'registros' => $registros, 'filtro' => $_POST['filtro']);
        return json_encode($resposta);
    }

    public function formularioValido() {

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
        } else if (trim($_POST['metodo']) == 'inserir') {
            if ($this->disciplinaM->existeDisciplina($_POST['descricao'], $_POST['id_disciplina'],$_POST['chs'],$_POST['cht'],$_POST['chs_ead'])) {
                $this->msg = 'Já existe esta disciplina cadastrada no sistema!';
                $valido = false;
            }
        } else if (trim($_POST['metodo']) == 'atualizar') {
            if (trim($_POST['id_disciplina']) == '') {
                $this->msg = 'Erro no sistema - Entre em contato com o desenvolvedor do sistema';
                $valido = false;
            } else if ($this->disciplinaM->existeDisciplina($_POST['descricao'], $_POST['id_disciplina'],$_POST['chs'],$_POST['cht'],$_POST['chs_ead'])) {
                $this->msg = 'Já existe esta disciplina cadastrada no sistema!';
                $valido = false;
            }
        } 
        
        if (!$valido) {
            $this->msg = '<div class="alert alert-danger">' . $this->msg . '</div>';
        }

        return $valido;
    }

    public function inserir() {
        $resultado = false;
        $id_disciplina = 0;
        if ($this->formularioValido()) {
            $_POST['chs'] = str_replace(",", ".",$_POST['chs']);
            $_POST['chs_ead'] = str_replace( ",", ".",$_POST['chs_ead']);
            $res = $this->disciplinaM->inserir($_POST);
            if ($res) {
                $this->msg = '<div class="alert alert-success">';
                $this->msg .= 'Disciplina cadastrada com sucesso!';
                $this->msg .= '</div>';
                $id_disciplina = $res;
                $resultado = true;
            } else {
                $this->msg .= '<div class="alert alert-danger">';
                $this->msg .= 'Erro ao inserir - Contactar o administrador do sistema';
                $this->msg .= '</div>';
            }
        }
        $resposta = array(
                        'resultado'=>$resultado,
                        'msg'=> $this->msg,
                        'id_disciplina'=>$id_disciplina);
        return json_encode($resposta);
    }

    public function atualizar() {
        $resultado = false;
        if ($this->formularioValido()) {
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
        if (!$this->disciplinaM->existeVinculo($_POST['id_disciplina'])) {
            $res = $this->disciplinaM->deletar($_POST['id_disciplina']);
            if ($res) {
                $this->msg .= '<div class="alert alert-success">';
                $this->msg .= 'Disciplina deletada com sucesso!';
                $this->msg .= '</div>';
                $resultado = true;
            } else {
                $this->msg .= '<div class="alert alert-danger">';
                $this->msg .= 'Erro ao deletar - Contactar o administrador do sistema';
                $this->msg .= '</div>';
                ;
            }
        } else {
            $this->msg .= '<div class="alert alert-danger">';
            $this->msg .= 'Não é possível deletar esta disciplina! Esta disciplina já está associado a uma disciplina!';
            $this->msg .= '</div>';
        }
        $resposta = array('resultado' => $resultado, 'msg' => $this->msg);
        return json_encode($resposta);
    }

    public function getDisciplina() {
        $tabela = '';
        
        $res_disciplina = $this->disciplinaM->getDisciplina($_POST['id_disciplina']);
        $linha_disciplina = mysqli_fetch_assoc($res_disciplina);

        $this->gradeM = new gradeModel();
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

}

// Callback
if (isset($_POST['metodo'])) {

    $metodo = $_POST['metodo'];
    $objeto = new disciplinaController();
    echo $objeto->$metodo();
}