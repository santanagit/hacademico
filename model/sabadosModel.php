<?php

require_once $_SESSION['diretorio_base'].'/model/conexaoModel.php';

class cursoModel {

    private $bd;
    
    public function __construct() {
        $conexao = new conexaoModel();
        $this->bd = $conexao->getConexao();
    }
    
    public function getCurso($id_curso) {
        $sql = "SELECT  
                    curso.*,
                    usuario.nome as coordenador
                FROM 
                    curso INNER JOIN `usuario`
                        ON curso.`id_coordenador` = usuario.`id_usuario`
                WHERE 
                    curso.id_curso = $id_curso";
        
        $stmt = $this->bd->prepare($sql);
        $stmt->execute() or die($this->bd->error); 
        $result = $stmt->get_result();
        return $result;        
    }
    
    public function getNucleos() {
        $sql = "SELECT  
                    DISTINCT(nucleo) as nucleo
                FROM 
                    curso
                ORDER BY
                    nucleo";
        
        $stmt = $this->bd->prepare($sql);
        $stmt->execute() or die($this->bd->error);
        $result = $stmt->get_result();
        return $result;
    }
                 
    public function listar($parametros=array(),$ordenacao=array(),$limit=array()) {
        
        $sql = "SELECT  
                    curso.*,
                    usuario.nome as coordenador
                FROM 
                    curso INNER JOIN `usuario`
                        ON curso.`id_coordenador` = usuario.`id_usuario` ";
        
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
            $sql .= ' ORDER BY ';
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
        $sql = "INSERT INTO "
            . "curso(nome,turno,nivel,regime,matriz,id_coordenador) "
            . "VALUES ('{$campos['nome']}','{$campos['turno']}','{$campos['nivel']}','{$campos['regime']}','{$campos['matriz']}',{$campos['id_coordenador']})";

        $stmt = $this->bd->prepare($sql);
        $result = $stmt->execute() or die($this->bd->error);
        if (!$result) {
            return false;
        } else {
            return mysqli_stmt_insert_id($stmt); 
        }     
    }
    
    public function atualizar($campos) {
         $sql = "UPDATE curso "
            . "SET "
            . "nome = '{$campos['nome']}', "
            . "turno = '{$campos['turno']}', "
            . "nivel = '{$campos['nivel']}', "
            . "regime = '{$campos['regime']}', "
            . "matriz = '{$campos['matriz']}', "
            . "id_coordenador = {$campos['id_coordenador']} "            
            . "WHERE id_curso = '{$campos['id_curso']}'";
        $stmt = $this->bd->prepare($sql);
        $result = $stmt->execute() or die($this->bd->error);
        return $result; 
    }

    public function deletar($id_curso) {
        $sql = "DELETE FROM curso WHERE id_curso = $id_curso";
        $stmt = $this->bd->prepare($sql);
        $result = $stmt->execute() or die($this->bd->error);
        return $result;         
    }
        
    public function existeVinculo($id_curso) {
        $sql = "SELECT id_turma FROM turma WHERE id_curso = $id_curso UNION ";
        $sql .= "SELECT id_grade FROM grade WHERE id_curso = $id_curso";
        $stmt = $this->bd->prepare($sql);
        $stmt->execute() or die($this->bd->error);
        $stmt->store_result();
        $stmt->num_rows;
        if ($stmt->num_rows > 0) {
            return true;
        } else {
            return false;
        } 
    }
    
    public function getNivel() {
        $sql = "SHOW COLUMNS FROM curso WHERE FIELD = 'nivel'";
        $stmt = $this->bd->prepare($sql);
        $stmt->execute() or die($this->bd->error); 
        $result = $stmt->get_result();
        return $result;
    }
    
    public function getTurno() {
        $sql = "SHOW COLUMNS FROM curso WHERE FIELD = 'turno'";
        $stmt = $this->bd->prepare($sql);
        $stmt->execute() or die($this->bd->error); 
        $result = $stmt->get_result();
        return $result;
    }
    
    public function getRegime() {
        $sql = "SHOW COLUMNS FROM curso WHERE FIELD = 'regime'";
        $stmt = $this->bd->prepare($sql);
        $stmt->execute() or die($this->bd->error); 
        $result = $stmt->get_result();
        return $result;
    }    
        
}
