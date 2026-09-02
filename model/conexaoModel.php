<?php

if (isset($_SESSION['diretorio_base'])) {
    require_once $_SESSION['diretorio_base'].'/config/configuracao.php';
} else {
    require_once 'config/configuracao.php';
}
class conexaoModel {
    
    private $mysqli;
    
    public function __construct() {
        $this->mysqli = new mysqli(SERVIDOR, USUARIO, SENHA, BD);
        //die(SERVIDOR.' '.USUARIO.' '.SENHA.' '.BD);
        if ($this->mysqli->connect_errno) {
            die("Failed to connect to MySQL: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error);       
        } 
    
        if (!$this->mysqli->set_charset("utf8")) {
            printf("Error loading character set utf8: %s\n", $this->mysqli->error);
            exit();
        } else {
            //printf("Current character set: %s\n", $this->mysqli->character_set_name());
        }    
    }
    
    public function getConexao() {
        return $this->mysqli;
    }
    
    
}
