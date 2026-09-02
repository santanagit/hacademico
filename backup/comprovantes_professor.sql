	
SELECT
	usuario.nome,
	CONCAT('comprovante_',atividade_docente.id_comprovante,'.pdf') AS arquivo,
	COUNT(ultimo_historico_atividade.`id_atividade_docente`) quantidade_atividades_comprovante,
	periodo.ano,
	periodo.semestre
FROM
	ultimo_historico_atividade INNER JOIN historico_atividade
		ON ultimo_historico_atividade.`id_historico_atividade` = historico_atividade.id_historico_atividade
	INNER JOIN atividade_docente
		ON ultimo_historico_atividade.`id_atividade_docente` = atividade_docente.`id_atividade_docente`
	INNER JOIN atividade
		ON atividade_docente.`id_atividade` = atividade.id_atividade
	INNER JOIN pid
		ON atividade_docente.`id_pid` = pid.`id_pid`
	INNER JOIN usuario
		ON usuario.id_usuario = pid.`id_usuario`
	INNER JOIN periodo
		ON pid.`id_periodo` = periodo.id_periodo
	INNER JOIN comprovante
		ON atividade_docente.`id_comprovante` = comprovante.id_comprovante
WHERE
	historico_atividade.`etapa` = 'RID' AND
	historico_atividade.`situacao` = 'APROVADA'
GROUP BY
	usuario.nome,
	arquivo,
	ano,
	semestre
ORDER BY 
	usuario.nome,
	ano,
	semestre