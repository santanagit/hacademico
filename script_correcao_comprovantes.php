<?php
error_reporting(E_ALL ^ E_NOTICE);
ini_set('display_errors', 1);

$mysql = mysqli_connect('localhost', 'root', '', 'bd_hacademico');

$sql = "  
        SELECT
            atividade_docente.id_atividade_docente,
            pid.id_pid,
            usuario.nome,
            periodo.id_periodo,
            periodo.ano,
            periodo.semestre,
            periodo.data_inicio,
            periodo.data_fim,
            atividade_docente.descricao,
            atividade_docente.id_comprovante,
            comprovante.inicio_vigencia,
            comprovante.fim_vigencia
        FROM 
            atividade_docente INNER JOIN pid
                ON atividade_docente.id_pid = pid.id_pid
            INNER JOIN periodo
                ON pid.id_periodo = periodo.id_periodo
            INNER JOIN comprovante
                ON atividade_docente.id_comprovante = comprovante.id_comprovante
            INNER JOIN usuario
                ON usuario.id_usuario = pid.id_usuario
        WHERE
            #atividade_docente.descricao LIKE '%NDE%' AND (atividade_docente.descricao LIKE '%TGA%' OR atividade_docente.descricao LIKE '%Gestão%')
            #atividade_docente.descricao LIKE '%NDE%' AND (atividade_docente.descricao LIKE '%Analise%' OR atividade_docente.descricao LIKE '%ADS%')
            #atividade_docente.descricao LIKE '%NDE%' AND (atividade_docente.descricao LIKE '%TGA%' OR atividade_docente.descricao LIKE '%Gestão Ambiental%')
            #atividade_docente.descricao LIKE '%colegiado%' AND atividade_docente.descricao LIKE '%Administração%'
            #atividade_docente.descricao LIKE '%colegiado%' AND (atividade_docente.descricao LIKE '%Analise%' OR atividade_docente.descricao LIKE '%ADS%')
            #atividade_docente.descricao LIKE '%colegiado%' AND atividade_docente.descricao LIKE '%Informática%'
            #atividade_docente.descricao LIKE '%colegiado%' AND atividade_docente.descricao LIKE '%Meio Ambiente%'
            #atividade_docente.descricao LIKE '%colegiado%' AND (atividade_docente.descricao LIKE '%TGA%' OR atividade_docente.descricao LIKE '%Gestão Ambiental%')
            #AND 
            #(
                -- Comprovante terminou antes do início do período
                comprovante.fim_vigencia < periodo.data_inicio

                OR

                -- Comprovante começou depois do fim do período
                comprovante.inicio_vigencia > periodo.data_fim
            #)
        ORDER BY
            periodo.ano,
            periodo.semestre,
            atividade_docente.descricao;    
";

$updates = array();

$result = mysqli_query($mysql, $sql);
if (mysqli_num_rows($result) > 0) {
    $tabela = '<table align="center" border="1">';
    $tabela .= '<tr>';
    
    $tabela .= '<td>ID Atividade Docente</td>';
    $tabela .= '<td>ID PID</td>';
    $tabela .= '<td>Nome</td>';
    $tabela .= '<td>ID Periodo</td>';
    $tabela .= '<td>Ano</td>';
    $tabela .= '<td>Semestre</td>';
    $tabela .= '<td>Início Periodo</td>';
    $tabela .= '<td>Fim Periodo</td>';
    $tabela .= '<td>Atividade</td>';
    $tabela .= '<td>ID Comprovante</td>';
    $tabela .= '<td>Início Vigência Comprovante</td>';
    $tabela .= '<td>Fim Vigência Comprovante</td>';
    
    $tabela .= '<td>Comprovante correto</td>';
    
    
    $tabela .= '</tr>';
    while ($linha = mysqli_fetch_assoc($result)) {
        $tabela .= '<tr>';
        $tabela .= '<td>'.$linha['id_atividade_docente'].'</td>';
        $tabela .= '<td>'.$linha['id_pid'].'</td>';
        $tabela .= '<td>'.$linha['nome'].'</td>';
        $tabela .= '<td>'.$linha['id_periodo'].'</td>';
        $tabela .= '<td>'.$linha['ano'].'</td>';
        $tabela .= '<td>'.$linha['semestre'].'</td>';
        $tabela .= '<td>'.$linha['data_inicio'].'</td>';
        $tabela .= '<td>'.$linha['data_fim'].'</td>';
        $tabela .= '<td>'.$linha['descricao'].'</td>';
        $tabela .= '<td>'.$linha['id_comprovante'].'</td>';        
        $tabela .= '<td>'.$linha['inicio_vigencia'].'</td>';
        $tabela .= '<td>'.$linha['fim_vigencia'].'</td>';
        
        $sql2 = "SELECT 
                    comprovante.id_comprovante,
                    comprovante.inicio_vigencia,
                    comprovante.fim_vigencia,
                    comprovante.descricao
                FROM comprovante
                WHERE
                    #(
                        #comprovante.descricao LIKE '%NDE%' AND (comprovante.descricao LIKE '%ADS%' OR comprovante.descricao LIKE '%Analise%')
                        #comprovante.descricao LIKE '%NDE%' AND (comprovante.descricao LIKE '%TGA%' OR comprovante.descricao LIKE '%Gestão%')
                        #comprovante.descricao LIKE '%colegiado%' AND comprovante.descricao LIKE '%administração%'
                        #comprovante.descricao LIKE '%colegiado%' AND (comprovante.descricao LIKE '%ADS%' OR comprovante.descricao LIKE '%Analise%')
                        #comprovante.descricao LIKE '%colegiado%' AND comprovante.descricao LIKE '%Informática%'
                        #comprovante.descricao LIKE '%colegiado%' AND comprovante.descricao LIKE '%Meio Ambiente%'
                        #comprovante.descricao LIKE '%colegiado%' AND (comprovante.descricao LIKE '%TGA%' OR comprovante.descricao LIKE '%Gestão Ambiental%')
                    )
                    #AND 
                    '{$linha['data_inicio']}' >= comprovante.inicio_vigencia
                    AND '{$linha['data_inicio']}' <= comprovante.fim_vigencia                           
";
        //die("<pre>".$sql2);
        $result2 = mysqli_query($mysql, $sql2);
        $registros = mysqli_num_rows($result2);
        if ($registros == 0) {
            $tabela .= '<td>Não encontrado</td>';
        } else {
            $tabela .= '<td>';
            $tabela .= '<table align="center" border="1">';
            while ($linha2 = mysqli_fetch_assoc($result2)) {
                
                $update = "UPDATE atividade_docente SET id_comprovante = {$linha2['id_comprovante']} WHERE id_atividade_docente = {$linha['id_atividade_docente']}";
                $updates[] = $update;
                
                $tabela .= '<tr>';
                $tabela .= '<td>'.$linha2['id_comprovante'].'</td>';
                $tabela .= '<td>'.$linha2['inicio_vigencia'].'</td>';
                $tabela .= '<td>'.$linha2['fim_vigencia'].'</td>';
                $tabela .= '<td>'.$linha2['descricao'].'</td>';
                $tabela .= '</tr>';
            }
            $tabela .= '</table>';
            $tabela .= '</td>';
        }
            
        $tabela .= '</tr>';        
    }
    $tabela .= '</table>';

    echo $tabela;
    
    foreach ($updates as $valor) {
        echo $valor.";<br>";
    }
}