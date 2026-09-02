SELECT
	pid.id_periodo,
	CONCAT(periodo.ano,'/',periodo.semestre) AS periodo,
	pid.id_pid,
	usuario.nome,
	usuario.cor,
	usuario.id_usuario,
	ultimo_historico_atividade.id_atividade_docente,
	atividade.descricao AS tipo_atividade,
	IF(atividade_docente.descricao = '',atividade.descricao,atividade_docente.descricao) AS atividade,
	atividade_docente.horas_executadas,
	comprovante.id_comprovante,
	CONCAT('ccmprovante_',comprovante.id_comprovante) AS arquivo
FROM 
	pid INNER JOIN historico_pid
		ON pid.id_pid = historico_pid.`id_pid`
	INNER JOIN periodo
		ON pid.id_periodo = periodo.id_periodo
	INNER JOIN usuario	
		ON pid.id_usuario = usuario.id_usuario
	INNER JOIN atividade_docente
		ON pid.id_pid = atividade_docente.id_pid
	INNER JOIN atividade
		ON atividade_docente.id_atividade = atividade.id_atividade
	INNER JOIN ultimo_historico_atividade
		ON atividade_docente.id_atividade_docente = ultimo_historico_atividade.id_atividade_docente
	INNER JOIN historico_atividade
		ON ultimo_historico_atividade.id_historico_atividade = historico_atividade.`id_historico_atividade`
	INNER JOIN comprovante
		ON atividade_docente.id_comprovante = comprovante.id_comprovante		
WHERE
	historico_pid.etapa = 'RID' AND
	historico_pid.situacao = 'APROVADO' AND
	historico_atividade.`situacao` = 'APROVADA' AND
	historico_atividade.`etapa` = 'RID' AND
	pid.id_periodo = 15 AND
	#usuario.nome like '%Antonio Rafael Santana%'
	usuario.nome LIKE '%%'
ORDER BY
	usuario.nome, ultimo_historico_atividade.id_atividade_docente
	
