<?php

session_start();
require_once $_SESSION['diretorio_base'].'/model/periodoModel.php';

class periodoController {
    
    private $periodoM;
    private $msg;
    
    public function __construct() {
        $this->periodoM = new periodoModel();
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
            $parametros = array('id_periodo'=>$_POST['filtro'],
                                'ano'=>$_POST['filtro'],
                                'semestre'=>$_POST['filtro'],
                                'data_inicio'=>$_POST['filtro'],
                                'data_fim'=>$_POST['filtro']
                                );
        } else {
            $parametros=array();
        }
        
        $ordenacao = array('ano'=>'DESC','semestre'=>'DESC');
        
        $inicio = ($pagina-1) * $registros;
        $limit = array('inicio'=>$inicio,'quantidade'=>$registros);
        
        $tabela = '';
        $result = $this->periodoM->listar($parametros,$ordenacao,$limit);
        $total_linhas = mysqli_num_rows($result);
        if ($total_linhas > 0) {
        
            $tabela .= '<table class="table table-striped table-hover table-condensed">';
            $tabela .= '<thead>';
            $tabela .= '<tr>';
            $tabela .= '<th width="2%">ID</th>';
            $tabela .= '<th width="10%">Ano</th>';
            $tabela .= '<th width="10%">Semestre</th>';
            $tabela .= '<th width="12%">Data início</th>';
            $tabela .= '<th width="12%">Data fim</th>';
            $tabela .= '<th width="11%">PID início</th>';
            $tabela .= '<th width="11%">PID fim</th>';
            $tabela .= '<th width="11%">RID início</th>';
            $tabela .= '<th width="11%">RID fim</th>';
            $tabela .= '<th width="6%" style="text-align:center">Publicado</th>';
            
            $tabela .= '<th width="2%">&nbsp;</th>';
            $tabela .= '<th width="2%">&nbsp;</th>';
            $tabela .= '</tr>';         
            $tabela .= '</thead>';
            $tabela .= '<tbory>';
            while ($linha = mysqli_fetch_assoc($result)) {
                               
                $tabela .= '<tr>';
                $tabela .= '<td>'.$linha['id_periodo'].'</td>';
                $tabela .= '<td>'.$linha['ano'].'</td>';
                $tabela .= '<td>'.$linha['semestre'].'</td>';
                $tabela .= '<td>'.$linha['data_inicio'].'</td>';
                $tabela .= '<td>'.$linha['data_fim'].'</td>';
                $tabela .= '<td>'.$linha['pid_inicio'].'</td>';
                $tabela .= '<td>'.$linha['pid_fim'].'</td>';
                $tabela .= '<td>'.$linha['rid_inicio'].'</td>';
                $tabela .= '<td>'.$linha['rid_fim'].'</td>';
                if ($linha['publicado']) {
                    $tabela .= '<td align="center">Sim</td>';
                } else {
                    $tabela .= '<td align="center">Não</td>';
                }
                
                
                
                $tabela .= '<td>';
                $tabela .= '<a href="#void" onclick="abrirModal('."'modal_formulario','atualizar',".$linha['id_periodo'].')" style="color:green">';
                $tabela .= '<span class="glyphicon glyphicon-edit"></span>';
                $tabela .= '</a>';
                $tabela .= '</td>';
                
                $tabela .= '<td>';
                $tabela .= '<a href="#void" onclick="abrirModal('."'modal_confirmacao','deletar',".$linha['id_periodo'].')" style="color:red">';
                $tabela .= '<span class="glyphicon glyphicon-remove"></span>';
                $tabela .= '</a>';
                $tabela .= '</td>';

                $tabela .= '</tr>';
            }
            $tabela .= '</tbory>';
            $tabela .= '</table>';
        }
        
        $resultado = $this->periodoM->listar($parametros,$ordenacao);
        $total_registros = mysqli_num_rows($resultado);
        $total_paginas = ceil($total_registros/$registros);
      
