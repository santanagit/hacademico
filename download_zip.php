<?php

require_once('controller/sessao.php');
sessao::validar(array('Coordenador de Ensino','Coordenador de Curso','Professor'));

if (isset($_GET['arquivo'])) {
    header("Content-Type: application/zip");
    header("Content-Transfer-Encoding: Binary");
    header("Cache-Control: no-cache");
    header("Content-length: " . filesize($_GET['arquivo']));
    header("Content-Disposition: attachment;filename=\"". basename($_GET['arquivo'])."\"");
    readfile($_GET['arquivo']);
} else {
    die('Acesso inválido!');
}