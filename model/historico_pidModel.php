<?php

require_once $_SESSION['diretorio_base'] . '/model/conexaoModel.php';

class historico_pidModel {

    private $bd;

    public function __construct() {
        $conexao = new conexaoModel();
        $this->bd = $conexao->getConexao();
    }

    public function getSituacao($id_pid, $etapa) {
        $sql = "SELECT 
                    MAX(id_historico_pid) as id_historico_pid 
                FROM 
                    historico_pid 
                WHERE 
                    historico_pid.id_pid = $id_pid AND 
                    etapa = '$etapa'";
        //echo $sql;
        $stmt = $this->bd->prepare($sql);
        $stmt->execute() or die($this->bd->error);
        $result = $stmt->get_result();
        $linha = mysqli_fetch_assoc($result);
        
        if ($linha['id_historico_pid'] != '') {
            $sql2 = "SELECT 
                    historico_pid.situacao,
                    historico_pid.data_situacao
                FROM 
                    historico_pid
                WHERE 
                    historico_pid.id_historico_pid = {$linha['id_historico_pid']}";

            $stmt2 = $this->bd->prepare($sql2);
            $stmt2->execute() or die($this->bd->error);
            $result2 = $stmt2->get_result();

            return $result2;
        } else {
            return false;
        }
    }

    public function listar($id_pid, $etapa, $parametros = array(), $ordenacao = array(), $limit = array()) {

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
                    historico_pid.etapa,
                    historico_pid.situacao,
                    historico_pid.data_situacao
                FROM 
                    pid INNER JOIN usuario 
                        ON pid.id_usuario = usuario.id_usuario
                    INNER JOIN periodo
                        ON pid.id_periodo = periodo.id_periodo
                    INNER JOIN historico_pid
                        ON pid.id_pid = historico_pid.id_pid
                 WHERE 
                    pid.id_pid = $id_pid AND etapa = '$etapa'    
                ";

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
        //echo "<pre>".$sql;
        $stmt = $this->bd->prepare($sql);
        $stmt->execute() or die($this->bd->error);
        $result = $stmt->get_result();
        return $result;
    }

    public function inserir($campos) {

        $sql = "INSERT INTO 
                    historico_pid(id_pid,etapa,situacao,data_situacao) 
                VALUES 
                (
                    {$campos['id_pid']},
                    '{$campos['etapa']}',
                    '{$campos['situacao']}',
                    '".date("Y-m-d H:i:s")."'
                )";
        $stmt = $this->bd->prepare($sql);
        $result = $stmt->execute() or die($this->bd->error);
        return $result;
    }
    
    public function inserirRID($campos) {
        
        $sql = '';
        $resultado = false;
        
        $this->bd->begin_transaction();
        try {
            $sql = "INSERT INTO 
                        historico_pid(id_pid,etapa,situacao,data_situacao) 
                    VALUES 
                    (
                        {$campos['id_pid']},
                        '{$campos['etapa']}',
                        '{$campos['situacao']}',
                        '".date("Y-m-d H:i:s")."'
                    )";
            $stmt = $this->bd->prepare($sql);
            $result = $stmt->execute() or die($this->bd->error);
            
            $sql = "INSERT INTO historico_atividade (id_atividade_docente,etapa,situacao,observacao,data_situacao,id_usuario_avaliador)
                    SELECT 
                            atividade_docente.id_atividade_docente,
                            'RID',
                            'AGUARDANDO AVALIAÇÃO',
                            'Atividade cadastrada no PID',
                            NOW(),
                            285
                    FROM 
                            atividade_docente INNER JOIN pid
                                    ON atividade_docente.id_pid = pid.id_pid
                            INNER JOIN historico_pid
                                    ON pid.id_pid = historico_pid.id_pid
                    WHERE 
                            pid.id_pid = {$campos['id_pid']} AND 
                            etapa = 'PID' AND 
                            situacao = 'APROVADO';";
            
            $stmt = $this->bd->prepare($sql);
            $result = $stmt->execute() or die($this->bd->error);
            
            $resultado = true;
            $this->bd->commit();
            
        } catch (mysqli_sql_exception $exception) {
            $this->bd->rollback();
            $resultado = false;
            throw $exception;
        }
        return $resultado;        
        
    }

    public function deletar($id_historico_pid) {
        $sql = "DELETE FROM historico_pid WHERE id_historico_pid = $id_historico_pid";
        $stmt = $this->bd->prepare($sql);
        $result = $stmt->execute() or die($this->bd->error);
        return $result;
    }

}
