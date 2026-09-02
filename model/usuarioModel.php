<?php

if (isset($_SESSION['diretorio_base'])) {
    require_once $_SESSION['diretorio_base'] . '/model/conexaoModel.php';
} else {
    require_once 'model/conexaoModel.php';
}

class usuarioModel {

    private $bd;

    public function __construct() {
        $conexao = new conexaoModel();
        $this->bd = $conexao->getConexao();
    }
    
    public function docentesRidPeriodo($id_periodo) {
        $sql = "SELECT
                    usuario.id_usuario,
                    usuario.nome,
                    periodo.id_periodo,
                    periodo.ano,
                    periodo.semestre
                FROM
                    usuario INNER JOIN pid
                        ON pid.id_usuario = usuario.id_usuario
                    INNER JOIN historico_pid
                        ON pid.id_pid = historico_pid.`id_pid`
                    INNER JOIN periodo
                        ON pid.id_periodo = periodo.id_periodo
                WHERE
                    historico_pid.etapa = 'RID' AND
                    historico_pid.situacao = 'APROVADO' AND
                    pid.id_periodo = $id_periodo
                ORDER BY
                    usuario.nome
               ";
        //echo '<pre>'.$sql;
        $stmt = $this->bd->prepare($sql);
        $stmt->execute() or die($this->bd->error);
        $result = $stmt->get_result();   
        return $result;
    }
    
    public function importar_aluno($campos) {

        $sql = "SELECT
                    aluno.id_usuario,
                    aluno.id_aluno,
                    aluno.id_curso,
                    curso.nome AS curso,
                    usuario.nome AS nome,
                    usuario.matricula,
                    usuario.cpf,
                    usuario.email,
                    usuario.ativo,
                    perfil.descricao AS perfil
                FROM
                    aluno INNER JOIN usuario
                        ON aluno.id_usuario = usuario.id_usuario
                    INNER JOIN curso
                        ON aluno.id_curso = curso.id_curso
                    INNER JOIN perfil
                        ON usuario.id_perfil = perfil.id_perfil
                WHERE
                    usuario.matricula = '{$campos['matricula']}'
                ";
        //echo '<pre>'.$sql;
        $stmt = $this->bd->prepare($sql);
        $stmt->execute() or die($this->bd->error);
        $result = $stmt->get_result();                        

        if (mysqli_num_rows($result) == 0) {        
        
            $this->bd->begin_transaction();
            try {
                $id_usuario = false;
                $id_aluno = false;
                $sql = "INSERT INTO
                            usuario(id_perfil,matricula,nome,email,ativo,senha,area,cpf)
                        VALUES
                        (
                            12,
                            '{$campos['matricula']}',
                            '".utf8_encode($campos['nome'])."',
                            '{$campos['email']}',
                            1,
                            '".md5($campos['matricula'])."',
                            'Setor de Ensino',
                            '{$campos['cpf']}'
                        )";
                //echo '<pre>'.$sql;
                $stmt = $this->bd->prepare($sql);
                $result = $stmt->execute() or die($this->bd->error);
                $id_usuario = mysqli_stmt_insert_id($stmt);

                $sql = "INSERT INTO 
                            aluno(id_usuario,id_curso) 
                        VALUES 
                        (
                            $id_usuario,
                            {$campos['id_curso']}
                        )";
                $stmt = $this->bd->prepare($sql);
                $result = $stmt->execute() or die($this->bd->error);
                $this->bd->commit();
                
            } catch (mysqli_sql_exception $exception) {
                $this->bd->rollback();
                throw $exception;
                return false;
            }
            return true;
        } else {
            return false;
        }
    }    

    public function getUsuario($email) {
        $sql = "SELECT
                    usuario.id_usuario,
                    usuario.`nome`,
                    usuario.`matricula`,
                    usuario.`email`,
                    usuario.`cor`,
                    usuario.`id_perfil`,
                    perfil.`descricao` AS perfil,
                    usuario.ativo,
                    usuario.cpf
                FROM 
                    usuario INNER JOIN `perfil`
                        ON usuario.`id_perfil` = perfil.`id_perfil`
                WHERE 
                    usuario.email = ?";
        $stmt = $this->bd->prepare($sql);
        $stmt->bind_param("s", $email);
        $stmt->execute() or die($this->bd->error);
        $result = $stmt->get_result();
        return $result;
    }

