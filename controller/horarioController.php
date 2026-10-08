<?php

session_start();
require_once $_SESSION['diretorio_base'] . '/model/oferta_disciplinaModel.php';
require_once $_SESSION['diretorio_base'] . '/model/turmaModel.php';
require_once $_SESSION['diretorio_base'] . '/model/disciplinaModel.php';
require_once $_SESSION['diretorio_base'] . '/model/cursoModel.php';
require_once $_SESSION['diretorio_base'] . '/model/usuarioModel.php';
require_once $_SESSION['diretorio_base'] . '/model/periodoModel.php';
require_once $_SESSION['diretorio_base'] . '/model/horarioModel.php';
require_once $_SESSION['diretorio_base'] . '/model/salaModel.php';

class horarioController {

    private $horarioM;
    private $etapa = 'Inicialização';

    public function __construct() {
        $this->horarioM = new horarioModel();
    }

    public function getEtapa() {
        return $this->etapa;
    }

    private function escapar($valor) {
        return htmlspecialchars((string) $valor, ENT_QUOTES, 'UTF-8');
    }

    private function mensagem($texto, $tipo = 'info') {
        $icone = 'info-sign';

        if ($tipo === 'success') {
            $icone = 'ok';
        } elseif ($tipo === 'warning') {
            $icone = 'warning-sign';
        }

        return '<span class="glyphicon glyphicon glyphicon-' . $icone .
                ' alert-' . $tipo .
                ' btn-sm" style="width:100%; text-align:center">&nbsp;' .
                $texto . '</span>';
    }

    private function resposta($dados) {
        return json_encode(
                $dados,
                JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE
        );
    }

    private function semestre() {
        $partes = isset($_POST['periodo']) ? explode('/', (string) $_POST['periodo']) : array();

        return isset($partes[1]) ? (int) $partes[1] : 1;
    }

    private function salas() {
        $model = new salaModel();
        $resultado = $model->listar();
        $salas = array();

        while ($linha = $resultado->fetch_assoc()) {
            $salas[(int) $linha['id_sala']] = $linha['descricao'];
        }

        return $salas;
    }

    private function ofertas($id_turma) {
        $model = new oferta_disciplinaModel();
        $resultado = $model->getDisciplinasOfertadas($id_turma);
        $ofertas = array();

        while ($linha = $resultado->fetch_assoc()) {
            /*
             * Compatibilidade com as duas formas de retorno
             * do model de ofertas. Nesta consulta, chs e chs_ead
             * são os campos da disciplina.
             */
            if (!isset($linha['chs_disciplina'])) {
                $linha['chs_disciplina'] = isset($linha['chs']) ? $linha['chs'] : 0;
            }

            if (!isset($linha['chs_ead_disciplina'])) {
                $linha['chs_ead_disciplina'] = isset($linha['chs_ead']) ? $linha['chs_ead'] : 0;
            }

            $ofertas[(int) $linha['id_oferta_disciplina']] = $linha;
        }

        return $ofertas;
    }

    private function buscarOferta($id_oferta) {
        $model = new oferta_disciplinaModel();
        $resultado = $model->getOfertaDisciplina($id_oferta);
        return $resultado->fetch_assoc();
    }

    /*
     * Uma contribuição por turma + disciplina em cada célula.
     * EAD integra CHS, preservando o comportamento anterior.
     */

    private function contribuicao($horarios, $salas) {
        if (!is_array($horarios)) {
            throw new RuntimeException(
                            'Retorno incompatível de horarioModel::getHorario(). ' .
                            'Esperado: array por posição. Recebido: ' .
                            gettype($horarios) . '. ' .
                            'Verifique o model carregado em ' .
                            $_SESSION['diretorio_base'] . '/model/horarioModel.php'
                    );
        }

        $contagem = array();

        foreach ($horarios as $horario) {
            if (!is_array($horario) ||
                    !isset($horario['id_turma']) ||
                    !isset($horario['id_disciplina']) ||
                    !isset($horario['id_sala'])) {
                throw new RuntimeException(
                                'Registro de horário incompatível com a contagem ' .
                                'por turma e disciplina.'
                        );
            }

            $chave = $horario['id_turma'] . '_' .
                    $horario['id_disciplina'];

            if (!isset($contagem[$chave])) {
                $contagem[$chave] = array(
                    'chs' => 1,
                    'ead' => 0
                );
            }

            $id_sala = (int) $horario['id_sala'];

            if (isset($salas[$id_sala]) &&
                    $salas[$id_sala] === 'EAD') {
                $contagem[$chave]['ead'] = 1;
            }
        }

        return $contagem;
    }

