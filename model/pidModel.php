<?php

require_once $_SESSION['diretorio_base'] . '/model/conexaoModel.php';

class pidModel {

    private $bd;

    public function __construct() {
        $conexao = new conexaoModel();
        $this->bd = $conexao->getConexao();
    }

    public function getPid($id_pid) {
        $sql = "SELECT * FROM pid WHERE pid.id_pid = ?";
        $stmt = $this->bd->prepare($sql);
        $stmt->bind_param("i", $id_pid);
        $stmt->execute() or die($this->bd->error);
        $result = $stmt->get_result();
        return $result;
    }
    
    public function getPidPeriodoProfessor($id_periodo,$id_usuario) {
        $sql = "SELECT * FROM pid WHERE pid.id_periodo = ? AND pid.id_usuario = ?";
        $stmt = $this->bd->prepare($sql);
        $stmt->bind_param("ii", $id_periodo,$id_usuario);
        $stmt->execute() or die($this->bd->error);
        $result = $stmt->get_result();
        return $result;
    }    

    public function listar($id_periodo, $etapa, $parametros = array(), $ordenacao = array()) {

        $sql = "SELECT 
                    pid.id_usuario,
                    pid.id_pid,
                    usuario.nome AS professor,
                    periodo.ano,
                    periodo.semestre,
                    pid.pid_correcao_inicio,
                    pid.pid_correcao_fim,
                    pid.rid_correcao_inicio,
                    pid.rid_correcao_fim,
                    DATE_FORMAT(pid.pid_correcao_inicio,'%d/%m/%Y') AS pid_correcao_inicio_formatado,
                    DATE_FORMAT(pid.pid_correcao_fim,'%d/%m/%Y') AS    pid_correcao_fim_formatado,
                    DATE_FORMAT(pid.rid_correcao_inicio,'%d/%m/%Y') AS rid_correcao_inicio_formatado,
                    DATE_FORMAT(pid.rid_correcao_fim,'%d/%m/%Y') AS    rid_correcao_fim_formatado,
                    MAX(id_historico_pid) AS id_historico_pid
                FROM 
                    pid INNER JOIN usuario 
                        ON pid.id_usuario = usuario.id_usuario
                    INNER JOIN periodo
                        ON pid.id_periodo = periodo.id_periodo
                    INNER JOIN historico_pid
                        ON pid.id_pid = historico_pid.id_pid
                 WHERE 
                    pid.id_periodo = $id_periodo AND
                    historico_pid.etapa = '$etapa'
                GROUP BY
                    pid.id_pid                        
                ";
        //echo $sql;
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

        //echo "<pre>".$sql;
        $stmt = $this->bd->prepare($sql);
        $stmt->execute() or die($this->bd->error);
        $result = $stmt->get_result();
        return $result;
    }

    public function inserir($campos) {
        $sql = '';
        $id_pid = '';

        $this->bd->begin_transaction();
        try {
            $sql = "INSERT INTO
                        pid(id_usuario,id_periodo)
                    VALUES
                    (
                        {$campos['id_usuario']},
                        {$campos['id_periodo']}
                    )";
            //echo '<pre>'.$sql;
            $stmt = $this->bd->prepare($sql);
            $result = $stmt->execute() or die($this->bd->error);
            $id_pid = mysqli_stmt_insert_id($stmt);
            
            $sql = "INSERT INTO 
                        historico_pid(id_pid,etapa,situacao,data_situacao) 
                    VALUES 
                    (
                        $id_pid,
                        'PID',    
                        'AGUARDANDO ENVIO',
                        '".date('Y-m-d H:i:s')."'
                    )";
            $stmt = $this->bd->prepare($sql);
            $result = $stmt->execute() or die($this->bd->error);
            $this->bd->commit();
            
        } catch (mysqli_sql_exception $exception) {
            $this->bd->rollback();
            $id_pid = false;
            throw $exception;
        }
        return $id_pid;
    }
    
    public function atualizar_correcao_pid($campos) {
        $sql = "UPDATE pid 
                SET 
                    pid_correcao_inicio = '{$campos['pid_correcao_inicio']}',
                    pid_correcao_fim = '{$campos['pid_correcao_fim']}'
                WHERE 
                    id_pid = '{$campos['id_pid']}'";

        $stmt = $this->bd->prepare($sql);
        $result = $stmt->execute() or die($this->bd->error);
        return $result;
    }

    public function atualizar_correcao_rid($campos) {
        $sql = "UPDATE pid 
                SET 
                    rid_correcao_inicio = '{$campos['rid_correcao_inicio']}',
                    rid_correcao_fim = '{$campos['rid_correcao_fim']}'
                WHERE 
                    id_pid = '{$campos['id_pid']}'";

        $stmt = $this->bd->prepare($sql);
        $result = $stmt->execute() or die($this->bd->error);
        return $result;
    }    

    public function atualizar($campos) {
        $sql = "UPDATE pid "
                . "SET "
                . "id_usuario = {$campos['id_usuario']},"
                . "pid_correcao_inicio = '{$campos['pid_correcao_inicio']}', "
                . "pid_correcao_fim = '{$campos['pid_correcao_fim']}', "
                . "rid_correcao_inicio = '{$campos['rid_correcao_inicio']}', "
                . "rid_correcao_fim = '{$campos['rid_correcao_fim']}' "
                . "WHERE id_pid = '{$campos['id_pid']}'";

        $stmt = $this->bd->prepare($sql);
        $result = $stmt->execute() or die($this->bd->error);
        return $result;
    }

    public function deletar($id_pid) {
        $sql = "DELETE FROM pid WHERE id_pid = $id_pid";
        $stmt = $this->bd->prepare($sql);
        $result = $stmt->execute() or die($this->bd->error);
        return $result;
    }

    public function existeVinculo($id_pid) {
        $sql = "SELECT id_atividade_docente FROM atividade_docente WHERE id_pid = $id_pid";
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
