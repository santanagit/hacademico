<?php

require_once $_SESSION['diretorio_base'] . '/model/conexaoModel.php';

class atividadeModel {

    private $bd;

    public function __construct() {
        $conexao = new conexaoModel();
        $this->bd = $conexao->getConexao();
    }

    public function getAtividade($id_atividade) {
        $sql = "SELECT * FROM atividade WHERE atividade.id_atividade = ? ORDER BY descricao";
        $stmt = $this->bd->prepare($sql);
        $stmt->bind_param("i", $id_atividade);
        $stmt->execute() or die($this->bd->error);
        $result = $stmt->get_result();
        return $result;
    }

    public function getAtividadeTipo($id_tipo_atividade) {
        $sql = "SELECT * FROM atividade WHERE id_tipo_atividade = ?";
        $stmt = $this->bd->prepare($sql);
        $stmt->bind_param("i", $id_tipo_atividade);
        $stmt->execute() or die($this->bd->error);
        $result = $stmt->get_result();
        return $result;        
    }    
    
    public function listar($parametros = array(), $ordenacao = array(), $limit = array()) {

        $sql = "SELECT 
                    atividade.id_tipo_atividade,
                    atividade.id_atividade,
                    tipo_atividade.descricao AS tipo_atividade,
                    atividade.`descricao` AS descricao
                FROM 
                    atividade INNER JOIN tipo_atividade 
                        ON atividade.id_tipo_atividade = tipo_atividade.id_tipo_atividade";

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
        //echo $sql;
        $stmt = $this->bd->prepare($sql);
        $stmt->execute() or die($this->bd->error);
        $result = $stmt->get_result();
        return $result;
    }

    public function inserir($campos) {
        $sql = "INSERT INTO "
            . "atividade(id_tipo_atividade,descricao) "
            . "VALUES "
            . "("
            . "{$campos['id_tipo_atividade']},"
            . "'{$campos['descricao']}')";
                
        //echo $sql;
        $stmt = $this->bd->prepare($sql);
        $result = $stmt->execute() or die($this->bd->error);
        if (!$result) {
            return false;
        } else {
            return mysqli_stmt_insert_id($stmt);
        }
    }

    public function atualizar($campos) {
        $sql = 
              "UPDATE atividade "
            . "SET "
            . "id_tipo_atividade = {$campos['id_tipo_atividade']} "
            . "descricao = '{$campos['descricao']}' "
            . "WHERE id_atividade = '{$campos['id_atividade']}'";

        $stmt = $this->bd->prepare($sql);
        $result = $stmt->execute() or die($this->bd->error);
        return $result;
    }

    public function deletar($id_atividade) {
        $sql = "DELETE FROM atividade WHERE id_atividade = $id_atividade";
        $stmt = $this->bd->prepare($sql);
        $result = $stmt->execute() or die($this->bd->error);
        return $result;
    }

    public function existeVinculo($id_atividade) {
        $sql = "SELECT id_atividade_docente FROM atividade_docente WHERE id_atividade = $id_atividade"; 
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
