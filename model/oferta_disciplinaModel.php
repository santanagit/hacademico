<?php

require_once $_SESSION['diretorio_base'] . '/model/conexaoModel.php';

class oferta_disciplinaModel {

    private $bd;

    public function __construct() {
        $conexao = new conexaoModel();
        $this->bd = $conexao->getConexao();
    }

    public function getConselhosDeClasse($id_periodo, $id_usuario) {
        $sql = "SELECT
                    DISTINCT(curso.nome) AS curso
                FROM
                    oferta_disciplina
                    INNER JOIN turma
                        ON oferta_disciplina.id_turma = turma.id_turma
                    INNER JOIN curso
                        ON turma.id_curso = curso.id_curso
                WHERE
                    curso.nivel = 'Técnico' AND
                    turma.id_periodo = $id_periodo AND
                    oferta_disciplina.id_usuario = $id_usuario";

        $stmt = $this->bd->prepare($sql);
        $stmt->execute() or die($this->bd->error);
        return $stmt->get_result();
    }

    public function getCargaHoraria($id_periodo, $semestre) {
        $sql = "SELECT
                    id_usuario,
                    nome,
                    SUM(chs) AS chs,
                    SUM(chs_ead) AS chs_ead
                FROM
                    carga_horaria_docente
                WHERE ";

        if ($semestre == 1) {
            $sql .= "id_periodo = $id_periodo";
        } else {
            $id_periodo_anterior = $id_periodo - 1;
            $sql .= "(
                        id_periodo = $id_periodo OR
                        (
                            id_periodo = $id_periodo_anterior AND
                            modulo = 'Anual'
                        )
                    )";
        }

        $sql .= "
                GROUP BY
                    id_usuario,
                    nome
                ORDER BY
                    nome";

