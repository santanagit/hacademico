<?php

require_once $_SESSION['diretorio_base'] . '/model/conexaoModel.php';

class horarioModel {

    private $bd;

    public function __construct() {
        $conexao = new conexaoModel();
        $this->bd = $conexao->getConexao();
    }

    function getMoldura($turno,$nivel) {

        $condicao = '';
        $inicio = '';
        $fim = '';
        if ($turno == 'Integral') {
            $condicao = 'inicio_integrado < "18:00:00"';
            $inicio = 'inicio_integrado';
            $fim = 'fim_integrado';
        } else if ($turno == 'Matutino') {
            $condicao = 'inicio_integrado < "12:00:00"';
            $inicio = 'inicio_integrado';
            $fim = 'fim_integrado';
        } else if ($turno == 'Vespertino') {
            $condicao = 'inicio_concomitante > "12:00:00" AND inicio_concomitante < "18:00:00"';
            $inicio = 'inicio_concomitante';
            $fim = 'fim_concomitante';
        } else if (($turno == 'Noturno') && ($nivel == 'Técnico')) {
            $condicao = 'inicio_superior > "17:00:00"';
            $inicio = 'inicio_superior';
            $fim = 'fim_superior';
        } else if ($turno == 'Noturno') {
            $condicao = 'inicio_superior > "18:00:00"';
            $inicio = 'inicio_superior';
            $fim = 'fim_superior';
        } else if ($turno == 'EAD') {
            return false;
        }

        $sql = "SELECT 
                    dia.`id_dia`,
                    dia.`descricao`,
                    hora.`id_hora`,
                    $inicio AS inicio,
                    $fim AS fim
                FROM
                    dia,hora
                WHERE
                    $condicao
                ORDER BY
                    id_hora,id_dia;";
        
        $stmt = $this->bd->prepare($sql);
        $stmt->execute() or die($this->bd->error);
        $result = $stmt->get_result();
        return $result;
    }

    public function getTipo() {
        $sql = "SHOW COLUMNS FROM horario WHERE FIELD = 'tipo'";
        $stmt = $this->bd->prepare($sql);
        $stmt->execute() or die($this->bd->error);
        $result = $stmt->get_result();
        return $result;
    }
    
    public function disciplinaCadastrada($id_turma,$id_dia,$id_hora,$id_disciplina,$id_usuario) {
        $sql = "SELECT
                    id_horario
                FROM
                    horario INNER JOIN oferta_disciplina
                        ON horario.`id_oferta_disciplina` = oferta_disciplina.`id_oferta_disciplina`
                WHERE 
                    id_usuario IS NOT NULL AND
                    id_usuario = $id_usuario AND
                    id_dia = $id_dia AND
                    id_hora = $id_hora AND
                    oferta_disciplina.`id_turma` = $id_turma AND
                    oferta_disciplina.id_disciplina = $id_disciplina
                ";

        $stmt = $this->bd->prepare($sql);
        $stmt->execute() or die($this->bd->error);
        $result = $stmt->get_result();
        return $result;       
    }
    
    public function existeChoque($id_usuario,$id_dia,$id_hora,$id_periodo, $semestre) {
        if  (trim($id_usuario) == '') $id_usuario = 0;
        $sql = "SELECT 
                    horario.id_horario,
                    disciplina.`descricao` AS disciplina,
                    turma.`descricao` AS turma,
                    turma.id_periodo
                FROM
                    horario INNER JOIN oferta_disciplina
                        ON horario.`id_oferta_disciplina` = oferta_disciplina.`id_oferta_disciplina`
                    INNER JOIN usuario
                        ON oferta_disciplina.`id_usuario` = usuario.`id_usuario`
                    INNER JOIN disciplina
                        ON oferta_disciplina.`id_disciplina` = disciplina.`id_disciplina`
                    INNER JOIN turma
                        ON oferta_disciplina.id_turma = turma.id_turma
                    INNER JOIN periodo
                        ON turma.`id_periodo` = periodo.`id_periodo`                        
                WHERE
                    horario.id_dia = $id_dia AND
                    horario.id_hora = $id_hora AND
                    usuario.`id_usuario` = $id_usuario AND ";
                    
                if ($semestre == 1) {     
                    $sql .= "turma.id_periodo = $id_periodo";
                } else {
                    $id_periodo_anterior = $id_periodo - 1;
                    $sql .= "(
                                turma.id_periodo = $id_periodo OR 
                                (turma.id_periodo = $id_periodo_anterior AND turma.turno = 'Integral')
                            )";                    
                }
                    
