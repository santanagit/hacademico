<?php

require_once $_SESSION['diretorio_base'] . '/model/conexaoModel.php';

class horarioModel {

    private $bd;

    public function __construct() {
        mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

        $conexao = new conexaoModel();
        $this->bd = $conexao->getConexao();
    }

    private function consultar($sql) {
        $stmt = $this->bd->prepare($sql);
        $stmt->execute();
        return $stmt->get_result();
    }

    private function executar($sql) {
        $stmt = $this->bd->prepare($sql);
        $stmt->execute();
        return true;
    }

    private function condicaoPeriodo($id_periodo, $semestre, $anualIntegral = false) {
        $id_periodo = (int) $id_periodo;

        if ((int) $semestre === 1) {
            return "turma.id_periodo = $id_periodo";
        }

        $anterior = $id_periodo - 1;
        $anual = $anualIntegral ? "turma.turno = 'Integral'" : "curso.modulo = 'Anual'";

        return "(turma.id_periodo = $id_periodo OR
            (turma.id_periodo = $anterior AND $anual))";
    }

    private function faixaHorario() {
        return "IF(curso.turno = 'Noturno',
            CONCAT(DATE_FORMAT(hora.inicio_superior,'%H:%i'),' - ',
                DATE_FORMAT(hora.fim_superior,'%H:%i')),
            IF(curso.turno = 'Integral',
                CONCAT(DATE_FORMAT(hora.inicio_integrado,'%H:%i'),' - ',
                    DATE_FORMAT(hora.fim_integrado,'%H:%i')),
                CONCAT(DATE_FORMAT(hora.inicio_concomitante,'%H:%i'),' - ',
                    DATE_FORMAT(hora.fim_concomitante,'%H:%i'))))";
    }

    private function joinsHorario() {
        return "FROM horario
            INNER JOIN dia ON horario.id_dia = dia.id_dia
            INNER JOIN hora ON horario.id_hora = hora.id_hora
            INNER JOIN sala ON horario.id_sala = sala.id_sala
            INNER JOIN oferta_disciplina
                ON horario.id_oferta_disciplina =
                   oferta_disciplina.id_oferta_disciplina
            INNER JOIN usuario
                ON oferta_disciplina.id_usuario = usuario.id_usuario
            INNER JOIN disciplina
                ON oferta_disciplina.id_disciplina = disciplina.id_disciplina
            INNER JOIN turma
                ON oferta_disciplina.id_turma = turma.id_turma
            INNER JOIN curso ON curso.id_curso = turma.id_curso";
    }

    public function getMoldura($turno, $nivel) {
        if ($turno === 'EAD') {
            return false;
        }

        if ($turno === 'Integral') {
            $condicao = "inicio_integrado < '18:00:00'";
            $inicio = 'inicio_integrado';
            $fim = 'fim_integrado';
        } elseif ($turno === 'Matutino') {
            $condicao = "inicio_integrado < '12:00:00'";
            $inicio = 'inicio_integrado';
            $fim = 'fim_integrado';
        } elseif ($turno === 'Vespertino') {
            $condicao = "inicio_concomitante > '12:00:00'
                AND inicio_concomitante < '18:00:00'";
            $inicio = 'inicio_concomitante';
            $fim = 'fim_concomitante';
        } elseif ($turno === 'Noturno') {
            $condicao = $nivel === 'Técnico' ? "inicio_superior > '17:00:00'" : "inicio_superior > '18:00:00'";
            $inicio = 'inicio_superior';
            $fim = 'fim_superior';
        } else {
            throw new InvalidArgumentException('Turno inválido para moldura.');
        }

        return $this->consultar(
                        "SELECT dia.id_dia, dia.descricao, hora.id_hora,
                $inicio AS inicio, $fim AS fim
             FROM dia, hora
             WHERE $condicao
             ORDER BY id_hora, id_dia"
                );
    }

    public function getTipo() {
        return $this->consultar(
                        "SHOW COLUMNS FROM horario WHERE FIELD = 'tipo'"
                );
    }

    public function disciplinaCadastrada(
            $id_turma, $id_dia, $id_hora, $id_disciplina, $id_usuario
    ) {
        $id_turma = (int) $id_turma;
        $id_dia = (int) $id_dia;
        $id_hora = (int) $id_hora;
        $id_disciplina = (int) $id_disciplina;
        $id_usuario = (int) $id_usuario;

        return $this->consultar(
                        "SELECT horario.id_horario
             FROM horario
             INNER JOIN oferta_disciplina
                ON horario.id_oferta_disciplina =
                   oferta_disciplina.id_oferta_disciplina
             WHERE oferta_disciplina.id_usuario = $id_usuario
               AND horario.id_dia = $id_dia
               AND horario.id_hora = $id_hora
               AND oferta_disciplina.id_turma = $id_turma
               AND oferta_disciplina.id_disciplina = $id_disciplina"
                );
    }

    public function existeChoque(
            $id_usuario, $id_dia, $id_hora, $id_periodo, $semestre
    ) {
        $id_usuario = (int) $id_usuario;
        $id_dia = (int) $id_dia;
        $id_hora = (int) $id_hora;

        // Preserva a regra existente para turmas integrais.
        $periodo = $this->condicaoPeriodo($id_periodo, $semestre, true);

        return $this->consultar(
                        "SELECT horario.id_horario,
                disciplina.descricao AS disciplina,
                turma.descricao AS turma, turma.id_periodo
             FROM horario
             INNER JOIN oferta_disciplina
                ON horario.id_oferta_disciplina =
                   oferta_disciplina.id_oferta_disciplina
             INNER JOIN usuario
                ON oferta_disciplina.id_usuario = usuario.id_usuario
             INNER JOIN disciplina
                ON oferta_disciplina.id_disciplina = disciplina.id_disciplina
             INNER JOIN turma
                ON oferta_disciplina.id_turma = turma.id_turma
             INNER JOIN periodo ON turma.id_periodo = periodo.id_periodo
             WHERE horario.id_dia = $id_dia
               AND horario.id_hora = $id_hora
               AND usuario.id_usuario = $id_usuario
               AND $periodo"
                );
    }

    public function getHorario($id_dia, $id_hora, $id_turma) {
    $id_dia = (int) $id_dia;
    $id_hora = (int) $id_hora;
    $id_turma = (int) $id_turma;

    $sql = "SELECT
                horario.id_horario,
                horario.id_oferta_disciplina,
                horario.id_sala,
                horario.posicao,
                sala.descricao AS sala,
                disciplina.descricao AS disciplina,
                turma.descricao AS turma,
                oferta_disciplina.id_disciplina,
                oferta_disciplina.id_turma,
                oferta_disciplina.id_usuario,
                oferta_disciplina.turma_dividida
            FROM horario
                INNER JOIN oferta_disciplina
                    ON horario.id_oferta_disciplina =
                       oferta_disciplina.id_oferta_disciplina
                INNER JOIN disciplina
                    ON oferta_disciplina.id_disciplina =
                       disciplina.id_disciplina
                INNER JOIN turma
                    ON oferta_disciplina.id_turma = turma.id_turma
                LEFT JOIN sala
                    ON horario.id_sala = sala.id_sala
            WHERE horario.id_dia = ?
              AND horario.id_hora = ?
              AND oferta_disciplina.id_turma = ?
            ORDER BY horario.posicao, horario.id_horario";

    $stmt = $this->bd->prepare($sql);

    if (!$stmt) {
        throw new RuntimeException(
            'Erro ao consultar horário: ' . $this->bd->error
        );
    }

    $stmt->bind_param("iii", $id_dia, $id_hora, $id_turma);

    if (!$stmt->execute()) {
        throw new RuntimeException(
            'Erro ao executar consulta de horário: ' . $stmt->error
        );
    }

    $resultado = $stmt->get_result();

    if (!$resultado) {
        throw new RuntimeException(
            'Não foi possível obter o resultado da consulta de horário.'
        );
    }

    $horarios = array();

    while ($linha = $resultado->fetch_assoc()) {
        $horarios[(int) $linha['posicao']] = $linha;
    }

    $stmt->close();

    return $horarios;
}

    private function excluirPosicao($id_turma, $id_dia, $id_hora, $posicao) {
        $sql = "DELETE h
            FROM horario AS h
            INNER JOIN oferta_disciplina AS o
                ON h.id_oferta_disciplina = o.id_oferta_disciplina
            WHERE o.id_turma = ?
              AND h.id_dia = ?
              AND h.id_hora = ?
              AND h.posicao = ?";

        $stmt = $this->bd->prepare($sql);
        $stmt->bind_param('iiii', $id_turma, $id_dia, $id_hora, $posicao);
        $stmt->execute();
    }

    public function gravarCelula($campos) {
        $id_turma = isset($campos['id_turma']) ? (int) $campos['id_turma'] : 0;

        $id_dia = isset($campos['id_dia']) ? (int) $campos['id_dia'] : 0;

        $id_hora = isset($campos['id_hora']) ? (int) $campos['id_hora'] : 0;

        $id_oferta = isset($campos['id_oferta_disciplina']) ? (int) $campos['id_oferta_disciplina'] : 0;

        $id_sala = isset($campos['id_sala']) ? (int) $campos['id_sala'] : 0;

        $posicao = isset($campos['posicao']) ? (int) $campos['posicao'] : 1;

        $id_usuario = isset($_SESSION['id_usuario']) ? (int) $_SESSION['id_usuario'] : 0;

        $data_hora = date('Y-m-d H:i:s');
        $transacao_iniciada = false;

        if ($id_turma <= 0 || $id_dia <= 0 || $id_hora <= 0 ||
                ($posicao !== 1 && $posicao !== 2)) {
            throw new RuntimeException('Dados de horário inválidos.');
        }

        if ($id_usuario <= 0) {
            throw new RuntimeException(
                            'Usuário de registro não identificado na sessão.'
                    );
        }

        if ($id_oferta > 0 && $id_sala <= 0) {
            throw new RuntimeException('Sala de aula inválida.');
        }

        /*
         * Verifica prepare() mesmo em ambientes nos quais
         * o MySQLi não lança exceções automaticamente.
         */
        $preparar = function ($sql) {
            $stmt = $this->bd->prepare($sql);

            if (!$stmt) {
                throw new RuntimeException(
                                'Erro ao preparar SQL do horário: ' . $this->bd->error
                        );
            }

            return $stmt;
        };

        try {
            if (!$this->bd->begin_transaction()) {
                throw new RuntimeException(
                                'Erro ao iniciar transação: ' . $this->bd->error
                        );
            }

            $transacao_iniciada = true;

            $stmt = $preparar(
                    "SELECT id_turma
             FROM turma
             WHERE id_turma = ?
             FOR UPDATE"
            );

            $stmt->bind_param("i", $id_turma);

            if (!$stmt->execute()) {
                throw new RuntimeException($stmt->error);
            }

            $resultado = $stmt->get_result();

            if (!$resultado || $resultado->num_rows !== 1) {
                throw new RuntimeException('Turma inválida.');
            }

            $dividida = 0;

            if ($id_oferta > 0) {
                $stmt = $preparar(
                        "SELECT turma_dividida
                 FROM oferta_disciplina
                 WHERE id_oferta_disciplina = ?
                   AND id_turma = ?
                 FOR UPDATE"
                );

                $stmt->bind_param("ii", $id_oferta, $id_turma);

                if (!$stmt->execute()) {
                    throw new RuntimeException($stmt->error);
                }

                $oferta = $stmt->get_result()->fetch_assoc();

                if (!$oferta) {
                    throw new RuntimeException(
                                    'A oferta não pertence à turma informada.'
                            );
                }

                $dividida = (int) $oferta['turma_dividida'];

                if ($posicao === 2) {
                    $stmt = $preparar(
                            "SELECT
                        esquerda.id_oferta_disciplina,
                        oferta.turma_dividida
                     FROM horario AS esquerda
                        INNER JOIN oferta_disciplina AS oferta
                            ON esquerda.id_oferta_disciplina =
                               oferta.id_oferta_disciplina
                     WHERE oferta.id_turma = ?
                       AND esquerda.id_dia = ?
                       AND esquerda.id_hora = ?
                       AND esquerda.posicao = 1
                     FOR UPDATE"
                    );

                    $stmt->bind_param(
                            "iii", $id_turma, $id_dia, $id_hora
                    );

                    if (!$stmt->execute()) {
                        throw new RuntimeException($stmt->error);
                    }

                    $esquerda = $stmt->get_result()->fetch_assoc();

                    if (!$esquerda ||
                            (int) $esquerda['turma_dividida'] !== 1 ||
                            $dividida !== 1 ||
                            (int) $esquerda['id_oferta_disciplina'] === $id_oferta) {
                        throw new RuntimeException(
                                        'A posição 2 exige duas ofertas distintas e divididas.'
                                );
                    }
                }
            }

            /*
             * Limpar a esquerda ou selecionar oferta não dividida
             * também exclui a direita desta célula.
             */
            if ($posicao === 1 && ($id_oferta === 0 || $dividida !== 1)) {
                $stmt = $preparar(
                        "DELETE horario
                 FROM horario
                    INNER JOIN oferta_disciplina AS oferta
                        ON horario.id_oferta_disciplina =
                           oferta.id_oferta_disciplina
                 WHERE oferta.id_turma = ?
                   AND horario.id_dia = ?
                   AND horario.id_hora = ?
                   AND horario.posicao = 2"
                );

                $stmt->bind_param(
                        "iii", $id_turma, $id_dia, $id_hora
                );

                if (!$stmt->execute()) {
                    throw new RuntimeException($stmt->error);
                }
            }

            /*
             * Consulta somente os registros da célula,
             * sem carregar a grade inteira.
             */
            $stmt = $preparar(
                    "SELECT horario.id_horario, horario.posicao
             FROM horario
                INNER JOIN oferta_disciplina AS oferta
                    ON horario.id_oferta_disciplina =
                       oferta.id_oferta_disciplina
             WHERE oferta.id_turma = ?
               AND horario.id_dia = ?
               AND horario.id_hora = ?
             ORDER BY horario.id_horario
             FOR UPDATE"
            );

            $stmt->bind_param("iii", $id_turma, $id_dia, $id_hora);

            if (!$stmt->execute()) {
                throw new RuntimeException($stmt->error);
            }

            $resultado = $stmt->get_result();
            $horarios = array();

            while ($linha = $resultado->fetch_assoc()) {
                $horarios[(int) $linha['posicao']] = $linha;
            }

            if ($id_oferta === 0) {
                $stmt = $preparar(
                        "DELETE horario
                 FROM horario
                    INNER JOIN oferta_disciplina AS oferta
                        ON horario.id_oferta_disciplina =
                           oferta.id_oferta_disciplina
                 WHERE oferta.id_turma = ?
                   AND horario.id_dia = ?
                   AND horario.id_hora = ?
                   AND horario.posicao = ?"
                );

                $stmt->bind_param(
                        "iiii", $id_turma, $id_dia, $id_hora, $posicao
                );
            } elseif (isset($horarios[$posicao])) {
                $id_horario = (int) $horarios[$posicao]['id_horario'];

                // Preserva o tipo já existente no banco.
                $stmt = $preparar(
                        "UPDATE horario
                 SET id_oferta_disciplina = ?,
                     id_sala = ?,
                     id_usuario = ?,
                     data_hora = ?
                 WHERE id_horario = ?"
                );

                $stmt->bind_param(
                        "iiisi",
                        $id_oferta,
                        $id_sala,
                        $id_usuario,
                        $data_hora,
                        $id_horario
                );
 } else {
                $stmt = $this->bd->prepare(
                    "INSERT INTO horario
                        (
                            id_dia,
                            id_hora,
                            id_oferta_disciplina,
                            id_sala,
                            posicao,
                            id_usuario,
                            data_hora
                        )
                     VALUES (?, ?, ?, ?, ?, ?, ?)"
                );

                if (!$stmt) {
                    throw new RuntimeException($this->bd->error);
                }

                $stmt->bind_param(
                    "iiiiiis",
                    $id_dia,
                    $id_hora,
                    $id_oferta,
                    $id_sala,
                    $posicao,
                    $id_usuario,
                    $data_hora
                );
            }

            if (!$stmt->execute()) {
                throw new RuntimeException(
                                'Erro ao gravar horário: ' . $stmt->error
                        );
            }

            if (!$this->bd->commit()) {
                throw new RuntimeException(
                                'Erro ao confirmar transação: ' . $this->bd->error
                        );
            }

            $transacao_iniciada = false;

            return true;
        } catch (Throwable $e) {
            if ($transacao_iniciada) {
                try {
                    $this->bd->rollback();
                } catch (Throwable $erroRollback) {
                    error_log(
                            'Erro ao desfazer gravação do horário: ' .
                            $erroRollback->getMessage()
                    );
                }
            }

            error_log(
                    'Erro ao gravar célula de horário' .
                    ' | turma=' . $id_turma .
                    ' | dia=' . $id_dia .
                    ' | hora=' . $id_hora .
                    ' | oferta=' . $id_oferta .
                    ' | sala=' . $id_sala .
                    ' | posição=' . $posicao .
                    ' | erro=' . $e->getMessage() .
                    ' | arquivo=' . $e->getFile() .
                    ' | linha=' . $e->getLine()
            );

            throw $e;
        }
    }

    public function inserir($campos) {
        $id_dia = isset($campos['id_dia']) ? (int) $campos['id_dia'] : 0;

        $id_hora = isset($campos['id_hora']) ? (int) $campos['id_hora'] : 0;

        $id_oferta_disciplina = isset($campos['id_oferta_disciplina']) ? (int) $campos['id_oferta_disciplina'] : 0;

        $id_sala = isset($campos['id_sala']) ? (int) $campos['id_sala'] : 0;

        $posicao = isset($campos['posicao']) ? (int) $campos['posicao'] : 1;

        if ($id_dia <= 0 ||
                $id_hora <= 0 ||
                $id_oferta_disciplina <= 0 ||
                $id_sala <= 0 ||
                ($posicao !== 1 && $posicao !== 2)) {
            return false;
        }

        $id_usuario = (int) $_SESSION['id_usuario'];
        $data_hora = date('Y-m-d H:i:s');

        try {
            $sql = "INSERT INTO horario
                        (
                            id_dia,
                            id_hora,
                            id_oferta_disciplina,
                            id_sala,
                            posicao,
                            id_usuario,
                            data_hora
                        )
                    VALUES (?, ?, ?, ?, ?, ?, ?)";

            $stmt = $this->bd->prepare($sql);

            if (!$stmt) {
                throw new RuntimeException($this->bd->error);
            }

            $stmt->bind_param(
                    "iiiiiis",
                    $id_dia,
                    $id_hora,
                    $id_oferta_disciplina,
                    $id_sala,
                    $posicao,
                    $id_usuario,
                    $data_hora
            );

            if (!$stmt->execute()) {
                throw new RuntimeException($stmt->error);
            }

            $stmt->close();
            return true;
        } catch (Throwable $e) {
            error_log(
                    'Erro ao inserir horário: ' . $e->getMessage()
            );

            return false;
        }
    }

    public function atualizar($campos) {
        $id_horario = (int) $campos['id_horario'];
        $id_dia = (int) $campos['id_dia'];
        $id_hora = (int) $campos['id_hora'];
        $id_oferta = (int) $campos['id_oferta_disciplina'];
        $id_sala = (int) $campos['id_sala'];
        $posicao = isset($campos['posicao']) ? (int) $campos['posicao'] : 1;
        $id_usuario = (int) $_SESSION['id_usuario'];
        $data_hora = date('Y-m-d H:i:s');

        if ($id_horario <= 0 || ($posicao !== 1 && $posicao !== 2)) {
            throw new InvalidArgumentException('Dados de atualização inválidos.');
        }

        $stmt = $this->bd->prepare(
                "UPDATE horario
             SET id_hora = ?, id_dia = ?, id_oferta_disciplina = ?,
                 id_sala = ?, posicao = ?, id_usuario = ?, data_hora = ?
             WHERE id_horario = ?"
        );
        $stmt->bind_param(
                'iiiiiisi',
                $id_hora, $id_dia, $id_oferta, $id_sala,
                $posicao, $id_usuario, $data_hora, $id_horario
        );
        $stmt->execute();
        return true;
    }

    public function excluir($id_horario) {
        $id_horario = (int) $id_horario;
        return $this->executar(
                        "DELETE FROM horario WHERE id_horario = $id_horario"
                );
    }

    public function excluirOfertaDisciplinaProfessor($id_oferta_disciplina, $id_usuario) {
        $id_oferta_disciplina = (int) $id_oferta_disciplina;
        $id_usuario = (int) $id_usuario;

        return $this->executar(
                        "DELETE h FROM horario AS h
             INNER JOIN oferta_disciplina AS o
                ON h.id_oferta_disciplina = o.id_oferta_disciplina
             WHERE h.id_oferta_disciplina = $id_oferta_disciplina
               AND o.id_usuario = $id_usuario"
                );
    }

    public function getDiaOferta($id_oferta_disciplina) {
        $id_oferta_disciplina = (int) $id_oferta_disciplina;

        return $this->consultar(
                        "SELECT DISTINCT horario.id_dia AS id_dia
             FROM horario
             INNER JOIN oferta_disciplina
                ON horario.id_oferta_disciplina =
                   oferta_disciplina.id_oferta_disciplina
             INNER JOIN turma ON oferta_disciplina.id_turma = turma.id_turma
             INNER JOIN periodo ON turma.id_periodo = periodo.id_periodo
             INNER JOIN hora ON horario.id_hora = hora.id_hora
             INNER JOIN dia ON horario.id_dia = dia.id_dia
             INNER JOIN sabados ON horario.id_dia = sabados.id_dia
             WHERE horario.id_oferta_disciplina = $id_oferta_disciplina
               AND sabados.data >= periodo.data_inicio
               AND sabados.data <= periodo.data_fim"
                );
    }

    public function getSabadosOferta($id_oferta_disciplina) {
        $id_oferta_disciplina = (int) $id_oferta_disciplina;

        return $this->consultar(
                        "SELECT horario.id_dia AS id_dia, dia.descricao AS dia,
                IF(turma.turno = 'Noturno', MIN(hora.inicio_superior),
                    IF(turma.turno = 'Integral', MIN(hora.inicio_integrado),
                        MIN(hora.inicio_concomitante))) AS inicio,
                IF(turma.turno = 'Noturno', MIN(hora.fim_superior),
                    IF(turma.turno = 'Integral', MIN(hora.fim_integrado),
                        MIN(hora.fim_concomitante))) AS fim,
                sabados.data,
                IF(turma.turno = 'Noturno',
                    CONCAT('Sábado letivo referente a ', dia.descricao,
                        '-Feira (', MIN(hora.inicio_superior), ' às ',
                        MAX(hora.fim_superior), ')'),
                    IF(turma.turno = 'Integral',
                        CONCAT('Sábado letivo referente a ', dia.descricao,
                            '-Feira (', MIN(hora.inicio_integrado), ' às ',
                            MAX(hora.fim_integrado), ')'),
                        CONCAT('Sábado letivo referente a ', dia.descricao,
                            '-Feira (', MIN(hora.inicio_concomitante), ' às ',
                            MAX(hora.fim_concomitante), ')'))) AS descricao
             FROM horario
             INNER JOIN oferta_disciplina
                ON horario.id_oferta_disciplina =
                   oferta_disciplina.id_oferta_disciplina
             INNER JOIN turma ON oferta_disciplina.id_turma = turma.id_turma
             INNER JOIN periodo ON turma.id_periodo = periodo.id_periodo
             INNER JOIN hora ON horario.id_hora = hora.id_hora
             INNER JOIN dia ON horario.id_dia = dia.id_dia
             INNER JOIN sabados ON horario.id_dia = sabados.id_dia
             WHERE horario.id_oferta_disciplina = $id_oferta_disciplina
               AND sabados.data >= periodo.data_inicio
               AND sabados.data <= periodo.data_fim
             GROUP BY sabados.id_sabados"
                );
    }

    private function selectMapa() {
        $faixa = $this->faixaHorario();

        return "SELECT horario.id_horario, horario.id_hora, horario.id_dia,
            curso.id_curso, oferta_disciplina.id_disciplina,
            oferta_disciplina.id_usuario,
            turma.descricao AS turma, dia.descricao AS dia,
            $faixa AS horario, sala.descricao AS sala,
            disciplina.descricao AS disciplina, usuario.cor,
            usuario.nome AS professor, oferta_disciplina.id_turma,
            curso.nivel, turma.turno ";
    }

    public function mapa_sala($id_sala, $id_periodo, $semestre) {
        $id_sala = (int) $id_sala;
        $periodo = $this->condicaoPeriodo($id_periodo, $semestre);

        return $this->consultar(
                        $this->selectMapa() . $this->joinsHorario() .
                        " INNER JOIN periodo ON turma.id_periodo = periodo.id_periodo
              WHERE horario.id_sala = $id_sala AND $periodo
              ORDER BY horario.id_hora, horario.id_dia"
                );
    }

    public function getGrade($id_disciplina, $id_curso) {
        $id_disciplina = (int) $id_disciplina;
        $id_curso = (int) $id_curso;

        return $this->consultar(
                        "SELECT cod_sigaa FROM grade
             WHERE id_disciplina = $id_disciplina AND id_curso = $id_curso"
                );
    }

    public function listar($id_turma) {
        $id_turma = (int) $id_turma;

        $sql = $this->selectMapa() .
            ", horario.posicao, horario.id_oferta_disciplina " .
            $this->joinsHorario() .
            " WHERE turma.id_turma = $id_turma
              ORDER BY
                  turma,
                  horario.id_hora,
                  horario.id_dia,
                  horario.posicao,
                  horario.id_horario";

        return $this->consultar($sql);
    }

    public function getDisciplinasTurmaEAD($id_turma) {
        $id_turma = (int) $id_turma;

        return $this->consultar(
                        "SELECT oferta_disciplina.id_disciplina,
                oferta_disciplina.id_usuario, curso.id_curso,
                turma.descricao AS turma, disciplina.descricao AS disciplina,
                usuario.nome AS professor, disciplina.chs, disciplina.cht,
                oferta_disciplina.id_turma, curso.nivel, usuario.cor, turma.turno,
                CASE
                    WHEN curso.nivel = 'FIC' THEN grade.modulo
                    WHEN curso.nivel = 'Técnico' AND grade.modulo = 7 THEN 'Optativa'
                    WHEN curso.nivel = 'Graduação' AND grade.modulo = 9 THEN 'Optativa'
                    ELSE grade.modulo
                END AS modulo
             FROM oferta_disciplina
             INNER JOIN usuario ON oferta_disciplina.id_usuario = usuario.id_usuario
             INNER JOIN disciplina
                ON oferta_disciplina.id_disciplina = disciplina.id_disciplina
             INNER JOIN turma ON oferta_disciplina.id_turma = turma.id_turma
             INNER JOIN curso ON curso.id_curso = turma.id_curso
             INNER JOIN grade
                ON grade.id_curso = turma.id_curso
               AND grade.id_disciplina = oferta_disciplina.id_disciplina
             WHERE turma.id_turma = $id_turma
             ORDER BY modulo, disciplina"
                );
    }

    public function listar_ponto($id_periodo) {
        $id_periodo = (int) $id_periodo;

        return $this->consultar(
                        $this->selectMapa() . $this->joinsHorario() .
                        " WHERE turma.id_periodo = $id_periodo
              ORDER BY horario.id_dia, professor, horario.id_hora"
                );
    }

    public function getHorarioEmail($email) {
        $stmt = $this->bd->prepare(
                "SELECT usuario.nome AS funcionario, usuario.email,
                disciplina.descricao AS disciplina, horario.id_horario
             FROM horario
             INNER JOIN oferta_disciplina
                ON horario.id_oferta_disciplina =
                   oferta_disciplina.id_oferta_disciplina
             INNER JOIN usuario ON oferta_disciplina.id_usuario = usuario.id_usuario
             INNER JOIN disciplina
                ON oferta_disciplina.id_disciplina = disciplina.id_disciplina
             WHERE usuario.email = ?"
        );
        $stmt->bind_param('s', $email);
        $stmt->execute();
        return $stmt->get_result();
    }

    public function horariosOfertaDisciplina($id_oferta_disciplina) {
        $id_oferta_disciplina = (int) $id_oferta_disciplina;
        $faixa = $this->faixaHorario();

        return $this->consultar(
                        "SELECT disciplina.descricao AS disciplina,
                dia.descricao AS dia, dia.id_dia, hora.id_hora,
                usuario.nome AS professor, turma.descricao AS turma,
                $faixa AS horario, sabados.data AS sabado " .
                        $this->joinsHorario() .
                        " INNER JOIN periodo ON turma.id_periodo = periodo.id_periodo
              INNER JOIN sabados ON horario.id_dia = sabados.id_dia
              WHERE oferta_disciplina.id_oferta_disciplina = $id_oferta_disciplina
              ORDER BY disciplina.descricao, dia.id_dia, hora.id_hora, sabados.data"
                );
    }

    public function horarioProfessor($id_usuario, $id_periodo, $semestre) {
        $id_usuario = (int) $id_usuario;
        $periodo = $this->condicaoPeriodo($id_periodo, $semestre);

        $faixa = "IF(hora.id_hora > 17,
            CONCAT(DATE_FORMAT(hora.inicio_superior,'%H:%i'),' - ',
                DATE_FORMAT(hora.fim_superior,'%H:%i')),
            IF(hora.id_hora < 12,
                CONCAT(DATE_FORMAT(hora.inicio_integrado,'%H:%i'),' - ',
                    DATE_FORMAT(hora.fim_integrado,'%H:%i')),
                CONCAT('Integrado<br>',
                    IF(hora.inicio_integrado IS NULL,'',
                        DATE_FORMAT(hora.inicio_integrado,'%H:%i')),
                    ' - ',
                    IF(hora.fim_integrado IS NULL,'',
                        DATE_FORMAT(hora.fim_integrado,'%H:%i')),
                    '<br><br>Concomitante<br>',
                    IF(hora.inicio_concomitante IS NULL,'',
                        DATE_FORMAT(hora.inicio_concomitante,'%H:%i')),
                    ' - ',
                    IF(hora.fim_concomitante IS NULL,'',
                        DATE_FORMAT(hora.fim_concomitante,'%H:%i')))))";

        return $this->consultar(
                        "SELECT horario.id_horario, curso.id_curso,
                horario.id_hora, horario.id_dia,
                oferta_disciplina.id_disciplina, oferta_disciplina.id_usuario,
                turma.descricao AS turma, dia.descricao AS dia,
                $faixa AS horario, sala.descricao AS sala,
                disciplina.descricao AS disciplina, oferta_disciplina.cht,
                usuario.id_usuario, usuario.nome AS professor,
                oferta_disciplina.id_turma, turma.turno, curso.nivel " .
                        $this->joinsHorario() .
                        " INNER JOIN periodo ON turma.id_periodo = periodo.id_periodo
              WHERE $periodo AND usuario.id_usuario = $id_usuario
              ORDER BY horario.id_hora, horario.id_dia"
                );
    }

    public function getCargaHorariaEadDocente($id_periodo, $id_usuario) {
        $id_periodo = (int) $id_periodo;
        $id_usuario = (int) $id_usuario;

        return $this->consultar(
                        "SELECT curso.nome AS curso, curso.id_curso, usuario.nome,
                turma.descricao AS turma,
                oferta_disciplina.chs AS chs, oferta_disciplina.cht AS cht,
                disciplina.descricao AS disciplina,
                oferta_disciplina.id_disciplina, oferta_disciplina.id_turma
             FROM oferta_disciplina
             INNER JOIN usuario ON oferta_disciplina.id_usuario = usuario.id_usuario
             INNER JOIN disciplina
                ON oferta_disciplina.id_disciplina = disciplina.id_disciplina
             INNER JOIN turma ON oferta_disciplina.id_turma = turma.id_turma
             INNER JOIN curso ON turma.id_curso = curso.id_curso
             WHERE turma.id_periodo = $id_periodo
               AND oferta_disciplina.id_usuario = $id_usuario
               AND curso.turno = 'EAD'
             ORDER BY curso.nome"
                );
    }

    public function horarioDisciplina($id_oferta_disciplina, $id_periodo) {
        $id_oferta_disciplina = (int) $id_oferta_disciplina;
        $id_periodo = (int) $id_periodo;
        $faixa = $this->faixaHorario();

        return $this->consultar(
                        "SELECT disciplina.descricao AS disciplina,
                dia.descricao AS dia, $faixa AS horario,
                sabados.data AS sabado " .
                        $this->joinsHorario() .
                        " INNER JOIN periodo ON turma.id_periodo = periodo.id_periodo
              INNER JOIN sabados ON horario.id_dia = sabados.id_dia
              WHERE turma.id_periodo = $id_periodo
                AND oferta_disciplina.id_oferta_disciplina = $id_oferta_disciplina
              ORDER BY sabados.data, dia, horario"
                );
    }
}
