SELECT 
	usuario.nome AS professor,
    atividade_docente.id_atividade_docente,
    atividade_docente.descricao as atividade,
    comprovante.descricao AS comprovante,
    comprovante.id_comprovante,
    historico_atividade.etapa,
    historico_atividade.situacao
FROM
	atividade_docente INNER JOIN pid
		ON atividade_docente.id_pid = pid.id_pid
	INNER JOIN usuario
		ON pid.id_usuario = usuario.id_usuario
	INNER JOIN comprovante
		on atividade_docente.id_comprovante = comprovante.id_comprovante
	INNER JOIN historico_atividade
		on atividade_docente.id_atividade_docente = historico_atividade.id_atividade_docente
	INNER JOIN ultimo_historico_atividade
		on historico_atividade.id_historico_atividade = ultimo_historico_atividade.id_historico_atividade
WHERE
	historico_atividade.etapa = 'RID'