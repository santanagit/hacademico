<?php

require_once $_SESSION['diretorio_base'].'/model/conexaoModel.php';

class turmaModel {

    private $bd;
    
    public function __construct() {
        $conexao = new conexaoModel();
        $this->bd = $conexao->getConexao();
    }
    
    public function getTurma($id_turma) {
        $sql = "SELECT
                    descricao,
                    turma.id_curso,
                    vagas,
                    turma.id_turma,
                    turma.id_periodo,
                    periodo.ano,
                    turma.turno,
                    periodo.semestre,
                    DATE_FORMAT(data_inicio,'%d/%m/%Y') as data_inicio,
                    DATE_FORMAT(data_fim,'%d/%m/%Y') as data_fim
                FROM 
                    turma INNER JOIN `periodo`
                        ON turma.`id_periodo` = periodo.`id_periodo`
                WHERE 
                    turma.id_turma = $id_turma";
        
        $stmt = $this->bd->prepare($sql);
        $stmt->execute() or die($this->bd->error); 
        $result = $stmt->get_result();
        return $result;        
    }    
           
    public function listar($parametros=array(),$ordenacao=array(),$limit=array()) {
        
        $sql = "SELECT
                    descricao,
                    curso.nome as curso,
                    turma.id_curso,
                    vagas,
                    turma.id_turma,
                    turma.id_periodo,
                    turma.turno,
                    periodo.ano,
                    periodo.semestre,
                    DATE_FORMAT(data_inicio,'%d/%m/%Y') as data_inicio,
                    DATE_FORMAT(data_fim,'%d/%m/%Y') as data_fim
                FROM 
                    turma INNER JOIN `periodo`
                        ON turma.`id_periodo` = periodo.`id_periodo` 
                    LEFT JOIN curso
                        ON turma.id_curso = curso.id_curso
                ";
        
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
        $sql = "INSERT INTO "
        . "turma(id_periodo,id_curso,descricao,vagas,turno) "
        . "VALUES ({$campos['id_periodo']},{$campos['id_curso']},'{$campos['descricao']}',{$campos['vagas']},'{$campos['turno']}')";

        $stmt = $this->bd->prepare($sql);
        $result = $stmt->execute() or die($this->bd->error);
        if (!$result) {
            return false;
        } else {
            return mysqli_stmt_insert_id($stmt); 
        }     
    }
    
    public function atualizar($campos) {
         $sql = "UPDATE turma "
            . "SET "
            . "id_periodo = {$campos['id_periodo']}, "
            . "id_curso = {$campos['id_curso']}, "
            . "descricao = '{$campos['descricao']}', "
            . "vagas = {$campos['vagas']}, "
            . "turno = '{$campos['turno']}'"
            . "WHERE id_turma = {$campos['id_turma']}";
        $stmt = $this->bd->prepare($sql);
        $result = $stmt->execute() or die($this->bd->error);
        return $result; 
    }

    public function deletar($id_turma) {
        $sql = "DELETE FROM turma WHERE id_turma = $id_turma";
        $stmt = $this->bd->prepare($sql);
        $result = $stmt->execute() or die($this->bd->error);
        return $result;         
    }
        
    public function existeVinculo($id_turma) {
        $sql = "SELECT id_oferta_disciplina FROM oferta_disciplina WHERE id_turma = $id_turma";
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
    
    public function getTurno() {
        $sql = "SHOW COLUMNS FROM turma WHERE FIELD = 'turno'";
        $stmt = $this->bd->prepare($sql);
        $stmt->execute() or die($this->bd->error); 
        $result = $stmt->get_result();
        return $result;
    }    
        
}
