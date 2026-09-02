<?php

session_start();
require_once $_SESSION['diretorio_base'].'/model/cursoModel.php';
require_once $_SESSION['diretorio_base'].'/model/usuarioModel.php';

class cursoController {
    
    private $cursoM;
    private $usuarioM;
    private $msg;
    
    public function __construct() {
        $this->cursoM = new cursoModel();
    }
    
    public function listar() {
        
        $registros = '';
        $pagina = '';
        
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
            $parametros = array('id_curso'=>$_POST['filtro'],
                                'curso.nome'=>$_POST['filtro'],
                                'turno'=>$_POST['filtro'],
                                'nivel'=>$_POST['filtro'],
                                'regime'=>$_POST['filtro'],
                                'matriz'=>$_POST['filtro'],
                                'usuario.nome'=>$_POST['filtro']
                                );
        } else {
            $parametros=array();
        }
        
        $ordenacao = array('curso.nome'=>'ASC');
        
        $inicio = ($pagina-1) * $registros;
        $limit = array('inicio'=>$inicio,'quantidade'=>$registros);
        
        $tabela = '';
        $result = $this->cursoM->listar($parametros,$ordenacao,$limit);
        $total_linhas = mysqli_num_rows($result);
        if ($total_linhas > 0) {
        
            $tabela .= '<table class="table table-striped table-hover table-condensed">';
            $tabela .= '<thead>';
            $tabela .= '<tr>';
            $tabela .= '<th width="5%">ID</th>';
            $tabela .= '<th width="38%">Nome</th>';
            $tabela .= '<th width="7%">Turno</th>';
            $tabela .= '<th width="10%">Nível</th>';
            $tabela .= '<th width="7%">Regime</th>';
            $tabela .= '<th width="7%">Matriz</th>';
            $tabela .= '<th width="20%">Coordenador</th>';
            $tabela .= '<th width="2%">&nbsp;</th>';
            $tabela .= '<th width="2%">&nbsp;</th>';
            $tabela .= '<th width="2%">&nbsp;</th>';
            $tabela .= '</tr>';         
            $tabela .= '</thead>';
            $tabela .= '<tbory>';
            while ($linha = mysqli_fetch_assoc($result)) {
                               
                $tabela .= '<tr>';
                $tabela .= '<td>'.$linha['id_curso'].'</td>';
                $tabela .= '<td>'.$linha['nome'].'</td>';
                $tabela .= '<td>'.$linha['turno'].'</td>';
                $tabela .= '<td>'.$linha['nivel'].'</td>';
                $tabela .= '<td>'.$linha['regime'].'</td>';
                $tabela .= '<td>'.$linha['matriz'].'</td>';
                $tabela .= '<td>'.$linha['coordenador'].'</td>';
                
                $tabela .= '<td>';
                $tabela .= '<a href="#void" onclick="abrirModal('."'modal_formulario','atualizar',".$linha['id_curso'].')" style="color:green">';
                $tabela .= '<span class="glyphicon glyphicon-edit"></span>';
                $tabela .= '</a>';
                $tabela .= '</td>';
                
                $tabela .= '<td>';
                $tabela .= '<a href="#void" onclick="abrirModal('."'modal_confirmacao','deletar',".$linha['id_curso'].')" style="color:red">';
                $tabela .= '<span class="glyphicon glyphicon-remove"></span>';
                $tabela .= '</a>';
                $tabela .= '</td>';
                
                $tabela .= '<td>';
                $tabela .= '<a href="#void" onclick="location.href='."'grade.php?id_curso={$linha['id_curso']}'".'" style="color:blue">';
                $tabela .= '<span class="glyphicon glyphicon-list-alt"></span>';
                $tabela .= '</a>';
                $tabela .= '</td>';                
                
                $tabela .= '</tr>';
            }
            $tabela .= '</tbory>';
            $tabela .= '</table>';
        }
        
        $resultado = $this->cursoM->listar($parametros,$ordenacao);
        $total_registros = mysqli_num_rows($resultado);
        $total_paginas = ceil($total_registros/$registros);
      
