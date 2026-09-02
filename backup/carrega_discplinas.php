<?php

$link = mysqli_connect('localhost', 'root', '', 'bd_hacademico') or die (mysqli_connect_error());
echo '<pre>';
$arquivo = fopen('disciplinas.csv', 'r');
$i = 0;
while (($linha = fgetcsv($arquivo)) !== false){
    if ($i > 0) {
        print_r($linha);
        $query = "SELECT * FROM disciplina WHERE id_disciplina = {$linha[0]}";
        $result = mysqli_query($link, $query) or die (mysqli_error($link));
        if (mysqli_num_rows($result) == 0) {
            $sql = "INSERT INTO disciplina (descricao,chs,cht,chs_ead) VALUES ('{$linha[1]}',{$linha[2]},{$linha[3]},{$linha[4]})";
            echo "\nExecutando query insert... ";
            $result_insert = mysqli_query($link,$sql) or die ("Falhou! \n\n".mysqli_error($link));
            echo "Ok!\n\n";
        } else {
            $sql = "UPDATE disciplina SET descricao = '{$linha[1]}',chs = {$linha[2]}, cht = {$linha[3]}, chs_ead = {$linha[4]} WHERE id_disciplina = {$linha[0]}";
            echo "\nExecutando query update... ";
            $result_update = mysqli_query($link,$sql) or die ("Falhou! \n\n".mysqli_error($link));
            echo "Ok!\n\n";
            
        }
    }
    $i++;
}
fclose($arquivo);