        $resposta = array('tabela'=>$tabela,'total_paginas'=>$total_paginas,'pagina'=>$pagina,'registros'=>$registros,'filtro'=>$_POST['filtro']);
        return json_encode($resposta);     
    }
    
    public function formularioValido(){
        
        $valido = true;
                
        if (trim($_POST['ano']) == '') {
            $this->msg = 'O preenchimento do campo ano é obrigatório!';
            $valido = false;
        } else if (trim($_POST['semestre']) == '') {
            $this->msg = 'O preenchimento do campo semestre é obrigatório!';
            $valido = false;
        } else if (trim($_POST['data_inicio']) == '') {
            $this->msg = 'O preenchimento do campo Data Início é obrigatório!';
            $valido = false;
        } else if (trim($_POST['data_fim']) == '') {
            $this->msg = 'O preenchimento do campo Data Fim é obrigatório!';
            $valido = false;
        }
        if (!$valido) {
            $this->msg = '<div class="alert alert-danger">'.$this->msg.'</div>';
        }        
        return $valido;
    }
    
    public function inserir() {
        $resultado = false;
        $id_periodo = 0;
        
        if (!isset($_POST['publicado'])) {
            $_POST['publicado'] = 0;
        } else {
            $_POST['publicado'] = 1;
        }           
        
        if ($this->formularioValido()) {
            
            $vetor = explode('/',$_POST['data_inicio']);
            $_POST['data_inicio'] = $vetor[2].'-'.$vetor[1].'-'.$vetor[0];
            
            $vetor = explode('/',$_POST['data_fim']);
            $_POST['data_fim'] = $vetor[2].'-'.$vetor[1].'-'.$vetor[0];           
            
            if (trim($_POST['pid_inicio']) != ''){
                $vetor = explode('/',$_POST['pid_inicio']);
                $_POST['pid_inicio'] = $vetor[2].'-'.$vetor[1].'-'.$vetor[0];
            } else {
                $_POST['pid_inicio'] = NULL;
            }
            
            if (trim($_POST['pid_fim']) != ''){
                $vetor = explode('/',$_POST['pid_fim']);
                $_POST['pid_fim'] = $vetor[2].'-'.$vetor[1].'-'.$vetor[0];   
            } else {
                $_POST['pid_fim'] = NULL;
            }

            if (trim($_POST['rid_inicio']) != ''){
                $vetor = explode('/',$_POST['rid_inicio']);
                $_POST['rid_inicio'] = $vetor[2].'-'.$vetor[1].'-'.$vetor[0];
            } else {
                $_POST['rid_inicio'] = NULL;
            }
            
            if (trim($_POST['rid_fim']) != ''){
                $vetor = explode('/',$_POST['rid_fim']);
                $_POST['rid_fim'] = $vetor[2].'-'.$vetor[1].'-'.$vetor[0];               
            } else {
                $_POST['rid_fim'] = NULL;
            }
            
            $res = $this->periodoM->inserir($_POST);
            if ($res) {
                $this->msg .= '<div class="alert alert-success">';
                $this->msg .= 'Período cadastrado com sucesso!';
                $this->msg .= '</div>';
                $id_periodo = $res;
                $resultado = true;
            } else {
                $this->msg .= '<div class="alert alert-danger">';
                $this->msg .= 'Erro ao inserir - Contactar o administrador do sistema';
                $this->msg .= '</div>';;
            }
        } 
        $resposta = array('resultado'=>$resultado,'msg'=>$this->msg,'id_periodo'=>$id_periodo);
        return json_encode($resposta);
    }
    
    public function atualizar() {
        $resultado = false;
        
        if (!isset($_POST['publicado'])) {
            $_POST['publicado'] = 0;
        } else {
            $_POST['publicado'] = 1;
        }        
        
        
        if ($this->formularioValido()) {
            
            $vetor = explode('/',$_POST['data_inicio']);
            $_POST['data_inicio'] = $vetor[2].'-'.$vetor[1].'-'.$vetor[0];
            
            $vetor = explode('/',$_POST['data_fim']);
            $_POST['data_fim'] = $vetor[2].'-'.$vetor[1].'-'.$vetor[0];
            
            if (trim($_POST['pid_inicio']) != ''){
                $vetor = explode('/',$_POST['pid_inicio']);
                $_POST['pid_inicio'] = $vetor[2].'-'.$vetor[1].'-'.$vetor[0];
            } else {
                $_POST['pid_inicio'] = NULL;
            }
            
            if (trim($_POST['pid_fim']) != ''){
                $vetor = explode('/',$_POST['pid_fim']);
                $_POST['pid_fim'] = $vetor[2].'-'.$vetor[1].'-'.$vetor[0];   
            } else {
                $_POST['pid_fim'] = NULL;
            }

            if (trim($_POST['rid_inicio']) != ''){
                $vetor = explode('/',$_POST['rid_inicio']);
                $_POST['rid_inicio'] = $vetor[2].'-'.$vetor[1].'-'.$vetor[0];
            } else {
                $_POST['rid_inicio'] = NULL;
            }
            
            if (trim($_POST['rid_fim']) != ''){
                $vetor = explode('/',$_POST['rid_fim']);
                $_POST['rid_fim'] = $vetor[2].'-'.$vetor[1].'-'.$vetor[0];               
            } else {
                $_POST['rid_fim'] = NULL;
            }
              
            
            $res = $this->periodoM->atualizar($_POST);
            if ($res) {
                $this->msg .= '<div class="alert alert-success">';
                $this->msg .= 'Período atualizado com sucesso!';
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
        if (!$this->periodoM->existeVinculo($_POST['id_periodo'])) {
            $res = $this->periodoM->deletar($_POST['id_periodo']);
            if ($res) {
                $this->msg .= '<div class="alert alert-success">';
                $this->msg .= 'Período deletado com sucesso!';
                $this->msg .= '</div>';
                $resultado = true;
            } else {
                $this->msg .= '<div class="alert alert-danger">';
                $this->msg .= 'Erro ao deletar - Contactar o administrador do sistema';
                $this->msg .= '</div>';;
            }
        } else {
            $this->msg .= '<div class="alert alert-danger">';
            $this->msg .= 'Não é possível deletar este periodo! Este periodo já está associado a outro registro!';
            $this->msg .= '</div>';            
        }
        $resposta = array('resultado'=>$resultado,'msg'=>$this->msg);
        return json_encode($resposta);        
    }
    
    public function getPeriodo(){
        $res = $this->periodoM->getPeriodo($_POST['id_periodo']);
        $linha = mysqli_fetch_assoc($res);
        return json_encode($linha);
    }   
}

// Callback
if (isset($_POST['metodo'])) {;
    $metodo = $_POST['metodo'];
    $objeto = new periodoController();
    echo $objeto->$metodo();
}