    public function getUsuarioSenha($campos) {
        $sql = "SELECT  
                    usuario.id_usuario,
                    usuario.`nome`,
                    usuario.`matricula`,
                    usuario.`email`,
                    usuario.`id_perfil`,
                    usuario.`cor`,
                    perfil.`descricao` AS perfil,
                    usuario.ativo,
                    usuario.cpf
                FROM 
                    usuario INNER JOIN `perfil`
                        ON usuario.`id_perfil` = perfil.`id_perfil`
                WHERE 
                    usuario.email = '{$campos['usuario']}' AND 
                    usuario.senha = '".md5($campos['senha'])."'";
        //echo $sql;             
        $stmt = $this->bd->prepare($sql);
        $stmt->execute() or die($this->bd->error);
        $result = $stmt->get_result();
        return $result;
    }
    
    
    public function getUsuarioId($id_usuario) {
        $sql = "SELECT  
                    usuario.`nome`,
                    usuario.`matricula`,
                    usuario.`email`,
                    usuario.`id_perfil`,
                    usuario.`id_usuario`,
                    usuario.`cor`,
                    perfil.`descricao` AS perfil,
                    usuario.ativo,
                    usuario.`area`,
                    usuario.cpf
                FROM 
                    usuario INNER JOIN `perfil`
                        ON usuario.`id_perfil` = perfil.`id_perfil`
                WHERE 
                    usuario.id_usuario = ?";
        $stmt = $this->bd->prepare($sql);
        $stmt->bind_param("i", $id_usuario);
        $stmt->execute() or die($this->bd->error);
        $result = $stmt->get_result();
        return $result;
    }

    public function listar($id_perfil,$parametros = array(), $ordenacao = array(), $limit = array()) {
        
        $sql = "SELECT usuario.*,perfil.descricao as perfil "
            . "FROM usuario INNER JOIN perfil "
            . "ON usuario.id_perfil = perfil.id_perfil";

        $sql .= " WHERE perfil.id_perfil = $id_perfil";
        
        if (count($parametros) > 0) {
            $i = 0;
            $sql .= ' AND (';
            foreach ($parametros as $key => $value) {
                if ($i > 0)
                    $sql .= " OR ";
                $sql .= "$key like '%$value%'";
                $i++;
            }
            $sql .= ')';
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
        //print_r($campos);
        $sql = "INSERT INTO "
            . "usuario(id_perfil,matricula,nome,email,ativo,cor,senha,cpf) "
            . "VALUES (?,?,?,?,?,?,?,?)";
        $stmt = $this->bd->prepare($sql);
        $stmt->bind_param("isssisss", $campos['id_perfil'], $campos['matricula'], $campos['nome'], $campos['email'], $campos['ativo'],$campos['cor'],md5($campos['senha']),$campos['cpf']);
        $result = $stmt->execute() or die($this->bd->error);
        if (!$result) {
            return false;
        } else {
            return mysqli_stmt_insert_id($stmt);
        }
    }

    public function atualizar($campos) {
        if (trim($campos['senha']) == '') {
            $sql = "UPDATE usuario "
                . "SET "
                . "id_perfil = {$campos['id_perfil']}, "
                . "matricula = '{$campos['matricula']}', "
                . "nome = '{$campos['nome']}', "
                . "email = '{$campos['email']}', "
                . "cor = '{$campos['cor']}', " 
                . "ativo = {$campos['ativo']}, " 
                . "cpf = '{$campos['cpf']}' "
                . "WHERE id_usuario = '{$campos['id_usuario']}'";
        } else {
            $sql = "UPDATE usuario "
                . "SET "
                . "id_perfil = {$campos['id_perfil']}, "
                . "matricula = '{$campos['matricula']}', "
                . "nome = '{$campos['nome']}', "
                . "email = '{$campos['email']}', "
                . "cor = '{$campos['cor']}', " 
                . "senha = '".md5($campos['senha'])."', "
                . "ativo = {$campos['ativo']}, " 
                . "cpf = '{$campos['cpf']}' "
                . "WHERE id_usuario = '{$campos['id_usuario']}'";
        }
        $stmt = $this->bd->prepare($sql);
        $result = $stmt->execute() or die($this->bd->error);
        return $result;
    }
    
    public function atualizarDados($campos) {
        $sql = "UPDATE usuario "
            . "SET "
            . "matricula = '{$campos['matricula']}', " 
            . "cpf = '{$campos['cpf']}', "
            . "nome = '{$campos['nome']}', "
            . "email = '{$campos['email']}', "
            . "senha = '".md5($campos['senha'])."' "
            . "WHERE id_usuario = '{$campos['id_usuario']}'";
        $stmt = $this->bd->prepare($sql);
        $result = $stmt->execute() or die($this->bd->error);
        return $result;
    }    

    public function deletar($id_usuario) {
        $sql = "DELETE FROM usuario WHERE id_usuario = $id_usuario";
        $stmt = $this->bd->prepare($sql);
        $result = $stmt->execute() or die($this->bd->error);
        return $result;
    }

    public function existeVinculo($id_usuario) {
        $sql = "SELECT id_curso FROM curso WHERE id_coordenador = $id_usuario UNION ";
        $sql .= "SELECT id_oferta_disciplina FROM oferta_disciplina WHERE id_usuario = $id_usuario";

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

}