        $stmt = $this->bd->prepare($sql);
        $stmt->execute() or die($this->bd->error);
        return $stmt->get_result();
    }

    public function listar($id_periodo, $nucleo, $id_turma, $semestre, $parametros = array(), $ordenacao = array(), $limit = array()) {
        $sql = "SELECT
                    periodo.ano,
                    periodo.semestre,
                    turma.descricao AS turma,
                    disciplina.descricao AS disciplina,
                    disciplina.chs AS chs_disciplina,
                    disciplina.cht AS cht_disciplina,
                    disciplina.chs_ead AS chs_ead_disciplina,
                    oferta_disciplina.chs,
                    oferta_disciplina.cht,
                    oferta_disciplina.chs_ead,
                    usuario.nome AS professor,
                    oferta_disciplina.id_oferta_disciplina,
                    oferta_disciplina.id_disciplina,
                    turma.id_turma,
                    oferta_disciplina.id_usuario,
                    curso.nucleo,
                    oferta_disciplina.tipo,
                    oferta_disciplina.turma_dividida
                FROM
                    turma
                    LEFT JOIN (
                        oferta_disciplina
                        INNER JOIN disciplina
                            ON oferta_disciplina.id_disciplina = disciplina.id_disciplina
                        LEFT JOIN usuario
                            ON oferta_disciplina.id_usuario = usuario.id_usuario
                    )
                        ON turma.id_turma = oferta_disciplina.id_turma
                    INNER JOIN periodo
                        ON turma.id_periodo = periodo.id_periodo
                    INNER JOIN curso
                        ON turma.id_curso = curso.id_curso
                WHERE ";

        if ($semestre == 1) {
            $sql .= "periodo.id_periodo = $id_periodo";
        } else {
            $id_periodo_anterior = $id_periodo - 1;
            $sql .= "(
                        periodo.id_periodo = $id_periodo OR
                        (
                            periodo.id_periodo = $id_periodo_anterior AND
                            curso.modulo = 'Anual'
                        )
                    )";
        }

        if ($id_turma !== '0') {
            $sql .= " AND turma.id_turma = $id_turma";
        }

        if ($nucleo !== '0') {
            $sql .= " AND curso.nucleo = '$nucleo'";
        }

        if (count($parametros) > 0) {
            $sql .= " AND (";
            $i = 0;

            foreach ($parametros as $key => $value) {
                if ($i > 0) {
                    $sql .= " OR ";
                }

                $sql .= "$key LIKE '%$value%'";
                $i++;
            }

            $sql .= ")";
        }

        if (count($ordenacao) > 0) {
            $sql .= " ORDER BY ";
            $i = 0;

            foreach ($ordenacao as $key => $value) {
                if ($i > 0) {
                    $sql .= ", ";
                }

                $sql .= "$key $value";
                $i++;
            }
        }

        if (count($limit) > 0) {
            $sql .= " LIMIT {$limit['inicio']},{$limit['quantidade']}";
        }

        $stmt = $this->bd->prepare($sql);
        $stmt->execute() or die($this->bd->error);
        return $stmt->get_result();
    }

    public function inserir($campos) {
        $id_usuario_post = isset($campos['id_usuario']) ? trim((string) $campos['id_usuario']) : '';

        if ($id_usuario_post === '' || strtoupper($id_usuario_post) === 'NULL') {
            $id_usuario = 'NULL';
        } else {
            $id_usuario = (int) $id_usuario_post;
        }

        $id_disciplina = (int) $campos['id_disciplina'];
        $id_turma = (int) $campos['id_turma'];
        $chs = (float) $campos['chs'];
        $chs_ead = (float) $campos['chs_ead'];
        $cht = (float) $campos['cht'];

        /*
         * O tipo permanece no banco por compatibilidade, mas novas ofertas
         * serão sempre cadastradas como Aula.
         */
        $sql = "INSERT INTO oferta_disciplina
                    (
                        id_disciplina,
                        id_turma,
                        id_usuario,
                        chs,
                        chs_ead,
                        cht,
                        tipo,
                        turma_dividida
                    )
                VALUES
                    (
                        $id_disciplina,
                        $id_turma,
                        $id_usuario,
                        $chs,
                        $chs_ead,
                        $cht,
                        'Aula',
                        0
                    )";

        $stmt = $this->bd->prepare($sql);
        $result = $stmt->execute() or die($this->bd->error);

        if (!$result) {
            return false;
        }

        return mysqli_stmt_insert_id($stmt);
    }

    public function atualizar($campos) {
        $id_usuario_post = isset($campos['id_usuario']) ? trim((string) $campos['id_usuario']) : '';

        if ($id_usuario_post === '' || strtoupper($id_usuario_post) === 'NULL') {
            $usuario = "id_usuario = NULL";
        } else {
            $usuario = "id_usuario = " . (int) $id_usuario_post;
        }

        $id_oferta_disciplina = (int) $campos['id_oferta_disciplina'];

        $sql = "UPDATE oferta_disciplina
            SET $usuario
            WHERE id_oferta_disciplina = $id_oferta_disciplina";

        $stmt = $this->bd->prepare($sql);
        return $stmt->execute() or die($this->bd->error);
    }

    public function atualizar_chs($campos) {
        $chs = (float) $campos['chs'];
        $cht = (float) $campos['cht'];
        $id_oferta_disciplina = (int) $campos['id_oferta_disciplina'];

        $sql = "UPDATE oferta_disciplina
                SET
                    chs = $chs,
                    cht = $cht
                WHERE id_oferta_disciplina = $id_oferta_disciplina";

        $stmt = $this->bd->prepare($sql);
        return $stmt->execute() or die($this->bd->error);
    }

    public function atualizar_chs_ead($campos) {
        $chs_ead = (float) $campos['chs_ead'];
        $id_oferta_disciplina = (int) $campos['id_oferta_disciplina'];

        $sql = "UPDATE oferta_disciplina
                SET chs_ead = $chs_ead
                WHERE id_oferta_disciplina = $id_oferta_disciplina";

        $stmt = $this->bd->prepare($sql);
        return $stmt->execute() or die($this->bd->error);
    }

    public function atualizar_turma_dividida($campos) {
        $id_oferta_disciplina = isset($campos['id_oferta_disciplina']) ? (int) $campos['id_oferta_disciplina'] : 0;

        $turma_dividida = !empty($campos['turma_dividida']) ? 1 : 0;

        if ($id_oferta_disciplina <= 0) {
            return false;
        }

        try {
            if (!$this->bd->begin_transaction()) {
                throw new RuntimeException('Erro ao iniciar a transação.');
            }

            $sql = "UPDATE oferta_disciplina
                    SET turma_dividida = ?
                    WHERE id_oferta_disciplina = ?";

            $stmt = $this->bd->prepare($sql);

            if (!$stmt) {
                throw new RuntimeException($this->bd->error);
            }

            $stmt->bind_param("ii", $turma_dividida, $id_oferta_disciplina);

            if (!$stmt->execute()) {
                throw new RuntimeException($stmt->error);
            }

            $stmt->close();

            if ($turma_dividida === 0) {
                /*
                 * Exclui a posição 2 das células em que a oferta
                 * desativada ocupa a posição 1.
                 *
                 * A oferta da posição 2 pode ser outra, mas deve
                 * pertencer à mesma turma.
                 */
                $sql = "DELETE direita
                    FROM horario AS direita
                    INNER JOIN oferta_disciplina AS oferta_direita
                        ON direita.id_oferta_disciplina =
                           oferta_direita.id_oferta_disciplina
                    INNER JOIN horario AS esquerda
                        ON esquerda.id_dia = direita.id_dia
                        AND esquerda.id_hora = direita.id_hora
                        AND esquerda.posicao = 1
                    INNER JOIN oferta_disciplina AS oferta_esquerda
                        ON esquerda.id_oferta_disciplina =
                           oferta_esquerda.id_oferta_disciplina
                        AND oferta_esquerda.id_turma =
                            oferta_direita.id_turma
                    WHERE
                        direita.posicao = 2
                        AND esquerda.id_oferta_disciplina = ?";

                $stmt = $this->bd->prepare($sql);

                if (!$stmt) {
                    throw new RuntimeException($this->bd->error);
                }

                $stmt->bind_param("i", $id_oferta_disciplina);

                if (!$stmt->execute()) {
                    throw new RuntimeException($stmt->error);
                }

                $stmt->close();

                /*
                 * Exclui também os horários em que a própria
                 * oferta desativada ocupa a posição 2.
                 */
                $sql = "DELETE FROM horario
                    WHERE
                        posicao = 2
                        AND id_oferta_disciplina = ?";

                $stmt = $this->bd->prepare($sql);

                if (!$stmt) {
                    throw new RuntimeException($this->bd->error);
                }

                $stmt->bind_param("i", $id_oferta_disciplina);

                if (!$stmt->execute()) {
                    throw new RuntimeException($stmt->error);
                }

                $stmt->close();
            }

            if (!$this->bd->commit()) {
                throw new RuntimeException('Erro ao confirmar a transação.');
            }

            return true;
        } catch (Throwable $e) {
            $this->bd->rollback();
            error_log('Erro ao atualizar turma dividida: ' . $e->getMessage());
            return false;
        }
    }

    public function deletar($id_oferta_disciplina) {
        $sql = "DELETE FROM oferta_disciplina
                WHERE id_oferta_disciplina = ?";

        $stmt = $this->bd->prepare($sql);
        $id_oferta_disciplina = (int) $id_oferta_disciplina;
        $stmt->bind_param("i", $id_oferta_disciplina);
        return $stmt->execute() or die($this->bd->error);
    }

    public function getOfertaDisciplina($id_oferta_disciplina) {
        $sql = "SELECT
                    oferta_disciplina.*,
                    usuario.nome,
                    disciplina.descricao
                FROM
                    oferta_disciplina
                    LEFT JOIN usuario
                        ON oferta_disciplina.id_usuario = usuario.id_usuario
                    INNER JOIN disciplina
                        ON oferta_disciplina.id_disciplina = disciplina.id_disciplina
                WHERE oferta_disciplina.id_oferta_disciplina = ?";

        $stmt = $this->bd->prepare($sql);
        $id_oferta_disciplina = (int) $id_oferta_disciplina;
        $stmt->bind_param("i", $id_oferta_disciplina);
        $stmt->execute() or die($this->bd->error);

        return $stmt->get_result();
    }

    public function existeVinculo($id_oferta_disciplina) {
        $id_oferta_disciplina = (int) $id_oferta_disciplina;

        $sql = "SELECT id_horario
                FROM horario
                WHERE id_oferta_disciplina = $id_oferta_disciplina";

        $stmt = $this->bd->prepare($sql);
        $stmt->execute() or die($this->bd->error);
        $stmt->store_result();

        return $stmt->num_rows > 0;
    }

    public function existeOfertaDisciplina($id_disciplina, $id_turma, $id_usuario) {
        $id_disciplina = (int) $id_disciplina;
        $id_turma = (int) $id_turma;

        if (trim((string) $id_usuario) === '' || strtoupper((string) $id_usuario) === 'NULL') {
            $sql = "SELECT id_oferta_disciplina
                    FROM oferta_disciplina
                    WHERE
                        id_disciplina = $id_disciplina AND
                        id_turma = $id_turma AND
                        id_usuario IS NULL";
        } else {
            $id_usuario = (int) $id_usuario;

            $sql = "SELECT id_oferta_disciplina
                    FROM oferta_disciplina
                    WHERE
                        id_disciplina = $id_disciplina AND
                        id_turma = $id_turma AND
                        id_usuario = $id_usuario";
        }

        $stmt = $this->bd->prepare($sql);
        $stmt->execute() or die($this->bd->error);
        $stmt->store_result();

        return $stmt->num_rows > 0;
    }

    public function getTurmasAtivas($id_periodo, $semestre, $turno = '') {
        $sql = "SELECT
                    turma.id_turma,
                    turma.descricao AS turma,
                    turma.turno,
                    turma.vagas,
                    periodo.ano,
                    periodo.semestre,
                    curso.nivel,
                    DATE_FORMAT(periodo.data_inicio,'%d/%m/%Y') AS data_inicio,
                    DATE_FORMAT(periodo.data_fim,'%d/%m/%Y') AS data_fim
                FROM
                    turma
                    INNER JOIN periodo
                        ON turma.id_periodo = periodo.id_periodo
                    INNER JOIN curso
                        ON curso.id_curso = turma.id_curso
                WHERE ";

        $tipos = '';
        $parametros = array();

        if ($semestre == 1) {
            $sql .= "turma.id_periodo = ?";
            $tipos .= 'i';
            $parametros[] = (int) $id_periodo;
        } else {
            $sql .= "(
                        turma.id_periodo = ? OR
                        (
                            turma.id_periodo = ? AND
                            curso.modulo = 'Anual'
                        )
                    )";

            $tipos .= 'ii';
            $parametros[] = (int) $id_periodo;
            $parametros[] = (int) $id_periodo - 1;
        }

        if ($turno !== '') {
            $sql .= " AND turma.turno = ?";
            $tipos .= 's';
            $parametros[] = $turno;
        }

        $sql .= " ORDER BY turma.descricao";

        $stmt = $this->bd->prepare($sql);
        $stmt->bind_param($tipos, ...$parametros);
        $stmt->execute() or die($this->bd->error);

        return $stmt->get_result();
    }

    public function getDisciplinasOfertadas($id_turma) {
        $id_turma = (int) $id_turma;

        $sql = "SELECT
                oferta_disciplina.id_oferta_disciplina,
                oferta_disciplina.id_turma,
                oferta_disciplina.id_usuario,
                oferta_disciplina.id_disciplina,
                disciplina.descricao AS disciplina,
                oferta_disciplina.chs AS chs,
                disciplina.chs_ead AS chs_ead,
                disciplina.cht AS cht,
                usuario.nome AS professor,
                oferta_disciplina.tipo,
                oferta_disciplina.turma_dividida
            FROM
                oferta_disciplina
                INNER JOIN disciplina
                    ON oferta_disciplina.id_disciplina = disciplina.id_disciplina
                LEFT JOIN usuario
                    ON oferta_disciplina.id_usuario = usuario.id_usuario
            WHERE
                oferta_disciplina.id_turma = ?
            ORDER BY
                disciplina.descricao,
                usuario.nome";

        $stmt = $this->bd->prepare($sql);
        $stmt->bind_param("i", $id_turma);
        $stmt->execute() or die($this->bd->error);

        return $stmt->get_result();
    }

    public function getDisciplinasOfertadasPeriodoProfessor($id_periodo, $id_usuario, $semestre) {
        $id_periodo = (int) $id_periodo;
        $id_usuario = (int) $id_usuario;

        $sql = "SELECT
                    oferta_disciplina.id_oferta_disciplina,
                    oferta_disciplina.id_turma,
                    oferta_disciplina.id_usuario,
                    oferta_disciplina.id_disciplina,
                    disciplina.descricao AS disciplina,
                    oferta_disciplina.chs,
                    oferta_disciplina.chs_ead,
                    oferta_disciplina.cht,
                    usuario.nome AS professor,
                    turma.descricao,
                    oferta_disciplina.tipo,
                    oferta_disciplina.turma_dividida
                FROM
                    oferta_disciplina
                    INNER JOIN disciplina
                        ON oferta_disciplina.id_disciplina = disciplina.id_disciplina
                    INNER JOIN turma
                        ON oferta_disciplina.id_turma = turma.id_turma
                    INNER JOIN usuario
                        ON oferta_disciplina.id_usuario = usuario.id_usuario
                    INNER JOIN curso
                        ON curso.id_curso = turma.id_curso
                WHERE ";

        if ($semestre == 2) {
            $id_periodo_anterior = $id_periodo - 1;

            $sql .= "(
                        turma.id_periodo = $id_periodo OR
                        (
                            turma.id_periodo = $id_periodo_anterior AND
                            curso.modulo = 'Anual'
                        )
                    )
                    AND ";
        } else {
            $sql .= "turma.id_periodo = $id_periodo AND ";
        }

        $sql .= "oferta_disciplina.id_usuario = $id_usuario
                 ORDER BY disciplina.descricao";

        $stmt = $this->bd->prepare($sql);
        $stmt->execute() or die($this->bd->error);

        return $stmt->get_result();
    }
}
