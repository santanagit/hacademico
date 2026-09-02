<?php
require_once $_SESSION['diretorio_base'].'/model/conexaoModel.php';

class salaModel {
    
    private $bd;
    
    public function __construct() {
        $conexao = new conexaoModel();
        $this->bd = $conexao->getConexao();
    }
        
    public function listar() {
        
        $sql = "SELECT * FROM sala ORDER BY descricao";   
        $stmt = $this->bd->prepare($sql);
        $stmt->execute() or die($this->bd->error);
        $result = $stmt->get_result();
        return $result;        
    }

}