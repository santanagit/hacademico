<?php

require_once $_SESSION['diretorio_base'] . '/model/conexaoModel.php';

class disciplinaModel {

    private $bd;

    public function __construct() {
        $conexao = new conexaoModel();
        $this->bd = $conexao->getConexao();
    }

    public function getDisciplina($id_disciplina) {
        $sql = "SELECT * FROM disciplina WHERE disciplina.id_disciplina = ?";
        $stmt = $this->bd->prepare($sql);
        $stmt->bind_param("i", $id_disciplina);
        $stmt->execute() or die($this->bd->error);
        $result = $stmt->get_result();
        return $result;
    }

    public function listar($parametros = array(), $ordenacao = array(), $limit = array()) {

        $sql = "SELECT * FROM disciplina ";

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
            . "disciplina(id_disciplina,descricao,chs,cht,chs_ead) "
            . "VALUES "
            . "("
            . "{$campos['id_disciplina']},"
            . "'{$campos['descricao']}',"
            . "{$campos['chs']},"
            . "{$campos['cht']}," 
            . "{$campos['chs_ead']})";
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
        $sql = "UPDATE disciplina "
            . "SET "
            . "descricao = '{$campos['descricao']}', "
            . "chs = {$campos['chs']}, " 
            . "cht = {$campos['cht']}, "
            . "chs_ead = {$campos['chs_ead']} "
            . "WHERE id_disciplina = '{$campos['id_disciplina']}'";

        $stmt = $this->bd->prepare($sql);
        $result = $stmt->execute() or die($this->bd->error);
        return $result;
    }

    public function deletar($id_disciplina) {
        $sql = "DELETE FROM disciplina WHERE id_disciplina = $id_disciplina";
        $stmt = $this->bd->prepare($sql);
        $result = $stmt->execute() or die($this->bd->error);
        return $result;
    }

    public function existeVinculo($id_disciplina) {
        $sql = "SELECT id_grade FROM grade WHERE id_disciplina = $id_disciplina UNION ";
        $sql .= "SELECT id_oferta_disciplina FROM oferta_disciplina WHERE id_disciplina = $id_disciplina";
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

    public function existeDisciplina($descricao, $id_disciplina,$chs,$cht,$chs_ead) {
        $sql = "SELECT descricao FROM disciplina WHERE descricao = ? AND chs = ? AND cht = ? AND chs_ead = ?";
        $stmt = $this->bd->prepare($sql);
        $stmt->bind_param("sidi", $descricao, $chs,$cht,$chs_ead);
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
