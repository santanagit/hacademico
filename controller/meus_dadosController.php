<?php

session_start();
require_once $_SESSION['diretorio_base'] . '/model/usuarioModel.php';

class meus_dadosController {

    private $usuarioM;
    private $msg;

    public function __construct() {
        $this->usuarioM = new usuarioModel();
    }

    public function formularioValido() {

        $valido = true;

        if (trim($_POST['nome']) == '') {
            $this->msg = 'O preenchimento do campo nome é obrigatório!';
            $valido = false;
        } else if (trim($_POST['email']) == '') {
            $this->msg = 'O preenchimento do campo email é obrigatório!';
            $valido = false;
        } else if (trim($_POST['matricula']) == '') {
            $this->msg = 'O preenchimento do campo matricula é obrigatório!';
            $valido = false;
        } else if (trim($_POST['senha']) == '') {
            $this->msg = 'O preenchimento do campo senha é obrigatório!';
            $valido = false;
        } else if (trim($_POST['senha']) != trim($_POST['confirmar_senha'])) {
            $this->msg = 'A senha e confirmação de senha estão diferentes!';
            $valido = false;
        }

        if (!$valido) {
            $this->msg = '<div class="alert alert-danger">' . $this->msg . '</div>';
        }
        return $valido;
    }

    public function atualizar() {
        $resultado = false;
        if ($this->formularioValido()) {
            $res = $this->usuarioM->atualizarDados($_POST);
            if ($res) {
                $this->msg .= '<div class="alert alert-success">';
                $this->msg .= 'Dados atualizados com sucesso!';
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

    public function carregarDados() {
        
        $this->usuarioM = new usuarioModel();
        
        $result = $this->usuarioM->getUsuarioId($_SESSION['id_usuario']);
        $linha = mysqli_fetch_assoc($result);
        return json_encode($linha);
    }

}

// Callback
if (isset($_POST['metodo'])) {
    $metodo = $_POST['metodo'];
    $objeto = new meus_dadosController();
    echo $objeto->$metodo();
}