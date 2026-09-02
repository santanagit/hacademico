SELECT
	usuario.`nome`,
	atividade_docente.id_pid,
	atividade_docente.`descricao`,
	comprovante.descricao AS comprovante,
	comprovante.inicio_vigencia,
	comprovante.fim_vigencia,
	comprovante.id_comprovante
FROM
	usuario INNER JOIN pid 
		ON usuario.`id_usuario` = pid.`id_usuario`
	INNER JOIN atividade_docente
		ON atividade_docente.`id_pid` = pid.`id_pid`
	INNER JOIN comprovante
		ON atividade_docente.`id_comprovante` = comprovante.id_comprovante
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
	
SELECT * FROM comprovante WHERE comprovante.inicio_vigencia IS NOT NULL;	