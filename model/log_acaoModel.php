<?php

if (isset($_SESSION['diretorio_base'])) {
    require_once $_SESSION['diretorio_base'].'/model/conexaoModel.php';
} else {
    require_once 'model/conexaoModel.php';
}

class log_acaoModel {

    private $bd;
    
    public function __construct() {
        $conexao = new conexaoModel();
        $this->bd = $conexao->getConexao();
    }
    
    public function inserir($campos) {
        $sql = "INSERT INTO "
        . "log_acao(id_usuario,acao,data_hora) "
        . "VALUES ({$campos['id_usuario']},'{$campos['acao']}','{$campos['data_hora']}')";

        $stmt = $this->bd->prepare($sql);
        $result = $stmt->execute() or die($this->bd->error);
        if (!$result) {
            return false;
        } else {
            return mysqli_stmt_insert_id($stmt); 
        }     
    }
        
}