        //echo $sql;
        $stmt = $this->bd->prepare($sql);
        $stmt->execute() or die($this->bd->error);
        $result = $stmt->get_result();
        return $result;        
    }
    
    public function getHorario($id_dia,$id_hora,$id_turma) {
        set_time_limit(0);
        $sql = "SELECT 
                    horario.id_horario,
                    horario.id_oferta_disciplina,
                    horario.id_sala,
                    sala.descricao as sala,
                    disciplina.`descricao` AS disciplina,
                    turma.`descricao` AS turma,
                    oferta_disciplina.id_usuario
                FROM
                    horario INNER JOIN oferta_disciplina
                        ON horario.`id_oferta_disciplina` = oferta_disciplina.`id_oferta_disciplina`
                    INNER JOIN usuario
                        ON oferta_disciplina.`id_usuario` = usuario.`id_usuario`
                    INNER JOIN disciplina
                        ON oferta_disciplina.`id_disciplina` = disciplina.`id_disciplina`
                    INNER JOIN turma
                        ON oferta_disciplina.id_turma = turma.id_turma
                    INNER JOIN sala
                        ON horario.id_sala = sala.id_sala
                WHERE
                    horario.id_dia = $id_dia AND
                    horario.id_hora = $id_hora AND
                    oferta_disciplina.`id_turma` = $id_turma
               ";
        //echo $sql;
        $stmt = $this->bd->prepare($sql);
        $stmt->execute() or die($this->bd->error);
        $result = $stmt->get_result();
        if (mysqli_num_rows($result) > 0) {            
            $linha = mysqli_fetch_assoc($result);
            return $linha;              
        } else {
            return 0;
        }        
      
    }
    
    public function getDiaOferta($id_oferta_disciplina) {
        $sql = "SELECT 
                    DISTINCT(horario.id_dia) as id_dia
                FROM
                    horario INNER JOIN oferta_disciplina
                        ON horario.id_oferta_disciplina = oferta_disciplina.id_oferta_disciplina
                    INNER JOIN turma
                        ON oferta_disciplina.id_turma = turma.id_turma
                    INNER JOIN periodo
                        ON turma.id_periodo = periodo.id_periodo
                    INNER JOIN hora
                        ON horario.id_hora = hora.id_hora
                    INNER JOIN dia
                        ON horario.id_dia = dia.id_dia
                    INNER JOIN sabados 
                        ON horario.`id_dia` = sabados.`id_dia`
                WHERE
                    horario.id_oferta_disciplina = $id_oferta_disciplina AND
                    sabados.data >= periodo.data_inicio AND
                    sabados.data <= periodo.data_fim  
               ";
        //echo $sql;
        $stmt = $this->bd->prepare($sql);
        $stmt->execute() or die($this->bd->error);
        $result = $stmt->get_result();
        return $result;
    }    
    
    public function getSabadosOferta($id_oferta_disciplina) {
        $sql = "SELECT 
                    horario.id_dia AS id_dia,
                    dia.`descricao` AS dia,
                    IF(turma.`turno` = 'Noturno',MIN(hora.`inicio_superior`),IF (turma.`turno` = 'Integral',MIN(hora.`inicio_integrado`),MIN(hora.`inicio_concomitante`))) AS inicio,
                    IF(turma.`turno` = 'Noturno',MIN(hora.`fim_superior`),IF (turma.`turno` = 'Integral',MIN(hora.`fim_integrado`),MIN(hora.`fim_concomitante`))) AS fim,
                    sabados.`data`,
                    IF(turma.`turno` = 'Noturno',
                        CONCAT('Sábado letivo referente a ',dia.`descricao`,'-Feira (',MIN(hora.`inicio_superior`),' às ',MAX(hora.fim_superior),')'),
                            IF (turma.`turno` = 'Integral',
                                CONCAT('Sábado letivo referente a ',dia.`descricao`,'-Feira (',MIN(hora.`inicio_integrado`),' às ',MAX(hora.fim_integrado),')'),
                                    CONCAT('Sábado letivo referente a ',dia.`descricao`,'-Feira (',MIN(hora.`inicio_concomitante`),' às ',MAX(hora.fim_concomitante),')')
                    )) AS descricao
                FROM
                    horario INNER JOIN oferta_disciplina
                        ON horario.id_oferta_disciplina = oferta_disciplina.id_oferta_disciplina
                    INNER JOIN turma
                        ON oferta_disciplina.id_turma = turma.id_turma
                    INNER JOIN periodo
                        ON turma.id_periodo = periodo.id_periodo
                    INNER JOIN hora
                        ON horario.id_hora = hora.id_hora
                    INNER JOIN dia
                        ON horario.id_dia = dia.id_dia
                    INNER JOIN sabados 
                        ON horario.`id_dia` = sabados.`id_dia`
                WHERE
                    horario.id_oferta_disciplina = $id_oferta_disciplina AND
                    sabados.data >= periodo.data_inicio AND
                    sabados.data <= periodo.data_fim                            
                GROUP BY
                    sabados.id_sabados
               ";
        //echo $sql;
        $stmt = $this->bd->prepare($sql);
        $stmt->execute() or die($this->bd->error);
        $result = $stmt->get_result();
        return $result;
      
    }     

    public function inserir($campos) {
        $sql = "INSERT INTO horario (id_dia,id_hora,id_oferta_disciplina,id_sala,id_usuario,data_hora)".
                " VALUES (".$campos['id_dia'].
                ",".$campos['id_hora'].
                ",".$campos['id_oferta_disciplina'].
                ",".$campos['id_sala'].
                ",".$_SESSION['id_usuario'].
                ",'".date('Y-m-d H:i:s').
                "')";
        
        $stmt = $this->bd->prepare($sql);
        $result = $stmt->execute() or die($this->bd->error);
        return $result;       
    }
    
    public function atualizar($campos) {
        $sql = "UPDATE horario SET"
                . " id_hora = ".$campos['id_hora']
                . ", id_dia = ".$campos['id_dia']
                . ", id_oferta_disciplina = ".$campos['id_oferta_disciplina']
                . ", id_sala = ".$campos['id_sala']
                . ", id_usuario = ".$_SESSION['id_usuario']
                . ", data_hora = '".date('Y-m-d H:i:s')
                . "' WHERE id_horario = ".$campos['id_horario'];
        
        $stmt = $this->bd->prepare($sql);
        $result = $stmt->execute() or die($this->bd->error);
        return $result;       
    }
    
    public function excluir($id_horario) {
        $sql = "DELETE FROM horario WHERE id_horario = $id_horario";        
        $stmt = $this->bd->prepare($sql);
        $result = $stmt->execute() or die($this->bd->error);
        return $result;       
    }

    public function excluirOfertaDisciplinaProfessor($id_oferta_disciplina,$id_usuario) {
        $sql = "DELETE
                    FROM horario 
                WHERE 
                    id_horario IN (
                        SELECT horario.id_horario FROM horario INNER JOIN oferta_disciplina 
                            ON horario.id_oferta_disciplina = oferta_disciplina.id_oferta_disciplina 
                        WHERE 
                            horario.id_oferta_disciplina = $id_oferta_disciplina AND 
                            oferta_disciplina.id_usuario = $id_usuario);
                ";        
        $stmt = $this->bd->prepare($sql);
        $result = $stmt->execute() or die($this->bd->error);
        return $result;       
    }    
    
    public function mapa_sala($id_sala,$id_periodo,$semestre) {
        $sql = "SELECT
                    horario.id_horario,
                    horario.id_hora,
                    horario.id_dia,
                    oferta_disciplina.`id_disciplina`,
                    oferta_disciplina.`id_usuario`,
                    turma.`descricao` AS turma,
                    dia.`descricao` AS dia,
                    IF (curso.turno = 'Noturno',
                        CONCAT(DATE_FORMAT(hora.`inicio_superior`,'%H:%i'),' - ',DATE_FORMAT(hora.`fim_superior`,'%H:%i')),
                            IF (curso.turno = 'Integral',
                                CONCAT(DATE_FORMAT(hora.`inicio_integrado`,'%H:%i'),' - ',DATE_FORMAT(hora.`fim_integrado`,'%H:%i')),
                                    CONCAT(DATE_FORMAT(hora.`inicio_concomitante`,'%H:%i'),' - ',DATE_FORMAT(hora.`fim_concomitante`,'%H:%i')))) AS horario,
                    sala.`descricao` AS sala,
                    disciplina.`descricao` AS disciplina,
                    usuario.cor,
                    usuario.`nome` AS professor,
                    oferta_disciplina.`id_turma`,
                    curso.nivel,
                    turma.turno
                FROM
                    horario INNER JOIN dia
                        ON horario.`id_dia` = dia.`id_dia`
                    INNER JOIN hora
                        ON horario.`id_hora` = hora.`id_hora`
                    INNER JOIN sala
                        ON horario.`id_sala` = sala.`id_sala`
                    INNER JOIN oferta_disciplina
                        ON horario.`id_oferta_disciplina` = oferta_disciplina.`id_oferta_disciplina`
                    INNER JOIN usuario
                        ON oferta_disciplina.`id_usuario` = usuario.`id_usuario`
                    INNER JOIN disciplina
                        ON oferta_disciplina.`id_disciplina` = disciplina.`id_disciplina`
                    INNER JOIN turma
                        ON oferta_disciplina.`id_turma` = turma.`id_turma`
                    INNER JOIN curso
                        ON curso.`id_curso` = turma.`id_curso`
                    INNER JOIN periodo
                        ON turma.id_periodo = periodo.id_periodo
                WHERE
                    horario.id_sala = $id_sala AND ";
                        
        if ($semestre == 1) {                
            $sql .= "turma.id_periodo = $id_periodo ";
        } else {
            $id_periodo_anterior = $id_periodo - 1;
            $sql .= "
                    (
                        (periodo.id_periodo = $id_periodo)
                        OR
                        (periodo.id_periodo = $id_periodo_anterior AND curso.`modulo` = 'Anual')
                    ) ";            
        }
        
        $sql .= "ORDER BY
                    horario.id_hora,horario.id_dia";
        
        //echo $sql;
        
        $stmt = $this->bd->prepare($sql);
        $stmt->execute() or die($this->bd->error);
        $result = $stmt->get_result();
        return $result;        
                    
    }
    
    public function getGrade($id_disciplina,$id_curso) {
        $sql = "SELECT 
                    cod_sigaa FROM grade 
                WHERE 
                    id_disciplina = $id_disciplina AND 
                    id_curso = $id_curso
                ";
        //echo $sql;
        $stmt = $this->bd->prepare($sql);
        $stmt->execute() or die($this->bd->error);
        $result = $stmt->get_result();
        return $result;        
    }
       
    public function listar($id_turma) {

        $sql = "SELECT
                    horario.id_horario,
                    horario.id_hora,
                    horario.id_dia,
                    curso.id_curso,
                    oferta_disciplina.`id_disciplina`,
                    oferta_disciplina.`id_usuario`,
                    turma.`descricao` AS turma,
                    dia.`descricao` AS dia,
                    IF (curso.turno = 'Noturno',
                        CONCAT(DATE_FORMAT(hora.`inicio_superior`,'%H:%i'),' - ',DATE_FORMAT(hora.`fim_superior`,'%H:%i')),
                            IF (curso.turno = 'Integral',
                                CONCAT(DATE_FORMAT(hora.`inicio_integrado`,'%H:%i'),' - ',DATE_FORMAT(hora.`fim_integrado`,'%H:%i')),
                                    CONCAT(DATE_FORMAT(hora.`inicio_concomitante`,'%H:%i'),' - ',DATE_FORMAT(hora.`fim_concomitante`,'%H:%i')))) AS horario,
                    sala.`descricao` AS sala,
                    disciplina.`descricao` AS disciplina,
                    usuario.cor,
                    usuario.`nome` AS professor,
                    oferta_disciplina.`id_turma`,
                    curso.nivel,
                    turma.turno
                FROM
                    horario INNER JOIN dia
                        ON horario.`id_dia` = dia.`id_dia`
                    INNER JOIN hora
                        ON horario.`id_hora` = hora.`id_hora`
                    INNER JOIN sala
                        ON horario.`id_sala` = sala.`id_sala`
                    INNER JOIN oferta_disciplina
                        ON horario.`id_oferta_disciplina` = oferta_disciplina.`id_oferta_disciplina`
                    INNER JOIN usuario
                        ON oferta_disciplina.`id_usuario` = usuario.`id_usuario`
                    INNER JOIN disciplina
                        ON oferta_disciplina.`id_disciplina` = disciplina.`id_disciplina`
                    INNER JOIN turma
                        ON oferta_disciplina.`id_turma` = turma.`id_turma`
                    INNER JOIN curso
                        ON curso.`id_curso` = turma.`id_curso`                        
                WHERE
                    turma.id_turma = $id_turma
                ORDER BY
                    turma,horario.id_hora,horario.id_dia";
               
        $stmt = $this->bd->prepare($sql);
        $stmt->execute() or die($this->bd->error);
        $result = $stmt->get_result();
        return $result;  
    }
    
    public function getDisciplinasTurmaEAD ($id_turma) {
        
           $sql = "SELECT
                        oferta_disciplina.`id_disciplina`,
                        oferta_disciplina.`id_usuario`,
                        curso.id_curso,
                        turma.`descricao` AS turma,
                        disciplina.`descricao` AS disciplina,
                        usuario.`nome` AS professor,
                        disciplina.chs,
                        disciplina.cht,
                        oferta_disciplina.`id_turma`,
                        curso.nivel,
                        usuario.cor,
                        turma.turno,
                        CASE
                            WHEN curso.nivel = 'FIC' THEN grade.modulo
                            WHEN curso.nivel = 'Técnico' AND grade.modulo = 7 THEN 'Optativa'
                            WHEN curso.nivel = 'Graduação' AND grade.modulo = 9 THEN 'Optativa'
                            ELSE grade.modulo
                        END AS modulo	
                    FROM
                        oferta_disciplina INNER JOIN usuario
                                ON oferta_disciplina.`id_usuario` = usuario.`id_usuario`
                        INNER JOIN disciplina
                                ON oferta_disciplina.`id_disciplina` = disciplina.`id_disciplina`
                        INNER JOIN turma
                                ON oferta_disciplina.`id_turma` = turma.`id_turma`
                        INNER JOIN curso
                                ON curso.`id_curso` = turma.`id_curso` 
                        INNER JOIN grade
                                ON grade.id_curso = turma.id_curso and grade.id_disciplina = oferta_disciplina.id_disciplina
                    WHERE
                        turma.id_turma = $id_turma
                    ORDER BY
                        modulo,disciplina";
        //echo $sql;
        $stmt = $this->bd->prepare($sql);
        $stmt->execute() or die($this->bd->error);
        $result = $stmt->get_result();
        return $result;       
        
    }

    public function listar_ponto($id_periodo) {

        $sql = "SELECT
                    horario.id_horario,
                    horario.id_hora,
                    horario.id_dia,
                    oferta_disciplina.`id_disciplina`,
                    oferta_disciplina.`id_usuario`,
                    turma.`descricao` AS turma,
                    dia.`descricao` AS dia,
                    IF (curso.turno = 'Noturno',
                            CONCAT(DATE_FORMAT(hora.`inicio_superior`,'%H:%i'),' - ',DATE_FORMAT(hora.`fim_superior`,'%H:%i')),
                                    IF (curso.turno = 'Integral',
                                            CONCAT(DATE_FORMAT(hora.`inicio_integrado`,'%H:%i'),' - ',DATE_FORMAT(hora.`fim_integrado`,'%H:%i')),
                                                    CONCAT(DATE_FORMAT(hora.`inicio_concomitante`,'%H:%i'),' - ',DATE_FORMAT(hora.`fim_concomitante`,'%H:%i')))) AS horario,
                    sala.`descricao` AS sala,
                    disciplina.`descricao` AS disciplina,
                    usuario.cor,
                    usuario.`nome` AS professor,
                    oferta_disciplina.`id_turma`,
                    curso.nivel,
                    turma.turno
                FROM
                    horario INNER JOIN dia
                        ON horario.`id_dia` = dia.`id_dia`
                    INNER JOIN hora
                        ON horario.`id_hora` = hora.`id_hora`
                    INNER JOIN sala
                        ON horario.`id_sala` = sala.`id_sala`
                    INNER JOIN oferta_disciplina
                        ON horario.`id_oferta_disciplina` = oferta_disciplina.`id_oferta_disciplina`
                    INNER JOIN usuario
                        ON oferta_disciplina.`id_usuario` = usuario.`id_usuario`
                    INNER JOIN disciplina
                        ON oferta_disciplina.`id_disciplina` = disciplina.`id_disciplina`
                    INNER JOIN turma
                        ON oferta_disciplina.`id_turma` = turma.`id_turma`
                    INNER JOIN curso
                        ON curso.`id_curso` = turma.`id_curso`                        
                WHERE
                    turma.id_periodo = $id_periodo
                ORDER BY
                    horario.id_dia,professor,horario.id_hora";
               
        $stmt = $this->bd->prepare($sql);
        $stmt->execute() or die($this->bd->error);
        $result = $stmt->get_result();
        return $result;  
    }
    
    
    public function getHorarioEmail($email){
        $sql = "SELECT
                    usuario.`nome` AS funcionario,
                    usuario.`email`,
                    disciplina.`descricao` AS disciplina,	
                    horario.`id_horario`
                FROM
                    horario INNER JOIN oferta_disciplina
                        ON horario.`id_oferta_disciplina` = oferta_disciplina.`id_oferta_disciplina`
                    INNER JOIN usuario
                        ON oferta_disciplina.`id_usuario` = usuario.`id_usuario`
                    INNER JOIN disciplina
                        ON oferta_disciplina.`id_disciplina` = disciplina.`id_disciplina`
                WHERE
                    usuario.`email` = '$email'"; 
        $stmt = $this->bd->prepare($sql);
        $stmt->execute() or die($this->bd->error);
        $result = $stmt->get_result();
        return $result;         
    }
    
    public function horariosOfertaDisciplina($id_oferta_disciplina) {
        $sql = "SELECT  
                    disciplina.`descricao` AS disciplina,
                    dia.descricao as dia,
                    dia.id_dia,
                    hora.id_hora,
                    usuario.nome as professor,
                    turma.descricao as turma,
                    IF (curso.turno = 'Noturno',
                        CONCAT(DATE_FORMAT(hora.`inicio_superior`,'%H:%i'),' - ',DATE_FORMAT(hora.`fim_superior`,'%H:%i')),
                            IF (curso.turno = 'Integral',
                                CONCAT(DATE_FORMAT(hora.`inicio_integrado`,'%H:%i'),' - ',DATE_FORMAT(hora.`fim_integrado`,'%H:%i')),
                                    CONCAT(DATE_FORMAT(hora.`inicio_concomitante`,'%H:%i'),' - ',DATE_FORMAT(hora.`fim_concomitante`,'%H:%i')))) AS horario,
                    sabados.`data` as sabado
                FROM
                    horario INNER JOIN dia
                        ON horario.`id_dia` = dia.`id_dia`
                    INNER JOIN hora
                        ON horario.`id_hora` = hora.`id_hora`
                    INNER JOIN sala
                        ON horario.`id_sala` = sala.`id_sala`
                    INNER JOIN oferta_disciplina
                        ON horario.`id_oferta_disciplina` = oferta_disciplina.`id_oferta_disciplina`
                    INNER JOIN usuario
                        ON oferta_disciplina.`id_usuario` = usuario.`id_usuario`
                    INNER JOIN disciplina
                        ON oferta_disciplina.`id_disciplina` = disciplina.`id_disciplina`
                    INNER JOIN turma
                        ON oferta_disciplina.`id_turma` = turma.`id_turma`
                    INNER JOIN periodo
                        ON turma.`id_periodo` = periodo.`id_periodo`
                    INNER JOIN curso
                        ON curso.`id_curso` = turma.`id_curso`
                    INNER JOIN sabados
                    	ON horario.id_dia = sabados.id_dia
                WHERE 
                    
                    oferta_disciplina.id_oferta_disciplina = $id_oferta_disciplina
                ORDER BY
                    disciplina.descricao,dia.id_dia,hora.id_hora,sabados.data";
        //echo $sql;
        $stmt = $this->bd->prepare($sql);
        $stmt->execute() or die($this->bd->error);
        $result = $stmt->get_result();
        return $result;      
    }
    
    public function horarioProfessor($id_usuario,$id_periodo,$semestre) {
        $sql = "SELECT  
                    horario.id_horario,
                    curso.id_curso,
                    horario.id_hora,
                    horario.id_dia,
                    oferta_disciplina.`id_disciplina`,
                    oferta_disciplina.`id_usuario`,
                    turma.`descricao` AS turma,
                    dia.`descricao` AS dia,
                    IF ((hora.id_hora > 17),
                        CONCAT(DATE_FORMAT(hora.`inicio_superior`,'%H:%i'),' - ',DATE_FORMAT(hora.`fim_superior`,'%H:%i')),
						IF ((hora.id_hora < 12),
							CONCAT(DATE_FORMAT(hora.`inicio_integrado`,'%H:%i'),' - ',DATE_FORMAT(hora.`fim_integrado`,'%H:%i')),
							CONCAT('Integrado<br>',
								IF (hora.inicio_integrado IS NULL,'',DATE_FORMAT(hora.`inicio_integrado`,'%H:%i')),
								' - ',
								IF (hora.fim_integrado IS NULL,'',DATE_FORMAT(hora.`fim_integrado`,'%H:%i')),
								'<br><br>Concomitante<br>',
								IF (hora.inicio_concomitante IS NULL,'',DATE_FORMAT(hora.`inicio_concomitante`,'%H:%i')),
								' - ',
								IF (hora.fim_concomitante IS NULL,'',DATE_FORMAT(hora.`fim_concomitante`,'%H:%i'))
							)
                    )) AS horario,
                    sala.`descricao` AS sala,
                    disciplina.`descricao` AS disciplina,
                    oferta_disciplina.cht,
                    usuario.id_usuario,
                    usuario.`nome` AS professor,
                    oferta_disciplina.`id_turma`,
                    turma.`turno`,
                    curso.nivel
                FROM
                    horario INNER JOIN dia
                        ON horario.`id_dia` = dia.`id_dia`
                    INNER JOIN hora
                        ON horario.`id_hora` = hora.`id_hora`
                    INNER JOIN sala
                        ON horario.`id_sala` = sala.`id_sala`
                    INNER JOIN oferta_disciplina
                        ON horario.`id_oferta_disciplina` = oferta_disciplina.`id_oferta_disciplina`
                    INNER JOIN usuario
                        ON oferta_disciplina.`id_usuario` = usuario.`id_usuario`
                    INNER JOIN disciplina
                        ON oferta_disciplina.`id_disciplina` = disciplina.`id_disciplina`
                    INNER JOIN turma
                        ON oferta_disciplina.`id_turma` = turma.`id_turma`
                    INNER JOIN periodo
                        ON turma.`id_periodo` = periodo.`id_periodo`
                    INNER JOIN curso
                        ON curso.`id_curso` = turma.`id_curso`
                WHERE ";
        if ($semestre == 2) {
            $id_periodo_anterior = $id_periodo - 1;
            $sql .= " 
                    (
                        periodo.id_periodo = $id_periodo OR
                        (periodo.id_periodo = $id_periodo_anterior AND curso.modulo = 'Anual')
                    )
                    AND            
            ";           
        } else {
            $sql .= " periodo.id_periodo = $id_periodo AND ";
        }
        $sql .= "
                    usuario.id_usuario = $id_usuario
                ORDER BY
                    horario.id_hora,horario.id_dia;";
        //echo $sql;
        $stmt = $this->bd->prepare($sql);
        $stmt->execute() or die($this->bd->error);
        $result = $stmt->get_result();
        return $result;      
    }    

    public function getCargaHorariaEadDocente($id_periodo,$id_usuario) {

        $sql = "SELECT
                    curso.nome AS curso,
                    curso.id_curso,
                    usuario.`nome`,
                    turma.descricao AS turma,
                    oferta_disciplina.`chs` AS chs,
                    oferta_disciplina.`cht` AS cht,
                    disciplina.descricao AS disciplina,
                    oferta_disciplina.id_disciplina,
                    oferta_disciplina.id_turma
                FROM
                    oferta_disciplina INNER JOIN usuario 
                        ON oferta_disciplina.`id_usuario` = usuario.id_usuario
                    INNER JOIN disciplina
                        ON oferta_disciplina.`id_disciplina` = disciplina.`id_disciplina`
                    INNER JOIN turma
                        ON oferta_disciplina.id_turma = turma.id_turma
                   INNER JOIN curso
                        ON turma.id_curso = curso.id_curso   
                WHERE
                    turma.id_periodo = $id_periodo AND
                    oferta_disciplina.id_usuario = $id_usuario AND
                    curso.turno = 'EAD'
                ORDER BY
                    curso.nome 
                ";
        //echo $sql;
        $stmt = $this->bd->prepare($sql);
        $stmt->execute() or die($this->bd->error);
        $result = $stmt->get_result();
        return $result;        
    }    
    
    public function horarioDisciplina($id_oferta_disciplina,$id_periodo) {
        $sql = "SELECT  
                    disciplina.`descricao` AS disciplina,
                    dia.descricao AS dia,
                    IF (curso.turno = 'Noturno',
                        CONCAT(DATE_FORMAT(hora.`inicio_superior`,'%H:%i'),' - ',DATE_FORMAT(hora.`fim_superior`,'%H:%i')),
                            IF (curso.turno = 'Integral',
                                CONCAT(DATE_FORMAT(hora.`inicio_integrado`,'%H:%i'),' - ',DATE_FORMAT(hora.`fim_integrado`,'%H:%i')),
                                    CONCAT(DATE_FORMAT(hora.`inicio_concomitante`,'%H:%i'),' - ',DATE_FORMAT(hora.`fim_concomitante`,'%H:%i')))) AS horario,
                    sabados.`data` as sabado
                FROM
                    horario INNER JOIN dia
                        ON horario.`id_dia` = dia.`id_dia`
                    INNER JOIN hora
                        ON horario.`id_hora` = hora.`id_hora`
                    INNER JOIN sala
                        ON horario.`id_sala` = sala.`id_sala`
                    INNER JOIN oferta_disciplina
                        ON horario.`id_oferta_disciplina` = oferta_disciplina.`id_oferta_disciplina`
                    INNER JOIN usuario
                        ON oferta_disciplina.`id_usuario` = usuario.`id_usuario`
                    INNER JOIN disciplina
                        ON oferta_disciplina.`id_disciplina` = disciplina.`id_disciplina`
                    INNER JOIN turma
                        ON oferta_disciplina.`id_turma` = turma.`id_turma`
                    INNER JOIN periodo
                        ON turma.`id_periodo` = periodo.`id_periodo`
                    INNER JOIN curso
                        ON curso.`id_curso` = turma.`id_curso`
                    INNER JOIN sabados
                    	ON horario.id_dia = sabados.id_dia
                WHERE 
                    turma.id_periodo = $id_periodo AND
                    oferta_disciplina.id_oferta_disciplina = $id_oferta_disciplina
                ORDER BY
                    sabados.data,dia,horario";
        //echo $sql;
        $stmt = $this->bd->prepare($sql);
        $stmt->execute() or die($this->bd->error);
        $result = $stmt->get_result();
        return $result;      
    }    
    
    
}
