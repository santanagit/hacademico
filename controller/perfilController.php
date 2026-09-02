<?php

session_start();
require_once $_SESSION['diretorio_base'].'/model/perfilModel.php';

class perfilController {
    
    private $perfilM;
    private $msg;
    
    public function __construct() {
        $this->perfilM = new perfilModel();
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
            $parametros = array('id_perfil'=>$_POST['filtro'],
                                'descricao'=>$_POST['filtro']
                                );
        } else {
            $parametros=array();
        }
        
        $ordenacao = array('descricao'=>'ASC');
        
        $inicio = ($pagina-1) * $registros;
        $limit = array('inicio'=>$inicio,'quantidade'=>$registros);
        
        $tabela = '';
        $result = $this->perfilM->listar($parametros,$ordenacao,$limit);
        $total_linhas = mysqli_num_rows($result);
        if ($total_linhas > 0) {
        
            $tabela .= '<table class="table table-striped table-hover table-condensed">';
            $tabela .= '<thead>';
            $tabela .= '<tr>';
            $tabela .= '<th width="5%">ID</th>';
            $tabela .= '<th width="85%">Perfil</th>';
            $tabela .= '<th width="5%">&nbsp;</th>';
            $tabela .= '<th width="5%">&nbsp;</th>';
            $tabela .= '</tr>';         
            $tabela .= '</thead>';
            $tabela .= '<tbory>';
            while ($linha = mysqli_fetch_assoc($result)) {
                               
                $tabela .= '<tr>';
                $tabela .= '<td>'.$linha['id_perfil'].'</td>';
                $tabela .= '<td>'.$linha['descricao'].'</td>';
                
                $tabela .= '<td>';
                $tabela .= '<a href="#void" onclick="abrirModal('."'modal_formulario','atualizar',".$linha['id_perfil'].')" style="color:green">';
                $tabela .= '<span class="glyphicon glyphicon-edit"></span>';
                $tabela .= '</a>';
                $tabela .= '</td>';
                
                $tabela .= '<td>';
                $tabela .= '<a href="#void" onclick="abrirModal('."'modal_confirmacao','deletar',".$linha['id_perfil'].')" style="color:red">';
                $tabela .= '<span class="glyphicon glyphicon-remove"></span>';
                $tabela .= '</a>';
                $tabela .= '</td>';

                $tabela .= '</tr>';
            }
            $tabela .= '</tbory>';
            $tabela .= '</table>';
        }
        
        $resultado = $this->perfilM->listar($parametros,$ordenacao);
        $total_registros = mysqli_num_rows($resultado);
        $total_paginas = ceil($total_registros/$registros);
      
        $resposta = array('tabela'=>$tabela,'total_paginas'=>$total_paginas,'pagina'=>$pagina,'registros'=>$registros,'filtro'=>$_POST['filtro']);
        return json_encode($resposta);     
    }
    
    public function formularioValido(){
        
        $valido = true;
                
        if (trim($_POST['descricao']) == '') {
            $this->msg = 'O preenchimento do campo descrição é obrigatório!';
            $valido = false;
        } else if (trim($_POST['metodo']) == 'inserir') {
            if ($this->perfilM->existePerfil($_POST['descricao'])) {
                $this->msg = 'Já existe este perfil no sistema!';
                $valido = false;
            }
        } else if (trim($_POST['metodo']) == 'atualizar') {
            if (trim($_POST['id_perfil']) == '') {
                $this->msg = 'Erro no sistema - Entre em contato com o desenvolvedor do sistema';
                $valido = false;                
            } else if ($this->perfilM->existePerfil($_POST['descricao'],$_POST['id_perfil'])) {
                $this->msg = 'Já existe este perfil no sistema!';
                $valido = false;
            }               
        } 
        
        if (!$valido) {
            $this->msg = '<div class="alert alert-danger">'.$this->msg.'</div>';
        }        
        return $valido;
    }
    
    public function inserir() {
        $resultado = false;
        $id_perfil = 0;
        if ($this->formularioValido()) {
            $res = $this->perfilM->inserir($_POST);
            if ($res) {
                $this->msg .= '<div class="alert alert-success">';
                $this->msg .= 'Perfil cadastrado com sucesso!';
                $this->msg .= '</div>';
                $id_perfil = $res;
                $resultado = true;
            } else {
                $this->msg .= '<div class="alert alert-danger">';
                $this->msg .= 'Erro ao inserir - Contactar o administrador do sistema';
                $this->msg .= '</div>';;
            }
        } 
        $resposta = array('resultado'=>$resultado,'msg'=>$this->msg,'id_perfil'=>$id_perfil);
        return json_encode($resposta);
    }
    
    public function atualizar() {
        $resultado = false;
        if ($this->formularioValido()) {
            $res = $this->perfilM->atualizar($_POST);
            if ($res) {
                $this->msg .= '<div class="alert alert-success">';
                $this->msg .= 'Perfil atualizado com sucesso!';
                $this->msg .= '</div>';
                $resultado = true;
            } else {
                $this->msg .= '<div class="alert alert-danger">';
                $this->msg .= 'Erro ao atualizar - Contactar o administrador do sistema';
                $this->msg .= '</div>';;
            }
        } 
        $resposta = array('resultado'=>$resultado,'msg'=>$this->msg);
        return json_encode($resposta);      
    }

    public function deletar() {
        $resultado = false;
        if (!$this->perfilM->existeUsuario($_POST['id_perfil'])) {
            $res = $this->perfilM->deletar($_POST['id_perfil']);
            if ($res) {
                $this->msg .= '<div class="alert alert-success">';
                $this->msg .= 'Perfil deletado com sucesso!';
                $this->msg .= '</div>';
                $resultado = true;
            } else {
                $this->msg .= '<div class="alert alert-danger">';
                $this->msg .= 'Erro ao deletar - Contactar o administrador do sistema';
                $this->msg .= '</div>';;
            }
        } else {
            $this->msg .= '<div class="alert alert-danger">';
            $this->msg .= 'Não é possível deletar este perfil! Este perfil já está associado a um usuário!';
            $this->msg .= '</div>';            
        }
        $resposta = array('resultado'=>$resultado,'msg'=>$this->msg);
        return json_encode($resposta);        
    }
    
    public function getPerfil(){
        $res = $this->perfilM->getPerfil($_POST['id_perfil']);
        $linha = mysqli_fetch_assoc($res);
        return json_encode($linha);
    }   
}

// Callback
if (isset($_POST['metodo'])) {;
    $metodo = $_POST['metodo'];
    $objeto = new perfilController();
    echo $objeto->$metodo();
}