    private function montarPosicao(
            $chave,
            $posicao,
            $horarios,
            $ofertas,
            $salas,
            $id_esquerda
    ) {
        $horario = isset($horarios[$posicao]) ? $horarios[$posicao] : array();

        $selecionada = isset($horario['id_oferta_disciplina']) ? (int) $horario['id_oferta_disciplina'] : 0;

        $id_sala = isset($horario['id_sala']) ? (int) $horario['id_sala'] : 0;

        $sufixo = $chave . '_' . $posicao;

        $html = 'Disciplina:<br>';
        $html .= '<select style="width:100%" id="d_' . $sufixo . '"';
        $html .= ' onchange="gravarOferta(this)"';
        $html .= ' onfocus="setDisciplina(this)"';
        $html .= ' onmouseover="this.title=this.options[this.selectedIndex].text">';
        $html .= '<option value=""></option>';

        foreach ($ofertas as $id_oferta => $oferta) {
            /*
             * Não exibe no select ofertas sem professor.
             * Mantém essas ofertas disponíveis para a carga horária.
             */
            if (
                    !isset($oferta['id_usuario']) ||
                    (int) $oferta['id_usuario'] <= 0 ||
                    !isset($oferta['professor']) ||
                    trim((string) $oferta['professor']) === ''
            ) {
                continue;
            }

            /*
             * Na direita, somente ofertas divididas,
             * diferentes da oferta selecionada na esquerda.
             */
            if (
                    $posicao === 2 &&
                    (
                    (int) $oferta['turma_dividida'] !== 1 ||
                    (int) $id_oferta === $id_esquerda
                    )
            ) {
                continue;
            }

            $valor = $id_oferta . '_' .
                    $oferta['id_usuario'] . '_' .
                    $oferta['id_disciplina'];

            $html .= '<option value="' . $this->escapar($valor) . '"';

            if ((int) $id_oferta === $selecionada) {
                $html .= ' selected="selected"';
            }

            $html .= '>' . $this->escapar($oferta['disciplina']) .
                    ' (' . $this->escapar($oferta['professor']) . ')</option>';
        }

        $html .= '</select><br><br>Sala:<br>';
        $html .= '<select style="width:100%" id="s_' . $sufixo . '"';
        $html .= ' onfocus="this.oldvalue=this.value"';
        $html .= ' onchange="gravarSala(this)">';

        foreach ($salas as $sala_id => $descricao) {
            $html .= '<option value="' . (int) $sala_id . '"';

            if ((int) $sala_id === $id_sala) {
                $html .= ' selected="selected"';
            }

            $html .= '>' . $this->escapar($descricao) . '</option>';
        }

        $html .= '</select>';

        return $html;
    }

    private function montarCelula($chave, $horarios, $ofertas, $salas) {
        if (count($ofertas) === 0) {
            return '';
        }

        $id_esquerda = isset($horarios[1]) ? (int) $horarios[1]['id_oferta_disciplina'] : 0;

        $dividida = isset($ofertas[$id_esquerda]) &&
                (int) $ofertas[$id_esquerda]['turma_dividida'] === 1;

        if (!$dividida) {
            return $this->montarPosicao(
                            $chave, 1, $horarios, $ofertas, $salas, $id_esquerda
                    );
        }

        $html = '<div style="display:flex;gap:6px">';
        $html .= '<div style="width:50%;min-width:0">';
        $html .= $this->montarPosicao(
                $chave, 1, $horarios, $ofertas, $salas, $id_esquerda
        );
        $html .= '</div><div style="width:50%;min-width:0">';
        $html .= $this->montarPosicao(
                $chave, 2, $horarios, $ofertas, $salas, $id_esquerda
        );
        $html .= '</div></div>';

        return $html;
    }

