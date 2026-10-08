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
        $result = $horarioM->listar((int) $_POST['id_turma']);

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

        $nomeProfessor = function ($nome) use ($primeiro_nome_professor) {
            $nome = trim((string) $nome);

            if ($primeiro_nome_professor) {
                $partes = preg_split('/\s+/', $nome);
                return isset($partes[0]) ? $partes[0] : '';
            }

            return $nome;
        };

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
         * A primeira linha determina disciplina, sala e cor.
         * Se houver uma segunda linha da mesma disciplina,
         * acrescenta somente o seu professor.
         */
        $montarOferta = function ($linha, $segunda = null) use (
                $codigo_sigaa,
                $escapar,
                $nomeProfessor,
                $buscarCodigos
        ) {
            $html = '<div style="color:blue; font-weight:bold !important">';
            $html .= $escapar($linha['disciplina']);
            $html .= '</div>';

            $html .= '<div style="color:green !important">';
            $html .= $escapar($nomeProfessor($linha['professor']));
            $html .= '</div>';

            if ($segunda !== null) {
                $html .= '<div style="color:green !important">';
                $html .= $escapar($nomeProfessor($segunda['professor']));
                $html .= '</div>';
            }

            // Na apresentação conjunta, usa somente a sala da esquerda.
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

        $tem_horarios = $result->num_rows > 0;

        while ($linha = $result->fetch_assoc()) {
            $id_dia = (int) $linha['id_dia'];

            // Preserva os cinco dias úteis do relatório.
            if ($id_dia < 2 || $id_dia > 6) {
                continue;
            }

            if (!isset($linha['posicao'])) {
                throw new RuntimeException(
                                'A consulta horarioModel::listar() precisa retornar ' .
                                'horario.posicao para imprimir as turmas divididas.'
                        );
            }

            $id_turma = (int) $linha['id_turma'];
            $id_hora = (int) $linha['id_hora'];
            $posicao = (int) $linha['posicao'];

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

            $dias = array(
                2 => 'Segunda',
                3 => 'Terça',
                4 => 'Quarta',
                5 => 'Quinta',
                6 => 'Sexta'
            );

            foreach ($turmas as $turma) {
                $tabela .= '<table class="table table-bordered">';
                $tabela .= '<thead><tr>';
                $tabela .= '<th colspan="6" ';
                $tabela .= 'style="background-color:#D9EDF7 !important">';
                $tabela .= '<center>' . $escapar($turma['nome']) . '</center>';
                $tabela .= '</th></tr><tr>';

                $tabela .= '<th width="10%" ';
                $tabela .= 'style="background-color:#DFF0D8 !important">';
                $tabela .= 'Horário</th>';

                foreach ($dias as $nome_dia) {
                    $tabela .= '<th width="18%" ';
                    $tabela .= 'style="background-color:#DFF0D8 !important">';
                    $tabela .= $nome_dia . '</th>';
                }

                $tabela .= '</tr></thead><tbody>';

                ksort($turma['horas'], SORT_NUMERIC);
                $clinha = 0;

                foreach ($turma['horas'] as $id_hora => $hora) {
                    $clinha++;

                    if (
                            in_array((int) $id_hora, $horas_intervalo, true) &&
                            $clinha > 2
                    ) {
                        $tabela .= '<tr align="center">';
                        $tabela .= '<td colspan="6"><b>Intervalo</b></td>';
                        $tabela .= '</tr>';
                    }

                    $tabela .= '<tr><td><b>';
                    $tabela .= $escapar($hora['horario']);
                    $tabela .= '</b></td>';

                    foreach ($dias as $id_dia => $nome_dia) {
                        $posicoes = isset($hora['dias'][$id_dia]) ? $hora['dias'][$id_dia] : array();

                        if (count($posicoes) === 0) {
                            $tabela .= '<td></td>';
                            continue;
                        }

                        /*
                         * Mesma disciplina nas duas posições:
                         * - disciplina uma vez;
                         * - professores um abaixo do outro;
                         * - sala e cor da esquerda.
                         *
                         * Compara o ID, não o nome da disciplina.
                         */
                        $mesma_disciplina = isset($posicoes[1], $posicoes[2]) &&
                                (int) $posicoes[1]['id_disciplina'] ===
                                (int) $posicoes[2]['id_disciplina'];

                        if ($mesma_disciplina) {
                            $esquerda = $posicoes[1];
                            $direita = $posicoes[2];

                            $tabela .= '<td style="background-color:';
                            $tabela .= $escapar($esquerda['cor']);
                            $tabela .= ' !important">';

                            $tabela .= $montarOferta($esquerda, $direita);

                            $tabela .= '</td>';
                            continue;
                        }

                        // Oferta única: ocupa toda a célula.
                        if (!isset($posicoes[2])) {
                            $linha = isset($posicoes[1]) ? $posicoes[1] : reset($posicoes);

                            $tabela .= '<td style="background-color:';
                            $tabela .= $escapar($linha['cor']);
                            $tabela .= ' !important">';
                            $tabela .= $montarOferta($linha);
                            $tabela .= '</td>';

                            continue;
                        }

                        /*
                         * Disciplinas diferentes:
                         * mantém as duas posições lado a lado.
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

                        $tabela .= '</tr></tbody></table></td>';
                    }

                    $tabela .= '</tr>';
                }

                $tabela .= '</tbody></table>';
            }
        } else {
            $result_ead = $horarioM->getDisciplinasTurmaEAD(
                    (int) $_POST['id_turma']
            );

            if ($result_ead->num_rows > 0) {
                $tabela .= '<table ';
                $tabela .= 'class="table table-responsive table-bordered">';
                $tabela .= '<thead>';

                $tabela .= '<tr class="success">';
                $tabela .= '<th colspan="5" style="text-align:center">';
                $tabela .= 'Disciplinas/Professor</th></tr>';

                $tabela .= '<tr class="info">';
                $tabela .= '<th width="10%">Módulo</th>';
                $tabela .= '<th width="35%">Disciplina</th>';
                $tabela .= '<th width="35%">Professor</th>';
                $tabela .= '<th width="10%">CHS</th>';
                $tabela .= '<th width="10%">CHT</th>';
                $tabela .= '</tr></thead><tbody>';

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