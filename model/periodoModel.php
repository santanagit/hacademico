<?php

require_once $_SESSION['diretorio_base'] . '/model/conexaoModel.php';

class periodoModel {

    private $bd;

    public function __construct() {
        $conexao = new conexaoModel();
        $this->bd = $conexao->getConexao();
    }

    public function listar($parametros = array(), $ordenacao = array(), $limit = array()) {

        $sql = "SELECT
                    id_periodo,
                    ano,
                    semestre,
                    DATE_FORMAT(data_inicio,'%d/%m/%Y') as data_inicio,
                    DATE_FORMAT(data_fim,'%d/%m/%Y') as data_fim,
                    DATE_FORMAT(pid_inicio,'%d/%m/%Y') as pid_inicio,
                    DATE_FORMAT(pid_fim,'%d/%m/%Y') as pid_fim,
                    DATE_FORMAT(rid_inicio,'%d/%m/%Y') as rid_inicio,
                    DATE_FORMAT(rid_fim,'%d/%m/%Y') as rid_fim,
                    publicado
                FROM periodo ";

        if (count($parametros) > 0) {
            $i = 0;
            $sql .= ' WHERE ';
            foreach ($parametros as $key => $value) {
                if ($i > 0)
                    $sql .= " OR ";
                $sql .= "$key like '%$value%'";
                $i++;
            }
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

        $stmt = $this->bd->prepare($sql);
        $stmt->execute() or die($this->bd->error);
        $result = $stmt->get_result();
        return $result;
    }

    public function inserir($campos) {
        $sql = "INSERT INTO periodo(ano,semestre,data_inicio,data_fim,pid_inicio,pid_fim,rid_inicio,rid_fim,publicado) VALUES (?,?,?,?,?,?,?,?,?)";
        $stmt = $this->bd->prepare($sql);
        $stmt->bind_param("iissssssi",
                $campos['ano'],
                $campos['semestre'],
                $campos['data_inicio'],
                $campos['data_fim'],
                $campos['pid_inicio'],
                $campos['pid_fim'],
                $campos['rid_inicio'],
                $campos['rid_fim'],
                $campos['publicado']
        );
        $result = $stmt->execute() or die($this->bd->error);
        if (!$result) {
            return false;
        } else {
            return mysqli_stmt_insert_id($stmt);
        }
    }

    public function atualizar($campos) {
        $sql = "UPDATE periodo "
                . "SET "
                . "ano = ? ,"
                . "semestre = ? ,"
                . "data_inicio = ? ,"
                . "data_fim = ? ,"
                . "pid_inicio = ? ,"
                . "pid_fim = ? ,"
                . "rid_inicio = ? ,"
                . "rid_fim = ? ,"
                . "publicado = ? "
                . "WHERE id_periodo = ?";
        $stmt = $this->bd->prepare($sql);
        $stmt->bind_param("iissssssii",
                $campos['ano'],
                $campos['semestre'],
                $campos['data_inicio'],
                $campos['data_fim'],
                $campos['pid_inicio'],
                $campos['pid_fim'],
                $campos['rid_inicio'],
                $campos['rid_fim'],
                $campos['publicado'],
                $campos['id_periodo']
        );
        $result = $stmt->execute() or die($this->bd->error);
        return $result;
    }

    public function deletar($id_periodo) {
        $sql = "DELETE FROM periodo WHERE id_periodo = ?";
        $stmt = $this->bd->prepare($sql);
        $stmt->bind_param("i", $id_periodo);
        $result = $stmt->execute() or die($this->bd->error);
        return $result;
    }

    public function getPeriodoAtual() {
        $sql = "SELECT
                    id_periodo,
                    ano,
                    semestre,
                    data_inicio,
                    data_fim,
                    pid_inicio,
                    pid_fim,
                    rid_inicio,
                    rid_fim,
                    DATE_FORMAT(data_inicio,'%d/%m/%Y') as data_inicio_formatado,
                    DATE_FORMAT(data_fim,'%d/%m/%Y') as data_fim_formatado,
                    DATE_FORMAT(pid_inicio,'%d/%m/%Y') as pid_inicio_formatado,
                    DATE_FORMAT(pid_fim,'%d/%m/%Y') as pid_fim_formatado,
                    DATE_FORMAT(rid_inicio,'%d/%m/%Y') as rid_inicio_formatado,
                    DATE_FORMAT(rid_fim,'%d/%m/%Y') as rid_fim_formatado,
                    publicado
                FROM 
                    periodo 
                WHERE 
                    data_inicio <= '" . date("Y-m-d") . "' AND
                    data_fim >= '" . date("Y-m-d") . "'";
        //echo $sql;
        $stmt = $this->bd->prepare($sql);
        $stmt->execute() or die($this->bd->error);
        $result = $stmt->get_result();

        if (mysqli_num_rows($result) == 0) {
            $sql = "SELECT
                    id_periodo,
                    ano,
                    semestre,
                    data_inicio,
                    data_fim,
                    pid_inicio,
                    pid_fim,
                    rid_inicio,
                    rid_fim,
                    DATE_FORMAT(data_inicio,'%d/%m/%Y') as data_inicio_formatado,
                    DATE_FORMAT(data_fim,'%d/%m/%Y') as data_fim_formatado,
                    DATE_FORMAT(pid_inicio,'%d/%m/%Y') as pid_inicio_formatado,
                    DATE_FORMAT(pid_fim,'%d/%m/%Y') as pid_fim_formatado,
                    DATE_FORMAT(rid_inicio,'%d/%m/%Y') as rid_inicio_formatado,
                    DATE_FORMAT(rid_fim,'%d/%m/%Y') as rid_fim_formatado,
                    publicado
                FROM 
                    periodo 
                ORDER BY
                    id_periodo DESC
                LIMIT 0,1";
            $stmt = $this->bd->prepare($sql);
            $stmt->execute() or die($this->bd->error);
            $result = $stmt->get_result();            
        }

        return $result;
    }

    public function getPeriodo($id_periodo) {
        $sql = "SELECT
                    id_periodo,
                    ano,
                    semestre,
                    data_inicio,
                    data_fim,
                    pid_inicio,
                    pid_fim,
                    rid_inicio,
                    rid_fim,
                    DATE_FORMAT(data_inicio,'%d/%m/%Y') as data_inicio_formatado,
                    DATE_FORMAT(data_fim,'%d/%m/%Y') as data_fim_formatado,
                    DATE_FORMAT(pid_inicio,'%d/%m/%Y') as pid_inicio_formatado,
                    DATE_FORMAT(pid_fim,'%d/%m/%Y') as pid_fim_formatado,
                    DATE_FORMAT(rid_inicio,'%d/%m/%Y') as rid_inicio_formatado,
                    DATE_FORMAT(rid_fim,'%d/%m/%Y') as rid_fim_formatado,
                    publicado
                FROM periodo WHERE id_periodo = $id_periodo";
        $stmt = $this->bd->prepare($sql);
        $stmt->execute() or die($this->bd->error);
        $result = $stmt->get_result();
        return $result;
    }

    public function existeVinculo($id_periodo) {

        $sql = "SELECT id_turma FROM turma WHERE id_periodo = $id_periodo";
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

}