    public function carregarPeriodo() {
        $this->etapa = 'Carregar períodos';

        $select = '<label for="id_periodo">Periodo:</label>';
        $select .= '<select id="id_periodo" name="id_periodo" class="form-control">';

        $model = new periodoModel();
        $criterios = array();

        if ($_SESSION['perfil'] === 'Professor') {
            $criterios['publicado'] = 1;
        }

        if ($_SESSION['perfil'] === 'Coordenador de Curso') {
            $criterios['publicado_coordenador'] = 1;
        }

        $resultado = $model->listar(
                array(), array('id_periodo' => 'DESC'), array(), $criterios
        );

        while ($linha = $resultado->fetch_assoc()) {
            $select .= '<option value="' . (int) $linha['id_periodo'] . '">';
            $select .= $this->escapar($linha['ano'] . '/' . $linha['semestre']);
            $select .= '</option>';
        }

        $select .= '</select>';
        return $this->resposta(array('select' => $select));
    }

    public function getMoldura() {
        $this->etapa = 'Carregar moldura';

        $salas = $this->salas();
        $model = new oferta_disciplinaModel();
        $validos = array('Matutino', 'Vespertino', 'Integral', 'Noturno', 'EAD');

        $turno = isset($_POST['turno']) &&
                in_array($_POST['turno'], $validos, true) ? $_POST['turno'] : 'Integral';

        $turmas = $model->getTurmasAtivas(
                (int) $_POST['id_periodo'], $this->semestre(), $turno
        );

        $html = '';

        while ($turma = $turmas->fetch_assoc()) {
            $id_turma = (int) $turma['id_turma'];
            $ofertas = $this->ofertas($id_turma);
            $disciplinas = array();
            $distribuida = array();

            foreach ($ofertas as $oferta) {
                $chave = $id_turma . '_' . $oferta['id_disciplina'];

                if (!isset($disciplinas[$chave])) {
                    $disciplinas[$chave] = array(
                        'nome' => $oferta['disciplina'],
                        'chs' => $oferta['chs_disciplina'],
                        'ead' => $oferta['chs_ead_disciplina'],
                        'professores' => array()
                    );
                    $distribuida[$chave] = array('chs' => 0, 'ead' => 0);
                }

                if (trim((string) $oferta['professor']) !== '') {
                    $disciplinas[$chave]['professores'][$oferta['professor']] = $oferta['professor'];
                }
            }

            $html .= '<div class="container-fluid"><div class="col-md-12">';
            $html .= '<div class="panel panel-success">';
            $html .= '<div class="panel panel-heading">Turma: ' .
                    $this->escapar($turma['turma']) . '</div>';
            $html .= '<div class="panel panel-body">';

            $moldura = $this->horarioM->getMoldura(
                    $turma['turno'], $turma['nivel']
            );

            if ($moldura) {
                $html .= '<div class="col-md-8">';
                $html .= '<table class="table table-striped table-hover">';
                $html .= '<thead><tr><th>&nbsp;</th>';
                $html .= '<th>Segunda</th><th>Terça</th><th>Quarta</th>';
                $html .= '<th>Quinta</th><th>Sexta</th><th>Sábado</th>';
                $html .= '</tr></thead><tbody>';

                $hora_anterior = '';

                while ($linha = $moldura->fetch_assoc()) {
                    if ($hora_anterior != $linha['id_hora']) {
                        if ($hora_anterior !== '') {
                            $html .= '</tr>';
                        }

                        $html .= '<tr>';
                        $html .= '<td style="vertical-align:middle;text-align:center;width:10%"><b>';
                        $html .= $this->escapar(substr($linha['inicio'], 0, 5));
                        $html .= '<br> as <br>';
                        $html .= $this->escapar(substr($linha['fim'], 0, 5));
                        $html .= '</b></td>';
                    }

                    $chave = $id_turma . '_' .
                            $linha['id_dia'] . '_' . $linha['id_hora'];

                    $horarios = $this->horarioM->getHorario(
                            $linha['id_dia'], $linha['id_hora'], $id_turma
                    );

                    foreach ($this->contribuicao($horarios, $salas)
                    as $disciplina => $valores) {
                        if (isset($distribuida[$disciplina])) {
                            $distribuida[$disciplina]['chs'] += $valores['chs'];
                            $distribuida[$disciplina]['ead'] += $valores['ead'];
                        }
                    }

                    $html .= '<td><div id="m_' . $chave . '">';
                    $html .= $this->mensagem('');
                    $html .= '</div><div id="c_' . $chave . '">';
                    $html .= $this->montarCelula(
                            $chave, $horarios, $ofertas, $salas
                    );
                    $html .= '</div></td>';

                    $hora_anterior = $linha['id_hora'];
                }

                if ($hora_anterior !== '') {
                    $html .= '</tr>';
                }

                $html .= '</tbody></table></div><div class="col-md-4">';
            } else {
                $html .= '<div class="col-md-12">';
            }

            $html .= '<table class="table table-striped table-hover" style="margin-top:30px">';
            $html .= '<thead><tr style="font-size:14px; background-color:#F0FFF0">';
            $html .= '<th>&nbsp;</th>';

            if (!$moldura) {
                $html .= '<th>&nbsp;</th>';
            }

            $html .= '<th colspan="2" style="text-align:left">Carga Horária</th>';

            if ($moldura) {
                $html .= '<th colspan="2" style="text-align:center">Carga Horária <br>Distribuída</th>';
            }

            $html .= '</tr><tr style="font-size:14px"><th>Disciplina</th>';

            if (!$moldura) {
                $html .= '<th>Professor</th>';
            }

            $html .= '<th>CHS</th><th>EAD</th>';

            if ($moldura) {
                $html .= '<th style="background-color:#FFFFF0">CHS</th>';
                $html .= '<th style="background-color:#FFFFF0">EAD</th>';
            }

            $html .= '</tr></thead><tbody>';

            foreach ($disciplinas as $chave => $disciplina) {
                $html .= '<tr><td>' . $this->escapar($disciplina['nome']) . '</td>';

                if (!$moldura) {
                    $html .= '<td>' .
                            $this->escapar(implode(', ', $disciplina['professores'])) .
                            '</td>';
                }

                $html .= '<td id="chs_disciplina_' . $chave .
                        '" style="font-weight:bold">' .
                        $this->escapar($disciplina['chs']) . '</td>';

                $html .= '<td id="chs_ead_disciplina_' . $chave .
                        '" style="font-weight:bold">' .
                        $this->escapar($disciplina['ead']) . '</td>';

                if ($moldura) {
                    $cor = $distribuida[$chave]['chs'] == $disciplina['chs'] ? 'blue' : 'red';
                    $cor_ead = $distribuida[$chave]['ead'] == $disciplina['ead'] ? 'green' : 'red';

                    $html .= '<td id="' . $chave .
                            '" style="background-color:#FFFFF0;font-weight:bold;color:' .
                            $cor . '">' . $distribuida[$chave]['chs'] . '</td>';

                    $html .= '<td id="ead_' . $chave . '" title="ead_' . $chave .
                            '" style="background-color:#FFFFF0;font-weight:bold;color:' .
                            $cor_ead . '">' . $distribuida[$chave]['ead'] . '</td>';
                }

                $html .= '</tr>';
            }

            $html .= '</tbody></table></div></div></div></div></div>';
        }

        return $this->resposta(array('moldura' => $html));
    }

