SELECT 
	usuario.nome AS professor,
	comprovante.descricao AS comprovante,
	comprovante.id_comprovante,
	atividade_docente.descricao AS atividade,
	atividade_docente.id_atividade_docente,
	atividade_docente.id_pid,
	historico_atividade.etapa,
	historico_atividade.situacao
FROM
	ultimo_historico_atividade INNER JOIN atividade_docente
		ON ultimo_historico_atividade.id_atividade_docente = atividade_docente.id_atividade_docente
	INNER JOIN historico_atividade
		ON ultimo_historico_atividade.id_historico_atividade = historico_atividade.id_historico_atividade
	INNER JOIN comprovante
		ON atividade_docente.id_comprovante = comprovante.id_comprovante
	INNER JOIN pid
		ON atividade_docente.id_pid = pid.id_pid
	INNER JOIN usuario
		ON pid.id_usuario = usuario.id_usuario
WHERE
	historico_atividade.etapa = 'RID';
	
SELECT * FROM comprovante;

SELECT * FROM atividade_docente WHERE id_comprovante IS NOT NULL;
SELECT * FROM atividade_docente WHERE id_comprovante > 7;

SELECT 
	usuario.nome AS professor,
	comprovante.descricao AS comprovante,
	comprovante.id_comprovante,
	atividade_docente.descricao AS atividade,
	atividade_docente.id_atividade_docente,
	atividade_docente.id_pid,
	historico_atividade.etapa,
	historico_atividade.situacao
FROM
	ultimo_historico_atividade INNER JOIN atividade_docente
		ON ultimo_historico_atividade.id_atividade_docente = atividade_docente.id_atividade_docente
	INNER JOIN historico_atividade
		ON ultimo_historico_atividade.id_historico_atividade = historico_atividade.id_historico_atividade
	INNER JOIN comprovante
		ON atividade_docente.id_comprovante = comprovante.id_comprovante
	INNER JOIN pid
		ON atividade_docente.id_pid = pid.id_pid
	INNER JOIN usuario
		ON pid.id_usuario = usuario.id_usuario
WHERE
	historico_atividade.etapa = 'RID' AND
	usuario.id_usuario = 290;

SELECT * FROM comprovante;
SELECT * FROM atividade_docente WHERE id_atividade_docente = 471;

SELECT 
	usuario.nome AS professor,
	comprovante.descricao AS comprovante,
	comprovante.id_comprovante,
	COUNT(atividade.id_tipo_atividade) AS tipo_atividade
FROM
	ultimo_historico_atividade INNER JOIN atividade_docente
		ON ultimo_historico_atividade.id_atividade_docente = atividade_docente.id_atividade_docente
	INNER JOIN historico_atividade
		ON ultimo_historico_atividade.id_historico_atividade = historico_atividade.id_historico_atividade
	INNER JOIN comprovante 
		ON atividade_docente.id_comprovante = comprovante.id_comprovante
	INNER JOIN atividade
		ON atividade.id_atividade = atividade_docente.id_atividade
	INNER JOIN pid
		ON atividade_docente.id_pid = pid.id_pid
	INNER JOIN usuario
		ON pid.id_usuario = usuario.id_usuario
WHERE
	historico_atividade.etapa = 'RID'		
GROUP BY
	usuario.nome,
	id_comprovante