        $resposta = array('tabela'=>$tabela,'total_paginas'=>$total_paginas,'pagina'=>$pagina,'registros'=>$registros,'filtro'=>$_POST['filtro']);
        return json_encode($resposta);     
    }
    
    public function formularioValido(){
        
        $valido = true;
                
        if (trim($_POST['nome']) == '') {
            $this->msg = 'O preenchimento do campo nome é obrigatório!';
            $valido = false;       
        } else if (trim($_POST['turno']) == '') {
            $this->msg = 'O preenchimento do campo turno é obrigatório!';
            $valido = false;
        } else if (trim($_POST['nivel']) == '') {
            $this->msg = 'O preenchimento do campo nível é obrigatório!';
            $valido = false;
        } else if (trim($_POST['regime']) == '') {
            $this->msg = 'O preenchimento do campo regime é obrigatório!';
            $valido = false;            
        } else if (trim($_POST['matriz']) == '') {
            $this->msg = 'O preenchimento do campo matriz é obrigatório!';
            $valido = false;
        } else if (trim($_POST['id_coordenador']) == '') {
            $this->msg = 'O preenchimento do campo coordenador é obrigatório!';
            $valido = false;
        }             
        
        if (!$valido) {
            $this->msg = '<div class="alert alert-danger">'.$this->msg.'</div>';
        }        
        return $valido;
    }
    
    public function inserir() {
        $resultado = false;
        $id_curso = 0;
        if ($this->formularioValido()) {         
            $res = $this->cursoM->inserir($_POST);
            if ($res) {
                $this->msg .= '<div class="alert alert-success">';
                $this->msg .= 'Curso cadastrado com sucesso!';
                $this->msg .= '</div>';
                $id_curso = $res;
                $resultado = true;
            } else {
                $this->msg .= '<div class="alert alert-danger">';
                $this->msg .= 'Erro ao inserir - Contactar o administrador do sistema';
                $this->msg .= '</div>';;
            }
        } 
        $resposta = array('resultado'=>$resultado,'msg'=>$this->msg,'id_curso'=>$id_curso);
        return json_encode($resposta);
    }
    
    public function atualizar() {
        $resultado = false;
        if ($this->formularioValido()) {          
            $res = $this->cursoM->atualizar($_POST);
            if ($res) {
                $this->msg .= '<div class="alert alert-success">';
                $this->msg .= 'Curso atualizado com sucesso!';
                $this->msg .= '</div>';
                $resultado = true;
            } else {
                $this->msg .= '<div class="alert alert-danger">';
                $this->msg .= 'Erro ao atualizar - Contactar o administrador do sistema';
                $this->msg .= '</div>';
            }
        } 
        $resposta = array('resultado'=>$resultado,'msg'=>$this->msg);
        return json_encode($resposta);      
    }

    public function deletar() {
        $resultado = false;
        if (!$this->cursoM->existeVinculo($_POST['id_curso'])) {
            $res = $this->cursoM->deletar($_POST['id_curso']);
            if ($res) {
                $this->msg .= '<div class="alert alert-success">';
                $this->msg .= 'Curso deletado com sucesso!';
                $this->msg .= '</div>';
                $resultado = true;
            } else {
                $this->msg .= '<div class="alert alert-danger">';
                $this->msg .= 'Erro ao deletar - Contactar o administrador do sistema';
                $this->msg .= '</div>';;
            }
        } else {
            $this->msg .= '<div class="alert alert-danger">';
            $this->msg .= 'Não é possível deletar este curso! Este usuário já está associado a um usuário!';
            $this->msg .= '</div>';            
        }
        $resposta = array('resultado'=>$resultado,'msg'=>$this->msg);
        return json_encode($resposta);        
    }
    
    public function getCurso(){
        $res = $this->cursoM->getCurso($_POST['id_curso']);
        $linha = mysqli_fetch_assoc($res);
        return json_encode($linha);
    }
    
    public function carregarProfessor() {
               
        $select = '<label for="id_coordenador">Coordenador:</label>';
        $select .= '<select id="id_coordenador" name="id_coordenador" class="form-control">';
        $select .= "<option value=''></option>";
        
        $this->usuarioM = new usuarioModel();
        $ordem = array('nome'=>'ASC');
        
        $result = $this->usuarioM->listar(11,array(),$ordem);
        while ($linha = mysqli_fetch_assoc($result)) {
            $select .= "<option value='{$linha['id_usuario']}'>"; 
            $select .= $linha['nome'];
            $select .= '</option>';
        }
        $select .= '</select>';        
        $resposta = array('select'=>$select);
        
        return json_encode($resposta);                   
    }
    
    public function carregarNivel(){
        $select = '<label for="nivel">Nível:</label>';
        $select .= '<select id="nivel" name="nivel" class="form-control">';
        $select .= "<option value=''></option>";
        
        $result = $this->cursoM->getNivel();
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
    
    public function carregarTurno(){
        $select = '<label for="turno">Turno:</label>';
        $select .= '<select id="turno" name="turno" class="form-control">';
        $select .= "<option value=''></option>";
        
        $result = $this->cursoM->getTurno();
        $linha = mysqli_fetch_assoc($result);
        $enum = str_replace('enum(', '', $linha['Type']);
        $enum = str_replace(')', '', $enum);
        $enum = str_replace("'", "", $enum);
        $turno = explode(",",$enum);
        
        foreach ($turno as $valor) {
            $select .= "<option value='{$valor}'>"; 
            $select .= $valor;
            $select .= '</option>';
        }
        $select .= '</select>';        
        $resposta = array('select'=>$select);
        
        return json_encode($resposta);          
    }
    
    public function carregarRegime(){
        $select = '<label for="regime">Regime de oferta:</label>';
        $select .= '<select id="regime" name="regime" class="form-control">';
        $select .= "<option value=''></option>";
        
        $result = $this->cursoM->getRegime();
        $linha = mysqli_fetch_assoc($result);
        $enum = str_replace('enum(', '', $linha['Type']);
        $enum = str_replace(')', '', $enum);
        $enum = str_replace("'", "", $enum);
        $regime = explode(",",$enum);
        
        foreach ($regime as $valor) {
            $select .= "<option value='{$valor}'>"; 
            $select .= $valor;
            $select .= '</option>';
        }
        $select .= '</select>';        
        $resposta = array('select'=>$select);
        
        return json_encode($resposta);          
    }     
}

// Callback
if (isset($_POST['metodo'])) {;
    $metodo = $_POST['metodo'];
    $objeto = new cursoController();
    echo $objeto->$metodo();
}