    private function verificarChoque($oferta, $horarios, $posicao) {
        $id_usuario = (int) $oferta['id_usuario'];

        if ($id_usuario <= 0) {
            return '';
        }

        $id_ignorado = isset($horarios[$posicao]) ? (int) $horarios[$posicao]['id_horario'] : 0;

        $resultado = $this->horarioM->existeChoque(
                $id_usuario,
                (int) $_POST['id_dia'],
                (int) $_POST['id_hora'],
                (int) $_POST['id_periodo'],
                $this->semestre()
        );

        while ($linha = $resultado->fetch_assoc()) {
            if ((int) $linha['id_horario'] === $id_ignorado) {
                continue;
            }

            return $this->mensagem(
                            'Conflito de horário:<br>Turma: ' .
                            $this->escapar($linha['turma']) .
                            '<br>Disciplina: ' .
                            $this->escapar($linha['disciplina']) . '<br>',
                            'warning'
                    );
        }

        return '';
    }

    public function existeChoque() {
        $this->etapa = 'Verificar choque';

        $id_oferta = isset($_POST['id_oferta_disciplina']) ? (int) $_POST['id_oferta_disciplina'] : 0;
        $posicao = isset($_POST['posicao']) ? (int) $_POST['posicao'] : 1;

        if ($id_oferta === 0) {
            return $this->resposta(array('resultado' => false, 'msg' => ''));
        }

        $oferta = $this->buscarOferta($id_oferta);

        if (!$oferta ||
                (int) $oferta['id_turma'] !== (int) $_POST['id_turma'] ||
                ($posicao !== 1 && $posicao !== 2)) {
            return $this->resposta(array(
                        'resultado' => true,
                        'msg' => $this->mensagem('Oferta ou posição inválida.', 'warning')
            ));
        }

        $horarios = $this->horarioM->getHorario(
                (int) $_POST['id_dia'],
                (int) $_POST['id_hora'],
                (int) $_POST['id_turma']
        );

        $msg = $this->verificarChoque($oferta, $horarios, $posicao);

        return $this->resposta(array(
                    'resultado' => $msg !== '', 'msg' => $msg
        ));
    }

