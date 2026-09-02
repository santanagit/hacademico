SELECT
	grade.id_grade,
    curso.nome,
    disciplina.`id_disciplina`,
    disciplina.`chs`,
    disciplina.`cht`,
    disciplina.`chs_ead`,
    disciplina.descricao,
    disciplina.id_disciplina,
    24 AS id_curso,
    grade.modulo
FROM
	grade INNER JOIN curso ON
		grade.id_curso = curso.id_curso
	INNER JOIN disciplina ON
		grade.id_disciplina = disciplina.id_disciplina
WHERE
	curso.id_curso = 27;