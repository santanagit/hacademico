<?php

require_once('controller/sessao.php');
sessao::validar(array('Coordenador de Ensino','Coordenador de Curso','Professor'));

if (isset($_GET['id_comprovante'])) {
    header('Content-type: application/pdf');
    header("Cache-Control: no-cache");
    header("Content-Disposition: attachment;filename=documento.pdf");
    header("Content-length: " . filesize($_SESSION['diretorio_base'].'/comprovantes/comprovante_'.$_GET['id_comprovante'].'.pdf'));
    readfile($_SESSION['diretorio_base'].'/comprovantes/comprovante_'.$_GET['id_comprovante'].'.pdf');
} else {
    die('Acesso inválido!');
}