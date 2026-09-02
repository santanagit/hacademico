<?php

require_once $_SESSION['diretorio_base'] . '/model/conexaoModel.php';

class oferta_disciplinaModel {

    private $bd;

    public function __construct() {
        $conexao = new conexaoModel();
        $this->bd = $conexao->getConexao();
    }

    public function getConselhosDeClasse($id_periodo,$id_usuario) {
        $sql = "SELECT
                    DISTINCT(curso.nome) AS curso
                FROM
                    oferta_disciplina INNER JOIN turma
                            ON oferta_disciplina.`id_turma` = turma.id_turma
                    INNER JOIN curso
                            ON turma.id_curso = curso.id_curso
                WHERE
                    curso.nivel = 'Técnico' AND
                    turma.id_periodo = $id_periodo AND
                    oferta_disciplina.`id_usuario` = $id_usuario";
//        echo $sql;
//        die();
        $stmt = $this->bd->prepare($sql);
        $stmt->execute() or die($this->bd->error);
        $result = $stmt->get_result();
        return $result;         
    }
    
    public function getCargaHoraria($id_periodo,$semestre) {

        $sql = "SELECT 
                    `id_usuario`,
                    `nome`,
                    SUM(chs) AS chs,
                    SUM(chs_ead) AS chs_ead
                FROM 
                    carga_horaria_docente
                WHERE ";

        if ($semestre == 1) {
            $sql .= "id_periodo = $id_periodo";
        } else { 
            $id_periodo_anterior = $id_periodo - 1;
            $sql .= "(
                        id_periodo = $id_periodo OR 
                        (id_periodo = $id_periodo_anterior AND modulo = 'Anual')
                    )";
        }        
        