    private function erro($texto) {
        return $this->resposta(array(
                    'resultado' => 'ERRO',
                    'msg' => $this->mensagem($this->escapar($texto), 'warning')
        ));
    }

    public function gravar() {
        $this->etapa = 'Validar dados da gravação';

        $id_turma = isset($_POST['id_turma']) ? (int) $_POST['id_turma'] : 0;
        $id_dia = isset($_POST['id_dia']) ? (int) $_POST['id_dia'] : 0;
        $id_hora = isset($_POST['id_hora']) ? (int) $_POST['id_hora'] : 0;
        $id_oferta = isset($_POST['id_oferta_disciplina']) ? (int) $_POST['id_oferta_disciplina'] : 0;
        $id_sala = isset($_POST['id_sala']) ? (int) $_POST['id_sala'] : 0;
        $posicao = isset($_POST['posicao']) ? (int) $_POST['posicao'] : 1;
        $id_periodo = isset($_POST['id_periodo']) ? (int) $_POST['id_periodo'] : 0;

        if ($id_turma <= 0 || $id_dia <= 0 || $id_hora <= 0 ||
                $id_periodo <= 0 || ($posicao !== 1 && $posicao !== 2)) {
            return $this->erro('Dados de horário inválidos.');
        }

        $this->etapa = 'Consultar célula antes da gravação';
        $salas = $this->salas();
        $horarios = $this->horarioM->getHorario($id_dia, $id_hora, $id_turma);
        $antes = $this->contribuicao($horarios, $salas);

        if ($id_oferta > 0) {
            $this->etapa = 'Validar oferta e professor';
            $oferta = $this->buscarOferta($id_oferta);

            if (!$oferta || (int) $oferta['id_turma'] !== $id_turma) {
                return $this->erro('A oferta não pertence a esta turma.');
            }

            if (!isset($salas[$id_sala])) {
                return $this->erro('Preencha o campo sala de aula!');
            }

            $choque = $this->verificarChoque($oferta, $horarios, $posicao);

            if ($choque !== '') {
                return $this->resposta(array(
                            'resultado' => 'ERRO', 'msg' => $choque
                ));
            }
        }

        $this->etapa = 'Gravar célula no banco';

        /*
         * O model lança exceção em erro técnico.
         * Não há retorno false que esconda a causa.
         */
        $this->horarioM->gravarCelula(array(
            'id_turma' => $id_turma,
            'id_dia' => $id_dia,
            'id_hora' => $id_hora,
            'id_oferta_disciplina' => $id_oferta,
            'id_sala' => $id_sala,
            'posicao' => $posicao,
            'id_periodo' => $id_periodo,
            'semestre' => $this->semestre()
        ));

        $this->etapa = 'Consultar célula após gravação';
        $depois_horarios = $this->horarioM->getHorario(
                $id_dia, $id_hora, $id_turma
        );
        $depois = $this->contribuicao($depois_horarios, $salas);

        $deltas = array();
        $chaves = array_unique(array_merge(
                        array_keys($antes), array_keys($depois)
                ));

        foreach ($chaves as $chave) {
            $a = isset($antes[$chave]) ? $antes[$chave] : array('chs' => 0, 'ead' => 0);
            $d = isset($depois[$chave]) ? $depois[$chave] : array('chs' => 0, 'ead' => 0);

            $deltas[$chave] = array(
                'chs' => $d['chs'] - $a['chs'],
                'ead' => $d['ead'] - $a['ead']
            );
        }

        /*
         * Falha no log não transforma uma gravação já confirmada
         * em uma falsa mensagem de erro de inserção.
         */
        try {
            $log = new log_acaoModel();
            $log->inserir(array(
                'id_usuario' => $_SESSION['id_usuario'],
                'acao' => 'Administrativo',
                'data_hora' => date('Y-m-d H:i:s')
            ));
        } catch (Throwable $e) {
            error_log('Horário gravado, mas falhou o log: ' . $e->getMessage());
        }

        $this->etapa = 'Montar resposta da gravação';
        $ofertas = $this->ofertas($id_turma);
        $chave_celula = $id_turma . '_' . $id_dia . '_' . $id_hora;

        $texto = $id_oferta === 0 ? 'Excluído!' :
                (isset($horarios[$posicao]) ? 'Atualizado!' : 'Inserido!');

        return $this->resposta(array(
                    'resultado' => 'OK',
                    'msg' => $this->mensagem($texto, 'success'),
                    'celula' => $this->montarCelula(
                            $chave_celula, $depois_horarios, $ofertas, $salas
                    ),
                    'deltas' => $deltas
        ));
    }
}

