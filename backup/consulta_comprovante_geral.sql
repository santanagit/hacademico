SELECT
	usuario.`nome`,
	atividade_docente.id_pid,
	atividade_docente.id_atividade_docente,
	atividade_docente.`descricao`,
	comprovante.descricao AS comprovante,
	comprovante.inicio_vigencia,
	comprovante.fim_vigencia,
	comprovante.id_comprovante,
	historico_atividade.`etapa`,
	historico_atividade.`situacao`,
	historico_atividade.`data_situacao`
FROM
	usuario INNER JOIN pid 
		ON usuario.`id_usuario` = pid.`id_usuario`
	INNER JOIN atividade_docente
		ON atividade_docente.`id_pid` = pid.`id_pid`
	INNER JOIN comprovante
		ON atividade_docente.`id_comprovante` = comprovante.id_comprovante
	INNER JOIN ultimo_historico_atividade
		ON ultimo_historico_atividade.id_atividade_docente = atividade_docente.`id_atividade_docente`
	INNER JOIN historico_atividade
		ON ultimo_historico_atividade.id_historico_atividade = historico_atividade.`id_historico_atividade`
		
WHERE 
	comprovante.inicio_vigencia IS NOT NULL
GROUP BY
	usuario.`nome`,
	atividade_docente.id_pid,
	atividade_docente.`descricao`,
	comprovante.descricao,
	comprovante.inicio_vigencia,
	comprovante.fim_vigencia,
	comprovante.id_comprovante		
ORDER BY
	usuario.nome;
	
