<?php

session_start();
require_once $_SESSION['diretorio_base'] . '/model/usuarioModel.php';
require_once $_SESSION['diretorio_base'] . '/model/perfilModel.php';

class usuarioController {

    private $usuarioM;
    private $perfilM;
    private $msg;

    public function __construct() {
        $this->usuarioM = new usuarioModel();
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
            $parametros = array('id_usuario' => $_POST['filtro'],
                'nome' => $_POST['filtro'],
                'matricula' => $_POST['filtro'],
                'email' => $_POST['filtro']
            );
        } else {
            $parametros = array();
        }

        $ordenacao = array('nome' => 'ASC');

        $inicio = ($pagina - 1) * $registros;
        $limit = array('inicio' => $inicio, 'quantidade' => $registros);

        $tabela = '';
        $id_perfil = 11;
        if (isset($_POST['id_perfil_busca'])) {
            if (trim($_POST['id_perfil_busca']) != '') {
                $id_perfil = $_POST['id_perfil_busca'];
            }
        }

        $result = $this->usuarioM->listar($id_perfil, $parametros, $ordenacao, $limit);
        $total_linhas = mysqli_num_rows($result);
        if ($total_linhas > 0) {

            $tabela .= '<table class="table table-striped table-hover table-condensed">';
            $tabela .= '<thead>';
            $tabela .= '<tr>';
            $tabela .= '<th width="5%">ID</th>';
            $tabela .= '<th width="23%">Nome</th>';
            $tabela .= '<th width="15%">Perfil</th>';
            $tabela .= '<th width="10%">Matricula</th>';
            $tabela .= '<th width="10%">CPF</th>';
            $tabela .= '<th width="21%">Email</th>';
            //$tabela .= '<th width="10%">Cor</th>';
            $tabela .= '<th width="2%">&nbsp;</th>';
            $tabela .= '<th width="2%">&nbsp;</th>';
            $tabela .= '<th width="2%">&nbsp;</th>';
            $tabela .= '</tr>';
            $tabela .= '</thead>';
            $tabela .= '<tbory>';
            while ($linha = mysqli_fetch_assoc($result)) {

                $tabela .= '<tr>';
                $tabela .= '<td>' . $linha['id_usuario'] . '</td>';
                $tabela .= '<td>' . $linha['nome'] . '</td>';
                $tabela .= '<td>' . $linha['perfil'] . '</td>';
                $tabela .= '<td>' . $linha['matricula'] . '</td>';
                $tabela .= '<td>' . $linha['cpf'] . '</td>';
                $tabela .= '<td>' . $linha['email'] . '</td>';
                //$tabela .= '<td>' . $linha['cor'] . '</td>';

                $tabela .= '<td>';
                if ($linha['ativo']) {
                    $tabela .= '<span class="glyphicon glyphicon-ok-circle" style="color:blue"></span>';
                } else {
                    $tabela .= '<span class="glyphicon glyphicon-ban-circle" style="color:red"></span>';
                }
                $tabela .= '</td>';                
                
                $tabela .= '<td>';
                $tabela .= '<a href="#void" onclick="abrirModal(' . "'modal_formulario','atualizar'," . $linha['id_usuario'] . ')" style="color:green">';
                $tabela .= '<span class="glyphicon glyphicon-edit"></span>';
                $tabela .= '</a>';
                $tabela .= '</td>';

                $tabela .= '<td>';
                $tabela .= '<a href="#void" onclick="abrirModal(' . "'modal_confirmacao','deletar'," . $linha['id_usuario'] . ')" style="color:red">';
                $tabela .= '<span class="glyphicon glyphicon-remove"></span>';
                $tabela .= '</a>';
                $tabela .= '</td>';

                $tabela .= '</tr>';
            }
            $tabela .= '</tbory>';
            $tabela .= '</table>';
        }

        $resultado = $this->usuarioM->listar($id_perfil,$parametros, $ordenacao);
        $total_registros = mysqli_num_rows($resultado);
        $total_paginas = ceil($total_registros / $registros);

