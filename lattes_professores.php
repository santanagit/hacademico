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
        if ($handle = opendir('lattes')) {

            echo '<div class="container-fluid">';
            echo '<div class="alert alert-info text-center" style="margin-top:20px;">Informações extraídas do XML do '
            . 'currículo Lattes</div>';

            while ($entry = readdir($handle)) {

                $types = array('xml');
                $ext = strtolower(pathinfo($entry, PATHINFO_EXTENSION));
                if (in_array($ext, $types)) {

                    $professor = pathinfo($entry, PATHINFO_FILENAME);
                    $xml = simplexml_load_file('lattes/' . $professor . '.xml');
                    $json = json_encode($xml);
                    $array = json_decode($json, TRUE);

                    echo '
                        <div class="panel panel-default">
                            <div class="panel-heading">' . $professor . '</div>
                                <div class="panel-body">';

                    
                    //----------------------------------------------------------
                    // DADOS GERAIS: (VETOR ABAIXO DA RAIZ):
                    // - FORMAÇÃO ACADÊMICA/TITULAÇÃO
                    // - ATUAÇÕES PROFISSONAIS
                    //----------------------------------------------------------
                    $vetor_dados_gerais = $array['DADOS-GERAIS'];


                    //----------------------------------------------------------
                    // AQUI COMEÇA A EXTRAÇÃO DA FORMAÇÃO ACADÊMICA/TITULAÇÃO
                    // FICA DENTRO DE DADOS GERAIS NO VETOR RAIZ
                    //----------------------------------------------------------
                    $vetor_formacao_academica = $vetor_dados_gerais['FORMACAO-ACADEMICA-TITULACAO'];

                    echo '<table class="table table-bordered">';
                    echo '<tr>';
                    echo '<th colspan="2">Formação Acadêmica/Titulação</th>';
                    echo '</tr>';
                    foreach ($vetor_formacao_academica as $nivel => $titulos) {

                        $info = array();
                        $formacao = '';
                        if ($nivel == 'CURSO-TECNICO-PROFISSIONALIZANTE') {
                            $formacao = 'Técnico Profissionalizante';
                            if (isset($titulos[0])) {
                                for ($i = 0; isset($titulos[$i]); $i++) {
                                    $titulo = $titulos[$i];
                                    $atributos = $titulo['@attributes'];
                                    echo '<tr>';
                                    echo '<td width="20%">' . $atributos['ANO-DE-INICIO'] . ' - ' . $atributos['ANO-DE-CONCLUSAO'] . '</td>';
                                    echo '<td width="80%">';
                                    echo 'Curso ' . $formacao . ' em ' . $atributos['NOME-CURSO'] . '<br>';
                                    echo $atributos['NOME-INSTITUICAO'] . '<br>';
                                    echo '</td>';
                                    echo '</tr>';
                                }
                            } else {
                                $atributos = $titulos['@attributes'];
                                echo '<tr>';
                                echo '<td width="20%">' . $atributos['ANO-DE-INICIO'] . ' - ' . $atributos['ANO-DE-CONCLUSAO'] . '</td>';
                                echo '<td width="80%">';
                                echo 'Curso ' . $formacao . ' em ' . $atributos['NOME-CURSO'] . '<br>';
                                echo $atributos['NOME-INSTITUICAO'] . '<br>';
                                echo '</td>';
                                echo '</tr>';
                            }
                        } else if ($nivel == 'GRADUACAO') {
                            $formacao = 'Graduação';
                            if (isset($titulos[0])) {
                                for ($i = 0; isset($titulos[$i]); $i++) {
                                    $titulo = $titulos[$i];
                                    $atributos = $titulo['@attributes'];
                                    echo '<tr>';
                                    echo '<td width="20%">' . $atributos['ANO-DE-INICIO'] . ' - ' . $atributos['ANO-DE-CONCLUSAO'] . '</td>';
                                    echo '<td width="80%">';
                                    echo $formacao . ' em ' . $atributos['NOME-CURSO'] . '<br>';
                                    echo $atributos['NOME-INSTITUICAO'] . '<br>';
                                    echo 'Título: ' . $atributos['TITULO-DO-TRABALHO-DE-CONCLUSAO-DE-CURSO'] . '<br>';
                                    echo 'Orientador: ' . $atributos['NOME-DO-ORIENTADOR'] . '<br>';
                                    echo '</td>';
                                    echo '</tr>';
                                }
                            } else {
                                $atributos = $titulos['@attributes'];
                                echo '<tr>';
                                echo '<td width="20%">' . $atributos['ANO-DE-INICIO'] . ' - ' . $atributos['ANO-DE-CONCLUSAO'] . '</td>';
                                echo '<td width="80%">';
                                echo $formacao . ' em ' . $atributos['NOME-CURSO'] . '<br>';
                                echo $atributos['NOME-INSTITUICAO'] . '<br>';
                                echo 'Título: ' . $atributos['TITULO-DO-TRABALHO-DE-CONCLUSAO-DE-CURSO'] . '<br>';
                                echo 'Orientador: ' . $atributos['NOME-DO-ORIENTADOR'] . '<br>';
                                echo '</td>';
                                echo '</tr>';
                            }
                        } else if ($nivel == 'ESPECIALIZACAO') {
                            $formacao = 'Especialização';
                            if (isset($titulos[0])) {
                                for ($i = 0; isset($titulos[$i]); $i++) {
                                    $titulo = $titulos[$i];
                                    $atributos = $titulo['@attributes'];
                                    echo '<tr>';
                                    echo '<td width="20%">' . $atributos['ANO-DE-INICIO'] . ' - ' . $atributos['ANO-DE-CONCLUSAO'] . '</td>';
                                    echo '<td width="80%">';
                                    echo $formacao . ' em ' . $atributos['NOME-CURSO'] . '<br>';
                                    echo $atributos['NOME-INSTITUICAO'] . '<br>';
                                    echo 'Título: ' . $atributos['TITULO-DA-MONOGRAFIA'] . '<br>';
                                    echo 'Orientador: ' . $atributos['NOME-DO-ORIENTADOR'] . '<br>';
                                    echo '</td>';
                                    echo '</tr>';
                                }
                            } else {
                                $atributos = $titulos['@attributes'];
                                echo '<tr>';
                                echo '<td width="20%">' . $atributos['ANO-DE-INICIO'] . ' - ' . $atributos['ANO-DE-CONCLUSAO'] . '</td>';
                                echo '<td width="80%">';
                                echo $formacao . ' em ' . $atributos['NOME-CURSO'] . '<br>';
                                echo $atributos['NOME-INSTITUICAO'] . '<br>';
                                echo 'Título: ' . $atributos['TITULO-DA-MONOGRAFIA'] . '<br>';
                                echo 'Orientador: ' . $atributos['NOME-DO-ORIENTADOR'] . '<br>';
                                echo '</td>';
                                echo '</tr>';
                            }
                        } else if ($nivel == 'MESTRADO') {
                            $formacao = 'Mestrado';
                            if (isset($titulos[0])) {
                                for ($i = 0; isset($titulos[$i]); $i++) {
                                    $titulo = $titulos[$i];
                                    $atributos = $titulo['@attributes'];
                                    echo '<tr>';
                                    echo '<td width="20%">' . $atributos['ANO-DE-INICIO'] . ' - ' . $atributos['ANO-DE-CONCLUSAO'] . '</td>';
                                    echo '<td width="80%">';
                                    echo $formacao . ' em ' . $atributos['NOME-CURSO'] . '<br>';
                                    echo $atributos['NOME-INSTITUICAO'] . '<br>';
                                    echo 'Título: ' . $atributos['TITULO-DA-DISSERTACAO-TESE'] . '<br>';
                                    echo 'Orientador: ' . $atributos['NOME-COMPLETO-DO-ORIENTADOR'] . '<br>';
                                    echo '</td>';
                                    echo '</tr>';
                                }
                            } else {
                                $atributos = $titulos['@attributes'];
                                echo '<tr>';
                                echo '<td width="20%">' . $atributos['ANO-DE-INICIO'] . ' - ' . $atributos['ANO-DE-CONCLUSAO'] . '</td>';
                                echo '<td width="80%">';
                                echo $formacao . ' em ' . $atributos['NOME-CURSO'] . '<br>';
                                echo $atributos['NOME-INSTITUICAO'] . '<br>';
                                echo 'Título: ' . $atributos['TITULO-DA-DISSERTACAO-TESE'] . '<br>';
                                echo 'Orientador: ' . $atributos['NOME-COMPLETO-DO-ORIENTADOR'] . '<br>';
                                echo '</td>';
                                echo '</tr>';
                            }
                        } else if ($nivel == 'DOUTORADO') {
                            $formacao = 'Doutorado';
                            if (isset($titulos[0])) {
                                for ($i = 0; isset($titulos[$i]); $i++) {
                                    $titulo = $titulos[$i];
                                    $atributos = $titulo['@attributes'];
                                    echo '<tr>';
                                    echo '<td width="20%">' . $atributos['ANO-DE-INICIO'] . ' - ' . $atributos['ANO-DE-CONCLUSAO'] . '</td>';
                                    echo '<td width="80%">';
                                    echo $formacao . ' em ' . $atributos['NOME-CURSO'] . '<br>';
                                    echo $atributos['NOME-INSTITUICAO'] . '<br>';
                                    echo 'Título: ' . $atributos['TITULO-DA-DISSERTACAO-TESE'] . '<br>';
                                    echo 'Orientador: ' . $atributos['NOME-COMPLETO-DO-ORIENTADOR'] . '<br>';
                                    echo '</td>';
                                    echo '</tr>';
                                }
                            } else {
                                $atributos = $titulos['@attributes'];
                                echo '<tr>';
                                echo '<td width="20%">' . $atributos['ANO-DE-INICIO'] . ' - ' . $atributos['ANO-DE-CONCLUSAO'] . '</td>';
                                echo '<td width="80%">';
                                echo $formacao . ' em ' . $atributos['NOME-CURSO'] . '<br>';
                                echo $atributos['NOME-INSTITUICAO'] . '<br>';
                                echo 'Título: ' . $atributos['TITULO-DA-DISSERTACAO-TESE'] . '<br>';
                                echo 'Orientador: ' . $atributos['NOME-COMPLETO-DO-ORIENTADOR'] . '<br>';
                                echo '</td>';
                                echo '</tr>';
                            }
                        } else if ($nivel == 'POS-DOUTORADO') {
                            $formacao = 'Pós-Doutorado';
                            if (isset($titulos[0])) {
                                for ($i = 0; isset($titulos[$i]); $i++) {
                                    $titulo = $titulos[$i];
                                    $atributos = $titulo['@attributes'];
                                    echo '<tr>';
                                    echo '<td width="20%">' . $atributos['ANO-DE-INICIO'] . ' - ' . $atributos['ANO-DE-CONCLUSAO'] . '</td>';
                                    echo '<td width="80%">';
                                    echo $formacao . '<br>';
                                    echo $atributos['NOME-INSTITUICAO'] . '<br>';
                                    echo 'Bolsista do(a): ' . $atributos['NOME-AGENCIA'] . '<br>';
                                    echo 'Areas do conhecimento: <br>';
                                    echo '</td>';
                                    echo '</tr>';
                                }
                            } else {
                                $atributos = $titulos['@attributes'];
                                echo '<tr>';
                                echo '<td width="20%">' . $atributos['ANO-DE-INICIO'] . ' - ' . $atributos['ANO-DE-CONCLUSAO'] . '</td>';
                                echo '<td width="80%">';
                                echo $formacao . '<br>';
                                echo $atributos['NOME-INSTITUICAO'] . '<br>';
                                echo 'Bolsista do(a): ' . $atributos['NOME-AGENCIA'] . '<br>';
                                echo 'Areas do conhecimento: <br>';
                                echo '</td>';
                                echo '</tr>';
                            }
                        }
                    }
                    echo "</table>";

//                    echo '<button type="button" class="collapsible" style="margin-bottom:20px">Mais informações (Formação Acadêmica)</button>';
//                    echo '<div class="content">';
//                    echo '<pre style="margin-bottom:20px">';
//                    print_r($vetor_formacao_academica);
//                    echo '</pre>';
//                    echo "</div>";

                    
                    //----------------------------------------------------------
                    // AQUI COMEÇA A EXTRAÇÃO DAS ATUAÇÕES PROFISSIONAIS
                    // FICA DENTRO DE DADOS GERAIS NO VETOR RAIZ
                    //----------------------------------------------------------
                    $vetor_atuacoes_profissionais = $vetor_dados_gerais['ATUACOES-PROFISSIONAIS'];

                    echo '<table class="table table-bordered">';
                    echo '<tr>';
                    echo '<th colspan="2">Atuações profissionais</th>';
                    echo '</tr>';
                    $vetor_atuacao_profissional = $vetor_atuacoes_profissionais['ATUACAO-PROFISSIONAL'];
                    if (isset($vetor_atuacao_profissional[0])) {
                        for ($i = 0; isset($vetor_atuacao_profissional[$i]); $i++) {

                            $atuacao = $vetor_atuacao_profissional[$i];

                            $atributos = $atuacao['@attributes'];
                            if (isset($atuacao['VINCULOS'])) {
                                $vinculos = $atuacao['VINCULOS'];
                            } else {
                                echo '<button type="button" class="collapsible" style="margin-bottom:20px">Não foi encontrado o vínculo dessa instituição! Clique para detalhes</button>';
                                echo '<div class="content">';
                                echo '<pre>';
                                print_r($atuacao);
                                echo '</pre>';
                                echo '</div>';
                            }
                            if (isset($vinculos['@attributes'])) {
                                $atributos_vinculos = $vinculos['@attributes'];
                                echo '<tr>';
                                echo '<td width="20%">' . $atributos_vinculos['ANO-INICIO'] . ' - ' . (($atributos_vinculos['ANO-FIM'] == '' ) ? 'Atual' : $atributos_vinculos['ANO-FIM']) . '</td>';
                                echo '<td width="80%">';
                                echo "Instituição: " . $atributos['NOME-INSTITUICAO'] . '<br>';
                                echo (($atributos_vinculos['OUTRO-ENQUADRAMENTO-FUNCIONAL-INFORMADO'] != '') ? "Enquadramento funcional: " . $atributos_vinculos['OUTRO-ENQUADRAMENTO-FUNCIONAL-INFORMADO'] . '<br>' : '');
                                echo (($atributos_vinculos['OUTRO-VINCULO-INFORMADO'] != '') ? "Vínculo: " . $atributos_vinculos['OUTRO-VINCULO-INFORMADO'] . '<br>' : '');
                                echo (($atributos_vinculos['OUTRAS-INFORMACOES'] != '') ? "Informações: " . $atributos_vinculos['OUTRAS-INFORMACOES'] . '<br>' : '');
                                echo '</td>';
                                echo '</tr>';
                            } else {
                                for ($j = 0; isset($vinculos[$j]); $j++) {
                                    $sub_vinculo = $vinculos[$j];
                                    $atributos_sub_vinculos = $sub_vinculo['@attributes'];
//                                    if (
//                                            str_contains($atributos_sub_vinculos['OUTRO-ENQUADRAMENTO-FUNCIONAL-INFORMADO'], 'Tutora') ||
//                                            str_contains($atributos_sub_vinculos['OUTRO-VINCULO-INFORMADO'], 'Tutora') ||
//                                            str_contains($atributos_sub_vinculos['OUTRO-ENQUADRAMENTO-FUNCIONAL-INFORMADO'], 'Tutor') ||
//                                            str_contains($atributos_sub_vinculos['OUTRO-VINCULO-INFORMADO'], 'Tutor') ||
//                                            str_contains($atributos_sub_vinculos['OUTRO-ENQUADRAMENTO-FUNCIONAL-INFORMADO'], 'Docente') ||
//                                            str_contains($atributos_sub_vinculos['OUTRO-VINCULO-INFORMADO'], 'Docente')
//                                    ) {
                                    if (
                                            strpos($atributos_sub_vinculos['OUTRO-ENQUADRAMENTO-FUNCIONAL-INFORMADO'], 'Tutora') ||
                                            strpos($atributos_sub_vinculos['OUTRO-VINCULO-INFORMADO'], 'Tutora') ||
                                            strpos($atributos_sub_vinculos['OUTRO-ENQUADRAMENTO-FUNCIONAL-INFORMADO'], 'Tutor') ||
                                            strpos($atributos_sub_vinculos['OUTRO-VINCULO-INFORMADO'], 'Tutor') ||
                                            strpos($atributos_sub_vinculos['OUTRO-ENQUADRAMENTO-FUNCIONAL-INFORMADO'], 'Docente') ||
                                            strpos($atributos_sub_vinculos['OUTRO-VINCULO-INFORMADO'], 'Docente')
                                    ) {                                    
                                        echo '<tr>';
                                        echo '<td width="20%">' . $atributos_sub_vinculos['ANO-INICIO'] . ' - ' . (($atributos_sub_vinculos['ANO-FIM'] == '' ) ? 'Atual' : $atributos_sub_vinculos['ANO-FIM']) . '</td>';
                                        echo '<td width="80%">';
                                        echo "Instituição: " . $atributos['NOME-INSTITUICAO'] . '<br>';
                                        echo (($atributos_sub_vinculos['OUTRO-ENQUADRAMENTO-FUNCIONAL-INFORMADO'] != '') ? "Enquadramento funcional: " . $atributos_sub_vinculos['OUTRO-ENQUADRAMENTO-FUNCIONAL-INFORMADO'] . '<br>' : '');
                                        echo (($atributos_sub_vinculos['OUTRO-VINCULO-INFORMADO'] != '') ? "Vínculo: " . $atributos_sub_vinculos['OUTRO-VINCULO-INFORMADO'] . '<br>' : '');
                                        echo (($atributos_sub_vinculos['OUTRAS-INFORMACOES'] != '') ? "Informações: " . $atributos_sub_vinculos['OUTRAS-INFORMACOES'] . '<br>' : '');
                                        echo '</td>';
                                        echo '</tr>';
                                    }
                                }
                            }
                        }
                    }
                    echo "</table>";

//                    echo '<button type="button" class="collapsible" style="margin-bottom:20px">Mais informações (Atuações Profissionais)</button>';
//                    echo '<div class="content">';
//                    echo '<pre style="margin-bottom:20px">';
//                    print_r($vetor_atuacoes_profissionais);
//                    echo '</pre>';
//                    echo "</div>";

                    
                    //----------------------------------------------------------
                    // AQUI COMEÇA A EXTRAÇÃO DAS PRODUÇÕES BIBLIOGRÁFICAS
                    //----------------------------------------------------------
                    $vetor_producao_bibliografica = $array['PRODUCAO-BIBLIOGRAFICA'];

                    //TRABALHOS-EM-EVENTOS (Resumos expandidos publicados em anais de congressos,
                    //                      Resumos publicados em anais de congressos,
                    //                      Apresentações de Trabalho
                    //                      )                    
                    echo '<table class="table table-bordered">';
                    echo '<tr>';
                    echo '<th>Trabalhos em eventos</th>';
                    echo '</tr>';
                    if (isset($vetor_producao_bibliografica['TRABALHOS-EM-EVENTOS'])) {
                        $vetor_trabalho_em_eventos = $vetor_producao_bibliografica['TRABALHOS-EM-EVENTOS'];
                        $trabalho_em_eventos = $vetor_trabalho_em_eventos['TRABALHO-EM-EVENTOS'];
                        if (isset($trabalho_em_eventos['DADOS-BASICOS-DO-TRABALHO'])) {
                            $vetor_dados_basicos = $trabalho_em_eventos['DADOS-BASICOS-DO-TRABALHO'];
                            $dados_basicos = $vetor_dados_basicos['@attributes'];
                            $vetor_detalhamento = $trabalho_em_eventos['DETALHAMENTO-DO-TRABALHO'];
                            $detalhamento = $vetor_detalhamento['@attributes'];
                            if ($dados_basicos['ANO-DO-TRABALHO'] >= 2021) {
                                echo '<tr>';
                                echo '<td>';
                                echo 'Ano: ' . $dados_basicos['ANO-DO-TRABALHO'] . '<br>';
                                echo 'Natureza: ' . $dados_basicos['NATUREZA'] . '<br>';
                                echo 'Título: ' . $dados_basicos['TITULO-DO-TRABALHO'] . '<br>';
                                echo 'Evento: ' . $detalhamento['NOME-DO-EVENTO'] . '<br>';
                                echo 'Cidade: ' . $detalhamento['CIDADE-DO-EVENTO'] . '<br>';
                                echo 'Autores: ';
                                $vetor_autores = $trabalho_em_eventos['AUTORES'];
                                if (isset($vetor_autores['@attributes'])){
                                    $atributos_autor = $vetor_autores['@attributes'];
                                    echo $atributos_autor['NOME-PARA-CITACAO'];
                                } else if (isset($vetor_autores[0])) {
                                    for ($k = 0; isset($vetor_autores[$k]); $k++) {
                                        $autor = $vetor_autores[$k];
                                        $atributos_autor = $autor['@attributes'];
                                        if ($k > 0)
                                            echo ',';
                                        echo $atributos_autor['NOME-PARA-CITACAO'];
                                    }
                                }
                                echo '</td>';
                                echo '</tr>';
                            }
                        } else if (isset($trabalho_em_eventos[0])) {
                            for ($l = 0; isset($trabalho_em_eventos[$l]); $l++) {
                                $trabalhos = $trabalho_em_eventos[$l];
                                $vetor_dados_basicos = $trabalhos['DADOS-BASICOS-DO-TRABALHO'];
                                $dados_basicos = $vetor_dados_basicos['@attributes'];
                                $vetor_detalhamento = $trabalhos['DETALHAMENTO-DO-TRABALHO'];
                                $detalhamento = $vetor_detalhamento['@attributes'];
                                if ($dados_basicos['ANO-DO-TRABALHO'] >= 2021) {
                                    echo '<tr>';
                                    echo '<td>';
                                    echo 'Ano: ' . $dados_basicos['ANO-DO-TRABALHO'] . '<br>';
                                    echo 'Natureza: ' . $dados_basicos['NATUREZA'] . '<br>';
                                    echo 'Título: ' . $dados_basicos['TITULO-DO-TRABALHO'] . '<br>';
                                    echo 'Evento: ' . $detalhamento['NOME-DO-EVENTO'] . '<br>';
                                    echo 'Cidade: ' . $detalhamento['CIDADE-DO-EVENTO'] . '<br>';
                                    echo 'Autores: ';
                                    $vetor_autores = $trabalhos['AUTORES'];
                                    if (isset($vetor_autores['@attributes'])){
                                        $atributos_autor = $vetor_autores['@attributes'];
                                        echo $atributos_autor['NOME-PARA-CITACAO'];
                                    } else if (isset($vetor_autores[0])) {
                                        for ($k = 0; isset($vetor_autores[$k]); $k++) {
                                            $autor = $vetor_autores[$k];
                                            $atributos_autor = $autor['@attributes'];
                                            if ($k > 0)
                                                echo ',';
                                            echo $atributos_autor['NOME-PARA-CITACAO'];
                                        }
                                    }
                                    echo '</td>';
                                    echo '</tr>';
                                }
                            }
                        }
                    }
                    echo "</table>";

//                    echo '<button type="button" class="collapsible" style="margin-bottom:20px">Mais informações (Trabalho em eventos)</button>';
//                    echo '<div class="content">';
//                    echo '<pre style="margin-bottom:20px">';
//                    print_r($vetor_producao_bibliografica['TRABALHOS-EM-EVENTOS']);
//                    echo '</pre>';
//                    echo "</div>";

                    //ARTIGOS-PUBLICADOS (Artigos completos publicados em periódicos)
                    echo '<table class="table table-bordered">';
                    echo '<tr>';
                    echo '<th>Artigos publicados em periódicos</th>';
                    echo '</tr>';
                    if (isset($vetor_producao_bibliografica['ARTIGOS-PUBLICADOS'])) {
                        $artigos = $vetor_producao_bibliografica['ARTIGOS-PUBLICADOS'];
                        $vetor_artigo = $artigos['ARTIGO-PUBLICADO'];
                        if (isset($vetor_artigo['DADOS-BASICOS-DO-ARTIGO'])) {
                            $dados_basicos = $vetor_artigo['DADOS-BASICOS-DO-ARTIGO'];
                            $dados_basicos_atributos = $dados_basicos['@attributes'];
                            $vetor_detalhamento = $vetor_artigo['DETALHAMENTO-DO-ARTIGO'];
                            $detalhamento = $vetor_detalhamento['@attributes'];
                            if ($dados_basicos_atributos['ANO-DO-ARTIGO'] >= 2021) {
                                echo '<tr>';
                                echo '<td>';
                                echo 'Ano: ' . $dados_basicos_atributos['ANO-DO-ARTIGO'] . '<br>';
                                echo 'Natureza: ' . $dados_basicos_atributos['NATUREZA'] . '<br>';
                                echo 'Título: ' . $dados_basicos_atributos['TITULO-DO-ARTIGO'] . '<br>';
                                echo 'Periódico: ' . $detalhamento['TITULO-DO-PERIODICO-OU-REVISTA'] . '<br>';
                                echo 'Autores: ';
                                $vetor_autores = $vetor_artigo['AUTORES'];
                                if (isset($vetor_autores['@attributes'])){
                                    $atributos_autor = $vetor_autores['@attributes'];
                                    echo $atributos_autor['NOME-PARA-CITACAO'];
                                } else if (isset($vetor_autores[0])) {
                                    for ($k = 0; isset($vetor_autores[$k]); $k++) {
                                        $autor = $vetor_autores[$k];
                                        $atributos_autor = $autor['@attributes'];
                                        if ($k > 0)
                                            echo ',';
                                        echo $atributos_autor['NOME-PARA-CITACAO'];
                                    }
                                }
                                echo '</td>';
                                echo '</tr>';
                            }
                        } else if (isset($vetor_artigo[0])) {
                            for ($a = 0; isset($vetor_artigo[$a]); $a++) {
                                $artigo = $vetor_artigo[$a];
                                $dados_basicos = $artigo['DADOS-BASICOS-DO-ARTIGO'];
                                $dados_basicos_atributos = $dados_basicos['@attributes'];
                                $vetor_detalhamento = $artigo['DETALHAMENTO-DO-ARTIGO'];
                                $detalhamento = $vetor_detalhamento['@attributes'];
                                if ($dados_basicos_atributos['ANO-DO-ARTIGO'] >= 2021) {
                                    echo '<tr>';
                                    echo '<td>';
                                    echo 'Ano: ' . $dados_basicos_atributos['ANO-DO-ARTIGO'] . '<br>';
                                    echo 'Natureza: ' . $dados_basicos_atributos['NATUREZA'] . '<br>';
                                    echo 'Título: ' . $dados_basicos_atributos['TITULO-DO-ARTIGO'] . '<br>';
                                    echo 'Periódico: ' . $detalhamento['TITULO-DO-PERIODICO-OU-REVISTA'] . '<br>';
                                    echo 'Autores: ';
                                    $vetor_autores = $artigo['AUTORES'];
                                    if (isset($vetor_autores['@attributes'])){
                                        $atributos_autor = $vetor_autores['@attributes'];
                                        echo $atributos_autor['NOME-PARA-CITACAO'];
                                    } else if (isset($vetor_autores[0])) {
                                        for ($k = 0; isset($vetor_autores[$k]); $k++) {
                                            $autor = $vetor_autores[$k];
                                            $atributos_autor = $autor['@attributes'];
                                            if ($k > 0)
                                                echo ',';
                                            echo $atributos_autor['NOME-PARA-CITACAO'];
                                        }
                                    }
                                    echo '</td>';
                                    echo '</tr>';
                                }
                            }
                        }
                    }
                    echo "</table>";

//                    echo '<button type="button" class="collapsible" style="margin-bottom:20px">Mais informações (Artigos publicados em periódicos)</button>';
//                    echo '<div class="content">';
//                    echo '<pre style="margin-bottom:20px">';
//                    print_r($vetor_producao_bibliografica['ARTIGOS-PUBLICADOS']);
//                    echo '</pre>';
//                    echo "</div>";

                    //LIVROS-E-CAPITULOS (Livros publicados/organizados ou edições)
                    echo '<table class="table table-bordered">';
                    echo '<tr>';
                    echo '<th>Livros publicados</th>';
                    echo '</tr>';
                    if (isset($vetor_producao_bibliografica['LIVROS-E-CAPITULOS'])) {
                        $vetor_livros_capitulos = $vetor_producao_bibliografica['LIVROS-E-CAPITULOS'];
                        $vetor_livros = $vetor_livros_capitulos['LIVROS-PUBLICADOS-OU-ORGANIZADOS'];
                        $livros = $vetor_livros['LIVRO-PUBLICADO-OU-ORGANIZADO'];
                        if (isset($livros['DADOS-BASICOS-DO-LIVRO'])) {
                            $dados_basicos = $livros['DADOS-BASICOS-DO-LIVRO'];
                            $dados_basicos_atributos = $dados_basicos['@attributes'];
                            $vetor_detalhamento = $livros['DETALHAMENTO-DO-LIVRO'];
                            $detalhamento = $vetor_detalhamento['@attributes'];
                            if ($dados_basicos_atributos['ANO'] >= 2021) {
                                echo '<tr>';
                                echo '<td>';
                                echo 'Ano: ' . $dados_basicos_atributos['ANO'] . '<br>';
                                echo 'Título: ' . $dados_basicos_atributos['TITULO-DO-LIVRO'] . '<br>';
                                echo 'ISBN: ' . $detalhamento['ISBN'] . '<br>';
                                echo 'Autores: ';
                                $vetor_autores = $livros['AUTORES'];
                                if (isset($vetor_autores['@attributes'])){
                                    $atributos_autor = $vetor_autores['@attributes'];
                                    echo $atributos_autor['NOME-PARA-CITACAO'];
                                } else if (isset($vetor_autores[0])) {
                                    for ($k = 0; isset($vetor_autores[$k]); $k++) {
                                        $autor = $vetor_autores[$k];
                                        $atributos_autor = $autor['@attributes'];
                                        if ($k > 0)
                                            echo ',';
                                        echo $atributos_autor['NOME-PARA-CITACAO'];
                                    }
                                }
                                echo '</td>';
                                echo '</tr>';
                            }
                        } else if (isset($livros[0])) {
                            for ($a = 0; isset($livros[$a]); $a++) {
                                $livro = $livros[$a];
                                $dados_basicos = $livro['DADOS-BASICOS-DO-LIVRO'];
                                $dados_basicos_atributos = $dados_basicos['@attributes'];
                                $vetor_detalhamento = $livro['DETALHAMENTO-DO-LIVRO'];
                                $detalhamento = $vetor_detalhamento['@attributes'];
                                if ($dados_basicos_atributos['ANO'] >= 2021) {
                                    echo '<tr>';
                                    echo '<td>';
                                    echo 'Ano: ' . $dados_basicos_atributos['ANO'] . '<br>';
                                    echo 'Título: ' . $dados_basicos_atributos['TITULO-DO-LIVRO'] . '<br>';
                                    echo 'ISBN: ' . $detalhamento['ISBN'] . '<br>';
                                    echo 'Autores: ';
                                    $vetor_autores = $livro['AUTORES'];
                                    if (isset($vetor_autores['@attributes'])){
                                        $atributos_autor = $vetor_autores['@attributes'];
                                        echo $atributos_autor['NOME-PARA-CITACAO'];
                                    } else if (isset($vetor_autores[0])) {
                                        for ($k = 0; isset($vetor_autores[$k]); $k++) {
                                            $autor = $vetor_autores[$k];
                                            $atributos_autor = $autor['@attributes'];
                                            if ($k > 0)
                                                echo ',';
                                            echo $atributos_autor['NOME-PARA-CITACAO'];
                                        }
                                    }
                                    echo '</td>';
                                    echo '</tr>';
                                }
                            }
                        }
                    }
                    echo "</table>";

//                    echo '<button type="button" class="collapsible" style="margin-bottom:20px">Mais informações (Livros publicados)</button>';
//                    echo '<div class="content">';
//                    echo '<pre style="margin-bottom:20px">';
//                    print_r($vetor_producao_bibliografica['LIVROS-E-CAPITULOS']);
//                    echo '</pre>';
//                    echo "</div>";

                    //CAPITULOS-DE-LIVROS-PUBLICADOS (Capítulos de livros publicados)
                    echo '<table class="table table-bordered">';
                    echo '<tr>';
                    echo '<th>Capítulos de livros publicados</th>';
                    echo '</tr>';
                    if (isset($vetor_producao_bibliografica['LIVROS-E-CAPITULOS'])) {
                        $vetor_livros_capitulos = $vetor_producao_bibliografica['LIVROS-E-CAPITULOS'];
                        if (isset($vetor_livros_capitulos['CAPITULOS-DE-LIVROS-PUBLICADOS'])) {
                            $vetor_livros = $vetor_livros_capitulos['CAPITULOS-DE-LIVROS-PUBLICADOS'];
                            $livros = $vetor_livros['CAPITULO-DE-LIVRO-PUBLICADO'];
                            if (isset($livros['DADOS-BASICOS-DO-CAPITULO]'])) {
                                $dados_basicos = $livros['DADOS-BASICOS-DO-CAPITULO'];
                                $dados_basicos_atributos = $dados_basicos['@attributes'];
                                $vetor_detalhamento = $livros['DETALHAMENTO-DO-CAPITULO'];
                                $detalhamento = $vetor_detalhamento['@attributes'];
                                if ($dados_basicos_atributos['ANO'] >= 2021) {
                                    echo '<tr>';
                                    echo '<td>';
                                    echo 'Ano: ' . $dados_basicos_atributos['ANO'] . '<br>';
                                    echo 'Título do capítulo: ' . $dados_basicos_atributos['TITULO-DO-CAPITULO-DO-LIVRO'] . '<br>';
                                    echo 'Título do livro: ' . $detalhamento['TITULO-DO-LIVRO'] . '<br>';
                                    echo 'ISBN: ' . $detalhamento['ISBN'] . '<br>';
                                    echo 'Autores: ';
                                    $vetor_autores = $livros['AUTORES'];
                                    if (isset($vetor_autores['@attributes'])){
                                        $atributos_autor = $vetor_autores['@attributes'];
                                        echo $atributos_autor['NOME-PARA-CITACAO'];
                                    } else if (isset($vetor_autores[0])) {
                                        for ($k = 0; isset($vetor_autores[$k]); $k++) {
                                            $autor = $vetor_autores[$k];
                                            $atributos_autor = $autor['@attributes'];
                                            if ($k > 0)
                                                echo ',';
                                            echo $atributos_autor['NOME-PARA-CITACAO'];
                                        }
                                    }
                                    echo '</td>';
                                    echo '</tr>';
                                }
                            } else if (isset($livros[0])) {
                                for ($a = 0; isset($livros[$a]); $a++) {
                                    $livro = $livros[$a];
                                    $dados_basicos = $livro['DADOS-BASICOS-DO-CAPITULO'];
                                    $dados_basicos_atributos = $dados_basicos['@attributes'];
                                    $vetor_detalhamento = $livro['DETALHAMENTO-DO-CAPITULO'];
                                    $detalhamento = $vetor_detalhamento['@attributes'];
                                    if ($dados_basicos_atributos['ANO'] >= 2021) {
                                        echo '<tr>';
                                        echo '<td>';
                                        echo 'Ano: ' . $dados_basicos_atributos['ANO'] . '<br>';
                                        echo 'Título do capítulo: ' . $dados_basicos_atributos['TITULO-DO-CAPITULO-DO-LIVRO'] . '<br>';
                                        echo 'Título do livro: ' . $detalhamento['TITULO-DO-LIVRO'] . '<br>';
                                        echo 'ISBN: ' . $detalhamento['ISBN'] . '<br>';
                                        echo 'Autores: ';
                                        $vetor_autores = $livro['AUTORES'];
                                        if (isset($vetor_autores['@attributes'])){
                                            $atributos_autor = $vetor_autores['@attributes'];
                                            echo $atributos_autor['NOME-PARA-CITACAO'];
                                        } else if (isset($vetor_autores[0])) {
                                            for ($k = 0; isset($vetor_autores[$k]); $k++) {
                                                $autor = $vetor_autores[$k];
                                                $atributos_autor = $autor['@attributes'];
                                                if ($k > 0)
                                                    echo ',';
                                                echo $atributos_autor['NOME-PARA-CITACAO'];
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                    echo "</table>";

//                    echo '<button type="button" class="collapsible" style="margin-bottom:20px">Mais informações (Capítulos de livros publicados)</button>';
//                    echo '<div class="content">';
//                    echo '<pre style="margin-bottom:20px">';
//                    print_r($vetor_producao_bibliografica['CAPITULOS-DE-LIVROS-PUBLICADOS']);
//                    echo '</pre>';
//                    echo "</div>";

                    //TEXTOS-EM-JORNAIS-OU-REVISTAS (Textos em jornais de notícias/revistas) 
                    echo '<table class="table table-bordered">';
                    echo '<tr>';
                    echo '<th>Textos em jornais de notícias/revistas</th>';
                    echo '</tr>';
                    if (isset($vetor_producao_bibliografica['TEXTOS-EM-JORNAIS-OU-REVISTAS'])) {
                        $vetor_textos_jornais_revistas = $vetor_producao_bibliografica['TEXTOS-EM-JORNAIS-OU-REVISTAS'];
                        if (isset($vetor_textos_jornais_revistas['TEXTO-EM-JORNAL-OU-REVISTA'])) {
                            $vetor_texto_jornal_revista = $vetor_textos_jornais_revistas['TEXTO-EM-JORNAL-OU-REVISTA'];
                            if (isset($vetor_texto_jornal_revista['DADOS-BASICOS-DO-TEXTO'])) {
                                $vetor_dados_basicos = $vetor_texto_jornal_revista['DADOS-BASICOS-DO-TEXTO'];
                                $dados_basicos = $vetor_dados_basicos['@attributes'];
                                $vetor_detalhamento = $vetor_texto_jornal_revista['DETALHAMENTO-DO-TEXTO'];
                                $detalhamento = $vetor_detalhamento['@attributes'];
                                if ($dados_basicos['ANO-DO-TEXTO'] >= 2021) {
                                    echo '<tr>';
                                    echo '<td>';
                                    echo 'Ano: ' . $dados_basicos['ANO-DO-TEXTO'] . '<br>';
                                    echo 'Título do texto: ' . $dados_basicos['TITULO-DO-TEXTO'] . '<br>';
                                    echo 'Jornal/Revista: ' . $detalhamento['TITULO-DO-JORNAL-OU-REVISTA'] . '<br>';
                                    echo 'Autores: ';
                                    $vetor_autores = $vetor_texto_jornal_revista['AUTORES'];
                                    if (isset($vetor_autores['@attributes'])){
                                        $atributos_autor = $vetor_autores['@attributes'];
                                        echo $atributos_autor['NOME-PARA-CITACAO'];
                                    } else if (isset($vetor_autores[0])) {
                                        for ($k = 0; isset($vetor_autores[$k]); $k++) {
                                            $autor = $vetor_autores[$k];
                                            $atributos_autor = $autor['@attributes'];
                                            if ($k > 0)
                                                echo ',';
                                            echo $atributos_autor['NOME-PARA-CITACAO'];
                                        }
                                    }
                                    echo '</td>';
                                    echo '</tr>';
                                }
                            } else if (isset($vetor_texto_jornal_revista[0])) {
                                for ($c = 0; isset($vetor_texto_jornal_revista[$c]); $c++) {
                                    $texto_jornal_revista = $vetor_texto_jornal_revista[$c];
                                    $vetor_dados_basicos = $texto_jornal_revista['DADOS-BASICOS-DO-TEXTO'];
                                    $dados_basicos = $vetor_dados_basicos['@attributes'];
                                    $vetor_detalhamento = $texto_jornal_revista['DETALHAMENTO-DO-TEXTO'];
                                    $detalhamento = $vetor_detalhamento['@attributes'];
                                    if ($dados_basicos['ANO-DO-TEXTO'] >= 2021) {
                                        echo '<tr>';
                                        echo '<td>';
                                        echo 'Ano: ' . $dados_basicos ['ANO-DO-TEXTO'] . '<br>';
                                        echo 'Título do texto: ' . $dados_basicos['TITULO-DO-TEXTO'] . '<br>';
                                        echo 'Jornal/Revista: ' . $detalhamento['TITULO-DO-JORNAL-OU-REVISTA'] . '<br>';
                                        echo 'Autores: ';
                                        $vetor_autores = $texto_jornal_revista['AUTORES'];
                                        if (isset($vetor_autores['@attributes'])){
                                            $atributos_autor = $vetor_autores['@attributes'];
                                            echo $atributos_autor['NOME-PARA-CITACAO'];
                                        } else if (isset($vetor_autores[0])) {
                                            for ($k = 0; isset($vetor_autores[$k]); $k++) {
                                                $autor = $vetor_autores[$k];
                                                $atributos_autor = $autor['@attributes'];
                                                if ($k > 0)
                                                    echo ',';
                                                echo $atributos_autor['NOME-PARA-CITACAO'];
                                            }
                                        }
                                        echo '</td>';
                                        echo '</tr>';
                                    }
                                }
                            }
                        }
                    }
                    echo "</table>";

//                    echo '<button type="button" class="collapsible" style="margin-bottom:20px">Mais informações (Textos em jornais de notícias/revistas)</button>';
//                    echo '<div class="content">';
//                    echo '<pre style="margin-bottom:20px">';
//                    print_r($vetor_producao_bibliografica['TEXTOS-EM-JORNAIS-OU-REVISTAS']);
//                    echo '</pre>';
//                    echo "</div>";

                    //DEMAIS-TIPOS-DE-PRODUCAO-BIBLIOGRAFICA (Outras produções bibliográficas)                    
                    echo '<table class="table table-bordered">';
                    echo '<tr>';
                    echo '<th>Demais produções bibliográficas</th>';
                    echo '</tr>';
                    if (isset($vetor_producao_bibliografica['DEMAIS-TIPOS-DE-PRODUCAO-BIBLIOGRAFICA'])) {
                        $vetor_demais_producoes = $vetor_producao_bibliografica['DEMAIS-TIPOS-DE-PRODUCAO-BIBLIOGRAFICA'];
                        if (isset($vetor_demais_producoes['OUTRA-PRODUCAO-BIBLIOGRAFICA'])) {
                            $vetor_outra_producao = $vetor_demais_producoes['OUTRA-PRODUCAO-BIBLIOGRAFICA'];
                            if (isset($vetor_outra_producao['DADOS-BASICOS-DE-OUTRA-PRODUCAO'])) {
                                $vetor_dados_basicos = $vetor_outra_producao['DADOS-BASICOS-DE-OUTRA-PRODUCAO'];
                                $dados_basicos = $vetor_dados_basicos['@attributes'];
                                $vetor_detalhamento = $vetor_outra_producao['DETALHAMENTO-DE-OUTRA-PRODUCAO'];
                                $detalhamento = $vetor_detalhamento['@attributes'];
                                if ($dados_basicos['ANO'] >= 2021) {
                                    echo '<tr>';
                                    echo '<td>';
                                    echo 'Ano: ' . $dados_basicos ['ANO'] . '<br>';
                                    echo 'Natureza: ' . $dados_basicos['NATUREZA'] . '<br>';
                                    echo 'Título: ' . $dados_basicos['TITULO'] . '<br>';
                                    echo 'Editora: ' . $detalhamento['EDITORA'] . '<br>';
                                    echo 'Autores: ';
                                    $vetor_autores = $vetor_outra_producao['AUTORES'];
                                    if (isset($vetor_autores['@attributes'])){
                                        $atributos_autor = $vetor_autores['@attributes'];
                                        echo $atributos_autor['NOME-PARA-CITACAO'];
                                    } else if (isset($vetor_autores[0])) {
                                        for ($k = 0; isset($vetor_autores[$k]); $k++) {
                                            $autor = $vetor_autores[$k];
                                            $atributos_autor = $autor['@attributes'];
                                            if ($k > 0)
                                                echo ',';
                                            echo $atributos_autor['NOME-PARA-CITACAO'];
                                        }
                                    }
                                    echo '</td>';
                                    echo '</tr>';
                                }
                            
                            } else if (isset($vetor_outra_producao[0])) {
                                for ($c = 0; isset($vetor_outra_producao[$c]); $c++) {
                                    $outra_producao = $vetor_outra_producao[$c];
                                    $vetor_dados_basicos = $outra_producao['DADOS-BASICOS-DE-OUTRA-PRODUCAO'];
                                    $dados_basicos = $vetor_dados_basicos['@attributes'];
                                    $vetor_detalhamento = $outra_producao['DETALHAMENTO-DE-OUTRA-PRODUCAO'];
                                    $detalhamento = $vetor_detalhamento['@attributes'];
                                    if ($dados_basicos['ANO'] >= 2021) {
                                        echo '<tr>';
                                        echo '<td>';
                                        echo 'Ano: ' . $dados_basicos ['ANO'] . '<br>';
                                        echo 'Natureza: ' . $dados_basicos['NATUREZA'] . '<br>';
                                        echo 'Título: ' . $dados_basicos['TITULO'] . '<br>';
                                        echo 'Editora: ' . $detalhamento['EDITORA'] . '<br>';
                                        echo 'Autores: ';
                                        $vetor_autores = $outra_producao['AUTORES'];
                                        if (isset($vetor_autores['@attributes'])){
                                            $atributos_autor = $vetor_autores['@attributes'];
                                            echo $atributos_autor['NOME-PARA-CITACAO'];
                                        } else if (isset($vetor_autores[0])) {
                                            for ($k = 0; isset($vetor_autores[$k]); $k++) {
                                                $autor = $vetor_autores[$k];
                                                $atributos_autor = $autor['@attributes'];
                                                if ($k > 0)
                                                    echo ',';
                                                echo $atributos_autor['NOME-PARA-CITACAO'];
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                    echo "</table>";

//                    echo '<button type="button" class="collapsible" style="margin-bottom:20px">Mais informações (Demais produções bibliográficas)</button>';
//                    echo '<div class="content">';
//                    echo '<pre style="margin-bottom:20px">';
//                    print_r($vetor_producao_bibliografica['DEMAIS-TIPOS-DE-PRODUCAO-BIBLIOGRAFICA']);
//                    echo '</pre>';
//                    echo "</div>";

                    //ARTIGOS-ACEITOS-PARA-PUBLICACAO (Artigos aceitos para publicação)
                    echo '<table class="table table-bordered">';
                    echo '<tr>';
                    echo '<th>Artigos aceitos para publicação</th>';
                    echo '</tr>';
                    if (isset($vetor_producao_bibliografica['ARTIGOS-ACEITOS-PARA-PUBLICACAO'])) {
                        $vetor_artigos_aceitos = $vetor_producao_bibliografica['ARTIGOS-ACEITOS-PARA-PUBLICACAO'];
                        if (isset($vetor_artigos_aceitos['ARTIGO-ACEITO-PARA-PUBLICACAO'])) {
                            $vetor_artigo_aceito = $vetor_artigos_aceitos['ARTIGO-ACEITO-PARA-PUBLICACAO'];
                            if (isset($vetor_artigo_aceito['DADOS-BASICOS-DO-ARTIGO'])) {
                                $vetor_dados_basicos = $vetor_artigo_aceito['DADOS-BASICOS-DO-ARTIGO'];
                                $dados_basicos = $vetor_dados_basicos['@attributes'];
                                $vetor_detalhamento = $vetor_artigo_aceito['DETALHAMENTO-DO-ARTIGO'];
                                $detalhamento = $vetor_detalhamento['@attributes'];
                                if ($dados_basicos['ANO-DO-ARTIGO'] >= 2021) {
                                    echo '<tr>';
                                    echo '<td>';
                                    echo 'Ano: ' . $dados_basicos ['ANO-DO-ARTIGO'] . '<br>';
                                    echo 'Título: ' . $dados_basicos['TITULO-DO-ARTIGO'] . '<br>';
                                    echo 'Periódico/Revista: ' . $detalhamento['TITULO-DO-PERIODICO-OU-REVISTA'] . '<br>';
                                    echo 'Autores: ';
                                    $vetor_autores = $vetor_artigo_aceito['AUTORES'];
                                    if (isset($vetor_autores['@attributes'])){
                                        $atributos_autor = $vetor_autores['@attributes'];
                                        echo $atributos_autor['NOME-PARA-CITACAO'];
                                    } else if (isset($vetor_autores[0])) {
                                        for ($k = 0; isset($vetor_autores[$k]); $k++) {
                                            $autor = $vetor_autores[$k];
                                            $atributos_autor = $autor['@attributes'];
                                            if ($k > 0)
                                                echo ',';
                                            echo $atributos_autor['NOME-PARA-CITACAO'];
                                        }
                                    }
                                    echo '</td>';
                                    echo '</tr>';
                                }
                            
                            } else if (isset($vetor_artigo_aceito[0])) {
                                for ($d = 0; isset($vetor_artigo_aceito[$d]); $d++) {
                                    $artigo_aceito = $vetor_artigo_aceito[$d];
                                    $vetor_dados_basicos = $artigo_aceito['DADOS-BASICOS-DO-ARTIGO'];
                                    $dados_basicos = $vetor_dados_basicos['@attributes'];
                                    $vetor_detalhamento = $artigo_aceito['DETALHAMENTO-DO-ARTIGO'];
                                    $detalhamento = $vetor_detalhamento['@attributes'];
                                    if ($dados_basicos['ANO-DO-ARTIGO'] >= 2021) {
                                        echo '<tr>';
                                        echo '<td>';
                                        echo 'Ano: ' . $dados_basicos ['ANO-DO-ARTIGO'] . '<br>';
                                        echo 'Título: ' . $dados_basicos['TITULO-DO-ARTIGO'] . '<br>';
                                        echo 'Periódico/Revista: ' . $detalhamento['TITULO-DO-PERIODICO-OU-REVISTA'] . '<br>';
                                        echo 'Autores: ';
                                        $vetor_autores = $artigo_aceito['AUTORES'];
                                        if (isset($vetor_autores['@attributes'])){
                                            $atributos_autor = $vetor_autores['@attributes'];
                                            echo $atributos_autor['NOME-PARA-CITACAO'];
                                        } else if (isset($vetor_autores[0])) {
                                            for ($k = 0; isset($vetor_autores[$k]); $k++) {
                                                $autor = $vetor_autores[$k];
                                                $atributos_autor = $autor['@attributes'];
                                                if ($k > 0)
                                                    echo ',';
                                                echo $atributos_autor['NOME-PARA-CITACAO'];
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                    echo "</table>";

//                    echo '<button type="button" class="collapsible" style="margin-bottom:20px">Mais informações (Artigos aceitos para publicação)</button>';
//                    echo '<div class="content">';
//                    echo '<pre style="margin-bottom:20px">';
//                    print_r($vetor_producao_bibliografica['ARTIGOS-ACEITOS-PARA-PUBLICACAO']);
//                    echo '</pre>';
//                    echo "</div>";                    



                    
                    /* PRODUCAO-TECNICA
                     *  - SOFTWARE
                     *  - TRABALHO-TECNICO 
                     *  - DEMAIS-TIPOS-DE-PRODUCAO-TECNICA                     * 
                     */                    
                    $vetor_producao_tecnica = $array['PRODUCAO-TECNICA'];
                    
                    // SOFTWARE
                    echo '<table class="table table-bordered">';
                    echo '<tr>';
                    echo '<th>Produção técnica: Software</th>';
                    echo '</tr>';
                    if (isset($vetor_producao_tecnica['SOFTWARE'])) {  
                        $vetor_software = $vetor_producao_tecnica['SOFTWARE'];
                        if (isset($vetor_software['DADOS-BASICOS-DO-SOFTWARE'])) {
                            $vetor_dados_basicos = $vetor_software['DADOS-BASICOS-DO-SOFTWARE'];
                            $dados_basicos = $vetor_dados_basicos['@attributes'];
                            $vetor_detalhamento = $vetor_software['DETALHAMENTO-DO-SOFTWARE'];
                            $detalhamento = $vetor_detalhamento['@attributes'];
                            if ($dados_basicos['ANO'] >= 2021) {
                                echo '<tr>';
                                echo '<td>';
                                echo 'Ano: ' . $dados_basicos ['ANO'] . '<br>';
                                echo 'Título do software: ' . $dados_basicos['TITULO-DO-SOFTWARE'] . '<br>';
                                echo 'Finalidade: ' . $detalhamento['FINALIDADE'] . '<br>';
                                echo 'Plataforma: ' . $detalhamento['PLATAFORMA'] . '<br>';
                                echo 'Instituição financiadora: ' . $detalhamento['INSTITUICAO-FINANCIADORA'] . '<br>';
                                echo 'Autores: ';
                                $vetor_autores = $vetor_software['AUTORES'];
                                if (isset($vetor_autores['@attributes'])){
                                    $atributos_autor = $vetor_autores['@attributes'];
                                    echo $atributos_autor['NOME-PARA-CITACAO'];
                                } else if (isset($vetor_autores[0])) {
                                    for ($k = 0; isset($vetor_autores[$k]); $k++) {
                                        $autor = $vetor_autores[$k];
                                        $atributos_autor = $autor['@attributes'];
                                        if ($k > 0)
                                            echo ',';
                                        echo $atributos_autor['NOME-PARA-CITACAO'];
                                    }
                                }
                                echo '</td>';
                                echo '</tr>';
                            }
                        } else if (isset($vetor_software[0])) {
                            for ($d=0;isset($vetor_software[$d]);$d++) {
                                $software = $vetor_software[$d];
                                $vetor_dados_basicos = $software['DADOS-BASICOS-DO-SOFTWARE'];
                                $dados_basicos = $vetor_dados_basicos['@attributes'];
                                $vetor_detalhamento = $software['DETALHAMENTO-DO-SOFTWARE'];
                                $detalhamento = $vetor_detalhamento['@attributes'];
                                if ($dados_basicos['ANO'] >= 2021) {
                                    echo '<tr>';
                                    echo '<td>';
                                    echo 'Ano: ' . $dados_basicos ['ANO'] . '<br>';
                                    echo 'Título do software: ' . $dados_basicos['TITULO-DO-SOFTWARE'] . '<br>';
                                    echo 'Finalidade: ' . $detalhamento['FINALIDADE'] . '<br>';
                                    echo 'Plataforma: ' . $detalhamento['PLATAFORMA'] . '<br>';
                                    echo 'Instituição financiadora: ' . $detalhamento['INSTITUICAO-FINANCIADORA'] . '<br>';
                                    echo 'Autores: ';
                                    $vetor_autores = $software['AUTORES'];
                                    if (isset($vetor_autores['@attributes'])){
                                        $atributos_autor = $vetor_autores['@attributes'];
                                        echo $atributos_autor['NOME-PARA-CITACAO'];
                                    } else if (isset($vetor_autores[0])) {
                                        for ($k = 0; isset($vetor_autores[$k]); $k++) {
                                            $autor = $vetor_autores[$k];
                                            $atributos_autor = $autor['@attributes'];
                                            if ($k > 0)
                                                echo ',';
                                            echo $atributos_autor['NOME-PARA-CITACAO'];
                                        }
                                    }
                                    echo '</td>';
                                    echo '</tr>';
                                }                                
                            }
                        }
                    }
                    echo "</table>";
//                    echo '<button type="button" class="collapsible" style="margin-bottom:20px">Mais informações (Produção técnica: Software)</button>';
//                    echo '<div class="content">';
//                    echo '<pre style="margin-bottom:20px">';
//                    print_r($vetor_producao_tecnica['SOFTWARE']);
//                    echo '</pre>';
//                    echo "</div>";                      
                    
                    // TRABALHO-TECNICO
                    echo '<table class="table table-bordered">';
                    echo '<tr>';
                    echo '<th>Produção técnica: Trabalho técnico</th>';
                    echo '</tr>';
                    if (isset($vetor_producao_tecnica['TRABALHO-TECNICO'])) {  
                        $vetor_trabalho_tecnico = $vetor_producao_tecnica['TRABALHO-TECNICO'];
                        if (isset($vetor_trabalho_tecnico['DADOS-BASICOS-DO-TRABALHO-TECNICO'])) {
                            $vetor_dados_basicos = $vetor_trabalho_tecnico['DADOS-BASICOS-DO-TRABALHO-TECNICO'];
                            $dados_basicos = $vetor_dados_basicos['@attributes'];
                            $vetor_detalhamento = $vetor_trabalho_tecnico['DETALHAMENTO-DO-TRABALHO-TECNICO'];
                            $detalhamento = $vetor_detalhamento['@attributes'];
                            if ($dados_basicos['ANO'] >= 2021) {
                                echo '<tr>';
                                echo '<td>';
                                echo 'Ano: ' . $dados_basicos ['ANO'] . '<br>';
                                echo 'Natureza: ' . $dados_basicos['NATUREZA'] . '<br>';
                                echo 'Título do trabalho técnico: ' . $dados_basicos['TITULO-DO-TRABALHO-TECNICO'] . '<br>';
                                echo 'Finalidade: ' . $detalhamento['FINALIDADE'] . '<br>';
                                echo 'Instituição financiadora: ' . $detalhamento['INSTITUICAO-FINANCIADORA'] . '<br>';
                                echo 'Autores: ';
                                $vetor_autores = $vetor_trabalho_tecnico['AUTORES'];
                                if (isset($vetor_autores['@attributes'])){
                                    $atributos_autor = $vetor_autores['@attributes'];
                                    echo $atributos_autor['NOME-PARA-CITACAO'];
                                } else if (isset($vetor_autores[0])) {
                                    for ($k = 0; isset($vetor_autores[$k]); $k++) {
                                        $autor = $vetor_autores[$k];
                                        $atributos_autor = $autor['@attributes'];
                                        if ($k > 0)
                                            echo ',';
                                        echo $atributos_autor['NOME-PARA-CITACAO'];
                                    }
                                }
                                echo '</td>';
                                echo '</tr>';
                            }
                        } else if (isset($vetor_trabalho_tecnico[0])) {
                            for ($e=0;isset($vetor_trabalho_tecnico[$e]);$e++) {
                                $trabalho_tecnico = $vetor_trabalho_tecnico[$e];
                                $vetor_dados_basicos = $trabalho_tecnico['DADOS-BASICOS-DO-TRABALHO-TECNICO'];
                                $dados_basicos = $vetor_dados_basicos['@attributes'];
                                $vetor_detalhamento = $trabalho_tecnico['DETALHAMENTO-DO-TRABALHO-TECNICO'];
                                $detalhamento = $vetor_detalhamento['@attributes'];
                                if ($dados_basicos['ANO'] >= 2021) {
                                    echo '<tr>';
                                    echo '<td>';
                                    echo 'Ano: ' . $dados_basicos ['ANO'] . '<br>';
                                    echo 'Natureza: ' . $dados_basicos['NATUREZA'] . '<br>';
                                    echo 'Título do trabalho técnico: ' . $dados_basicos['TITULO-DO-TRABALHO-TECNICO'] . '<br>';
                                    echo 'Finalidade: ' . $detalhamento['FINALIDADE'] . '<br>';
                                    echo 'Instituição financiadora: ' . $detalhamento['INSTITUICAO-FINANCIADORA'] . '<br>';
                                    echo 'Autores: ';
                                    $vetor_autores = $trabalho_tecnico['AUTORES'];
                                    if (isset($vetor_autores['@attributes'])){
                                        $atributos_autor = $vetor_autores['@attributes'];
                                        echo $atributos_autor['NOME-PARA-CITACAO'];
                                    } else if (isset($vetor_autores[0])) {
                                        for ($k = 0; isset($vetor_autores[$k]); $k++) {
                                            $autor = $vetor_autores[$k];
                                            $atributos_autor = $autor['@attributes'];
                                            if ($k > 0)
                                                echo ',';
                                            echo $atributos_autor['NOME-PARA-CITACAO'];
                                        }
                                    }
                                    echo '</td>';
                                    echo '</tr>';
                                }                                
                            }
                        }
                    }
                    echo "</table>";
//                    echo '<button type="button" class="collapsible" style="margin-bottom:20px">Mais informações (Produção técnica: Trabalho técnico)</button>';
//                    echo '<div class="content">';
//                    echo '<pre style="margin-bottom:20px">';
//                    print_r($vetor_producao_tecnica['TRABALHO-TECNICO']);
//                    echo '</pre>';
//                    echo "</div>";                     
                    
                    // DEMAIS-TIPOS-DE-PRODUCAO-TECNICA
                    /*  
                        APRESENTACAO-DE-TRABALHO    
                        CURSO-DE-CURTA-DURACAO-MINISTRADO 
                        DESENVOLVIMENTO-DE-MATERIAL-DIDATICO-OU-INSTRUCIONAL
                        ORGANIZACAO-DE-EVENTO
                        PROGRAMA-DE-RADIO-OU-TV
                        RELATORIO-DE-PESQUISA
                     */
                    if (isset($vetor_producao_tecnica['DEMAIS-TIPOS-DE-PRODUCAO-TECNICA'])) {  
                        
                        $vetor_demais_tipos = $vetor_producao_tecnica['DEMAIS-TIPOS-DE-PRODUCAO-TECNICA'];
                        
                        //APRESENTACAO-DE-TRABALHO 
                        if (isset($vetor_demais_tipos['APRESENTACAO-DE-TRABALHO'])){
                            echo '<table class="table table-bordered">';
                            echo '<tr>';
                            echo '<th>';
                            echo "Produção técnica: Demais tipos de produção técnica <br>"
                               . "Apresentação de Trabalho";
                            echo '</th>';
                            echo '</tr>';

                            $vetor_tipo = $vetor_demais_tipos['APRESENTACAO-DE-TRABALHO'];
                            if (isset($vetor_tipo['DADOS-BASICOS-DA-APRESENTACAO-DE-TRABALHO'])) {
                                $vetor_dados = $vetor_tipo['DADOS-BASICOS-DA-APRESENTACAO-DE-TRABALHO'];
                                $vetor_detalhamento = $vetor_tipo['DETALHAMENTO-DA-APRESENTACAO-DE-TRABALHO'];
                                $dados_basicos = $vetor_dados['@attributes'];
                                $detalhamento = $vetor_detalhamento['@attributes'];                                
                                if ($dados_basicos['ANO'] >= 2021) {
                                    echo '<tr>';
                                    echo '<td>';
                                    echo 'Ano: ' . $dados_basicos ['ANO'] . '<br>';
                                    echo 'Natureza: ' . $dados_basicos['NATUREZA'] . '<br>';
                                    echo 'Título: ' . $dados_basicos['TITULO'] . '<br>';
                                    echo 'Evento: ' . $detalhamento['NOME-DO-EVENTO'] . '<br>';
                                    echo 'Cidade da apresentação: ' . $detalhamento['CIDADE-DA-APRESENTACAO'] . '<br>';
                                    echo 'Autores: ';                                
                                    $vetor_autores = $vetor_tipo['AUTORES'];
                                    if (isset($vetor_autores['@attributes'])){
                                        $atributos_autor = $vetor_autores['@attributes'];
                                        echo $atributos_autor['NOME-PARA-CITACAO'];
                                    } else if (isset($vetor_autores[0])) {
                                        for ($k = 0; isset($vetor_autores[$k]); $k++) {
                                            $autor = $vetor_autores[$k];
                                            $atributos_autor = $autor['@attributes'];
                                            if ($k > 0)
                                                echo ',';
                                            echo $atributos_autor['NOME-PARA-CITACAO'];
                                        }
                                    }
                                    echo '</td>';
                                    echo '</tr>';
                                }
                            } else if (isset($vetor_tipo[0])) {
                                for ($e=0;isset($vetor_tipo[$e]);$e++) {
                                    $vetor_items = $vetor_tipo[$e];
                                    $vetor_dados = $vetor_items['DADOS-BASICOS-DA-APRESENTACAO-DE-TRABALHO'];
                                    $vetor_detalhamento = $vetor_items['DETALHAMENTO-DA-APRESENTACAO-DE-TRABALHO'];
                                    $dados_basicos = $vetor_dados['@attributes'];
                                    $detalhamento = $vetor_detalhamento['@attributes'];                                      
                                    if ($dados_basicos['ANO'] >= 2021) {
                                        echo '<tr>';
                                        echo '<td>';
                                        echo 'Ano: ' . $dados_basicos ['ANO'] . '<br>';
                                        echo 'Natureza: ' . $dados_basicos['NATUREZA'] . '<br>';
                                        echo 'Título: ' . $dados_basicos['TITULO'] . '<br>';
                                        echo 'Evento: ' . $detalhamento['NOME-DO-EVENTO'] . '<br>';
                                        echo 'Cidade da apresentação: ' . $detalhamento['CIDADE-DA-APRESENTACAO'] . '<br>';
                                        echo 'Autores: ';                                     
                                        $vetor_autores = $vetor_items['AUTORES'];
                                        if (isset($vetor_autores['@attributes'])){
                                            $atributos_autor = $vetor_autores['@attributes'];
                                            echo $atributos_autor['NOME-PARA-CITACAO'];
                                        } else if (isset($vetor_autores[0])) {
                                            for ($k = 0; isset($vetor_autores[$k]); $k++) {
                                                $autor = $vetor_autores[$k];
                                                $atributos_autor = $autor['@attributes'];
                                                if ($k > 0)
                                                    echo ',';
                                                echo $atributos_autor['NOME-PARA-CITACAO'];
                                            }
                                        }
                                        echo '</td>';
                                        echo '</tr>';  
                                    }
                                }
                            }
                            
                            echo "</table>";  
                        } 

                        //CURSO-DE-CURTA-DURACAO-MINISTRADO 
                        if (isset($vetor_demais_tipos['CURSO-DE-CURTA-DURACAO-MINISTRADO'])){
                            echo '<table class="table table-bordered">';
                            echo '<tr>';
                            echo '<th>';
                            echo "Produção técnica: Demais tipos de produção técnica <br>"
                               . "Curso de curta duração ministrado";
                            echo '</th>';
                            echo '</tr>';

                            $vetor_tipo = $vetor_demais_tipos['CURSO-DE-CURTA-DURACAO-MINISTRADO'];
                            if (isset($vetor_tipo['DADOS-BASICOS-DE-CURSOS-CURTA-DURACAO-MINISTRADO'])) {
                                $vetor_dados = $vetor_tipo['DADOS-BASICOS-DE-CURSOS-CURTA-DURACAO-MINISTRADO'];
                                $vetor_detalhamento = $vetor_tipo['DETALHAMENTO-DE-CURSOS-CURTA-DURACAO-MINISTRADO'];
                                $dados_basicos = $vetor_dados['@attributes'];
                                $detalhamento = $vetor_detalhamento['@attributes'];                                
                                if ($dados_basicos['ANO'] >= 2021) {
                                    echo '<tr>';
                                    echo '<td>';
                                    echo 'Ano: ' . $dados_basicos ['ANO'] . '<br>';
                                    echo 'Nível do curso: ' . $dados_basicos['NIVEL-DO-CURSO'] . '<br>';
                                    echo 'Título: ' . $dados_basicos['TITULO'] . '<br>';
                                    echo 'Participação dos autores: ' . $detalhamento['PARTICIPACAO-DOS-AUTORES'] . '<br>';
                                    echo 'Instituição promotora do curso: ' . $detalhamento['INSTITUICAO-PROMOTORA-DO-CURSO'] . '<br>';
                                    echo 'Local do curso: ' . $detalhamento['LOCAL-DO-CURSO'] . '<br>';
                                    echo 'Cidade: ' . $detalhamento['CIDADE'] . '<br>';
                                    echo 'Autores: ';                                
                                    $vetor_autores = $vetor_tipo['AUTORES'];
                                    if (isset($vetor_autores['@attributes'])){
                                        $atributos_autor = $vetor_autores['@attributes'];
                                        echo $atributos_autor['NOME-PARA-CITACAO'];
                                    } else if (isset($vetor_autores[0])) {
                                        for ($k = 0; isset($vetor_autores[$k]); $k++) {
                                            $autor = $vetor_autores[$k];
                                            $atributos_autor = $autor['@attributes'];
                                            if ($k > 0)
                                                echo ',';
                                            echo $atributos_autor['NOME-PARA-CITACAO'];
                                        }
                                    }
                                    echo '</td>';
                                    echo '</tr>';
                                }
                            } else if (isset($vetor_tipo[0])) {
                                for ($f=0;isset($vetor_tipo[$f]);$f++) {
                                    $vetor_items = $vetor_tipo[$f];
                                    $vetor_dados = $vetor_items['DADOS-BASICOS-DE-CURSOS-CURTA-DURACAO-MINISTRADO'];
                                    $vetor_detalhamento = $vetor_items['DETALHAMENTO-DE-CURSOS-CURTA-DURACAO-MINISTRADO'];
                                    $dados_basicos = $vetor_dados['@attributes'];
                                    $detalhamento = $vetor_detalhamento['@attributes'];                                      
                                    if ($dados_basicos['ANO'] >= 2021) {
                                        echo '<tr>';
                                        echo '<td>';
                                        echo 'Ano: ' . $dados_basicos ['ANO'] . '<br>';
                                        echo 'Nível do curso: ' . $dados_basicos['NIVEL-DO-CURSO'] . '<br>';
                                        echo 'Título: ' . $dados_basicos['TITULO'] . '<br>';
                                        echo 'Participação dos autores: ' . $detalhamento['PARTICIPACAO-DOS-AUTORES'] . '<br>';
                                        echo 'Instituição promotora do curso: ' . $detalhamento['INSTITUICAO-PROMOTORA-DO-CURSO'] . '<br>';
                                        echo 'Local do curso: ' . $detalhamento['LOCAL-DO-CURSO'] . '<br>';
                                        echo 'Cidade: ' . $detalhamento['CIDADE'] . '<br>';
                                        echo 'Autores: ';                                     
                                        $vetor_autores = $vetor_items['AUTORES'];
                                        if (isset($vetor_autores['@attributes'])){
                                            $atributos_autor = $vetor_autores['@attributes'];
                                            echo $atributos_autor['NOME-PARA-CITACAO'];
                                        } else if (isset($vetor_autores[0])) {
                                            for ($k = 0; isset($vetor_autores[$k]); $k++) {
                                                $autor = $vetor_autores[$k];
                                                $atributos_autor = $autor['@attributes'];
                                                if ($k > 0)
                                                    echo ',';
                                                echo $atributos_autor['NOME-PARA-CITACAO'];
                                            }
                                        }
                                        echo '</td>';
                                        echo '</tr>';  
                                    }
                                }
                            }

                            
                            echo "</table>";  
                        }
                        
                        //DESENVOLVIMENTO-DE-MATERIAL-DIDATICO-OU-INSTRUCIONAL 
                        if (isset($vetor_demais_tipos['DESENVOLVIMENTO-DE-MATERIAL-DIDATICO-OU-INSTRUCIONAL'])){
                            echo '<table class="table table-bordered">';
                            echo '<tr>';
                            echo '<th>';
                            echo "Produção técnica: Demais tipos de produção técnica <br>"
                               . "Desenvolvimento de material didático ou institucional";
                            echo '</th>';
                            echo '</tr>';

                            $vetor_tipo = $vetor_demais_tipos['DESENVOLVIMENTO-DE-MATERIAL-DIDATICO-OU-INSTRUCIONAL'];
                            if (isset($vetor_tipo['DADOS-BASICOS-DO-MATERIAL-DIDATICO-OU-INSTRUCIONAL'])) {
                                $vetor_dados = $vetor_tipo['DADOS-BASICOS-DE-CURSOS-CURTA-DURACAO-MINISTRADO'];
                                $vetor_detalhamento = $vetor_tipo['DETALHAMENTO-DO-MATERIAL-DIDATICO-OU-INSTRUCIONAL'];
                                $dados_basicos = $vetor_dados['@attributes'];
                                $detalhamento = $vetor_detalhamento['@attributes'];                                
                                if ($dados_basicos['ANO'] >= 2021) {
                                    echo '<tr>';
                                    echo '<td>';
                                    echo 'Ano: ' . $dados_basicos ['ANO'] . '<br>';
                                    echo 'Natureza: ' . $dados_basicos['NATUREZA'] . '<br>';
                                    echo 'Título: ' . $dados_basicos['TITULO'] . '<br>';
                                    echo 'Finalidade: ' . $detalhamento['FINALIDADE'] . '<br>';
                                    echo 'Autores: ';                                
                                    $vetor_autores = $vetor_tipo['AUTORES'];
                                    if (isset($vetor_autores['@attributes'])){
                                        $atributos_autor = $vetor_autores['@attributes'];
                                        echo $atributos_autor['NOME-PARA-CITACAO'];
                                    } else if (isset($vetor_autores[0])) {
                                        for ($k = 0; isset($vetor_autores[$k]); $k++) {
                                            $autor = $vetor_autores[$k];
                                            $atributos_autor = $autor['@attributes'];
                                            if ($k > 0)
                                                echo ',';
                                            echo $atributos_autor['NOME-PARA-CITACAO'];
                                        }
                                    }
                                    echo '</td>';
                                    echo '</tr>';
                                }
                            } else if (isset($vetor_tipo[0])) {
                                for ($f=0;isset($vetor_tipo[$f]);$f++) {
                                    $vetor_items = $vetor_tipo[$f];
                                    $vetor_dados = $vetor_items['DADOS-BASICOS-DO-MATERIAL-DIDATICO-OU-INSTRUCIONAL'];
                                    $vetor_detalhamento = $vetor_items['DETALHAMENTO-DO-MATERIAL-DIDATICO-OU-INSTRUCIONAL'];
                                    $dados_basicos = $vetor_dados['@attributes'];
                                    $detalhamento = $vetor_detalhamento['@attributes'];                                      
                                    if ($dados_basicos['ANO'] >= 2021) {
                                        echo '<tr>';
                                        echo '<td>';
                                        echo 'Ano: ' . $dados_basicos ['ANO'] . '<br>';
                                        echo 'Natureza: ' . $dados_basicos['NATUREZA'] . '<br>';
                                        echo 'Título: ' . $dados_basicos['TITULO'] . '<br>';
                                        echo 'Finalidade: ' . $detalhamento['FINALIDADE'] . '<br>';
                                        echo 'Autores: ';                                
                                        $vetor_autores = $vetor_items['AUTORES'];
                                        if (isset($vetor_autores['@attributes'])){
                                            $atributos_autor = $vetor_autores['@attributes'];
                                            echo $atributos_autor['NOME-PARA-CITACAO'];
                                        } else if (isset($vetor_autores[0])) {
                                            for ($k = 0; isset($vetor_autores[$k]); $k++) {
                                                $autor = $vetor_autores[$k];
                                                $atributos_autor = $autor['@attributes'];
                                                if ($k > 0)
                                                    echo ',';
                                                echo $atributos_autor['NOME-PARA-CITACAO'];
                                            }
                                        }
                                        echo '</td>';
                                        echo '</tr>';  
                                    }
                                }
                            }
                            
                            echo "</table>";  
                        }      
                        
                        //ORGANIZACAO-DE-EVENTO 
                        if (isset($vetor_demais_tipos['ORGANIZACAO-DE-EVENTO'])){
                            echo '<table class="table table-bordered">';
                            echo '<tr>';
                            echo '<th>';
                            echo "Produção técnica: Demais tipos de produção técnica <br>"
                               . "Organização de evento";
                            echo '</th>';
                            echo '</tr>';

                            $vetor_tipo = $vetor_demais_tipos['ORGANIZACAO-DE-EVENTO'];
                            if (isset($vetor_tipo['ORGANIZACAO-DE-EVENTO'])) {
                                $vetor_dados = $vetor_tipo['DADOS-BASICOS-DA-ORGANIZACAO-DE-EVENTO'];
                                $vetor_detalhamento = $vetor_tipo['DETALHAMENTO-DO-MATERIAL-DIDATICO-OU-INSTRUCIONAL'];
                                $dados_basicos = $vetor_dados['@attributes'];
                                $detalhamento = $vetor_detalhamento['@attributes'];                                
                                if ($dados_basicos['ANO'] >= 2021) {
                                    echo '<tr>';
                                    echo '<td>';
                                    echo 'Ano: ' . $dados_basicos ['ANO'] . '<br>';
                                    echo 'Natureza: ' . $dados_basicos['NATUREZA'] . '<br>';
                                    echo 'Título: ' . $dados_basicos['TITULO'] . '<br>';
                                    echo 'Instituição promotora: ' . $detalhamento['INSTITUICAO-PROMOTORA'] . '<br>';
                                    echo 'Autores: ';                                
                                    $vetor_autores = $vetor_tipo['AUTORES'];
                                    if (isset($vetor_autores['@attributes'])){
                                        $atributos_autor = $vetor_autores['@attributes'];
                                        echo $atributos_autor['NOME-PARA-CITACAO'];
                                    } else if (isset($vetor_autores[0])) {
                                        for ($k = 0; isset($vetor_autores[$k]); $k++) {
                                            $autor = $vetor_autores[$k];
                                            $atributos_autor = $autor['@attributes'];
                                            if ($k > 0)
                                                echo ',';
                                            echo $atributos_autor['NOME-PARA-CITACAO'];
                                        }
                                    }
                                    echo '</td>';
                                    echo '</tr>';
                                }
                            } else if (isset($vetor_tipo[0])) {
                                for ($f=0;isset($vetor_tipo[$f]);$f++) {
                                    $vetor_items = $vetor_tipo[$f];
                                    $vetor_dados = $vetor_items['DADOS-BASICOS-DA-ORGANIZACAO-DE-EVENTO'];
                                    $vetor_detalhamento = $vetor_items['DETALHAMENTO-DA-ORGANIZACAO-DE-EVENTO'];
                                    $dados_basicos = $vetor_dados['@attributes'];
                                    $detalhamento = $vetor_detalhamento['@attributes'];                                      
                                    if ($dados_basicos['ANO'] >= 2021) {
                                        echo '<tr>';
                                        echo '<td>';
                                        echo 'Ano: ' . $dados_basicos ['ANO'] . '<br>';
                                        echo 'Natureza: ' . $dados_basicos['NATUREZA'] . '<br>';
                                        echo 'Título: ' . $dados_basicos['TITULO'] . '<br>';
                                        echo 'Instituição promotora: ' . $detalhamento['INSTITUICAO-PROMOTORA'] . '<br>';
                                        echo 'Autores: ';                                
                                        $vetor_autores = $vetor_items['AUTORES'];
                                        if (isset($vetor_autores['@attributes'])){
                                            $atributos_autor = $vetor_autores['@attributes'];
                                            echo $atributos_autor['NOME-PARA-CITACAO'];
                                        } else if (isset($vetor_autores[0])) {
                                            for ($k = 0; isset($vetor_autores[$k]); $k++) {
                                                $autor = $vetor_autores[$k];
                                                $atributos_autor = $autor['@attributes'];
                                                if ($k > 0)
                                                    echo ',';
                                                echo $atributos_autor['NOME-PARA-CITACAO'];
                                            }
                                        }
                                        echo '</td>';
                                        echo '</tr>';  
                                    }
                                }
                            }
                            
                            echo "</table>";  
                        }                         

                        //PROGRAMA-DE-RADIO-OU-TV 
                        if (isset($vetor_demais_tipos['PROGRAMA-DE-RADIO-OU-TV'])){
                            echo '<table class="table table-bordered">';
                            echo '<tr>';
                            echo '<th>';
                            echo "Produção técnica: Demais tipos de produção técnica <br>"
                               . "Programa de rádio ou TV";
                            echo '</th>';
                            echo '</tr>';

                            $vetor_tipo = $vetor_demais_tipos['PROGRAMA-DE-RADIO-OU-TV'];
                            if (isset($vetor_tipo['PROGRAMA-DE-RADIO-OU-TV'])) {
                                $vetor_dados = $vetor_tipo['DADOS-BASICOS-DO-PROGRAMA-DE-RADIO-OU-TV'];
                                $vetor_detalhamento = $vetor_tipo['DETALHAMENTO-DO-PROGRAMA-DE-RADIO-OU-TV'];
                                $dados_basicos = $vetor_dados['@attributes'];
                                $detalhamento = $vetor_detalhamento['@attributes'];                                
                                if ($dados_basicos['ANO'] >= 2021) {
                                    echo '<tr>';
                                    echo '<td>';
                                    echo 'Ano: ' . $dados_basicos ['ANO'] . '<br>';
                                    echo 'Natureza: ' . $dados_basicos['NATUREZA'] . '<br>';
                                    echo 'Título: ' . $dados_basicos['TITULO'] . '<br>';
                                    echo 'Emissora: ' . $detalhamento['EMISSORA'] . '<br>';
                                    echo 'Tema: ' . $detalhamento['TEMA'] . '<br>';
                                    echo 'Autores: ';                                
                                    $vetor_autores = $vetor_tipo['AUTORES'];
                                    if (isset($vetor_autores['@attributes'])){
                                        $atributos_autor = $vetor_autores['@attributes'];
                                        echo $atributos_autor['NOME-PARA-CITACAO'];
                                    } else if (isset($vetor_autores[0])) {
                                        for ($k = 0; isset($vetor_autores[$k]); $k++) {
                                            $autor = $vetor_autores[$k];
                                            $atributos_autor = $autor['@attributes'];
                                            if ($k > 0)
                                                echo ',';
                                            echo $atributos_autor['NOME-PARA-CITACAO'];
                                        }
                                    }
                                    echo '</td>';
                                    echo '</tr>';
                                }
                            } else if (isset($vetor_tipo[0])) {
                                for ($f=0;isset($vetor_tipo[$f]);$f++) {
                                    $vetor_items = $vetor_tipo[$f];
                                    $vetor_dados = $vetor_items['DADOS-BASICOS-DO-PROGRAMA-DE-RADIO-OU-TV'];
                                    $vetor_detalhamento = $vetor_items['DETALHAMENTO-DO-PROGRAMA-DE-RADIO-OU-TV'];
                                    $dados_basicos = $vetor_dados['@attributes'];
                                    $detalhamento = $vetor_detalhamento['@attributes'];                                      
                                    if ($dados_basicos['ANO'] >= 2021) {
                                        echo '<tr>';
                                        echo '<td>';
                                        echo 'Ano: ' . $dados_basicos ['ANO'] . '<br>';
                                        echo 'Natureza: ' . $dados_basicos['NATUREZA'] . '<br>';
                                        echo 'Título: ' . $dados_basicos['TITULO'] . '<br>';
                                        echo 'Emissora: ' . $detalhamento['EMISSORA'] . '<br>';
                                        echo 'Tema: ' . $detalhamento['TEMA'] . '<br>';
                                        echo 'Autores: ';                                
                                        $vetor_autores = $vetor_items['AUTORES'];
                                        if (isset($vetor_autores['@attributes'])){
                                            $atributos_autor = $vetor_autores['@attributes'];
                                            echo $atributos_autor['NOME-PARA-CITACAO'];
                                        } else if (isset($vetor_autores[0])) {
                                            for ($k = 0; isset($vetor_autores[$k]); $k++) {
                                                $autor = $vetor_autores[$k];
                                                $atributos_autor = $autor['@attributes'];
                                                if ($k > 0)
                                                    echo ',';
                                                echo $atributos_autor['NOME-PARA-CITACAO'];
                                            }
                                        }
                                        echo '</td>';
                                        echo '</tr>';  
                                    }
                                }
                            }
                            
                            echo "</table>";  
                        }                              
                        
                        //RELATORIO-DE-PESQUISA 
                        if (isset($vetor_demais_tipos['RELATORIO-DE-PESQUISA'])){
                            echo '<table class="table table-bordered">';
                            echo '<tr>';
                            echo '<th>';
                            echo "Produção técnica: Demais tipos de produção técnica <br>"
                               . "Relatório de pesquisa";
                            echo '</th>';
                            echo '</tr>';

                            $vetor_tipo = $vetor_demais_tipos['RELATORIO-DE-PESQUISA'];
                            if (isset($vetor_tipo['RELATORIO-DE-PESQUISA'])) {
                                $vetor_dados = $vetor_tipo['DADOS-BASICOS-DO-RELATORIO-DE-PESQUISA'];
                                $vetor_detalhamento = $vetor_tipo['DETALHAMENTO-DO-RELATORIO-DE-PESQUISA'];
                                $dados_basicos = $vetor_dados['@attributes'];
                                $detalhamento = $vetor_detalhamento['@attributes'];                                
                                if ($dados_basicos['ANO'] >= 2021) {
                                    echo '<tr>';
                                    echo '<td>';
                                    echo 'Ano: ' . $dados_basicos ['ANO'] . '<br>';
                                    echo 'Natureza: ' . $dados_basicos['NATUREZA'] . '<br>';
                                    echo 'Título: ' . $dados_basicos['TITULO'] . '<br>';
                                    echo 'Instituição promotora: ' . $detalhamento['INSTITUICAO-PROMOTORA'] . '<br>';
                                    echo 'Autores: ';                                
                                    $vetor_autores = $vetor_tipo['AUTORES'];
                                    if (isset($vetor_autores['@attributes'])){
                                        $atributos_autor = $vetor_autores['@attributes'];
                                        echo $atributos_autor['NOME-PARA-CITACAO'];
                                    } else if (isset($vetor_autores[0])) {
                                        for ($k = 0; isset($vetor_autores[$k]); $k++) {
                                            $autor = $vetor_autores[$k];
                                            $atributos_autor = $autor['@attributes'];
                                            if ($k > 0)
                                                echo ',';
                                            echo $atributos_autor['NOME-PARA-CITACAO'];
                                        }
                                    }
                                    echo '</td>';
                                    echo '</tr>';
                                }
                            } else if (isset($vetor_tipo[0])) {
                                for ($f=0;isset($vetor_tipo[$f]);$f++) {
                                    $vetor_items = $vetor_tipo[$f];
                                    $vetor_dados = $vetor_items['DADOS-BASICOS-DO-RELATORIO-DE-PESQUISA'];
                                    $vetor_detalhamento = $vetor_items['DETALHAMENTO-DO-RELATORIO-DE-PESQUISA'];
                                    $dados_basicos = $vetor_dados['@attributes'];
                                    $detalhamento = $vetor_detalhamento['@attributes'];                                      
                                    if ($dados_basicos['ANO'] >= 2021) {
                                        echo '<tr>';
                                        echo '<td>';
                                        echo 'Ano: ' . $dados_basicos ['ANO'] . '<br>';
                                        echo 'Natureza: ' . $dados_basicos['NATUREZA'] . '<br>';
                                        echo 'Título: ' . $dados_basicos['TITULO'] . '<br>';
                                        echo 'Instituição promotora: ' . $detalhamento['INSTITUICAO-PROMOTORA'] . '<br>';
                                        echo 'Autores: ';                                
                                        $vetor_autores = $vetor_items['AUTORES'];
                                        if (isset($vetor_autores['@attributes'])){
                                            $atributos_autor = $vetor_autores['@attributes'];
                                            echo $atributos_autor['NOME-PARA-CITACAO'];
                                        } else if (isset($vetor_autores[0])) {
                                            for ($k = 0; isset($vetor_autores[$k]); $k++) {
                                                $autor = $vetor_autores[$k];
                                                $atributos_autor = $autor['@attributes'];
                                                if ($k > 0)
                                                    echo ',';
                                                echo $atributos_autor['NOME-PARA-CITACAO'];
                                            }
                                        }
                                        echo '</td>';
                                        echo '</tr>';  
                                    }
                                }
                            }
                            
                            echo "</table>";  
                        }                          
                        
                    }
                                     
                    /*
                     * 1)OUTRA-PRODUCAO (RAIZ)
                     * 2)ORIENTACOES-CONCLUIDAS
                     *  - ORIENTACOES-CONCLUIDAS-PARA-MESTRADO
                     *  - OUTRAS-ORIENTACOES-CONCLUIDAS 
                     */ 
                    
                    if (isset($array['OUTRA-PRODUCAO'])) {
                        $vetor_outra_producao = $array['OUTRA-PRODUCAO'];                    
                        if (isset($vetor_outra_producao['ORIENTACOES-CONCLUIDAS'])) {
                            $vetor_orientacoes_concluidas = $vetor_outra_producao['ORIENTACOES-CONCLUIDAS'];
                            //ORIENTACOES-CONCLUIDAS-PARA-MESTRADO
                            if (isset($vetor_orientacoes_concluidas['ORIENTACOES-CONCLUIDAS-PARA-MESTRADO'])) {
                                echo '<table class="table table-bordered">';
                                echo '<tr>';
                                echo '<th>';
                                echo "Outras produções: Orientações concluídas para mestrado<br>";
                                echo '</th>';
                                echo '</tr>';                            

                                $vetor_tipo = $vetor_orientacoes_concluidas['ORIENTACOES-CONCLUIDAS-PARA-MESTRADO'];
                                if (isset($vetor_tipo['DADOS-BASICOS-DE-ORIENTACOES-CONCLUIDAS-PARA-MESTRADO'])) {
                                    $vetor_dados = $vetor_tipo['DADOS-BASICOS-DE-ORIENTACOES-CONCLUIDAS-PARA-MESTRADO'];
                                    $vetor_detalhamento = $vetor_tipo['DETALHAMENTO-DE-ORIENTACOES-CONCLUIDAS-PARA-MESTRADO'];
                                    $dados_basicos = $vetor_dados['@attributes'];
                                    $detalhamento = $vetor_detalhamento['@attributes'];                                
                                    if ($dados_basicos['ANO'] >= 2021) {
                                        echo '<tr>';
                                        echo '<td>';
                                        echo 'Ano: ' . $dados_basicos ['ANO'] . '<br>';
                                        echo 'Natureza: ' . $dados_basicos['NATUREZA'] . '<br>';
                                        echo 'Tipo: ' . $dados_basicos['TIPO'] . '<br>';
                                        echo 'Título: ' . $dados_basicos['TITULO'] . '<br>';
                                        echo 'Tipo de orientação: ' . $detalhamento['TIPO-DE-ORIENTACAO'] . '<br>';
                                        echo 'Nome do orientado: ' . $detalhamento['NOME-DO-ORIENTADO'] . '<br>';
                                        echo 'Nome da instituição: ' . $detalhamento['NOME-DA-INSTITUICAO'] . '<br>';
                                        echo 'Nome do curso: ' . $detalhamento['NOME-DO-CURSO'] . '<br>';
                                        echo 'Nome da agência' . $detalhamento['NOME-DA-AGENCIA'] . '<br>';
                                        echo '</td>';
                                        echo '</tr>';
                                    }
                                } else if (isset($vetor_tipo[0])) {
                                    for ($g=0;isset($vetor_tipo[$g]);$g++) {
                                        $vetor_items = $vetor_tipo[$g];
                                        $vetor_dados = $vetor_items['DADOS-BASICOS-DE-ORIENTACOES-CONCLUIDAS-PARA-MESTRADO'];
                                        $vetor_detalhamento = $vetor_items['DETALHAMENTO-DE-ORIENTACOES-CONCLUIDAS-PARA-MESTRADO'];
                                        $dados_basicos = $vetor_dados['@attributes'];
                                        $detalhamento = $vetor_detalhamento['@attributes'];                                      
                                        if ($dados_basicos['ANO'] >= 2021) {
                                            echo '<tr>';
                                            echo '<td>';
                                            echo 'Ano: ' . $dados_basicos ['ANO'] . '<br>';
                                            echo 'Natureza: ' . $dados_basicos['NATUREZA'] . '<br>';
                                            echo 'Tipo: ' . $dados_basicos['TIPO'] . '<br>';
                                            echo 'Título: ' . $dados_basicos['TITULO'] . '<br>';
                                            echo 'Tipo de orientação: ' . $detalhamento['TIPO-DE-ORIENTACAO'] . '<br>';
                                            echo 'Nome do orientado: ' . $detalhamento['NOME-DO-ORIENTADO'] . '<br>';
                                            echo 'Nome da instituição: ' . $detalhamento['NOME-DA-INSTITUICAO'] . '<br>';
                                            echo 'Nome do curso: ' . $detalhamento['NOME-DO-CURSO'] . '<br>';
                                            echo 'Nome da agência' . $detalhamento['NOME-DA-AGENCIA'] . '<br>';
                                            echo '</td>';
                                            echo '</tr>';  
                                        }
                                    }
                                }
                                echo "</table>";
                            }    
                            
                            // OUTRAS-ORIENTACOES-CONCLUIDAS
                            if (isset($vetor_orientacoes_concluidas['OUTRAS-ORIENTACOES-CONCLUIDAS'])) {
                                
                                echo '<table class="table table-bordered">';
                                echo '<tr>';
                                echo '<th>';
                                echo "Outras produções: Outras orientações concluídas<br>";
                                echo '</th>';
                                echo '</tr>'; 
                                $vetor_tipo = $vetor_orientacoes_concluidas['OUTRAS-ORIENTACOES-CONCLUIDAS'];
                                if (isset($vetor_tipo['DADOS-BASICOS-DE-OUTRAS-ORIENTACOES-CONCLUIDAS'])) {
                                    $vetor_dados = $vetor_tipo['DADOS-BASICOS-DE-OUTRAS-ORIENTACOES-CONCLUIDAS'];
                                    $vetor_detalhamento = $vetor_tipo['DETALHAMENTO-DE-OUTRAS-ORIENTACOES-CONCLUIDAS'];
                                    $dados_basicos = $vetor_dados['@attributes'];
                                    $detalhamento = $vetor_detalhamento['@attributes'];                                
                                    if ($dados_basicos['ANO'] >= 2021) {
                                        echo '<tr>';
                                        echo '<td>';
                                        echo 'Ano: ' . $dados_basicos ['ANO'] . '<br>';
                                        echo 'Natureza: ' . $dados_basicos['NATUREZA'] . '<br>';
                                        echo 'Tipo: ' . $dados_basicos['TIPO'] . '<br>';
                                        echo 'Título: ' . $dados_basicos['TITULO'] . '<br>';
                                        echo 'Tipo de orientação concluída: ' . $detalhamento['TIPO-DE-ORIENTACAO-CONCLUIDA'] . '<br>';
                                        echo 'Nome do orientado: ' . $detalhamento['NOME-DO-ORIENTADO'] . '<br>';
                                        echo 'Nome da instituição: ' . $detalhamento['NOME-DA-INSTITUICAO'] . '<br>';
                                        echo 'Nome do curso: ' . $detalhamento['NOME-DO-CURSO'] . '<br>';
                                        echo 'Nome da agência' . $detalhamento['NOME-DA-AGENCIA'] . '<br>';
                                        echo '</td>';
                                        echo '</tr>';
                                    }
                                } else if (isset($vetor_tipo[0])) {
                                    for ($g=0;isset($vetor_tipo[$g]);$g++) {
                                        $vetor_items = $vetor_tipo[$g];
                                        $vetor_dados = $vetor_items['DADOS-BASICOS-DE-OUTRAS-ORIENTACOES-CONCLUIDAS'];
                                        $vetor_detalhamento = $vetor_items['DETALHAMENTO-DE-OUTRAS-ORIENTACOES-CONCLUIDAS'];
                                        $dados_basicos = $vetor_dados['@attributes'];
                                        $detalhamento = $vetor_detalhamento['@attributes'];                                      
                                        if ($dados_basicos['ANO'] >= 2021) {
                                            echo '<tr>';
                                            echo '<td>';
                                            echo 'Ano: ' . $dados_basicos ['ANO'] . '<br>';
                                            echo 'Natureza: ' . $dados_basicos['NATUREZA'] . '<br>';
                                            echo 'Tipo: ' . $dados_basicos['TIPO'] . '<br>';
                                            echo 'Título: ' . $dados_basicos['TITULO'] . '<br>';
                                            echo 'Tipo de orientação concluída: ' . $detalhamento['TIPO-DE-ORIENTACAO-CONCLUIDA'] . '<br>';
                                            echo 'Nome do orientado: ' . $detalhamento['NOME-DO-ORIENTADO'] . '<br>';
                                            echo 'Nome da instituição: ' . $detalhamento['NOME-DA-INSTITUICAO'] . '<br>';
                                            echo 'Nome do curso: ' . $detalhamento['NOME-DO-CURSO'] . '<br>';
                                            echo 'Nome da agência' . $detalhamento['NOME-DA-AGENCIA'] . '<br>';
                                            echo '</td>';
                                            echo '</tr>';  
                                        }
                                    }
                                }
                                echo "</table>";
                            }   
                            
                            echo '<button type="button" class="collapsible" style="margin-bottom:20px">Mais informações (Outras produções: Orientações concluídas)</button>';
                            echo '<div class="content">';
                            echo '<pre style="margin-bottom:20px">';
                            print_r($vetor_outra_producao);
                            echo '</pre>';
                            echo "</div>"; 
                        }
                    }
                   

                    // Fecha o panel-body e o panel
                    echo '</div></div>';
                }
            }
            echo "</div>";  
            closedir($handle);            
        }
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