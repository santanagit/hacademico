<?php

session_start();
require_once $_SESSION['diretorio_base'] . '/model/horarioModel.php';
require_once $_SESSION['diretorio_base'] . '/model/oferta_disciplinaModel.php';
require_once $_SESSION['diretorio_base'] . '/model/periodoModel.php';
require_once $_SESSION['diretorio_base'] . '/model/log_acaoModel.php';

class horario_imprimirController {

    private $msg;
    private $logM;

    public function __construct() {
        $this->msg = '';
    }

    public function getTurmasAtivas() {

        $periodo = explode("/", $_POST['periodo']);
        $semestre = $periodo[1];

        $options = '';
        $ofertaM = new oferta_disciplinaModel();
        $result = $ofertaM->getTurmasAtivas($_POST['id_periodo'], $semestre);
        if ($result) {
            while ($linha = $result->fetch_assoc()) {
                $options .= '<option value="' . $linha['id_turma'] . '">';
                $options .= $linha['turma'];
                $options .= '</option>';
            }
        }

        $resposta = array('options' => $options);
        return json_encode($resposta);
    }

    public function carregarPeriodo() {
        $select = '<label for="id_periodo">Periodo:</label>';
        $select .= '<select id="id_periodo" name="id_periodo" class="form-control" style="width:100%" onChange="getTurmasAtivas()">';
        $periodoM = new periodoModel();

        $criterios = array();
        if ($_SESSION['perfil'] == 'Professor') {
            $criterios['publicado'] = 1;
        }
        if ($_SESSION['perfil'] == 'Coordenador de Curso') {
            $criterios['publicado_coordenador'] = 1;
        }

        $resultado_periodos = $periodoM->listar(array(), array('id_periodo' => 'DESC'), array(), $criterios);

        while ($linha = mysqli_fetch_assoc($resultado_periodos)) {
            $select .= "<option value='{$linha['id_periodo']}'>";
            $select .= $linha['ano'] . '/' . $linha['semestre'];
            $select .= '</option>';
        }
        $select .= '</select>';
        $resposta = array('select' => $select);
        return json_encode($resposta);
    }

    public function getHorarioEmail() {

        $horarios = array();
        $horarioM = new horarioModel();
        $result = $horarioM->getHorarioEmail($_SESSION['email']);
        if ($result) {
            while ($linha = $result->fetch_assoc()) {
                $horarios[] = $linha['id_horario'];
            }
        }

        $resposta = array('horarios' => $horarios);
        return json_encode($resposta);
    }

