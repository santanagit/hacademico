SELECT 
	log_acao.*,
	usuario.nome
FROM 
	log_acao INNER JOIN usuario
		ON log_acao.id_usuario = usuario.id_usuario
ORDER BY
	id_log DESC;
    
SELECT 
	log_acao.*,
    date(data_hora) as dia,
    usuario.nome
FROM 
	log_acao INNER JOIN usuario
		ON log_acao.id_usuario = usuario.id_usuario
ORDER BY
	data_hora DESC;

SELECT 
	log_acao.*,
    date(data_hora) as dia,
    usuario.nome,
    count(log_acao.id_log)
FROM 
	log_acao INNER JOIN usuario
		ON log_acao.id_usuario = usuario.id_usuario
GROUP BY
    dia,
    usuario.nome  
ORDER BY
	data_hora DESC;
    
SELECT 
	max(data_hora),
    date(data_hora) as dia,
    log_acao.acao,
    usuario.nome,
    count(log_acao.id_log)
FROM 
	log_acao INNER JOIN usuario
		ON log_acao.id_usuario = usuario.id_usuario
#where
#	usuario.id_usuario <> 301
GROUP BY
    dia,
    usuario.nome,
    log_acao.acao
ORDER BY
	data_hora DESC     
    