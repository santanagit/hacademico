<?php

//die('Em manutenção');
session_start();

class sessao {

    public static function validar($perfis) {
        
        if (!isset($_SESSION['ativo'])) { 
            header('Location: ./index.php?erro=1');
            
        } else {
            $acesso = false;
            foreach ($perfis as $perfil) {
               
                if ($perfil == $_SESSION['perfil']) {
                    $acesso = true;
                    break;
                }
                
            }
         
            if (!$acesso) {
                header('Location: ./index.php?erro=2');
            }
        }
    }
}