    public function listar() {
        $codigo_sigaa = isset($_POST['codigo_sigaa']);
        $primeiro_nome_professor = isset($_POST['primeiro_nome_professor']);

        $horarioM = new horarioModel();
        $result = $horarioM->listar($_POST['id_turma']);

        $tabela = '';
        $turmas = array();
        $cache_codigos = array();

        $escapar = function ($valor) {
            return htmlspecialchars(
            (string) $valor,
            ENT_QUOTES | ENT_SUBSTITUTE,
            'UTF-8'
            );
        };

        /*
         * Consulta o código SIGAA uma vez para cada combinação
         * de disciplina e curso, mesmo que apareça em várias células.
         */
        $buscarCodigos = function ($id_disciplina, $id_curso) use (
                $horarioM,
                &$cache_codigos
        ) {
            $chave = (int) $id_disciplina . '_' . (int) $id_curso;

            if (!isset($cache_codigos[$chave])) {
                $cache_codigos[$chave] = array();

                $resultado = $horarioM->getGrade(
                        (int) $id_disciplina,
                        (int) $id_curso
                );

                while ($linha = $resultado->fetch_assoc()) {
                    $cache_codigos[$chave][] = $linha['cod_sigaa'];
                }
            }

            return $cache_codigos[$chave];
        };

        /*
         * Monta o conteúdo de uma oferta sem alterar
         * as cores usadas na impressão original.
         */
        $montarOferta = function ($linha) use (
                $codigo_sigaa,
                $primeiro_nome_professor,
                $escapar,
                $buscarCodigos
        ) {
            $professor = (string) $linha['professor'];

            if ($primeiro_nome_professor) {
                $partes = preg_split('/\s+/', trim($professor));
                $professor = isset($partes[0]) ? $partes[0] : '';
            }

            $html = '<div style="color:blue; font-weight:bold !important">';
            $html .= $escapar($linha['disciplina']);
            $html .= '</div>';

            $html .= '<div style="color:green !important">';
            $html .= $escapar($professor);
            $html .= '</div>';

            $html .= '<div style="color:brown !important">';
            $html .= $escapar($linha['sala']);
            $html .= '</div>';

            if ($codigo_sigaa) {
                $codigos = $buscarCodigos(
                        $linha['id_disciplina'],
                        $linha['id_curso']
                );

                foreach ($codigos as $codigo) {
                    $html .= '<div style="color:SlateBlue !important">';
                    $html .= 'COD SIGAA: ' . $escapar($codigo);
                    $html .= '</div>';
                }
            }

            return $html;
        };

        /*
         * Agrupa os registros para impedir que a segunda posição
         * seja impressa na coluna do dia seguinte.
         */
        $tem_horarios = $result->num_rows > 0;

        while ($linha = $result->fetch_assoc()) {
            $id_dia = (int) $linha['id_dia'];

            /*
             * A impressão original apresenta segunda a sexta.
             * Sábado não deve ser deslocado para outra coluna.
             */
            if ($id_dia < 2 || $id_dia > 6) {
                continue;
            }

            $id_turma = (int) $linha['id_turma'];
            $id_hora = (int) $linha['id_hora'];
            $posicao = isset($linha['posicao']) ? (int) $linha['posicao'] : 1;

            if (!isset($turmas[$id_turma])) {
                $turmas[$id_turma] = array(
                    'nome' => $linha['turma'],
                    'horas' => array()
                );
            }

            if (!isset($turmas[$id_turma]['horas'][$id_hora])) {
                $turmas[$id_turma]['horas'][$id_hora] = array(
                    'horario' => $linha['horario'],
                    'dias' => array()
                );
            }

            if (!isset(
                            $turmas[$id_turma]['horas'][$id_hora]['dias'][$id_dia]
                    )) {
                $turmas[$id_turma]['horas'][$id_hora]['dias'][$id_dia] = array();
            }

            $turmas[$id_turma]['horas'][$id_hora]['dias'][$id_dia][$posicao] = $linha;
        }

        if ($tem_horarios) {
            $horas_intervalo = array(9, 13, 15, 18, 20);

            foreach ($turmas as $turma) {
                $tabela .= '<table class="table table-bordered">' . "\n";
                $tabela .= '<thead>' . "\n";

                $tabela .= '<tr>' . "\n";
                $tabela .= '<th colspan="6" ';
                $tabela .= 'style="background-color:#D9EDF7 !important">';
                $tabela .= '<center>' . $escapar($turma['nome']) . '</center>';
                $tabela .= '</th>' . "\n";
                $tabela .= '</tr>' . "\n";

                $tabela .= '<tr>' . "\n";
                $tabela .= '<th width="10%" ';
                $tabela .= 'style="background-color:#DFF0D8 !important">';
                $tabela .= 'Horário</th>' . "\n";

                $dias = array(
                    2 => 'Segunda',
                    3 => 'Terça',
                    4 => 'Quarta',
                    5 => 'Quinta',
                    6 => 'Sexta'
                );

                foreach ($dias as $nome_dia) {
                    $tabela .= '<th width="18%" ';
                    $tabela .= 'style="background-color:#DFF0D8 !important">';
                    $tabela .= $nome_dia . '</th>' . "\n";
                }

                $tabela .= '</tr>' . "\n";
                $tabela .= '</thead>' . "\n";
                $tabela .= '<tbody>' . "\n";

                ksort($turma['horas'], SORT_NUMERIC);
                $clinha = 0;

                foreach ($turma['horas'] as $id_hora => $hora) {
                    $clinha++;

                    if (
                            in_array((int) $id_hora, $horas_intervalo, true) &&
                            $clinha > 2
                    ) {
                        $tabela .= '<tr align="center">' . "\n";
                        $tabela .= '<td colspan="6"><b>Intervalo</b></td>';
                        $tabela .= '</tr>' . "\n";
                    }

                    $tabela .= '<tr>' . "\n";
                    $tabela .= '<td><b>';
                    $tabela .= $escapar($hora['horario']);
                    $tabela .= '</b></td>' . "\n";

                    foreach ($dias as $id_dia => $nome_dia) {
                        $posicoes = isset($hora['dias'][$id_dia]) ? $hora['dias'][$id_dia] : array();

                        if (count($posicoes) === 0) {
                            $tabela .= '<td></td>' . "\n";
                            continue;
                        }

                        /*
                         * Sem posição 2: mantém a célula original,
                         * com a cor do professor no próprio TD.
                         */
                        if (!isset($posicoes[2])) {
                            $linha = isset($posicoes[1]) ? $posicoes[1] : reset($posicoes);

                            $tabela .= '<td style="background-color:';
                            $tabela .= $escapar($linha['cor']);
                            $tabela .= ' !important">';
                            $tabela .= $montarOferta($linha);
                            $tabela .= '</td>' . "\n";

                            continue;
                        }

                        /*
                         * Com posição 2: tabela interna para preservar
                         * o alinhamento lado a lado também na impressão.
                         * Cada posição conserva a sua própria cor e sala.
                         */
                        $tabela .= '<td style="padding:0">';
                        $tabela .= '<table style="width:100%;';
                        $tabela .= 'border-collapse:collapse;table-layout:fixed">';
                        $tabela .= '<tbody><tr>';

                        for ($posicao = 1; $posicao <= 2; $posicao++) {
                            $estilo = 'width:50%;vertical-align:top;padding:4px;';

                            if ($posicao === 2) {
                                $estilo .= 'border-left:1px solid #ddd;';
                            }

                            if (isset($posicoes[$posicao])) {
                                $linha = $posicoes[$posicao];

                                $estilo .= 'background-color:' .
                                        $escapar($linha['cor']) . ' !important;';

                                $tabela .= '<td style="' . $estilo . '">';
                                $tabela .= $montarOferta($linha);
                                $tabela .= '</td>';
                            } else {
                                $tabela .= '<td style="' . $estilo . '"></td>';
                            }
                        }

                        $tabela .= '</tr></tbody></table>';
                        $tabela .= '</td>' . "\n";
                    }

                    $tabela .= '</tr>' . "\n";
                }

                $tabela .= '</tbody>' . "\n";
                $tabela .= '</table>' . "\n";
            }
        } else {
            /*
             * Preserva a apresentação das disciplinas EAD
             * quando a turma não possui horários cadastrados.
             */
            $result_ead = $horarioM->getDisciplinasTurmaEAD(
                    $_POST['id_turma']
            );

            if ($result_ead->num_rows > 0) {
                $tabela .= '<table ';
                $tabela .= 'class="table table-responsive table-bordered">';
                $tabela .= '<thead>';

                $tabela .= '<tr class="success">';
                $tabela .= '<th colspan="5" style="text-align:center">';
                $tabela .= 'Disciplinas/Professor</th>';
                $tabela .= '</tr>';

                $tabela .= '<tr class="info">';
                $tabela .= '<th width="10%">Módulo</th>';
                $tabela .= '<th width="35%">Disciplina</th>';
                $tabela .= '<th width="35%">Professor</th>';
                $tabela .= '<th width="10%">CHS</th>';
                $tabela .= '<th width="10%">CHT</th>';
                $tabela .= '</tr>';

                $tabela .= '</thead><tbody>';

                while ($linha_ead = $result_ead->fetch_assoc()) {
                    $codigos = $buscarCodigos(
                            $linha_ead['id_disciplina'],
                            $linha_ead['id_curso']
                    );

                    $tabela .= '<tr>';
                    $tabela .= '<td>';
                    $tabela .= $escapar($linha_ead['modulo']);
                    $tabela .= '</td>';

                    $tabela .= '<td>';
                    $tabela .= $escapar($linha_ead['disciplina']);
                    $tabela .= ' (' . $escapar(implode(' ', $codigos)) . ')';
                    $tabela .= '</td>';

                    $tabela .= '<td>';
                    $tabela .= $escapar($linha_ead['professor']);
                    $tabela .= '</td>';

                    $tabela .= '<td>';
                    $tabela .= $escapar($linha_ead['chs']);
                    $tabela .= '</td>';

                    $tabela .= '<td>';
                    $tabela .= $escapar($linha_ead['cht']);
                    $tabela .= '</td>';
                    $tabela .= '</tr>';
                }

                $tabela .= '</tbody></table>';
            }
        }

        $this->logM = new log_acaoModel();
        $this->logM->inserir(array(
            'id_usuario' => $_SESSION['id_usuario'],
            'acao' => 'Consulta Horário Turma',
            'data_hora' => date('Y-m-d H:i:s')
        ));

        return json_encode(
                array('tabela' => $tabela),
                JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE
        );
    }
}

// Callback
if (isset($_POST['metodo'])) {
    $metodo = $_POST['metodo'];
    $objeto = new horario_imprimirController();
    echo $objeto->$metodo();
}