
SELECT
	pid.id_pid,
	atividade_docente.id_comprovante,
    periodo.ano,
    periodo.semestre,
    periodo.data_inicio,
    periodo.data_fim,
    atividade_docente.descricao,
    atividade_docente.id_comprovante,
    comprovante.inicio_vigencia,
    comprovante.fim_vigencia
FROM atividade_docente
INNER JOIN pid
    ON atividade_docente.id_pid = pid.id_pid
INNER JOIN periodo
    ON pid.id_periodo = periodo.id_periodo
INNER JOIN comprovante
    ON atividade_docente.id_comprovante = comprovante.id_comprovante
WHERE
    atividade_docente.descricao LIKE '%colegiado%' 
    AND (atividade_docente.descricao LIKE '%Meio Ambiente%')
    #AND (atividade_docente.descricao LIKE '%TGA%' OR atividade_docente.descricao LIKE '%Gestão Ambiental%')
    AND (
        -- Comprovante terminou antes do início do período
        comprovante.fim_vigencia < periodo.data_inicio

        OR

        -- Comprovante começou depois do fim do período
        comprovante.inicio_vigencia > periodo.data_fim
    )
ORDER BY
    periodo.ano,
    periodo.semestre,
    atividade_docente.descricao;