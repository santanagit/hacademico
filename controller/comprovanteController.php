<?php

session_start();
require_once $_SESSION['diretorio_base'] . '/model/comprovanteModel.php';

class comprovanteController {

    private $comprovanteM;
    private $msg;

    public function __construct() {
        $this->comprovanteM = new comprovanteModel();
    }

    public function listar() {

        if (trim($_POST['registros']) == '') {
            $registros = 20;
        } else {
            $registros = $_POST['registros'];
        }

        if (trim($_POST['pagina']) == '') {
            $pagina = 1;
        } else {
            $pagina = $_POST['pagina'];
        }

        if (trim($_POST['filtro']) != '') {
            $parametros = array('comprovante.id_comprovante' => $_POST['filtro'],
                'inicio_vigencia' => $_POST['filtro'],
                'fim_vigencia' => $_POST['filtro'],
                'descricao' => $_POST['filtro']
            );
        } else {
            $parametros = array();
        }

        if (isset($_POST['criterios'])) {
            foreach ($_POST['criterios'] as $criterio) {
                if ($criterio == 'grupo') {
                    $criterios['grupo'] = 1;
                }
                if ($criterio == 'vigencia') {
                    $criterios['vigencia'] = 1;
                }
            }
            
        } else {
            $criterios = array();
        }

        
        $ordenacao = array('descricao' => 'DESC', 'id_comprovante' => 'DESC');

        $inicio = ($pagina - 1) * $registros;
        $limit = array('inicio' => $inicio, 'quantidade' => $registros);

        $tabela = '';
        $result = $this->comprovanteM->listar($criterios, $parametros, $ordenacao, $limit);
        $total_linhas = mysqli_num_rows($result);
        if ($total_linhas > 0) {

            $tabela .= '<table class="table table-striped table-hover table-condensed">';
            $tabela .= '<thead>';
            $tabela .= '<tr>';
            $tabela .= '<th width="4%">ID</th>';
            $tabela .= '<th width="70%">Descricação</th>';
            $tabela .= '<th width="10%">Início Vigência</th>';
            $tabela .= '<th width="10%">Fim Vigência</th>';
            $tabela .= '<th width="2%">&nbsp;</th>';
            $tabela .= '<th width="2%">&nbsp;</th>';
            $tabela .= '<th width="2%">&nbsp;</th>';
            $tabela .= '</tr>';
            $tabela .= '</thead>';
            $tabela .= '<tbory>';
            while ($linha = mysqli_fetch_assoc($result)) {

                $tabela .= '<tr>';
                $tabela .= '<td>' . $linha['id_comprovante'] . '</td>';
                $tabela .= '<td>' . $linha['descricao'] . '</td>';
                $tabela .= '<td>' . $linha['inicio_vigencia'] . '</td>';
                $tabela .= '<td>' . $linha['fim_vigencia'] . '</td>';

                $tabela .= '<td>';
                $tabela .= '<a href="comprovante_associar.php?id_comprovante='.$linha['id_comprovante'].'" style="color:green">';
                $tabela .= '<span class="glyphicon glyphicon-link"></span>';
                $tabela .= '</a>';
                $tabela .= '</td>';               
                
                $tabela .= '<td>';
                $tabela .= '<a href="download.php?id_comprovante='.$linha['id_comprovante'].'" target="_blank" style="color:blue">';
                $tabela .= '<span class="glyphicon glyphicon-download-alt"></span>';
                $tabela .= '</a>';
                $tabela .= '</td>';                
                
                $tabela .= '<td>';
                $tabela .= '<a href="#void" onclick="'."location.href='comprovante_dados.php?id_comprovante={$linha['id_comprovante']}'".'" style="color:green">';
                $tabela .= '<span class="glyphicon glyphicon-edit"></span>';
                $tabela .= '</a>';
                $tabela .= '</td>';

                $tabela .= '<td>';
                $tabela .= '<a href="#void" onclick="abrirModal(' . "'modal_confirmacao','deletar'," . $linha['id_comprovante'] . ')" style="color:red">';
                $tabela .= '<span class="glyphicon glyphicon-remove"></span>';
                $tabela .= '</a>';
                $tabela .= '</td>';

                $tabela .= '</tr>';
            }
            $tabela .= '</tbory>';
            $tabela .= '</table>';
        }

        $resultado = $this->comprovanteM->listar($parametros, $ordenacao);
        $total_registros = mysqli_num_rows($resultado);
        $total_paginas = ceil($total_registros / $registros);

        $resposta = array('tabela' => $tabela, 'total_paginas' => $total_paginas, 'pagina' => $pagina, 'registros' => $registros, 'filtro' => $_POST['filtro']);
        return json_encode($resposta);
    }

    public function formularioValido() {

        $valido = true;
        $path_parts = '';

        if (!isset($_FILES['file'])) {
            $this->msg = "<div class='alert alert-danger'>Selecione um arquivo!</div>";
            $valido = false;
        } else {
            $path_parts = pathinfo($_FILES['arquivo']['name']);
            if ($path_parts['extension'] != 'pdf') {
                $this->msg = "<div class='alert alert-danger'>O arquivo enviado deve estar no formato pdf!</div>";
                $valido = false;
            } else if (trim($_POST['metodo']) == 'atualizar') {
                if (trim($_POST['id_comprovante']) == '') {
                    $this->msg = 'Erro no sistema - Entre em contato com o desenvolvedor do sistema';
                    $valido = false;
                }
            }
        }

        if (!$valido) {
            $this->msg = '<div class="alert alert-danger">' . $this->msg . '</div>';
        }

        return $valido;
    }

    public function inserir() {
        $resultado = false;
        $id_comprovante = 0;
        if ($this->formularioValido()) {
            $res = $this->comprovanteM->inserir($_POST);
            if ($res) {
                $this->msg = '<div class="alert alert-success">';
                $this->msg .= 'Comprovante cadastrado com sucesso!';
                $this->msg .= '</div>';
                $id_comprovante = $res;
                $resultado = true;
            } else {
                $this->msg .= '<div class="alert alert-danger">';
                $this->msg .= 'Erro ao inserir - Contactar o administrador do sistema';
                $this->msg .= '</div>';
            }
        }
        $resposta = array(
            'resultado' => $resultado,
            'msg' => $this->msg,
            'id_comprovante' => $id_comprovante);
        return json_encode($resposta);
    }

    public function atualizar() {
        $resultado = false;
        if ($this->formularioValido()) {
            $res = $this->comprovanteM->atualizar($_POST);
            if ($res) {
                $this->msg = '<div class="alert alert-success">';
                $this->msg .= 'Comprovante atualizado com sucesso!';
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
        if (!$this->comprovanteM->existeVinculo($_POST['id_comprovante'])) {
            $res = $this->comprovanteM->deletar($_POST['id_comprovante']);
            if ($res) {
                $this->msg .= '<div class="alert alert-success">';
                $this->msg .= 'Comprovante deletado com sucesso!';
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
            $this->msg .= 'Não é possível deletar esta disciplina! Este comprovante já está associado a outro registro!';
            $this->msg .= '</div>';
        }
        $resposta = array('resultado' => $resultado, 'msg' => $this->msg);
        return json_encode($resposta);
    }

    public function getComprovante() {
        $res = $this->comprovanteM->getComprovante($_POST['id_comprovante']);
        $linha = mysqli_fetch_assoc($res);
        return json_encode($linha);
    }

}

// Callback
if (isset($_POST['metodo'])) {

    $metodo = $_POST['metodo'];
    $objeto = new comprovanteController();
    echo $objeto->$metodo();
}