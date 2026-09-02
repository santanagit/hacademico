SELECT
	periodo.id_periodo,
	periodo.ano,
	periodo.semestre,
	pid_inicio,
	pid_fim,
	rid_inicio,
	rid_fim,
	pid.id_pid,
	pid.id_usuario,
	usuario.nome,
	historico_pid.situacao
FROM
	periodo LEFT JOIN 
	(
		usuario INNER JOIN pid ON
			pid.id_usuario = usuario.id_usuario
		LEFT JOIN 
		(
			historico_pid INNER JOIN ultimo_historico_pid ON
				historico_pid.id_pid = ultimo_historico_pid.id_historico_pid
		) ON
			pid.id_pid = historico_pid.id_pid
	) ON
		periodo.id_periodo = pid.id_periodo
WHERE
	periodo.pid_inicio IS NOT NULL;
		

	