        $sql .= "        
                GROUP BY
                    `id_usuario`,
                    `nome`
                ORDER BY 
                    nome
                ";
        //echo $sql;
        $stmt = $this->bd->prepare($sql);
        $stmt->execute() or die($this->bd->error);
        $result = $stmt->get_result();
        return $result;        
    }

    public function listar($id_periodo, $nucleo, $id_turma,$semestre, $parametros = array(), $ordenacao = array(), $limit = array()) {
        //echo ' Núcleo: '.$nucleo.' ';
        $sql = "SELECT
                    periodo.`ano`,
                    periodo.`semestre`,
                    turma.descricao AS turma,
                    disciplina.`descricao` AS disciplina,
                    disciplina.chs AS chs_disciplina,
                    disciplina.cht AS cht_disciplina,
                    disciplina.chs_ead AS chs_ead_disciplina,
                    oferta_disciplina.`chs` AS chs,
                    oferta_disciplina.cht AS cht,
                    oferta_disciplina.`chs_ead` AS chs_ead,
                    usuario.`nome` AS professor,
                    oferta_disciplina.`id_oferta_disciplina`,
                    oferta_disciplina.`id_disciplina`,
                    turma.`id_turma`,
                    oferta_disciplina.`id_usuario`,
                    curso.nucleo,
                    oferta_disciplina.tipo
                FROM 
                    turma LEFT JOIN (oferta_disciplina 
                                        INNER JOIN disciplina 
                                            ON oferta_disciplina.id_disciplina = disciplina.id_disciplina
                                        LEFT JOIN usuario
                                            ON oferta_disciplina.`id_usuario` = usuario.`id_usuario`)
                        ON turma.id_turma = oferta_disciplina.id_turma
                    INNER JOIN periodo
                        ON turma.`id_periodo` = periodo.`id_periodo` 
                    INNER JOIN curso
                        ON turma.`id_curso` = curso.`id_curso`                             
                WHERE";
        if ($semestre == 1) {
            $sql .= " periodo.id_periodo = $id_periodo";
        } else {
            $id_periodo_anterior = $id_periodo - 1;
            $sql .= "
                    (
                        (periodo.id_periodo = $id_periodo)
                        OR
                        (periodo.id_periodo = $id_periodo_anterior AND curso.`modulo` = 'Anual')
                    )";
        }   
        
        if ($id_turma !== '0') {
            $sql .= " AND turma.id_turma = $id_turma";
        }

        if ($nucleo !== '0') {
            $sql .= " AND curso.nucleo = '$nucleo'";
        } 
        
        if (count($parametros) > 0) {
            $i = 0;
            $sql .= " AND (";
            foreach ($parametros as $key => $value) {
                if ($i > 0)
                    $sql .= " OR ";
                $sql .= "$key like '%$value%'";
                $i++;
            }
            $sql .= ")";
        }

        if (count($ordenacao) > 0) {
            $i = 0;
            $sql .= ' ORDER BY ';
            foreach ($ordenacao as $key => $value) {
                if ($i > 0)
                    $sql .= ", ";
                $sql .= "$key $value";
                $i++;
            }
        }

        if (count($limit) > 0) {
            $sql .= " LIMIT {$limit['inicio']},{$limit['quantidade']}";
        }
        
        //echo $sql;
        $stmt = $this->bd->prepare($sql);
        $stmt->execute() or die($this->bd->error);
        $result = $stmt->get_result();
        return $result;
    }

    public function inserir($campos) {
        if (!isset($campos['id_usuario'])) {
            $campos['id_usuario'] = 'NULL';
        }
        $sql = "INSERT INTO oferta_disciplina(id_disciplina,id_turma,id_usuario,chs,chs_ead,cht,tipo) "
                . "VALUES ({$campos['id_disciplina']},{$campos['id_turma']},{$campos['id_usuario']},{$campos['chs']},{$campos['chs_ead']},{$campos['cht']},'{$campos['tipo']}')";

        $stmt = $this->bd->prepare($sql);
        $result = $stmt->execute() or die($this->bd->error);
        if (!$result) {
            return false;
        } else {
            return mysqli_stmt_insert_id($stmt);
        }
    }

    public function atualizar($campos) {
        if (!isset($campos['id_usuario'])) {
            $campos['id_usuario'] = 'id_usuario = NULL ';
        } else if ($campos['id_usuario'] == '') {
            $campos['id_usuario'] = 'id_usuario = NULL ';
        } else {
            $campos['id_usuario'] = "id_usuario = {$campos['id_usuario']} ";
        }
        $sql = "UPDATE oferta_disciplina "
                . "SET "
                . $campos['id_usuario']
                . "WHERE id_oferta_disciplina = {$campos['id_oferta_disciplina']}";
        $stmt = $this->bd->prepare($sql);
        //echo $sql;
        $result = $stmt->execute() or die($this->bd->error);
        return $result;
    }
    
    public function atualizar_chs($campos) {

        $sql = "UPDATE oferta_disciplina 
                SET 
                    chs = {$campos['chs']},
                    cht = {$campos['cht']}
                WHERE id_oferta_disciplina = {$campos['id_oferta_disciplina']}";
        $stmt = $this->bd->prepare($sql);
        //echo $sql;
        $result = $stmt->execute() or die($this->bd->error);
        return $result;
    }    

    public function atualizar_chs_ead($campos) {

        $sql = "UPDATE oferta_disciplina 
                SET 
                    chs_ead = {$campos['chs_ead']}
                WHERE id_oferta_disciplina = {$campos['id_oferta_disciplina']}";
        $stmt = $this->bd->prepare($sql);
        //echo $sql;
        $result = $stmt->execute() or die($this->bd->error);
        return $result;
    }    
    
    public function atualizar_tipo($campos) {

        $sql = "UPDATE oferta_disciplina 
                SET 
                    tipo = '{$campos['tipo']}'
                WHERE id_oferta_disciplina = {$campos['id_oferta_disciplina']}";
        //echo $sql;
        $stmt = $this->bd->prepare($sql);
        $result = $stmt->execute() or die($this->bd->error);
        return $result;
    }  

    
    public function deletar($id_oferta_disciplina) {
        $sql = "DELETE FROM oferta_disciplina WHERE id_oferta_disciplina = ?";
        $stmt = $this->bd->prepare($sql);
        $stmt->bind_param("i", $id_oferta_disciplina);
        $result = $stmt->execute() or die($this->bd->error);
        return $result;
    }

    public function getOfertaDisciplina($id_oferta_disciplina) {
        $sql = "SELECT oferta_disciplina.*, usuario.nome, disciplina.descricao "
                . "FROM oferta_disciplina INNER JOIN usuario ON oferta_disciplina.id_usuario = usuario.id_usuario "
                . "INNER JOIN disciplina ON oferta_disciplina.id_disciplina = disciplina.id_disciplina "
                . "WHERE id_oferta_disciplina = ?";
        $stmt = $this->bd->prepare($sql);
        $stmt->bind_param("i", $id_oferta_disciplina);
        $stmt->execute() or die($this->bd->error);
        $result = $stmt->get_result();
        return $result;
    }

    public function existeVinculo($id_oferta_disciplina) {
        $sql = "SELECT id_horario FROM horario WHERE id_oferta_disciplina = $id_oferta_disciplina";

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

    public function existeOfertaDisciplina($id_disciplina, $id_turma, $id_usuario) {
        $sql = "SELECT * FROM oferta_disciplina WHERE id_disciplina = $id_disciplina AND id_turma = $id_turma AND id_usuario = $id_usuario";
        //echo $sql;        
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

    public function getTurmasAtivas($id_periodo,$semestre) {
        $sql = "SELECT 
                    DISTINCT turma.id_turma,
                    turma.`descricao` as turma,
                    turma.turno,
                    turma.`vagas`,
                    periodo.`ano`,
                    periodo.`semestre`,
                    curso.nivel,
                    DATE_FORMAT(periodo.`data_inicio`,'%d/%m/%Y') AS data_inicio,
                    DATE_FORMAT(periodo.`data_fim`,'%d/%m/%Y') AS data_fim
                FROM 
                    turma LEFT JOIN (oferta_disciplina 
                        INNER JOIN disciplina 
                            ON oferta_disciplina.id_disciplina = disciplina.id_disciplina
                        LEFT JOIN usuario
                            ON oferta_disciplina.`id_usuario` = usuario.`id_usuario`)
                        ON turma.id_turma = oferta_disciplina.id_turma
                    INNER JOIN periodo
                        ON turma.`id_periodo` = periodo.`id_periodo`
                    INNER JOIN curso
                        ON curso.`id_curso` = turma.`id_curso`                          
                WHERE";
        if ($semestre == 1) {
            $sql .= " periodo.id_periodo = $id_periodo";
        } else {
            $id_periodo_anterior = $id_periodo - 1;
            $sql .= "
                    (
                        (periodo.id_periodo = $id_periodo)
                        OR
                        ($id_periodo_anterior AND curso.`modulo` = 'Anual')
                    )";
        }
        $sql .= "
                ORDER BY 
                    turma.`descricao`";

        $stmt = $this->bd->prepare($sql);
        $stmt->execute() or die($this->bd->error);
        $result = $stmt->get_result();
        return $result;
    }

    public function getDisciplinasOfertadas($id_turma) {
        $sql = "SELECT
                    oferta_disciplina.id_oferta_disciplina,
                    oferta_disciplina.id_turma,
                    oferta_disciplina.id_usuario,
                    oferta_disciplina.id_disciplina,
                    disciplina.descricao as disciplina,
                    disciplina.chs,
                    disciplina.chs_ead,
                    disciplina.cht,
                    disciplina.id_disciplina,
                    usuario.nome as professor,
                    oferta_disciplina.tipo
                FROM
                    oferta_disciplina 
                    INNER JOIN disciplina 
                        ON oferta_disciplina.id_disciplina = disciplina.id_disciplina
                    INNER JOIN usuario
                        ON oferta_disciplina.id_usuario = usuario.id_usuario
                WHERE 
                    id_turma = $id_turma 
                ORDER BY
                    disciplina.descricao
                ";
        $stmt = $this->bd->prepare($sql);
        $stmt->execute() or die($this->bd->error);
        $result = $stmt->get_result();
        return $result;
    }
    
    public function getDisciplinasOfertadasPeriodo($id_periodo) {
        $sql = "SELECT
                    oferta_disciplina.id_oferta_disciplina,
                    oferta_disciplina.id_turma,
                    oferta_disciplina.id_usuario,
                    oferta_disciplina.id_disciplina,
                    disciplina.descricao as disciplina,
                    disciplina.chs,
                    disciplina.chs_ead,
                    disciplina.cht,
                    disciplina.id_disciplina,
                    usuario.nome as professor,
                    turma.descricao,
                    oferta_disciplina.tipo
                FROM
                    oferta_disciplina 
                    INNER JOIN disciplina 
                        ON oferta_disciplina.id_disciplina = disciplina.id_disciplina
                    INNER JOIN turma
                        ON oferta_disciplina.id_turma = turma.id_turma
                    INNER JOIN usuario
                        ON oferta_disciplina.id_usuario = usuario.id_usuario
                WHERE turma.`id_periodo` = $id_periodo ORDER BY disciplina.descricao";
        $stmt = $this->bd->prepare($sql);
        $stmt->execute() or die($this->bd->error);
        $result = $stmt->get_result();
        return $result;
    }
    
    public function getDisciplinasOfertadasPeriodoProfessor($id_periodo,$id_usuario,$semestre) {
        $sql = "SELECT
                    oferta_disciplina.id_oferta_disciplina,
                    oferta_disciplina.id_turma,
                    oferta_disciplina.id_usuario,
                    oferta_disciplina.id_disciplina,
                    disciplina.descricao as disciplina,
                    oferta_disciplina.chs,
                    oferta_disciplina.chs_ead,
                    oferta_disciplina.cht,
                    disciplina.id_disciplina,
                    usuario.nome as professor,
                    turma.descricao,
                    oferta_disciplina.tipo
                FROM
                    oferta_disciplina 
                    INNER JOIN disciplina 
                        ON oferta_disciplina.id_disciplina = disciplina.id_disciplina
                    INNER JOIN turma
                        ON oferta_disciplina.id_turma = turma.id_turma
                    INNER JOIN usuario
                        ON oferta_disciplina.id_usuario = usuario.id_usuario
                    INNER JOIN curso
                        ON curso.`id_curso` = turma.`id_curso`                        
                WHERE ";
        if ($semestre == 2) {
            $id_periodo_anterior = $id_periodo - 1;
            $sql .= " 
                    (
                        turma.id_periodo = $id_periodo OR
                        (turma.id_periodo = $id_periodo_anterior AND curso.modulo = 'Anual')
                    )
                    AND            
            ";           
        } else {
            $sql .= " turma.id_periodo = $id_periodo AND ";
        }
        $sql .= " 
                   
                    oferta_disciplina.id_usuario = $id_usuario
                ORDER BY 
                    disciplina.descricao";
        $stmt = $this->bd->prepare($sql);
        $stmt->execute() or die($this->bd->error);
        $result = $stmt->get_result();
        return $result;
    }      
    
    public function getTipo() {
        $sql = "SHOW COLUMNS FROM oferta_disciplina WHERE FIELD = 'tipo'";
        $stmt = $this->bd->prepare($sql);
        $stmt->execute() or die($this->bd->error); 
        $result = $stmt->get_result();
        return $result;    
    }
}
