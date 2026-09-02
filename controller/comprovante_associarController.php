<?php

session_start();
require_once $_SESSION['diretorio_base'] . '/model/comprovante_docenteModel.php';
require_once $_SESSION['diretorio_base'] . '/model/comprovanteModel.php';
require_once $_SESSION['diretorio_base'] . '/model/atividadeModel.php';
require_once $_SESSION['diretorio_base'] . '/model/usuarioModel.php';

class comprovante_associarController {

    private $comprovante_docenteM;
    private $comprovanteM;
    private $atividadeM;
    private $usuarioM;
    private $msg;

    public function __construct() {
        $this->comprovante_docenteM = new comprovante_docenteModel();
        $this->atividadeM = new atividadeModel();
        $this->usuarioM = new usuarioModel();
        $this->comprovanteM = new comprovanteModel();
    }

    public function listar() {

        $tabela = '';
        $result = $this->comprovante_docenteM->listar($_POST['id_comprovante'],array(),array('atividade'=>'ASC','professor'=>'ASC'));
        $total_linhas = mysqli_num_rows($result);
        if ($total_linhas > 0) {

            $tabela .= '<table class="table table-striped table-hover table-condensed">';
            $tabela .= '<thead>';
            $tabela .= '<tr>';
            $tabela .= '<th width="5%">ID</th>';
            $tabela .= '<th width="40%">Atividade</th>';
            $tabela .= '<th width="40%">Professor</th>';
            $tabela .= '<th width="10%">Horas</th>';
            $tabela .= '<th width="5%">&nbsp;</th>';
            $tabela .= '</tr>';
            $tabela .= '</thead>';
            $tabela .= '<tbory>';
            while ($linha = mysqli_fetch_assoc($result)) {

                $tabela .= '<tr>';
                $tabela .= '<td>' . $linha['id_comprovante_docente'] . '</td>';
                $tabela .= '<td>' . $linha['atividade'] . '</td>';
                $tabela .= '<td>' . $linha['professor'] . '</td>';
                $tabela .= '<td>' . $linha['horas'] . '</td>';

                $tabela .= '<td>';
                $tabela .= '<a href="#void" onclick="abrirModal(' . "'modal_confirmacao','deletar'," . $linha['id_comprovante_docente'] . ')" style="color:red">';
                $tabela .= '<span class="glyphicon glyphicon-remove"></span>';
                $tabela .= '</a>';
                $tabela .= '</td>';

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
        $_POST['horas'] = str_replace(',', '.', $_POST['horas']);
        if (trim($_POST['id_atividade']) == '') {
            $this->msg = 'O preenchimento do campo atividade é obrigatório!';
            $valido = false;
        } else if (trim($_POST['id_usuario']) == '') {
            $this->msg = 'O preenchimento do campo professor é obrigatório!';
            $valido = false;
        } else if (trim($_POST['horas']) == '') {
            $this->msg = 'O preenchimento do campo horas é obrigatório!';
            $valido = false;
        } else if (trim($_POST['metodo']) == 'inserir') {
            if ($this->comprovante_docenteM->existeAssociacao($_POST['id_atividade'], $_POST['id_usuario'])) {
                $this->msg = 'Já existe esta associação para este comprovante!';
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
        if ($this->formularioValido()) {
            $_POST['horas'] = str_replace(",", ".", $_POST['horas']);
            $res = $this->comprovante_docenteM->inserir($_POST);
            if ($res) {
                $this->msg = '<div class="alert alert-success">';
                $this->msg .= 'Associação realizada com sucesso!';
                $this->msg .= '</div>';
                $resultado = true;
            } else {
                $this->msg .= '<div class="alert alert-danger">';
                $this->msg .= 'Erro ao inserir - Contactar o administrador do sistema';
                $this->msg .= '</div>';
            }
        }
        $resposta = array(
            'resultado' => $resultado,
            'msg' => $this->msg
        );
        return json_encode($resposta);
    }

    public function deletar() {
        $resultado = false;

        $res = $this->comprovante_docenteM->deletar($_POST['id_comprovante_docente']);
        if ($res) {
            $this->msg .= '<div class="alert alert-success">';
            $this->msg .= 'Associação deletada com sucesso!';
            $this->msg .= '</div>';
            $resultado = true;
        } else {
            $this->msg .= '<div class="alert alert-danger">';
            $this->msg .= 'Erro ao deletar - Contactar o administrador do sistema';
            $this->msg .= '</div>';
            ;
        }
        $resposta = array('resultado' => $resultado, 'msg' => $this->msg);
        return json_encode($resposta);
    }
    
    public function carregarAtividade() {
        $select = '<label for="id_atividade">Atividade:</label>';
        $select .= '<select id="id_periodo" name="id_atividade" class="form-control" style="width:100%;">';
        $result_atividade = $this->atividadeM->listar(array(),array('descricao'=>'ASC'));

        $select .= "<option value=''>Selecione uma atividade</option>";
        while ($linha = mysqli_fetch_assoc($result_atividade)) {
            $select .= "<option value='{$linha['id_atividade']}'>";
            $select .= $linha['descricao'];
            $select .= '</option>';
        }
        $select .= '</select>';
        $resposta = array('select' => $select);
        return json_encode($resposta);
    }

    public function carregarComprovante() {
        $result_comprovante = $this->comprovanteM->getComprovante($_POST['id_comprovante']);
        $linha_comprovante = mysqli_fetch_assoc($result_comprovante);
        $select = "<div class='alert alert-info'>";
        $select .= "<div style='padding:5px;font-weight:bold'>Comprovante:</div>";
        $select .= "<ul>";
        $select .= "<li> ID: {$linha_comprovante['id_comprovante']} </li>";
        $select .= "<li> Descrição: {$linha_comprovante['descricao']} </li>";
        $select .= "<li> Vigência: {$linha_comprovante['inicio_vigencia']} à {$linha_comprovante['fim_vigencia']} </li>";
        $select .= "</ul>";
        $resposta = array('select' => $select);
        return json_encode($resposta);
    } 
    
    public function carregarProfessor() {

        $select = '<label for="id_usuario">Professor:</label>';
        $select .= '<select id="id_usuario" name="id_usuario" class="form-control">';

        $this->usuarioM = new usuarioModel();
        $ordem = array('nome' => 'ASC');
        $parametro = array();

        $result = $this->usuarioM->listar(11, $parametro, $ordem);
         $select .= "<option value=''>Selecione um professor</option>";
        while ($linha = mysqli_fetch_assoc($result)) {
            $select .= "<option value='{$linha['id_usuario']}'>";
            $select .= $linha['nome'];
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
    $objeto = new comprovante_associarController();
    echo $objeto->$metodo();
}