        $resposta = array('tabela' => $tabela, 'total_paginas' => $total_paginas, 'pagina' => $pagina, 'registros' => $registros, 'filtro' => $_POST['filtro']);
        return json_encode($resposta);
    }

    public function formularioValido() {

        $valido = true;

        if (trim($_POST['nome']) == '') {
            $this->msg = 'O preenchimento do campo nome é obrigatório!';
            $valido = false;
        } else if (trim($_POST['email']) == '') {
            $this->msg = 'O preenchimento do campo email é obrigatório!';
            $valido = false;
        } else if (trim($_POST['id_perfil']) == '') {
            $this->msg = 'O preenchimento do campo perfil é obrigatório!';
            $valido = false;
        } else if (trim($_POST['matricula']) == '') {
            $this->msg = 'O preenchimento do campo matricula é obrigatório!';
            $valido = false;
        } else if ((trim($_POST['senha']) == '') && ($_POST['metodo'] != 'atualizar')) {
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

    public function inserir() {
        $resultado = false;
        $id_usuario = 0;

        if (!isset($_POST['ativo'])) {
            $_POST['ativo'] = 0;
        } else {
            $_POST['ativo'] = 1;
        }

        if ($this->formularioValido()) {

            $res = $this->usuarioM->inserir($_POST);
            if ($res) {
                $this->msg .= '<div class="alert alert-success">';
                $this->msg .= 'Usuário cadastrado com sucesso!';
                $this->msg .= '</div>';
                $id_usuario = $res;
                $resultado = true;
            } else {
                $this->msg .= '<div class="alert alert-danger">';
                $this->msg .= 'Erro ao inserir - Contactar o administrador do sistema';
                $this->msg .= '</div>';
            }
        }
        $resposta = array('resultado' => $resultado, 'msg' => $this->msg, 'id_usuario' => $id_usuario);
        return json_encode($resposta);
    }

    public function atualizar() {
        $resultado = false;

        if (!isset($_POST['ativo'])) {
            $_POST['ativo'] = 0;
        } else {
            $_POST['ativo'] = 1;
        }

        if ($this->formularioValido()) {
            $res = $this->usuarioM->atualizar($_POST);
            if ($res) {
                $this->msg .= '<div class="alert alert-success">';
                $this->msg .= 'Usuário atualizado com sucesso!';
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
        if (!$this->usuarioM->existeVinculo($_POST['id_usuario'])) {
            $res = $this->usuarioM->deletar($_POST['id_usuario']);
            if ($res) {
                $this->msg .= '<div class="alert alert-success">';
                $this->msg .= 'Usuário deletado com sucesso!';
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
            $this->msg .= 'Não é possível deletar este usuario! Este usuário já está associado a um usuário!';
            $this->msg .= '</div>';
        }
        $resposta = array('resultado' => $resultado, 'msg' => $this->msg);
        return json_encode($resposta);
    }

    public function getUsuario() {
        $res = $this->usuarioM->getUsuario($_POST['email']);
        $linha = mysqli_fetch_assoc($res);
        return json_encode($linha);
    }

    public function getUsuarioId() {
        $res = $this->usuarioM->getUsuarioId($_POST['id_usuario']);
        $linha = mysqli_fetch_assoc($res);
        return json_encode($linha);
    }

    public function carregarPerfil() {
        $select = '<label for="id_perfil">Perfil:</label>';
        $select .= '<select id="id_perfil" name="id_perfil" class="form-control">';
        $select .= "<option value=''></option>";
        $this->perfilM = new perfilModel();
        $result = $this->perfilM->listar();
        while ($linha = mysqli_fetch_assoc($result)) {
            $select .= "<option value='{$linha['id_perfil']}'>";
            $select .= $linha['descricao'];
            $select .= '</option>';
        }
        $select .= '</select>';
        $resposta = array('select' => $select);
        return json_encode($resposta);
    }

    public function carregarPerfilBusca() {
        $select = '<label for="id_perfil_busca">Perfil:</label>';
        $select .= '<select id="id_perfil_busca" name="id_perfil_busca" class="form-control">';
        $this->perfilM = new perfilModel();
        $result = $this->perfilM->listar();
        while ($linha = mysqli_fetch_assoc($result)) {
            if ($linha['id_perfil'] == 11) {
                $select .= "<option selected='selected' value='{$linha['id_perfil']}'>";
            } else {
                $select .= "<option value='{$linha['id_perfil']}'>";
            }
            $select .= $linha['descricao'];
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
    $objeto = new usuarioController();
    echo $objeto->$metodo();
}