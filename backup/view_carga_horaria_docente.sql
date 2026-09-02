
CREATE VIEW carga_horaria_docente AS
(SELECT
	turma.id_periodo,
	oferta_disciplina.`id_usuario`,
	usuario.`nome`,
	SUM(oferta_disciplina.`chs`) AS chs,
	SUM(oferta_disciplina.`chs_ead`) AS chs_ead,
	ROUND(SUM(oferta_disciplina.`cht`)/20,2) AS cht
FROM
	oferta_disciplina INNER JOIN usuario 
		ON oferta_disciplina.`id_usuario` = usuario.id_usuario
	INNER JOIN disciplina
		ON oferta_disciplina.`id_disciplina` = disciplina.`id_disciplina`
	INNER JOIN turma
		ON oferta_disciplina.id_turma = turma.id_turma
WHERE
	turma.turno <> 'Integral'
GROUP BY
	turma.id_periodo,
    usuario.`nome`,
    usuario.id_usuario
ORDER BY
	turma.id_periodo,
	usuario.`nome`)
UNION
(SELECT
	turma.id_periodo,
    oferta_disciplina.`id_usuario`,
	usuario.`nome`,
	SUM(oferta_disciplina.`chs`) AS chs,
	SUM(oferta_disciplina.`chs_ead`) AS chs_ead,
	ROUND(SUM(oferta_disciplina.`cht`)/40,2) AS cht
FROM
	oferta_disciplina INNER JOIN usuario 
		ON oferta_disciplina.`id_usuario` = usuario.id_usuario
	INNER JOIN disciplina
		ON oferta_disciplina.`id_disciplina` = disciplina.`id_disciplina`
	INNER JOIN turma
		ON oferta_disciplina.id_turma = turma.id_turma
WHERE
	turma.turno = 'Integral'
GROUP BY
	turma.id_periodo,
	usuario.`nome`,
    usuario.id_usuario
ORDER BY
	turma.id_periodo,
	usuario.`nome`);