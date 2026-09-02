<?php

require_once $_SESSION['diretorio_base'].'/model/conexaoModel.php';

class feriadoModel {

    private $bd;
    
    public function __construct() {
        $conexao = new conexaoModel();
        $this->bd = $conexao->getConexao();
    }
    
    public function listar($parametros=array(),$ordenacao=array(),$limit=array()) {
        
        $sql = "SELECT  
                    DATE_FORMAT(data_feriado,'%d/%m/%Y') as data_feriado,
                    feriado.id_feriado
                FROM 
                    feriado ";
        
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

    public function getFeriado($id_periodo) {
        $sql = "SELECT
                    DATE_FORMAT(data_feriado,'%d/%m/%Y') as data_feriado_formatado,
                    feriado.data_feriado,
                    feriado.id_feriado,
                    feriado.id_periodo
                FROM feriado WHERE id_periodo = $id_periodo";
        $stmt = $this->bd->prepare($sql);
        $stmt->execute() or die($this->bd->error);   
        $result = $stmt->get_result();
        return $result;
    }
    
    public function inserir($campos) {
        $sql = "INSERT INTO "
            . "feriado(id_periodo,data_feriado) "
            . "VALUES ({$campos['id_periodo']},'{$campos['data_feriado']}')";

        $stmt = $this->bd->prepare($sql);
        $result = $stmt->execute() or die($this->bd->error);
        if (!$result) {
            return false;
        } else {
            return mysqli_stmt_insert_id($stmt); 
        }     
    }
    
    public function atualizar($campos) {
         $sql = "UPDATE feriado "
            . "SET "
            . "id_periodo = {$campos['id_periodo']}, "
            . "data_feriado = '{$campos['data_feriado']}'"
            . "WHERE id_feriado = '{$campos['id_feriado']}'";
        $stmt = $this->bd->prepare($sql);
        $result = $stmt->execute() or die($this->bd->error);
        return $result; 
    }

    public function deletar($id_feriado) {
        $sql = "DELETE FROM feriado WHERE id_feriado = $id_feriado";
        $stmt = $this->bd->prepare($sql);
        $result = $stmt->execute() or die($this->bd->error);
        return $result;         
    }
  
        
}
