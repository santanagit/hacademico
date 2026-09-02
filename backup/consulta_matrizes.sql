SELECT * FROM disciplina WHERE id_disciplina NOT IN (
SELECT
    disciplina.id_disciplina/*,
    curso.nome,
    curso.matriz,
    grade.modulo,
	disciplina.descricao,
    disciplina.chs,
    disciplina.chs_ead,
    disciplina.cht*/
FROM
	disciplina INNER JOIN grade 
		ON disciplina.id_disciplina = grade.id_disciplina
	INNER JOIN curso
		ON curso.id_curso = grade.id_curso
ORDER BY
    curso.nome,
    curso.matriz,
    grade.modulo) AND id_disciplina NOT IN (
    
SELECT
	oferta_disciplina.id_disciplina
FROM
	oferta_disciplina)
