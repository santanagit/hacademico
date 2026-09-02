<?php
require_once $_SESSION['diretorio_base'].'/model/conexaoModel.php';

class perfilModel {
    
    private $bd;
    
    public function __construct() {
        $conexao = new conexaoModel();
        $this->bd = $conexao->getConexao();
    }
        
    public function listar($parametros=array(),$ordenacao=array(),$limit=array()) {
        
        $sql = "SELECT * FROM perfil ";
        
        if (count($parametros) > 0) {
            $i = 0;
            $sql .= ' WHERE ';
            foreach ($parametros as $key => $value) {               
                if ($i > 0) $sql .= " OR ";
                $sql .= "$key like '%$value%'";                
                $i++;
            }  
        }
        
        if (count($ordenacao) > 0) {
            $i = 0;
            $sql .= 'ORDER BY ';
            foreach ($ordenacao as $key => $value) {
                if ($i > 0) $sql .= ", ";
                $sql .= "$key $value";               
                $i++;
            }          
        }

        if (count($limit) > 0) {
            $sql .= " LIMIT {$limit['inicio']},{$limit['quantidade']}";      
        }
                
        $stmt = $this->bd->prepare($sql);
        $stmt->execute() or die($this->bd->error);
        $result = $stmt->get_result();
        return $result;        
    }
    
    public function inserir($campos) {
        $sql = "INSERT INTO perfil(descricao) VALUES (?)";
        $stmt = $this->bd->prepare($sql);
        $stmt->bind_param("s", $campos['descricao']);
        $result = $stmt->execute() or die($this->bd->error);
        if (!$result) {
            return false;
        } else {
            return mysqli_stmt_insert_id($stmt); 
        }     
    }
    
    public function atualizar($campos) {
        $sql = "UPDATE perfil SET descricao = ? WHERE id_perfil = ?";
        $stmt = $this->bd->prepare($sql);
        $stmt->bind_param("si", $campos['descricao'],$campos['id_perfil']);
        $result = $stmt->execute() or die($this->bd->error);
        return $result; 
    }

    public function deletar($id_perfil) {
        $sql = "DELETE FROM perfil WHERE id_perfil = ?";
        $stmt = $this->bd->prepare($sql);
        $stmt->bind_param("i",$id_perfil);
        $result = $stmt->execute() or die($this->bd->error);
        return $result;         
    }
    
    public function existePerfil($descricao,$id_perfil=0) {
        $sql = "SELECT descricao FROM perfil WHERE descricao = ? AND id_perfil <> ?";
        $stmt = $this->bd->prepare($sql);
        $stmt->bind_param("si", $descricao,$id_perfil);
        $stmt->execute() or die($this->bd->error);
        $stmt->store_result();
        $stmt->num_rows;
        if ($stmt->num_rows > 0) {
            return true;
        } else {
            return false;
        }              
    }
    
    public function getPerfil($id_perfil) {
        $sql = "SELECT * FROM perfil WHERE id_perfil = ?";
        $stmt = $this->bd->prepare($sql);
        $stmt->bind_param("i", $id_perfil);
        $stmt->execute() or die($this->bd->error);   
        $result = $stmt->get_result();
        return $result;
    }
    
    public function existeUsuario($id_perfil) {
        $sql = "SELECT * FROM usuario WHERE id_perfil = ?";
        $stmt = $this->bd->prepare($sql);
        $stmt->bind_param("i", $id_perfil);
        $stmt->execute() or die($this->bd->error);
        $stmt->store_result();
        $stmt->num_rows;
        if ($stmt->num_rows > 0) {
            return true;
        } else {
            return false;
        } 
    }    
}