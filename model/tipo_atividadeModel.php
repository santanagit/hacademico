<?php

require_once $_SESSION['diretorio_base'] . '/model/conexaoModel.php';

class tipo_atividadeModel {

    private $bd;

    public function __construct() {
        $conexao = new conexaoModel();
        $this->bd = $conexao->getConexao();
    }

    public function getTipoAtividade($id_tipo_atividade) {
        $sql = "SELECT * FROM tipo_atividade WHERE id_tipo_atividade = ?";
        $stmt = $this->bd->prepare($sql);
        $stmt->bind_param("i", $id_atividade);
        $stmt->execute() or die($this->bd->error);
        $result = $stmt->get_result();
        return $result;
    }

    public function listar($parametros = array(), $ordenacao = array(), $limit = array()) {

        $sql = "SELECT * FROM tipo_atividade";

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

}
