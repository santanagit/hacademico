<?php
    function formacao_academica($nivel,$titulos) {
        $tabela = '';
        
        $info = array();

        if (isset($titulos[0])) {
            for ($i = 0; isset($titulos[$i]); $i++) {
                $titulo = $titulos[$i];
                $atributos = $titulo['@attributes'];
                $tabela .= '<tr>';
                $tabela .= '<td width="20%">' . $atributos['ANO-DE-INICIO'] . ' - ' . $atributos['ANO-DE-CONCLUSAO'] . '</td>';
                $tabela .= '<td width="80%">';
                $tabela .= 'Curso ' . $nivel . ' em ' . $atributos['NOME-CURSO'] . '<br>';
                $tabela .= $atributos['NOME-INSTITUICAO'] . '<br>';
                $tabela .= '</td>';
                $tabela .= '</tr>';
            }
        } else {
            $atributos = $titulos['@attributes'];
            $tabela .= '<tr>';
            $tabela .= '<td width="20%">' . $atributos['ANO-DE-INICIO'] . ' - ' . $atributos['ANO-DE-CONCLUSAO'] . '</td>';
            $tabela .= '<td width="80%">';
            $tabela .= 'Curso ' . $nivel . ' em ' . $atributos['NOME-CURSO'] . '<br>';
            $tabela .= $atributos['NOME-INSTITUICAO'] . '<br>';
            $tabela .= '</td>';
            $tabela .= '</tr>';
        }
        
        return $tabela;
    }
?>
<!DOCTYPE html>
<html lang="pt-BR">
    <head>    
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta name="description" content="Reconhecimento de cursos">
        <meta name="author" content="Antonio Rafael Santana">

        <title>hAcademico</title>

        <!-- Bootstrap CSS file -->
        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">

        <!-- Jquery and Bootstrap Script files -->
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
        <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>

        <style>
            /* Style the button that is used to open and close the collapsible content */
            .collapsible {
                background-color: #F0F8FF;
                color: #444;
                cursor: pointer;
                padding: 18px;
                width: 100%;
                border: none;
                text-align: left;
                outline: none;
                font-size: 15px;
            }

            /* Add a background color to the button if it is clicked on (add the .active class with JS), and when you move the mouse over it (hover) */
            .active, .collapsible:hover {
                background-color: #E6E6FA;
            }

            /* Style the collapsible content. Note: hidden by default */
            .content {
                padding: 0 18px;
                display: none;
                overflow: hidden;
                background-color: #f1f1f1;
            }             
        </style>

    </head>
    <body>
        <?php
        ini_set('display_errors', 1);

        echo '<div class="container-fluid">';
        echo '<div class="alert alert-info text-center" style="margin-top:20px;">Informações extraídas do XML do '
        . 'currículo Lattes</div>';

        $professor = 'Antonio Rafael Santana';
        $xml = simplexml_load_file('lattes/' . $professor . '.xml');
        $json = json_encode($xml);
        $array = json_decode($json, TRUE);

        echo '
            <div class="panel panel-default">
                <div class="panel-heading">' . $professor . '</div>
                    <div class="panel-body">';


        //----------------------------------------------------------
        // DADOS GERAIS: 
        // * FORMACAO-ACADEMICA-TITULACAO
        // * ATUACOES-PROFISSIONAIS
        //----------------------------------------------------------
        $vetor_dados_gerais = $array['DADOS-GERAIS'];
        $vetor_formacao_academica = $vetor_dados_gerais['FORMACAO-ACADEMICA-TITULACAO'];
        
        $tabela = '';
        $tabela .= '<table class="table table-bordered">';
        $tabela .= '<tr>';
        $tabela .= '<th colspan="2">Formação Acadêmica/Titulação</th>';
        $tabela .= '</tr>';
        foreach ($vetor_formacao_academica as $nivel => $titulos) {
            $tabela .= formacao_academica($nivel,$titulos);
        }
        $tabela .= "</table>";
        
        echo $tabela;
    ?>
    </body>
    <script>
        var coll = document.getElementsByClassName("collapsible");
        var i;

        for (i = 0; i < coll.length; i++) {
            coll[i].addEventListener("click", function () {
                this.classList.toggle("active");
                var content = this.nextElementSibling;
                if (content.style.display === "block") {
                    content.style.display = "none";
                } else {
                    content.style.display = "block";
                }
            });
        }
    </script>    
</html>    