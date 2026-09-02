/*
Apagar os RIDs cadastrados para PIDs de 2024/1
*/
DELETE FROM historico_atividade WHERE id_historico_atividade IN (
    SELECT
		historico_atividade.id_historico_atividade
	FROM
		pid INNER JOIN usuario
			ON pid.id_usuario = usuario.id_usuario
		INNER JOIN periodo
			ON pid.id_periodo = periodo.id_periodo
		INNER JOIN atividade_docente
			ON atividade_docente.id_pid = pid.id_pid
		INNER JOIN atividade
			ON atividade_docente.id_atividade = atividade.id_atividade
		INNER JOIN historico_atividade
			ON atividade_docente.id_atividade_docente = historico_atividade.id_atividade_docente
	WHERE
		periodo.ano = 2024 AND
		periodo.semestre = 1 AND
		etapa = 'RID'
);

/*
Apagar o RID do Heber do historico_pid
*/
DELETE FROM historico_pid WHERE id_historico_pid = 144;

/*
Apaga PID da professora SARA (historico_atividade)
*/
DELETE FROM historico_atividade WHERE id_historico_atividade IN (
SELECT
	historico_atividade.id_historico_atividade
FROM
	pid INNER JOIN usuario
		ON pid.id_usuario = usuario.id_usuario
	INNER JOIN periodo
		ON pid.id_periodo = periodo.id_periodo
	INNER JOIN atividade_docente
		ON atividade_docente.id_pid = pid.id_pid
	INNER JOIN atividade
		ON atividade_docente.id_atividade = atividade.id_atividade
	INNER JOIN historico_atividade
		ON atividade_docente.id_atividade_docente = historico_atividade.id_atividade_docente
WHERE
	periodo.ano = 2024 AND
	periodo.semestre = 1 AND
	pid.id_pid = 29
);

/*
Apaga PID da professora SARA (atividade_docente)
*/
DELETE FROM atividade_docente WHERE id_atividade_docente IN (
SELECT
	atividade_docente.id_atividade_docente
FROM
	pid INNER JOIN usuario
		ON pid.id_usuario = usuario.id_usuario
	INNER JOIN periodo
		ON pid.id_periodo = periodo.id_periodo
	INNER JOIN atividade_docente
		ON atividade_docente.id_pid = pid.id_pid
	INNER JOIN atividade
		ON atividade_docente.id_atividade = atividade.id_atividade	
WHERE
	periodo.ano = 2024 AND
	periodo.semestre = 1 AND
	pid.id_pid = 29
);

/*
Apaga PID da professora SARA (historico_pid)
*/
DELETE FROM historico_pid WHERE historico_pid.id_pid = 29;

/*
Apaga PID da professora SARA (pid)
*/
DELETE FROM pid WHERE pid.id_pid = 29;

/*
Apaga os historicos dos PIDs aprovados sem aprovação das atividades
*/
DELETE FROM historico_pid WHERE id_historico_pid IN (
SELECT
	#historico_pid.id_pid,
	MAX(historico_pid.id_historico_pid) AS id_historico_pid
FROM
	pid INNER JOIN usuario
		ON pid.id_usuario = usuario.id_usuario
	INNER JOIN historico_pid
		ON pid.id_pid = historico_pid.id_pid
	INNER JOIN periodo
		ON pid.id_periodo = periodo.id_periodo
WHERE
	periodo.ano = 2024 AND
	periodo.semestre = 1 AND
	pid.id_pid >= 30 AND pid.id_pid <= 39 AND
	historico_pid.situacao = 'APROVADO'
GROUP BY
	historico_pid.id_pid
);

/*
Script para aprovar todas as atividades propostas nos PIDs.
*/
INSERT INTO historico_atividade (id_atividade_docente,etapa,situacao,observacao,data_situacao,id_usuario_avaliador)
SELECT
	atividade_docente.id_atividade_docente AS id_atividade_docente,
	'PID' AS etapa,
	'APROVADA' AS situacao,
	NULL AS observacao,
	'2024-08-19 11:00:00' AS data_situacao,
	285 AS id_usuario_avaliador 
FROM
	pid INNER JOIN usuario
		ON pid.id_usuario = usuario.id_usuario
	INNER JOIN periodo
		ON pid.id_periodo = periodo.id_periodo
	INNER JOIN atividade_docente
		ON atividade_docente.id_pid = pid.id_pid
	INNER JOIN atividade
		ON atividade_docente.id_atividade = atividade.id_atividade
	INNER JOIN historico_atividade
		ON atividade_docente.id_atividade_docente = historico_atividade.id_atividade_docente
WHERE
	periodo.ano = 2024 AND
	periodo.semestre = 1 AND
	pid.id_pid >= 30 AND pid.id_pid <= 39;

/*
Script para aprovar todos os PIDs.
*/
INSERT INTO historico_pid (id_pid,etapa,situacao,data_situacao)
SELECT
	pid.id_pid,
	'PID' AS etapa,
	'APROVADO' AS situacao,
	'2024-08-19 11:00:00' AS data_situacao
FROM
	pid INNER JOIN usuario
		ON pid.id_usuario = usuario.id_usuario
	INNER JOIN periodo
		ON pid.id_periodo = periodo.id_periodo	
WHERE
	periodo.ano = 2024 AND
	periodo.semestre = 1 AND
	pid.id_pid >= 30 AND pid.id_pid <= 39;

	
/*
Consulta de checagem dos dados
*/
 SELECT
		pid.id_pid,
		usuario.nome,
		atividade.descricao,
		situacao,
		data_situacao
	FROM
		pid INNER JOIN usuario
			ON pid.id_usuario = usuario.id_usuario
		INNER JOIN periodo
			ON pid.id_periodo = periodo.id_periodo
		INNER JOIN atividade_docente
			ON atividade_docente.id_pid = pid.id_pid
		INNER JOIN atividade
			ON atividade_docente.id_atividade = atividade.id_atividade
		INNER JOIN historico_atividade
			ON atividade_docente.id_atividade_docente = historico_atividade.id_atividade_docente
	WHERE
		periodo.ano = 2024 AND
		periodo.semestre = 1;
	
