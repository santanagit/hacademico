<?php

require_once $_SESSION['diretorio_base'].'/model/conexaoModel.php';

class gradeModel {

    private $bd;
    
    public function __construct() {
        $conexao = new conexaoModel();
        $this->bd = $conexao->getConexao();
    }
    
    public function getGrade($id_grade) {
        $sql = "SELECT * FROM grade WHERE grade.id_grade = $id_grade";
        
        $stmt = $this->bd->prepare($sql);
        $stmt->execute() or die($this->bd->error); 
        $result = $stmt->get_result();
        return $result;        
    }    
           
    public function listar($id_curso=0,$parametros=array(),$ordenacao=array(),$limit=array()) {
                
        $sql = "SELECT  
                    grade.id_grade,
                    grade.modulo,
                    grade.id_curso,
                    grade.cod_sigaa as cod_sigaa,
                    disciplina.descricao as disciplina,
                    grade.id_disciplina as id_disciplina,
                    curso.nome as curso,
                    curso.regime,
                    curso.matriz,
                    curso.turno,
                    disciplina.chs,
                    disciplina.chs_ead,
                    disciplina.cht
                FROM 
                    grade INNER JOIN `disciplina`
                        ON grade.`id_disciplina` = disciplina.`id_disciplina` 
                    INNER JOIN `curso`
                        ON grade.`id_curso` = curso.`id_curso`";
        
        if (count($parametros) > 0) {
            $i = 0;
            $sql .= ' WHERE (';
            foreach ($parametros as $key => $value) {               
                if ($i > 0) $sql .= " OR ";
                $sql .= "$key like '%$value%'";                
                $i++;
            }
            $sql .= ") AND grade.id_curso = $id_curso";
        } else {
            $sql .= " WHERE grade.id_curso = $id_curso";
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
        $sql .= ';';
     
        $stmt = $this->bd->prepare($sql);
        $stmt->execute() or die($this->bd->error);
        $result = $stmt->get_result();
        return $result;        
    }
    
    public function inserir($campos) {
        $sql = "INSERT INTO "
            . "grade(id_disciplina,id_curso,modulo,ementa,cod_sigaa) "
            . "VALUES (?,?,?,?,?)";
        $stmt = $this->bd->prepare($sql);
        $stmt->bind_param("iiiss", 
            $campos['id_disciplina'],
            $campos['id_curso'],
            $campos['modulo'],
            $campos['ementa'],
            $campos['cod_sigaa']);
        $result = $stmt->execute() or die($this->bd->error);
        if (!$result) {
            return false;
        } else {
            return mysqli_stmt_insert_id($stmt); 
        }     
    }
    
    public function atualizar($campos) {
         $sql = "UPDATE grade "
            . "SET "
            . "id_disciplina = '{$campos['id_disciplina']}', "           
            . "modulo = '{$campos['modulo']}', "            
            . "ementa = '{$campos['ementa']}', "            
            . "cod_sigaa = '{$campos['cod_sigaa']}' "                
            . "WHERE id_grade = '{$campos['id_grade']}'";
        $stmt = $this->bd->prepare($sql);
        $result = $stmt->execute() or die($this->bd->error);
        return $result; 
    }

    public function deletar($id_grade) {
        $sql = "DELETE FROM grade WHERE id_grade = $id_grade";
        $stmt = $this->bd->prepare($sql);
        $result = $stmt->execute() or die($this->bd->error);
        return $result;         
    } 
    
    public function existeGrade($campos) {
        $sql = "SELECT * FROM grade WHERE modulo = ? AND id_disciplina = ? AND id_curso = ? AND ementa = ? AND cod_sigaa = ?";
        $stmt = $this->bd->prepare($sql);
        $stmt->bind_param("iiiss",$campos['modulo'],$campos['id_disciplina'],$campos['id_curso'],$campos['ementa'],$campos['cod_sigaa']);
        $stmt->execute() or die($this->bd->error);
        $stmt->store_result();
        $stmt->num_rows;
        if ($stmt->num_rows > 0) {
            return true;
        } else {
            return false;
        }              
    }

    public function getCursoModulo(){
        $sql = "SELECT
                    grade.`id_curso`,
                    curso.nome,
                    curso.`regime`,
                    grade.modulo,
                    curso.`turno`,
                    curso.matriz	
                FROM 
                    grade INNER JOIN `curso`
                        ON grade.`id_curso` = curso.`id_curso`
                GROUP BY
                    grade.`id_curso`,
                    curso.nome,
                    curso.`regime`,
                    grade.modulo,
                    curso.`turno`,
                    curso.matriz
                ORDER BY
                    curso.nome,
                    curso.`regime`,
                    grade.modulo,
                    curso.`turno`,
                    curso.matriz";
        $stmt = $this->bd->prepare($sql);
        $stmt->execute() or die($this->bd->error);
        $result = $stmt->get_result();
        return $result;         
    }
    
    public function getDisciplinas($id_curso,$modulo){
        $sql = "SELECT
                    grade.`id_curso`,
                    grade.id_disciplina,
                    grade.ementa,
                    grade.cod_sigaa,
                    curso.nome,
                    curso.`regime`,
                    curso.`turno`,
                    curso.matriz,
                    disciplina.descricao,
                    disciplina.chs,
                    disciplina.chs_ead,
                    disciplina.cht
                FROM 
                    grade INNER JOIN curso
                        ON grade.`id_curso` = curso.`id_curso`
                    INNER JOIN disciplina
                        ON grade.`id_disciplina` = disciplina.`id_disciplina`
                WHERE 
                    grade.id_curso = $id_curso AND 
                    grade.modulo = $modulo
                ORDER BY
                    disciplina.descricao";
        
        $stmt = $this->bd->prepare($sql);
        $stmt->execute() or die($this->bd->error);
        $result = $stmt->get_result();
        return $result;                 
    }
    
    public function getMatrizVinculada($id_disciplina) {
        $sql = "SELECT
                    disciplina.descricao as disciplina,
                    curso.nome as curso,
                    curso.matriz as matriz
                FROM
                    grade INNER JOIN curso 
                        ON grade.id_curso = curso.id_curso
                    INNER JOIN disciplina
                        ON grade.id_disciplina = disciplina.id_disciplina
                WHERE
                    disciplina.id_disciplina = $id_disciplina
                ORDER BY
                    curso.nome, 
                    curso.matriz,
                    disciplina.descricao";
        $stmt = $this->bd->prepare($sql);
        $stmt->execute() or die($this->bd->error);
        $result = $stmt->get_result();
        return $result;          
    }
}