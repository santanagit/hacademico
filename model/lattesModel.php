<?php

require_once $_SESSION['diretorio_base'] . '/model/conexaoModel.php';

class lattesModel {

    private $bd;

    public function __construct() {
        $conexao = new conexaoModel();
        $this->bd = $conexao->getConexao();
    }

    public function getLattes($id_lattes) {
        $sql = "SELECT * FROM lattes WHERE id_lattes = ?";
        $stmt = $this->bd->prepare($sql);
        $stmt->bind_param("i", $id_lattes);
        $stmt->execute() or die($this->bd->error);
        $result = $stmt->get_result();
        return $result;
    }

    public function listar($id_usuario) {

        $sql = "SELECT * FROM lattes WHERE id_usuario = $id_usuario ORDER BY categoria, sub_categoria, ano, descricao";

        //echo $sql;
        $stmt = $this->bd->prepare($sql);
        $stmt->execute() or die($this->bd->error);
        $result = $stmt->get_result();
        return $result;
    }

    public function inserir($campos) {
        $sql = "INSERT INTO "
            . "lattes(id_usuario,ano,ano_fim,categoria,sub_categoria,descricao) "
            . "VALUES "
            . "("
            . "{$campos['id_usuario']},"
            . "{$campos['ano']},"
            . "{$campos['ano_fim']},"
            . "'{$campos['categoria']}',"
            . "'{$campos['sub_categoria']}'," 
            . "'{$campos['descricao']}'
                )";
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
        $sql = "UPDATE lattes "
            . "SET "
            . "id_usuario = {$campos['id_usuario']}, "
            . "ano = {$campos['ano']}, " 
            . "ano_fim = {$campos['ano_fim']}, "
            . "categoria = '{$campos['categoria']}', "
            . "sub_categoria = '{$campos['sub_categoria']}', "
            . "descricao = '{$campos['descricao']}' "
            . "WHERE id_lattes = '{$campos['id_lattes']}'";

        $stmt = $this->bd->prepare($sql);
        $result = $stmt->execute() or die($this->bd->error);
        return $result;
    }

    public function deletar($id_lattes) {
        $sql = "DELETE FROM lattes WHERE id_lattes = $id_lattes";
        $stmt = $this->bd->prepare($sql);
        $result = $stmt->execute() or die($this->bd->error);
        return $result;
    }

    public function existeLattes($id_usuario, $ano,$ano_fim,$categoria,$sub_categoria,$descricao) {
        $sql = "SELECT * FROM lattes WHERE id_usuario = ? AND ano = ? AND ano_fim = ? AND categoria = ? AND sub_categoria = ? AND descricao = ?";
        $stmt = $this->bd->prepare($sql);
        $stmt->bind_param("iiisss", $id_usuario, $ano,$ano_fim,$categoria,$sub_categoria,$descricao);
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
