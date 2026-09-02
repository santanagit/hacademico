SELECT 
	 historico_pid.id_pid,
     historico_pid.id_historico_pid,
     usuario.id_usuario,
     usuario.nome AS professor,
     periodo.ano,
     periodo.semestre,
     historico_pid.etapa,
     historico_pid.situacao,
     historico_pid.data_situacao
FROM
	ultimo_historico_pid INNER JOIN historico_pid
		on ultimo_historico_pid.id_historico_pid = historico_pid.id_historico_pid
    INNER JOIN pid
		ON historico_pid.id_pid = pid.id_pid
	INNER JOIN periodo
		ON pid.id_periodo = periodo.id_periodo
	INNER JOIN usuario
		ON pid.id_usuario = usuario.id_usuario
ORDER BY
	usuario.nome,
	periodo.ano,
    periodo.semestre
    