if (isset($_POST['metodo'])) {
    header('Content-Type: application/json; charset=utf-8');

    $permitidos = array(
        'carregarPeriodo',
        'getMoldura',
        'existeChoque',
        'gravar'
    );

    $metodo = (string) $_POST['metodo'];

    if (!in_array($metodo, $permitidos, true)) {
        echo json_encode(array(
            'resultado' => 'ERRO',
            'msg' => 'Método inválido.'
        ));
        exit;
    }

    try {
        $objeto = new horarioController();
        echo $objeto->$metodo();
    } catch (Throwable $e) {
        $detalhes = $e->getMessage() .
                ' | Arquivo: ' . $e->getFile() .
                ' | Linha: ' . $e->getLine();

        error_log(
                'Erro no horarioController' .
                ' | método=' . $metodo .
                ' | ' . $detalhes
        );

        http_response_code(500);

        $texto_seguro = htmlspecialchars(
                $detalhes,
                ENT_QUOTES | ENT_SUBSTITUTE,
                'UTF-8'
        );

        echo json_encode(
                array(
                    'resultado' => 'ERRO',
                    'msg' =>
                    '<span class="glyphicon glyphicon glyphicon-warning-sign ' .
                    'alert-warning btn-sm" ' .
                    'style="width:100%; text-align:center">' .
                    '&nbsp;Erro ao processar o horário:<br>' .
                    $texto_seguro .
                    '</span>',
                    'erro_tecnico' => $detalhes
                ),
                JSON_INVALID_UTF8_SUBSTITUTE
        );
    }
}